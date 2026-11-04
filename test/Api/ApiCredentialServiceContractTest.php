<?php declare(strict_types=1);

namespace CredentialFoundation\Test\Api;

use CredentialFoundation\Api\IApiCredentialService;
use CredentialFoundation\Api\ICredentialOperationAccess;
use CredentialFoundation\Api\ICredentialAccess;
use CredentialFoundation\Dto\CredentialAuthenticationResult;
use CredentialFoundation\Dto\HmacAuthenticationRequest;
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
