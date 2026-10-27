<?php declare(strict_types=1);

namespace CredentialFoundation\Dto;

use InvalidArgumentException;

/**
 * Trusted principal established by a successful credential authentication.
 *
 * A type/id pair is not a grant and cannot authenticate itself. Service subjects
 * must never be interpreted as user ids. A subject becomes trusted only when
 * returned by a credential implementation after verifying its persisted key.
 */
final class CredentialSubject {

	public const TYPE_USER = 'user';
	public const TYPE_SERVICE = 'service';

	public function __construct(
		private readonly string $type,
		private readonly string $id
	) {
		if (!in_array($type, [self::TYPE_USER, self::TYPE_SERVICE], true)) {
			throw new InvalidArgumentException('Unsupported credential subject type.');
		}
		if ($id === '' || trim($id) !== $id || strlen($id) > 160 || preg_match('/[\x00-\x1F\x7F]/', $id)) {
			throw new InvalidArgumentException('Credential subject identifiers must be nonempty and contain no control characters.');
		}
		if ($type === self::TYPE_SERVICE && !preg_match('/^[a-z][a-z0-9._:-]{0,159}$/', $id)) {
			throw new InvalidArgumentException('Service subject ids must be lowercase stable identifiers.');
		}
	}

	public function getType(): string {
		return $this->type;
	}

	public function getId(): string {
		return $this->id;
	}

	public function isUser(): bool {
		return $this->type === self::TYPE_USER;
	}

	public function isService(): bool {
		return $this->type === self::TYPE_SERVICE;
	}

	/** @return array{type:string,id:string} */
	public function toArray(): array {
		return ['type' => $this->type, 'id' => $this->id];
	}
}
