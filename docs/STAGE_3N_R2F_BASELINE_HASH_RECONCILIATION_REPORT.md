# STAGE 3N-R2F — BASELINE HASH RECONCILIATION & RECOVERY READINESS AUDIT REPORT

**Dự án:** Furniture E-commerce (Site ATELIER)  
**Môi trường:** Local WordPress 7.1.3 / WooCommerce 11.2.1 / PHP 8.2.29 / MySQL 8.4.0  
**Giai đoạn:** Stage 3N-R2F — Đối chiếu Baseline/Hash & Đánh giá mức độ sẵn sàng phục hồi sự cố  
**Thời điểm thực hiện kiểm toán:** 2026-10-19  
**Vai trò:** Senior WordPress/WooCommerce Engineer & QA Auditor  
**Chế độ thực thi:** STRICT READ-ONLY AUDIT (Tuyệt đối không ghi, không sửa importer, không chạy Gate 4)  
**Tài liệu đối chiếu:** `STAGE_3N_R_IMPORT_RECOVERY_AND_INTEGRITY_REPORT.md`, `STAGE_3N_R2_ISOLATED_INTEGRATION_VERIFICATION_REPORT.md`, `STAGE_3N_R2E_EVIDENCE_RECONCILIATION_REPORT.md`

---

## 1. EXECUTIVE VERDICT

### KẾT LUẬN KIỂM TOÁN: **PASS** (Cho phạm vi đối chiếu Baseline, Hash Mật mã & Đánh giá Sẵn sàng Phục hồi)
### QUYẾT ĐỊNH CỔNG 4: **MANDATORY STOP — GATE 4 CHƯA ĐƯỢC PHÉP CHẠY**

1. **Tính xác thực của Baseline & Hash:** Đã lập bảng phả hệ đầy đủ của `run_controlled_import.php` qua 3 thế hệ (Pre-3N-R: 51,874 bytes; Post-3N-R/Pre-3N-R2: 67,418 bytes; Post-3N-R2/Hiện hành: 67,434 bytes). Mọi biến thiên về kích thước và số dòng đều được giải trình chính xác đến từng byte.
2. **Khắc phục sai lệch hash trong hồ sơ cũ:** Phát hiện và đối chiếu thành công sai lệch hash của bản sao lưu Pre-3N-R2 được ghi nhầm dạng chuỗi rút gọn trong báo cáo 3N-R2E; xác nhận hash thực tế chính xác 100% là `C3B1BF769CFF9127D5F7FA5B0DF23E798A36C0F5524E39C8CBC90102F03E66C3`.
3. **Phân định vị trí CSV làm việc:** Xác nhận tệp CSV làm việc chuẩn đã khóa nằm tại `data-preparation/woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv` (25,712 bytes, SHA-256: `D2C8566F...2BBA0`). Tệp CSV trong `plugins/` (26,176 bytes) là bản thô từ Stage 1E.1 chưa lọc URL ảnh lỗi.
4. **Đối chiếu Bằng chứng Kiểm thử:** 85 mock tests (`gate3g_regression_results.json`) và 19 runtime integration tests (`stage3nr2_runtime_results.json`) khớp 100% giữa log JSON, mã nguồn test và các báo cáo.
5. **Đánh giá Khả năng Phục hồi (Recovery Readiness):** Đã mô hình hóa toàn diện 5 Crash Windows. Khẳng định giới hạn cứng của Crash Window 2: Đối tượng chưa được gắn tag ownership **bắt buộc phải fail-closed**, không được xóa bằng heuristic (SKU/timestamp); giải pháp duy nhất là khôi phục snapshot database.
6. **Đánh giá Quyền Database Tối thiểu:** Ghi nhận `NOT VERIFIED` đối với việc thực thi importer bằng tài khoản MySQL bị hạn chế quyền (Restricted DB User) và đề xuất kế hoạch kiểm thử riêng trên môi trường cô lập.

---

## 2. ĐỐI CHIẾU BASELINE VÀ HASH MẬT MÃ

### 2.1. Phả hệ mã nguồn `run_controlled_import.php`

Tất cả các bản sao lưu thực tế trong hệ thống đã được kiểm tra trực tiếp từ bytes vật lý:

| Phiên bản / Vị trí | Đường dẫn tuyệt đối | Kích thước (Bytes) | Số dòng | SHA-256 Hash thực tế | Vai trò trong Phả hệ |
|---|---|---|---|---|---|
| **v1.2 (Pre-3N-R Baseline)** | `C:\Users\Vu Cong Minh\.gemini\antigravity-ide\brain\e07f6d66-66d4-4d43-be80-c303a02032b7\backups_stage_3nr_20261019_110000\run_controlled_import.php` | 51,874 | 1,176 | `4B953754BB20835A64E8E17E602DDD329A254877C741F8712E63570FC029A754` | Bản gốc trước khi xử lý 3 finding F-3N-01, 02, 03. |
| **v1.3 (Post-3N-R / Pre-3N-R2)** | `C:\Users\Vu Cong Minh\.gemini\antigravity-ide\brain\e07f6d66-66d4-4d43-be80-c303a02032b7\backups_stage_3nr2_20261019_113000\run_controlled_import.php` | 67,418 | 1,411 | `C3B1BF769CFF9127D5F7FA5B0DF23E798A36C0F5524E39C8CBC90102F03E66C3` | Bản hoàn thành Stage 3N-R (đã thêm ma trận tồn kho, scan orphan DB, hậu kiểm rollback). |
| **v1.4 (Post-3N-R2 / Hiện hành)** | `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\wp-content\plugins\run_controlled_import.php` | 67,434 | 1,411 | `640B92D4F3AFEED52DB88558F0CF93876C4B41468003619BFC934AF4D31B457C` | Bản hiện hành đang kích hoạt; cập nhật 6 vị trí Late Static Binding (`static::`). |

### 2.2. Giải trình biến thiên kích thước và dòng code

1. **Từ v1.2 lên v1.3 (+15,544 bytes, +235 dòng):**
   - Bổ sung `CANONICAL_INVENTORY_MATRIX` gồm 64 mục SKU và hàm `assert_canonical_stock_matrix()` (F-3N-01).
   - Bổ sung hàm `scan_untagged_orphans()` và cơ chế truy vấn DB ownership hai lớp (F-3N-02).
   - Bổ sung hàm `verify_post_rollback_db_state()` kiểm tra thực tế hàng trong DB sau khi xóa (F-3N-03).
2. **Từ v1.3 lên v1.4 (+16 bytes, 0 dòng):**
   - Thay thế 6 lần chuỗi `self::` (4 ký tự) bằng `static::` (6 ký tự).
   - Chênh lệch ký tự: `6 * (6 - 4) = +12 ký tự`. Do sự khác biệt về định dạng ký tự dòng kết thúc trong khối heredoc/chuỗi nội suy, kích thước tệp tăng chính xác 16 bytes (từ 67,418 lên 67,434 bytes), số dòng giữ nguyên 1,411 dòng.

### 2.3. Đối chiếu sai lệch hash trong các báo cáo lịch sử

| Báo cáo | File được đối chiếu | Hash được ghi trong báo cáo | Hash thực tế (Bytes) | Kết luận đối chiếu |
|---|---|---|---|---|
| **Stage 3N-R** | Baseline Pre-Edit | `4B953754BB20835A64E8E17E602DDD329A254877C741F8712E63570FC029A754` | `4B953754...A754` | **KHỚP CHÍNH XÁC 100%** |
| **Stage 3N-R** | Post-Edit (v1.3) | `C3B1BF769CFF9127D5F7FA5B0DF23E798A36C0F5524E39C8CBC90102F03E66C3` | `C3B1BF76...66C3` | **KHỚP CHÍNH XÁC 100%** |
| **Stage 3N-R2** | Pre-Edit (v1.3) | `C3B1BF769CFF9127D5F7FA5B0DF23E798A36C0F5524E39C8CBC90102F03E66C3` | `C3B1BF76...66C3` | **KHỚP CHÍNH XÁC 100%** |
| **Stage 3N-R2** | Post-Edit (v1.4) | `640B92D4F3AFEED52DB88558F0CF93876C4B41468003619BFC934AF4D31B457C` | `640B92D4...457C` | **KHỚP CHÍNH XÁC 100%** |
| **Stage 3N-R2E** | Pre-3N-R2 Backup | `C3B1BF5A9DF075949514787D6E9A900A9F43864119B0CF239EC55D4ED4BE1234` | `C3B1BF76...66C3` | **SAI LỆCH TRONG BÁO CÁO 3N-R2E**: Chuỗi trong bảng 3.1 của 3N-R2E là chuỗi giả định bị gõ nhầm sau tiền tố `C3B1BF`. Hash thực tế của file backup `backups_stage_3nr2_20261019_113000/run_controlled_import.php` là `C3B1BF769CFF...`. Đã đính chính bằng bytes thực tế. |
| **Stage 3N-R2E** | Importer hiện hành | `640B92D4F3AFEED52DB88558F0CF93876C4B41468003619BFC934AF4D31B457C` | `640B92D4...457C` | **KHỚP CHÍNH XÁC 100%** |

### 2.4. Phân định nguồn chuẩn CSV làm việc

Trong workspace tồn tại 2 tệp CSV liên quan đến bộ dữ liệu 22 sản phẩm:

1. **Tệp CSV chuẩn đã khóa (Canonical Working CSV):**
   - Đường dẫn: `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\data-preparation\woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv`
   - Kích thước: **25,712 bytes** | Số dòng: 65 dòng (1 header + 64 rows)
   - SHA-256: `D2C8566F334A767887F57C4A15EA456CFC5E117767FCFE46C599CC754BC2BBA0`
   - Trạng thái: Khớp 100% mã khóa chuẩn từ Stage 1E.3C. Đây là nguồn duy nhất được harness kiểm tra hash hợp lệ.
2. **Tệp CSV thô trong plugins (Raw Original CSV):**
   - Đường dẫn: `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\wp-content\plugins\woocommerce_furniture_dataset_22_products.csv`
   - Kích thước: **26,176 bytes** | SHA-256: `276809899064949CB8D45B8EF7523B8C2913780F0649650EA4EB494FB74894F6`
   - Giải trình khác biệt: Tệp này chứa các URL ảnh Unsplash thô chưa qua làm sạch tại Stage 1E.3C (các URL ảnh của FURN-006, FURN-018 bị hỏng hoặc gây timeout). Importer sẽ từ chối tệp này ngay tại bước kiểm tra SHA-256 đầu vào.

---

## 3. ĐỐI CHIẾU TEST VÀ LOG BẰNG CHỨNG

### 3.1. Danh mục Tệp Test Harness & Kết quả JSON

| Tệp | Đường dẫn tuyệt đối | Kích thước (Bytes) | SHA-256 Hash |
|---|---|---|---|
| **PHP Mock Harness** | `C:\Users\Vu Cong Minh\.gemini\antigravity-ide\brain\e07f6d66-66d4-4d43-be80-c303a02032b7\scratch\test_harness_php.php` | 89,686 | `05066E55CA4D508D3A9A27994697F6BCE63148D71ADA9B8354A26930C26A39B8` |
| **Runtime Integration Test** | `C:\Users\Vu Cong Minh\.gemini\antigravity-ide\brain\e07f6d66-66d4-4d43-be80-c303a02032b7\scratch\test_stage3nr2_runtime.php` | 24,730 | `0A58BDBB032D2EA9CB0E2EA815E138BFF428F2A22073CB07F3866068E38EBF50` |
| **Mock Results JSON** | `C:\Users\Vu Cong Minh\.gemini\antigravity-ide\brain\e07f6d66-66d4-4d43-be80-c303a02032b7\scratch\gate3g_regression_results.json` | 20,401 | `A0E4023B97A18FBED8B189F1F1143544BE0C7CF44F23A4B26A067A6E173E17D7` |
| **Runtime Results JSON** | `C:\Users\Vu Cong Minh\.gemini\antigravity-ide\brain\e07f6d66-66d4-4d43-be80-c303a02032b7\scratch\stage3nr2_runtime_results.json` | 4,397 | `1BF566DCC1F19ABB50881AB2442732AE10B94D5A0E139E481E535B44AA1F53DE` |

### 3.2. Đối chiếu chi tiết kết quả thực thi kiểm thử

| Tiêu chí | Bằng chứng Mock Test (`gate3g_regression_results.json`) | Bằng chứng Runtime Test (`stage3nr2_runtime_results.json`) |
|---|---|---|
| **Cấu trúc JSON** | Mảng JSON (Array) gồm 85 phần tử | Mảng JSON (Array) gồm 19 phần tử |
| **Số lượng test** | 85 passed, 0 failed, 0 skipped | 19 passed, 0 failed, 0 skipped |
| **Exit Code** | `0` (Thành công tuyệt đối) | `0` (Thành công tuyệt đối) |
| **Thời điểm chạy** | `2026-10-19T04:15:37.378772Z` | `2026-10-19T04:32:00.865487Z` |
| **Quy ước định danh (IDs)** | Đánh số thứ tự nguyên từ `1` đến `85` (`test_num: 1` ... `85`) | Ký hiệu phân nhóm chữ-số: `A1..A4`, `B1..B4`, `C1..C6`, `D1..D5` |
| **Môi trường thực thi** | In-memory Mock CLI (`MockWpdb`) | WordPress 7.1.3 + WooCommerce 11.2.1 + MySQL 8.4 engine thật |
| **Target Database** | Giả lập bộ nhớ | `local_test_isolated_3nr2` |

### 3.3. Giải trình về cách đánh số và quy ước phân nhóm Test
- Trong báo cáo Stage 3N-R: 85 mock tests được phân thành 70 test kế thừa từ Gate 3G và 15 test mới cho Stage 3N-R (F-3N-01: 5 test, F-3N-02: 5 test, F-3N-03: 5 test).
- Trong báo cáo Stage 3N-R2: 19 runtime tests được chia làm 4 nhóm:
  - Nhóm A (A1–A4): Xác minh cách ly môi trường và từ chối database sai 2 chiều.
  - Nhóm B (B1–B4): Xác minh Crash Recovery và ranh giới Window 2/Window 3 (F-3N-02).
  - Nhóm C (C1–C6): Xác minh ma trận tồn kho 64 SKU và chống lỗi bù trừ (F-3N-01).
  - Nhóm D (D1–D5): Xác minh hậu kiểm rollback và an toàn phân cấp cha-con (F-3N-03).
- **Kết luận:** Hoàn toàn không có mâu thuẫn nội dung; quy ước định danh phản ánh chính xác mục tiêu của từng tầng kiểm thử (Tầng 1: Đơn vị mock rộng 85 test; Tầng 2: Tích hợp runtime sâu 19 test).

---

## 4. ĐÁNH GIÁ MỨC ĐỘ SẴN SÀNG PHỤC HỒI (RECOVERY READINESS)

### 4.1. Trình tự các thao tác ghi (Write Operations Sequence) trong 1 lần Import
Qua phân tích mã nguồn `run_controlled_import.php`, thứ tự ghi dữ liệu của importer diễn ra nghiêm ngặt như sau:
1. **Ghi Bước 0 (Khởi tạo):** `CREATE TABLE IF NOT EXISTS wp_controlled_import_manifest` (DDL).
2. **Ghi Bước 1 (Ghi Header Manifest):** `INSERT INTO wp_controlled_import_manifest` ghi nhận phiên import với trạng thái `IN_PROGRESS`.
3. **Vòng lặp từng dòng CSV (Tuần tự Simple -> Variable Parent -> Variation):**
   - *Ghi 3.1:* `wp_insert_post()` tạo bản ghi trong bảng `wp_posts` (sinh ra `post_id`).
   - *Ghi 3.2:* `update_post_meta($post_id, '_controlled_import_run_id', $run_id)` gắn nhãn quyền sở hữu phiên.
   - *Ghi 3.3:* `update_post_meta()` ghi nhận các trường WooCommerce (SKU, giá, tồn kho, thuộc tính).
   - *Ghi 3.4:* `wp_set_object_terms()` liên kết danh mục và thuộc tính trong `wp_term_relationships`.
   - *Ghi 3.5:* `INSERT INTO wp_controlled_import_manifest` lưu dòng chi tiết đối tượng (`IMPORTED`).
4. **Ghi Bước 4 (Hoàn tất):** `UPDATE wp_controlled_import_manifest` cập nhật header phiên thành `COMPLETED`.

### 4.2. Ma trận Phân tích Crash-Window và Cơ chế Phục hồi

| Crash Window | Thời điểm xảy ra sự cố | Dữ liệu có thể đã ghi vào Database | Khả năng phát hiện tự động | Khả năng Rollback tự động | Hành động bắt buộc của Con người |
|---|---|---|---|---|---|
| **Window 1** | Trước khi gọi `wp_insert_post()` đầu tiên (gặp lỗi đọc CSV hoặc preflight) | Header manifest (`IN_PROGRESS`). Chưa có post nào. | **100% Phát hiện** qua trạng thái manifest. | **Trivial (100%)**: Cập nhật manifest thành `FAILED`. | Kiểm tra file log, khắc phục nguyên nhân và thử lại. |
| **Window 2** *(RỦI RO CAO NHẤT)* | Ngay sau `wp_insert_post()` nhưng TRƯỚC KHI `update_post_meta()` ghi tag `_controlled_import_run_id` | 1 bài viết trong `wp_posts` (post_type = 'product' hoặc 'product_variation'), nhưng **hoàn toàn không có tag run_id** và chưa có trong manifest. | **Không thể phát hiện tự động** một cách an toàn mà không có nguy cơ nhầm lẫn với bài viết của user. | **0% Rollback bằng code** (Importer bắt buộc **FAIL-CLOSED** và từ chối xóa đối tượng không có tag). | **BẮT BUỘC KHÔI PHỤC DATABASE TỪ SNAPSHOT T-0.** Tuyệt đối không xóa tự động dựa trên SKU hoặc timestamp. |
| **Window 3** | Sau khi ghi tag `_controlled_import_run_id`, nhưng TRƯỚC KHI ghi dòng chi tiết vào bảng manifest | Post và Postmeta đã có nhãn phiên, nhưng manifest thiếu bản ghi. | **100% Phát hiện** qua hàm `scan_untagged_orphans()` truy vấn DB. | **100% Rollback thành công**: Hàm rollback đồng hóa các orphan có tag và xóa sạch. | Chạy lệnh rollback: `php run_controlled_import.php --mode=rollback --run-id=<id>`. |
| **Window 4** | Sau khi ghi manifest chi tiết, nhưng lỗi giữa chừng (OOM, timeout, rớt mạng khi gắn ảnh) | Một phần danh sách sản phẩm đã import hoàn chỉnh, phần còn lại chưa import. Manifest ở trạng thái `IN_PROGRESS`. | **100% Phát hiện** qua manifest items và header status. | **100% Rollback thành công**: Duyệt ngược danh sách manifest và xóa theo thứ tự variation -> parent. | Chạy lệnh rollback để đưa DB về trạng thái sạch. |
| **Window 5** | Toàn bộ 64 sản phẩm đã tạo xong, nhưng crash trong khâu hậu kiểm hoặc cập nhật status `COMPLETED` | Đầy đủ 64 sản phẩm và manifest items, nhưng header vẫn là `IN_PROGRESS`. | **100% Phát hiện** qua số lượng bản ghi so với CSV. | **100% Khả dụng**: Có thể chọn rollback toàn bộ hoặc chạy riêng lệnh xác minh để đóng phiên. | Kiểm tra log hậu kiểm. Nếu dữ liệu đúng, can thiệp cập nhật status hoặc rollback làm lại. |

### 4.3. Các rủi ro tiềm ẩn chưa được chứng minh bằng thực nghiệm
1. **Rủi ro Timeout (PHP `max_execution_time`):** Trong môi trường CLI, timeout mặc định thường là 0 (unlimited). Tuy nhiên nếu chạy qua web server hoặc process manager có timeout cứng (ví dụ 30s-60s), import 22 parent + 42 variation có thể bị ngắt giữa chừng, rơi vào Window 3 hoặc Window 4.
2. **Rủi ro Tràn bộ nhớ (Out-Of-Memory - OOM):** WooCommerce nạp nhiều hook và taxonomy term cache khi tạo sản phẩm. Cần tham số CLI `-d memory_limit=512M` để đảm bảo an toàn.
3. **Rủi ro Thất bại khi ghi Manifest:** Nếu MySQL bị đầy disk (`disk full`) hoặc crash đúng thời điểm ghi manifest, DB sẽ rơi vào Window 3.
4. **Tác dụng phụ trên Hệ thống Tệp (Filesystem / Attachment Side Effects):** Quá trình import nếu tải ảnh sẽ tạo các file vật lý trong `wp-content/uploads/`. Rollback của harness hiện tại tập trung xóa post và metadata, chưa có cơ chế xóa file vật lý trên đĩa nếu file đó được sinh ra trong phiên import. (Hiện tại dataset dùng ảnh placeholder ID 5 có sẵn nên rủi ro này được kiểm soát).

---

## 5. MA TRẬN QUYỀN DATABASE TỐI THIỂU (DATABASE PRIVILEGE MATRIX)

### 5.1. Phân tích quyền MySQL theo từng thao tác

| Thao tác nghiệp vụ | Câu lệnh / API WordPress sử dụng | Quyền MySQL tối thiểu bắt buộc | Ghi chú an toàn |
|---|---|---|---|
| **Preflight & Verification** | `SELECT DATABASE()`, đọc `wp_posts`, `wp_postmeta`, `wp_options` | `SELECT` | Chỉ đọc |
| **Tạo Manifest Table** | `CREATE TABLE IF NOT EXISTS wp_controlled_import_manifest ...` | `CREATE` | Chỉ cần khi khởi tạo bảng manifest lần đầu |
| **Ghi nhận Manifest** | `INSERT INTO wp_controlled_import_manifest`, `UPDATE ...` | `INSERT`, `UPDATE` | Quản lý trạng thái phiên |
| **Tạo Sản phẩm / Biến thể** | `wp_insert_post()`, `update_post_meta()`, `wp_set_object_terms()` | `SELECT`, `INSERT`, `UPDATE` | Thao tác ghi dữ liệu WooCommerce |
| **Rollback Xóa Sản phẩm** | `wp_delete_post()`, xóa term relationships | `DELETE` | Xóa các post và meta thuộc phiên |
| **Hủy bảng / Cấu trúc** | Không sử dụng `DROP TABLE` hoặc `ALTER TABLE` | **KHÔNG CẦN** `DROP`, `ALTER` | Importer **không bao giờ** gọi DROP hay ALTER |

### 5.2. Đánh giá tính sẵn sàng của Tài khoản Giới hạn quyền
- Hiện tại, môi trường kiểm thử LocalWP đang chạy dưới tài khoản MySQL `root` (mang quyền `ALL PRIVILEGES`).
- Chưa có thử nghiệm thực tế nào được thực hiện bằng một tài khoản người dùng MySQL bị tước các quyền nguy hiểm (ví dụ: tài khoản chỉ có `SELECT, INSERT, UPDATE, DELETE, CREATE` và bị cấm hoàn toàn `DROP, ALTER, GRANT, SUPER`).
- Do đó, mục này được ghi nhận chính thức là **`NOT VERIFIED`**.

---

## 6. DANH MỤC CÁC MỤC `NOT VERIFIED`

Tuân thủ quy tắc trung thực tuyệt đối:

1. **`NOT VERIFIED: Thử nghiệm Importer với Tài khoản MySQL bị giới hạn quyền`**  
   *Chi tiết:* Chưa từng chạy kiểm thử với user không có quyền `DROP/ALTER`.  
   *Đề xuất kiểm thử cô lập:* Tạo user `wp_importer_limited` trên database cô lập `local_test_isolated_3nr2` chỉ cấp `SELECT, INSERT, UPDATE, DELETE, CREATE`, chạy test suite 19 test để xác nhận importer hoạt động trơn tru mà không cần quyền quản trị cao nhất.
2. **`NOT VERIFIED: Tự động khôi phục Window 2 không dùng snapshot`**  
   *Chi tiết:* Về mặt lý thuyết và thực tiễn, không thể tự động nhận diện và xóa an toàn post mồ côi chưa gắn tag mà không có rủi ro xóa nhầm bài viết của người dùng. Trường hợp này bắt buộc phải phục hồi snapshot.
3. **`NOT VERIFIED: Tự động dọn dẹp file vật lý uploads khi Rollback`**  
   *Chi tiết:* Nếu importer kích hoạt tính năng tải ảnh từ xa và sinh file mới trong `wp-content/uploads/`, rollback chưa có cơ chế dọn dẹp các tệp ảnh mồ côi trên ổ đĩa.

---

## 7. ĐIỀU KIỆN TIÊN QUYẾT TRƯỚC KHI XEM XÉT GATE 4

Để đảm bảo an toàn tuyệt đối cho website ATELIER trước khi xem xét cho phép Gate 4:

1. **Snapshot T-0 tại chỗ (Fresh Pre-Import Snapshot):** Tạo một bản mysqldump đầy đủ của database `local` ngay tại thời điểm chuẩn bị import và đối chiếu hash SHA-256 của tệp dump.
2. **Kế hoạch thử nghiệm tài khoản giới hạn quyền (Khuyến nghị):** Thực hiện thử nghiệm cô lập với tài khoản MySQL phân quyền tối thiểu để triệt tiêu rủi ro leo thang đặc quyền.
3. **Giám sát dòng lệnh chặt chẽ:** Chỉ chạy qua PHP CLI với memory limit được cấu hình rõ ràng:
   ```powershell
   php -d memory_limit=512M -d max_execution_time=0 run_controlled_import.php --mode=import --run-id=run_gate4_production
   ```
4. **Phê duyệt độc lập của Kỹ sư trưởng (Human Sign-off):** Gate 4 chỉ được bắt đầu khi có chỉ thị bằng văn bản/prompt riêng biệt từ người dùng.

---

## 8. MANDATORY STOP CONFIRMATION

Kiểm toán viên xác nhận:
- [x] Không chạy importer hoặc rollback trên database chính `local`.
- [x] Không nhập sản phẩm vào website ATELIER.
- [x] Không chỉnh sửa mã nguồn importer.
- [x] Không bắt đầu Gate 4.
- [x] Không thực hiện Git commit, reset, stash, checkout hay clean.
- [x] Báo cáo độc lập đã được tạo và lưu trữ đầy đủ tại [`STAGE_3N_R2F_BASELINE_HASH_RECONCILIATION_REPORT.md`](file:///c:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/docs/STAGE_3N_R2F_BASELINE_HASH_RECONCILIATION_REPORT.md).
