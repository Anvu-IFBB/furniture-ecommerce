# STAGE 3N-R2 — ISOLATED WORDPRESS/WOOCOMMERCE INTEGRATION VERIFICATION REPORT

**Project:** `furniture-ecommerce` (ATELIER)  
**Execution Timestamp:** 2026-10-19T11:45:00+07:00  
**Environment:** WordPress 7.1.3 | WooCommerce 11.2.1 | PHP 8.2.29 | MySQL 8.4.0 (`127.0.0.1:10011`)  
**Operating Mode:** Provably Isolated Secondary Database Runtime Verification  
**Primary Live Database:** `local` (**STRICTLY UNTOUCHED — 0 Mutations**)  
**Isolated Test Database:** `local_test_isolated_3nr2` (**DEDICATED RUNTIME ENVIRONMENT**)  
**Verdict:** **`PASS`** *(Isolated Runtime Integration Scope; Production Import Remains Strictly Unauthorized)*

---

## 1. Executive Summary & Verification Levels

Stage 3N-R2 independently validates all remediations from Stage 3N-R (F-3N-01, F-3N-02, F-3N-03) using **real WordPress and WooCommerce runtime APIs** (`wp_insert_post`, `update_post_meta`, `get_post_meta`, `wp_delete_post`, `reconcile_import`, `find_posts_by_ownership_tag`, and real SQL execution) executed within a **provably isolated database** (`local_test_isolated_3nr2`).

### Tri-Level Verification Evidence Matrix

| Verification Level | Scope & Mechanism | Tests Executed | Result | Safety & State Guarantee |
| :--- | :--- | :---: | :---: | :--- |
| **Level 1: Static Code Audit** | PHP 8.2 AST syntax lint (`php -l`), type checks, constant bindings | All Files | **PASS** | 0 syntax errors, valid late static binding |
| **Level 2: Isolated Mock Harness** | In-memory `Mock_Post_Database` & `Mock_Wpdb` regression suite | 85 Tests | **85 / 85 PASS** | Deterministic unit validation across edge cases |
| **Level 3: Runtime Integration** | Real WordPress Core 7.1.3 & WooCommerce 11.2.1 runtime on real MySQL engine | 19 Tests | **19 / 19 PASS** | Real SQL execution in `local_test_isolated_3nr2` |
| **Grand Total** | **Combined Multi-Tier Verification Suite** | **104 Tests** | **104 / 104 PASS** | **100% Passing — Zero Live DB Touch** |

---

## 2. Database Isolation Evidence & Proof of Non-Interference

### 2.1 Connection Parameters

- **Database Host:** `127.0.0.1` (or `localhost`)
- **Database Port:** `10011`
- **Database User:** `root`
- **Table Prefix:** `wp_`
- **Active Production Database:** `local`
- **Isolated Secondary Test Database:** `local_test_isolated_3nr2`

### 2.2 Proof of Database Separation

1. **Physical Schema Separation:**  
   `local_test_isolated_3nr2` was created as an independent MySQL database on server port 10011:
   ```sql
   CREATE DATABASE local_test_isolated_3nr2;
   ```
   Core WordPress tables (`wp_posts`, `wp_postmeta`, `wp_terms`, `wp_term_taxonomy`, `wp_term_relationships`, `wp_termmeta`, `wp_options`, `wp_woocommerce_attribute_taxonomies`, `wp_wc_product_meta_lookup`, `wp_wc_category_lookup`) were cloned into `local_test_isolated_3nr2`.
2. **Two-Way Mutual Exclusion (Fail-Closed Barrier):**
   - **Production Harness (`Controlled_Import_Harness`):** Has `const EXPECTED_DB_NAME = 'local'`. If pointed at the test database, it immediately aborts:  
     `Target database mismatch! Expected 'local', found 'local_test_isolated_3nr2'` (Verified in Test A4).
   - **Isolated Harness (`Isolated_Runtime_Import_Harness`):** Has `const EXPECTED_DB_NAME = 'local_test_isolated_3nr2'`. If pointed at the production database, it immediately aborts:  
     `Target database mismatch! Expected 'local_test_isolated_3nr2', found 'local'`.
3. **Active Connection Verification:**
   At runtime, WordPress `$wpdb` executed:
   ```php
   $wpdb->select( 'local_test_isolated_3nr2' );
   ```
   Verified via SQL query `SELECT DATABASE()` returning strictly `'local_test_isolated_3nr2'`.
4. **Zero Production Mutation Proof:**
   Queries executed directly on production database `local` before and after all 19 runtime tests:
   ```sql
   SELECT post_type, COUNT(*) FROM local.wp_posts GROUP BY post_type;
   SELECT option_name, option_value FROM local.wp_options WHERE option_name = 'woocommerce_coming_soon';
   ```
   **Output:**
   - `product` count in `local`: **Exactly 0**
   - `product_variation` count in `local`: **Exactly 0**
   - `attachment` count in `local`: **1 (Placeholder ID 5 preserved)**
   - `woocommerce_coming_soon` setting: **`yes` (Preserved)**

---

## 3. Runtime Environment Specification

- **PHP CLI Executable:** `"C:\Users\Vu Cong Minh\AppData\Roaming\Local\lightning-services\php-8.2.29+0\bin\win64\php.exe"`
- **Active PHP Configuration (`php.ini`):** `"C:\Users\Vu Cong Minh\AppData\Roaming\Local\run\X8slLDe4p\conf\php\php.ini"` (Loaded with `mysqli` extension active)
- **WordPress Root:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public`
- **WordPress Version:** 7.1.3
- **WooCommerce Version:** 11.2.1
- **MySQL Engine:** 8.4.0

---

## 4. Source Code Diff & Integrity

To enable clean object-oriented inheritance while enforcing the two-way mutual exclusion barrier, [run_controlled_import.php](file:///C:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/run_controlled_import.php) was updated to use late static binding (`static::`) instead of `self::` for target database name and table prefix constants.

### 4.1 Git Working Tree Status
```text
On branch master
Changes not staged for commit:
	modified:   ../themes/twentytwentyfive-child/functions.php
	modified:   ../themes/twentytwentyfive-child/style.css
	modified:   ../themes/twentytwentyfive-child/theme.json
Untracked files:
	run_controlled_import.php
	docs/...
```
*(Strict conformance with Safety Invariant #5: Zero Git commit, reset, stash, checkout, or clean performed).*

### 4.2 File Hashes (SHA-256)

| File | Pre-Edit Hash (SHA-256) | Post-Edit Hash (SHA-256) | Status |
| :--- | :--- | :--- | :--- |
| `woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv` | `D2C8566F334A767887F57C4A15EA456CFC5E117767FCFE46C599CC754BC2BBA0` | `D2C8566F334A767887F57C4A15EA456CFC5E117767FCFE46C599CC754BC2BBA0` | **100% Locked & Untouched** |
| `run_controlled_import.php` | `C3B1BF769CFF9127D5F7FA5B0DF23E798A36C0F5524E39C8CBC90102F03E66C3` | `640B92D4F3AFEED52DB88558F0CF93876C4B41468003619BFC934AF4D31B457C` | **Updated with Late Static Binding** |
| `test_stage3nr2_runtime.php` | *(new)* | `0A58BDBB032D2EA9CB0E2EA815E138BFF428F2A22073CB07F3866068E38EBF50` | **19-Test Runtime Suite** |

### 4.3 Code Modification Diff in `run_controlled_import.php`
```diff
@@ -367,7 +367,7 @@
         if ( $wpdb ) {
             $db_name = $this->get_active_db_name( $wpdb );
-            if ( $db_name !== self::EXPECTED_DB_NAME ) {
-                throw new RuntimeException( "Target database mismatch! Expected '" . self::EXPECTED_DB_NAME . "', found '{$db_name}'" );
+            if ( $db_name !== static::EXPECTED_DB_NAME ) {
+                throw new RuntimeException( "Target database mismatch! Expected '" . static::EXPECTED_DB_NAME . "', found '{$db_name}'" );
             }
@@ -372,7 +372,7 @@
             $prefix = isset( $wpdb->prefix ) ? $wpdb->prefix : ( defined( 'TABLE_PREFIX' ) ? TABLE_PREFIX : '' );
-            if ( $prefix !== self::EXPECTED_TABLE_PREFIX ) {
-                throw new RuntimeException( "Target table prefix mismatch! Expected '" . self::EXPECTED_TABLE_PREFIX . "', found '{$prefix}'" );
+            if ( $prefix !== static::EXPECTED_TABLE_PREFIX ) {
+                throw new RuntimeException( "Target table prefix mismatch! Expected '" . static::EXPECTED_TABLE_PREFIX . "', found '{$prefix}'" );
             }
@@ -462,8 +462,8 @@
-            'target_db'                => self::EXPECTED_DB_NAME,
-            'table_prefix'             => self::EXPECTED_TABLE_PREFIX,
+            'target_db'                => static::EXPECTED_DB_NAME,
+            'table_prefix'             => static::EXPECTED_TABLE_PREFIX,
@@ -1088,7 +1088,7 @@
-        $expected_db = self::EXPECTED_DB_NAME;
+        $expected_db = static::EXPECTED_DB_NAME;
@@ -1101,7 +1101,7 @@
-        $expected_prefix = self::EXPECTED_TABLE_PREFIX;
+        $expected_prefix = static::EXPECTED_TABLE_PREFIX;
```

---

## 5. Runtime Integration Test Results (19 / 19 PASS)

The runtime integration test suite was executed via PHP 8.2 CLI with WordPress runtime loaded:
```text
Command: "C:\Users\Vu Cong Minh\AppData\Roaming\Local\lightning-services\php-8.2.29+0\bin\win64\php.exe" -c "...\conf\php\php.ini" test_stage3nr2_runtime.php
Result: 19 executed, 19 passed, 0 failed. Exit code: 0.
```

### 5.1 Section A: Database Isolation Verification (Tests A1 – A4)

| Test ID | Test Description | Expected Result | Actual Result | Status |
| :-: | :--- | :--- | :--- | :-: |
| **A1** | Verify initial connection was `local`, switch to `local_test_isolated_3nr2` | Initial: `local`, Switched: `local_test_isolated_3nr2` | Confirmed initial `local`, active `local_test_isolated_3nr2` | **PASS** |
| **A2** | Verify MySQL host (`localhost`/`127.0.0.1`) and table prefix (`wp_`) | Host contains `127.0.0.1`/`localhost`, prefix `wp_` | Host: `localhost`, Prefix: `wp_` | **PASS** |
| **A3** | Verify live database `local` contains 0 products/variations | Product/variation count = 0 | Count in `local`: 0 | **PASS** |
| **A4** | Two-way mutual exclusion between production and test harness | Prod harness rejects test DB; isolated harness accepts test DB | Verified two-way fail-closed rejection | **PASS** |

### 5.2 Section B: Crash Recovery in Real WordPress Runtime (Tests B1 – B4)

| Test ID | Test Description | Expected Result | Actual Result | Status |
| :-: | :--- | :--- | :--- | :-: |
| **B1** | Unmanifested tagged product in real DB discovered and rolled back (Window 3) | Real SQL query discovers post ID, deletes via `wp_delete_post`, confirms absent | Post ID 17 discovered via SQL and deleted; DB check = null | **PASS** |
| **B2** | Object belonging to another run protected in real database | Post with foreign run tag is NOT discovered or deleted | Post ID 18 preserved intact in database | **PASS** |
| **B3** | Object without ownership tag preserved (Crash Window 2 boundary) | Post without tag is untouched at fail-closed boundary | Post ID 19 safely untouched; requires snapshot restore | **PASS** |
| **B4** | Database ownership query failure fails closed | Throws RuntimeException, performs 0 deletions | Caught: Database query failed while searching tags | **PASS** |

### 5.3 Section C: Per-SKU Reconciliation in Real Runtime (Tests C1 – C6)

| Test ID | Test Description | Expected Result | Actual Result | Status |
| :-: | :--- | :--- | :--- | :-: |
| **C1** | Read approved CSV directly, verify SHA-256, parse all 64 rows | SHA-256 matches locked hash, exactly 64 rows parsed | SHA-256 verified; 64 SKU rows parsed directly from file | **PASS** |
| **C2** | Cross-verify embedded matrix 100% against CSV lines (449 stock) | All 64 SKUs, types, parent links, and stocks match CSV | All 64 entries match CSV 1:1; total stock = 449 | **PASS** |
| **C3** | Runtime reconciliation succeeds on valid 64-SKU canonical dataset | Status `PASSED`, 64 unique SKUs, 449 total stock | Status: `PASSED`, SKUs: 64, Stock: 449 | **PASS** |
| **C4** | **Compensating stock error caught despite identical total stock (449)** | Reconciler catches `FURN-001` (stock 30 vs 25) even though sum = 449 | Caught: Stock quantity mismatch for SKU 'FURN-001'. Expected 25, found 30 | **PASS** |
| **C5** | Variation linked to incorrect parent SKU rejected in runtime | Reconciler rejects `FURN-013-BLK-S` linked to `FURN-014` | Caught: Variation linked to parent SKU 'FURN-014', expected 'FURN-013' | **PASS** |
| **C6** | Variable parent with direct stock > 0 rejected in runtime | Reconciler rejects variable parent `FURN-013` with direct stock 10 | Caught: Stock quantity mismatch for SKU 'FURN-013'. Expected 0, found 10 | **PASS** |

### 5.4 Section D: Rollback Integrity & Deletion Verification in Real Runtime (Tests D1 – D5)

| Test ID | Test Description | Expected Result | Actual Result | Status |
| :-: | :--- | :--- | :--- | :-: |
| **D1** | Real deletion succeeds and post-deletion query confirms absence | `wp_delete_post` deletes posts; SQL check confirms null; status `ROLLED_BACK` | Parent 20 & Variation 21 deleted; manifest status `ROLLED_BACK` | **PASS** |
| **D2** | Real deletion API failure halts and marks `FAILED_ROLLBACK_INCOMPLETE` | Filter returns false; rollback halts; manifest status updated | Caught deletion failure, marked `FAILED_ROLLBACK_INCOMPLETE` | **PASS** |
| **D3** | Variation deletion failure prevents parent deletion in real runtime | Parent remains intact in database when variation deletion fails | Parent 23 preserved intact in DB when variation 24 failed | **PASS** |
| **D4** | Consecutive rollback executions return cleanly as idempotent no-op | 1st run deletes 1 post; 2nd run safely deletes 0 posts | 1st deleted 1, 2nd deleted 0; status `ROLLED_BACK` | **PASS** |
| **D5** | Final post-audit of live database `local` confirms 0 modifications | Product count in `local` = 0; Coming Soon = `yes` | Confirmed count in `local`: 0; live DB 100% untouched | **PASS** |

---

## 6. Real Runtime Reconciliation vs Approved CSV

Ground truth verification:  
Tệp CSV: `woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv`  
SHA-256: `D2C8566F334A767887F57C4A15EA456CFC5E117767FCFE46C599CC754BC2BBA0` (Xác minh trực tiếp bằng mã SHA-256 byte-level).

### 6.1 Tổng kết Đối chiếu Tồn kho theo SKU Thực tế

- **12 Simple Products:**
  - `FURN-001` (25), `FURN-002` (30), `FURN-003` (15), `FURN-004` (20), `FURN-005` (18), `FURN-006` (12), `FURN-007` (40), `FURN-008` (22), `FURN-009` (50), `FURN-010` (0), `FURN-011` (16), `FURN-012` (0).
  - **Tổng tồn kho Simple:** **248**
- **10 Variable Parents:**
  - `FURN-013` đến `FURN-022`: Tồn kho trực tiếp = **0** (Quy tắc bắt buộc: variable parent không quản lý stock trực tiếp).
- **42 Child Variations:**
  - 42 SKU biến thể với số lượng tồn kho từ 2 đến 10 theo đúng bảng thuộc tính phân loại (Color / Size).
  - **Tổng tồn kho Variations:** **201**
- **Toàn bộ Catalog:** **64 Unique SKUs** | **Tổng tồn kho: 449**

### 6.2 Bằng chứng Phát hiện Lỗi Bù trừ (Compensating Error)

Trong Test C4 tại môi trường WordPress runtime:
- `FURN-001` tăng từ 25 lên 30 (+5)
- `FURN-002` giảm từ 30 xuống 25 (-5)
- **Tổng tồn kho toàn catalog:** $248 + 201 = 449$ (Hoàn toàn bằng nhau!).
- **Kết quả đối soát runtime:** Bộ validator mới lập tức ném ngoại lệ:  
  `Reconciliation failed: Stock quantity mismatch for SKU 'FURN-001'. Expected 25, found 30.`  
  **Chứng minh:** Hệ thống phát hiện chính xác sai lệch từng SKU kể cả khi tổng tồn kho catalog không đổi.

---

## 7. Rủi ro Còn lại & Ranh giới Fail-Closed (Unresolved Limitations)

1. **Giới hạn Cố hữu của Crash Window 2 (Tạo Post thành công nhưng Crash trước khi gán Meta Tag):**
   Nếu tiến trình PHP bị dừng đột ngột (OOM killer, segfault, ngắt nguồn) tại đúng khoảng thời gian giữa `wp_insert_post` và `update_post_meta(_controlled_import_run_id)`, bản ghi post đã tồn tại trong MySQL nhưng hoàn toàn không có metadata định danh run.
   - **Ranh giới an toàn:** Importer **tuyệt đối không xóa phỏng đoán** dựa trên thời gian tạo hoặc SKU, vì có thể xóa nhầm bài viết của người quản trị website.
   - **Phương án xử lý:** Trường hợp hy hữu này được xếp vào danh mục **`NOT VERIFIED FOR AUTO-RECOVERY`** và bắt buộc phải phục hồi qua snapshot database (Gate 3I / Gate 2 snapshot) hoặc quy trình đối soát thủ công có ghi log.
2. **Thiếu hỗ trợ Distributed ACID Transaction của WordPress Core:**
   WordPress core không hỗ trợ 2-phase commit hay transaction phân tán bao trùm cả filesystem (upload ảnh) và MySQL. Harness Stage 3N-R khắc phục triệt để bằng mô hình manifest bền vững, interception hook và rollback xác minh hai chiều.

---

## 8. Kết luận & Điểm dừng Bắt buộc

### Kết luận Chung
- **Section A (Xác minh cách ly):** `PASS` (Database `local_test_isolated_3nr2` tách biệt 100%, 0 tác động đến `local`).
- **Section B (Crash recovery runtime):** `PASS` (Truy vấn ownership tag bằng SQL thật, phục hồi unmanifested object an toàn, fail-closed ở Window 2).
- **Section C (SKU reconciliation runtime):** `PASS` (Đối chiếu 64 SKU trực tiếp từ CSV, bắt lỗi bù trừ, kiểm tra parent link và stock variable).
- **Section D (Rollback integrity runtime):** `PASS` (Kiểm tra API return value, đối soát biến mất khỏi DB bằng SQL, bảo vệ parent khi variation lỗi, idempotent hoàn hảo).

> [!CAUTION]
> **MANDATORY STOP — ĐIỂM DỪNG BẮT BUỘC ĐƯỢC THI HÀNH NGHIÊM NGẶT**
> 1. **KHÔNG** chạy importer trên database WordPress hiện tại (`local` tại `127.0.0.1:10011`).
> 2. **KHÔNG** import 22 sản phẩm thật vào website ATELIER.
> 3. **KHÔNG** bắt đầu Gate 4.
> 4. **KHÔNG** thay đổi cài đặt Coming Soon (`woocommerce_coming_soon` vẫn là `yes`).
> 5. **KHÔNG** thực hiện Git commit, reset, stash, checkout hoặc clean.
> 6. Mọi công việc đã dừng lại hoàn toàn để chờ chỉ đạo độc lập từ người dùng.
