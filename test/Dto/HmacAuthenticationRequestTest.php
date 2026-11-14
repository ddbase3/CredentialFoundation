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
use PHPUnit\Framework\TestCase;

final class HmacAuthenticationRequestTest extends TestCase {

	public function testNormalizesMethodAndPreservesRequestMaterial(): void {
		$request = new HmacAuthenticationRequest(
			'b3k_public_secret',
			'post',
			'/api/report',
			'period=month',
			1700000000,
			'nonce-1',
			'signature-1',
			'{"limit":10}'
		);

		$this->assertSame('POST', $request->getMethod());
		$this->assertSame('/api/report', $request->getPath());
		$this->assertSame('period=month', $request->getQueryString());
		$this->assertSame('{"limit":10}', $request->getBody());
	}

	public function testRejectsRelativePath(): void {
		$this->expectException(InvalidArgumentException::class);
		new HmacAuthenticationRequest(
			'b3k_public_secret',
			'GET',
			'api/report',
			'',
			1700000000,
			'nonce-1',
			'signature-1'
		);
	}
}
