<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="360" alt="Laravel Logo">
</p>

<p align="center">
  <strong>Reltroner Gateway</strong><br>
  Central Authentication & Trust Gateway • Laravel 12
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Role-Auth%20Gateway-blue">
  <img src="https://img.shields.io/badge/Protocol-JWT-success">
  <img src="https://img.shields.io/badge/SSO-Keycloak-orange">
  <img src="https://img.shields.io/badge/PHP-8.2+-8892BF">
  <img src="https://img.shields.io/badge/Laravel-12.x-red">
</p>

---

## 📌 Overview

**Reltroner Gateway** is the **central authentication, authorization, and trust authority** for the Reltroner ecosystem.

This service is responsible for:

- User authentication (SSO)
- Token issuance (JWT)
- Cross-module trust verification
- Acting as the **single source of identity truth**

It is intentionally **stateless, deterministic, and security-first**.

---

## 🎯 Core Responsibilities

- 🔐 Authenticate users via **Keycloak**
- 🪪 Issue signed JWT tokens
- 🔁 Validate session trust for downstream modules
- 🌐 Serve as SSO entry point for all Reltroner applications

Downstream services (Finance, HRM, ERP, etc.) **never authenticate users directly**.

---

## 🧠 Architectural Principles

### 1️⃣ Single Source of Identity
- All authentication flows pass through this gateway
- No duplicated login logic in downstream services

### 2️⃣ Trust, Not State
- Gateway issues **signed tokens**
- Modules verify tokens using shared trust keys
- No session replication across services

### 3️⃣ Security Over Convenience
- Explicit token validation
- Clear issuer & audience checks
- No silent fallback or auto-login

### 4️⃣ Determinism Over Complexity
- Unit tests must be deterministic
- Crypto correctness belongs to cryptographic libraries
- Gateway tests validate behavior, not OpenSSL internals

---

## 🔐 Authentication Flow (High Level)

```text
User
 └─▶ Reltroner Gateway
      ├─▶ Keycloak (Auth)
      ├─▶ Issue JWT
      └─▶ Redirect to Target App
               └─▶ Token Verified (Trust Only)
````

---

## ⚙️ Installation & Setup

### 1️⃣ Install Dependencies

```bash
composer install
npm install
```

---

### 2️⃣ Environment Configuration

Copy `.env.example` to `.env` and configure:

```env
KEYCLOAK_BASE_URL=
KEYCLOAK_REALM=
KEYCLOAK_CLIENT_ID=
KEYCLOAK_CLIENT_SECRET=
KEYCLOAK_REDIRECT_URI=
KEYCLOAK_LOGOUT_URL=
```

Recommended gateway variables:

```env
APP_URL=http://app.reltroner.test
JWT_TTL=3600
JWT_ISSUER=reltroner-gateway
```

---

## 🧪 Testing

Run full test suite:

```bash
php artisan test
```

Tests cover:

* SSO redirect flow
* Callback validation
* State mismatch protection
* JWT leeway tolerance
* Module trust validation
* Audience validation
* Gateway-only responsibilities

---

## ⚠️ Known Issue — Windows OpenSSL RSA (Test Environment)

On some Windows PHP builds (e.g., Laragon), RSA signing or verification may throw:

```
DomainException: OpenSSL unable to validate key
```

This is caused by:

* Windows OpenSSL binding strictness
* PEM formatting sensitivity
* Environment-level crypto parsing

### Important:

This does **NOT** affect production logic.

Gateway production path uses:

```
JWKS → RS256 verification
```

Unit tests may use deterministic test-mode keys to avoid OS-specific OpenSSL behavior.

This is an **environment constraint**, not a gateway security flaw.

---

## 🔗 Integration with Other Modules

Downstream modules configure:

```env
RELTRONER_GATEWAY_ISSUER=http://app.reltroner.test
RELTRONER_GATEWAY_AUDIENCE=finance.reltroner.test
RELTRONER_MODULE_SIGNING_KEY=shared-secret
```

Gateway:

* Does not depend on module state
* Does not call module APIs
* Only issues identity trust tokens

Modules must trust the gateway — not vice versa.

---

## 🗂️ Typical Consumers

* Finance Module
* HRM Module
* ERP Dashboard
* Admin Console
* Future Reltroner services

---

## ⚠️ Design Constraints

This repository must remain minimal and security-focused.

### ❌ Never Add

* Business logic
* Accounting logic
* Cross-module mutation
* Session storage replication
* Feature creep

### ✅ Only Responsible For

* Authentication
* Token issuance
* Trust validation
* Identity boundary enforcement

---

## 🚀 Roadmap

| Phase | Scope                      | Status  |
| ----- | -------------------------- | ------- |
| 1     | SSO via Keycloak           | ✅ done  |
| 2     | JWT Trust Model            | ✅ done  |
| 3     | Multi-module Audience      | ✅ done  |
| 4     | Token Hardening & Rotation | planned |
| 5     | Key Rotation Automation    | planned |

---

## 🧩 Engineering Philosophy

Identity systems must be:

* Predictable
* Auditable
* Boring
* Deterministic

Complexity belongs in business modules — not in the trust boundary.

---

## 🤝 Contribution Rules

* Do not weaken token validation
* Do not add implicit trust
* Security review required for crypto changes
* No OpenSSL experimentation in production path

---

## 📄 License

Built on top of **Laravel Framework (MIT License)**.

Reltroner Gateway follows the same license unless stated otherwise.

---

> **“Identity must be boring, predictable, and unquestionable.”**
> — Reltroner Gateway Principle

