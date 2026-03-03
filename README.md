<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="360" alt="Laravel Logo">
</p>

<p align="center">
  <strong>Reltroner Gateway</strong><br>
  Central Authentication & Trust Gateway • Laravel 12
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Role-SSO%20Gateway-blue">
  <img src="https://img.shields.io/badge/Authority-Keycloak-orange">
  <img src="https://img.shields.io/badge/Protocol-OIDC%20%2B%20JWT-success">
  <img src="https://img.shields.io/badge/PHP-8.2+-8892BF">
  <img src="https://img.shields.io/badge/Laravel-12.x-red">
  <img src="https://img.shields.io/badge/Auth-Layer%20Frozen-black">
</p>

---

# 🔐 Reltroner Gateway

Reltroner Gateway is the **single authentication entry point** for the Reltroner ecosystem.

It implements a strict **Gateway Pattern**:

- Laravel is **NOT** the identity authority
- Keycloak is the **single source of identity truth**
- The gateway only:
  - Redirects to Keycloak
  - Exchanges authorization codes
  - Verifies ID tokens
  - Establishes a controlled session boundary
  - Issues trust tokens for internal modules

This layer is **intentionally minimal, deterministic, and frozen**.

---

# 📌 Architectural Role

```text
User
 └─▶ Reltroner Gateway (Laravel)
      ├─▶ Keycloak (Authentication Authority)
      ├─▶ ID Token Verification (RS256)
      ├─▶ Session Establishment
      └─▶ Redirect to Target Module
               └─▶ Module verifies Gateway trust
````

---

# 🎯 Core Responsibilities

The Gateway is responsible for:

* 🔐 Redirecting users to Keycloak
* 🔁 Handling OIDC authorization code callbacks
* 🪪 Verifying ID tokens (RS256 via JWKS)
* 🧱 Establishing session trust boundary
* 🪪 Issuing short-lived module tokens (HS256 internal trust)

It is **NOT** responsible for:

* User registration
* Password management
* Profile editing
* Business logic
* Role modeling
* Database-driven authentication

---

# 🧠 Architectural Principles

## 1️⃣ Single Source of Identity

Keycloak is the only identity authority.

Laravel does not:

* Store passwords
* Authenticate locally
* Mutate identity

---

## 2️⃣ Explicit Trust Boundary

Gateway establishes:

```php
session([
    'sso_authenticated' => true,
    'access_token'      => '...',
    'id_token'          => '...',
    'identity'          => [...],
]);
```

No user model mutation.
No implicit guards.

---

## 3️⃣ Deterministic Authentication

Authentication flow must be:

* Auditable
* Predictable
* Stateless across services
* Explicit in failure

No silent fallback.
No hidden behavior.

---

## 4️⃣ Frozen Authentication Layer

Phases 2–4 are permanently frozen.

Allowed changes only if:

* Critical security vulnerability
* OIDC protocol break
* Cryptographic CVE

Otherwise:

> Authentication layer must not evolve.

---

# 🔐 Authentication Flow (Detailed)

### 1️⃣ Entry

```
GET /
```

Redirects to `/sso/login` if not authenticated.

---

### 2️⃣ SSO Redirect

```
/sso/login
```

Redirects to:

```
/realms/{realm}/protocol/openid-connect/auth
```

With:

* client_id
* redirect_uri
* state
* scope=openid

---

### 3️⃣ Callback

```
/sso/callback?code=XXX&state=YYY
```

Gateway performs:

* State validation
* Authorization code exchange
* ID token verification (RS256)
* Session regeneration
* Session creation
* State invalidation

---

### 4️⃣ Logout (OIDC Compliant)

```
POST /logout
```

Flow:

* Redirect to Keycloak logout endpoint
* Include id_token_hint
* Return to `/logged-out`
* Local session invalidated

Fully OIDC RP-Initiated Logout compliant.

---

# 🧪 Testing

Run:

```bash
php artisan test
```

Tests validate:

* SSO redirect logic
* State mismatch protection
* Callback failure handling
* JWT leeway enforcement
* Module token issuance
* Audience & issuer validation
* Middleware boundary enforcement

Authentication behavior is covered.

---

# ⚠️ Windows OpenSSL Note (Development Only)

Some Windows PHP builds require explicit OpenSSL provider activation.

Minimal config example:

```ini
openssl_conf = openssl_init

[openssl_init]
providers = provider_sect

[provider_sect]
default = default_sect

[default_sect]
activate = 1
```

Environment variable:

```
OPENSSL_CONF=path/to/openssl_local.cnf
```

Production behavior is unaffected.

---

# 🗂 Current Route Surface

Minimal and intentional:

```
/
dashboard
modules/finance
sso/login
sso/callback
logout
logged-out
```

No:

* login
* register
* profile
* password reset
* local auth routes

---

# 🔗 Downstream Module Integration

Modules must:

* Trust Gateway issuer
* Verify HS256 internal tokens
* Enforce TTL (default: 60s)
* Never authenticate users directly

Gateway never calls modules.
Modules trust gateway — not vice versa.

---

# 🚫 Design Constraints (Non-Negotiable)

Do NOT add:

* Local authentication
* User profile editing
* Role logic
* Business logic
* Session replication across domains
* Feature creep into identity layer

This repository must remain:

> Authentication-only.

---

# 📊 Freeze Status

| Phase | Scope          | Status    |
| ----- | -------------- | --------- |
| 2     | Auth Gateway   | 🧊 Frozen |
| 3     | Trust Boundary | 🧊 Frozen |
| 4     | UI Contract    | 🧊 Frozen |

Authentication is considered **solved**.

---

# 🧩 Engineering Philosophy

Identity systems must be:

* Boring
* Deterministic
* Minimal
* Auditable
* Immutable once stable

Complexity belongs in business modules — not in the trust boundary.

---

# 🤝 Contribution Rules

Any change affecting:

* JWT signing
* Token validation
* Session semantics
* Issuer / audience logic
* Logout flow

Requires architectural review.

---

# 📄 License

Built on top of Laravel (MIT).

Reltroner Gateway follows the same license unless specified otherwise.

---

> “Identity must be boring, predictable, and unquestionable.”
> — Reltroner Gateway Principle

