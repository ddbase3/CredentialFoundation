# Credential-protected HTTP services and CORS contract

This reference covers the CredentialFoundation ownership boundary for credential-protected HTTP services and their use from browser-based clients. It distinguishes **credential contracts**, **optional HTTP endpoint metadata**, and **CORS behavior owned by the active HTTP host**. The current `Base3XrmWebsite` host supports opt-in CORS using the shared endpoint catalog; CredentialFoundation remains implementation neutral. Installing this document alone does not enable CORS or add HTTP routes.

## 1. Scope and dependency direction

CredentialFoundation is a contract package. It defines credential identities, service grants, operation grants, discoverable service providers, authentication results and canonical HMAC request material. It must stay independent of KeyHarbor, MissionBay, Base3XrmWebsite, ILIAS, a particular reverse proxy and browser implementations.

```text
An application plugin
  -> ICredentialServiceProvider (logical services)
  -> its own HTTP handler (domain behavior)

CredentialFoundation
  -> contracts and DTOs only

KeyHarbor (one possible implementation)
  -> credential catalog, validation, grants, lifecycle

Host/project HTTP entry boundary
  -> route and method selection
  -> cross-origin HTTP policy where deliberately enabled
  -> existing credential authentication and authorization
  -> domain authorization and existing handler
```

CORS must not live in a second authentication or credential-grant engine. It controls whether a **browser** can read a cross-origin HTTP response. It does not identify a user, confer a service grant, prove that a caller is a browser, or grant a native domain permission.

## 2. Source of truth and compatibility

The stable foundation contracts are:

| Contract | Responsibility | Not its responsibility |
| --- | --- | --- |
| `ICredentialServiceProvider` | Supplies one or more logical service definitions, discoverable through `IClassMap` | Routing or user authentication |
| `CredentialServiceDefinition` | Immutable stable service ID, label, description, operation list and optional resource-constraint capabilities | Credential storage, token verification or endpoint execution |
| `ICredentialAccess` | Establishes the verified request identity and checks a user service grant | Native resource ACL or browser origin policy |
| `ICredentialOperationAccess` | Checks a specific operation for an authenticated technical principal | Implicit administrator access or unrestricted service-wide grants |
| `IApiCredentialService` | Authenticates bearer/HMAC material and authorizes the requested logical service | Transport routing, response headers or session UI |
| `CredentialIdentityResult` | Verified principal and authentication outcome, without a service grant | Granting an operation |
| `CredentialAuthenticationResult` | Result of a specified grant check | Replacing domain authorization |
| `CredentialSubject` | Explicit `user` or `service` subject | Inferring a human user from a service identity |
| `HmacAuthenticationRequest` | Exact method, path, query, timestamp, nonce, signature and raw body for verification | Canonicalizing an arbitrary proxy rewrite |
| `HmacRequestSigner` | Canonicalization and HMAC-SHA256 signing/verification | Storing credentials or suppressing replays |

**Version boundary:** Foundation versions that only declare logical services do not identify HTTP route paths or methods. They cannot safely infer which routes may receive CORS headers. An extended installation may additionally expose HTTP endpoint metadata, for example via `CredentialHttpEndpointDefinition` and `CredentialServiceDefinition::getHttpEndpoints()`. Any host using this extension must verify the *installed* contract and consume its declared method/path mapping as the single source of truth. It must not maintain a second, conflicting hard-coded endpoint table. In all cases, the logical service ID and explicitly declared operations remain authoritative.

This document intentionally does not invent an alternative endpoint interface, separate service router, fallback credential mode, or host configuration key.

## 3. Identity is distinct from authorization

An authenticated credential **subject** is not the same as a grant:

```text
Presented secret -> identifyBearer() or identifyHmac()
                 -> CredentialIdentityResult
                 -> authorizeService(service_id) for an eligible user credential
                 OR
                 -> authorizeOperation(service_id, operation) for a technical principal
                 -> host IAuthorizationService decision where required
                 -> native resource/operation authorization
                 -> permitted handler invocation
```

`identifyBearer()` authenticates the token without authorizing a specific service. `identifyHmac()` authenticates a signed request and enforces timestamp and nonce anti-replay in its implementation. Neither returns an automatic resource grant.

`CredentialSubject('user', '...')` means an authenticated **native account subject** as defined by the issuing host. `CredentialSubject('service', '...')` means an authenticated **technical principal**, not a person. A service identity must never be silently mapped to an administrator or human login.

**Native account ID versus login:** On a host such as Base3System/XRM, a credential result's user ID may represent the native primary key `User::$id`, whereas the active accesscontrol may use a login identifier such as `User::$userid`. They are different identifiers even if both look numeric. The current trusted native user lookup must establish the matching login where required; comparing or converting values by their numerical appearance is forbidden. Unknown or deleted accounts must fail closed.

The `getSubject()` value is null on failure. An implementation must not treat `CredentialIdentityResult::isAuthenticated()` alone as permission to execute an HTTP operation.

## 4. User and service credentials

### User credentials

- Bearer tokens are suitable for personal API clients.
- The verified token must be explicitly granted the requested service ID.
- A working browser login or cookie does not substitute for that token on an endpoint declared as credential-required.
- Domain tools remain bound by current user permissions.
- The token must not be embedded in public JavaScript packages, example repositories, browser content scripts or URLs.

### Technical service credentials

- Technical credentials use an explicit service subject and the established signed-request HMAC mode.
- The credential is not a user session.
- The requested operation must be in the owning provider's catalog and among that credential's explicitly granted operations.
- A host must still perform `IAuthorizationService` and native resource checks for the action.
- Adding browser access does not change the HMAC signature canonicalization, timestamp, nonce, revocation, expiration, or the native authorization model.
- Technical microservice endpoints are not automatically browser-public merely because they exist in a service catalog.

`authorizeService()` must deny an unrestricted service-level allow for a technical subject. `authorizeOperation()` must not be approximated by a UI-visible list of operations or by parsing the HTTP path without an authoritative mapping.

## 5. HMAC request canonicalization

The existing `HmacRequestSigner` builds exactly six newline-separated values:

```text
UPPERCASE_HTTP_METHOD
EXTERNAL_REQUEST_PATH
EXTERNAL_QUERY_STRING
UNIX_TIMESTAMP
NONCE
SHA256_HEX_OF_RAW_REQUEST_BODY
```

It computes a lowercase hexadecimal HMAC-SHA256 digest over those bytes. The endpoint verifier checks the signature using constant-time equality. The active credential implementation supplies the replay store and validity window.

The query string must be the original externally requested query, not an internal routing rewrite. The raw request body must match the bytes signed by the client. Reformatting JSON after signing changes its body digest. The timestamp, nonce and signature are transmitted in the established KeyHarbor headers and never in a query parameter. The public token format alone does not grant any operation.

A CORS preflight is **not** an HMAC request. It has no authenticated principal and must neither verify nor consume a nonce. It must never cause an HMAC-protected operation to execute.

## 6. What CORS is and is not

A browser compares its page origin with the destination origin. An origin consists of the URL **scheme, hostname and port**. A path, URL query and fragment are not part of an origin.

Cross-origin JavaScript sending `Authorization: Bearer ...` and `Content-Type: application/json` commonly triggers an `OPTIONS` preflight. The preflight normally contains neither a personal bearer token nor an HMAC signature. Its purpose is to ask whether the browser may send the eventual method and headers.

CORS is not:

- An authentication scheme or a substitute for KeyHarbor.
- A role/group model, service grant or native ACL.
- A server-side IP firewall, anti-CSRF system or request rate limiter.
- Protection from non-browser clients such as `curl` or native applications.
- Permission to trust a client-supplied `Origin` as an identity claim.
- A reason to create another HTTP API or copy a second credential catalog.

A server must continue authenticating and authorizing the **actual request** even after a successful preflight.

## 7. Host-side CORS contract for published services

The implementation belongs at the owning host's HTTP request boundary, prior to the normal credential authentication rejection for an unauthenticated `OPTIONS` preflight. It is intentionally not implemented by this foundation library. In the current XRM project, `XrmCredentialCorsMiddleware` is composed first and consumes the existing `KeyHarbor\Service\CredentialServiceCatalog`.

XRM obtains the allowlist through the existing deployment configuration,
not from this Foundation interface. For example, the XRM host uses:

```ini
[keyharbor]
cors_origins[] = "https://client.example"
```

Missing or empty lists deny cross-origin access by default. This is an
XRM host setting, not a new CredentialFoundation contract or persisted token
property. Other hosts must use their own established static configuration.

### 7.1 Inputs

For a preflight, the host reads:

```http
OPTIONS /published-service.json HTTP/1.1
Origin: https://client.example
Access-Control-Request-Method: POST
Access-Control-Request-Headers: authorization,content-type
```

The host must check all of the following before approving:

1. `Origin` is syntactically valid, a precise allowed origin, and not the special `null` origin.
2. The target method/path pair is a **discovered, published HTTP endpoint**, not an arbitrary framework output route.
3. The requested method is exactly one declared method for this endpoint.
4. Every requested header is within the endpoint's explicit set. A minimal bearer JSON endpoint needs `Authorization` and `Content-Type`; optional `Accept` is harmless but must follow the host's documented policy.
5. The deployment has opted into a concrete origin allowlist. An empty or missing list means no cross-origin allowance.
6. A technical-HMAC-only endpoint is not implicitly enabled for browser use. Its existing signed-request access stays as configured.

Reject malformed or unrecognized preflights without invoking the handler, consuming credentials or exposing additional service metadata. An origin pattern must not match using substring comparison, case-insensitive path tricks, suffixes like `evil.example`, or implicit wildcard expansion. Treat scheme and port as part of the exact origin. No origin information should be trusted as an authentication principal.

### 7.2 Successful preflight

A successful preflight may return HTTP `204 No Content` with:

```http
Access-Control-Allow-Origin: https://client.example
Access-Control-Allow-Methods: POST
Access-Control-Allow-Headers: Authorization, Content-Type, Accept, X-BASE3-Timestamp, X-BASE3-Nonce, X-BASE3-Signature
Access-Control-Max-Age: 600
Vary: Origin, Access-Control-Request-Method, Access-Control-Request-Headers
```

`204` denotes a positive transport negotiation only, **not** an authorized service call. The example max-age is the XRM host's implemented 600-second limit; it is not a credential grant TTL or a setting of CredentialFoundation. Browsers may cap the cache duration. Do not send a credential, cookie, token, nonce or HMAC secret in this response.

`Access-Control-Allow-Credentials: true` is **not needed** for applications using `fetch` with explicit Bearer authorization and `credentials: 'omit'`. Do not use this header by default to enable cookie/session forwarding. If a different hosting scenario deliberately permits browser-managed credentials, that is a separate security decision with its own risk assessment. In any event, a credentialed cross-origin response must not use `Access-Control-Allow-Origin: *`.

### 7.3 Actual response

An allowed-origin **actual** `POST` request must still run the existing identity, service/operation and domain authorization. Its response should carry:

```http
Access-Control-Allow-Origin: https://client.example
Vary: Origin
```

These headers accompany success and error responses for **allowed Origins and declared HTTP methods/paths**, including downstream `400`, `401`, `403`, `404`, `502` or `503` results. An undeclared actual HTTP method or URL alias does not gain a cross-origin response header merely because another method/path is declared. Otherwise browser clients can receive a misleading generic CORS error instead of the actual HTTP status. Do not expose stack traces, tokens, internal authentication failure details or secrets to clients.

When an origin is not allowed, omit the allow-origin response header. The host may return an explicit deny status, but the browser may report only a generic CORS failure. Do not reflect arbitrary origins.

### 7.4 Response consistency

CORS headers are transport metadata; they must be applied at a boundary that can cover early middleware responses, including the existing technical credential guard and route dispatcher. Emitting allow-origin only from one successful JSON handler is insufficient. The host must not add response headers to a redirected login page and treat that as successful API authentication.

Do not add an alternate CORS router, create a second grant authority, or clone the existing `CredentialServiceCatalog`. The HTTP host consumes the catalog's current declared endpoint metadata, if present, and leaves service/operation authorization to the existing owners.

## 8. Client integration from different platforms

| Client | Browser CORS needed? | Credential handling |
| --- | --- | --- |
| Linux `curl`, Python CLI | No | Per-user secret input or OS keyring |
| Windows PowerShell or native .NET HTTP | No | Windows credential storage or controlled memory |
| macOS native Swift or CLI | No | Keychain or protected input |
| Android native HTTP | No | Keystore-backed storage |
| iOS native `URLSession` | No | Keychain |
| Electron/Tauri privileged native networking | Usually no browser CORS for the privileged/native transport; depends on chosen runtime | Keep tokens out of untrusted renderer pages |
| Chrome/Edge/Firefox extension background context | May use extension host permissions; test browser-specific behavior | Keep tokens outside webpage/content-script context |
| Ordinary foreign-origin webpage or PWA | Yes | A personally entered token, no shared app secret, and a deliberately allowed origin |
| Same-origin authenticated web page | Cross-origin preflight not applicable | Still needs the API's declared credential if required |

Example browser call, **only after the host has enabled CORS for the origin and endpoint**:

```js
const response = await fetch('https://api.example/published-service.json', {
    method: 'POST',
    credentials: 'omit',
    headers: {
        'Authorization': `Bearer ${personalToken}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
    body: JSON.stringify({ message: 'Hello' })
});
if (!response.ok) throw new Error(`API HTTP ${response.status}`);
const payload = await response.json();
```

The body shown above is illustrative. It is valid only for a service whose actual HTTP handler declares a `message` field, such as the project-owned Jarvis API. CredentialFoundation does not define a universal payload schema, model selector, streaming protocol or conversation ID.

Never store a personal bearer token in an untrusted webpage, `localStorage` of an untrusted origin, URL, telemetry, diagnostic log or shared application bundle. CORS does not prevent an authorized malicious site from abusing a token willingly given to it.

## 9. Required negative tests for an implementation

A correct host CORS implementation must demonstrate all of these behaviors:

| Test | Expected behavior |
| --- | --- |
| `OPTIONS`, exact allowed origin and endpoint, declared method/headers | No handler invocation; successful preflight headers |
| `OPTIONS` without bearer or HMAC | May pass transport negotiation, never authenticates a user |
| `OPTIONS` on unknown path or query alias | No CORS grant and no handler invocation |
| `OPTIONS` on an undeclared method | No CORS grant |
| Unexpected requested header | Denied, not silently echoed |
| Allowed origin on actual authorized `POST` | Normal KeyHarbor/domain checks; allow-origin header |
| Allowed origin on actual invalid bearer | Existing `401`/`403`; appropriate allowed-origin header, no session fallback |
| Disallowed origin with valid bearer | No CORS grant; native token authorization itself not broadened |
| Missing/malformed or `null` Origin | No CORS grant |
| HTTPS host versus HTTP origin, different port, lookalike domain | No accidental match |
| Technical HMAC `POST` with valid signature | Exact existing verification and operation authorization |
| HMAC without operation grant | Same denial as baseline |
| HMAC timestamp or nonce invalid/replayed | Same denial as baseline |
| Ordinary session routes, login pages and asset routes | No accidental CORS exposure |
| Missing/empty origin allowlist | CORS disabled by default |
| Duplicate service or endpoint declarations | Explicit configuration failure, not last-wins |
| Normal native CLI requests without `Origin` | Unchanged existing behavior |
| Cross-origin browser error response | Valid HTTP status remains visible for an allowed origin |

The source review should include actual middleware ordering, the original request path versus rewritten routing parameters, server header forwarding and proxy/server responses. A positive `curl -X OPTIONS` result alone is not proof that browser preflights and actual requests work end to end.

## 10. Reference commands for operator verification

These are verification requests against the current XRM implementation, not proof that a particular deployed instance has the new middleware installed or a permitted Origin configured. Replace the URL and origin with an actually configured route and a deliberately allowed origin.

```sh
curl -i -X OPTIONS 'https://api.example/published-service.json' \
  -H 'Origin: https://client.example' \
  -H 'Access-Control-Request-Method: POST' \
  -H 'Access-Control-Request-Headers: authorization,content-type'
```

Negative control:

```sh
curl -i -X OPTIONS 'https://api.example/published-service.json' \
  -H 'Origin: https://untrusted.example' \
  -H 'Access-Control-Request-Method: POST' \
  -H 'Access-Control-Request-Headers: authorization,content-type'
```

Verify the allowed origin, methods, headers and status. A browser also checks those values against its actual fetch request. Do not test authentication with real tokens in shell history or in shared test logs.

## 11. Threats and operational decisions

**Origin spoofing:** Any non-browser HTTP client can send an arbitrary Origin header. Therefore origin checks do not authenticate users or services.

**Cross-site scripting:** A script executing within an allowlisted origin can act as that origin. Approval should reflect the trust level of the entire application, including dependencies and deployment practices.

**Client token exposure:** A bearer credential is transferable if stolen. Browser applications need an explicit secret-handling design and must not silently share a single personal account token among users.

**CSRF:** If any endpoint accepts cookie-backed authentication, address CSRF independently. The personal bearer-only request does not create a new cookie authentication mode.

**Error disclosure:** A browser client should see the HTTP status for its own failed request, but not secret material, native user IDs, SQL errors or stack traces.

**Preflight caching:** Browser preflight caching is not the credential-grant cache. Rotating/revoking a token continues to affect the actual request regardless of a cached preflight.

**Retention and privacy:** CORS introduces no credential storage on its own. The host still decides where request logs, prompts, responses and analytics are processed and retained. Consult its root `PRIVACY.md` as well as the owning endpoint's privacy documentation.

**Resource availability:** Repeated `OPTIONS` calls must not trigger heavy agent execution, database queries that grant access, HMAC replay state changes, or background jobs. Abuse/rate limiting remains with existing infrastructure.

## 12. Implementation ownership checklist

Before changing source code, verify:

- [ ] The installed `CredentialServiceDefinition` and optional HTTP endpoint DTO signatures.
- [ ] `ICredentialServiceProvider` and the existing KeyHarbor catalog remain the sole service metadata owner.
- [ ] The HTTP host's current middleware chain and earliest response boundary.
- [ ] The existing `IConfiguration` contract for reading deployment-level values.
- [ ] Exact personal-token session identity mapping and service-grant checks.
- [ ] Technical HMAC operation grants and native ACL checks.
- [ ] The owning endpoints and their declared methods/paths, including legacy routing aliases.
- [ ] Source-local tests for both positive and negative cases, plus real browser testing.
- [ ] Source-verified rollback of all affected files and configuration.
- [ ] Updated KeyHarbor/host docs, privacy information, and any affected localized UI strings.

The desired result is a single CORS-aware HTTP transport boundary over existing credential-protected services, **not** additional credentials, profiles, router layers, authorization decisions or persistence. If the installed source cannot provide authoritative endpoint metadata, do not guess route ownership. Repair that contract at the existing boundary before enabling CORS.

## 13. Standards and source references

- [WHATWG Fetch Standard, CORS protocol](https://fetch.spec.whatwg.org/#http-cors-protocol)
- [MDN: Cross-Origin Resource Sharing](https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/CORS)
- `src/Api/ICredentialAccess.php`
- `src/Api/ICredentialOperationAccess.php`
- `src/Api/ICredentialServiceProvider.php`
- `src/Api/IApiCredentialService.php`
- `src/Dto/CredentialServiceDefinition.php`
- `src/Dto/CredentialHttpEndpointDefinition.php`
- `src/Dto/CredentialIdentityResult.php`
- `src/Dto/CredentialAuthenticationResult.php`
- `src/Dto/CredentialSubject.php`
- `src/Dto/HmacAuthenticationRequest.php`
- `src/Security/HmacRequestSigner.php`

Source paths in the last list are relative to the CredentialFoundation plugin root. The XRM host uses `IConfiguration::getArray('keyharbor', 'cors_origins', [])` for its exact HTTPS Origin allowlist, with optional HTTP loopback origins for development. CredentialFoundation itself declares no CORS config key and performs no HTTP header emission.
