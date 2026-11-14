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
 * Identifies one configured consumer of managed encryption, independent of its implementation class.
 */
final class EncryptionTargetDefinition {

	public function __construct(
		private readonly string $targetId,
		private readonly string $label,
		private readonly string $description = ''
	) {
		if (!preg_match('/^[a-z0-9][a-z0-9._:-]{0,127}$/D', $targetId)) {
			throw new InvalidArgumentException('Encryption target IDs must use stable lowercase identifiers.');
		}
		if (trim($label) === '') {
			throw new InvalidArgumentException('An encryption target requires a label.');
		}
	}

	public function getTargetId(): string {
		return $this->targetId;
	}

	public function getLabel(): string {
		return $this->label;
	}

	public function getDescription(): string {
		return $this->description;
	}

	/** @return array<string,string> */
	public function toArray(): array {
		return ['target_id' => $this->targetId, 'label' => $this->label, 'description' => $this->description];
	}
}
