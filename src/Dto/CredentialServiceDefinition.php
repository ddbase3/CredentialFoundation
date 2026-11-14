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
 * Describes one logical service that can be granted to an API credential.
 */
final class CredentialServiceDefinition {

	private const ID_PATTERN = '/^[a-z0-9][a-z0-9._:-]*$/D';
	private const OPERATION_PATTERN = '/^[A-Za-z_][A-Za-z0-9_]*$/D';

	/** @var array<int,string> */
	private readonly array $operations;

	/** @var array<string,array<int,string>> */
	private readonly array $resourceAttributes;

	/** @var array<int,CredentialHttpEndpointDefinition> */
	private readonly array $httpEndpoints;

	public function __construct(
		private readonly string $serviceId,
		private readonly string $label,
		private readonly string $description = '',
		array $operations = [],
		array $resourceAttributes = [],
		array $httpEndpoints = []
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
			if (!is_string($attribute) || !preg_match('/^[a-z][a-z0-9_]*$/D', $attribute) || !is_array($operators) || $operators === []) {
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

		$endpoints = [];
		foreach ($httpEndpoints as $endpoint) {
			if (!$endpoint instanceof CredentialHttpEndpointDefinition) {
				throw new InvalidArgumentException('Invalid credential HTTP endpoint definition.');
			}
			$operation = $endpoint->getOperation();
			if (($operation !== null && !in_array($operation, $this->operations, true)) ||
				($endpoint->getOperationParameter() !== null && $this->operations === [])) {
				throw new InvalidArgumentException('Credential HTTP endpoint operation must be declared by the service.');
			}
			$key = $endpoint->getMethod() . ' ' . $endpoint->getPath();
			if (isset($endpoints[$key])) {
				throw new InvalidArgumentException('Duplicate credential HTTP endpoint declaration.');
			}
			$endpoints[$key] = $endpoint;
		}
		$this->httpEndpoints = array_values($endpoints);
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

	/** @return array<int,CredentialHttpEndpointDefinition> HTTP entry points owned by this service. */
	public function getHttpEndpoints(): array {
		return $this->httpEndpoints;
	}

	/** @return array<string,mixed> */
	public function toArray(): array {
		$data = [
			'service_id' => $this->serviceId,
			'label' => $this->label,
			'description' => $this->description,
			'operations' => $this->operations,
			'resource_attributes' => $this->resourceAttributes
		];
		if ($this->httpEndpoints !== []) {
			$data['http_endpoints'] = array_map(static fn(CredentialHttpEndpointDefinition $endpoint): array => $endpoint->toArray(), $this->httpEndpoints);
		}
		return $data;
	}
}
