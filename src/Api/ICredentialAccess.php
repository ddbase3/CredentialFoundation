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

	public function reset(): void;

	public function identifyBearer(string $token): CredentialIdentityResult;

	public function identifyHmac(HmacAuthenticationRequest $request): CredentialIdentityResult;

	public function authorizeService(string $serviceId): CredentialAuthenticationResult;

	public function getIdentity(): ?CredentialIdentityResult;
}
