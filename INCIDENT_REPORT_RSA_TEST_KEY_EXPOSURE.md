# 📄 INCIDENT_REPORT_RSA_TEST_KEY_EXPOSURE.md

---

# 🔐 INCIDENT REPORT

## RSA Test Private Key Exposure

**Project:** Reltroner Gateway (`reltroner-app-main`)
**Date of Detection:** 2026-03-04
**Severity:** Low (Test Artifact Exposure)
**Status:** Resolved
**Architecture Impact:** None
**Production Impact:** None

---

# 1️⃣ Executive Summary

On March 3rd, 2026, GitGuardian detected an exposed RSA Private Key in the GitHub repository:

```
Secret type: RSA Private Key  
Repository: Reltroner/reltroner-app-main  
```

The exposed key was confirmed to be:

* A **test/debug RSA private key**
* Not used in production
* Not used for gateway signing
* Not used in JWT runtime
* Not used for HS256 secret
* Not used in Keycloak integration

Immediate remediation actions were taken:

* Key removed from repository
* Git history rewritten
* Force push performed
* Repository resynchronized
* System integrity validated
* Security posture reassessed

Incident lifecycle is now closed.

---

# 2️⃣ Root Cause

The RSA private key existed in:

```
crypto-debug/private.pem
tests/Fixtures/private.pem
```

These files were originally created for:

* OpenSSL debugging
* Deterministic JWT test validation

They were mistakenly committed to the repository and pushed to GitHub.

GitHub secret scanning + GitGuardian detected the exposed private key.

---

# 3️⃣ Scope Assessment

### Affected Files

```
crypto-debug/private.pem
crypto-debug/public.pem
tests/Fixtures/private.pem
```

### Not Affected

* Production signing keys
* HS256 secrets
* Keycloak client secrets
* JWT runtime verification
* Trust boundary
* Gateway architecture

---

# 4️⃣ Impact Assessment

| Area                | Impact    |
| ------------------- | --------- |
| Production Systems  | None      |
| Gateway Runtime     | None      |
| Token Issuance      | None      |
| Module Trust        | None      |
| User Authentication | None      |
| Data Exposure       | None      |
| System Integrity    | Preserved |

The exposed key was never used in any production path.

Severity classification: **Low**

---

# 5️⃣ Incident Timeline

### T0 — Detection

GitGuardian email alert received:

```
RSA Private Key exposed
Repository: Reltroner/reltroner-app-main
```

---

### T1 — Immediate Investigation

Verified:

* File location
* Whether key used in production
* Whether gateway signing depended on it

Conclusion:

> Test artifact only. No production dependency.

---

### T2 — Containment (Working Tree Cleanup)

Executed:

```bash
git rm --cached crypto-debug/private.pem
git rm --cached tests/Fixtures/private.pem
git rm -r --cached crypto-debug
git commit -m "security: remove exposed RSA test keys"
git push
```

Removed files from active repository state.

---

### T3 — History Rewrite (Critical Step)

Because the secret was previously pushed, removal from HEAD was insufficient.

Performed:

```bash
git clone --mirror https://github.com/Reltroner/reltroner-app-main.git
cd reltroner-app-main.git

git filter-repo --path crypto-debug --invert-paths
git filter-repo --path tests/Fixtures/private.pem --invert-paths
```

Then:

```bash
git remote add origin https://github.com/Reltroner/reltroner-app-main.git
git push origin --force --all
git push origin --force --tags
```

This permanently removed the private key from Git history.

---

### T4 — Repository Resynchronization

In working repository:

```bash
git fetch
git reset --hard origin/master
```

Ensured local state matched rewritten history.

---

### T5 — Verification

Executed:

```bash
git grep "BEGIN RSA PRIVATE KEY"
git log --all -- crypto-debug
```

Result: No occurrences found.

---

### T6 — System Integrity Validation

Executed full test suite:

```bash
php artisan test
```

Result:

```
16 passed (30 assertions)
```

Verified:

* SSO flow intact
* JWT verification intact
* Module token factory intact
* Clock skew handling intact
* Audience validation intact

Executed:

```bash
php artisan route:list
```

Confirmed routing contract unchanged.

---

### T7 — Hardening

Added preventive layers:

1. `.gitignore` rules:

   ```
   *.pem
   crypto-debug/
   tests/Fixtures/private.pem
   ```

2. Local `pre-commit` hook to block `.pem` files

3. Maintained GitHub secret scanning

---

# 6️⃣ Architecture Integrity Confirmation

Post-incident validation confirmed:

| Component                  | Status    |
| -------------------------- | --------- |
| Phase 2 — Auth Gateway     | Intact    |
| Phase 3 — Trust Model      | Intact    |
| Phase 4 — UI Stabilization | Intact    |
| JWT Determinism            | Preserved |
| Trust Boundary             | Unchanged |
| Security Model             | Hardened  |

No code refactor was required.

No crypto model changes were made.

No key rotation required (production keys unaffected).

---

# 7️⃣ Why Key Rotation Was Not Required

The exposed key:

* Was not deployed to production
* Was not used for runtime signing
* Was not referenced in configuration
* Was not used by any external service

Therefore:

> No operational cryptographic material was compromised.

---

# 8️⃣ Lessons Learned

1. Test artifacts must never be committed.
2. Debug folders must be excluded by default.
3. Secret scanning is effective and should remain enabled.
4. Defense-in-depth should include:

   * `.gitignore`
   * Pre-commit hooks
   * CI scanning
   * Documentation clarity

---

# 9️⃣ Security Posture After Incident

| Control                   | Status |
| ------------------------- | ------ |
| Secret removed            | ✅      |
| History purged            | ✅      |
| Repo synced               | ✅      |
| Tests green               | ✅      |
| Production unaffected     | ✅      |
| Preventive controls added | ✅      |

---

# 🔟 Final Classification

```
Incident Type: Test Private Key Exposure
Production Impact: None
Data Breach: No
Architecture Compromise: No
Status: Resolved
```

---

# 🧊 Closing Statement

The RSA private key exposure was:

* Detected quickly
* Assessed correctly
* Contained immediately
* Purged completely
* Validated thoroughly
* Hardened preventively

No runtime systems were compromised.

No architectural rollback occurred.

No trust boundary was weakened.

Incident officially closed.

