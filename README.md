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

Copy `.env.example` to `.env` and configure the following **Keycloak variables**:

```env
KEYCLOAK_BASE_URL=
KEYCLOAK_REALM=
KEYCLOAK_CLIENT_ID=
KEYCLOAK_CLIENT_SECRET=
KEYCLOAK_REDIRECT_URI=
KEYCLOAK_LOGOUT_URL=
```

Additional recommended gateway variables:

```env
APP_URL=http://app.reltroner.test
JWT_TTL=3600
JWT_ISSUER=reltroner-gateway
```

---

## 🧪 Testing

Run the full test suite with:

```bash
composer test
```

or:

```bash
php artisan test
```

Tests focus on:

* Authentication flow
* Token issuance
* Invalid token rejection
* Gateway-only responsibilities

---

## 🔗 Integration with Other Modules

Downstream modules must configure:

```env
RELTRONER_GATEWAY_ISSUER=http://app.reltroner.test
RELTRONER_GATEWAY_AUDIENCE=finance.reltroner.test
RELTRONER_MODULE_SIGNING_KEY=shared-secret
```

Gateway **never depends on module state**.
Modules **must trust the gateway**, not the other way around.

---

## 🗂️ Typical Consumers

* Finance Module
* HRM Module
* ERP Dashboard
* Admin Console
* Future Reltroner services

---

## ⚠️ Design Constraints

* ❌ No business logic
* ❌ No accounting logic
* ❌ No cross-module mutation
* ✅ Authentication & trust only

This repository **must remain lean and security-focused**.

---

## 🚀 Roadmap

| Phase | Scope                      | Status  |
| ----- | -------------------------- | ------- |
| 1     | SSO via Keycloak           | ✅ done  |
| 2     | JWT Trust Model            | ✅ done  |
| 3     | Multi-module Audience      | ✅ done  |
| 4     | Token Hardening & Rotation | planned |

---

## 🤝 Contribution Rules

* Do not add domain-specific logic
* Do not weaken token validation
* Security reviews required for all changes

---

## 📄 License

This project is built on top of the **Laravel Framework**.

Laravel is open-sourced software licensed under the **MIT License**.
Reltroner Gateway follows the same license unless stated otherwise.

---

> **“Identity must be boring, predictable, and unquestionable.”**
> — Reltroner Gateway Principle

