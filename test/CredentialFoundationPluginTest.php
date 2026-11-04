<?php declare(strict_types=1);

namespace CredentialFoundation\Test;

use Base3\Api\IContainer;
use Base3\Api\IPlugin;
use CredentialFoundation\CredentialFoundationPlugin;
use PHPUnit\Framework\TestCase;

final class CredentialFoundationPluginTest extends TestCase {

	public function testPluginHasDiscoverableStableName(): void {
		self::assertSame('credentialfoundationplugin', CredentialFoundationPlugin::getName());
		self::assertTrue(is_subclass_of(CredentialFoundationPlugin::class, IPlugin::class));
	}

	public function testInitializationRegistersOnlyThePluginInstance(): void {
		$container = $this->createMock(IContainer::class);
		$plugin = new CredentialFoundationPlugin($container);

		$container->expects($this->once())
			->method('set')
			->with(CredentialFoundationPlugin::getName(), $this->identicalTo($plugin), IContainer::SHARED)
			->willReturnSelf();

		$plugin->init();
	}
}
