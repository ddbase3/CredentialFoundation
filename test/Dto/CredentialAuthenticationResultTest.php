<?php declare(strict_types=1);

/***********************************************************************
 * This file is part of CredentialFoundation for BASE3 Framework.
 *
 * CredentialFoundation defines reusable credential and encryption contracts.
 * It supplies DTOs and stateless signing support for BASE3 integrations.
 * Credential storage and management belong to implementation plugins.
 *
 * Developed by Daniel Dahme
 * Licensed under GPL-3.0
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 *
 * https://base3.de
 * https://github.com/ddbase3/CredentialFoundation
 **********************************************************************/

namespace Base3\CredentialFoundation\Test\Dto;

use Base3\CredentialFoundation\Dto\CredentialAuthenticationResult;
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

	public function testServiceAuthenticationCannotImpersonateAUser(): void {
		$subject = new \Base3\CredentialFoundation\Dto\CredentialSubject('service', 'contourz');
		$result = CredentialAuthenticationResult::success('credential-id', null, 'memora:entries', null, $subject);
		self::assertTrue($result->isAuthenticated());
		self::assertNull($result->getUserId());
		self::assertSame('contourz', $result->getSubject()->getId());
		self::assertSame('service', $result->toArray()['subject']['type']);
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
