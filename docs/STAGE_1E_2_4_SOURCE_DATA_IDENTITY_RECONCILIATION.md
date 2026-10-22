# STAGE 1E.2.4 — SOURCE DATA IDENTITY RECONCILIATION
## FORENSIC AUDIT & IDENTITY RECONCILIATION REPORT

> **MODE: STRICT READ-ONLY — NO DATABASE WRITES**
> 
> **Mục tiêu điều tra:** Làm sáng tỏ triệt để mâu thuẫn giữa báo cáo Stage 1E.2.1 và Stage 1E.2.3 liên quan đến:
> 1. Kích thước byte của tệp CSV nguồn (`26,176 bytes` vs `16,367 bytes`).
> 2. Cùng mã SHA-256: `276809899064949CB8D45B8EF7523B8C2913780F0649650EA4EB494FB74894F6`.
> 3. Sự sai lệch giữa hai cặp URL ảnh 404 được nêu trong hai báo cáo.
> 
> **Cam kết an toàn tuyệt đối:**
> - KHÔNG sửa, ghi đè, đổi tên hoặc di chuyển tệp CSV nguồn.
> - KHÔNG ghi dữ liệu vào database.
> - KHÔNG tạo sản phẩm, biến thể, thuộc tính, term hoặc attachment.
> - KHÔNG chạy importer.
> - KHÔNG thực hiện thao tác Git mang tính hủy hoại (reset, clean, checkout).

---

## 1. Đường dẫn và Danh tính Tệp Nguồn Thực tế (Ground Truth)

Kiểm tra trực tiếp tệp CSV nguồn duy nhất của dự án trên hệ thống tệp:

| Thông số | Giá trị xác minh trực tiếp | Phương pháp xác minh |
| :--- | :--- | :--- |
| **Đường dẫn tuyệt đối** | `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\wp-content\plugins\woocommerce_furniture_dataset_22_products.csv` | Filesystem probe |
| **Kích thước byte thực tế** | **`26,176 bytes`** | `os.stat().st_size` / PowerShell `(Get-Item).Length` |
| **Mã băm SHA-256** | **`276809899064949CB8D45B8EF7523B8C2913780F0649650EA4EB494FB74894F6`** | `hashlib.sha256()` / `Get-FileHash` |
| **Thời điểm sửa đổi cuối** | `2026-10-15 17:47:25` (10/15/2026 5:47:25 PM) | NTFS Metadata (`LastWriteTime`) |
| **Thời điểm tạo tệp** | `2026-10-15 18:59:24` (10/15/2026 6:59:24 PM) | NTFS Metadata (`CreationTime`) |
| **Định dạng ký tự (Encoding)**| `UTF-8` (Không BOM) | Python binary header inspection |
| **Dấu phân cách (Delimiter)** | Dấu phẩy (`,`) | CSV Sniffer |
| **Số dòng tệp** | **`65 dòng`** (1 dòng header + 64 dòng dữ liệu) | Line count |
| **Số cột dữ liệu** | **`28 cột`** | Header array count |
| **Sản phẩm cha (Parents)** | **`22`** (12 Simple, 10 Variable) | Phân tích cột `Type` |
| **Biến thể (Variations)** | **`42`** | Phân tích cột `Type = variation` |
| **Mã SKU duy nhất** | **`64`** (0 mã rỗng, 0 mã trùng, 100% độc nhất) | Phân tích cột `SKU` |
| **Danh mục (Categories)** | **`7`** (`Beds`, `Chairs`, `Decoration`, `Lighting`, `Sofas`, `Storage`, `Tables`) | Phân tích cột `Categories` |
| **Thương hiệu (Brands)** | **`5`** (`LuxeVibe`, `Minimalia`, `NordicLiving`, `OakCraft`, `UrbanoHaus`) | Cột `Attribute 3 value(s)` |
| **Màu sắc (Colors)** | **`3`** (`Black`, `Brown`, `White`) | Cột `Attribute 1 value(s)` |
| **Kích thước (Sizes)** | **`3`** (`L`, `M`, `S`) | Cột `Attribute 2 value(s)` |
| **Tổng tồn kho khai báo** | **`449 units`** | Tổng cột `Stock` |

### Quét các bản sao CSV khác trong toàn bộ dự án
Thực hiện quét đệ quy toàn bộ thư mục `furniture-ecommerce`:
- **Kết quả:** Không có bất kỳ bản sao nào khác của tệp `woocommerce_furniture_dataset_22_products.csv` trong toàn bộ workspace. Các tệp `.csv` khác được tìm thấy hoàn toàn thuộc về thư mục mẫu của WooCommerce core (`woocommerce/sample-data/...`).

---

## 2. Kiểm tra Kết nối Trực tiếp Toàn bộ 25 URL Ảnh trong CSV

Đã thực hiện gửi HTTP request trực tiếp tới toàn bộ 25 URL ảnh Unsplash trích xuất từ 64 dòng sản phẩm:

| STT | Mã ảnh Unsplash | Trạng thái HTTP | Xuất hiện tại dòng & SKU trong CSV | Tên sản phẩm trong CSV |
| :---: | :--- | :---: | :--- | :--- |
| 1 | `photo-1493663284031-b7e3aefcae8e` | **200 OK** | Dòng 26 (`FURN-015`), Dòng 30 (`FURN-015-BRN-L`) | Cloud Modular L-Shape Sofa |
| 2 | `photo-1503602642458-232111445657` | **200 OK** | Dòng 2 (`FURN-001`) | Ergonomic Mesh Office Chair |
| 3 | `photo-1505693416388-ac5ce068fe85` | **200 OK** | Dòng 31 (`FURN-016`), Dòng 32, 33, 61 (`FURN-022`), Dòng 64, 65 | Scandinavian Oak Dining Table / Floating Platform Bed |
| 4 | `photo-1507473885765-e6ed057f782c` | **200 OK** | Dòng 9 (`FURN-008`), Dòng 36 (`FURN-017`), Dòng 40 | Warm Brass Table Lamp / Arc Floor Lamp with Marble Base |
| 5 | `photo-1513506003901-1e6a229e2d15` | **200 OK** | Dòng 8 (`FURN-007`), Dòng 36 (`FURN-017`), Dòng 37, 38, 39 | Minimalist Floating Wall Shelf / Arc Floor Lamp |
| 6 | `photo-1522771739844-6a9f6d5f14af` | **200 OK** | Dòng 31 (`FURN-016`), Dòng 34, 35, 61 (`FURN-022`), Dòng 62, 63 | Scandinavian Oak Dining Table / Floating Platform Bed |
| 7 | `photo-1532372320572-cda25653a26d` | **200 OK** | Dòng 5 (`FURN-004`) | Industrial Reclaimed Wood Coffee Table |
| 8 | `photo-1533090481720-856c6e3c1fdc` | **200 OK** | Dòng 4 (`FURN-003`) | Mid-Century Modern Armchair |
| 9 | `photo-1540518614846-7ede433c4577` | **404 NOT FOUND** | **Dòng 7 (`FURN-006`)** | **Solid Pine Nightstand** |
| 10 | `photo-1540574163026-643ea20ade25` | **200 OK** | Dòng 26 (`FURN-015`), Dòng 29 (`FURN-015-BLK-M`) | Cloud Modular L-Shape Sofa |
| 11 | `photo-1541961017774-22349e4a1262` | **200 OK** | Dòng 41 (`FURN-018`), Dòng 44 (`FURN-018-BLK-S`) | Abstract Acoustic Wall Art Panel |
| 12 | `photo-1555041469-a586c61ea9bc` | **200 OK** | Dòng 6 (`FURN-005`), Dòng 26 (`FURN-015`), Dòng 27, 28 | Velvet Pouf Ottoman / Cloud Modular Sofa |
| 13 | `photo-1567538096630-e0c55bd6374c` | **200 OK** | Dòng 14 (`FURN-013`), Dòng 18, 19, 56 (`FURN-021`), Dòng 58 | Ergonomic Executive Desk Chair / Upholstered Platform Bed |
| 14 | `photo-1577140917170-285929fb55b7` | **200 OK** | Dòng 21 (`FURN-014`), Dòng 24, 25 | Velvet Accent Dining Chair |
| 15 | `photo-1578500494198-246f612d3b3d` | **200 OK** | Dòng 10 (`FURN-009`) | Ceramic Sculptural Table Lamp |
| 16 | `photo-1579783902614-a3fb3927b675` | **404 NOT FOUND** | **Dòng 41 (`FURN-018`), Dòng 42, 43, 45** | **Abstract Acoustic Wall Art Panel** |
| 17 | `photo-1580480055273-228ff5388ef8` | **200 OK** | Dòng 12 (`FURN-011`) | Rattan Weave Storage Basket |
| 18 | `photo-1586023492125-27b2c045efd7` | **200 OK** | Dòng 3 (`FURN-002`), Dòng 51 (`FURN-020`), Dòng 55 | Nordic Solid Ash Dining Chair / 3-Seater Linen Sofa |
| 19 | `photo-1592078615290-033ee584e267` | **200 OK** | Dòng 14 (`FURN-013`), Dòng 15, 16, 17, 56 (`FURN-021`), Dòng 57, 60 | Ergonomic Desk Chair / Upholstered Bed |
| 20 | `photo-1594620302200-9a762244a156` | **200 OK** | Dòng 11 (`FURN-010`), Dòng 51 (`FURN-020`), Dòng 52, 53, 54 | Minimalist Bookshelf Divider / 3-Seater Linen Sofa |
| 21 | `photo-1595428774223-ef52624120d2` | **200 OK** | Dòng 46 (`FURN-019`), Dòng 47, 48 | Mid-Century Walnut Coffee Table |
| 22 | `photo-1595515106969-1ce29566ff1c` | **200 OK** | Dòng 46 (`FURN-019`), Dòng 49, 50 | Mid-Century Walnut Coffee Table |
| 23 | `photo-1598300042247-d088f8ab3a91` | **200 OK** | Dòng 14 (`FURN-013`), Dòng 20, 56 (`FURN-021`), Dòng 59 | Ergonomic Desk Chair / Upholstered Bed |
| 24 | `photo-1615066390971-03e4e1c36ddf` | **200 OK** | Dòng 21 (`FURN-014`), Dòng 22, 23 | Velvet Accent Dining Chair |
| 25 | `photo-1618220179428-22790b461013` | **200 OK** | Dòng 13 (`FURN-012`) | Ceramic Fluted Decorative Vase |

### Kết luận kết nối ảnh
Trong toàn bộ tệp CSV, **chỉ có duy nhất 2 URL trả về HTTP 404**:
1. `https://images.unsplash.com/photo-1540518614846-7ede433c4577?auto=format&fit=crop&w=900&q=80` (Dòng 7, SKU `FURN-006`, *Solid Pine Nightstand*).
2. `https://images.unsplash.com/photo-1579783902614-a3fb3927b675?auto=format&fit=crop&w=900&q=80` (Dòng 41, SKU `FURN-018`, *Abstract Acoustic Wall Art Panel* và 3 biến thể con của nó).

---

## 3. Bảng Đối Chiếu Mâu Thuẫn (Reconciliation Matrix)

| Tiêu chí | Báo cáo Stage 1E.2.1 | Báo cáo Stage 1E.2.3 | Thực tế tệp nguồn hiện tại (Ground Truth) | Đánh giá đối chiếu |
| :--- | :--- | :--- | :--- | :--- |
| **Kích thước tệp** | **26,176 bytes** | 16,367 bytes *(SAI)* | **26,176 bytes** | **Stage 1E.2.1 ĐÚNG**; Stage 1E.2.3 ghi chép sai số byte. |
| **Mã SHA-256** | `2768098990649...` | `2768098990649...` | **`276809899064949CB8D45B8EF7523B8C2913780F0649650EA4EB494FB74894F6`** | **TRÙNG KHỚP 100%**. Tệp CSV chưa từng thay đổi một byte nào. |
| **URL 404 thứ nhất** | `photo-1540518614846-7ede433c4577` | `photo-1507473885765-e6ed057f782c` *(SAI)* | **`photo-1540518614846-7ede433c4577`** | **Stage 1E.2.1 ĐÚNG**. URL trong 1E.2.3 thực tế trả về HTTP 200. |
| **Sản phẩm của URL 404 #1** | `FURN-006` (*Solid Pine Nightstand*) | `FURN-006` (*Minimalist Geometric Floor Lamp*) *(SAI)* | `FURN-006` (*Solid Pine Nightstand*, Dòng 7) | **Stage 1E.2.1 ĐÚNG**. 1E.2.3 gán sai tên sản phẩm. |
| **URL 404 thứ hai** | `photo-1579783902614-a3fb3927b675` | `photo-1555041469-a586c61ea9bc` *(SAI)* | **`photo-1579783902614-a3fb3927b675`** | **Stage 1E.2.1 ĐÚNG**. URL trong 1E.2.3 thực tế trả về HTTP 200. |
| **Sản phẩm của URL 404 #2** | `FURN-018` (*Abstract Acoustic Wall Art Panel*) | `FURN-018` (*Modular Fabric Sectional Sofa*) *(SAI)* | `FURN-018` (*Abstract Acoustic Wall Art Panel*, Dòng 41) | **Stage 1E.2.1 ĐÚNG**. 1E.2.3 gán sai tên sản phẩm. |
| **Số sản phẩm / SKU** | 22 cha / 42 con / 64 SKU | 22 cha / 42 con / 64 SKU | 22 cha / 42 con / 64 SKU | Trùng khớp 100%. |

---

## 4. Phân Tích Nguyên Nhân Gốc Rễ (Root Cause Analysis)

### Bằng chứng kỹ thuật bất biến
1. **Tính bất biến của SHA-256:**
   Mã băm SHA-256 là hàm băm mật mã một chiều nhạy cảm đến từng bit. Cả Stage 1E.2.1, 1E.2.2 và 1E.2.3 đều ghi nhận chính xác cùng mã băm:
   `276809899064949CB8D45B8EF7523B8C2913780F0649650EA4EB494FB74894F6`.
   Nếu tệp CSV bị sửa đổi, thay thế hoặc rút ngắn kích thước từ 26,176 xuống 16,367 bytes, mã SHA-256 bắt buộc phải thay đổi hoàn toàn. Việc SHA-256 không đổi là bằng chứng toán học chứng minh tệp CSV nguồn không bị chỉnh sửa.
2. **Metadata hệ thống tệp:**
   `LastWriteTime` của tệp CSV là `10/15/2026 5:47:25 PM`. Tệp không hề có lượt ghi (write) nào trong ngày 16/10/2026.
3. **Nguồn gốc con số `16,367 bytes`:**
   Quét toàn bộ hệ thống tệp xác nhận **không tồn tại bất kỳ tệp nào có kích thước 16,367 bytes** trong dự án. Đây là lỗi sao chép/ghi nhầm thủ công (clerical transcription hallucination) của mô hình khi tạo văn bản báo cáo Stage 1E.2.3 thay vì truy vấn bằng lệnh `Get-Item`.
4. **Nguồn gốc cặp URL sai lệch trong Stage 1E.2.3:**
   - Trong quá trình soạn thảo Stage 1E.2.3, mô hình đã trích xuất nhầm 2 URL Unsplash khác có trong tệp CSV (`photo-1507473885765-e6ed057f782c` và `photo-1555041469-a586c61ea9bc`) và tự động diễn giải sai tên sản phẩm.
   - Thử nghiệm kết nối mạng thực tế chứng minh 2 URL này hoàn toàn hoạt động bình thường (**HTTP 200 OK**).
   - Ngược lại, báo cáo Stage 1E.2.1 dựa trên kết quả kiểm thử thực tế từ tệp `true_image_connectivity_results.json` ghi nhận chuẩn xác hai URL 404 thực sự là:
     * `photo-1540518614846-7ede433c4577` (`FURN-006`, *Solid Pine Nightstand*).
     * `photo-1579783902614-a3fb3927b675` (`FURN-018`, *Abstract Acoustic Wall Art Panel*).

---

## 5. Đánh Giá Tác Động Hệ Thống (Impact Assessment)

- **Database:** Hoàn toàn nguyên vẹn. 0 sản phẩm, 0 biến thể, 0 thuộc tính, 0 terms, 0 thao tác ghi database.
- **Git Working Tree:** 100% sạch trên tất cả các tệp mã nguồn tracked.
- **Tệp CSV nguồn:** 100% nguyên vẹn, giữ nguyên bản từ Stage 1A/1E.1 đến nay.
- **Mức độ rủi ro:** Không có rủi ro về tính toàn vẹn dữ liệu. Báo cáo Stage 1E.2.3 chỉ gặp lỗi trình bày văn bản (reporting artifact error), không ảnh hưởng đến dữ liệu thực tế của hệ thống.

---

## 6. Quyết Định Stage-Gate

### **STAGE-GATE DECISION: PASS**

### Căn cứ phê duyệt:
1. **Đã xác minh danh tính tệp nguồn:** Đường dẫn tuyệt đối, kích thước byte (`26,176 bytes`), mã băm SHA-256 (`276809899...`) và số lượng cấu trúc dữ liệu đã được chứng minh và đối chiếu nhất quán.
2. **Mâu thuẫn được làm sáng tỏ bằng bằng chứng thực tế:**
   - Kích thước chuẩn xác của CSV là **`26,176 bytes`**. Con số `16,367 bytes` trong báo cáo 1E.2.3 là lỗi trình bày văn bản.
   - Cặp URL ảnh 404 chuẩn xác là **`photo-1540518614846-7ede433c4577` (`FURN-006`)** và **`photo-1579783902614-a3fb3927b675` (`FURN-018`)** như Stage 1E.2.1 đã chỉ ra.
3. **Khẳng định an toàn:** Không có sửa đổi tệp nguồn, không có ghi database, không có rủi ro tiềm ẩn.

---

## 7. STOP

Theo quy tắc bắt buộc của Stage 1E.2.4:
- Dừng toàn bộ thao tác ngay sau khi lập báo cáo.
- KHÔNG tạo thuộc tính, terms, sản phẩm, biến thể hoặc media.
- KHÔNG chạy importer.
- Chờ người dùng xem xét kết quả đối chiếu và chỉ thị bước tiếp theo.
