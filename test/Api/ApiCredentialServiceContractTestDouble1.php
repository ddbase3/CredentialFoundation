<?php declare(strict_types=1);

namespace CredentialFoundation\Test\Api;

use CredentialFoundation\Api\IApiCredentialService;
use CredentialFoundation\Api\ICredentialOperationAccess;
use CredentialFoundation\Api\ICredentialAccess;
use CredentialFoundation\Dto\CredentialAuthenticationResult;
use CredentialFoundation\Dto\HmacAuthenticationRequest;
use PHPUnit\Framework\TestCase;

final class ApiCredentialServiceContractTestDouble1 implements IApiCredentialService  {

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
		}
