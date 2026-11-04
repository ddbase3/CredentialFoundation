<?php declare(strict_types=1);

namespace CredentialFoundation\Test\Dto;

use CredentialFoundation\Dto\CredentialSubject;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CredentialSubjectValidationTest extends TestCase {

	public function testUserSubjectPreservesOpaqueNativeId(): void {
		$subject = new CredentialSubject(CredentialSubject::TYPE_USER, 'External User + 42');

		self::assertTrue($subject->isUser());
		self::assertFalse($subject->isService());
		self::assertSame(CredentialSubject::TYPE_USER, $subject->getType());
		self::assertSame('External User + 42', $subject->getId());
		self::assertSame(['type' => 'user', 'id' => 'External User + 42'], $subject->toArray());
	}

	public function testServiceSubjectUsesStrictStableId(): void {
		$subject = new CredentialSubject(CredentialSubject::TYPE_SERVICE, 'worker:report.v2_name-1');

		self::assertTrue($subject->isService());
		self::assertFalse($subject->isUser());
		self::assertSame(['type' => 'service', 'id' => 'worker:report.v2_name-1'], $subject->toArray());
	}

	public function testBothSubjectTypesAcceptAnIdOfExactly160Bytes(): void {
		$user = new CredentialSubject(CredentialSubject::TYPE_USER, str_repeat('u', 160));
		$service = new CredentialSubject(CredentialSubject::TYPE_SERVICE, 's' . str_repeat('v', 159));

		self::assertSame(160, strlen($user->getId()));
		self::assertSame(160, strlen($service->getId()));
	}

	#[DataProvider('invalidSubjectTypes')]
	public function testRejectsUnknownSubjectType(string $type): void {
		$this->expectException(InvalidArgumentException::class);
		new CredentialSubject($type, 'identity');
	}

	public static function invalidSubjectTypes(): iterable {
		yield 'empty' => [''];
		yield 'uppercase' => ['User'];
		yield 'unknown' => ['admin'];
	}

	#[DataProvider('invalidSharedIdentifiers')]
	public function testRejectsInvalidIdsForBothSubjectTypes(string $id): void {
		foreach ([CredentialSubject::TYPE_USER, CredentialSubject::TYPE_SERVICE] as $type) {
			try {
				new CredentialSubject($type, $id);
				self::fail('An invalid subject ID was accepted for ' . $type);
			} catch (InvalidArgumentException $exception) {
				self::assertNotSame('', $exception->getMessage());
			}
		}
	}

	public static function invalidSharedIdentifiers(): iterable {
		yield 'empty' => [''];
		yield 'surrounding whitespace' => [' id '];
		yield 'leading tab' => ["\tid"];
		yield 'embedded newline' => ["id\nother"];
		yield 'null byte' => ["id\0other"];
		yield 'delete control' => ["id\x7Fother"];
		yield 'over 160 bytes' => [str_repeat('x', 161)];
	}

	#[DataProvider('invalidServiceIdentifiers')]
	public function testServiceIdentityRejectsNonStableIds(string $id): void {
		$this->expectException(InvalidArgumentException::class);
		new CredentialSubject(CredentialSubject::TYPE_SERVICE, $id);
	}

	public static function invalidServiceIdentifiers(): iterable {
		yield 'numeric first character' => ['3worker'];
		yield 'leading underscore' => ['_worker'];
		yield 'uppercase' => ['Worker'];
		yield 'internal whitespace' => ['my worker'];
		yield 'slash' => ['app/worker'];
		yield 'non-ASCII prefix' => ['äworker'];
	}
}
