<?php declare(strict_types=1);

namespace CredentialFoundation\Test\Dto;

use CredentialFoundation\Dto\HmacAuthenticationRequest;
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
