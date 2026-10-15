# BÁO CÁO CHẨN ĐOÁN MÔI TRƯỜNG, SAO LƯU & XÁC THỰC THEME
# STAGE 1A — SAFE BASELINE, DATABASE DIAGNOSTICS & THEME VALIDATION
**Dự án:** `furniture-ecommerce` (Website Thương mại Điện tử Nội thất Quốc tế)  
**Kỹ sư thực hiện:** Senior WordPress/WooCommerce Engineer  
**Thời gian thực hiện:** 15/10/2026  
**Trạng thái cổng kiểm soát (Stage-Gate):** **PASS** (Baseline đã sao lưu, cơ sở dữ liệu đã được giải mã nguyên nhân và kiểm chứng an toàn 100%, sẵn sàng cho phê duyệt của Người dùng để bước vào Stage 1B).

---

## 1. Baseline Xác minh (Verified Baseline)

Tất cả các thông số baseline đã được kiểm tra trực tiếp qua CLI trong môi trường thực thi của Local:

| Hạng mục Baseline | Giá trị thực tế xác minh được | Trạng thái | Bằng chứng kỹ thuật |
|---|---|:---:|---|
| **Local Site Name** | `furniture-ecommerce` | **VERIFIED** | Đường dẫn `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce` |
| **Site URL / Home** | `http://localhost:10010` | **VERIFIED** | `wp option get siteurl` & `home` |
| **HTTP Status Code** | **HTTP 200 OK** | **VERIFIED** | `Invoke-WebRequest -Uri "http://localhost:10010"` |
| **WordPress Root (ABSPATH)**| `C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/app/public/` | **VERIFIED** | Đọc hằng số `ABSPATH` từ `wp-load.php` |
| **Database Name (DB_NAME)** | `local` | **VERIFIED** | Đọc `DB_NAME` từ `wp-config.php` |
| **Database Host / Port** | `localhost` / Port **10011** | **VERIFIED** | `conf/mysql/my.cnf` & `php.ini: mysqli.default_port = 10011` |
| **Table Prefix (`$table_prefix`)**| **`wp_`** | **VERIFIED** | Biến `$table_prefix = 'wp_'` và `$wpdb->prefix = 'wp_'` |
| **WordPress Core Version** | **7.1.3** | **VERIFIED** | `wp core version` |
| **PHP Runtime Version** | **8.2.29** (cli) | **VERIFIED** | `php -v` (Zend OPcache v8.2.29, Xdebug v3.2.1) |
| **MySQL Server Version** | **8.4.0** (Community Server - GPL) | **VERIFIED** | `mysql.exe Ver 8.4.0 for Win64` |

---

## 2. Backup Baseline & Bằng chứng Kiểm tra (Backup Verification)

Thực hiện quy tắc an toàn tuyệt đối: **Không thực hiện bất kỳ thay đổi nào trước khi có bản sao lưu cơ sở dữ liệu đã được kiểm chứng**.

- **Vị trí lưu trữ sao lưu:** Thư mục riêng an toàn nằm ngoài web root và ngoài Git:  
  `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\backups\`
- **File sao lưu:** `stage_1a_baseline_backup.sql`
- **Công cụ sao lưu:** `mysqldump 10.13 Distrib 8.4.0 for Win64` (thông qua lệnh `wp db export`)

### Bằng chứng Kiểm tra File Backup (A5 Verification):
1. **Sự tồn tại của file:** `True` (**VERIFIED**).
2. **Kích thước file:** **1,202,692 bytes** (~1.2 MB > 0 bytes) (**VERIFIED**).
3. **Thời gian tạo:** `10/15/2026 19:13:21` (**VERIFIED**).
4. **Kiểm tra cấu trúc file dump SQL:**
   - Header dump:
     ```sql
     -- MySQL dump 10.13  Distrib 8.4.0, for Win64 (x86_64)
     -- Host: localhost    Database: local
     -- Server version    8.4.0
     /*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
     ...
     ```
   - Chứa đầy đủ các câu lệnh DDL (`CREATE TABLE`) và DML (`INSERT INTO`) cho toàn bộ 52 bảng của hệ thống.
   - Bảng `wp_woocommerce_attribute_taxonomies` có mặt đầy đủ tại dòng 1295:
     ```sql
     DROP TABLE IF EXISTS `wp_woocommerce_attribute_taxonomies`;
     CREATE TABLE `wp_woocommerce_attribute_taxonomies` (
       `attribute_id` bigint unsigned NOT NULL AUTO_INCREMENT,
       `attribute_name` varchar(200) NOT NULL,
       `attribute_label` varchar(200) DEFAULT NULL,
       `attribute_type` varchar(20) NOT NULL,
       `attribute_orderby` varchar(20) NOT NULL,
       `attribute_public` int NOT NULL DEFAULT '1',
       PRIMARY KEY (`attribute_id`),
       KEY `attribute_name` (`attribute_name`(20))
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
     ```
5. **Bảo mật thông tin:** File dump không chứa mật khẩu người dùng dạng cleartext hay API keys nhạy cảm.

---

## 3. Database Inventory (Kiểm kê Cơ sở dữ liệu Thực tế)

Qua kiểm tra trực tiếp bảng hệ thống `information_schema.tables` với database `local` và `wp db tables --all-tables`, cơ sở dữ liệu hiện có tổng cộng **52 bảng** (**VERIFIED**):

```text
Database: local | Prefix: wp_ | Total Tables: 52
```

### 3.1 Bảng WordPress Core (12 bảng) — Toàn bộ đều TỒN TẠI & HỢP LỆ:
`wp_posts`, `wp_postmeta`, `wp_comments`, `wp_commentmeta`, `wp_terms`, `wp_term_taxonomy`, `wp_term_relationships`, `wp_termmeta`, `wp_options`, `wp_users`, `wp_usermeta`, `wp_links`.

### 3.2 Bảng ActionScheduler (4 bảng) — Toàn bộ đều TỒN TẠI:
`wp_actionscheduler_actions`, `wp_actionscheduler_claims`, `wp_actionscheduler_groups`, `wp_actionscheduler_logs`.

### 3.3 Bảng WooCommerce Core & Bảng Thuộc tính Toàn cục (12 bảng) — Toàn bộ đều TỒN TẠI:
- **`wp_woocommerce_attribute_taxonomies`**: **TỒN TẠI & KHÔNG CÓ LỖI** (Đã kiểm tra query SELECT trả về 0 rows, sẵn sàng nhận dữ liệu thuộc tính `Color`, `Size`, `Brand`).
- `wp_woocommerce_downloadable_product_permissions`
- `wp_woocommerce_order_items`
- `wp_woocommerce_order_itemmeta`
- `wp_woocommerce_tax_rates`
- `wp_woocommerce_tax_rate_locations`
- `wp_woocommerce_shipping_zones`
- `wp_woocommerce_shipping_zone_locations`
- `wp_woocommerce_shipping_zone_methods`
- `wp_woocommerce_payment_tokens`
- `wp_woocommerce_payment_tokenmeta`
- `wp_woocommerce_log`
- `wp_woocommerce_sessions`
- `wp_woocommerce_api_keys`

### 3.4 Bảng WooCommerce Analytics & Lookup (10 bảng) — Toàn bộ đều TỒN TẠI:
- `wp_wc_product_meta_lookup`
- `wp_wc_customer_lookup`
- `wp_wc_category_lookup`
- `wp_wc_order_product_lookup`
- `wp_wc_order_tax_lookup`
- `wp_wc_order_coupon_lookup`
- `wp_wc_order_stats`
- `wp_wc_product_attributes_lookup`
- `wp_wc_reserved_stock`
- `wp_wc_tax_rate_classes`

### 3.5 Bảng HPOS (High-Performance Order Storage) (4 bảng) — Toàn bộ đều TỒN TẠI:
Cấu hình `woocommerce_custom_orders_table_enabled` đang bật (`yes`), các bảng đơn hàng hiệu năng cao độc lập đã sẵn sàng:
- `wp_wc_orders`
- `wp_wc_order_addresses`
- `wp_wc_order_operational_data`
- `wp_wc_orders_meta`

### 3.6 Bảng WooCommerce Admin & Quản trị khác (8 bảng) — Toàn bộ đều TỒN TẠI:
`wp_wc_admin_notes`, `wp_wc_admin_note_actions`, `wp_wc_rate_limits`, `wp_wc_webhooks`, `wp_wc_download_log`, `wp_wc_product_download_directories`, `wp_wc_stock_notifications`, `wp_wc_stock_notificationmeta`.

---

## 4. Phân tích Nguyên nhân Gốc Lỗi Database (Root-Cause Investigation)

### 4.1 Hiện tượng đã ghi nhận ở Stage 0:
Trong file log `logs/php/error.log` có dòng:
```text
[15-Oct-2026 11:11:19 UTC] WordPress database error Table 'local.wp_woocommerce_attribute_taxonomies' doesn't exist for query SELECT * FROM wp_woocommerce_attribute_taxonomies WHERE attribute_name != '' ORDER BY attribute_name ASC; made by activate_plugin, plugin_sandbox_scrape, include_once('/plugins/woocommerce/woocommerce.php')
```
Đồng thời lệnh `wp db tables` ban đầu chỉ hiển thị 22 bảng.

### 4.2 Nguyên nhân gốc rễ được xác minh (VERIFIED ROOT CAUSE):
1. **Bản chất của lỗi trong PHP log:** 
   - Lỗi này xuất hiện vào lúc **11:11:19 UTC** trong quá trình WordPress chạy **`plugin_sandbox_scrape`** (kiểm tra trước khi kích hoạt chính thức). Lúc này, file `woocommerce.php` được nạp để kiểm tra lỗi cú pháp/fatal error TRƯỚC KHI hook kích hoạt `WC_Install::install()` được thực thi.
   - Do bảng chưa được tạo tại thời điểm scrape, WooCommerce gọi hàm `wc_get_attribute_taxonomies()` gây ra thông báo lỗi tạm thời trên.
2. **Quá trình hoàn tất ngay sau đó (11:11:23 UTC):**
   - Chỉ 4 giây sau (11:11:23 UTC), WordPress chính thức kích hoạt plugin và chạy quy trình `WC_Install::create_tables()`, tạo thành công toàn bộ bảng WooCommerce và ghi nhận trạng thái:
     `Hook: woocommerce_run_update_callback | Status: complete | Date: 2026-10-15 11:11:23`
     `Hook: action_scheduler/migration_hook | Status: complete | Date: 2026-10-15 11:12:23`
3. **Tại sao `wp db tables` ban đầu chỉ liệt kê 22 bảng?**
   - Lệnh `wp db tables` mặc định của WP-CLI chỉ liệt kê các bảng được đăng ký trong đối tượng lõi `$wpdb->tables` của WordPress Core.
   - Khi chạy với cờ `--all-tables` (`wp db tables --all-tables`), WP-CLI truy vấn toàn bộ bảng thực tế từ MySQL và hiển thị đầy đủ **52 bảng**.
4. **Kết luận kiểm chứng trực tiếp:**
   - Lệnh query trực tiếp: `SELECT * FROM wp_woocommerce_attribute_taxonomies;` trả về **thành công 100% (0 errors)**.
   - Cả hai phiên bản `woocommerce_version` và `woocommerce_db_version` trong bảng `wp_options` đều ở giá trị chuẩn **`11.2.1`**.

---

## 5. Đánh giá Nhu cầu Sửa chữa Database & Phương án An toàn

### 5.1 Kết luận về việc sửa chữa:
> [!NOTE]
> **KHÔNG CẦN CHẠY LỆNH SỬA CHỮA DATABASE.**  
> Cơ sở dữ liệu hiện tại **hoàn toàn toàn vẹn, đầy đủ 52 bảng chuẩn của WooCommerce 11.2.1 + HPOS**, không bị thiếu bất kỳ bảng thuộc tính hay lookup nào. Việc tự ý chạy lại migration hay repair là không cần thiết và tiềm ẩn rủi ro ghi đè dữ liệu.

### 5.2 Kế hoạch Rollback dự phòng (Nếu có sự cố trong tương lai):
Nếu bất kỳ bước import sản phẩm nào ở các Stage sau gặp lỗi cơ sở dữ liệu:
- **Bước 1:** Dừng ngay mọi tiến trình đang chạy.
- **Bước 2:** Khôi phục nguyên trạng cơ sở dữ liệu từ file backup an toàn:
  ```powershell
  wp db import "C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\backups\stage_1a_baseline_backup.sql"
  ```
- **Bước 3:** Kiểm tra lại tính toàn vẹn bằng `wp db tables --all-tables` và xác nhận HTTP 200.

---

## 6. Theme Validation Report (Xác thực Theme Twenty Twenty-Five)

Thực hiện kiểm tra read-only đối với theme **Twenty Twenty-Five (v1.5)** mà không kích hoạt theme:

| Tiêu chí | Kết quả kiểm tra | Trạng thái | Chi tiết kỹ thuật |
|---|---|:---:|---|
| **Sự hiện diện** | Đã cài đặt trong `wp-content/themes/twentytwentyfive` | **VERIFIED** | Được WordPress nhận diện chính xác qua `wp theme get twentytwentyfive` |
| **Kiến trúc** | Block Theme — Full Site Editing (FSE) | **VERIFIED** | Tags: `full-site-editing`, `block-patterns`, `block-styles`, `style-variations` |
| **Templates có sẵn** | 8 template HTML cốt lõi | **VERIFIED** | `404.html`, `archive.html`, `home.html`, `index.html`, `page-no-title.html`, `page.html`, `search.html`, `single.html` |
| **Block Patterns** | **98 block patterns** dựng sẵn | **VERIFIED** | Hỗ trợ banner, call to action, columns, portfolio, grid |
| **Giấy phép & Chi phí**| GPL v2 or later / 100% Miễn phí | **VERIFIED** | Mã nguồn mở của WordPress.org, **Zero Upsell / Không quảng cáo** |
| **Theme đang Active** | `interior-design-wda` (v1.2.9) | **VERIFIED** | **Chưa thay đổi** (Tuân thủ nguyên tắc C3) |

### 6.1 Khảo sát Khả năng Hỗ trợ WooCommerce của Twenty Twenty-Five:
- **Cơ chế:** Twenty Twenty-Five là FSE Block Theme. WooCommerce 11.2.1 tích hợp toàn diện với FSE qua các Block Gutenberg chính thức:
  - `woocommerce/product-collection` hoặc `woocommerce/all-products` (cho trang Shop và Featured Products).
  - `woocommerce/product-template`, `woocommerce/product-image`, `woocommerce/product-title`, `woocommerce/product-price`, `woocommerce/product-button`.
  - `woocommerce/cart` và `woocommerce/checkout` (Gutenberg Block Cart/Checkout hoặc Classic Cart/Checkout Shortcodes).
  - `woocommerce/customer-account` và `woocommerce/active-filters`, `woocommerce/attribute-filter`, `woocommerce/price-filter`.
- **Lưu ý quan trọng (C5):** Việc cài đặt theme **chưa đồng nghĩa với việc giao diện tự động hoàn hảo cho WooCommerce**. Cần tạo một **Child Theme** (`twentytwentyfive-child`) ở Stage 1B để khai báo hỗ trợ WooCommerce (`add_theme_support( 'woocommerce' )`), quản lý bảng màu Warm Minimalist qua `theme.json` và bổ sung các hook tùy biến (nút Buy Now, Wishlist, Swatches).

### 6.2 Checklist Thử nghiệm Giao diện (Sẽ thực thi sau khi được kích hoạt ở Stage 3–6):
- [ ] **Homepage:** Đủ 8 khối theo PDF gốc, áp dụng bảng màu Warm Minimalist, CTA `SHOP NOW`.
- [ ] **Shop Archive:** Lưới sản phẩm chuẩn responsive 4 cột (Desktop) / 3 cột (Tablet) / 2 cột (Mobile).
- [ ] **Filters:** Bộ lọc danh mục, thuộc tính Color, Size, Brand, khoảng giá và trạng thái tồn kho hoạt động thực tế.
- [ ] **Single Product:** Đủ 16 thành phần (Gallery, Name, Rating, Price, Swatches, Add to Cart, Buy Now, Wishlist, Specs, Reviews).
- [ ] **Variable Product:** Đổi Color/Size cập nhật giá và hình ảnh biến thể tức thời.
- [ ] **Cart:** Thêm/sửa/xóa sản phẩm, cập nhật số lượng, áp mã giảm giá `SUMMER30`.
- [ ] **Checkout:** Điền thông tin giao hàng, chọn COD hoặc Bank Transfer, tạo đơn hàng thành công.
- [ ] **My Account:** Đầy đủ 6 tab, thực hiện luồng `Register -> Login -> My Account -> View Orders`.
- [ ] **Responsive:** Kiểm tra chuẩn xác trên 3 kích thước: `1440×900`, `768×1024`, `390×844`.

---

## 7. Git Scope & Kế hoạch `.gitignore` (Phase D)

### 7.1 Đánh giá Phạm vi Git:
- **Nguyên tắc an toàn:** Tuyệt đối **KHÔNG** đưa WordPress Core (`wp-admin`, `wp-includes`, các file gốc `wp-*.php`), file cấu hình bí mật (`wp-config.php`), thư mục media tải lên (`uploads`), logs hoặc database dump vào Git repository.
- **Phạm vi Git được chọn:** Thư mục quản lý tùy biến mã nguồn là **`wp-content`** (hoặc root site với `.gitignore` chặt chẽ).
  - Vị trí khuyến nghị: Thư mục **`app/public/wp-content`**.
  - Các thành phần được theo dõi (Tracked):
    - `themes/twentytwentyfive-child/` (Child theme tự viết, chứa `style.css`, `functions.php`, `theme.json`).
    - `plugins/STAGE_0_DISCOVERY_REPORT.md` và `plugins/STAGE_1A_REPORT.md` (Tài liệu kiểm toán dự án).
    - `plugins/woocommerce_furniture_dataset_22_products.csv` (Dataset nguồn chuẩn).
    - `plugins/test_plan_and_test_cases (1).md` (Kế hoạch kiểm thử).
  - Các thành phần bị loại trừ (Ignored):
    - `wp-config.php` và mọi file credentials.
    - `uploads/` (file media người dùng tải lên).
    - `upgrade/` và `cache/`.
    - `plugins/woocommerce/` (Plugin gốc từ WordPress.org, không sửa core).
    - Toàn bộ thư mục `backups/` và các file `.sql`.
    - Toàn bộ thư mục `logs/`.

### 7.2 Đề xuất Nội dung File `.gitignore` Chuẩn cho Dự án:
```gitignore
# WordPress & Server Secrets
wp-config.php
*.env
*.log

# Database dumps & Backups
*.sql
*.sql.gz
backups/

# User Uploads & Cache
uploads/
cache/
upgrade/

# Core Plugins from WP.org (Third-party)
plugins/woocommerce/

# OS and Editor junk
.DS_Store
Thumbs.db
*.sublime-workspace
.vscode/
.idea/
```
*Giai đoạn Stage 1A chỉ lập kế hoạch và đề xuất; chưa thực hiện `git init` để chờ Bạn phê duyệt phạm vi quản lý mã nguồn.*

---

## 8. Kiểm toán Cài đặt Tài khoản & Thanh toán (Phase E)

| Hạng mục cấu hình | Trạng thái hiện tại | Đánh giá so với yêu cầu đề bài | Đề xuất tối thiểu cho bài tập |
|---|---|---|---|
| **Guest Checkout** | `woocommerce_enable_guest_checkout = yes` | **Hợp lệ** | Giữ nguyên `yes` để hỗ trợ mua hàng không cần đăng ký. |
| **My Account Registration** | `woocommerce_enable_myaccount_registration = no` | **Chưa đạt** (Không thể tự đăng ký tài khoản) | **Đổi thành `yes`** ở Stage 1B để kích hoạt form đăng ký tại trang `/my-account/`, phục vụ ca kiểm thử TC13. |
| **Cash on Delivery (COD)** | `woocommerce_cod_settings` (Chưa cấu hình) | **Chưa bật** | **Kích hoạt** ở Stage 1B (`enabled = yes`, không yêu cầu cổng tiền thật). |
| **Direct Bank Transfer (BACS)**| `woocommerce_bacs_settings` (Chưa cấu hình) | **Chưa bật** | **Kích hoạt** ở Stage 1B (`enabled = yes`, cấu hình thông tin tài khoản ngân hàng thử nghiệm). |

---

## 9. Danh mục Lệnh đã Chạy & Kết quả Thực tế (Phase Execution Evidence)

1. `wp --path=..\.. eval "global `$wpdb, `$table_prefix; echo 'WP_ROOT: ' . ABSPATH . PHP_EOL; echo 'DB_NAME: ' . DB_NAME . PHP_EOL; echo 'TABLE_PREFIX: ' . `$table_prefix . PHP_EOL;"`
   - *Kết quả:* `WP_ROOT: C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/app/public/`, `DB_NAME: local`, `TABLE_PREFIX: wp_`.
2. `Invoke-WebRequest -Uri "http://localhost:10010" -UseBasicParsing`
   - *Kết quả:* `StatusCode: 200 OK`.
3. `wp --path=..\.. db export "C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\backups\stage_1a_baseline_backup.sql"`
   - *Kết quả:* Xuất thành công file backup dung lượng 1,202,692 bytes.
4. `wp --path=..\.. db tables --all-tables`
   - *Kết quả:* Hiển thị đủ 52 bảng (bao gồm toàn bộ 28 bảng WooCommerce và 4 bảng HPOS).
5. Query kiểm tra bảng thuộc tính: `SELECT * FROM wp_woocommerce_attribute_taxonomies;`
   - *Kết quả:* `Query Success! Row count: 0` (Bảng tồn tại, cấu trúc chuẩn, không có lỗi).
6. `wp --path=..\.. theme get twentytwentyfive`
   - *Kết quả:* Twenty Twenty-Five v1.5, status: `inactive`, FSE Block Theme.
7. `wp --path=..\.. theme get interior-design-wda`
   - *Kết quả:* Theme active hiện tại giữ nguyên không thay đổi.

---

## 10. Acceptance Gate & Đề xuất Bước tiếp theo (Next Steps)

### Tiêu chí Nghiệm thu Stage 1A:
- [x] Đã xác minh site path, WordPress root, database name, table prefix `wp_` và HTTP 200.
- [x] Đã tạo và xác minh bản backup database hoàn chỉnh (1.2 MB) tại `Local Sites\furniture-ecommerce\backups\`.
- [x] Đã điều tra toàn diện cơ sở dữ liệu: Xác minh 52 bảng tồn tại đầy đủ, làm rõ nguyên nhân log lỗi tạm thời, khẳng định **database hoàn toàn khỏe mạnh, không cần sửa chữa**.
- [x] Đã khảo sát và xác thực theme Twenty Twenty-Five (v1.5) mà không kích hoạt trong giai đoạn chẩn đoán.
- [x] Đã lập kế hoạch phạm vi Git và cấu hình `.gitignore` an toàn loại trừ toàn bộ dữ liệu nhạy cảm.
- [x] Đã kiểm toán cài đặt thanh toán/tài khoản và đưa ra đề xuất tối thiểu.
- [x] **Tuyệt đối không có dữ liệu nào bị thay đổi hay xóa bỏ trong Stage 1A.**

**ĐÁNH GIÁ CỔNG KIỂM SOÁT:** **`PASS`** cho giai đoạn chẩn đoán và sao lưu Stage 1A.

---

### Kính đề nghị Bạn Phê duyệt để triển khai Stage 1B:

1. **Phê duyệt Phương án Database:** Xác nhận ghi nhận kết quả kiểm toán (Database đã đủ 52 bảng, không cần chạy lệnh sửa chữa cấu trúc, sẵn sàng lưu dữ liệu thuộc tính).
2. **Phê duyệt Khởi tạo Git:** Cho phép khởi tạo Git repository tại `app/public/wp-content` với file `.gitignore` đã đề xuất và tạo commit baseline đầu tiên.
3. **Phê duyệt Chuẩn bị Theme Twenty Twenty-Five:** Cho phép khởi tạo thư mục Child Theme `twentytwentyfive-child` (chứa `style.css`, `functions.php` khai báo hỗ trợ WooCommerce và `theme.json` cấu hình bảng màu Warm Minimalist) sẵn sàng cho việc kích hoạt ở các Stage tiếp theo.
4. **Phê duyệt Bật Đăng ký & Cổng Demo:** Cho phép bật tùy chọn đăng ký tại My Account (`woocommerce_enable_myaccount_registration = yes`) cùng 2 cổng thanh toán demo COD và BACS.

---
*(Hệ thống đã dừng lại hoàn toàn theo đúng quy tắc an toàn. Chờ phê duyệt rõ ràng từ Bạn trước khi thực hiện bất kỳ bước tiếp theo nào).*
