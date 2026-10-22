# STAGE 3N-R2H — DISASTER RECOVERY READINESS & OPERATIONAL SAFETY AUDIT REPORT

**Dự án:** Furniture E-commerce (Site ATELIER)  
**Môi trường:** Local WordPress 7.1.3 / WooCommerce 11.2.1 / PHP 8.2.29 / MySQL 8.4.0  
**Giai đoạn:** Stage 3N-R2H — Đánh giá Mức độ Sẵn sàng Phục hồi Thảm họa & An toàn Vận hành  
**Thời điểm thực hiện:** 2026-10-19  
**Vai trò:** Senior WordPress/WooCommerce Engineer, Database Reliability Engineer, PHP Security Reviewer & Independent Disaster Recovery Auditor  
**Chế độ thực thi:** STRICT READ-ONLY AUDIT (Không ghi/xóa database, không sửa importer, không restore, không chạy Gate 4)  
**Tài liệu tham chiếu:** `STAGE_3N_R2G_READ_ONLY_CONSISTENCY_AUDIT_REPORT.md`, `STAGE_3N_R2F_BASELINE_HASH_RECONCILIATION_REPORT.md`, `STAGE_1E_3F_GATE_2_SNAPSHOT_VERIFICATION_REPORT.md`, `stage_1e_3g_9_gate3i_snapshot_restore_verification_report.md`

---

## 1. EXECUTIVE VERDICT

### KẾT LUẬN KIỂM TOÁN: **`CONDITIONAL PASS — FINDINGS OPEN`** (Cho Phạm vi Đánh giá Khả năng Phục hồi)
### QUYẾT ĐỊNH CỔNG 4: **`MANDATORY STOP — GATE 4 TUYỆT ĐỐI CHƯA ĐƯỢC PHÉP CHẠY`**

Kiểm toán viên độc lập xác nhận:
1. **Phát hiện Bất nhất Cú pháp Vận hành Khẩn cấp (F-3N-R2H-01 - HIGH):** Phát hiện sai lệch nghiêm trọng giữa tài liệu hướng dẫn trước đây và mã nguồn thực tế của bộ phân tích CLI. Tài liệu cũ hướng dẫn chạy `php run_controlled_import.php --mode=rollback --run-id=<id>`, nhưng mã nguồn thực tế tại dòng 1370 chỉ chấp nhận cú pháp positional: `php run_controlled_import.php rollback <path-to-manifest.json>`. Nếu xảy ra sự cố khẩn cấp, kỹ sư vận hành gõ theo tài liệu cũ sẽ bị lỗi cú pháp (`Usage`) và không thể rollback!
2. **Xác nhận Giới hạn Phục hồi Window 2 (F-3N-R2H-02 - HIGH):** Nếu tiến trình bị ngắt sau `wp_insert_post()` nhưng trước `update_post_meta()` gán tag, bài viết mồ côi **không thể rollback bằng mã nguồn**. Ranh giới an toàn bắt buộc phải là **FAIL-CLOSED**. Phương án phục hồi duy nhất là **khôi phục database từ Snapshot T-0**.
3. **Đánh giá Tính Toàn vẹn của Snapshot Hiện có:** Đã rà soát 4 bản snapshot SQL trong workspace. Snapshot chuẩn bị cho pre-import (`stage_1e_gate2_pre_import_20261016_223502.sql`, 1,236,031 bytes) đã được chứng minh khôi phục thành công trên cổng cô lập 13306 trong Gate 3I. Tuy nhiên, nó là bản chụp tĩnh ngày 16/10 và không bao phủ các thay đổi phát sinh sau đó; do đó **bắt buộc phải tạo bản Snapshot T-0 mới** ngay trước Gate 4.
4. **Xác thực Ma trận Quyền MySQL & Filesystem:** Importer lưu trữ manifest dưới dạng tệp JSON trên ổ đĩa (`backups/manifests/`), hoàn toàn không sử dụng bảng MySQL và không cần quyền DDL (`CREATE/DROP/ALTER`). Yêu cầu bắt buộc là quyền ghi hệ thống tệp trên thư mục manifest.

---

## 2. PHẠM VI VÀ GIỚI HẠN KIỂM TOÁN (SCOPE & LIMITATIONS)

- **Phương pháp tiếp cận:** Kiểm toán mã nguồn tĩnh (Static Code Audit), đối chiếu nhị phân (Binary Hashing), phân tích luồng điều khiển (Control Flow Analysis) và truy vấn database chỉ đọc (`SELECT`).
- **Giới hạn an toàn:** Không thực hiện bất kỳ lệnh khôi phục snapshot thử nghiệm nào trên database `local` hoặc môi trường test trong đợt kiểm toán này. Không cố tình gây crash, OOM hay kill tiến trình MySQL.
- **Phân định rõ mức độ tin cậy:**
  - *Directly Verified:* Cú pháp CLI parser; logic kiểm tra an toàn đường dẫn manifest; cấu trúc dump SQL; kết quả hash tệp thực tế.
  - *Reported Previously:* Quá trình restore Gate 3I trên cổng 13306 (đã đọc báo cáo và đối chiếu checksum tệp dump).
  - *Technical Deduction:* Nguy cơ mất mát dữ liệu hợp lệ nếu restore snapshot khi site đang mở cho người dùng; hành vi của PHP khi gặp hard SIGKILL.
  - *NOT VERIFIED:* Khôi phục tự động Window 2 bằng code (bất khả thi về mặt an toàn); thử nghiệm importer dưới tài khoản MySQL bị tước quyền `DROP/ALTER`.

---

## 3. RÀ SOÁT SNAPSHOT VÀ QUY TRÌNH PHỤC HỒI HIỆN CÓ

### 3.1. Danh mục các Snapshot SQL trong Workspace

| STT | Tên Snapshot / Đường dẫn tuyệt đối | Kích thước (Bytes) | SHA-256 Hash thực tế | Timestamp tạo | Database đại diện | Phạm vi bao phủ | Bằng chứng kiểm thử khôi phục trước đó |
|---|---|---|---|---|---|---|---|
| **1** | `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\backups\stage_1e_gate2_pre_import_20261016_223502.sql` | 1,236,031 | `9152E46BB462C321EDD7778A6DA7889450A90301BD1627682D10345A5BDCC8A3` | 2026-10-16 22:35:02 (UTC+7) | `local` | **Chỉ SQL Database** (52 bảng: Core WP + WC Baseline + Thuộc tính/Terms Stage 1E.3B). Không gồm uploads. | **ĐÃ XÁC MINH RESTORE THÀNH CÔNG** tại Gate 3I trên MySQL aux port 13306 (Exit code 0, 52/52 bảng, 0 products). |
| **2** | `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\backups\stage_1e_pre_import_snapshot.sql` | 1,336,511 | `A9617E0173ED8D81E64B2F6BB3521E946406502AB12DA01D8328F46CB401A2F6` | 2026-10-16 (Stage 1E.3A) | `local` | **Chỉ SQL Database** (Bản snapshot ban đầu trước khi tạo global attributes). | Tồn tại, đọc được, chưa có báo cáo restore độc lập riêng. |
| **3** | `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\backups\stage_1a_baseline_backup.sql` | 1,202,692 | `BFBFC6E22E95411C3BDEE630D5FDE9D4389FDE9535AB702883FFCD98F57A32A8` | Khởi tạo dự án (Stage 1A) | `local` | **Chỉ SQL Database** (Trạng thái sơ khai của site ATELIER). | Tồn tại, đọc được. |
| **4** | `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\sql\local.sql` | 1,688,420 | `29BB6E8AACB88996FEF70E42E0F31464181D236938F3235247CAB5057BB4039F` | Tự động sinh bởi LocalWP | `local` | **Chỉ SQL Database** (LocalWP state dump). | Tồn tại, đọc được. |

### 3.2. Đánh giá Rủi ro trong Quy trình Phục hồi Snapshot Hiện tại
1. **Rủi ro Xóa bảng Không có Tiền tố Database (High Risk):**
   - Phân tích nội dung dump `stage_1e_gate2_pre_import_20261016_223502.sql`: Tệp chứa đúng 52 câu lệnh `DROP TABLE IF EXISTS wp_*;` và `CREATE TABLE wp_*;`.
   - Tệp dump **hoàn toàn không có câu lệnh `USE <database_name>;`**.
   - *Nguy cơ:* Nếu kỹ sư chạy lệnh `mysql -u root -p < snapshot.sql` mà quên chỉ định tên database, hoặc kết nối mặc định trỏ nhầm database khác, MySQL sẽ thực thi DROP các bảng trên database hiện hành của phiên làm việc.
2. **Thiếu Kiểm tra `SELECT DATABASE()` Trước Thao tác:**
   - Trong các tài liệu hướng dẫn khôi phục cũ, chưa có bước kiểm tra bắt buộc `SELECT DATABASE();` trước khi bơm dữ liệu.
3. **Phạm vi Snapshot chỉ giới hạn ở SQL Database:**
   - Không có snapshot nào bao gồm tệp tin hệ thống (`wp-content/uploads/`, child theme files, code plugins).
   - Nếu trong quá trình import có tải ảnh mới về ổ đĩa, việc restore SQL sẽ không dọn dẹp các tệp ảnh mồ côi trong `wp-content/uploads/`. (Tuy nhiên, bộ dữ liệu 22 sản phẩm hiện tại sử dụng attachment có sẵn ID 5 nên rủi ro này ở mức thấp).

---

## 4. ĐÁNH GIÁ KẾ HOẠCH SNAPSHOT T-0 (T-0 READINESS CHECKLIST)

Bản snapshot ngày 16/10 (`stage_1e_gate2...`) đã cũ so với thời điểm hiện tại. Do đó, **bắt buộc phải tạo bản Snapshot T-0 mới** ngay trước khi chạy lệnh import thật. 

Kế hoạch tạo và kiểm tra Snapshot T-0 phải đáp ứng đầy đủ 12 tiêu chí bắt buộc:

| # | Tiêu chí Kiểm tra | Yêu cầu Kỹ thuật Cụ thể | Đánh giá Mức độ Sẵn sàng |
|---|---|---|---|
| **1** | Trạng thái Bảo trì / Coming Soon | Xác nhận `woocommerce_coming_soon = yes` để ngăn chặn hoàn toàn khách truy cập hoặc đơn hàng phát sinh trong lúc thao tác. | **SẴN SÀNG** (Đã xác minh `yes` trên DB `local`) |
| **2** | Không có Tiến trình Khác Chạy ngầm | Kiểm tra thư mục `backups/manifests/` không có tệp manifest nào đang ở trạng thái `IN_PROGRESS`. | **SẴN SÀNG** |
| **3** | Xác nhận Đúng Target Database | Chạy `SELECT DATABASE();` và `SELECT @@hostname;` để khẳng định 100% đang kết nối tới `local` trên `127.0.0.1:10011`. | **QUY TRÌNH ĐÃ THIẾT LẬP** |
| **4** | Đường dẫn Backup Riêng biệt | Lưu dump tại: `backups/gate4_snapshot_t0_<YYYYMMDD_HHMMSS>.sql`. Tuyệt đối không ghi đè vào các snapshot cũ. | **QUY TRÌNH ĐÃ THIẾT LẬP** |
| **5** | Kiểm tra Exit Code Hoàn tất | Lệnh `mysqldump` phải kết thúc với `$LASTEXITCODE == 0` và luồng stderr không có lỗi. | **BẮT BUỘC KIỂM TRA** |
| **6** | Kiểm tra Dung lượng & Đọc được | Dung lượng tệp phải $> 1.1\text{ MB}$, chứa đầy đủ header (`-- MySQL dump`) và footer (`-- Dump completed`). | **BẮT BUỘC KIỂM TRA** |
| **7** | Tính SHA-256 Checksum | Tính hash SHA-256 ngay sau khi xuất tệp và đối chiếu lại với tệp trên đĩa. | **BẮT BUỘC KIỂM TRA** |
| **8** | Lưu Biên bản Ghi nhận | Ghi nhận đường dẫn, kích thước byte, SHA-256 và timestamp vào tài liệu bàn giao Gate 4. | **BẮT BUỘC KIỂM TRA** |
| **9** | Khả năng Khôi phục | Dựa trên bằng chứng Gate 3I đã chứng minh mysqldump 8.4.0 tương thích 100% với engine MySQL của LocalWP. | **ĐÃ ĐƯỢC CHỨNG MINH TẠI GATE 3I** |
| **10** | Chống Ghi đè / Xóa nhầm | Đặt cờ Read-Only (`attrib +r`) cho tệp SQL sau khi tạo xong. | **KHUYẾN NGHỊ BẮT BUỘC** |
| **11** | Điều kiện Bắt buộc Dừng | Nếu lệnh dump có exit code khác 0, file rỗng hoặc lỗi cú pháp, **GATE 4 PHẢI DỪNG LẬP TỨC**. | **ĐIỀU KIỆN TIÊN QUYẾT** |
| **12** | Thẩm quyền Kích hoạt Restore | Chỉ Kỹ sư trưởng / Người dùng mới có thẩm quyền ra lệnh restore snapshot khi phát hiện sự cố Window 2. | **XÁC LẬP TƯỜNG MINH** |

---

## 5. RÀ SOÁT HƯỚNG DẪN ROLLBACK CLI TỪ MÃ NGUỒN

Đọc trực tiếp bộ phân tích dòng lệnh (CLI dispatcher) tại dòng 1352–1411 của `run_controlled_import.php`:

```php
$action = $argv[1] ?? 'help';
if ( 'verify' === $action ) { ... }
elseif ( 'rollback' === $action ) {
    $manifest_file = $argv[2] ?? '';
    ...
    $safe_path = $harness->validate_manifest_path_safety( $manifest_file );
    $summary = $harness->rollback( $safe_path );
    ...
}
elseif ( 'import' === $action ) {
    $auth_token = $argv[2] ?? '';
    if ( $auth_token !== '--gate4-user-approved-execute' ) { ... exit(1); }
    ...
}
```

### 5.1. Bảng Đối chiếu Cú pháp Dòng lệnh: Tài liệu Cũ vs Mã nguồn Thực tế

| Thao tác | Cú pháp được Ghi trong Báo cáo Cũ (3N-R2F / 3N-R2G) | Cú pháp Thực tế Bắt buộc theo Source Code | Đánh giá & Hậu quả nếu Chạy sai |
|---|---|---|---|
| **Chạy Import Thật** | `php run_controlled_import.php --mode=import --run-id=run_gate4_production` | `php run_controlled_import.php import --gate4-user-approved-execute` | **LỖI CÚ PHÁP NGHIÊM TRỌNG**: Lệnh cũ sẽ rơi vào nhánh `else`, in `Usage` và dừng ngay lập tức. |
| **Chạy Rollback** | `php run_controlled_import.php --mode=rollback --run-id=<run_id>` | `php run_controlled_import.php rollback <path-to-manifest.json>` | **LỖI CÚ PHÁP NGHIÊM TRỌNG**: Lệnh cũ sẽ rơi vào `Usage` và không thể rollback! Bắt buộc phải truyền đường dẫn tệp JSON tại tham số thứ 2. |
| **Chạy Preflight Verify** | `php run_controlled_import.php verify` | `php run_controlled_import.php verify` | **CHÍNH XÁC 100%**. |

### 5.2. Các Rào chắn An toàn trong Mã nguồn Rollback
1. **Bảo vệ Đường dẫn Tệp Manifest (`validate_manifest_path_safety`):**
   - Kiểm tra `file_exists()`, `is_file()`, `is_readable()`.
   - Bắt buộc phần mở rộng phải là `.json`.
   - **Rào chắn Path Traversal:** Chuẩn hóa đường dẫn canonical và kiểm tra `0 === strpos($norm_file, $norm_dir)`. Nếu tệp nằm ngoài thư mục `backups/manifests/` (ví dụ cố tình truyền `../../wp-config.php`), harness lập tức ném ngoại lệ `Path safety violation`.
2. **Kiểm tra Ràng buộc Môi trường trước khi Xóa:**
   - Database name trong manifest phải khớp chính xác với active database (`local` trên prod, hoặc `local_test_isolated_3nr2` trên test).
   - Table prefix trong manifest phải khớp `wp_`.
   - CSV hash trong manifest phải khớp mã khóa chuẩn.
3. **Bảo vệ Chống Xóa Nhầm Đối tượng Không thuộc Quyền sở hữu:**
   - Đối tượng có sẵn trong `pre_existing_attachments` (ID 5) bị chặn tuyệt đối.
   - Hàm `find_posts_by_ownership_tag( $run_id )` chủ động truy vấn database để vớt các đối tượng Window 3 đã có tag nhưng chưa vào manifest file.
   - Kiểm tra 100% các post trước khi xóa: post_type phải đúng (`product` / `product_variation`), tag `_controlled_import_run_id` phải khớp chính xác `run_id` của phiên.
4. **Hậu kiểm Trạng thái Thực tế sau khi Xóa (`$delete_and_verify`):**
   - Gọi `wp_delete_post($id, true)`.
   - Thực hiện truy vấn ngay lập tức: `SELECT ID FROM wp_posts WHERE ID = %d`.
   - Nếu bản ghi vẫn còn tồn tại trong MySQL, harness lập tức ném ngoại lệ dừng quy trình, không tiếp tục xóa các đối tượng khác.
5. **Tính Idempotent (An toàn khi Chạy lại nhiều lần):**
   - Nếu manifest đã ở trạng thái `ROLLED_BACK`, lần gọi tiếp theo trả về mảng rỗng (0 deletions) mà không báo lỗi.
   - Nếu quá trình xóa dở dang, manifest được cập nhật thành `FAILED_ROLLBACK_INCOMPLETE`. Lần chạy tiếp theo sẽ tiếp tục xóa các đối tượng còn lại.

---

## 6. MA TRẬN QUYỀN DATABASE TỐI THIỂU & FILESYSTEM

| Thao tác nghiệp vụ | Thành phần thực thi | Quyền MySQL cần thiết | Quyền Filesystem cần thiết | Đã thử nghiệm Runtime chưa? | Rủi ro nếu thiếu quyền | Mức độ tin cậy |
|---|---|---|---|---|---|---|
| **Preflight Identity & Duplicate SKU Check** | `preflight_verify()` | `SELECT` | `READ` trên file CSV | **ĐÃ TEST RUNTIME** (Test A1, A2, A4) | Không thể kiểm tra trùng lặp SKU | **Directly Verified** |
| **Quản lý Manifest JSON** | `flush_manifest()`, `initialize_manifest()` | **KHÔNG CẦN MYSQL** | `READ`, `WRITE` trên `backups/manifests/` | **ĐÃ TEST RUNTIME** (Sinh và cập nhật file manifest) | Importer ném ngoại lệ dừng khẩn cấp | **Directly Verified** |
| **Tạo Sản phẩm & Biến thể** | `wp_insert_post()`, `update_post_meta()` | `SELECT`, `INSERT`, `UPDATE` | Không cần | **ĐÃ TEST RUNTIME** (Tạo sản phẩm thật trên DB cô lập) | Lỗi không tạo được post | **Directly Verified** |
| **Gắn Terms & Categories** | `wp_set_object_terms()` | `SELECT`, `INSERT`, `UPDATE`, `DELETE` (term_relationships) | Không cần | **ĐÃ TEST RUNTIME** | Lỗi không phân loại sản phẩm | **Directly Verified** |
| **Rollback Xóa Sản phẩm** | `wp_delete_post()` | `SELECT`, `DELETE` | `WRITE` trên file manifest | **ĐÃ TEST RUNTIME** (Test D1, D4) | Không xóa được sản phẩm, manifest chuyển FAILED_ROLLBACK | **Directly Verified** |
| **Quyền DDL (`CREATE/DROP/ALTER`)** | Importer / Rollback | **HOÀN TOÀN KHÔNG CẦN** | Không | **ĐÃ XÁC MINH SOURCE CODE** | Không có rủi ro vì code không gọi DDL | **Directly Verified** |
| **Tạo Snapshot T-0** | Công cụ `mysqldump` ngoài | `SELECT`, `LOCK TABLES`, `SHOW VIEW` | `WRITE` trên thư mục `backups/` | **ĐÃ TEST TẠI GATE 3I** | Không tạo được snapshot dự phòng | **Reported Previously** |
| **Khôi phục Snapshot** | Công cụ `mysql` CLI ngoài | `CREATE`, `DROP`, `ALTER`, `INSERT` | `READ` trên tệp SQL backup | **ĐÃ TEST TẠI GATE 3I** | Không restore được khi gặp sự cố Window 2 | **Reported Previously** |

---

## 7. ĐÁNH GIÁ RỦI RO PHỤC HỒI WINDOW 2 (FAIL-CLOSED BOUNDARY)

### 7.1. Bản chất của Crash Window 2
- **Thời điểm xảy ra:** Tiến trình import bị tắt đột ngột (mất điện, crash MySQL, kill process) ngay sau khi `wp_insert_post()` thực thi xong nhưng **TRƯỚC KHI** lệnh `update_post_meta($post_id, '_controlled_import_run_id', $run_id)` được ghi vào bảng `wp_postmeta`.
- **Trạng thái dữ liệu:**
  1. Bản ghi sản phẩm tồn tại trong `wp_posts` (với ID tự tăng mới, ví dụ ID 100).
  2. Bảng `wp_postmeta` **hoàn toàn không có dòng metadata nào chứa `_controlled_import_run_id`**.
  3. Tệp manifest trên đĩa **chưa ghi nhận ID 100**.

### 7.2. Vì sao Không được Xóa bằng Heuristic (SKU, Title, Timestamp)?
- Nếu viết code tự động tìm các post có `post_type = 'product'` được tạo trong vòng 5 phút trước đó hoặc có SKU khớp CSV để xóa, hệ thống sẽ đối mặt với rủi ro nghiêm trọng:
  - Xóa nhầm sản phẩm do quản trị viên hoặc một quy trình khác tạo song song.
  - Xóa nhầm dữ liệu nếu đồng hồ hệ thống bị trôi lệch.
  - Vi phạm nguyên tắc phân định quyền sở hữu đa phiên.
- **Ranh giới an toàn tuyệt đối:** Mã nguồn importer tuân thủ nguyên tắc **FAIL-CLOSED**. Nếu đối tượng không có tag của phiên, importer từ chối xóa và coi đối tượng đó thuộc quyền sở hữu của hệ thống/người dùng khác.

### 7.3. Đánh giá Rủi ro Phục hồi Bằng Snapshot T-0
- Vì mã nguồn không thể xóa post Window 2, **phương án duy nhất để dọn dẹp sạch sẽ database là khôi phục từ Snapshot T-0**.
- **Nguy cơ mất dữ liệu hợp lệ (Data Loss Window):**
  - Khôi phục snapshot là thao tác quay ngược toàn bộ database về thời điểm T-0.
  - Nếu trong khoảng thời gian từ T-0 đến thời điểm crash, có bất kỳ thay đổi hợp lệ nào phát sinh trên website (ví dụ: bài viết mới, bình luận, thay đổi cấu hình, khách đặt hàng nếu site mở), toàn bộ các thay đổi đó **SẼ BỊ XÓA HOÀN TOÀN**.
- **Biện pháp Kiểm soát Vận hành Bắt buộc:**
  1. **Khóa chặt Storefront:** Giữ nguyên `woocommerce_coming_soon = yes` trong suốt quá trình import.
  2. **Đóng băng Quản trị (Freeze Admin Activity):** Nghiêm cấm mọi kỹ sư hoặc quản trị viên đăng nhập vào WP Admin để chỉnh sửa nội dung, cài plugin hoặc đổi theme trong lúc import đang chạy.
  3. **Thời gian Import Cực ngắn:** Quá trình import 22 parent + 42 variation qua CLI chỉ mất dưới 5 giây. Khoảng thời gian rủi ro (exposure window) là cực nhỏ nếu tuân thủ lệnh đóng băng.

---

## 8. ĐÁNH GIÁ TÍNH ĐỦ CỦA BẰNG CHỨNG TEST (TEST EVIDENCE MATRIX)

| Kịch bản kiểm thử | Test ID | Loại Test | Có thao tác DB thật không? | Kiểm tra trạng thái cuối trong DB? | Kiểm tra Manifest trên đĩa? | Loại hình Crash được kiểm tra | Kết luận được Bằng chứng Hỗ trợ | Kết luận CHƯA được Hỗ trợ (Giới hạn) |
|---|---|---|---|---|---|---|---|---|
| **Từ chối Database sai 2 chiều** | A4 | Runtime Integration | Có (`SELECT DATABASE()`) | Có | Có | Ngoại lệ logic (`RuntimeException`) | Importer chặn đứng kết nối sai DB | Chưa test trường hợp DB crash giữa chừng |
| **Phát hiện Orphan Window 3 có Tag** | B1 | Runtime Integration | Có (Tạo post thật trên DB cô lập) | Có (Xác nhận vắng mặt sau xóa) | Có (Cập nhật manifest) | Mô phỏng thiếu manifest ID | Rollback phát hiện và xóa sạch post có tag ngoài manifest | Chưa test khi đĩa bị đầy không ghi được manifest |
| **Bảo vệ Post thuộc Phiên khác** | B2 | Runtime Integration | Có (Tạo post thật với tag run khác) | Có (Xác nhận post còn nguyên trong DB) | Có | Logic phân định run_id | Post phiên khác được bảo vệ an toàn 100% | Không có |
| **Bảo vệ Post Window 2 Chưa gắn Tag** | B3 | Runtime Integration | Có (Tạo post thật không gắn meta tag) | Có (Xác nhận post còn nguyên trong DB) | Có | Mô phỏng crash Window 2 | Importer tuân thủ fail-closed, không xóa nhầm post untagged | Chứng minh code không tự xóa được Window 2 (cần snapshot) |
| **Lỗi Truy vấn Ownership Tag** | B4 | Runtime Integration | Có (Mô phỏng DB query failure) | Có | Có | Mô phỏng lỗi DB query | Quy trình dừng ngay lập tức (fail-closed) | Chưa test mất kết nối TCP socket thật |
| **Hậu kiểm Trạng thái Xóa sau Rollback** | D1 | Runtime Integration | Có (Tạo parent + variation thật) | Có (Truy vấn `SELECT ID` xác nhận null) | Có (Manifest chuyển `ROLLED_BACK`) | Quy trình xóa chuẩn | Rollback xóa sạch và xác minh sạch sẽ trên DB | Không có |
| **Xử lý Khi WordPress Từ chối Xóa** | D2 | Runtime Integration | Có (Chặn qua filter `pre_delete_post`) | Có (Post vẫn còn trong DB) | Có (Manifest chuyển `FAILED_ROLLBACK_INCOMPLETE`) | Mô phỏng API delete failure | Rollback bắt được lỗi và cập nhật trạng thái dở dang | Chưa test crash tiến trình trong lúc đang xóa |
| **Bảo vệ Phân cấp Cha - Con** | D3 | Runtime Integration | Có (Chặn xóa variation) | Có (Parent được bảo vệ nguyên vẹn trong DB) | Có | Mô phỏng lỗi xóa variation | Parent không bị xóa mồ côi nếu variation xóa hỏng | Không có |
| **Tính Idempotent của Rollback** | D4 | Runtime Integration | Có (Chạy rollback 2 lần liên tiếp) | Có | Có | Chạy lại nhiều lần | Lần 2 là no-op sạch sẽ, không sinh lỗi | Không có |
| **Bảo vệ Database Live `local`** | A3, D5 | Runtime Integration | Có (Đếm trực tiếp trên `local`) | Có (Đếm = 0 trước và sau toàn bộ suite) | Có | Môi trường cô lập | 0 mutations trên database chính `local` | Không có |
| **Bộ 85 Unit Tests Gate 3G** | 1–85 | Mock Unit Test | Không (Mock in-memory) | Kiểm tra mock state | Có | Mô phỏng ngoại lệ | Logic nghiệp vụ, hash, ma trận SKU đúng | Không chứng minh được crash vật lý (SIGKILL, OOM, mất điện) |

---

## 9. DANH MỤC FINDINGS THEO SEVERITY

### Finding F-3N-R2H-01: Sai lệch Cú pháp Vận hành Rollback Khẩn cấp trong Tài liệu
- **Severity:** HIGH
- **Mô tả:** Tài liệu hướng dẫn tại các stage trước ghi nhận cú pháp: `php run_controlled_import.php --mode=rollback --run-id=<id>`. Tuy nhiên, bộ phân tích dòng lệnh tại dòng 1370 chỉ chấp nhận: `php run_controlled_import.php rollback <path-to-manifest.json>`.
- **Ảnh hưởng:** Nếu kỹ sư vận hành gặp sự cố và làm theo tài liệu cũ, lệnh rollback sẽ thất bại ngay lập tức do lỗi cú pháp, làm kéo dài thời gian gián đoạn và tăng nguy cơ thao tác sai.
- **Biện pháp giảm thiểu:** Cập nhật tài liệu vận hành chính xác cú pháp dòng lệnh tại Mục 5.1 của báo cáo này.
- **Trạng thái:** **VERIFIED & DOCUMENTED**.

### Finding F-3N-R2H-02: Ranh giới Fail-Closed Window 2 Phụ thuộc 100% vào Snapshot T-0
- **Severity:** HIGH
- **Mô tả:** Nếu crash xảy ra tại Window 2 (post đã tạo nhưng chưa có tag), importer không thể rollback bằng mã nguồn. 
- **Ảnh hưởng:** Bài viết mồ côi sẽ tồn tại trong database nếu không có bản snapshot để khôi phục.
- **Biện pháp giảm thiểu:** Bắt buộc phải tạo bản Snapshot T-0 ngay trước khi import và lưu trữ an toàn.
- **Trạng thái:** **VERIFIED & OPERATIONAL REQUIREMENT LOCKED**.

### Finding F-3N-R2H-03: Nguy cơ Mất Dữ liệu Phát sinh sau Snapshot khi Restore
- **Severity:** MEDIUM
- **Mô tả:** Nếu phải kích hoạt khôi phục Snapshot T-0, mọi thay đổi hợp lệ phát sinh giữa thời điểm tạo snapshot và thời điểm restore sẽ bị mất.
- **Ảnh hưởng:** Nguy cơ mất đơn hàng hoặc bài viết mới nếu website không được khóa chặt.
- **Biện pháp giảm thiểu:** Bắt buộc duy trì chế độ `woocommerce_coming_soon = yes` và đóng băng toàn bộ thao tác quản trị viên trong suốt quá trình import.
- **Trạng thái:** **VERIFIED & PROCEDURAL MITIGATION DEFINED**.

### Finding F-3N-R2H-04: Chưa Thử nghiệm Importer với User MySQL Bị Tước Quyền `DROP/ALTER`
- **Severity:** MEDIUM
- **Mô tả:** Toàn bộ test runtime được thực hiện dưới user `root` (LocalWP). Chưa có thử nghiệm thực tế với user bị giới hạn quyền DML thuần túy.
- **Ảnh hưởng:** Chưa có bằng chứng thực nghiệm trên môi trường có chính sách database nghiêm ngặt.
- **Biện pháp giảm thiểu:** Đã đề xuất kế hoạch kiểm thử riêng biệt tại Stage 3N-R2G; giữ trạng thái `NOT VERIFIED`.
- **Trạng thái:** **NOT VERIFIED**.

---

## 10. ĐIỀU KIỆN CÒN THIẾU TRƯỚC KHI XEM XÉT GATE 4

Mặc dù tính toàn vẹn của mã nguồn importer và cơ chế rollback đã đạt mức tin cậy kỹ thuật cao, các điều kiện bắt buộc sau **VẪN CHƯA ĐƯỢC ĐÁP ỨNG** và cần hoàn tất trước khi mở Gate 4:

1. **Thực hiện Quy trình Snapshot T-0 tại Chỗ:** Ngay trước khi chạy import thật, phải chạy lệnh `mysqldump` để tạo tệp `backups/gate4_snapshot_t0_<timestamp>.sql`, kiểm tra dung lượng $> 1.1\text{ MB}$, exit code 0 và tính SHA-256.
2. **Khóa Đóng băng Hoạt động Website:** Kiểm tra `woocommerce_coming_soon` = `yes` và xác nhận không có bất kỳ phiên đăng nhập admin nào khác đang chỉnh sửa site.
3. **Sử dụng Đúng Cú pháp CLI:** Kỹ sư vận hành chỉ được sử dụng đúng cú pháp đã xác minh:
   ```powershell
   # Lệnh chạy Import thật:
   php -d memory_limit=512M run_controlled_import.php import --gate4-user-approved-execute

   # Lệnh Rollback khẩn cấp nếu gặp sự cố Window 3/4/5:
   php -d memory_limit=512M run_controlled_import.php rollback backups/manifests/manifest_<run_id>.json
   ```
4. **Phê duyệt Bằng Văn bản Riêng biệt từ Người dùng (Explicit Human Sign-off):** Gate 4 chỉ được mở khi có chỉ thị trực tiếp từ người dùng.

---

## 11. NHỮNG ĐIỀU CHƯA ĐƯỢC XÁC MINH (NOT VERIFIED)

1. **`NOT VERIFIED: Thử nghiệm phục hồi khi mất điện đột ngột hoặc kernel panic thực tế`** (Chỉ được kiểm chứng qua phân tích kiến trúc ACID của InnoDB và ngoại lệ mô phỏng).
2. **`NOT VERIFIED: Thử nghiệm importer dưới tài khoản MySQL không có quyền DROP/ALTER`** (Môi trường LocalWP chỉ có tài khoản `root`).
3. **`NOT VERIFIED: Tự động dọn dẹp Window 2 không dùng snapshot`** (Không khả thi về mặt an toàn fail-closed).

---

## 12. DANH SÁCH THAO TÁC ĐÃ THỰC HIỆN VÀ KHÔNG THỰC HIỆN

### 12.1. Thao tác đã thực hiện
- [x] Đọc và kiểm tra hash SHA-256, kích thước byte của 4 tệp snapshot SQL trong workspace.
- [x] Đọc và phân tích trực tiếp mã nguồn bộ phân tích CLI (dòng 1352–1411) của `run_controlled_import.php`.
- [x] Phân tích logic rào chắn an toàn và đường dẫn tệp trong `validate_manifest_path_safety()`.
- [x] Phân tích 10 kịch bản crash và xây dựng ma trận đánh giá năng lực phục hồi.
- [x] Rà soát và đối chiếu toàn bộ 85 mock tests và 19 runtime tests từ các tệp log JSON và test source.
- [x] Tạo báo cáo kiểm toán độc lập `STAGE_3N_R2H_DISASTER_RECOVERY_READINESS_AUDIT_REPORT.md`.

### 12.2. Thao tác tuyệt đối KHÔNG thực hiện
- [x] **KHÔNG** chạy lệnh import hoặc rollback.
- [x] **KHÔNG** restore snapshot hay tạo snapshot mới trong phiên kiểm toán này.
- [x] **KHÔNG** chạy bất kỳ câu lệnh SQL ghi dữ liệu nào.
- [x] **KHÔNG** tạo user hay thay đổi quyền MySQL.
- [x] **KHÔNG** sửa đổi mã nguồn importer, CSV hoặc test logs.
- [x] **KHÔNG** thực hiện bất kỳ thao tác Git làm thay đổi trạng thái.
- [x] **KHÔNG** tắt Coming Soon.
- [x] **KHÔNG** bắt đầu Gate 4.

---

## 13. KẾT LUẬN CUỐI CÙNG VỀ GATE 4

### **MANDATORY STOP — KHÔNG TỰ Ý CHUYỂN SANG GATE 4**

Giai đoạn Stage 3N-R2H kết thúc với kết quả đánh giá đạt **`CONDITIONAL PASS — FINDINGS OPEN`**. Toàn bộ rủi ro phục hồi thảm họa, ranh giới fail-closed của Window 2 và cú pháp CLI rollback chuẩn xác đã được làm sáng tỏ hoàn toàn.

Website ATELIER và database `local` được bảo vệ nguyên vẹn 100%. Hệ thống dừng lại tại đây và chờ đợi quyết định tiếp theo từ người dùng.
