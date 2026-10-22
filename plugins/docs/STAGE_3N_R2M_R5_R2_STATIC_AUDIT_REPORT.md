# STAGE 3N-R2M-R5-R2 — RUNBOOK V2 STATIC AUDIT & OPERATIONAL READINESS REPORT

**Project:** ATELIER — WordPress / WooCommerce Furniture E-commerce  
**Workspace:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\`  
**Primary Target Under Audit:** `docs/STAGE_3N_R2M_R5_GATE4_PREFLIGHT_RUNBOOK_DESIGN_V2.md`  
**Previous Audits:** `docs/STAGE_3N_R2M_R5_R1_PREFLIGHT_CONSISTENCY_AUDIT.md`, `docs/STAGE_3N_R2M_R5_GATE4_PREFLIGHT_RUNBOOK_DESIGN.md`  
**Target Importer Source:** `app\public\wp-content\plugins\run_controlled_import.php`  
**Target Dataset Source:** `data-preparation/woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv`  
**Mode:** READ-ONLY STATIC AUDIT AND OPERATIONAL READINESS DESIGN  
**Date:** 2026-10-19  
**Stage Decision:** **`PASS (STATIC DESIGN & AUDIT SPECIFICATION ONLY)`**  
**Gate 4 Status:** **`MANDATORY STOP — GATE 4 REMAINS STRICTLY BLOCKED & NOT AUTHORIZED`**

---

## 1. EXECUTIVE VERDICT & SAFETY COMPLIANCE

This audit conducted an exhaustive static analysis of Runbook Version 2.0 (`STAGE_3N_R2M_R5_GATE4_PREFLIGHT_RUNBOOK_DESIGN_V2.md`) to verify its precision, completeness, and alignment with actual codebase invariants, database architecture, and disaster recovery boundaries.

### 1.1 Strict Compliance with Absolute Safety Restrictions
During this audit:
- **Zero Database Connections:** No connection was opened to MySQL on port `10011` or any other network endpoint.
- **Zero WordPress Bootstrap:** `wp-load.php` was never invoked or included.
- **Zero Importer Invocations:** Neither `verify`, `rollback`, nor `import` was executed.
- **Zero Snapshot Operations:** No SQL snapshot was created, restored, or touched.
- **Zero Code Modifications:** Production importer [`run_controlled_import.php`](file:///C:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/run_controlled_import.php) (`DBF3F73C...`) and test suite [`scratch/test_r2m_isolated_suite.php`](file:///C:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/scratch/test_r2m_isolated_suite.php) (`C8843ED5...`) remain byte-identical to their post-R2M-R4 baseline.
- **Zero Secret Exposure:** Credentials, database passwords, salts, and auth tokens were omitted.
- **Document History Preserved:** Runbook V1 and V2 remain intact; all refinements are formalized herein and captured in companion deliverable: [`STAGE_3N_R2M_R5_GATE4_PREFLIGHT_RUNBOOK_DESIGN_V3_DRAFT.md`](file:///C:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/docs/STAGE_3N_R2M_R5_GATE4_PREFLIGHT_RUNBOOK_DESIGN_V3_DRAFT.md).

---

## 2. DETAILED AUDIT FINDINGS

| Finding ID | Severity | Category | Description | Status / Remediation |
|---|---|---|---|---|
| **F-R5R2-01** | **MEDIUM** | Dataset Documentation Ambiguity | Historical documentation inconsistently cited the working CSV as having "23 rows" (referring only to 22 parent rows + 1 header). Static parsing proves the CSV has 65 lines (1 header + 64 data records: 12 simple, 10 variable parent, 42 variations). | **CLARIFIED & RECONCILED IN V3** |
| **F-R5R2-02** | **HIGH** | WooCommerce Lookup Tables | WooCommerce 11.2.1 updates two dedicated lookup tables (`wp_wc_product_meta_lookup` and `wp_wc_product_attributes_lookup`) during product saves. Runbook V2 did not include these tables in the preflight schema existence check. | **EXPANDED TABLE INVENTORY IN V3** |
| **F-R5R2-03** | **MEDIUM** | DML-Only Privilege Insufficiency Risk | A restricted user with only `SELECT, INSERT, UPDATE, DELETE` could fail if WooCommerce or attached plugins attempt `CREATE TEMPORARY TABLE`, `LOCK TABLES`, or `ALTER TABLE` during import. | **DOCUMENTED AS UNVERIFIED / OUTSTANDING** |
| **F-R5R2-04** | **HIGH** | Post-1F Recovery Blindspot | Any attempt to restore existing snapshot `stage_1e_gate2_pre_import_20261016_223502.sql` would destroy the completed Stage 1F storefront block templates and theme customizer options. A fresh T-0 snapshot is a hard prerequisite. | **HARD REQUISITE LOCKED IN V3** |
| **F-R5R2-05** | **LOW** | Image URL Reconciliation | Working CSV incorporates 5 image URL cleanups across column 14 (removing 2 HTTP 404 Unsplash URLs). All other 1,787 data cells remain byte-identical to the source dataset. | **VERIFIED & INCORPORATED IN V3** |

---

## 3. AUDIT AREA A — SOURCE CODE VERIFICATION

Every claim regarding importer internals was verified by direct static code inspection:

### 3.1 Ownership Meta Key & Tagging Mechanism
- **Symbol / Function:** [`set_post_ownership_tag()`](file:///C:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/run_controlled_import.php#L458-L467), [`on_product_inserted()`](file:///C:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/run_controlled_import.php#L816-L823), [`on_attachment_created()`](file:///C:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/run_controlled_import.php#L800-L811).
- **Exact Meta Key:** `_controlled_import_run_id`.
- **Assignment Logic:** 
  - For products/variations: Hooked to `woocommerce_product_import_inserted_product_object` (lines 768, 816–823). Calls `record_created_object()` which executes `update_post_meta( $post_id, '_controlled_import_run_id', $this->run_id )`.
  - For attachments: Hooked to `add_attachment` (lines 767, 800–811). Checks against `$this->pre_existing_attachment_ids` (e.g. ID 5) and assigns `_controlled_import_run_id`.
- **Source Verification:** `VERIFIED DIRECT EVIDENCE`.

### 3.2 Manifest Path, Schema, Naming & Run-ID Handling
- **Symbol / Class:** [`Controlled_Import_Path_Resolver`](file:///C:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/run_controlled_import.php#L35-L191), [`initialize_manifest()`](file:///C:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/run_controlled_import.php#L672-L705), [`flush_manifest()`](file:///C:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/run_controlled_import.php#L714-L755).
- **Path Anchor:** Resolved canonically to `<project_root>/backups/manifests/`.
- **Naming Pattern:** `manifest_<run_id>.json`.
- **Run ID Structure:** Regex enforced at line 174: `/^run_[0-9]{8}_[0-9]{6}_[0-9a-f]{8}$/i`. Formatted as `run_YYYYMMDD_HHMMSS_<hex8>`.
- **Atomic Writing Pattern:** Writes to `$tmp_path = $this->manifest_path . '.tmp.' . getmypid() . '.' . bin2hex(random_bytes(4))` with `LOCK_EX`. Verifies `$bytes_written === $expected_bytes` (short-write detection). Renames via `@rename()` with `error_clear_last()`. In `finally`, unlinks `$tmp_path` if `$published` is false.
- **Source Verification:** `VERIFIED DIRECT EVIDENCE`.

### 3.3 Active Database Ownership Discovery & Rollback Reconciliation
- **Symbol / Function:** [`rollback()`](file:///C:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/run_controlled_import.php#L1334-L1480), [`find_posts_by_ownership_tag()`](file:///C:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/run_controlled_import.php#L414-L453).
- **Reconciliation Logic:** Lines 1419–1458 execute:
  ```php
  $discovered_db_objects = $this->find_posts_by_ownership_tag( $run_id );
  ```
  Iterates over all discovered posts in MySQL having `_controlled_import_run_id = $run_id`. If an object exists in MySQL but was omitted from the manifest file (Window 3 crash), it is appended to `created_variations`, `created_parents`, or `created_attachments`. Sets `$manifest['recovered_unmanifested_count']` and flushes manifest to disk prior to deletion.
- **Reverse Deletion Hierarchy:** Lines 1485–1540 delete objects in strict order:
  1. Product Variations (`product_variation`)
  2. Parent Products (`product`)
  3. Created Media Attachments (`attachment`)
- **Attachment ID 5 Protection:** Lines 1408–1415 and 1429–1432 enforce fail-closed security: if any discovered object ID matches `$manifest['pre_existing_attachments']` (Attachment ID 5), rollback halts immediately with a `Security violation` exception.
- **Source Verification:** `VERIFIED DIRECT EVIDENCE`.

### 3.4 Orphan Scenario Recoverability Matrix
- **Window 1 (Pre-Import Failure):** Aborts before writing records. Recoverable automatically (no DB changes).
- **Window 2 (Post Inserted, Meta Tag Not Inserted):** Process dies between `wp_insert_post()` and `update_post_meta()`. Post has NO tag in `wp_postmeta`. Rollback cannot discover or claim it (fail-closed boundary). **Cannot be recovered by code; requires T-0 snapshot restore.**
- **Window 3 (Tagged Post, Unflushed Manifest):** Post has tag in `wp_postmeta`. Discovered by `find_posts_by_ownership_tag()` and deleted by rollback. **Fully recoverable automatically.**
- **Window 4 (Post-Import Validation Failure):** Full manifest exists with status `FAILED_VERIFICATION`. Deleted by normal rollback. **Fully recoverable automatically.**
- **Source Verification:** `VERIFIED DIRECT EVIDENCE`.

### 3.5 Importer `verify` Execution Path Side Effects
- **Method:** [`preflight_verify()`](file:///C:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/run_controlled_import.php#L569-L628).
- **Static Findings:**
  - `preflight_verify()` does not call `initialize_manifest()`, does not call `flush_manifest()`, and writes no files.
  - Queries executed: `SELECT DATABASE()` and `SELECT post_id, meta_value FROM wp_postmeta WHERE meta_key = '_sku' AND meta_value IN (...)`. Both are strictly read-only.
  - **Bootstrap Boundary:** Calls `bootstrap_wordpress_runtime_safely()` which executes `require_once $wp_load`. This bootstraps WordPress core and active plugins. Any plugin with code attached to `plugins_loaded` or `init` could execute background options updates or evaluate transients.
- **Auditor Deduction:** `verify` is read-only in its own code, but WordPress bootstrap hooks cannot be guaranteed 100% side-effect-free across third-party extensions. Independent direct SQL queries must serve as the primary preflight check.

### 3.6 WooCommerce APIs & Lookup Table Operations
- **Core Importer Invocation:** Lines 1170–1180 instantiate `WC_Product_CSV_Importer`.
- **Database Write Surface During Save:**
  - Tables written: `wp_posts`, `wp_postmeta`, `wp_term_relationships`.
  - Additional WooCommerce Lookup Tables written:
    - `wp_wc_product_meta_lookup`: Stores cached pricing, stock status, ratings, and sales totals.
    - `wp_wc_product_attributes_lookup`: Stores attribute taxonomies and term assignments for faceted filtering.
- **Implication for Preflight:** The preflight table existence check must verify that `wp_wc_product_meta_lookup` and `wp_wc_product_attributes_lookup` exist and are structurally valid.

---

## 4. AUDIT AREA B — CORRECTED OPERATIONAL PREFLIGHT MATRIX

Runbook V3 incorporates all audited preflight checks. All proposed queries are **NOT EXECUTED** during this stage:

| Check # | Target Subsystem | Proposed SQL / Command [NOT EXECUTED] | Expected Result | What It Proves vs Does NOT Prove |
|---|---|---|---|---|
| **Check 1** | Active DB Identity, Host & Port | `SELECT DATABASE() AS active_db, @@port AS active_port, @@hostname, @@version, @@datadir;` | `active_db = 'local'`, `active_port = 10011`, `datadir` belongs to LocalWP | **Proves:** Connection is bound to intended instance. **Does NOT Prove:** Tables are valid. |
| **Check 2** | Table Prefix & Required Table Inventory | `SELECT table_name FROM information_schema.tables WHERE table_schema = 'local' AND table_name IN ('wp_posts', 'wp_postmeta', 'wp_options', 'wp_terms', 'wp_term_taxonomy', 'wp_term_relationships', 'wp_woocommerce_attribute_taxonomies', 'wp_wc_product_meta_lookup', 'wp_wc_product_attributes_lookup') ORDER BY table_name;` | Exactly 9 rows returned | **Proves:** All 7 core WP/WC tables + 2 WC lookup tables exist under `wp_`. **Does NOT Prove:** Row contents. |
| **Check 3** | Site & Home URL Routing | `SELECT option_name, option_value FROM wp_options WHERE option_name IN ('siteurl', 'home');` | Exactly 2 rows with `http://localhost:10010` | **Proves:** WordPress internal routing is intact. **Does NOT Prove:** Web server daemon status. |
| **Check 4A** | Global Attribute Definitions | `SELECT attribute_id, attribute_name, attribute_label, attribute_type FROM wp_woocommerce_attribute_taxonomies ORDER BY attribute_id;` | Exactly 3 rows: `color` (`Color`), `size` (`Size`), `brand` (`Brand`) | **Proves:** Metadata layer for attributes is defined. **Does NOT Prove:** Terms exist. |
| **Check 4B** | Taxonomy Term Counts | `SELECT tt.taxonomy, COUNT(t.term_id) AS count FROM wp_terms t INNER JOIN wp_term_taxonomy tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('pa_color', 'pa_size', 'pa_brand', 'product_cat') GROUP BY tt.taxonomy;` | `pa_color`=3, `pa_size`=3, `pa_brand`=5, `product_cat`=8 | **Proves:** All 11 attribute terms + 8 category terms exist in DB. **Does NOT Prove:** Assignment to posts. |
| **Check 5** | Product & Variation Baseline | `SELECT post_type, post_status, COUNT(*) FROM wp_posts WHERE post_type IN ('product', 'product_variation') GROUP BY post_type, post_status;` | 0 rows returned (empty set) | **Proves:** Pristine baseline (0 products, 0 variations across all statuses). **Does NOT Prove:** Absence of non-product posts. |
| **Check 6** | Media Attachment ID 5 Integrity | `SELECT p.ID, p.post_type, p.post_mime_type, p.guid, pm.meta_value AS attached_file FROM wp_posts p LEFT JOIN wp_postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attached_file' WHERE p.ID = 5;` | Exactly 1 row: `attachment`, `image/webp`, `woocommerce-placeholder.webp` | **Proves:** Placeholder attachment record is protected and intact. **Does NOT Prove:** Physical disk file existence. |
| **Check 7** | Tagged Orphans & Manifest State | `SELECT post_id, meta_key, meta_value FROM wp_postmeta WHERE meta_key = '_controlled_import_run_id';` + PowerShell scan of `backups/manifests/*.json` | 0 rows in SQL; 0 un-reconciled manifests on disk | **Proves:** No unrecovered runs or tagged orphans exist. **Does NOT Prove:** Absence of Window 2 untagged orphans. |
| **Check 8** | Storefront Maintenance Lock | `SELECT option_name, option_value FROM wp_options WHERE option_name = 'woocommerce_coming_soon';` | Exactly 1 row with `option_value = 'yes'` | **Proves:** Public storefront lock is enabled in options. **Does NOT Prove:** Cookie bypass status. |
| **Check 9** | Working Dataset File Integrity | `Get-FileHash -Path "...\woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv" -Algorithm SHA256` | Hash: `D2C8566F334A...`, Size: 25,712 bytes, 65 lines (64 data rows: 12 simple, 10 variable parent, 42 variations) | **Proves:** Working CSV matches immutable baseline with 5 image cleanups. **Does NOT Prove:** Remote image HTTP availability. |
| **Check 10** | Importer & Test Suite Hashes | `Get-FileHash 'run_controlled_import.php', 'scratch\test_r2m_isolated_suite.php' -Algorithm SHA256` | Importer: `DBF3F73C...`, Suite: `C8843ED5...` | **Proves:** Code has zero unapproved modifications since R2M-R4. **Does NOT Prove:** Runtime DB compatibility. |

---

## 5. AUDIT AREA C — SNAPSHOT AND RECOVERY SAFETY

### 5.1 Acceptance Criteria for Future T-0 Backup (Design Only)
A future T-0 snapshot must satisfy a 6-tier acceptance standard before any import may begin:
1. **Physical File Existence & Minimum Size:** Output file must exist in `<project_root>/backups/` with size $> 1.2\text{ MB}$.
2. **Cryptographic Checksum Verification:** SHA-256 digest computed immediately following dump generation and recorded in audit log.
3. **Structural SQL Header/Footer Validation:**
   - Starts with `-- MySQL dump 10.13` (or server-equivalent header).
   - Ends with `-- Dump completed on YYYY-MM-DD HH:MM:SS`.
   - Contains exactly 52 `DROP TABLE IF EXISTS` and `CREATE TABLE` definitions matching all active tables.
4. **Target Schema Containment:** Dump must NOT contain foreign schema statements (`CREATE DATABASE`, `USE foreign_db`).
5. **Non-Database Asset Protection:** Verify Git status of child theme (`twentytwentyfive-child`) and filesystem state of `wp-content/uploads/` are clean and committed before dump.
6. **Isolated Restore Rehearsal Requirement:** The T-0 snapshot must be test-restored into an isolated disposable test database (e.g. on auxiliary port `13306`) with zero live database mutations before Gate 4 dispatch.

### 5.2 Window 2 Crash Dynamics & Recovery Impossibility via Code
- **The Gap:** `wp_insert_post()` creates the row in `wp_posts`. If the process is hard-killed before `update_post_meta()` assigns `_controlled_import_run_id`, the post is an untagged orphan.
- **Why Software Rollback Cannot Recover:** Software rollback searches exclusively by `_controlled_import_run_id`. Attempting to guess untagged posts via heuristic criteria (e.g. matching SKU or timestamp) introduces catastrophic risk of deleting user-created products or administrator test items.
- **Fail-Closed Policy:** The importer refuses to delete untagged objects. Consequently, **T-0 database snapshot restoration is the ONLY safe and deterministic recovery path for a Window 2 crash.**
- **Post-T-0 Overwrite Risk:** Restoring a snapshot drops and recreates all tables, erasing any legitimate database changes made after T-0. Therefore, a strict **administrative freeze** and `woocommerce_coming_soon = 'yes'` lock are non-negotiable prerequisites.

---

## 6. AUDIT AREA D — LEAST-PRIVILEGE DATABASE REVIEW

### 6.1 Database Operations Profile
- **Importer Core Operations:** `SELECT, INSERT, UPDATE, DELETE` on `wp_posts`, `wp_postmeta`, `wp_term_relationships`. Requires zero DDL.
- **WooCommerce Core & Data Store Operations:**
  - DML on core tables and lookup tables (`wp_wc_product_meta_lookup`, `wp_wc_product_attributes_lookup`).
  - Transients updates on `wp_options`.
  - Potential internal operations: WooCommerce or active plugins may attempt `CREATE TEMPORARY TABLE` during complex taxonomy queries, or check table structure via `SHOW TABLES`.
- **Verdict on DML-Only Restriction:** While the importer code only executes DML, restricting the MySQL account to `SELECT, INSERT, UPDATE, DELETE` without granting `CREATE TEMPORARY TABLE` or testing WooCommerce lifecycle hooks risks runtime failures during product insertion.
- **Operational Status:** Provisioning a restricted user has **NOT** been performed in LocalWP (which runs as `root`). Marked **`OUTSTANDING / UNVERIFIED`**.

---

## 7. AUDIT AREA E — GATE 4 READINESS BLOCKERS

Gate 4 remains strictly **BLOCKED**. The following table details the hard blockers that must be cleared before any production import can be authorized:

| # | Blocker Item | Description / Prerequisite | Current Status |
|---|---|---|---|
| **B-01** | Post-1F T-0 Snapshot Missing | A fresh T-0 snapshot capturing completed Stage 1F storefront block templates does not yet exist. Existing snapshot is pre-1F. | **HARD BLOCKER** |
| **B-02** | Isolated Restore Rehearsal of T-0 Snapshot | The post-1F T-0 snapshot must be restored and verified on an auxiliary MySQL port before import. | **HARD BLOCKER** |
| **B-03** | Operational Preflight Execution | The 10 read-only preflight checks specified in Runbook V3 must be executed live and produce 100% PASS evidence. | **HARD BLOCKER** |
| **B-04** | Administrative Freeze Enforcement | Site owner must confirm no administrative logins, background crons, or plugin updates during import. | **HARD BLOCKER** |
| **B-05** | Explicit Operator & Owner Sign-Off | Formal written authorization instrument signed by project owner and lead engineer. | **HARD BLOCKER** |

---

## 8. DELIVERABLE COMPANION CREATED

In accordance with Section 4 of the user prompt, the companion deliverable has been authored:  
[`docs/STAGE_3N_R2M_R5_GATE4_PREFLIGHT_RUNBOOK_DESIGN_V3_DRAFT.md`](file:///C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/app/public/wp-content/plugins/docs/STAGE_3N_R2M_R5_GATE4_PREFLIGHT_RUNBOOK_DESIGN_V3_DRAFT.md)  
- **Document Status:** `DRAFT — NOT AUTHORIZED FOR EXECUTION`.
- **V1 and V2 Preservation:** Both previous runbook files remain unmodified.

---

## 9. CONCLUSION & FINAL GATE VERDICT

- **Static Audit Decision:** **`PASS (STATIC AUDIT & DESIGN SPECIFICATION ONLY)`**  
  All source code call paths, dataset metrics, failure windows, and preflight queries are rigorously verified and aligned with the physical codebase.
- **Production Gate 4 Decision:**  
  **`MANDATORY STOP — GATE 4 REMAINS STRICTLY BLOCKED`**  
  No production import, live database connection, or snapshot restoration is authorized by this review.
