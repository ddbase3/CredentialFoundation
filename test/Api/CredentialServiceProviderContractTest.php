<?php declare(strict_types=1);

namespace CredentialFoundation\Test\Api;

use CredentialFoundation\Api\ICredentialServiceProvider;
use CredentialFoundation\Dto\CredentialServiceDefinition;
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
