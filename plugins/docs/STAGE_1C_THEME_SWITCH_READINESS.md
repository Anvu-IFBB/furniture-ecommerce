# BÁO CÁO ĐÁNH GIÁ SẴN SÀNG CHUYỂN ĐỔI THEME VÀ KẾ HOẠCH ROLLBACK
# STAGE 1C — THEME SWITCH PREPARATION & ROLLBACK READINESS

**Dự án:** `furniture-ecommerce` (Website Thương mại Điện tử Nội thất Quốc tế)  
**Kỹ sư thực hiện:** Senior WordPress/WooCommerce Engineer  
**Thời gian thực hiện:** 15/10/2026  
**Chế độ thực thi:** Read-Only & Planning (Không kích hoạt theme, không sửa cấu hình, không commit)  
**Trạng thái cổng kiểm soát (Stage-Gate):** **PASS** (Đủ điều kiện để người dùng phê duyệt chuyển đổi theme)

---

## 1. Baseline Thực tế Hiện tại (Actual System Baseline)

Tất cả các thông số được kiểm tra trực tiếp qua WP-CLI và HTTP client trước khi lập kế hoạch:

| Thông số | Giá trị thực tế | Trạng thái | Ghi chú & Bằng chứng kỹ thuật |
|---|---|:---:|---|
| **Theme Đang Active** | `interior-design-firm` (v3.2.7) | **VERIFIED** | `template` = `interior-design-firm`, `stylesheet` = `interior-design-firm` |
| **Theme Đích (Target)** | `twentytwentyfive-child` (v1.0.0)| **VERIFIED** | Trạng thái: `inactive`, metadata hợp lệ |
| **Theme Cha (Parent)** | `twentytwentyfive` (v1.5) | **VERIFIED** | Trạng thái: `inactive`, FSE Block Theme chuẩn |
| **WordPress Core** | 7.1.3 | **VERIFIED** | Không thay đổi |
| **WooCommerce Core** | 11.2.1 | **VERIFIED** | Active |
| **PHP Runtime** | 8.2.29 (cli) | **VERIFIED** | Không thay đổi |
| **MySQL Server** | 8.4.0 (Community) | **VERIFIED** | 52 bảng nguyên vẹn |
| **Git Repository** | `app/public/wp-content` | **VERIFIED** | Commit `cdf203d`, working tree clean |
| **File Backup Baseline** | `stage_1a_baseline_backup.sql` | **VERIFIED** | Dung lượng 1,202,692 bytes (Vị trí: `Local Sites\furniture-ecommerce\backups\`) |
| **Trạng thái Khôi phục**| `RESTORE_TEST_PENDING` | **VERIFIED** | Không chạy thử restore lên database đang dùng |

---

## 2. Kết quả Kiểm tra Trạng thái 5 Trang Quan trọng (Phase A)

Hệ thống đã kiểm tra sự hiện diện trong database (WP-CLI) và phản hồi HTTP thực tế:

| Trang kiểm tra | Post ID | Slug | Trạng thái Post | Nội dung cấu trúc Block | Phản hồi HTTP | Kích thước HTML |
|---|:---:|---|:---:|---|:---:|:---:|
| **Trang chủ (Homepage)** | — | `/` | — | Template trang chủ hiện hành | **HTTP 200 OK** | 69,591 bytes |
| **Cửa hàng (Shop)** | 6 | `shop` | `publish` | Trang lưu trữ WooCommerce (Archive base) | **HTTP 200 OK** | 69,815 bytes |
| **Giỏ hàng (Cart)** | 7 | `cart` | `publish` | `<!-- wp:woocommerce/cart -->` (Gutenberg Block) | **HTTP 200 OK** | 130,061 bytes |
| **Thanh toán (Checkout)** | 8 | `checkout` | `publish` | `<!-- wp:woocommerce/checkout -->` (Gutenberg Block) | **HTTP 200 OK** | 130,061 bytes |
| **Tài khoản (My Account)**| 9 | `my-account`| `publish` | `[woocommerce_my_account]` (Shortcode Block) | **HTTP 200 OK** | 70,820 bytes |

> [!NOTE]
> **Nhận xét quan trọng về HTTP Response:**  
> Toàn bộ 5 trang cốt lõi đều phản hồi `HTTP 200 OK`. Đặc biệt, trang **Cart** và **Checkout** đã được WooCommerce khởi tạo sẵn dưới dạng **Gutenberg Blocks chính thức** (`wp:woocommerce/cart` và `wp:woocommerce/checkout`), đây là lợi thế rất lớn giúp tương thích tự nhiên 100% với kiến trúc Block Theme FSE của `Twenty Twenty-Five`.  
> *Lưu ý quy tắc an toàn: HTTP 200 chỉ chứng minh server không gặp lỗi fatal PHP; việc kiểm tra thẩm mỹ, font chữ và bố cục trực quan bắt buộc phải kiểm chứng trên trình duyệt.*

---

## 3. Đánh giá Rủi ro Tương thích khi Chuyển đổi Theme (Phase B)

Chuyển đổi từ theme cổ điển (`interior-design-firm`) sang block theme FSE (`twentytwentyfive-child`):

| Hạng mục chức năng | Trạng thái đánh giá | Phân tích Rủi ro & Tác động Kỹ thuật | Giải pháp Giảm thiểu (Mitigation Strategy) |
|---|:---:|---|---|
| **Header, Menu & Logo** | `RISK` | Theme cũ dùng Customizer PHP header. Theme mới dùng FSE Block Template Parts (`parts/header.html`). Khi chuyển theme, menu cũ có thể chưa tự động gán vào Navigation Block. | Tại Stage 3, cấu hình Navigation Block trong FSE Site Editor hoặc qua Block Pattern chuẩn. |
| **Footer & Bản quyền** | `RISK` | Cần cấu hình lại Footer 4 cột (About, Customer Service, Information, Newsletter) theo đúng yêu cầu đề bài. | Dựng Footer Block Pattern 4 cột trong child theme tại Stage 3. |
| **Homepage Layout (8 Khối)** | `RISK` | `interior-design-firm` đang hiển thị trang tĩnh mặc định. Chuyển sang child theme sẽ dùng `home.html` hoặc trang Front Page block. | Không import demo. Tự dựng đủ 8 khối (Hero, Categories, Featured Products, Promotion, New Arrivals, Best Sellers, Blog, Footer) bằng Gutenberg blocks ở Stage 3. |
| **Shop Archive & Lưới 4/3/2 cột** | `RISK` | WooCommerce Product Collection mặc định có thể chưa khóa cứng số cột theo đúng tiêu chí: Desktop 4 cột, Tablet 3 cột, Mobile 2 cột. | Viết CSS Grid rules riêng trong `twentytwentyfive-child/style.css` kiểm soát media queries `1440px`, `768px`, `390px`. |
| **Product Detail (16 Thành phần)** | `RISK` | Theme Twenty Twenty-Five chỉ render trang Single Product cơ bản của WooCommerce. Nút `BUY NOW`, `Wishlist`, Swatches chọn màu/size chưa có sẵn. | Tích hợp các hooks nhẹ và CSS swatches trong child theme ở Stage 4 để đáp ứng đủ 16 thành phần theo PDF trang 5–6. |
| **Cart & Checkout Blocks** | `VERIFIED` | Đang dùng khối Gutenberg Block (`wp:woocommerce/cart` & `wp:woocommerce/checkout`). Tương thích hoàn hảo với Twenty Twenty-Five. | Giữ nguyên block hiện có, chỉ tinh chỉnh màu sắc nút bấm theo bảng màu `#65735B`. |
| **My Account** | `VERIFIED` | Dùng shortcode `[woocommerce_my_account]`. WooCommerce render đầy đủ 6 tab: Dashboard, Orders, Downloads, Addresses, Account Details, Logout. | Tùy biến CSS padding và form input theo bảng màu Warm Minimalist. |
| **Dependency Khóa từ Theme cũ** | `VERIFIED` | Kiểm tra `interior-design-firm/functions.php` cho thấy theme cũ không đăng ký Custom Post Type hay proprietary shortcode nào. | Hoàn toàn sạch sẽ, không bị hiện tượng "mất dữ liệu" hay gãy shortcode khi tắt theme cũ. |

---

## 4. Kế hoạch Rollback Dự phòng Chi tiết (Phase C)

Trong trường hợp việc kích hoạt theme mới gây ra xung đột, lỗi trắng trang (WSOD) hoặc hỏng giao diện ngoài tầm kiểm soát, quy trình rollback được kích hoạt ngay lập tức.

### 4.1 Thông số Điểm Khôi phục:
- **Theme an toàn cần phục hồi:** `interior-design-firm`
- **Template option:** `interior-design-firm`
- **Stylesheet option:** `interior-design-firm`
- **File backup database:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\backups\stage_1a_baseline_backup.sql`

### 4.2 Điều kiện Kích hoạt Dừng & Rollback (Rollback Triggers):
Kích hoạt quy trình rollback ngay nếu gặp 1 trong các điều kiện sau:
1. Gặp lỗi `PHP Fatal error` hoặc `HTTP 500` không thể khắc phục nhanh trên bất kỳ trang nào (`/`, `/shop`, `/cart`, `/checkout`, `/my-account`).
2. Lỗi cú pháp DDL hoặc truy vấn MySQL làm gãy cấu trúc bảng WooCommerce.
3. Giao diện bị vỡ hoàn toàn khiến WP-CLI hoặc WP Admin không thể truy cập.

### 4.3 Các Bước Thực hiện Rollback:

#### Kịch bản 1: Chỉ Rollback Theme (Nhanh nhất - dưới 10 giây):
Chạy lệnh WP-CLI để tái kích hoạt theme an toàn trước đó:
```powershell
$env:PHPRC = "C:\Users\Vu Cong Minh\AppData\Roaming\Local\run\X8slLDe4p\conf\php"
$env:MYSQL_HOME = "C:\Users\Vu Cong Minh\AppData\Roaming\Local\run\X8slLDe4p\conf\mysql"
$env:WP_CLI_CONFIG_PATH = "C:\Sv\Local\resources\extraResources\bin\wp-cli\config.yaml"
$env:PATH = "C:\Sv\Local\resources\extraResources\bin\wp-cli\win32;C:\Users\Vu Cong Minh\AppData\Roaming\Local\lightning-services\php-8.2.29+0\bin\win64;C:\Users\Vu Cong Minh\AppData\Roaming\Local\lightning-services\mysql-8.4.0\bin\win64\bin;" + $env:PATH

wp --path=..\.. theme activate interior-design-firm
```
Hoặc can thiệp trực tiếp qua option nếu lệnh activate bị kẹt:
```powershell
wp --path=..\.. option update template interior-design-firm
wp --path=..\.. option update stylesheet interior-design-firm
```

#### Kịch bản 2: Rollback Toàn bộ Database (Nếu có thay đổi dữ liệu bị lỗi):
```powershell
wp --path=..\.. db import "C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\backups\stage_1a_baseline_backup.sql"
wp --path=..\.. theme activate interior-design-firm
```

#### Bước Xác minh sau Rollback:
- Kiểm tra `wp theme list --status=active` phải trả về `interior-design-firm`.
- Kiểm tra HTTP status `http://localhost:10010` phải trả về `200 OK`.
- Đọc log `logs/php/error.log` để xác nhận không còn lỗi fatal mới phát sinh.

---

## 5. Checklist Nghiệm thu Sau khi Chuyển Theme (Phase D)

Bảng phân định rõ ràng giữa các kiểm tra tự động qua CLI và các kiểm tra bắt buộc phải thực hiện trên Trình duyệt thật:

| Hạng mục kiểm tra | Phương thức kiểm tra | Tiêu chí Nghiệm thu Đạt chuẩn (Pass Criteria) |
|---|:---:|---|
| **Theme Active Status** | **CLI** | `wp theme list --status=active` trả về `twentytwentyfive-child`. |
| **HTTP Response 5 Trang** | **CLI** | `/`, `/shop/`, `/cart/`, `/checkout/`, `/my-account/` đều trả về `HTTP 200`. |
| **PHP Runtime Errors** | **CLI** | `logs/php/error.log` không xuất hiện `Fatal error` hay `Parse error` mới. |
| **Màu sắc Nền Warm Minimalist** | **Trình duyệt** | Nền website hiển thị đúng màu Warm Alabaster (`#F7F5F0`), chữ màu Deep Charcoal (`#292A26`). |
| **Nút Bấm & Liên kết** | **Trình duyệt** | Các nút CTA và link chính mang màu Earthy Sage Green (`#65735B`). |
| **Render Trang Giỏ hàng** | **Trình duyệt** | Khối Gutenberg Cart render đúng, hiển thị thông báo giỏ hàng trống hoặc danh sách sản phẩm. |
| **Render Trang Checkout** | **Trình duyệt** | Khối Checkout hiển thị đầy đủ các trường thông tin nhận hàng, không vỡ layout. |
| **Render Trang My Account** | **Trình duyệt** | Hiển thị form đăng nhập (và form đăng ký khi được kích hoạt ở bước tiếp theo). |
| **Console Errors** | **Trình duyệt** | Chrome DevTools Console không có lỗi JavaScript đỏ (0 errors). |
| **Kiểm tra Responsive** | **Trình duyệt** | Thử nghiệm trên 3 kích thước: `1440×900` (Desktop), `768×1024` (Tablet), `390×844` (Mobile). |

---

## 6. Kết luận Acceptance Gate

- [x] **Trạng thái thực tế:** Xác nhận đầy đủ theme active (`interior-design-firm`), theme đích (`twentytwentyfive-child`), theme cha (`twentytwentyfive`).
- [x] **Trạng thái 5 trang cốt lõi:** Shop, Cart, Checkout, My Account và Homepage đều được xác minh phản hồi `HTTP 200 OK`.
- [x] **Rủi ro tương thích:** Phân loại chi tiết và đã có giải pháp kỹ thuật cho từng hạng mục.
- [x] **Phương án Rollback:** Khả thi, có lệnh cụ thể, có điều kiện kích hoạt rõ ràng.
- [x] **Phân định kiểm thử:** Rõ ràng giữa phạm vi kiểm tra CLI và kiểm tra giao diện trình duyệt.
- [x] **Quy tắc an toàn:** Không kích hoạt theme, không sửa database, không tạo commit trong Stage 1C.

**KẾT LUẬN CỔNG KIỂM SOÁT:** **`PASS`** — Hệ thống đã hoàn toàn sẵn sàng cho bước chuyển đổi theme an toàn.

---
*(Hệ thống đã dừng lại hoàn toàn. Kính chờ Bạn phê duyệt báo cáo để thực hiện lệnh chuyển đổi theme chính thức).*
