# STAGE 3N-R2M-R5-R3 — GATE 4 BLOCKER CLOSURE DESIGN AUDIT

**Project:** ATELIER — WordPress / WooCommerce Furniture E-commerce  
**Workspace:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\`  
**Primary Reference:** `docs/STAGE_3N_R2M_R5_GATE4_PREFLIGHT_RUNBOOK_DESIGN_V3_DRAFT.md`  
**Previous Audit:** `docs/STAGE_3N_R2M_R5_R2_STATIC_AUDIT_REPORT.md`  
**Target Importer Source:** `app\public\wp-content\plugins\run_controlled_import.php`  
**Mode:** READ-ONLY STATIC AUDIT & BLOCKER CLOSURE DESIGN  
**Date:** 2026-10-19  
**Audit Decision:** **`PASS (BLOCKER CLOSURE SPECIFICATION & AUDIT ONLY)`**  
**Gate 4 Status:** **`MANDATORY STOP — GATE 4 REMAINS STRICTLY BLOCKED & NOT AUTHORIZED`**

---

## 1. AUDIT MANDATE & ABSOLUTE SAFETY INVARIANTS

This audit evaluates whether the six Gate 4 readiness blockers identified in Stage 3N-R2M-R5-R2 have technically precise, unambiguous, and safe closure specifications.

### 1.1 Invariants Strictly Maintained
1. **Zero Database Connections:** No socket or TCP connection was initiated to MySQL port `10011`.
2. **Zero SQL Execution:** No SQL queries were executed against live tables.
3. **Zero WordPress Bootstrap:** `wp-load.php` was never loaded.
4. **Zero CLI Action Invocations:** Neither `verify`, `rollback`, nor `import` was executed.
5. **Zero Snapshot Operations:** No snapshot was created, restored, or modified.
6. **Zero Code Modifications:** Production importer [`run_controlled_import.php`](file:///C:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/run_controlled_import.php) (`DBF3F73C...`) and test suite [`scratch/test_r2m_isolated_suite.php`](file:///C:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/scratch/test_r2m_isolated_suite.php) (`C8843ED5...`) remain byte-identical to their post-R2M-R4 baseline.
7. **Document Immutability:** Runbook V1, V2, and V3 Draft remain untouched; all audit findings are recorded in this file and structured into the companion deliverable: [`STAGE_3N_R2M_R5_R3_GATE4_BLOCKER_CLOSURE_PLAN.md`](file:///C:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/docs/STAGE_3N_R2M_R5_R3_GATE4_BLOCKER_CLOSURE_PLAN.md).

---

## 2. BLOCKER STATUS & AUDIT SUMMARY

| Blocker ID | Blocker Title | Current Status | Audit Finding | Closure Plan Requirement |
|---|---|---|---|---|
| **B-01** | Post-Stage-1F T-0 Snapshot | **OPEN (HARD)** | Existing snapshot `stage_1e_gate2_...sql` pre-dates Stage 1F; restoring it would destroy theme block templates. | Capture fresh dump `gate4_snapshot_t0_<TIMESTAMP>.sql` with explicit verification. |
| **B-02** | Isolated Restore Rehearsal | **OPEN (HARD)** | Snapshot restorability cannot be assumed from file size or checksums alone; semantic execution proof is mandatory. | Full dry-run restore into auxiliary MySQL instance on port `13306` with zero writes to port `10011`. |
| **B-03** | Operational Preflight Execution | **OPEN (HARD)** | All 10 preflight checks in Runbook V3 remain unexecuted and classified as `NOT VERIFIED`. | Define strict PASS/FAIL/NOT VERIFIED states and execute via non-bootstrapping SQL probe. |
| **B-04** | Operational Freeze | **OPEN (HARD)** | No auditable lock currently prevents concurrent admin edits or scheduled cron jobs during import. | Formalize administrative freeze, WP-Cron deactivation, and release procedure. |
| **B-05** | Storefront Lock | **OPEN (HARD)** | Option `woocommerce_coming_soon = yes` must be confirmed both in database and via unauthenticated HTTP probe. | Verify database option and execute unauthenticated HTTP response code check. |
| **B-06** | Formal Operator Sign-Off | **OPEN (HARD)** | No blanket approval may be accepted; independent, granular sign-offs are required for each phase. | 6 distinct, sequential approval instruments with explicit zero-implicit-authorization rules. |

---

## 3. AUDIT OF BLOCKERS B-01 & B-02 (T-0 SNAPSHOT & ISOLATED RESTORE)

### 3.1 Capturing the Intended Post-Stage-1F Database State
- **Root Cause of B-01:** The existing verified snapshot (`stage_1e_gate2_pre_import_20261016_223502.sql`, 1,236,031 B) was generated on October 16, 2026 (Stage 1E.3F). Subsequent to that date, Stage 1F implemented extensive block theme templates, header/footer patterns, and custom styling in `twentytwentyfive-child`. Restoring the October 16 snapshot would revert `wp_posts` and `wp_postmeta` to pre-1F state, deleting theme template revisions and storefront blocks.
- **Closure Requirement for B-01:** A fresh **T-0 snapshot** must be captured immediately prior to the Gate 4 operation.

### 3.2 Acceptance Standard for Snapshot File
A generated snapshot cannot be declared valid based solely on file creation. It must satisfy a 4-point structural verification:
1. **Physical Size:** Must be $> 1.2\text{ MB}$ (expected baseline ~1.25 MB to 1.35 MB post-Stage-1F).
2. **Cryptographic Hash:** SHA-256 computed immediately following dump generation.
3. **SQL Dump Structure:**
   - Starts with valid mysqldump header: `-- MySQL dump 10.13` (or server-equivalent).
   - Ends with valid completion footer: `-- Dump completed on YYYY-MM-DD HH:MM:SS`.
   - Exactly 52 table definitions containing `DROP TABLE IF EXISTS` and `CREATE TABLE`.
4. **Schema Safety Containment:** Zero `CREATE DATABASE` or `USE <db>` statements targeting foreign schemas.

### 3.3 Semantic Restorability: Why Hash & Header Checks Are Insufficient
A valid checksum merely proves bitwise integrity against a previously saved file; it does not prove that the SQL statements inside the file can execute without constraint violations, syntax errors, or collation mismatches. True restorability requires **execution proof**.

### 3.4 Isolated Dry-Run Rehearsal Protocol (B-02)
To prove restorability without risking the live site, the rehearsal must replicate the architecture successfully validated in Gate 3I ([`stage_1e_3g_9_gate3i_snapshot_restore_verification_report.md`](file:///C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/app/public/wp-content/plugins/docs/stage_1e_3g_9_gate3i_snapshot_restore_verification_report.md)):
1. **Auxiliary Instance Binding:** Dedicated mysqld process initialized on port `13306` with a temporary, disposable data directory (`AppData/Local/Temp/gate4_aux_datadir`).
2. **Total Network & Physical Isolation:**
   - Live database remains on `127.0.0.1:10011` with datadir in LocalWP's protected run directory.
   - Auxiliary database runs on `127.0.0.1:13306`.
3. **Execution Proof:** Dump loaded into `furniture_restore_test` on port `13306` using `mysql.exe --port=13306`. Exit code must be `0`.
4. **Post-Restore Comparative Audit:**
   - Table count: Exactly 52 tables.
   - Core counts: 0 products, 0 variations, 0 SKUs.
   - Media: Attachment ID 5 intact.
   - Taxonomies: 3 attributes, 11 attribute terms, 8 categories (`product_cat`).
   - Storefront options: `woocommerce_coming_soon = yes`.
5. **Accidental Live Mutation Prevention:**
   - Before executing the restore command, the script must query `SELECT @@port, @@datadir, DATABASE()` to guarantee it is connected to port `13306` and NOT port `10011`.
   - The live database on port `10011` must be queried before and after the rehearsal to confirm zero row mutations occurred.
6. **Auxiliary Teardown:** Stop auxiliary mysqld via `mysqladmin --port=13306 shutdown` and delete temporary datadir.

### 3.5 Non-Database Asset Protection
SQL snapshots capture only the MySQL schema. Non-database assets must be independently safeguarded:
- **Child Theme Templates:** Confirm Git status of `twentytwentyfive-child` is clean and committed.
- **Media Uploads:** `wp-content/uploads/` must be checked; ensure `woocommerce-placeholder.webp` exists on disk.

### 3.6 Legitimate Changes After T-0
Restoring a snapshot drops and recreates all tables. If any legitimate user orders, comments, or administrative page edits occur after T-0, **they will be permanently erased**. Hence, an **operational freeze (B-04)** and **storefront lock (B-05)** are absolute prerequisites.

---

## 4. AUDIT OF WINDOW 2 RECOVERY & UNTAGGED ORPHAN HANDLING

### 4.1 The Precise Ingestion Lifecycle Gap
Static inspection of [`run_controlled_import.php`](file:///C:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/run_controlled_import.php) reveals the exact execution timeline during product ingestion:

```
[Phase 1: wp_insert_post()]
  │  Writes row into wp_posts (ID: $post_id)
  │
  ├─► CRASH WINDOW 2 (SIGKILL, OOM, Server Crash, Power Outage)
  │   Object exists in wp_posts; postmeta tag DOES NOT EXIST; manifest has no record.
  │
[Phase 2: update_post_meta()]
  │  Writes '_controlled_import_run_id' = $this->run_id into wp_postmeta
  │
  ├─► CRASH WINDOW 3 (Process dies before flush_manifest completes)
  │   Object is tagged in wp_postmeta; manifest on disk is partial or unwritten.
  │
[Phase 3: flush_manifest()]
     Writes manifest JSON to disk via atomic .tmp and rename pattern
```

### 4.2 Categorization of Crash Scenarios & Recovery Capabilities

| Crash Category | DB State | Manifest State | Importer Rollback Capability | Safe Recovery Mechanism |
|---|---|---|---|---|
| **Window 1 (Preflight Fail)** | Clean (0 created) | Not created | N/A | Automated termination (Exit 1). No recovery needed. |
| **Window 2 (Untagged Post)** | Post in `wp_posts`, NO tag in `wp_postmeta` | Absent | **FAIL-CLOSED: CANNOT RECOVER.** Refuses to delete untagged objects. | **T-0 Database Snapshot Restoration** |
| **Window 3 (Tagged, Unflushed)** | Post in `wp_posts`, TAG in `wp_postmeta` | Partial / Missing | **AUTOMATICALLY RECOVERABLE:** `find_posts_by_ownership_tag()` discovers and deletes tagged posts. | **CLI Rollback** (`php run_controlled_import.php rollback <manifest>`) |
| **Window 4 (Post-Import Fail)** | All posts tagged | Complete (`FAILED_VERIFICATION`) | **AUTOMATICALLY RECOVERABLE:** Clean hierarchical deletion in reverse order. | **CLI Rollback** |

### 4.3 Comparison of Recovery Options for Window 2
1. **Option A: Heuristic Code Deletion (SKU, Title, Timestamp Matching):**
   - *Risk:* Extremely dangerous. If clock skew occurs or an administrator created a draft product minutes prior, heuristic scanning risks deleting legitimate business data.
   - *Verdict:* Strictly prohibited by importer architecture. Importer operates **fail-closed**.
2. **Option B: Manual SQL Cleanup by Operator:**
   - *Risk:* Requires manual inspection of auto-increment IDs. High potential for human error and orphaned term relationships in `wp_term_relationships`.
   - *Verdict:* Feasible only under certified DBA review, but inferior to clean snapshot restore.
3. **Option C: T-0 Database Snapshot Restoration:**
   - *Safety:* 100% deterministic. Returns all 52 tables to the verified pre-import baseline.
   - *Verdict:* **The ONLY recommended, fully verified recovery path for Window 2.**

### 4.4 Explicit STOP Condition for Untagged Objects
If an untagged object is detected in `wp_posts` with `post_type IN ('product', 'product_variation')`:
> **STOP CONDITION:**  
> The operator MUST immediately halt. Software rollback must NOT be forced. The operator must verify whether the post was created during the failed run or pre-existed. If it is an import orphan, the operator must trigger **T-0 Database Snapshot Restoration** under explicit authorization.

---

## 5. AUDIT OF BLOCKER B-03 (OPERATIONAL PREFLIGHT VERIFICATION)

Every check in Runbook V3 is audited to establish unambiguous PASS, FAIL, and NOT VERIFIED evaluation criteria:

| Check # | Check Name | Authoritative Evidence Source | PASS Criteria | FAIL Criteria | Ambiguity / Edge Case Risk |
|---|---|---|---|---|---|
| **Check 1** | Active DB Identity, Host & Port | LocalWP configuration, MySQL engine | `active_db = 'local'`, `active_port = 10011`, `datadir` contains LocalWP container path | `active_db != 'local'` OR `active_port != 10011` | Connecting to a remote/different database named `local`. Handled by checking `datadir`. |
| **Check 2** | 9-Table Schema Inventory | `wp-admin/includes/schema.php`, WooCommerce 11.2.1 | Exactly 9 rows returned matching all 9 core and lookup tables under `wp_` | Row count != 9 (any table missing) | Table existence does not prove column types. Supplemented by structural snapshot check. |
| **Check 3** | Site & Home URL Routing | `wp_options` records from Stage 1E/1F | Exactly 2 rows with `option_value = 'http://localhost:10010'` | Any value != `http://localhost:10010` | Trailing slashes or protocol divergence (`https` vs `http`). |
| **Check 4A** | Global Attributes | `stage_1e_3b_1_global_attribute_creation_report.md` | Exactly 3 rows: `color` (`Color`), `size` (`Size`), `brand` (`Brand`) | Row count != 3 or missing slug | Confusion with core `product_brand` taxonomy. Handled by checking attribute table. |
| **Check 4B** | Taxonomy Term Counts | `stage_1e_3b_2_attribute_terms_categories_creation_report.md` | `pa_color`=3, `pa_size`=3, `pa_brand`=5, `product_cat`=8 | Any count mismatch | Term counts changing due to auto-created categories. Handled by exact term ID matching. |
| **Check 5** | Product & Variation Baseline | Catalog clean-slate requirement | 0 rows returned (empty result set across all post statuses) | Any row returned (count > 0) | Trashed products or drafts ignored by front-end. Handled by querying all `post_status`. |
| **Check 6** | Media Attachment ID 5 | `stage_1e_3g_9_gate3i_snapshot_restore_verification_report.md` | 1 row: `attachment`, `image/webp`, `woocommerce-placeholder.webp` | 0 rows or altered metadata | Postmeta key missing while post exists. Handled by LEFT JOIN on `_wp_attached_file`. |
| **Check 7** | Tagged Orphans & Manifests | Importer run-tag architecture | SQL returns 0 rows; directory contains 0 incomplete manifests | Any tagged row found or active manifest | Stray `.tmp` files from interrupted writes. Handled by scanning for `*.tmp*` and `*.json`. |
| **Check 8** | Storefront Maintenance Lock | WooCommerce Core Coming Soon option | Exactly 1 row with `option_value = 'yes'` | `option_value != 'yes'` or row missing | Admin bypass cookie active in operator's browser. Handled by unauthenticated HTTP probe. |
| **Check 9** | Working Dataset File Integrity | `stage_1e_3c_working_csv_preparation_report.md` | Hash: `D2C8566F334A...`, 25,712 B, 65 lines (64 data rows: 12 simple, 10 variable, 42 variations) | Hash mismatch or line count != 65 | Line endings (CRLF vs LF) altering byte hash. Handled by exact SHA-256 match. |
| **Check 10** | Importer & Suite Source Hashes | Stage 3N-R2M-R4 verification baseline | Importer: `DBF3F73C...`, Suite: `C8843ED5...` | Hash mismatch | Local whitespace or formatting changes. Handled by strict SHA-256 validation. |

---

## 6. AUDIT OF BLOCKERS B-04 & B-05 (FREEZE & STOREFRONT LOCK)

### 6.1 B-04: Operational Freeze Architecture
- **Administrative Freeze:**
  - *Risk:* An administrator editing a page or creating a test product during the import window alters the database post-T-0, making clean snapshot recovery impossible without data loss.
  - *Enforcement:* 
    1. Confirm zero active sessions in `wp_usermeta` / session tokens.
    2. Enforce operator-only maintenance protocol.
    3. Deactivate WordPress background cron (`define('DISABLE_WP_CRON', true)` in runtime or ensure no CLI cron runners are active).
- **Confirmation & Release:** Freeze status must be verified before Check 1 and released only after post-import reconciliation is signed off.

### 6.2 B-05: Storefront Lock & Effective Access Control
- **Database Option Check:** Query `SELECT option_value FROM wp_options WHERE option_name = 'woocommerce_coming_soon'` (must be `'yes'`).
- **Effective HTTP Boundary Check:** 
  - Simply checking the database option does not prove that unauthenticated HTTP requests are blocked (e.g. if a caching plugin serves stale pages or Coming Soon rules are misconfigured).
  - *Requirement:* Execute an unauthenticated HTTP GET probe against `http://localhost:10010/shop/` and `http://localhost:10010/` using curl or PowerShell `Invoke-WebRequest`. The response must serve the Coming Soon template / redirect, and must NOT expose product catalog or cart endpoints.

---

## 7. AUDIT OF BLOCKER B-06 (GRANULAR OPERATIONAL AUTHORIZATION)

Blanket authorization creates unacceptable operational hazard. B-06 is closed by establishing **six distinct, sequential approval gates**:

```
[Approval 1: T-0 Snapshot Creation]
       │
       ▼
[Approval 2: Isolated Restore Rehearsal on Port 13306]
       │
       ▼
[Approval 3: Live Read-Only Preflight Verification]
       │
       ▼
[Approval 4: Operational Pre-Execution Confirmation] ──(Emergency)──► [Approval 4R: Recovery/Rollback]
       │
       ▼
[Approval 5: Controlled Import Live Execution]
       │
       ▼
[Approval 6: Post-Reconciliation Acceptance & Freeze Release]
```

### Authorization Rules:
1. **Zero Implicit Authorization:** Approval of Step N never authorizes Step N+1.
2. **Explicit Parameter Locking:** Every approval must lock exact file paths, SHA-256 digests, and database endpoints.
3. **Emergency Rollback Authority:** If any post-import reconciliation check fails, Approval 4R is triggered immediately to execute scoped rollback.

---

## 8. LEAST-PRIVILEGE DATABASE AUDIT

### 8.1 Direct Importer Operations vs WordPress / Plugin Surface
- **Direct Importer SQL:** Operates strictly via DML (`SELECT, INSERT, UPDATE, DELETE`) on `wp_posts`, `wp_postmeta`, `wp_term_relationships`.
- **WordPress Core & Plugin Execution Surface:**
  - Product insertion triggers WooCommerce data stores (`WC_Product_Data_Store_CPT`).
  - Lookup tables are updated: `wp_wc_product_meta_lookup`, `wp_wc_product_attributes_lookup`.
  - WordPress transients and options are updated in `wp_options`.
  - Plugins hooked to `save_post` or `woocommerce_product_import_inserted_product_object` may attempt `CREATE TEMPORARY TABLE` or schema inspection (`SHOW TABLES`).
- **Audit Conclusion on Least Privilege:**
  - A user restricted solely to `SELECT, INSERT, UPDATE, DELETE` without `CREATE TEMPORARY TABLE` or `LOCK TABLES` has **NOT** been tested against the full active plugin stack in this LocalWP environment.
  - Imposing restricted permissions in production without prior isolated testing creates a high risk of import abort due to MySQL error 1142 (`Access denied`).
  - **Verdict:** Least-privilege account creation remains **`OUTSTANDING / UNVERIFIED`**. Production execution will proceed under LocalWP's standard service account, backed by strict application-level isolation and T-0 snapshot recovery.

---

## 9. CONCLUSION & FINAL GATE VERDICT

- **Audit Decision:** **`PASS (BLOCKER CLOSURE SPECIFICATION & AUDIT ONLY)`**  
  All six Gate 4 blockers now have rigorous, deterministic, evidence-based closure criteria.
- **Production Status:** **`MANDATORY STOP — GATE 4 REMAINS STRICTLY BLOCKED`**  
  No production import, live database connection, or snapshot restoration is authorized by this audit.
