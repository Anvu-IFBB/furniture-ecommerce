# STAGE 1E.1 — PRODUCT DATASET AUDIT & NORMALIZATION PLAN

> **Môi trường:** Local WordPress (Windows 11)  
> **WordPress Root:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public`  
> **Git Root:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\wp-content`  
> **Tệp dữ liệu nguồn:** `.../wp-content/plugins/woocommerce_furniture_dataset_22_products.csv`  
> **Thời điểm thẩm định:** 2026-10-16 15:22:00+07:00  
> **Số lượng dòng dữ liệu:** 64 dòng sản phẩm (12 Simple + 10 Variable + 42 Variations) + 1 dòng Header  
> **Trạng thái cấu trúc dữ liệu:** Chuẩn WooCommerce CSV Importer Schema (28 cột)  
> **Quyết định Stage-Gate:** **PASS WITH CONDITIONS** (Cấu trúc dữ liệu hoàn hảo, không có lỗi logic/SKU/Giá blocker; cần phê duyệt chiến lược xử lý ảnh Unsplash và khởi tạo thuộc tính toàn cục trước khi import thực tế)

---

## 1. Executive Summary (Tóm tắt điều hành)

Báo cáo **Stage 1E.1** thực hiện kiểm định chuyên sâu (Data Forensic Audit) đối với bộ dữ liệu sản phẩm `woocommerce_furniture_dataset_22_products.csv` trước khi tiến hành nạp vào WooCommerce.

### Các kết quả then chốt:
1. **Tính toàn vẹn tệp:** File CSV có dung lượng **26,176 bytes**, mã băm SHA-256: `276809899064949CB8D45B8EF7523B8C2913780F0649650EA4EB494FB74894F6`, định dạng UTF-8 không BOM, chuẩn 28 cột của trình nhập liệu WooCommerce Core Importer.
2. **Cơ cấu dữ liệu:**
   - **Tổng số dòng dữ liệu:** 64 dòng (không tính header).
   - **Sản phẩm cha (Parent Products):** 22 sản phẩm (Vượt yêu cầu tối thiểu 20 sản phẩm của đề bài).
     - **12 Simple Products:** `FURN-001` đến `FURN-012` (Gồm 10 sản phẩm giá thường, 2 sản phẩm giảm giá `FURN-011`, `FURN-012`).
     - **10 Variable Products:** `FURN-013` đến `FURN-022` (Gồm 8 sản phẩm giá thường, 2 sản phẩm giảm giá `FURN-021`, `FURN-022`).
   - **Dòng biến thể con (Variations):** 42 dòng biến thể (FURN-013 có 6 biến thể; 9 sản phẩm còn lại mỗi loại có 4 biến thể).
3. **Độ sạch của dữ liệu (Data Hygiene):**
   - **SKU:** 64/64 SKU duy nhất, không trùng lặp, không có SKU trống.
   - **Liên kết biến thể (Parent-Child):** 100% biến thể trỏ đúng mã SKU cha tồn tại (`Parent`), không có biến thể mồ côi (orphan variations), không có sản phẩm variable nào thiếu biến thể.
   - **Tổ hợp thuộc tính:** 100% biến thể có cặp thuộc tính `Color × Size` độc nhất, không trùng lặp tổ hợp.
   - **Giá bán & Khuyến mãi:** 100% giá hợp lệ nằm trong dải `$45.0 — $499.0` (khớp hoàn hảo với bộ lọc trượt giá `$0 — $500` của đề bài). Không có sản phẩm nào có `Sale price >= Regular price` hoặc giá âm.
   - **Tồn kho:** Trạng thái `In stock?` và số lượng `Stock` khớp logic 100%. Đã cấu hình sẵn 2 sản phẩm hết hàng (`FURN-010` và `FURN-012` có `Stock = 0`, `In stock? = 0`) để phục vụ kiểm thử bộ lọc `Availability`.
4. **Cảnh báo quan trọng cần xử lý trước khi import:**
   - **URL Ảnh:** Toàn bộ 64 dòng sử dụng ảnh trực tuyến từ `images.unsplash.com` (33 URL độc nhất). Do môi trường Local/sandbox có thể bị chặn hoặc hạn chế mạng tải ảnh tự động, cần có phương án xử lý nạp ảnh dự phòng để tránh việc WooCommerce Importer bị timeout khi tải ảnh ngoài.
   - **Thuộc tính toàn cục:** Trước khi import, cần khởi tạo 3 thuộc tính toàn cục trong WooCommerce (`pa_color`, `pa_size`, `pa_brand`) để đảm bảo bộ lọc Layered Nav và Swatches hoạt động chính xác.

---

## 2. File Metadata & SHA-256 Checksum

| Thông số | Giá trị thực tế xác minh |
| :--- | :--- |
| **Đường dẫn tệp** | `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\wp-content\plugins\woocommerce_furniture_dataset_22_products.csv` |
| **Kích thước tệp** | **26,176 bytes** (~25.56 KB) |
| **Thời điểm sửa đổi** | `2026-10-15 17:47:25` |
| **Mã băm SHA-256** | `276809899064949CB8D45B8EF7523B8C2913780F0649650EA4EB494FB74894F6` |
| **Định dạng mã hóa (Encoding)** | `UTF-8 without BOM` (hoặc ASCII tương thích) |
| **Ký tự phân cách (Delimiter)** | Dấu phẩy `,` (Comma-separated values) |
| **Tổng số dòng tệp** | **65 dòng** (1 Header + 64 Data Rows) |

---

## 3. CSV Schema (Đặc tả 28 cột dữ liệu)

Tệp dữ liệu tuân thủ chuẩn 100% cấu trúc xuất/nhập của WooCommerce Core Product CSV Importer:

| STT | Tên cột (Header) | Kiểu dữ liệu | Mục đích sử dụng |
| :---: | :--- | :---: | :--- |
| 1 | `Type` | String | Phân loại: `simple`, `variable`, `variation` |
| 2 | `SKU` | String | Mã định danh duy nhất (VD: `FURN-001`, `FURN-013-BLK-S`) |
| 3 | `Name` | String | Tên sản phẩm / Tên biến thể hiển thị |
| 4 | `Published` | Integer | `1` (Hiển thị công khai) |
| 5 | `Is featured?` | Integer | `1` (Nổi bật - 10 SP) / `0` (Bình thường - 54 dòng) |
| 6 | `Visibility in catalog` | String | `visible` (Hiển thị đầy đủ trong Shop & Search) |
| 7 | `Short description` | Text/HTML | Mô tả ngắn gọn sản phẩm |
| 8 | `Description` | Text/HTML | Mô tả chi tiết kỹ thuật, vật liệu, xuất xứ |
| 9 | `Regular price` | Float | Giá niêm yết (USD) |
| 10 | `Sale price` | Float | Giá khuyến mãi (USD) |
| 11 | `In stock?` | Integer | `1` (Còn hàng) / `0` (Hết hàng) |
| 12 | `Stock` | Integer | Số lượng tồn kho thực tế |
| 13 | `Categories` | String | Danh mục (phân cách bằng dấu phẩy) |
| 14 | `Tags` | String | Thẻ từ khóa nội thất (phân cách bằng dấu phẩy) |
| 15 | `Images` | URL | Đường dẫn ảnh đại diện Unsplash |
| 16 | `Parent` | String | Mã SKU cha (dành riêng cho dòng `variation`) |
| 17 | `Attribute 1 name` | String | Tên thuộc tính 1 (`Color`) |
| 18 | `Attribute 1 value(s)` | String | Giá trị thuộc tính 1 (`Black`, `White`, `Brown`) |
| 19 | `Attribute 1 visible` | Integer | `1` (Hiển thị trên trang chi tiết) |
| 20 | `Attribute 1 global` | Integer | `1` (Thuộc tính toàn cục taxonomy) |
| 21 | `Attribute 2 name` | String | Tên thuộc tính 2 (`Size`) |
| 22 | `Attribute 2 value(s)` | String | Giá trị thuộc tính 2 (`S`, `M`, `L`) |
| 23 | `Attribute 2 visible` | Integer | `1` (Hiển thị trên trang chi tiết) |
| 24 | `Attribute 2 global` | Integer | `1` (Thuộc tính toàn cục taxonomy) |
| 25 | `Attribute 3 name` | String | Tên thuộc tính 3 (`Brand`) |
| 26 | `Attribute 3 value(s)` | String | Giá trị thương hiệu (`NordicLiving`, `Minimalia`...) |
| 27 | `Attribute 3 visible` | Integer | `1` (Hiển thị trên trang chi tiết) |
| 28 | `Attribute 3 global` | Integer | `1` (Thuộc tính toàn cục taxonomy) |

---

## 4. Tổng quan số lượng Parent / Simple / Variable / Variation

| Phân loại đối tượng | Số lượng dòng | Tỷ lệ (%) | Ghi chú kỹ thuật |
| :--- | :---: | :---: | :--- |
| **Simple Products (Đơn giản)** | **12** | 18.75% | `FURN-001` đến `FURN-012` |
| **Variable Products (Cha có biến thể)**| **10** | 15.625%| `FURN-013` đến `FURN-022` |
| **Tổng số Sản phẩm gốc (Parent)** | **22** | **34.375%**| **Vượt chỉ tiêu tối thiểu 20 sản phẩm** |
| **Variations (Biến thể con)** | **42** | 65.625%| Các dòng biến thể kích thước × màu sắc |
| **Tổng cộng các dòng dữ liệu** | **64** | **100%** | Khớp chính xác tệp CSV nguồn |

---

## 5. Bảng thống kê chi tiết từng Product Type

### 5.1. Nhóm 12 Sản phẩm Đơn giản (Simple Products)
| SKU | Tên sản phẩm | Danh mục | Thương hiệu | Giá gốc | Giá Sale | Tồn kho | Trạng thái |
| :--- | :--- | :--- | :--- | :---: | :---: | :---: | :---: |
| `FURN-001` | Nordic Oak Dining Chair | Chairs | NordicLiving | $120 | — | 25 | In stock (Featured) |
| `FURN-002` | Minimalist Metal Bar Stool | Chairs | Minimalia | $85 | — | 30 | In stock |
| `FURN-003` | Scandic Round Coffee Table | Tables | NordicLiving | $210 | — | 15 | In stock (Featured) |
| `FURN-004` | Compact Walnut Side Table | Tables | OakCraft | $95 | — | 20 | In stock |
| `FURN-005` | Velvet Pouf Ottoman | Sofas | LuxeVibe | $110 | — | 18 | In stock |
| `FURN-006` | Solid Pine Nightstand | Beds, Storage | OakCraft | $140 | — | 12 | In stock |
| `FURN-007` | Matte Black Pendant Lamp | Lighting | UrbanoHaus | $75 | — | 40 | In stock (Featured) |
| `FURN-008` | Minimalist Ceramic Table Lamp | Lighting | Minimalia | $65 | — | 35 | In stock |
| `FURN-009` | Minimalist Ceramic Vase Set | Decoration | NordicLiving | $45 | — | 50 | In stock |
| `FURN-010` | Modular Floating Wall Shelf | Storage, Decoration | UrbanoHaus | $130 | — | **0** | **Out of stock** |
| `FURN-011` | Ergonomic Mesh Office Chair | Chairs | Minimalia | $250 | **$199** | 16 | **On Sale** (Featured) |
| `FURN-012` | Boho Woven Floor Mirror | Decoration | LuxeVibe | $180 | **$129** | **0** | **On Sale & Out of stock** |

### 5.2. Nhóm 10 Sản phẩm Biến thể (Variable Products) & Biến thể con
| SKU Cha | Tên sản phẩm cha | Danh mục | Thương hiệu | Số biến thể | Dải giá biến thể | Trạng thái |
| :--- | :--- | :--- | :--- | :---: | :---: | :---: |
| `FURN-013` | Modern Lounge Chair | Chairs | NordicLiving | **6** | $299 — $399 | In stock (Featured) |
| `FURN-014` | Extendable Solid Oak Dining Table | Tables | OakCraft | **4** | $380 — $490 | In stock (Featured) |
| `FURN-015` | Cloud Modular L-Shape Sofa | Sofas | LuxeVibe | **4** | $450 — $499 | In stock (Featured) |
| `FURN-016` | Upholstered Linen Platform Bed | Beds | NordicLiving | **4** | $399 — $499 | In stock (Featured) |
| `FURN-017` | Arc Floor Lamp with Marble Base | Lighting | UrbanoHaus | **4** | $189 — $259 | In stock |
| `FURN-018` | Abstract Acoustic Wall Art Panel | Decoration | Minimalia | **4** | $120 — $160 | In stock |
| `FURN-019` | Rattan Double-Door Wardrobe Cabinet | Storage, Beds | OakCraft | **4** | $420 — $490 | In stock |
| `FURN-020` | Industrial Steel Bookcase Rack | Storage | UrbanoHaus | **4** | $220 — $310 | In stock |
| `FURN-021` | Signature Velvet Accent Armchair | Chairs, Sofas | LuxeVibe | **4** | **$299 — $349** *(Gốc $399-$459)* | **On Sale** (Featured) |
| `FURN-022` | Smart LED Bedside Storage Frame | Beds, Storage | Minimalia | **4** | **$245 — $294** *(Gốc $350-$420)* | **On Sale** (Featured) |

---

## 6. Bảng kiểm tra lỗi dữ liệu (Data Error Audit Table)

Tiến hành kiểm thử tự động toàn diện qua script phân tích:

| Hạng mục kiểm tra | Tiêu chuẩn chất lượng | Kết quả thực tế | Số lỗi | Mức độ |
| :--- | :--- | :--- | :---: | :---: |
| **SKU trống** | Mọi dòng phải có SKU | 64/64 dòng có SKU | 0 | Không có |
| **SKU trùng lặp** | SKU phải duy nhất toàn hệ thống | 64 SKU duy nhất tuyệt đối | 0 | Không có |
| **Biến thể mồ côi (Orphan)** | `Parent` phải trỏ đúng SKU cha | 42/42 biến thể trỏ đúng 10 SKU variable | 0 | Không có |
| **Sản phẩm cha thiếu biến thể** | Variable product phải có >= 1 biến thể | Cả 10 variable đều có từ 4 đến 6 biến thể | 0 | Không có |
| **Trùng lặp tổ hợp biến thể** | Cùng cha không được trùng `Color × Size` | Không có tổ hợp trùng trong cùng cha | 0 | Không có |
| **Lỗi logic giá (Sale >= Regular)**| `Sale price` phải nhỏ hơn `Regular price` | 10 dòng có giá Sale đều nhỏ hơn Regular | 0 | Không có |
| **Giá âm hoặc không phải số** | Giá phải là số thực dương | Dải giá từ 45.0 đến 499.0 | 0 | Không có |
| **Mâu thuẫn Tồn kho** | `In stock? = 0` thì `Stock = 0` | 2 sản phẩm hết hàng đều có `Stock = 0` | 0 | Không có |
| **Thiếu tên / mô tả** | Phải có Name, Short desc, Description | 100% dòng có đầy đủ nội dung | 0 | Không có |

> [!TIP]
> **KẾT LUẬN KIỂM ĐỊNH:** Tệp CSV **hoàn toàn sạch về mặt cấu trúc (Zero Structural/Logic Errors)**. Không có bất kỳ lỗi cú pháp nào gây chặn đứng (blocker) quá trình import.

---

## 7. Danh sách Cảnh báo & Các trường hợp cần lưu ý (Warnings & Risks)

Mặc dù file CSV chuẩn cấu trúc, có **3 vấn đề kỹ thuật quan trọng** cần lưu ý trước khi nạp vào WooCommerce:

### Cảnh báo 1: Nguồn ảnh sử dụng URL trực tuyến Unsplash (Mức độ: CAUTION)
- **Hiện trạng:** Cả 64 dòng dữ liệu đều chứa URL ảnh từ `https://images.unsplash.com/...`.
- **Rủi ro:** Khi sử dụng WooCommerce Native CSV Importer, WordPress sẽ thực hiện tải từng ảnh về thư mục `wp-content/uploads/` qua HTTP request. Nếu môi trường Local trên Windows gặp độ trễ mạng hoặc cấm kết nối ngoài, quá trình import có thể bị **Timeout / Execution Limit**.
- **Giải pháp:** Cần kiểm tra timeout của PHP (`max_execution_time`) hoặc cân nhắc import cấu trúc sản phẩm trước, sau đó đồng bộ media theo lô hoặc gắn ảnh đại diện.

### Cảnh báo 2: Phân tách danh mục đa tầng (Multiple Categories)
- **Hiện trạng:** Một số sản phẩm thuộc nhiều danh mục được ghi dưới dạng phân cách bằng dấu phẩy, ví dụ: `"Beds, Storage"`, `"Chairs, Sofas"`, `"Storage, Decoration"`.
- **Đánh giá:** WooCommerce CSV Importer mặc định hiểu dấu phẩy `,` là phân cách nhiều danh mục độc lập. Điều này giúp sản phẩm xuất hiện ở cả 2 danh mục khi lọc, hoàn toàn khớp với logic thương mại điện tử.

### Cảnh báo 3: Đăng ký thuộc tính toàn cục (Global Taxonomy Attributes)
- **Hiện trạng:** Các cột `Attribute 1 global`, `Attribute 2 global`, `Attribute 3 global` đều được đặt giá trị `1`.
- **Yêu cầu kỹ thuật:** Để các bộ lọc WooCommerce (`Filter by Color`, `Filter by Size`, `Filter by Brand`) hoạt động tự động trên Shop Sidebar, WooCommerce cần tạo taxonomy `pa_color`, `pa_size`, `pa_brand`. Nếu import trực tiếp mà chưa có taxonomy, WooCommerce vẫn tự động tạo, nhưng cần xác minh nhãn và slug sau import.

---

## 8. Kết quả đối chiếu với Tài liệu dự án (Cross-Doc Reconciliation)

Đối chiếu chéo 3 nguồn tài liệu gốc:

| Tiêu chí dự án | Yêu cầu trong PDF đề bài gốc | Đặc tả trong Project & Test Plan | Dữ liệu thực tế trong CSV | Đánh giá khớp nối |
| :--- | :--- | :--- | :--- | :---: |
| **Số lượng sản phẩm** | Tối thiểu 20 sản phẩm | 22 sản phẩm gốc (kèm biến thể) | **22 sản phẩm gốc** (12 Simple + 10 Variable) | **KHỚP 100%** (Vượt chuẩn) |
| **Số dòng biến thể** | Phải có sản phẩm biến thể | Kế hoạch cũ ước tính 48 dòng | **Thực tế CSV có 42 dòng biến thể** | **GHI NHẬN SAI LỆCH VĂN BẢN** |
| **Danh mục sản phẩm** | Bàn, ghế, sofa, giường, đèn, tủ... | Đủ 7 danh mục: `Chairs`, `Tables`, `Sofas`, `Beds`, `Lighting`, `Decoration`, `Storage` | Đủ 7 danh mục chuẩn xác | **KHỚP 100%** |
| **Thương hiệu nội thất** | Có thương hiệu để lọc | 5 thương hiệu: `NordicLiving`, `Minimalia`, `OakCraft`, `UrbanoHaus`, `LuxeVibe` | Đủ 5 thương hiệu phân bổ đều | **KHỚP 100%** |
| **Dải giá niêm yết** | Bộ lọc giá `$0 — $500` | Giá niêm yết bằng USD (`$`) | Giá từ `$45` đến `$499` (USD) | **KHỚP 100%** |
| **Sản phẩm Out-of-stock**| Kiểm tra bộ lọc còn/hết hàng | 2 sản phẩm hết hàng (`FURN-010`, `FURN-012`) | Đúng `FURN-010` và `FURN-012` có `Stock = 0` | **KHỚP 100%** |
| **Sản phẩm On Sale** | Kiểm tra hiển thị Sale badge | 4 sản phẩm On Sale (2 Simple + 2 Variable) | Đúng 4 sản phẩm có giá khuyến mãi | **KHỚP 100%** |

> [!NOTE]
> **Ghi nhận sai lệch tài liệu:** Trong bảng Section 2 của `wordpress_woocommerce_ecommerce_project_plan_refined (1).md` có dòng ghi *"48 dòng biến thể con = 70 dòng dữ liệu"*. Tuy nhiên, tệp CSV thực tế được tạo và chốt từ đầu là **42 dòng biến thể con + 22 sản phẩm cha = 64 dòng dữ liệu (+ 1 dòng header = 65 lines)**. Sự sai lệch này không ảnh hưởng đến nghiệp vụ vì 10 sản phẩm variable đều có đầy đủ biến thể hoàn chỉnh (`Color × Size`).

---

## 9. Import Readiness Matrix (Ma trận sẵn sàng nạp dữ liệu)

| Tiêu chí đánh giá | Trạng thái kỹ thuật | Đánh giá mức độ sẵn sàng |
| :--- | :---: | :---: |
| **1. Cấu trúc Schema & Cột** | 28 cột chuẩn WooCommerce Importer | **PASS** |
| **2. Định danh SKU & Tính duy nhất** | 64 SKU duy nhất, không trùng lặp | **PASS** |
| **3. Phân loại Simple / Variable / Variation** | Logic quan hệ Parent-Child chuẩn xác | **PASS** |
| **4. Giá bán & Khuyến mãi (USD)** | Giá hợp lệ, nằm trong dải lọc $0-$500 | **PASS** |
| **5. Thuộc tính & Biến thể (`Color × Size`)** | 100% biến thể có cặp thuộc tính duy nhất | **PASS** |
| **6. Danh mục & Thương hiệu** | Đủ 7 danh mục và 5 thương hiệu mẫu | **PASS** |
| **7. Trạng thái Tồn kho & Sale** | Đúng kịch bản kiểm thử (2 OOS, 4 Sale) | **PASS** |
| **8. Nguồn ảnh đại diện (Unsplash URLs)** | URL hợp lệ nhưng là remote HTTP link | **NEEDS REVIEW** (Cần theo dõi mạng khi import) |
| **9. Kiểm tra thực nghiệm Import** | Chưa chạy import vào database | **NOT VERIFIED** |

---

## 10. Đề xuất Kế hoạch Chuẩn hóa & Nạp dữ liệu (Stage 1E.2 Plan)

*(Chỉ đề xuất phương án tối ưu, tuyệt đối chưa thực thi trong Stage 1E.1)*

Quy trình 5 bước được khuyến nghị cho Stage 1E.2:
1. **Bước 1 — Pre-import Snapshot:** Tạo một bản sao lưu database nhanh hoặc checkpoint trạng thái bảng sản phẩm WooCommerce (`wp_posts`, `wp_postmeta`, `wp_wc_product_meta_lookup`).
2. **Bước 2 — Cấu hình tiền tệ & Thuộc tính cốt lõi:**
   - Đảm bảo WooCommerce Base Currency đang là **USD (`$`)**.
   - Khởi tạo trước 3 Global Attributes: `Color` (`pa_color`), `Size` (`pa_size`), `Brand` (`pa_brand`).
3. **Bước 3 — Dry-run / Kiểm tra nhập mẫu:** Sử dụng WP-CLI hoặc công cụ import WooCommerce để nạp thử 1 sản phẩm simple (`FURN-001`) và 1 sản phẩm variable (`FURN-013`) để kiểm tra tốc độ tải ảnh và tính chính xác của biến thể.
4. **Bước 4 — Batch Import toàn bộ 22 sản phẩm:** Chạy import toàn bộ tệp CSV, theo dõi tiến trình để không bị gián đoạn.
5. **Bước 5 — Post-import Verification:** Chạy kiểm toán database để xác nhận đủ 22 sản phẩm cha, 42 biến thể, đủ danh mục và bộ lọc hoạt động.

---

## 11. Các giới hạn kiểm tra (Audit Limitations)

1. **Giới hạn kiểm tra Runtime Media:** Báo cáo này kiểm tra cú pháp và định dạng chuỗi URL ảnh trong CSV, nhưng chưa thực hiện gửi request HTTP tải ảnh về máy chủ Local (theo quy tắc cấm tải dữ liệu từ internet trong giai đoạn này).
2. **Chưa nạp vào Cơ sở dữ liệu:** Chưa có sản phẩm nào được tạo trong database WordPress, đảm bảo nguyên tắc Read-Only an toàn tuyệt đối.

---

## 12. Final Stage-Gate Decision: PASS WITH CONDITIONS

### Quyết định: **PASS WITH CONDITIONS**

**Điều kiện:**
1. Bộ dữ liệu `woocommerce_furniture_dataset_22_products.csv` đạt chuẩn xuất sắc về mặt cấu trúc và chất lượng dữ liệu (100% hợp lệ, không có lỗi blocker).
2. Đã làm rõ sự sai lệch số lượng dòng biến thể (42 biến thể thực tế vs 48 biến thể ghi chú trong text dự án cũ).
3. Cần phê duyệt kế hoạch xử lý ảnh Unsplash và chuẩn bị môi trường trước khi tiến hành import thực tế trong **Stage 1E.2**.

---

### QUY TẮC DỪNG BẮT BUỘC:
- **ĐÃ DỪNG TOÀN BỘ HOẠT ĐỘNG.**
- **KHÔNG CHẠY TRÌNH NHẬP LIỆU WOOCOMMERCE.**
- **KHÔNG TẠO HOẶC SỬA SẢN PHẨM TRONG DATABASE.**
- **KHÔNG THAY ĐỔI CẤU HÌNH WOOCOMMERCE.**
- **KHÔNG COMMIT GIT.**
- **CHỜ PHÊ DUYỆT TỪ NGƯỜI DÙNG ĐỂ CHUYỂN SANG STAGE 1E.2.**
