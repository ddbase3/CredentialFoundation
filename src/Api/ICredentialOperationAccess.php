<?php declare(strict_types=1);

namespace CredentialFoundation\Api;

use CredentialFoundation\Dto\CredentialAuthenticationResult;

/**
 * Optional operation-aware capability extending the stable ICredentialAccess contract.
 *
 * Implementers may expose this capability to endpoints requiring individually
 * restricted technical service principals. Existing ICredentialAccess providers
 * remain valid and are not forced to implement another method.
 */
interface ICredentialOperationAccess extends ICredentialAccess {

	/**
	 * Authorize an exact operation on the already HMAC-authenticated credential.
	 * @param string $serviceId Stable service id discovered through IClassMap.
	 * @param string $operation Case-sensitive operation name declared by the provider.
	 * @return CredentialAuthenticationResult Explicit allow/deny for this credential and operation.
	 * @throws \Throwable If the credential backend is unavailable.
	 * Invariant: service identities require exact operation grants and never inherit issuer rights.
	 * Invariant: the endpoint still MUST invoke IAuthorizationService and native ACL checks for the action/resource.
	 * Side effect: none beyond prior identification; this does not establish a user login.
	 */
	public function authorizeOperation(string $serviceId, string $operation): CredentialAuthenticationResult;
}
