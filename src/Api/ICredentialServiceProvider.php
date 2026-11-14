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

use Base3\CoreFoundation\Api\IBase;
use Base3\CredentialFoundation\Dto\CredentialServiceDefinition;

/**
 * Provides one or more credential-protected service definitions.
 *
 * Provider classes are discoverable through the BASE3 class map. One provider
 * may expose multiple logical services, while every service id must be globally
 * unique in the final runtime composition.
 */
interface ICredentialServiceProvider extends IBase {

	/**
	 * Describes the logical services controlled by this provider.
	 *
	 * Every returned service id MUST be stable and unique in the final classmap
	 * composition. Operations MUST be explicit stable identifiers, not dynamic
	 * permissions inferred from incoming HTTP requests. A service MAY attach
	 * optional CredentialHttpEndpointDefinition metadata for its owning host to
	 * enforce. The provider MUST NOT authenticate callers or authorize resources.
	 * An endpoint declaration never publishes a route or grants access by itself.
	 *
	 * @return array<int,CredentialServiceDefinition> Complete service descriptions;
	 *         an empty array is valid when the provider exposes no services.
	 * @throws \InvalidArgumentException If any service metadata is malformed.
	 * @throws \UnexpectedValueException If configuration cannot supply valid,
	 *         unambiguous service metadata.
	 * Side effects: none beyond reading existing configuration or translations.
	 */
	public function getServices(): array;
}
