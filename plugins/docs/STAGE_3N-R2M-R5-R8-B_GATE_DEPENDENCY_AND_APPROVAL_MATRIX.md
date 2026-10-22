# STAGE 3N-R2M-R5-R8-B — GATE DEPENDENCY & APPROVAL MATRIX

**Project:** ATELIER — WordPress / WooCommerce Furniture E-commerce  
**Workspace:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\`  
**WordPress Root:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\`  
**Current Stage:** Stage 3N-R2M-R5-R8-B — Operational Planning  
**Current Authorization:** READ-ONLY STATIC INSPECTION AND DOCUMENTATION ONLY  
**Gate 1A Status:** **NOT AUTHORIZED**  
**Gate 4 Status:** **STRICTLY BLOCKED**  
**Matrix Status:** **MATRIX COMPLETE — 12 OPERATIONAL GATES INDEPENDENTLY BOUNDED**

---

## 1. Executive Governance & Invariant Rules

This matrix governs the operational lifecycle of the ATELIER platform import workflow. Coarse operational groupings are strictly eliminated. Every operational phase carries its own independent, non-fungible authorization gate.

### Core Governance Principles:
1. **Strict Sequential Non-Fungibility:** Passing any individual gate provides evidence for that specific phase only. No gate may automatically or implicitly authorize any subsequent phase.
2. **Explicit Human Authorization Invariant:** Every phase involving live database access, filesystem writes, storefront changes, or code execution requires explicit human sign-off prior to invocation.
3. **Fail-Closed Boundary:** If any acceptance criterion fails or any stop condition is triggered, execution halts immediately and the gate is marked BLOCKED.
4. **Gate 4 Invariant:** Approval Gate 4 (Live Ingestion) remains **STRICTLY BLOCKED**.

---

## 2. Master Gate Dependency & Approval Matrix

| Gate ID | Gate Name & Target Operation | Preconditions | Required Input Evidence | Operation Type | Required Separate Authorization? | Dependencies | Acceptance Criteria | Stop Conditions | Expected Output Artifact | Current Gate Status | Gate 4 Status |
|---|---|---|---|---|---|---|---|---|---|---|---|
| **Pre-Gate** | Static Verification & Inventory | Workspace paths confirmed | Importer, CSV, and test suite files present on disk | Read-Only Static | Yes (Granted in charter) | None | All file hashes match baselines 100%; zero missing code artifacts | Any hash mismatch or missing code file | `STAGE_3N-R2M-R5-R8-A_STATIC_EVIDENCE_INVENTORY.md` | **`PASS`** | Strictly Blocked |
| **Gate 0-GIT** | Stage 1F Git Baseline & Theme Archive | Pre-Gate PASS; clean diff review | `themes/twentytwentyfive-child/` assets | State-Changing Filesystem / Git | **Yes (Explicit Owner Approval Required)** | Pre-Gate | Diff reviewed; clean commit; tag `stage-1f-verified-baseline` created; zip archive in `backups/filesystem/` | Destructive Git commands (`clean`, `reset`), unstaged secrets, or modified core files | Git tag & `backups/filesystem/stage_1f_theme_backup.zip` | **`NOT AUTHORIZED`** | Strictly Blocked |
| **Gate 1A** | Read-Only DB Identity Verification | Gate 0-GIT completed; MySQL running | MySQL port 10011 credentials | Read-Only Runtime | **Yes (Explicit DBA Approval Required)** | Gate 0-GIT | `DATABASE() === 'local'`, `port === 10011`, local datadir confirmed | Target DB != `local`, external host detected, or connection failure | `docs/STAGE_3N_GATE1A_DB_IDENTITY_VERIFICATION_LOG.md` | **`NOT AUTHORIZED`** | Strictly Blocked |
| **Gate 1B** | Fresh T-0 Baseline Snapshot Capture | Gate 1A PASS; disk space > 2 GB | Verified DB connection; `mysqldump` binary | State-Changing Runtime (Filesystem) | **Yes (Explicit DBA Approval Required)** | Gate 1A | Exit code 0; size > 1.2 MB; zero `USE` or `CREATE DATABASE` statements; Stage 1F template posts verified | Exit code != 0, size < 1.2 MB, presence of `USE` statements, or missing templates | `backups/stage_3n_t0_snapshot.sql` & verification log | **`NOT AUTHORIZED`** | Strictly Blocked |
| **Gate 1C** | Isolated Restore Rehearsal | Gate 1B verified; auxiliary DB available | Verified `stage_3n_t0_snapshot.sql` dump file | State-Changing Runtime (Auxiliary DB Only) | **Yes (Explicit QA Lead & DBA Approval Required)** | Gate 1B | Auxiliary DB `local_isolated_rehearsal` restored cleanly; 52 tables; Stage 1F templates verified; auxiliary DB dropped | Table count != 52, missing Stage 1F templates, SQL error, or any query touching `local` | `docs/STAGE_3N_GATE1C_RESTORE_REHEARSAL_REPORT.md` | **`NOT AUTHORIZED`** | Strictly Blocked |
| **Gate 2A** | Storefront Freeze & Worker Control | Gate 1C PASS (Restore capability proven) | Administrative DB access to `local` | State-Changing Runtime (Database) | **Yes (Explicit Operations Approval Required)** | Gate 1C | `woocommerce_coming_soon` set to `'yes'`; WordPress cron paused | Update fails or storefront remains accessible to unauthenticated users | `docs/STAGE_3N_GATE2A_FREEZE_EXECUTION_LOG.md` | **`NOT AUTHORIZED`** | Strictly Blocked |
| **Gate 2B** | HTTP Storefront-Lock Verification | Gate 2A completed | Local web server running on port 10010 | Read-Only Runtime (Network) | **Yes (Explicit QA Approval Required)** | Gate 2A | cURL probes to `/` and `/shop/` return HTTP 200 with Coming Soon banner and zero catalog leakage | Public page renders products, prices, or add-to-cart buttons | `docs/STAGE_3N_GATE2B_HTTP_LOCK_PROBE_REPORT.md` | **`NOT AUTHORIZED`** | Strictly Blocked |
| **Gate 2C** | Live Read-Only SQL Preflight Suite | Gate 2B PASS | Target DB `local` under storefront lock | Read-Only Runtime (Database) | **Yes (Explicit Tech Lead Approval Required)** | Gate 2B | All 11 queries pass: 52 tables, siteurl matches, 0 products, 0 variations, 0 tagged postmeta, lookup tables empty | Any query returns unexpected data, existing products, or SKU conflicts | `docs/STAGE_3N_GATE2C_SQL_PREFLIGHT_RESULTS.md` | **`NOT AUTHORIZED`** | Strictly Blocked |
| **Gate 2D** | WordPress CLI Verify Execution (Conditional) | Gate 2C PASS; decision to run CLI verify | PHP CLI runtime; importer script | Runtime Execution (PHP / WP Bootstrap) | **Yes (Explicit Tech Lead & Owner Approval Required)** | Gate 2C | CLI exits with code 0; outputs `[SUCCESS] Preflight verification passed!`; memory limit set to 512M | Nonzero exit code, unhandled PHP exception, or unmonitored DB writes | `docs/STAGE_3N_GATE2D_VERIFY_EXECUTION_LOG.md` | **`NOT AUTHORIZED`** | Strictly Blocked |
| **Gate 3** | Ingestion Authorization Review | All prior gates (1A through 2D) PASSED | Evidence dossier from Gates 1A through 2D | Administrative / Human Review | **Yes (Unanimous Owner & Auditor Sign-Off Required)** | Gates 1A, 1B, 1C, 2A, 2B, 2C, 2D | Unanimous stakeholder approval; token `--gate4-user-approved-execute` generated | Any open blocker, missing proof, or unapproved recovery plan | `docs/STAGE_3N_GATE3_IMPORT_AUTHORIZATION_DOSSIER.md` | **`NOT AUTHORIZED`** | Strictly Blocked |
| **Gate 4** | Controlled Import Execution | Gate 3 Token issued; live monitoring active | Importer harness; locked working CSV | State-Changing Runtime (Production Database) | **Yes (Explicit Execution Operator Authorization Required)** | Gate 3 | 22 parents, 42 variations, attachments created; stock = 449; manifest written and marked `COMPLETED` | Any PHP fatal error, DB timeout, or exception. Halt immediately -> Trigger Gate 4R | `backups/manifests/manifest_run_*.json` | **`STRICTLY BLOCKED`** | **`STRICTLY BLOCKED`** |
| **Gate 4R** | Recovery & Disaster Triage | Failure in Gate 4 or Gate 5 acceptance | Failed manifest, crash dump, T-0 snapshot | State-Changing Runtime (Recovery Mutation) | **Yes (Explicit Owner & DBA Approval Required)** | Gate 4 Failure | Selected pathway executed cleanly: 4R-SW (Software Rollback), 4R-DR (T-0 Restore), or 4R-DBA (Manual SQL) | Database remains in corrupted or untagged orphan state | `docs/STAGE_3N_GATE4R_RECOVERY_EXECUTION_LOG.md` | **`STANDBY / EMERGENCY ONLY`** | Strictly Blocked |
| **Gate 5** | Post-Import Acceptance & Storefront Thaw | Gate 4 completed successfully | Live DB queries; manifest status | State-Changing Runtime (Public Release) | **Yes (Explicit Owner & QA Lead Approval Required)** | Gate 4 Success | Acceptance queries verify 64 products, 449 stock, 64 SKUs; Coming Soon set to `'no'`; cron resumed; shop smoke test PASS | Any acceptance metric mismatch -> Trigger Gate 4R Rollback immediately | `docs/STAGE_3N_GATE5_POST_IMPORT_ACCEPTANCE_REPORT.md` | **`NOT AUTHORIZED`** | Strictly Blocked |

---

## 3. Open-Item Register to Gate Traceability

Every open item from the Stage 3N-R2M-R5-R8-A Open-Item Register is mapped to its gating authority and prerequisite dependencies:

| Open Item ID | Item Description | Priority | Mapped Gate | Prerequisite For Gate Execution | Resolution Condition |
|---|---|---|---|---|---|
| **`R8A-01`** | Live DB Identity & Port Binding | `P0` | **Gate 1A** | Gate 0-GIT completed | Query `SELECT DATABASE()` returns strictly `local` on port 10011. |
| **`R8A-02`** | Missing Post-Stage-1F T-0 Dump | `P0` | **Gate 1B** | Gate 1A PASSED | `backups/stage_3n_t0_snapshot.sql` created and verified > 1.2 MB. |
| **`R8A-03`** | T-0 Isolated Restore Rehearsal | `P0` | **Gate 1C** | Gate 1B PASSED | Successful restore of T-0 dump into auxiliary DB with 52 tables. |
| **`R8A-04`** | Live DB Baseline Product Counts | `P0` | **Gate 2C** | Gate 2B PASSED | Direct SQL confirms 0 products, 0 variations, and 0 tagged postmeta. |
| **`R8A-05`** | HTTP Coming Soon Lock Probe | `P1` | **Gate 2B** | Gate 2A PASSED | cURL probe proves unauthenticated access renders Coming Soon banner. |
| **`R8A-06`** | Background Cron/Worker Freeze | `P1` | **Gate 2A** | Gate 1C PASSED | WordPress cron paused and Action Scheduler workers suspended. |
| **`R8A-07`** | Manifest Directory Write Perms | `P1` | **Gate 2C / 4** | Gate 2A PASSED | Directory `backups/manifests/` created with 0755 permissions and verified writable. |
| **`R8A-08`** | MySQL User Schema Isolation | `P1` | **Gate 1A** | Gate 0-GIT completed | User privileges verified restricted to `local` schema. |
| **`R8A-09`** | Importer verify vs Direct SQL | `P1` | **Gate 2D** | Gate 2C PASSED | Human decision approves Gate 2C as primary gating authority. |
| **`R8A-10`** | Window 2 Recovery Protocol | `P0` | **Gate 4R** | Gate 1C PASSED | Formal recovery protocol designating T-0 snapshot restore as primary approved. |
| **`R8A-11`** | Importer / CSV Hash Integrity | `P0` | **Pre-Gate** | None | Physical file hashes verified matching approved baseline digests (CLOSED). |
| **`R8A-12`** | Gate 4 Acceptance & Auth Dossier | `P0` | **Gate 3** | Gates 1A–2D PASSED | Evidence dossier assembled with unanimous stakeholder signatures. |
| **`R8A-13`** | Stage 1F Git Baseline & Archive | `P0` | **Gate 0-GIT** | Pre-Gate PASSED | Theme assets committed, tagged, and archived to filesystem. |
| **`R8A-14`** | Dump Restore Safety Inspection | `P0` | **Gate 1B / 1C** | Gate 1B completed | Static inspection proves zero `USE` or `CREATE DATABASE` statements in dump. |

---

## 4. Non-Fungibility & Separate Authorization Declarations

1. **Gate 1A Execution Precondition:** Gate 1A cannot be authorized until Stage 1F assets are preserved under Gate 0-GIT, ensuring that if any database connection issue requires environment inspection, theme assets cannot be lost.
2. **Gate 1B Snapshot Precondition:** Gate 1B cannot be authorized until Gate 1A proves the database identity is strictly `local` on port 10011. Creating a snapshot against an unverified database is strictly prohibited.
3. **Gate 1C Rehearsal Precondition:** Gate 1C cannot be authorized until Gate 1B produces a dump that passes static inspection for destructive redirection statements.
4. **Gate 2A Freeze Precondition:** Gate 2A cannot be authorized until Gate 1C proves the T-0 snapshot is 100% restorable. Public storefronts must never be disrupted for a backup that cannot be restored.
5. **Gate 4 Invariant:** Gate 4 cannot be unlocked by any technical operator. It strictly requires the human stakeholder token generated at Gate 3.

---

## 5. Mandatory Operational Sign-Off Invariant

**ALL OPERATIONAL ACTIVITY REQUIRES SEPARATE, SEQUENTIAL, PRIOR AUTHORIZATION.**  
**NO GATE MAY BE MERGED, ASSUMED, OR BYPASSED.**  
**GATE 1A IS NOT AUTHORIZED BY THIS DOCUMENT.**  
**GATE 4 REMAINS STRICTLY BLOCKED.**
