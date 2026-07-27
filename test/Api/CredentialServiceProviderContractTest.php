<?php declare(strict_types=1);

namespace CredentialFoundation\Test\Api;

use CredentialFoundation\Api\ICredentialServiceProvider;
use CredentialFoundation\Dto\CredentialServiceDefinition;
use PHPUnit\Framework\TestCase;

final class CredentialServiceProviderContractTest extends TestCase {

	public function testOneProviderCanExposeMultipleServices(): void {
		$provider = new class implements ICredentialServiceProvider {

			public static function getName(): string {
				return 'credentialfoundationtestprovider';
			}

			public function getServices(): array {
				return [
					new CredentialServiceDefinition('demo:ping', 'Ping'),
					new CredentialServiceDefinition('demo:report', 'Report')
				];
			}
		};

		$this->assertCount(2, $provider->getServices());
		$this->assertSame('demo:ping', $provider->getServices()[0]->getServiceId());
		$this->assertSame('demo:report', $provider->getServices()[1]->getServiceId());
	}
}
