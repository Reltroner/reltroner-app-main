# 🔐 SECURITY MODEL

## Reltroner Gateway

### Authentication & Trust Boundary Specification

---

# 1. Purpose

This document defines the security architecture, trust boundaries, threat model, guarantees, and incident handling posture of the Reltroner Gateway.

The Gateway is the **Authentication Entry Point** and **Trust Authority** for all Reltroner modules.

Security in this system is:

* Explicit
* Deterministic
* Minimal
* Boundary-driven

---

# 2. System Role

Reltroner Gateway:

* Redirects users to Keycloak
* Validates OIDC responses
* Verifies ID tokens (RS256 via JWKS)
* Establishes a controlled session boundary
* Issues short-lived internal trust tokens (HS256)

It does NOT:

* Authenticate users locally
* Store passwords
* Manage roles
* Perform business logic
* Act as a user authority

Identity authority belongs exclusively to **Keycloak**.

---

# 3. Trust Boundaries

## 3.1 External Boundary

```
User Browser ↔ Gateway ↔ Keycloak
```

Keycloak is the identity authority.

The Gateway trusts:

* Keycloak’s JWKS endpoint
* RS256-signed ID tokens
* Explicit issuer
* Explicit audience

The Gateway does NOT trust:

* Browser input
* Query parameters without validation
* Implicit state
* Unsigned JWTs
* HS-based ID tokens

---

## 3.2 Internal Boundary

```
Gateway → Downstream Module (Finance, HRM, etc.)
```

Gateway issues short-lived HS256 tokens.

Modules trust:

* Explicit issuer
* Explicit audience
* TTL enforcement (60s)
* Signature verification

Gateway does NOT trust:

* Module sessions
* Cross-domain cookies
* Module state
* Implicit trust escalation

No cross-service session replication exists.

---

# 4. Threat Model

---

## 4.1 CSRF / State Injection

Mitigated by:

* Cryptographically secure `state`
* State stored in session
* Exact match validation
* Immediate 403 on mismatch

---

## 4.2 Authorization Code Replay

Mitigated by:

* Keycloak single-use enforcement
* State invalidation after use
* Session regeneration on success

---

## 4.3 ID Token Forgery

Mitigated by:

* RS256 verification
* JWKS validation
* Issuer check
* Audience check
* Expiration check
* Explicit leeway (60 seconds)

Unsigned or malformed tokens are rejected deterministically.

---

## 4.4 Session Fixation

Mitigation:

```php
$request->session()->regenerate();
```

Executed immediately after successful authentication.

---

## 4.5 Token Replay Between Modules

Mitigated by:

* Explicit `aud` claim per module
* Short TTL (60 seconds)
* Strict issuer validation
* No shared cookies

---

## 4.6 Implicit Trust Escalation

Mitigated by:

* No shared Laravel session across services
* No module-local authentication fallback
* Gateway-only identity boundary

---

## 4.7 Logout Bypass

Mitigated by:

* OIDC RP-Initiated Logout
* `id_token_hint`
* Post logout redirect validation
* Local session invalidation

---

## 4.8 Crypto Environment Instability (Development)

Mitigated by:

* Explicit OpenSSL provider configuration (Windows)
* Deterministic test fixtures
* Production uses JWKS, not local PEM keys

---

# 5. 2026 RSA Test Key Exposure Incident (Resolved)

## 5.1 Incident Summary

On March 3rd, 2026, a test RSA private key was detected in the repository by GitGuardian.

Location:

* `crypto-debug/private.pem`
* `tests/Fixtures/private.pem`

The key was:

* Test-only
* Not used in production
* Not referenced in configuration
* Not used for JWT runtime

---

## 5.2 Risk Assessment

Production systems were NOT affected.

The exposed key:

* Was never deployed
* Was never referenced in environment config
* Was never used for gateway signing
* Was never used for HS256 secrets

Severity classification: Low (Test Artifact Exposure)

---

## 5.3 Remediation Actions

1. Removed files from working tree
2. Removed from Git index
3. Rewrote Git history via `git filter-repo`
4. Force-pushed rewritten history
5. Re-synchronized working repository
6. Verified removal via:

   ```
   git grep "BEGIN RSA PRIVATE KEY"
   git log --all -- crypto-debug
   ```
7. Executed full regression test suite
8. Added `.gitignore` rule for `*.pem`
9. Added pre-commit protection layer

Incident lifecycle: CLOSED

---

## 5.4 Architecture Impact

No changes were made to:

* SSOController
* JWT algorithm
* Trust boundary
* Session model
* Token TTL
* Audience validation
* Issuer validation

Architecture freeze preserved.

---

# 6. Authentication Guarantees

The gateway guarantees:

* No silent authentication
* No fallback login
* No partial session state
* Explicit failure on invalid state
* Explicit failure on invalid token
* Session regeneration on login
* Session invalidation on logout

Authentication is atomic.

---

# 7. JWT Security Model

## 7.1 External ID Tokens

* Algorithm: RS256
* Verified via JWKS
* Required claims:

  * iss
  * aud
  * exp
  * iat
  * sub

Unsigned tokens are rejected.

---

## 7.2 Internal Trust Tokens

* Algorithm: HS256
* TTL: 60 seconds
* Explicit issuer
* Explicit audience
* No long-lived tokens

TTL modification requires architectural approval.

---

# 8. Defense in Depth

Security layers:

1. Keycloak as identity authority
2. RS256 signature validation
3. Issuer & audience enforcement
4. TTL enforcement
5. Session regeneration
6. History purge capability
7. GitHub secret scanning
8. `.gitignore` secret exclusion
9. Pre-commit guard (development layer)

No single layer is relied upon exclusively.

---

# 9. Explicit Non-Features

Gateway does NOT support:

* RBAC
* Permission management
* Profile editing
* Password reset
* Email verification
* Account lifecycle management
* MFA logic

These belong to Keycloak.

---

# 10. Security Constraints (Non-Negotiable)

The following must not change without formal review:

* Token algorithm selection
* TTL enforcement
* Session regeneration
* State validation
* Issuer validation
* Audience validation
* Logout semantics

---

# 11. Secure Development Rules

Changes to:

* SSOController
* KeycloakIdentityService
* ModuleTokenFactory
* JWT handling
* Session model

Require:

* Threat analysis
* Test coverage
* Explicit reasoning
* Architectural approval

---

# 12. Residual Risk

Acceptable trade-offs:

* Dependency on Keycloak availability
* Short TTL may require re-auth in edge cases
* JWKS endpoint dependency

These are intentional design constraints.

---

# 13. Security Philosophy

The Gateway follows:

* Identity must be boring
* Trust must be explicit
* Tokens must be short-lived
* Failure must be deterministic
* No hidden behavior
* No implicit state

Minimalism reduces attack surface.

---

# 14. Final Security Statement

Reltroner Gateway enforces:

* Explicit identity boundary
* Deterministic authentication
* Short-lived trust delegation
* Zero implicit cross-service trust
* Documented incident response capability

Security is not an add-on.

It is embedded in the architecture.

---

> “Security is clarity enforced through boundaries.”
> — Reltroner Gateway Security Principle
