# CredentialFoundation Privacy and Data Processing

This document describes the data-processing boundary of CredentialFoundation itself. CredentialFoundation is an implementation-neutral contract plugin. It defines interfaces and DTOs but does not provide a credential store, HTTP endpoint, persistence backend, logging backend, message transport, or administration interface.

This document is technical documentation and is not a legal privacy notice.

## Scope

CredentialFoundation provides shared contracts for:

- credential identity checks
- bearer and HMAC authentication
- service-grant authorization
- discoverable credential service definitions
- normalized authentication and identity results

The plugin does not decide how long data is stored, where credentials are stored, which users may create credentials, which transport is used, or whether a concrete deployment sends data to another system.

## Data that can pass through the contracts

Although the foundation does not persist data, its public DTOs and interfaces can carry security-sensitive and potentially personal data.

### Credential identity data

Successful result DTOs can contain:

- a credential identifier
- a user or owner identifier
- a service identifier
- an expiration timestamp

A user identifier is personal data when it can be related to an identifiable person or account.

### Failure information

Failure results can contain stable codes such as `expired`, `revoked`, `invalid_signature`, or `service_not_granted`. These codes describe credential state and should be exposed publicly only when the consuming API intentionally wants to reveal that level of detail.

### HMAC request material

`HmacAuthenticationRequest` can hold:

- the full credential token
- HTTP method
- path
- raw query string
- timestamp
- nonce
- signature
- raw request body

The request body and query string can contain arbitrary application data, including personal or confidential information. The credential token and signature are authentication secrets or authentication material and require stronger protection than ordinary request metadata.

## Persistence

CredentialFoundation itself does not persist any of the data described above.

It contains no:

- database repository
- migration provider
- file storage
- settings storage
- state storage
- cache storage
- session storage

Persistence behavior is entirely determined by the active credential implementation and the consuming application.

## Logging

CredentialFoundation contains no logger usage and does not log tokens, signatures, request bodies, identities, service ids, or result objects.

Consumers should take care when using the `toArray()` methods on `CredentialIdentityResult` and `CredentialAuthenticationResult`. Those arrays may contain credential ids and user ids and can become personal or security-relevant log data if written to logs.

`HmacAuthenticationRequest` intentionally has no `toArray()` method. This reduces the risk of accidentally serializing the full token, signature, nonce, and raw body as one generic structure.

## Network communication

CredentialFoundation performs no network requests and does not contact external providers.

Any network handling occurs in the consumer endpoint, access-control implementation, credential implementation, or surrounding host runtime.

## Authentication secrets

The foundation does not generate, hash, encrypt, decrypt, rotate, or store credential secrets. It only defines method parameters through which a token or HMAC request can be presented to an implementation.

A concrete implementation must define how secrets are generated, protected at rest, compared, rotated, and destroyed.

## Request identity state

`ICredentialAccess` defines a request-oriented identity boundary. An implementation may hold the currently authenticated credential identity between `identifyBearer()` or `identifyHmac()` and a subsequent `authorizeService()` call.

CredentialFoundation does not provide that stateful implementation. The lifecycle and isolation of this current-request state are the responsibility of the active implementation.

## Service metadata

`CredentialServiceDefinition` carries:

- technical service id
- label
- description

These values are normally technical metadata and are not inherently personal data. A provider should still avoid embedding secrets or user-specific data into service labels or descriptions.

## Data minimization guidance for consumers

Consumers should normally:

- avoid logging full credential tokens
- avoid logging HMAC signatures and nonces unless a concrete diagnostic purpose requires it
- avoid logging raw request bodies by default
- expose only the result fields needed by the caller
- map detailed failure codes to generic public responses where appropriate
- keep stable service ids technical rather than user-specific
- apply domain authorization after credential authentication where required

## Retention and deletion

CredentialFoundation defines no retention policy because it stores no runtime data.

Retention and deletion must be documented by the concrete implementation for any persisted credential records, replay state, logs, audit records, messages, or other operational data.

## External providers and international transfers

CredentialFoundation has no external provider integration and therefore introduces no external transfer on its own.

A concrete deployment may add external systems around credential-protected APIs, but those data flows are outside this plugin and must be documented where they are implemented.

## Security boundary

CredentialFoundation separates three concerns:

1. Establish whether a credential identity is valid.
2. Check whether that credential is granted a stable logical service.
3. Leave application-specific authorization to the consuming application.

The foundation should therefore not be treated as a complete authorization policy for the application domain.

## Operator checklist

When selecting a concrete credential implementation, document at least:

- credential storage location
- secret protection method
- key rotation behavior
- revocation behavior
- HMAC replay protection and state retention
- logging behavior
- management and administration permissions
- credential deletion behavior
- notification behavior, if any
- service-grant administration
- backup and restore handling for secret-related data

Those items are not implemented by CredentialFoundation itself.
