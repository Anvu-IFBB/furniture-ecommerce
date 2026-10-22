# STAGE 3N-R2I — READ-ONLY OPERATIONAL DOCUMENTATION CONSISTENCY AUDIT REPORT

**Dự án:** Furniture E-commerce (Site ATELIER)  
**Môi trường:** Local WordPress 7.1.3 / WooCommerce 11.2.1 / PHP 8.2.29 / MySQL 8.4.0  
**Giai đoạn:** Stage 3N-R2I — Kiểm toán Tính nhất quán của Tài liệu Vận hành Chỉ đọc  
**Thời điểm thực hiện:** 2026-10-19  
**Vai trò:** Senior WordPress/WooCommerce Engineer, PHP CLI Reviewer & Operational Safety Auditor  
**Chế độ thực thi:** STRICT READ-ONLY AUDIT (Không ghi/xóa database, không sửa importer, không restore, không chạy Gate 4)  
**Tài liệu tham chiếu:** `run_controlled_import.php`, `STAGE_3N_R2H_DISASTER_RECOVERY_READINESS_AUDIT_REPORT.md`, `STAGE_3N_R2G_READ_ONLY_CONSISTENCY_AUDIT_REPORT.md`, `STAGE_3N_R2F_BASELINE_HASH_RECONCILIATION_REPORT.md`, `STAGE_3N_R2E_EVIDENCE_RECONCILIATION_REPORT.md`, `stage_1e_3g_15_gate3o_remediation_design_report.md`

---

## 1. EXECUTIVE VERDICT

### KẾT LUẬN KIỂM TOÁN: **`CONDITIONAL PASS — FINDINGS OPEN`** (Cho Phạm vi Kiểm toán Tài liệu Vận hành)
### QUYẾT ĐỊNH CỔNG 4: **`MANDATORY STOP — GATE 4 TUYỆT ĐỐI CHƯA ĐƯỢC PHÉP CHẠY`**

Kiểm toán viên độc lập xác nhận:
1. **Nguồn Sự thật Tuyệt đối (Source of Truth):** Bộ phân tích dòng lệnh (CLI dispatcher) của tệp `run_controlled_import.php` hiện hành (67,434 bytes, SHA-256: `640B92D4...457C`, dòng 1352–1411) sử dụng mô hình **Positional Actions** (`verify`, `rollback <manifest>`, `import --gate4-user-approved-execute`). Code **hoàn toàn không hỗ trợ các cờ `--mode=` hoặc `--run-id=`**.
2. **Định vị Toàn bộ Tài liệu Lỗi thời:** Đã rà soát toàn bộ kho tài liệu và xác định chính xác các vị trí chứa cú pháp không tương thích trong `STAGE_3N_R2E` (dòng 277), `STAGE_3N_R2F` (dòng 130, 184) và bản thiết kế cũ `gate3o` (dòng 184). 
3. **Mức độ Rủi ro Vận hành:** Mức độ nghiêm trọng được xếp loại **HIGH**. Nếu một kỹ sư vận hành trong tình huống khẩn cấp gõ lệnh theo tài liệu cũ (`--mode=rollback --run-id=...`), tiến trình sẽ dừng ngay lập tức do rơi vào khối `Usage` (Exit code 1) và không thể thực hiện rollback.
4. **Bảo vệ Trạng thái Hiện tại:** Database chính `local` được xác minh nguyên vẹn (0 products, 0 variations, `woocommerce_coming_soon = yes`). Thư mục `backups/manifests/` hoàn toàn chưa tồn tại, khẳng định chưa từng có phiên import nào được kích hoạt trên môi trường thật.

---

## 2. ĐẶC TẢ IMPORTER HIỆN HÀNH (IMPORTER IDENTITY)

| Thuộc tính | Giá trị Xác minh Thực tế | Trạng thái |
|---|---|---|
| **Đường dẫn Tuyệt đối** | `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\wp-content\plugins\run_controlled_import.php` | Tồn tại, đọc được |
| **Kích thước Tệp** | **67,434 bytes** | Khớp chính xác nhị phân |
| **Số dòng Mã nguồn** | **1,411 dòng** (1,412 dòng tính EOF newline) | Định dạng CRLF (`\r\n`) |
| **SHA-256 Checksum** | `640B92D4F3AFEED52DB88558F0CF93876C4B41468003619BFC934AF4D31B457C` | Khóa cứng từ Stage 3N-R2 |
| **Phiên bản Harness** | `const HARNESS_VERSION = '1.3'` | Định nghĩa tại dòng 32 |
| **Tên Dự án Khóa cứng** | `const PROJECT_NAME = 'ATELIER_FURNITURE_ECOMMERCE'` | Định nghĩa tại dòng 31 |
| **Cú pháp PHP Lint** | `No syntax errors detected` (PHP 8.2.29 CLI) | PASS |

---

## 3. MA TRẬN NGUỒN SỰ THẬT CÚ PHÁP DÒNG LỆNH (CLI SYNTAX SOURCE OF TRUTH)

Phân tích trực tiếp từ mã nguồn thực tế tại dòng 1352–1411 của `run_controlled_import.php`:

```php
if ( 'cli' === php_sapi_name() && isset( $argv[0] ) && realpath( $argv[0] ) === realpath( __FILE__ ) ) {
    $action = $argv[1] ?? 'help';
    if ( 'verify' === $action ) { ... }
    elseif ( 'rollback' === $action ) { ... }
    elseif ( 'import' === $action ) { ... }
    else { ... Usage ... exit(1); }
}
```

### Bảng Đặc tả Chi tiết các Lệnh CLI Hợp lệ

| Lệnh (Action) | Tham số Bắt buộc | Cú pháp CLI Chuẩn xác 100% | Luồng Xử lý Nội bộ | Hành vi khi Đối số Sai / Thiếu | Exit Code Thành công / Thất bại |
|---|---|---|---|---|---|
| **`verify`** | Không có | `php -d memory_limit=512M run_controlled_import.php verify` | Khởi tạo harness; gọi `preflight_verify()`: kiểm tra working CSV, hash SHA-256, target DB (`local`), table prefix (`wp_`), kiểm tra trùng SKU. | Ném `RuntimeException` nếu sai DB, sai hash hoặc trùng SKU. | `0` (Success) / `1` (Failure) |
| **`rollback`** | `<path-to-manifest.json>` (Vị trí `$argv[2]`) | `php -d memory_limit=512M run_controlled_import.php rollback backups/manifests/manifest_<run_id>.json` | 1. `validate_manifest_path_safety()` kiểm tra tệp tồn tại, đuôi `.json`, không thoát khỏi `backups/manifests/`.<br>2. Kiểm tra schema, CSV hash, target DB.<br>3. `find_posts_by_ownership_tag()` quét orphan có tag.<br>4. Xóa tuần tự variation -> parent -> attachment và hậu kiểm DB qua `$delete_and_verify()`. | Nếu thiếu tham số `$argv[2]`: In thông báo lỗi và Usage.<br>Nếu truyền đường dẫn ngoài thư mục: Ném `Path safety violation`. | `0` (Success) / `1` (Failure) |
| **`import`** | `--gate4-user-approved-execute` (Vị trí `$argv[2]`) | `php -d memory_limit=512M run_controlled_import.php import --gate4-user-approved-execute` | 1. Kiểm tra khóa ủy quyền `$argv[2] === '--gate4-user-approved-execute'`.<br>2. Gọi `run_import()`: preflight, tạo JSON manifest, import 22 parent + 42 variation, đối soát ma trận tồn kho 449, hoàn tất manifest. | Nếu thiếu hoặc sai token ủy quyền (kể cả truyền rỗng): In `[SAFETY STOP] Live import execution is strictly BLOCKED.` và dừng ngay. | `0` (Success) / `1` (Failure) |
| **Bất kỳ lệnh nào khác** | N/A | `php run_controlled_import.php [anything else]` | Không khớp với `verify`, `rollback`, `import`. Rơi vào khối `else`. | In Usage chuẩn và thoát ngay lập tức. | `1` (Failure) |

---

## 4. DANH SÁCH TÀI LIỆU CÓ CÚ PHÁP LỖI THỜI (OUTDATED DOCUMENTATION REGISTRY)

Kiểm toán viên đã rà soát toàn bộ các tài liệu kỹ thuật trong thư mục `docs/` và phát hiện các trường hợp hướng dẫn cú pháp không tương thích với parser thực tế:

| STT | Tệp Tài liệu | Vị trí (Dòng) | Nội dung Cú pháp Lỗi thời Hiện tại | Cú pháp Đúng theo Mã nguồn Thực tế | Mức độ Nghiêm trọng | Đề xuất Hành động Kỹ thuật |
|---|---|---|---|---|---|---|
| **1** | `docs/STAGE_3N_R2E_EVIDENCE_RECONCILIATION_REPORT.md` | Dòng 277 | `php -d memory_limit=512M run_controlled_import.php --mode=import --run-id=run_gate4_production` | `php -d memory_limit=512M run_controlled_import.php import --gate4-user-approved-execute` | **HIGH** | Giữ nguyên tệp cũ vì là biên bản lịch sử; lập phụ lục cảnh báo trong hồ sơ Gate 4. |
| **2** | `docs/STAGE_3N_R2F_BASELINE_HASH_RECONCILIATION_REPORT.md` | Dòng 130 | `Chạy lệnh rollback: php run_controlled_import.php --mode=rollback --run-id=<id>.` | `php -d memory_limit=512M run_controlled_import.php rollback backups/manifests/manifest_<run_id>.json` | **HIGH** | Giữ nguyên tệp cũ; ghi nhận vào ma trận vận hành khẩn cấp của Stage 3N-R2I. |
| **3** | `docs/STAGE_3N_R2F_BASELINE_HASH_RECONCILIATION_REPORT.md` | Dòng 184 | `php -d memory_limit=512M -d max_execution_time=0 run_controlled_import.php --mode=import --run-id=run_gate4_production` | `php -d memory_limit=512M run_controlled_import.php import --gate4-user-approved-execute` | **HIGH** | Giữ nguyên tệp cũ; ghi nhận vào ma trận vận hành khẩn cấp của Stage 3N-R2I. |
| **4** | `docs/stage_1e_3g_15_gate3o_remediation_design_report.md` | Dòng 184 | `php run_controlled_import.php recover-scan <run_id> [--dry-run]` | Lệnh `recover-scan` không tồn tại trong code; chức năng này đã được tích hợp tự động vào lệnh `rollback <manifest>`. | **MEDIUM** | Giữ nguyên vì đây là tài liệu thiết kế sơ bộ (Remediation Design) từ Gate 3O trước khi có code Stage 3N-R. |

*Lưu ý bảo vệ tài liệu:* Theo đúng quy tắc an toàn bất khả xâm phạm, kiểm toán viên **KHÔNG sửa trực tiếp** các tệp báo cáo lịch sử trên. Việc ghi nhận minh bạch tại Bảng 4 đóng vai trò là "Sổ tay đính chính vận hành" có giá trị cao nhất trước khi xem xét Gate 4.

---

## 5. KIỂM TOÁN KHẢ NĂNG PHỤC HỒI (RECOVERY AUDIT)

### 5.1. Crash Window 2: Post Đã Tạo Nhưng Chưa Gắn Tag Meta
- **Thời điểm:** Tiến trình import bị tắt đột ngột (mất điện, kill process, OOM) ngay sau `wp_insert_post()` nhưng trước khi lệnh `update_post_meta($id, '_controlled_import_run_id', ...)` được Persist vào MySQL.
- **Hiện trạng:** Bản ghi post nằm trong `wp_posts`, hoàn toàn không có metadata sở hữu, không có trong manifest JSON.
- **Ranh giới an toàn:** Hàm rollback tuân thủ nguyên tắc **FAIL-CLOSED**. Code từ chối xóa đối tượng không có tag run_id để tránh nguy cơ xóa nhầm bài viết của người dùng.
- **Giải pháp phục hồi duy nhất:** **Khôi phục database từ Snapshot T-0**. Mọi ý định viết script tự động xóa theo phỏng đoán (heuristic như SKU, title, timestamp) đều bị cấm tuyệt đối.

### 5.2. Crash Window 3: Post Đã Gắn Tag Nhưng Chưa Có Trong Manifest
- **Thời điểm:** Post và metadata tag đã ghi thành công vào MySQL, nhưng tiến trình bị ngắt trước khi tệp manifest trên đĩa kịp append ID.
- **Cơ chế phục hồi bằng Code:** 
  Khi chạy lệnh `php run_controlled_import.php rollback <manifest>`, phương thức `find_posts_by_ownership_tag( $run_id )` (dòng 190) sẽ thực thi truy vấn SQL:
  ```sql
  SELECT p.ID, p.post_type, p.post_parent 
  FROM wp_posts p 
  INNER JOIN wp_postmeta pm ON p.ID = pm.post_id 
  WHERE pm.meta_key = '_controlled_import_run_id' 
    AND pm.meta_value = %s
  ```
  Tất cả các post mang tag của run hiện tại nhưng chưa có trong manifest sẽ được tự động đồng hóa vào hàng đợi xóa (`created_parents` hoặc `created_variations`) và bị xóa sạch sẽ qua `$delete_and_verify()`.

### 5.3. Rủi ro của Phục hồi Bằng Snapshot T-0 Đối với Dữ liệu Phát sinh
- Khôi phục Snapshot T-0 là thao tác quay ngược toàn bộ database về trạng thái trước import.
- **Nguy cơ mất mát dữ liệu:** Nếu website đang mở cho khách truy cập hoặc có quản trị viên đang thao tác, mọi đơn hàng, khách hàng mới, bài viết hoặc cấu hình phát sinh sau thời điểm T-0 sẽ bị **xóa sạch vĩnh viễn**.
- **Yêu cầu quy trình bắt buộc:**
  1. Duy trì chế độ bảo trì `woocommerce_coming_soon = yes` trong suốt phiên import.
  2. Đóng băng toàn bộ hoạt động của quản trị viên (không ai được đăng nhập WP Admin trong lúc import).

### 5.4. Giới hạn Tuyệt đối của Rollback Trong-Tiến-Trình (In-Process Rollback)
- Khối `catch (Throwable)` trong mã nguồn chỉ xử lý được các ngoại lệ PHP thông thường.
- Các sự cố hệ thống sau đây sẽ **hạ gục tiến trình ngay lập tức mà không thể kích hoạt rollback tự động**:
  1. **Hard SIGKILL / Task Manager End Process:** Tiến trình bị hủy ngay lập tức bởi hệ điều hành.
  2. **Out Of Memory (Fatal OOM):** PHP cạn kiệt bộ nhớ và dừng khẩn cấp.
  3. **Mất kết nối MySQL Socket:** Rollback không thể kết nối tới MySQL để xóa bản ghi.
  4. **Mất điện đột ngột:** Phần cứng dừng hoạt động.
- Trong tất cả các trường hợp trên, kỹ sư vận hành bắt buộc phải can thiệp thủ công: chạy lệnh CLI rollback độc lập hoặc khôi phục snapshot database.

---

## 6. YÊU CẦU AN TOÀN SNAPSHOT T-0 VÀ RESTORE

Bản snapshot cũ ngày 16/10 (`stage_1e_gate2_pre_import_20261016_223502.sql`) dù đã được kiểm chứng khôi phục thành công tại Gate 3I, nhưng là bản chụp tĩnh và không phản ánh các tinh chỉnh giao diện Storefront phát sinh ở Stage 1F.

**Quy trình Bắt buộc Thực hiện Snapshot T-0 Ngay Trước Gate 4:**
1. **Kiểm tra Môi trường:** Xác nhận `woocommerce_coming_soon = yes` và kết nối đúng `127.0.0.1:10011` database `local`.
2. **Xuất Bản Dump Mới:** Chạy công cụ `mysqldump` tạo tệp riêng: `backups/gate4_snapshot_t0_<YYYYMMDD_HHMMSS>.sql`.
3. **Kiểm tra Tính Toàn vẹn:** Kiểm tra exit code = 0, kích thước $> 1.1\text{ MB}$, đầy đủ header và footer dump.
4. **Tính SHA-256:** Tính mã hash và ghi vào hồ sơ vận hành.
5. **Khóa Tệp Chống Ghi đè:** Đặt thuộc tính Read-Only (`attrib +r`) cho tệp dump.
6. **Thẩm quyền Kích hoạt:** Chỉ người dùng/kỹ sư trưởng mới có quyền ra quyết định khôi phục snapshot khi phát hiện sự cố Window 2.

---

## 7. DANH MỤC FINDINGS THEO MỨC ĐỘ NGHIÊM TRỌNG

### Finding F-3N-R2I-01: Bất nhất Cú pháp Dòng lệnh Rollback giữa Tài liệu Cũ và Parser Thực tế
- **Severity:** HIGH
- **Mô tả:** Các báo cáo `STAGE_3N_R2E` và `STAGE_3N_R2F` hướng dẫn chạy `php run_controlled_import.php --mode=rollback --run-id=<id>`, trong khi bộ phân tích dòng lệnh thực tế chỉ chấp nhận `php run_controlled_import.php rollback <path-to-manifest.json>`.
- **Bằng chứng:** Mã nguồn `run_controlled_import.php` dòng 1370–1375 so với `STAGE_3N_R2E` dòng 277 và `STAGE_3N_R2F` dòng 130.
- **Ảnh hưởng:** Kỹ sư vận hành khi gặp sự cố khẩn cấp sẽ không thể rollback được nếu copy lệnh từ tài liệu cũ, gây chậm trễ và lúng túng trong ứng phó thảm họa.
- **Biện pháp giảm thiểu:** Sử dụng Bảng 3 của báo cáo này làm cẩm nang vận hành chuẩn; cấm sử dụng các lệnh có cờ `--mode=` hoặc `--run-id=`.
- **Trạng thái:** **VERIFIED & RECONCILED**.

### Finding F-3N-R2I-02: Bất nhất Cú pháp Dòng lệnh Import Thật
- **Severity:** HIGH
- **Mô tả:** Báo cáo cũ hướng dẫn `php run_controlled_import.php --mode=import --run-id=...`, trong khi parser thực tế bắt buộc token: `php run_controlled_import.php import --gate4-user-approved-execute`.
- **Bằng chứng:** Dòng 1387–1393 của `run_controlled_import.php`.
- **Ảnh hưởng:** Lệnh import theo tài liệu cũ sẽ bị chặn ngay tại khối `Usage`.
- **Biện pháp giảm thiểu:** Chuẩn hóa cú pháp lệnh import tại Bảng 3 của báo cáo này.
- **Trạng thái:** **VERIFIED & RECONCILED**.

### Finding F-3N-R2I-03: Rủi ro Vận hành khi Chưa Có Bản Snapshot T-0 tại Chỗ
- **Severity:** HIGH
- **Mô tả:** Snapshot hiện có (`stage_1e_gate2...`) được tạo từ ngày 16/10. Nếu chạy import mà gặp Crash Window 2, việc restore bản cũ này sẽ làm mất các cấu hình storefront đã hoàn thiện ở Stage 1F.
- **Ảnh hưởng:** Nguy cơ thụt lùi trạng thái dự án (Regression) nếu phải khôi phục thảm họa.
- **Biện pháp giảm thiểu:** Bắt buộc tạo Snapshot T-0 mới tinh tại thời điểm chuẩn bị bấm lệnh import Gate 4.
- **Trạng thái:** **VERIFIED & PRE-CONDITION ENFORCED**.

---

## 8. PHẠM VI ĐÃ XÁC MINH VÀ CHƯA XÁC MINH

### 8.1. Đã Xác minh Trực tiếp (Directly Verified)
- [x] Đọc trực tiếp bộ phân tích dòng lệnh (CLI dispatcher) của `run_controlled_import.php` (dòng 1352–1411).
- [x] Tính SHA-256 (`640B92D4...457C`) và kích thước (67,434 bytes) của importer hiện hành.
- [x] Định vị 100% các dòng văn bản chứa cú pháp lỗi thời trong các báo cáo cũ.
- [x] Kiểm tra database chính `local`: `product = 0`, `product_variation = 0`, `woocommerce_coming_soon = yes`.
- [x] Kiểm tra thư mục `backups/manifests/`: Hoàn toàn chưa tồn tại, khẳng định chưa có import dở dang nào.

### 8.2. Chưa Thể Xác minh (NOT VERIFIED)
- [x] **`NOT VERIFIED: Thử nghiệm importer với user MySQL giới hạn quyền`** (Môi trường LocalWP chạy `root`, chưa chạy thử nghiệm cô lập).
- [x] **`NOT VERIFIED: Khôi phục Window 2 tự động bằng mã nguồn`** (Bất khả thi về mặt nguyên lý an toàn fail-closed).

---

## 9. DANH SÁCH THAY ĐỔI TÀI LIỆU ĐƯỢC ĐỀ XUẤT (PROPOSED ROADMAP)

*Lưu ý: Các thay đổi này chỉ được đề xuất để thực hiện trong tài liệu vận hành Gate 4 chính thức, tuyệt đối KHÔNG sửa đè vào các báo cáo lịch sử:*

1. **Đề xuất Đ1 (Cập nhật Sổ tay Gate 4):** Soạn thảo một tài liệu hướng dẫn vận hành Gate 4 độc lập, trong đó chỉ ghi nhận duy nhất 2 lệnh chuẩn xác:
   - Import: `php -d memory_limit=512M run_controlled_import.php import --gate4-user-approved-execute`
   - Rollback: `php -d memory_limit=512M run_controlled_import.php rollback backups/manifests/manifest_<run_id>.json`
2. **Đề xuất Đ2 (Bổ sung Cảnh báo Khẩn cấp):** Thêm ghi chú nổi bật tại đầu tài liệu vận hành: "TUYỆT ĐỐI KHÔNG SỬ DỤNG CÁC CỜ `--mode=` HOẶC `--run-id=`".
3. **Đề xuất Đ3 (Quy trình Snapshot T-0):** Đưa toàn bộ 12 bước của Checklist Snapshot T-0 (Mục 6) vào quy trình bắt buộc trước khi cấp quyền chạy Gate 4.

---

## 10. QUYẾT ĐỊNH CỔNG 4 (GATE 4 DECISION)

### **MANDATORY STOP — GATE 4 TUYỆT ĐỐI CHƯA ĐƯỢC PHÉP BẮT ĐẦU**

Giai đoạn Stage 3N-R2I kết thúc với kết quả đánh giá đạt **`CONDITIONAL PASS — FINDINGS OPEN`**. Toàn bộ các mâu thuẫn về cú pháp dòng lệnh đã được đối chiếu và làm sáng tỏ hoàn toàn với mã nguồn thực tế.

Website ATELIER và database `local` được bảo vệ an toàn 100%. Hệ thống dừng lại tại đây và chờ đợi chỉ thị tiếp theo từ người dùng.
