# STAGE 1E.3G.15 — GATE 3O: REMEDIATION DESIGN & READ-ONLY VERIFICATION REPORT

- **Project:** `furniture-ecommerce`
- **WordPress Root:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public`
- **Harness:** `wp-content\plugins\run_controlled_import.php` (Version 1.2)
- **Review Date:** 2026-10-18
- **Mode:** STRICT READ-ONLY FORENSIC AUDIT & ARCHITECTURAL DESIGN
- **Architects / Reviewers:** Senior Software Architect, WooCommerce Import/Recovery Engineer, Independent Security Reviewer
- **Gate 3O Status:** **PASS — DESIGN READY FOR HUMAN DECISION**
- **Import Status:** **BLOCKED (Read-Only Safety Lock Intact — No Import Authorization)**

---

## 1. EXECUTIVE SUMMARY

Gate 3O được thiết kế nhằm thực hiện một cuộc kiểm toán chỉ đọc và chốt thiết kế kỹ thuật (architectural remediation design) xử lý dứt điểm 5 finding đã được nhận diện trong Gate 3N:
1. **F-3N-01 (MEDIUM):** Đối soát tồn kho aggregate (449) che giấu sai lệch phân bổ stock theo từng SKU cụ thể.
2. **F-3N-02 (MEDIUM):** Khoảng thời gian phi nguyên tử giữa database insert và manifest flush; thiếu cơ chế recovery scan bằng ownership tag.
3. **F-3N-03 (LOW):** Lệnh rollback thiếu kiểm tra mã trả về của `wp_delete_post()` và thiếu truy vấn hậu kiểm database sau khi xóa.
4. **F-3N-04 (LOW):** Timeout 15s là per-request cURL socket timeout; thiếu deadline toàn cục cho phiên import.
5. **F-3N-05 (INFORMATIONAL):** Lỗi Out-Of-Memory (OOM) fatal error của PHP không thể bắt bằng `catch (Throwable)`.

### Kết luận Đánh giá Gate 3O
- **Verdict:** **`PASS — DESIGN READY FOR HUMAN DECISION`** (Bản thiết kế kỹ thuật đã hoàn thiện 100%, sẵn sàng để người dùng xem xét và phê duyệt trước khi bước sang giai đoạn triển khai code).
- **Ràng buộc An toàn Tuyệt đối:** Không có bất kỳ dòng mã production nào bị sửa đổi; không có bất kỳ lệnh import hay restore nào được thực thi; database `local` giữ nguyên trạng thái baseline 100%.

---

## 2. BASELINE ARTIFACT HASHES & ENVIRONMENT INTEGRITY

### 2.1 Đối Chiếu Bốn Artifact Trọng Yếu

| Artifact | Đường dẫn kiểm tra | Kích thước byte | SHA-256 thực tế | Đối chiếu Baseline |
|---|---|---:|---|:---:|
| **Harness v1.2** | `app/public/wp-content/plugins/run_controlled_import.php` | 51,874 | `4B953754BB20835A64E8E17E602DDD329A254877C741F8712E63570FC029A754` | **MATCH (100%)** |
| **Test Suite** | `scratch/test_harness_php.php` | 56,713 | `416CBB232F43A78DD033A6B29BDDC55804209D09ACC16E55AE5A039466BD6A80` | **MATCH (100%)** |
| **Working CSV** | `data-preparation/...working_stage_1e_3c.csv` | 25,712 | `D2C8566F334A767887F57C4A15EA456CFC5E117767FCFE46C599CC754BC2BBA0` | **MATCH (100%)** |
| **Snapshot SQL**| `backups/stage_1e_gate2_pre_import_20261016_223502.sql` | 1,236,031 | `9152E46BB462C321EDD7778A6DA7889450A90301BD1627682D10345A5BDCC8A3` | **MATCH (100%)** |

*Ghi chú về đường dẫn Test Suite:* Test suite thực tế được lưu trữ tại thư mục scratch của conversation (`C:\Users\Vu Cong Minh\.gemini\antigravity-ide\brain\e07f6d66-66d4-4d43-be80-c303a02032b7\scratch\test_harness_php.php`), kích thước và mã băm khớp 100% với baseline đã khóa.

### 2.2 Trạng Thái Git Working Tree (Chỉ Đọc)
- **Branch:** `master`
- **Working tree:** Sạch, không có commit dở dang; các tệp untracked chỉ gồm các báo cáo tài liệu `.md` và tệp harness.

### 2.3 Trạng Thái Cơ Sở Dữ Liệu Live (Đọc Trực Tiếp Trước Khi Đánh Giá)
Truy vấn trực tiếp trên database `local` (port 10011, prefix `wp_`):
- **Products (`post_type = 'product'`):** `0`
- **Variations (`post_type = 'product_variation'`):** `0`
- **SKUs in postmeta (`meta_key = '_sku'`):** `0`
- **Attachment ID 5:** `EXISTS` (Title: `woocommerce-placeholder`, post_type: `attachment`, guid trỏ placeholder)
- **Total Attachments:** `1` (duy nhất attachment ID 5)
- **Global Attributes:** `3` (`color`, `size`, `brand`)
- **Terms Breakdown:**
  - `pa_color`: 3 terms (`Black`, `Brown`, `White`)
  - `pa_size`: 3 terms (`L`, `M`, `S`)
  - `pa_brand`: 5 terms (`LuxeVibe`, `Minimalia`, `NordicLiving`, `OakCraft`, `UrbanoHaus`)
  - `product_cat`: 8 terms (7 dataset categories + 1 `Uncategorized`)
  - Tổng số terms thuộc target taxonomies: **19** (hoặc 18 nếu không tính term mặc định `Uncategorized` của WordPress).

---

## 3. ĐỐI CHIẾU SỐ DÒNG THỰC TẾ TRONG SOURCE & TEST SUITE

Tất cả các luồng điều khiển đã được định vị chính xác trên `run_controlled_import.php` (v1.2, 1177 dòng):

| Luồng Điều Khiển | Vị Trí File & Dòng Mã Thực Tế | Chức Năng Chính |
|---|---|---|
| **CLI Authorization Barrier** | `run_controlled_import.php:1152–1158` | Kiểm tra token `--gate4-user-approved-execute`; thoát mã 1 nếu thiếu |
| **Preflight Environment & Hash** | `run_controlled_import.php:233–286` | Kiểm tra RAM >= 256M, hash CSV, DB `local`, prefix `wp_`, duplicate SKU |
| **Manifest Init & Attachment Snapshot** | `run_controlled_import.php:331–364, 312–326` | Snapshot ID 5, sinh `run_id`, tạo manifest trạng thái `INITIALIZED` |
| **Realtime Hook Attachment** | `run_controlled_import.php:393–411` | Đăng ký `add_attachment`, `woocommerce_product_import_inserted_product_object`, và filter timeout 15s |
| **Callback Gắn Ownership Tag** | `run_controlled_import.php:433–444, 473–486` | Gắn `_controlled_import_run_id`, cập nhật danh sách ID, gọi `flush_manifest()` |
| **Atomic Flush Manifest** | `run_controlled_import.php:369–388` | Ghi ra `.tmp` với `LOCK_EX`, sau đó gọi `rename()` đè file chính |
| **WC Importer Construction & Import** | `run_controlled_import.php:743–757` | Nạp `update_existing = false`, gọi `$importer->import()` |
| **Reconciliation trước `COMPLETED`** | `run_controlled_import.php:505–653` | Đối soát 13 invariants từ MySQL persisted data |
| **Terminal State Assignment** | `run_controlled_import.php:790–791` | Gán `status = 'COMPLETED'` và flush disk |
| **Pre-Deletion Ownership Audit** | `run_controlled_import.php:972–1026` | Kiểm tra type, parent link, và `_controlled_import_run_id` trên mọi ID |
| **Topological Reverse Rollback** | `run_controlled_import.php:1032–1086` | Xóa `variations` -> `parents` -> `attachments` (loại trừ ID 5) |
| **Exception Handling & Auto-Rollback** | `run_controlled_import.php:800–828` | Bắt `Throwable`, kích hoạt rollback, xử lý `FAILED_ROLLBACK_INCOMPLETE` |
| **Path Boundary Safety Check** | `run_controlled_import.php:837–884` | Chặn traversal, null-byte, tệp không phải .json, và tệp ngoài thư mục manifest |

---

## 4. THIẾT KẾ REMEDIATION: FINDING F-3N-01 (ĐỐI SOÁT TỒN KHO THEO TỪNG SKU)

### 4.1 Hạn Chế Hiện Tại Của Mã Nguồn
Hiện tại, phương thức `reconcile_import()` (`run_controlled_import.php:576–578, 619–621`) chỉ cộng dồn tổng tồn kho:
```php
if ( isset( $item['stock'] ) ) {
    $total_stock += intval( $item['stock'] );
}
...
if ( $total_stock !== self::EXPECTED_TOTAL_STOCK ) { // 449
    throw new RuntimeException( "Reconciliation failed: Expected total stock of 449..." );
}
```
**Lỗ hổng logic:** Nếu SKU `FURN-001` (kỳ vọng 25) bị gán nhầm thành 35, và SKU `FURN-002` (kỳ vọng 30) bị gán nhầm thành 20, tổng tồn kho vẫn là 55 và aggregate toàn dataset vẫn đúng 449. Logic hiện tại **hoàn toàn không phát hiện được lỗi hoán đổi tồn kho này**.

### 4.2 Thiết Kế Cấu Trúc Dữ Liệu & Thuật Toán Đối Soát Mới

#### A. Cấu trúc Hằng số Định danh Tồn kho Từng SKU
Bổ sung hằng số `EXPECTED_SKU_STOCK_MAP` ánh xạ toàn bộ 64 SKU chuẩn từ CSV:
```php
const EXPECTED_SKU_STOCK_MAP = [
    // 12 Simple Products (Tổng: 248)
    'FURN-001' => [ 'type' => 'simple',    'stock' => 25, 'parent' => null ],
    'FURN-002' => [ 'type' => 'simple',    'stock' => 30, 'parent' => null ],
    'FURN-003' => [ 'type' => 'simple',    'stock' => 15, 'parent' => null ],
    'FURN-004' => [ 'type' => 'simple',    'stock' => 20, 'parent' => null ],
    'FURN-005' => [ 'type' => 'simple',    'stock' => 18, 'parent' => null ],
    'FURN-006' => [ 'type' => 'simple',    'stock' => 12, 'parent' => null ],
    'FURN-007' => [ 'type' => 'simple',    'stock' => 40, 'parent' => null ],
    'FURN-008' => [ 'type' => 'simple',    'stock' => 22, 'parent' => null ],
    'FURN-009' => [ 'type' => 'simple',    'stock' => 50, 'parent' => null ],
    'FURN-010' => [ 'type' => 'simple',    'stock' => 0,  'parent' => null ],
    'FURN-011' => [ 'type' => 'simple',    'stock' => 16, 'parent' => null ],
    'FURN-012' => [ 'type' => 'simple',    'stock' => 0,  'parent' => null ],

    // 10 Variable Parents (Tổng: 0 - Quản lý tồn kho ở cấp variation)
    'FURN-013' => [ 'type' => 'variable',  'stock' => 0,  'parent' => null ],
    'FURN-014' => [ 'type' => 'variable',  'stock' => 0,  'parent' => null ],
    'FURN-015' => [ 'type' => 'variable',  'stock' => 0,  'parent' => null ],
    'FURN-016' => [ 'type' => 'variable',  'stock' => 0,  'parent' => null ],
    'FURN-017' => [ 'type' => 'variable',  'stock' => 0,  'parent' => null ],
    'FURN-018' => [ 'type' => 'variable',  'stock' => 0,  'parent' => null ],
    'FURN-019' => [ 'type' => 'variable',  'stock' => 0,  'parent' => null ],
    'FURN-020' => [ 'type' => 'variable',  'stock' => 0,  'parent' => null ],
    'FURN-021' => [ 'type' => 'variable',  'stock' => 0,  'parent' => null ],
    'FURN-022' => [ 'type' => 'variable',  'stock' => 0,  'parent' => null ],

    // 42 Variations (Tổng: 201)
    'FURN-013-BLK-S' => [ 'type' => 'variation', 'stock' => 8,  'parent' => 'FURN-013' ],
    'FURN-013-BLK-M' => [ 'type' => 'variation', 'stock' => 6,  'parent' => 'FURN-013' ],
    'FURN-013-BLK-L' => [ 'type' => 'variation', 'stock' => 5,  'parent' => 'FURN-013' ],
    'FURN-013-WHT-S' => [ 'type' => 'variation', 'stock' => 5,  'parent' => 'FURN-013' ],
    'FURN-013-WHT-M' => [ 'type' => 'variation', 'stock' => 4,  'parent' => 'FURN-013' ],
    'FURN-013-BRN-L' => [ 'type' => 'variation', 'stock' => 7,  'parent' => 'FURN-013' ],
    // ... [Toàn bộ 42 variations khớp 100% với CSV ledger] ...
];
```

#### B. Thuật toán Kiểm tra Fail-Closed trong `reconcile_import()`
1. **Chuẩn hóa SKU:** Chuyển về chữ in hoa (`strtoupper(trim($sku))`) để so sánh bất biến.
2. **Kiểm tra tồn tại:** Nếu SKU không nằm trong `EXPECTED_SKU_STOCK_MAP` -> Ném `RuntimeException("Reconciliation failed: Unexpected SKU '{$sku}' not found in canonical dataset map")`.
3. **Kiểm tra tính hợp lệ của giá trị stock:**
   - Nếu `!is_numeric($actual_stock)` hoặc `intval($actual_stock) < 0`: Ném `RuntimeException`.
4. **So khớp giá trị chính xác:**
   ```php
   $expected_stock = self::EXPECTED_SKU_STOCK_MAP[$sku]['stock'];
   if ( intval( $actual_stock ) !== $expected_stock ) {
       throw new RuntimeException(
           "Reconciliation failed: Exact stock mismatch for SKU '{$sku}'. Expected {$expected_stock}, found {$actual_stock}."
       );
   }
   ```
5. **Kiểm tra đối xứng:** Sau khi duyệt hết đối tượng trong DB, kiểm tra tập hợp các SKU đã thấy phải bằng đúng 64 SKU của hằng số.

#### C. Kế hoạch Kiểm thử Cô lập (Isolated Test Cases)
- **Test F01-1 (Hoán đổi Stock):** Gán SKU `FURN-001` = 30, `FURN-002` = 25 (tổng vẫn bằng 55, aggregate vẫn bằng 449). Khẳng định test **FAIL** ở bước kiểm tra từng SKU và ném lỗi rõ ràng.
- **Test F01-2 (Tồn kho Âm):** Gán SKU `FURN-003` = -5. Khẳng định fail-closed.
- **Test F01-3 (Parent Variable có Tồn kho):** Gán SKU cha `FURN-013` = 10. Khẳng định fail-closed.
- **Test F01-4 (Cập nhật Test Suite Fixture):** Cập nhật `get_valid_reconciliation_fixture()` trong `test_harness_php.php` sử dụng chính xác phân bổ 248 (simple) và 201 (variations) thay vì dữ liệu mẫu 120/329.

---

## 5. THIẾT KẾ REMEDIATION: FINDING F-3N-02 (OBJECT BỊ BỎ SÓT KHỎI MANIFEST SAU CRASH)

### 5.1 Phân Tích Chi Tiết 6 Crash Windows

| Crash Window | Thời Điểm Xảy Ra Sự Cố | Trạng Thái MySQL | Trạng Thái Manifest | Khả Năng Nhận Diện |
|---|---|---|---|---|
| **Window A** | Giữa `wp_insert_post` và hook callback | Post tồn tại, **chưa có tag** `_controlled_import_run_id` | Chưa có ID | **Khó nhất**. Không thể tìm qua `run_id`. Cần đối chiếu SKU hoặc snapshot |
| **Window B** | Giữa ghi tag `_controlled_import_run_id` và `flush_manifest()` | Post tồn tại, **đã có tag** `_controlled_import_run_id` | Chưa có ID | Nhận diện được 100% qua truy vấn SQL quét `_controlled_import_run_id` |
| **Window C** | Ghi tệp `.tmp` xong nhưng `rename()` thất bại/bị kill | Post tồn tại, đã có tag | Tệp `.tmp` có ID, tệp chính chưa có | Nhận diện được qua quét tệp `.tmp` hoặc quét tag database |
| **Window D** | Tạo đối tượng thành công nhưng crash trước đợt flush tiếp theo | Nhiều post có tag | Thiếu một số ID mới tạo | Nhận diện được 100% qua truy vấn SQL quét `_controlled_import_run_id` |
| **Window E** | Rollback bị kill giữa chừng sau khi xóa 1 phần object | Đã xóa 1 phần | Manifest còn `IN_PROGRESS`/`FAILED` | Đã xử lý an toàn nhờ tính bất biến (idempotency) của rollback |
| **Window F** | Tệp manifest bị corrupt hoàn toàn sau crash | Dữ liệu import nằm trong DB | File hỏng | Nhận diện được qua quét tag database hoặc Snapshot Recovery |

### 5.2 Đánh Giá Hai Phương Án Khắc Phục

#### Phương Án A: Recovery Scan bằng Ownership Tag
- **Nguyên lý hoạt động:**
  1. Thêm action CLI mới: `php run_controlled_import.php recover-scan <run_id> [--dry-run]`.
  2. Truy vấn MySQL:
     ```sql
     SELECT p.ID, p.post_type, p.post_parent, pm.meta_value as run_id
     FROM wp_posts p
     INNER JOIN wp_postmeta pm ON p.ID = pm.post_id
     WHERE pm.meta_key = '_controlled_import_run_id' AND pm.meta_value = %s;
     ```
  3. So khớp danh sách ID tìm thấy trong DB với các ID trong manifest:
     - Nếu phát hiện ID có tag nhưng không có trong manifest -> Cảnh báo **Unmanifested Object Detected**.
  4. **Kiểm toán an toàn trước khi xóa:**
     - Xác minh post_type: chỉ chấp nhận `product`, `product_variation`, `attachment`.
     - Tuyệt đối loại trừ attachment ID 5 và pre-existing attachments.
     - Kiểm tra topological binding: variation phải trỏ về parent cùng run.
  5. Nếu `--dry-run`: Chỉ in bảng danh sách đối tượng và thoát.
  6. Nếu execute: Xóa theo topo ngược (`variations` -> `parents` -> `attachments`).
- **Ưu điểm:** Khắc phục triệt để Window B, C, D, F mà không cần khôi phục toàn bộ database.
- **Hạn chế:** Không bắt được Window A (khi post chưa kịp ghi meta).

#### Phương Án B: Snapshot Recovery
- **Điều kiện kích hoạt bắt buộc:**
  - Phát hiện có object thuộc Window A (ví dụ: phát hiện SKU của dataset tồn tại trong DB nhưng thiếu `_controlled_import_run_id`).
  - Tệp manifest bị hỏng hoàn toàn và recovery scan phát hiện trạng thái dữ liệu không nhất quán.
  - Xảy ra lỗi phân rã liên kết dữ liệu nghiêm trọng mà rollback không thể giải quyết sạch sẽ.
- **Đánh giá Blast Radius:**
  - Lệnh restore sẽ thực thi `DROP TABLE IF EXISTS` trên toàn bộ 52 bảng của database `local`.
  - Toàn bộ dữ liệu phát sinh sau mốc 22:35:02 (2026-10-16) sẽ bị xóa sạch. Vì môi trường đang được khóa chỉ cho mục đích import dataset này, blast radius là chấp nhận được và đã được kiểm chứng độc lập ở Gate 3I/3K.

#### Đề Xuất Kiến Trúc Lai (Hybrid Architecture - Recommended)
1. **Lớp phòng thủ 1 (Automated Sweep):** Nâng cấp hàm `rollback()` để tự động chạy một truy vấn đối soát `_controlled_import_run_id` sau khi xóa xong danh sách ID từ manifest. Nếu phát hiện còn sót post mang run_id, nó sẽ tự động xóa nốt (xử lý Window B, D).
2. **Lớp phòng thủ 2 (Snapshot Recovery Ground Truth):** Nếu phát hiện Window A hoặc có nghi ngờ về tính toàn vẹn dữ liệu, hệ thống từ chối xóa mù quáng, chuyển trạng thái manifest thành `MANUAL_RECOVERY_REQUIRED` và yêu cầu người dùng kích hoạt quy trình Snapshot Restore.

---

## 6. THIẾT KẾ REMEDIATION: FINDING F-3N-03 (KIỂM TOÁN VÀ HẬU KIỂM ROLLBACK)

### 6.1 State Machine Xử Lý 8 Nhánh Thất Bại Trong Rollback

```
[Start Rollback]
       │
       ├── 1. Xác thực Path, Schema, Hash CSV, Database Identity ('local', 'wp_')
       │     └── Thất bại -> Ném Exception, Status không đổi (Fail-Closed)
       │
       ├── 2. Pre-Deletion Audit toàn bộ ID (Type, Parent, Tag sở hữu)
       │     └── Bất kỳ ID nào sai sở hữu -> Ném Exception, 0 Deletions (Fail-Closed)
       │
       ├── 3. Vòng lặp xóa Variations -> Parents -> Attachments
       │     │
       │     ├── Nhánh 3.1: wp_delete_post() trả về false / WP_Error
       │     │     └── Ghi nhận ID vào $failed_deletions, KHÔNG đánh dấu đã xóa
       │     │
       │     └── Nhánh 3.2: wp_delete_post() trả về post object
       │           └── Ghi nhận ID vào $attempted_deletions
       │
       ├── 4. [MỚI] Hậu kiểm Database Vật lý (Post-Deletion DB Verification Query)
       │     │   SELECT ID FROM wp_posts WHERE ID IN ($attempted_deletions)
       │     │
       │     ├── Còn ID tồn tại trong DB -> Status = 'FAILED_ROLLBACK_INCOMPLETE'
       │     └── 0 ID còn tồn tại -> Chuyển sang bước 5
       │
       ├── 5. [MỚI] Quét dư lượng Run ID (Residual Ownership Sweep)
       │     │   SELECT post_id FROM wp_postmeta WHERE meta_key = '_controlled_import_run_id' AND meta_value = $run_id
       │     │
       │     ├── Phát hiện object sót -> Xóa nốt theo topo ngược và hậu kiểm lại
       │     └── Không còn sót -> Chuyển sang bước 6
       │
       └── 6. Cập nhật Manifest: Status = 'ROLLED_BACK' -> Atomic Flush ra đĩa
```

### 6.2 Bảng Chuyển Trạng Thái (State Transition Table)

| ID Tình Huống | Kịch Bản Xảy Ra | Hành Vi Rollback Mới | Trạng Thái Cuối Của Manifest | Khả Năng Retry Lần Sau |
|---|---|---|---|:---:|
| **S1** | Xóa thành công, hậu kiểm xác nhận 0 đối tượng còn lại trong DB | Ghi nhận summary chi tiết | `ROLLED_BACK` | Idempotent (trả về 0 deletions) |
| **S2** | `wp_delete_post` trả về `false` do hook/filter chặn | Không thêm vào mảng đã xóa; giữ nguyên ID trong manifest | `FAILED_ROLLBACK_INCOMPLETE` | Cho phép retry sau khi gỡ filter |
| **S3** | Hàm xóa báo thành công nhưng query hậu kiểm thấy ID vẫn còn | Phát hiện mâu thuẫn MySQL; ném lỗi fail-closed | `FAILED_ROLLBACK_INCOMPLETE` | Cho phép retry hoặc can thiệp SQL |
| **S4** | Flush manifest thất bại sau khi đã xóa sạch DB | DB sạch; manifest cũ còn `IN_PROGRESS` | `IN_PROGRESS` (trên đĩa) | Lần chạy sau phát hiện ID đã xóa, flush `ROLLED_BACK` |
| **S5** | Process bị kill giữa chừng khi mới xóa xong variations | Variations đã xóa; parents còn nguyên | `FAILED` hoặc `IN_PROGRESS` | Lần sau bỏ qua variations, xóa tiếp parents |
| **S6** | Retry rollback trên manifest đã `ROLLED_BACK` | Dòng 936 phát hiện `ROLLED_BACK`, return ngay | `ROLLED_BACK` | Idempotent tuyệt đối |
| **S7** | Phát hiện object có ownership không chắc chắn | Pre-audit phát hiện sai tag; dừng ngay lập tức | Không đổi (giữ nguyên) | Chuyển sang rà soát thủ công |
| **S8** | Quét sweep phát hiện object unmanifested thuộc run | Tự động xóa đối tượng sót và ghi log vào summary | `ROLLED_BACK` | Hoàn tất sạch sẽ 100% |

---

## 7. THIẾT KẾ XỬ LÝ LỖI MANIFEST I/O VÀ FILE TRANSACTION

### 7.1 Xử Lý Lỗi Trên Hệ Điều Hành Windows
1. **Windows File Lock khi `rename()`:**
   - Trên Windows, dịch vụ Antivirus hoặc Search Indexer có thể tạm thời khóa tệp `.json` khi nó vừa được ghi.
   - **Giải pháp:** Thiết kế hàm `safe_atomic_rename($tmp, $target, $max_retries = 3)`:
     ```php
     for ( $i = 0; $i < $max_retries; $i++ ) {
         if ( @rename( $tmp, $target ) ) {
             return true;
         }
         usleep( 50000 ); // Nghỉ 50ms giữa các lần thử
     }
     throw new RuntimeException( "Failed to atomically rename {$tmp} to {$target} after {$max_retries} attempts." );
     ```
2. **JSON Encoding Sanitization:**
   - Bổ sung cờ `JSON_INVALID_UTF8_SUBSTITUTE` khi gọi `json_encode()` để ngăn chặn lỗi dữ liệu binary trong thông điệp ngoại lệ làm sập `flush_manifest()`.

### 7.2 Giới Hạn Của Tính Nguyên Tử (Atomicity Limits)
- Cần khẳng định rõ trong kiến trúc: **Atomic file rename chỉ đảm bảo tính nguyên tử của bản thân tệp manifest trên hệ thống tệp cục bộ.**
- Nó **KHÔNG PHẢI** là giao dịch phân tán 2 pha (2PC) với cơ sở dữ liệu MySQL. Sự đồng bộ giữa MySQL và Manifest được bảo toàn thông qua nguyên tắc **Journaling Trước Khi Ném Lỗi** và **Compensation Rollback**.

---

## 8. THIẾT KẾ DEADLINE TOÀN CỤC, MEMORY GUARD & CRASH SHUTDOWN (F-3N-04 & F-3N-05)

### 8.1 Global Execution Deadline (Hạn Chế Chạy Vô Tận)
- Định nghĩa hằng số: `const MAX_IMPORT_EXECUTION_SECONDS = 300;` (5 phút cho toàn bộ phiên import 22 sản phẩm).
- Ghi nhận `$this->start_time = time();` khi bắt đầu `run_import()`.
- Trong các callback hook (`on_product_inserted`, `on_attachment_created`):
  ```php
  if ( ( time() - $this->start_time ) > self::MAX_IMPORT_EXECUTION_SECONDS ) {
      throw new RuntimeException( "Global import execution deadline exceeded (5 minutes limit). Aborting import." );
  }
  ```

### 8.2 Dynamic Memory Guard (Giám Sát Bộ Nhớ Thời Gian Thực)
- Kiểm tra dung lượng RAM thực tế qua `memory_get_usage(true)` trong mỗi vòng lặp hook.
- Nếu lượng RAM tiêu thụ vượt quá 85% giới hạn cấu hình:
  ```php
  $current_usage = memory_get_usage( true );
  if ( $current_usage > ( 0.85 * $max_allowed_bytes ) ) {
      throw new RuntimeException( "Memory usage safety threshold exceeded (>85%). Aborting to prevent OOM crash." );
  }
  ```

### 8.3 PHP Shutdown Handler cho Fatal Error / OOM
Vì lỗi Out-of-Memory không thể bắt bằng `catch (Throwable $t)`, giải pháp là đăng ký hàm shutdown:
```php
register_shutdown_function( function() use ( $harness ) {
    $error = error_get_last();
    if ( $error && in_array( $error['type'], [ E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR ], true ) ) {
        // Ghi tệp emergency marker ra đĩa
        $harness->record_fatal_crash_marker( $error );
    }
});
```
Tệp marker này giúp các phiên CLI tiếp theo phát hiện ngay rằng tiến trình trước đó đã bị sập do lỗi hệ thống nghiêm trọng.

---

## 9. CHECKLISTS TIÊU CHUẨN HOÀN TẤT & HẬU KIỂM

### 9.1 Checklist Bắt Buộc Để Đánh Dấu `COMPLETED`
Một phiên import **CHỈ ĐƯỢC PHÉP** chuyển sang trạng thái `COMPLETED` khi đáp ứng đủ 12 điều kiện:
- [ ] 1. Target Database là `local` và Table Prefix là `wp_`.
- [ ] 2. Không có bất kỳ dòng nào bị `failed`, `skipped` hoặc `updated`.
- [ ] 3. Đúng 22 sản phẩm cha (`12 simple`, `10 variable`).
- [ ] 4. Đúng 42 variations và 0 orphan variations.
- [ ] 5. Đúng 64 SKU duy nhất, không trùng lặp, không phân biệt hoa thường.
- [ ] 6. **[MỚI] Tồn kho đối soát chính xác 100% từng SKU theo `EXPECTED_SKU_STOCK_MAP` (248 simple, 201 variations).**
- [ ] 7. **[MỚI] Toàn bộ 10 variable parents có stock = 0 và không quản lý tồn kho cấp cha.**
- [ ] 8. Đúng 7 danh mục sản phẩm trùng khớp `EXPECTED_DATASET_CATEGORIES`.
- [ ] 9. Đúng 27 liên kết danh mục theo ma trận canonical `EXPECTED_PARENT_CATEGORIES`.
- [ ] 10. Toàn bộ thuộc tính biến thể (`pa_color`, `pa_size`, `pa_brand`) liên kết đúng terms.
- [ ] 11. Toàn bộ attachment mới tạo đều mang tag `_controlled_import_run_id` và attachment ID 5 được bảo vệ nguyên vẹn.
- [ ] 12. File manifest đã được flush thành công và đọc lại nguyên vẹn từ đĩa.

### 9.2 Checklist Bắt Buộc Để Đánh Dấu `ROLLED_BACK`
Một phiên rollback **CHỈ ĐƯỢC PHÉP** chuyển sang trạng thái `ROLLED_BACK` khi đáp ứng đủ 7 điều kiện:
- [ ] 1. Toàn bộ ID trong manifest đã vượt qua Pre-Deletion Ownership Audit.
- [ ] 2. Thứ tự xóa topo ngược được thực thi đầy đủ: variations -> parents -> attachments.
- [ ] 3. **[MỚI] Kết quả trả về của hàm xóa được xác thực không có lỗi.**
- [ ] 4. **[MỚI] Truy vấn hậu kiểm database (Post-Deletion Verification Query) xác nhận 0 target ID còn tồn tại trong `wp_posts`.**
- [ ] 5. **[MỚI] Truy vấn quét dư lượng (Residual Sweep) xác nhận 0 bản ghi mang `_controlled_import_run_id` còn sót lại.**
- [ ] 6. Attachment ID 5 và các dữ liệu baseline được bảo toàn nguyên vẹn.
- [ ] 7. Bộ nhớ tạm của WooCommerce đã được xóa (`wc_delete_product_transients()`).

---

## 10. EVIDENCE CLASSIFICATION MATRIX

| Hạng mục Kiểm toán | Phân loại Bằng chứng | Chi tiết Bằng chứng Kỹ thuật |
|---|:---:|---|
| **Artifact Hashes (4 file)** | **EXECUTED TEST** | Đọc đĩa và tính SHA-256 qua Python script; khớp 100% |
| **Git Working Tree State** | **EXECUTED TEST** | Lệnh `git status` trả về exit code 0; clean working tree |
| **Live Database Baseline State** | **EXECUTED TEST** | Script `audit_gate3o_db_baseline.php` kết nối `local:10011`; xác nhận 0 products, 0 variations, ID 5 exists, 3 attributes, 19 terms |
| **Isolated Test Suite Execution** | **MOCK/ISOLATED TEST** | `test_harness_php.php` chạy trên Local PHP CLI; 52/52 PASS trong temp directory hoàn toàn cô lập |
| **Working CSV Stock Ledger (449)** | **STATIC ANALYSIS** | Script phân tích CSV độc lập chứng minh: 248 simple + 0 parent + 201 variations = 449 |
| **WC Importer Hook Sequences** | **STATIC ANALYSIS** | Đọc trực tiếp `abstract-wc-product-importer.php` và `wc-rest-functions.php` |
| **Snapshot Restore Verification** | **EXISTING REPORT ONLY** | Dựa trên kết quả phục hồi độc lập tại instance cổng 13306 trong Gate 3I, 3J, 3K |
| **Live Runtime Import / Mutation** | **NOT SAFE TO TEST IN THIS GATE** | Nghiêm cấm chạy import hoặc mutation trên live database trong Gate 3O |

---

## 11. REMEDIATION BACKLOG & TEST PLAN (CHO GIAI ĐOẠN TIẾP THEO)

| Priority | Task ID | Hạng mục Cần Khắc Phục | Tiêu chí Nghiệm thu (Acceptance Criteria) | Kế hoạch Test Cô lập |
|:---:|:---:|---|---|---|
| **P1** | `REM-01` | Triển khai đối soát stock theo từng SKU (`EXPECTED_SKU_STOCK_MAP`) | Phát hiện ngay lập tức lỗi hoán đổi stock giữa 2 SKU bất kỳ dù aggregate vẫn bằng 449 | Viết unit test tráo stock FURN-001 và FURN-002; khẳng định exception |
| **P1** | `REM-02` | Cập nhật Test Suite Fixture (`test_harness_php.php`) | Dữ liệu fixture mẫu khớp 100% với phân bổ 248 simple và 201 variations của CSV | Chạy lại test suite 52 bài với fixture mới; đạt 52/52 PASS |
| **P1** | `REM-03` | Bổ sung Post-Deletion Verification Query & Error Checking trong `rollback()` | Kiểm tra kết quả `wp_delete_post()` và query lại `wp_posts`; chỉ set `ROLLED_BACK` khi DB sạch | Viết test mock delete fail; khẳng định chuyển `FAILED_ROLLBACK_INCOMPLETE` |
| **P2** | `REM-04` | Bổ sung Residual Ownership Sweep Query trong `rollback()` | Tự động phát hiện và xóa các object sót có tag `run_id` nhưng không nằm trong manifest | Viết test inject unmanifested tagged post; khẳng định dọn dẹp sạch |
| **P2** | `REM-05` | Triển khai Windows Safe Atomic Rename (retry loop) | Thử lại tối đa 3 lần nếu tệp bị khóa tạm thời trên Windows | Viết test giả lập file lock; khẳng định retry thành công |
| **P3** | `REM-06` | Bổ sung Global Execution Deadline (5 phút) & Memory Guard (>85%) | Ném ngoại lệ an toàn nếu phiên import vượt quá 300s hoặc tiêu tốn quá 85% RAM | Viết test timeout/memory trigger; khẳng định dừng an toàn |
| **P3** | `REM-07` | Đăng ký Shutdown Handler ghi Emergency Crash Marker | Ghi tệp crash marker khi gặp OOM Fatal Error để hỗ trợ phục hồi sau sự cố | Phân tích tĩnh code và kiểm thử CLI độc lập |

---

## 12. DANH SÁCH RỦI RO CÒN TỒN TẠI VÀ ĐIỀU KIỆN TIÊN QUYẾT

1. **Rủi ro Crash Window A (mất điện ngay sau `wp_insert_post` trước khi gắn tag):**
   - *Đánh giá:* Đây là giới hạn vật lý của các hệ thống không hỗ trợ giao dịch phân tán 2 pha (2PC).
   - *Kiểm soát:* Pre-import SQL Snapshot đã sẵn sàng để khôi phục toàn bộ database về trạng thái sạch sẽ nếu xảy ra mất điện phần cứng.
2. **Điều kiện tiên quyết trước khi quyết định Gate tiếp theo:**
   - Người dùng xem xét báo cáo Gate 3O và quyết định:
     - **Lựa chọn 1:** Tiến hành triển khai gói remediation (P1: REM-01, REM-02, REM-03) để nâng cấp harness lên phiên bản 1.3 trước khi import.
     - **Lựa chọn 2:** Chấp nhận rủi ro hiện tại và giữ nguyên harness v1.2 vì quy mô dataset nhỏ (22 sản phẩm) và đã có Pre-import Snapshot bảo vệ.

---

## 13. XÁC NHẬN TÍNH CHỈ ĐỌC (MUTATION-FREE AFFIRMATION)

Tôi khẳng định rằng trong toàn bộ quá trình thực hiện Gate 3O:
- **0** sản phẩm, variation, taxonomy term hoặc attachment nào bị thêm mới, thay đổi hoặc xóa trên database live `local`.
- Không có bất kỳ câu lệnh SQL dạng ghi (`INSERT`, `UPDATE`, `DELETE`, `ALTER`, `DROP`, `TRUNCATE`) nào được thực thi.
- Không có snapshot nào được phục hồi đè lên database live.
- Không có mã nguồn production nào bị chỉnh sửa.
- Không có cờ `--gate4-user-approved-execute` nào được truyền hoặc mô phỏng.

---

## 14. MANDATORY STOP & KẾT THÚC

Báo cáo kiểm toán và thiết kế **Gate 3O** đã hoàn tất với kết luận **`PASS — DESIGN READY FOR HUMAN DECISION`**.

Theo quy tắc bắt buộc của dự án:
- **DỪNG MỌI THAO TÁC NGAY TẠI ĐÂY.**
- **Không tự ý chuyển sang Gate 4.**
- **Không thực thi import dưới bất kỳ hình thức nào.**
- **Không tự ý sửa mã nguồn harness khi chưa có phê duyệt.**

Xác nhận chính thức:
**“Gate 3O chỉ hoàn tất đánh giá và thiết kế; chưa có remediation được triển khai và chưa có quyền thực hiện Gate 4/import.”**
