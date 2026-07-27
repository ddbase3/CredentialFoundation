<?php declare(strict_types=1);

namespace CredentialFoundation\Test\Dto;

use CredentialFoundation\Dto\CredentialAuthenticationResult;
use PHPUnit\Framework\TestCase;

final class CredentialAuthenticationResultTest extends TestCase {

	public function testCreatesSuccessfulResult(): void {
		$result = CredentialAuthenticationResult::success(
			'key-public-id',
			42,
			'datahawk:report:sales',
			2000000000
		);

		$this->assertTrue($result->isAuthenticated());
		$this->assertSame('', $result->getFailureCode());
		$this->assertSame('key-public-id', $result->getCredentialId());
		$this->assertSame(42, $result->getUserId());
		$this->assertSame('datahawk:report:sales', $result->getServiceId());
		$this->assertSame(2000000000, $result->getExpiresAt());
	}

	public function testCreatesRejectedResultWithoutIdentityData(): void {
		$result = CredentialAuthenticationResult::failure(
			CredentialAuthenticationResult::FAILURE_EXPIRED,
			'datahawk:report:sales'
		);

		$this->assertFalse($result->isAuthenticated());
		$this->assertSame(CredentialAuthenticationResult::FAILURE_EXPIRED, $result->getFailureCode());
		$this->assertSame('', $result->getCredentialId());
		$this->assertNull($result->getUserId());
		$this->assertNull($result->getExpiresAt());
	}
}
