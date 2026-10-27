<?php declare(strict_types=1);

namespace CredentialFoundation\Test\Dto;

use CredentialFoundation\Dto\CredentialServiceDefinition;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CredentialServiceDefinitionTest extends TestCase {

	public function testCreatesStableServiceDefinition(): void {
		$definition = new CredentialServiceDefinition(
			'missionbay:mcp:internal',
			'MissionBay MCP',
			'Internal MCP endpoint.'
		);

		$this->assertSame('missionbay:mcp:internal', $definition->getServiceId());
		$this->assertSame('MissionBay MCP', $definition->getLabel());
		$this->assertSame('Internal MCP endpoint.', $definition->getDescription());
		$this->assertSame('missionbay:mcp:internal', $definition->toArray()['service_id']);
	}

	public function testDefinesExactOperationCatalog(): void {
		$definition = new CredentialServiceDefinition('memora:entries', 'Memora', '', ['getEntry', 'getEntries', 'getEntry']);
		self::assertSame(['getEntries', 'getEntry'], $definition->getOperations());
		self::assertSame(['getEntries', 'getEntry'], $definition->toArray()['operations']);
	}

	public function testRejectsUppercaseServiceId(): void {
		$this->expectException(InvalidArgumentException::class);
		new CredentialServiceDefinition('MissionBay:MCP', 'MissionBay MCP');
	}

	public function testRejectsEmptyLabel(): void {
		$this->expectException(InvalidArgumentException::class);
		new CredentialServiceDefinition('missionbay:mcp', '');
	}
}
