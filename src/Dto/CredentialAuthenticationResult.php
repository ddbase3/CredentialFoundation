<?php declare(strict_types=1);

namespace CredentialFoundation\Dto;

use InvalidArgumentException;

/**
 * Normalized result of a credential authentication and service-grant check.
 */
final class CredentialAuthenticationResult {

	public const FAILURE_MALFORMED_CREDENTIAL = 'malformed_credential';
	public const FAILURE_INVALID_CREDENTIAL = 'invalid_credential';
	public const FAILURE_REVOKED = 'revoked';
	public const FAILURE_EXPIRED = 'expired';
	public const FAILURE_SERVICE_NOT_FOUND = 'service_not_found';
	public const FAILURE_SERVICE_NOT_GRANTED = 'service_not_granted';
	public const FAILURE_HMAC_NOT_ENABLED = 'hmac_not_enabled';
	public const FAILURE_HMAC_REQUIRED = 'hmac_required';
	public const FAILURE_INVALID_TIMESTAMP = 'invalid_timestamp';
	public const FAILURE_INVALID_NONCE = 'invalid_nonce';
	public const FAILURE_REPLAY_DETECTED = 'replay_detected';
	public const FAILURE_INVALID_SIGNATURE = 'invalid_signature';

	private function __construct(
		private readonly bool $authenticated,
		private readonly string $failureCode,
		private readonly string $credentialId,
		private readonly int|string|null $userId,
		private readonly string $serviceId,
		private readonly ?int $expiresAt
	) {}

	public static function success(
		string $credentialId,
		int|string|null $userId,
		string $serviceId,
		?int $expiresAt = null
	): self {
		if (trim($credentialId) === '') {
			throw new InvalidArgumentException('Authenticated credential ids must not be empty.');
		}
		if (trim($serviceId) === '') {
			throw new InvalidArgumentException('Authenticated service ids must not be empty.');
		}

		return new self(
			true,
			'',
			$credentialId,
			$userId,
			$serviceId,
			$expiresAt
		);
	}

	public static function failure(string $failureCode, string $serviceId = ''): self {
		if (trim($failureCode) === '') {
			throw new InvalidArgumentException('Credential authentication failure codes must not be empty.');
		}

		return new self(false, $failureCode, '', null, $serviceId, null);
	}

	public function isAuthenticated(): bool {
		return $this->authenticated;
	}

	public function getFailureCode(): string {
		return $this->failureCode;
	}

	public function getCredentialId(): string {
		return $this->credentialId;
	}

	public function getUserId(): int|string|null {
		return $this->userId;
	}

	public function getServiceId(): string {
		return $this->serviceId;
	}

	public function getExpiresAt(): ?int {
		return $this->expiresAt;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function toArray(): array {
		return [
			'authenticated' => $this->authenticated,
			'failure_code' => $this->failureCode,
			'credential_id' => $this->credentialId,
			'user_id' => $this->userId,
			'service_id' => $this->serviceId,
			'expires_at' => $this->expiresAt
		];
	}
}
