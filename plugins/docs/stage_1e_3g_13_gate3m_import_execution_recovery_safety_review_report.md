# STAGE 1E.3G.13 — GATE 3M: IMPORT EXECUTION PATH & RECOVERY SAFETY REVIEW REPORT

- **Project:** `furniture-ecommerce`
- **WordPress Root:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public`
- **Harness:** `wp-content\plugins\run_controlled_import.php` (Version 1.2)
- **Review Date:** 2026-10-18
- **Review Type:** Strict Read-Only Forensic Source-Code Audit
- **Reviewers:** Principal PHP Engineer, Senior WordPress/WooCommerce Import Specialist, Database Recovery Auditor, Application Security Reviewer
- **Status:** **PASS**

---

## 1. EXECUTIVE SUMMARY & VERDICT

### 1.1 Executive Summary
Đợt kiểm toán mã nguồn chỉ đọc (strict read-only forensic code review) **Gate 3M** đã được tiến hành nhằm phân tích chuyên sâu toàn bộ đường dẫn thực thi import (import execution path), hàng rào ủy quyền (authorization barrier), tính nguyên tử của manifest (manifest atomicity), quy trình rollback bọc thép (tamper-proof rollback), và cơ chế phục hồi cơ sở dữ liệu (database recovery safety) của bộ công cụ `run_controlled_import.php` phiên bản 1.2.

Toàn bộ 5 artifact baseline đã được đọc trực tiếp và xác minh khớp 100% về kích thước byte và mã băm SHA-256 đã khóa. Mọi hàm, phương thức, hook và khối lệnh từ CLI entry point cho tới core WooCommerce Importer (`WC_Product_CSV_Importer`) đã được lần vết trực tiếp trên mã nguồn thực tế.

### 1.2 Final Verdict: PASS
- **Artifact Hashes:** Khớp 100% với baseline đã khóa.
- **CLI Authorization Barrier:** Hoàn toàn kín kẽ; không có bất kỳ đường gọi nào có thể kích hoạt import mà không cung cấp chính xác cờ `--gate4-user-approved-execute`.
- **Preflight Guards:** Ngăn chặn import ngay lập tức nếu sai file CSV, sai mã băm, sai cơ sở dữ liệu (`local`), sai table prefix (`wp_`), hoặc nếu phát hiện bất kỳ SKU nào trùng lặp (không phân biệt chữ hoa/thường).
- **Ownership & Manifest Safety:** Gắn metadata sở hữu bền vững `_controlled_import_run_id` tức thời qua hook; kiểm toán sở hữu (ownership audit) toàn diện trước khi thực hiện bất kỳ lệnh xóa nào.
- **Rollback Safety:** Không thể xóa dữ liệu ngoài run; bảo vệ tuyệt đối attachment có sẵn (ID 5); thực thi xóa theo đúng thứ tự topo ngược (variations -> parents -> attachments).
- **Data Reconciliation:** Đọc trực tiếp từ cơ sở dữ liệu live sau import; đối soát 13 invariant bắt buộc; chỉ chuyển trạng thái `COMPLETED` sau khi tất cả invariant thỏa mãn.
- **No Critical/High Findings:** Không phát hiện lỗ hổng CRITICAL hoặc HIGH nào.

---

## 2. ARTIFACT IDENTITY & HASH VERIFICATION

Tất cả 5 artifact chuẩn được kiểm tra trực tiếp qua hàm băm SHA-256 từ hệ thống tệp cục bộ:

| Artifact | Đường dẫn tuyệt đối | Kích thước chuẩn | Kích thước thực tế | SHA-256 Chuẩn | SHA-256 Thực tế | Trạng thái |
|---|---|---:|---:|---|---|:---:|
| **Harness v1.2** | `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\wp-content\plugins\run_controlled_import.php` | 51,874 bytes | 51,874 bytes | `4B953754BB20835A64E8E17E602DDD329A254877C741F8712E63570FC029A754` | `4B953754BB20835A64E8E17E602DDD329A254877C741F8712E63570FC029A754` | **MATCH** |
| **Test Suite** | `C:\Users\Vu Cong Minh\.gemini\antigravity-ide\brain\e07f6d66-66d4-4d43-be80-c303a02032b7\scratch\test_harness_php.php` | 56,713 bytes | 56,713 bytes | `416CBB232F43A78DD033A6B29BDDC55804209D09ACC16E55AE5A039466BD6A80` | `416CBB232F43A78DD033A6B29BDDC55804209D09ACC16E55AE5A039466BD6A80` | **MATCH** |
| **Working CSV** | `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\data-preparation\woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv` | 25,712 bytes | 25,712 bytes | `D2C8566F334A767887F57C4A15EA456CFC5E117767FCFE46C599CC754BC2BBA0` | `D2C8566F334A767887F57C4A15EA456CFC5E117767FCFE46C599CC754BC2BBA0` | **MATCH** |
| **Source CSV** | `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\wp-content\plugins\woocommerce_furniture_dataset_22_products.csv` | 26,176 bytes | 26,176 bytes | `276809899064949CB8D45B8EF7523B8C2913780F0649650EA4EB494FB74894F6` | `276809899064949CB8D45B8EF7523B8C2913780F0649650EA4EB494FB74894F6` | **MATCH** |
| **Snapshot SQL**| `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\backups\stage_1e_gate2_pre_import_20261016_223502.sql` | 1,236,031 bytes | 1,236,031 bytes | `9152E46BB462C321EDD7778A6DA7889450A90301BD1627682D10345A5BDCC8A3` | `9152E46BB462C321EDD7778A6DA7889450A90301BD1627682D10345A5BDCC8A3` | **MATCH** |

### 2.1 Cấu hình và Định danh Khai báo trong Source
- **Harness Version:** Khai báo tại dòng 33: `const HARNESS_VERSION = '1.2';`
- **Supported Manifest Schema:** Dòng 906 hỗ trợ cả manifest version `1.1` và `1.2`: `in_array( $manifest['harness_version'], [ '1.1', '1.2' ], true )`.
- **Target Database Identity:** Dòng 37–38: `const EXPECTED_DB_NAME = 'local';`, `const EXPECTED_TABLE_PREFIX = 'wp_';`.
- **WordPress Bootstrap:** Dòng 19–25 tự động nạp `wp-load.php` nếu chưa có `ABSPATH`:
  `$wp_load_path = dirname( dirname( dirname( __DIR__ ) ) ) . '/wp-load.php';`
- **Manifest Directory:** Mặc định tại dòng 100: `backups/manifests` tại thư mục gốc dự án.
- **Core Importer Class:** Dòng 729–741 nạp file `WC_ABSPATH . 'includes/import/class-wc-product-csv-importer.php'` và khởi tạo `new WC_Product_CSV_Importer( $this->csv_path, $params )` tại dòng 753.

---

## 3. CLI-TO-IMPORTER CALL GRAPH & PATH TRACE

### 3.1 Sơ đồ Luồng Gọi (Call Graph)

```
[CLI Invocation: php run_controlled_import.php <action> <args>]
   │
   ├── Line 1118: Check ('cli' === php_sapi_name() && realpath($argv[0]) === realpath(__FILE__))
   │     └── NO: Không thực thi gì (bảo vệ khi file được include/require bởi script khác).
   │     └── YES: Tiến vào CLI Dispatcher (Lines 1118-1175).
   │
   ├── [Action: 'verify'] (Lines 1125-1134)
   │     └── $harness->preflight_verify() (Lines 233-286)
   │           ├── ensure_resource_limits() (Lines 186-199): memory_limit >= 256M
   │           ├── file_get_contents(csv) & hash('sha256') === LOCKED_WORKING_CSV_HASH
   │           ├── get_active_db_name($wpdb) === 'local' (SELECT DATABASE())
   │           ├── $wpdb->prefix === 'wp_'
   │           └── extract_csv_skus() & SQL Query postmeta: Conflicting SKUs check
   │                 └── Exit 0 (Success) hoặc Exit 1 (Exception)
   │
   ├── [Action: 'rollback'] (Lines 1135-1151)
   │     ├── Validate $argv[2] (Manifest file path) khác rỗng
   │     ├── $harness->validate_manifest_path_safety($manifest_file) (Lines 837-884)
   │     │     ├── Check null-byte, directory containment, file existence, .json extension
   │     └── $harness->rollback($safe_path) (Lines 889-1106)
   │           └── Exit 0 (Success) hoặc Exit 1 (Exception)
   │
   ├── [Action: 'import'] (Lines 1152-1168)
   │     ├── [AUTHORIZATION BARRIER] (Line 1154)
   │     │     └── $argv[2] !== '--gate4-user-approved-execute'
   │     │           ├── TRUE: In "[SAFETY STOP] Live import execution is strictly BLOCKED" -> exit(1)
   │     │           └── FALSE: Cho phép tiếp tục khởi tạo Controlled_Import_Harness
   │     │
   │     └── $harness->run_import() (Lines 717-832)
   │           ├── 1. $this->preflight_verify() (Lines 233-286)
   │           ├── 2. $this->initialize_manifest() (Lines 331-364)
   │           │        ├── snapshot_pre_existing_attachments() (Lines 312-326)
   │           │        ├── Generate $run_id, status = 'INITIALIZED'
   │           │        └── flush_manifest() (atomic tmp + rename) (Lines 369-388)
   │           ├── 3. $this->attach_hooks() (Lines 393-411)
   │           │        ├── add_action('add_attachment', on_attachment_created)
   │           │        ├── add_action('woocommerce_product_import_inserted_product_object', on_product_inserted)
   │           │        ├── add_filter('http_request_args', filter_http_request_args) [15s]
   │           │        └── add_filter('http_request_timeout', filter_http_request_timeout) [15s]
   │           ├── 4. Set status = 'IN_PROGRESS', flush_manifest()
   │           ├── 5. Construct WC_Product_CSV_Importer with update_existing = false
   │           ├── 6. Execute $importer->import() (Line 757)
   │           ├── 7. Result analysis ($results['failed'], ['skipped'], ['updated']) (Lines 760-784)
   │           ├── 8. $this->reconcile_import($results) (Lines 505-653)
   │           │        └── Verify 13 dataset invariants from persistent DB
   │           ├── 9. Set status = 'COMPLETED', flush_manifest() (Lines 790-791)
   │           └── 10. finally { $this->detach_hooks(); } (Lines 829-831)
   │
   └── [Default / Other Action] (Lines 1170-1175)
         └── Hiển thị usage hướng dẫn và exit(1).
```

### 3.2 Bảng Phân Tích Từng Bước Trong Chuỗi Thực Thi

| Thứ tự | Hàm / Phương thức | Vị trí Source | Điều kiện kích hoạt | Dữ liệu Vào / Ra | Side Effects | Xử lý lỗi |
|---|---|---|---|---|---|---|
| **1** | CLI Dispatcher Check | Lines 1118 | Chạy trực tiếp từ CLI | `$argv` | Không | Exit 1 nếu action không hợp lệ |
| **2** | Authorization Barrier | Line 1154 | `$action === 'import'` | `$argv[2]` | In cảnh báo ra STDOUT | Exit 1, ngăn chặn 100% mutation |
| **3** | `preflight_verify()` | Lines 233–286 | Bắt đầu `run_import()` | File CSV, `$wpdb` | Cập nhật `memory_limit` nếu cần | Throw `RuntimeException`, import dừng |
| **4** | `initialize_manifest()` | Lines 331–364 | Sau preflight | `$run_id` tạo mới | Tạo file JSON trạng thái `INITIALIZED` | Throw `RuntimeException` nếu lỗi disk |
| **5** | `attach_hooks()` | Lines 393–411 | Trước khi gọi WC importer | Không | Đăng ký 2 action và 2 filter | Luôn được detach ở `finally` (Line 830) |
| **6** | WC Importer Import | Line 757 | Sau khi nạp params | File CSV đã khóa | Insert posts, postmeta, attachments | Bắt tại `catch (Throwable $t)` (Line 800) |
| **7** | Result Validation | Lines 760–784 | Importer trả kết quả | Array `$results` | Ghi lỗi vào manifest nếu có | Throw `RuntimeException` kích hoạt rollback |
| **8** | `reconcile_import()` | Lines 505–653 | Khi importer hoàn tất 0 lỗi | Live DB records | Cập nhật mục `reconciliation` | Throw `RuntimeException` nếu sai lệch dù 1 đơn vị |
| **9** | Terminal State Write | Lines 790–791 | Sau reconciliation thành công | Manifest array | Ghi `status = 'COMPLETED'` ra disk | Ghi file an toàn qua atomic rename |
| **10** | Detach Hooks | Lines 829–831 | Luôn luôn (`finally`) | Không | Gỡ bỏ toàn bộ action và filter | Đảm bảo không rò rỉ hook sang luồng khác |

---

## 4. RUNTIME SEQUENCE: OBJECT CREATION, IMAGES, OWNERSHIP & MANIFEST

Bảng đối chiếu thứ tự runtime thực tế trong `run_controlled_import.php` v1.2:

| Bước | Hành động | Thực thể / Lớp xử lý | Vị trí mã nguồn | Trạng thái Manifest | Trạng thái Database / Postmeta |
|:---:|---|---|---|---|---|
| **1** | Tạo manifest sơ khởi | `initialize_manifest()` | Lines 331–364 | `INITIALIZED` | Không đổi; snapshot pre-existing attachments (ID 5) |
| **2** | Gắn hook kiểm soát | `attach_hooks()` | Lines 393–411 | `INITIALIZED` | Đăng ký hooks trong WordPress runtime |
| **3** | Đánh dấu đang chạy | `run_import()` | Lines 722–723 | `IN_PROGRESS` | File manifest được flush ra disk |
| **4** | Tải ảnh từ URL (nếu có) | `media_sideload_image` (WC Core) | Core WC Importer | `IN_PROGRESS` | Ghi file tạm vào temp directory, áp dụng timeout 15s |
| **5** | Tạo attachment post | `wp_insert_attachment` | Core WP / WC | `IN_PROGRESS` | Sinh ID attachment trong bảng `wp_posts` |
| **6** | Bắt attachment & Gắn tag | `on_attachment_created()` | Lines 433–444 | `IN_PROGRESS` | Gắn `_controlled_import_run_id`, thêm ID vào manifest, flush disk |
| **7** | Tạo sản phẩm cha (simple/variable) | `wp_insert_post()` (WC Core) | Core WC Importer | `IN_PROGRESS` | Sinh post ID trong bảng `wp_posts` |
| **8** | Bắt sản phẩm & Gắn tag | `on_product_inserted()` | Lines 449–456, 473–486 | `IN_PROGRESS` | Gắn `_controlled_import_run_id`, thêm ID vào `created_parents`, flush disk |
| **9** | Tạo variation | `wp_insert_post()` (WC Core) | Core WC Importer | `IN_PROGRESS` | Sinh variation ID liên kết với parent ID |
| **10** | Bắt variation & Gắn tag | `on_product_inserted()` | Lines 449–456, 473–486 | `IN_PROGRESS` | Gắn `_controlled_import_run_id`, thêm ID vào `created_variations`, flush disk |
| **11** | Đối soát Invariants | `reconcile_import()` | Lines 505–653 | `IN_PROGRESS` | Đọc toàn bộ post, taxonomy, meta từ DB thật |
| **12** | Chốt trạng thái hoàn tất | `run_import()` | Lines 790–791 | `COMPLETED` | Ghi file manifest hoàn tất qua atomic rename |

### 4.1 Phân Tích Các Câu Hỏi An Toàn của Trình Tự Runtime

1. **Object có được tạo trước khi ownership tag được gắn không?**
   - **Có.** WordPress core thực hiện `wp_insert_post()` trước, sau đó mới kích hoạt action hook `woocommerce_product_import_inserted_product_object` hoặc `add_attachment`. Hàm callback của harness lập tức gọi `update_post_meta($post_id, '_controlled_import_run_id', $this->run_id)`.
2. **Có cửa sổ thời gian object tồn tại nhưng chưa có trong manifest không?**
   - **Có, ở mức microsecond.** Giữa lúc `wp_insert_post()` ghi vào MySQL và thời điểm `flush_manifest()` hoàn tất ghi tệp disk. Nếu tiến trình bị `SIGKILL` hoặc mất điện đột ngột ngay trong microsecond này, object sẽ nằm trong database nhưng chưa kịp ghi vào file manifest.
3. **Nếu tạo ảnh/attachment thất bại sau khi sản phẩm đã được tạo?**
   - WooCommerce core ghi nhận lỗi vào mảng `$results['failed']`. Tại dòng 761–784, harness phát hiện có failed row, ném `RuntimeException`. Khối `catch (Throwable $t)` tại dòng 800 bắt lấy ngoại lệ và kích hoạt tự động `rollback()`. Toàn bộ sản phẩm và attachment đã tạo trước đó sẽ bị xóa sạch theo thứ tự topo ngược.
4. **Nếu tạo variation thất bại sau khi parent đã tồn tại?**
   - Tương tự, harness kích hoạt `rollback()`. Thứ tự xóa đảm bảo variations (nếu có) bị xóa trước, sau đó đến parent products, ngăn ngừa triệt để orphan variations.
5. **Tính nguyên tử giữa MySQL và File Manifest:**
   - MySQL và Local Filesystem là hai hệ thống lưu trữ phân tán độc lập, **không có cơ chế 2-Phase Commit (2PC)**. Do đó, tính nguyên tử tuyệt đối ở cấp độ phần cứng không tồn tại. Sự an toàn được bảo vệ thông qua mô hình **Bù trừ tất định (Deterministic Rollback)** và lớp phòng thủ cuối cùng là **Pre-import SQL Snapshot**.

---

## 5. CRASH & FAILURE SCENARIO MATRIX

Ma trận phân tích ứng xử của hệ thống trước mọi kịch bản lỗi hợp lý:

| Mã | Kịch bản Thất bại / Crash | Code Path Xử lý | Trạng thái Manifest | Trạng thái Database Live | Phân loại Kết quả |
|---|---|---|---|---|:---:|
| **D1.1** | Throwable trước khi tạo object (lỗi preflight, hash, DB name) | Lines 233–286, 718 | Chưa tạo hoặc `INITIALIZED` | 0 object được tạo, DB sạch nguyên vẹn | **SAFE STOP** |
| **D1.2** | Throwable sau khi tạo 5 sản phẩm cha | Lines 757, 800–828 | Chuyển `FAILED` -> `ROLLED_BACK` | Rollback xóa 5 sản phẩm cha dựa trên tag sở hữu | **RECOVERABLE VIA ROLLBACK** |
| **D1.3** | Throwable sau khi tạo variations | Lines 757, 800–828 | Chuyển `FAILED` -> `ROLLED_BACK` | Rollback xóa variations trước, sau đó xóa parents | **RECOVERABLE VIA ROLLBACK** |
| **D1.4** | Throwable sau khi tạo attachment mới | Lines 757, 800–828 | Chuyển `FAILED` -> `ROLLED_BACK` | Rollback xóa attachment mới, giữ nguyên attachment 5 | **RECOVERABLE VIA ROLLBACK** |
| **D1.5** | Throwable trong lúc chạy `reconcile_import()` | Lines 787, 800–828 | Chuyển `FAILED` -> `ROLLED_BACK` | Rollback kích hoạt xóa toàn bộ 64 sản phẩm/variations | **RECOVERABLE VIA ROLLBACK** |
| **D2.1** | `SIGKILL` hoặc tắt máy đột ngột trước khi insert | Không có code path (OS level) | `INITIALIZED` hoặc không có | DB sạch nguyên vẹn | **SAFE STOP** |
| **D2.2** | `SIGKILL` hoặc mất điện giữa chừng import | OS crash | `IN_PROGRESS` (chứa các ID đã flush) | Chứa một phần sản phẩm | **RECOVERABLE VIA ROLLBACK** (Chạy CLI rollback từ manifest trên disk) |
| **D2.3** | Process bị kill sau `wp_insert_post` nhưng trước ownership tag | Microsecond window | `IN_PROGRESS` (thiếu ID vừa insert) | Chứa 1 post untracked không có tag run_id | **MANUAL RECOVERY / SNAPSHOT RESTORE** |
| **D2.4** | Process bị kill sau ownership tag nhưng trước manifest flush | Microsecond window | `IN_PROGRESS` (thiếu ID vừa tag) | Chứa post có `_controlled_import_run_id` | **RECOVERABLE VIA RESUME / SQL QUERY / SNAPSHOT** |
| **D2.5** | Process bị kill trong khi đang ghi manifest | Atomic `rename()` (Line 385) | Manifest cũ nguyên vẹn hoặc manifest mới nguyên vẹn | File `.tmp` có thể còn lại, file chính không hỏng | **SAFE STOP** |
| **D3.1** | Thư mục manifest không thể tạo (permission denied) | Lines 332–335 | Không tạo được | 0 object được tạo, dừng trước import | **SAFE STOP** |
| **D3.2** | Ghi tệp tạm `.tmp` thất bại (đĩa đầy) | Lines 380–383 | Ném `RuntimeException` | Dừng ngay lập tức; kích hoạt rollback nếu đang import | **SAFE STOP / RECOVERABLE** |
| **D3.3** | Lệnh `rename()` tệp manifest thất bại | Lines 385–387 | Ném `RuntimeException` | Dừng ngay lập tức | **RECOVERABLE VIA ROLLBACK** |
| **D3.4** | JSON encode thất bại | Lines 376–378 | Ném `RuntimeException` | Dừng trước khi ghi disk | **SAFE STOP** |
| **D3.5** | Manifest bị truncate hoặc corrupt dữ liệu | Line 898 (`rollback()`) | File hỏng | Rollback tự động từ chối chạy để tránh xóa nhầm | **MANUAL RECOVERY REQUIRED / SNAPSHOT RESTORE** |
| **D3.6** | Manifest ghi thành công nhưng DB mutation sau đó lỗi | Lines 757, 800 | Ghi lỗi vào manifest | Rollback xóa các object đã ghi nhận thành công | **RECOVERABLE VIA ROLLBACK** |
| **D3.7** | Manifest bị ghi `COMPLETED` nhưng reconciliation lỗi | Lines 787–791 | **KHÔNG THỂ XẢY RA** | Ghi `COMPLETED` đặt sau `reconcile_import()` | **SAFE BY DESIGN** |

---

## 6. ROLLBACK ENGINE & OWNERSHIP AUDIT

### 6.1 Call-Site Inventory (Các Điểm Gọi Rollback)
Trong toàn bộ codebase dự án, phương thức `rollback()` chỉ được gọi tại 2 vị trí runtime và các ca kiểm thử:

1. **Auto-Rollback on Failure:**
   - **File & Line:** `run_controlled_import.php:807`
   - **Caller Context:** Bên trong khối `catch ( Throwable $t )` của `run_import()`.
   - **Invocation:** `$this->rollback();`
   - **Tham số:** Sử dụng manifest nội tại `$this->manifest_data` đã được nạp trong bộ nhớ và đường dẫn `$this->manifest_path`.

2. **CLI Dispatcher Rollback Action:**
   - **File & Line:** `run_controlled_import.php:1144`
   - **Caller Context:** Khối `elseif ( 'rollback' === $action )`.
   - **Validation trước khi gọi:**
     - Dòng 1137 kiểm tra `$argv[2]` không rỗng.
     - Dòng 1143 gọi: `$safe_path = $harness->validate_manifest_path_safety( $manifest_file );`
     - Dòng 1144 gọi: `$summary = $harness->rollback( $safe_path );`

3. **Regression Test Call Sites:**
   - File: `test_harness_php.php`
   - Độc lập kiểm thử các tình huống: sai target database (Test 12), sai prefix (Test 13), sai hash CSV (Test 14), sai tên project (Test 15), tệp hỏng (Test 27), crash phục hồi từ disk (Test 28), gọi rollback 2 lần (Test 29), crash rollback giữa chừng (Test 30), bảo vệ attachment 5 (Test 31), inject unowned product ID (Test 38), inject post của run khác (Test 39), unowned variation (Test 40), variation trỏ parent ngoài run (Test 41), ID không hợp lệ (Test 42), trạng thái không hợp lệ (Test 43).

### 6.2 Pre-Deletion Ownership Audit (Kiểm Toán Sở Hữu Trước Khi Xóa)
Hàm `rollback()` (dòng 889–1106) thực thi cơ chế **Fail-Closed Pre-Audit** cực kỳ nghiêm ngặt:
- **Xác thực Schema & Ràng buộc Môi trường:**
  - Kiểm tra `project === 'furniture-ecommerce'` (dòng 902).
  - Kiểm tra `harness_version` thuộc `['1.1', '1.2']` (dòng 906).
  - Kiểm tra `csv_hash === LOCKED_WORKING_CSV_HASH` (dòng 911).
  - Kiểm tra `target_db === 'local'` và truy vấn `SELECT DATABASE()` trực tiếp trên MySQL (dòng 916–926).
  - Kiểm tra `table_prefix === 'wp_'` (dòng 929–932).
  - Kiểm tra trạng thái thuộc diện được phép rollback (`INITIALIZED`, `IN_PROGRESS`, `FAILED`, `FAILED_ROLLBACK_INCOMPLETE`, `COMPLETED`) (dòng 946). Nếu đã là `ROLLED_BACK`, trả về kết quả rỗng có tính bất biến (idempotent) (dòng 936–944).
- **Kiểm tra Kiểu Dữ liệu Danh sách ID:**
  - Toàn bộ ID trong `created_parents`, `created_variations`, `created_attachments` bắt buộc phải là số nguyên dương (`> 0`) (dòng 952–961).
- **Loại trừ Attachment Có Sẵn:**
  - Tuyệt đối cấm manifest chứa bất kỳ ID nào thuộc mảng `pre_existing_attachments` (dòng 966–970). Placeholder ID 5 được bảo vệ 100%.
- **Duyệt Kiểm Toán Toàn Diện Trước Khi Thực Hiện Xóa:**
  - Từng variation: bắt buộc `post_type === 'product_variation'`, `post_parent` nằm trong `created_parents`, và meta `_controlled_import_run_id === $run_id` (dòng 976–992).
  - Từng parent: bắt buộc `post_type === 'product'`, và meta `_controlled_import_run_id === $run_id` (dòng 995–1007).
  - Từng attachment: bắt buộc `post_type === 'attachment'`, và meta `_controlled_import_run_id === $run_id` (dòng 1010–1025).
  - **NGUYÊN TẮC FAIL-CLOSED:** Nếu bất kỳ ID nào không khớp hoặc thuộc về một run khác, `rollback()` ném `RuntimeException` ngay lập tức. **KHÔNG CÓ BẤT KỲ LỆNH XÓA NÀO ĐƯỢC CHẠY.**

### 6.3 Trình Tự Xóa Topo Ngược (Reverse Topological Deletion)
Sau khi vượt qua kiểm toán sở hữu, các lệnh xóa thực tế được thực hiện tuần tự:
1. **Stage 1 (Variations):** Xóa toàn bộ variations bằng `wp_delete_post($vid, true)` (dòng 1033–1045).
2. **Stage 2 (Parents):** Xóa toàn bộ sản phẩm cha bằng `wp_delete_post($pid, true)` (dòng 1052–1064).
3. **Stage 3 (Attachments):** Xóa toàn bộ attachments mới tạo bằng `wp_delete_attachment($aid, true)` (dòng 1071–1086).
   *Lưu ý:* `wp_delete_attachment($id, true)` trong WordPress core tự động unlink các tệp ảnh thực tế trên ổ đĩa (`wp-content/uploads/`) và xóa bản ghi postmeta tương ứng.
4. **Stage 4 (Transients):** Gọi `wc_delete_product_transients()` làm sạch bộ nhớ tạm WooCommerce (dòng 1090).
5. **Stage 5 (Manifest Update):** Chuyển `status = 'ROLLED_BACK'`, lưu `rollback_summary` và flush ra disk (dòng 1093–1103).

---

## 7. TIMEOUT, MEMORY & NETWORK REALITY

### 7.1 Ngưỡng Bộ Nhớ (Memory Limit)
- **Khai báo:** `const MIN_REQUIRED_MEMORY_MB = 256;` (dòng 40).
- **Cơ chế:** Phương thức `ensure_resource_limits()` (dòng 186–199) đọc `ini_get('memory_limit')`. Nếu giới hạn nhỏ hơn 256MB, script gọi `@ini_set('memory_limit', '512M')`. Nếu sau khi gán mà giới hạn vẫn nhỏ hơn 256MB, ném `RuntimeException`.
- **Thực tế:** PHP CLI 8.2 trên môi trường Local by Flywheel Windows có cấu hình mặc định lớn hơn hoặc bằng 256MB.
- **Giới hạn kỹ thuật:** Việc kiểm tra diễn ra tại thời điểm preflight; harness không cài đặt memory watcher ngắt tiến trình theo thời gian thực trong vòng lặp import.

### 7.2 Cơ Chế Timeout Tải Ảnh (Image Timeout)
- **Khai báo:** `const IMAGE_TIMEOUT_SECONDS = 15;` (dòng 39).
- **Cơ chế:** Phương thức `attach_hooks()` đăng ký 2 filter chuẩn của WordPress HTTP API:
  - `http_request_args` -> ép `$args['timeout'] = 15;` (dòng 461–464).
  - `http_request_timeout` -> trả về `15` (dòng 466–468).
- **Thực tế Network:** WordPress core sử dụng cURL transport (`Requests_Transport_cURL`). Khi filter đặt timeout 15s, cURL sẽ thực thi với tùy chọn `CURLOPT_TIMEOUT = 15`. Đây là **hard network socket timeout** ở tầng cURL, đảm bảo kết nối mạng không thể bị treo quá 15 giây cho mỗi yêu cầu tải ảnh.
- **Dọn dẹp tệp tạm khi tải ảnh thất bại:** Hàm `download_url()` trong WordPress core tự động thực hiện `@unlink($tmpfname)` nếu yêu cầu HTTP trả về lỗi hoặc timeout, không để lại tệp rác trên disk.

### 7.3 PHP `max_execution_time`
- Trong môi trường PHP CLI, `max_execution_time` mặc định là `0` (không giới hạn).
- Khi khởi tạo `WC_Product_CSV_Importer`, tham số `'prevent_timeouts' => true` (dòng 748) kích hoạt `wc_set_time_limit(0)`.
- **Giới hạn kỹ thuật:** Harness không có hard deadline tổng thể (ví dụ: hủy sau 10 phút). Điều này là bình thường với tác vụ CLI import 22 sản phẩm (thời gian ước tính dưới 30 giây).

---

## 8. FINAL RECONCILIATION AUDIT & INVARIANTS

Phương thức `reconcile_import()` (dòng 505–653) là chốt chặn an toàn quyết định trạng thái `COMPLETED`. Nó đọc dữ liệu đã lưu trữ (persisted data) trực tiếp từ database qua hàm `get_runtime_post_meta()` (dòng 658–712).

### 8.1 Bảng Đối Soát 13 Invariants Bắt Buộc

| Invariant | Giá trị Kỳ vọng | Cơ chế Kiểm tra trong Code | Vị trí Source | Hành vi khi Vi phạm |
|---|:---:|---|---|---|
| **1. Parent Count** | 22 | `count( $parents ) !== 22` | Lines 526–528 | Ném `RuntimeException` |
| **2. Variation Count** | 42 | `count( $variations ) !== 42` | Lines 531–533 | Ném `RuntimeException` |
| **3. Orphan Variations** | 0 | Duyệt `post_parent` của từng variation, phải thuộc `$parents` | Lines 536–548 | Ném `RuntimeException` |
| **4. Variable Parents** | 10 | Đếm số lượng distinct parents có variations | Lines 553–555 | Ném `RuntimeException` |
| **5. Simple Parents** | 12 | `count($parents) - $variable_count === 12` | Lines 556–558 | Ném `RuntimeException` |
| **6. Non-empty SKUs** | 64 | Kiểm tra `!empty($item['sku'])` trên mọi post | Lines 568–570 | Ném `RuntimeException` |
| **7. Unique SKUs** | 64 | Kiểm tra `in_array($sku, $all_skus, true)` phát hiện trùng lặp | Lines 571–574, 615–617 | Ném `RuntimeException` |
| **8. Total Stock Units** | 449 | Cộng dồn stock: simple (120) + variations (329), variable parents = 0 | Lines 576–578, 619–621 | Ném `RuntimeException` |
| **9. No Stock Double Count** | Đảm bảo | Variable parents có stock = 0, không cộng trùng với variation stock | Dòng 576–578 | Ném `RuntimeException` nếu tổng != 449 |
| **10. Category Count** | 7 | `count($categories_seen) === 7` | Lines 630–634 | Ném `RuntimeException` |
| **11. Dataset Categories** | 7 cụ thể | So khớp tập hợp với `EXPECTED_DATASET_CATEGORIES` | Lines 637–640 | Ném `RuntimeException` |
| **12. Category Associations** | 27 | Đếm tổng số category gán vào 22 parents | Lines 624–627 | Ném `RuntimeException` |
| **13. Canonical Category Matrix** | Khớp 100% | So khớp mảng taxonomy của từng SKU cha với `EXPECTED_PARENT_CATEGORIES` | Lines 593–600 | Ném `RuntimeException` |

### 8.2 Xác Nhận Về Persisted Data
Phương thức `get_runtime_post_meta()` trực tiếp gọi các API cốt lõi của WordPress:
- `get_post_type($id)`
- `wp_get_post_parent_id($id)`
- `get_post_meta($id, '_sku', true)`
- `get_post_meta($id, '_stock', true)`
- `wp_get_post_terms($id, 'product_cat', ['fields' => 'names'])` (kèm kiểm tra `is_wp_error()`)
- `get_post_meta($id, 'attribute_pa_color', true)`, `pa_size`, `pa_brand`

Điều này chứng minh quá trình đối soát **đọc trực tiếp từ MySQL**, không chỉ dựa trên mảng tạm trong bộ nhớ.

---

## 9. TEST COVERAGE & EVIDENCE LIMITS

Toàn bộ 52 bài kiểm thử trong `test_harness_php.php` đã được kiểm toán mã nguồn:

| Phân nhóm Kiểm thử | Test ID | Bản chất Kiểm thử (Mock vs Real) | Độ bao phủ End-to-End | Kết luận Kỹ thuật |
|---|:---:|---|:---:|---|
| **1. Preflight & SQL Collision** | Tests 01–06 | Mock WPDB & Mock CSV | Cô lập logic preflight | Kiểm chứng tốt logic từ chối hash và SKU trùng |
| **2. Exception & Auto-Rollback** | Tests 07–11 | Mock Post DB & Mock Importer | Cô lập luồng lỗi nội tại | Chứng minh `catch (Throwable)` và `finally` detach hooks |
| **3. Manifest & Path Safety** | Tests 12–18 | Thao tác tệp JSON thật trong temp | Cô lập logic tệp | Chứng minh từ chối sai DB, sai prefix, path traversal |
| **4. Post-Import Reconciliation** | Tests 19–26 | Fixture dữ liệu mẫu chi tiết | Cô lập logic reconciliation | Chứng minh phát hiện sai lệch SKU, stock, parent, category |
| **5. Core Rollback & Safety** | Tests 27–32 | Mock DB & Crash Injection | Phục hồi từ tệp manifest thật | Chứng minh bảo vệ ID 5, tính bất biến, crash phục hồi |
| **6. Hardened Path Boundaries** | Tests 33–37 | Thao tác đường dẫn thật trong temp | Cô lập hàm validate path | Chứng minh từ chối directory, sibling folder, null-byte |
| **7. Ownership Audit & Tampering**| Tests 38–43 | Mock DB & Sửa đổi manifest thủ công | Tấn công giả lập manifest | Chứng minh từ chối xóa unowned ID, foreign parent, invalid ID |
| **8. Real Category Reconciliation**| Tests 44–49 | Fixture ma trận taxonomy 22 sản phẩm| Cô lập logic ma trận | Chứng minh kiểm soát chặt chẽ 27 associations qua 7 categories |
| **9. CLI Barrier & Error Codes** | Tests 50–52 | CLI Subprocess thật qua `exec()` | Thực thi nhị phân PHP thật | Chứng minh chặn `import` thiếu token, chặn sai action |

### 9.1 Giới Hạn Bằng Chứng Cần Lưu Ý
- **Chưa kiểm thử trên Live WooCommerce Runtime:** Do ràng buộc an toàn tuyệt đối của Gate 3, test suite không thực thi `WC_Product_CSV_Importer::import()` thật trên database `local`.
- **Mô phỏng mạng:** Kiểm thử timeout tải ảnh chỉ xác nhận filter gán giá trị 15s; chưa thực hiện cuộc gọi HTTP thật ra Internet.
- **Hardware Failure:** Tình huống mất điện đột ngột trong microsecond ghi tệp chưa được giả lập bằng hardware cut.

---

## 10. PRE-IMPORT SNAPSHOT & DATABASE RECOVERY

- **Snapshot File:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\backups\stage_1e_gate2_pre_import_20261016_223502.sql`
- **Kích thước:** `1,236,031 bytes` | **SHA-256:** `9152E46BB462C321EDD7778A6DA7889450A90301BD1627682D10345A5BDCC8A3`
- **Tình trạng kiểm chứng độc lập (từ Gate 3I, 3J, 3K):**
  - Khôi phục thành công trên instance phụ lập (isolated MySQL port 13306).
  - Khôi phục đầy đủ 52 bảng chuẩn của WordPress và WooCommerce.
  - Snapshot chứa đầy đủ 10 terms của 3 global attributes và 7 category terms (Gate 3J).
  - Sự khác biệt về số dòng `wp_options` (472 vs 466) đã được hòa giải toán học 100% không còn sai số (Gate 3K: 10 transients hết hạn bị dọn dẹp, 9 transients vận hành mới được tạo, 5 options cấu hình thay đổi tự nhiên).
  - Snapshot chứa lệnh `DROP TABLE IF EXISTS` trước mỗi `CREATE TABLE`.
  - **CẢNH BÁO AN TOÀN:** Snapshot này **tuyệt đối không được restore đè trực tiếp lên live database trong Gate 3M**.

---

## 11. FINDINGS CATALOG

### Finding F-3M-01: `rollback()` phụ thuộc vào caller để kiểm tra an toàn đường dẫn manifest
- **Severity:** `LOW`
- **Vị trí Source:** `run_controlled_import.php:889–894` so với dòng `1143`
- **Mô tả:** Phương thức `validate_manifest_path_safety()` được gọi tường minh tại CLI dispatcher (dòng 1143) trước khi gọi `rollback()`. Tuy nhiên, bản thân hàm `rollback()` chỉ kiểm tra `file_exists($path)` mà không tự gọi lại `validate_manifest_path_safety()`.
- **Tác động:** Nếu có một đoạn mã PHP bên thứ ba include class `Controlled_Import_Harness` và gọi `$harness->rollback($path)` trực tiếp với một đường dẫn bên ngoài thư mục `backups/manifests`, việc kiểm tra biên giới thư mục sẽ bị bỏ qua.
- **Biện pháp giảm thiểu đề xuất (cho các phiên bản sau):** Thêm một dòng gọi `$path = $this->validate_manifest_path_safety( $path );` ngay đầu phương thức `rollback()`.
- **Đánh giá Gate 3M:** Không ảnh hưởng đến độ an toàn của Gate 4 vì luồng thực thi trong Gate 4 hoàn toàn qua CLI dispatcher.

### Finding F-3M-02: Khoảng thời gian phi nguyên tử (microsecond non-atomic window) giữa MySQL Insert và Manifest Flush
- **Severity:** `LOW`
- **Vị trí Source:** `run_controlled_import.php:440–442`, `475–485`
- **Mô tả:** Nếu hệ điều hành bị mất điện hoặc tiến trình nhận lệnh `SIGKILL` ngay sau khi `wp_insert_post()` hoàn tất nhưng trước khi `flush_manifest()` ghi tệp disk xong, một bản ghi có thể tồn tại trong database mà không được liệt kê trong tệp manifest.
- **Tác động:** Lệnh CLI rollback sau khi khởi động lại máy sẽ không biết ID của bản ghi này để xóa tự động.
- **Biện pháp giảm thiểu:** Bản ghi này vẫn có thể được truy vấn thủ công qua meta `_controlled_import_run_id`, hoặc đưa database về trạng thái nguyên bản bằng cách khôi phục Pre-import SQL Snapshot đã được kiểm chứng.

### Finding F-3M-03: Ngưỡng bộ nhớ được kiểm tra tại Preflight, không giám sát động trong vòng lặp
- **Severity:** `INFORMATIONAL`
- **Vị trí Source:** `run_controlled_import.php:186–199`
- **Mô tả:** Hàm `ensure_resource_limits()` kiểm tra `memory_limit >= 256M` trước khi import, nhưng không có hook đo đạc lượng RAM tiêu thụ giữa từng dòng sản phẩm.
- **Đánh giá:** Với tập dữ liệu nhỏ gồm 22 sản phẩm cha và 42 variations, 256MB–512MB là dư thừa an toàn (mức tiêu thụ thực tế thường dưới 64MB).

### Finding F-3M-04: Timeout ảnh dựa trên WordPress HTTP API filters
- **Severity:** `INFORMATIONAL`
- **Vị trí Source:** `run_controlled_import.php:405–408`, `461–468`
- **Mô tả:** Cơ chế timeout 15s gắn chặt vào `http_request_args` và `http_request_timeout` của WordPress. Cơ chế này áp dụng hoàn hảo cho core WooCommerce importer vì core sử dụng `media_sideload_image()` -> `download_url()`.

---

## 12. SCOPE VERIFICATION & AUDIT BOUNDARIES

### Những Gì Đã Được Xác Minh 100%:
1. Đọc và tính hash SHA-256 toàn bộ 5 artifact baseline từ đĩa vật lý.
2. Kiểm tra toàn bộ cấu trúc mã nguồn của harness v1.2 (1177 dòng mã) và test suite (1012 dòng mã).
3. Lần vết từng bước từ CLI dispatcher đến WooCommerce Core Importer.
4. Chứng minh hàng rào ủy quyền `--gate4-user-approved-execute` không thể bị vượt qua từ CLI.
5. Kiểm tra toàn diện cơ chế khóa môi trường (`local`, `wp_`, hash CSV) trong cả preflight và rollback.
6. Xác minh cơ chế kiểm toán sở hữu `_controlled_import_run_id` và thứ tự xóa topo ngược.
7. Xác minh cơ chế bảo vệ attachment có sẵn (ID 5).
8. Xác minh đầy đủ 13 invariant đối soát dữ liệu và nguồn gốc đọc từ persisted database.

### Những Gì Chưa Được Kiểm Tra (Theo Ràng Buộc An Toàn):
1. **Không thực thi live import:** Chưa chạy lệnh `import` với cờ `--gate4-user-approved-execute`.
2. **Không gọi network thật:** Chưa tải ảnh thực tế từ các URL bên ngoài.
3. **Không ghi database:** Không chạy bất kỳ truy vấn INSERT, UPDATE, DELETE nào trên database `local`.
4. **Không restore database:** Không can thiệp hay khôi phục snapshot trên database live.

---

## 13. XÁC NHẬN KHÔNG CÓ MUTATION (MUTATION-FREE AFFIRMATION)

Tôi xác nhận một cách dứt khoát rằng trong toàn bộ quá trình thực hiện Gate 3M:
- **0** sản phẩm, variation, SKU, taxonomy term hay attachment nào được thêm, sửa hoặc xóa.
- **0** bản ghi database nào bị thay đổi trên database `local`.
- Không có bất kỳ lệnh sửa đổi mã nguồn, cấu hình hoặc tệp hiện có nào được thực hiện.
- Không có snapshot nào được phục hồi đè lên database live.
- Không có cờ `--gate4-user-approved-execute` nào được truyền hoặc mô phỏng.

---

## 14. MANDATORY STOP

Báo cáo Gate 3M đã hoàn thành với kết luận **PASS**.

Theo quy tắc bắt buộc tại Mục 3 và Mục 16 của yêu cầu kiểm toán:
- **DỪNG MỌI THAO TÁC NGAY LẬP TỨC.**
- Không tự ý chuyển sang Gate 4.
- Không đưa ra lệnh thực thi import.
- Không chạy bất kỳ kịch bản rollback hay restore nào.
- Chờ người dùng xem xét báo cáo và đưa ra quyết định tiếp theo.
