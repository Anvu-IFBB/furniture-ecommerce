# STAGE 3N-R2M-R5-R8-A — OPEN-ITEM REGISTER & OPERATIONAL PREREQUISITE MATRIX

**Project:** ATELIER — WordPress / WooCommerce Furniture E-commerce  
**Workspace:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\`  
**WordPress Root:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public`  
**Mode:** STRICTLY READ-ONLY STATIC INSPECTION AND DOCUMENTATION  
**Current Authorization:** DOCUMENTATION ONLY  
**Gate 4 Status:** **STRICTLY BLOCKED**  
**Register Status:** **REGISTER COMPLETE — 14 ITEMS TRACKED (6 LOCKED POLICIES / 1 RESOLVED / 7 OPEN RUNTIME)**

---

## 1. Executive Summary & Governance Model

This document establishes the authoritative, prioritized open-item register for the ATELIER platform following the static evidence inventory in Stage 3N-R2M-R5-R8-A. Every prerequisite for future operational gates is tracked with an assigned unique identifier, priority level, dependency tree, safe acquisition method, explicit stop condition, approving role, and target artifact.

### Core Governance Principles:
1. **No Phantom Closures:** No open item requiring live database, HTTP, or execution evidence is marked closed based on static code inspection or historical claims.
2. **Prioritization Standards:**
   - **`P0` (Critical Safety Blocker):** Must be resolved before any operational database write, snapshot restore, or import action can be considered.
   - **`P1` (Operational Preflight Requirement):** Must be verified before issuing the Gate 3 execution authorization token.
   - **`P2` (Process & Archival Requirement):** Required for post-import acceptance, storefront thaw, and final documentation lock.
3. **Owner Decision Invariants:** The six foundational owner decisions established in Section 2 are permanently recorded as `OWNER_DECISION_LOCKED` and cannot be reopened.

---

## 2. Locked Project Owner Decisions

The following six project decisions have been confirmed by the project owner and serve as binding policy constraints for all future phases:

| Decision ID | Title | Policy Definition | Governance Status |
|---|---|---|---|
| **A2** | **Import Authorization Invariant** | Live product import is strictly unauthorized. Approval Gate 4 remains locked until all prerequisite gates (1A through 3) achieve explicit, verified sign-off. | **`OWNER_DECISION_LOCKED`** |
| **A3** | **Separate Authorization Invariant** | Every technical operation capable of modifying the database, storefront visibility, background cron/workers, configuration, or filesystem requires its own separate, prior authorization. | **`OWNER_DECISION_LOCKED`** |
| **A4** | **Evidence Integrity Invariant** | Static evidence must never be represented as runtime evidence. A PASS verdict requires direct, verifiable evidence appropriate to the specific acceptance criterion. | **`OWNER_DECISION_LOCKED`** |
| **B4** | **Stage 1F Preservation Invariant** | All valid Stage 1F changes (including custom FSE block templates in `wp_posts` and on disk) must be preserved. Restoring historical pre-Stage-1F snapshots is strictly forbidden. | **`OWNER_DECISION_LOCKED`** |
| **C5** | **Window 2 Recovery Invariant** | Never delete an untagged post based on guessed SKUs, timestamps, or heuristic scanning. Untagged post recovery requires an explicitly approved disaster recovery or surgical DBA strategy. | **`OWNER_DECISION_LOCKED`** |
| **D4** | **Gate 4 Prerequisite Sequence** | Gate 4 may only be considered after prerequisite evidence, a fresh T-0 snapshot, an isolated restore rehearsal, read-only preflight checks, and recovery planning are accepted. | **`OWNER_DECISION_LOCKED`** |

---

## 3. Prioritized Open-Item Register

```
+----------------------------------------------------------------------------------------------------+
|                                    OPEN-ITEM REGISTER LIFECYCLE                                    |
+----------------------------------------------------------------------------------------------------+
| P0 Items (Blockers to Gate 1 & Gate 4):                                                            |
|  - R8A-01: Live Database Identity & Port Binding (Gate 1A)                                         |
|  - R8A-02: Missing Post-Stage-1F T-0 Baseline Snapshot (Gate 1B)                                    |
|  - R8A-03: Isolated Restore Rehearsal in Auxiliary DB (Gate 1C)                                    |
|  - R8A-04: Live Database Product Baseline & Lookup Tables (Gate 2C)                                 |
|  - R8A-10: Window 2 Crash Recovery & Disaster Plan (Gate 4R)                                       |
|  - R8A-11: Static Hashes Reconciliation (RESOLVED STATICALLY)                                      |
|  - R8A-12: Gate 4 Acceptance & Human Authorization Assembly (Gate 3)                               |
|  - R8A-13: Stage 1F Asset Inventory & Git Tracking Baseline (Gate 1B / Gate 5)                     |
|  - R8A-14: Dump Restore Safety & Redirection Prevention (Gate 1B / Gate 1C)                        |
|                                                                                                    |
| P1 Items (Operational Controls & Preflight):                                                       |
|  - R8A-05: HTTP Storefront Coming Soon Interception (Gate 2B)                                      |
|  - R8A-06: Cron & Action Scheduler Worker Suspension (Gate 2A)                                     |
|  - R8A-07: Manifest Directory Runtime Permissions (Gate 2C / Gate 4)                               |
|  - R8A-08: Database User Grants & Schema Isolation (Gate 1A)                                       |
|  - R8A-09: Importer CLI verify Bootstrap Side Effects (Gate 2D)                                    |
+----------------------------------------------------------------------------------------------------+
```

---

### Item R8A-01: Live Database Identity & Port Binding Verification
- **Title:** Verification of Active Database Name, Host, Port, and Data Directory
- **Priority:** `P0`
- **Type:** `TECHNICAL_EVIDENCE`
- **Current Status:** **OPEN — RUNTIME VERIFICATION REQUIRED**
- **What is Known:** `wp-config.php` defines `DB_NAME = 'local'`, `DB_HOST = 'localhost'`, and prefix `wp_`. Importer constants expect `local` and `wp_`. LocalWP environment historically binds MySQL on port 10011.
- **What Remains Unknown:** Whether the currently running MySQL process on port 10011 is active, accessible, bound to database `local`, and hosted locally.
- **Required Evidence:** Output of `SELECT DATABASE(), USER(), @@hostname, @@port, @@datadir;` executed against port 10011.
- **Safe Acquisition Method:** Execute read-only SQL session in Phase P-02 under Gate 1A.
- **Method Classification:** `RUNTIME`
- **Dependencies:** Separate Gate 1A authorization.
- **Explicit Stop Condition:** `DATABASE() !== 'local'` or host/port connects to an external, staging, or production instance.
- **Approving Authority:** Database Administrator (DBA).
- **Gate Affected:** Gate 1A.
- **Expected Artifact:** `docs/STAGE_3N_GATE1A_DB_IDENTITY_VERIFICATION_LOG.md`.

---

### Item R8A-02: Post-Stage-1F T-0 Baseline Snapshot Creation
- **Title:** Missing Authoritative Pre-Import Snapshot Capturing Stage 1F Block Templates
- **Priority:** `P0`
- **Type:** `TECHNICAL_EVIDENCE`
- **Current Status:** **OPEN — ARTIFACT MISSING ON DISK**
- **What is Known:** `backups/stage_3n_t0_snapshot.sql` does not exist. Existing snapshots (`stage_1e_gate2`, `stage_1a`, `stage_1e_pre_import`) predate Stage 1F and contain zero `wp_template` block template posts. Restoring an existing snapshot destroys the storefront.
- **What Remains Unknown:** Cryptographic digest, byte size, and table structure of the fresh T-0 snapshot.
- **Required Evidence:** Physical creation of `backups/stage_3n_t0_snapshot.sql` (> 1.2 MB) containing all 52 tables and Stage 1F templates.
- **Safe Acquisition Method:** Run `mysqldump` with `--single-transaction --quick --hex-blob --no-tablespaces` targeting port 10011 under Gate 1B.
- **Method Classification:** `RUNTIME`
- **Dependencies:** Gate 1A approval.
- **Explicit Stop Condition:** `mysqldump` exits with nonzero code, size < 1.2 MB, or disk space < 1 GB free.
- **Approving Authority:** Database Administrator (DBA).
- **Gate Affected:** Gate 1B.
- **Expected Artifact:** `backups/stage_3n_t0_snapshot.sql` and `docs/STAGE_3N_GATE1B_SNAPSHOT_VERIFICATION_REPORT.md`.

---

### Item R8A-03: Isolated Restore Rehearsal in Auxiliary Database
- **Title:** Proof of Snapshot Restorability and Schema Fidelity in Isolated Database
- **Priority:** `P0`
- **Type:** `TECHNICAL_EVIDENCE`
- **Current Status:** **OPEN — RUNTIME REHEARSAL REQUIRED**
- **What is Known:** Historical rehearsal proved `stage_1e_gate2` was restorable in Gate 3I, but the newly required post-Stage-1F T-0 dump has never been captured or restored.
- **What Remains Unknown:** Whether `stage_3n_t0_snapshot.sql` restores cleanly without syntax errors or table truncation.
- **Required Evidence:** Clean restoration into `local_isolated_rehearsal`, verification of exactly 52 tables, verification of Stage 1F template posts, and clean drop of rehearsal DB.
- **Safe Acquisition Method:** Execute `mysql local_isolated_rehearsal < backups/stage_3n_t0_snapshot.sql` under Gate 1C.
- **Method Classification:** `RUNTIME`
- **Dependencies:** Gate 1B approval and verified dump digest.
- **Explicit Stop Condition:** Table count != 52, missing Stage 1F templates, SQL execution error, or any query touching `local`.
- **Approving Authority:** QA Lead & Database Administrator (DBA).
- **Gate Affected:** Gate 1C.
- **Expected Artifact:** `docs/STAGE_3N_GATE1C_RESTORE_REHEARSAL_REPORT.md`.

---

### Item R8A-04: Live Database Baseline Row Counts & Schema Cleanliness
- **Title:** Proof of Clean Baseline (Zero Products, Zero Variations, Empty Lookups)
- **Priority:** `P0`
- **Type:** `TECHNICAL_EVIDENCE`
- **Current Status:** **OPEN — RUNTIME VERIFICATION REQUIRED**
- **What is Known:** The dataset requires importing 22 parents and 42 variations. Static dumps confirm pre-existing baseline was 0 products.
- **What Remains Unknown:** Current row counts in live `wp_posts`, `wp_postmeta`, and lookup tables.
- **Required Evidence:** Execution of the 11 read-only preflight queries showing `post_type IN ('product', 'product_variation')` returns 0 rows, `_controlled_import_run_id` returns 0 rows, and lookup tables have 0 rows.
- **Safe Acquisition Method:** Execute read-only SQL preflight suite under Gate 2C.
- **Method Classification:** `RUNTIME`
- **Dependencies:** Gate 2B storefront freeze verification.
- **Explicit Stop Condition:** Any pre-existing product, variation, or ownership tag found in database.
- **Approving Authority:** Technical Lead.
- **Gate Affected:** Gate 2C.
- **Expected Artifact:** `docs/STAGE_3N_GATE2C_SQL_PREFLIGHT_RESULTS.md`.

---

### Item R8A-05: HTTP Storefront Coming Soon Interception Verification
- **Title:** Automated HTTP Probe Confirming Block Theme Intercepts Public Traffic
- **Priority:** `P1`
- **Type:** `TECHNICAL_EVIDENCE`
- **Current Status:** **OPEN — RUNTIME VERIFICATION REQUIRED**
- **What is Known:** `woocommerce_coming_soon` exists as an option in `wp_options`.
- **What Remains Unknown:** Whether unauthenticated HTTP GET requests to `/` and `/shop/` on the active FSE block theme actually return the Coming Soon template and leak zero catalog data.
- **Required Evidence:** Automated cURL probe returning HTTP 200, Coming Soon banner text, and zero catalog entities.
- **Safe Acquisition Method:** Dispatch unauthenticated cURL GET requests with cookies disabled under Gate 2B.
- **Method Classification:** `RUNTIME`
- **Dependencies:** Gate 2A (Coming Soon set to `'yes'`).
- **Explicit Stop Condition:** Public shop or homepage renders active catalog products or add-to-cart buttons.
- **Approving Authority:** QA Engineer.
- **Gate Affected:** Gate 2B.
- **Expected Artifact:** `docs/STAGE_3N_GATE2B_HTTP_LOCK_PROBE_REPORT.md`.

---

### Item R8A-06: Cron & Action Scheduler Worker Suspension Plan
- **Title:** Elimination of Concurrent Background Database Mutations During Import
- **Priority:** `P1`
- **Type:** `TECHNICAL_EVIDENCE` / `POLICY`
- **Current Status:** **OPEN — RUNTIME VERIFICATION REQUIRED**
- **What is Known:** `wp-config.php` does not currently define `DISABLE_WP_CRON`. While the importer sets `DOING_CRON = true` during CLI runs, public web requests or Action Scheduler runners could fire during import if Coming Soon is bypassed.
- **What Remains Unknown:** Current queue depth of `wp_actionscheduler_actions` and active scheduled tasks in `wp_options` `cron`.
- **Required Evidence:** Verification that `cron` option is temporarily cleared or disabled, and Action Scheduler workers are paused.
- **Safe Acquisition Method:** Query `wp_options` and update `cron` value under Gate 2A.
- **Method Classification:** `RUNTIME`
- **Dependencies:** Gate 1C approval.
- **Explicit Stop Condition:** Background workers trigger unlogged database writes during preflight.
- **Approving Authority:** Operations Engineer.
- **Gate Affected:** Gate 2A.
- **Expected Artifact:** `docs/STAGE_3N_GATE2A_FREEZE_EXECUTION_LOG.md`.

---

### Item R8A-07: Manifest Directory Permissions & Write Behavior
- **Title:** Verification of `backups/manifests/` Creation and Filesystem Write Capabilities
- **Priority:** `P1`
- **Type:** `TECHNICAL_EVIDENCE`
- **Current Status:** **OPEN — DISK DIRECTORY CURRENTLY MISSING**
- **What is Known:** `Controlled_Import_Path_Resolver` defines manifest root at `<project_root>/backups/manifests`. The directory does not exist on disk today.
- **What Remains Unknown:** Whether the executing PHP process has filesystem write permissions to create `backups/manifests/` and write `.json` manifest files.
- **Required Evidence:** Directory created with `0755` permissions and validated as writable.
- **Safe Acquisition Method:** Verified during Gate 2C filesystem check or initialized under explicit Gate 2 authorization.
- **Method Classification:** `RUNTIME`
- **Dependencies:** Gate 2A.
- **Explicit Stop Condition:** Directory creation fails or filesystem is read-only.
- **Approving Authority:** Technical Lead.
- **Gate Affected:** Gate 2C / Gate 4.
- **Expected Artifact:** Manifest directory present on disk with write verification log.

---

### Item R8A-08: Database User Permissions & Schema Isolation Restrictions
- **Title:** Verification that Database Connection is Restricted to Target Schema
- **Priority:** `P1`
- **Type:** `TECHNICAL_EVIDENCE`
- **Current Status:** **OPEN — RUNTIME VERIFICATION REQUIRED**
- **What is Known:** LocalWP runs as MySQL `root`. Importer lines 219–227 check `SELECT DATABASE() === 'local'`.
- **What Remains Unknown:** Whether the user connection can inadvertently mutate system tables (`mysql`, `information_schema`) or external databases.
- **Required Evidence:** Query `SHOW GRANTS FOR CURRENT_USER();` confirming required schema privileges on `local`.
- **Safe Acquisition Method:** Execute read-only privilege check query in Gate 1A.
- **Method Classification:** `RUNTIME`
- **Dependencies:** Gate 1A authorization.
- **Explicit Stop Condition:** User grants permit modifying external non-local production schemas.
- **Approving Authority:** Database Administrator (DBA).
- **Gate Affected:** Gate 1A.
- **Expected Artifact:** Recorded in `docs/STAGE_3N_GATE1A_DB_IDENTITY_VERIFICATION_LOG.md`.

---

### Item R8A-09: Importer CLI `verify` Bootstrap Side Effects & Direct SQL Decision
- **Title:** Architectural Decision on Direct Read-Only SQL vs WordPress Bootstrap Preflight
- **Priority:** `P1`
- **Type:** `MIXED` (Technical Evaluation & Owner Decision)
- **Current Status:** **RESOLVED STATICALLY (Review) / DECISION PENDING (Execution)**
- **What is Known:** `run_controlled_import.php verify` loads `wp-load.php`, which executes active plugins (WooCommerce, Action Scheduler) and sets memory limits. Gate 2C direct SQL queries can perform all database preflight checks without loading WordPress.
- **What Remains Unknown:** Whether human stakeholders will mandate running `verify` CLI (Gate 2D) or rely on direct read-only SQL (Gate 2C).
- **Required Evidence:** Stakeholder decision approving Gate 2C as primary gating authority, with Gate 2D executed conditionally.
- **Safe Acquisition Method:** Static review complete; human decision to be recorded at Gate 2 sign-off.
- **Method Classification:** `STATIC` (Review) / `OWNER_DECISION` (Execution)
- **Dependencies:** Gate 2C results.
- **Explicit Stop Condition:** Bootstrap triggers unmonitored database writes or unexpected plugin errors.
- **Approving Authority:** Project Owner & Technical Lead.
- **Gate Affected:** Gate 2D.
- **Expected Artifact:** Documented approval in Gate 2 sign-off report.

---

### Item R8A-10: Window 2 Crash Boundary & Untagged Orphan Post Recovery Strategy
- **Title:** Formally Approved Recovery Protocol for Crashes Between Post Creation and Tagging
- **Priority:** `P0`
- **Type:** `TECHNICAL_EVIDENCE` / `POLICY`
- **Current Status:** **POLICY LOCKED / OPERATIONAL PROTOCOL OPEN**
- **What is Known:** In Window 2 (crash after `wp_insert_post()` but before `update_post_meta('_controlled_import_run_id')`), the post exists untagged. Software rollback fail-closes and refuses heuristic deletion. Policy C5 locks this prohibition.
- **What Remains Unknown:** Whether the operator protocol designates Gate 4R-DR (T-0 Snapshot Restore) as primary recovery and Gate 4R-DBA (Manual SQL) as secondary.
- **Required Evidence:** Formal recovery protocol approved in Gate 3 prior to issuing Gate 4 token.
- **Safe Acquisition Method:** Documented protocol review in Stage 3N-R2M-R5-R7.
- **Method Classification:** `STATIC`
- **Dependencies:** Gate 1C rehearsal success.
- **Explicit Stop Condition:** Live import executed without an approved Gate 4R disaster recovery plan.
- **Approving Authority:** Project Owner & DBA.
- **Gate Affected:** Gate 4R.
- **Expected Artifact:** `docs/STAGE_3N_GATE4R_RECOVERY_PROTOCOL.md`.

---

### Item R8A-11: Static Hashes Reconciliation for Importer, Tests, and Working CSV
- **Title:** Cryptographic Baseline Verification of Importer Code, Test Suite, and Working CSV
- **Priority:** `P0`
- **Type:** `TECHNICAL_EVIDENCE`
- **Current Status:** **RESOLVED STATICALLY — PASS**
- **What is Known:** Physical inspection in Stage 3N-R2M-R5-R8-A confirms:
  1. `run_controlled_import.php`: SHA-256 `dbf3f73cca10d388962bd096bc18e7899f5e79089907c92121ca376106f92f02` (81,532 B) matches approved R2M-R4 baseline.
  2. `scratch/test_r2m_isolated_suite.php`: SHA-256 `c8843ed564c18f2156a3f303194e285b11bb6dadf922b5ff4bd40753dfdbd779` (52,906 B) matches approved R2M-R4 baseline.
  3. `data-preparation/woocommerce_furniture_dataset_...csv`: SHA-256 `d2c8566f334a767887f57c4a15ea456cfc5e117767fcfe46c599cc754bc2bba0` (25,712 B) matches locked hash constant in importer.
- **What Remains Unknown:** None (static integrity verified).
- **Required Evidence:** Physical hash logs recorded in Inventory Report Section 3.
- **Safe Acquisition Method:** Completed via Python SHA-256 computation.
- **Method Classification:** `STATIC`
- **Dependencies:** None.
- **Explicit Stop Condition:** Any file hash mismatch halts the process immediately.
- **Approving Authority:** Lead Security Auditor.
- **Gate Affected:** Pre-Gate / Gate 3.
- **Expected Artifact:** Verified in `docs/STAGE_3N-R2M-R5-R8-A_STATIC_EVIDENCE_INVENTORY.md`.

---

### Item R8A-12: Gate 4 Acceptance Criteria & Human Authorization Protocol Assembly
- **Title:** Formal Assembly of Authoritative Acceptance Metrics and Multi-Stakeholder Sign-Off
- **Priority:** `P0`
- **Type:** `OWNER_DECISION` / `TECHNICAL_EVIDENCE`
- **Current Status:** **OPEN — AWAITING PREREQUISITE GATE EVIDENCE**
- **What is Known:** Authoritative import metrics: 22 parent products, 42 variations (64 total), 449 total stock, 64 unique SKUs, 7 categories, and 64 lookup rows.
- **What Remains Unknown:** Evidence logs from Gates 1A through 2D.
- **Required Evidence:** Assembled dossier containing all prerequisite evidence logs and unanimous stakeholder signatures.
- **Safe Acquisition Method:** Conduct formal Gate 3 review session after Gate 2 preflight.
- **Method Classification:** `STATIC` (Review) / `OWNER_DECISION`
- **Dependencies:** Successful closure of Gates 1A, 1B, 1C, 2A, 2B, 2C, and 2D.
- **Explicit Stop Condition:** Any prerequisite check failed, open blocker, or unapproved recovery plan.
- **Approving Authority:** Project Owner & Lead Security Auditor.
- **Gate Affected:** Gate 3.
- **Expected Artifact:** `docs/STAGE_3N_GATE3_IMPORT_AUTHORIZATION_DOSSIER.md`.

---

### Item R8A-13: Stage 1F Storefront Asset Inventory & Git Tracking Baseline Gap
- **Title:** Version Control Gap on Stage 1F Block Templates and CSS Files
- **Priority:** `P0`
- **Type:** `TECHNICAL_EVIDENCE`
- **Current Status:** **OPEN — UNCOMMITTED WORKING TREE FILES DETECTED**
- **What is Known:** Stage 1F templates exist physically in `themes/twentytwentyfive-child/templates/` and `parts/`. However, `git status` in `app/public/wp-content` shows they are untracked or modified since Stage 1B commit `cdf203d`.
- **What Remains Unknown:** Exact diff between disk templates and active database template records.
- **Required Evidence:** Filesystem archive or Git baseline commit capturing all Stage 1F files prior to operational preflight.
- **Safe Acquisition Method:** Execute read-only archive or Git commit of theme files under separate authorization.
- **Method Classification:** `STATIC`
- **Dependencies:** None.
- **Explicit Stop Condition:** Operational execution commences without securing an immutable copy of Stage 1F theme files.
- **Approving Authority:** Project Owner & Lead Security Auditor.
- **Gate Affected:** Gate 1B / Gate 5.
- **Expected Artifact:** Verified filesystem archive or Git commit hash for Stage 1F assets.

---

### Item R8A-14: Dump Restore Safety & Cross-Database Redirection Prevention
- **Title:** Pre-Restore Static Inspection Protocol for `USE` and `CREATE DATABASE` Statements
- **Priority:** `P0`
- **Type:** `TECHNICAL_EVIDENCE`
- **Current Status:** **OPEN — REQUIRES INSPECTION OF NEW T-0 DUMP**
- **What is Known:** `mysql <target_db> < dump.sql` will redirect queries if the dump file contains `USE <other_db>;`. Historical dump `stage_1e_gate2` contains table drops but no `USE` statement. The newly created T-0 dump must be proven free of `USE` statements before any restore command is executed.
- **What Remains Unknown:** Contents of `stage_3n_t0_snapshot.sql` (file not yet created).
- **Required Evidence:** Execution of `Select-String -Path "backups/stage_3n_t0_snapshot.sql" -Pattern "USE |CREATE DATABASE "` returning 0 matches.
- **Safe Acquisition Method:** Static string inspection of captured dump under Gate 1B prior to Gate 1C rehearsal.
- **Method Classification:** `STATIC`
- **Dependencies:** Gate 1B snapshot capture.
- **Explicit Stop Condition:** Dump contains `USE ` or `CREATE DATABASE ` statements pointing to any database.
- **Approving Authority:** Database Administrator (DBA).
- **Gate Affected:** Gate 1B / Gate 1C.
- **Expected Artifact:** Documented in `docs/STAGE_3N_GATE1B_SNAPSHOT_VERIFICATION_REPORT.md`.

---

## 4. Comprehensive Approval Gate Mapping

| Operational Gate | Target Operation | Required Open Items & Pre-Conditions | Responsible Role | Target Output Artifact |
|---|---|---|---|---|
| **Gate 1A** | Read-Only DB Identity Verification | `R8A-01`, `R8A-08` | Database Administrator | `docs/STAGE_3N_GATE1A_DB_IDENTITY_VERIFICATION_LOG.md` |
| **Gate 1B** | Fresh T-0 Baseline Snapshot Capture | `R8A-02`, `R8A-13`, `R8A-14` | Database Administrator | `backups/stage_3n_t0_snapshot.sql` |
| **Gate 1C** | Isolated Restore Rehearsal | `R8A-03`, `R8A-14` | QA Lead & DBA | `docs/STAGE_3N_GATE1C_RESTORE_REHEARSAL_REPORT.md` |
| **Gate 2A** | Storefront Freeze & Worker Control | `R8A-06` | Operations Engineer | `docs/STAGE_3N_GATE2A_FREEZE_EXECUTION_LOG.md` |
| **Gate 2B** | HTTP Storefront-Lock Verification | `R8A-05` | QA Engineer | `docs/STAGE_3N_GATE2B_HTTP_LOCK_PROBE_REPORT.md` |
| **Gate 2C** | Live Read-Only SQL Preflight Suite | `R8A-04`, `R8A-07` | Technical Lead | `docs/STAGE_3N_GATE2C_SQL_PREFLIGHT_RESULTS.md` |
| **Gate 2D** | WordPress CLI Verify (Conditional) | `R8A-09` | Technical Lead | `docs/STAGE_3N_GATE2D_VERIFY_EXECUTION_LOG.md` |
| **Gate 3** | Ingestion Authorization Review | `R8A-10`, `R8A-11`, `R8A-12` | Project Owner & Auditor | `docs/STAGE_3N_GATE3_IMPORT_AUTHORIZATION_DOSSIER.md` |
| **Gate 4** | Controlled Import Execution | Gate 3 Token Issued | Execution Operator | `backups/manifests/manifest_run_*.json` |
| **Gate 4R** | Recovery & Disaster Triage | `R8A-10` | Project Owner & DBA | `docs/STAGE_3N_GATE4R_RECOVERY_EXECUTION_LOG.md` |
| **Gate 5** | Acceptance & Storefront Thaw | Post-Import Acceptance Queries | Project Owner & QA Lead | `docs/STAGE_3N_GATE5_POST_IMPORT_ACCEPTANCE_REPORT.md` |

---

## 5. Mandatory Operational Invariant

**ALL SEVEN RUNTIME OPEN ITEMS (R8A-01, R8A-02, R8A-03, R8A-04, R8A-05, R8A-06, R8A-07) REMAIN OPEN.**  
**NO OPERATIONAL PREFLIGHT, DATABASE CONNECTION, SNAPSHOT, OR IMPORT OPERATION IS AUTHORIZED BY THIS REGISTER.**  
**GATE 4 REMAINS STRICTLY BLOCKED.**
