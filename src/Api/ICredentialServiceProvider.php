<?php declare(strict_types=1);

namespace CredentialFoundation\Api;

use Base3\Api\IBase;
use CredentialFoundation\Dto\CredentialServiceDefinition;

/**
 * Provides one or more credential-protected service definitions.
 *
 * Provider classes are discoverable through the BASE3 class map. One provider
 * may expose multiple logical services, while every service id must be globally
 * unique in the final runtime composition.
 */
interface ICredentialServiceProvider extends IBase {

	/**
	 * @return array<int,CredentialServiceDefinition>
	 */
	public function getServices(): array;
}
