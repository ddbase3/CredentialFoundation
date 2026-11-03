<?php declare(strict_types=1);

namespace CredentialFoundation\Test\Api;

use CredentialFoundation\Api\ICredentialServiceProvider;
use CredentialFoundation\Dto\CredentialServiceDefinition;
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
