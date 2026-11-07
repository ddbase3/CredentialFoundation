<?php declare(strict_types=1);

namespace CredentialFoundation\Dto;

use InvalidArgumentException;

/**
 * Declares one HTTP entry point for an existing credential-protected service.
 *
 * The endpoint is metadata for the owning host's request boundary, not a
 * router, a credential verifier, or a replacement for domain authorization.
 * A method/path pair identifies the public request exactly. An operation is
 * either fixed by the provider or selected from a declared request parameter.
 */
final class CredentialHttpEndpointDefinition {

	private const OPERATION_PATTERN = '/^[A-Za-z_][A-Za-z0-9_]*$/D';

	/**
	 * @param string $method Uppercase HTTP method.
	 * @param string $path Exact public absolute path, without query or fragment.
	 * @param string|null $operation Fixed operation name; mutually exclusive with $operationParameter.
	 * @param string|null $operationParameter Request parameter containing the operation name.
	 * @param bool $credentialRequired Whether anonymous/session access to this entry point is forbidden.
	 * @param bool $technicalOnly Whether only a verified HMAC service principal may access this entry point.
	 * @param array<string,mixed> $exampleJsonBody Optional public example JSON request, without secrets or actual user data.
	 * @throws InvalidArgumentException For malformed or ambiguous endpoint metadata.
	 */
	public function __construct(
		private readonly string $method,
		private readonly string $path,
		private readonly ?string $operation = null,
		private readonly ?string $operationParameter = null,
		private readonly bool $credentialRequired = false,
		private readonly bool $technicalOnly = false,
		private readonly array $exampleJsonBody = []
	) {
		if (!in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD'], true)) {
			throw new InvalidArgumentException('Credential HTTP endpoints require an uppercase supported method.');
		}
		if (!preg_match('~^/(?:[a-zA-Z0-9._/-]+)$~D', $path) || str_contains($path, '//') || str_contains($path, '..')) {
			throw new InvalidArgumentException('Credential HTTP endpoints require an unambiguous absolute path.');
		}
		if (($operation === null) === ($operationParameter === null)) {
			throw new InvalidArgumentException('Credential HTTP endpoints must declare exactly one operation source.');
		}
		if ($operation !== null && !preg_match(self::OPERATION_PATTERN, $operation)) {
			throw new InvalidArgumentException('Credential HTTP endpoint operation is invalid.');
		}
		if ($operationParameter !== null && !preg_match(self::OPERATION_PATTERN, $operationParameter)) {
			throw new InvalidArgumentException('Credential HTTP operation parameter is invalid.');
		}
	}

	public function getMethod(): string {
		return $this->method;
	}

	public function getPath(): string {
		return $this->path;
	}

	public function getOperation(): ?string {
		return $this->operation;
	}

	public function getOperationParameter(): ?string {
		return $this->operationParameter;
	}

	/** @return array<string,mixed> Public example request body provided by the owning service. */
	public function getExampleJsonBody(): array {
		return $this->exampleJsonBody;
	}

	public function isCredentialRequired(): bool {
		return $this->credentialRequired || $this->technicalOnly;
	}

	public function isTechnicalOnly(): bool {
		return $this->technicalOnly;
	}

	/** @return array<string,mixed> Safe public endpoint metadata without credentials. */
	public function toArray(): array {
		$data = [
			'method' => $this->method,
			'path' => $this->path,
			'operation' => $this->operation,
			'operation_parameter' => $this->operationParameter,
			'credential_required' => $this->isCredentialRequired(),
			'technical_only' => $this->technicalOnly
		];
		if ($this->exampleJsonBody !== []) {
			$data['example_json_body'] = $this->exampleJsonBody;
		}
		return $data;
	}
}
