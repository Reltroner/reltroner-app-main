<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="360" alt="Laravel Logo">
</p>

<p align="center">
  <strong>Reltroner Gateway</strong><br>
  Central Authentication & Trust Gateway • Laravel • JWT Issuer
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Role-Auth%20Gateway-blue">
  <img src="https://img.shields.io/badge/Protocol-JWT-success">
  <img src="https://img.shields.io/badge/PHP-8.2+-8892BF">
  <img src="https://img.shields.io/badge/Laravel-12.x-red">
</p>

---

## 📌 Overview

**Reltroner Gateway** adalah **central authentication & trust authority** untuk seluruh ekosistem Reltroner.

Gateway ini **bukan aplikasi bisnis**, melainkan:
- issuer identitas
- pengelola sesi terpusat
- sumber kebenaran autentikasi lintas modul

Semua modul (Finance, HRM, ERP, dll) **mempercayai Gateway**, bukan sebaliknya.

---

## 🎯 Primary Responsibilities

- 🔐 **User Authentication**
- 🪪 **JWT Issuance & Signing**
- 🧭 **Single Sign-On (SSO) Authority**
- 🧱 **Trust Boundary Enforcement**
- 🔁 **Session & Token Lifecycle Control**

---

## 🧠 Design Philosophy

> **Gateway is a security boundary, not a feature playground.**

Prinsip utama:
- Stateless authentication (JWT)
- Explicit trust contracts
- No business logic
- No domain mutation
- Predictable behavior

---

## 🔗 Position in Reltroner Architecture

```text
[ User / Browser ]
        |
        v
[ Reltroner Gateway ]
        |
        v
[ Finance | HRM | ERP | Other Modules ]
