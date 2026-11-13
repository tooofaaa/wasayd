# WASAYD Project Constitution

> Generated from `/speckit.constitution` — Plan reference: Step 1
> Plan file: `/tmp/opencode/WASAYD-SPECKIT-IMPLEMENTATION-PLAN.md`

## Project

WASAYD is a Medical Inventory & RFID SaaS platform for healthcare organizations
(hospitals, EMS services, pharmacies, medical warehouses). Every module is designed
around the operational, compliance, and governance requirements of medical supply
chains.

## Non-Negotiable Principles

### 1. IMMUTABILITY-FIRST

Every inventory or asset state change requires a transaction record.
No direct stock edits. No historical deletions. Corrections are made through
reversal transactions, never edits.

**Enforced by:** `REVOKE UPDATE, DELETE ON inventory_transactions FROM wasayd_app` +
Laravel `StockLedgerService` that only exposes `post()`, never `update()`.

### 2. DUAL-TRUST

Controlled medication operations require two separate authenticated users:
Initiator + Witness, different users, different roles. Enforced at both
application and database layers.

**Enforced by:** `CHECK (authorized_by_1 <> authorized_by_2)` on
`controlled_medication_logs` table + `DualAuthService::validate()`.

### 3. TENANT-ISOLATION

Every business table carries `company_id`. PostgreSQL Row-Level Security enforces
`company_id = current_setting('app.current_company_id')`. No query may return
data across tenant boundaries.

**Enforced by:** Global Eloquent scope `BelongsToCompany` on all business models +
`SET app.current_company_id = ?` at session start.

### 4. EVIDENCE-BEFORE-ASSERTION

No feature is complete without a passing lean loop test gate. Tests are written
before or during implementation, never after. The gate must turn green before
the next feature starts.

**Lean Loop rule:** `Fail gate → fix → re-converge → re-test`. No skipping.

### 5. GS1-COMPLIANCE

Every EPC follows GS1 SGTIN-96 encoding. Every barcode is validated against
GS1 rules (GTIN check digit, AI parsing). No custom formats.

**Enforced by:** `EpcGeneratorService::generateSGTIN96()` + `BarcodeService::validateGTIN()`.

### 6. FEFO-DEFAULT

First-Expired-First-Out is the default stock rotation for all expiry-controlled
items. FEFO bypass requires authorized permission + reason code + audit record.

**Enforced by:** `FefoService::allocate()` + `FefoViolationException` on unauthorized bypass.

### 7. ARABIC-FIRST

Every user-facing string has both an English and Arabic translation. RTL layout
is the design baseline; LTR is the variant. All reports export in both languages.

**Enforced by:** `lang/en/*.php` + `lang/ar/*.php` parity. `dir="rtl"` on `<html>`
when `locale === 'ar'`.

### 8. FAIL-LOUD

Integration failures, RFID reader errors, and sync conflicts create explicit
exception records. Silent failures are defects.

**Enforced by:** `IntegrationException` model created on every failed external call.
No `try/catch` without logging.

### 9. ZERO-DRIFT

`.specify/` artifacts are the source of truth. Code must match the spec.
When they diverge, the spec wins — fix the code, not the spec.

**Process:** Run `/speckit.converge` after every feature. If it reports gaps,
run `/speckit.implement` again.

### 10. HEALTHCARE-GRADE

Audit logs are tamper-evident (SHA-256 hash chain). RBAC has 30+ granular
permissions. Controlled medication flows are completely separate from standard
inventory flows.

**Enforced by:** `audit_logs.row_hash` chain + `REVOKE UPDATE, DELETE ON audit_logs`.

---

## Technology Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 11 (upgrading to 13), PHP 8.4 |
| Frontend | Vue 3 + Inertia.js v2 (upgrading to v3) |
| CSS | Tailwind CSS v4 |
| DB | PostgreSQL 16 |
| Cache/Queue | Redis 7 |
| Auth | Laravel Sanctum |
| RFID Bridge | Node.js 20 + ws |
| Mobile | React Native + Expo |
| Testing | PHPUnit 11 + Pest + Playwright |

## Implementation Plan Reference

All implementation follows the step-by-step plan in:
`/tmp/opencode/WASAYD-SPECKIT-IMPLEMENTATION-PLAN.md`

Phases:
- Phase 1: Foundation (Features 1.1–1.6)
- Phase 2: Item Master + Inventory (Features 2.1–2.8)
- Phase 3: RFID/EPC (Features 3.1–3.6)
- Phase 4: Transactions + Approvals + Audit (Features 4.1–4.6)
- Phase 5: Assets + Healthcare Compliance (Features 5.1–5.6)
- Phase 6: Mobile + Reports + API (Features 6.1–6.9)

**Current phase:** Phase 1 — Foundation
