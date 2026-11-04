<?php declare(strict_types=1);

namespace CredentialFoundation\Test\Dto;

use CredentialFoundation\Dto\CredentialServiceDefinition;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CredentialServiceDefinitionValidationTest extends TestCase {

	public function testDefaultDefinitionHasNoDeclaredOperationsOrResources(): void {
		$definition = new CredentialServiceDefinition('api:v1', 'API');

		self::assertSame('api:v1', $definition->getServiceId());
		self::assertSame('API', $definition->getLabel());
		self::assertSame('', $definition->getDescription());
		self::assertSame([], $definition->getOperations());
		self::assertSame([], $definition->getResourceAttributes());
		self::assertSame([
			'service_id' => 'api:v1',
			'label' => 'API',
			'description' => '',
			'operations' => [],
			'resource_attributes' => []
		], $definition->toArray());
	}

	public function testOperationsAndResourceOperatorsAreNormalizedWithoutLosingDeclarations(): void {
		$definition = new CredentialServiceDefinition(
			'api:reports',
			'Reports',
			'Protected reports',
			['getOne', '_internal', 'getOne', 'getAll2'],
			[
				'zones' => ['not_in', 'eq', 'in', 'eq'],
				'owner_id' => ['not_has', 'has', 'ne', 'not_has']
			]
		);

		self::assertSame(['_internal', 'getAll2', 'getOne'], $definition->getOperations());
		self::assertSame([
			'owner_id' => ['has', 'ne', 'not_has'],
			'zones' => ['eq', 'in', 'not_in']
		], $definition->getResourceAttributes());
		self::assertSame('Protected reports', $definition->getDescription());
		self::assertSame($definition->getOperations(), $definition->toArray()['operations']);
		self::assertSame($definition->getResourceAttributes(), $definition->toArray()['resource_attributes']);
	}

	public function testEveryDeclaredResourceOperatorIsAccepted(): void {
		$definition = new CredentialServiceDefinition('api:reports', 'Reports', '', [], [
			'target' => ['not_in', 'in', 'not_has', 'has', 'ne', 'eq']
		]);

		self::assertSame(['eq', 'has', 'in', 'ne', 'not_has', 'not_in'], $definition->getResourceAttributes()['target']);
	}

	#[DataProvider('validServiceIds')]
	public function testServiceIdsAcceptOnlyTheDeclaredIdentifierAlphabet(string $serviceId): void {
		self::assertSame($serviceId, (new CredentialServiceDefinition($serviceId, 'Service'))->getServiceId());
	}

	public static function validServiceIds(): iterable {
		yield 'single letter' => ['a'];
		yield 'initial digit' => ['9'];
		yield 'lowercase with separators' => ['api:reports.v1_test-run'];
	}

	#[DataProvider('invalidServiceIds')]
	public function testRejectsInvalidServiceIdentifiers(string $serviceId): void {
		$this->expectException(InvalidArgumentException::class);
		new CredentialServiceDefinition($serviceId, 'Service');
	}

	public static function invalidServiceIds(): iterable {
		yield 'empty' => [''];
		yield 'leading colon' => [':service'];
		yield 'leading dash' => ['-service'];
		yield 'leading period' => ['.service'];
		yield 'uppercase' => ['Api:Reports'];
		yield 'space' => ['api:report name'];
		yield 'path slash' => ['api/reports'];
		yield 'trailing newline' => ["api:reports\n"];
	}

	#[DataProvider('blankLabels')]
	public function testRejectsEmptyOrWhitespaceLabels(string $label): void {
		$this->expectException(InvalidArgumentException::class);
		new CredentialServiceDefinition('api:reports', $label);
	}

	public static function blankLabels(): iterable {
		yield 'empty' => [''];
		yield 'spaces' => ['   '];
		yield 'tabs' => ["\t\n"];
	}

	#[DataProvider('invalidOperations')]
	public function testRejectsInvalidOperationDeclarations(mixed $operation): void {
		$this->expectException(InvalidArgumentException::class);
		new CredentialServiceDefinition('api:reports', 'Reports', '', [$operation]);
	}

	public static function invalidOperations(): iterable {
		yield 'empty' => [''];
		yield 'leading digit' => ['7lookup'];
		yield 'dash' => ['get-item'];
		yield 'colon' => ['get:item'];
		yield 'space' => ['get Item'];
		yield 'trailing newline' => ["getItem\n"];
		yield 'integer' => [42];
		yield 'null' => [null];
		yield 'array' => [['getItem']];
	}

	#[DataProvider('invalidResourceAttributes')]
	public function testRejectsInvalidResourceAttributeDeclarations(array $attributes): void {
		$this->expectException(InvalidArgumentException::class);
		new CredentialServiceDefinition('api:reports', 'Reports', '', [], $attributes);
	}

	public static function invalidResourceAttributes(): iterable {
		yield 'upper case attribute' => [['Owner' => ['eq']]];
		yield 'attribute starting with digit' => [['7owner' => ['eq']]];
		yield 'attribute with dash' => [['owner-id' => ['eq']]];
		yield 'attribute with newline' => [["owner\n" => ['eq']]];
		yield 'numeric attribute' => [[7 => ['eq']]];
		yield 'empty operators' => [['owner' => []]];
		yield 'string instead of list' => [['owner' => 'eq']];
		yield 'null operators' => [['owner' => null]];
		yield 'unsupported operator' => [['owner' => ['eq', 'contains']]];
		yield 'upper case operator' => [['owner' => ['EQ']]];
		yield 'numeric operator' => [['owner' => [42]]];
		yield 'null operator' => [['owner' => [null]]];
	}
}
