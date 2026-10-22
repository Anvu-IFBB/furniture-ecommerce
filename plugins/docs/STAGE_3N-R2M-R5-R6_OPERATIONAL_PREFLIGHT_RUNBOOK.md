# STAGE 3N-R2M-R5-R6 — OPERATIONAL PREFLIGHT RUNBOOK

**Project:** ATELIER — WordPress / WooCommerce Furniture E-commerce  
**Workspace:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\`  
**Target Importer:** `app/public/wp-content/plugins/run_controlled_import.php`  
**Test Suite:** `app/public/wp-content/plugins/scratch/test_r2m_isolated_suite.php`  
**Working CSV:** `data-preparation/woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv`  
**Document Status:** PROPOSED OPERATIONAL SPECIFICATION — PREPARATION ONLY  
**Execution Status:** ALL COMMANDS AND QUERIES ARE PROPOSED — NOT EXECUTED  
**Gate Status:** GATE 4 STRICTLY BLOCKED — NO LIVE EXECUTION AUTHORIZATION

---

## 1. Executive Summary & Preparation Scope

This document provides the definitive, production-grade operational runbook for executing the preflight, baseline backup, isolated restore rehearsal, storefront protection, and controlled import verification phases for the ATELIER platform.

### Scope Boundaries:
- **PREPARATION ONLY:** This document authorizes **zero** commands, **zero** database connections, **zero** file writes, and **zero** runtime executions.
- **Strict Invariant:** Every command line, shell instruction, and SQL query in this runbook is explicitly marked `PROPOSED — NOT EXECUTED`.
- **Gate 4 Invariant:** Approval Gate 4 (Live Import Execution) remains **STRICTLY BLOCKED**. Operational activities are segmented across independent, sequential approval gates (Gate 1 through Gate 5), where no gate may implicitly authorize any subsequent phase.

---

## 2. CLI Syntax Reconciliation & Argument Parser Audit

An in-depth static audit of [`run_controlled_import.php`](file:///c:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/run_controlled_import.php#L1625-L1675) reveals a critical syntax difference compared to early runbook drafts:

### A. Observed CLI Argument Parser Implementation
```php
// Lines 1632-1674 of run_controlled_import.php
$argc_count = $argc ?? count( $argv ?? [] );
$action = $argv[1] ?? 'help';

if ( 'verify' === $action ) {
    if ( 2 !== $argc_count ) {
        echo "[ERROR] Action 'verify' takes no additional arguments. Received {$argc_count} arguments.\n";
        exit( 1 );
    }
} elseif ( 'rollback' === $action ) {
    if ( 3 !== $argc_count ) {
        echo "[ERROR] Action 'rollback' requires exactly 1 argument (<manifest_path>). Received {$argc_count} arguments.\n";
        exit( 1 );
    }
} elseif ( 'import' === $action ) {
    if ( 3 !== $argc_count ) {
        echo "[SAFETY STOP] Live import execution is strictly BLOCKED.\n";
        echo "Action 'import' requires exactly the authorization token: --gate4-user-approved-execute\n";
        exit( 1 );
    }
    if ( '--gate4-user-approved-execute' !== ($argv[2] ?? '') ) {
        exit( 1 );
    }
}
```

### B. Defect Resolution & Canonical Syntax
- **Historical Draft Defect:** Early drafts (e.g. Runbook V2/V3 Drafts) proposed running `php run_controlled_import.php verify --csv=data-preparation/...`.
- **Consequence of Running Historical Syntax:** The CLI parser checks `if ( 2 !== $argc_count )` and terminates with exit code 1 because 3 arguments were supplied.
- **Canonical Binding:** The importer binds to the working CSV internally via `Controlled_Import_Harness::LOCKED_WORKING_CSV_PATH` (`const LOCKED_WORKING_CSV_PATH = 'C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/data-preparation/woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv'`) and verifies its SHA-256 digest (`D2C8566F...`) automatically.
- **Authoritative Invocations:**
  - **Verify:** `php run_controlled_import.php verify` (strictly 2 arguments: script and action).
  - **Rollback:** `php run_controlled_import.php rollback <path-to-manifest.json>` (strictly 3 arguments).
  - **Import:** `php run_controlled_import.php import --gate4-user-approved-execute` (strictly 3 arguments).

---

## 3. Static Source Code & Runtime Architecture Review

Static inspection of [`run_controlled_import.php`](file:///c:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/run_controlled_import.php) establishes the following runtime characteristics:

### A. WordPress Bootstrap Side Effects
- **Theme Suppression:** `define( 'WP_USE_THEMES', false );` prevents loading theme template hierarchies during CLI execution.
- **Cron Suppression:** `define( 'DOING_CRON', true );` prevents WordPress from firing scheduled cron events during importer execution.
- **Active Plugin Initialization:** Loading `wp-load.php` executes active plugins. Active WooCommerce components (Action Scheduler, webhooks, analytics) are loaded into memory.
- **Safety Safeguard:** `bootstrap_wordpress_runtime_safely()` executes immediately after `wp-load.php` to verify:
  1. `$GLOBALS['wpdb']` is initialized.
  2. `SELECT DATABASE()` returns strictly `local` (`Controlled_Import_Harness::EXPECTED_DB_NAME`).
  3. Table prefix is strictly `wp_` (`Controlled_Import_Harness::EXPECTED_TABLE_PREFIX`).
  4. Class `WooCommerce` exists.
  If any check fails, execution aborts with a `RuntimeException` before any harness logic executes.

### B. Database Tables Read & Written
- **Read Tables:** `wp_options` (`siteurl`, `home`, `woocommerce_coming_soon`), `wp_posts` (existing products, attachment ID 5), `wp_postmeta` (SKU uniqueness, ownership tags), `wp_terms`, `wp_term_taxonomy` (global attributes, categories), `wp_wc_product_meta_lookup`, `wp_wc_product_attributes_lookup`.
- **Written Tables during Import:**
  - `wp_posts`: inserts products, parent variables, variations, and image attachments.
  - `wp_postmeta`: stores SKU, stock, prices, attributes, and ownership tag `_controlled_import_run_id`.
  - `wp_term_relationships`: maps categories and attribute terms to posts.
  - `wp_wc_product_meta_lookup` & `wp_wc_product_attributes_lookup`: updated via WooCommerce hooks (`wc_delete_product_transients`, `WC_Product::save()`).
- **Written Tables during Rollback:**
  - `wp_posts`: `wp_delete_post( $id, true )` deletes variations, parents, attachments.
  - `wp_postmeta`, `wp_term_relationships`, lookup tables: cleaned up via WordPress core deletion cascades.

### C. Object Creation Lifecycle & Ownership Tagging
1. **Creation Order:**
   - Global categories and attributes are verified pre-existing (Stage 1E.3B).
   - Images: intercepted via `add_attachment` hook, logged in manifest.
   - Parent Products (Simple and Variable): created via `wp_insert_post()`, immediately tagged with `_controlled_import_run_id`, terms attached.
   - Variations: created via `wp_insert_post()`, `post_parent` linked to parent ID, tagged with `_controlled_import_run_id`, attributes/prices/stock set.
   - Parent Synchronization: `WC_Product_Variable::sync()` called to calculate min/max variable prices.
   - Manifest Flush: updated manifest written to disk.
2. **Reverse Rollback Order:**
   - Variations first (`post_type = 'product_variation'`).
   - Parents second (`post_type = 'product'`).
   - Attachments third (`post_type = 'attachment'`), strictly skipping protected attachment ID 5.

### D. Crash Boundaries & Failure Modes
- **Window 1 (Image Temp File):** Crash during image download leaves temporary file in upload directory. Remediated in R2M-R4: temp files are tracked and cleaned via `unlink()` on error.
- **Window 2 (Untagged Post Orphan):** Crash between `wp_insert_post()` and `update_post_meta( '_controlled_import_run_id' )`. Post exists in `wp_posts` without metadata and without manifest record. Software rollback cannot discover untagged posts. Snapshot restoration is the sole automated recovery.
- **Window 3 (Pre-Flush Crash):** Post created and tagged, but crash occurs before in-memory manifest is flushed to disk. Remediated in R2M: harness discovers tagged posts by active database query `find_posts_by_ownership_tag( $run_id )`.
- **Process Termination / OOM / MySQL Outage:** Hard fatal errors terminate PHP execution immediately. `try-catch` blocks and `finally` blocks do not run. This reinforces the necessity of the pre-import snapshot (T-0).

---

## 4. Phase-by-Phase Operational Execution Specification

### Phase P-01: Environment & Workspace Baseline Verification (Read-Only)
- **Objective:** Verify workspace paths, file sizes, and cryptographic hashes before requesting operational execution.
- **Approval Gate:** Pre-Gate.
- **Authority:** Lead Security Auditor.
- **Status:** **STATIC VERIFIED** (Completed in Stage 3N-R2M-R5-R5).
- **Proposed Shell Commands:** `PROPOSED — NOT EXECUTED`
  ```powershell
  # Verify Importer SHA-256
  Get-FileHash -Algorithm SHA256 "app/public/wp-content/plugins/run_controlled_import.php"
  # Expected: DBF3F73CCA10D388962BD096BC18E7899F5E79089907C92121CA376106F92F02

  # Verify Isolated Test Suite SHA-256
  Get-FileHash -Algorithm SHA256 "app/public/wp-content/plugins/scratch/test_r2m_isolated_suite.php"
  # Expected: C8843ED564C18F2156A3F303194E285B11BB6DADF922B5FF4BD40753DFDBD779

  # Verify Working CSV SHA-256
  Get-FileHash -Algorithm SHA256 "data-preparation/woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv"
  # Expected: D2C8566F334A767887F57C4A15EA456CFC5E117767FCFE46C599CC754BC2BBA0
  ```
- **Stop Condition:** Any hash mismatch halts the process immediately.

---

### Phase P-02: Target Database Isolation & Access Verification
- **Objective:** Verify target database name, host, port, credentials, and user grants without modifying data.
- **Approval Gate:** **Gate 1 Approval Required**.
- **Authority:** Database Administrator (DBA).
- **Execution Mode:** `PROPOSED — NOT EXECUTED`
- **Verification Query:**
  ```sql
  -- PROPOSED — NOT EXECUTED
  SELECT 
      DATABASE() AS current_database,
      USER() AS current_user,
      @@hostname AS host_name,
      @@port AS port_number,
      @@datadir AS mysql_datadir;
  ```
- **Acceptance Criteria:** `current_database` is strictly `local`; `port_number` is `10011` (or local socket); user has required schema permissions on `local`.
- **Stop Condition:** `current_database` != `local` or non-local host detected.

---

### Phase P-03: Baseline T-0 Snapshot Protocol Design
- **Objective:** Capture a fresh, post-Stage-1F baseline database dump (`backups/stage_3n_t0_snapshot.sql`) immediately prior to import.
- **Approval Gate:** **Gate 1 Approval Required**.
- **Authority:** Database Administrator (DBA).
- **Execution Mode:** `PROPOSED — NOT EXECUTED`

#### 1. Why `stage_1e_gate2` Cannot Be Used:
Static audit in R5-R5 proved that `stage_1e_gate2_pre_import_20261016_223502.sql` was taken before Stage 1F storefront development. It contains only 12 posts and **zero** block templates. Restoring it would destroy all Stage 1F storefront headers, footers, homepage, archive, and product templates. A fresh T-0 snapshot is mandatory.

#### 2. Proposed mysqldump Command:
```bash
# PROPOSED — NOT EXECUTED
# Captured using LocalWP MySQL binary targeting port 10011
mysqldump -h 127.0.0.1 -P 10011 -u root -p local \
    --single-transaction \
    --quick \
    --hex-blob \
    --default-character-set=utf8mb4 \
    --no-tablespaces \
    --result-file="C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/backups/stage_3n_t0_snapshot.sql"
```

#### 3. Dump Verification & Integrity Protocol:
1. **Exit Code Check:** mysqldump process must return exit code 0.
2. **File Size Check:** Size must be > 1.2 MB (historical baselines are ~1.23 MB – 1.71 MB).
3. **Cryptographic Digest:** Compute and log SHA-256 immediately:
   ```powershell
   # PROPOSED — NOT EXECUTED
   Get-FileHash -Algorithm SHA256 "backups/stage_3n_t0_snapshot.sql"
   ```
4. **Dump Structure & Redirection Safety Inspection:**
   Inspect first 50 lines and search for database redirection statements:
   ```powershell
   # PROPOSED — NOT EXECUTED
   Select-String -Path "backups/stage_3n_t0_snapshot.sql" -Pattern "USE |CREATE DATABASE "
   ```
   **Invariant:** Dump must **NOT** contain `USE ` or `CREATE DATABASE ` statements pointing to any other database.
5. **Post-Stage-1F Content Proof:**
   Verify presence of Stage 1F block templates in the dump:
   ```powershell
   # PROPOSED — NOT EXECUTED
   Select-String -Path "backups/stage_3n_t0_snapshot.sql" -Pattern "wp_template|wp_template_part"
   ```
   **Acceptance Criteria:** Dump must contain matches for Stage 1F custom templates (`front-page`, `archive-product`, `single-product`, `header`, `footer`).

#### 4. Non-Database Files Requiring Separate Protection:
The following filesystem assets must be archived or verified in Git:
- Theme templates: `app/public/wp-content/themes/`
- Media uploads: `app/public/wp-content/uploads/` (including placeholder ID 5)
- Configuration: `app/public/wp-config.php`

#### 5. Failure Handling:
If dump fails, file size is 0, or disk space < 100 MB free, abort immediately. Mark Blocker `BLK-02` OPEN.

---

### Phase P-04: Isolated Restore Rehearsal Design
- **Objective:** Prove that `stage_3n_t0_snapshot.sql` can be restored without error into an isolated auxiliary database.
- **Approval Gate:** **Gate 1 Approval Required**.
- **Authority:** QA Lead & Database Administrator.
- **Execution Mode:** `PROPOSED — NOT EXECUTED`

#### 1. Isolated Target Identity Verification:
- Target database: `local_isolated_rehearsal` (created solely for rehearsal).
- Host: `127.0.0.1:10011`.
- **Pre-Rehearsal Verification Query:**
  ```sql
  -- PROPOSED — NOT EXECUTED
  SELECT DATABASE() AS rehearsal_db;
  -- Must return 'local_isolated_rehearsal', NEVER 'local'
  ```

#### 2. Safe Restoration Execution:
```bash
# PROPOSED — NOT EXECUTED
# Database explicitly specified on command line
mysql -h 127.0.0.1 -P 10011 -u root -p local_isolated_rehearsal < "C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/backups/stage_3n_t0_snapshot.sql"
```

#### 3. Post-Rehearsal Verification:
```sql
-- PROPOSED — NOT EXECUTED
USE local_isolated_rehearsal;

-- Check table count
SELECT COUNT(*) AS table_count 
FROM information_schema.tables 
WHERE table_schema = 'local_isolated_rehearsal';
-- Expected: 52 tables

-- Check post count
SELECT post_type, COUNT(*) AS count 
FROM wp_posts 
GROUP BY post_type;
-- Expected: zero products, zero variations, Stage 1F templates preserved

-- Drop rehearsal database after successful audit
DROP DATABASE local_isolated_rehearsal;
```
- **Acceptance Criteria:** Exactly 52 tables restored; post counts match baseline; Stage 1F templates verified; exit code 0.
- **Stop Condition:** Table count != 52 or SQL execution error.

---

### Phase P-05: Production Storefront Freeze & Background Protection
- **Objective:** Lock the public storefront, pause background cron jobs, and terminate active user sessions to ensure zero database mutations occur during import.
- **Approval Gate:** **Gate 2 Approval Required**.
- **Authority:** Project Owner & Operations Engineer.
- **Execution Mode:** `PROPOSED — NOT EXECUTED`

#### 1. Freeze Execution:
1. **Enable WooCommerce Coming Soon:**
   ```sql
   -- PROPOSED — NOT EXECUTED
   UPDATE wp_options SET option_value = 'yes' WHERE option_name = 'woocommerce_coming_soon';
   ```
2. **Pause WordPress Cron:**
   Ensure `wp-config.php` has `define( 'DISABLE_WP_CRON', true );` or lock cron option:
   ```sql
   -- PROPOSED — NOT EXECUTED
   UPDATE wp_options SET option_value = '' WHERE option_name = 'cron';
   ```
3. **Session Freeze:** Invalidate non-admin auth sessions.

#### 2. Dual Verification (SQL & Automated HTTP Probe):
- **SQL Check:**
  ```sql
  -- PROPOSED — NOT EXECUTED
  SELECT option_name, option_value FROM wp_options WHERE option_name IN ('woocommerce_coming_soon', 'woocommerce_store_pages_restricted');
  -- Expected: 'yes'
  ```
- **Automated HTTP cURL Probe:**
  ```bash
  # PROPOSED — NOT EXECUTED
  curl -s -i "http://localhost:10010/" | head -n 30
  curl -s -i "http://localhost:10010/shop/" | head -n 30
  ```
- **Acceptance Criteria:** Unauthenticated HTTP requests return HTTP 200 with Coming Soon banner; zero catalog/product data rendered.
- **Stop Condition:** Public shop or single-product URL renders live catalog content.

---

### Phase P-06: Production Live Read-Only Preflight Query Suite
- **Objective:** Verify live database readiness and run importer preflight verification in strictly read-only mode.
- **Approval Gate:** **Gate 2 Approval Required**.
- **Authority:** Technical Lead.
- **Execution Mode:** `PROPOSED — NOT EXECUTED`

#### Part 1: Importer Preflight CLI Invocation
```bash
# PROPOSED — NOT EXECUTED
# Note: Strictly 2 arguments; CSV is bound internally via LOCKED_WORKING_CSV_PATH
php app/public/wp-content/plugins/run_controlled_import.php verify
```
- **Acceptance Criteria:** Exits with code 0 and outputs `[SUCCESS] Preflight verification passed! Working CSV and target DB verified.`

#### Part 2: Eleven Proposed Verification SQL Queries

##### Query 1: Database Name & Server Identity
- **Purpose:** Confirm connection is bound strictly to `local` database on expected host.
- **Proposed SQL:** `PROPOSED — NOT EXECUTED`
  ```sql
  SELECT DATABASE() AS active_db, @@hostname AS host, @@port AS port, @@version AS mysql_version;
  ```
- **Expected Result:** `active_db` = `local`; `port` = `10011` (or local socket); `mysql_version` = `8.4.0`.
- **Zero-Row Handling:** If empty, database connection is broken.
- **Stop Condition:** `active_db` != `local`.
- **Non-Mutation Proof:** Standard read-only MySQL server status function.

##### Query 2: Table Prefix & Mandatory Table Count
- **Purpose:** Verify presence of all 52 standard WordPress and WooCommerce tables under `wp_` prefix.
- **Proposed SQL:** `PROPOSED — NOT EXECUTED`
  ```sql
  SELECT COUNT(*) AS total_tables,
         SUM(CASE WHEN table_name LIKE 'wp_%' THEN 1 ELSE 0 END) AS wp_prefixed_tables
  FROM information_schema.tables 
  WHERE table_schema = 'local';
  ```
- **Expected Result:** `total_tables` = 52; `wp_prefixed_tables` = 52.
- **Zero-Row Handling:** If 0, database is empty.
- **Stop Condition:** `wp_prefixed_tables` < 52.
- **Non-Mutation Proof:** Information schema read-only query.

##### Query 3: Core Site URLs
- **Purpose:** Verify site URL and home URL bindings match local development environment.
- **Proposed SQL:** `PROPOSED — NOT EXECUTED`
  ```sql
  SELECT option_name, option_value 
  FROM wp_options 
  WHERE option_name IN ('siteurl', 'home');
  ```
- **Expected Result:** 2 rows; both return `http://localhost:10010`.
- **Zero-Row Handling:** Missing critical WordPress options.
- **Stop Condition:** URLs point to external, staging, or production domains.
- **Non-Mutation Proof:** Standard `SELECT` query on `wp_options`.

##### Query 4: Global Attribute Taxonomies
- **Purpose:** Verify global taxonomies `pa_color`, `pa_size`, and `pa_brand` created in Stage 1E.3B exist.
- **Proposed SQL:** `PROPOSED — NOT EXECUTED`
  ```sql
  SELECT attribute_id, attribute_name, attribute_label, attribute_type 
  FROM wp_woocommerce_attribute_taxonomies 
  ORDER BY attribute_name ASC;
  ```
- **Expected Result:** 3 rows: `brand` (Brand), `color` (Color), `size` (Size).
- **Zero-Row Handling:** Attribute taxonomies missing; requires Stage 1E.3B replay.
- **Stop Condition:** Missing any of the 3 attributes.
- **Non-Mutation Proof:** Read-only `SELECT` query.

##### Query 5: Global Attribute Terms & Categories
- **Purpose:** Verify taxonomy terms and 7 product categories exist in `wp_terms`.
- **Proposed SQL:** `PROPOSED — NOT EXECUTED`
  ```sql
  SELECT tt.taxonomy, COUNT(t.term_id) AS term_count 
  FROM wp_term_taxonomy tt 
  JOIN wp_terms t ON tt.term_id = t.term_id 
  WHERE tt.taxonomy IN ('product_cat', 'pa_color', 'pa_size', 'pa_brand') 
  GROUP BY tt.taxonomy;
  ```
- **Expected Result:** `product_cat` >= 7; `pa_color` >= 4; `pa_size` >= 3; `pa_brand` >= 1.
- **Zero-Row Handling:** Missing required taxonomy terms.
- **Stop Condition:** Missing categories or attributes.
- **Non-Mutation Proof:** Read-only join query.

##### Query 6: Baseline Product & Variation Counts
- **Purpose:** Confirm clean baseline with zero products and zero variations.
- **Proposed SQL:** `PROPOSED — NOT EXECUTED`
  ```sql
  SELECT post_type, COUNT(*) AS count 
  FROM wp_posts 
  WHERE post_type IN ('product', 'product_variation') 
  GROUP BY post_type;
  ```
- **Expected Result:** 0 rows returned (or count = 0 for both types).
- **Zero-Row Handling:** Zero rows returned is the **exact expected pass condition**.
- **Stop Condition:** Any row returned with count > 0.
- **Non-Mutation Proof:** Read-only aggregated query on `wp_posts`.

##### Query 7: Attachment Placeholder ID 5 Integrity
- **Purpose:** Verify Attachment ID 5 exists, is `woocommerce-placeholder`, and has image MIME type.
- **Proposed SQL:** `PROPOSED — NOT EXECUTED`
  ```sql
  SELECT ID, post_title, post_name, post_type, post_mime_type, guid 
  FROM wp_posts 
  WHERE ID = 5;
  ```
- **Expected Result:** 1 row: `ID` = 5, `post_type` = `attachment`, `post_mime_type` = `image/webp` (or `image/png`), `post_name` = `woocommerce-placeholder`.
- **Zero-Row Handling:** ID 5 missing; placeholder protection compromised.
- **Stop Condition:** ID 5 missing or not an attachment.
- **Non-Mutation Proof:** Read-only primary key lookup.

##### Query 8: Ownership Metadata Cleanliness
- **Purpose:** Verify zero postmeta records exist with key `_controlled_import_run_id` prior to import.
- **Proposed SQL:** `PROPOSED — NOT EXECUTED`
  ```sql
  SELECT COUNT(*) AS tagged_postmeta_count 
  FROM wp_postmeta 
  WHERE meta_key = '_controlled_import_run_id';
  ```
- **Expected Result:** Exactly 1 row with `tagged_postmeta_count` = 0.
- **Zero-Row Handling:** Cannot occur with `COUNT(*)`.
- **Stop Condition:** `tagged_postmeta_count` > 0 (indicates uncleaned prior run).
- **Non-Mutation Proof:** Read-only indexed count on `wp_postmeta`.

##### Query 9: Manifest Directory & Filesystem State
- **Purpose:** Confirm `backups/manifests/` directory state and ensure zero conflicting manifests exist.
- **Proposed SQL:** N/A (Filesystem Check).
- **Proposed Command:** `PROPOSED — NOT EXECUTED`
  ```powershell
  Test-Path "backups/manifests"
  ```
- **Expected Result:** False (not yet created) or directory exists with 0 `.json` manifests.
- **Stop Condition:** Existing manifest for an incomplete or unknown run ID.
- **Non-Mutation Proof:** Read-only PowerShell existence check.

##### Query 10: Coming Soon Mode Verification
- **Purpose:** Confirm `woocommerce_coming_soon` option is set to 'yes'.
- **Proposed SQL:** `PROPOSED — NOT EXECUTED`
  ```sql
  SELECT option_value 
  FROM wp_options 
  WHERE option_name = 'woocommerce_coming_soon';
  ```
- **Expected Result:** Exactly 1 row: `option_value` = `'yes'`.
- **Zero-Row Handling:** Option missing.
- **Stop Condition:** `option_value` != `'yes'`.
- **Non-Mutation Proof:** Read-only single-row lookup.

##### Query 11: Lookup Tables Schema & Count
- **Purpose:** Verify WooCommerce lookup tables exist and are clean.
- **Proposed SQL:** `PROPOSED — NOT EXECUTED`
  ```sql
  SELECT 
      (SELECT COUNT(*) FROM wp_wc_product_meta_lookup) AS meta_lookup_count,
      (SELECT COUNT(*) FROM wp_wc_product_attributes_lookup) AS attr_lookup_count;
  ```
- **Expected Result:** Exactly 1 row: `meta_lookup_count` = 0, `attr_lookup_count` = 0.
- **Zero-Row Handling:** Table missing causes SQL error (which serves as an immediate fail-closed stop).
- **Stop Condition:** Any lookup row count > 0.
- **Non-Mutation Proof:** Read-only table count queries.

---

### Phase P-07: Controlled Import Authorization Gate
- **Objective:** Formal multi-stakeholder review of P-01 through P-06 evidence to decide whether to issue the Gate 4 execution token.
- **Approval Gate:** **Gate 3 Approval Required**.
- **Authority:** Project Owner & Lead Security Auditor.
- **Prerequisites:** All 11 blockers in `STAGE_3N-R2M-R5-R6_BLOCKER_MATRIX.md` marked CLOSED; T-0 snapshot verified; preflight verify log verified.
- **Decision:** Grant execution token `--gate4-user-approved-execute`.

---

### Phase P-08: Controlled Import Execution
- **Objective:** Execute production import using the hardened importer under strict monitoring.
- **Approval Gate:** **Gate 4 Authorization Required**.
- **Authority:** Execution Operator.
- **Execution Mode:** `PROPOSED — NOT EXECUTED`
- **Execution Command:**
  ```bash
  # PROPOSED — NOT EXECUTED
  php app/public/wp-content/plugins/run_controlled_import.php import --gate4-user-approved-execute
  ```
- **Runtime Monitoring:**
  - Track memory usage (< 256 MB).
  - Monitor execution timeout (< 300s).
  - Verify manifest file creation in `backups/manifests/manifest_run_*.json`.
- **Stop Condition:** Any PHP exception, nonzero exit code, or WooCommerce line-level error. If aborted, automatic rollback triggers; if rollback fails, escalate immediately to Gate 4R-DR.

---

### Phase P-09: Post-Import Data Acceptance Verification
- **Objective:** Validate that imported entities, stock, attributes, and lookup tables match authoritative baselines.
- **Approval Gate:** Post-Import Review.
- **Authority:** QA Lead.
- **Execution Mode:** `PROPOSED — NOT EXECUTED`
- **Verification Queries:**
  ```sql
  -- PROPOSED — NOT EXECUTED
  -- 1. Check Product Entities
  SELECT post_type, COUNT(*) AS count 
  FROM wp_posts 
  WHERE post_type IN ('product', 'product_variation') 
  GROUP BY post_type;
  -- Expected: product = 22, product_variation = 42 (Total = 64)

  -- 2. Check Total Stock
  SELECT SUM(CAST(meta_value AS UNSIGNED)) AS total_stock 
  FROM wp_postmeta 
  WHERE meta_key = '_stock';
  -- Expected: 449

  -- 3. Check Unique SKUs
  SELECT COUNT(DISTINCT meta_value) AS unique_skus 
  FROM wp_postmeta 
  WHERE meta_key = '_sku' AND meta_value != '';
  -- Expected: 64

  -- 4. Check Lookup Tables Populated
  SELECT COUNT(*) FROM wp_wc_product_meta_lookup;
  -- Expected: 64
  ```
- **Acceptance Criteria:** Exactly 64 products/variations; 449 stock; 64 SKUs; lookup tables populated; manifest marked `COMPLETED`.
- **Stop Condition:** Any metric mismatch triggers **Gate 4R Emergency Rollback**.

---

### Phase P-10: Emergency Rollback Execution (Gate 4R)
- **Objective:** Safely delete all imported products, variations, and attachments if acceptance fails.
- **Approval Gate:** **Gate 4R Emergency Approval Required**.
- **Authority:** Technical Lead / Execution Operator.
- **Execution Mode:** `PROPOSED — NOT EXECUTED`
- **Execution Command:**
  ```bash
  # PROPOSED — NOT EXECUTED
  php app/public/wp-content/plugins/run_controlled_import.php rollback backups/manifests/manifest_run_YYYYMMDD_HHMMSS_xxxx.json
  ```
- **Verification:**
  - Variations deleted first, parents second, attachments third.
  - Attachment ID 5 preserved.
  - Zero products remaining in `wp_posts`.
  - Manifest updated to `ROLLED_BACK`.
- **Stop Condition:** If rollback fails or manifest is missing, escalate immediately to Phase P-11.

---

### Phase P-11: Disaster Recovery Snapshot Restoration (Gate 4R-DR)
- **Objective:** Restore database to baseline using verified pre-import snapshot `stage_3n_t0_snapshot.sql` if software rollback cannot recover (e.g. Window 2 crash).
- **Approval Gate:** **Gate 4R-DR Emergency Approval Required**.
- **Authority:** Project Owner & Database Administrator (DBA).
- **Execution Mode:** `PROPOSED — NOT EXECUTED`
- **Execution Command:**
  ```bash
  # PROPOSED — NOT EXECUTED
  mysql -h 127.0.0.1 -P 10011 -u root -p local < "C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/backups/stage_3n_t0_snapshot.sql"
  ```
- **Verification:**
  - Database schema contains 52 tables.
  - `wp_posts` contains baseline row count; zero product orphans.
  - Stage 1F block templates intact.

---

### Phase P-12: Storefront Unfreeze & Thaw
- **Objective:** Re-enable public catalog access and resume background processing after acceptance.
- **Approval Gate:** **Gate 5 Release Approval Required**.
- **Authority:** Project Owner.
- **Execution Mode:** `PROPOSED — NOT EXECUTED`
- **Actions:**
  1. Set `woocommerce_coming_soon` to 'no'.
  2. Resume WordPress cron (`DISABLE_WP_CRON` = false).

---

### Phase P-13: Post-Release Smoke Verification
- **Objective:** Verify public rendering of storefront catalog and product pages.
- **Approval Gate:** Post-Release Verification.
- **Authority:** QA Engineer.
- **Execution Mode:** `PROPOSED — NOT EXECUTED`
- **Verification:**
  - Automated cURL to `http://localhost:10010/shop/` returns HTTP 200 with product grid.
  - Single-product page renders title, price, add-to-cart, and gallery.
  - Placeholder ID 5 renders properly for products with removed images.

---

### Phase P-14: Archival & Manifest Lock
- **Objective:** Archive execution logs, manifest files, and lock filesystem artifacts.
- **Approval Gate:** Post-Release Archival.
- **Authority:** Lead Security Auditor.
- **Execution Mode:** `PROPOSED — NOT EXECUTED`
- **Actions:** Set manifest file to read-only; record SHA-256 in final closure audit report.

---

## 5. Operational Approval & Responsibility Matrix

| Phase | Description | Permitted Action | Authority | Approval Gate | Stop Condition |
|---|---|---|---|---|---|
| **P-01** | Static Baseline Audit | Inspect files, hashes, and paths | Lead Auditor | Pre-Gate | Hash mismatch or missing file |
| **P-02** | Target Isolation Verification | Connect & check DB identity | System Admin / DBA | **Gate 1** | Target != `local` or non-local host |
| **P-03** | T-0 Snapshot Capture | Run mysqldump to `stage_3n_t0_snapshot.sql` | DBA | **Gate 1** | mysqldump exit != 0 or size < 1.2 MB |
| **P-04** | Isolated Restore Rehearsal | Restore T-0 in auxiliary DB | QA Lead / DBA | **Gate 1** | Tables != 52 or Stage 1F missing |
| **P-05** | Storefront Freeze | Enable Coming Soon, pause cron | Operations Engineer | **Gate 2** | Public access not blocked |
| **P-06** | Live Read-Only Preflight | Run importer verify & 11 SQL queries | Technical Lead | **Gate 2** | Any preflight check fails |
| **P-07** | Import Authorization Review | Review P-01..P-06 evidence | Project Owner & Auditor | **Gate 3** | Any open blocker or missing proof |
| **P-08** | Controlled Import Execution | Run importer with auth token | Execution Operator | **Gate 4** | PHP exception, timeout, or error |
| **P-09** | Post-Import Acceptance | Verify 64 products, 449 stock | QA Lead | Post-Import | Metric mismatch -> Trigger Gate 4R |
| **P-10** | Emergency Rollback | Run importer rollback | Technical Lead | **Gate 4R** | Tagged posts remain -> Trigger P-11 |
| **P-11** | Disaster Recovery Restore | Restore T-0 snapshot | Project Owner & DBA | **Gate 4R-DR** | Baseline mismatch |
| **P-12** | Storefront Unfreeze | Disable Coming Soon, resume cron | Project Owner | **Gate 5** | Catalog fails to render |
| **P-13** | Smoke Verification | HTTP verification of shop & product | QA Engineer | Post-Release | Layout or image 404 error |
| **P-14** | Manifest Lock | Set manifest to read-only | Lead Auditor | Post-Release | Manifest altered |

---

## 6. Mandatory Operational Sign-Off Invariant

**GATE 4 REMAINS STRICTLY BLOCKED.**  
**NO LIVE DATABASE CONNECTION, SNAPSHOT CAPTURE, STOREFRONT MUTATION, LIVE PREFLIGHT EXECUTION, IMPORT, OR ROLLBACK IS AUTHORIZED BY THIS DOCUMENT.**
