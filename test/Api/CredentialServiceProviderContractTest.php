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

final class CredentialServiceProviderContractTest extends TestCase {

	public function testOneProviderCanExposeMultipleServices(): void {
		$provider = new CredentialServiceProviderContractTestDouble1();

		$this->assertCount(2, $provider->getServices());
		$this->assertSame('demo:ping', $provider->getServices()[0]->getServiceId());
		$this->assertSame('demo:report', $provider->getServices()[1]->getServiceId());
	}
}

require_once __DIR__ . '/CredentialServiceProviderContractTestDouble1.php';
