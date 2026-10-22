# BÁO CÁO KIỂM TOÁN TÀI LIỆU NGUỒN VÀ PHẠM VI GIT
# STAGE 1B.1 — SOURCE-OF-TRUTH & GIT AUDIT

**Dự án:** `furniture-ecommerce` (Website Thương mại Điện tử Nội thất Quốc tế)  
**Kỹ sư thực hiện:** Senior WordPress/WooCommerce Engineer  
**Thời gian thực hiện:** 15/10/2026  
**Chế độ thực thi:** Read-Only (Không sửa code, không commit, không đổi cấu hình)  
**Trạng thái cổng kiểm soát (Stage-Gate):** **PASS**  

---

## 1. Danh mục Tài liệu Nguồn Thực tế (Source-of-Truth Inventory)

Quá trình quét và kiểm tra hệ thống tệp tin trên toàn bộ thư mục dự án xác nhận các tài liệu nguồn sau:

| Loại tài liệu | Tên file thực tế | Đường dẫn tuyệt đối | Kích thước | Định dạng | Trạng thái tồn tại |
|---|---|---|:---:|:---:|:---:|
| **Yêu cầu bài tập gốc** | `Xây dựng website E-commerce quốc tế.pdf` | `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\wp-content\plugins\Xây dựng website E-commerce quốc tế.pdf` | 152,402 bytes | PDF (13 trang) | **VERIFIED** |
| **Kế hoạch dự án Markdown** | `wordpress_woocommerce_ecommerce_project_plan_refined (1).md` | `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\wp-content\plugins\wordpress_woocommerce_ecommerce_project_plan_refined (1).md` | 33,455 bytes | Markdown (v3.0) | **VERIFIED** |
| **Kế hoạch kiểm thử Markdown** | `test_plan_and_test_cases (1).md` | `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\wp-content\plugins\test_plan_and_test_cases (1).md` | 32,601 bytes | Markdown (v2.0) | **VERIFIED** |
| **Dataset sản phẩm CSV** | `woocommerce_furniture_dataset_22_products.csv` | `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\wp-content\plugins\woocommerce_furniture_dataset_22_products.csv` | 26,176 bytes | CSV (UTF-8, 28 cột) | **VERIFIED** |
| **Bảng tính Excel (`.xlsx` / `.xls`)** | — *(Không tồn tại)* | — | — | Excel Workbook | **NOT FOUND / TỒN TẠI RIÊNG DƯỚI DẠNG CSV** |

> [!IMPORTANT]
> **Làm rõ về Workbook / Dataset:**  
> Hệ thống đã quét toàn bộ cây thư mục `Local Sites\furniture-ecommerce` và xác nhận **không có bất kỳ file định dạng `.xlsx` hay `.xls` nào**. Nguồn dữ liệu bảng tính duy nhất hiện có trong dự án là file **`woocommerce_furniture_dataset_22_products.csv`** (được chuẩn hóa theo đúng định dạng WooCommerce Product CSV Importer). Hai định dạng này được ghi nhận rành mạch, không bị suy đoán hay gộp chung.

---

## 2. Kết quả Kiểm toán Git (Git Audit Results)

Thu thập bằng chứng thực tế từ Git CLI tại repository:

### 2.1 Đường dẫn Repository Root:
- Lệnh: `git rev-parse --show-toplevel`
- Kết quả: `C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/app/public/wp-content` (**VERIFIED**).

### 2.2 Trạng thái Working Tree:
- Lệnh: `git status --short`
- Kết quả: *(Trống - working tree hoàn toàn sạch sẽ, không có file bị sót)* (**VERIFIED**).

### 2.3 Lịch sử Commit Gần nhất:
- Lệnh: `git log -n 5 --oneline`
- Kết quả:
  - `cdf203d docs(stage-1b): add stage 1b controlled git baseline & child theme scaffold report`
  - `8e550bf chore(stage-1b): initial git baseline with twentytwentyfive-child scaffold and project artifacts`

### 2.4 Danh sách 15 File được Theo dõi (Tracked Files) qua `git ls-files`:
1. `.gitignore`
2. `docs/STAGE_1B_REPORT.md`
3. `index.php`
4. `plugins/STAGE_0_DISCOVERY_REPORT.md`
5. `plugins/STAGE_1A_REPORT.md`
6. `plugins/Xây dựng website E-commerce quốc tế.pdf` *(Tài liệu nguồn gốc)*
7. `plugins/docs/STAGE_1B_REPORT.md`
8. `plugins/index.php`
9. `plugins/test_plan_and_test_cases (1).md` *(Test plan nguồn)*
10. `plugins/woocommerce_furniture_dataset_22_products.csv` *(Dataset nguồn)*
11. `plugins/wordpress_woocommerce_ecommerce_project_plan_refined (1).md` *(Project plan nguồn)*
12. `themes/index.php`
13. `themes/twentytwentyfive-child/functions.php` *(Scaffold child theme)*
14. `themes/twentytwentyfive-child/style.css` *(Scaffold child theme)*
15. `themes/twentytwentyfive-child/theme.json` *(Scaffold child theme)*

### 2.5 Xác minh Bảo mật Phạm vi Git (Zero Sensitive Leakage Verification):
- [x] **Không có file bí mật hay credentials:** `wp-config.php`, `*.env` không bị track.
- [x] **Không có bản sao lưu cơ sở dữ liệu:** File backup `stage_1a_baseline_backup.sql` nằm ngoài `wp-content` (ở `backups/`) và được `.gitignore` loại trừ.
- [x] **Không có file tải lên của người dùng:** Thư mục `uploads/` bị loại trừ 100%.
- [x] **Không có WordPress Core runtime:** Thư mục `wp-admin/`, `wp-includes/` nằm ngoài repository.
- [x] **Không có plugin/theme bên thứ ba:** `plugins/woocommerce/` và các theme mặc định/bên thứ ba (`twentytwentyfive`, `interior-design-*`) được `.gitignore` loại trừ 100%.

---

## 3. Kiểm kê & Đối chiếu Dataset và Test Plan (Read-Only)

### 3.1 Cấu trúc Chi tiết File Dataset `woocommerce_furniture_dataset_22_products.csv`:
- **Tổng số dòng dữ liệu:** **64 dòng dữ liệu** (+ 1 dòng tiêu đề = **65 dòng tổng cộng**).
- **Số lượng cột thuộc tính:** **28 cột**.
- **Danh sách tiêu đề 28 cột:**
  `Type`, `SKU`, `Name`, `Published`, `Is featured?`, `Visibility in catalog`, `Short description`, `Description`, `Regular price`, `Sale price`, `In stock?`, `Stock`, `Categories`, `Tags`, `Images`, `Parent`, `Attribute 1 name`, `Attribute 1 value(s)`, `Attribute 1 visible`, `Attribute 1 global`, `Attribute 2 name`, `Attribute 2 value(s)`, `Attribute 2 visible`, `Attribute 2 global`, `Attribute 3 name`, `Attribute 3 value(s)`, `Attribute 3 visible`, `Attribute 3 global`.

### 3.2 Phân rã Cơ cấu Sản phẩm trong Dataset:
1. **Sản phẩm Đơn giản (Simple Products):** **12 sản phẩm**
   - 10 sản phẩm giá thường: `FURN-001` đến `FURN-010` (Trong đó `FURN-010` có tồn kho = 0, phục vụ test lọc `In stock`).
   - 2 sản phẩm giảm giá (Sale): `FURN-011` (Regular $250 / Sale $199) và `FURN-012` (Regular $180 / Sale $129, Out of stock).
2. **Sản phẩm Biến thể Cha (Parent Variable Products):** **10 sản phẩm**
   - 8 sản phẩm giá thường: `FURN-013` đến `FURN-020` (Trong đó `FURN-013` là *Modern Lounge Chair* chuẩn mẫu PDF).
   - 2 sản phẩm biến thể giảm giá (Sale): `FURN-021` (*Signature Velvet Accent Armchair*, giảm từ $399 xuống $299) và `FURN-022` (*Smart LED Bedside Storage Frame*, giảm 30% từ $350 xuống $245).
3. **Các dòng Biến thể Con (Variations):** **42 dòng**
   - Gắn với SKU cha qua cột `Parent` (`FURN-013` đến `FURN-022`).
   - Mỗi biến thể có tổ hợp `Color` (`Black`, `White`, `Brown`) và `Size` (`S`, `M`, `L`), có ảnh Unsplash riêng và đơn giá riêng.
- **Tổng số sản phẩm gốc:** 12 + 10 = **22 sản phẩm** (Vượt chuẩn tối thiểu 20 sản phẩm của PDF).
- **Thuộc tính toàn cục:**
  - `Attribute 1`: `Color` (Global = 1)
  - `Attribute 2`: `Size` (Global = 1)
  - `Attribute 3`: `Brand` (Global = 1, gồm 5 thương hiệu: `NordicLiving`, `Minimalia`, `OakCraft`, `UrbanoHaus`, `LuxeVibe`)
- **Danh mục sản phẩm (`Categories`):** Đủ 7 danh mục: `Chairs`, `Tables`, `Sofas`, `Beds`, `Lighting`, `Decoration`, `Storage`.

### 3.3 Phân rã & Đối chiếu Kế hoạch Kiểm thử (`test_plan_and_test_cases (1).md`):
- **Tổng số ca kiểm thử:** **28 Test Cases** (`TC01` đến `TC28`).
- **Nhóm Cốt lõi (`TC01 – TC19`, 19 Test Cases):** Bám sát 100% thang điểm 100 của PDF gốc:
  - `TC01`: AJAX Live Search (`chair`)
  - `TC02`: Lọc theo 7 danh mục tại `/shop`
  - `TC03`: Lọc theo thanh trượt giá `$0 — $500`
  - `TC04`: Lọc theo màu sắc (`Black`, `White`, `Brown`)
  - `TC05`: Mở và kiểm tra đủ 16 thành phần trang chi tiết sản phẩm
  - `TC06`: Chuyển đổi biến thể Color/Size cập nhật giá và ảnh động
  - `TC07`: Thêm 2 sản phẩm vào giỏ hàng và kiểm tra nút `BUY NOW`
  - `TC08`: Tăng và giảm số lượng sản phẩm trong giỏ hàng (kịch bản trang 7 PDF)
  - `TC09`: Xóa sản phẩm khỏi giỏ hàng và kiểm tra cập nhật tổng tiền
  - `TC10`: Áp dụng mã giảm giá hợp lệ (`SUMMER30` giảm 30%)
  - `TC11`: Thanh toán hoàn chỉnh qua Cash on Delivery (COD)
  - `TC12`: Thanh toán qua Direct Bank Transfer (BACS)
  - `TC13`: Luồng khách hàng `Register -> Login -> My Account -> View Orders`
  - `TC14`: Thêm và xóa sản phẩm khỏi Wishlist
  - `TC15`: Trang Blog (6 bài, 3 danh mục, featured post, related posts)
  - `TC16`: Kiểm tra hiển thị responsive tại 3 kích thước: `1440×900`, `768×1024`, `390×844`
  - `TC17`: Trang chủ (8 khối) và trang giới thiệu `/about-us`
  - `TC18`: Bộ lọc nâng cao theo Brand và Availability (`In stock`, `On sale`)
  - `TC19`: Kiểm tra tùy biến chống lỗi "Copy nguyên demo theme"
- **Nhóm Mở rộng (`TC20 – TC28`, 9 Test Cases):**
  - `TC20`: Validate form Checkout khi thiếu trường hoặc sai định dạng
  - `TC21`: Xử lý sản phẩm hết hàng và kiểm tra giới hạn tồn kho
  - `TC22`: Nhập mã giảm giá sai / hết hạn (`EXPIRED10`)
  - `TC23`: Kiểm tra phân quyền RBAC và chống xem trộm đơn hàng người khác (IDOR)
  - `TC24`: Kiểm tra chống tấn công mã độc XSS trên Search, Reviews, Order Notes
  - `TC25`: Kiểm tra loại trừ bộ nhớ đệm (Cache Bypass) cho Cart/Checkout/Account
  - `TC26`: Tốc độ trang, ảnh WebP và không có lỗi Console
  - `TC27`: Diễn tập nạp lại dataset CSV và sao lưu phục hồi
  - `TC28`: Tối ưu trải nghiệm bàn phím, tooltip ô màu và thanh sticky trên mobile

---

## 4. Kiểm tra Tính Nhất quán của Báo cáo (Consistency Audit)

### 4.1 Đối chiếu Tên Theme Active:
- **Tại Báo cáo Stage 0:** Khảo sát ban đầu ghi nhận `interior-design-wda` (v1.2.9) đang active.
- **Tại Báo cáo Stage 1A và Stage 1B:**
  - Lệnh WP-CLI `wp theme list --status=active` và tùy chọn `template`/`stylesheet` trong bảng `wp_options` thực tế trả về:
    **`interior-design-firm` (v3.2.7)**.
  - **Lý do sai khác:** Sau thời điểm khảo sát Stage 0, thao tác kích hoạt theme đã được thực hiện trong giao diện WordPress Admin (chuyển từ `interior-design-wda` sang `interior-design-firm`).
  - **Trạng thái tuân thủ:** Cả trong Stage 1A và Stage 1B, Senior Engineer đã giữ nguyên hiện trạng của database, **tuyệt đối không can thiệp, không kích hoạt và không chuyển đổi theme**.
  - Child theme `twentytwentyfive-child` hiện vẫn đang ở trạng thái **`inactive`**.

### 4.2 Xác minh Đường dẫn Git và Commit Baseline:
- Thư mục quản lý Git: `app/public/wp-content` (**VERIFIED**).
- Commit baseline: `8e550bf` (**VERIFIED**).
- Toàn bộ 4 tài liệu nguồn quan trọng đều nằm trong danh mục file được theo dõi (Tracked) của Git repository (**VERIFIED**).

---

## 5. Danh mục Sai lệch & Điểm Chưa Xác minh (Discrepancies & Open Items)

1. **Về file Excel Workbook:**
   - Trong yêu cầu có nhắc tới "file Excel hoặc bảng tính chứa dataset sản phẩm". Xác nhận trong dự án **chưa có file `.xlsx` / `.xls`**, mà chỉ có file bảng tính chuẩn **`woocommerce_furniture_dataset_22_products.csv`**. Cần ghi nhận đây là nguồn dữ liệu chuẩn duy nhất hiện có.
2. **Khả năng import ảnh từ Unsplash:**
   - Trong file CSV, 64 dòng sản phẩm và biến thể đều có đường dẫn ảnh từ `images.unsplash.com`. Việc import ảnh vào Media Library ở Stage 2 sẽ phụ thuộc vào kết nối mạng internet của máy tính người dùng.

---

## 6. Kết luận Acceptance Gate

- [x] **Xác định chính xác 100% tài liệu nguồn:** Đường dẫn, kích thước, định dạng và trạng thái của cả 4 tài liệu đều được ghi nhận bằng chứng thực tế.
- [x] **Kiểm toán Git an toàn:** Repository hoạt động ổn định, `git ls-files` chỉ chứa mã tùy biến và tài liệu dự án, hoàn toàn không rò rỉ file bí mật, dump, uploads hay mã nguồn bên thứ ba.
- [x] **Kiểm kê dataset & test plan đầy đủ:** Đã đối chiếu 22 sản phẩm (64 dòng data, 28 cột) và 28 ca kiểm thử cốt lõi/mở rộng.
- [x] **Tính nhất quán của báo cáo được làm rõ:** Đã giải thích rõ ràng sự thay đổi của active theme (`interior-design-firm`) và bảo toàn trạng thái an toàn.
- [x] **Chế độ chỉ đọc được giữ trọn vẹn:** Không tạo commit mới, không sửa database, không kích hoạt theme.

**KẾT LUẬN CỔNG KIỂM SOÁT:** **`PASS`**.

---
*(Hệ thống dừng lại và không tự ý thực hiện bất kỳ hành động nào. Kính chờ phản hồi và phê duyệt từ Bạn).*
