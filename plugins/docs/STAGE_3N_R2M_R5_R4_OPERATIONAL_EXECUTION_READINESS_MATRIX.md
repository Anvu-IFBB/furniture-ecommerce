# STAGE 3N-R2M-R5-R4 — OPERATIONAL EXECUTION READINESS MATRIX

**Project:** ATELIER — WordPress / WooCommerce Furniture E-commerce  
**Workspace:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\`  
**Target Importer:** `app\public\wp-content\plugins\run_controlled_import.php`  
**Test Suite:** `app\public\wp-content\plugins\scratch\test_r2m_isolated_suite.php`  
**Working Dataset:** `data-preparation/woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv`  
**Document Status:** **`AUTHORITATIVE READINESS MATRIX — NOT AUTHORIZED FOR EXECUTION`**  
**Audit Basis:** `docs/STAGE_3N_R2M_R5_R4_OPERATIONAL_SAFETY_AUDIT.md`  
**Date:** 2026-10-19  
**Decision:** **`PASS (READINESS MATRIX APPROVED)`**  
**Gate 4 Status:** **`MANDATORY STOP — GATE 4 IS STRICTLY BLOCKED & NOT AUTHORIZED`**

---

## 1. MATRIX SCOPE & OPERATIONAL INVARIANTS

This matrix establishes the comprehensive engineering readiness criteria, verification commands, stop conditions, and sign-off requirements for each operational phase leading to Gate 4.

> [!CAUTION]
> **STRICT EXECUTION LOCK:**  
> This document is a static readiness matrix and control instrument only. Under NO circumstances does this document authorize running `php run_controlled_import.php import --gate4-user-approved-execute`, creating or restoring database snapshots, connecting to live MySQL, or mutating any production file. All commands specified herein are marked **[NOT EXECUTED - DESIGN SPECIFICATION ONLY]**.

---

## 2. OPERATIONAL PHASES & READINESS CRITERIA MATRIX

| Phase # | Operational Phase Title | Prerequisites Required | Method / Command [NOT EXECUTED] | Expected Acceptance Target | Stop / Abort Condition | Approval Gate |
|---|---|---|---|---|---|---|
| **P-01** | **Operational Freeze Enforcement** | Maintenance window agreed | Verify active sessions in `wp_usermeta`; pause WP-Cron; verify no background PHP tasks | Zero active sessions; zero active ActionScheduler actions; WP-Cron paused | Any active session updated within 30 min, or cron task in-progress | **Gate 1** (Freeze Sign-Off) |
| **P-02** | **Storefront Access Control Validation** | `woocommerce_coming_soon = yes` set | Unauthenticated HTTP GET probe to `http://localhost:10010/` and `/shop/` with `Cache-Control: no-cache` | HTTP 200/302 serving Coming Soon page; 0 catalog items or filters visible | Public shop archive accessible; product grid rendered; HTTP 200 with normal shop | **Gate 1** (Lock Sign-Off) |
| **P-03** | **Fresh Post-1F T-0 Snapshot Creation** | P-01 and P-02 verified PASS | `mysqldump.exe --host=127.0.0.1 --port=10011 --user=root local > backups/gate4_snapshot_t0_<TS>.sql` | Exit code 0; size > 1.25 MB; SHA-256 computed; exactly 52 `DROP/CREATE` tables; 0 `USE` statements | Exit code != 0; stderr warnings; size < 1.25 MB; presence of `USE` statement | **Gate 1** (Snapshot Sign-Off) |
| **P-04** | **Auxiliary Environment Setup** | P-03 snapshot verified | Initialize temp datadir in `AppData/Local/Temp/gate4_aux_datadir`; launch mysqld on port `13306` | Process active on port 13306; console confirms `ready for connections. Version: '8.4.0' port: 13306` | Port collision; daemon fails to start; datadir overlaps LocalWP live directory | **Gate 2** (Auxiliary Setup) |
| **P-05** | **Pre-Restore Target Protection Validation** | P-04 daemon active | Programmatic handshake: `SELECT @@port, @@datadir, DATABASE();` on port 13306 | `@@port = 13306`, `DATABASE() = 'furniture_restore_test'`, `@@datadir` matches temp directory | `@@port != 13306` OR `@@datadir` contains LocalWP live path OR DB is `'local'` | **Gate 2** (Target Validation) |
| **P-06** | **Isolated Restore Dry-Run Rehearsal** | P-05 target handshake PASS | Ingest snapshot into `furniture_restore_test` on `127.0.0.1:13306` via `mysql.exe --port=13306` | Ingestion exit code 0; zero errors; exactly 52 tables created | Ingestion syntax error; collation error; exit code != 0 | **Gate 2** (Restore Rehearsal) |
| **P-07** | **Restored Baseline State Audit** | P-06 restore completed | Direct SQL queries on port 13306 auditing products, attributes, terms, and attachment ID 5 | 0 products, 0 variations, 0 SKUs, Attachment ID 5 intact, 3 attributes, 11 terms, 8 categories | Any count mismatch; missing attribute or term; missing table | **Gate 2** (Rehearsal Audit) |
| **P-08** | **Live Database Zero-Mutation Verification** | P-06 and P-07 completed | Direct SQL count of `wp_posts` on live port `10011` before vs after rehearsal | Post count on port 10011 strictly identical (zero writes to live database) | Any row count change on live database port 10011 | **Gate 2** (Zero-Mutation Proof) |
| **P-09** | **Auxiliary Environment Teardown** | P-08 verified PASS | `mysqladmin.exe --port=13306 shutdown` and delete `gate4_aux_datadir` | Daemon cleanly stopped; temporary directory completely removed | Auxiliary process left orphaned in memory | **Gate 2** (Teardown Sign-Off) |
| **P-10** | **Live Read-Only Preflight Execution** | Gate 2 fully cleared | Execute 10 direct read-only SQL/PowerShell checks against live database `local:10011` | All 10 checks evaluate to 100% PASS with zero ambiguities | Any check returns FAIL or unexpected state | **Gate 3** (Preflight Sign-Off) |
| **P-11** | **Controlled Import Live Dispatch** | Gate 3 fully cleared | `php -d memory_limit=512M run_controlled_import.php import --gate4-user-approved-execute` | Exit code 0; manifest marked `COMPLETED`; reconciliation status `PASSED` | Exception thrown; manifest FAILED; non-zero exit code | **Gate 4** (Import Authorization) |
| **P-12** | **Post-Import Reconciliation Audit** | P-11 completed | Direct SQL inspection of products, variations, SKUs, stock total, categories, lookup tables | 22 parents (12 simple, 10 variable), 42 variations, 64 SKUs, 449 stock units, 27 cat assocs | Stock count != 449, SKU count != 64, missing variation link | **Gate 5** (Acceptance Sign-Off) |
| **P-13** | **Emergency Rollback / Recovery (Contingency)** | Triggered if P-11 or P-12 fails | If tagged: `php run_controlled_import.php rollback <manifest>`. If Window 2 orphan: restore T-0 snapshot | Clean rollback to 0 products OR clean snapshot restore to pre-import baseline | Secondary rollback failure (manifest `FAILED_ROLLBACK_INCOMPLETE`) | **Gate 4R** (Emergency Recovery) |
| **P-14** | **Operational Freeze Release** | P-12 signed off | Re-enable WP-Cron; permit admin logins; confirm `woocommerce_coming_soon = yes` | Normal administrative access restored; storefront remains closed to public | Coming Soon prematurely disabled; cron errors | **Gate 5** (Release Sign-Off) |

---

## 3. GRANULAR APPROVAL GATES MATRIX (B-06 SPECIFICATION)

| Gate ID | Permitted Action | Preconditions | Evidence Required | Responsible Role | Strict Exclusions |
|---|---|---|---|---|---|
| **Gate 1** | Capture T-0 Snapshot | P-01 (Freeze) & P-02 (Storefront Lock) PASS | Active sessions = 0, HTTP Coming Soon = 200/302 | Lead Database Engineer & Site Owner | Running import, restoring snapshot |
| **Gate 2** | Execute Isolated Restore Rehearsal | Snapshot size > 1.25 MB, SHA-256 verified | Port 13306 handshake, 52 tables restored, 0 live mutations | Database Safety Auditor | Modifying live port 10011, running import |
| **Gate 3** | Execute Live Read-Only Preflight | Gate 2 cleared with 100% rehearsal match | 10/10 preflight checks PASS on live DB | Independent QA Auditor | Running import, creating/writing manifests |
| **Gate 4** | Dispatch Controlled Import | Gate 3 cleared; T-0 snapshot on standby | Signed authorization instrument with parameter lock | Project Owner & Release Lead | Running rollback without cause, altering options |
| **Gate 4R** | Execute Emergency Recovery | Import failed, or Window 2 orphan detected | Failure diagnostic log, manifest status | Safety Auditor & Project Owner | Resuming import without root-cause analysis |
| **Gate 5** | Accept Import & Release Freeze | 64 SKUs / 449 stock units reconciled | Manifest COMPLETED, zero errors recorded | Project Owner | Disabling Coming Soon before launch date |

---

## 4. DETAILED POST-IMPORT ACCEPTANCE TARGETS

Following any authorized import execution, the database must be audited against these authoritative targets:
- **Parent Products:** Exactly 22 (`post_type = 'product'`, `post_status = 'publish'`). 12 simple products and 10 variable parent products.
- **Product Variations:** Exactly 42 (`post_type = 'product_variation'`, `post_status = 'publish'`).
- **Catalog SKUs:** Exactly 64 unique non-empty SKUs in `wp_postmeta` under `_sku`.
- **Inventory Stock:** Exactly 449 total stock units across the 64 SKUs. Variable parents direct stock = 0.
- **Global Attributes:** `pa_color` (3 terms: Black, Brown, White), `pa_size` (3 terms: S, M, L), `pa_brand` (5 terms: LuxeVibe, Minimalia, NordicLiving, OakCraft, UrbanoHaus).
- **Product Categories:** Exactly 7 dataset categories (`Beds`, `Chairs`, `Decoration`, `Lighting`, `Sofas`, `Storage`, `Tables`), with exactly 27 category associations.
- **Lookup Tables:** Exactly 64 rows in `wp_wc_product_meta_lookup`; attribute taxonomies correctly synced in `wp_wc_product_attributes_lookup`.
- **Media Attachment ID 5:** Untouched, type `attachment`, MIME `image/webp`, GUID pointing to `woocommerce-placeholder.webp`.
- **Manifest State:** Status `'COMPLETED'`, `reconciliation.status = 'PASSED'`, zero errors recorded.
- **Storefront Maintenance:** `woocommerce_coming_soon` remains `'yes'` (Storefront remains closed to the public).

---

## 5. CONCLUSION & FINAL GATE VERDICT

- **Readiness Matrix Status:** **`PASS (SPECIFICATION APPROVED)`**  
  All 14 operational phases have enforceable invariants, objective acceptance standards, containment safeguards, and explicit stop conditions.
- **Production Execution Authority:**  
  **`MANDATORY STOP — GATE 4 REMAINS STRICTLY BLOCKED & NOT AUTHORIZED`**  
  No production import, live database connection, or snapshot restoration is authorized by this matrix.
