# 🔁 SEQUENCE DIAGRAM
## Reltroner Gateway
### Authentication & Trust Flow Sequences

---

# 1. Purpose

This document defines the **exact runtime interaction sequences** between:

- User (Browser)
- Reltroner Gateway (Laravel)
- Keycloak (Identity Provider)
- Downstream Modules (Finance, HRM, etc.)

All sequences are deterministic and explicitly fail closed.

---

# 2. Primary Authentication Flow (OIDC Authorization Code)

## Actors

- User Browser
- Gateway
- Keycloak

---

## Sequence

```text
User Browser
    │
    │  GET /
    ▼
Gateway
    │
    │  Redirect → /sso/login
    ▼
Gateway
    │
    │  Generate state
    │  Store state in session
    │
    │  Redirect →
    │  /realms/{realm}/protocol/openid-connect/auth
    ▼
Keycloak
    │
    │  User Authentication
    │  (password/MFA handled by IdP)
    │
    │  Redirect →
    │  /sso/callback?code=XXX&state=YYY
    ▼
Gateway
    │
    │  Validate:
    │   - State exists
    │   - State matches
    │   - Code present
    │
    │  Exchange code →
    │  POST /protocol/openid-connect/token
    ▼
Keycloak
    │
    │  Returns:
    │   - access_token
    │   - id_token (RS256)
    │   - expires_in
    ▼
Gateway
    │
    │  Verify ID token:
    │   - RS256 signature via JWKS
    │   - iss check
    │   - aud check
    │   - exp check
    │
    │  Regenerate session
    │  Store session data
    │  Invalidate state
    │
    │  Redirect → /dashboard
    ▼
User Browser
````

---

# 3. Failure Sequences (Authentication)

## 3.1 State Mismatch

```text
Callback received
    │
    ├─ State missing
    ├─ OR state mismatch
    ▼
Gateway
    │
    └─ HTTP 403 (fail closed)
```

---

## 3.2 Missing Code

```text
Callback without code
    ▼
Gateway
    └─ HTTP 403
```

---

## 3.3 Invalid ID Token

```text
ID token verification fails:
    - Signature invalid
    - Issuer mismatch
    - Audience mismatch
    - Expired token
    ▼
Gateway
    └─ HTTP 403
```

No partial session is created.

---

# 4. Session Lifecycle

## On Successful Authentication

```php
session([
    'sso_authenticated' => true,
    'identity'          => [...],
    'access_token'      => '...',
    'id_token'          => '...',
    'expires_at'        => timestamp,
]);
```

Properties:

* Session regenerated
* State removed
* Bound to gateway domain only

---

## On Logout

```text
User
  │
  │ POST /logout
  ▼
Gateway
  │
  │ Redirect →
  │ /protocol/openid-connect/logout
  │  (id_token_hint included)
  ▼
Keycloak
  │
  │ Clears SSO session
  │ Redirect → /logged-out
  ▼
Gateway
  │
  │ Invalidate local session
  │ Regenerate CSRF token
  │ Redirect → /sso/login
```

Logout is complete and authoritative.

---

# 5. Module Trust Flow

## Actors

* User Browser
* Gateway
* Finance Module (example)

---

## Sequence

```text
User Browser
    │
    │  Click Finance
    ▼
Gateway
    │
    │  Generate short-lived HS256 token:
    │   - iss
    │   - aud = finance
    │   - exp (TTL 60s)
    │
    │  Redirect →
    │  finance.reltroner.test/sso/consume?token=XYZ
    ▼
Finance Module
    │
    │  Verify:
    │   - HS256 signature
    │   - Issuer
    │   - Audience
    │   - Expiration
    │
    │  Bootstrap local session
    ▼
User Browser
```

---

# 6. Forbidden Sequences

The following flows are explicitly disallowed:

## ❌ Direct Module Authentication

```text
User → Finance Module → Login
```

Not allowed.

---

## ❌ Module Direct Keycloak Login

```text
Finance → Keycloak → Session
```

Not allowed.

---

## ❌ Cross-Domain Session Trust

```text
Finance trusts Gateway cookie
```

Not allowed.

All trust must be token-based.

---

# 7. Timing Constraints

* Authorization codes are single-use.
* Internal tokens TTL = 60 seconds.
* ID token expiration enforced.
* Leeway tolerance explicitly defined.

No long-lived cross-service trust.

---

# 8. Determinism Guarantees

Each sequence guarantees:

* Either full success
* Or explicit failure

No intermediate trust states.

No silent fallback.

---

# 9. Summary of System Guarantees

| Flow         | Deterministic | Fail Closed | Cryptographically Enforced |
| ------------ | ------------- | ----------- | -------------------------- |
| Login        | ✅             | ✅           | RS256                      |
| Callback     | ✅             | ✅           | RS256                      |
| Session      | ✅             | N/A         | Domain-bound               |
| Module Trust | ✅             | ✅           | HS256                      |
| Logout       | ✅             | ✅           | OIDC                       |

---

# 10. Final Statement

Authentication in Reltroner Gateway follows strict sequential guarantees:

1. Identity validated by IdP.
2. Tokens verified cryptographically.
3. Session established deterministically.
4. Trust delegated explicitly.
5. Logout authoritative and complete.

No implicit behavior exists.

---

> “Authentication must be predictable at every step.”
> — Reltroner Gateway Principle

