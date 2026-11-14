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
 * Holds the resolved write key for one operation. Never persist or log this object.
 */
final class EncryptionKeyMaterial {

	public function __construct(
		private readonly string $method,
		private readonly string $keyId,
		private readonly string $secret
	) {
		if ($method === '' || $keyId === '' || $secret === '') {
			throw new InvalidArgumentException('Encryption method, key ID and secret must be non-empty.');
		}
	}

	public function getMethod(): string {
		return $this->method;
	}

	public function getKeyId(): string {
		return $this->keyId;
	}

	public function getSecret(): string {
		return $this->secret;
	}
}
