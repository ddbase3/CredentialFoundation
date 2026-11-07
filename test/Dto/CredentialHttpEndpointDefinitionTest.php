<?php declare(strict_types=1);

namespace CredentialFoundation\Test\Dto;

use CredentialFoundation\Dto\CredentialHttpEndpointDefinition;
use CredentialFoundation\Dto\CredentialServiceDefinition;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CredentialHttpEndpointDefinitionTest extends TestCase {

	public function testAddsHttpMetadataWithoutChangingLegacyServiceRepresentation(): void {
		$legacy = new CredentialServiceDefinition('api:legacy', 'Legacy', '', ['getEntry']);
		self::assertArrayNotHasKey('http_endpoints', $legacy->toArray());
		self::assertSame([], $legacy->getHttpEndpoints());

		$endpoint = new CredentialHttpEndpointDefinition('POST', '/legacy.json', null, 'call');
		$service = new CredentialServiceDefinition('api:legacy', 'Legacy', '', ['getEntry'], [], [$endpoint]);
		self::assertSame([$endpoint], $service->getHttpEndpoints());
		self::assertSame('POST', $service->toArray()['http_endpoints'][0]['method']);
		self::assertSame('call', $service->toArray()['http_endpoints'][0]['operation_parameter']);
	}

	public function testDeclaresExactTechnicalOperation(): void {
		$endpoint = new CredentialHttpEndpointDefinition('POST', '/verify.json', null, 'call', true, true);
		self::assertTrue($endpoint->isCredentialRequired());
		self::assertTrue($endpoint->isTechnicalOnly());
		self::assertSame('/verify.json', $endpoint->getPath());
	}

	public function testRequiresOneOperationSource(): void {
		$this->expectException(InvalidArgumentException::class);
		new CredentialHttpEndpointDefinition('POST', '/verify.json', 'login', 'call');
	}

	public function testRejectsUndeclaredFixedOperation(): void {
		$this->expectException(InvalidArgumentException::class);
		new CredentialServiceDefinition('api:verify', 'Verify', '', ['login'], [], [
			new CredentialHttpEndpointDefinition('POST', '/verify.json', 'deleteAccount')
		]);
	}

	public function testRejectsEndpointWithoutOperationCatalog(): void {
		$this->expectException(InvalidArgumentException::class);
		new CredentialServiceDefinition('api:verify', 'Verify', '', [], [], [
			new CredentialHttpEndpointDefinition('POST', '/verify.json', null, 'call')
		]);
	}

	public function testRejectsDuplicateMethodPath(): void {
		$this->expectException(InvalidArgumentException::class);
		new CredentialServiceDefinition('api:verify', 'Verify', '', ['login'], [], [
			new CredentialHttpEndpointDefinition('POST', '/verify.json', 'login'),
			new CredentialHttpEndpointDefinition('POST', '/verify.json', null, 'call')
		]);
	}

	public function testRejectsUnsafePath(): void {
		$this->expectException(InvalidArgumentException::class);
		new CredentialHttpEndpointDefinition('POST', '/../../verify.json', 'login');
	}
	public function testPublicJsonUsageExampleDoesNotChangeLegacyMetadata(): void {
		$example = ['message' => '<MESSAGE>'];
		$endpoint = new CredentialHttpEndpointDefinition('POST', '/agent.json', 'execute', null, true, false, $example);
		self::assertSame($example, $endpoint->getExampleJsonBody());
		self::assertSame($example, $endpoint->toArray()['example_json_body']);
		$legacy = new CredentialHttpEndpointDefinition('GET', '/legacy.json', 'read');
		self::assertArrayNotHasKey('example_json_body', $legacy->toArray());
	}

}
