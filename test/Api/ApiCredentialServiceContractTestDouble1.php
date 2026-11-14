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
