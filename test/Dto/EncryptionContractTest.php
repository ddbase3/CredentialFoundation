<?php declare(strict_types=1);

namespace CredentialFoundation\Test\Dto;

use CredentialFoundation\Dto\EncryptionKeyMaterial;
use CredentialFoundation\Dto\EncryptionTargetDefinition;
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
