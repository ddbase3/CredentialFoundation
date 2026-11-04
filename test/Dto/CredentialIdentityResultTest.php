<?php declare(strict_types=1);

namespace CredentialFoundation\Test\Dto;

use CredentialFoundation\Dto\CredentialIdentityResult;
use CredentialFoundation\Dto\CredentialSubject;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CredentialIdentityResultTest extends TestCase {

	public function testSuccessfulUserIdentityCarriesEveryField(): void {
		$result = CredentialIdentityResult::success('public-key', 42, 2000000000);

		self::assertTrue($result->isAuthenticated());
		self::assertSame('', $result->getFailureCode());
		self::assertSame('public-key', $result->getCredentialId());
		self::assertSame(42, $result->getUserId());
		self::assertSame(2000000000, $result->getExpiresAt());
		self::assertSame(CredentialSubject::TYPE_USER, $result->getSubject()?->getType());
		self::assertSame('42', $result->getSubject()?->getId());
		self::assertSame([
			'authenticated' => true,
			'failure_code' => '',
			'credential_id' => 'public-key',
			'user_id' => 42,
			'subject' => ['type' => 'user', 'id' => '42'],
			'expires_at' => 2000000000
		], $result->toArray());
	}

	public function testStringUserIdIsPreservedWithoutCoercingItsResultType(): void {
		$result = CredentialIdentityResult::success('public-key', 'account-17');

		self::assertSame('account-17', $result->getUserId());
		self::assertSame('account-17', $result->getSubject()?->getId());
		self::assertNull($result->getExpiresAt());
	}

	public function testNullUserIdRetainsHistoricalSubjectlessResult(): void {
		$result = CredentialIdentityResult::success('public-key', null);

		self::assertTrue($result->isAuthenticated());
		self::assertNull($result->getUserId());
		self::assertNull($result->getSubject());
		self::assertNull($result->toArray()['subject']);
	}

	public function testServiceIdentityNeverAcquiresAUserId(): void {
		$subject = new CredentialSubject(CredentialSubject::TYPE_SERVICE, 'service:worker');
		$result = CredentialIdentityResult::success('public-key', null, 2000000000, $subject);

		self::assertSame($subject, $result->getSubject());
		self::assertNull($result->getUserId());
		self::assertSame(['type' => 'service', 'id' => 'service:worker'], $result->toArray()['subject']);
	}

	public function testExplicitMatchingUserSubjectIsAccepted(): void {
		$subject = new CredentialSubject(CredentialSubject::TYPE_USER, '0');
		$result = CredentialIdentityResult::success('public-key', 0, null, $subject);

		self::assertSame($subject, $result->getSubject());
		self::assertSame(0, $result->getUserId());
	}

	public function testRejectedIdentityDoesNotExposeCredentialOrPrincipal(): void {
		$result = CredentialIdentityResult::failure(CredentialIdentityResult::FAILURE_REPLAY_DETECTED);

		self::assertFalse($result->isAuthenticated());
		self::assertSame(CredentialIdentityResult::FAILURE_REPLAY_DETECTED, $result->getFailureCode());
		self::assertSame('', $result->getCredentialId());
		self::assertNull($result->getUserId());
		self::assertNull($result->getExpiresAt());
		self::assertNull($result->getSubject());
		self::assertSame([
			'authenticated' => false,
			'failure_code' => CredentialIdentityResult::FAILURE_REPLAY_DETECTED,
			'credential_id' => '',
			'user_id' => null,
			'subject' => null,
			'expires_at' => null
		], $result->toArray());
	}

	#[DataProvider('invalidText')]
	public function testSuccessRejectsBlankCredentialIdentifier(string $value): void {
		$this->expectException(InvalidArgumentException::class);
		CredentialIdentityResult::success($value, 42);
	}

	#[DataProvider('invalidText')]
	public function testFailureRejectsBlankFailureCode(string $value): void {
		$this->expectException(InvalidArgumentException::class);
		CredentialIdentityResult::failure($value);
	}

	public static function invalidText(): iterable {
		yield 'empty' => [''];
		yield 'spaces' => ['   '];
		yield 'tabs and newlines' => ["\t\n"];
	}

	public function testServiceSubjectCannotImpersonateUser(): void {
		$this->expectException(InvalidArgumentException::class);
		CredentialIdentityResult::success(
			'public-key',
			42,
			null,
			new CredentialSubject(CredentialSubject::TYPE_SERVICE, 'service:worker')
		);
	}

	public function testUserSubjectMustMatchExplicitUserId(): void {
		$this->expectException(InvalidArgumentException::class);
		CredentialIdentityResult::success(
			'public-key',
			42,
			null,
			new CredentialSubject(CredentialSubject::TYPE_USER, '43')
		);
	}

	public function testExplicitUserSubjectCannotBeAssociatedWithNullUserId(): void {
		$this->expectException(InvalidArgumentException::class);
		CredentialIdentityResult::success(
			'public-key',
			null,
			null,
			new CredentialSubject(CredentialSubject::TYPE_USER, '42')
		);
	}
}
