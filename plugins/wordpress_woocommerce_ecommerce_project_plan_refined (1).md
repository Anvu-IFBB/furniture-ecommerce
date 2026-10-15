# Kế hoạch triển khai Website E-commerce Nội thất Quốc tế (WordPress + WooCommerce)

**Dự án:** Website bán nội thất trực tuyến tại thị trường Việt Nam bằng WordPress + WooCommerce  
**Loại tài liệu:** Đặc tả kiến trúc, kế hoạch triển khai và quản trị dự án (Đã tách riêng kế hoạch kiểm thử sang `Test_Plan_and_Test_Cases.md` và kèm file dữ liệu `woocommerce_furniture_dataset_22_products.csv`)  
**Phiên bản:** 3.0 (Bản chính thức — Đã khóa toàn bộ 6 quyết định kỹ thuật & dữ liệu)  
**Ngày lập:** 15/10/2026  
**Đối tượng:** Thực tập sinh (TTS), Tech Lead / Mentor và Chủ dự án (MaiATech)  
**Trạng thái:** Đã chốt toàn bộ phương án — Sẵn sàng triển khai

---

## 1. Tóm tắt điều hành — Kiến trúc WordPress + WooCommerce kết hợp WoodMart Child Theme, đa tiền tệ (USD/VND) và bộ dữ liệu chuẩn 22 sản phẩm

Dự án xây dựng website thương mại điện tử cho một thương hiệu nội thất nước ngoài gia nhập thị trường Việt Nam, sử dụng **WordPress + WooCommerce** trên nền tảng theme **[WoodMart](https://www.google.com/search?q=https%3A%2F%2Fthemeforest.net%2Fitem%2Fwoodmart-woocommerce-wordpress-theme%2F20264492)** (dựng lại layout riêng qua Child Theme, tuyệt đối không giữ nguyên bản demo import). Trọng tâm đánh giá của bài toán nằm ở khả năng **quản lý và render dữ liệu động** (biến thể sản phẩm `Color × Size`, bộ lọc AJAX thực, tìm kiếm Live Search, giỏ hàng và luồng thanh toán chuẩn WooCommerce).

Hệ thống bao gồm đầy đủ 11 hạng mục chức năng cốt lõi từ PDF gốc: Trang giới thiệu thương hiệu (`/about-us`), Trang chủ (`/`), Danh mục sản phẩm (`/shop`), Tìm kiếm (AJAX Live Search), Bộ lọc thực (Filter theo Category, Brand, Color, Size, Price, Availability), Chi tiết sản phẩm (Product Detail) có biến thể (Variable Product), Giỏ hàng (`/cart`), Thanh toán (`/checkout`), Tài khoản khách hàng (`/my-account`), Danh sách yêu thích (`/wishlist`), Blog (`/blog`) và hiển thị tương thích trên 3 thiết bị (`1440×900`, `768×1024`, `390×844`).

Toàn bộ chỉ tiêu kiểm thử và 28 ca kiểm thử (Test Cases `TC01–TC28`) được quản lý độc lập tại tài liệu `Test_Plan_and_Test_Cases.md`, đi kèm bộ dữ liệu nguồn `woocommerce_furniture_dataset_22_products.csv` sẵn sàng nạp vào hệ thống.

---

## 2. Bảng Quyết định Kỹ thuật & Dữ liệu Đã Khóa (Architecture & Data Decisions)

Toàn bộ các điểm mâu thuẫn hoặc bỏ ngỏ trong tài liệu **“Xây dựng website E-commerce quốc tế.pdf” (13 trang)** đã được chủ dự án chốt chính thức theo 6 quyết định dưới đây:

| STT | Hạng mục quyết định | Phương án đã khóa chính thức | Cách thức triển khai cụ thể trong WordPress + WooCommerce |
|---|---|---|---|
| **1** | **Danh mục sản phẩm (Categories)** | **Tạo gộp đủ 7 danh mục** (`Chairs`, `Tables`, `Sofas`, `Beds`, `Lighting`, `Decoration`, `Storage`), hiển thị **6 danh mục chính** ngoài Trang chủ. | - Khởi tạo cả 7 `product_cat` trong WooCommerce để người chấm kiểm tra theo Trang 2 (có `Storage`) hay Trang 10–11 (có `Beds`) đều đạt 100%.<br>- Trên Homepage (Section Category), hiển thị Grid 6 danh mục chủ đạo; trên Mega Menu và Sidebar Filter tại `/shop`, hiển thị đầy đủ cả 7 danh mục. |
| **2** | **Cơ cấu Dataset Sản phẩm** | **Tổng cộng 22 sản phẩm gốc** (kèm 48 dòng biến thể con = **70 dòng dữ liệu trong file CSV**):<br>- **10 Simple Products** (giá thường)<br>- **8 Variable Products** (giá thường, biến đổi theo Size/Color)<br>- **2 Simple Sale Products** (đang giảm giá)<br>- **2 Variable Sale Products** (đang giảm giá) | - Đảm bảo dù người chấm đếm tách biệt hay đếm gộp (`10 Simple + 8 Variable + 2 Sale`) thì số lượng đều vượt chuẩn tối thiểu 20 sản phẩm.<br>- Mỗi Variable Product có đủ 2 thuộc tính toàn cục: `Color` (`Black, White, Brown`) và `Size` (`S, M, L`), mỗi biến thể có SKU, ảnh và mức giá riêng để khi chọn trên giao diện sẽ đổi ảnh và giá tức thời. |
| **3** | **Đơn vị tiền tệ (Currency) & Ngôn ngữ** | **Hỗ trợ song song cả 2 đơn vị tiền tệ `USD ($)` và `VND (₫)`** (Multi-currency Switcher) và hỗ trợ chuyển đổi ngôn ngữ Anh – Việt. | - Đặt **USD (`$`)** làm đơn vị tiền tệ cơ sở (Base Currency) để khớp 100% với các con số kiểm thử trong PDF (`$0 — $500`, `$299`, `$399`).<br>- Tích hợp bộ chuyển đổi tiền tệ (**FOX – Currency Switcher** hoặc **CURCY – Multi Currency for WooCommerce**) trên Top Bar / Header với tỷ giá cấu hình sẵn (`1 USD = 25,000 VND`) để khách tại Việt Nam bấm đổi sang **VNĐ (`₫`)** xem giá và đặt hàng trực tiếp. |
| **4** | **Theme nền tảng** | **Sử dụng Theme [WoodMart](https://www.google.com/search?q=https%3A%2F%2Fthemeforest.net%2Fitem%2Fwoodmart-woocommerce-wordpress-theme%2F20264492) (đã được cấp sẵn) + WoodMart Child Theme**, tự dựng lại layout từ đầu. | - Tận dụng các module gốc của WoodMart Core (AJAX Shop Filter, Attribute Swatches, Built-in Wishlist, Quick View, Buy Now button, Header Builder, Live AJAX Search) để không phải cài thêm nhiều plugin rời.<br>- Tuân thủ nghiêm ngặt Mục 3 Trang 10 PDF: Xây dựng lại layout Homepage, đổi bảng màu thương hiệu, đổi bộ font (Typography), thiết kế lại Header/Menu, tùy biến lại kiểu dáng Product Card và bố cục Product Detail. |
| **5** | **Dữ liệu Bộ lọc `Brand` & `Availability`** | **Khởi tạo sẵn 5 thương hiệu nội thất mẫu** và **cấu hình sẵn 2 sản phẩm `Out of stock` + 4 sản phẩm `On sale`** ngay trong file CSV Dataset. | - **5 Thương hiệu mẫu (`pa_brand`)**: `NordicLiving`, `UrbanoHaus`, `OakCraft`, `Minimalia`, `LuxeVibe` được gán đều cho 22 sản phẩm.<br>- **Trạng thái tồn kho (`Availability`)**: 20 sản phẩm ở trạng thái `In stock` (còn hàng) và **2 sản phẩm ở trạng thái `Out of stock` (hết hàng: `SKU FURN-010`, `SKU FURN-012`)** để khi người chấm tích lọc `In stock` hoặc `On sale` tại trang `/shop`, danh sách sản phẩm thay đổi thực sự. |
| **6** | **Phạm vi Kiểm thử (Test Report)** | **Giữ trọn bộ 28 Test Cases toàn diện (`TC01–TC28`)** trong file `Test_Plan_and_Test_Cases.md`. | - Bao gồm 19 Test Cases chức năng & responsive cốt lõi bám sát PDF (`TC01–TC19`) và 9 Test Cases mở rộng về Ngoại lệ (Negative), Phân quyền/Bảo mật (RBAC, IDOR, XSS) và Hiệu năng/Cache (`TC20–TC28`). |

---

## 3. Cấu trúc Chi tiết Bộ Dataset 22 Sản phẩm Nội thất (Đã xuất sẵn ra file CSV)

Bộ dữ liệu chuẩn đã được khởi tạo tại file `woocommerce_furniture_dataset_22_products.csv` theo đúng định dạng chuẩn của trình nhập liệu **WooCommerce Product CSV Importer** (`Products → All Products → Import`). Dưới đây là bảng danh mục 22 sản phẩm gốc trong Dataset:

| STT | Mã SKU | Tên sản phẩm | Phân loại chuẩn chấm bài | Danh mục (Category) | Thương hiệu (Brand) | Giá gốc (USD) | Giá Sale (USD) | Thuộc tính biến thể (`Color × Size`) | Tồn kho (Stock) |
|---:|---|---|---|---|---|---:|---:|---|---|
| 1 | `FURN-001` | Nordic Oak Dining Chair | Simple (1/10) | `Chairs` | NordicLiving | $120 | — | Color: Brown / Size: M | In stock (25) |
| 2 | `FURN-002` | Minimalist Metal Bar Stool | Simple (2/10) | `Chairs` | Minimalia | $85 | — | Color: Black / Size: S | In stock (30) |
| 3 | `FURN-003` | Scandic Round Coffee Table | Simple (3/10) | `Tables` | NordicLiving | $210 | — | Color: White / Size: M | In stock (15) |
| 4 | `FURN-004` | Compact Walnut Side Table | Simple (4/10) | `Tables` | OakCraft | $95 | — | Color: Brown / Size: S | In stock (20) |
| 5 | `FURN-005` | Velvet Pouf Ottoman | Simple (5/10) | `Sofas` | LuxeVibe | $110 | — | Color: White / Size: S | In stock (18) |
| 6 | `FURN-006` | Solid Pine Nightstand | Simple (6/10) | `Beds`, `Storage` | OakCraft | $140 | — | Color: Brown / Size: S | In stock (12) |
| 7 | `FURN-007` | Matte Black Pendant Lamp | Simple (7/10) | `Lighting` | UrbanoHaus | $75 | — | Color: Black / Size: M | In stock (40) |
| 8 | `FURN-008` | Warm Brass Table Lamp | Simple (8/10) | `Lighting` | LuxeVibe | $130 | — | Color: Brown / Size: S | In stock (22) |
| 9 | `FURN-009` | Ceramic Minimalist Vase Set | Simple (9/10) | `Decoration` | Minimalia | $45 | — | Color: White / Size: S | In stock (50) |
| 10 | `FURN-010` | Modular Floating Wall Shelf | Simple (10/10) | `Storage`, `Decoration` | UrbanoHaus | $65 | — | Color: Black / Size: M | **Out of stock (0)** |
| 11 | `FURN-011` | Ergonomic Mesh Office Chair | **Simple Sale (1/2)** | `Chairs` | UrbanoHaus | $250 | **$199** | Color: Black / Size: L | In stock (16) |
| 12 | `FURN-012` | Boho Woven Floor Mirror | **Simple Sale (2/2)** | `Decoration` | NordicLiving | $180 | **$129** | Color: Brown / Size: L | **Out of stock (0)** |
| 13 | `FURN-013` | **Modern Lounge Chair** *(Mẫu PDF)* | Variable (1/8) | `Chairs` | NordicLiving | $349 – $399 | — | `Black, White, Brown` × `S, M, L` (6 biến thể) | In stock (30) |
| 14 | `FURN-014` | Extendable Solid Oak Dining Table | Variable (2/8) | `Tables` | OakCraft | $380 – $480 | — | `Brown, White` × `M, L` (4 biến thể) | In stock (16) |
| 15 | `FURN-015` | Cloud Modular L-Shape Sofa | Variable (3/8) | `Sofas` | LuxeVibe | $420 – $499 | — | `White, Black, Brown` × `M, L` (6 biến thể) | In stock (14) |
| 16 | `FURN-016` | Upholstered Linen Platform Bed | Variable (4/8) | `Beds` | NordicLiving | $360 – $460 | — | `White, Brown` × `M, L` (4 biến thể) | In stock (12) |
| 17 | `FURN-017` | Arc Floor Lamp with Marble Base | Variable (5/8) | `Lighting` | UrbanoHaus | $160 – $220 | — | `Black, White` × `S, M, L` (6 biến thể) | In stock (24) |
| 18 | `FURN-018` | Abstract Acoustic Wall Art Panel | Variable (6/8) | `Decoration` | Minimalia | $90 – $150 | — | `Black, White, Brown` × `S, M` (6 biến thể) | In stock (30) |
| 19 | `FURN-019` | Rattan Double-Door Wardrobe Cabinet | Variable (7/8) | `Storage`, `Beds` | OakCraft | $320 – $440 | — | `Brown, Black` × `M, L` (4 biến thể) | In stock (10) |
| 20 | `FURN-020` | Industrial Steel Bookcase Rack | Variable (8/8) | `Storage` | UrbanoHaus | $190 – $290 | — | `Black, White` × `S, M, L` (6 biến thể) | In stock (18) |
| 21 | `FURN-021` | **Signature Velvet Accent Armchair** | **Variable Sale (1/2)** | `Chairs`, `Sofas` | LuxeVibe | $399 – $459 | **$299 – $349** | `Black, White, Brown` × `S, M, L` (6 biến thể, có biến thể `$299 / $399` khớp trang 5 PDF) | In stock (20) |
| 22 | `FURN-022` | **Smart LED Bedside Storage Frame** | **Variable Sale (2/2)** | `Beds`, `Storage` | Minimalia | $350 – $420 | **$245 – $294** | `Black, White` × `M, L` (4 biến thể, giảm 30% khớp Banner Summer Collection) | In stock (15) |

---

## 4. Kiến trúc Hệ thống, Công cụ và Cấu hình WoodMart + WooCommerce

### 4.1 Kiến trúc hệ thống (System Architecture)

```text
Trình duyệt người dùng (Desktop 1440×900 / Tablet 768×1024 / Mobile 390×844)
   │ HTTPS / HTTP2
   ▼
Web Server (Nginx hoặc Apache) + PHP-FPM (PHP 8.2 / 8.3) + Zend OPcache
   │
   ├── WordPress Core (Quản lý Pages, Blog Posts, Media, REST API, RBAC)
   ├── WooCommerce Core (Catalog, Variable Products, Session Cart, Checkout, My Account, Orders)
   ├── Multi-Currency Layer (Chuyển đổi song song USD $ ↔ VND ₫ trên Header/Widget/Checkout)
   ├── WoodMart Parent Theme + WoodMart Core + WoodMart Child Theme (Tùy biến Layout & Brand riêng)
   │     ├── WoodMart Header Builder (Logo, Mega Menu, AJAX Search, Account, Wishlist, Cart, Currency)
   │     ├── WoodMart AJAX Shop Filter & Swatches (Color, Size, Brand, Price, Stock/Sale)
   │     └── Built-in Quick View, Buy Now Redirect & Off-Canvas Mini Cart
   └── Cơ sở dữ liệu MySQL 8.0+ / MariaDB 10.11+ (InnoDB, bật HPOS & Product Attributes Lookup Table)
```

- **Bật HPOS (High-Performance Order Storage)**: Lưu trữ đơn hàng vào các bảng độc lập (`wp_wc_orders`, `wp_wc_order_addresses`, `wp_wc_order_operational_data`) giúp tăng tốc độ xử lý Checkout và truy vấn trang `My Account → Orders`.
- **Kích hoạt Product Attributes Lookup Table (`wp_wc_product_attributes_lookup`)**: Vào `WooCommerce → Settings → Products → Advanced` bật tính năng tra cứu thuộc tính để bộ lọc AJAX của WoodMart lọc chính xác các biến thể theo `Color`, `Size`, `Brand` và trạng thái tồn kho `In stock`.

### 4.2 Bộ Plugin tối giản đi kèm WoodMart

Vì **WoodMart + WoodMart Core** đã tích hợp sẵn Wishlist, Quick View, Variation Swatches, AJAX Search, AJAX Filter, Mega Menu và nút Buy Now, bạn **không cần cài các plugin rời trùng lặp chức năng**. Danh sách plugin duy nhất cần cài gồm:

1. **WooCommerce**: Core thương mại điện tử.
2. **WoodMart Core**: Plugin bắt buộc đi kèm theme WoodMart.
3. **Elementor** (hoặc **WPBakery** / **Gutenberg** tùy trình dựng bạn chọn trong WoodMart): Dùng để dựng lại bố cục trang chủ, trang `/about-us` và Custom Layout cho Product Detail.
4. **FOX – Currency Switcher Professional for WooCommerce** (hoặc **CURCY – Multi Currency for WooCommerce**): Cấu hình 2 đơn vị tiền tệ **`USD ($)`** (mặc định để chấm test case `$0 — $500`, `$299`, `$399`) và **`VND (₫)`** (tỷ giá `1 USD = 25,000 ₫`, làm tròn đến hàng nghìn đồng).
5. **WP Mail Logging** (trên Localhost/Staging): Ghi lại toàn bộ email xác nhận đơn hàng và đăng ký tài khoản ngay trong trang quản trị để chụp bằng chứng kiểm thử mà không cần cấu hình SMTP gửi mail thật ra ngoài.

### 4.3 Đặc tả kỹ thuật từng nhóm chức năng trên WoodMart để đạt 100/100 điểm

1. **Header, Mega Menu & Footer (10 điểm)**:
   - Dùng **WoodMart Header Builder** thiết kế Header mới (khác bản demo gốc):
     - **Top Bar**: Chuyển đổi tiền tệ `USD ($) | VND (₫)`, chuyển đổi ngôn ngữ `EN | VI`, link nhanh tới `/about-us` và `Order Tracking`.
     - **Main Header**: Logo thương hiệu riêng (trái), Main Menu ở giữa (mục `Shop` gắn **Mega Menu** hiển thị 7 danh mục nội thất kèm icon/ảnh đại diện), cụm công cụ bên phải gồm: **AJAX Search** (loại Post Type = `Product` + `Post`), **Account** (mở sidebar đăng nhập hoặc vào `/my-account/`), **Wishlist** (hiển thị số lượng sản phẩm đã lưu) và **Cart** (mở ngăn kéo trượt Mini Cart Drawer).
   - **Footer**: Chia 4 cột rõ ràng gồm `About` (giới thiệu ngắn + địa chỉ showroom Việt Nam + Social icons), `Customer Service` (Shipping Info, Return Policy, FAQs), `Information` (About Us, Blog, Privacy Policy, Terms & Conditions), `Newsletter` (form đăng ký nhận bản tin khuyến mại) và dòng `Copyright © 2026` ở đáy.
2. **Homepage (10 điểm)** & **Trang giới thiệu thương hiệu (`/about-us`)**:
   - Xây dựng lại bố cục Homepage gồm 8 section theo đúng thứ tự PDF trang 2–3:
     1. **Hero Banner**: Tiêu đề lớn `"MAKE YOUR SPACE, FEEL LIKE HOME"`, mô tả ngắn và nút CTA **`SHOP NOW`** dẫn tới `/shop`.
     2. **Category Grid**: Hiển thị 6 danh mục nổi bật (`Chairs, Tables, Sofas, Beds, Lighting, Decoration`) dạng thẻ ảnh bất đối xứng (Masonry/Bento Grid), còn mục thứ 7 (`Storage`) xuất hiện trên Mega Menu, trang `/shop` và thanh danh mục phụ.
     3. **Featured Products**: Dạng Grid 4 cột. Trong `Theme Settings → Product Archive → Product Styles`, chọn kiểu Product Card hiển thị đầy đủ 9 phần tử: **Ảnh sản phẩm, Tên, Giá gốc, Giá khuyến mại, Rating sao, Nhãn giảm giá (`-25%` / `Sale`), nút Wishlist, nút Quick View, nút Add to Cart / Select Options**.
     4. **Promotion Banner**: Khối băng rôn chiến dịch `"Summer Collection — Up to 30% OFF"` kèm đồng hồ đếm ngược (Countdown) và nút xem các sản phẩm đang giảm giá.
     5. **New Arrivals**: Hiển thị 4–8 sản phẩm mới nhất (`Order by: Date DESC`).
     6. **Best Sellers**: Hiển thị 4–8 sản phẩm bán chạy (`Data source: Best Sellers`).
     7. **Blog Section**: Hiển thị 3 bài viết mới nhất từ chuyên mục Blog kèm ảnh đại diện, ngày đăng, tác giả.
     8. **Footer**: Đầy đủ 6 thành phần theo yêu cầu.
   - **Trang `/about-us`**: Thiết kế riêng 1 trang giới thiệu câu chuyện thương hiệu nội thất quốc tế tại Việt Nam, triết lý vật liệu bền vững, số liệu bảo hành và thông tin liên hệ.
3. **Shop Page & Bộ lọc thực (15 + 10 = 25 điểm)**:
   - Trong `Theme Settings → Product Archive → Products Grid`, khóa cấu hình số cột: **Desktop = 4 sản phẩm/hàng**, **Tablet = 3 sản phẩm/hàng**, **Mobile = 2 sản phẩm/hàng**.
   - Bật **AJAX Shop** của WoodMart và cấu hình `Shop Sidebar` bằng các widget chuẩn:
     - **Product Categories**: Danh sách 7 danh mục (`Chairs, Tables, Sofas, Beds, Lighting, Decoration, Storage`).
     - **WoodMart WooCommerce Layered Nav (Brand)**: Gắn với thuộc tính `pa_brand` hiển thị 5 thương hiệu (`NordicLiving, UrbanoHaus, OakCraft, Minimalia, LuxeVibe`).
     - **WoodMart WooCommerce Layered Nav (Color)**: Hiển thị dạng **Swatch màu + Tên màu** (`Black`, `White`, `Brown`).
     - **WoodMart WooCommerce Layered Nav (Size)**: Hiển thị dạng nhãn nút bấm (`S`, `M`, `L`).
     - **Filter Products by Price**: Thanh trượt giá từ `$0` đến `$500` (tự động quy đổi sang `0 ₫ — 12.500.000 ₫` khi người dùng chuyển sang tiền tệ VND).
     - **WoodMart Stock Status (Availability)**: Hai hộp kiểm (Checkbox) thực tế: **`In stock`** và **`On sale`**.
4. **Product Detail & Variable Product (15 + 10 = 25 điểm)**:
   - Dùng **WoodMart Layouts → Single Product** tùy biến bố cục trang chi tiết sản phẩm gồm đầy đủ 16 thành phần theo PDF trang 5–6:
     - **Product Gallery** (ảnh lớn bên trái + danh sách ảnh thu nhỏ dưới đáy hoặc bên cạnh), **Product Name**, **Rating (`★★★★★ 4.8`)**, **Giá khuyến mại & Giá gốc (`$299` / `$399`)**, bộ chọn **Color Swatches** (`Black, White, Brown`), bộ chọn **Size Buttons** (`[ S ] [ M ] [ L ]`), nút tăng giảm **Quantity (`[-] 1 [+]`)**, nút **`[ ADD TO CART ]`**, nút **`[ BUY NOW ]`** (bật trong `Theme Settings → Single Product → Buy now`), nút **`♡ Add to Wishlist`**.
     - Khối thông tin **Shipping information** và **Return policy** đặt ngay dưới cụm nút mua hàng (hoặc trong tab riêng).
     - Khu vực Tabs bên dưới gồm: **Product description**, **Specifications** (bảng thuộc tính kỹ thuật), **Reviews** và khối **Related products** ở cuối trang.
   - **Đảm bảo tính năng Variable Product động**: Toàn bộ 10 sản phẩm biến thể trong file CSV (`FURN-013` đến `FURN-022`) đều có các dòng `variation` riêng với mức giá tăng dần theo Size (`S < M < L`) và đường dẫn ảnh riêng theo từng màu (`Black, White, Brown`). Khi người chấm bấm đổi màu hoặc kích cỡ, ảnh chính và giá tiền sẽ thay đổi ngay lập tức.
5. **Cart, Checkout & My Account (10 + 5 + 5 = 20 điểm)**:
   - **Cart (`/cart/`)**: Sử dụng trang Giỏ hàng thật của WooCommerce, đầy đủ cột `Product, Price, Quantity, Subtotal, Remove`, nút `Update cart`, ô nhập `Coupon` (khởi tạo sẵn mã `SUMMER30` giảm 30% và `WELCOME50` giảm $50), nút `Continue Shopping` và khung `Cart Totals` (`Subtotal, Shipping, Total`) kèm nút `Proceed to Checkout`.
   - **Checkout (`/checkout/`)**: Đảm bảo có đủ các trường: `First Name, Last Name, Country, Address, City, Phone, Email, Order Notes`; khung `Order Summary` (`Product, Quantity, Subtotal, Shipping, Total`); bật đúng 2 phương thức thanh toán **Cash on Delivery (COD)** và **Direct Bank Transfer (BACS)**. Đặt trường `Postcode / ZIP` thành không bắt buộc để phù hợp với thói quen mua hàng tại Việt Nam.
   - **My Account (`/my-account/`)**: Bật đăng ký tài khoản tại `WooCommerce → Settings → Accounts & Privacy → Allow customers to create an account on the "My account" page`. Hiển thị đầy đủ 6 tab: `Dashboard, Orders, Downloads, Addresses, Account Details, Logout` phục vụ bài kiểm tra luồng `Register → Login → My Account → View Orders`.
6. **Blog & Search**:
   - **Blog (`/blog/`)**: Khởi tạo 3 chuyên mục (`Interior Trends`, `Buying Guides`, `Space Styling`) và 6 bài viết có đầy đủ `Featured image, Author, Date, Related posts`. Trang chủ Blog bố trí 1 bài `Featured Post` lớn ở đầu trang, lưới `Latest Posts` bên dưới và Sidebar chứa danh sách `Category` + ô `Search` bài viết.
   - **Search**: Bật AJAX Search trên thanh tìm kiếm của WoodMart, trả về ngay danh sách sản phẩm khi gõ từ khóa `chair` kèm dòng thông báo tổng số kết quả (`Results: X products found`).

---

## 5. Phân quyền (Roles & Permissions), Bảo mật, Hiệu năng và Tối ưu người dùng (UX)

### 5.1 Ma trận phân quyền (RBAC — Role-Based Access Control)

| Vai trò (Role) | Quyền hạn được phép (Capabilities) | Giới hạn bảo mật bắt buộc |
|---|---|---|
| **Administrator (Quản trị hệ thống)** | Toàn quyền cấu hình WordPress, cài đặt/cập nhật Theme & Plugin, quản lý người dùng, cấu hình hệ thống. | Chỉ cấp cho 1–2 tài khoản kỹ thuật; không dùng username `admin`; bật xác thực 2 lớp (2FA); không dùng tài khoản này để viết bài hay test mua hàng. |
| **Shop Manager (Quản lý cửa hàng)** | Quản lý 22 sản phẩm (`product`), biến thể, tồn kho, 7 danh mục, 5 thương hiệu, mã giảm giá (`shop_coupon`), xem/cập nhật trạng thái đơn hàng (`shop_order`), xem báo cáo doanh thu. | Không có quyền cài đặt/xóa plugin, không sửa code theme (`DISALLOW_FILE_EDIT`), không thay đổi cấu hình hệ thống WordPress. |
| **Editor / Content Author (Biên tập viên)** | Tạo, sửa, xuất bản 6+ bài viết Blog, quản lý 3 chuyên mục Blog, chỉnh sửa nội dung các trang tĩnh (`/about-us`, Policy). | Không được truy cập dữ liệu đơn hàng của khách (PII), không sửa giá hoặc cấu hình sản phẩm WooCommerce. |
| **Customer (Khách hàng đã đăng ký)** | Đăng nhập, xem trang `/my-account/`, xem lịch sử đơn hàng của **chính mình**, cập nhật địa chỉ giao hàng và thông tin cá nhân, lưu Wishlist theo tài khoản. | Bị chặn truy cập `/wp-admin/` (tự động redirect về `/my-account/`); chỉ xem được `order_id` thuộc `user_id` của chính mình (chống lỗ hổng IDOR). |
| **Guest (Khách vãng lai)** | Xem trang chủ, danh mục, lọc/tìm kiếm sản phẩm, đổi tiền tệ USD/VND, xem chi tiết biến thể, thêm vào giỏ hàng, lưu Wishlist tạm (Cookie), đặt hàng Guest Checkout. | Không xem được trang `/my-account/orders/`; nếu truy cập URL đơn hàng của người khác mà không có `order_key` hợp lệ trong phiên sẽ bị từ chối. |

### 5.2 Bảo mật ứng dụng và Cấu hình cứng hóa (Security Hardening)

1. **Khóa trình sửa file trực tiếp và ẩn lỗi hệ thống trong `wp-config.php`**:
   ```php
   define( 'DISALLOW_FILE_EDIT', true );
   define( 'WP_DEBUG', false );
   define( 'WP_DEBUG_LOG', true );
   define( 'WP_DEBUG_DISPLAY', false );
   ```
2. **Bảo vệ chống Brute-force và lạm dụng XML-RPC / REST API**:
   - Vô hiệu hóa `xmlrpc.php` và ẩn danh sách người dùng qua `/wp-json/wp/v2/users` đối với khách chưa đăng nhập.
   - Giới hạn số lần đăng nhập sai (tối đa 5 lần / 15 phút) tại `/my-account/` và `/wp-login.php`.
3. **Kiểm soát toàn vẹn dữ liệu Giỏ hàng, Đa tiền tệ & Thanh toán**:
   - Mọi thao tác cập nhật giỏ hàng, đổi tiền tệ, áp mã giảm giá và đặt hàng bắt buộc kiểm tra **WordPress Nonce** (`woocommerce-cart`, `woocommerce-process_checkout`).
   - Giá sản phẩm, tỷ giá quy đổi USD/VND và mức giảm giá Coupon được tính toán lại 100% ở phía Server (PHP) dựa trên cơ sở dữ liệu khi tạo đơn, tuyệt đối không tin tưởng giá truyền từ HTML/JavaScript phía trình duyệt.
   - Sanitize mọi dữ liệu người dùng nhập ở trường *Order Notes*, *Search query* và *Product Review* bằng các hàm chuẩn của WordPress (`sanitize_text_field`, `wp_kses_post`, `esc_html`, `esc_attr`) để chống XSS và SQL Injection.

### 5.3 Tối ưu hiệu năng (Performance Optimization) cho WoodMart + WooCommerce

1. **Cấu hình tối ưu sẵn có trong WoodMart (`Theme Settings → Performance`)**:
   - Bật **Combine & Minify CSS/JS** của WoodMart (chỉ tải file CSS/JS của những element thực sự xuất hiện trên trang).
   - Bật **Swap Google Fonts display** (`font-display: swap`) và chỉ tải đúng 2 độ đậm chữ cần thiết (ví dụ `400 Regular`, `600 SemiBold`, `700 Bold`).
   - Tắt các thư viện không dùng tới (ví dụ Slider Revolution nếu Hero Banner đã dựng bằng khối Banner nhẹ của WoodMart).
2. **Quy tắc loại trừ Cache bắt buộc (Cache Exclusion Rules)**:
   - Tuyệt đối **KHÔNG cache HTML** cho các đường dẫn động: `/cart/`, `/checkout/`, `/my-account/`, `/wishlist/` và các request có tham số `?wc-ajax=*`.
   - Cấu hình Bypass Page Cache khi trình duyệt có chứa các cookie phiên mua hàng và tiền tệ: `woocommerce_items_in_cart`, `woocommerce_cart_hash`, `wp_woocommerce_session_*`, `wordpress_logged_in_*`, `woocs_current_currency` (hoặc cookie của plugin đa tiền tệ).
3. **Tối ưu hình ảnh nội thất và Core Web Vitals (LCP, CLS, INP)**:
   - Chuyển đổi toàn bộ ảnh sản phẩm và banner sang định dạng **WebP**; khóa tỷ lệ khung hình ảnh sản phẩm trong `Appearance → Customize → WooCommerce → Product Images` ở tỷ lệ **4:5** hoặc **1:1** đồng nhất để chỉ số dịch chuyển bố cục **CLS = 0** và tuyệt đối không xảy ra lỗi *"Product image méo"* (Trang 9 PDF).
   - Ảnh **Hero Banner** trên trang chủ và ảnh chính ở **Product Detail** gắn `fetchpriority="high"` và loại trừ khỏi Lazy Load để tối ưu chỉ số LCP (< 2.5 giây).
   - Đặt độ trễ **Debounce 300ms** và giới hạn tối thiểu 2 ký tự cho thanh AJAX Live Search.

### 5.4 Tối ưu trải nghiệm người dùng (UX Optimization)

1. **Chuyển đổi tiền tệ `USD ($)` ↔ `VND (₫)` mượt mà**:
   - Đặt nút chuyển đổi `USD ($) / VND (₫)` nổi bật ngay trên Header (cả Desktop và Mobile Menu) để người chấm bài kiểm tra khoảng giá `$0 — $500` bằng USD xong có thể bấm 1 chạm sang `VND (₫)` kiểm tra trải nghiệm bản địa hóa cho khách Việt Nam.
2. **Trải nghiệm chọn biến thể trực quan (Visual Swatches & Out-of-stock State)**:
   - Hiển thị ô màu (Color Swatch) kèm Tooltip tên màu; các tổ hợp biến thể hết hàng tự động hiển thị dấu gạch chéo mờ (`disabled`) ngay trên nút Size/Color.
3. **Trải nghiệm Bộ lọc trên Desktop & Mobile (Filter UX)**:
   - Hiển thị thanh **Active Filters** kèm nút **`Clear All`** ngay phía trên lưới sản phẩm.
   - Trên Tablet (`768 × 1024`) và Mobile (`390 × 844`), bật tính năng **Off-canvas Sidebar Filter** của WoodMart (gom toàn bộ bộ lọc vào nút `Filter` mở ngăn kéo trượt bên trái) để người dùng thấy ngay danh sách sản phẩm mà không phải cuộn qua một cột lọc dài.
4. **Sticky Add to Cart trên Mobile**:
   - Bật thanh **Sticky Add to Cart** cố định dưới đáy màn hình Mobile (`390 × 844`) trong trang Product Detail để khách dễ dàng chọn biến thể và thêm vào giỏ khi đang cuộn đọc mô tả dài.

---

## 6. Kết luận về Model AI và các Công cụ Mã nguồn mở từ GitHub

- **Không cài đặt Model AI từ GitHub**: Toàn bộ hệ thống vận hành bằng cơ sở dữ liệu quan hệ (MySQL/InnoDB), truy vấn Taxonomy/Meta của WooCommerce và cơ chế AJAX của WoodMart. Không cần bất kỳ mô hình AI/LLM nào.
- **Các kho mã nguồn / công cụ trên GitHub khuyên dùng để tự động hóa và kiểm soát chất lượng**:
  1. **[woocommerce/woocommerce](https://www.google.com/search?q=https%3A%2F%2Fgithub.com%2Fwoocommerce%2Fwoocommerce)**: Chuẩn định dạng cột CSV Importer (đã áp dụng trực tiếp vào file `woocommerce_furniture_dataset_22_products.csv` đính kèm).
  2. **[wp-cli/wp-cli](https://www.google.com/search?q=https%3A%2F%2Fgithub.com%2Fwp-cli%2Fwp-cli)**: Tự động hóa việc tạo 7 danh mục, tạo các trang tĩnh (`/about-us`, `/blog`), tạo 6 bài viết Blog và cấu hình WooCommerce qua dòng lệnh.
  3. **[WordPress-Coding-Standards (WPCS)](https://www.google.com/search?q=https%3A%2F%2Fgithub.com%2FWordPress%2FWordPress-Coding-Standards)**: Kiểm tra chuẩn code trong `woodmart-child/functions.php`.
  4. **[microsoft/playwright](https://www.google.com/search?q=https%3A%2F%2Fgithub.com%2Fmicrosoft%2Fplaywright)**: Hỗ trợ chạy tự động và chụp ảnh màn hình đồng loạt ở đúng 3 breakpoint (`1440×900`, `768×1024`, `390×844`) phục vụ báo cáo kiểm thử Excel.

---

## 7. Lộ trình Triển khai (Stage-Gate) và Bộ Tài liệu Bàn giao

| Giai đoạn | Hạng mục công việc cụ thể | Đầu ra kiểm soát (Gate) |
|---|---|---|
| **Stage 1: Khởi tạo Môi trường & Cấu hình Core** | Cài đặt WordPress + WooCommerce; kích hoạt `WoodMart` + `WoodMartCore` + `WoodMart Child`; bật HPOS & bảng Lookup thuộc tính; cài plugin đa tiền tệ (`USD $` mặc định & `VND ₫`). | Hệ thống chạy ổn định trên Localhost/Server test; chuyển đổi tiền tệ `USD ↔ VND` hoạt động đúng. |
| **Stage 2: Nạp Dataset 22 Sản phẩm & Nội dung** | Vào `Products → Import` nạp file `woocommerce_furniture_dataset_22_products.csv` (tự động tạo 7 danh mục, 5 thương hiệu, thuộc tính Color/Size và 22 sản phẩm kèm 48 biến thể); tạo 2 mã Coupon (`SUMMER30`, `EXPIRED10`), 6 bài Blog (3 chuyên mục) và trang `/about-us`. | Đủ 22 sản phẩm (10 Simple, 8 Variable, 2 Simple Sale, 2 Variable Sale; trong đó có 2 sản phẩm Out of stock); đủ 7 danh mục và 5 thương hiệu. |
| **Stage 3: Tùy biến Giao diện WoodMart (Chống lỗi Copy Demo)** | Thiết kế lại bảng màu, bộ font, Header (Mega Menu, AJAX Search, Account, Wishlist, Cart, Currency Switcher) và Footer; dựng lại Homepage 8 khối; tùy biến Product Card (đủ 9 phần tử) và Product Detail (đủ 16 phần tử, có nút `Buy Now` và Swatches đổi ảnh/giá). | Giao diện mang bản sắc riêng biệt; chọn biến thể `Color / Size` cập nhật giá và ảnh tức thời. |
| **Stage 4: Cấu hình Bộ lọc, Checkout, My Account & Responsive** | Cấu hình Sidebar Filter (Category, Brand, Color, Size, Price `$0–$500`, Availability `In stock / On sale`); cấu hình COD & Bank Transfer; bật form Register tại `/my-account/`; kiểm tra kỹ 3 độ phân giải `1440×900`, `768×1024`, `390×844`. | Grid đạt 4/3/2 cột; bộ lọc trả kết quả thật; không mắc bất kỳ lỗi nào trong 7 lỗi cấm Responsive của PDF trang 9. |
| **Stage 5: Chạy 28 Test Cases & Xuất Báo cáo Excel** | Thực thi trọn bộ `TC01–TC28` trong file `Test_Plan_and_Test_Cases.md`; chụp ảnh bằng chứng; xuất sang file Excel kiểm thử theo mẫu bạn cung cấp. | 100% Test Cases đạt Pass; hoàn tất đóng gói Website + Dataset + Excel Test Report. |
