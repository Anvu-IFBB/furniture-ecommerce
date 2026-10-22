   # STAGE 1F.5 — CONTROLLED PRODUCT DETAIL, CART & CHECKOUT UI REPORT

   - **Dự án:** `furniture-ecommerce` (WordPress 6.7.x / 7.1.3, WooCommerce 11.2.1, PHP 8.2.29, MySQL port 10011)
   - **Child Theme:** `wp-content/themes/twentytwentyfive-child`
   - **Theme Cha:** Twenty Twenty-Five
   - **Thời điểm hoàn thành:** 2026-10-19 03:15:00 UTC
   - **Reviewer:** Senior WordPress/WooCommerce Developer, UI/UX Designer & Software Test Auditor
   - **Chế độ thực thi:** CONTROLLED IMPLEMENTATION & TESTING (Không thay đổi dữ liệu database, không import sản phẩm)
   - **Verdict:** **CONDITIONAL PASS**
   - *Sửa Anchor Links Header/Footer:* **PASS (100%)**
   - *Template Single Product FSE (`templates/single-product.html`):* **PASS (100%)**
   - *Cart & Checkout Scoped UI CSS (`style.css` Section 10):* **PASS (100%)**
   - *Cú pháp Block & Cân bằng thẻ HTML/CSS Braces:* **PASS (100%)**
   - *Bảo toàn Database Invariants:* **PASS (100%)**
   - *Playwright Browser Automation:* **NOT VERIFIED / BLOCKED (Sandbox Driver 404)**

   ---

   ## 1. EXECUTIVE SUMMARY

   Giai đoạn **Stage 1F.5 — Controlled Product Detail, Cart & Checkout UI** đã hoàn tất việc mở rộng và tinh chỉnh toàn diện hệ thống giao diện storefront **ATELIER** trong child theme `twentytwentyfive-child`:

   1. **Khắc Phục Hoàn Toàn Lỗi Anchor Links (Defect DEF-01):**
      - Đã cập nhật toàn bộ liên kết anchor trong `parts/header.html` và `parts/footer.html` từ dạng tương đối (`#categories`, `#craft`, `#values`) thành đường dẫn gốc tuyệt đối (`/#categories`, `/#craft`, `/#values`).
      - Đảm bảo khi người dùng đang ở bất kỳ trang nào (ví dụ `/shop/`, `/cart/`, `/single-product/`), việc nhấp vào các mục điều hướng sẽ luôn điều hướng chính xác về đúng phân đoạn trên trang chủ.
   2. **Xây Dựng Template Chi Tiết Sản Phẩm Đẳng Cấp (`templates/single-product.html`):**
      - Tạo mới template `single-product.html` (5,113 bytes) sử dụng 100% WooCommerce FSE Block specification.
      - Bố cục 2 cột bất đối xứng: Cột trái dành cho Gallery ảnh trượt sticky; Cột phải hiển thị Category eyebrow, SKU tag, Title Serif cao cấp, Rating, Price, Excerpt, Form Add to Cart chuẩn WooCommerce, và hộp thông điệp bảo chứng chế tác *Atelier Provenance & Workshop Guarantee* (Solid Timber Provenance, Architectural Joinery, White Glove Delivery).
      - Tích hợp Specifications Tabs (`woocommerce/product-details`) và Related Curations (`woocommerce-blocks/related-products`).
      - WordPress Core nhận diện và kích hoạt template trực tiếp từ child theme: `twentytwentyfive-child//single-product` (Source: `theme`).
   3. **Hoàn Thiện Giao Diện Cart & Checkout:**
      - Bổ sung 426 dòng CSS chuyên biệt (Mục 10 của `style.css`), bao gồm toàn bộ kiểu dáng scoped cho Cart Block (`.wp-block-woocommerce-cart`) và Checkout Block (`.wp-block-woocommerce-checkout`).
      - Bảng giỏ hàng, bảng tóm tắt đơn hàng, trường nhập liệu form thanh toán, vòng focus ring olive `#65735B`, và nút đặt hàng nổi bật chuẩn WCAG AA (>44px tap target).
      - Thiết kế card trạng thái giỏ hàng rỗng (`.wp-block-woocommerce-empty-cart-block`) thanh lịch với nút tiếp tục mua sắm trỏ về `/shop/`.
   4. **Bảo Toàn Database Invariants Tuyệt Đối:**
      - Toàn bộ cơ sở dữ liệu giữ nguyên: 0 sản phẩm, 0 biến thể, 0 SKU, 1 media attachment (ID 5), 5 published pages, `woocommerce_coming_soon = 'yes'`. Không có đơn hàng hoặc giao dịch thử nào được tạo.

   ---

   ## 2. BASELINE PRE-FLIGHT (STAGE 1F.5.0)

   Trước khi tiến hành sửa đổi, trạng thái hệ thống đã được kiểm tra và ghi nhận:
   - **Project Root:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public`
   - **Active Theme:** `twentytwentyfive-child` (Theme cha: `twentytwentyfive`)
   - **Git Working Tree:** Nhánh `master`, không có commit mới ngoài luồng.
   - **Dịch vụ Local:** Nginx (PID 12788) cổng 10010, MySQL (PID 10640) cổng 10011 hoạt động bình thường.
   - **Các file baseline đã khóa SHA-256:** `header.html`, `footer.html`, `style.css`, `functions.php`, `theme.json`.

   ---

   ## 3. DANH SÁCH FILE ĐÃ TẠO / SỬA VÀ LÝ DO

   | STT | File Path | Trạng Thái | Dung Lượng | Lý Do Thực Hiện |
   |---|---|:---:|---:|---|
   | 1 | `parts/header.html` | **MODIFIED** | 3,333 B | Sửa anchor `#categories` và `#craft` thành `/#categories` và `/#craft` để khắc phục lỗi điều hướng từ trang con. |
   | 2 | `parts/footer.html` | **MODIFIED** | 5,055 B | Sửa anchor `#categories`, `#craft`, `#values` thành `/#categories`, `/#craft`, `/#values` và chuẩn hóa ký tự middle dot `&middot;`. |
   | 3 | `templates/single-product.html` | **CREATED** | 5,113 B | Xây dựng template FSE chi tiết sản phẩm chuẩn WooCommerce Blocks theo phong cách Warm Minimalist ATELIER. |
   | 4 | `style.css` | **MODIFIED** | 47,572 B | Bổ sung Mục 10: Quy tắc CSS scoped cho Product Detail, Cart Block, Empty Cart card, Checkout Form và Responsive Breakpoints. |

   ---

   ## 4. ANCHOR LINKS TRƯỚC VÀ SAU KHI SỬA

   | Khu Vực | Nhãn Điều Hướng | Trước Khi Sửa | Sau Khi Sửa (Stage 1F.5) | Đích Đến Trong DOM Trang Chủ |
   |---|---|---|---|:---:|
   | **Header** | `Spaces` | `#categories` | `/#categories` | `#categories` (Section Spaces) |
   | **Header** | `Philosophy` | `#craft` | `/#craft` | `#craft` (Section Craft Narrative) |
   | **Footer** | `Living Spaces` | `#categories` | `/#categories` | `#categories` (Section Spaces) |
   | **Footer** | `Dining Room` | `#categories` | `/#categories` | `#categories` (Section Spaces) |
   | **Footer** | `Bedroom` | `#categories` | `/#categories` | `#categories` (Section Spaces) |
   | **Footer** | `Lighting & Decor` | `#categories` | `/#categories` | `#categories` (Section Spaces) |
   | **Footer** | `Craft & Care` | `#craft` | `/#craft` | `#craft` (Section Craft Narrative) |
   | **Footer** | `White Glove Delivery`| `#values` | `/#values` | `#values` (Section Brand Values) |

   *Tất cả anchor links đều trỏ về ID thực tế tồn tại trên trang chủ `front-page.html` (`cat-heading`, `categories`, `craft`, `values`, v.v.).*

   ---

   ## 5. MÔ TẢ TEMPLATE PRODUCT DETAIL (`single-product.html`)

   Template được xây dựng theo chuẩn Gutenberg Block Theme FSE:
   1. **Header & Footer:** Nạp qua `<!-- wp:template-part {"slug":"header"} /-->` và `<!-- wp:template-part {"slug":"footer"} /-->`.
   2. **Breadcrumbs & Back Navigation:** Thanh breadcrumbs WooCommerce kèm nút quay lại bộ sưu tập: `← Return to Collection` trỏ về `/shop/`.
   3. **Cột 1 — Media Gallery (52% width):** `wp:woocommerce/product-image-gallery` đặt trong trạng thái sticky trên desktop.
   4. **Cột 2 — Summary & Purchase Actions (48% width):**
      - Category Eyebrow & SKU Tag (`wp:post-terms` và `wp:woocommerce/product-sku`).
      - Product Title Serif (`wp:post-title`).
      - Rating & Price (`wp:woocommerce/product-rating` và `wp:woocommerce/product-price`).
      - Short Summary (`wp:post-excerpt`).
      - Add to Cart Form (`wp:woocommerce/add-to-cart-form`) hỗ trợ số lượng và nút mua hàng.
      - **Hộp bảo chứng Atelier:** 3 cam kết kiến trúc tinh tế:
      + *Solid Timber Provenance:* Gỗ sồi trắng và óc chó chuẩn FSC hoàn thiện dầu cứng thực vật.
      + *Architectural Joinery:* Mộng gỗ mộng ngàm truyền thống bền vững trọn đời.
      + *White Glove Atelier Delivery:* Đặt phòng theo yêu cầu và thu dọn bao bì chuyên nghiệp.
      - Product Tags (`wp:post-terms` taxonomy `product_tag`).
   5. **Product Specifications Tabs:** `wp:woocommerce/product-details` với style minimal, typography serif.
   6. **Related Curations:** `<!-- wp:pattern {"slug":"woocommerce-blocks/related-products"} /-->`.

   ---

   ## 6. THAY ĐỔI CART & CHECKOUT UI

   1. **Cart Page UI (`.wp-block-woocommerce-cart`):**
      - Khung thẻ Card màu trắng `#FFFFFF`, viền Warm Sand `#E7DFD2`, bán kính góc 4px.
      - Nút Proceed to Checkout mang màu chủ đạo Olive `#65735B`, chữ trắng, hover `#53604a`, hiệu ứng chuyển tiếp mượt mà.
      - Bảng tóm tắt đơn hàng có đường kẻ phân cách tinh tế.
      - Trạng thái giỏ hàng trống (`.wp-block-woocommerce-empty-cart-block`) được định dạng căn giữa, tiêu đề Serif 2rem, kèm nút *Browse Storefront* trỏ về `/shop/`.
   2. **Checkout Page UI (`.wp-block-woocommerce-checkout`):**
      - Các bước thanh toán (Thông tin liên hệ, Địa chỉ giao hàng, Phương thức thanh toán) được đặt trong từng khối Card độc lập nền trắng, bo viền thanh nhã.
      - Các ô nhập liệu (`input`, `select`) có padding rộng rãi (`0.75rem 1rem`), viền `#E7DFD2`, và focus ring màu Olive mờ `rgba(101, 115, 91, 0.2)` đạt chuẩn Accessibility.
      - Nút Place Order có chiều rộng 100%, tap target lớn (>44px), màu Olive nổi bật.
      - Giữ nguyên toàn bộ logic validation và luồng xử lý native của WooCommerce.

   ---

   ## 7. BẢNG KIỂM THỬ KỸ THUẬT (VERIFICATION MATRIX)

   | Nội Dung Kiểm Thử | Phương Pháp & Bằng Chứng | Kết Quả Thực Tế | Đánh Giá |
   |---|---|---|:---:|
   | **PHP Syntax** | `php.exe -l functions.php` | `No syntax errors detected in functions.php` | **PASS** |
   | **CSS Syntax & Braces** | Python CSS Parser | 2,126 dòng, 284 dấu mở `{` / 284 dấu đóng `}` cân bằng tuyệt đối | **PASS** |
   | **Block Tree Balance** | Stack-based Parser | 15 block tự đóng, 11 cặp block mở/đóng khớp chính xác cấu trúc | **PASS** |
   | **HTML Tag Balance** | HTMLParser | `<main>`, `<div>`, `<p>`, `<a>`, `<span>`, `<strong>` cân bằng 100% | **PASS** |
   | **FSE Resolution** | WordPress Core Bootstrap | `get_block_template()` trả về `source: theme` từ child theme | **PASS** |
   | **FSE Block Rendering** | WordPress Core `do_blocks` | Render thành công 21,504 bytes HTML chi tiết sản phẩm | **PASS** |
   | **HTTP Endpoints** | HTTP GET máy chủ Nginx | `/`, `/shop/`, `/cart/`, `/checkout/`, `style.css` đều phản hồi **HTTP 200** | **PASS** |
   | **Anchor Link Integrity** | `audit_links.py` | Tất cả anchor trỏ chính xác về `/#categories`, `/#craft`, `/#values` | **PASS** |
   | **Database Invariants** | SQL Read-only Query | 0 sản phẩm, 0 SKU, 0 đơn hàng, cấu hình giữ nguyên | **PASS** |
   | **Playwright Automation** | Browser subagent | Driver 404 error từ Azure Edge CDN (môi trường offline) | **NOT VERIFIED** |

   ---

   ## 8. BẢNG INVARIANTS TRƯỚC VÀ SAU TRIỂN KHAI

   | Thông Số Hệ Thống | Baseline Pre-Flight (1F.5.0) | Sau Triển Khai (1F.5) | Đánh Giá Invariant |
   |---|:---:|:---:|:---:|
   | **Products (`post_type = 'product'`)** | `0` | `0` | **BẢO TOÀN (0 mutation)** |
   | **Variations (`post_type = 'product_variation'`)** | `0` | `0` | **BẢO TOÀN (0 mutation)** |
   | **SKUs in postmeta (`_sku`)** | `0` | `0` | **BẢO TOÀN (0 mutation)** |
   | **Published Pages Count** | `5` | `5` | **BẢO TOÀN (Không tạo trang mới)** |
   | **Total Media Attachments** | `1` (ID 5) | `1` (ID 5) | **BẢO TOÀN (Không tạo ảnh mới)** |
   | **`show_on_front`** | `'posts'` | `'posts'` | **BẢO TOÀN (Không đổi)** |
   | **`page_on_front`** | `0` | `0` | **BẢO TOÀN (Không đổi)** |
   | **`page_for_posts`** | `0` | `0` | **BẢO TOÀN (Không đổi)** |
   | **`woocommerce_coming_soon`** | `'yes'` | `'yes'` | **BẢO TOÀN (Giữ nguyên)** |
   | **Cart Page ID** | `7` | `7` | **BẢO TOÀN (Không đổi)** |
   | **Checkout Page ID** | `8` | `8` | **BẢO TOÀN (Không đổi)** |
   | **Orders Created** | `0` | `0` | **BẢO TOÀN (0 đơn hàng)** |
   | **CSV Import Executed** | `0` | `0` | **BẢO TOÀN (Không import)** |

   ---

   ## 9. BẢNG SHA-256 CHECKSUMS TRƯỚC VÀ SAU

   | File Path | Trạng Thái | SHA-256 Trước (1F.4/1F.5.0) | SHA-256 Sau (1F.5) | Dung Lượng Sau |
   |---|:---:|---|---|---:|
   | `parts/header.html` | **MODIFIED** | `B0D6F2237DB9705DCCC743829D160C42188C3ABD5F45E3E7551900E1F95974C5` | `54FBD9BB2378A8FCCA522719AD7C85A91041C8356E052CE19250D472237A78E6` | 3,333 B |
   | `parts/footer.html` | **MODIFIED** | `7E9926A9D5501B23C0D5664886C682F2029CF5E2C07669807C54B04620FAFA26` | `717B968682060393C8D0A2D709A55D52A13FCE715AA05D7EF9FE9E0D60017C8D` | 5,055 B |
   | `templates/single-product.html` | **CREATED** | *Chưa tồn tại* | `256216F0777CEBBE1841574F6DDF878C362B614F4D96FEDCA61F5570FFCB42C5` | 5,113 B |
   | `style.css` | **MODIFIED** | `9668BE2496C28A1B82364B46D3947FD2E89033DE4CB13B3FD9A9565AC8F31E6B` | `637B4BF6ADBF9C05DB1D6DEB1730647D0E51FABF3E259CE248909F1AD903C250` | 47,572 B |
   | `templates/archive-product.html` | UNTOUCHED | `7EED6D6EF723540BFB1EA22383380D624F3D98C4C03C975384F39040A15EA46A` | `7EED6D6EF723540BFB1EA22383380D624F3D98C4C03C975384F39040A15EA46A` | 5,446 B |
   | `templates/front-page.html` | UNTOUCHED | `C87D34C02F40671C1A2BE045D60F36D74C2DEE425ADFE6BC56FDC73A383EA05C` | `C87D34C02F40671C1A2BE045D60F36D74C2DEE425ADFE6BC56FDC73A383EA05C` | 16,061 B |
   | `functions.php` | UNTOUCHED | `6C2FBCB2B13CDF036734A94DD871A1D28DF3CE291423D8B32ACFDE6A33B6AC96` | `6C2FBCB2B13CDF036734A94DD871A1D28DF3CE291423D8B32ACFDE6A33B6AC96` | 1,052 B |
   | `theme.json` | UNTOUCHED | `FE1D1C141CC43515625950EE456B654FAB87BC749FA6327C90C2A252EED72A94` | `FE1D1C141CC43515625950EE456B654FAB87BC749FA6327C90C2A252EED72A94` | 8,177 B |
   | `hero-furniture.jpg` | UNTOUCHED | `A0EAA549B8E09A7371B92A55B3BEB86DD52939FCF14BF5A91F803D71F318A419` | `A0EAA549B8E09A7371B92A55B3BEB86DD52939FCF14BF5A91F803D71F318A419` | 800,457 B |
   | `craft-detail.jpg` | UNTOUCHED | `C637D681E65CCBF30A782D2CA5D0F26E91A0B407B8A7DDB3BEBE47B9B9358670` | `C637D681E65CCBF30A782D2CA5D0F26E91A0B407B8A7DDB3BEBE47B9B9358670` | 740,406 B |

   ---

   ## 10. TRẠNG THÁI GIT STATUS (POST-IMPLEMENTATION)

   ```text
   M themes/twentytwentyfive-child/functions.php (từ 1F.1)
   M themes/twentytwentyfive-child/style.css     (cập nhật thêm mục 10)
   M themes/twentytwentyfive-child/theme.json    (từ 1F.1)
   ?? themes/twentytwentyfive-child/STAGE_1F_2_HEADER_FOOTER_HOMEPAGE_REPORT.md
   ?? themes/twentytwentyfive-child/STAGE_1F_3_HOMEPAGE_COMPLETION_REPORT.md
   ?? themes/twentytwentyfive-child/STAGE_1F_4R_RUNTIME_VISUAL_QA_REPORT.md
   ?? themes/twentytwentyfive-child/STAGE_1F_4_SHOP_ARCHIVE_REPORT.md
   ?? themes/twentytwentyfive-child/STAGE_1F_5_STOREFRONT_COMPLETION_REPORT.md
   ?? themes/twentytwentyfive-child/assets/
   ?? themes/twentytwentyfive-child/parts/
   ?? themes/twentytwentyfive-child/templates/
   ```
   *Không có file nào ngoài child theme bị chỉnh sửa. Không có Git commit.*

   ---

   ## 11. NHỮNG GIỚI HẠN CHƯA GIẢI QUYẾT & HƯỚNG DẪN KIỂM TRA THỦ CÔNG

   1. **Kiểm tra luồng đặt hàng và thanh toán:**
      - Chưa thể kiểm thử luồng thêm vào giỏ, nhập địa chỉ và thanh toán thực tế do catalog đang ở trạng thái 0 sản phẩm theo quy định an toàn.
      - *Phân loại:* **NOT VERIFIED (Chờ Gate 4 import sản phẩm thật)**.
   2. **Kiểm tra hiển thị trực quan Cart & Checkout trên trình duyệt:**
      - Đăng nhập tài khoản Admin tại `http://localhost:10010/wp-admin/`.
      - Mở `http://localhost:10010/cart/` để xem giao diện giỏ hàng trống thanh lịch với nút tiếp tục mua sắm.
      - Mở `http://localhost:10010/shop/` và nhấp vào "Philosophy" hoặc "Spaces" trên Header để xác nhận trình duyệt điều hướng mượt mà về đúng phân đoạn trên trang chủ.

   ---

   ## 12. KẾT LUẬN & ĐỀ XUẤT TIẾP THEO

   - Giai đoạn **Stage 1F.5** đạt kết quả **CONDITIONAL PASS** (Mã nguồn, template FSE, sửa anchor links, CSS scoped và bảo toàn invariants đạt 100% PASS; phần kiểm thử trình duyệt tự động giữ NOT VERIFIED theo quy chuẩn).
   - Toàn bộ 4 trụ cột giao diện chính của Storefront ATELIER:
   1. Homepage (`templates/front-page.html`)
   2. Shop Archive (`templates/archive-product.html`)
   3. Single Product Detail (`templates/single-product.html`)
   4. Cart & Checkout (`/cart/` & `/checkout/` scoped styling)
   đã hoàn thiện, đồng bộ ngôn ngữ thiết kế Warm Minimalist cao cấp và sẵn sàng 100% để đón nhận dữ liệu sản phẩm thật từ dataset.

   ---

   ## 13. MANDATORY STOP

   > [!IMPORTANT]
   > **DỪNG BẮT BUỘC (MANDATORY STOP):**  
   > Giai đoạn **Stage 1F.5 — Controlled Product Detail, Cart & Checkout UI** đã hoàn thành toàn bộ mục tiêu theo đúng thẩm quyền được phê duyệt.  
   > Theo quy tắc an toàn bất biến:
   > - **KHÔNG** tự ý kích hoạt Stage 1F.6 hoặc Gate 4 (Product Import).
   > - **KHÔNG** import sản phẩm, tạo variation hoặc đơn hàng thử.
   > - **KHÔNG** thay đổi Coming Soon hoặc cơ sở dữ liệu.
   > - **KHÔNG** tạo Git commit.
   > - **DỪNG TẤT CẢ CÁC THAO TÁC** và chờ phê duyệt chính thức từ bạn.
