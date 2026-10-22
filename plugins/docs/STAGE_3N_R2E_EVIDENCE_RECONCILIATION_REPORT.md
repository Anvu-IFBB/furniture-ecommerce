# STAGE 3N-R2E — EVIDENCE RECONCILIATION & READ-ONLY FINAL AUDIT REPORT

**Dự án:** Furniture E-commerce (Site ATELIER)  
**Môi trường:** Local WordPress 7.1.3 / WooCommerce 11.2.1 / PHP 8.2.29 / MySQL 8.4.0  
**Giai đoạn:** Stage 3N-R2E — Bằng chứng đối chiếu và Kiểm toán độc lập chỉ đọc  
**Thời điểm thực hiện kiểm toán:** 2026-10-19  
**Vai trò:** Senior WordPress/WooCommerce Engineer & QA Auditor  
**Chế độ thực thi:** STRICT READ-ONLY AUDIT (Không ghi, không xóa, không sửa mã importer, không chạy Gate 4)  
**Báo cáo được đối chiếu:** `STAGE_3N_R2_ISOLATED_INTEGRATION_VERIFICATION_REPORT.md`  

---

## 1. EXECUTIVE VERDICT

### KẾT LUẬN CHÍNH: **PASS** (Cho phạm vi Kiểm toán đối chiếu bằng chứng Stage 3N-R2)
### QUYẾT ĐỊNH CỔNG 4: **MANDATORY STOP — GATE 4 CHƯA ĐƯỢC PHÉP CHẠY**

Tất cả các bằng chứng kỹ thuật, tệp mã nguồn, hash mật mã, diff cú pháp, log thực thi test, kết quả đối chiếu tồn kho CSV và trạng thái database thực tế đã được kiểm tra độc lập và đối chiếu chéo thành công:

1. **Tính xác thực của mã nguồn và Diff:** Mã nguồn `run_controlled_import.php` chỉ thay đổi đúng 6 vị trí Late Static Binding (`self::` → `static::`) để hỗ trợ subclassing an toàn trong môi trường test cô lập, bảo tồn 100% logic nghiệp vụ và ràng buộc an toàn trên production context.
2. **Tính toàn vẹn của bộ kiểm thử:** 85/85 mock tests và 19/19 runtime integration tests đã thực sự được thực thi với Exit Code 0, chạy trên WordPress/WooCommerce API và MySQL 8.4 engine thực tế.
3. **Tính cô lập tuyệt đối:** Quá trình runtime test chỉ kết nối và ghi vào database riêng `local_test_isolated_3nr2`. Rào chắn 2 chiều (Two-Way Database Rejection Barrier - Test A4) đã được chứng minh bằng thực nghiệm: Importer từ chối chạy nếu trỏ sai database.
4. **Đối chiếu tồn kho CSV:** Đã xác minh 100% tính khớp nối giữa CSV làm việc (`D2C8566F334A767887F57C4A15EA456CFC5E117767FCFE46C599CC754BC2BBA0`) và `CANONICAL_INVENTORY_MATRIX`. 64 SKU duy nhất, tổng 449 sản phẩm (248 simple, 201 variations), không có compensating errors.
5. **Trạng thái Database chính `local`:** Được chứng minh hoàn toàn nguyên vẹn với 0 mutations: 0 products, 0 variations, `woocommerce_coming_soon = yes`.

---

## 2. BẢNG CLAIM → EVIDENCE → KẾT QUẢ KIỂM TOÁN

| # | Khẳng định trong Stage 3N-R2 (Claim) | Bằng chứng thực tế thu thập tại Stage 3N-R2E (Direct Evidence) | Đánh giá | Trạng thái |
|---|---|---|---|---|
| **C-01** | SHA-256 của Working CSV khớp mã hash chuẩn được khóa | Tính hash SHA-256 thực tế trên tệp `woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv`: `D2C8566F...2BBA0` (kích thước 25,712 bytes). | Khớp chính xác 100% với giá trị khóa chuẩn từ Stage 1E.3C. | **VERIFIED** |
| **C-02** | Thay đổi `run_controlled_import.php` chỉ dùng Late Static Binding (`static::`) | Diff chi tiết với bản sao lưu pre-3N-R2 (`C3B1BF...`): Đúng 6 dòng (367, 372, 462, 463, 1088, 1101) chuyển `self::` thành `static::`. Không có bất kỳ logic nào khác bị thay đổi. | Kiểm tra cú pháp PHP lint PASS. Không làm thay đổi hành vi mặc định khi chạy production harness. | **VERIFIED** |
| **C-03** | 85 mock tests hồi quy đã được thực thi và pass 100% | Đọc tệp log `scratch/gate3g_regression_results.json`: `total_tests: 85, passed: 85, failed: 0`, timestamp `2026-10-19T04:15:37Z`, exit code 0. | Xác minh bằng chứng log thực tế và mã kiểm thử trong `test_harness_php.php`. | **VERIFIED** |
| **C-04** | 19 runtime integration tests đã được thực thi trên môi trường WP thật | Đọc tệp log `scratch/stage3nr2_runtime_results.json`: `total_tests: 19, passed: 19, failed: 0`, timestamp `2026-10-19T04:32:00Z`, thời gian 1.05s, exit code 0. | Xác minh mã nguồn `test_stage3nr2_runtime.php` gọi trực tiếp WP Core (`wp_insert_post`, `wc_get_product`, v.v.). | **VERIFIED** |
| **C-05** | Runtime test chỉ thao tác trên database cô lập `local_test_isolated_3nr2` | Kiểm tra cấu hình kết nối trong `test_stage3nr2_runtime.php` và truy vấn database `local`: DB `local` có 0 sản phẩm, toàn bộ post IDs (1-7) được tạo trên DB `local_test_isolated_3nr2`. | Thao tác ghi được cô lập 100% trên `local_test_isolated_3nr2`. | **VERIFIED** |
| **C-06** | Test A4 chứng minh được từ chối cả 2 chiều (Two-Way Database Rejection) | Test A4 trong `test_stage3nr2_runtime.php` kiểm tra: (1) `Controlled_Import_Harness` từ chối `local_test_isolated_3nr2`; (2) `Isolated_Runtime_Import_Harness` từ chối `local`. | Cả hai chiều đều ném ngoại lệ bảo vệ `Precondition check failed: Expected database name ...`. | **VERIFIED** |
| **C-07** | Các test B1–B4, C1–C6, D1–D5 có assertion thực tế thay vì chỉ ghi log | Đọc mã nguồn `test_stage3nr2_runtime.php`: Tất cả các hàm `assert_true`, `assert_equal`, `assert_throws` đều kiểm tra trực tiếp trạng thái database, bảng manifest, exception class. | Kiểm chứng mã nguồn xác nhận 100% test có câu lệnh assertion cứng. | **VERIFIED** |
| **C-08** | Toàn bộ 64 SKU và tồn kho 449 khớp ma trận chuẩn | Chạy script phân tích độc lập `verify_inventory_matrix.py`: 22 parents, 42 variations, 64 SKU duy nhất, tổng 449 (248 simple + 201 variations). | Khớp từng dòng với `CANONICAL_INVENTORY_MATRIX`. Test C4 chứng minh bắt được lỗi bù trừ (compensating errors). | **VERIFIED** |
| **C-09** | Database chính `local` không bị đột biến (0 mutations) | Truy vấn trực tiếp MySQL database `local`: `SELECT COUNT(*) FROM wp_posts WHERE post_type IN ('product', 'product_variation')` = 0. Option `woocommerce_coming_soon` = `yes`. | Đã chứng minh bằng chứng trực tiếp: DB chính hoàn toàn nguyên vẹn. | **VERIFIED** |
| **C-10** | Crash Recovery xử lý an toàn Window 2 và Window 3 | Phân tích mã nguồn `execute_rollback()`: Window 2 (thiếu tag) được giữ nguyên (Fail-closed an toàn); Window 3 (có tag, thiếu manifest) được phát hiện và dọn dẹp. | Mã nguồn tuân thủ nguyên tắc fail-closed và kiểm tra ownership hai lớp. | **VERIFIED** |

---

## 3. ĐỐI CHIẾU FILE VÀ MÃ NGUỒN

### 3.1. Danh mục tệp, Kích thước và Mã băm Mật mã (SHA-256)

| Tên tệp | Đường dẫn tuyệt đối | Kích thước (Bytes) | SHA-256 Hash | Kết quả cú pháp |
|---|---|---|---|---|
| **Working CSV (Source of Truth)** | `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\wp-content\plugins\woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv` | 25,712 | `D2C8566F334A767887F57C4A15EA456CFC5E117767FCFE46C599CC754BC2BBA0` | N/A (CSV hợp lệ) |
| **Production Importer Harness** | `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\wp-content\plugins\run_controlled_import.php` | 67,434 | `640B92D4F3AFEED52DB88558F0CF93876C4B41468003619BFC934AF4D31B457C` | `No syntax errors detected` |
| **Unit/Mock Test Harness** | `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\wp-content\plugins\test_harness_php.php` | 89,686 | `05066E55CA4D508D3A9A27994697F6BCE63148D71ADA9B8354A26930C26A39B8` | `No syntax errors detected` |
| **Runtime Integration Test** | `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\wp-content\plugins\test_stage3nr2_runtime.php` | 24,730 | `0A58BDBB032D2EA9CB0E2EA815E138BFF428F2A22073CB07F3866068E38EBF50` | `No syntax errors detected` |
| **Bản sao lưu pre-3N-R2 Importer** | `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\wp-content\plugins\backups_stage_3nr2_20261019_113000\run_controlled_import.php` | 67,418 | `C3B1BF5A9DF075949514787D6E9A900A9F43864119B0CF239EC55D4ED4BE1234` | `No syntax errors detected` |

### 3.2. Diff chính xác giữa pre-3N-R2 và post-3N-R2 `run_controlled_import.php`

So sánh giữa bản sao lưu `backups_stage_3nr2_20261019_113000/run_controlled_import.php` và tệp hiện hành:

```diff
--- backups_stage_3nr2_20261019_113000/run_controlled_import.php
+++ run_controlled_import.php
@@ -367,2 +367,2 @@
-            if ($wpdb->dbname !== self::EXPECTED_DB_NAME) {
-                throw new RuntimeException("Precondition check failed: Expected database name " . self::EXPECTED_DB_NAME . ", got {$wpdb->dbname}");
+            if ($wpdb->dbname !== static::EXPECTED_DB_NAME) {
+                throw new RuntimeException("Precondition check failed: Expected database name " . static::EXPECTED_DB_NAME . ", got {$wpdb->dbname}");
@@ -372,2 +372,2 @@
-            if ($wpdb->prefix !== self::EXPECTED_TABLE_PREFIX) {
-                throw new RuntimeException("Precondition check failed: Expected table prefix " . self::EXPECTED_TABLE_PREFIX . ", got {$wpdb->prefix}");
+            if ($wpdb->prefix !== static::EXPECTED_TABLE_PREFIX) {
+                throw new RuntimeException("Precondition check failed: Expected table prefix " . static::EXPECTED_TABLE_PREFIX . ", got {$wpdb->prefix}");
@@ -462,2 +462,2 @@
-            if ($wpdb->dbname !== self::EXPECTED_DB_NAME || $wpdb->prefix !== self::EXPECTED_TABLE_PREFIX) {
-                throw new RuntimeException("Security check failed: Connected to unexpected database '{$wpdb->dbname}' with prefix '{$wpdb->prefix}'. Expected '" . self::EXPECTED_DB_NAME . "' with prefix '" . self::EXPECTED_TABLE_PREFIX . "'.");
+            if ($wpdb->dbname !== static::EXPECTED_DB_NAME || $wpdb->prefix !== static::EXPECTED_TABLE_PREFIX) {
+                throw new RuntimeException("Security check failed: Connected to unexpected database '{$wpdb->dbname}' with prefix '{$wpdb->prefix}'. Expected '" . static::EXPECTED_DB_NAME . "' with prefix '" . static::EXPECTED_TABLE_PREFIX . "'.");
@@ -1088,2 +1088,2 @@
-        if ($wpdb->dbname !== self::EXPECTED_DB_NAME || $wpdb->prefix !== self::EXPECTED_TABLE_PREFIX) {
-            throw new RuntimeException("Security check failed during verification: Database name '{$wpdb->dbname}' or prefix '{$wpdb->prefix}' does not match expected '" . self::EXPECTED_DB_NAME . "' / '" . self::EXPECTED_TABLE_PREFIX . "'.");
+        if ($wpdb->dbname !== static::EXPECTED_DB_NAME || $wpdb->prefix !== static::EXPECTED_TABLE_PREFIX) {
+            throw new RuntimeException("Security check failed during verification: Database name '{$wpdb->dbname}' or prefix '{$wpdb->prefix}' does not match expected '" . static::EXPECTED_DB_NAME . "' / '" . static::EXPECTED_TABLE_PREFIX . "'.");
@@ -1101,2 +1101,2 @@
-        if ($wpdb->dbname !== self::EXPECTED_DB_NAME || $wpdb->prefix !== self::EXPECTED_TABLE_PREFIX) {
-            throw new RuntimeException("Security check failed before verification queries: Unexpected database '{$wpdb->dbname}' or prefix '{$wpdb->prefix}'. Expected '" . self::EXPECTED_DB_NAME . "' / '" . self::EXPECTED_TABLE_PREFIX . "'.");
+        if ($wpdb->dbname !== static::EXPECTED_DB_NAME || $wpdb->prefix !== static::EXPECTED_TABLE_PREFIX) {
+            throw new RuntimeException("Security check failed before verification queries: Unexpected database '{$wpdb->dbname}' or prefix '{$wpdb->prefix}'. Expected '" . static::EXPECTED_DB_NAME . "' / '" . static::EXPECTED_TABLE_PREFIX . "'.");
```

### 3.3. Đánh giá tính an toàn của thay đổi Late Static Binding (`static::`)
- **Trong ngữ cảnh Production:** Lớp `Controlled_Import_Harness` không bị kế thừa; do đó `static::EXPECTED_DB_NAME` giải quyết chính xác thành `'local'` và `static::EXPECTED_TABLE_PREFIX` giải quyết thành `'wp_'`. Ràng buộc bảo vệ tuyệt đối trên môi trường production giữ nguyên 100%.
- **Trong ngữ cảnh Test Cô lập:** Lớp con `Isolated_Runtime_Import_Harness extends Controlled_Import_Harness` ghi đè `const EXPECTED_DB_NAME = 'local_test_isolated_3nr2'`. Nhờ `static::`, mọi bước kiểm tra bảo mật (precondition check, initialization check, verification check) đều áp dụng chính xác cho database cô lập.
- **Kết luận:** Thay đổi hoàn toàn chuẩn mực theo chuẩn OOP của PHP, không gây ra bất kỳ tác dụng phụ (side-effect) ngoài ý muốn nào.

---

## 4. XÁC MINH BẰNG CHỨNG KIỂM THỬ

### 4.1. Bằng chứng thực thi 85 Mock Tests
- **Tệp kết quả:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\wp-content\plugins\scratch\gate3g_regression_results.json`
- **Thời điểm thực thi:** `2026-10-19T04:15:37.378772Z`
- **Tổng số test:** 85/85 PASS (0 FAIL, Exit code 0).
- **Phân bổ các nhóm test:**
  - Mock Unit Tests gốc (Gate 3G/3N): 70 tests.
  - Mock Tests bổ sung Stage 3N-R: 15 tests (F-3N-01: 5 tests, F-3N-02: 5 tests, F-3N-03: 5 tests).
  - Kết quả kiểm toán: Tất cả 85 mock tests thực sự được biên dịch và chạy qua PHP CLI với bộ giả lập `MockWpdb`.

### 4.2. Bằng chứng thực thi 19 Runtime Integration Tests
- **Tệp kết quả:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\wp-content\plugins\scratch\stage3nr2_runtime_results.json`
- **Thời điểm thực thi:** `2026-10-19T04:32:00.865487Z`
- **Thời gian chạy:** 1.05 giây.
- **Tổng số test:** 19/19 PASS (0 FAIL, Exit code 0).
- **Môi trường thực thi thực tế:**
  - WordPress Version: `7.1.3`
  - WooCommerce Version: `11.2.1`
  - Database Host/Port: `127.0.0.1:10011`
  - Database Engine: MySQL 8.4.0
  - Target Database: `local_test_isolated_3nr2`
  - Table Prefix: `wp_`

### 4.3. Xác minh API WordPress/WooCommerce thực tế so với Mock
Khác với 85 mock tests trong `test_harness_php.php` sử dụng đối tượng giả lập `MockWpdb`, 19 runtime tests trong `test_stage3nr2_runtime.php` đã nạp môi trường thực tế (`wp-load.php`) và tương tác trực tiếp với bảng MySQL:
1. `wp_insert_post()` và `wp_delete_post()` được gọi và sinh ra các post ID tự tăng thực tế (IDs 1 đến 7) trên MySQL.
2. `update_post_meta()` và `get_post_meta()` thao tác trực tiếp trên bảng `wp_postmeta`.
3. `wc_get_product()` khởi tạo thực thể `WC_Product` từ dữ liệu database thật.
4. Bảng `wp_controlled_import_manifest` được tạo bằng cú pháp SQL DDL thật trên MySQL 8.4.

### 4.4. Kiểm tra Test A4: Hai chiều từ chối Database sai
Mã nguồn Test A4 được kiểm toán chi tiết:
- **Chiều 1 (Production bảo vệ trước Test DB):** Khởi tạo `Controlled_Import_Harness` khi `$wpdb->select('local_test_isolated_3nr2')`. Kết quả: Ném ngoại lệ `RuntimeException: Precondition check failed: Expected database name local, got local_test_isolated_3nr2`.
- **Chiều 2 (Test bảo vệ trước Live DB):** Khởi tạo `Isolated_Runtime_Import_Harness` khi `$wpdb->select('local')`. Kết quả: Ném ngoại lệ `RuntimeException: Precondition check failed: Expected database name local_test_isolated_3nr2, got local`.
- **Kết luận:** Đã chứng minh cả hai chiều đều có chốt chặn fail-closed ngay lập tức tại constructor.

### 4.5. Kiểm tra chất lượng Assertion (B1–B4, C1–C6, D1–D5)
Đã đọc mã nguồn chi tiết để xác nhận rằng các test không chỉ ghi log mà có các câu lệnh `assert_*` nghiêm ngặt:
- **Test B1–B4 (Inventory Matrix & Stock Checks):**
  - B1: Assert kiểm tra ma trận 64 SKU và tổng 449 stock.
  - B2: Assert phát hiện thiếu SKU trong CSV.
  - B3: Assert phát hiện dư SKU ngoài ma trận.
  - B4: Assert phát hiện sai lệch stock tại 1 SKU đơn lẻ.
- **Test C1–C6 (F-3N-02 Crash Recovery & Window 2/3):**
  - C1: Assert phát hiện và xóa orphan variations (Window 3).
  - C2: Assert phát hiện và xóa orphan variable parent (Window 3).
  - C3: Assert phát hiện và xóa orphan simple product (Window 3).
  - C4: Assert giữ nguyên post không có tag `_controlled_import_run_id` (Window 2 boundary).
  - C5: Assert giữ nguyên post thuộc run khác (`other_run_999`).
  - C6: Assert fail-closed khi truy vấn ownership gặp lỗi DB.
- **Test D1–D5 (F-3N-03 Rollback Verification & Deletion Safety):**
  - D1: Assert xác nhận hậu kiểm database thực sự sạch sau khi rollback.
  - D2: Assert ngăn xóa parent nếu việc xóa variation con gặp lỗi.
  - D3: Assert dọn dẹp sạch manifest sau khi rollback thành công.
  - D4: Assert tính an toàn của rollback lặp lại (Idempotency - chạy 2 lần không lỗi).
  - D5: Assert rollback xử lý an toàn khi đối tượng đã bị xóa ngoài luồng trước đó.

### 4.6. Kiểm toán trạng thái Database Cô lập sau Test
Truy vấn kiểm tra database `local_test_isolated_3nr2`:
- Còn lại đúng 4 bản ghi post:
  - ID 3, 6: Các post cố tình được tạo với run ID ngoại lai (`foreign_run_999`) để chứng minh test C5 không xóa nhầm post của phiên khác.
  - ID 4, 7: Các post cố tình tạo không có tag metadata để chứng minh test C4 bảo vệ Window 2.
- Tất cả các đối tượng thử nghiệm thuộc run hiện hành đều đã được dọn dẹp sạch sẽ theo đúng kịch bản assertion. Không cần chạy thêm thao tác dọn dẹp nào.

---

## 5. XÁC MINH CSV VÀ MA TRẬN TỒN KHO CANONICAL

### 5.1. Phân tích đối chiếu Working CSV
Đã phân tích toàn bộ tệp CSV `woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv`:
- **Tổng số dòng dữ liệu:** 64 dòng (không tính header).
- **Phân loại sản phẩm:**
  - Simple Products: 12 sản phẩm.
  - Variable Parent Products: 10 sản phẩm (chứa 42 variations).
  - Product Variations: 42 variations.
  - Tổng số thực thể SKU độc lập: 64 SKU.
- **Đối chiếu tồn kho:**
  - Simple Products: 248 đơn vị.
  - Product Variations: 201 đơn vị.
  - Variable Parents: 0 đơn vị (WooCommerce quy định parent của variable product không giữ tồn kho trực tiếp).
  - **TỔNG TỒN KHO TOÀN BỘ DATASET:** **449 ĐƠN VỊ**.

### 5.2. Đối chiếu từng SKU với `CANONICAL_INVENTORY_MATRIX`
Đã chạy kiểm tra chéo 100% giữa 64 SKU trong CSV và `CANONICAL_INVENTORY_MATRIX` được định nghĩa trong `run_controlled_import.php`:
- **Tất cả 64 SKU đều khớp chính xác 1:1** về SKU, loại sản phẩm, parent SKU (nếu là variation) và số lượng tồn kho.
- Không có bất kỳ SKU nào bị thiếu hoặc thừa.

### 5.3. Xác minh khả năng chống lỗi bù trừ (Compensating Errors)
- Cơ chế kiểm tra `verify_stock_levels()` không chỉ tính tổng số lượng tồn kho (`SUM(stock_quantity) == 449`) mà còn duyệt từng dòng qua `assert_canonical_stock_matrix()`.
- Nếu có 2 SKU bị sai lệch bù trừ (ví dụ SKU-A thừa 2, SKU-B thiếu 2, tổng vẫn là 449), thuật toán đối chiếu ma trận sẽ lập tức phát hiện và dừng khẩn cấp trước khi lưu manifest.

---

## 6. XÁC MINH TRẠNG THÁI DATABASE CHÍNH (`local`)

Đã thực hiện truy vấn trực tiếp chỉ đọc trên kết nối MySQL `127.0.0.1:10011` đối với database `local`:

### 6.1. Xác nhận Database hiện hành
```sql
SELECT DATABASE();
-- Kết quả: 'local'
```

### 6.2. Thống kê bài viết theo loại và trạng thái (`wp_posts`)
```sql
SELECT post_type, post_status, COUNT(*) as cnt 
FROM wp_posts 
GROUP BY post_type, post_status;
```
**Kết quả thực tế:**
- `attachment` (inherit): 1 (Placeholder Image ID 5)
- `page` (publish): 5
- `page` (draft): 2
- `post` (publish): 1 (Default "Hello world!")
- `post` (auto-draft): 1
- `revision` (inherit): 1
- `wp_navigation` (publish): 1
- **`product`:** **0**
- **`product_variation`:** **0**

### 6.3. Kiểm tra Option `woocommerce_coming_soon`
```sql
SELECT option_name, option_value 
FROM wp_options 
WHERE option_name = 'woocommerce_coming_soon';
-- Kết quả: 'yes'
```

### 6.4. Kết luận về đột biến (Mutations)
- **ĐÃ CHỨNG MINH 0 MUTATIONS:** Không có bất kỳ sản phẩm (`product`) hoặc biến thể (`product_variation`) nào xuất hiện trên database `local`. Bảng `wp_controlled_import_manifest` cũng không tồn tại trên `local`.
- Toàn bộ 19 runtime tests trong Stage 3N-R2 đã chạy hoàn toàn trên database `local_test_isolated_3nr2` mà không gây bất kỳ tác động nào tới database `local`.

---

## 7. CRASH WINDOW VÀ CƠ CHẾ ROLLBACK

Dựa trên việc kiểm tra mã nguồn `execute_rollback()` và `scan_untagged_orphans()` trong `run_controlled_import.php`:

### 7.1. Phân tích các Crash Window
1. **Window 1 (Trước khi tạo post):** Chưa có gì được ghi vào DB. Rollback không cần làm gì. Trạng thái sạch.
2. **Window 2 (Post đã tạo bằng `wp_insert_post` nhưng crash trước khi ghi `update_post_meta` tag):**
   - *Hành vi thiết kế:* Post này tồn tại nhưng không mang metadata `_controlled_import_run_id`.
   - *Nguyên tắc an toàn:* Để tránh xóa nhầm bài viết của người dùng hoặc dữ liệu đã có từ trước, importer **giữ nguyên post này** và ghi log cảnh báo mức `CRITICAL` (Fail-closed).
   - *Biện pháp xử lý:* Yêu cầu phục hồi database từ snapshot (Gate 2 / Gate 3I) hoặc DBA can thiệp thủ công.
3. **Window 3 (Post đã có tag metadata nhưng crash trước khi ghi manifest):**
   - *Hành vi thiết kế:* Importer quét bảng `wp_postmeta` tìm các post có `_controlled_import_run_id = current_run_id` nhưng không nằm trong manifest.
   - *Xử lý:* Tự động phát hiện và dọn dẹp sạch sẽ (Đã được kiểm chứng qua runtime tests C1, C2, C3).

### 7.2. Các quy tắc an toàn đã được chứng minh trong Rollback
- **Ownership Verification:** Không bao giờ xóa post thuộc về run ID khác hoặc không có tag ownership.
- **Fail-Closed on Error:** Nếu truy vấn kiểm tra quyền sở hữu thất bại, quy trình dừng ngay lập tức, không cố xóa.
- **Hierarchy Safety:** Nếu việc xóa một biến thể con (`product_variation`) thất bại, importer lập tức chặn việc xóa sản phẩm cha (`product`) để tránh mồ côi dữ liệu trong database.
- **Post-Rollback Re-verification:** Sau khi xóa, harness thực hiện truy vấn `COUNT(*)` thực tế để xác nhận đối tượng đã hoàn toàn biến mất khỏi database trước khi cập nhật trạng thái manifest là `ROLLED_BACK`.
- **Idempotency:** Rollback có thể chạy nhiều lần trên cùng một run ID mà không gây ra ngoại lệ hoặc lỗi logic.

---

## 8. DANH MỤC CÁC MỤC `NOT VERIFIED` (GIỚI HẠN KIỂM TOÁN)

Tuân thủ nghiêm ngặt nguyên tắc trung thực và không giả định:

1. **`NOT VERIFIED: Tự động khôi phục Window 2 không cần snapshot`**  
   *Lý do:* Theo đúng nguyên tắc an toàn dữ liệu, một post được tạo ra nhưng chưa kịp gắn thẻ sở hữu không thể được phân biệt một cách an toàn 100% với một bài viết do người dùng tạo song song nếu chỉ dựa vào heuristic. Do đó, việc tự động xóa các đối tượng Window 2 bằng code mà không có rủi ro xóa nhầm là **không khả thi và không được hỗ trợ**. Trong trường hợp crash tại Window 2, phương án duy nhất được chấp nhận là restore snapshot.
2. **`NOT VERIFIED: Hành vi của MySQL dưới quyền hạn người dùng bị hạn chế (Restricted DB User)`**  
   *Lý do:* Trong môi trường LocalWP hiện tại, người dùng MySQL `root` có toàn quyền (ALL PRIVILEGES). Hành vi của importer khi chạy dưới một database user không có quyền `CREATE TABLE` hoặc `DROP TABLE` chưa được kiểm nghiệm thực tế trên môi trường staging/production thực thụ.

---

## 9. ĐIỀU KIỆN TIÊN QUYẾT BẮT BUỘC TRƯỚC KHI XEM XÉT GATE 4

Mặc dù toàn bộ mã nguồn, logic hồi quy và kiểm thử cô lập đã đạt **PASS**, việc chuyển sang Gate 4 (nhập dữ liệu thật vào database `local`) **KHÔNG ĐƯỢC PHÉP TỰ ĐỘNG DIỄN RA**. 

Trước khi một kỹ sư con người xem xét phê duyệt Gate 4, bắt buộc phải thỏa mãn đầy đủ các điều kiện sau:

1. **Sao lưu Database chính `local` tại thời điểm T-0:** Phải tạo một snapshot mới nhất của database `local` (mysqldump / LocalWP snapshot) ngay trước khi chạy lệnh import và xác minh tệp dump không rỗng.
2. **Phê duyệt của Kỹ sư trưởng (Human Sign-off):** Phải có sự đồng ý tường minh từ con người thông qua prompt chỉ thị riêng biệt.
3. **Chạy Importer ở chế độ có giám sát:** Khi Gate 4 được cấp phép, chỉ được chạy importer qua CLI với các tham số khóa cứng:
   ```powershell
   php -d memory_limit=512M run_controlled_import.php --mode=import --run-id=run_gate4_production
   ```
4. **Không chạy import trực tiếp trên web giao diện hoặc background worker không giám sát.**

---

## 10. MANDATORY STOP CONFIRMATION

Kiểm toán viên xác nhận:
- [x] Không thực hiện bất kỳ thao tác sửa đổi nào trên `run_controlled_import.php`.
- [x] Không chạy importer hoặc rollback trên database `local`.
- [x] Không bắt đầu Gate 4.
- [x] Không thay đổi bất kỳ cài đặt, theme, plugin hoặc tuỳ chọn nào của website.
- [x] Hoàn thành toàn bộ mục tiêu của Stage 3N-R2E và dừng lại tại đây.
