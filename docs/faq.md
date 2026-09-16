# CredentialFoundation FAQ

## What is CredentialFoundation?

CredentialFoundation is the implementation-neutral BASE3 contract layer for API credentials. It defines the interfaces and data transfer objects that credential implementations and credential-protected consumer plugins can share without depending on one concrete credential store.

CredentialFoundation itself does not create or persist credentials, authenticate HTTP requests on its own, render management screens, run jobs, create database tables, send messages, or select a concrete credential implementation.

## What does the plugin provide?

The current plugin provides three API contracts and four DTOs:

- `IApiCredentialService` for direct bearer and HMAC authentication against one logical service.
- `ICredentialAccess` for request identity resolution followed by a separate service authorization check.
- `ICredentialServiceProvider` for discoverable definitions of credential-protected services.
- `CredentialAuthenticationResult` for the combined authentication and service-grant result.
- `CredentialIdentityResult` for authentication without a service-grant decision.
- `CredentialServiceDefinition` for stable service metadata.
- `HmacAuthenticationRequest` for the complete material required to validate an HMAC request.

The plugin class only registers itself as a shared BASE3 plugin service.

## What is the difference between `IApiCredentialService` and `ICredentialAccess`?

`IApiCredentialService` performs authentication and service authorization in one call:

```php
$result = $credentialService->authenticateBearer($token, 'example:report:read');
```

`ICredentialAccess` separates those two steps. Access control can first establish the credential identity for the current request:

```php
$identity = $credentialAccess->identifyBearer($token);
```

A consumer can then authorize its own stable service id:

```php
$result = $credentialAccess->authorizeService('example:report:read');
```

This separation lets request authentication establish the current user without coupling the authentication layer to every consumer service id.

## Does successful credential authentication replace domain authorization?

No. CredentialFoundation only defines credential identity and service-grant contracts. A successful credential result can identify an owner and prove that the credential has a grant for a logical service. The consuming application may still apply its own role, permission, object, tenant, or domain authorization rules.

## How are credential-protected services declared?

A plugin implements `ICredentialServiceProvider` and returns one or more `CredentialServiceDefinition` objects. The provider is discoverable through the BASE3 class map.

Example:

```php
final class ExampleCredentialServiceProvider implements ICredentialServiceProvider {

    public static function getName(): string {
        return 'examplecredentialserviceprovider';
    }

    public function getServices(): array {
        return [
            new CredentialServiceDefinition(
                'example:report:read',
                'Example report read',
                'Allows credential access to example reports.'
            )
        ];
    }
}
```

CredentialFoundation validates the syntax of each service id. Service ids may contain lowercase letters, numbers, dots, underscores, colons, and hyphens, and must start with a lowercase letter or number.

## Are service ids required to be globally unique?

The foundation describes one logical service by one stable service id. Global duplicate handling belongs to the concrete implementation that builds the service catalog. Consumers should nevertheless choose ids that are unique in the final runtime composition.

A practical naming convention is:

```text
<plugin>:<area>:<service>
```

## What does `CredentialIdentityResult` contain?

A successful identity result contains:

- whether authentication succeeded
- the credential id
- the owner user id, if available
- the credential expiration timestamp, if present

A failed result contains a stable failure code and no credential or user identity.

`toArray()` is available for structured handling.

## What does `CredentialAuthenticationResult` add?

It adds the service id and represents the result after the requested service grant has also been checked.

Successful results contain:

- `authenticated`
- `credential_id`
- `user_id`
- `service_id`
- `expires_at`

Failed results contain a stable failure code and the relevant service id where applicable.

## Which failure codes are defined?

The current result DTOs cover credential syntax, lifecycle, HMAC validation, and service authorization. Depending on the result type, the defined codes include:

```text
malformed_credential
invalid_credential
revoked
expired
service_not_found
service_not_granted
hmac_not_enabled
hmac_required
invalid_timestamp
invalid_nonce
replay_detected
invalid_signature
```

Consumer HTTP endpoints should normally translate detailed internal failures into appropriately generic public authentication or authorization responses when revealing the exact credential state would be undesirable.

## What is carried by `HmacAuthenticationRequest`?

The DTO contains:

- the complete credential token
- the HTTP method
- the request path
- the raw query string
- the Unix timestamp
- the nonce
- the HMAC signature
- the raw request body

The method is normalized to uppercase. The DTO validates required values and requires the path to start with `/`.

## Why does the HMAC request contain the raw body instead of a body hash?

The contract intentionally gives the credential implementation the original body so the implementation can calculate the body digest itself. This avoids trusting a caller-supplied digest as input to signature verification.

## Does `HmacAuthenticationRequest` provide `toArray()`?

No. The DTO deliberately exposes typed getters only. The token, signature, nonce, and raw body are sensitive request material and are therefore not made especially convenient to serialize or log accidentally.

## Does CredentialFoundation store credentials or secrets?

No. It contains no credential repository, database schema, filesystem persistence, state store, or secret storage implementation.

## Does CredentialFoundation generate tokens or hash secrets?

No. Token generation, parsing, hashing, encryption, rotation, revocation, and replay protection belong to a concrete implementation plugin.

## Does CredentialFoundation read HTTP headers?

No. It defines DTOs and interfaces that can be used by an HTTP authentication implementation, but it does not read request headers or request bodies itself.

## Does CredentialFoundation provide a management UI?

No. User management and administration displays belong to concrete credential implementations or project-specific integrations.

## Does CredentialFoundation send expiration notifications?

No. It has no worker job, message type, queue integration, or transport integration.

## Does CredentialFoundation depend on a database?

No. The foundation can be loaded without any database service because it defines contracts only.

## Does CredentialFoundation depend on one particular credential implementation?

No. That is the main purpose of the plugin. Consumer code can depend on the foundation interfaces while the final runtime selects the active implementation.

## Where should privacy and data-processing details be documented?

See [PRIVACY.md](../PRIVACY.md). Because CredentialFoundation is a contract plugin, most concrete storage, retention, network, logging, and security decisions are made by the active implementation and the consuming endpoint rather than by this plugin itself.
