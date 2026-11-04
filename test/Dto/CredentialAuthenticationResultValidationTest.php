<?php declare(strict_types=1);

namespace CredentialFoundation\Test\Dto;

use CredentialFoundation\Dto\CredentialAuthenticationResult;
use CredentialFoundation\Dto\CredentialSubject;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CredentialAuthenticationResultValidationTest extends TestCase {

	public function testUserAuthenticationIncludesTrustedSubjectAndService(): void {
		$result = CredentialAuthenticationResult::success('public-key', 'user-7', 'api:reports', 2000000000);

		self::assertTrue($result->isAuthenticated());
		self::assertSame('', $result->getFailureCode());
		self::assertSame('public-key', $result->getCredentialId());
		self::assertSame('user-7', $result->getUserId());
		self::assertSame('api:reports', $result->getServiceId());
		self::assertSame(2000000000, $result->getExpiresAt());
		self::assertSame(['type' => 'user', 'id' => 'user-7'], $result->getSubject()?->toArray());
		self::assertSame([
			'authenticated' => true,
			'failure_code' => '',
			'credential_id' => 'public-key',
			'user_id' => 'user-7',
			'subject' => ['type' => 'user', 'id' => 'user-7'],
			'service_id' => 'api:reports',
			'expires_at' => 2000000000
		], $result->toArray());
	}

	public function testHistoricalNullUserSuccessRemainsSubjectless(): void {
		$result = CredentialAuthenticationResult::success('public-key', null, 'api:reports');

		self::assertTrue($result->isAuthenticated());
		self::assertNull($result->getSubject());
		self::assertNull($result->getUserId());
		self::assertNull($result->getExpiresAt());
		self::assertNull($result->toArray()['subject']);
	}

	public function testTechnicalSubjectRequiresNullUserId(): void {
		$subject = new CredentialSubject(CredentialSubject::TYPE_SERVICE, 'service:worker');
		$result = CredentialAuthenticationResult::success('public-key', null, 'api:reports', null, $subject);

		self::assertSame($subject, $result->getSubject());
		self::assertNull($result->getUserId());
		self::assertSame(['type' => 'service', 'id' => 'service:worker'], $result->toArray()['subject']);
	}

	public function testMatchingExplicitUserSubjectIsAccepted(): void {
		$subject = new CredentialSubject(CredentialSubject::TYPE_USER, '42');
		$result = CredentialAuthenticationResult::success('public-key', 42, 'api:reports', null, $subject);

		self::assertSame($subject, $result->getSubject());
		self::assertSame(42, $result->getUserId());
	}

	public function testFailedAuthenticationCannotCarryCredentialIdentity(): void {
		$result = CredentialAuthenticationResult::failure(CredentialAuthenticationResult::FAILURE_OPERATION_NOT_GRANTED, 'api:reports');

		self::assertFalse($result->isAuthenticated());
		self::assertSame(CredentialAuthenticationResult::FAILURE_OPERATION_NOT_GRANTED, $result->getFailureCode());
		self::assertSame('', $result->getCredentialId());
		self::assertNull($result->getUserId());
		self::assertNull($result->getSubject());
		self::assertNull($result->getExpiresAt());
		self::assertSame('api:reports', $result->getServiceId());
		self::assertSame([
			'authenticated' => false,
			'failure_code' => CredentialAuthenticationResult::FAILURE_OPERATION_NOT_GRANTED,
			'credential_id' => '',
			'user_id' => null,
			'subject' => null,
			'service_id' => 'api:reports',
			'expires_at' => null
		], $result->toArray());
	}

	public function testFailureMayOmitServiceId(): void {
		$result = CredentialAuthenticationResult::failure(CredentialAuthenticationResult::FAILURE_INVALID_CREDENTIAL);

		self::assertSame('', $result->getServiceId());
		self::assertFalse($result->isAuthenticated());
	}

	#[DataProvider('invalidText')]
	public function testSuccessRejectsBlankCredentialId(string $value): void {
		$this->expectException(InvalidArgumentException::class);
		CredentialAuthenticationResult::success($value, 42, 'api:reports');
	}

	#[DataProvider('invalidText')]
	public function testSuccessRejectsBlankServiceId(string $value): void {
		$this->expectException(InvalidArgumentException::class);
		CredentialAuthenticationResult::success('public-key', 42, $value);
	}

	#[DataProvider('invalidText')]
	public function testFailureRejectsBlankFailureCode(string $value): void {
		$this->expectException(InvalidArgumentException::class);
		CredentialAuthenticationResult::failure($value);
	}

	public static function invalidText(): iterable {
		yield 'empty' => [''];
		yield 'whitespace' => ['  '];
		yield 'control whitespace' => ["\t\n"];
	}

	public function testServicePrincipalCannotImpersonateAUser(): void {
		$this->expectException(InvalidArgumentException::class);
		CredentialAuthenticationResult::success(
			'public-key', 42, 'api:reports', null,
			new CredentialSubject(CredentialSubject::TYPE_SERVICE, 'service:worker')
		);
	}

	public function testExplicitUserPrincipalMustMatchUserId(): void {
		$this->expectException(InvalidArgumentException::class);
		CredentialAuthenticationResult::success(
			'public-key', 42, 'api:reports', null,
			new CredentialSubject(CredentialSubject::TYPE_USER, '43')
		);
	}

	public function testExplicitUserPrincipalCannotHaveNullUserId(): void {
		$this->expectException(InvalidArgumentException::class);
		CredentialAuthenticationResult::success(
			'public-key', null, 'api:reports', null,
			new CredentialSubject(CredentialSubject::TYPE_USER, '42')
		);
	}
}
