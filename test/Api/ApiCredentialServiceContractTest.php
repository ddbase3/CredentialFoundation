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

namespace Base3\CredentialFoundation\Test\Api;

use Base3\CredentialFoundation\Api\IApiCredentialService;
use Base3\CredentialFoundation\Api\ICredentialOperationAccess;
use Base3\CredentialFoundation\Api\ICredentialAccess;
use Base3\CredentialFoundation\Dto\CredentialAuthenticationResult;
use Base3\CredentialFoundation\Dto\HmacAuthenticationRequest;
use PHPUnit\Framework\TestCase;

final class ApiCredentialServiceContractTest extends TestCase {

	public function testNewOperationCapabilityExtendsUnchangedAccessInterface(): void {
		self::assertTrue(is_subclass_of(ICredentialOperationAccess::class, ICredentialAccess::class));
	}

	public function testBearerAndHmacChecksUseTheSameResultContract(): void {
		$service = new ApiCredentialServiceContractTestDouble1();

		$bearer = $service->authenticateBearer('token', 'demo:report');
		$hmac = $service->authenticateHmac(
			new HmacAuthenticationRequest(
				'token',
				'GET',
				'/api/report',
				'',
				1700000000,
				'nonce',
				'signature'
			),
			'demo:report'
		);

		$this->assertTrue($bearer->isAuthenticated());
		$this->assertFalse($hmac->isAuthenticated());
		$this->assertSame('demo:report', $hmac->getServiceId());
	}
}

require_once __DIR__ . '/ApiCredentialServiceContractTestDouble1.php';
