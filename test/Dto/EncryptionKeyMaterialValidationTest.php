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
