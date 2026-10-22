# STAGE 1E.3G.1 — GATE 3A: DISCREPANCY RECONCILIATION REPORT

**Execution Timestamp:** 2026-10-18 18:52:00 (UTC+7)  
**Project:** `furniture-ecommerce`  
**Stage:** Stage 1E.3G.1 — Gate 3A: Discrepancy Reconciliation (Strict Read-Only)  
**Evaluator:** Senior WooCommerce Data Migration Engineer, WordPress Database Auditor, and QA Engineer  
**Stage Conclusion:** **PASS — READY FOR HUMAN REVIEW**

---

## 1. AUTHORITY BOUNDARY & CONSTRAINTS COMPLIANCE

This audit was conducted strictly under read-only authority:
- **No CSV import** was performed.
- **No database records** were inserted, updated, or deleted.
- **No snapshots** were created or restored.
- **Neither CSV file** was modified, reformatted, or touched.
- **No server, WordPress, WooCommerce, plugin, or theme settings** were modified.
- **Gate 4 has NOT been initiated.**

---

## 2. VERIFICATION OF EXACT INPUTS

All authoritative input files, database identity, and software components were independently re-verified against locked baselines:

| Artifact / Environment | Expected Value | Verified Actual Value | Status |
| :--- | :--- | :--- | :--- |
| **Working CSV Path** | `data-preparation\woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv` | `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\data-preparation\woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv` | **MATCH** |
| **Working CSV Size** | `25,712` bytes | `25,712` bytes | **MATCH** |
| **Working CSV SHA-256** | `D2C8566F334A767887F57C4A15EA456CFC5E117767FCFE46C599CC754BC2BBA0` | `D2C8566F334A767887F57C4A15EA456CFC5E117767FCFE46C599CC754BC2BBA0` | **MATCH** |
| **Original Source CSV Path** | `app\public\wp-content\plugins\woocommerce_furniture_dataset_22_products.csv` | `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\wp-content\plugins\woocommerce_furniture_dataset_22_products.csv` | **MATCH** |
| **Original CSV Size** | `26,176` bytes | `26,176` bytes | **MATCH** |
| **Original CSV SHA-256** | `276809899064949CB8D45B8EF7523B8C2913780F0649650EA4EB494FB74894F6` | `276809899064949CB8D45B8EF7523B8C2913780F0649650EA4EB494FB74894F6` | **MATCH** |
| **Gate 2 Snapshot Path** | `backups\stage_1e_gate2_pre_import_20261016_223502.sql` | `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\backups\stage_1e_gate2_pre_import_20261016_223502.sql` | **MATCH** |
| **Gate 2 Snapshot Size** | `1,236,031` bytes | `1,236,031` bytes | **MATCH** |
| **Gate 2 Snapshot SHA-256** | `9152E46BB462C321EDD7778A6DA7889450A90301BD1627682D10345A5BDCC8A3` | `9152E46BB462C321EDD7778A6DA7889450A90301BD1627682D10345A5BDCC8A3` | **MATCH** |
| **WooCommerce Version** | `11.2.1` | `11.2.1` | **MATCH** |
| **Database Identity** | `local` (Prefix: `wp_`, Endpoint: `127.0.0.1:10011`) | `local` (Prefix: `wp_`, Endpoint: `127.0.0.1:10011`) | **MATCH** |

---

## 3. INVENTORY TOTALS RECONCILIATION

### 3.1. Reconciling Historical Conflicting Figures
- **Earlier report claim (Stage 1E.3D):** Simple = 168, Variations = 281, Total = 449.
- **Gate 3 report claim (Stage 1E.3G):** Simple = 248, Variations = 201, Total = 449.

An independent row-by-row summation using an RFC-4180-compliant parser proves that the verified working CSV (as well as the original source CSV) unequivocally contains:
- **Simple Products Stock:** **`248` units**
- **Variable Parent Stock:** **`0` units** (unmanaged at parent level)
- **Product Variations Stock:** **`201` units**
- **Overall Declared Inventory:** **`449` units**

The earlier report figure (`168 simple + 281 variations`) was a **narrative drafting error** in Stage 1E.3D where exactly 80 units of stock were mistakenly attributed to variations instead of simple products (`248 - 80 = 168` and `201 + 80 = 281`). Both the working CSV and source CSV have always contained 248 simple stock and 201 variation stock.

### 3.2. Complete Row-by-Row Inventory Manifest

#### Simple Products (12 rows | Sum = 248 units)
| CSV Line | SKU | Type | Parent | Declared Stock | Cumulative Stock |
| :--- | :--- | :--- | :--- | :--- | :--- |
| Line 2 | `FURN-001` | `simple` | *(none)* | `25` | 25 |
| Line 3 | `FURN-002` | `simple` | *(none)* | `30` | 55 |
| Line 4 | `FURN-003` | `simple` | *(none)* | `15` | 70 |
| Line 5 | `FURN-004` | `simple` | *(none)* | `20` | 90 |
| Line 6 | `FURN-005` | `simple` | *(none)* | `18` | 108 |
| Line 7 | `FURN-006` | `simple` | *(none)* | `12` | 120 |
| Line 8 | `FURN-007` | `simple` | *(none)* | `40` | 160 |
| Line 9 | `FURN-008` | `simple` | *(none)* | `22` | 182 |
| Line 10 | `FURN-009` | `simple` | *(none)* | `50` | 232 |
| Line 11 | `FURN-010` | `simple` | *(none)* | `0` | 232 |
| Line 12 | `FURN-011` | `simple` | *(none)* | `16` | 248 |
| Line 13 | `FURN-012` | `simple` | *(none)* | `0` | **248** |

#### Variable Parent Products (10 rows | Sum = 0 units)
| CSV Line | SKU | Type | Parent | Declared Stock | Stock Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| Line 14 | `FURN-013` | `variable` | *(none)* | `""` (empty) | In stock (`1`) |
| Line 21 | `FURN-014` | `variable` | *(none)* | `""` (empty) | In stock (`1`) |
| Line 26 | `FURN-015` | `variable` | *(none)* | `""` (empty) | In stock (`1`) |
| Line 31 | `FURN-016` | `variable` | *(none)* | `""` (empty) | In stock (`1`) |
| Line 36 | `FURN-017` | `variable` | *(none)* | `""` (empty) | In stock (`1`) |
| Line 41 | `FURN-018` | `variable` | *(none)* | `""` (empty) | In stock (`1`) |
| Line 46 | `FURN-019` | `variable` | *(none)* | `""` (empty) | In stock (`1`) |
| Line 51 | `FURN-020` | `variable` | *(none)* | `""` (empty) | In stock (`1`) |
| Line 56 | `FURN-021` | `variable` | *(none)* | `""` (empty) | In stock (`1`) |
| Line 61 | `FURN-022` | `variable` | *(none)* | `""` (empty) | In stock (`1`) |

#### Product Variations (42 rows | Sum = 201 units)
| CSV Line | SKU | Type | Parent SKU | Declared Stock | Cumulative Stock |
| :--- | :--- | :--- | :--- | :--- | :--- |
| Line 15 | `FURN-013-BLK-S` | `variation` | `FURN-013` | `8` | 8 |
| Line 16 | `FURN-013-BLK-M` | `variation` | `FURN-013` | `6` | 14 |
| Line 17 | `FURN-013-BLK-L` | `variation` | `FURN-013` | `5` | 19 |
| Line 18 | `FURN-013-WHT-S` | `variation` | `FURN-013` | `5` | 24 |
| Line 19 | `FURN-013-WHT-M` | `variation` | `FURN-013` | `4` | 28 |
| Line 20 | `FURN-013-BRN-L` | `variation` | `FURN-013` | `7` | **35** |
| Line 22 | `FURN-014-BRN-M` | `variation` | `FURN-014` | `5` | 40 |
| Line 23 | `FURN-014-BRN-L` | `variation` | `FURN-014` | `4` | 44 |
| Line 24 | `FURN-014-WHT-M` | `variation` | `FURN-014` | `4` | 48 |
| Line 25 | `FURN-014-WHT-L` | `variation` | `FURN-014` | `3` | **51** |
| Line 27 | `FURN-015-WHT-M` | `variation` | `FURN-015` | `4` | 55 |
| Line 28 | `FURN-015-WHT-L` | `variation` | `FURN-015` | `3` | 58 |
| Line 29 | `FURN-015-BLK-M` | `variation` | `FURN-015` | `3` | 61 |
| Line 30 | `FURN-015-BRN-L` | `variation` | `FURN-015` | `4` | **65** |
| Line 32 | `FURN-016-WHT-M` | `variation` | `FURN-016` | `4` | 69 |
| Line 33 | `FURN-016-WHT-L` | `variation` | `FURN-016` | `3` | 72 |
| Line 34 | `FURN-016-BRN-M` | `variation` | `FURN-016` | `3` | 75 |
| Line 35 | `FURN-016-BRN-L` | `variation` | `FURN-016` | `2` | **77** |
| Line 37 | `FURN-017-BLK-S` | `variation` | `FURN-017` | `6` | 83 |
| Line 38 | `FURN-017-BLK-M` | `variation` | `FURN-017` | `6` | 89 |
| Line 39 | `FURN-017-BLK-L` | `variation` | `FURN-017` | `4` | 93 |
| Line 40 | `FURN-017-WHT-M` | `variation` | `FURN-017` | `8` | **101** |
| Line 42 | `FURN-018-BRN-S` | `variation` | `FURN-018` | `10` | 111 |
| Line 43 | `FURN-018-BRN-M` | `variation` | `FURN-018` | `8` | 119 |
| Line 44 | `FURN-018-BLK-S` | `variation` | `FURN-018` | `6` | 125 |
| Line 45 | `FURN-018-WHT-M` | `variation` | `FURN-018` | `6` | **131** |
| Line 47 | `FURN-019-BRN-M` | `variation` | `FURN-019` | `5` | 136 |
| Line 48 | `FURN-019-BRN-L` | `variation` | `FURN-019` | `4` | 140 |
| Line 49 | `FURN-019-BLK-M` | `variation` | `FURN-019` | `3` | 143 |
| Line 50 | `FURN-019-BLK-L` | `variation` | `FURN-019` | `2` | **145** |
| Line 52 | `FURN-020-BLK-S` | `variation` | `FURN-020` | `6` | 151 |
| Line 53 | `FURN-020-BLK-M` | `variation` | `FURN-020` | `6` | 157 |
| Line 54 | `FURN-020-BLK-L` | `variation` | `FURN-020` | `4` | 161 |
| Line 55 | `FURN-020-WHT-M` | `variation` | `FURN-020` | `5` | **166** |
| Line 57 | `FURN-021-BLK-S` | `variation` | `FURN-021` | `6` | 172 |
| Line 58 | `FURN-021-WHT-M` | `variation` | `FURN-021` | `5` | 177 |
| Line 59 | `FURN-021-BRN-L` | `variation` | `FURN-021` | `5` | 182 |
| Line 60 | `FURN-021-BLK-L` | `variation` | `FURN-021` | `4` | **186** |
| Line 62 | `FURN-022-BLK-M` | `variation` | `FURN-022` | `5` | 191 |
| Line 63 | `FURN-022-BLK-L` | `variation` | `FURN-022` | `4` | 195 |
| Line 64 | `FURN-022-WHT-M` | `variation` | `FURN-022` | `3` | 198 |
| Line 65 | `FURN-022-WHT-L` | `variation` | `FURN-022` | `3` | **201** |

---

## 4. ATTRIBUTE COLUMN ORDER & TAXONOMY MAPPING RECONCILIATION

### 4.1. Reconciliation: Brand as Attribute 1 vs. Attribute 3
In some earlier audit tables, `Brand` was casually listed as Attribute 1 in example snippets. Direct inspection of the actual CSV file establishes the ground truth:

```text
Column 17: Attribute 1 name       -> "Color"
Column 18: Attribute 1 value(s)   -> e.g. "Brown", "Black, White, Brown"
Column 19: Attribute 1 visible    -> "1" (parents), "" (variations)
Column 20: Attribute 1 global     -> "1"

Column 21: Attribute 2 name       -> "Size"
Column 22: Attribute 2 value(s)   -> e.g. "M", "S, M, L"
Column 23: Attribute 2 visible    -> "1" (parents), "" (variations)
Column 24: Attribute 2 global     -> "1"

Column 25: Attribute 3 name       -> "Brand" (parents), "" (variations)
Column 26: Attribute 3 value(s)   -> e.g. "NordicLiving", "" (variations)
Column 27: Attribute 3 visible    -> "1" (parents), "" (variations)
Column 28: Attribute 3 global     -> "1" (parents), "" (variations)
```

**Conclusion:** In the actual working CSV, **`Brand` is unequivocally `Attribute 3`**, while **`Color` is `Attribute 1`** and **`Size` is `Attribute 2`**.

### 4.2. Distinct Attribute Patterns across All 64 Rows

| Pattern # | Product Type | Matching Rows | Attribute 1 (`pa_color`) | Attribute 2 (`pa_size`) | Attribute 3 (`pa_brand`) |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Pattern 1** | `simple` | 12 rows | Name: `Color`<br>Values: Single term<br>Visible: `1`<br>Global: `1` | Name: `Size`<br>Values: Single term<br>Visible: `1`<br>Global: `1` | Name: `Brand`<br>Values: Single term<br>Visible: `1`<br>Global: `1` |
| **Pattern 2** | `variable` | 10 rows | Name: `Color`<br>Values: Multi terms (2–3)<br>Visible: `1`<br>Global: `1` | Name: `Size`<br>Values: Multi terms (2–3)<br>Visible: `1`<br>Global: `1` | Name: `Brand`<br>Values: Single term<br>Visible: `1`<br>Global: `1` |
| **Pattern 3** | `variation`| 42 rows | Name: `Color`<br>Values: Single term<br>Visible: `""`<br>Global: `1` | Name: `Size`<br>Values: Single term<br>Visible: `""`<br>Global: `1` | *(all 4 fields blank)* |

### 4.3. WooCommerce 11.2.1 Importer Attribute Resolution
Inspection of `abstract-wc-product-importer.php` (lines 426–443) demonstrates that WooCommerce resolves taxonomy attributes via `$this->get_attribute_taxonomy_id($attribute['name'])`. The importer matches the attribute string dynamically against registered attribute slugs and labels:
- `Color` matches label `Color` -> resolves to `attribute_id: 1` (`pa_color`).
- `Size` matches label `Size` -> resolves to `attribute_id: 2` (`pa_size`).
- `Brand` matches label `Brand` -> resolves to `attribute_id: 3` (`pa_brand`).
The column index (1, 2, 3) only specifies relative display position and does **not** interfere with taxonomy resolution.

### 4.4. 100% Parent-Child Variation Attribute Option Validation
Every variation row was audited against its parent variable product's declared attribute options:
- **`FURN-013` (6 variations):** Parent options `Color: Black, White, Brown` / `Size: S, M, L` -> All 6 variations valid.
- **`FURN-014` (4 variations):** Parent options `Color: Brown, White` / `Size: M, L` -> All 4 variations valid.
- **`FURN-015` (4 variations):** Parent options `Color: White, Black, Brown` / `Size: M, L` -> All 4 variations valid.
- **`FURN-016` (4 variations):** Parent options `Color: White, Brown` / `Size: M, L` -> All 4 variations valid.
- **`FURN-017` (4 variations):** Parent options `Color: Black, White` / `Size: S, M, L` -> All 4 variations valid.
- **`FURN-018` (4 variations):** Parent options `Color: Brown, Black, White` / `Size: S, M` -> All 4 variations valid.
- **`FURN-019` (4 variations):** Parent options `Color: Brown, Black` / `Size: M, L` -> All 4 variations valid.
- **`FURN-020` (4 variations):** Parent options `Color: Black, White` / `Size: S, M, L` -> All 4 variations valid.
- **`FURN-021` (4 variations):** Parent options `Color: Black, White, Brown` / `Size: S, M, L` -> All 4 variations valid.
- **`FURN-022` (4 variations):** Parent options `Color: Black, White` / `Size: M, L` -> All 4 variations valid.

**Result: Exactly 42 variations checked. Zero attribute mismatches.**

---

## 5. CATEGORY ASSOCIATIONS RECONCILIATION

### 5.1. Reconciliation: "29 Associations" vs. Category Sum of 27
In the Gate 3 report Section 4, the category distribution was stated as:
`Chairs: 5, Tables: 3, Sofas: 3, Beds: 4, Storage: 5, Lighting: 3, Decoration: 4; sum = 29 parent-category associations`

Notice the arithmetic error in that text:
`5 + 3 + 3 + 4 + 5 + 3 + 4 = 27`, **NOT 29**.
The author made a typographical addition error (`27` was mistakenly written as `29`).

### 5.2. Verified Product-Category Breakdown
Parsing all 22 parent products reveals:
- **17 products** are assigned to exactly **1 category** (17 associations).
- **5 products** are assigned to exactly **2 categories** (10 associations):
  1. `FURN-006` (Line 7, simple): `Beds, Storage`
  2. `FURN-010` (Line 11, simple): `Storage, Decoration`
  3. `FURN-019` (Line 46, variable): `Storage, Beds`
  4. `FURN-021` (Line 56, variable): `Chairs, Sofas`
  5. `FURN-022` (Line 61, variable): `Beds, Storage`
- **Total distinct product-category associations:** `17 + 10 = 27`.
- **Blank, unknown, or unmapped categories:** `0` (all 27 associations resolve to existing dataset categories).

### 5.3. Category Frequency Table
| Category Slug | Category Name | Parent Product Count | Term ID | Status |
| :--- | :--- | :--- | :--- | :--- |
| `beds` | Beds | 4 | 27 | Registered |
| `chairs` | Chairs | 5 | 28 | Registered |
| `decoration` | Decoration | 4 | 29 | Registered |
| `lighting` | Lighting | 3 | 30 | Registered |
| `sofas` | Sofas | 3 | 31 | Registered |
| `storage` | Storage | 5 | 32 | Registered |
| `tables` | Tables | 3 | 33 | Registered |
| **SUM** | **7 Categories** | **27 Product-Category Links** | — | **100% Resolved** |

*(Note: Term ID 15 `Uncategorized` remains present in the database as the default category with 0 products assigned).*

---

## 6. RECHECK OF LIVE DATABASE STATE

Live read-only SQL queries executed against MySQL (`127.0.0.1:10011`, DB `local`, prefix `wp_`) confirm the database remains in its clean baseline state:

| Metric | Target Database State | Baseline Expectation | Audit Verdict |
| :--- | :--- | :--- | :--- |
| **`product` posts** | `0` | `0` | **CLEAN** |
| **`product_variation` posts** | `0` | `0` | **CLEAN** |
| **`_sku` metadata records** | `0` | `0` | **CLEAN** |
| **Conflicting CSV SKUs** | `0` | `0` | **CLEAN** |
| **Existing attachments** | `1` (`ID: 5`, placeholder) | `1` | **CLEAN** |
| **Global attributes** | `3` (`color`, `size`, `brand`) | `3` | **INTACT** |
| **Registered `pa_color` terms** | `3` (Black, Brown, White) | `3` | **INTACT** |
| **Registered `pa_size` terms** | `3` (L, M, S) | `3` | **INTACT** |
| **Registered `pa_brand` terms** | `5` (5 brands) | `5` | **INTACT** |
| **Registered `product_cat` terms** | `8` (7 dataset + Uncategorized) | `8` | **INTACT** |

---

## 7. AUDIT OF IMPORT FAILURE MODES & ROLLBACK READINESS

An engineering review of the proposed import and rollback architecture identified several critical implementation failure modes and required safeguards:

### 7.1. Incremental Manifest Logging
- **Risk:** If the import manifest (`import_manifest.json`) is only written to disk at the completion of all 64 rows, any unhandled fatal exception, script timeout, or memory exhaustion will terminate execution without persisting the manifest. Consequently, products created prior to the crash would become untracked.
- **Required Safeguard:** The import execution harness must append each newly created product ID, variation ID, and attachment ID to a persistent manifest file immediately after each row is saved (`fflush` / atomic append).

### 7.2. Media Attachment Tracking
- **Risk:** When WooCommerce imports products with `Images` URLs, it downloads media and creates `attachment` posts in `wp_posts`. If rollback only deletes `product` and `product_variation` post types, downloaded media attachments will remain orphaned in `wp_posts` and on the filesystem in `wp-content/uploads/`.
- **Required Safeguard:** The import harness must hook into `woocommerce_product_import_inserted_product_object` or inspect `$product->get_image_id()` and `$product->get_gallery_image_ids()` to log all created attachment IDs into the manifest, ensuring the rollback procedure purges them via `wp_delete_attachment($attachment_id, true)`.

### 7.3. Rollback Deletion Order (Children before Parents)
- **Risk:** Deleting a variable parent product in WordPress does not cleanly cascade to delete variations in all database configurations, potentially leaving orphaned variations with invalid parent IDs.
- **Required Safeguard:** The rollback script must delete objects in reverse topological order:
  1. Delete all tracked `product_variation` IDs.
  2. Delete all tracked `product` (parent) IDs.
  3. Delete all tracked `attachment` IDs.
  4. Clear WooCommerce transients (`wc_delete_product_transients`).

### 7.4. Handling of Importer "Skip" Behavior vs. Hard Failure
- **Risk:** In WooCommerce core `WC_Product_CSV_Importer::import()` (lines 1432–1442), when `update_existing` is `false`, encountering an existing SKU causes the importer to log a skipped row in `$data['skipped']` and silently continue. A naive runner might complete with exit code 0 even if products were skipped.
- **Required Safeguard:** The execution harness must assert `$data['skipped'] === []` and `$data['failed'] === []`. If any row is skipped or fails, the runner must halt immediately, abort the import, and trigger rollback.

---

## 8. ACCEPTANCE CRITERIA EVALUATION

| Criterion | Evaluation Standard | Verdict |
| :--- | :--- | :--- |
| **1. File & Snapshot Identity** | Exact paths, sizes, and SHA-256 match locked baselines | **PASS** |
| **2. Inventory Totals Reconciled** | 248 simple + 0 parent + 201 variation = 449 total stock verified row-by-row; historical 168+281 discrepancy resolved | **PASS** |
| **3. Attribute Order & Taxonomy Mapping** | Verified Brand is Attribute 3, Color is Attribute 1, Size is Attribute 2; dynamic taxonomy lookup confirmed | **PASS** |
| **4. Parent-Child Relationship Integrity** | All 42 variations match parent color/size options with 0 mismatches; row order guarantees parent precedence | **PASS** |
| **5. Category Associations Reconciled** | Verified 22 parents, 17 single, 5 double = exactly 27 associations; historical "29" typo resolved | **PASS** |
| **6. Live Database State** | Confirmed 0 products, 0 variations, 0 SKUs, 0 conflicts, all attributes and terms intact | **PASS** |
| **7. Failure & Rollback Safeguards** | Incremental manifest writing, media attachment logging, and deletion order constraints documented | **PASS** |

---

## 9. FINAL RECOMMENDATION & MANDATORY STOP

### **FINAL EVALUATION:** **PASS — READY FOR HUMAN REVIEW**

All historical textual discrepancies between earlier stage reports have been completely reconciled against the ground-truth working CSV file. The technical dataset properties, database preconditions, and safety mechanisms are fully aligned and verified.

### **MANDATORY FINAL STOP ENFORCEMENT:**
- **No CSV import was performed.**
- **No products, variations, or SKUs were created.**
- **No database records were modified.**
- **No snapshots were created or restored.**
- **Gate 4 has NOT been initiated.**

Execution is stopped immediately. Awaiting your explicit review and separate approval before proceeding to product import execution.
