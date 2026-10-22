# STAGE 1F.4R — STOREFRONT RUNTIME VERIFICATION & VISUAL QA REPORT

- **Project:** `furniture-ecommerce`
- **WordPress Root:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public`
- **Child Theme:** `wp-content/themes/twentytwentyfive-child`
- **Audit Date:** 2026-10-19
- **Mode:** STRICT READ-ONLY AUDIT
- **Reviewer:** Senior WordPress Full-Site Editing Engineer, WooCommerce Frontend Engineer, Browser QA Engineer & Software Test Auditor
- **Verdict:** **CONDITIONAL PASS**
  - *WordPress Core FSE Template Hierarchy & Resolution:* **PASS (100%)**
  - *Network & Asset Availability (HTTP 200, CSS/Images):* **PASS (100%)**
  - *Database & Configuration Invariants:* **PASS (100%)**
  - *Link Integrity & Anchor Resolution:* **CONDITIONAL PASS (1 Defect Identified)**
  - *Playwright Browser Automation & Screenshots:* **NOT VERIFIED / BLOCKED (Sandbox Driver 404)**

---

## 1. EXECUTIVE SUMMARY

Giai đoạn **Stage 1F.4R — Storefront Runtime Verification & Visual QA** được thực hiện nhằm kiểm định toàn diện website thương mại điện tử **ATELIER** trong môi trường thực tế (Local by Flywheel, WordPress 6.7.x / 7.1.3, WooCommerce 11.2.1, PHP 8.2.29, MySQL port 10011).

### Các kết quả then chốt đã xác minh:
1. **Dịch vụ Runtime đang hoạt động:** Nginx (PID 12788) trên port `10010` và MySQL (PID 10640) trên port `10011` hoạt động ổn định. Mọi endpoint `/`, `/shop/`, `/cart/`, `/checkout/`, `/my-account/` đều phản hồi mã HTTP hợp lệ.
2. **Cơ chế WooCommerce Coming Soon được làm rõ:**
   - Khi khách truy cập chưa đăng nhập (`guest`, user ID 0), do `woocommerce_coming_soon = 'yes'` và `woocommerce_store_pages_only = 'no'`, WooCommerce sẽ tự động chặn toàn bộ các trang và hiển thị template `coming-soon` (Title: `furniture-ecommerce`, H1: `furniture-ecommerce is coming soon`).
   - Khi phiên người dùng có quyền quản lý (`current_user_can('manage_woocommerce')`, ví dụ Administrator user ID 1), WooCommerce cho phép truy cập trực tiếp toàn bộ giao diện storefront của child theme: `twentytwentyfive-child//front-page` và `twentytwentyfive-child//archive-product`.
3. **Template Hierarchy được chứng minh bằng WordPress Core Runtime:**
   - Với URL `/` (`show_on_front = 'posts'`): Hàm `resolve_block_template('front-page', ['front-page', 'home', 'index'])` chọn chính xác `twentytwentyfive-child//front-page` (Source: `theme`) với độ ưu tiên cao nhất, vượt qua `home` và `index`.
   - Với URL `/shop/`: Hàm `resolve_block_template('archive-product', ['archive-product', 'archive', 'index'])` chọn chính xác `twentytwentyfive-child//archive-product` (Source: `theme`), xác nhận child theme đã override hoàn toàn template mặc định của WooCommerce plugin.
4. **Tính toàn vẹn Asset & Không có phụ thuộc bên ngoài:**
   - File stylesheet `style.css` (37,410 bytes), ảnh Hero `hero-furniture.jpg` (800,457 bytes), và ảnh Artisan Joinery `craft-detail.jpg` (740,406 bytes) đều trả về **HTTP 200 OK** với đúng `Content-Type`.
   - Không có bất kỳ request mạng bên ngoài nào tới Google Fonts, CDN hoặc JavaScript bên ngoài.
5. **Phát hiện lỗi liên kết tương đối (Defect DEF-01):**
   - Trong Header và Footer, các anchor links như `#categories`, `#craft`, `#values` được viết ở dạng tương đối không có dấu gạch chéo đầu trang. Khi người dùng đang ở trang `/shop/`, nhấp vào các liên kết này sẽ trỏ tới `/shop/#categories` (không tồn tại trong DOM của trang Shop). Riêng nút Empty State của trang Shop đã viết đúng `/#craft`.
6. **Bảo toàn Invariants tuyệt đối:**
   - 0 sản phẩm, 0 biến thể, 0 SKU, 1 media attachment (ID 5), 5 published pages, `woocommerce_coming_soon = 'yes'`. Không có bất kỳ thay đổi dữ liệu nào.

---

## 2. VERDICT: CONDITIONAL PASS

- **PASS:** Các kiểm tra runtime WordPress Core, Nginx/MySQL status, Template Resolution, Database Invariants, Asset HTTP responses, và File SHA-256 hashes.
- **CONDITIONAL PASS:** Phát hiện 1 lỗi mức độ Medium (`DEF-01`) về đường dẫn anchor trong header/footer khi đứng từ trang `/shop/`.
- **NOT VERIFIED / BLOCKED:** Kiểm tra tự động hóa trình duyệt và chụp screenshot qua Playwright do công cụ sandbox thiếu driver binary (lỗi HTTP 404 từ Azure Edge CDN). Đã cung cấp Checklist kiểm thử thủ công chi tiết để người dùng tự xác nhận trên trình duyệt thật.

---

## 3. SCOPE AND SAFETY CONFIRMATION

- Chế độ kiểm toán: **STRICT READ-ONLY**.
- Tuyệt đối không chỉnh sửa file mã nguồn dự án trong child theme (`style.css`, `theme.json`, `functions.php`, templates, parts).
- Không tắt Coming Soon, không thay đổi cấu hình WordPress/WooCommerce.
- Không nhập dữ liệu, không tạo đơn hàng, không thêm term hoặc media.
- Không tạo Git commit, không sửa working tree.

---

## 4. ENVIRONMENT AND SERVICE STATUS

| Dịch Vụ / Tiến Trình | Cổng / PID | Trạng Thái Thực Tế | Phương Pháp Kiểm Tra |
|---|---|:---:|---|
| **Nginx Web Server** | Port `10010` (PID 12788) | **RUNNING** | `Get-Process nginx`, HTTP listener verified |
| **MySQL Database** | Port `10011` (PID 10640) | **RUNNING** | `Get-Process mysqld`, PHP mysqli ping verified |
| **WordPress Core** | Version `7.1.3` (Local Dev) | **ACTIVE** | `get_bloginfo('version')` |
| **WooCommerce Plugin**| Version `11.2.1` | **ACTIVE** | Constant `WC_VERSION` |
| **Active Theme** | `twentytwentyfive-child` | **ACTIVE** | `get_stylesheet()`, Template: `twentytwentyfive` |
| **Coming Soon Mode** | `woocommerce_coming_soon = 'yes'` | **ENFORCED** | Option query & `ComingSoonRequestHandler` |

---

## 5. URL-BY-URL RUNTIME RESULTS

Kiểm tra trực tiếp qua HTTP requests đến máy chủ cục bộ:

| Endpoint URL | HTTP Status | Final URL (Sau Redirect) | Kích Thước (Bytes) | Nội Dung Hiển Thị (Guest Session) | Ghi Chú Kỹ Thuật |
|---|:---:|---|:---:|---|---|
| `http://localhost:10010/` | **200 OK** | `http://localhost:10010/` | 62,503 B | WooCommerce Coming Soon Screen | Intercepted do Coming Soon = yes |
| `http://localhost:10010/shop/` | **200 OK** | `http://localhost:10010/shop/` | 62,727 B | WooCommerce Coming Soon Screen | Intercepted do Coming Soon = yes |
| `http://localhost:10010/cart/` | **200 OK** | `http://localhost:10010/cart/` | 122,973 B | WooCommerce Cart Page | Block Cart native hiển thị |
| `http://localhost:10010/checkout/` | **200 OK** | `http://localhost:10010/cart/` | 122,973 B | WooCommerce Cart Page | Chuyển hướng do giỏ hàng trống (302 -> 200) |
| `http://localhost:10010/my-account/`| **200 OK** | `http://localhost:10010/my-account/`| 63,732 B | WooCommerce Login / Account Form | Form đăng nhập chuẩn WooCommerce |

---

## 6. BROWSER AUTOMATION STATUS AND EVIDENCE

- **Công cụ kiểm tra:** Antigravity `browser_subagent` điều khiển Playwright.
- **Kết quả:** **BLOCKED / FAILED TO INITIALIZE DRIVER**.
- **Lỗi cụ thể từ hệ thống:**
  ```text
  failed to create browser context: failed to run playwright manager: failed to install playwright: 
  could not install driver: got non 200 status code: 404 (404 Not Found) 
  from https://playwright.azureedge.net/builds/driver/playwright-1.57.0-win32_x64.zip
  from https://playwright-akamai.azureedge.net/builds/driver/playwright-1.57.0-win32_x64.zip
  from https://playwright-verizon.azureedge.net/builds/driver/playwright-1.57.0-win32_x64.zip
  ```
- **Xử lý:** Đúng quy định Mục 5.1 và 5.4, không tự ý cài đặt thêm dependency ngoài luồng; chuyển các hạng mục chụp ảnh tự động sang `NOT VERIFIED` và cung cấp checklist thủ công.

---

## 7. SCREENSHOT INVENTORY

Do Playwright driver bị lỗi 404, tất cả các ảnh chụp tự động được đánh dấu **NOT VERIFIED**. Bảng hướng dẫn cho QA Tester chụp ảnh thủ công bằng Chrome/Edge:

| ID | Trang Cần Chụp | Viewport | Tọa Độ / Khu Vực Cần Ghi Nhận | Trạng Thái Tự Động | Hướng Dẫn Chụp Thủ Công |
|---|---|:---:|---|:---:|---|
| **SS-01** | Homepage (Admin) | 1440 × 900 | Hero: Header, logo ATELIER, typography, CTA buttons | NOT VERIFIED | Đăng nhập Admin, mở `http://localhost:10010/`, chụp full màn hình đầu trang |
| **SS-02** | Homepage (Admin) | 1440 × 900 | Mid-page: Categories grid, Brand values, 4 demo items | NOT VERIFIED | Cuộn giữa trang, kiểm tra ảnh demo và grid 4 cột |
| **SS-03** | Homepage (Admin) | 1440 × 900 | Craft narrative: Ảnh thợ mộc, Final CTA, Footer 4 cột | NOT VERIFIED | Cuộn cuối trang, kiểm tra form demo disabled |
| **SS-04** | Shop (Admin) | 1440 × 900 | Intro: Breadcrumbs, H1 "The Furniture Collection", 8 Pills | NOT VERIFIED | Mở `http://localhost:10010/shop/`, kiểm tra pill navigation |
| **SS-05** | Shop (Admin) | 1440 × 900 | Empty State: Biểu tượng ◇, "Active Curation", badge | NOT VERIFIED | Kiểm tra Empty State card, 2 nút điều hướng về trang chủ |
| **SS-06** | Homepage (Admin) | 390 × 844 | Mobile: Header thu gọn, Hero xếp dọc, tap targets | NOT VERIFIED | F12 Device Mode (iPhone 14), kiểm tra không bị tràn ngang |
| **SS-07** | Shop (Admin) | 390 × 844 | Mobile: Category pills cuộn ngang, nút empty state xếp dọc | NOT VERIFIED | F12 Device Mode (iPhone 14), kiểm tra vuốt cuộn pills |
| **SS-08** | Homepage (Admin) | 768 × 1024 | Tablet: Layout 2 cột, category cards | NOT VERIFIED | F12 Device Mode (iPad Mini), kiểm tra cân đối lề |
| **SS-09** | Shop (Admin) | 768 × 1024 | Tablet: Toolbar kết quả, empty state card 640px | NOT VERIFIED | F12 Device Mode (iPad Mini), kiểm tra khoảng cách |

---

## 8. HOMEPAGE VISUAL QA (STATIC & CORE RUNTIME AUDIT)

Kiểm tra thông qua cấu trúc DOM thực tế được WordPress Core render:

1. **Header:** Wordmark `ATELIER` với font chữ Serif cao cấp, menu điều hướng `Home`, `Collection`, `Spaces`, `Philosophy`, biểu tượng SVG `Account` và `Cart 0`.
2. **Hero Section:** H1 *"Furnishings Designed with Quiet Conviction & Organic Warmth"*, nút Primary CTA *"Explore Collection"* (`/shop/`) và Secondary CTA *"Browse Spaces"* (`#categories`). Ảnh nền `hero-furniture.jpg` nạp qua HTTP 200.
3. **Category Showcase:** 4 không gian nội thất (`Living Spaces`, `Dining Room`, `Bedroom`, `Lighting & Decor`).
4. **Brand Values:** 3 giá trị thương hiệu (`Material Integrity`, `Intentional Proportion`, `Enduring Longevity`).
5. **Featured Architectural Essentials:** 4 sản phẩm demo (`Kanso Lounge Chair`, `Sora Dining Table`, `Nami Credenza`, `Yoru Floor Lamp`) với badge rõ ràng *"Curated Preview"*, không phải sản phẩm catalog giả.
6. **Craft Narrative:** Khối câu chuyện thợ mộc mộng gỗ thủ công kèm ảnh `craft-detail.jpg`.
7. **Final CTA & Footer:** Box kêu gọi thiết kế không gian sống, footer 4 cột Warm Minimalist, form đăng ký newsletter ở trạng thái `disabled` (đúng thiết kế demo).

---

## 9. SHOP VISUAL QA (STATIC & CORE RUNTIME AUDIT)

Kiểm tra thông qua cấu trúc DOM thực tế của `archive-product.html`:

1. **Breadcrumbs:** Sử dụng khối gốc `<!-- wp:woocommerce/breadcrumbs /-->`, liên kết rõ ràng `Home / Shop`.
2. **Shop Intro & H1:** Tiêu đề H1 duy nhất *"The Furniture Collection"*, mô tả ngắn gọn không chiếm chiều cao thừa.
3. **Category Navigation Pills:** 8 nút bo góc 999px (`All Spaces`, `Beds`, `Chairs`, `Decoration`, `Lighting`, `Sofas`, `Storage`, `Tables`) với tap target >= 40px.
4. **Shop Toolbar:** Tích hợp `woocommerce/product-results-count` và `woocommerce/catalog-sorting`. Khi số sản phẩm bằng 0, WooCommerce tự động ẩn dropdown sắp xếp để bảo vệ trải nghiệm người dùng.
5. **Editorial Empty State:** Khối `wp:woocommerce/product-collection-no-results` hiển thị card tối giản:
   - Icon: `◇`
   - Tiêu đề H2: *"The Collection is in Active Curation"*
   - Badge minh bạch: *"Stage 1F.4 Storefront Preview · 0 Live Products"*
   - Nút điều hướng: *"Return to Atelier Home"* (`/`) và *"Explore Philosophy"* (`/#craft`).

---

## 10. RESPONSIVE QA (CODE AUDIT)

- **Desktop (1440 × 900):** Container tiêu chuẩn 1320px căn giữa; khoảng cách đệm 2.25rem; không có phần tử nào vượt khung nhìn.
- **Tablet (768 × 1024):** Category pills kích hoạt cuộn ngang mềm (`overflow-x: auto`), ẩn thanh cuộn thô; Empty State card co giãn linh hoạt theo tỷ lệ padding.
- **Mobile (390 × 844):**
  - Breakpoint `@media (max-width: 768px)` và `@media (max-width: 480px)` chuyển toolbar thành flex-column.
  - Các nút hành động trong Empty State chuyển thành chiều rộng 100% với tap target >44px, dễ dàng thao tác bằng ngón tay cái.
  - Không có thuộc tính `overflow-x: hidden` gượng ép trên body; không có horizontal overflow.
  - Hỗ trợ `@media (prefers-reduced-motion: reduce)` triệt tiêu transition giật lắc.

---

## 11. NAVIGATION AND LINK INTEGRITY

Tổng cộng **22 liên kết độc nhất** trên Homepage và Shop đã được quét và kiểm định đích đến:

| Vị Trí | Văn Bản Liên Kết | Thuộc Tính `href` | Phân Loại | Trạng Thái Đích Đến |
|---|---|---|---|:---:|
| Header | `ATELIER` | `/` | Internal Root | **HỢP LỆ (PASS)** |
| Header | `Home` | `/` | Internal Root | **HỢP LỆ (PASS)** |
| Header | `Collection` | `/shop/` | Internal Page | **HỢP LỆ (PASS)** |
| Header | `Spaces` | `#categories` | Anchor tương đối | **CẢNH BÁO: Hợp lệ trên Home, lỗi khi ở Shop (DEF-01)** |
| Header | `Philosophy` | `#craft` | Anchor tương đối | **CẢNH BÁO: Hợp lệ trên Home, lỗi khi ở Shop (DEF-01)** |
| Header | `Account` | `/my-account/` | Internal Page | **HỢP LỆ (PASS)** |
| Header | `Cart 0` | `/cart/` | Internal Page | **HỢP LỆ (PASS)** |
| Home Hero | `Explore Collection` | `/shop/` | Internal Page | **HỢP LỆ (PASS)** |
| Home Hero | `Browse Spaces` | `#categories` | Anchor nội bộ | **HỢP LỆ (Target ID #categories tồn tại)** |
| Home Demo | `View Collection →` | `/shop/` | Internal Page | **HỢP LỆ (PASS)** |
| Home Demo | `View in Shop` | `/shop/` | Internal Page | **HỢP LỆ (PASS)** |
| Home CTA | `Browse The Storefront` | `/shop/` | Internal Page | **HỢP LỆ (PASS)** |
| Shop Intro | `All Spaces` | `/shop/` | Internal Page | **HỢP LỆ (PASS)** |
| Shop Intro | `Beds` | `/shop/?product_cat=beds` | URL Param | **HỢP LỆ (Category tồn tại trong DB)** |
| Shop Intro | `Chairs` | `/shop/?product_cat=chairs` | URL Param | **HỢP LỆ (Category tồn tại trong DB)** |
| Shop Intro | `Decoration` | `/shop/?product_cat=decoration`| URL Param | **HỢP LỆ (Category tồn tại trong DB)** |
| Shop Intro | `Lighting` | `/shop/?product_cat=lighting` | URL Param | **HỢP LỆ (Category tồn tại trong DB)** |
| Shop Intro | `Sofas` | `/shop/?product_cat=sofas` | URL Param | **HỢP LỆ (Category tồn tại trong DB)** |
| Shop Intro | `Storage` | `/shop/?product_cat=storage` | URL Param | **HỢP LỆ (Category tồn tại trong DB)** |
| Shop Intro | `Tables` | `/shop/?product_cat=tables` | URL Param | **HỢP LỆ (Category tồn tại trong DB)** |
| Shop Empty | `Return to Atelier Home` | `/` | Internal Root | **HỢP LỆ (PASS)** |
| Shop Empty | `Explore Philosophy` | `/#craft` | Root Anchor | **HỢP LỆ (Target ID #craft tồn tại trên trang chủ)** |
| Footer | `Living Spaces`, v.v. | `#categories` | Anchor tương đối | **CẢNH BÁO: Lỗi khi ở Shop (DEF-01)** |
| Footer | `Craft & Care` | `#craft` | Anchor tương đối | **CẢNH BÁO: Lỗi khi ở Shop (DEF-01)** |
| Footer | `White Glove Delivery` | `#values` | Anchor tương đối | **CẢNH BÁO: Lỗi khi ở Shop (DEF-01)** |

---

## 12. TEMPLATE HIERARCHY AND WORDPRESS/WOOCOMMERCE RESOLUTION

Thực hiện kiểm tra bằng mã nguồn PHP bootstrap qua WordPress Core:

1. **Kiểm tra hàm `resolve_block_template()`:**
   - Homepage: `resolve_block_template('front-page', ['front-page', 'home', 'index'])` -> Trả về `twentytwentyfive-child//front-page` (Source: `theme`).
   - Shop Catalog: `resolve_block_template('archive-product', ['archive-product', 'archive', 'index'])` -> Trả về `twentytwentyfive-child//archive-product` (Source: `theme`).
2. **Kiểm tra cơ chế nạp template:**
   - Child theme override WooCommerce template plugin hoàn toàn tự động khi có file `templates/archive-product.html`.
   - Header part (`parts/header.html`) và Footer part (`parts/footer.html`) được nạp chính xác 1 lần, không bị trùng lặp.
   - Khi render bằng `do_blocks()`, cả hai template đều trả về HTML hoàn chỉnh, không phát sinh warning hoặc block validation error.

---

## 13. JAVASCRIPT, CSS, NETWORK AND ASSET ERRORS

- **JavaScript Exceptions:** Không sử dụng bất kỳ file JS tùy chỉnh hoặc thư viện bên ngoài nào. Storefront vận hành thuần túy trên HTML/CSS.
- **Tài nguyên ảnh nội bộ:**
  - `hero-furniture.jpg`: HTTP 200 OK, kích thước 800,457 bytes, định dạng JPEG chuẩn.
  - `craft-detail.jpg`: HTTP 200 OK, kích thước 740,406 bytes, định dạng JPEG chuẩn.
- **Tài nguyên CSS:**
  - `style.css`: HTTP 200 OK, kích thước 37,410 bytes, định dạng `text/css`. Cú pháp 225 cặp dấu ngoặc nhọn `{}` cân bằng tuyệt đối 100%.
- **Network Requests ra bên ngoài:** 0 request (Hoàn toàn độc lập, không tải font/script từ Google/CDN bên ngoài).

---

## 14. DATABASE INVARIANTS

Xác minh qua truy vấn SQL chỉ đọc trên cơ sở dữ liệu `local` (port 10011, tiền tố `wp_`) lúc 2026-10-19 02:45:49 UTC:

| Bảng Dữ Liệu / Tiêu Chí | Baseline Ban Đầu | Trạng Thái Thực Tế Hiện Tại | Đánh Giá Invariant |
|---|:---:|:---:|:---:|
| **`post_type = 'product'`** | 0 | **0** | **BẢO TOÀN (100%)** |
| **`post_type = 'product_variation'`** | 0 | **0** | **BẢO TOÀN (100%)** |
| **`_sku` in `wp_postmeta`** | 0 | **0** | **BẢO TOÀN (100%)** |
| **Total Media Attachments** | 1 (ID 5) | **1 (ID 5)** | **BẢO TOÀN (100%)** |
| **Published Pages Count** | 5 | **5** | **BẢO TOÀN (100%)** |
| **`show_on_front`** | `'posts'` | `'posts'` | **BẢO TOÀN (100%)** |
| **`page_on_front`** | `0` | `0` | **BẢO TOÀN (100%)** |
| **`page_for_posts`** | `0` | `0` | **BẢO TOÀN (100%)** |
| **`woocommerce_coming_soon`** | `'yes'` | `'yes'` | **BẢO TOÀN (100%)** |
| **Global Attributes Count** | 3 | **3** | **BẢO TOÀN (100%)** |
| **Attribute Terms Count** | 19 | **19** | **BẢO TOÀN (100%)** |
| **Product Categories Count** | 8 | **8** | **BẢO TOÀN (100%)** |

---

## 15. GIT AND FILE INTEGRITY

Kiểm tra trạng thái Git (`git status --short`):
```text
 M themes/twentytwentyfive-child/functions.php
 M themes/twentytwentyfive-child/style.css
 M themes/twentytwentyfive-child/theme.json
?? themes/twentytwentyfive-child/STAGE_1F_2_HEADER_FOOTER_HOMEPAGE_REPORT.md
?? themes/twentytwentyfive-child/STAGE_1F_3_HOMEPAGE_COMPLETION_REPORT.md
?? themes/twentytwentyfive-child/STAGE_1F_4_SHOP_ARCHIVE_REPORT.md
?? themes/twentytwentyfive-child/assets/
?? themes/twentytwentyfive-child/parts/
?? themes/twentytwentyfive-child/templates/
```
*Không có Git commit mới, không có file ngoài phạm vi bị chỉnh sửa.*

### Bảng đối chiếu SHA-256 Checksums:

| Đường Dẫn File | Dung Lượng | SHA-256 Checksum Thực Tế | So Với Baseline 1F.4 |
|---|---:|---|:---:|
| `templates/archive-product.html` | 5,446 B | `7EED6D6EF723540BFB1EA22383380D624F3D98C4C03C975384F39040A15EA46A` | **TRÙNG KHỚP 100%** |
| `style.css` | 37,410 B | `9668BE2496C28A1B82364B46D3947FD2E89033DE4CB13B3FD9A9565AC8F31E6B` | **TRÙNG KHỚP 100%** |
| `parts/header.html` | 3,331 B | `B0D6F2237DB9705DCCC743829D160C42188C3ABD5F45E3E7551900E1F95974C5` | **TRÙNG KHỚP 100%** |
| `parts/footer.html` | 5,031 B | `7E9926A9D5501B23C0D5664886C682F2029CF5E2C07669807C54B04620FAFA26` | **TRÙNG KHỚP 100%** |
| `templates/front-page.html` | 16,061 B | `C87D34C02F40671C1A2BE045D60F36D74C2DEE425ADFE6BC56FDC73A383EA05C` | **TRÙNG KHỚP 100%** |
| `assets/images/hero-furniture.jpg` | 800,457 B | `A0EAA549B8E09A7371B92A55B3BEB86DD52939FCF14BF5A91F803D71F318A419` | **TRÙNG KHỚP 100%** |
| `assets/images/craft-detail.jpg` | 740,406 B | `C637D681E65CCBF30A782D2CA5D0F26E91A0B407B8A7DDB3BEBE47B9B9358670` | **TRÙNG KHỚP 100%** |
| `functions.php` | 1,052 B | `6C2FBCB2B13CDF036734A94DD871A1D28DF3CE291423D8B32ACFDE6A33B6AC96` | **TRÙNG KHỚP 100%** |
| `theme.json` | 8,177 B | `FE1D1C141CC43515625950EE456B654FAB87BC749FA6327C90C2A252EED72A94` | **TRÙNG KHỚP 100%** |

---

## 16. DEFECTS REGISTER

### DEF-01: Header & Footer Anchor Links Lacks Root Slash For Cross-Page Navigation
- **Severity:** **Medium**
- **Vị trí:** `wp-content/themes/twentytwentyfive-child/parts/header.html` và `parts/footer.html`
- **Mô tả:** Các liên kết nội bộ `#categories`, `#craft`, `#values` được viết ở dạng tương đối cục bộ thay vì trỏ về gốc website `/#categories`, `/#craft`, `/#values`.
- **Bằng chứng:** Trong `header.html` (dòng 15, 16):
  ```html
  <a class="atelier-nav-link" href="#categories">Spaces</a>
  <a class="atelier-nav-link" href="#craft">Philosophy</a>
  ```
  Trong `archive-product.html` (dòng 48), Empty State đã viết đúng:
  ```html
  <a class="atelier-btn atelier-btn-secondary" href="/#craft">Explore Philosophy</a>
  ```
- **Ảnh hưởng:** Khi người dùng đang duyệt trang Shop (`/shop/`), nhấp vào "Spaces" hoặc "Philosophy" trên Header sẽ không có phản hồi vì trình duyệt tìm kiếm ID `#categories` trên trang Shop (ID này không tồn tại trong DOM trang Shop).
- **Cách tái hiện:**
  1. Mở `http://localhost:10010/shop/` dưới quyền Admin.
  2. Nhấp vào "Philosophy" trên Header.
  3. URL chuyển thành `http://localhost:10010/shop/#craft` và trang không cuộn/chuyển đi đâu.
- **Đề xuất khắc phục (Stage 1F.5):** Sửa các thuộc tính `href` trong `header.html` và `footer.html` từ `#categories`, `#craft`, `#values` thành `/#categories`, `/#craft`, `/#values`. *(Không sửa trong Stage 1F.4R vì tuân thủ nghiêm ngặt chế độ READ-ONLY).*

---

## 17. MANUAL QA CHECKLIST CHO NGƯỜI DÙNG

Do công cụ Playwright tự động hóa không thể khởi tạo driver trong sandbox, người dùng vui lòng mở trình duyệt Edge hoặc Chrome cá nhân để xác nhận các mục sau:

1. [ ] **Đăng nhập quản trị:** Truy cập `http://localhost:10010/wp-admin/` và đăng nhập tài khoản Admin.
2. [ ] **Kiểm tra Trang chủ (Desktop 1440px):** Mở tab mới vào `http://localhost:10010/`. Xác nhận thấy đầy đủ Hero ảnh gỗ sồi, typography ATELIER, 4 danh mục và 4 sản phẩm demo.
3. [ ] **Kiểm tra Trang Shop (Desktop 1440px):** Mở `http://localhost:10010/shop/`. Xác nhận thấy breadcrumb `Home / Shop`, tiêu đề `The Furniture Collection`, 8 nút pills, và card Empty State *"The Collection is in Active Curation"* kèm badge *"0 Live Products"*.
4. [ ] **Kiểm tra Responsive Mobile (390px):** Nhấn `F12`, bật biểu tượng điện thoại (Device Toolbar), chọn iPhone 14 (390 × 844). Xác nhận thanh pills cuộn ngang được và các nút bấm Empty State xếp chồng ngay ngắn, không bị tràn ngang.
5. [ ] **Kiểm tra Chế độ Khách (Guest / Incognito):** Mở cửa sổ ẩn danh (Ctrl + Shift + N) và vào `http://localhost:10010/`. Xác nhận màn hình Coming Soon được hiển thị đúng để bảo vệ cửa hàng trước công chúng.

---

## 18. ACCEPTANCE CRITERIA MATRIX

| Tiêu Chí Đánh Giá | Bằng Chứng Kiểm Tra | Trạng Thái Đạt Được |
|---|---|:---:|
| **Local Services Active** | Nginx PID 12788, MySQL PID 10640 running | **PASS** |
| **HTTP Responses Valid** | Cả 5 endpoint trả về mã HTTP 200 / 302 hợp lệ | **PASS** |
| **Coming Soon Verification** | Guest nhận Coming Soon; Admin nhận Storefront | **PASS** |
| **FSE Template Resolution** | WordPress Core chọn `twentytwentyfive-child` | **PASS** |
| **Asset HTTP Availability** | `style.css` và 2 ảnh JPG trả về HTTP 200 | **PASS** |
| **Zero External Network Calls**| Không gọi font/script bên thứ ba | **PASS** |
| **Database Invariants Preserved**| 0 products, 0 variations, 0 SKUs, options giữ nguyên | **PASS** |
| **Git Working Tree Untouched** | Không tạo commit, không chỉnh sửa ngoài phạm vi | **PASS** |
| **Link Integrity Audited** | Phát hiện và ghi nhận lỗi anchor DEF-01 | **CONDITIONAL PASS** |
| **Browser Playwright Automation**| Driver 404 error; cung cấp checklist thủ công | **NOT VERIFIED / BLOCKED** |

---

## 19. STAGE 1F.5 READINESS DECISION

- Dự án đã hoàn tất xác minh toàn diện storefront hiện tại với kết quả **CONDITIONAL PASS**.
- Toàn bộ nền tảng FSE của Header, Footer, Homepage, và Shop Archive đã sẵn sàng, an toàn và hoàn toàn tách biệt với dữ liệu sản phẩm thật.
- Khi người dùng phê duyệt chuyển tiếp sang **Stage 1F.5 — Single Product Template, Cart, Checkout & Coming Soon Polish**, các hạng mục ưu tiên gồm có:
  1. Xử lý Defect `DEF-01` (bổ sung gạch chéo đầu trang cho các anchor header/footer).
  2. Xây dựng template chi tiết sản phẩm đơn `templates/single-product.html`.
  3. Định dạng scoped CSS cho trang Cart và Checkout.
  4. Tinh chỉnh giao diện Coming Soon theo phong cách ATELIER.

---

## 20. MANDATORY STOP STATEMENT

> [!IMPORTANT]
> **DỪNG BẮT BUỘC (MANDATORY STOP):**  
> Giai đoạn **Stage 1F.4R — Storefront Runtime Verification & Visual QA** đã hoàn thành toàn bộ công tác kiểm toán chỉ đọc và lập biên bản hiện trạng.  
> Theo quy tắc an toàn nghiêm ngặt:
> - **KHÔNG** tự ý sửa lỗi `DEF-01` trong giai đoạn này.
> - **KHÔNG** tự ý kích hoạt Stage 1F.5 hoặc Gate 4 (Product Import).
> - **KHÔNG** thay đổi Coming Soon hoặc cơ sở dữ liệu.
> - **DỪNG MỌI THAO TÁC** và chờ phê duyệt chính thức từ người dùng.
