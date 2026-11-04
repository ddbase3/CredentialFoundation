<?php declare(strict_types=1);

namespace CredentialFoundation\Test\Security;

use CredentialFoundation\Dto\HmacAuthenticationRequest;
use CredentialFoundation\Security\HmacRequestSigner;
use RuntimeException;

if (!defined('DIR_ROOT')) define('DIR_ROOT', dirname(__DIR__, 4) . '/');
if (!defined('DIR_SRC')) define('DIR_SRC', DIR_ROOT . 'src/');
if (!defined('DIR_PLUGIN')) define('DIR_PLUGIN', DIR_ROOT . 'plugin/');
if (!defined('DIR_TEST')) define('DIR_TEST', DIR_ROOT . 'test/');
require_once DIR_SRC . 'Core/Autoloader.php';
\Base3\Core\Autoloader::register();

function check(bool $value, string $message): void {
	if (!$value) throw new RuntimeException('HmacRequestSigner: ' . $message);
}

$signer = new HmacRequestSigner();
$token = 'b3k_' . str_repeat('a', 20) . '_' . str_repeat('b', 43);
$secret = str_repeat('b', 43);
$body = 'call=getEntries&params=%7B%22options%22%3A%5B%5D%7D';
$request = new HmacAuthenticationRequest($token, 'POST', '/entitydatamicroservice.json', 'a=1', 1234567890, 'abcd1234', 'unsigned', $body);
$canonical = "POST\n/entitydatamicroservice.json\na=1\n1234567890\nabcd1234\n" . hash('sha256', $body);
check($signer->createCanonicalRequest($request) === $canonical, 'canonical material');
$signature = hash_hmac('sha256', $canonical, $secret);
check($signer->sign($request, $secret) === $signature, 'signing matches XRM baseline');
$signed = new HmacAuthenticationRequest($token, 'POST', '/entitydatamicroservice.json', 'a=1', 1234567890, 'abcd1234', $signature, $body);
check($signer->verify($signed, $secret), 'verification accepts signed request');
check(!$signer->verify($signed, str_repeat('c', 43)), 'wrong secret rejected');
$tampered = new HmacAuthenticationRequest($token, 'POST', '/entitydatamicroservice.json', 'a=1', 1234567890, 'abcd1234', $signature, $body . 'x');
check(!$signer->verify($tampered, $secret), 'body tampering rejected');
echo "CredentialFoundation HmacRequestSigner: OK\n";
