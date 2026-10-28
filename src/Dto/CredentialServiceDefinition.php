<?php declare(strict_types=1);

namespace CredentialFoundation\Dto;

use InvalidArgumentException;

/**
 * Describes one logical service that can be granted to an API credential.
 */
final class CredentialServiceDefinition {

	private const ID_PATTERN = '/^[a-z0-9][a-z0-9._:-]*$/';
	private const OPERATION_PATTERN = '/^[A-Za-z_][A-Za-z0-9_]*$/';

	/** @var array<int,string> */
	private readonly array $operations;

	/** @var array<string,array<int,string>> */
	private readonly array $resourceAttributes;

	public function __construct(
		private readonly string $serviceId,
		private readonly string $label,
		private readonly string $description = '',
		array $operations = [],
		array $resourceAttributes = []
	) {
		if (!preg_match(self::ID_PATTERN, $this->serviceId)) {
			throw new InvalidArgumentException(
				'Credential service ids must use lowercase letters, numbers, dots, underscores, colons or hyphens.'
			);
		}

		if (trim($this->label) === '') {
			throw new InvalidArgumentException('Credential service labels must not be empty.');
		}
		$normalized = [];
		foreach ($operations as $operation) {
			if (!is_string($operation) || !preg_match(self::OPERATION_PATTERN, $operation)) {
				throw new InvalidArgumentException('Credential operation ids must be method-like names.');
			}
			$normalized[$operation] = true;
		}
		$operations = array_keys($normalized);
		sort($operations, SORT_STRING);
		$this->operations = $operations;

		$allowedOperators = ['eq', 'ne', 'has', 'not_has', 'in', 'not_in'];
		$attributes = [];
		foreach ($resourceAttributes as $attribute => $operators) {
			if (!is_string($attribute) || !preg_match('/^[a-z][a-z0-9_]*$/', $attribute) || !is_array($operators) || $operators === []) {
				throw new InvalidArgumentException('Invalid resource attribute declaration.');
			}
			$unique = [];
			foreach ($operators as $operator) {
				if (!is_string($operator) || !in_array($operator, $allowedOperators, true)) {
					throw new InvalidArgumentException('Invalid resource operator declaration.');
				}
				$unique[$operator] = true;
			}
			$attributes[$attribute] = array_keys($unique);
			sort($attributes[$attribute], SORT_STRING);
		}
		ksort($attributes, SORT_STRING);
		$this->resourceAttributes = $attributes;
	}

	/** Declared resource attributes and operators; empty means no resource predicates are supported. */
	public function getResourceAttributes(): array {
		return $this->resourceAttributes;
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

	/** @return array<int,string> Exact callable operation ids declared by the service owner. */
	public function getOperations(): array {
		return $this->operations;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function toArray(): array {
		return [
			'service_id' => $this->serviceId,
			'label' => $this->label,
			'description' => $this->description,
			'operations' => $this->operations,
			'resource_attributes' => $this->resourceAttributes
		];
	}
}
