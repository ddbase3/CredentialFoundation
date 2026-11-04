<?php declare(strict_types=1);

namespace CredentialFoundation\Test\Api;

use Base3\Api\IBase;
use CredentialFoundation\Api\IApiCredentialService;
use CredentialFoundation\Api\ICredentialAccess;
use CredentialFoundation\Api\ICredentialOperationAccess;
use CredentialFoundation\Api\ICredentialServiceProvider;
use CredentialFoundation\Api\IEncryptionProfileService;
use CredentialFoundation\Api\IEncryptionTargetProvider;
use CredentialFoundation\Dto\CredentialAuthenticationResult;
use CredentialFoundation\Dto\EncryptionKeyMaterial;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class CredentialInterfaceContractTest extends TestCase {

	public function testCredentialIdentityAndServiceGrantLifecycleRemainInTheBaseContract(): void {
		foreach (['reset', 'identifyBearer', 'identifyHmac', 'authorizeService', 'getIdentity'] as $method) {
			self::assertTrue(method_exists(ICredentialAccess::class, $method), $method);
		}

		self::assertFalse(method_exists(ICredentialAccess::class, 'authorizeOperation'));
		self::assertTrue(is_subclass_of(ICredentialOperationAccess::class, ICredentialAccess::class));
		self::assertTrue(method_exists(ICredentialOperationAccess::class, 'authorizeOperation'));
	}

	public function testCredentialApiMaintainsBothAuthenticationMethodsAndTypedResults(): void {
		foreach (['authenticateBearer', 'authenticateHmac'] as $name) {
			$method = new ReflectionMethod(IApiCredentialService::class, $name);
			self::assertSame(CredentialAuthenticationResult::class, $method->getReturnType()?->getName());
		}
	}

	public function testEncryptionProfileContractHasExplicitAssignmentWriteAndHistoricalReadMethods(): void {
		foreach (['getAssignedProfileId', 'getWriteKey', 'getReadKey'] as $name) {
			self::assertTrue(method_exists(IEncryptionProfileService::class, $name));
		}

		self::assertSame('string', (new ReflectionMethod(IEncryptionProfileService::class, 'getAssignedProfileId'))->getReturnType()?->getName());
		self::assertTrue((new ReflectionMethod(IEncryptionProfileService::class, 'getAssignedProfileId'))->getReturnType()?->allowsNull());
		self::assertSame(EncryptionKeyMaterial::class, (new ReflectionMethod(IEncryptionProfileService::class, 'getWriteKey'))->getReturnType()?->getName());
		self::assertSame('string', (new ReflectionMethod(IEncryptionProfileService::class, 'getReadKey'))->getReturnType()?->getName());
	}

	public function testProviderContractsRemainClassMapDiscoverable(): void {
		self::assertTrue(is_subclass_of(ICredentialServiceProvider::class, IBase::class));
		self::assertTrue(is_subclass_of(IEncryptionTargetProvider::class, IBase::class));
		self::assertTrue(method_exists(ICredentialServiceProvider::class, 'getServices'));
		self::assertTrue(method_exists(IEncryptionTargetProvider::class, 'getTargets'));
	}
}
