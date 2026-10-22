# STAGE 3N-R2M-R5-R3 — GATE 4 BLOCKER CLOSURE OPERATIONAL PLAN

**Project:** ATELIER — WordPress / WooCommerce Furniture E-commerce  
**Workspace:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\`  
**Target Importer:** `app\public\wp-content\plugins\run_controlled_import.php`  
**Test Suite:** `app\public\wp-content\plugins\scratch\test_r2m_isolated_suite.php`  
**Working Dataset:** `data-preparation/woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv`  
**Document Status:** **`DRAFT OPERATIONAL PLAN — NOT AUTHORIZED FOR EXECUTION`**  
**Audit Basis:** `docs/STAGE_3N_R2M_R5_R3_GATE4_BLOCKER_CLOSURE_AUDIT.md`  
**Date:** 2026-10-19  
**Decision:** **`PASS (PLAN SPECIFICATION ONLY)`**  
**Gate 4 Status:** **`MANDATORY STOP — GATE 4 IS STRICTLY BLOCKED & NOT AUTHORIZED`**

---

## 1. OPERATIONAL SCOPE & ABSOLUTE EXECUTION LOCK

This plan establishes the authoritative, step-by-step engineering sequence required to systematically resolve all six Gate 4 blockers before any production import can be authorized.

> [!CAUTION]
> **MANDATORY STOP — ZERO EXECUTION IN THIS STAGE:**  
> This document is an operational specification only. It does NOT authorize executing commands against the live database, connecting to MySQL port 10011, bootstrapping WordPress, or running `php run_controlled_import.php import --gate4-user-approved-execute`. All commands specified herein are marked **[NOT EXECUTED - DESIGN SPECIFICATION ONLY]**.

---

## 2. BLOCKER CLOSURE SEQUENCE OVERVIEW

The six blockers must be cleared in strict linear dependency order. No step may begin until the preceding step's acceptance criteria are fully met and signed off:

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│ STEP 1: Operational Freeze & Storefront Lock Verification (B-04, B-05)          │
│ ➔ Confirms admin freeze, pauses WP-Cron, validates Coming Soon HTTP boundary    │
└────────────────────────────────────────┬────────────────────────────────────────┘
                                         │ Approval Gate 1
┌────────────────────────────────────────▼────────────────────────────────────────┐
│ STEP 2: Fresh Post-Stage-1F T-0 Snapshot Generation (B-01)                       │
│ ➔ Captures current baseline, validates size, computes SHA-256, verifies header │
└────────────────────────────────────────┬────────────────────────────────────────┘
                                         │ Approval Gate 2
┌────────────────────────────────────────▼────────────────────────────────────────┐
│ STEP 3: Isolated Dry-Run Restore Rehearsal on Port 13306 (B-02)                 │
│ ➔ Tests restoration in auxiliary instance, proves zero live mutations           │
└────────────────────────────────────────┬────────────────────────────────────────┘
                                         │ Approval Gate 3
┌────────────────────────────────────────▼────────────────────────────────────────┐
│ STEP 4: Live Read-Only Operational Preflight Execution (B-03)                   │
│ ➔ Executes 10 direct SQL checks; proves schema, terms, attachment ID 5, SKUs    │
└────────────────────────────────────────┬────────────────────────────────────────┘
                                         │ Approval Gate 4 (Final Preflight Clearance)
┌────────────────────────────────────────▼────────────────────────────────────────┐
│ STEP 5: Controlled Import Live Execution & Post-Reconciliation                  │
│ ➔ Runs importer with --gate4-user-approved-execute, audits 449 stock units      │
└────────────────────────────────────────┬────────────────────────────────────────┘
                                         │ Approval Gate 5 (Post-Import Acceptance)
┌────────────────────────────────────────▼────────────────────────────────────────┐
│ STEP 6: Release Operational Freeze (B-04 Release)                               │
│ ➔ Restores normal administrative operations; preserves Coming Soon until launch│
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 3. STEP-BY-STEP BLOCKER CLOSURE PROTOCOL

### STEP 1: Operational Freeze & Storefront Lock Verification (Blockers B-04 & B-05)

#### Objective:
Guarantee that zero administrative modifications, background cron jobs, or public customer requests can alter the database during the preflight and import window.

#### Protocol:
1. **Administrative Freeze Enforcement (B-04):**
   - Confirm all external administrative browser sessions are logged out.
   - Verify zero background cron processes are running.
   - Disable WordPress alternate cron in runtime.
2. **Storefront Lock Verification (B-05):**
   - Query option via direct read-only SQL:
     ```sql
     -- [NOT EXECUTED - DESIGN SPECIFICATION ONLY]
     SELECT option_name, option_value 
     FROM wp_options 
     WHERE option_name = 'woocommerce_coming_soon';
     ```
   - Execute unauthenticated HTTP GET probe via PowerShell:
     ```powershell
     # [NOT EXECUTED - DESIGN SPECIFICATION ONLY]
     $res = Invoke-WebRequest -Uri "http://localhost:10010/shop/" -UseBasicParsing
     # Response must contain Coming Soon markup / 200/302 redirect, NOT catalog items
     ```
3. **Acceptance Criteria:**
   - `option_value` is exactly `'yes'`.
   - HTTP response confirms storefront catalog is blocked to unauthenticated traffic.
   - Admin activity confirmed frozen.
4. **Responsible Role:** Lead Safety Engineer & Release Auditor.

---

### STEP 2: Post-Stage-1F T-0 Snapshot Creation & Integrity Check (Blocker B-01)

#### Objective:
Capture the current post-Stage-1F database baseline into an immutable SQL dump before any import operation is initiated.

#### Protocol:
1. **Directory Preparation:**
   - Ensure destination directory exists: `<project_root>/backups/`.
   - Name format: `gate4_snapshot_t0_<YYYYMMDD_HHMMSS>.sql`.
2. **Execution Command [NOT EXECUTED - DESIGN SPECIFICATION ONLY]:**
   ```powershell
   # [NOT EXECUTED - DESIGN SPECIFICATION ONLY]
   & "C:\Users\Vu Cong Minh\AppData\Roaming\Local\lightning-services\mysql-8.4.0\bin\win64\bin\mysqldump.exe" `
       --host=127.0.0.1 --port=10011 --user=root --protocol=TCP `
       --add-drop-table --routines --triggers --hex-blob `
       --result-file="C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\backups\gate4_snapshot_t0_20261019_T0.sql" `
       local
   ```
3. **Four-Tier Structural Verification:**
   - **Exit Code:** `$LASTEXITCODE == 0`.
   - **Physical Size:** File size $> 1.25\text{ MB}$.
   - **Cryptographic Hash:** Compute post-dump SHA-256 via `Get-FileHash`.
   - **Header & Footer Inspection:**
     - Starts with `-- MySQL dump`.
     - Ends with `-- Dump completed on ...`.
     - Contains 52 `DROP TABLE IF EXISTS` and `CREATE TABLE` definitions.
4. **Non-Database Asset Check:**
   - Verify Git status of `app/public/wp-content/themes/twentytwentyfive-child` is clean.
   - Verify `wp-content/uploads/woocommerce-placeholder.webp` exists on disk.
5. **Acceptance Criteria:** Dump file verified, SHA-256 recorded, non-database assets confirmed clean.
6. **Responsible Role:** Database Reliability Engineer.

---

### STEP 3: Isolated Dry-Run Restore Rehearsal on Port 13306 (Blocker B-02)

#### Objective:
Prove semantic restorability of the T-0 snapshot in an isolated disposable auxiliary instance with provable zero writes to live database port 10011.

#### Protocol:
1. **Auxiliary Environment Setup:**
   - Initialize temporary data directory:
     ```powershell
     # [NOT EXECUTED - DESIGN SPECIFICATION ONLY]
     & "C:\Users\Vu Cong Minh\AppData\Roaming\Local\lightning-services\mysql-8.4.0\bin\win64\bin\mysqld.exe" `
         --no-defaults --initialize-insecure `
         --datadir="C:\Users\Vu Cong Minh\AppData\Local\Temp\gate4_aux_datadir"
     ```
   - Start auxiliary daemon on port `13306`:
     ```powershell
     # [NOT EXECUTED - DESIGN SPECIFICATION ONLY]
     Start-Process -FilePath "C:\Users\Vu Cong Minh\AppData\Roaming\Local\lightning-services\mysql-8.4.0\bin\win64\bin\mysqld.exe" `
         -ArgumentList "--no-defaults --port=13306 --datadir=C:\Users\Vu Cong Minh\AppData\Local\Temp\gate4_aux_datadir" `
         -WindowStyle Hidden
     ```
2. **Accidental Live Mutation Prevention Check:**
   - Query auxiliary instance:
     ```sql
     -- [NOT EXECUTED - DESIGN SPECIFICATION ONLY]
     SELECT @@port, @@datadir, DATABASE();
     ```
   - **STOP CONDITION:** If port is `10011` or datadir matches LocalWP live directory, **ABORT IMMEDIATELY**.
3. **Create Database & Restore Dump:**
   ```powershell
   # [NOT EXECUTED - DESIGN SPECIFICATION ONLY]
   & "...\mysql.exe" --host=127.0.0.1 --port=13306 --user=root -e "CREATE DATABASE furniture_restore_test;"
   & "...\mysql.exe" --host=127.0.0.1 --port=13306 --user=root furniture_restore_test < "...\backups\gate4_snapshot_t0_20261019_T0.sql"
   ```
4. **Post-Restore Comparative Audit on Port 13306:**
   - Verify exactly 52 tables created.
   - Verify 0 products, 0 variations, 0 SKUs.
   - Verify Attachment ID 5 intact.
   - Verify 3 attributes (`color`, `size`, `brand`), 11 attribute terms, 8 categories (`product_cat`).
   - Verify `woocommerce_coming_soon = 'yes'`.
5. **Live Baseline Verification on Port 10011:**
   - Query live database on port 10011 to prove zero mutations occurred during rehearsal.
6. **Auxiliary Teardown:**
   - Shutdown daemon: `& "...\mysqladmin.exe" --port=13306 shutdown`.
   - Delete temporary datadir: `Remove-Item -Recurse "C:\Users\Vu Cong Minh\AppData\Local\Temp\gate4_aux_datadir"`.
7. **Acceptance Criteria:** Restoration completes with exit code 0; all counts match baseline; live database confirmed 100% untouched.
8. **Responsible Role:** Database Reliability Engineer & Safety Auditor.

---

### STEP 4: Live Read-Only Operational Preflight Execution (Blocker B-03)

#### Objective:
Execute all 10 verified preflight checks against live database `local` on port `10011` without bootstrapping WordPress.

#### Protocol:
Execute the 10 audited checks from Runbook V3:
1. **Check 1 (DB Identity & Port):** `SELECT DATABASE(), @@port, @@hostname, @@version, @@datadir;` (Port = 10011, DB = local).
2. **Check 2 (9-Table Schema Inventory):** Query `information_schema.tables` for all 7 core tables + `wp_wc_product_meta_lookup` + `wp_wc_product_attributes_lookup` (Count = 9).
3. **Check 3 (Site & Home Routing):** Query `wp_options` for `siteurl` and `home` (Both = `http://localhost:10010`).
4. **Check 4A (Attributes):** Query `wp_woocommerce_attribute_taxonomies` (Count = 3: `color`, `size`, `brand`).
5. **Check 4B (Taxonomy Terms):** Query `wp_terms` joined to `wp_term_taxonomy` (`pa_brand`=5, `pa_color`=3, `pa_size`=3, `product_cat`=8).
6. **Check 5 (Product Baseline):** Query `wp_posts` for `post_type IN ('product', 'product_variation')` (Count = 0).
7. **Check 6 (Media ID 5):** Query `wp_posts` and `wp_postmeta` for ID 5 (Type = attachment, MIME = `image/webp`, file = `woocommerce-placeholder.webp`).
8. **Check 7 (Manifest & Tagged Orphans):** Query `wp_postmeta` for `_controlled_import_run_id` (Count = 0); scan `backups/manifests/` (0 incomplete files).
9. **Check 8 (Coming Soon):** Query `wp_options` for `woocommerce_coming_soon` (Value = `yes`).
10. **Check 9 & 10 (File Hashes):** Verify working CSV (`D2C8566F...`, 65 lines), importer (`DBF3F73C...`), test suite (`C8843ED5...`).
11. **Acceptance Criteria:** All 10 checks evaluate to **`PASS`**. Zero ambiguous results.
12. **Responsible Role:** Release Engineer & Independent QA Auditor.

---

### STEP 5: Controlled Import Live Execution & Post-Reconciliation

#### Objective:
Dispatch the controlled import harness with parameter-locked authorization and verify complete reconciliation of the 22 furniture products.

#### Protocol:
1. **Execution Command [NOT EXECUTED - DESIGN SPECIFICATION ONLY]:**
   ```powershell
   # [NOT EXECUTED - DESIGN SPECIFICATION ONLY]
   cd "C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\wp-content\plugins"
   & "C:\Users\Vu Cong Minh\AppData\Roaming\Local\lightning-services\php-8.2.29+0\bin\win64\php.exe" -d memory_limit=512M `
       run_controlled_import.php import --gate4-user-approved-execute
   ```
2. **Post-Import Reconciliation Audit:**
   - Manifest status: Must be `'COMPLETED'` with zero errors.
   - Simple products created: Exactly 12.
   - Variable parent products created: Exactly 10.
   - Product variations created: Exactly 42.
   - Total catalog stock units: Exactly 449.
   - Category associations: Exactly 27.
   - Attachment ID 5: Completely preserved and unmutated.
3. **Emergency Rollback Contingency (Approval 4R):**
   - If ANY reconciliation check fails or an unhandled exception occurs:
     ```powershell
     # [NOT EXECUTED - CONTINGENCY SPECIFICATION ONLY]
     & "...\php.exe" run_controlled_import.php rollback <manifest_path>
     ```
   - If an untagged orphan (Window 2 crash) is detected: Halt immediately and execute **T-0 Database Snapshot Restoration**.
4. **Acceptance Criteria:** Import exit code 0; manifest marked COMPLETED; 100% reconciliation match.
5. **Responsible Role:** Release Lead & Database Safety Engineer.

---

### STEP 6: Release Operational Freeze (Blocker B-04 Release)

#### Objective:
Safely return the site to standard operational state while maintaining the storefront Coming Soon lock pending final launch.

#### Protocol:
1. Re-enable WordPress cron if disabled.
2. Permit administrative logins.
3. Verify `woocommerce_coming_soon` remains `'yes'` (Storefront remains closed to public).
4. Archive all preflight logs, snapshot hashes, and the final manifest into `docs/`.
5. **Responsible Role:** Lead Operations Engineer & Site Owner.

---

## 4. GRANULAR APPROVAL GATES (BLOCKER B-06 SPECIFICATION)

Under Safety Invariant B-06, each operational phase requires its own independent, signed authorization block. **Approval of any step never implies authorization of subsequent steps:**

### Approval Gate 1: Operational Freeze & T-0 Snapshot Creation
- **Action Authorized:** Capturing `gate4_snapshot_t0_<TIMESTAMP>.sql` via `mysqldump`.
- **Preconditions Required:** Admin freeze confirmed active; storefront lock verified.
- **Sign-Off Required:** Lead Database Engineer & Site Owner.

### Approval Gate 2: Isolated Restore Rehearsal
- **Action Authorized:** Starting auxiliary mysqld on port `13306` and restoring T-0 dump into `furniture_restore_test`.
- **Preconditions Required:** Gate 1 cleared; snapshot size and SHA-256 verified.
- **Sign-Off Required:** Database Safety Auditor.

### Approval Gate 3: Live Read-Only Operational Preflight
- **Action Authorized:** Executing 10 read-only SQL and filesystem checks against live database `local:10011`.
- **Preconditions Required:** Gate 2 cleared with 100% isolated restore match.
- **Sign-Off Required:** Independent QA Auditor.

### Approval Gate 4: Production Import Authorization
- **Action Authorized:** Running `php run_controlled_import.php import --gate4-user-approved-execute`.
- **Preconditions Required:** Gate 3 cleared (10/10 preflight checks PASS); T-0 snapshot confirmed on standby.
- **Sign-Off Required:** Project Owner & Lead Release Engineer.

### Approval Gate 4R: Emergency Recovery Authorization (Contingency)
- **Action Authorized:** Executing CLI rollback OR restoring T-0 snapshot into `local:10011`.
- **Trigger Conditions:** Import failure, Window 2 crash, or reconciliation mismatch.
- **Sign-Off Required:** Lead Safety Auditor & Project Owner.

### Approval Gate 5: Operational Acceptance & Freeze Release
- **Action Authorized:** Releasing administrative freeze after verified import.
- **Preconditions Required:** Manifest COMPLETED; 64 SKUs / 449 stock units verified.
- **Sign-Off Required:** Project Owner.

---

## 5. LEAST-PRIVILEGE DATABASE ACCOUNT SPECIFICATION

- **Current Architecture:** LocalWP operates with `root` privileges on `127.0.0.1:10011`.
- **Operational Assessment:** While the importer code only executes DML (`SELECT, INSERT, UPDATE, DELETE`), WooCommerce Core and active plugins execute hooks (`save_post`, lookup table synchronization) that may evaluate temporary tables or transients.
- **Recommendation:** Do not attempt ad-hoc MySQL user provisioning in production LocalWP without prior isolated testing. The operation proceeds under standard service credentials, with fail-safe recovery guaranteed by the verified T-0 snapshot.
- **Status:** **`OUTSTANDING / UNVERIFIED`**.

---

## 6. CONCLUSION & FINAL GATE DECISION

- **Operational Plan Status:** **`PASS (DESIGN SPECIFICATION APPROVED)`**  
  All six blockers have concrete, auditable, evidence-based closure protocols.
- **Production Gate 4 Decision:**  
  **`MANDATORY STOP — GATE 4 REMAINS STRICTLY BLOCKED & NOT AUTHORIZED`**  
  No production import, live database connection, or snapshot restoration is authorized by this document. Execution requires explicit authorization of Phase 1 through Phase 5 under separate sign-offs.
