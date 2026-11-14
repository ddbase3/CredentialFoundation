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
 * Result of credential authentication without a service-grant decision.
 */
final class CredentialIdentityResult {

	public const FAILURE_MALFORMED_CREDENTIAL = 'malformed_credential';
	public const FAILURE_INVALID_CREDENTIAL = 'invalid_credential';
	public const FAILURE_REVOKED = 'revoked';
	public const FAILURE_EXPIRED = 'expired';
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
		private readonly ?int $expiresAt,
		private readonly ?CredentialSubject $subject
	) {}

	public static function success(
		string $credentialId,
		int|string|null $userId,
		?int $expiresAt = null,
		?CredentialSubject $subject = null
	): self {
		if (trim($credentialId) === '') {
			throw new InvalidArgumentException('Authenticated credential ids must not be empty.');
		}

		$subject ??= $userId !== null ? new CredentialSubject(CredentialSubject::TYPE_USER, (string)$userId) : null;
		self::assertSubject($subject, $userId);
		return new self(true, '', $credentialId, $userId, $expiresAt, $subject);
	}

	public static function failure(string $failureCode): self {
		if (trim($failureCode) === '') {
			throw new InvalidArgumentException('Credential identity failure codes must not be empty.');
		}

		return new self(false, $failureCode, '', null, null, null);
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

	/** Authenticated principal; null on failure or for historical null-user results. */
	public function getSubject(): ?CredentialSubject {
		return $this->subject;
	}

	private static function assertSubject(?CredentialSubject $subject, int|string|null $userId): void {
		if ($subject === null) {
			return;
		}
		if ($subject->isService() && $userId !== null) {
			throw new InvalidArgumentException('A service subject must never impersonate a user.');
		}
		if ($subject->isUser() && (string)$userId !== $subject->getId()) {
			throw new InvalidArgumentException('The authenticated user must match the user subject.');
		}
	}

	/**
	 * The credential subject's native host User::$id, not User::$userid/login
	 * and not IAccesscontrol::getUserId() where the host uses login identifiers.
	 * A service subject has no user ID. This value is not a session identity.
	 * @return int|string|null Verified native user ID, or null.
	 */
	public function getUserId(): int|string|null {
		return $this->userId;
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
			'subject' => $this->subject?->toArray(),
			'expires_at' => $this->expiresAt
		];
	}
}
