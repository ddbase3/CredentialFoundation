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

namespace Base3\CredentialFoundation\Test\Dto;

use Base3\CredentialFoundation\Dto\EncryptionKeyMaterial;
use Base3\CredentialFoundation\Dto\EncryptionTargetDefinition;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class EncryptionContractTest extends TestCase {

	public function testTargetDefinitionKeepsStableIdentityAndLabel(): void {
		$target = new EncryptionTargetDefinition('example:instance:one', 'Example instance');
		self::assertSame('example:instance:one', $target->getTargetId());
		self::assertSame('Example instance', $target->toArray()['label']);
	}

	public function testTargetDefinitionRejectsUnstableIdentifier(): void {
		$this->expectException(InvalidArgumentException::class);
		new EncryptionTargetDefinition('Example Instance', 'Label');
	}

	public function testKeyMaterialCarriesOnlyRuntimeValuesWithoutSerializationHelpers(): void {
		$key = new EncryptionKeyMaterial('xchacha20poly1305-v1', 'key-1', str_repeat('k', 32));
		self::assertSame('key-1', $key->getKeyId());
		self::assertSame(str_repeat('k', 32), $key->getSecret());
		self::assertFalse(method_exists($key, 'toArray'));
	}
}
