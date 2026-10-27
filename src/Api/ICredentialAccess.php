<?php declare(strict_types=1);

namespace CredentialFoundation\Api;

use CredentialFoundation\Dto\CredentialAuthenticationResult;
use CredentialFoundation\Dto\CredentialIdentityResult;
use CredentialFoundation\Dto\HmacAuthenticationRequest;

/**
 * Holds the credential identity of the current request and checks service grants.
 *
 * Accesscontrol uses the identity methods to resolve the current BASE3 user.
 * Consumer services call authorizeService() with their own stable service id.
 */
interface ICredentialAccess {

	/**
	 * Clear the current credential and principal between requests.
	 * @return void
	 * @throws \Throwable If the underlying request state cannot be reset.
	 * Side effect: invalidates the in-memory authentication context.
	 */
	public function reset(): void;

	/**
	 * Authenticate a bearer token without granting a service operation.
	 * @param string $token Full secret token; never log it.
	 * @return CredentialIdentityResult Verified identity or failure; service HMAC credentials must reject bearer.
	 * @throws \Throwable For an unavailable credential backend.
	 * Side effect: replaces the current request identity.
	 */
	public function identifyBearer(string $token): CredentialIdentityResult;

	/**
	 * Authenticate a signed request including timestamp, nonce, path and body.
	 * @param HmacAuthenticationRequest $request Complete signed request material.
	 * @return CredentialIdentityResult Verified identity or failure, including replay rejection.
	 * @throws \Throwable For an unavailable credential backend.
	 * Side effect: records a nonce on successful verification and replaces request identity.
	 */
	public function identifyHmac(HmacAuthenticationRequest $request): CredentialIdentityResult;

	/**
	 * Check an authenticated user's service grant; service principals must require an operation.
	 * @param string $serviceId Stable discovered service id.
	 * @return CredentialAuthenticationResult Allow or explicit denial; never bypass resource authorization.
	 * @throws \Throwable For an unavailable credential backend.
	 * Invariant: a service principal cannot receive an unrestricted service-wide allow.
	 */
	public function authorizeService(string $serviceId): CredentialAuthenticationResult;

	/**
	 * Inspect the authenticated request principal without making an access decision.
	 * @return CredentialIdentityResult|null Verified result or null before identification/reset.
	 */
	public function getIdentity(): ?CredentialIdentityResult;
}
