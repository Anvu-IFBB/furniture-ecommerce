# STAGE 3N-R2M-R5-R1 — PREFLIGHT RUNBOOK CONSISTENCY AUDIT

**Project:** ATELIER — WordPress / WooCommerce Furniture E-commerce  
**Workspace:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\`  
**Primary Target Under Audit:** `docs/STAGE_3N_R2M_R5_GATE4_PREFLIGHT_RUNBOOK_DESIGN.md`  
**Target Importer Source:** `app\public\wp-content\plugins\run_controlled_import.php`  
**Dataset Source:** `data-preparation/woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv`  
**Mode:** READ-ONLY SOURCE AND DOCUMENT AUDIT  
**Date:** 2026-10-19  
**Audit Decision:** **`PASS WITH CORRECTIONS REQUIRED (V2 ISSUED)`**  
**Gate 4 Status:** **`MANDATORY STOP — GATE 4 IS STRICTLY BLOCKED & NOT AUTHORIZED`**

---

## 1. AUDIT MANDATE & ABSOLUTE SAFETY INVARIANTS

This independent audit evaluated the consistency, completeness, and technical fidelity of `STAGE_3N_R2M_R5_GATE4_PREFLIGHT_RUNBOOK_DESIGN.md` against authoritative source files, dataset artifacts, historical Stage 1E/1F evidence, and current importer code.

### 1.1 Invariants Upheld During Audit
1. **Zero Database Connections:** No connection was opened to MySQL on port `10011` or any other port.
2. **Zero WordPress Bootstrap:** `wp-load.php` was never loaded.
3. **Zero CLI Action Execution:** Neither `verify`, `rollback`, nor `import` was invoked.
4. **Zero Snapshot Operations:** No database dumps were created or restored.
5. **Zero Source Modifications:** No production PHP files, CSV datasets, theme templates, or manifests were altered.
6. **Zero Secret Exposure:** Database credentials, salts, and tokens remain strictly withheld.
7. **Document Immutability:** The original `STAGE_3N_R2M_R5_GATE4_PREFLIGHT_RUNBOOK_DESIGN.md` report is preserved unmodified. All corrections are formally documented herein and consolidated into a revised companion runbook: `STAGE_3N_R2M_R5_GATE4_PREFLIGHT_RUNBOOK_DESIGN_V2.md`.

---

## 2. AUDIT FINDINGS SUMMARY

| Finding ID | Severity | Category | Summary Description | Status |
|---|---|---|---|---|
| **F-R5R1-01** | **HIGH** | Attribute Discrepancy | R2M-R5 Runbook specified `pa_material` instead of `pa_brand`. Source code, dataset, and Stage 1E evidence conclusively establish `color`, `size`, and `brand`. | **RECONCILED & CORRECTED** |
| **F-R5R1-02** | **MEDIUM** | Query Defect | Check 3 in R2M-R5 used a circular `WHERE table_name LIKE 'wp_%' LIMIT 1` query that does not prove all required tables exist with prefix `wp_`. | **RECONCILED & CORRECTED** |
| **F-R5R1-03** | **MEDIUM** | Read-Only Boundary | Importer's `verify` action executes `wp-load.php`, exposing the run to WordPress core/plugin bootstrap hooks. It is not proven 100% side-effect-free under all conditions. | **EVALUATED & ISOLATED** |
| **F-R5R1-04** | **LOW** | Media Check Scope | Check 8 for Attachment ID 5 verified `wp_posts` columns but omitted checking `_wp_attached_file` in `wp_postmeta`. | **RECONCILED & CORRECTED** |
| **F-R5R1-05** | **HIGH** | Disaster Recovery | Restoring historical snapshot `stage_1e_gate2_...sql` would permanently destroy Stage 1F storefront block templates. A fresh post-1F T-0 snapshot is strictly mandatory. | **CONFIRMED & RE-EMPHASIZED** |
| **F-R5R1-06** | **LOW** | Least-Privilege Account | LocalWP environment runs as `root`; restricted-user provisioning has never been tested in this environment and remains an open operational item. | **CONFIRMED AS OUTSTANDING** |

---

## 3. ATTRIBUTE & TAXONOMY RECONCILIATION

### 3.1 The `pa_material` vs `pa_brand` Discrepancy
In `STAGE_3N_R2M_R5_GATE4_PREFLIGHT_RUNBOOK_DESIGN.md` (Section 3, Check 6), the runbook proposed verifying:
> *"Confirm WooCommerce attributes (`pa_color`, `pa_size`, `pa_material`) and their 18 terms exist before product import."*

#### Physical Evidence Audit:
1. **Working CSV (`data-preparation/woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv`):**
   - Header inspection reveals columns:
     - `Attribute 1 name`: `Color` (Attribute 1 global: `1`)
     - `Attribute 2 name`: `Size` (Attribute 2 global: `1`)
     - `Attribute 3 name`: `Brand` (Attribute 3 global: `1`)
   - The word `Material` does **not** appear anywhere in the CSV headers or data rows.
2. **Current Importer Source (`run_controlled_import.php`):**
   - Lines 1121–1126 explicitly read metadata keys:
     ```php
     $attrs['pa_color'] = get_post_meta( $id, 'attribute_pa_color', true );
     $attrs['pa_size']  = get_post_meta( $id, 'attribute_pa_size', true );
     $attrs['pa_brand'] = get_post_meta( $id, 'attribute_pa_brand', true );
     ```
   - No reference to `material` exists in `run_controlled_import.php`.
3. **Stage 1E Authoritative Reports:**
   - `docs/stage_1e_3b_1_global_attribute_creation_report.md`: Attributes created are `Color` (ID 1, `pa_color`), `Size` (ID 2, `pa_size`), and `Brand` (ID 3, `pa_brand`).
   - `docs/stage_1e_3b_2_attribute_terms_categories_creation_report.md`: 11 attribute terms were created across `pa_color` (3 terms), `pa_size` (3 terms), and `pa_brand` (5 terms).
   - `docs/STAGE_1E_3D_1_MAPPING_ROLLBACK_READINESS_REPORT.md` (Section 4): Specifically resolved the brand taxonomy architecture: `Brand` maps to `pa_brand` (Attribute 3), while `product_brand` remains with 0 terms.

#### Reconciled Verdict:
The reference to `pa_material` in R2M-R5 was an unvetted typographical error. The authoritative global attributes are:
- **`pa_color`** (Slug: `color`, Label: `Color`)
- **`pa_size`** (Slug: `size`, Label: `Size`)
- **`pa_brand`** (Slug: `brand`, Label: `Brand`)

### 3.2 Four Architectural Layers of Attributes & Terms
To prevent future operational confusion, the four distinct layers of attribute data in WooCommerce are formally distinguished:

```
┌──────────────────────────────────────────────────────────────────────────────────┐
│ LAYER 1: Global Attribute Definitions                                           │
│ Table: wp_woocommerce_attribute_taxonomies                                       │
│ Records: attribute_id, attribute_name ('color', 'size', 'brand'), label, type   │
└────────────────────────────────────────┬─────────────────────────────────────────┘
                                         │ Registers pa_* taxonomies on WP init
┌────────────────────────────────────────▼─────────────────────────────────────────┐
│ LAYER 2: Attribute Taxonomy Terms                                                │
│ Tables: wp_terms & wp_term_taxonomy                                              │
│ Records: pa_color (3: Black, Brown, White)                                       │
│          pa_size  (3: S, M, L)                                                   │
│          pa_brand (5: LuxeVibe, Minimalia, NordicLiving, OakCraft, UrbanoHaus)   │
└────────────────────────────────────────┬─────────────────────────────────────────┘
                                         │ Term-to-Post association on import
┌────────────────────────────────────────▼─────────────────────────────────────────┐
│ LAYER 3: Product Taxonomy Relationships                                          │
│ Table: wp_term_relationships                                                     │
│ Records: links post_id to term_taxonomy_id (for both pa_* terms and product_cat) │
└────────────────────────────────────────┬─────────────────────────────────────────┘
                                         │ Metadata storage on individual post objects
┌────────────────────────────────────────▼─────────────────────────────────────────┐
│ LAYER 4: Product-Specific Attribute Postmeta                                     │
│ Table: wp_postmeta                                                               │
│ Parent Product: _product_attributes (serialized array defining used attributes)  │
│ Variation Post: attribute_pa_color, attribute_pa_size, attribute_pa_brand        │
└──────────────────────────────────────────────────────────────────────────────────┘
```

### 3.3 Exact Term & Category Count Inventory

| Taxonomy Slug | Taxonomy Type | Terms in Database | Specific Expected Term Names / Slugs |
|---|---|---|---|
| **`pa_color`** | WooCommerce Global Attribute | **3** | `Black` (`black`), `Brown` (`brown`), `White` (`white`) |
| **`pa_size`** | WooCommerce Global Attribute | **3** | `S` (`s`), `M` (`m`), `L` (`l`) |
| **`pa_brand`** | WooCommerce Global Attribute | **5** | `LuxeVibe` (`luxevibe`), `Minimalia` (`minimalia`), `NordicLiving` (`nordicliving`), `OakCraft` (`oakcraft`), `UrbanoHaus` (`urbanohaus`) |
| **`product_cat`** | WooCommerce Product Category | **8** | 7 dataset categories (`Beds`, `Chairs`, `Decoration`, `Lighting`, `Sofas`, `Storage`, `Tables`) + 1 default (`Uncategorized`) |
| **`product_brand`** | WooCommerce Core Taxonomy | **0** | Pre-registered in code, 0 terms created (decision locked in Stage 1E.2.1) |

---

## 4. AUDIT OF PROPOSED READ-ONLY QUERIES

Every query proposed in R2M-R5 was audited for technical rigor, false-positive resistance, and completeness.

> [!IMPORTANT]
> All queries documented below are **NOT EXECUTED** during this audit.

---

### Query 1: Active Database Name, Port, Hostname & Version
- **Original Proposed SQL [NOT EXECUTED]:**
  ```sql
  SELECT DATABASE() AS active_db;
  SELECT @@port AS active_port, @@hostname AS host_name;
  ```
- **Audit Assessment:** Correct, but can be consolidated into a single atomic inspection row.
- **Improved Consolidated Query [NOT EXECUTED]:**
  ```sql
  SELECT 
      DATABASE() AS active_db,
      @@port AS active_port,
      @@hostname AS host_name,
      @@version AS server_version,
      @@datadir AS data_directory;
  ```
- **What It Proves:** Proves the active connection's database, port, host, version, and physical data directory path.
- **What It Does NOT Prove:** Does not prove the tables inside the database are uncorrupted or match the expected schema.
- **Expected Result:** `active_db = 'local'`, `active_port = 10011`, `data_directory` contains LocalWP container path.
- **Stop Condition:** `active_db != 'local'` OR `active_port != 10011`.
- **Live Connection Required:** YES.

---

### Query 2: Table Prefix & Required Table Inventory (F-R5R1-02 Correction)
- **Original Proposed SQL [NOT EXECUTED]:**
  ```sql
  SELECT DISTINCT SUBSTRING(table_name, 1, 3) AS table_prefix 
  FROM information_schema.tables 
  WHERE table_schema = 'local' AND table_name LIKE 'wp_%' 
  LIMIT 1;
  ```
- **Defect Identified:** Circular logic. It filters by `LIKE 'wp_%'`, which guarantees that if any table starts with `wp_`, the check passes. It fails to verify that the required WordPress and WooCommerce tables actually exist.
- **Corrected SQL [NOT EXECUTED]:**
  ```sql
  SELECT 
      table_name 
  FROM information_schema.tables 
  WHERE table_schema = 'local' 
    AND table_name IN (
        'wp_posts', 
        'wp_postmeta', 
        'wp_options', 
        'wp_terms', 
        'wp_term_taxonomy', 
        'wp_term_relationships', 
        'wp_woocommerce_attribute_taxonomies'
    )
  ORDER BY table_name;
  ```
- **What It Proves:** Proves all 7 mandatory core and WooCommerce schema tables exist under the `'wp_'` prefix in database `'local'`.
- **What It Does NOT Prove:** Does not prove row counts or table contents.
- **Expected Result:** Exactly 7 rows returned, matching all 7 specified table names.
- **Stop Condition:** Row count != 7 (any required table missing).
- **Live Connection Required:** YES.

---

### Query 3: WordPress Site & Home URL
- **Original Proposed SQL [NOT EXECUTED]:**
  ```sql
  SELECT option_name, option_value 
  FROM wp_options 
  WHERE option_name IN ('siteurl', 'home');
  ```
- **Audit Assessment:** Verified and sound.
- **What It Proves:** Proves that WordPress routing options point to LocalWP's local HTTP listener.
- **What It Does NOT Prove:** Does not prove the web server (Nginx/Apache) is currently listening on port 10010.
- **Expected Result:** Both rows return `http://localhost:10010`.
- **Stop Condition:** Value differs from `http://localhost:10010` or row count != 2.
- **Live Connection Required:** YES.

---

### Query 4: Global Attributes & Taxonomy Terms (F-R5R1-01 Correction)
- **Original Proposed SQL [NOT EXECUTED]:** Contained `pa_material`.
- **Corrected SQL [NOT EXECUTED]:**
  ```sql
  -- Layer 1: Global Attribute Definitions
  SELECT attribute_id, attribute_name, attribute_label, attribute_type 
  FROM wp_woocommerce_attribute_taxonomies 
  ORDER BY attribute_id;

  -- Layer 2: Taxonomy Term Counts
  SELECT tt.taxonomy, COUNT(t.term_id) AS term_count 
  FROM wp_terms t 
  INNER JOIN wp_term_taxonomy tt ON t.term_id = tt.term_id 
  WHERE tt.taxonomy IN ('pa_color', 'pa_size', 'pa_brand', 'product_cat')
  GROUP BY tt.taxonomy
  ORDER BY tt.taxonomy;
  ```
- **What It Proves:** Proves attributes `color`, `size`, `brand` are defined, and that exact term counts exist.
- **What It Does NOT Prove:** Does not prove terms are assigned to products (products should be 0).
- **Expected Result:**
  - Attributes: 3 rows (`color`, `size`, `brand`).
  - Term counts: `pa_brand = 5`, `pa_color = 3`, `pa_size = 3`, `product_cat = 8` (7 dataset categories + 1 `Uncategorized`).
- **Stop Condition:** Missing attribute row, or any term count mismatch.
- **Live Connection Required:** YES.

---

### Query 5: Product & Variation Baseline Counts
- **Original Proposed SQL [NOT EXECUTED]:**
  ```sql
  SELECT post_type, COUNT(*) AS count 
  FROM wp_posts 
  WHERE post_type IN ('product', 'product_variation') 
  GROUP BY post_type;
  ```
- **Audit Assessment:** Sound. To detect drafts, trashed items, or soft-deleted products, an unrestricted check is added.
- **Comprehensive SQL [NOT EXECUTED]:**
  ```sql
  SELECT post_type, post_status, COUNT(*) AS count 
  FROM wp_posts 
  WHERE post_type IN ('product', 'product_variation') 
  GROUP BY post_type, post_status;
  ```
- **What It Proves:** Proves whether any products exist in the catalog under any status (`publish`, `draft`, `trash`).
- **What It Does NOT Prove:** Does not inspect other post types (e.g. pages, posts, attachments).
- **Expected Result:** 0 rows returned (empty result set).
- **Stop Condition:** Any row returned with count > 0.
- **Live Connection Required:** YES.

---

### Query 6: Media Attachment ID 5 Integrity (F-R5R1-04 Correction)
- **Original Proposed SQL [NOT EXECUTED]:** Checked only `wp_posts`.
- **Corrected Comprehensive SQL [NOT EXECUTED]:**
  ```sql
  SELECT 
      p.ID, p.post_type, p.post_mime_type, p.guid,
      pm.meta_value AS attached_file
  FROM wp_posts p
  LEFT JOIN wp_postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attached_file'
  WHERE p.ID = 5;
  ```
- **What It Proves:** Proves Attachment ID 5 exists, is of type `attachment`, has MIME type `image/webp`, and has metadata pointing to `woocommerce-placeholder.webp`.
- **What It Does NOT Prove:** Does not prove the physical file exists on disk in `wp-content/uploads/`.
- **Expected Result:** Exactly 1 row with `post_type = 'attachment'`, `post_mime_type = 'image/webp'`, `attached_file` containing `woocommerce-placeholder.webp`.
- **Stop Condition:** 0 rows returned, or post_type != 'attachment'.
- **Live Connection Required:** YES.

---

### Query 7: Manifest & Tagged Orphan Audit
- **Original Proposed SQL & PowerShell [NOT EXECUTED]:**
  ```sql
  SELECT post_id, meta_key, meta_value 
  FROM wp_postmeta 
  WHERE meta_key = '_controlled_import_run_id';
  ```
  ```powershell
  Get-ChildItem -Path "C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\backups\manifests" -Filter "*.json"
  ```
- **Audit Assessment:** Sound and necessary.
- **What It Proves:** Proves that no leftover tagged objects from a previous crashed run exist in the database.
- **What It Does NOT Prove:** Does not detect Window 2 untagged orphans (which have no postmeta tag).
- **Expected Result:** SQL returns 0 rows. PowerShell returns 0 active/incomplete manifest files.
- **Stop Condition:** Any row returned with `_controlled_import_run_id`, or any un-reconciled manifest found.
- **Live Connection Required:** YES (for SQL check).

---

### Query 8: WooCommerce Coming Soon Status
- **Original Proposed SQL [NOT EXECUTED]:**
  ```sql
  SELECT option_name, option_value 
  FROM wp_options 
  WHERE option_name = 'woocommerce_coming_soon';
  ```
- **Audit Assessment:** Sound.
- **What It Proves:** Proves whether the storefront maintenance lock is active in options.
- **What It Does NOT Prove:** Does not prove whether admin bypass cookies are active in a specific browser.
- **Expected Result:** `option_value = 'yes'`.
- **Stop Condition:** `option_value != 'yes'` or row missing.
- **Live Connection Required:** YES.

---

## 5. STATIC AUDIT OF IMPORTER `verify` ACTION

The user request explicitly instructed:
> *"Statically inspect the actual current importer source and its full call paths. Determine whether the verify action can write files or manifests, create directories, update options or metadata, trigger hooks or plugin side effects during bootstrap, or execute SQL other than reads."*

### 5.1 Call Path Trace for `php run_controlled_import.php verify`

1. **CLI Dispatcher Entry (Lines 1637–1650):**
   - Validates `$argc === 2`. Rejects extra arguments. (Zero side effects).
2. **Path Resolver Initialization (Line 1678):**
   - `Controlled_Import_Path_Resolver::__construct()` calls `resolve_roots()`.
   - Performs `realpath()`, `is_file()`, `is_dir()`, `file_exists()`.
   - **Does NOT write files or create directories** (`resolve_manifest_write_destination()` is NOT called).
3. **WordPress Bootstrap (Line 1686):**
   - Calls `bootstrap_wordpress_runtime_safely($resolver)`.
   - Defines `WP_USE_THEMES = false`, `DOING_CRON = true`.
   - Executes `require_once $wp_load;`.
   - **POTENTIAL SIDE-EFFECT SURFACE IDENTIFIED:**
     - `wp-load.php` bootstraps the WordPress core runtime, initializes active plugins (including WooCommerce), and fires WordPress lifecycle hooks (`plugins_loaded`, `init`, `wp_loaded`).
     - While `WP_USE_THEMES` is set to `false`, plugins loaded in the environment may execute transient cleanups, scheduled cron task evaluations, or internal option updates during bootstrap.
     - `bootstrap_wordpress_runtime_safely()` executes:
       - `SELECT DATABASE()` via `$wpdb->get_var()` (Read-only query).
       - Compares active DB to `'local'`.
       - Compares table prefix to `'wp_'`.
       - Checks `class_exists('WooCommerce')`.
4. **Harness Instantiation (Line 1695):**
   - `new Controlled_Import_Harness(null, null, false, null, null, $resolver)`.
   - Sets property paths. **Does NOT write manifests or attach hooks** (`attach_hooks()` is only called in `run_import()`).
5. **Preflight Verification Method (Line 1696):**
   - Calls `$harness->preflight_verify()`:
     - `ensure_resource_limits()`: Calls `ini_set('memory_limit', '512M')` if below 256MB. (Runtime memory setting only, zero DB/disk writes).
     - Working CSV verification: Reads CSV via `file_get_contents()` and verifies SHA-256 against `LOCKED_WORKING_CSV_HASH`. (Read-only).
     - Target DB & prefix check: Re-checks database name and prefix. (Read-only).
     - Duplicate SKU check:
       - Calls `extract_csv_skus()` which opens CSV via `fopen(..., 'r')`, reads headers and rows, closes handle. (Read-only).
       - Prepares and executes:
         ```sql
         SELECT post_id, meta_value FROM wp_postmeta WHERE meta_key = '_sku' AND meta_value IN (...)
         ```
       - Uses `$wpdb->get_results(..., ARRAY_A)`. (Read-only `SELECT`).
       - If conflicts found, throws `RuntimeException`.

### 5.2 Verdict on `verify` Read-Only Status
- **Within Importer Code:** The importer's own code in `preflight_verify()` is **100% read-only**. It contains zero `INSERT`, `UPDATE`, `DELETE`, `DROP`, or `file_put_contents()` calls.
- **Within Bootstrap Boundary:** Because it invokes `require_once $wp_load`, WordPress core and active plugins are loaded into memory. While standard for WP-CLI tools, it cannot be statically guaranteed that third-party plugins or WordPress transients will not perform background writes during bootstrap.
- **Auditor Recommendation:**
  1. The **primary preflight verification** must be performed via direct, independent MySQL queries (as detailed in Section 4 above) using a read-only script or SQL client that does NOT bootstrap WordPress.
  2. The importer's `verify` command should only be executed as the final verification step immediately before import, after independent checks have cleared.

---

## 6. RECOVERY READINESS AUDIT

### 6.1 Classification of Recovery Evidence

| Snapshot Artifact | File Existence | Hash Verified | Structural SQL Inspection | Isolated Restore Rehearsal | Recovery of Current Post-1F State |
|---|---|---|---|---|---|
| `stage_1e_gate2_pre_import_20261016_223502.sql` | **CONFIRMED** (1,236,031 B) | **CONFIRMED** (`9152E46BB462...`) | **CONFIRMED** (52 tables, zero DB prefix in DDL) | **CONFIRMED** (Gate 3I on port 13306) | **FAILED / NOT CAPABLE** (Pre-dates Stage 1F; restores would erase storefront block templates!) |
| `stage_1e_pre_import_snapshot.sql` | **CONFIRMED** (1,336,511 B) | **CONFIRMED** (`A9617E0173ED...`) | **CONFIRMED** | **NOT REHEARSED** | **NOT CAPABLE** (Pre-attributes baseline) |
| `gate4_snapshot_t0_<TIMESTAMP>.sql` | **DOES NOT EXIST** | N/A | N/A | N/A | **MANDATORY PREREQUISITE** (Must be created and verified before Gate 4) |

### 6.2 The Window 2 Untagged Orphan Problem & Software Rollback Limits

```
Execution Sequence in wp_insert_post() vs update_post_meta():

[Step A: wp_insert_post()] ────► Post inserted into wp_posts (ID: 105)
                                     │
      CRASH WINDOW 2 ───────────────►├─ (Crash: SIGKILL, OOM, power loss)
                                     │
[Step B: update_post_meta()] ──► Post tagged in wp_postmeta (_controlled_import_run_id)
[Step C: flush_manifest()]   ──► Post recorded in manifest JSON on disk
```

#### Technical Reality:
- If a crash occurs at **Window 2**, Post ID 105 exists in `wp_posts`, but has **zero metadata tags** and is **absent from the manifest file**.
- **Software Rollback Behavior:** The importer's rollback mechanism operates strictly **FAIL-CLOSED**. It deletes posts identified in the manifest or tagged with `_controlled_import_run_id`. It deliberately refuses to guess or delete untagged objects via heuristic scans (such as matching SKU or creation timestamp) because doing so risks deleting legitimate user or administrator data.
- **Recovery Requirement:** Therefore, **software rollback cannot recover from a Window 2 crash**. The only deterministic recovery for Window 2 is a full database restore from a verified T-0 snapshot.

### 6.3 Snapshot Overwrite Risk & Administrative Freeze
- A snapshot restore executes `DROP TABLE IF EXISTS` across all 52 tables.
- Any legitimate changes committed after the snapshot timestamp (user registrations, admin edits, option adjustments) will be **permanently wiped**.
- **Operational Requirement:** The storefront must remain locked under `woocommerce_coming_soon = 'yes'`, and an **administrative freeze** must be enforced from the moment the T-0 snapshot is captured until the import is reconciled.

---

## 7. LEAST-PRIVILEGE DATABASE ACCOUNT REVIEW

### 7.1 Separation of Importer Privileges vs Core WordPress Operations

| Subsystem | Component | Required Privileges | Notes |
|---|---|---|---|
| **Controlled Importer** | `run_controlled_import.php` | `SELECT`, `INSERT`, `UPDATE`, `DELETE` | Operates strictly via DML on `local.*`. Requires ZERO `CREATE`, `DROP`, `ALTER`, `GRANT`. |
| **WordPress Core & Plugins** | `wp-load.php` / WooCommerce | `SELECT`, `INSERT`, `UPDATE`, `DELETE` | May occasionally create temporary tables or execute schema migrations if major updates are triggered. |
| **Snapshot Backup** | `mysqldump` CLI | `SELECT`, `LOCK TABLES`, `SHOW VIEW` | Standard backup privileges on `local`. |
| **Snapshot Restore** | `mysql` CLI | `CREATE`, `DROP`, `ALTER`, `INSERT` | Full DDL/DML required to drop and rebuild tables. |

### 7.2 Status in LocalWP
- **Active User:** LocalWP connects as `root` with all administrative privileges over `127.0.0.1:10011`.
- **Restricted Account Provisioning:** Provisioning and testing a least-privilege user (e.g., `atelier_dml` with only `SELECT, INSERT, UPDATE, DELETE`) has **NOT** been performed in this workspace.
- **Status:** Marked **`OUTSTANDING / UNVERIFIED`**. It must not be assumed that a restricted user exists or that WordPress core will function without warning under restricted DML-only grants without explicit testing.

---

## 8. RESOLUTION & COMPANION DELIVERABLE

To maintain strict audit history and adhere to Section 7 of the user prompt:
1. The original report `docs/STAGE_3N_R2M_R5_GATE4_PREFLIGHT_RUNBOOK_DESIGN.md` remains intact as the V1 draft.
2. The revised runbook incorporating all audited corrections is generated as:  
   [`docs/STAGE_3N_R2M_R5_GATE4_PREFLIGHT_RUNBOOK_DESIGN_V2.md`](file:///C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/app/public/wp-content/plugins/docs/STAGE_3N_R2M_R5_GATE4_PREFLIGHT_RUNBOOK_DESIGN_V2.md).

---

## 9. CONCLUSION & FINAL GATE DECISION

- **Audit Evaluation:** **`PASS WITH CORRECTIONS RECONCILED`**  
  All discrepancies between documentation, source code, and dataset have been rigorously reconciled.
- **Gate 4 Status:**  
  **`MANDATORY STOP — GATE 4 REMAINS STRICTLY BLOCKED`**  
  No production import, live database connection, or snapshot restoration is authorized by this audit.
