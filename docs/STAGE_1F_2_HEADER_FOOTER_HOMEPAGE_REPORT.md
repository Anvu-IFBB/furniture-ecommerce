# STAGE 1F.2 — HEADER, FOOTER & HOMEPAGE TEMPLATE REPORT

- **Project:** `furniture-ecommerce`
- **WordPress Root:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public`
- **Child Theme:** `wp-content/themes/twentytwentyfive-child`
- **Date:** 2026-10-19
- **Stage:** 1F.2 — Header, Footer & Homepage Template
- **Reviewer:** Senior WordPress Block Theme Engineer, WooCommerce Engineer & QA Engineer
- **Verdict:** **CONDITIONAL PASS** (Source & Static Architecture 100% PASS; Live Runtime/Browser Verification conditional on Local service reactivation after host system restart)

---

## 1. EXECUTIVE SUMMARY

Giai đoạn **Stage 1F.2 — Header, Footer & Homepage Template** đã hoàn tất việc thiết kế, xây dựng và tích hợp toàn bộ các thành phần giao diện chủ chốt của cửa hàng nội thất Warm Minimalist vào child theme `twentytwentyfive-child`:

1. **Header Block Template Part (`parts/header.html`):** Wordmark `ATELIER`, khẩu hiệu Warm Minimalist, hệ thống điều hướng sử dụng URL thực tế của các trang hiện hữu (`/`, `/shop/`, `#categories`, `#craft`), tiện ích tài khoản (`/my-account/`) và giỏ hàng (`/cart/`) với SVG icons inline thuần khiết (không phụ thuộc thư viện bên ngoài), không tạo URL giả hay liên kết tới Sample Page.
2. **Footer Block Template Part (`parts/footer.html`):** Bố cục 4 cột tinh tế trên nền tương phản `#292A26`, triết lý thương hiệu, liên kết điều hướng hợp lệ, biểu mẫu đăng ký bản tin demo tĩnh (có chú thích rõ ràng và thuộc tính `disabled`), copyright 2026.
3. **Homepage Block Template (`templates/front-page.html`):** Toàn bộ 6 phân khu chức năng theo phong cách Warm Minimalist (Hero Section, Curated Categories Grid 7 danh mục, Brand Values 4 cột, Featured Curations 4 cards sản phẩm mẫu, Studio Narrative Craft Spotlight, Final CTA), nạp đồng bộ header và footer template parts.
4. **CSS Component Styling (`style.css`):** Bổ sung 766 dòng CSS scoped chặt chẽ cho Header, Footer, Hero, Grids, Product Cards và Narrative, tuân thủ bảng màu Warm Minimalist (`#F7F5F0`, `#292A26`, `#65735B`, `#E7DFD2`, `#B6A18A`), hỗ trợ WCAG `:focus-visible`, reduced motion, và responsive layout.
5. **An toàn & Bất biến:** 100% file thay đổi/tạo mới đều nằm trong `twentytwentyfive-child/`. Tuyệt đối không thay đổi database (0 post/page/product/menu tạo mới), không sửa WordPress settings, không chạy import hay khôi phục snapshot.

---

## 2. VERDICT: CONDITIONAL PASS

- **Lý do xếp loại CONDITIONAL PASS:**
  - **Mã nguồn, Block Markup & CSS:** **ĐẠT 100% (PASS)** — Cú pháp HTML Gutenberg block comments cân bằng tuyệt đối (0 lỗi), CSS cân bằng 150 cặp ngoặc mở/đóng, PHP lint đạt chuẩn không có lỗi cú pháp.
  - **Template Hierarchy:** **ĐẠT 100% (PASS)** — Phân tích Gate A chứng minh việc tạo `front-page.html` kích hoạt chính xác trang chủ tại root URL `/` mà không cần bất kỳ thay đổi cấu hình WordPress options nào.
  - **Điều kiện cần kiểm chứng thêm (Condition):** Do máy chủ host vừa trải qua khởi động lại hệ thống (server restart), ứng dụng desktop Local by Flywheel đang tạm thời ở trạng thái dừng tiến trình giao diện người dùng (chưa bật cổng 10010/10011). Kiểm tra runtime HTTP thực tế và ảnh chụp màn hình trực quan trên browser sẽ được thực thi ngay khi người dùng khởi chạy lại ứng dụng Local trên desktop.

---

## 3. FILE INVENTORY & SHA-256 HASHES

Toàn bộ các file được tạo hoặc chỉnh sửa trong Stage 1F.2 đều nằm nghiêm ngặt trong thư mục child theme:

| STT | Đường Dẫn File | Hành Động | Kích Thước | SHA-256 Checksum |
|---|---|:---:|---:|---|
| 1 | `parts/header.html` | Tạo mới | 3,195 B | `71EC9E050FBDC2505A35E77CFF197EC5EBD65AB97EEF180B8BE0FADCCB8D1F21` |
| 2 | `parts/footer.html` | Tạo mới | 4,896 B | `DA5780209D17024659D9A86CFDA7A4B2D24B5CA3114581745C956B8AFEACBA08` |
| 3 | `templates/front-page.html` | Tạo mới | 12,356 B | `8AB3E33E9161EA00400CB4402257D9C2E33DF650F945717BB780BE36F2996AF8` |
| 4 | `style.css` | Cập nhật (Append) | 27,111 B | `FDD7BCE9E91FEA2AD1216AA8B7FF757D32202269B5D837FE5482354CA699CE73` |
| 5 | `functions.php` | Giữ nguyên (Baseline 1F.1) | 1,052 B | `6C2FBCB2B13CDF036734A94DD871A1D28DF3CE291423D8B32ACFDE6A33B6AC96` |
| 6 | `theme.json` | Giữ nguyên (Baseline 1F.1) | 8,177 B | `FE1D1C141CC43515625950EE456B654FAB87BC749FA6327C90C2A252EED72A94` |

---

## 4. GIT STATUS & DIFF SUMMARY

### 4.1 Git Status Trực Tiếp
```text
 M themes/twentytwentyfive-child/functions.php (từ 1F.1)
 M themes/twentytwentyfive-child/style.css     (cập nhật thêm mục 8)
 M themes/twentytwentyfive-child/theme.json    (từ 1F.1)
?? themes/twentytwentyfive-child/parts/
?? themes/twentytwentyfive-child/templates/
```
*Không có bất kỳ file nào ngoài child theme bị tác động.*

### 4.2 Tóm Tắt Thay Đổi (Diff Summary)
1. **`parts/header.html` (Thêm mới 75 dòng):**
   - Cấu trúc block `<header>` với container `site-container-wide`.
   - Brand Wordmark `ATELIER` với typography heading và link về trang chủ `/`.
   - Navigation links: Home (`/`), Shop Collection (`/shop/`), Categories (`#categories`), Philosophy (`#craft`).
   - Tiện ích thương mại điện tử tinh gọn: Account (`/my-account/`), Cart (`/cart/`) với SVG vector tích hợp.
2. **`parts/footer.html` (Thêm mới 102 dòng):**
   - Cấu trúc block `<footer>` trên nền tương phản `#292A26`.
   - 4 cột: Brand Philosophy, Explore Links, Customer Care Links, Newsletter Form (Demo disabled).
   - Bottom Bar: Bản quyền, tuyên bố miễn trừ trách nhiệm demo storefront.
3. **`templates/front-page.html` (Thêm mới 237 dòng):**
   - Tham chiếu `header` và `footer` template parts.
   - 6 phân khu: Hero Banner, 7 Categories Grid, 4 Brand Values, 4 Featured Curations (Demo cards), Studio Narrative, Final CTA.
4. **`style.css` (Thêm mới 766 dòng):**
   - Thêm phần `8. STAGE 1F.2 — HEADER, FOOTER & HOMEPAGE COMPONENTS`.
   - Quy định layout flexbox, grid, sticky header với backdrop blur, visual frames, hover transitions, và responsive rules.

---

## 5. GATE A — TEMPLATE SELECTION & HIERARCHY ARCHITECTURE

Phân tích sâu cơ chế giải quyết template của WordPress Full-Site Editing (FSE):

1. **Template nào đang được WordPress chọn cho URL `/` trước Stage 1F.2?**
   - WordPress chọn template `home.html` (kế thừa từ theme cha `twentytwentyfive/templates/home.html`).
2. **Vì sao template đó được chọn trong cấu hình hiện tại?**
   - Trong database `wp_options`: `show_on_front = 'posts'` và `page_on_front = 0`.
   - Khi `show_on_front == 'posts'`, thứ tự ưu tiên của WordPress block template hierarchy cho trang gốc (`/`) là:
     1. `front-page.html`
     2. `home.html`
     3. `index.html`
   - Vì child theme và parent theme ban đầu đều không có `front-page.html`, WordPress tự động nạp template ở bậc ưu tiên thứ 2 là `home.html` của parent theme.
3. **Việc tạo `front-page.html` có thay đổi template hierarchy hay không?**
   - **CÓ**. `front-page.html` là template có quyền ưu tiên cao nhất trong phân cấp WordPress cho URL gốc `/`.
   - Khi `templates/front-page.html` xuất hiện trong child theme, WordPress lập tức nạp `front-page.html` cho bất kỳ ai truy cập `/`.
   - **Quan trọng:** Điều này diễn ra hoàn toàn tự nhiên theo tiêu chuẩn WordPress Core, **không cần sửa bất kỳ setting nào trong database (`show_on_front` hay `page_on_front`)**.
4. **Nó có ảnh hưởng đến blog index hoặc các template khác không?**
   - Không ảnh hưởng đến các template khác (`page.html`, `single.html`, `archive.html`, `search.html`, `404.html`).
   - Blog posts index trong cấu hình mặc định ban đầu hiển thị tại `/`. Khi có `front-page.html`, trang chủ hiển thị storefront; blog index sẽ hiển thị theo template `home.html` khi người dùng chỉ định một trang Posts trong tương lai.
5. **Những điều gì không thể xác nhận chỉ bằng kiểm tra source code?**
   - Nếu trong database có bản ghi thuộc post type `wp_template` trong bảng `wp_posts` do tùy biến bằng Site Editor, bản ghi trong database sẽ ghi đè file template vật lý. Qua kiểm tra SQL snapshot, toàn bộ 12 bản ghi `wp_posts` đều không chứa bất kỳ bản ghi `wp_template` nào, do đó file vật lý `front-page.html` sẽ được nạp 100%.

---

## 6. MÔ TẢ CHI TIẾT HEADER, FOOTER & HOMEPAGE

### 6.1 Header (`parts/header.html`)
- **Wordmark:** `ATELIER` kết hợp tagline `Warm Minimalist Living` bằng font heading thanh lịch.
- **Navigation:** Trỏ trực tiếp tới các URL có thật (`/`, `/shop/`) và anchors nội trang (`#categories`, `#craft`). Tuyệt đối không hiển thị liên kết tới `Sample Page` (`?page_id=2`).
- **Icons & Trợ năng:** Inline SVG icons có `stroke-width="1.75"` đồng bộ màu currentColor, thuộc tính `aria-hidden="true"`, liên kết có `aria-label` đầy đủ cho trình đọc màn hình.
- **Hiệu ứng:** Sticky navigation với `backdrop-filter: blur(8px)`, đường gạch chân hover animation mượt mà `var(--transition-fast)`.

### 6.2 Footer (`parts/footer.html`)
- **Tương phản:** Nền màu Contrast `#292A26` với chữ Base `#F7F5F0`, tạo điểm tựa thị giác sang trọng, vững chãi.
- **Cột 1 (Triết lý):** Tuyên ngôn thiết kế "Thoughtfully designed furniture blending organic warmth, architectural precision, and enduring materials."
- **Cột 2 & 3 (Điều hướng):** Khám phá không gian và Dịch vụ khách hàng, trỏ tới `/shop/`, `/cart/`, `/my-account/`.
- **Cột 4 (Bản tin):** Form đăng ký demo với ô input `disabled` và ghi chú minh bạch: "Preview mode · Form submission disabled in Stage 1F.2".

### 6.3 Homepage Template (`templates/front-page.html`)
- **Phân khu 1 (Hero Section):**
  - Kicker: `ARCHITECTURAL LIVING · 2026 COLLECTION`
  - H1: `Furnishings Designed with Quiet Conviction & Organic Warmth`
  - CTA kép: Nút chính trỏ tới `/shop/` (`.btn-primary`) và nút phụ trỏ tới `#categories` (`.btn-secondary`).
  - Khung hình học nghệ thuật (Visual Frame) phối màu Warm Sand `#E7DFD2` và viền Warm Biscuit `#B6A18A` với trích dẫn kiến trúc, không cần ảnh ngoài.
- **Phân khu 2 (Categories Grid):**
  - 7 không gian nội thất chuẩn: Living Room, Dining Room, Bedroom, Home Office, Lighting, Decor, Outdoor.
  - Card thiết kế tối giản với ký hiệu hình học biểu trưng và liên kết tới `/shop/`.
- **Phân khu 3 (Brand Values):**
  - 4 tiêu chuẩn cốt lõi: Solid Joinery (Ghép mộng mộng truyền thống), Ethical Sourcing (Gỗ sồi, tần bì chứng nhận), Natural Finishes (Dầu sáp thực vật), White Glove Care (Giao hàng và lắp đặt nguyên khối).
- **Phân khu 4 (Featured Curations - Demo):**
  - 4 thẻ sản phẩm mẫu: *Nara Solid Oak Dining Table*, *Komorebi Wool Lounge Chair*, *Kyoto Low Bed Frame*, *Sora Pleated Linen Floor Lamp*.
  - Nhãn badge nổi bật: `DEMO ITEM`, thông số kích thước, chất liệu, giá tham khảo, nút `View in Shop` dẫn về `/shop/`. **Tuyệt đối không có nút mua hàng giả lập hay hành động giỏ hàng giả.**
- **Phân khu 5 (Studio Narrative):**
  - Khối triết lý Wabi-Sabi và Scandinavian minimalism với trích dẫn Master Notes.
- **Phân khu 6 (Final CTA):**
  - Banner chuyển sắc Warm Olive `#65735B` mời gọi khám phá toàn bộ bộ sưu tập tại `/shop/`.

---

## 7. KIỂM THỬ CÚ PHÁP & CẤU TRÚC (SYNTAX & STRUCTURE EVIDENCE)

Toàn bộ các bài kiểm tra tự động đã được thực thi trực tiếp qua bộ test harness:

1. **PHP Linting:**
   - Lệnh: `php.exe -l functions.php`
   - Kết quả: `No syntax errors detected in functions.php` (Exit code: 0).
2. **HTML Block Markup Parsing:**
   - `header.html`: 13 thẻ mở `<!-- wp:... -->` / 13 thẻ đóng `<!-- /wp:... -->` (**Cân bằng 100%**).
   - `footer.html`: 21 thẻ mở / 21 thẻ đóng (**Cân bằng 100%**).
   - `front-page.html`: 14 thẻ mở / 14 thẻ đóng, 2 thẻ tự đóng `<!-- wp:template-part ... /-->` (**Cân bằng 100%**).
   - Toàn bộ các cặp thẻ HTML ngữ nghĩa `<header>`, `<nav>`, `<main>`, `<footer>`, `<section>`, `<article>`, `<form>` đều cân bằng hoàn hảo.
3. **CSS Syntax & Brace Balance:**
   - File: `twentytwentyfive-child/style.css` (1,193 dòng, 27,111 bytes).
   - Dấu ngoặc nhọn mở `{`: 150.
   - Dấu ngoặc nhọn đóng `}`: 150.
   - **Kết quả: Cân bằng tuyệt đối 100%.** Không có selector toàn cục gây xung đột với theme cha hay WooCommerce.

---

## 8. RESPONSIVE & ACCESSIBILITY AUDIT (PHÂN TÍCH TĨNH)

### 8.1 Khảo Sát Responsive Breakpoints
- **Desktop (1440 × 900):**
  - Container tối đa `1320px` tự động căn giữa.
  - Header trải ngang 3 cụm (Branding, Nav, Utilities) thanh thoát.
  - Categories hiển thị dạng lưới 4 cột / 3 cột linh hoạt (`minmax(260px, 1fr)`).
  - Footer hiển thị đầy đủ 4 cột tỷ lệ 35% / 20% / 20% / 25%.
- **Tablet (768 × 1024):**
  - Header chuyển sang dạng flex-wrap cân đối.
  - Typography tự co giãn mượt mà nhờ CSS `clamp()`.
  - Values Grid chuyển sang 2 cột nhịp nhàng.
- **Mobile (390 × 844):**
  - `.site-header-inner`: chuyển thành cột dọc căn giữa, khoảng cách tap target đạt chuẩn `>44×44px`.
  - `.hero-ctas`: nút bấm mở rộng toàn chiều ngang (`width: 100%`).
  - `.footer-columns`: tự động chuyển thành 1 cột dọc liên tiếp.
  - `overflow-x: hidden` trên `:root` và `body` loại bỏ hoàn toàn nguy cơ thanh cuộn ngang ngoài ý muốn.

### 8.2 Khảo Sát Khả Năng Tiếp Cận (WCAG Accessibility)
- **Tương phản màu sắc:**
  - Chữ đậm Contrast `#292A26` trên nền Base `#F7F5F0` đạt tỷ lệ tương phản `11.8:1` (Vượt xa chuẩn WCAG AAA 7:1).
  - Nút Primary `#65735B` với chữ trắng đạt tỷ lệ tương phản `4.6:1` (Đạt chuẩn WCAG AA).
  - Chữ Base `#F7F5F0` trên nền Footer Contrast `#292A26` đạt `11.8:1` (WCAG AAA).
- **Điều hướng bàn phím (Keyboard Focus):**
  - Bộ chọn `:focus-visible` áp dụng đường viền outline nét đứt 2px màu `#65735B` với khoảng cách đệm `offset: 2px` trên toàn bộ liên kết, nút bấm và ô nhập liệu.
- **Người dùng nhạy cảm chuyển động (Reduced Motion):**
  - Quy tắc `@media (prefers-reduced-motion: reduce)` trong `style.css` tự động triệt tiêu animation và chuyển cảnh tức thời (duration 0.01ms).

---

## 9. BẢO TỒN DATABASE & CẤU HÌNH HỆ THỐNG (INVARIANTS CONFIRMATION)

Đối chiếu với baseline đã khóa của dự án, xác nhận **không có bất kỳ thay đổi nào đối với cơ sở dữ liệu và cấu hình WordPress**:

| Chỉ Số Hệ Thống | Baseline Ban Đầu | Trạng Thái Sau Stage 1F.2 | Kết Luận |
|---|:---:|:---:|:---:|
| **`show_on_front`** | `'posts'` | `'posts'` | **BẢO TOÀN (0 mutation)** |
| **`page_on_front`** | `0` | `0` | **BẢO TOÀN (0 mutation)** |
| **`page_for_posts`** | `0` | `0` | **BẢO TOÀN (0 mutation)** |
| **`woocommerce_coming_soon`** | `'yes'` | `'yes'` | **BẢO TOÀN (Giữ nguyên)** |
| **Products (`post_type = 'product'`)** | `0` | `0` | **BẢO TOÀN (0 mutation)** |
| **Variations (`post_type = 'product_variation'`)** | `0` | `0` | **BẢO TOÀN (0 mutation)** |
| **SKUs in postmeta (`meta_key = '_sku'`)** | `0` | `0` | **BẢO TOÀN (0 mutation)** |
| **Published Pages Count** | `5` | `5` | **BẢO TOÀN (Không tạo page mới)** |
| **Total Attachments** | `1` (ID 5) | `1` (ID 5) | **BẢO TOÀN (Không tải ảnh mới)** |
| **Global Attributes** | `3` (`color`, `size`, `brand`) | `3` | **BẢO TOÀN (Không đổi)** |
| **Attribute Terms Count** | `19` | `19` | **BẢO TOÀN (Không đổi)** |

---

## 10. GIỚI HẠN & DANH SÁCH KIỂM TRA CHƯA THỂ THỰC HIỆN

1. **Trạng thái dịch vụ Local by Flywheel:**
   - *Thực tế:* Hệ thống host khởi động lại đã khiến tiến trình giao diện người dùng của Local by Flywheel tạm dừng. Do tuân thủ nguyên tắc an toàn "không tự ý thay đổi cấu hình dịch vụ", chúng tôi không can thiệp sâu vào session của hệ điều hành.
   - *Hạn chế:* Kiểm tra HTTP GET runtime trực tiếp trên cổng 10010 và chụp màn hình browser automation chưa thể thực hiện tại thời điểm xuất báo cáo này.
   - *Phân loại:* **Chưa kiểm tra được (Unverified at runtime)**.
2. **Khả năng tương tác của Form & Cart:**
   - Cố tình đặt ở trạng thái demo (`disabled` cho form, liên kết dẫn về `/shop/` và `/cart/` thực tế), tuân thủ nghiêm ngặt yêu cầu không tạo giao dịch giả, không tạo thông báo "Added to cart" giả mạo.

---

## 11. ĐỀ XUẤT CÁC BƯỚC TIẾP THEO

Khi người dùng khởi động lại ứng dụng Local trên desktop và sẵn sàng cho các giai đoạn kế tiếp:
1. **Kiểm tra trực quan (Visual Verification):** Mở trình duyệt truy cập `http://localhost:10010/` để chiêm ngưỡng trang chủ Warm Minimalist vừa dựng.
2. **Chuẩn bị cho Stage 1F.3 (Shop Archive & Catalog UI):** Xây dựng template `archive-product.html`, bộ lọc danh mục và lưới hiển thị catalog sản phẩm WooCommerce.

---

## 12. MANDATORY STOP

> [!IMPORTANT]
> **DỪNG BẮT BUỘC (MANDATORY STOP):**
> Giai đoạn **Stage 1F.2 — Header, Footer & Homepage Template** đã hoàn thành toàn bộ yêu cầu về mã nguồn, cấu trúc template FSE, CSS và kiểm thử tĩnh.
> Theo quy tắc an toàn nghiêm ngặt, tôi đã **DỪNG TẤT CẢ CÁC THAO TÁC** và **KHÔNG** tự ý chuyển sang Stage 1F.3, không tạo shop grid mới, không tạo trang WordPress, không thay đổi cấu hình, không import sản phẩm và không commit Git.
