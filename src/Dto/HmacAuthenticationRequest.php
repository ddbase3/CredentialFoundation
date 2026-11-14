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

namespace Base3\CredentialFoundation\Dto;

use InvalidArgumentException;

/**
 * Carries the complete request material required for HMAC authentication.
 *
 * The raw request body is included so the credential service can calculate the
 * body digest itself instead of trusting a caller-provided digest.
 */
final class HmacAuthenticationRequest {

	public function __construct(
		private readonly string $token,
		string $method,
		private readonly string $path,
		private readonly string $queryString,
		private readonly int $timestamp,
		private readonly string $nonce,
		private readonly string $signature,
		private readonly string $body = ''
	) {
		$this->method = strtoupper(trim($method));

		if (trim($this->token) === '') {
			throw new InvalidArgumentException('HMAC credential tokens must not be empty.');
		}
		if ($this->method === '') {
			throw new InvalidArgumentException('HMAC request methods must not be empty.');
		}
		if ($this->path === '' || $this->path[0] !== '/') {
			throw new InvalidArgumentException('HMAC request paths must start with a slash.');
		}
		if ($this->timestamp <= 0) {
			throw new InvalidArgumentException('HMAC request timestamps must be positive Unix timestamps.');
		}
		if (trim($this->nonce) === '') {
			throw new InvalidArgumentException('HMAC request nonces must not be empty.');
		}
		if (trim($this->signature) === '') {
			throw new InvalidArgumentException('HMAC request signatures must not be empty.');
		}
	}

	private readonly string $method;

	public function getToken(): string {
		return $this->token;
	}

	public function getMethod(): string {
		return $this->method;
	}

	public function getPath(): string {
		return $this->path;
	}

	public function getQueryString(): string {
		return $this->queryString;
	}

	public function getTimestamp(): int {
		return $this->timestamp;
	}

	public function getNonce(): string {
		return $this->nonce;
	}

	public function getSignature(): string {
		return $this->signature;
	}

	public function getBody(): string {
		return $this->body;
	}
}
