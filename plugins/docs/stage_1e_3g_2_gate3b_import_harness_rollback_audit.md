# STAGE 1E.3G.2 — GATE 3B: IMPORT HARNESS & ROLLBACK READINESS AUDIT

**Execution Timestamp:** 2026-10-18 19:05:00 (UTC+7)  
**Project:** `furniture-ecommerce`  
**Stage:** Stage 1E.3G.2 — Gate 3B: Import Harness & Rollback Readiness Audit  
**Evaluator:** Senior WooCommerce Data Migration Engineer, WordPress Database Auditor, and QA Engineer  
**Stage Conclusion:** **CONDITIONAL PASS — HARNESS SPECIFICATION & ROLLBACK LOGIC VERIFIED (PRODUCTION SCRIPT PENDING IMPLEMENTATION)**

---

## 1. QUYỀN HẠN VÀ PHẠM VI AUDIT (AUTHORITY & AUDIT SCOPE)

Cuộc kiểm tra kỹ thuật này được thực hiện tuân thủ nghiêm ngặt ranh giới an toàn **chỉ đọc (strict read-only)**:
- **KHÔNG** thực hiện import bất kỳ dòng dữ liệu nào vào WooCommerce.
- **KHÔNG** tạo, sửa đổi hoặc xóa bất kỳ bài viết, sản phẩm, biến thể, attachment hay metadata nào trên database thật.
- **KHÔNG** chạy lệnh SQL có tính chất ghi (INSERT/UPDATE/DELETE/ALTER).
- **KHÔNG** tạo mới, khôi phục hoặc ghi đè bất kỳ bản sao lưu (snapshot) nào.
- **KHÔNG** chạy thử rollback trên database thật của website.
- **KHÔNG** sửa đổi CSV, theme, plugin hoặc cấu hình hệ thống.
- **KHÔNG** cài đặt plugin hoặc dependency mới.
- Toàn bộ các thử nghiệm kiểm thử lỗi và rollback được thực thi trong môi trường **mock/stub cô lập 100% (isolated in-memory test harness)**, hoàn toàn không kết nối hay tác động đến cơ sở dữ liệu MySQL thật của dự án.

---

## 2. FILE VÀ MÃ NGUỒN ĐÃ KIỂM TRA (INSPECTED FILES & CODEBASE)

1. **Working CSV:**
   - Đường dẫn: `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\data-preparation\woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv`
   - Kích thước: `25,712` bytes
   - SHA-256: `D2C8566F334A767887F57C4A15EA456CFC5E117767FCFE46C599CC754BC2BBA0`
2. **Original Source CSV:**
   - Đường dẫn: `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\wp-content\plugins\woocommerce_furniture_dataset_22_products.csv`
   - Kích thước: `26,176` bytes
   - SHA-256: `276809899064949CB8D45B8EF7523B8C2913780F0649650EA4EB494FB74894F6`
3. **Gate 2 Snapshot:**
   - Đường dẫn: `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\backups\stage_1e_gate2_pre_import_20261016_223502.sql`
   - Kích thước: `1,236,031` bytes
   - SHA-256: `9152E46BB462C321EDD7778A6DA7889450A90301BD1627682D10345A5BDCC8A3`
4. **Mã nguồn WooCommerce Core Importer (WooCommerce 11.2.1):**
   - `wp-content/plugins/woocommerce/includes/admin/importers/class-wc-product-csv-importer-controller.php`
   - `wp-content/plugins/woocommerce/includes/import/class-wc-product-csv-importer.php`
   - `wp-content/plugins/woocommerce/includes/import/abstract-wc-product-importer.php`
5. **Cơ sở dữ liệu trực tiếp:**
   - Endpoint: `127.0.0.1:10011`, Database: `local`, Prefix: `wp_`.

---

## 3. DANH SÁCH TỪNG SAFEGUARD (SAFEGUARD AUDIT MATRIX)

| # | Cơ chế an toàn (Safeguard) | Yêu cầu kỹ thuật | Trạng thái dự án | Đánh giá & Bằng chứng |
| :--- | :--- | :--- | :--- | :--- |
| **S1** | **CSV Path & Hash Preflight** | Kiểm tra đường dẫn và khớp mã SHA-256 trước khi khởi chạy | **VERIFIED (IN MODEL)** | Đã kiểm thử độc lập (Test 01). Script từ chối ngay lập tức nếu hash sai khác dù chỉ 1 bit. |
| **S2** | **Target Database Identity Check** | Xác thực tên database `local` và endpoint trước khi kết nối | **VERIFIED (IN MODEL)** | Mô hình kiểm tra đối số `target_db_name == 'local'` trước khi thực hiện bất kỳ thao tác nào. |
| **S3** | **Enforce `update_existing = false`** | Không cho phép cập nhật đè sản phẩm đã có; ngăn ngừa ghi đè dữ liệu | **VERIFIED (IN CORE & MODEL)** | Xác minh trong mã nguồn WC Core (`class-wc-product-csv-importer.php` dòng 72, 1432). |
| **S4** | **Duplicate SKU Pre-detection** | Quét trùng SKU trước khi xử lý từng dòng | **VERIFIED (IN MODEL)** | Đã kiểm thử độc lập (Test 02). Nếu SKU đã tồn tại, lập tức dừng và ghi lỗi. |
| **S5** | **Line-by-Line Result Verification** | Kiểm tra kết quả chi tiết từng dòng, không chỉ dựa vào exit code | **VERIFIED (IN MODEL)** | Mỗi dòng được đối chiếu kết quả trả về, ghi nhận vào manifest chi tiết. |
| **S6** | **Strict Handling of Skipped / Failed Rows** | Coi dòng bị skip hoặc fail là hard failure, không âm thầm bỏ qua | **VERIFIED (IN MODEL)** | Đã kiểm thử độc lập (Test 03, 04). Bắt buộc dừng và kích hoạt cờ rollback. |
| **S7** | **Timeout & Memory Limits** | Cấu hình giới hạn thời gian (time limit) và bộ nhớ (memory limit) hợp lý | **VERIFIED (IN WC CORE)** | WooCommerce core importer tích hợp sẵn `prevent_timeouts` (dòng 74) và `wc_set_time_limit(0)`. |
| **S8** | **Distinguish Rollback-Safe Errors** | Phân loại lỗi có thể rollback an toàn vs lỗi không xác định trạng thái | **VERIFIED (IN MODEL)** | Trạng thái manifest phân biệt `INITIALIZED`, `IN_PROGRESS`, `FAILED`, `ROLLED_BACK`. |
| **S9** | **Production Harness Script in Repo** | File script thực thi import chính thức đã được commit vào codebase | **NOT IMPLEMENTED** | Chưa có file script import tùy biến trong `wp-content/plugins/`. *(Đang ở giai đoạn thiết kế mô hình & test harness)*. |
| **S10**| **Incremental Manifest Logging** | Ghi nhận từng đối tượng ngay sau khi lưu thành công | **VERIFIED (IN MODEL)** | Ghi log từng ID sản phẩm/biến thể/attachment ngay khi lưu; đã kiểm thử (Test 05, 06, 08). |
| **S11**| **Atomic Manifest Flush** | Cơ chế ghi file nguyên tử (atomic write qua file tạm + fsync) chống ghi dở | **VERIFIED (IN MODEL)** | Đã kiểm thử độc lập (Test 07). Dùng file tạm `.tmp` và `os.replace` ngăn ngừa file hỏng. |
| **S12**| **Pre-existing Attachment Protection** | Nhận diện và khóa các attachment có sẵn (như ID 5) khỏi rollback | **VERIFIED (IN MODEL)** | Đã kiểm thử độc lập (Test 11). Snapshot danh sách attachment trước import, rollback không xóa ID 5. |
| **S13**| **Real-time Attachment Interception** | Bắt attachment ID ngay khi WordPress tạo (hook `add_attachment`) | **VERIFIED (IN MODEL)** | Bắt attachment ID trước khi lưu sản phẩm, giải quyết triệt để rủi ro crash giữa chừng (Test 06). |
| **S14**| **Strict Rollback Order** | Thứ tự xóa: Variations → Parent Products → Attachments mới | **VERIFIED (IN MODEL)** | Đã kiểm thử độc lập (Test 05, 08, 10). Xóa biến thể trước để tránh orphan post. |
| **S15**| **Idempotent Rollback** | Cho phép gọi rollback nhiều lần mà không sinh lỗi | **VERIFIED (IN MODEL)** | Đã kiểm thử độc lập (Test 09). Lần gọi thứ 2 an toàn xác nhận 0 đối tượng còn sót. |
| **S16**| **Rollback Resume Capability** | Khả năng tiếp tục rollback nếu tiến trình rollback bị gián đoạn | **VERIFIED (IN MODEL)** | Đã kiểm thử độc lập (Test 10). Tự động xóa tiếp các đối tượng còn lại. |

---

## 4. KIỂM THỬ ĐỘC LẬP (INDEPENDENT UNIT TEST SUITE)

Để chứng minh tính an toàn kỹ thuật mà **hoàn toàn không ghi vào cơ sở dữ liệu thật**, một bộ kiểm thử cô lập sử dụng mock database và filesystem giả lập đã được xây dựng và thực thi tại `scratch/test_import_harness_isolated.py`.

### 4.1. Lệnh thực thi kiểm thử
```powershell
python "C:\Users\Vu Cong Minh\.gemini\antigravity-ide\brain\e07f6d66-66d4-4d43-be80-c303a02032b7\scratch\test_import_harness_isolated.py"
```

### 4.2. Bằng chứng log kết quả kiểm thử (12 Test Cases)
```text
[PASS] Test 01: CSV hash mismatch -> Rejected corrupted hash: CSV hash mismatch! Expected D2C8566F334A767887F57C4A15EA456CFC5E117767FCFE46C599CC754BC2BBA0, got 092EBA9571B1F7D930E5F87AE48C3239033E53738F71A6DE3ECC959BC299CF49
[PASS] Test 02: SKU already exists -> Detected duplicate SKU: Duplicate SKU error: FURN-001
[PASS] Test 03: Importer returns skipped row -> Treated skipped row as hard stop: Importer skipped row for SKU: FURN-002
[PASS] Test 04: Importer returns failed row -> Treated failed row as hard stop: Importer failed row for SKU: FURN-003
[PASS] Test 05: Error after parent creation before variations -> Rollback cleaned parent, DB posts: 0
[PASS] Test 06: Error after attachment before product save -> Attachment intercepted and rolled back: [1]
[PASS] Test 07: Manifest corrupted/unreadable -> Detected corrupt manifest: Manifest corrupted or unreadable: Expecting property name enclosed in double quotes: line 1 column 2 (char 1)
[PASS] Test 08: Process interrupted recovery -> Recovered from disk manifest and deleted [1, 2]
[PASS] Test 09: Rollback called twice (Idempotency) -> Second rollback safely succeeded with 0 extra deletions
[PASS] Test 10: Rollback fails halfway resume -> Finished rollback on resumption without corruption
[PASS] Test 11: Pre-existing attachment protection -> Placeholder ID 5 preserved in DB: True
[PASS] Test 12: Invalid manifest path rejected -> Rejected invalid path: Invalid manifest directory: 'C:\Users\VUCONG~1\AppData\Local\Temp\harness_test_q5sirhdl\non_existent_subdir'
```

### 4.3. Phân tích kết quả kiểm thử then chốt

1. **Test 06 (Rủi ro Attachment Treo):**
   - *Vấn đề:* WooCommerce tải ảnh và tạo attachment post trong `wp_posts` trước khi lưu sản phẩm cha/con. Nếu tiến trình chết ngay sau khi tạo attachment, đối tượng này có thể bị sót.
   - *Giải pháp kiểm chứng:* Bằng việc hook trực tiếp vào sự kiện tạo attachment (`add_attachment`), harness ghi nhận attachment ID vào manifest ngay lập tức. Khi tiến trình gặp lỗi, rollback đã xóa thành công attachment `[1]` mà không để lại rác.
2. **Test 11 (Bảo vệ Attachment Có Sẵn):**
   - *Vấn đề:* Tránh trường hợp rollback xóa nhầm ảnh placeholder đã có từ trước (ID: 5).
   - *Giải pháp kiểm chứng:* Trước khi import, harness snapshot toàn bộ attachment hiện hữu vào tập `pre_existing_attachments`. Khi rollback thực thi, ID 5 được giữ nguyên vẹn 100%.
3. **Test 09 & 10 (Tính Idempotent và Phục hồi Rollback):**
   - Rollback có thể gọi lặp lại nhiều lần an toàn mà không sinh ngoại lệ. Nếu rollback bị crash ở giai đoạn xóa biến thể, lần gọi tiếp theo sẽ tiếp tục xóa sản phẩm cha mà không bị mất dấu.

---

## 5. SNAPSHOT VÀ DISASTER RECOVERY

- **Gate 2 Snapshot:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\backups\stage_1e_gate2_pre_import_20261016_223502.sql`
- **Kích thước:** `1,236,031` bytes | **SHA-256:** `9152E46BB462C321EDD7778A6DA7889450A90301BD1627682D10345A5BDCC8A3`
- **Cảnh báo bắt buộc về Disaster Recovery:**  
  Bản snapshot Gate 2 hiện là hiện vật bảo toàn trạng thái trước import, **chưa từng được kiểm thử phục hồi thực tế (isolated restore test)** do việc này nằm ngoài phạm vi được phê duyệt. **Không được tuyên bố cơ chế disaster recovery đã được kiểm chứng khôi phục thành công.**

---

## 6. RỦI RO CÒN LẠI VÀ BIỆN PHÁP KHẮC PHỤC (RESIDUAL RISKS & MITIGATIONS)

| Rủi ro còn lại | Mức độ | Biện pháp khắc phục bắt buộc khi triển khai Gate 4 |
| :--- | :--- | :--- |
| **1. Chưa có file script production trong repo** | Trung bình | Khi người dùng phê duyệt Gate 4, viết script `run_controlled_import.php` tích hợp chính xác cấu trúc `ImportHarnessModel` đã test. |
| **2. Gián đoạn kết nối mạng khi tải ảnh Unsplash** | Trung bình | Dù các URL lỗi đã được loại bỏ ở Stage 1E.3C, mạng ngoài vẫn có thể timeout. Cần đặt timeout cURL rõ ràng (15s) và kích hoạt rollback tự động nếu tải ảnh thất bại. |
| **3. Bộ nhớ PHP CLI vượt ngưỡng** | Thấp | Cấu hình `ini_set('memory_limit', '512M')` trong script thực thi import. |

---

## 7. KẾT LUẬN ACCEPTANCE CRITERIA

- [x] File working CSV, original CSV và Gate 2 snapshot khớp mã hash đã khóa.
- [x] Database thật được kiểm tra chỉ đọc và giữ nguyên trạng thái 0 products, 0 variations, 0 SKUs.
- [x] Cơ chế theo dõi attachment và manifest chống lỗi ghi dở đã được chứng minh qua unit test.
- [x] Đã thực thi đầy đủ 12 kịch bản kiểm thử độc lập với log thực tế.
- [x] Giới hạn của snapshot Gate 2 được nêu rõ (chưa restore test).
- [!] Script production trong repo ghi nhận trạng thái: **NOT IMPLEMENTED** (Đã có mô hình chuẩn bị sẵn sàng triển khai).

### **KẾT LUẬN CHUNG:** **CONDITIONAL PASS — READY FOR PRODUCTION SCRIPT DEPLOYMENT**

Thiết kế an toàn, cơ chế ghi nhận manifest nguyên tử, bẫy lỗi và quy trình rollback đã được chứng minh an toàn qua kiểm thử độc lập. Hệ thống sẵn sàng để chuyển sang viết và thực thi script import có kiểm soát khi có lệnh phê duyệt rõ ràng từ người dùng.

---

## 8. MANDATORY STOP

Giai đoạn Stage 1E.3G.2 Gate 3B kết thúc tại đây.

**TUYỆT ĐỐI TUÂN THỦ:**
- **Không import CSV.**
- **Không tạo sản phẩm hoặc biến thể.**
- **Không ghi/sửa/xóa database.**
- **Không restore snapshot.**
- **Không tự động chuyển sang Gate 4.**

Dừng toàn bộ hoạt động và chờ người dùng xem xét, phê duyệt riêng.
