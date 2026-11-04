<?php declare(strict_types=1);

namespace CredentialFoundation\Test\Dto;

use CredentialFoundation\Dto\EncryptionKeyMaterial;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EncryptionKeyMaterialValidationTest extends TestCase {

	public function testResolvedKeyKeepsMethodIdentifierAndSecretExactly(): void {
		$secret = str_repeat("\x00\xFF", 16);
		$key = new EncryptionKeyMaterial('xchacha20poly1305-v1', 'key:2026-01', $secret);

		self::assertSame('xchacha20poly1305-v1', $key->getMethod());
		self::assertSame('key:2026-01', $key->getKeyId());
		self::assertSame($secret, $key->getSecret());
	}

	#[DataProvider('invalidKeyMaterial')]
	public function testRejectsEmptyKeyFields(string $method, string $keyId, string $secret): void {
		$this->expectException(InvalidArgumentException::class);
		new EncryptionKeyMaterial($method, $keyId, $secret);
	}

	public static function invalidKeyMaterial(): iterable {
		yield 'missing method' => ['', 'id', 'secret'];
		yield 'missing key id' => ['method', '', 'secret'];
		yield 'missing secret' => ['method', 'id', ''];
	}
}
