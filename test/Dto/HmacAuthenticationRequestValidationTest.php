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

use Base3\CredentialFoundation\Dto\HmacAuthenticationRequest;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class HmacAuthenticationRequestValidationTest extends TestCase {

	public function testRequestPreservesSignedDataWithoutPrematureNormalization(): void {
		$request = new HmacAuthenticationRequest(
			'  raw-token  ',
			'  pAtCh  ',
			'/api/resource',
			'a=1&b=two%20words',
			1700000000,
			'nonce-1',
			'signature-base64',
			"{\"amount\":5}\n"
		);

		self::assertSame('  raw-token  ', $request->getToken());
		self::assertSame('PATCH', $request->getMethod());
		self::assertSame('/api/resource', $request->getPath());
		self::assertSame('a=1&b=two%20words', $request->getQueryString());
		self::assertSame(1700000000, $request->getTimestamp());
		self::assertSame('nonce-1', $request->getNonce());
		self::assertSame('signature-base64', $request->getSignature());
		self::assertSame("{\"amount\":5}\n", $request->getBody());
	}

	public function testBodyDefaultsToEmptyWithoutChangingOtherSignedFields(): void {
		$request = new HmacAuthenticationRequest('token', 'GET', '/', '', 1, 'nonce', 'signature');

		self::assertSame('GET', $request->getMethod());
		self::assertSame('/', $request->getPath());
		self::assertSame('', $request->getQueryString());
		self::assertSame('', $request->getBody());
		self::assertSame(1, $request->getTimestamp());
	}

	#[DataProvider('invalidText')]
	public function testRejectsBlankToken(string $value): void {
		$this->expectException(InvalidArgumentException::class);
		new HmacAuthenticationRequest($value, 'GET', '/', '', 1, 'nonce', 'signature');
	}

	#[DataProvider('invalidText')]
	public function testRejectsBlankHttpMethod(string $value): void {
		$this->expectException(InvalidArgumentException::class);
		new HmacAuthenticationRequest('token', $value, '/', '', 1, 'nonce', 'signature');
	}

	#[DataProvider('invalidText')]
	public function testRejectsBlankNonce(string $value): void {
		$this->expectException(InvalidArgumentException::class);
		new HmacAuthenticationRequest('token', 'GET', '/', '', 1, $value, 'signature');
	}

	#[DataProvider('invalidText')]
	public function testRejectsBlankSignature(string $value): void {
		$this->expectException(InvalidArgumentException::class);
		new HmacAuthenticationRequest('token', 'GET', '/', '', 1, 'nonce', $value);
	}

	public static function invalidText(): iterable {
		yield 'empty' => [''];
		yield 'spaces' => ['   '];
		yield 'tabs' => ["\t\n"];
	}

	#[DataProvider('invalidPaths')]
	public function testRejectsNonAbsolutePath(string $path): void {
		$this->expectException(InvalidArgumentException::class);
		new HmacAuthenticationRequest('token', 'GET', $path, '', 1, 'nonce', 'signature');
	}

	public static function invalidPaths(): iterable {
		yield 'empty' => [''];
		yield 'relative' => ['api/resource'];
		yield 'query only' => ['?key=value'];
		yield 'leading whitespace' => [' /api'];
	}

	#[DataProvider('invalidTimestamps')]
	public function testRejectsNonPositiveTimestamp(int $timestamp): void {
		$this->expectException(InvalidArgumentException::class);
		new HmacAuthenticationRequest('token', 'GET', '/', '', $timestamp, 'nonce', 'signature');
	}

	public static function invalidTimestamps(): iterable {
		yield 'zero' => [0];
		yield 'negative' => [-1];
	}
}
