# STAGE 3N-R2M-R5-R7 — OPERATIONAL APPROVAL GATE MATRIX

**Project:** ATELIER — WordPress / WooCommerce Furniture E-commerce  
**Workspace:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\`  
**Scope:** Decoupled Operational Approval Boundaries & Lifecycle Invariants  
**Review Mode:** STRICTLY READ-ONLY STATIC REVIEW — NO OPERATIONAL EXECUTION  
**Current Status:** **SPECIFICATION PASS — OPERATIONAL EXECUTION STRICTLY BLOCKED**  
**Gate Invariant:** **GATE 4 REMAINS STRICTLY BLOCKED**

---

## 1. Executive Summary & Approval Governance

This matrix establishes the definitive, decoupled operational approval architecture for the ATELIER platform import workflow. Coarse operational groupings (such as bundling database access, snapshot creation, and restore rehearsal into a single decision) introduce unacceptable catastrophic risk.

Under this matrix, **each operational action requires its own explicit, independent authorization boundary**. No approval gate may be combined, assumed, or bypassed.

---

## 2. Decoupled Operational Approval Gate Specification

```mermaid
graph TD
    subgraph Pre-Gate: Static Verification
        PG[Pre-Gate: Source & File Verification]
    end

    subgraph Gate 1: Baseline Preservation & Isolation
        G1A[Gate 1A: Read-Only DB Identity Verification]
        G1B[Gate 1B: Fresh T-0 Baseline Snapshot Capture]
        G1C[Gate 1C: Isolated Restore Rehearsal in Auxiliary DB]
    end

    subgraph Gate 2: Protection, Isolation & Preflight
        G2A[Gate 2A: Storefront Freeze & Worker Pause]
        G2B[Gate 2B: HTTP Storefront-Lock Probe]
        G2C[Gate 2C: Live Read-Only SQL Preflight Query Suite]
        G2D[Gate 2D: WordPress CLI Verify Invocation - Conditional]
    end

    subgraph Gate 3: Ingestion Authorization
        G3[Gate 3: Multi-Stakeholder Evidence Review & Token Grant]
    end

    subgraph Gate 4: Production Ingestion
        G4[Gate 4: Controlled Import Execution]
    end

    subgraph Gate 4R: Triage & Recovery
        G4R_SW[Gate 4R-SW: Scoped Software Rollback]
        G4R_DR[Gate 4R-DR: Disaster Recovery Snapshot Restore]
        G4R_DBA[Gate 4R-DBA: Manual Surgical Remediation]
    end

    subgraph Gate 5: Acceptance & Storefront Thaw
        G5[Gate 5: Post-Import Acceptance & Public Release]
    end

    PG --> G1A
    G1A -->|Identity Confirmed| G1B
    G1B -->|Snapshot Verified| G1C
    G1C -->|Rehearsal Passed| G2A
    G2A -->|Store Frozen| G2B
    G2B -->|HTTP Locked| G2C
    G2C -->|SQL Preflight Passed| G2D
    G2D -->|Verify Passed| G3
    G3 -->|Token Issued| G4
    G4 -->|Execution Success| G5
    G4 -->|Soft Failure & Valid Manifest| G4R_SW
    G4 -->|Window 2 Crash / Corrupted DB| G4R_DR
    G4 -->|Unusual State / Edge Case| G4R_DBA
    G4R_SW -->|Rollback Verified| G1A
    G4R_DR -->|Database Restored| G1A
    G5 -->|Acceptance Passed| Complete((Storefront Live))
```

---

## 3. Comprehensive Approval Gate Detail

### Gate 1A: Read-Only Database Identity Verification
- **Purpose:** Confirm that the local MySQL client connects strictly to the expected LocalWP database instance before executing any snapshot or operational command.
- **Authorized Operation:** Connect to MySQL on port 10011 and execute identity query:
  ```sql
  SELECT DATABASE() AS active_db, USER() AS active_user, @@hostname AS host, @@port AS port, @@datadir AS datadir;
  ```
- **Expected Side Effects:** Zero database or filesystem mutations. Read-only session initialization.
- **Required Inputs:** LocalWP MySQL credentials (port 10011, user `root`, database `local`).
- **Prerequisites:** Pre-Gate physical file audits completed.
- **Acceptance Criteria:** `active_db === 'local'`; `port === 10011` (or local socket); user has administrative privileges on `local`.
- **Immediate Stop Condition:** `active_db !== 'local'` or connection points to an external or staging host.
- **Authorizing Role:** Database Administrator (DBA).
- **Blocker Linkage:** `BLK-10`.

---

### Gate 1B: T-0 Baseline Snapshot Creation
- **Purpose:** Capture a fresh, complete, post-Stage-1F database snapshot (`backups/stage_3n_t0_snapshot.sql`) immediately prior to any operational activity.
- **Authorized Operation:** Execute `mysqldump`:
  ```bash
  mysqldump -h 127.0.0.1 -P 10011 -u root -p local \
      --single-transaction \
      --quick \
      --hex-blob \
      --default-character-set=utf8mb4 \
      --no-tablespaces \
      --result-file="backups/stage_3n_t0_snapshot.sql"
  ```
- **Expected Side Effects:**
  - Filesystem: New SQL dump file written to `backups/stage_3n_t0_snapshot.sql` (~1.5–2.0 MB).
  - Database: Brief read transaction (`--single-transaction`). Zero database mutations.
- **Required Inputs:** Gate 1A sign-off; minimum 1 GB free disk space.
- **Prerequisites:** Gate 1A verified.
- **Acceptance Criteria:**
  1. `mysqldump` exits with code 0.
  2. Dump file size > 1.2 MB.
  3. SHA-256 computed and recorded.
  4. Static inspection confirms **zero** `USE ` or `CREATE DATABASE ` statements:
     ```powershell
     Select-String -Path "backups/stage_3n_t0_snapshot.sql" -Pattern "USE |CREATE DATABASE "
     ```
  5. Dump contains Stage 1F block templates (`wp_template`, `wp_template_part`).
- **Immediate Stop Condition:** Nonzero exit code, size < 1.2 MB, presence of `USE` statements, or missing Stage 1F templates.
- **Authorizing Role:** Database Administrator (DBA).
- **Blocker Linkage:** `BLK-02`.

---

### Gate 1C: Isolated Restore Rehearsal
- **Purpose:** Prove that the newly captured `stage_3n_t0_snapshot.sql` can be restored completely and cleanly into an isolated auxiliary database without error.
- **Authorized Operation:** Create temporary database `local_isolated_rehearsal`, restore dump, audit restored schema and templates, and drop temporary database:
  ```bash
  mysql -h 127.0.0.1 -P 10011 -u root -p -e "CREATE DATABASE local_isolated_rehearsal;"
  mysql -h 127.0.0.1 -P 10011 -u root -p local_isolated_rehearsal < "backups/stage_3n_t0_snapshot.sql"
  ```
- **Expected Side Effects:**
  - Database: Creates, writes, and subsequently drops auxiliary schema `local_isolated_rehearsal`.
  - Invariant: Zero reads or writes targeting database `local`.
- **Required Inputs:** Gate 1B verified dump file.
- **Prerequisites:** Gate 1B verified.
- **Acceptance Criteria:**
  1. Pre-restore check confirms target is strictly `local_isolated_rehearsal`.
  2. Restore exits with code 0.
  3. Restored database contains exactly 52 tables.
  4. Restored `wp_posts` contains Stage 1F custom templates (`front-page`, `archive-product`, `single-product`).
  5. Temporary database dropped cleanly after audit.
- **Immediate Stop Condition:** SQL syntax error, table count != 52, missing Stage 1F templates, or any query touching `local`.
- **Authorizing Role:** QA Lead & Database Administrator (DBA).
- **Blocker Linkage:** `BLK-03`.

---

### Gate 2A: Storefront Freeze & Background Worker Control
- **Purpose:** Prevent all concurrent database writes by shoppers, visitors, or background scheduled workers during preflight and import execution.
- **Authorized Operation:**
  1. Enable WooCommerce Coming Soon mode in `wp_options`:
     ```sql
     UPDATE wp_options SET option_value = 'yes' WHERE option_name = 'woocommerce_coming_soon';
     ```
  2. Pause WordPress cron and background workers:
     ```sql
     UPDATE wp_options SET option_value = '' WHERE option_name = 'cron';
     ```
- **Expected Side Effects:**
  - Database: Updates 2 rows in `wp_options`.
  - Application: Frontend storefront renders Coming Soon banner; unauthenticated checkout/cart browsing blocked.
- **Required Inputs:** Gate 1C sign-off (T-0 backup is proven restorable).
- **Prerequisites:** Gate 1A, 1B, and 1C completed and verified.
- **Acceptance Criteria:** `SELECT option_value FROM wp_options WHERE option_name = 'woocommerce_coming_soon'` returns `'yes'`.
- **Immediate Stop Condition:** Database update fails or errors.
- **Authorizing Role:** Project Owner & Operations Engineer.
- **Blocker Linkage:** `BLK-08`.

---

### Gate 2B: HTTP Storefront-Lock Verification
- **Purpose:** Verify via automated HTTP probe that the active block theme actually intercepts public traffic and prevents catalog data leakage.
- **Authorized Operation:** Execute read-only cURL probes as an unauthenticated visitor:
  ```bash
  curl -s -i "http://localhost:10010/" | head -n 30
  curl -s -i "http://localhost:10010/shop/" | head -n 30
  ```
- **Expected Side Effects:** Read-only web server GET request. Zero database mutations. Cookies disabled.
- **Required Inputs:** Gate 2A sign-off.
- **Prerequisites:** Gate 2A completed.
- **Acceptance Criteria:**
  1. Both `/` and `/shop/` return HTTP 200.
  2. HTML contains Coming Soon banner text.
  3. HTML contains zero product catalog items, prices, or add-to-cart buttons.
- **Immediate Stop Condition:** HTTP request renders full catalog or bypasses Coming Soon mode.
- **Authorizing Role:** QA Engineer.
- **Blocker Linkage:** `BLK-09`.

---

### Gate 2C: Live Read-Only SQL Preflight Query Suite
- **Purpose:** Perform comprehensive, direct verification of all database baselines, taxonomy terms, attributes, attachment ID 5, and lookup tables without bootstrapping WordPress.
- **Authorized Operation:** Execute the 11 read-only SQL queries defined in Runbook Section 4 (P-06 Part 2).
- **Expected Side Effects:** Pure database `SELECT` queries. Zero row modifications.
- **Required Inputs:** Gate 2B sign-off.
- **Prerequisites:** Gate 2B completed.
- **Acceptance Criteria:**
  1. Query 1: Active DB is `local`, port is 10011.
  2. Query 2: Exactly 52 tables with prefix `wp_`.
  3. Query 3: `siteurl` and `home` return `http://localhost:10010`.
  4. Query 4: 3 global attributes (`brand`, `color`, `size`) exist.
  5. Query 5: 7 product categories and global attribute terms exist.
  6. Query 6: Zero existing products (`post_type IN ('product', 'product_variation')` returns 0 rows).
  7. Query 7: Attachment ID 5 exists, is `woocommerce-placeholder`, and has image MIME type.
  8. Query 8: Zero postmeta rows with `meta_key = '_controlled_import_run_id'`.
  9. Query 9: Zero conflicting SKUs exist in `wp_postmeta`.
  10. Query 10: `woocommerce_coming_soon` is `'yes'`.
  11. Query 11: Lookup tables (`wp_wc_product_meta_lookup`, `wp_wc_product_attributes_lookup`) are empty (count = 0).
- **Immediate Stop Condition:** Any query returns unexpected data, existing products, conflicting SKUs, or missing attributes.
- **Authorizing Role:** Technical Lead.
- **Blocker Linkage:** `BLK-05`, `BLK-06`.

---

### Gate 2D: WordPress CLI Verify Execution (Conditional)
- **Purpose:** Verify that the PHP CLI environment, memory limits, and WooCommerce runtime class loaders function properly when executed through the importer harness.
- **Authorized Operation:** Execute CLI `verify` action:
  ```bash
  php app/public/wp-content/plugins/run_controlled_import.php verify
  ```
- **Expected Side Effects:**
  - Memory: Adjusts PHP `memory_limit` to 512M via `ini_set`.
  - WordPress Runtime: Loads `wp-load.php`, initializes active plugins (`plugins_loaded`, `init` hooks).
  - Database: Reads `wp_postmeta` for SKUs; may update expired transients during core initialization.
  - Filesystem: Zero file writes.
- **Required Inputs:** Gate 2C sign-off.
- **Prerequisites:** Gate 2C completed.
- **Acceptance Criteria:** CLI outputs `[SUCCESS] Preflight verification passed! Working CSV and target DB verified.` and exits with code 0.
- **Immediate Stop Condition:** Nonzero exit code or PHP exception.
- **Authorizing Role:** Technical Lead.
- **Blocker Linkage:** `BLK-04`, `BLK-05`.

---

### Gate 3: Ingestion Authorization Review
- **Purpose:** Formal multi-stakeholder human gate to review all evidence from Gates 1A through 2D before issuing the live execution token.
- **Authorized Operation:** Inspect audit logs, cryptographic digests, and preflight outputs. Generate authorization token: `--gate4-user-approved-execute`.
- **Expected Side Effects:** Zero technical mutation. Administrative review and token generation.
- **Required Inputs:** Completed, verified evidence logs from Gates 1A, 1B, 1C, 2A, 2B, 2C, and 2D.
- **Prerequisites:** All 11 blockers in `STAGE_3N-R2M-R5-R6_BLOCKER_MATRIX.md` confirmed closed.
- **Acceptance Criteria:** Unanimous sign-off by Project Owner, Lead Security Auditor, and DBA.
- **Immediate Stop Condition:** Any open blocker, missing proof, or unresolved contradiction.
- **Authorizing Role:** Project Owner & Lead Security Auditor.
- **Blocker Linkage:** `BLK-07`.

---

### Gate 4: Controlled Import Execution
- **Purpose:** Ingest the 22 furniture products (64 total parent/variation entities) into WooCommerce under strict resource monitoring.
- **Authorized Operation:** Execute import CLI command:
  ```bash
  php app/public/wp-content/plugins/run_controlled_import.php import --gate4-user-approved-execute
  ```
- **Expected Side Effects:**
  - Database: Inserts 22 parent products, 42 variations, and image attachments; updates postmeta with SKUs, prices, stock (449 total), and ownership tags `_controlled_import_run_id`; populates lookup tables.
  - Filesystem: Writes durable manifest to `backups/manifests/manifest_run_YYYYMMDD_HHMMSS_xxxx.json`.
- **Required Inputs:** Gate 3 token `--gate4-user-approved-execute`.
- **Prerequisites:** Gate 3 completed.
- **Acceptance Criteria:**
  1. CLI outputs `[SUCCESS] Import completed successfully!` and exits with code 0.
  2. Manifest file is written and marked `COMPLETED`.
  3. Zero unhandled PHP exceptions or timeouts.
- **Immediate Stop Condition:** Any PHP fatal error, database exception, or nonzero exit code. If aborted, halt immediately and trigger Gate 4R triage.
- **Authorizing Role:** Execution Operator.
- **Blocker Linkage:** `BLK-11`.

---

### Gate 4R: Recovery & Disaster Triage
- **Purpose:** Select and execute the appropriate recovery pathway if Gate 4 fails or Gate 5 acceptance fails.
- **Triage Decision Tree:**
  1. **Gate 4R-SW (Software Rollback):**
     - **Condition:** Failure occurred gracefully inside PHP `try-catch`; manifest exists and is readable; all created posts possess valid `_controlled_import_run_id` tags.
     - **Operation:** `php run_controlled_import.php rollback <manifest_path>`
     - **Verification:** Reverse-order deletion (variations, parents, attachments); attachment ID 5 preserved; zero products remaining.
  2. **Gate 4R-DR (Disaster Recovery Snapshot Restore):**
     - **Condition:** Failure occurred in Window 2 (untagged orphan created); hard crash / OOM; manifest missing or corrupted; or software rollback failed.
     - **Operation:** `mysql -h 127.0.0.1 -P 10011 -u root -p local < "backups/stage_3n_t0_snapshot.sql"`
     - **Verification:** Database restored to exact T-0 state; 52 tables; Stage 1F templates verified intact; zero product orphans.
  3. **Gate 4R-DBA (Manual Surgical Remediation):**
     - **Condition:** Edge case where software rollback is blocked and snapshot restore is contraindicated.
     - **Operation:** DBA identifies orphan post ID using AUTO_INCREMENT sequence and deletes it with audited, scoped SQL.
- **Authorizing Role:** Project Owner & DBA.
- **Blocker Linkage:** `BLK-07`, `BLK-11`.

---

### Gate 5: Post-Import Acceptance & Storefront Release
- **Purpose:** Verify imported data integrity against authoritative baselines before releasing the storefront to the public.
- **Authorized Operation:**
  1. Execute acceptance SQL queries:
     - 22 parent products (`post_type = 'product'`).
     - 42 variations (`post_type = 'product_variation'`).
     - Total stock = 449.
     - 64 unique SKUs.
     - Lookup table rows = 64.
  2. If accepted, unfreeze storefront:
     ```sql
     UPDATE wp_options SET option_value = 'no' WHERE option_name = 'woocommerce_coming_soon';
     ```
  3. Re-enable WordPress cron and background workers.
  4. Perform post-release cURL smoke test on `/shop/` and single product page.
- **Expected Side Effects:** Re-enables public shopping access; resumes background cron processing.
- **Required Inputs:** Gate 4 success; manifest status `COMPLETED`.
- **Prerequisites:** Gate 4 completed.
- **Acceptance Criteria:** All acceptance queries match baselines 100%; shop page renders product catalog; zero 404s on images.
- **Immediate Stop Condition:** Any metric mismatch triggers immediate **Gate 4R Rollback**. Storefront remains frozen.
- **Authorizing Role:** Project Owner & QA Lead.
- **Blocker Linkage:** `BLK-11`.

---

## 4. Responsibility & Authority Matrix

| Gate | Name | Authority | Permitted Action | Side Effects | Stop Condition |
|---|---|---|---|---|---|
| **Pre-Gate** | Static Verification | Lead Security Auditor | Read & hash files | None | Hash mismatch or missing file |
| **Gate 1A** | DB Identity Verification | Database Administrator | `SELECT DATABASE()` | None | Target != `local` or wrong port |
| **Gate 1B** | T-0 Snapshot Creation | Database Administrator | `mysqldump` | Writes dump file | Nonzero exit code or `USE` statements |
| **Gate 1C** | Isolated Restore Rehearsal | QA Lead & DBA | Restore into auxiliary DB | Temp DB created & dropped | Table count != 52 or missing templates |
| **Gate 2A** | Storefront Freeze | Operations Engineer | Set Coming Soon = 'yes' | Updates `wp_options` | Public access not blocked |
| **Gate 2B** | HTTP Lock Verification | QA Engineer | cURL `/` and `/shop/` | None (HTTP GET) | Public catalog renders |
| **Gate 2C** | Live Read-Only SQL Preflight | Technical Lead | 11 `SELECT` queries | None | Existing products or SKU collision |
| **Gate 2D** | WordPress CLI Verify | Technical Lead | Importer `verify` | Sets memory limit, loads WP | Nonzero exit code or WP error |
| **Gate 3** | Ingestion Authorization | Project Owner & Auditor | Issue Gate 4 token | None | Any blocker open or missing proof |
| **Gate 4** | Controlled Import | Execution Operator | Importer `import` | Mutates DB, writes manifest | PHP exception or timeout |
| **Gate 4R** | Rollback / DR Recovery | Project Owner & DBA | Rollback or restore | Deletes posts or restores DB | Unrecoverable orphan |
| **Gate 5** | Acceptance & Release | Project Owner & QA Lead | Set Coming Soon = 'no' | Updates `wp_options`, thaws cron | Metric mismatch |

---

## 5. Mandatory Operational Sign-Off Invariant

**ALL OPERATIONAL ACTIVITY REQUIRES SEQUENTIAL, SEPARATE APPROVAL.**  
**NO GATE MAY BE MERGED, BYPASSED, OR EXECUTED CONCURRENTLY.**  
**GATE 4 REMAINS STRICTLY BLOCKED. ZERO RUNTIME OPERATIONS ARE AUTHORIZED BY THIS DOCUMENT.**
