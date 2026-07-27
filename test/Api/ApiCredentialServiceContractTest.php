<?php declare(strict_types=1);

namespace CredentialFoundation\Test\Api;

use CredentialFoundation\Api\IApiCredentialService;
use CredentialFoundation\Dto\CredentialAuthenticationResult;
use CredentialFoundation\Dto\HmacAuthenticationRequest;
use PHPUnit\Framework\TestCase;

final class ApiCredentialServiceContractTest extends TestCase {

	public function testBearerAndHmacChecksUseTheSameResultContract(): void {
		$service = new class implements IApiCredentialService {

			public function authenticateBearer(
				string $token,
				string $serviceId
			): CredentialAuthenticationResult {
				return CredentialAuthenticationResult::success('public-id', 42, $serviceId);
			}

			public function authenticateHmac(
				HmacAuthenticationRequest $request,
				string $serviceId
			): CredentialAuthenticationResult {
				return CredentialAuthenticationResult::failure(
					CredentialAuthenticationResult::FAILURE_INVALID_SIGNATURE,
					$serviceId
				);
			}
		};

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
