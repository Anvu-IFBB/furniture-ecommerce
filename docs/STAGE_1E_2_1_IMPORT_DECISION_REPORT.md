# STAGE 1E.2.1 — IMPORT DECISION & MAPPING LOCK REPORT

> **Môi trường:** Local WordPress (Windows 11)  
> **WordPress Root:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public`  
> **Git Root:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\wp-content`  
> **Tệp CSV nguồn:** `.../wp-content/plugins/woocommerce_furniture_dataset_22_products.csv`  
> **Thời điểm thẩm định:** 2026-10-16 15:38:00+07:00  
> **Số lượng sản phẩm trong DB:** 0 products, 0 variations  
> **Chế độ thực thi:** **READ-ONLY / Tuyệt đối không ghi Database**  
> **Trạng thái phục hồi Database:** `DATABASE RESTORE: NOT VERIFIED`  
> **Quyết định Stage-Gate:** **PASS WITH CONDITIONS** (Đã chốt toàn bộ kiến trúc taxonomy và mapping; sẵn sàng trình duyệt trước khi import)

---

## 1. Executive Summary (Tóm tắt điều hành)

Báo cáo **Stage 1E.2.1** giải quyết dứt điểm các quyết định kiến trúc then chốt trước khi nạp dữ liệu:
1. **Khóa kiến trúc Taxonomy:**
   - **Color & Size:** Thống nhất sử dụng Global Attributes chuẩn của WooCommerce với slug `pa_color` và `pa_size`. API `wc_create_attribute()` có sẵn trong WooCommerce Core.
   - **Brand:** Điều tra nguồn gốc xác nhận taxonomy `product_brand` được tích hợp trong WooCommerce Core (`Automattic\WooCommerce\Internal\Brands`). Tuy nhiên, do tệp CSV thiết kế `Brand` dưới dạng `Attribute 3` (Global = 1), việc chọn **`pa_brand` (Global Attribute)** là phương án tối ưu tuyệt đối: đồng bộ 100% với CSV không cần sửa cột, tương thích hoàn hảo với Gutenberg Filter by Attribute Block của Twenty Twenty-Five.
2. **Xử lý hai URL ảnh bị 404 (`FURN-006` và `FURN-018`):**
   - Xác nhận tình trạng HTTP 404 cho 2 URL ảnh ngoại vi Unsplash.
   - Trạng thái ảnh thay thế hiện tại: **`REPLACEMENT NOT APPROVED`** (Chưa tự ý thay ảnh; đề xuất 2 giải pháp rõ ràng để người dùng lựa chọn).
3. **Chiến lược Import & Công cụ thực tế:**
   - Xác minh thực tế lệnh `wp wc product import` **KHÔNG TỒN TẠI** trong WP-CLI hiện tại (`wp wc product` chỉ có `create`, `delete`, `get`, `list`, `update`).
   - Hai công cụ khả thi được kiểm chứng: Trình nhập liệu chuẩn **WooCommerce Native CSV Importer UI** (`WC_Product_CSV_Importer_Controller`) hoặc **Scripted Runner** sử dụng trực tiếp class `WC_Product_CSV_Importer` của WooCommerce Core.
4. **Quyết định Stage-Gate:** Đạt **PASS WITH CONDITIONS** (Kế hoạch đã khóa và an toàn; chờ người dùng phê duyệt phương án ảnh và lệnh khởi tạo taxonomy trước khi thực thi Stage 1E.3).

---

## 2. Actual Evidence (Bằng chứng kiểm tra hiện trạng)

Mọi nhận định kỹ thuật đều được kiểm chứng trực tiếp bằng mã PHP/WP-CLI:

| Hạng mục | Lệnh / Đoạn mã kiểm chứng | Kết quả thực tế | Ý nghĩa kỹ thuật |
| :--- | :--- | :---: | :--- |
| **Sản phẩm trong DB** | `wp post list --post_type=product` | **0** | Database sạch 100%, không xung đột |
| **Biến thể trong DB** | `wp post list --post_type=product_variation` | **0** | Chưa có biến thể nào |
| **Nguồn gốc `product_brand`** | `class_exists('Automattic\WooCommerce\Internal\Brands')` | **TRUE** | Tính năng Brands gốc của WooCommerce Core 9.4+/11.x |
| **Hàm `wc_create_attribute`**| `function_exists('wc_create_attribute')` | **TRUE** | Có sẵn API tạo thuộc tính toàn cục |
| **Lệnh `wp wc product import`**| `wp help wc product` | **KHÔNG CÓ** | Chỉ có `create`, `delete`, `get`, `list`, `update` |
| **Class CSV Importer Core** | `class_exists('WC_Product_CSV_Importer')` | **TRUE** | Nằm tại `woocommerce/includes/import/...` |
| **Tình trạng ảnh Unsplash** | HTTP HEAD request trực tiếp | **23 OK / 2 lỗi 404** | Xác nhận `FURN-006` và `FURN-018` bị 404 |

---

## 3. Taxonomy Decisions & Rationale (Quyết định Taxonomy & Lý do)

### 3.1. Quyết định cho Color & Size: KHÓA `pa_color` và `pa_size`
- **Tên hiển thị:** `Color` và `Size`.
- **Slug taxonomy:** `pa_color` và `pa_size`.
- **Cơ chế hoạt động:**
  - WooCommerce lưu trữ các thuộc tính toàn cục trong bảng `wp_woocommerce_attribute_taxonomies`.
  - Khi đăng ký, WooCommerce tự động tạo 2 custom taxonomy tương ứng: `pa_color` và `pa_size`.
  - Cả 10 sản phẩm variable trong CSV đều khai báo:
    - `Attribute 1 name` = `Color`, `Attribute 1 global` = `1`.
    - `Attribute 2 name` = `Size`, `Attribute 2 global` = `1`.
  - Trình nhập liệu WooCommerce sẽ tự động ánh xạ giá trị vào terms của `pa_color` (`Black`, `White`, `Brown`) và `pa_size` (`S`, `M`, `L`).

### 3.2. Quyết định cho Brand: KHÓA `pa_brand` (Global Attribute)
Chúng tôi đã phân tích và so sánh chi tiết hai phương án kỹ thuật:

| Tiêu chí so sánh | Phương án A: Dùng taxonomy `product_brand` | Phương án B: Dùng Global Attribute `pa_brand` (CHỌN) |
| :--- | :--- | :--- |
| **Nguồn gốc** | WooCommerce Core Brands feature | WooCommerce Core Product Attributes |
| **Cấu trúc trong CSV** | Không khớp (CSV đặt là `Attribute 3 name = Brand`) | **Khớp 100%** (`Attribute 3 name = Brand`, `global = 1`) |
| **Cần sửa CSV?** | Có (phải đổi header thành `Taxonomy: product_brand`) | **Không** (giữ nguyên vẹn 100% CSV nguồn) |
| **Tương thích Block Theme** | Cần widget/block riêng của Brands | **Tương thích hoàn hảo** với Gutenberg Block "Filter by Attribute" |
| **Tính đồng nhất hệ thống** | Tách rời 1 taxonomy riêng và 2 global attributes | **Đồng nhất 100%**: Cả 3 tiêu chí lọc (`Color`, `Size`, `Brand`) đều là Attribute |

> [!IMPORTANT]
> **QUYẾT ĐỊNH CHÍNH THỨC:** Đề xuất chọn duy nhất **Phương án B (`pa_brand`)**.  
> Lý do: Giữ nguyên vẹn tệp CSV nguồn mà không cần đổi tên cột, đảm bảo bộ lọc thuộc tính trong Block Theme Twenty Twenty-Five hoạt động đồng nhất cho cả Màu sắc, Kích thước và Thương hiệu.

---

## 4. CSV-to-WooCommerce Field Mapping Lock (Khóa Bảng Ánh xạ)

Toàn bộ 28 cột của tệp `woocommerce_furniture_dataset_22_products.csv` được khóa ánh xạ chính xác như sau:

| STT | Cột trong CSV | Trường WooCommerce mục tiêu | Xử lý dữ liệu đặc biệt |
| :---: | :--- | :--- | :--- |
| 1 | `Type` | `type` | Chuyển thành `simple`, `variable`, `variation` |
| 2 | `SKU` | `sku` | Mã định danh duy nhất (Unique ID) |
| 3 | `Name` | `post_title` | Tên sản phẩm / Tên biến thể |
| 4 | `Published` | `post_status` | `1` → `publish` |
| 5 | `Is featured?` | `featured` | `1` → Featured Product (10 sản phẩm cha) |
| 6 | `Visibility in catalog` | `catalog_visibility` | `visible` → Hiển thị cả Shop và Search |
| 7 | `Short description` | `post_excerpt` | Đoạn mô tả ngắn |
| 8 | `Description` | `post_content` | Nội dung mô tả chi tiết |
| 9 | `Regular price` | `regular_price` | Giá niêm yết (USD). Variable cha để trống |
| 10 | `Sale price` | `sale_price` | Giá khuyến mãi (USD). 10 dòng có giá sale |
| 11 | `In stock?` | `manage_stock` & `stock_status` | `1` → `instock`; `0` → `outofstock` |
| 12 | `Stock` | `stock_quantity` | Số lượng tồn kho (0 đối với FURN-010, FURN-012) |
| 13 | `Categories` | Taxonomy `product_cat` | Dấu phẩy phân tách nhiều danh mục (VD: `Beds, Storage`) |
| 14 | `Tags` | Taxonomy `product_tag` | Dấu phẩy phân tách các tag nội thất |
| 15 | `Images` | `images` / `_thumbnail_id` + gallery | Ảnh 1: Ảnh đại diện; Ảnh 2 (nếu có): Thư viện ảnh gallery |
| 16 | `Parent` | `parent_id` (via Parent SKU) | Ánh xạ biến thể con về đúng SKU của sản phẩm cha |
| 17-20 | `Attribute 1 ...` | Global Attribute `pa_color` | Tên: `Color`, Giá trị: `Black, White, Brown`, Visible: 1, Global: 1 |
| 21-24 | `Attribute 2 ...` | Global Attribute `pa_size` | Tên: `Size`, Giá trị: `S, M, L`, Visible: 1, Global: 1 |
| 25-28 | `Attribute 3 ...` | Global Attribute `pa_brand` | Tên: `Brand`, Giá trị: Tên 5 thương hiệu, Visible: 1, Global: 1 |

---

## 5. Phương án xử lý hai URL ảnh bị lỗi 404 (`FURN-006` và `FURN-018`)

### 5.1. Bằng chứng kiểm tra lỗi
- **`FURN-006` (Solid Pine Nightstand):**
  - URL: `https://images.unsplash.com/photo-1540518614846-7ede433c4577?auto=format&fit=crop&w=900&q=80`
  - Kết quả: **HTTP 404 Not Found** (Ảnh gốc đã bị gỡ khỏi Unsplash CDN).
- **`FURN-018` (Abstract Acoustic Wall Art Panel):**
  - URL: `https://images.unsplash.com/photo-1579783902614-a3fb3927b675?auto=format&fit=crop&w=900&q=80`
  - Kết quả: **HTTP 404 Not Found** (Ảnh gốc đã bị gỡ khỏi Unsplash CDN).

### 5.2. Trạng thái hiện tại
> **`REPLACEMENT NOT APPROVED`**  
> Theo nguyên tắc an toàn, hệ thống **tuyệt đối không tự ý thay thế** bằng một URL ngẫu nhiên nào khi chưa có sự phê duyệt rõ ràng từ người dùng.

### 5.3. Hai lựa chọn đề xuất trình User phê duyệt:
- **Lựa chọn 1 (Khuyến nghị):** Cung cấp 2 URL ảnh Unsplash nội thất thay thế chất lượng cao (cùng thể loại Tủ đầu giường bằng gỗ thông cho `FURN-006` và Tranh treo tường nghệ thuật tối giản cho `FURN-018`) để toàn bộ 22 sản phẩm hiển thị ảnh đầy đủ và đồng bộ thẩm mỹ.
- **Lựa chọn 2:** Giữ nguyên dữ liệu hiện tại khi import; chấp nhận `FURN-006` và `FURN-018` tạm thời chưa có ảnh đại diện và sẽ bổ sung ảnh thủ công sau trong trang quản trị.

---

## 6. Import và Retry Strategy (Chiến lược Thực thi & Chống trùng lặp)

### 6.1. Xác định công cụ thực thi khả dụng
- Lệnh CLI `wp wc product import` **không có sẵn** trong môi trường WP-CLI hiện tại.
- Do đó, có 2 phương án thực thi đã được kiểm chứng về mặt mã nguồn:
  1. **Phương án Giao diện (WP Admin Importer):** Truy cập `http://localhost:10010/wp-admin/edit.php?post_type=product&page=product_importer`, chọn file CSV và tích chọn *"Update existing products"*.
  2. **Phương án Tự động hóa có kiểm soát (Scripted Runner via `wp eval-file`):** Sử dụng trực tiếp class `WC_Product_CSV_Importer` của WooCommerce Core để nạp theo lô mà không sợ timeout trình duyệt.

### 6.2. Cơ chế chống trùng lặp dữ liệu (De-duplication / Retry Safety)
1. **Duy nhất hóa qua SKU:** Toàn bộ 64 dòng đều có SKU độc nhất.
2. **Cờ "Update existing products":** Khi kích hoạt tính năng này, nếu quá trình import bị gián đoạn giữa chừng và phải chạy lại, trình nhập liệu sẽ cập nhật sản phẩm đã có dựa trên mã SKU thay vì tạo ra sản phẩm thứ hai bị trùng lặp.
3. **Cơ chế xử lý ảnh (Attachment deduplication):** Vì có 23 ảnh độc nhất được chia sẻ giữa các biến thể, khi nạp cần kiểm tra nếu ảnh đã tồn tại trong Media Library thì gắn ID ảnh cũ thay vì tải lại nhiều lần.

---

## 7. Backup & Rollback Limitations (Giới hạn Sao lưu & Phục hồi)

- **Tệp backup SQL hiện có:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\backups\stage_1a_baseline_backup.sql` (1,202,692 bytes).
- **Ghi chú bắt buộc:**
  > **`DATABASE RESTORE: NOT VERIFIED`**  
  > Tệp backup SQL được xác nhận toàn vẹn về mặt cấu trúc tệp (52 bảng), nhưng chưa từng chạy lệnh restore trên môi trường database thực tế.
- **Kế hoạch Rollback trước khi nạp dữ liệu (Pre-import Safeguard):**
  1. Ngay trước khi import, xuất một bản snapshot dữ liệu: `backups/stage_1e_pre_import_clean_db.sql`.
  2. Nếu import thất bại hoặc dữ liệu bị lỗi, chỉ cần xóa các bản ghi sản phẩm bằng lệnh WP-CLI:
     ```powershell
     # Lệnh rollback dọn dẹp an toàn không cần restore toàn bộ DB:
     # wp post delete $(wp post list --post_type=product,product_variation --format=ids) --force
     ```
     Phương pháp này an toàn hơn nhiều so với việc restore toàn bộ database vì không ảnh hưởng đến cấu hình WordPress options hay users.

---

## 8. Acceptance Criteria Matrix (Ma trận tiêu chí chấp thuận Stage 1E.2.1)

| Tiêu chí | Nội dung kiểm tra | Bằng chứng thực tế | Đánh giá |
| :--- | :--- | :--- | :---: |
| **AC-1** | Có bằng chứng về nguồn gốc taxonomy thương hiệu | Xác minh class `Internal\Brands` trong WooCommerce Core | **PASS** |
| **AC-2** | Quyết định đề xuất cho Color, Size và Brand | Khóa `pa_color`, `pa_size` và duy nhất `pa_brand` | **PASS** |
| **AC-3** | Phương án xử lý rõ ràng cho 2 ảnh lỗi 404 | Xác nhận 404; ghi nhận `REPLACEMENT NOT APPROVED` | **PASS** |
| **AC-4** | Chiến lược import phù hợp với công cụ thực tế | Xác nhận WP-CLI không có lệnh import, chọn UI/Core Runner | **PASS** |
| **AC-5** | Phương án tránh trùng lặp khi import gián đoạn | Ánh xạ SKU và kích hoạt cờ Update Existing Products | **PASS** |
| **AC-6** | Quy trình backup/rollback và disclaimer | Ghi rõ: `DATABASE RESTORE: NOT VERIFIED` | **PASS** |
| **AC-7** | Không có thay đổi database, CSV, plugin, Git | Giữ nguyên trạng 100% hệ thống (Read-Only) | **PASS** |

---

## 9. Các quyết định cần Người dùng Phê duyệt (Decisions for User Approval)

Trước khi chuyển sang **Stage 1E.3 (Thực hiện Import có kiểm soát)**, vui lòng cho biết quyết định của bạn đối với 3 vấn đề sau:

1. **Phê duyệt Taxonomy Thương hiệu:**  
   Chấp thuận sử dụng **`pa_brand`** (Global Attribute) để đồng bộ 100% với CSV và bộ lọc Block Theme Twenty Twenty-Five? *(Đồng ý / Không đồng ý)*
2. **Phê duyệt Xử lý 2 ảnh Unsplash bị 404 (`FURN-006` và `FURN-018`):**  
   - **Lựa chọn 1:** Cho phép trợ lý cung cấp 2 link ảnh Unsplash mới cùng thể loại nội thất (Tủ đầu giường và Tranh tường) để import hiển thị đẹp 100%.  
   - **Lựa chọn 2:** Giữ nguyên CSV gốc, chấp nhận 2 sản phẩm này tạm thời chưa có ảnh và bổ sung sau trong Admin.
3. **Phê duyệt tạo 3 Global Attributes trong WooCommerce:**  
   Cho phép tạo 3 thuộc tính toàn cục (`pa_color`, `pa_size`, `pa_brand`) ngay trước khi chạy lệnh import? *(Đồng ý / Không đồng ý)*

---

## 10. Final Stage-Gate Decision: PASS WITH CONDITIONS

### Quyết định: **PASS WITH CONDITIONS**

**Điều kiện:**
1. Toàn bộ kiến trúc dữ liệu, ánh xạ trường và chiến lược import đã được khóa an toàn và logic.
2. Công cụ thực thi thực tế đã được kiểm chứng (không sử dụng các lệnh giả định).
3. **Dừng toàn bộ hoạt động để chờ phê duyệt rõ ràng từ người dùng đối với 3 quyết định tại Mục 9.**

---

### QUY TẮC DỪNG BẮT BUỘC:
- **ĐÃ DỪNG TOÀN BỘ HOẠT ĐỘNG.**
- **KHÔNG CHẠY IMPORTER.**
- **KHÔNG TẠO SẢN PHẨM HOẶC THUỘC TÍNH.**
- **KHÔNG TỰ Ý CHUYỂN SANG STAGE 1E.3.**
- **CHỜ PHÊ DUYỆT TỪ NGƯỜI DÙNG.**
