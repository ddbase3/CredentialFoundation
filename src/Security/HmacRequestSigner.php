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

namespace Base3\CredentialFoundation\Security;

use Base3\CredentialFoundation\Dto\HmacAuthenticationRequest;

/**
 * Builds and verifies the canonical KeyHarbor HMAC-SHA256 signature.
 */
final class HmacRequestSigner {

	public function createCanonicalRequest(HmacAuthenticationRequest $request): string {
		return implode("\n", [
			$request->getMethod(),
			$request->getPath(),
			$request->getQueryString(),
			(string)$request->getTimestamp(),
			$request->getNonce(),
			hash('sha256', $request->getBody())
		]);
	}

	public function sign(HmacAuthenticationRequest $request, string $secret): string {
		return hash_hmac('sha256', $this->createCanonicalRequest($request), $secret);
	}

	public function verify(HmacAuthenticationRequest $request, string $secret): bool {
		$signature = strtolower(trim($request->getSignature()));
		if (!preg_match('/^[a-f0-9]{64}$/', $signature)) {
			return false;
		}

		return hash_equals($this->sign($request, $secret), $signature);
	}
}
