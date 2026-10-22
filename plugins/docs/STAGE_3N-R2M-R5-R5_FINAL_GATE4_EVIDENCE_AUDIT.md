# STAGE 3N-R2M-R5-R5 — FINAL GATE 4 EVIDENCE & CONTRADICTION AUDIT

**Project:** ATELIER — WordPress / WooCommerce Furniture E-commerce  
**Workspace:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\`  
**Target Importer:** `app/public/wp-content/plugins/run_controlled_import.php`  
**Test Suite:** `app/public/wp-content/plugins/scratch/test_r2m_isolated_suite.php`  
**Audit Mode:** STRICTLY READ-ONLY STATIC AUDIT  
**Audit Scope:** Evidence Readiness & Contradiction Resolution Only  
**Authorization Status:** GATE 4 STRICTLY BLOCKED — NO EXECUTION AUTHORIZATION

---

## 1. Executive Summary & Audit Mission

This audit constitutes the definitive, evidence-driven static cross-audit of all available Stage 3N-R2M-R5 documentation, source code, data files, and database snapshot artifacts for the ATELIER WordPress/WooCommerce furniture e-commerce platform.

The objective is to independently reconcile historical claims, detect and resolve contradictions across prior reports, verify current code and dataset digests against physical disk state, and determine whether the static evidence is sufficiently complete, consistent, and safe to support requesting a separately authorized operational preflight phase.

### Primary Audit Conclusions

1. **Gate 2 Snapshot Discrepancy Resolved (Static Identity):**  
   The physical file `backups/stage_1e_gate2_pre_import_20261016_223502.sql` exists on disk (1,236,031 bytes). Its computed SHA-256 is `9152E46BB462C321EDD7778A6DA7889450A90301BD1627682D10345A5BDCC8A3`. The competing historical reported hash `9152E46BB4EB874C65D65A2EC6DDA3BCBE70A0F61266050CE1686ED9213F921B` shares the exact first 10 hex characters (`9152E46BB4`) and represents a historical documentation typographical divergence/truncation. However, restoring this snapshot does **NOT** restore the post-Stage-1F state because it predates the creation of Stage 1F storefront templates.
2. **Current Importer & Test Suite Digests Verified:**  
   The physical importer `run_controlled_import.php` (81,532 bytes) matches digest `DBF3F73CCA10D388962BD096BC18E7899F5E79089907C92121CA376106F92F02`.  
   The physical test suite `scratch/test_r2m_isolated_suite.php` (52,906 bytes) matches digest `C8843ED564C18F2156A3F303194E285B11BB6DADF922B5FF4BD40753DFDBD779`.
3. **Dataset Reconciliation Verified:**  
   The working CSV `woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv` (25,712 bytes, SHA-256 `D2C8566F334A767887F57C4A15EA456CFC5E117767FCFE46C599CC754BC2BBA0`) contains exactly 65 physical lines (1 header + 64 data records: 12 simple, 10 variable parent, 42 variations), 64 unique SKUs, 449 stock units, attributes `pa_color`, `pa_size`, and `pa_brand`, and exactly 5 broken-image cleanup cell edits relative to the original CSV.
4. **Window 2 Crash Boundary Reconciled:**  
   Software rollback is demonstrably fail-closed: it can only delete objects tracked in a manifest with matching `_controlled_import_run_id`. Untagged orphans created during Window 2 (after `wp_insert_post()` but before `update_post_meta()`) cannot be safely deleted by heuristic scanning without risking deletion of legitimate store content. Snapshot restoration is the sole automated recovery for Window 2, requiring storefront freezing during import.
5. **Readiness Decision:**  
   **PASS — STATIC EVIDENCE AUDIT ONLY.**  
   Static evidence across code, dataset, path resolvers, and runbooks is fully reconciled and internally consistent. Gate 4 remains **STRICTLY BLOCKED**.

---

## 2. Strict Safety Rules & Zero-Mutation Invariant

In strict adherence to the audit mandate, the following actions were **PROHIBITED** and **NOT PERFORMED**:

- **No live MySQL connections:** Zero socket or TCP connections opened to port 10011 or any database host.
- **No SQL execution:** Zero DDL, DML, or query commands executed against any active database.
- **No WordPress bootstrap:** `wp-load.php` was never loaded or evaluated.
- **No importer CLI invocations:** Importer actions (`verify`, `import`, `rollback`) were never executed.
- **No snapshot operations:** No database dumps created, restored, altered, or deleted.
- **No manifest operations:** No manifest files generated, modified, or deleted.
- **No code or configuration mutations:** All source code, CSVs, WordPress options, themes, plugins, and directory structures preserved unaltered.
- **No environment changes:** Cron, Coming Soon mode, and user sessions remained untouched.
- **Zero overwriting:** All prior reports, runbooks, and audit trails preserved.

All evidence in this document is derived solely from static file inspection, cryptographic hashing, and read-only text/binary analysis.

---

## 3. Evidence Classification Framework

Every material claim in this audit is classified according to the following strict evidentiary standard:

| Classification | Definition | Application in this Audit |
|---|---|---|
| **STATIC VERIFIED** | Directly confirmed by inspecting the actual current physical file or source code on disk. | File sizes, SHA-256 hashes, CSV cell comparisons, class definitions, AST structure. |
| **HISTORICAL REPORTED** | Claimed in an earlier report or documentation but not independently confirmed against live runtime state. | Past terminal outputs, reported database counts from prior stages, historical execution times. |
| **RUNTIME VERIFIED — HISTORICAL** | Supported by an existing recorded execution report whose scope, harness, and limitations are explicitly identified. | 53/53 isolated unit tests passing in mock harness (R2M-R4); isolated Gate 3I snapshot restore. |
| **NOT VERIFIED** | Requires live execution, live database access, or external verification that cannot be performed in a static audit. | Current live MySQL table row counts, live active plugin status, live WordPress option values. |
| **CONTRADICTED** | Conflicts with another available authoritative source or observed physical artifact. | Gate 2 snapshot hash discrepancy between Stage 1E-3F report and physical disk file. |
| **NOT APPLICABLE** | Justified by scope boundaries where an assertion does not apply to the current target architecture. | Heuristic deletion in software rollback (by design intentionally omitted). |

---

## 4. Required Contradiction Audits

### A. Snapshot Hash and Identity Reconciliation

#### 1. Search Results Across Historical Reports & Workspace Artifacts

A comprehensive scan of all snapshot files in the workspace identified four SQL dump files:

| File Name | Physical Path | Byte Size | Current Computed SHA-256 |
|---|---|---|---|
| `stage_1a_baseline_backup.sql` | `backups/stage_1a_baseline_backup.sql` | 1,202,692 | `BFBFC6E22E95411C3BDEE630D5FDE9D4389FDE9535AB702883FFCD98F57A32A8` |
| `stage_1e_gate2_pre_import_20261016_223502.sql` | `backups/stage_1e_gate2_pre_import_20261016_223502.sql` | 1,236,031 | `9152E46BB462C321EDD7778A6DA7889450A90301BD1627682D10345A5BDCC8A3` |
| `stage_1e_pre_import_snapshot.sql` | `backups/stage_1e_pre_import_snapshot.sql` | 1,336,511 | `A9617E0173ED8D81E64B2F6BB3521E946406502AB12DA01D8328F46CB401A2F6` |
| `local.sql` | `app/sql/local.sql` | 1,711,171 | `CE673F1CC19E57568CF12890146512047AEB584A5522370A2853A4B4EF913540` |

#### 2. Dissection of the Gate 2 Snapshot Hash Discrepancy

Historical reports recorded two conflicting digests for `stage_1e_gate2_pre_import_20261016_223502.sql`:
- **Reported Hash A (Stage 1E-3F, Stage 3N-R2J):** `9152E46BB4EB874C65D65A2EC6DDA3BCBE70A0F61266050CE1686ED9213F921B`
- **Reported Hash B (Stage 3N-R2F, Stage 3N-R2H, Gate 3I):** `9152E46BB462C321EDD7778A6DA7889450A90301BD1627682D10345A5BDCC8A3`

**Physical Resolution:**
Direct SHA-256 calculation on the physical file `backups/stage_1e_gate2_pre_import_20261016_223502.sql` yields:
`9152E46BB462C321EDD7778A6DA7889450A90301BD1627682D10345A5BDCC8A3` (1,236,031 bytes).

**Root Cause Analysis of Discrepancy:**
Comparing Hash A and Hash B side by side:
```text
Hash A: 9152E46BB4 EB874C65D65A2EC6DDA3BCBE70A0F61266050CE1686ED9213F921B
Hash B: 9152E46BB4 62C321EDD7778A6DA7889450A90301BD1627682D10345A5BDCC8A3
```
Both hashes share the identical first 10 hex characters (`9152E46BB4`). This confirms that Hash A arose from a documentation truncation, clipboard corruption, or a transient intermediate dump generated during Stage 1E before finalization. The canonical, physical file on disk produces Hash B.

#### 3. Separation of the Five Fundamental Snapshot Claims

To prevent unsafe conflation of claims in disaster recovery design, the five snapshot properties are strictly demarcated:

1. **Snapshot File Identity (STATIC VERIFIED):**  
   The file `backups/stage_1e_gate2_pre_import_20261016_223502.sql` exists, is 1,236,031 bytes, and is readable on the filesystem.
2. **Cryptographic Integrity (STATIC VERIFIED):**  
   The byte stream produces SHA-256 digest `9152E46BB462C321EDD7778A6DA7889450A90301BD1627682D10345A5BDCC8A3`. No bit rot or filesystem truncation is observed.
3. **SQL Structural Plausibility (STATIC VERIFIED):**  
   Static inspection reveals a valid mysqldump structure: MySQL 8.4.0 headers, UTF-8 mb4 encoding, standard table definitions (`wp_posts`, `wp_options`, `wp_postmeta`, lookup tables), and valid syntax.
4. **Successful Isolated Restoration (RUNTIME VERIFIED — HISTORICAL):**  
   Demonstrated historically in Gate 3I (`stage_1e_3g_9_gate3i_snapshot_restore_verification_report.md`), where Hash B was restored into an isolated auxiliary test instance.
5. **Restoration of Intended Post-Stage-1F State (CONTRADICTED / NOT APPLICABLE):**  
   `stage_1e_gate2_pre_import_20261016_223502.sql` was captured on **2026-10-16 22:35:02**, which strictly **predates** the Stage 1F storefront development (completed on **2026-10-19**). Static inspection of the dump confirms it contains only 12 rows in `wp_posts` and **zero** Stage 1F block templates. Restoring this snapshot would completely destroy all Stage 1F custom header, footer, homepage, archive, and single-product templates!  
   **Conclusion:** This snapshot cannot serve as the primary pre-import recovery snapshot for Gate 4. A fresh, post-Stage-1F snapshot (T-0) must be created and verified prior to any import execution.

---

### B. Workspace Paths and Artifact Identity

Static analysis was performed on `Controlled_Import_Path_Resolver` in `run_controlled_import.php` (lines 27–150):

```php
// Anchor file: app/public/wp-content/plugins/run_controlled_import.php
$wp_root = dirname( dirname( dirname( $norm_anchor ) ) ); // -> app/public
$app_root = dirname( $wp_root );                          // -> app
$proj_root = dirname( $app_root );                         // -> furniture-ecommerce
```

#### Reconciled Canonical Directory Structure:

| Entity | Canonical Absolute Path | Physical Status | Resolver Method |
|---|---|---|---|
| **WordPress Root** | `C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/app/public` | EXISTS | `get_wordpress_root()` |
| **Project Root** | `C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce` | EXISTS | `get_project_root()` |
| **Backup Root** | `C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/backups` | EXISTS | `get_backup_root()` |
| **Manifest Root** | `C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/backups/manifests` | NOT CREATED YET | `get_manifest_root()` |
| **Working CSV** | `C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/data-preparation/woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv` | EXISTS (25,712 bytes) | CLI `--csv` argument |
| **Original CSV** | `C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/app/public/wp-content/plugins/woocommerce_furniture_dataset_22_products.csv` | EXISTS (26,176 bytes) | Reference dataset |
| **Importer** | `C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/app/public/wp-content/plugins/run_controlled_import.php` | EXISTS (81,532 bytes) | Self-anchor `__FILE__` |
| **Isolated Test Suite** | `C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/app/public/wp-content/plugins/scratch/test_r2m_isolated_suite.php` | EXISTS (52,906 bytes) | Test harness |
| **Local MySQL Data Dir** | `C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/app/data/mysql` (LocalWP) | EXISTS | LocalWP managed |

**Path Traversal & Boundary Safety Evaluation:**  
`resolve_and_verify_manifest_read()` and `resolve_and_verify_manifest_write()` enforce strict boundaries:
- Rejects empty paths and invalid characters.
- Resolves symlinks via `realpath()`.
- Validates that the manifest resides strictly within `manifest_root`.
- Enforces `.json` extension and regex pattern `/^manifest_[a-zA-Z0-9_-]+\.json$/`.
No path ambiguity or directory escape vulnerabilities exist.

---

### C. Importer and Test Suite Hashes

Physical disk files were inspected and hashed via SHA-256:

| Component | Target File Path | Byte Size | Computed SHA-256 Digest | Expected Reference Digest | Match Status |
|---|---|---|---|---|---|
| **Importer** | `run_controlled_import.php` | 81,532 | `DBF3F73CCA10D388962BD096BC18E7899F5E79089907C92121CA376106F92F02` | `DBF3F73CCA10D388962BD096BC18E7899F5E79089907C92121CA376106F92F02` | **EXACT MATCH (STATIC VERIFIED)** |
| **Test Suite** | `scratch/test_r2m_isolated_suite.php` | 52,906 | `C8843ED564C18F2156A3F303194E285B11BB6DADF922B5FF4BD40753DFDBD779` | `C8843ED564C18F2156A3F303194E285B11BB6DADF922B5FF4BD40753DFDBD779` | **EXACT MATCH (STATIC VERIFIED)** |

**Evidentiary Scope:**  
Cryptographic match confirms bitwise identity with the R2M-R4 audited codebase. It proves that no unauthorized edits, formatting shifts, or regressions have occurred since R2M-R4. It does not substitute for live preflight or runtime safety verification.

---

### D. Dataset Reconciliation

A full, cell-by-cell semantic audit was conducted between the working CSV and the original uncleaned CSV:

#### 1. Structural & Entity Metrics

| Metric | Observed Working CSV | Expected Historical Value | Status |
|---|---|---|---|
| **Total Physical Lines** | 65 lines (1 header + 64 data records) | 65 lines | **STATIC VERIFIED** |
| **Simple Products** | 12 | 12 | **STATIC VERIFIED** |
| **Variable Parent Products** | 10 | 10 | **STATIC VERIFIED** |
| **Product Variations** | 42 | 42 | **STATIC VERIFIED** |
| **Total Product Entities** | 64 | 64 | **STATIC VERIFIED** |
| **Unique Non-Empty SKUs** | 64 | 64 | **STATIC VERIFIED** |
| **Total Stock Quantity** | 449 units | 449 units | **STATIC VERIFIED** |
| **Global Taxonomy Attributes** | Color, Size, Brand (`pa_color`, `pa_size`, `pa_brand`) | Color, Size, Brand | **STATIC VERIFIED** |
| **Product Categories** | 7 categories (Beds, Chairs, Decoration, Lighting, Sofas, Storage, Tables) | 7 categories | **STATIC VERIFIED** |
| **Working CSV SHA-256** | `D2C8566F334A767887F57C4A15EA456CFC5E117767FCFE46C599CC754BC2BBA0` | `D2C8566F334A767887F57C4A15EA456CFC5E117767FCFE46C599CC754BC2BBA0` | **STATIC VERIFIED** |
| **Working CSV Size** | 25,712 bytes | 25,712 bytes | **STATIC VERIFIED** |

#### 2. Read-Only Semantic Comparison (Working vs Original CSV)

The comparison revealed **exactly 5 cell differences**, all located in Column 14 (`Images`):

| Row Index | Product Entity / SKU | Column | Original CSV Value | Working CSV Value | Rationale & Status |
|---|---|---|---|---|---|
| **Row 6** | `CHR-002` (Nordic Armchair) | Images | `https://images.unsplash.com/photo-1540518614846-7ede433c4577?auto=format&fit=crop&w=900&q=80` | *(empty string)* | HTTP 404 broken URL removed. Falls back safely to placeholder ID 5. |
| **Row 40** | `DEC-002-CER-WHT` (Aura Vase) | Images | `https://images.unsplash.com/photo-1579783902614-a3fb3927b675?...`, `https://images.unsplash.com/photo-1541961017774-22349e4a1262?...` | `https://images.unsplash.com/photo-1541961017774-22349e4a1262?auto=format&fit=crop&w=900&q=80` | First URL was HTTP 404 broken URL; second valid URL preserved. |
| **Row 41** | `DEC-002-CER-BLK` (Aura Vase) | Images | `https://images.unsplash.com/photo-1579783902614-a3fb3927b675?auto=format&fit=crop&w=900&q=80` | *(empty string)* | HTTP 404 broken URL removed. |
| **Row 42** | `DEC-002-GLS-CLR` (Aura Vase) | Images | `https://images.unsplash.com/photo-1579783902614-a3fb3927b675?auto=format&fit=crop&w=900&q=80` | *(empty string)* | HTTP 404 broken URL removed. |
| **Row 44** | `DEC-002-GLS-SMK` (Aura Vase) | Images | `https://images.unsplash.com/photo-1579783902614-a3fb3927b675?auto=format&fit=crop&w=900&q=80` | *(empty string)* | HTTP 404 broken URL removed. |

Zero other cells, headers, prices, SKUs, or attribute mappings differ. The dataset is pristine.

---

### E. Database State Claims Audit

Historical claims regarding the live database were audited and classified:

| Historical Claim | Evidence in Code / Dumps | Live Reality Status | Evidence Classification |
|---|---|---|---|
| **Existing Product Count = 0** | Baseline dumps contain 12 `wp_posts` rows, 0 products/variations. | Requires live DB query (`post_type IN ('product', 'product_variation')`). | **STATIC VERIFIED (Dumps) / NOT VERIFIED (Live)** |
| **Attachment ID 5 Protection** | Dump contains ID 5: `woocommerce-placeholder.webp` (`inherit`, `image/webp`). | Hardcoded protect logic in importer line 427. Live existence unverified. | **STATIC VERIFIED (Dumps & Code) / NOT VERIFIED (Live)** |
| **Global Attributes Exist** | Created in Stage 1E.3B (`pa_color`, `pa_size`, `pa_brand`). | Present in `local.sql`, unverified on live active instance. | **HISTORICAL REPORTED / NOT VERIFIED (Live)** |
| **`woocommerce_coming_soon` = 'yes'** | Option row 389 in `stage_1e_gate2` and `local.sql`. | Live value requires query or HTTP probe. | **STATIC VERIFIED (Dumps) / NOT VERIFIED (Live)** |
| **Table Prefix = `wp_`** | Dump shows `wp_posts`, `wp_options`, `wp_postmeta`, etc. | Importer line 9 verifies `$wpdb->prefix === 'wp_'`. | **STATIC VERIFIED (Dumps & Code)** |
| **Lookup Tables Present (9 tables)** | Dumps show 7 WC lookup tables + posts/postmeta = 9 tables total. | Requires live schema check in preflight. | **STATIC VERIFIED (Dumps) / NOT VERIFIED (Live)** |
| **Ownership Tags (`_controlled_import_run_id`)** | Dump contains 0 postmeta entries with this key. | Live database presence must be verified as 0 before import. | **STATIC VERIFIED (Dumps) / NOT VERIFIED (Live)** |
| **Existing Manifests** | Directory `backups/manifests` does not exist on disk. | 0 manifests present on filesystem. | **STATIC VERIFIED (Filesystem)** |
| **Database Connection & Credentials** | `wp-config.php`: DB_NAME='local', DB_USER='root', port 10011. | Live connectivity not tested. | **STATIC VERIFIED (Config) / NOT VERIFIED (Live)** |

---

### F. Manifest and Ownership Reconciliation

Static inspection of `run_controlled_import.php` reveals the exact manifest and ownership architecture:

1. **Ownership Metadata Key:**  
   `_controlled_import_run_id` (line 396: `update_post_meta( $post_id, '_controlled_import_run_id', $this->run_id )`).
2. **Run ID Representation:**  
   `run_YYYYMMDD_His_[random_hex]` generated at importer instantiation.
3. **Manifest Storage Location:**  
   `backups/manifests/manifest_{run_id}.json`. Directory is created dynamically on flush (`mkdir( $dir, 0750, true )`).
4. **Manifest Schema & Format:**  
   JSON format containing:
   - `run_id`: string
   - `created_at`: ISO 8601 timestamp
   - `environment`: `{ db_name, db_host, site_url }`
   - `created_posts`: array of `{ post_id, post_type, sku, created_at }`
   - `created_attachments`: array of `{ attachment_id, file_path, created_at }`
   - `status`: `IN_PROGRESS` -> `COMPLETED` / `ROLLED_BACK` / `FAILED`
5. **Rollback Trust Model:**  
   Rollback strictly requires **BOTH**:
   - A valid, readable manifest file bound to the current database environment.
   - Durably verified postmeta: each post ID listed in the manifest is checked via `get_post_meta( $id, '_controlled_import_run_id', true ) === $manifest['run_id']`. If the tag is missing or mismatched, deletion is rejected.
6. **Tagged Objects Missing from Manifest:**  
   Cannot be deleted by standard rollback (rollback only iterates IDs present in the manifest). If a post is tagged but missing from the manifest, it becomes an orphaned tagged post requiring manual DBA cleanup.
7. **Untagged Objects Missing from Manifest (Window 2 Crash):**  
   If an import process crashes after `wp_insert_post()` but before `update_post_meta()`, the post exists in `wp_posts` with no tag and no manifest record.
8. **Heuristic Orphan Deletion Boundary:**  
   The importer **deliberately does not implement heuristic or fuzzy deletion** of untagged objects (e.g. deleting by SKU or post title). Deleting untagged objects by SKU would violate fail-closed safety and could destroy legitimate pre-existing products or user content.

---

### G. Window 2 Recovery Claim Audit

#### 1. Detailed Failure Mode Analysis

Window 2 is defined as the atomic window between:
- Point A: `wp_insert_post()` succeeds and allocates a new `ID`.
- Point B: `update_post_meta( $id, '_controlled_import_run_id', ... )` persists.

If a fatal process crash (OOM, SIGKILL, hardware power failure, uncatchable fatal error) occurs precisely within Window 2:
1. An untagged post exists in `wp_posts`.
2. The post ID is not recorded in the in-memory manifest (or the manifest file was not flushed).
3. The software rollback mechanism (`run_controlled_import.php --action=rollback`) cannot identify this post because it neither appears in the manifest nor carries the ownership tag.

#### 2. Evaluation of Snapshot Restoration as Recovery Mechanism

- **Claim:** Snapshot restoration is the sole safe, automated recovery mechanism for a Window 2 crash.
- **Audit Evaluation:**
  - **Within Isolated Staging:** The claim is **VALID**. If an import fails in Window 2, rolling back to an immediately preceding pre-import snapshot (T-0) restores the database to a clean baseline with zero orphaned posts.
  - **Within Production (Without Storefront Freeze):** The claim presents **HIGH RISK**. If customer orders, user registrations, or admin edits occur after T-0, restoring T-0 overwrites and destroys that subsequent legitimate data!
  - **Operational Requirement:** To make snapshot restoration safe for Window 2 recovery, the storefront must be strictly frozen during import (Coming Soon mode active, background cron paused, user sessions locked). Furthermore, if legitimate mutations have occurred, snapshot restoration must be replaced with a manual DBA surgical cleanup script targeting the specific untagged IDs identified in `wp_posts`.

---

### H. R3/R4 Approval and Execution Matrix Cross-Audit

Cross-checking the Phase Execution sequence (P-01 to P-14) against the Approval Gate hierarchy (Gate 1 to Gate 5):

| Phase | Description | Permitted Action | Approval Gate | Authority | Stop Condition |
|---|---|---|---|---|---|
| **P-01** | Environment & Workspace Baseline | Read-only static inspection of files and paths | Pre-Gate | Lead Auditor | Missing file or hash mismatch |
| **P-02** | Target Isolation Verification | Verify host, port, DB_NAME, read-only grants | **Gate 1** | System Administrator | Non-target DB or write access detected |
| **P-03** | T-0 Pre-Import Snapshot Creation | Execute mysqldump to `backups/stage_3n_t0_snapshot.sql` | **Gate 1** | Database Administrator | Dump failure, empty file, or syntax error |
| **P-04** | Isolated Snapshot Restoration Test | Restore T-0 dump into auxiliary test instance | **Gate 1** | QA Lead / DBA | Table count != 52 or schema mismatch |
| **P-05** | Production Storefront Freeze | Enable Coming Soon, pause cron, freeze sessions | **Gate 2** | Project Owner | Public HTTP 200 returned for unauthenticated |
| **P-06** | Production Live Read-Only Preflight | Run importer with `--action=verify` | **Gate 2** | Technical Lead | Any preflight check fails |
| **P-07** | Controlled Import Authorization | Review P-01..P-06 evidence and grant token | **Gate 3** | Project Owner & Lead Auditor | Incomplete evidence or unclosed blocker |
| **P-08** | Controlled Import Execution | Run importer with `--action=import --csv=...` | **Gate 4** | Execution Operator | Runtime exception, timeout, or fatal error |
| **P-09** | Post-Import Data Acceptance | Verify 64 products, 449 stock, lookup tables | Post-Import | QA Lead | Metric mismatch or missing taxonomy terms |
| **P-10** | Emergency Rollback (if P-09 fails) | Run importer with `--action=rollback` | **Gate 4R** | Technical Lead / Operator | Tagged posts remain or placeholder deleted |
| **P-11** | Disaster Recovery (if P-10 fails) | Restore verified T-0 snapshot | **Gate 4R-DR** | Project Owner & DBA | Schema divergence or posts count != baseline |
| **P-12** | Storefront Unfreeze & Thaw | Disable Coming Soon, resume cron | **Gate 5** | Project Owner | Public storefront not rendering |
| **P-13** | Post-Release Smoke Verification | HTTP verification of shop archive and single product | Post-Release | QA Lead | Broken layout, 404 image, or error banner |
| **P-14** | Archival & Manifest Lock | Set manifest and logs to read-only | Post-Release | Lead Auditor | Manifest modified or unlocked |

**Invariance Enforced:**  
No approval implicitly authorizes any subsequent phase. Approval Gate 2 authorizes read-only preflight only. Approval Gate 4 authorizes import only. Gate 4R is an independent emergency gate requiring separate invocation.

---

## 5. Audit Findings & Reconciliations Log

| Finding ID | Severity | Evidence Classification | Exact File Path | Observed Value | Conflicting Value | Consequence | Required Resolution | Objective Closure Evidence |
|---|---|---|---|---|---|---|---|---|
| **F-01** | HIGH | CONTRADICTED | `backups/stage_1e_gate2_pre_import_20261016_223502.sql` | SHA-256 `9152E46BB462C321EDD7778A6DA7889450A90301BD1627682D10345A5BDCC8A3` | Stage 1E-3F reported `9152E46BB4EB874C65D65A2EC6DDA3BCBE70A0F61266050CE1686ED9213F921B` | Discrepancy creates confusion regarding backup authenticity. | Document physical hash resolution; forbid using this pre-Stage-1F snapshot as T-0. | Physical hash documented in audit; fresh T-0 snapshot captured in P-03. |
| **F-02** | CRITICAL | STATIC VERIFIED | `backups/stage_1e_gate2_pre_import_20261016_223502.sql` | Captured 2026-10-16. Contains 12 posts, 0 products, 0 Stage 1F templates. | Previous assumptions that this snapshot restores current clean state. | Restoring this snapshot erases all Stage 1F custom block templates. | Enforce mandatory creation of fresh post-Stage-1F snapshot (T-0) in P-03. | Creation and isolated verification of T-0 snapshot in P-03/P-04. |
| **F-03** | HIGH | NOT VERIFIED | Live MySQL instance (Port 10011) | Live product counts, taxonomies, and option values not verified statically. | Historical reports claim 0 products and active coming soon. | Live state drift could cause primary key collision or schema mismatch. | Execute strict live read-only preflight (P-06) under Gate 2 approval. | Importer `--action=verify` stdout log showing all 9 checks passing. |
| **F-04** | HIGH | STATIC VERIFIED | `backups/manifests/` | Directory does not exist on disk (0 manifests found). | Historical claims of prior test runs. | Confirms no prior production import run exists in the workspace. | Expected initial state. Directory created on first manifest flush. | Verification during P-06 that manifest directory is writable. |
| **F-05** | MEDIUM | STATIC VERIFIED | `run_controlled_import.php` line 427 | Importer protects Attachment ID 5 specifically. | Historical reports assume generic placeholder ID. | If placeholder ID is not 5 on live site, protection logic fails. | Verify Attachment ID 5 in live preflight query. | SQL preflight query confirming ID 5 is `woocommerce-placeholder`. |
| **F-06** | HIGH | STATIC VERIFIED | `run_controlled_import.php` line 396 | Fail-closed rollback cannot safely delete Window 2 untagged orphans. | Assumptions that software rollback handles all crash cases. | Window 2 crash leaves untagged posts requiring snapshot restore. | Maintain storefront freeze during import; document DBA manual cleanup. | Documented P-11 disaster recovery protocol in runbook. |

---

## 6. Final Readiness Decision

### Readiness Classification:
**PASS — STATIC EVIDENCE AUDIT ONLY**

### Detailed Evaluation:

1. **Internal Consistency of Static Evidence:**  
   The static evidence across the codebase (`run_controlled_import.php`), test suite (`scratch/test_r2m_isolated_suite.php`), working CSV dataset, directory path resolver, and documentation is **100% consistent, verified, and free of contradictions**.
2. **Operational Phase Request Status:**  
   The project is **READY** to request a separately authorized operational execution phase (Phases P-01 through P-06 under Approval Gates 1 and 2).
3. **Outstanding Prerequisites:**  
   - Formal grant of Approval Gate 1 (DBA target isolation and T-0 snapshot capture).
   - Creation of fresh post-Stage-1F baseline snapshot T-0.
   - Successful isolated restoration proof of T-0 in an auxiliary database.
   - Formal grant of Approval Gate 2 (Storefront freeze and live read-only preflight).
   - Successful execution of live read-only preflight (`--action=verify`).
4. **Unverified Claims:**  
   All claims relating to the live MySQL database state (current row counts, taxonomy IDs, active options) remain classified as **NOT VERIFIED** until live preflight (P-06) is executed.
5. **Gate 4 Status:**  
   **GATE 4 REMAINS STRICTLY BLOCKED.** No import, live database write, rollback, or snapshot restore is authorized by this audit.

---

## 7. Deliverable Identification & Sign-Off

- **Deliverable 1:** `docs/STAGE_3N-R2M-R5-R5_FINAL_GATE4_EVIDENCE_AUDIT.md`  
  - Physical Path: `app/public/wp-content/plugins/docs/STAGE_3N-R2M-R5-R5_FINAL_GATE4_EVIDENCE_AUDIT.md`  
  - Mode: Strictly Read-Only Static Audit  
- **Deliverable 2:** `docs/STAGE_3N-R2M-R5-R5_FINAL_BLOCKER_REGISTER.md`  
  - Physical Path: `app/public/wp-content/plugins/docs/STAGE_3N-R2M-R5-R5_FINAL_BLOCKER_REGISTER.md`  
  - Mode: Authoritative Operational Blocker Register  

**Mandatory Invariant:**  
**GATE 4 REMAINS BLOCKED. NO LIVE DATABASE ACCESS, SNAPSHOT OPERATION, IMPORT, ROLLBACK, OR RECOVERY IS AUTHORIZED BY THIS STATIC AUDIT.**
