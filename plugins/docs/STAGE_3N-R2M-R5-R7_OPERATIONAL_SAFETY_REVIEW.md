# STAGE 3N-R2M-R5-R7 — OPERATIONAL SAFETY REVIEW & EVIDENCE AUDIT

**Project:** ATELIER — WordPress / WooCommerce Furniture E-commerce  
**Workspace:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\`  
**Scope:** Independent Static Safety Review of R6 Deliverables & Operational Separation  
**Review Mode:** STRICTLY READ-ONLY STATIC REVIEW — NO OPERATIONAL EXECUTION  
**Current Status:** **REVIEW PASS — OPERATIONAL EXECUTION STRICTLY BLOCKED**  
**Gate Invariant:** **GATE 4 REMAINS STRICTLY BLOCKED**

---

## 1. Executive Summary & Review Scope

This document provides the authoritative, independent operational safety review for the ATELIER WooCommerce furniture import workflow. Following the completion of Stage 3N-R2M-R5-R6 (Operational Preflight Preparation), this review independently verifies all R6 deliverables, source code implementations, historical claims, and recovery boundaries.

### Core Review Invariants:
1. **Zero Runtime Execution:** No live database connections, SQL queries, WordPress bootstraps, WP-CLI invocations, importer runs (`verify`, `import`, `rollback`), snapshot captures, or restore operations were executed during this review.
2. **Zero Project Code/Data Mutations:** No application code, test suites, CSV data, theme templates, or existing backup files were altered.
3. **No Unearned Claim Promotion:** No historical claim or AI report assertion is accepted as verified without direct physical disk evidence.
4. **Gate 4 Invariant:** Gate 4 (Live Import Execution) remains **STRICTLY BLOCKED**.

---

## 2. Review A — Verification of R6 Deliverables & Historical Evidence

Every referenced deliverable and baseline artifact has been physically inspected on disk. File sizes and SHA-256 digests were computed independently during this session.

### Physical Artifact Register & Evidentiary Classification

| File Path | Actual Size | Computed SHA-256 Digest | Evidentiary Classification | Notes & Source Location |
|---|---|---|---|---|
| `docs/STAGE_3N-R2M-R5-R6_OPERATIONAL_PREFLIGHT_RUNBOOK.md` | 31,329 bytes | `07ac936935e1d66ed87e30ce4720dde92dcd4bde1c430d04f5851931e720155e` | **STATIC VERIFIED** | R6 Runbook defining 14 phases. Proposes commands but executes none. |
| `docs/STAGE_3N-R2M-R5-R6_BLOCKER_MATRIX.md` | 12,294 bytes | `bbadf4d4dec5ba3edee444a52a0d1f6b052187a504acd09c0c66ed6a2128d3dd` | **STATIC VERIFIED** | R6 Blocker Register defining BLK-01 through BLK-11. |
| `run_controlled_import.php` | 81,532 bytes | `dbf3f73cca10d388962bd096bc18e7899f5e79089907c92121ca376106f92f02` | **STATIC VERIFIED** | Audited importer script. Matches R2M-R4 baseline digest exactly. |
| `scratch/test_r2m_isolated_suite.php` | 52,906 bytes | `c8843ed564c18f2156a3f303194e285b11bb6dadf922b5ff4bd40753dfdbd779` | **STATIC VERIFIED** | Isolated unit test suite (31 tests). Matches R2M-R4 baseline digest. |
| `docs/STAGE_3N-R2M-R5-R5_FINAL_GATE4_EVIDENCE_AUDIT.md` | 30,238 bytes | `2e16e758a84ed664220e6862b860a23d96b64b2713df87d30021c1396f208ed9` | **STATIC VERIFIED** | R5-R5 Evidence Audit document. |
| `docs/STAGE_3N-R2M-R5-R5_FINAL_BLOCKER_REGISTER.md` | 9,819 bytes | `1ecd83211237d932104e4aac4470c7e04d0416ca7859aa3e5406b3a1839e0c5b` | **STATIC VERIFIED** | R5-R5 Blocker Register document. |
| `docs/STAGE_3N_R2M_R5_R4_OPERATIONAL_SAFETY_AUDIT.md` | 18,133 bytes | `53a776ca100eb975d3b67c51007742b8438cae770c7096ad3b6ac617e6855904` | **STATIC VERIFIED** | R5-R4 Operational Safety Audit document. |
| `docs/STAGE_3N_R2M_R5_R4_OPERATIONAL_EXECUTION_READINESS_MATRIX.md` | 9,999 bytes | `3e03f3098f6ed8b05f25635792e13e8a5a36e208548b017731f743cc2bab8681` | **STATIC VERIFIED** | R5-R4 Readiness Matrix document. |
| `docs/STAGE_3N_R2M_R5_R3_GATE4_BLOCKER_CLOSURE_PLAN.md` | 19,023 bytes | `5f12d5647c8fc6499e5118ff554aebc3441008f4dfde7aea09ff463e7932974c` | **STATIC VERIFIED** | R5-R3 Closure Plan document. |
| `docs/STAGE_3N_R2M_R5_R3_GATE4_BLOCKER_CLOSURE_AUDIT.md` | 19,140 bytes | `4acd4a0770dbe82ea5f580098da2025577aba9694fd35478785afe21a701594d` | **STATIC VERIFIED** | R5-R3 Closure Audit document. |
| `docs/STAGE_3N_R2M_R5_GATE4_PREFLIGHT_RUNBOOK_DESIGN_V3_DRAFT.md` | 21,472 bytes | `3a139171f47ad1e1bbe258879f99fcce55bbc2fe7b3a4e350fd57fe12930daf7` | **STATIC VERIFIED** | R5 Runbook V3 Draft document. |
| `data-preparation/woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv` | 25,712 bytes | `d2c8566f334a767887f57c4a15ea456cfc5e117767fcfe46c599cc754bc2bba0` | **STATIC VERIFIED** | Canonical approved working CSV dataset. Matches locked hash in importer. |
| `backups/stage_1e_gate2_pre_import_20261016_223502.sql` | 1,236,031 bytes | `9152e46bb462c321edd7778a6da7889450a90301bd1627682d10345a5bdcc8a3` | **CONTRADICTED (Historical) / STATIC VERIFIED (Physical)** | Historical reports claimed hash ending in `...EB87...`. Physical file hash ends in `...62C3...`. Pre-dates Stage 1F. |
| Live MySQL Database (`local` on 127.0.0.1:10011) | N/A | N/A | **NOT VERIFIED** | Live DB state cannot be queried during static audit. |
| Historical Reports claiming "Preflight Ready" | N/A | N/A | **HISTORICAL REPORTED** | Previous AI reports asserted readiness; must not be treated as live runtime evidence. |

---

## 3. Review B — Separation of Operational Approvals

In the R6 runbook, operational activities were grouped into coarse approval gates (e.g. Gate 1 covering DB access, backup capture, and restore rehearsal; Gate 2 covering storefront freeze, verify execution, and SQL preflight).

This review concludes that **coarse grouping creates severe operational risks** and that operational activities must be strictly decoupled into **independent, non-fungible approval gates**.

### Detailed Justification for Independent Gates:

```
+---------------------------------------------------------------------------------------------------+
| PRE-GATE: STATIC BASELINE (Audit Hashes, Files, Importer Source) -> VERIFIED                     |
+---------------------------------------------------------------------------------------------------+
                                                  |
                                                  v
+---------------------------------------------------------------------------------------------------+
| GATE 1A: Read-Only DB Identity Verification (SELECT DATABASE(), USER(), @@port)                   |
| Rationale: Must prove target identity BEFORE capturing backups or running scripts.                |
+---------------------------------------------------------------------------------------------------+
                                                  |
                                                  v
+---------------------------------------------------------------------------------------------------+
| GATE 1B: T-0 Snapshot Creation (Capture fresh stage_3n_t0_snapshot.sql)                           |
| Rationale: Backup creation can fail due to disk space, grants, or timeouts; must be inspected.     |
+---------------------------------------------------------------------------------------------------+
                                                  |
                                                  v
+---------------------------------------------------------------------------------------------------+
| GATE 1C: Isolated Restore Rehearsal (Restore into local_isolated_rehearsal & audit)               |
| Rationale: Proves dump is restorable. If it fails, abort BEFORE freezing store.                   |
+---------------------------------------------------------------------------------------------------+
                                                  |
                                                  v
+---------------------------------------------------------------------------------------------------+
| GATE 2A: Storefront Freeze & Worker Control (Coming Soon = yes, cron pause)                       |
| Rationale: Mutates live state and stops traffic. Must only occur AFTER backups are proven viable. |
+---------------------------------------------------------------------------------------------------+
                                                  |
                                                  v
+---------------------------------------------------------------------------------------------------+
| GATE 2B: HTTP Storefront-Lock Verification (cURL unauthenticated probe)                           |
| Rationale: Verifies block theme actually intercepts traffic before any catalog changes.           |
+---------------------------------------------------------------------------------------------------+
                                                  |
                                                  v
+---------------------------------------------------------------------------------------------------+
| GATE 2C: Live Read-Only SQL Preflight (Execute 11 SQL queries directly)                           |
| Rationale: Pure SQL checks database without triggering PHP/WordPress bootstrap side effects.      |
+---------------------------------------------------------------------------------------------------+
                                                  |
                                                  v
+---------------------------------------------------------------------------------------------------+
| GATE 2D: WordPress Verify Execution (php run_controlled_import.php verify)                        |
| Rationale: Loads wp-load.php, runs plugin hooks, checks memory limit and CSV SKUs.                |
+---------------------------------------------------------------------------------------------------+
                                                  |
                                                  v
+---------------------------------------------------------------------------------------------------+
| GATE 3: Multi-Stakeholder Evidence Review & Token Grant (--gate4-user-approved-execute)          |
| Rationale: Formal human sign-off evaluating all prior gate evidence.                              |
+---------------------------------------------------------------------------------------------------+
                                                  |
                                                  v
+---------------------------------------------------------------------------------------------------+
| GATE 4: Controlled Import Execution (Ingest 64 products, update stock, write manifest)            |
| Rationale: The sole data mutation phase.                                                          |
+---------------------------------------------------------------------------------------------------+
                   |                                                              |
    [Import Error or Gate 5 Failure]                                       [Import Succeeded]
                   v                                                              v
+--------------------------------------+                       +------------------------------------+
| GATE 4R: Rollback / Disaster Recovery|                       | GATE 5: Post-Import Acceptance     |
| 4R-SW: Software Rollback (Manifest)  |                       | Audit 64 products, 449 stock, SKUs |
| 4R-DR: Snapshot Restore (T-0)        |                       | Unfreeze Coming Soon & resume cron |
| 4R-DBA: Manual Surgical Remediation  |                       +------------------------------------+
+--------------------------------------+
```

### Analysis of Why Bundling Approvals is Hazardous:
1. **Bundling 1A, 1B, and 1C:** Taking a dump (1B) before proving database identity (1A) risks backing up the wrong database (e.g. system schemas or stale instances). Attempting a storefront freeze before restore rehearsal (1C) risks causing public storefront downtime for a backup that cannot even be restored.
2. **Bundling 2A, 2B, 2C, and 2D:** Setting Coming Soon (2A) is a database write; HTTP probing (2B) is a network verification; SQL preflight (2C) is a read-only database query; and `verify` (2D) is a PHP runtime execution that bootstraps WordPress core and plugins. Bundling them conceals failures and blurs failure boundaries.
3. **Bundling 4, 4R, and 5:** Import execution (4) must never implicitly authorize rollback (4R) or release (5). A failed import requires distinct triage: choosing between software rollback and full disaster recovery cannot be left to an automated assumption.

---

## 4. Review C — Snapshot and Restore Safety Static Analysis

### 1. Analysis of Dump Generation Command
The R6 runbook proposes the following command:
```bash
mysqldump -h 127.0.0.1 -P 10011 -u root -p local \
    --single-transaction \
    --quick \
    --hex-blob \
    --default-character-set=utf8mb4 \
    --no-tablespaces \
    --result-file="C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/backups/stage_3n_t0_snapshot.sql"
```

#### Static Safety Evaluation:
- **`--single-transaction`:** Ensures InnoDB snapshot consistency without taking global table locks. Safe for InnoDB tables (`wp_posts`, `wp_postmeta`, etc.).
- **`--quick` & `--hex-blob`:** Streams row-by-row and safely preserves binary attachment metadata. Safe.
- **`--no-tablespaces`:** Prevents failure if the MySQL user lacks the `PROCESS` privilege on MySQL 8.0+. Safe.
- **Absence of `-B` / `--databases`:** When `local` is specified as a plain argument without `-B`, `mysqldump` does **NOT** output `CREATE DATABASE local` or `USE local;`.
- **Destructive SQL Inside Dump:** The dump file **DOES** contain `DROP TABLE IF EXISTS wp_...;` statements before every `CREATE TABLE`.
  - **Risk:** When restored via `mysql <target_db> < dump.sql`, MySQL executes `DROP TABLE IF EXISTS` on whatever database is targeted. If an operator mistakenly runs `mysql local < ...` when aiming for rehearsal, or if the dump contains an unexpected `USE local;`, production tables will be instantly destroyed.
  - **Mandatory Safety Pre-Condition:** The operator must inspect the captured dump with:
    `Select-String -Path "backups/stage_3n_t0_snapshot.sql" -Pattern "USE |CREATE DATABASE "`
    to guarantee zero database redirection statements exist.

### 2. Target Database Constraint Independence
Because `mysql target_db < dump.sql` relies on the CLI argument to set the active database, any `USE` statement inside `dump.sql` would override the CLI argument and redirect all subsequent `DROP TABLE` and `CREATE TABLE` statements to that database.
- **Verification Requirement:** Dump inspection must be a separate, mandatory gate requirement before any restore rehearsal command is executed.

### 3. Rehearsal Database Identity Verification
The R6 runbook proposes restoring into `local_isolated_rehearsal`.
- **Pre-Restore Check:** The operator must query `SELECT DATABASE()` inside the rehearsal connection to prove it resolves strictly to `local_isolated_rehearsal` and **NEVER** `local`.
- **Post-Restore Check:** Query `information_schema.tables WHERE table_schema = 'local_isolated_rehearsal'` to confirm table count is 52.

### 4. Protection of Legitimate Stage 1F Changes
In Stage 1F, the storefront was built using WordPress block templates:
- Templates on disk: `templates/front-page.html`, `templates/archive-product.html`, `templates/single-product.html`, `parts/header.html`, `parts/footer.html`.
- Templates in database: Custom block template records in `wp_posts` (`post_type = 'wp_template'` and `'wp_template_part'`).
- **Critical Finding on `stage_1e_gate2`:** Static inspection of `stage_1e_gate2_pre_import_20261016_223502.sql` proves it was captured on 2026-10-16 and contains zero `wp_template` posts. Restoring this historical snapshot in an emergency would completely obliterate Stage 1F work.
- **Mitigation:** A fresh baseline dump (`stage_3n_t0_snapshot.sql`) captured after Stage 1F is strictly mandatory.

### 5. Separation of Database Backup & Non-Database Assets
A MySQL dump only protects the database. It does **not** protect:
- Custom theme template files: `app/public/wp-content/themes/`
- Media upload directory: `app/public/wp-content/uploads/` (including placeholder ID 5)
- Core configuration: `app/public/wp-config.php`
- **Mitigation:** These files must be verified in Git or backed up to an independent filesystem archive before preflight execution.

### 6. Restore Acceptance Criteria Beyond File Size & Table Count
Checking that a dump is > 1.2 MB and has 52 tables is necessary but insufficient.
- **Content-Level Acceptance Criteria:**
  1. `SELECT COUNT(*) FROM wp_posts WHERE post_type IN ('wp_template', 'wp_template_part');` must match post-Stage-1F count.
  2. `SELECT ID, post_name, post_mime_type FROM wp_posts WHERE ID = 5;` must return `woocommerce-placeholder`.
  3. `SELECT option_value FROM wp_options WHERE option_name = 'siteurl';` must return `http://localhost:10010`.

---

## 5. Review D — Importer Runtime Side Effects & WordPress Bootstrap Analysis

Static review of `run_controlled_import.php` ([lines 193–240](file:///c:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/run_controlled_import.php#L193-L240), [498–535](file:///c:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/run_controlled_import.php#L498-L535), [568–628](file:///c:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/run_controlled_import.php#L568-L628), and [1625–1729](file:///c:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/run_controlled_import.php#L1625-L1729)) reveals the exact runtime footprint of the importer bootstrap and `verify` command.

### 1. Operations that Write to Filesystem or Database
- **During CLI Invocation of `verify`:**
  - `Controlled_Import_Path_Resolver::__construct()`: Resolves directory roots. Does **not** write to disk.
  - `bootstrap_wordpress_runtime_safely()`:
    - Sets `define('WP_USE_THEMES', false)` (prevents theme loading).
    - Sets `define('DOING_CRON', true)` (prevents cron event firing).
    - Executes `require_once $wp_load` (loads `wp-load.php`).
    - Checks `$GLOBALS['wpdb']`, runs `SELECT DATABASE()`, checks prefix `wp_`, checks `class_exists('WooCommerce')`.
  - `ensure_resource_limits()`:
    - Reads `ini_get('memory_limit')`. If < 256MB, executes `@ini_set('memory_limit', '512M')`. Modifies PHP process memory limit.
  - `preflight_verify()`:
    - Reads `woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv`, verifies SHA-256 in memory.
    - Queries `wp_postmeta` for conflicting SKUs: `SELECT post_id, meta_value FROM wp_postmeta WHERE meta_key = '_sku' AND meta_value IN (...)`.
    - Returns `true`.
  - **Direct File/DB Writes in `verify`:** The importer itself performs **zero** file writes and **zero** SQL insert/update/delete statements during `verify`.
- **Potential Indirect Plugin / WordPress Core Side Effects During `wp-load.php`:**
  - When `wp-load.php` executes, WordPress core and all active plugins (`wp_options` `active_plugins`) are loaded into memory.
  - Active plugins include WooCommerce, Action Scheduler, and potentially other site plugins.
  - Hooks fired during bootstrap: `muplugins_loaded`, `plugins_loaded`, `setup_theme`, `after_setup_theme`, `init`, `wp_loaded`.
  - In certain configurations, plugins or core can update expired transients in `wp_options`, initialize database tables, or register cron actions in `wp_actionscheduler_actions`.
  - While `DOING_CRON = true` stops cron task execution, Action Scheduler table checks or transient updates can occur during `init`.

### 2. HTTP Probes and State Changes
- The R6 runbook proposes running cURL probes against `http://localhost:10010/` and `/shop/` under Phase P-05.
- **Safety Evaluation:** HTTP GET probes to frontend URLs render pages. In WordPress/WooCommerce, frontend GET requests can trigger session cookies (`wp_woocommerce_session_*`), set transients, or invoke template render hooks.
- **Recommendation:** HTTP probes must be executed with `-s -i -o /dev/null` or piped to `head` without writing cookies (`--cookie-jar` disabled) to prevent session table bloat.

### 3. Verification Checks Performable via Direct Read-Only SQL
Almost all preflight assertions performed by `run_controlled_import.php verify` can be performed via **pure, read-only SQL queries** without bootstrapping WordPress:

| Check | Importer `verify` Logic | Direct Read-Only SQL Alternative |
|---|---|---|
| Target Database Identity | `SELECT DATABASE()` in `$wpdb` | `SELECT DATABASE() AS active_db;` |
| Table Prefix & Count | `prefix === 'wp_'` | `SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'local' AND table_name LIKE 'wp_%';` |
| Site URL / Home URL | Checked by WP core | `SELECT option_name, option_value FROM wp_options WHERE option_name IN ('siteurl', 'home');` |
| Conflicting CSV SKUs | SQL query on `wp_postmeta` | `SELECT post_id, meta_value FROM wp_postmeta WHERE meta_key = '_sku' AND meta_value IN ('FURN-001', 'FURN-002', ...);` |
| Clean Product Baseline | Not checked in `verify` | `SELECT post_type, COUNT(*) FROM wp_posts WHERE post_type IN ('product', 'product_variation') GROUP BY post_type;` |
| Ownership Tag Cleanliness | Not checked in `verify` | `SELECT COUNT(*) FROM wp_postmeta WHERE meta_key = '_controlled_import_run_id';` |
| Placeholder ID 5 | Not checked in `verify` | `SELECT ID, post_name, post_mime_type FROM wp_posts WHERE ID = 5;` |
| Lookup Tables Cleanliness | Not checked in `verify` | `SELECT COUNT(*) FROM wp_wc_product_meta_lookup;` |
| CSV Integrity | PHP `hash('sha256', ...)` | PowerShell `Get-FileHash -Algorithm SHA256 "data-preparation/..."` |

### 4. Safer Recommendation
Direct read-only SQL queries (Gate 2C) provide a cleaner, safer, zero-side-effect preflight than bootstrapping WordPress. Therefore:
- **Gate 2C (Direct Read-Only SQL Preflight)** must be the primary gating preflight.
- **Gate 2D (WordPress `verify` Execution)** should only be executed if the operator explicitly requires testing the PHP environment limits and WooCommerce class availability prior to import.

---

## 6. Review E — Recovery Dynamics, Window 2 & Disaster Recovery

### 1. The Window 2 Crash Boundary
Static inspection of `run_controlled_import.php` ([lines 1000–1260](file:///c:/Users/Vu Cong Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/run_controlled_import.php#L1000-L1260)) details the exact entity creation sequence:
```
Step 1: wp_insert_post() -> Creates post row in wp_posts, returns generated post ID.
Step 2: update_post_meta($id, '_controlled_import_run_id', $run_id) -> Tags post with ownership.
Step 3: add $id to in-memory $this->manifest_data['created_parents'].
Step 4: $this->flush_manifest() -> Writes manifest JSON buffer to disk.
```

#### What Happens if Execution Terminates in "Window 2" (Between Step 1 and Step 2):
1. The post row exists in `wp_posts` with an AUTO_INCREMENT ID.
2. The post has **no** `_controlled_import_run_id` meta record in `wp_postmeta`.
3. The post is **not** recorded in the disk manifest file.
4. **Behavior of Software Rollback (`php run_controlled_import.php rollback <manifest>`):**
   - The rollback discovery query (`find_posts_by_ownership_tag($run_id)`) searches `wp_postmeta` for `_controlled_import_run_id = $run_id`. Because the post was never tagged, the discovery query **cannot find it**.
   - If the in-memory manifest had captured the ID before crash but the tag was missing, lines 1473–1476 and 1488–1491 execute:
     ```php
     $owner = $this->get_post_ownership_tag( $pid );
     if ( $owner !== $run_id ) {
         throw new RuntimeException( "Ownership audit failed: Product ID {$pid} is not owned by run '{$run_id}'... Rollback refused." );
     }
     ```
   - **Rollback halts immediately and refuses to delete the post.**
   - **Why this is intentional:** The importer enforces strict fail-closed safety. It will **never** heuristically delete posts by title or SKU, because heuristic matching could delete pre-existing or legitimate store products.
   - **Result:** The untagged post remains as an orphan in `wp_posts`. Software rollback cannot clean it up.

### 2. Manifest Capabilities and Limitations

| The Manifest CAN Identify | The Manifest CANNOT Identify |
|---|---|
| All post IDs successfully tagged with `_controlled_import_run_id` | Posts created in `wp_posts` where the process died before meta tagging |
| All variation and attachment IDs recorded before a flush | Posts created in memory where a hard crash (SIGKILL/power) preceded disk flush |
| Protected pre-existing attachment IDs (ID 5) | External concurrent database writes (e.g. if storefront was not frozen) |
| Import execution status (`IN_PROGRESS`, `FAILED`, `COMPLETED`) | Database schema corruption or broken primary key sequences |

### 3. Automated vs. Manual Recovery Pathways
- **Automated Software Rollback (Gate 4R-SW):**
  - Fully automated via `php run_controlled_import.php rollback <manifest>`.
  - Safely deletes variations, parent products, and attachments in reverse dependency order.
  - Preserves attachment ID 5.
  - **Prerequisite:** Manifest is valid, and all objects possess valid ownership tags.
- **Automated Disaster Recovery Snapshot Restore (Gate 4R-DR):**
  - Restores the verified `stage_3n_t0_snapshot.sql` into database `local`.
  - Completely wipes all post-T-0 mutations, eliminating untagged orphans, corrupted lookup tables, or partial runs.
  - **Prerequisite:** T-0 snapshot was captured after Stage 1F and verified in Gate 1C isolated rehearsal.
- **Manual Surgical DBA Remediation (Gate 4R-DBA):**
  - Used if a Window 2 crash occurred, software rollback failed, and snapshot restore is contraindicated (e.g. if preserving subsequent configuration changes is required).
  - DBA queries MySQL binary log or AUTO_INCREMENT sequence in `wp_posts` to identify the orphan post ID, verifies zero relationships exist, and deletes it with audited SQL.

### 4. Risks of Restoring an Older Snapshot After Legitimate Changes
- If an operator mistakenly restores `stage_1e_gate2_pre_import_20261016_223502.sql`:
  - All Stage 1F block theme templates (`wp_template` posts) are dropped.
  - The entire storefront reverts to its pre-development state.
  - Any orders, customer accounts, or configuration settings saved since October 16 are destroyed.
- **Mitigation:** The pre-import snapshot must be captured immediately before the import run (T-0), and the storefront must remain completely frozen from T-0 until acceptance.

### 5. Mandatory Freeze, Evidence Preservation & Approval Conditions Before Recovery
Before executing any rollback or restore operation:
1. **Storefront Freeze:** `woocommerce_coming_soon` must be verified as `'yes'`.
2. **Evidence Preservation:** The failed manifest (`backups/manifests/manifest_run_*.json`), PHP error log, and a diagnostic dump of the corrupted state (`backups/stage_3n_crash_state.sql`) must be archived.
3. **Formal Approval:** A specific recovery gate (Gate 4R-SW, 4R-DR, or 4R-DBA) must be formally approved by the Project Owner and DBA.

---

## 7. Audit Findings, Blockers & Final Review Verdict

### Summary of Findings:
1. **R6 Deliverables Integrity:** Both `STAGE_3N-R2M-R5-R6_OPERATIONAL_PREFLIGHT_RUNBOOK.md` and `STAGE_3N-R2M-R5-R6_BLOCKER_MATRIX.md` are physically present, well-structured, and explicitly state that all commands are proposed and not executed.
2. **Importer Syntax Precision:** R6 correctly documents that `run_controlled_import.php verify` takes strictly 2 arguments and binds internally to `LOCKED_WORKING_CSV_PATH`, discarding the erroneous `--csv` flag from early drafts.
3. **Approval Decoupling Requirement:** Coarse bundling of operational phases in R6 is unsafe. Independent approval gates (1A through 5) must be established.
4. **Preflight Direct SQL Superiority:** Direct read-only SQL queries (Gate 2C) provide superior safety and zero side effects compared to bootstrapping WordPress (Gate 2D).
5. **Recovery Protocol Completeness:** Window 2 crash boundaries and snapshot restore dependencies are fully mapped and accounted for.

### Open Blocker Status:
All operational blockers (`BLK-02`, `BLK-03`, `BLK-05`, `BLK-06`, `BLK-07`, `BLK-08`, `BLK-09`, `BLK-10`, `BLK-11`) remain **OPEN**. Gate 4 remains **STRICTLY BLOCKED**.

### Final Review Verdict:
**REVIEW PASS — OPERATIONAL EXECUTION STRICTLY BLOCKED**

*Zero runtime commands or database mutations were authorized or executed during this review.*
