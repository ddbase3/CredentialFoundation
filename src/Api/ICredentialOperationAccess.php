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

namespace Base3\CredentialFoundation\Api;

use Base3\CredentialFoundation\Dto\CredentialAuthenticationResult;

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
