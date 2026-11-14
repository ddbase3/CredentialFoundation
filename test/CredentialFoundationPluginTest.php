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

namespace Base3\CredentialFoundation\Test;

use Base3\CoreFoundation\Api\IContainer;
use Base3\CoreFoundation\Api\IPlugin;
use Base3\CredentialFoundation\CredentialFoundationPlugin;
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
