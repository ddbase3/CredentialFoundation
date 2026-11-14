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

use Base3\CoreFoundation\Api\IBase;
use Base3\CredentialFoundation\Api\IApiCredentialService;
use Base3\CredentialFoundation\Api\ICredentialAccess;
use Base3\CredentialFoundation\Api\ICredentialOperationAccess;
use Base3\CredentialFoundation\Api\ICredentialServiceProvider;
use Base3\CredentialFoundation\Api\IEncryptionProfileService;
use Base3\CredentialFoundation\Api\IEncryptionTargetProvider;
use Base3\CredentialFoundation\Dto\CredentialAuthenticationResult;
use Base3\CredentialFoundation\Dto\EncryptionKeyMaterial;
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
