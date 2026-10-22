# STAGE 1E.3E — POST-RESTART VERIFICATION REPORT (READ-ONLY)

> **STAGE IDENTIFIER:** STAGE 1E.3E — Post-Restart Read-Only Verification  
> **EXECUTION TIMESTAMP:** 2026-10-16 21:46:00+07:00  
> **EXECUTION ENVIRONMENT:** Local by Flywheel on Windows 11 (MySQL 8.4.0 on `127.0.0.1:10011`, Nginx 1.26.1 on `127.0.0.1:10010`, PHP 8.2.29, WordPress 7.1.3, WooCommerce 11.2.1)  
> **STAGE AUTHORITY:** Strictly Read-Only Verification. **ZERO DATABASE WRITES. NO IMPORT EXECUTED.**  
> **OVERALL RESULT:** **`PASS — HTTP SERVER OPERATIONAL & READY FOR GATE 2`**

---

## 1. Executive Summary

Following the user's manual site startup in the Local by Flywheel interface, a strict, evidence-backed read-only audit was conducted to verify that the web server stack and database are fully operational.

### Key Verification Highlights:
1. **Local & Service Processes (VERIFIED):**
   - `Local.exe` is actively running (PIDs: 3484, 5092, 15424, 19048, 19296).
   - `nginx.exe` is actively running (PIDs: 15780, 17460).
   - `php-cgi.exe` is actively running (PIDs: 3420, 4700).
   - `mysqld.exe` is actively running (PIDs: 12672, 19364).
   - `mailpit.exe` is actively running (PID: 18280).
2. **TCP Listen Sockets (VERIFIED):**
   - Port `10010` (HTTP Web Server): **LISTEN** on `127.0.0.1` and `::1` (Owned by `nginx.exe` PID 17460).
   - Port `10011` (MySQL Database): **LISTEN** on `127.0.0.1` and `::1` (Owned by `mysqld.exe` PID 19364).
   - Port `4000` (Local GraphQL IPC): **LISTEN** on `127.0.0.1` (Owned by `Local.exe` PID 19296).
3. **HTTP Endpoint Health (VERIFIED):**
   - All 6 critical store endpoints returned **HTTP 200 OK** without errors or connection timeouts.
4. **Database State & Baseline Reconciliation (VERIFIED):**
   - Exactly **0 products**, **0 variations**, and **0 SKUs** exist in the database (Zero conflicts with CSV).
   - Exactly **1 media attachment** (pre-existing placeholder), **3 global attributes** (`color`, `size`, `brand`), and pre-created terms (3 colors, 3 sizes, 5 brands, 8 categories) remain intact.
   - **Zero database writes occurred during this verification.**
5. **CSV Cryptographic Integrity (VERIFIED):**
   - Working CSV matches the locked baseline byte-for-byte (25,712 bytes, SHA-256 `D2C8566F...`).
   - Source CSV matches the locked baseline byte-for-byte (26,176 bytes, SHA-256 `27680989...`).

---

## 2. Local Process & TCP Port Evidence

Evidence collected via PowerShell `Get-Process` and `Get-NetTCPConnection`:

### 2.1. Active Site Processes

| Process Name | Process ID (PID) | Start Time | Executable Path | Verification Status |
| :--- | :---: | :---: | :--- | :---: |
| `Local.exe` | `19296`, `19048`, `3484` | 20:56:14 | `C:\Sv\Local\Local.exe` | **VERIFIED** |
| `nginx.exe` | `15780`, `17460` | 21:31:32 | `...\lightning-services\nginx-1.26.1+3\bin\win32\nginx.exe` | **VERIFIED** |
| `php-cgi.exe` | `3420`, `4700` | 21:31:32 | `...\lightning-services\php-8.2.29+0\bin\win64\php-cgi.exe` | **VERIFIED** |
| `mysqld.exe` | `12672`, `19364` | 21:31:32 | `...\lightning-services\mysql-8.4.0\bin\win64\bin\mysqld.exe` | **VERIFIED** |
| `mailpit.exe` | `18280` | 21:31:32 | `...\lightning-services\mailpit-1.24.1+0\bin\win32\mailpit.exe` | **VERIFIED** |

### 2.2. TCP Listen Socket Verification

| Local Address | Port | State | Owning Process ID | Associated Service | Status |
| :---: | :---: | :---: | :---: | :--- | :---: |
| `127.0.0.1` | **10010** | `Listen` | `17460` | Nginx HTTP Web Router | **PASS** |
| `::1` | **10010** | `Listen` | `17460` | Nginx HTTP Web Router | **PASS** |
| `127.0.0.1` | **10011** | `Listen` | `19364` | MySQL Database Server | **PASS** |
| `::1` | **10011** | `Listen` | `19364` | MySQL Database Server | **PASS** |
| `127.0.0.1` | **4000** | `Listen` | `19296` | Local Electron GraphQL | **PASS** |

---

## 3. HTTP Response Audit for Store Endpoints

Each URL was probed via `Invoke-WebRequest` with a 15-second timeout:

| Target URL | HTTP Code | Status Description | Content Length | HTML Document Title | Evaluation |
| :--- | :---: | :---: | :---: | :--- | :---: |
| `http://localhost:10010/` | **200** | `OK` | 61,901 bytes | `furniture-ecommerce` | **PASS** |
| `http://localhost:10010/shop/` | **200** | `OK` | 62,125 bytes | `Shop – furniture-ecommerce` | **PASS** |
| `http://localhost:10010/cart/` | **200** | `OK` | 122,371 bytes | `Cart – furniture-ecommerce` | **PASS** |
| `http://localhost:10010/checkout/` | **200** | `OK` | 122,371 bytes | `Cart – furniture-ecommerce` *(Empty cart redirect)* | **PASS** |
| `http://localhost:10010/my-account/` | **200** | `OK` | 63,130 bytes | `My account – furniture-ecommerce` | **PASS** |
| `http://localhost:10010/wp-login.php`| **200** | `OK` | 6,797 bytes | `Log In ‹ furniture-ecommerce — WordPress` | **PASS** |

*Finding:* All core WordPress and WooCommerce public/administrative entry points respond immediately with HTTP 200 OK. Zero network drops or timeouts detected.

---

## 4. Runtime & Database Read-Only Audit

### 4.1. Runtime Environment Ground Truth
- **WordPress Core:** Version **`7.1.3`** (DB version `61833`).
- **WooCommerce Core:** Version **`11.2.1`**.
- **PHP CLI & FastCGI:** Version **`8.2.29`**.
- **Active Theme:** `twentytwentyfive-child` (Parent: `twentytwentyfive`).

### 4.2. Database Counts vs. Pre-Import Baseline

Queries executed via `mysqli` against `127.0.0.1:10011`:

| Database Entity | Pre-Import Baseline | Live Database Value | Discrepancy | Status |
| :--- | :---: | :---: | :---: | :---: |
| **Posts of type `product`** | 0 | **0** | None | **PASS** |
| **Posts of type `product_variation`** | 0 | **0** | None | **PASS** |
| **Product SKUs (`_sku` meta)** | 0 | **0** | None | **PASS** |
| **Conflicting SKUs with CSV** | 0 | **0** | None | **PASS** |
| **Media Attachments** | 1 | **1** | None (`ID: 5`, placeholder) | **PASS** |
| **Global Attributes** | 3 | **3** | None (`color`, `size`, `brand`) | **PASS** |
| **Terms in `pa_color`** | 3 | **3** | None (`Black`, `Brown`, `White`) | **PASS** |
| **Terms in `pa_size`** | 3 | **3** | None (`S`, `M`, `L`) | **PASS** |
| **Terms in `pa_brand`** | 5 | **5** | None (`LuxeVibe`, `Minimalia`, ...)| **PASS** |
| **Terms in `product_cat`** | 8 | **8** | None (7 dataset + 1 Uncategorized)| **PASS** |

---

## 5. Working & Source CSV Cryptographic Verification

Computed via PowerShell `Get-FileHash -Algorithm SHA256`:

| Dataset File | File Size | SHA-256 Checksum | Locked Reference Baseline | Status |
| :--- | :---: | :--- | :--- | :---: |
| **Prepared Working CSV** | **`25,712`** bytes | `D2C8566F334A767887F57C4A15EA456CFC5E117767FCFE46C599CC754BC2BBA0` | `D2C8566F...` | **MATCH** |
| **Original Source CSV** | **`26,176`** bytes | `276809899064949CB8D45B8EF7523B8C2913780F0649650EA4EB494FB74894F6` | `27680989...` | **MATCH** |

*Finding:* Both dataset files are 100% byte-identical to their locked baselines.

---

## 6. Discrepancy & Anomaly Evaluation

- **Port & HTTP Availability:** Resolved. Cổng 10010 và 10011 đang LISTEN bình thường.
- **Data Integrity:** Hoàn toàn không có sai lệch dữ liệu hay SKU xung đột nào.
- **Database Non-Mutation:** Xác nhận không có thao tác ghi nào diễn ra trong quá trình kiểm tra.

---

## 7. Overall Evaluation: PASS

Tất cả các tiêu chí tiền kiểm tra sau khởi động đều đạt 100%:
- [x] Local.exe, Nginx, PHP, MySQL đang hoạt động.
- [x] Cổng 10010 và 10011 đang LISTEN.
- [x] 6/6 URLs trả về HTTP 200 OK.
- [x] Database giữ nguyên 0 sản phẩm, 0 variations, 0 SKU.
- [x] SHA-256 và dung lượng working CSV khớp 100%.

---

## 8. MANDATORY STOP

Tuân thủ nghiêm ngặt quy tắc an toàn của Stage 1E.3E:
1. **KHÔNG TẠO SNAPSHOT.**
2. **KHÔNG IMPORT CSV HOẶC TẠO SẢN PHẨM.**
3. **KHÔNG THAY ĐỔI CẤU HÌNH.**
4. **KHÔNG TỰ ĐỘNG CHUYỂN SANG GATE 2.**

Hệ thống **DỪNG LẠI TẠI ĐÂY** để chờ người dùng xem xét báo cáo và cấp phê duyệt riêng cho **GATE 2 (Tạo snapshot mới có timestamp)**.
