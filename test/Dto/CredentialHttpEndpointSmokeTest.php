<?php declare(strict_types=1);

use CredentialFoundation\Dto\CredentialHttpEndpointDefinition;
use CredentialFoundation\Dto\CredentialServiceDefinition;

$root = dirname(__DIR__, 4);
foreach (['DIR_ROOT' => $root . '/', 'DIR_SRC' => $root . '/src/', 'DIR_PLUGIN' => $root . '/plugin/', 'DIR_TEST' => $root . '/test/'] as $name => $path) {
	if (!defined($name)) define($name, $path);
}
require_once DIR_SRC . 'Core/Autoloader.php';
Base3\Core\Autoloader::register();

$old = new CredentialServiceDefinition('api:old', 'Old', '', ['read']);
if (array_key_exists('http_endpoints', $old->toArray())) {
	throw new RuntimeException('Legacy service representation changed.');
}
$endpoint = new CredentialHttpEndpointDefinition('POST', '/api.json', 'execute', null, true);
$service = new CredentialServiceDefinition('api:agent', 'Agent', '', ['execute'], [], [$endpoint]);
if ($service->getHttpEndpoints() !== [$endpoint] || $service->toArray()['http_endpoints'][0]['operation'] !== 'execute') {
	throw new RuntimeException('HTTP metadata was not retained.');
}
foreach ([
	fn() => new CredentialHttpEndpointDefinition('POST', '/api.json', null, null),
	fn() => new CredentialHttpEndpointDefinition('POST', '/api.json', 'execute', 'call'),
	fn() => new CredentialHttpEndpointDefinition('POST', '/../../bad', 'execute'),
	fn() => new CredentialServiceDefinition('api:agent', 'Agent', '', ['read'], [], [$endpoint])
] as $invalid) {
	try {
		$invalid();
	} catch (InvalidArgumentException) {
		continue;
	}
	throw new RuntimeException('Unsafe credential endpoint declaration was accepted.');
}
echo "Credential HTTP definitions: OK\n";
