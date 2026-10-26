<?php declare(strict_types=1);

namespace CredentialFoundation\Dto;

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
