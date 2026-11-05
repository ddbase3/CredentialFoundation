<?php declare(strict_types=1);

namespace CredentialFoundation\Test\Security;

use CredentialFoundation\Dto\HmacAuthenticationRequest;
use CredentialFoundation\Security\HmacRequestSigner;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HmacRequestSigner::class)]
final class HmacRequestSignerTest extends TestCase {

	private function request(string $signature, string $body = 'call=getEntries&params=%7B%22options%22%3A%5B%5D%7D'): HmacAuthenticationRequest {
		return new HmacAuthenticationRequest(
			'b3k_' . str_repeat('a', 20) . '_' . str_repeat('b', 43),
			'POST',
			'/entitydatamicroservice.json',
			'a=1',
			1234567890,
			'abcd1234',
			$signature,
			$body
		);
	}

	public function testCanonicalRequestUsesRawBodyDigestAndAllRequestFields(): void {
		$body = 'call=getEntries&params=%7B%22options%22%3A%5B%5D%7D';
		$canonical = "POST\n/entitydatamicroservice.json\na=1\n1234567890\nabcd1234\n" . hash('sha256', $body);

		self::assertSame($canonical, (new HmacRequestSigner())->createCanonicalRequest($this->request('unsigned', $body)));
	}

	public function testSigningMatchesTheHmacSha256Baseline(): void {
		$request = $this->request('unsigned');
		$signer = new HmacRequestSigner();
		$signature = hash_hmac('sha256', $signer->createCanonicalRequest($request), str_repeat('b', 43));

		self::assertSame($signature, $signer->sign($request, str_repeat('b', 43)));
	}

	public function testVerificationAcceptsMatchingSignatureAndRejectsDifferentSecret(): void {
		$signer = new HmacRequestSigner();
		$signature = $signer->sign($this->request('unsigned'), str_repeat('b', 43));
		$signed = $this->request($signature);

		self::assertTrue($signer->verify($signed, str_repeat('b', 43)));
		self::assertFalse($signer->verify($signed, str_repeat('c', 43)));
	}

	public function testVerificationRejectsTamperedBodyAndMalformedSignatures(): void {
		$signer = new HmacRequestSigner();
		$signature = $signer->sign($this->request('unsigned'), str_repeat('b', 43));

		self::assertFalse($signer->verify($this->request($signature, 'tampered'), str_repeat('b', 43)));
		self::assertFalse($signer->verify($this->request('invalid-signature'), str_repeat('b', 43)));
	}
}
