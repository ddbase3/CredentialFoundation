<?php declare(strict_types=1);

namespace CredentialFoundation\Test\Dto;

use CredentialFoundation\Dto\EncryptionTargetDefinition;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EncryptionTargetDefinitionValidationTest extends TestCase {

	public function testTargetRoundTripIncludesOptionalDescription(): void {
		$target = new EncryptionTargetDefinition('service:tenant.v1_instance-2', 'Tenant', 'Encrypted data');

		self::assertSame('service:tenant.v1_instance-2', $target->getTargetId());
		self::assertSame('Tenant', $target->getLabel());
		self::assertSame('Encrypted data', $target->getDescription());
		self::assertSame([
			'target_id' => 'service:tenant.v1_instance-2',
			'label' => 'Tenant',
			'description' => 'Encrypted data'
		], $target->toArray());
	}

	public function testOptionalDescriptionDefaultsToEmpty(): void {
		$target = new EncryptionTargetDefinition('0', 'Numbered tenant');

		self::assertSame('', $target->getDescription());
		self::assertSame('', $target->toArray()['description']);
	}

	public function testIdentifierAcceptsExactly128Characters(): void {
		$id = 'a' . str_repeat('b', 127);
		$target = new EncryptionTargetDefinition($id, 'Longest allowed target');

		self::assertSame($id, $target->getTargetId());
	}

	#[DataProvider('invalidTargetIds')]
	public function testRejectsUnstableTargetIdentifiers(string $targetId): void {
		$this->expectException(InvalidArgumentException::class);
		new EncryptionTargetDefinition($targetId, 'Target');
	}

	public static function invalidTargetIds(): iterable {
		yield 'empty' => [''];
		yield 'leading underscore' => ['_target'];
		yield 'uppercase' => ['Target'];
		yield 'whitespace' => ['tenant one'];
		yield 'path separator' => ['tenant/one'];
		yield 'trailing newline' => ["tenant\n"];
		yield 'over 128 characters' => ['a' . str_repeat('b', 128)];
	}

	#[DataProvider('blankLabels')]
	public function testRejectsBlankTargetLabels(string $label): void {
		$this->expectException(InvalidArgumentException::class);
		new EncryptionTargetDefinition('tenant:one', $label);
	}

	public static function blankLabels(): iterable {
		yield 'empty' => [''];
		yield 'spaces' => ['   '];
		yield 'control whitespace' => ["\t\n"];
	}
}
