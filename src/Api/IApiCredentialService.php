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

	/**
	 * Authenticate a bearer token and check a service-wide user credential grant.
	 * @param string $token Presented secret; never log it.
	 * @param string $serviceId Discovered stable service id.
	 * @return CredentialAuthenticationResult Authentication and grant outcome; service principals cannot be granted without an operation.
	 * @throws \Throwable For an unavailable backend.
	 * Side effect: replaces request authentication state.
	 */
	public function authenticateBearer(
		string $token,
		string $serviceId
	): CredentialAuthenticationResult;

	/**
	 * Authenticate a signed request and check a service-wide user credential grant.
	 * @param HmacAuthenticationRequest $request Signed request including replay nonce.
	 * @param string $serviceId Discovered stable service id.
	 * @return CredentialAuthenticationResult Authentication and grant outcome; service principals require authorizeOperation().
	 * @throws \Throwable For an unavailable backend.
	 * Side effect: consumes nonce and replaces request authentication state.
	 */
	public function authenticateHmac(
		HmacAuthenticationRequest $request,
		string $serviceId
	): CredentialAuthenticationResult;
}
