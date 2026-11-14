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
use Base3\CredentialFoundation\Dto\EncryptionTargetDefinition;

/**
 * Classmap-discoverable source of configured consumers that support managed encryption.
 * A provider may enumerate many instances of one resource class. IDs must be stable and
 * globally unique across providers, including different host installations or tenants.
 */
interface IEncryptionTargetProvider extends IBase {

	/**
	 * Returns the currently configured encryption consumers without revealing secrets.
	 * The provider must enumerate real configured instances, not just an implementation class.
	 * It must not change target settings or trigger encryption while enumerating.
	 * Duplicate IDs across providers invalidate the catalog instead of choosing a winner.
	 *
	 * @return EncryptionTargetDefinition[] Configured target definitions, possibly empty
	 * @throws \RuntimeException When a required configuration cannot be enumerated
	 */
	public function getTargets(): array;
}
