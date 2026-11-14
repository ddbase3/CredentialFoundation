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

namespace Base3\CredentialFoundation;

use Base3\CoreFoundation\Api\IContainer;
use Base3\CoreFoundation\Api\IPlugin;

final class CredentialFoundationPlugin implements IPlugin {

	public function __construct(
		private readonly IContainer $container
	) {}

	public static function getName(): string {
		return 'credentialfoundationplugin';
	}

	public function init() {
		$this->container->set(
			self::getName(),
			$this,
			IContainer::SHARED
		);
	}
}
