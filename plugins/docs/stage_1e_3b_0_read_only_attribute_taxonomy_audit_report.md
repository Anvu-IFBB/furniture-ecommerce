# STAGE 1E.3B.0 — READ-ONLY ATTRIBUTE & TAXONOMY AUDIT REPORT
## PRE-INITIALIZATION INTEGRITY AUDIT

> **STAGE ID:** STAGE 1E.3B.0  
> **DATE & TIME:** 2026-10-16 16:08:45+07:00  
> **OVERALL RESULT:** **`PASS`**  
> **AUDIT MODE:** **STRICT READ-ONLY — NO DATABASE WRITES**

---

## 1. Executive Summary

This audit evaluated the exact pre-initialization state of WooCommerce global attributes, taxonomy registrations, database term storage, and baseline content for the `furniture-ecommerce` project.

- **Global Attributes in Database (`wp_woocommerce_attribute_taxonomies`):** Exactly **0** attributes exist. Metadata for `color`, `size`, and `brand` is currently absent (`NOT_FOUND`).
- **Taxonomy Runtime Status:**
  - `pa_color`, `pa_size`, `pa_brand` are **NOT REGISTERED** in runtime (WooCommerce registers `pa_*` taxonomies dynamically from database attribute metadata).
  - `product_brand` is **REGISTERED** in runtime by WooCommerce Core Brands feature (`Automattic\WooCommerce\Internal\Brands`), but has **0 terms** in database.
  - `product_cat` is **REGISTERED** in runtime, containing only the default `Uncategorized` term.
- **Terms in Database:** 0 terms for color, size, brand. Exactly 1 term for `product_cat` (`Uncategorized`, count 0).
- **Products & SKUs:** Exactly **0** products, **0** variations, and **0** SKUs in the database.
- **Integrity Anchors:**
  - Source CSV SHA-256: `276809899064949CB8D45B8EF7523B8C2913780F0649650EA4EB494FB74894F6` (**MATCHES**).
  - Pre-import Snapshot SHA-256: `A9617E0173ED8D81E64B2F6BB3521E946406502AB12DA01D8328F46CB401A2F6` (**MATCHES**).
- **Safety Invariant:** Zero database writes were executed. Zero attributes or terms were created.

---

## 2. Environment & Database Verification

All parameters were verified directly against active system processes and read queries:

| Parameter | Configured Value | Verified Runtime State | Method | Status |
| :--- | :--- | :--- | :--- | :---: |
| **WordPress Root** | `.../app/public` | `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public` | Filesystem probe | **VERIFIED** |
| **Database Host** | `127.0.0.1` | `localhost` (`127.0.0.1:10011`) | `$wpdb->dbhost` | **VERIFIED** |
| **Database Name** | `local` | `local` | `$wpdb->dbname` | **VERIFIED** |
| **Table Prefix** | `wp_` | `wp_` | `$wpdb->prefix` | **VERIFIED** |
| **WordPress Core** | 7.1.3 | **7.1.3** | `$wp_version` | **VERIFIED** |
| **WooCommerce** | 11.2.1 | **11.2.1** | `WC_VERSION` / `get_option` | **VERIFIED** |

---

## 3. Database Table Structure & Existence Checks

Inspection of required WooCommerce product and taxonomy tables:

| Table Name | Type | Existence | Structure Overview |
| :--- | :---: | :---: | :--- |
| `wp_woocommerce_attribute_taxonomies` | Base Table | **EXISTS** | `attribute_id` (bigint PK), `attribute_name` (varchar), `attribute_label` (varchar), `attribute_type` (varchar), `attribute_orderby` (varchar), `attribute_public` (int) |
| `wp_wc_product_attributes_lookup` | Base Table | **EXISTS** | Product attribute lookup table (0 rows) |
| `wp_wc_product_meta_lookup` | Base Table | **EXISTS** | Product meta lookup table (0 rows) |
| `wp_term_taxonomy` | Base Table | **EXISTS** | WordPress taxonomy table |
| `wp_terms` | Base Table | **EXISTS** | WordPress term table |
| `wp_term_relationships` | Base Table | **EXISTS** | WordPress term-to-object relationship table |

*Conclusion:* All necessary database tables exist and are structurally valid. No repair or migration is needed.

---

## 4. Global Attributes Audit (`wp_woocommerce_attribute_taxonomies`)

Direct query: `SELECT * FROM wp_woocommerce_attribute_taxonomies`

| Target Attribute | Target Slug | Found in Metadata? | Attribute ID | Type | Order-By | Public? | Anomaly / Collision |
| :--- | :--- | :---: | :---: | :---: | :---: | :---: | :--- |
| **Color** | `color` / `pa_color` | **NO (`NOT_FOUND`)** | None | N/A | N/A | N/A | None (Clean slate) |
| **Size** | `size` / `pa_size` | **NO (`NOT_FOUND`)** | None | N/A | N/A | N/A | None (Clean slate) |
| **Brand** | `brand` / `pa_brand` | **NO (`NOT_FOUND`)** | None | N/A | N/A | N/A | None (Clean slate) |

*Total Global Attribute Rows in DB:* **`0`**.  
*Finding:* No global attributes have been created in the database.

---

## 5. Runtime Taxonomy Status & State Differentiation

A critical principle of WooCommerce taxonomy architecture is that database metadata, runtime registration, term storage, and product relationships represent four distinct states:

```
[1. DB Metadata in attribute_taxonomies] ──(Dynamic Register)──> [2. Registered in WP Runtime]
                                                                            │
[4. Associated Products] <──(term_relationships)── [3. Terms in wp_terms] <─┘
```

Detailed breakdown:

| Taxonomy Slug | (1) Metadata in DB? | (2) Registered in Runtime? | (3) Terms in DB? | (4) Products Attached? | Runtime Object Types |
| :--- | :---: | :---: | :---: | :---: | :--- |
| **`pa_color`** | **NO** | **NO** (`false`) | **0** | **0** | `[]` (Unregistered) |
| **`pa_size`** | **NO** | **NO** (`false`) | **0** | **0** | `[]` (Unregistered) |
| **`pa_brand`** | **NO** | **NO** (`false`) | **0** | **0** | `[]` (Unregistered) |
| **`product_brand`**| **NO** | **YES** (`true`) | **0** | **0** | `['product']` (Core feature) |
| **`product_cat`** | N/A (Core) | **YES** (`true`) | **1** (`Uncategorized`) | **0** | `['product']` |
| **`product_tag`** | N/A (Core) | **YES** (`true`) | **0** | **0** | `['product']` |

### Architectural Insight:
- `pa_color`, `pa_size`, and `pa_brand` do not exist in runtime because WooCommerce reads `wp_woocommerce_attribute_taxonomies` on init to call `register_taxonomy()`. Since the table is empty, WooCommerce does not register them.
- `product_brand` is pre-registered by WooCommerce Core's internal brands feature (`Automattic\WooCommerce\Internal\Brands`), but contains 0 terms. In Stage 1E.2.1, the project locked the decision to use **`pa_brand`** as a global attribute to match the CSV structure (`Attribute 3 name = Brand`, `Attribute 3 global = 1`).

---

## 6. Terms and Category Storage Audit

Read-only queries against `wp_terms` and `wp_term_taxonomy`:

| Taxonomy | Term Count in DB | Stored Terms Detail |
| :--- | :---: | :--- |
| **`pa_color`** | **0** | None |
| **`pa_size`** | **0** | None |
| **`pa_brand`** | **0** | None |
| **`product_brand`**| **0** | None |
| **`product_tag`** | **0** | None |
| **`product_cat`** | **1** | Term ID `15`: Name `Uncategorized`, Slug `uncategorized`, Count `0` |

### Expected CSV Terms vs Current DB Terms:
- **Color:** Expected 3 terms (`Black`, `Brown`, `White`) → **0 in DB**.
- **Size:** Expected 3 terms (`L`, `M`, `S`) → **0 in DB**.
- **Brand:** Expected 5 terms (`NordicLiving`, `Minimalia`, `OakCraft`, `UrbanoHaus`, `LuxeVibe`) → **0 in DB**.
- **Categories:** Expected 7 terms (`Beds`, `Chairs`, `Decoration`, `Lighting`, `Sofas`, `Storage`, `Tables`) → **0 in DB** (only `Uncategorized` exists).

---

## 7. Product, Variation & SKU Baseline

Read-only verification of post tables and metadata:

| Item | Count | Status |
| :--- | :---: | :--- |
| **Posts of type `product`** | **0** | Clean |
| **Posts of type `product_variation`** | **0** | Clean |
| **SKUs in `wp_postmeta` (`_sku`)** | **0** | Clean |
| **Product Attributes Meta (`_product_attributes`)** | **0** | Clean |
| **Lookup Rows (`wp_wc_product_attributes_lookup`)** | **0** | Clean |

---

## 8. Cryptographic Anchors & Dataset Verification

| File | Target Path | Verified SHA-256 | Expected SHA-256 | Status |
| :--- | :--- | :--- | :--- | :---: |
| **Source CSV** | `.../woocommerce_furniture_dataset_22_products.csv` | `276809899064949CB8D45B8EF7523B8C2913780F0649650EA4EB494FB74894F6` | `276809899064949CB8D45B8EF7523B8C2913780F0649650EA4EB494FB74894F6` | **MATCH** |
| **Pre-Import Snapshot** | `.../backups/stage_1e_pre_import_snapshot.sql` | `A9617E0173ED8D81E64B2F6BB3521E946406502AB12DA01D8328F46CB401A2F6` | `A9617E0173ED8D81E64B2F6BB3521E946406502AB12DA01D8328F46CB401A2F6` | **MATCH** |

- Snapshot size: `1,336,511 bytes`.
- CSV size: `26,176 bytes`.

---

## 9. Limitations & Audit Verification Methodology

1. **No Credentials Logged:** All database connections were established via the local WordPress environment abstraction (`wp-load.php` / `$wpdb`). No passwords or secrets were passed via command line or included in logs.
2. **True Read-Only Verification:** All checks used `SHOW TABLES`, `DESCRIBE`, and `SELECT` queries with zero data modifications.
3. **Limitation:** Runtime taxonomy registration was evaluated in a non-admin CLI context. When WooCommerce loads in admin or REST contexts, it executes `wc_register_attribute_taxonomies()`, which relies entirely on rows in `wp_woocommerce_attribute_taxonomies`.

---

## 10. Risk Register for Upcoming Stage 1E.3B

1. **Missing Attribute Registration:** Because `color`, `size`, and `brand` are not in `wp_woocommerce_attribute_taxonomies`, importing variable products directly without pre-registering them would cause WooCommerce to treat them as custom product-level attributes rather than global filtering taxonomies.
2. **Missing Product Categories:** The 7 categories do not exist. While WooCommerce CSV importer can create categories on the fly, creating them with proper hierarchies or verifying them beforehand prevents categorization issues.
3. **Image 404 URLs:** Rows 14 (`FURN-006`) and 49 (`FURN-018`) remain in `REPLACEMENT NOT APPROVED` status and must be resolved before executing the import.

---

## 11. Acceptance Criteria Evaluation

- [x] Database connectivity and WooCommerce 11.2.1 verified.
- [x] All 6 required WooCommerce product and taxonomy tables confirmed existing and intact.
- [x] Read-only audit of `wp_woocommerce_attribute_taxonomies` confirmed 0 attributes.
- [x] Runtime taxonomy registration checked (`pa_color`, `pa_size`, `pa_brand` unregistered; `product_brand` core registered).
- [x] Term counts and product category counts fully verified (0 attribute terms, 1 default category).
- [x] Product / variation / SKU baseline confirmed at 0.
- [x] Source CSV hash and snapshot hash confirmed 100% matched.
- [x] Zero database writes performed; zero files modified.

### **OVERALL EVALUATION: PASS**

---

## 12. Stage Gate — STOP

In compliance with Stage 1E.3B.0 instructions:
- Execution is **HALTED**.
- DO NOT proceed to Stage 1E.3B.
- DO NOT create attributes, terms, or taxonomies.
- DO NOT import products or create working CSVs.
- Await user review and explicit authorization.
