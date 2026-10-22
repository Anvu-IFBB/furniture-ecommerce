# STAGE 1E.3D.1 — IMPORT MAPPING & ROLLBACK READINESS AUDIT REPORT

> **STAGE IDENTIFIER:** STAGE 1E.3D.1  
> **STAGE TYPE:** Strict Read-Only Import Mapping & Rollback Architecture Audit  
> **EXECUTION TIMESTAMP:** 2026-10-16 19:05:00+07:00  
> **EXECUTION ENVIRONMENT:** Local by Flywheel on Windows 11 (MySQL 8.4.0 on `127.0.0.1:10011`, PHP 8.2.29, WordPress 7.1.3, WooCommerce 11.2.1)  
> **STAGE AUTHORITY:** Strictly Read-Only Inspection. **NO REAL IMPORT EXECUTED. ZERO DATABASE MUTATIONS.**  
> **OVERALL AUDIT OUTCOME:** **`PASS — ELIGIBLE FOR HUMAN REVIEW`**

---

## 1. Executive Summary

This audit resolves all outstanding technical and architectural ambiguities identified in prior reports regarding:
1. Ground-truth environment versions (resolving the WordPress 7.1.3 vs 6.8-alpha discrepancy).
2. Complete, line-by-line static analysis of WooCommerce 11.2.1 Core Importer mapping across all 28 dataset headers.
3. Variable product and variation attribute linking mechanics (`set_variation_data()`, `parse_relative_field()`).
4. Brand taxonomy architecture: reconciling global attribute `pa_brand` vs WooCommerce Core taxonomy `product_brand`.
5. Rollback mechanisms: defining safe manifest-based deletion versus pre-import database snapshot recovery.

### Key Conclusions:
- **Environment Ground Truth (VERIFIED):** Active WordPress version is **`7.1.3`** (`wp-includes/version.php`, DB version 61833). Active WooCommerce version is **`11.2.1`**. Active theme is **`twentytwentyfive-child`** (Parent: `twentytwentyfive`). Database engine is **MySQL Community Server 8.4.0**.
- **Dataset Column Mapping (VERIFIED):** All 28 headers of the working CSV map 100% natively without custom rules via `WC_Product_CSV_Importer_Controller::auto_map_columns()`.
- **Brand Architecture Resolved (VERIFIED):** The dataset does **not** contain a standalone `Brands` column. Brand is structured as **Attribute 3** (`Attribute 3 name = Brand`, `Attribute 3 global = 1`), which maps directly to pre-created global attribute `pa_brand` (terms: `LuxeVibe`, `Minimalia`, `NordicLiving`, `OakCraft`, `UrbanoHaus`). Zero terms exist in taxonomy `product_brand`.
- **Parent-Child Integrity (VERIFIED):** All 10 variable parents are physically ordered **before** their respective 42 variations in the CSV. `parse_relative_field()` successfully resolves parent SKUs via `wc_get_product_id_by_sku()`, ensuring proper variation attribute binding without orphan creation.
- **Rollback Readiness (VERIFIED):** Stale snapshot `stage_1e_pre_import_snapshot.sql` must **not** be restored. Primary recovery protocol is **Scoped Manifest Rollback** (deleting imported IDs by manifest), backed by a fresh pre-import snapshot taken in Gate 2.

---

## 2. Item A — Environment Ground Truth Verification

Direct inspection of files and database options yielded the following verified facts:

| Component | Source of Evidence | Verified Value | Discrepancy Reconciliation |
| :--- | :--- | :--- | :--- |
| **WordPress Core** | `wp-includes/version.php` | **`7.1.3`** (DB Version `61833`) | Prior mention of `6.8-alpha` in Stage 1E.3D was an unverified assumption. `7.1.3` is verified ground truth. |
| **WooCommerce** | `plugins/woocommerce/woocommerce.php` line 6 | **`11.2.1`** | Confirmed exact version. |
| **PHP Runtime** | Local lightning-services binary | **`8.2.29`** (win64) | Invocation via Local PHP CLI executable. |
| **Database Engine** | MySQL `SELECT VERSION()` | **`8.4.0`** (MySQL Community Server GPL) | Running on `127.0.0.1:10011`. |
| **Active Theme** | `wp_options` table: `stylesheet` / `template` | `twentytwentyfive-child` / `twentytwentyfive` | Child theme active. |
| **Working CSV** | File existence and read access | `data-preparation/woocommerce_..._1e_3c.csv` | Exists, readable, outside web root. |

---

## 3. Item B — File Identity & Structural Reconciliation

Both CSV files were re-verified on disk:

| File Property | Original Source CSV | Prepared Working CSV | Status |
| :--- | :--- | :--- | :---: |
| **Path** | `.../plugins/woocommerce_furniture_dataset_22_products.csv` | `.../data-preparation/..._working_stage_1e_3c.csv` | **VERIFIED** |
| **Size** | `26,176` bytes | `25,712` bytes | **VERIFIED** |
| **SHA-256** | `276809899064949CB8D45B8EF7523B8C2913780F0649650EA4EB494FB74894F6` | `D2C8566F334A767887F57C4A15EA456CFC5E117767FCFE46C599CC754BC2BBA0` | **MATCH** |
| **Encoding** | UTF-8 (No BOM) | UTF-8 (No BOM) | **VERIFIED** |
| **Total Rows** | 65 (1 header + 64 data records) | 65 (1 header + 64 data records) | **VERIFIED** |
| **Columns** | 28 columns | 28 columns | **VERIFIED** |

### Structural Metrics:
- **Parent Products:** 22 (12 simple, 10 variable).
- **Variations:** 42 records (each linked to an existing parent SKU).
- **SKUs:** Exactly 64 unique SKUs (both exact string and case-insensitive unique).
- **Total Stock:** Exactly 449 units (Simple: 168, Variations: 281, Parents: 0).
- **Taxonomy Entities:** 7 categories, 5 brands, 3 colors, 3 sizes.
- **Cell Differences:** Exactly 5 cell modifications across 1,792 cells, all strictly confined to column `Images` (Rows 7, 41, 42, 43, 45). Zero non-image differences.

---

## 4. Item C — Exhaustive 28-Column Importer Mapping Audit

Code evidence audited directly from WooCommerce 11.2.1 Core:
- `includes/admin/importers/class-wc-product-csv-importer-controller.php`
- `includes/import/class-wc-product-csv-importer.php`
- `includes/import/abstract-wc-product-importer.php`

| # | CSV Header | Target Mapping Key | Target WC Property / Field | Auto-Mapped? | Normalization / Conversion | Validation & Error Handling | Source Code Evidence |
| :-: | :--- | :--- | :--- | :-: | :--- | :--- | :--- |
| **1** | `Type` | `type` | `WC_Product::set_type()` / class instantiation | **YES** | Lowercased; splits virtual/downloadable; defaults to `simple` | Validated against `WC_Admin_Exporters::get_product_types()`; throws `invalid_type` if unknown | `controller.php:779`, `importer.php:986-997`, `abstract.php:180` |
| **2** | `SKU` | `sku` | `WC_Product::set_sku()` | **YES** | Trimmed string | Checked for uniqueness via `wc_get_product_id_by_sku()`; skips on duplicate if `update_existing=false` | `controller.php:780`, `abstract.php:303`, `importer.php:1432` |
| **3** | `Name` | `name` | `WC_Product::set_name()` | **YES** | Sanitized text string | Handled by `set_props()` | `controller.php:781`, `abstract.php:344` |
| **4** | `Published` | `published` | `WC_Product::set_status()` | **YES** | `1` -> `publish`, `0` -> `private`, `-1` -> `draft` | Variations with `-1` coerced to `private` or `publish` | `controller.php:782`, `importer.php:1000-1020` |
| **5** | `Is featured?` | `featured` | `WC_Product::set_featured()` | **YES** | Converted to boolean | Handled by `set_props()` | `controller.php:783`, `importer.php:418` |
| **6** | `Visibility in catalog` | `catalog_visibility` | `WC_Product::set_catalog_visibility()` | **YES** | Lowercased (`visible`, `catalog`, `search`, `hidden`) | Handled by `set_props()` | `controller.php:784`, `abstract.php:344` |
| **7** | `Short description` | `short_description` | `WC_Product::set_short_description()` | **YES** | Allowed HTML preserved via `wp_kses_post()` | Handled by `parse_description_field()` | `controller.php:785`, `importer.php:791` |
| **8** | `Description` | `description` | `WC_Product::set_description()` | **YES** | Allowed HTML preserved via `wp_kses_post()` | Handled by `parse_description_field()` | `controller.php:786`, `importer.php:791` |
| **9** | `Regular price` | `regular_price` | `WC_Product::set_regular_price()` | **YES** | Parsed as numeric float string | Non-numeric cleaned | `controller.php:807`, `importer.php:438` |
| **10** | `Sale price` | `sale_price` | `WC_Product::set_sale_price()` | **YES** | Parsed as numeric float string | Must be < regular price; handled in WC data store | `controller.php:806`, `importer.php:438` |
| **11** | `In stock?` | `stock_status` | `WC_Product::set_stock_status()` | **YES** | `1` -> `instock`, `0` -> `outofstock`, `backorder` | Coerced to valid enum values | `controller.php:791`, `importer.php:1032-1038` |
| **12** | `Stock` | `stock_quantity` | `WC_Product::set_stock_quantity()`, `set_manage_stock()` | **YES** | Parsed integer/float; sets `manage_stock = true` when numeric | Negative stock prevented by core schema | `controller.php:792`, `importer.php:1022-1029` |
| **13** | `Categories` | `category_ids` | `wp_set_post_terms( ..., 'product_cat' )` | **YES** | Exploded by comma; trims whitespace; hierarchy via `>` | Resolves term ID; creates if missing; pre-created in 1E.3B.2 | `controller.php:808`, `importer.php:500-551` |
| **14** | `Tags` | `tag_ids` | `wp_set_post_terms( ..., 'product_tag' )` | **YES** | Exploded by comma; trims whitespace | Resolves or creates tag term | `controller.php:809`, `importer.php:553-582` |
| **15** | `Images` | `images` | `WC_Product::set_image_id()`, `set_gallery_image_ids()` | **YES** | Exploded by comma; first -> featured, rest -> gallery; empty -> `array()` | Download via `get_attachment_id_from_url()`; HTTP 404 aborts row | `controller.php:811`, `importer.php:640, 960-983`, `abstract.php:654-730` |
| **16** | `Parent` | `parent_id` | `WC_Product_Variation::set_parent_id()` | **YES** | Resolved via `parse_relative_field()` by parent SKU | `wc_get_product_id_by_sku()`; throws error if parent not found | `controller.php:814`, `importer.php:263-324`, `abstract.php:538-549` |
| **17** | `Attribute 1 name` | `attributes:name1` | `$data['raw_attributes'][0]['name']` | **YES** | Special column regex `Attribute %d name` | Trimmed string | `controller.php:846, 880`, `importer.php:1062` |
| **18** | `Attribute 1 value(s)` | `attributes:value1` | `$data['raw_attributes'][0]['value']` | **YES** | Special column regex; exploded by `, ` | Sanitized term list | `controller.php:848, 880`, `importer.php:1068` |
| **19** | `Attribute 1 visible` | `attributes:visible1` | `$data['raw_attributes'][0]['visible']` | **YES** | Special column regex; boolean conversion (`1` -> `true`) | Coerced to 0 or 1 | `controller.php:850, 880`, `importer.php:1076` |
| **20** | `Attribute 1 global` | `attributes:taxonomy1` | `$data['raw_attributes'][0]['taxonomy']` | **YES** | Special column regex; boolean conversion (`1` -> `true`) | Directs WC to global taxonomy `pa_...` | `controller.php:852, 880`, `importer.php:1072` |
| **21** | `Attribute 2 name` | `attributes:name2` | `$data['raw_attributes'][1]['name']` | **YES** | Special column regex `Attribute %d name` | Trimmed string | `controller.php:846, 880`, `importer.php:1062` |
| **22** | `Attribute 2 value(s)` | `attributes:value2` | `$data['raw_attributes'][1]['value']` | **YES** | Special column regex; exploded by `, ` | Sanitized term list | `controller.php:848, 880`, `importer.php:1068` |
| **23** | `Attribute 2 visible` | `attributes:visible2` | `$data['raw_attributes'][1]['visible']` | **YES** | Special column regex; boolean conversion (`1` -> `true`) | Coerced to 0 or 1 | `controller.php:850, 880`, `importer.php:1076` |
| **24** | `Attribute 2 global` | `attributes:taxonomy2` | `$data['raw_attributes'][1]['taxonomy']` | **YES** | Special column regex; boolean conversion (`1` -> `true`) | Directs WC to global taxonomy `pa_...` | `controller.php:852, 880`, `importer.php:1072` |
| **25** | `Attribute 3 name` | `attributes:name3` | `$data['raw_attributes'][2]['name']` | **YES** | Special column regex `Attribute %d name` | Matches `Brand` | `controller.php:846, 880`, `importer.php:1062` |
| **26** | `Attribute 3 value(s)` | `attributes:value3` | `$data['raw_attributes'][2]['value']` | **YES** | Special column regex; exploded by `, ` | Matches 5 pre-created brand terms | `controller.php:848, 880`, `importer.php:1068` |
| **27** | `Attribute 3 visible` | `attributes:visible3` | `$data['raw_attributes'][2]['visible']` | **YES** | Special column regex; boolean conversion (`1` -> `true`) | Coerced to 0 or 1 | `controller.php:850, 880`, `importer.php:1076` |
| **28** | `Attribute 3 global` | `attributes:taxonomy3` | `$data['raw_attributes'][2]['taxonomy']` | **YES** | Special column regex; boolean conversion (`1` -> `true`) | Directs WC to global taxonomy `pa_brand` | `controller.php:852, 880`, `importer.php:1072` |

---

## 5. Item D — Variable Products & Global Attributes Architecture

### 5.1. Order of Execution (Parent vs. Variation)
- In `abstract-wc-product-importer.php` line 547:
  ```php
  if ( ! $parent ) {
      return new WP_Error( 'woocommerce_product_importer_missing_variation_parent_id', ... );
  }
  ```
  If a variation row is encountered before its parent product exists in the database, the importer aborts that variation row immediately with error `woocommerce_product_importer_missing_variation_parent_id`.
- **Dataset Verification:** In our CSV, all 10 variable parent products appear **before** their respective variation rows. For example, `FURN-013` (parent) is on Row 14, followed by its variations on Rows 15–20. Parent SKUs are indexed prior to variation processing.

### 5.2. Parent SKU Resolution
- `parse_relative_field()` (lines 303–307 of `class-wc-product-csv-importer.php`) resolves parent references via:
  ```php
  $id = wc_get_product_id_by_sku( $value );
  if ( $id ) {
      return $id;
  }
  ```
  Parent references in the CSV use the parent SKU (e.g., `FURN-013`), resolving directly to the parent product ID.

### 5.3. Attribute Linking to Variations
- In `abstract-wc-product-importer.php` lines 556–598 (`set_variation_data()`):
  1. The importer fetches `$parent_attributes` from the parent product.
  2. For each attribute in the variation row, it checks `isset( $parent_attributes[ $attribute_name ] )`.
  3. If the parent attribute is a taxonomy (`pa_color`, `pa_size`), it resolves the term slug via `get_term_by( 'name', $attribute_value, $taxonomy_name )`.
  4. It assigns the term slug to the variation attribute array: `$attributes[ $attribute_key ] = $term->slug`.
- **Pre-Created Term Alignment:** Because `pa_color` (`Black`, `Brown`, `White`) and `pa_size` (`S`, `M`, `L`) were pre-created with matching slugs, `get_term_by()` resolves the exact terms without on-the-fly term creation.

---

## 6. Item E — Brand Taxonomy Architecture Decision

### 6.1. Analysis of Current Database & Runtime State
1. **Database Taxonomies:**
   - Active terms exist in `pa_brand` (Term IDs 22–26: `LuxeVibe`, `Minimalia`, `NordicLiving`, `OakCraft`, `UrbanoHaus`).
   - Zero terms exist in taxonomy `product_brand`.
2. **CSV Column Structure:**
   - The CSV does **not** contain a standalone column named `Brands` or `Brand`.
   - Brand data is provided exclusively in columns 25–28:
     - `Attribute 3 name` = `Brand`
     - `Attribute 3 value(s)` = `NordicLiving`, `Minimalia`, etc.
     - `Attribute 3 visible` = `1`
     - `Attribute 3 global` = `1`
3. **Importer Mechanics:**
   - WooCommerce Core CSV Importer does not have a dedicated `product_brand` column handler.
   - Headers `Attribute 3 name`, `value(s)`, `global` are parsed by `expand_data()` and mapped to global attribute taxonomy `pa_brand`.
   - In `abstract-wc-product-importer.php` line 431, `get_attribute_taxonomy_id( 'Brand' )` returns attribute ID `3` (`pa_brand`), linking the product to the pre-created `pa_brand` taxonomy.

### 6.2. Technical Recommendation
- **Approved Approach:** Proceed with **Global Attribute `pa_brand`**.
  - Matches the CSV structure 100% out-of-the-box.
  - Fully compatible with WooCommerce Layered Navigation and Attribute Filters.
  - Requires zero CSV alterations or custom mapping scripts.
  - Prevents split-taxonomy conflicts between `pa_brand` and `product_brand`.

---

## 7. Item F — Rollback Readiness & Recovery Architecture

### 7.1. Stale Snapshot Warning (VERIFIED)
- `stage_1e_pre_import_snapshot.sql` (`A9617E...`, 1,336,511 bytes) was created in Stage 1E.3A, *before* attributes and terms were populated in Stage 1E.3B.
- **RESTORE FORBIDDEN:** Restoring this snapshot would wipe out all pre-configured global attributes, terms, and categories.

### 7.2. Dual-Layer Recovery Architecture for Stage 1E.3E

```
+-------------------------------------------------------------------------------+
| LAYER 1: SCOPED MANIFEST ROLLBACK (PRIMARY RECOVERY)                          |
| Records created during import are logged by Post ID and SKU.                  |
| In case of partial failure, a deterministic script deletes only newly created |
| IDs from wp_posts, wp_postmeta, and wp_term_relationships.                    |
| PRESERVES: Pre-existing attributes, terms, categories, and settings.         |
+-------------------------------------------------------------------------------+
                                        |
                                        v
+-------------------------------------------------------------------------------+
| LAYER 2: FRESH PRE-IMPORT SNAPSHOT (DISASTER RECOVERY)                        |
| Generated immediately before import in Gate 2.                               |
| Contains current DB state including all attributes, terms, and categories.    |
| Reserved strictly for catastrophic database corruption or interrupted schema. |
+-------------------------------------------------------------------------------+
```

### 7.3. Interruption and Rerun Behavior
- **If import interrupts halfway (e.g. at row 30 of 64):**
  - Rows 1–30 are saved in the database.
  - Rerunning with "Update existing products" **UNCHECKED**: The importer will skip the first 30 products (`'A product with this SKU already exists.'`) and import the remaining 34 products.
  - Rerunning with "Update existing products" **CHECKED**: The importer updates the first 30 products in place and imports the remaining 34 products.
  - However, if a partial batch cuts between a parent and its variations, variations may fail. Therefore, running Scoped Manifest Rollback to clean the partial batch before a clean rerun is the safest procedure.

---

## 8. Item G — Audit Evaluation Matrix

| Audit Item | Scope | Outcome | Status |
| :--- | :--- | :--- | :---: |
| **Item A** | Environment versions (WP 7.1.3, WC 11.2.1, PHP 8.2, MySQL 8.4) | Ground truth verified via source files & database | **PASS** |
| **Item B** | CSV identity, hashes, and cell-by-cell semantic diff | Hashes match locked baselines; exactly 5 image diffs | **PASS** |
| **Item C** | Exhaustive 28-column importer mapping audit | All 28 headers map natively; zero manual mapping needed | **PASS** |
| **Item D** | Parent-variation hierarchy, SKU resolution, attribute linking | Parents precede variations; SKU resolution verified in core | **PASS** |
| **Item E** | Brand taxonomy decision (`pa_brand` vs `product_brand`) | CSV uses Attribute 3; `pa_brand` is exact native fit | **PASS** |
| **Item F** | Rollback readiness, stale snapshot warning, dual-layer recovery | Scoped manifest design verified; fresh backup mandated | **PASS** |
| **Safety** | Zero writes, zero product creations, zero database changes | Read-only inspection verified; DB untouched | **PASS** |

### **OVERALL AUDIT OUTCOME: PASS — ELIGIBLE FOR HUMAN REVIEW**

---

## 9. Prerequisites for Stage 1E.3E (Controlled Real Import)

Before Stage 1E.3E may be initiated:
1. **User Explicit Approval:** User must review this report and explicitly authorize proceeding.
2. **Environment Confirmation:** Local by Flywheel site running at `http://localhost:10010`.
3. **Working CSV Lock:** `data-preparation/woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv` (SHA-256 `D2C856...`).
4. **Gate 2 Execution:** Generate a fresh pre-import snapshot `backups/stage_1e_fresh_pre_import_snapshot_[TIMESTAMP].sql`.
5. **UI Settings Lock:** Leave "Update existing products" **UNCHECKED** for initial clean import.

---

## 10. Mandatory Stop Condition

In strict accordance with Stage 1E.3D.1 instructions:
- **NO PRODUCT IMPORT WAS PERFORMED.**
- **NO DATABASE WRITES WERE EXECUTED.**
- **NO SNAPSHOTS WERE RESTORED OR OVERWRITTEN.**
- **EXECUTION IS HALTED.** Awaiting user review and authorization.
