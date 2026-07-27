<?php declare(strict_types=1);

namespace CredentialFoundation\Api;

use CredentialFoundation\Dto\CredentialAuthenticationResult;
use CredentialFoundation\Dto\HmacAuthenticationRequest;

/**
 * Validates API credentials against one logical service.
 *
 * Implementations are responsible for credential lookup, lifecycle checks and
 * service grants. Consumers should depend on this interface instead of a
 * concrete credential-store plugin.
 */
interface IApiCredentialService {

	public function authenticateBearer(
		string $token,
		string $serviceId
	): CredentialAuthenticationResult;

	public function authenticateHmac(
		HmacAuthenticationRequest $request,
		string $serviceId
	): CredentialAuthenticationResult;
}
