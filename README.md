# CredentialFoundation

CredentialFoundation is the implementation-neutral contract plugin for API credentials and reusable encryption in BASE3.

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
│   │   ├── ICredentialAccess.php
│   │   ├── ICredentialOperationAccess.php
│   │   └── ICredentialServiceProvider.php
│   └── Dto/
│       ├── CredentialAuthenticationResult.php
│       ├── CredentialIdentityResult.php
│       ├── CredentialSubject.php
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

The credential service does not replace domain authorization. For user credentials the owner remains the authenticated user; for technical credentials the subject is a separate service identity, never the issuing administrator. A successful credential grant does not authorize domain resources. The receiving endpoint must call its authoritative `IAuthorizationService` and enforce native resource ACLs.

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


## Technical service credentials (service-identity extension)

`CredentialSubject` identifies the verified principal as `user` or `service`. User principals retain the pre-existing `getUserId()` contract. Service principals carry a stable lower-case service id such as `contourz`, while `getUserId()` is `null`; they cannot establish a human session. `CredentialIdentityResult` and `CredentialAuthenticationResult` expose `getSubject()`, and append an optional subject argument to the existing success factories. The previous calls remain valid.

Service providers declare exact operations in the fourth, optional constructor argument of `CredentialServiceDefinition`:

```php
new CredentialServiceDefinition(
    'xrm:entity:data',
    'XRM Entity Data',
    'Selected entity operations',
    ['getEntries', 'getEntry']
);
```

The credential API separates identity verification from operation grants. `ICredentialOperationAccess extends ICredentialAccess`; original implementations are unchanged and the new capability is opt-in:

```php
$identity = $access->identifyHmac($signedRequest);
if (!$identity->isAuthenticated() || !$identity->getSubject()?->isService()) {
    // Deny.
}
$grant = $access->authorizeOperation('xrm:entity:data', 'getEntries');
if (!$grant->isAuthenticated()) {
    // Deny.
}
// Additionally authorize the actual resource and action through IAuthorizationService.
```

`ICredentialAccess::authorizeService()` intentionally **denies** a technical service principal without an exact operation; `ICredentialOperationAccess::authorizeOperation()` performs the method-grant check; legacy user-owned service grants still behave as before. Technical credentials must be HMAC-only. Merely identifying a service does not grant a method, privileged group, or resource permission. The current code does not yet install a service-principal mapping into XRM or any other host.

**Integration boundary:** The project plugin (for XRM, `Base3XrmWebsitePlugin`) selects the authentication method. A service endpoint must require the exact operation grant and separately call the host's `IAuthorizationService` and resource-owning native ACL.

## 3. Architectural boundaries

CredentialFoundation intentionally does not define:

* key persistence or database schema
* token generation or secret hashing
* actual encryption key material or secret storage
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

## Documentation

- [Frequently Asked Questions](docs/faq.md)
- [Privacy and data processing](PRIVACY.md)


## Managed encryption contracts

Encryption profiles are a separate lifecycle from API tokens. The same foundation
exposes `IEncryptionTargetProvider`, `IEncryptionProfileService`,
`EncryptionTargetDefinition`, and `EncryptionKeyMaterial`. Consumers use only
these contracts and do not import KeyHarbor classes.

A classmap-discoverable provider enumerates **configured instances**, not just
implementation classes. Its target IDs must remain globally stable and distinct.
A project or host supplies the normal `IEncryptionProfileService` binding.
For example:

```php
final class ExampleEncryptionTargets implements IEncryptionTargetProvider {
	public static function getName(): string { return 'exampleencryptiontargets'; }
	public function getTargets(): array {
		return [new EncryptionTargetDefinition('example:document-store', 'Document Store')];
	}
}
```

On new writes, consumers call `getWriteKey($targetId)`; a null result means no
assignment. With an assignment, failures must stop the write rather than write
plaintext. Consumers persist the returned versioned method and globally unique
key ID beside the ciphertext. For reads they call `getReadKey($method, $keyId)`
to resolve historical keys independently of today's assignments. The provider
must never assume that removing an assignment means older data no longer exists.

The foundation is not a key vault and cannot assert that historical ciphertext
has been destroyed. Cryptographic implementations remain under BASE3 `ICrypt`.
