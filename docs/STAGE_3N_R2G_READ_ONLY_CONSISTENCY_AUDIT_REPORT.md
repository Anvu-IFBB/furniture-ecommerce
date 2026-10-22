# STAGE 3N-R2G — READ-ONLY CONSISTENCY AUDIT & GATE 4 SAFETY REVIEW REPORT

**Dự án:** Furniture E-commerce (Site ATELIER)  
**Môi trường:** Local WordPress 7.1.3 / WooCommerce 11.2.1 / PHP 8.2.29 / MySQL 8.4.0  
**Giai đoạn:** Stage 3N-R2G — Kiểm toán Tính nhất quán Chỉ đọc & Đánh giá An toàn Gate 4  
**Thời điểm thực hiện:** 2026-10-19  
**Vai trò:** Senior WordPress/WooCommerce Engineer, PHP Security Reviewer, Database Reliability Engineer & Independent QA Auditor  
**Chế độ thực thi:** STRICT READ-ONLY AUDIT (Không ghi/xóa database, không sửa mã nguồn, không chạy Gate 4)  
**Tài liệu tham chiếu:** `STAGE_3N_R_IMPORT_RECOVERY_AND_INTEGRITY_REPORT.md`, `STAGE_3N_R2_ISOLATED_INTEGRATION_VERIFICATION_REPORT.md`, `STAGE_3N_R2E_EVIDENCE_RECONCILIATION_REPORT.md`, `STAGE_3N_R2F_BASELINE_HASH_RECONCILIATION_REPORT.md`

---

## 1. EXECUTIVE VERDICT

### KẾT LUẬN KIỂM TOÁN: **CONDITIONAL PASS — FINDINGS OPEN** (Cho Phạm vi Kiểm toán Chỉ đọc)
### QUYẾT ĐỊNH CỔNG 4: **MANDATORY STOP — GATE 4 TUYỆT ĐỐI CHƯA ĐƯỢC PHÉP CHẠY**

Toàn bộ 4 vấn đề kỹ thuật chưa đóng (F-1, F-2, F-3, F-4) đã được kiểm tra độc lập và đối chiếu tường minh ở mức byte, mã nguồn, AST và cơ sở dữ liệu:

1. **F-1 (Byte-Level Importer):** Đã giải mã dứt điểm chênh lệch kích thước giữa v1.3 và v1.4 (+16 bytes). Có chính xác **8 lần thay thế `self::` thành `static::` trên 8 dòng code riêng biệt** (mỗi thay thế làm tăng 2 bytes: `8 * 2 = 16 bytes`), bác bỏ con số ước lượng "6 lần" trong các báo cáo trước.
2. **F-2 (Recovery Limits across Crash Scenarios):** Đã xóa bỏ khẳng định cảm tính "100% phục hồi" trong các tình huống crash. Chứng minh rằng trong các kịch bản OOM, hard timeout, rớt kết nối MySQL hoặc crash tại Window 2 (post đã tạo nhưng chưa có tag), rollback bằng mã nguồn **không thể tự động dọn dẹp**. Ranh giới an toàn Window 2 là **FAIL-CLOSED tuyệt đối** và phương án phục hồi bắt buộc duy nhất là **khôi phục snapshot database**.
3. **F-3 (Ownership Tag vs Manifest):** Đính chính sai lệch thuật ngữ lịch sử: Mã nguồn hoàn toàn không có hàm nào tên `scan_untagged_orphans()`. Phương thức thực tế là `find_posts_by_ownership_tag( $run_id )`, chỉ tìm các đối tượng **ĐÃ CÓ TAG NHƯNG CHƯA GHI MANIFEST** (Window 3). Đối tượng thực sự "untagged" (Window 2) được code bảo vệ fail-closed, tuyệt đối không bị xóa.
4. **F-4 (Minimum MySQL Privileges):** Đính chính sai sót trong báo cáo R2F: Importer lưu trữ manifest dưới dạng **tệp JSON trên ổ đĩa**, hoàn toàn không tạo bảng `wp_controlled_import_manifest` trong MySQL. Do đó, importer **không cần quyền `CREATE` hay bất kỳ quyền DDL nào (`DROP`, `ALTER`, `TRUNCATE`)**. Mục kiểm thử tài khoản giới hạn quyền được ghi nhận là **`NOT VERIFIED`** và kèm theo kế hoạch kiểm thử đề xuất.

---

## 2. PHẠM VI VÀ GIỚI HẠN KIỂM TOÁN (SCOPE & LIMITATIONS)

### 2.1. Phân loại mức độ bằng chứng
- **Đã xác minh bằng thao tác trực tiếp (Directly Verified):** Byte-level diff của các file importer; hash SHA-256 tính từ bytes vật lý; cấu trúc và logic mã nguồn PHP; nội dung các tệp log JSON; trạng thái database `local` qua truy vấn `SELECT`.
- **Được báo cáo trước đó nhưng chưa xác minh lại (Reported Previously):** Quá trình chạy test suite gốc trong Stage 3N-R/R2 (đã đọc log kết quả, không chạy lại để đảm bảo an toàn).
- **Suy luận kỹ thuật (Technical Deduction):** Phân tích hành vi engine PHP khi gặp fatal error/OOM/timeout; phân tích cơ chế rollback InnoDB đối với các hàm WordPress Core không dùng nested transactions.
- **Chưa thể xác minh (NOT VERIFIED):** Thực thi importer bằng tài khoản MySQL bị tước quyền `DROP`/`ALTER` (môi trường LocalWP chỉ có user `root`); khả năng tự động khôi phục Window 2 không dùng snapshot (về mặt kỹ thuật là bất khả thi).

---

## 3. EVIDENCE INVENTORY

| Tệp / Thành phần | Đường dẫn tuyệt đối | Kích thước (Bytes) | Số dòng | SHA-256 Hash thực tế | Trạng thái xác minh |
|---|---|---|---|---|---|
| **Importer v1.2 (Pre-3N-R)** | `C:\Users\Vu Cong Minh\.gemini\antigravity-ide\brain\e07f6d66-66d4-4d43-be80-c303a02032b7\backups_stage_3nr_20261019_110000\run_controlled_import.php` | 51,874 | 1,176 | `4B953754BB20835A64E8E17E602DDD329A254877C741F8712E63570FC029A754` | **Directly Verified** |
| **Importer v1.3 (Post-3N-R)** | `C:\Users\Vu Cong Minh\.gemini\antigravity-ide\brain\e07f6d66-66d4-4d43-be80-c303a02032b7\backups_stage_3nr2_20261019_113000\run_controlled_import.php` | 67,418 | 1,411 | `C3B1BF769CFF9127D5F7FA5B0DF23E798A36C0F5524E39C8CBC90102F03E66C3` | **Directly Verified** |
| **Importer v1.4 (Hiện hành)** | `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\wp-content\plugins\run_controlled_import.php` | 67,434 | 1,411 | `640B92D4F3AFEED52DB88558F0CF93876C4B41468003619BFC934AF4D31B457C` | **Directly Verified** |
| **Working CSV Chuẩn** | `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\data-preparation\woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv` | 25,712 | 65 | `D2C8566F334A767887F57C4A15EA456CFC5E117767FCFE46C599CC754BC2BBA0` | **Directly Verified** |
| **CSV Thô (Plugins)** | `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\wp-content\plugins\woocommerce_furniture_dataset_22_products.csv` | 26,176 | 65 | `276809899064949CB8D45B8EF7523B8C2913780F0649650EA4EB494FB74894F6` | **Directly Verified** (Từ chối tại preflight) |
| **PHP Mock Harness** | `C:\Users\Vu Cong Minh\.gemini\antigravity-ide\brain\e07f6d66-66d4-4d43-be80-c303a02032b7\scratch\test_harness_php.php` | 89,686 | 1,451 | `05066E55CA4D508D3A9A27994697F6BCE63148D71ADA9B8354A26930C26A39B8` | **Directly Verified** |
| **Runtime Integration Test** | `C:\Users\Vu Cong Minh\.gemini\antigravity-ide\brain\e07f6d66-66d4-4d43-be80-c303a02032b7\scratch\test_stage3nr2_runtime.php` | 24,730 | 465 | `0A58BDBB032D2EA9CB0E2EA815E138BFF428F2A22073CB07F3866068E38EBF50` | **Directly Verified** |
| **Mock Results JSON** | `C:\Users\Vu Cong Minh\.gemini\antigravity-ide\brain\e07f6d66-66d4-4d43-be80-c303a02032b7\scratch\gate3g_regression_results.json` | 20,401 | 512 | `A0E4023B97A18FBED8B189F1F1143544BE0C7CF44F23A4B26A067A6E173E17D7` | **Directly Verified** (85 tests) |
| **Runtime Results JSON** | `C:\Users\Vu Cong Minh\.gemini\antigravity-ide\brain\e07f6d66-66d4-4d43-be80-c303a02032b7\scratch\stage3nr2_runtime_results.json` | 4,397 | 135 | `1BF566DCC1F19ABB50881AB2442732AE10B94D5A0E139E481E535B44AA1F53DE` | **Directly Verified** (19 tests) |

---

## 4. F-1 — ĐỐI CHIẾU BYTE-LEVEL IMPORTER

### 4.1. Giải trình chính xác 16 Bytes chênh lệch giữa v1.3 và v1.4
Trong các báo cáo Stage 3N-R2 và 3N-R2F, nguyên nhân tăng kích thước từ 67,418 lên 67,434 bytes được ghi nhận là: "Thay thế 6 lần self:: thành static::, tăng 12 bytes và 4 bytes do ký tự dòng".

**Kiểm toán thực tế ở mức byte nhị phân đã chứng minh kết luận trên là thiếu chính xác về số lần:**
So sánh chi tiết nhị phân giữa tệp `backups_stage_3nr2_20261019_113000/run_controlled_import.php` (v1.3) và `run_controlled_import.php` hiện hành (v1.4) cho thấy có **đúng 8 dòng khác biệt**, mỗi dòng có đúng 1 từ `self` (4 bytes) được đổi thành `static` (6 bytes), làm tăng đúng 2 bytes trên mỗi dòng:

1. **Dòng 367 (+2 bytes):**
   - v1.3: `if ( $db_name !== self::EXPECTED_DB_NAME ) {\r\n`
   - v1.4: `if ( $db_name !== static::EXPECTED_DB_NAME ) {\r\n`
2. **Dòng 368 (+2 bytes):**
   - v1.3: `throw new RuntimeException( "... Expected '" . self::EXPECTED_DB_NAME . ...\r\n`
   - v1.4: `throw new RuntimeException( "... Expected '" . static::EXPECTED_DB_NAME . ...\r\n`
3. **Dòng 372 (+2 bytes):**
   - v1.3: `if ( $prefix !== self::EXPECTED_TABLE_PREFIX ) {\r\n`
   - v1.4: `if ( $prefix !== static::EXPECTED_TABLE_PREFIX ) {\r\n`
4. **Dòng 373 (+2 bytes):**
   - v1.3: `throw new RuntimeException( "... Expected '" . self::EXPECTED_TABLE_PREFIX . ...\r\n`
   - v1.4: `throw new RuntimeException( "... Expected '" . static::EXPECTED_TABLE_PREFIX . ...\r\n`
5. **Dòng 462 (+2 bytes):**
   - v1.3: `'target_db' => self::EXPECTED_DB_NAME,\r\n`
   - v1.4: `'target_db' => static::EXPECTED_DB_NAME,\r\n`
6. **Dòng 463 (+2 bytes):**
   - v1.3: `'table_prefix' => self::EXPECTED_TABLE_PREFIX,\r\n`
   - v1.4: `'table_prefix' => static::EXPECTED_TABLE_PREFIX,\r\n`
7. **Dòng 1088 (+2 bytes):**
   - v1.3: `$expected_db = self::EXPECTED_DB_NAME;\r\n`
   - v1.4: `$expected_db = static::EXPECTED_DB_NAME;\r\n`
8. **Dòng 1101 (+2 bytes):**
   - v1.3: `$expected_prefix = self::EXPECTED_TABLE_PREFIX;\r\n`
   - v1.4: `$expected_prefix = static::EXPECTED_TABLE_PREFIX;\r\n`

$$\text{Tổng chênh lệch} = 8 \text{ dòng} \times (+2 \text{ bytes/dòng}) = \mathbf{+16 \text{ bytes}}$$

- **Định dạng kết thúc dòng:** Cả hai file đều sử dụng thuần túy `CRLF` (`\r\n`) trên tất cả 1,411 dòng. Không có thay đổi BOM, không có khoảng trắng thừa cuối dòng, không có thay đổi encoding.
- **Lý do báo cáo trước ghi nhầm "6 lần":** Người đánh giá trước đã đếm theo 6 khối diff logic (gộp dòng 367-368 thành 1 khối, dòng 372-373 thành 1 khối) thay vì đếm 8 lần xuất hiện thực tế của token.

### 4.2. So sánh v1.2 với v1.3 (+15,544 bytes, +235 dòng)
So sánh giữa `backups_stage_3nr_20261019_110000/run_controlled_import.php` (v1.2, 51,874 bytes, 1,176 dòng) và v1.3 (67,418 bytes, 1,411 dòng):
1. **Dòng 86-153 (+67 dòng):** Khai báo hằng số `public const CANONICAL_INVENTORY_MATRIX = [...]` gồm 64 mục SKU.
2. **Dòng 183-228 (+45 dòng):** Bổ sung hàm `assert_canonical_stock_matrix()` và logic đối chiếu ma trận.
3. **Dòng 671-816 (+60 dòng):** Bổ sung logic kiểm tra đối chiếu tồn kho chi tiết từng dòng trong `run_import()` (F-3N-01).
4. **Dòng 1143-1186 (+43 dòng):** Bổ sung cơ chế phát hiện đối tượng đã có tag nhưng chưa vào manifest `find_posts_by_ownership_tag()` và đồng hóa vào danh sách xóa (F-3N-02).
5. **Dòng 1244-1295 (+51 dòng):** Bổ sung closure `$delete_and_verify()` hậu kiểm trực tiếp trạng thái database sau khi xóa và bảo vệ phân cấp cha-con (F-3N-03).

---

## 5. F-2 — ĐÁNH GIÁ KHẢ NĂNG PHỤC HỒI KHI CÓ SỰ CỐ (RECOVERY MATRIX)

Bác bỏ hoàn toàn cách diễn đạt "100% phục hồi". Khả năng phục hồi phụ thuộc vào bản chất của điểm crash:

| # | Kịch bản sự cố | Dữ liệu có thể đã ghi vào DB | Khả năng phát hiện từ Manifest | Có Tag Run ID trên Post | Cơ chế Rollback tự động | Khả năng xác minh hậu kiểm | Phương án phục hồi an toàn | Bằng chứng kiểm thử thực tế |
|---|---|---|---|---|---|---|---|---|
| **1** | Lỗi PHP chuẩn (bắt bằng `Throwable`) | Một số post/meta đã tạo; manifest có thể ở trạng thái `IN_PROGRESS` | Có (các post đã tạo có trong manifest) | Có | **Có thể chạy in-process**: Khối `catch (Throwable)` tự gọi `rollback()`. | Có (truy vấn DB xác nhận vắng mặt) | Tự động hoàn trả; nếu lỗi xóa thì manifest chuyển `FAILED_ROLLBACK_INCOMPLETE`. | Unit Tests 71-85; Runtime Test D1, D2. |
| **2** | Timeout tiến trình (CLI max_execution_time / SIGKILL) | Post/meta dở dang; manifest trên đĩa có thể thiếu post mới nhất | Chỉ phát hiện các post trước đó; post đang tạo bị thiếu | Có thể có hoặc không (tùy thời điểm) | **Không chạy được in-process**. Phải gọi lệnh CLI rollback thủ công bằng tay. | Có khi chạy CLI rollback | Chạy `php run_controlled_import.php rollback <manifest>`. Nếu dính Window 2 thì bắt buộc restore snapshot. | Chưa test mô phỏng SIGKILL (**Technical Deduction**). |
| **3** | Hết bộ nhớ (Out Of Memory - Fatal Error) | Post/meta dở dang | Giống trường hợp 2 | Tùy thời điểm | **Không chạy được in-process** (PHP dừng khẩn cấp, không vào catch). | Có khi chạy CLI rollback | Chạy CLI rollback độc lập sau khi tăng memory_limit lên 512M. | Chưa test mô phỏng OOM (**Technical Deduction**). |
| **4** | Mất kết nối MySQL (`MySQL server has gone away`) | Post/meta dở dang | Manifest ghi nhận tới đối tượng trước đó | Có | **Không chạy được**: Rollback cần MySQL để xóa. Nếu DB chết, rollback lập tức thất bại. | Không thể đọc DB | Khắc phục dịch vụ MySQL trước; sau đó chạy CLI rollback. | Test B4 (mô phỏng query DB thất bại thì ném ngoại lệ fail-closed). |
| **5** | Đầy ổ đĩa / Không thể ghi Manifest | Post/meta đã ghi vào MySQL; manifest trên đĩa không cập nhật được | Không có trong manifest file | Có | **Thất bại khi cập nhật manifest**. Post trong DB trở thành Window 3 orphan. | Có | Giải phóng dung lượng đĩa; chạy CLI rollback (hàm `find_posts_by_ownership_tag` sẽ vớt orphan có tag). | Chưa test đầy đĩa (**Technical Deduction**). |
| **6** | Lỗi rename tệp tạm manifest trên Windows | Post/meta đã tạo; tệp `.tmp` tồn tại nhưng không đổi tên được | Manifest chính bị lỗi thời | Có | Thất bại khi ghi trạng thái; ném ngoại lệ fail-closed. | Có | Đổi tên tệp `.tmp` thủ công hoặc chạy rollback chỉ định file. | Chưa test lock file antivirus (**Technical Deduction**). |
| **7** | MySQL hoặc OS bị sập nguồn đột ngột | Dữ liệu dở dang trong InnoDB buffer pool | Phụ thuộc vào flush disk của manifest | Tùy thời điểm | Cần khởi động lại OS/MySQL trước; InnoDB tự crash-recovery dữ liệu chưa commit. | Có | Kiểm tra manifest và chạy CLI rollback hoặc khôi phục snapshot. | Chưa test cắt nguồn đột ngột (**Technical Deduction**). |
| **8** | **Crash Window 2** (Sau `wp_insert_post`, TRƯỚC tag meta) | 1 post trong `wp_posts`, **HOÀN TOÀN KHÔNG CÓ META TAG** | **Không** | **KHÔNG CÓ** | **0% KHẢ NĂNG ROLLBACK BẰNG CODE**. Code từ chối xóa để tránh xóa nhầm post user. | Không thể phân biệt bằng tag | **BẮT BUỘC KHÔI PHỤC SNAPSHOT T-0.** Tuyệt đối không xóa bằng heuristic SKU/title/timestamp. | Runtime Test B3 (chứng minh post untagged được giữ nguyên). |
| **9** | **Crash Window 3** (Đã có tag meta, TRƯỚC ghi manifest) | Post + meta đã có tag, nhưng manifest thiếu ID | Không có trong file manifest | **CÓ** | **Có thể chạy qua CLI rollback**: Hàm `find_posts_by_ownership_tag` quét DB và gom vào hàng đợi xóa. | Có | Chạy `php run_controlled_import.php rollback <manifest>`. | Runtime Test B1 (chứng minh post có tag được tìm thấy và xóa sạch). |
| **10** | Rollback bị dừng giữa chừng (Partial Rollback) | Một số variation đã xóa, parent chưa kịp xóa | Manifest ghi `FAILED_ROLLBACK_INCOMPLETE` | Có | **Có thể chạy lại (Idempotent)**: Chạy lại rollback lần 2 sẽ tiếp tục xóa phần còn lại. | Có | Chạy lại lệnh rollback lần 2. | Runtime Test D4 (chứng minh tính idempotent khi chạy liên tiếp). |

---

## 6. F-3 — KIỂM TRA OWNERSHIP TAG VÀ MANIFEST

### 6.1. Đính chính thuật ngữ `scan_untagged_orphans()`
- **Thực tế mã nguồn:** Hoàn toàn **không tồn tại** hàm nào tên `scan_untagged_orphans()` trong `run_controlled_import.php`.
- **Hàm thực tế:** Phương thức tại dòng 190 là `find_posts_by_ownership_tag( $run_id )`.
- **Bản chất kỹ thuật:** Hàm này thực hiện truy vấn SQL:
  ```sql
  SELECT p.ID, p.post_type, p.post_parent 
  FROM wp_posts p 
  INNER JOIN wp_postmeta pm ON p.ID = pm.post_id 
  WHERE pm.meta_key = '_controlled_import_run_id' 
    AND pm.meta_value = %s
  ```
  Nó chỉ tìm các đối tượng **ĐÃ CÓ TAG `_controlled_import_run_id`** của phiên hiện tại nhưng bị thiếu trong tệp manifest (Crash Window 3). Việc các báo cáo trước gọi đây là "untagged orphans" là một sự nhầm lẫn nghiêm trọng về mặt ngữ nghĩa: nó là **"un-manifested tagged objects"** chứ không phải "untagged".

### 6.2. Ma trận Hành vi đối với 7 Trạng thái Đối tượng

| Trạng thái Đối tượng | Được phát hiện? | Đưa vào Rollback Queue? | Có bị xóa không? | Cơ chế bảo vệ | Cảnh báo / Lỗi sinh ra |
|---|---|---|---|---|---|
| **A. Không có Tag, Không có Manifest ID** (User post hoặc Window 2 crash) | **Không**. SQL lọc theo `pm.meta_value = $run_id` nên bỏ qua. | **Không** | **KHÔNG** | **Bảo vệ tuyệt đối (Fail-closed)**. Không xóa đối tượng không thuộc quyền sở hữu. | Không có lỗi trong rollback. Nếu do Window 2 crash, post thành rác mồ côi trong DB, bắt buộc khôi phục snapshot. |
| **B. Có Tag của run hiện tại, Không có Manifest ID** (Window 3 crash) | **Có**, qua `find_posts_by_ownership_tag()`. | **Có**, được append vào `created_parents` hoặc `created_variations`. | **CÓ**, xóa an toàn qua `$delete_and_verify()`. | Kiểm tra post_type hợp lệ và không trùng attachment ID có sẵn. | Tăng biến `recovered_unmanifested_count` và cập nhật lại manifest file. |
| **C. Có Tag của run khác** (Ví dụ `run_other_999`) | Nếu nằm trong DB: SQL bỏ qua. Nếu manifest cố tình khai báo: Bị phát hiện tại dòng 1201. | **Không** (Bị loại trừ ngay) | **KHÔNG** | **Bảo vệ tuyệt đối**. Không bao giờ xóa dữ liệu của phiên khác. | `RuntimeException: Ownership audit failed: ... is not owned by run 'X' (owner: 'Y'). Rollback refused.` |
| **D. Có Manifest ID, nhưng Không có Tag trong DB** | Có, trong quá trình duyệt mảng manifest trước khi xóa. | Có trong mảng ban đầu, nhưng bị chặn trước khi xóa. | **KHÔNG** | **Dừng khẩn cấp toàn bộ quá trình rollback**. | `RuntimeException: Ownership audit failed: ... is not owned by run 'X' (owner: ''). Rollback refused.` |
| **E. Có Tag và Manifest ID khớp nhau** (Trường hợp bình thường) | Có, qua cả manifest và DB audit. | Có | **CÓ**, xóa tuần tự: variation -> parent. | Hậu kiểm `SELECT ID FROM wp_posts WHERE ID = %d` để xác nhận đã biến mất. | Ghi nhận chi tiết vào `rollback_summary`. |
| **F. Post đã bị xóa từ trước hoặc không tồn tại** | Có, `get_post_type_safe()` trả về `null`. | Được đánh dấu là đã vắng mặt. | Bỏ qua việc gọi `wp_delete_post()`. | **Idempotent**: Không báo lỗi khi đối tượng đã không còn. | Bỏ qua an toàn, ghi nhận vào log. |
| **G. Attachment có sẵn từ trước** (Placeholder ID 5) | Có, qua `snapshot_pre_existing_attachments()`. | **Tuyệt đối cấm**. | **KHÔNG BAO GIỜ**. | **Bảo vệ nhiều lớp**: Bị chặn tại dòng 1139 và dòng 1157. | `RuntimeException: Security violation: Manifest attempts to claim ownership of pre-existing object ID 5!` |

---

## 7. F-4 — KIỂM TRA QUYỀN MYSQL TỐI THIỂU (DATABASE PRIVILEGE MATRIX)

### 7.1. Phân loại Quyền MySQL theo từng Thao tác

| Thao tác nghiệp vụ | API / Câu lệnh thực thi | Quyền MySQL tối thiểu | Phân loại | Ghi chú an toàn |
|---|---|---|---|---|
| **Preflight DB Identity Check** | `SELECT DATABASE()` | Không cần quyền bảng | Connection | Chỉ đọc |
| **Preflight Duplicate SKU Check** | `SELECT post_id, meta_value FROM wp_postmeta WHERE ...` | `SELECT` | DML | Chỉ đọc |
| **Snapshot Pre-existing Attachments** | `SELECT ID FROM wp_posts WHERE post_type = 'attachment'` | `SELECT` | DML | Chỉ đọc |
| **Discovery Tagged Orphans** | `SELECT ... FROM wp_posts p INNER JOIN wp_postmeta pm ...` | `SELECT` | DML | Chỉ đọc |
| **Post-deletion Verification** | `SELECT ID FROM wp_posts WHERE ID = %d` | `SELECT` | DML | Chỉ đọc |
| **Post-import Stock Verification** | `SELECT meta_value FROM wp_postmeta ...` | `SELECT` | DML | Chỉ đọc |
| **Tạo Bài viết / Biến thể** | `wp_insert_post()` | `SELECT`, `INSERT`, `UPDATE` | Core DML | Tác động `wp_posts` |
| **Gắn Metadata & Tag** | `update_post_meta()` | `SELECT`, `INSERT`, `UPDATE`, `DELETE` | Core DML | Tác động `wp_postmeta` |
| **Gắn Danh mục & Thuộc tính** | `wp_set_object_terms()` | `SELECT`, `INSERT`, `DELETE`, `UPDATE` | Core DML | Tác động `wp_terms`, `wp_term_taxonomy`, `wp_term_relationships` |
| **Rollback Xóa Bài viết** | `wp_delete_post()` | `SELECT`, `DELETE` | Core DML | Xóa bài viết và metadata liên quan |
| **Lưu trữ Manifest** | `file_put_contents()` / `rename()` trên ổ đĩa | **0 QUYỀN MYSQL** | Filesystem | Manifest là tệp JSON, không dùng MySQL |
| **Xóa bảng / Đổi cấu trúc** | Importer **không bao giờ gọi** `DROP`, `ALTER`, `TRUNCATE` | **KHÔNG CẦN DDL** | DDL | Quyền DDL hoàn toàn không cần thiết cho importer |
| **Backup ngoài (mysqldump)** | Công cụ dòng lệnh bên ngoài | `SELECT`, `LOCK TABLES`, `SHOW VIEW` | Admin/Dump | Thao tác bên ngoài importer |
| **Restore ngoài (mysql < dump)** | Công cụ dòng lệnh bên ngoài | `CREATE`, `DROP`, `ALTER`, `INSERT` | Full DDL/DML | Thao tác bên ngoài importer |

### 7.2. Đính chính Sai sót trong Báo cáo R2F về Bảng Manifest
Trong báo cáo Stage 3N-R2F trước đó, bảng ma trận quyền có nêu: "CREATE TABLE IF NOT EXISTS wp_controlled_import_manifest... Cần quyền CREATE".
**Kiểm toán mã nguồn xác nhận:** Importer hoàn toàn **không có bất kỳ câu lệnh SQL `CREATE TABLE` nào**. Manifest được quản lý 100% bằng tệp JSON trong thư mục `backups/manifests/`. Truy vấn kiểm tra trên MySQL (`SHOW TABLES LIKE '%manifest%'`) trả về 0 bảng.
**Do đó, Importer chỉ cần đúng 4 quyền DML: `SELECT`, `INSERT`, `UPDATE`, `DELETE` trên database WordPress. Không cần bất kỳ quyền DDL nào.**

### 7.3. Đề xuất Kế hoạch Kiểm thử Tài khoản Giới hạn Quyền (Chỉ Đề xuất, Không Thực thi)
Để chứng minh importer hoạt động an toàn mà không cần quyền quản trị MySQL cao nhất, đề xuất kế hoạch kiểm thử cho một giai đoạn tương lai riêng biệt:
1. **Môi trường:** Thực hiện hoàn toàn trên database cô lập `local_test_isolated_3nr2` tại cổng 10011. Tuyệt đối không chạm vào `local`.
2. **Tạo tài khoản thử nghiệm:**
   ```sql
   CREATE USER 'wp_importer_limited'@'localhost' IDENTIFIED BY 'limited_pass_123';
   GRANT SELECT, INSERT, UPDATE, DELETE ON local_test_isolated_3nr2.* TO 'wp_importer_limited'@'localhost';
   FLUSH PRIVILEGES;
   ```
3. **Bài kiểm tra 1 (Xác nhận DDL bị từ chối):** Chạy lệnh thử `DROP TABLE` hoặc `ALTER TABLE` dưới user này; kỳ vọng MySQL trả về lỗi `Access denied for user`.
4. **Bài kiểm tra 2 (Chạy Runtime Test Suite):** Khởi chạy bộ 19 runtime tests kết nối qua user `wp_importer_limited` tới `local_test_isolated_3nr2`; kỳ vọng 19/19 tests PASS.
5. **Dọn dẹp:** Xóa user `wp_importer_limited` sau khi hoàn tất.
6. **Trạng thái hiện tại:** **`NOT VERIFIED`** (Chưa thực thi kế hoạch này trong đợt audit chỉ đọc hiện tại).

---

## 8. KIỂM TRA TÍNH NHẤT QUÁN CỦA TEST VÀ LOG

### 8.1. Kiểm tra Đối chiếu Nội dung Log JSON
- **`gate3g_regression_results.json`:** Mảng JSON 85 phần tử, tất cả đều `passed: true`. Exit code: 0. Chạy trên in-memory mock harness. Kiểm tra toàn diện các tình huống logic, kiểm tra hash, kiểm tra rollback giả lập.
- **`stage3nr2_runtime_results.json`:** Mảng JSON 19 phần tử, tất cả đều `passed: true`. Exit code: 0. Chạy trên WordPress 7.1.3 + WooCommerce 11.2.1 thật kết nối database `local_test_isolated_3nr2`.
  - Nhóm A (A1–A4): Kiểm tra cách ly DB và rào chắn từ chối 2 chiều.
  - Nhóm B (B1–B4): Kiểm tra phát hiện orphan Window 3 qua SQL thật, bảo vệ Window 2 untagged, bảo vệ run khác.
  - Nhóm C (C1–C6): Kiểm tra ma trận tồn kho 64 SKU và phát hiện lỗi bù trừ.
  - Nhóm D (D1–D5): Kiểm tra xóa thật trên MySQL, bắt lỗi pre_delete_post, kiểm tra tính idempotent.

### 8.2. Mâu thuẫn giữa Báo cáo Lịch sử và Test Source
1. **Cách gọi tên nhóm test:** Trong báo cáo 3N-R, nhóm 15 test mới được ký hiệu là F-3N-01 (5 test), F-3N-02 (5 test), F-3N-03 (5 test). Trong khi ở runtime test Stage 3N-R2, chúng được chuẩn hóa thành ký hiệu chữ cái A1–A4, B1–B4, C1–C6, D1–D5.
2. **Khẳng định "xóa orphan untagged":** Báo cáo 3N-R và 3N-R2 ghi "test B3 kiểm tra xóa orphan untagged". Thực tế mã nguồn test B3 khẳng định ngược lại: Test B3 tạo một post không có tag và **chứng minh rằng rollback KHÔNG XÓA post đó** (`$db_check_b3 === $post_b3_untagged`). Đây là hành vi đúng chuẩn fail-closed, nhưng mô tả trong báo cáo cũ đã gây hiểu lầm.

---

## 9. READ-ONLY DATABASE OBSERVATIONS TRÊN DATABASE CHÍNH (`local`)

Đã thực hiện truy vấn `SELECT` an toàn trực tiếp trên MySQL port 10011 vào database `local`:

```sql
SELECT post_type, post_status, COUNT(*) FROM wp_posts GROUP BY post_type, post_status;
SELECT option_name, option_value FROM wp_options WHERE option_name = 'woocommerce_coming_soon';
SELECT ID, post_title, post_type, guid FROM wp_posts WHERE ID = 5;
SHOW TABLES LIKE '%manifest%';
```

**Kết quả ghi nhận:**
- `product`: **0**
- `product_variation`: **0**
- `attachment`: **1** (ID 5, `woocommerce-placeholder`, status: `inherit`, URL: `http://localhost:10010/wp-content/uploads/2026/10/woocommerce-placeholder.webp`)
- `page`: 7 (5 publish, 2 draft)
- `post`: 2 (1 publish, 1 auto-draft)
- `woocommerce_coming_soon`: **`yes`**
- Bảng manifest trong MySQL: **0 bảng** (Xác nhận manifest không dùng MySQL).

*Lưu ý bảo mật & tính trung thực:* Không in ra bất kỳ mật khẩu hay credential nào. Các số liệu trên chỉ chứng minh các trạng thái được đọc trực tiếp là nguyên vẹn, không suy diễn vượt quá phạm vi truy vấn.

---

## 10. DANH MỤC FINDINGS THEO MỨC ĐỘ NGHIÊM TRỌNG

### Finding F-3N-R2G-01: Chênh lệch Kích thước Importer giữa v1.3 và v1.4 (+16 Bytes)
- **Severity:** INFORMATIONAL
- **Mô tả:** Chênh lệch 16 bytes giữa v1.3 và v1.4 được chứng minh chính xác bằng 8 lần thay thế `self::` thành `static::` trên 8 dòng code riêng biệt (mỗi dòng tăng đúng 2 bytes nhị phân).
- **Bằng chứng:** Diff nhị phân giữa `backups_stage_3nr2_20261019_113000/run_controlled_import.php` và `run_controlled_import.php` hiện hành.
- **Ảnh hưởng:** Không ảnh hưởng tiêu cực; Late Static Binding hoạt động chuẩn mực.
- **Trạng thái:** **VERIFIED & RECONCILED**.

### Finding F-3N-R2G-02: Sai lệch Thuật ngữ `scan_untagged_orphans()` vs `find_posts_by_ownership_tag()`
- **Severity:** HIGH
- **Mô tả:** Các báo cáo trước dùng thuật ngữ gây hiểu lầm rằng importer có thể tự động "quét và xóa orphan chưa gắn tag". Trong thực tế mã nguồn, hàm là `find_posts_by_ownership_tag()` và chỉ tìm đối tượng ĐÃ CÓ TAG. Đối tượng thực sự chưa gắn tag (Crash Window 2) hoàn toàn không thể xóa tự động bằng code.
- **Bằng chứng:** Dòng 190 của `run_controlled_import.php` và mã nguồn Test B3.
- **Ảnh hưởng:** Người vận hành có thể chủ quan cho rằng mọi sự cố crash đều có thể rollback bằng lệnh CLI mà không chuẩn bị phương án restore snapshot.
- **Biện pháp giảm thiểu:** Bắt buộc phổ biến quy trình: Nếu crash tại Window 2, phương án duy nhất là restore snapshot database.
- **Trạng thái:** **VERIFIED & MITIGATED VIA FAIL-CLOSED SPECIFICATION**.

### Finding F-3N-R2G-03: Giới hạn Phục hồi khi Crash Tiến trình (Timeout, OOM, SIGKILL)
- **Severity:** HIGH
- **Mô tả:** Các báo cáo trước khẳng định "100% rollback capability" là không phản ánh đầy đủ thực tế. Khi PHP bị crash khẩn cấp do OOM hoặc timeout cứng, khối `catch` không thể chạy in-process. Việc phục hồi đòi hỏi người vận hành can thiệp chạy lệnh rollback thủ công qua CLI hoặc khôi phục snapshot.
- **Bằng chứng:** Phân tích kiến trúc xử lý lỗi của PHP CLI và quy trình vòng đời của `run_controlled_import.php`.
- **Ảnh hưởng:** Nếu không có tài liệu vận hành chi tiết, sự cố giữa chừng có thể gây hoang mang cho kỹ sư vận hành.
- **Biện pháp giảm thiểu:** Thiết lập ma trận Crash-Window chi tiết (Mục 5) và yêu cầu cấu hình `memory_limit=512M` khi chạy CLI.
- **Trạng thái:** **VERIFIED & DOCUMENTED**.

### Finding F-3N-R2G-04: Đính chính Cơ chế Lưu trữ Manifest và Quyền MySQL DDL
- **Severity:** MEDIUM
- **Mô tả:** Báo cáo Stage 3N-R2F khẳng định importer cần quyền `CREATE TABLE` để tạo bảng manifest trong MySQL. Thực tế manifest là tệp JSON trên đĩa và MySQL không chứa bảng manifest nào. Importer hoàn toàn không cần bất kỳ quyền DDL nào (`CREATE`, `DROP`, `ALTER`, `TRUNCATE`).
- **Bằng chứng:** Mã nguồn `flush_manifest()`, `initialize_manifest()` và kết quả truy vấn `SHOW TABLES LIKE '%manifest%'` (0 bảng).
- **Ảnh hưởng:** Đánh giá sai ma trận quyền database tối thiểu.
- **Biện pháp giảm thiểu:** Đã đính chính ma trận quyền tại Mục 7; loại bỏ yêu cầu quyền `CREATE`.
- **Trạng thái:** **VERIFIED & RECONCILED**.

### Finding F-3N-R2G-05: Chưa Kiểm nghiệm Thực tế với Tài khoản MySQL Giới hạn Quyền
- **Severity:** MEDIUM
- **Mô tả:** Importer chưa từng được chạy thử nghiệm dưới một tài khoản MySQL bị tước các quyền nguy hiểm (`DROP`, `ALTER`, `GRANT`, `SUPER`). Môi trường LocalWP hiện tại chạy user `root` đầy đủ quyền.
- **Bằng chứng:** Cấu hình kết nối MySQL trong `wp-config.php` và LocalWP settings.
- **Ảnh hưởng:** Chưa có bằng chứng thực nghiệm rằng importer sẽ chạy mượt mà trên môi trường hosting có phân quyền database chặt chẽ.
- **Biện pháp giảm thiểu:** Đề xuất kế hoạch kiểm thử cô lập tại Mục 7.3 cho giai đoạn tiếp theo.
- **Trạng thái:** **NOT VERIFIED**.

---

## 11. ĐÁNH GIÁ TỔNG THỂ CÁC MỤC KIỂM TOÁN

| Hạng mục Kiểm toán | Kết quả Đánh giá | Trạng thái |
|---|---|---|
| **F-1: Đối chiếu Byte-level Importer** | Giải trình chính xác 8 dòng, 8 lần thay thế, tăng đúng 16 bytes. | **PASS** |
| **F-2: Đánh giá Khả năng Phục hồi** | Lập ma trận 10 kịch bản crash, loại bỏ tuyên bố "100%", xác định rõ vai trò snapshot. | **PASS** |
| **F-3: Rà soát Tag và Manifest** | Đính chính thuật ngữ, phân tích đầy đủ 7 trạng thái A-G, xác nhận ranh giới fail-closed. | **PASS** |
| **F-4: Ma trận Quyền MySQL** | Đính chính lưu trữ JSON của manifest, xác định đúng chỉ cần 4 quyền DML. | **PASS** |
| **Thử nghiệm User MySQL Giới hạn Quyền** | Chưa có bằng chứng thực nghiệm; đã đề xuất kế hoạch kiểm thử. | **NOT VERIFIED** |
| **Tự động Phục hồi Window 2 bằng Code** | Về mặt an toàn dữ liệu, không thể tự động hóa; bắt buộc snapshot restore. | **NOT VERIFIED** |
| **Trạng thái Database chính `local`** | Xác nhận 0 products, 0 variations, Coming Soon = yes, 0 manifest tables. | **PASS** |

---

## 12. ĐIỀU KIỆN CÒN THIẾU TRƯỚC KHI XEM XÉT GATE 4

Mặc dù toàn bộ mã nguồn và logic nghiệp vụ đã được kiểm toán sạch sẽ, các điều kiện bắt buộc sau đây **VẪN CHƯA ĐƯỢC ĐÁP ỨNG** và bắt buộc phải hoàn thành trước khi mở Gate 4:

1. **Snapshot T-0 tại chỗ của Database `local`:** Phải tạo một bản sao lưu mysqldump mới nhất của database `local` ngay trước thời điểm bấm lệnh import, kiểm tra dung lượng > 1MB và tính mã băm SHA-256 để dự phòng Crash Window 2.
2. **Kế hoạch Thực thi Phân quyền Tối thiểu (Tùy chọn khuyến nghị cao):** Thực thi kế hoạch kiểm thử cô lập tại Mục 7.3 để đóng Finding F-3N-R2G-05.
3. **Chỉ thị Bằng văn bản Tường minh từ Người dùng (Human Explicit Authorization):** Phải có prompt chỉ định rõ ràng cho phép chuyển sang Gate 4.

---

## 13. DANH SÁCH THAO TÁC ĐÃ CHẠY VÀ KHÔNG CHẠY

### 13.1. Các thao tác đã chạy
- [x] Đọc và so sánh nhị phân, tính SHA-256 trực tiếp từ bytes của các phiên bản importer v1.2, v1.3, v1.4.
- [x] Đọc và phân tích cú pháp mã nguồn `run_controlled_import.php` và `test_stage3nr2_runtime.php`.
- [x] Đọc và đối chiếu cấu trúc tệp log JSON `gate3g_regression_results.json` và `stage3nr2_runtime_results.json`.
- [x] Thực hiện truy vấn `SELECT` chỉ đọc trên MySQL port 10011 vào database `local` để đếm bài viết, kiểm tra option Coming Soon và kiểm tra bảng manifest.
- [x] Tạo báo cáo kiểm toán mới `STAGE_3N_R2G_READ_ONLY_CONSISTENCY_AUDIT_REPORT.md`.

### 13.2. Các thao tác tuyệt đối KHÔNG chạy
- [x] **KHÔNG** chạy importer ở bất kỳ chế độ nào (`import`, `rollback`, `dry-run`).
- [x] **KHÔNG** chạy bất kỳ câu lệnh SQL ghi dữ liệu (`INSERT`, `UPDATE`, `DELETE`, `DROP`, `ALTER`, `CREATE`).
- [x] **KHÔNG** tạo user MySQL hay sửa đổi quyền database.
- [x] **KHÔNG** chạy lại bộ test suite hay tạo test mới có side-effect.
- [x] **KHÔNG** sửa đổi mã nguồn importer, CSV, hay các tệp báo cáo cũ.
- [x] **KHÔNG** thực hiện Git commit, reset, checkout, stash hay clean.
- [x] **KHÔNG** thay đổi trạng thái Coming Soon.
- [x] **KHÔNG** bắt đầu Gate 4.

---

## 14. KẾT LUẬN CUỐI CÙNG VỀ GATE 4

### **MANDATORY STOP — KHÔNG MỞ GATE 4**

Giai đoạn Stage 3N-R2G chính thức kết thúc tại đây với kết quả kiểm toán chỉ đọc đạt **CONDITIONAL PASS — FINDINGS OPEN**. Importer vẫn được giữ nguyên vẹn, database chính `local` hoàn toàn không bị đột biến, và hệ thống dừng lại để chờ chỉ thị tiếp theo từ người dùng.
