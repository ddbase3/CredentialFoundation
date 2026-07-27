<?php declare(strict_types=1);

namespace CredentialFoundation\Dto;

use InvalidArgumentException;

/**
 * Describes one logical service that can be granted to an API credential.
 */
final class CredentialServiceDefinition {

	private const ID_PATTERN = '/^[a-z0-9][a-z0-9._:-]*$/';

	public function __construct(
		private readonly string $serviceId,
		private readonly string $label,
		private readonly string $description = ''
	) {
		if (!preg_match(self::ID_PATTERN, $this->serviceId)) {
			throw new InvalidArgumentException(
				'Credential service ids must use lowercase letters, numbers, dots, underscores, colons or hyphens.'
			);
		}

		if (trim($this->label) === '') {
			throw new InvalidArgumentException('Credential service labels must not be empty.');
		}
	}

	public function getServiceId(): string {
		return $this->serviceId;
	}

	public function getLabel(): string {
		return $this->label;
	}

	public function getDescription(): string {
		return $this->description;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function toArray(): array {
		return [
			'service_id' => $this->serviceId,
			'label' => $this->label,
			'description' => $this->description
		];
	}
}
