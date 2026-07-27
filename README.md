# CredentialFoundation

CredentialFoundation is the implementation-neutral contract plugin for API credentials in BASE3.

It defines the public service, provider and DTO contracts required by credential-store implementations and consumer plugins. It does **not** store keys, discover providers itself, create database tables, render administration pages, schedule jobs or send notifications.

A concrete implementation is expected to be provided by a separate plugin such as `KeyHarbor`.

---

## 1. Purpose

CredentialFoundation allows reusable BASE3 plugins to expose credential-protected services and validate presented credentials without depending on one concrete key-store plugin.

Typical dependency direction:

```text
Consumer plugin -> CredentialFoundation
Credential implementation plugin -> CredentialFoundation
Project plugin -> concrete implementation plugins
```

CredentialFoundation must not depend on `KeyHarbor`, `MessageHub`, `Base3Ilias`, MissionBay, DataHawk or another consumer plugin.

---

## 2. Provided contracts

```text
CredentialFoundation/
├── src/
│   ├── CredentialFoundationPlugin.php
│   ├── Api/
│   │   ├── IApiCredentialService.php
│   │   └── ICredentialServiceProvider.php
│   └── Dto/
│       ├── CredentialAuthenticationResult.php
│       ├── CredentialServiceDefinition.php
│       └── HmacAuthenticationRequest.php
└── test/
```

### `ICredentialServiceProvider`

A discoverable provider exposes one or more logical services:

```php
final class DemoCredentialServiceProvider implements ICredentialServiceProvider {

	public static function getName(): string {
		return 'democredentialserviceprovider';
	}

	public function getServices(): array {
		return [
			new CredentialServiceDefinition(
				'demo:ping',
				'Demo ping',
				'Allows access to the protected ping endpoint.'
			),
			new CredentialServiceDefinition(
				'demo:report',
				'Demo report',
				'Allows access to the protected report endpoint.'
			)
		];
	}
}
```

Provider classes are discovered through `IClassMap`. One provider may expose multiple services. Service ids must be globally unique in the final runtime composition.

Recommended service-id form:

```text
<plugin>:<area>:<service>
```

Examples:

```text
missionbay:mcp:internal
datahawk:report:sales
vizion:dashboard:operations
```

Service ids use lowercase letters, numbers, dots, underscores, colons and hyphens.

### `IApiCredentialService`

Consumer endpoints depend on this known container service:

```php
$result = $credentialService->authenticateBearer(
	$token,
	'demo:report'
);

if (!$result->isAuthenticated()) {
	// Return a generic unauthorized response.
}

$userId = $result->getUserId();
```

The implementation is responsible for:

* parsing and locating the credential
* verifying the stored secret
* rejecting revoked or expired credentials
* verifying that the requested service still exists
* verifying the credential grant for that service
* performing HMAC timestamp, nonce and signature checks

The credential service does not replace domain authorization. A successful credential result identifies the credential owner and proves the service grant; the endpoint may still apply its own RBAC and domain rules.

### `HmacAuthenticationRequest`

HMAC validation receives the complete request material:

```php
$request = new HmacAuthenticationRequest(
	$token,
	$requestMethod,
	$requestPath,
	$queryString,
	$timestamp,
	$nonce,
	$signature,
	$rawBody
);

$result = $credentialService->authenticateHmac(
	$request,
	'demo:report'
);
```

The raw body is passed instead of a caller-provided body digest so the implementation can calculate the digest itself.

The DTO deliberately has no `toArray()` method because token, signature and request body must not be made convenient to log accidentally.

### `CredentialAuthenticationResult`

The result normalizes successful and rejected checks.

Successful result:

```php
CredentialAuthenticationResult::success(
	'public-key-id',
	$userId,
	'demo:report',
	$expiresAt
);
```

Rejected result:

```php
CredentialAuthenticationResult::failure(
	CredentialAuthenticationResult::FAILURE_EXPIRED,
	'demo:report'
);
```

Failure codes are intended for internal handling and diagnostics. Public HTTP endpoints should normally map detailed credential failures to generic authentication or authorization responses instead of revealing credential state.

---

## 3. Architectural boundaries

CredentialFoundation intentionally does not define:

* key persistence or database schema
* token generation or secret hashing
* encryption keys or secret storage
* service catalog implementation
* duplicate-service resolution
* administration or user displays
* migrations
* expiration jobs
* MessageHub message types
* notification recipients
* HTTP middleware or routing

Those decisions belong to the implementation plugin and the final project composition.

The foundation defines only the contracts required across plugin boundaries.

## HMAC result codes

HMAC-capable implementations may additionally return `hmac_required`,
`hmac_not_enabled`, `invalid_timestamp`, `invalid_nonce`, `invalid_signature`
or `replay_detected`.
