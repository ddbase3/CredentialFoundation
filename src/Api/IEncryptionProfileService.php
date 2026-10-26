<?php declare(strict_types=1);

namespace CredentialFoundation\Api;

use CredentialFoundation\Dto\EncryptionKeyMaterial;

/**
 * Runtime access to centrally managed encryption settings, independent of KeyHarbor.
 * Consumers persist the method and key ID alongside ciphertext, never the secret.
 */
interface IEncryptionProfileService {

	/**
	 * Returns the current assignment for a configured target.
	 * A null result means no encryption is assigned; an unknown non-empty assignment
	 * must not silently fall back to null or plaintext.
	 *
	 * @param string $targetId Stable ID from IEncryptionTargetProvider
	 * @return string|null Assigned profile ID or null when no assignment exists
	 * @throws \RuntimeException When an assignment is malformed
	 */
	public function getAssignedProfileId(string $targetId): ?string;

	/**
	 * Resolves the assigned profile's active encryption method and key for new data.
	 * A missing assignment returns null. An invalid profile, method, key ID or source
	 * MUST throw instead of permitting plaintext writes. Secret material stays in memory.
	 *
	 * @param string $targetId Stable ID of the consuming resource
	 * @return EncryptionKeyMaterial|null Resolved write key, or null if unassigned
	 * @throws \RuntimeException When configured encryption cannot be used safely
	 */
	public function getWriteKey(string $targetId): ?EncryptionKeyMaterial;

	/**
	 * Resolves a historical key from its globally unique ID and stored method.
	 * This operation is intentionally independent of current assignments: the same
	 * consumer may previously have used another profile. Never return an unrelated
	 * key on a method mismatch, missing ID or duplicate definition.
	 *
	 * @param string $method Persisted versioned ICrypt method identifier
	 * @param string $keyId Persisted globally unique key ID
	 * @return string Original secret for decryption only, never for persistence/logging
	 * @throws \RuntimeException When the key is unavailable, ambiguous or has another method
	 */
	public function getReadKey(string $method, string $keyId): string;
}
