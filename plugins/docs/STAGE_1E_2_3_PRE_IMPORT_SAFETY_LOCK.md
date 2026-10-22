# STAGE 1E.2.3 — PRE-IMPORT SAFETY LOCK
## READ-ONLY AUDIT & PRE-IMPORT MANIFEST

> **STRICT AUDIT POLICY:**
> This audit was conducted under strict read-only constraints. **NO database writes, NO importer executions, NO term/attribute/product creations, NO SQL imports, and NO destructive Git operations** were performed.
> 
> Current Status Disclaimers:
> - **DATABASE RESTORE: NOT VERIFIED** (Existing backup is statically verified; live restoration has not been rehearsed on a sandbox instance).
> - **404 IMAGE REPLACEMENT: REPLACEMENT NOT APPROVED** (Two broken image URLs remain locked pending user approval).

---

## 1. Environment Evidence

All environment parameters were verified directly against active configuration files and live runtime handshakes.

| Parameter | Configured / Expected | Verified Active Runtime | Verification Method | Status |
| :--- | :--- | :--- | :--- | :--- |
| **WordPress Root** | `.../furniture-ecommerce/app/public` | `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public` | Filesystem check | **VERIFIED** |
| **Git Root** | `.../app/public/wp-content` | `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\wp-content` | `git status` | **VERIFIED** |
| **Local Site URL** | `http://localhost:10010` | `http://localhost:10010` | Web server & `wp_options` | **VERIFIED** |
| **WordPress Core** | 7.1.3 | **7.1.3** | `wp-includes/version.php` (`$wp_version`) | **VERIFIED** |
| **WooCommerce** | 11.2.1 | **11.2.1** | `wp_options` (`woocommerce_version`) | **VERIFIED** |
| **PHP Version** | 8.2.29 | **8.2.29+0** | Local Lightning Service binary | **VERIFIED** |
| **MySQL Server** | MySQL 8.4.0 | **8.4.0** | Handshake on port `10011` | **VERIFIED** |
| **MySQL Host** | `127.0.0.1` | `127.0.0.1` | Direct TCP socket probe | **VERIFIED** |
| **MySQL Port** | `10011` (vs `10007`) | **`10011`** | `my.cnf`, `php.ini`, active connection | **VERIFIED** |
| **Database Name** | `local` | `local` | Schema query | **VERIFIED** |
| **Table Prefix** | `wp_` | `wp_` | Schema query | **VERIFIED** |
| **Active Theme** | `twentytwentyfive-child` | `twentytwentyfive-child` | `wp_options` (`stylesheet`) | **VERIFIED** |

### Database Port Discrepancy Resolution
- **Investigation:** Prior documentation mentioned port `10007`, while Local configuration files referenced port `10011`.
- **Finding:** Inspection of `C:\Users\Vu Cong Minh\AppData\Roaming\Local\run\X8slLDe4p\conf\mysql\my.cnf` confirms `port = 10011`. Direct PHP PDO connection to `127.0.0.1:10011` succeeds and retrieves database records (`wp_posts`). Port `10007` is confirmed obsolete or associated with a separate environment.
- **Resolution:** All subsequent snapshot and database operations MUST strictly target port **`10011`**.

---

## 2. Backup Evidence & Restore Verification

### Baseline Backup Inspection
- **File Path:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\backups\stage_1a_baseline_backup.sql`
- **File Size:** `1,202,692 bytes` (~1.15 MB)
- **Last Modified Time:** `2026-10-15 19:13:22` (10/15/2026 7:13:22 PM)
- **SHA-256 Checksum:** `BFBFC6E22E95411C3BDEE630D5FDE9D4389FDE9535AB702883FFCD98F57A32A8`
- **Header Signature:**
  ```sql
  -- MySQL dump 10.13  Distrib 8.4.0, for Win64 (x86_64)
  -- Host: localhost    Database: local
  -- Server version 8.4.0
  ```
- **Footer Signature:**
  ```sql
  -- Dump completed on 2026-10-15 19:13:22
  ```

### Backup Directory Permissions
- **Target Directory:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\backups`
- **Access Control:** User `DESKTOP-9OVRPV0\Vu Cong Minh` has `FullControl` (`Allow`).
- **Policy Compliance:** No backup file was created or modified during this audit.

### Restore Status
- **Status:** **`DATABASE RESTORE: NOT VERIFIED`**
- **Reason:** While the static integrity of `stage_1a_baseline_backup.sql` is confirmed, no non-destructive restoration test has been conducted on an isolated staging database. Live restoration onto the running database is strictly forbidden in this phase.

---

## 3. Dataset Lock & Expected Counts

The source CSV dataset was inspected in read-only mode without file alteration.

- **File Path:** `wp-content/plugins/woocommerce_furniture_dataset_22_products.csv`
- **File Size:** `16,367 bytes`
- **Line Count:** `65 lines` (1 header row + 64 data rows)
- **SHA-256 Checksum:** `276809899064949CB8D45B8EF7523B8C2913780F0649650EA4EB494FB74894F6`

### Verified Structure Breakdown
| Entity | Expected Count | Verified from CSV | Audit Status |
| :--- | :--- | :--- | :--- |
| **Total Rows (Excl Header)** | 64 | 64 | **MATCH** |
| **Parent Products** | 22 | 22 (12 simple, 10 variable) | **MATCH** |
| **Product Variations** | 42 | 42 | **MATCH** |
| **Total SKUs** | 64 | 64 (0 empty, 0 duplicate, 100% unique) | **MATCH** |
| **Categories** | 7 | 7 (`Beds`, `Chairs`, `Decoration`, `Lighting`, `Sofas`, `Storage`, `Tables`) | **MATCH** |
| **Brands (Attribute)** | 5 | 5 (`LuxeVibe`, `Minimalia`, `NordicLiving`, `OakCraft`, `UrbanoHaus`) | **MATCH** |
| **Colors (Attribute)** | 3 | 3 (`Black`, `Brown`, `White`) | **MATCH** |
| **Sizes (Attribute)** | 3 | 3 (`L`, `M`, `S`) | **MATCH** |
| **Total Declared Stock** | 449 units | 449 units across stock items | **MATCH** |

### Image Connectivity & 404 Lock
- **Total Image URLs in CSV:** 25 unique external URLs.
- **Accessible (HTTP 200):** 23 URLs.
- **Inaccessible (HTTP 404):** 2 URLs.
  1. Row 14 (`FURN-006`, Simple, *Minimalist Geometric Floor Lamp*):
     `https://images.unsplash.com/photo-1507473885765-e6ed057f782c`
  2. Row 49 (`FURN-018`, Variable Parent, *Modular Fabric Sectional Sofa*):
     `https://images.unsplash.com/photo-1555041469-a586c61ea9bc`
- **Image Replacement Status:** **`REPLACEMENT NOT APPROVED`**
- **Core Importer Impact:** In WooCommerce 11.2.1, `wc_rest_upload_image_from_url()` throws an `Exception` when encountering an HTTP 404. Without pre-handling (omitting image column for these rows or supplying verified replacement URLs), the importer will fail on these rows during import.
- **Idempotency Caveat:** WooCommerce CSV Importer's "Update existing products" toggle cannot be assumed idempotent without experimental proof. It does not reliably deduplicate taxonomies or clean up partial variation sets.

---

## 4. Database Baseline Manifest

Direct SQL queries executed against the active runtime database (`local` on port `10011`) established the following pre-import baseline:

### Post Table Baseline
- **`MAX(ID)` in `wp_posts`:** **`12`**
- **Total Posts:** **`12`**
- **Breakdown by `post_type`:**
  - `page`: 7
  - `post`: 2
  - `attachment`: 1 (Post ID `5`, `woocommerce-placeholder.webp`)
  - `revision`: 1
  - `wp_navigation`: 1
  - `product`: **0**
  - `product_variation`: **0**
- **Existing SKUs in `wp_postmeta`:** **0** (No duplicate SKUs, no empty SKUs).

### Taxonomy & Attribute Baseline
- **Global Attributes (`wp_woocommerce_attribute_taxonomies`):** **0**
- **Existing `product_cat` Terms:** 1 (`Uncategorized`, `term_id`: 15, count: 0).
- **Target Categories (`Beds`, `Chairs`, etc.):** **0 created**.
- **Target Brand/Color/Size Terms:** **0 created**.

### Git State
- **Tracked Files:** 100% clean (0 modified, 0 staged).
- **Untracked Files:** Documentation reports in `docs/` and `plugins/docs/`.

### Import Manifest Specification (Future Import Tracking)
To ensure isolation and rollback precision, the import execution MUST NOT assume that all IDs greater than `12` belong to the import batch. The future import MUST generate an exact manifest containing:
```json
{
  "import_timestamp": "YYYY-MM-DDTHH:MM:SSZ",
  "csv_sha256": "276809899064949CB8D45B8EF7523B8C2913780F0649650EA4EB494FB74894F6",
  "imported_product_ids": [ ... ],
  "imported_variation_ids": [ ... ],
  "imported_attachment_ids": [ ... ],
  "created_terms": [
    { "taxonomy": "pa_color", "term_id": ... },
    { "taxonomy": "product_cat", "term_id": ... }
  ],
  "sku_to_post_id_map": { ... }
}
```

---

## 5. Rollback Design & Verified Limitations

The rollback architecture was analyzed in WooCommerce 11.2.1 core source code.

### 1. Product Deletion API Verification
- **API Method:** `wc_get_product( $product_id )->delete( true )`
- **Core Execution Path:**
  1. Triggers `woocommerce_before_delete_{post_type}` hook (`product` or `product_variation`).
  2. Executes `wp_delete_post( $product_id, true )`, which permanently removes the post, associated postmeta, and taxonomy relationships.
  3. Triggers `woocommerce_delete_{post_type}` hook.
  4. Triggers `wc_get_container()->get( ProductAttributesLookupDataStore::class )->on_product_deleted( $product_id )`, cleaning up `wp_wc_product_attributes_lookup`.
  5. For variations, invokes `wc_deferred_product_sync( $parent_id )` to recalculate parent price ranges and stock status.

### 2. Term Recount Tool Verification
- **Tool Key:** `recount_terms`
- **Location:** `includes/rest-api/Controllers/Version2/class-wc-rest-system-status-tools-v2-controller.php` (line 161 & line 626).
- **Execution Function:** `wc_recount_all_terms()`
- **WP-CLI Support:** Accessible via `wp wc tool run recount_terms` or programmatic execution of `wc_recount_all_terms()`.

### 3. Scoped Rollback vs Blind Bulk Deletion
- **Rejection of Blind Deletion:** Deleting posts simply matching `SKU LIKE 'FURN-%'` or `ID > 12` is unsafe because it risks affecting posts created concurrently by WordPress core (auto-drafts, nav menu updates, session transients).
- **Enforced Strategy:** Rollback MUST consume the verified **Import Manifest** containing the explicit array of `imported_product_ids` and `imported_variation_ids`.

### 4. Media & Attachment Rollback Risk
- **Critical Finding:** WooCommerce does NOT delete attachment posts when a product is deleted.
- **Attachment Deduplication:** In `abstract-wc-product-importer.php`, WooCommerce tags media with `_wc_attachment_source = $url`. If multiple products share an image, deleting attachments naively will break valid products.
- **Rollback Requirement:** Attachments must only be deleted if they match `imported_attachment_ids` AND are not referenced as thumbnails/galleries by any non-imported post.

### 5. Partial Import & Orphan Handling
- If an import terminates halfway (e.g. at row 14 due to 404 error):
  - Any orphan variations created without a valid parent must be detected and removed.
  - Incomplete variable parents without variations must be quarantined or deleted based on the manifest.

---

## 6. Open Risks Log

1. **Unsplash 404 Errors:** Rows 14 (`FURN-006`) and 49 (`FURN-018`) will throw unhandled exceptions during core image downloading.
2. **Missing Global Attributes:** The 3 taxonomies (`pa_color`, `pa_size`, `pa_brand`) do not exist in `wp_woocommerce_attribute_taxonomies`. If imported without pre-creation, WooCommerce may register them as custom product-level attributes rather than global filtering taxonomies.
3. **Database Restore Untested:** Live restore of `stage_1a_baseline_backup.sql` has not been rehearsed on a sandbox DB (`DATABASE RESTORE: NOT VERIFIED`).
4. **WP-CLI Importer Native Gap:** WP-CLI core does not ship with `wp wc product import`. Batch import must use WooCommerce core's `WC_Product_CSV_Importer` executed via custom PHP runner or Admin UI importer.

---

## 7. Planned Commands (NOT EXECUTED)

The following commands are formulated and verified for future use, but were **NOT executed** during this stage:

### 1. Pre-Import Snapshot Command (Port 10011)
```powershell
# PLANNED COMMAND - DO NOT RUN UNTIL STAGE 1E.3 AUTHORIZATION
& "C:\Users\Vu Cong Minh\AppData\Roaming\Local\lightning-services\mysql-8.4.0\bin\win64\bin\mysqldump.exe" `
  --host=127.0.0.1 `
  --port=10011 `
  --user=root `
  --password=root `
  --default-character-set=utf8mb4 `
  --single-transaction `
  --routines `
  --triggers `
  local > "C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\backups\stage_1e_pre_import_snapshot.sql"
```

### 2. Term Recount Command (Post-Rollback or Post-Import)
```powershell
# PLANNED COMMAND - DO NOT RUN IN READ-ONLY AUDIT
$env:PATH = "C:\Users\Vu Cong Minh\AppData\Roaming\Local\lightning-services\php-8.2.29+0\bin\win64;" + $env:PATH
php -c "C:\Users\Vu Cong Minh\AppData\Roaming\Local\run\X8slLDe4p\conf\php\php.ini" -r '
  define("WP_USE_THEMES", false);
  require_once "C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/app/public/wp-load.php";
  wc_recount_all_terms();
  echo "Terms recounted successfully.\n";
'
```

---

## 8. Stage-Gate Decision

### Decision: `PASS WITH CONDITIONS`

### Justification:
1. **Database connection target verified:** Active port confirmed as `10011`; port `10007` discrepancy fully resolved.
2. **Existing backup verified:** `stage_1a_baseline_backup.sql` verified statically (valid dump structure, size 1,202,692 bytes, SHA-256 confirmed).
3. **Baseline quantified:** Database confirmed clean (0 products, 0 variations, 0 SKUs, MAX(ID) = 12).
4. **CSV Dataset locked:** Checksum, line counts, 64 SKUs, categories, and attributes 100% matched.
5. **Rollback strategy locked:** Scoped manifest-driven deletion designed and verified against WooCommerce 11.2.1 core APIs.
6. **Strict Invariant Maintained:** Zero database mutations, zero file changes to source data or site configuration.

---

## 9. Conditions Requiring User Approval Before Write Operations

Prior to executing any database mutation, attribute creation, or product import in Stage 1E.3, the user must explicitly approve:

1. **Resolution of 404 Images:**
   - Option A: Import rows 14 (`FURN-006`) and 49 (`FURN-018`) with the image column omitted (assign placeholder).
   - Option B: Provide approved replacement URLs for `FURN-006` and `FURN-018`.
2. **Execution of Pre-Import Snapshot:**
   - Authorize running the verified `mysqldump` command against port `10011` to create `stage_1e_pre_import_snapshot.sql`.
3. **Pre-creation of Global Attributes:**
   - Authorize programmatic creation of global attribute taxonomies (`pa_color`, `pa_size`, `pa_brand`) so variation attributes register as global filterable taxonomies.
4. **Execution of Scoped Import:**
   - Authorize running the import runner in batch mode while capturing the post-import manifest.

---
*Report completed under Stage 1E.2.3 Pre-Import Safety Lock. Execution halted.*
