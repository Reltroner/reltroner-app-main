# 🧱 TRUST BOUNDARY DIAGRAM
## Reltroner Gateway
### Identity & Trust Segmentation Model

---

# 1. Purpose

This document defines the **explicit trust boundaries** within the Reltroner ecosystem.

It explains:

- Who trusts whom
- Where trust begins
- Where trust ends
- What is cryptographically enforced
- What is intentionally not trusted

The goal is clarity over convenience.

---

# 2. High-Level Architecture

```text
┌──────────────────────────────────────────────┐
│                  USER BROWSER               │
└──────────────────────────────────────────────┘
                     │
                     │  (Untrusted Input)
                     ▼
┌──────────────────────────────────────────────┐
│           RELTRONER GATEWAY (Laravel)       │
│----------------------------------------------│
│  - OIDC Redirect                             │
│  - Code Exchange                             │
│  - ID Token Verification (RS256)            │
│  - Session Boundary                          │
│  - Internal Token Issuance (HS256)          │
└──────────────────────────────────────────────┘
                     │
                     │  (OIDC / RS256 Trust)
                     ▼
┌──────────────────────────────────────────────┐
│                KEYCLOAK (IdP)               │
│----------------------------------------------│
│  - Identity Authority                        │
│  - Passwords                                 │
│  - MFA / Policies                            │
│  - Token Issuance (RS256)                    │
└──────────────────────────────────────────────┘
````

---

# 3. External Trust Boundary

## Boundary A: Browser → Gateway

Status: ❌ Untrusted

The gateway does NOT trust:

* Query parameters
* Cookies blindly
* Authorization codes without validation
* Any client-supplied claim

Mitigations:

* State validation
* Code presence validation
* Explicit ID token verification
* Session regeneration

All user input is treated as hostile until proven otherwise.

---

## Boundary B: Gateway → Keycloak

Status: ✅ Cryptographically Trusted

The gateway trusts Keycloak only through:

* RS256-signed ID tokens
* JWKS public key verification
* Issuer validation
* Audience validation
* Expiration validation

The gateway does NOT trust:

* Unsigned tokens
* HS256 ID tokens
* Tokens without proper issuer
* Tokens with invalid `aud`

Trust is cryptographically enforced, not assumed.

---

# 4. Internal Trust Boundary

```text
Gateway ───────▶ Downstream Module
         (HS256 Token)
```

Status: Limited & Explicit

The gateway issues short-lived trust tokens with:

* HS256 signature
* TTL = 60 seconds
* Explicit `iss`
* Explicit `aud`

Downstream modules must:

* Verify signature
* Verify issuer
* Verify audience
* Enforce expiration

Modules do NOT:

* Trust browser session
* Trust cookies from other domains
* Authenticate users directly

---

# 5. Session Boundary Model

Within the Gateway:

```php
session([
    'sso_authenticated' => true,
    'identity'          => [...],
    'id_token'          => '...',
    'expires_at'        => timestamp,
]);
```

This session:

* Exists only within gateway domain
* Is not shared across services
* Is regenerated on login
* Is invalidated on logout

Session trust does not cross domains.

---

# 6. Logout Boundary

```text
Browser
  │
  ▼
Gateway (POST /logout)
  │
  ▼
Keycloak Logout Endpoint
  │
  ▼
Gateway /logged-out
```

Guarantees:

* IdP session terminated
* Gateway session invalidated
* No partial logout state

Logout is authoritative and complete.

---

# 7. What Is Intentionally NOT Trusted

The system intentionally does not trust:

* Cross-domain cookies
* Shared sessions
* Browser local storage
* Client-generated tokens
* Module-to-module identity sharing
* Implicit authentication

All trust must originate from:

Keycloak → Gateway → Module

Never:

Browser → Module

---

# 8. Data Flow Summary

## Authentication Flow

```text
User → Gateway → Keycloak → Gateway → Session
```

## Module Access Flow

```text
User → Gateway → Internal Token → Module
```

## No Direct Flow Allowed

```text
User ✖ Module (direct authentication)
Module ✖ Keycloak (direct login)
Module ✖ Browser (implicit trust)
```

---

# 9. Trust Segmentation Rules

1. Identity authority is centralized.
2. Session authority is local to gateway.
3. Module trust is delegated and time-limited.
4. No transitive trust exists.
5. No circular trust relationships exist.

---

# 10. Failure Model

If any boundary validation fails:

* State mismatch → 403
* Missing code → 403
* Invalid signature → 403
* Expired token → 403
* Invalid audience → 403

System fails closed, never permissive.

---

# 11. Security Philosophy

Trust boundaries are explicit lines in the architecture.

They are:

* Minimal
* Enforced
* Measurable
* Non-negotiable

Security is achieved through:

* Separation
* Determinism
* Cryptographic enforcement
* Strict scope control

---

# 12. Final Boundary Statement

Reltroner Gateway enforces three strict layers:

1. Untrusted Input Layer (Browser)
2. Identity Authority Layer (Keycloak)
3. Delegated Trust Layer (Modules)

No trust crosses boundaries without cryptographic proof.

---

> “Trust must be earned cryptographically, not assumed architecturally.”
> — Reltroner Gateway Principle

