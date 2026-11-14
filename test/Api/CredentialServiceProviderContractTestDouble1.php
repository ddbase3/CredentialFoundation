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

namespace Base3\CredentialFoundation\Test\Api;

use Base3\CredentialFoundation\Api\ICredentialServiceProvider;
use Base3\CredentialFoundation\Dto\CredentialServiceDefinition;
use PHPUnit\Framework\TestCase;

final class CredentialServiceProviderContractTestDouble1 implements ICredentialServiceProvider  {

			public static function getName(): string {
				return 'credentialfoundationtestprovider';
			}

			public function getServices(): array {
				return [
					new CredentialServiceDefinition('demo:ping', 'Ping'),
					new CredentialServiceDefinition('demo:report', 'Report')
				];
			}
		}
