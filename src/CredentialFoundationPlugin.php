<?php declare(strict_types=1);

namespace CredentialFoundation;

use Base3\Api\IContainer;
use Base3\Api\IPlugin;

final class CredentialFoundationPlugin implements IPlugin {

	public function __construct(
		private readonly IContainer $container
	) {}

	public static function getName(): string {
		return 'credentialfoundationplugin';
	}

	public function init() {
		$this->container->set(
			self::getName(),
			$this,
			IContainer::SHARED
		);
	}
}
