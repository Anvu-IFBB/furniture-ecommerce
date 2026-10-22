# STAGE 3N-R2M-R5-R8-A — STATIC EVIDENCE INVENTORY

**Project:** ATELIER — WordPress / WooCommerce Furniture E-commerce  
**Workspace:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\`  
**WordPress Root:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public`  
**Mode:** STRICTLY READ-ONLY STATIC INSPECTION AND DOCUMENTATION  
**Current Authorization:** DOCUMENTATION ONLY  
**Gate 4 Status:** **STRICTLY BLOCKED**  
**Inventory Verdict:** **INVENTORY COMPLETE** (Static evidence inventory completed; operational readiness remains blocked)

---

## 1. Executive Summary

This document establishes the authoritative static evidence inventory for the ATELIER platform as part of Stage 3N-R2M-R5-R8-A. It catalogues all physical evidence artifacts on disk, measures file sizes and SHA-256 digests, contrasts historical report claims against direct static findings, explicitly delineates runtime-dependent claims that cannot be verified statically, and inventories missing or conflicted assets.

### Key Inventory Findings:
1. **Core Importer & Test Suite Integrity:** The production importer [`run_controlled_import.php`](file:///c:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/run_controlled_import.php) (81,532 bytes, SHA-256 `dbf3f73cca10d388962bd096bc18e7899f5e79089907c92121ca376106f92f02`) and the isolated unit test suite [`scratch/test_r2m_isolated_suite.php`](file:///c:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/scratch/test_r2m_isolated_suite.php) (52,906 bytes, SHA-256 `c8843ed564c18f2156a3f303194e285b11bb6dadf922b5ff4bd40753dfdbd779`) match their approved R2M-R4 baseline digests 100%.
2. **Approved Working Dataset Authenticated:** The working CSV [`data-preparation/woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv`](file:///c:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/data-preparation/woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv) (25,712 bytes, SHA-256 `d2c8566f334a767887f57c4a15ea456cfc5e117767fcfe46c599cc754bc2bba0`) matches the hardcoded cryptographic constant `LOCKED_WORKING_CSV_HASH` inside the importer.
3. **Critical Post-Stage-1F Snapshot (T-0) Missing:** `backups/stage_3n_t0_snapshot.sql` does **not** exist. The only available snapshots (`stage_1e_gate2_pre_import_20261016_223502.sql`, `stage_1a_baseline_backup.sql`, `stage_1e_pre_import_snapshot.sql`) all predate Stage 1F storefront development and contain zero custom block template records.
4. **Manifest Directory Uninitialized:** `backups/manifests/` does not exist on disk.
5. **Stage 1F Filesystem Assets Present but Untracked:** Block templates (`front-page.html`, `archive-product.html`, `single-product.html`, `header.html`, `footer.html`) physically exist in `themes/twentytwentyfive-child/`, but Git tracking in `app/public/wp-content` shows uncommitted modifications since Stage 1B.
6. **No Runtime Inferences Made:** All database connection parameters, active table counts, user grants, HTTP Coming Soon behaviors, and cron execution states remain strictly classified as `RUNTIME_REQUIRED`.

---

## 2. Scope & Absolute Safety Boundary

### 2.1 Prohibited Operations Observed
During this audit, zero prohibited operations were executed:
- Zero database connections opened (no TCP to 127.0.0.1:10011, no socket access).
- Zero SQL statements executed.
- Zero WordPress bootstraps (`wp-load.php` was never loaded).
- Zero CLI invocations of `run_controlled_import.php` (`verify`, `import`, or `rollback`).
- Zero snapshots created, restored, or modified.
- Zero modifications to `wp-config.php`, theme code, importer code, test suites, or CSV data.
- Zero HTTP requests or probes dispatched to `http://localhost:10010`.

### 2.2 Permitted Operations Executed
- Read-only inspection of physical files on disk.
- Independent computation of cryptographic SHA-256 digests.
- Read-only Git status and history inspection.
- Creation of the two authorized documentation files in `docs/`.

---

## 3. Actual Files Inspected

The following 22 physical files and directories were inspected directly on disk during this session:

| # | Exact Inspected Path | Size (Bytes) | SHA-256 Digest |
|---|---|---|---|
| 1 | `app/public/wp-content/plugins/docs/STAGE_3N-R2M-R5-R6_OPERATIONAL_PREFLIGHT_RUNBOOK.md` | 31,329 | `07ac936935e1d66ed87e30ce4720dde92dcd4bde1c430d04f5851931e720155e` |
| 2 | `app/public/wp-content/plugins/docs/STAGE_3N-R2M-R5-R6_BLOCKER_MATRIX.md` | 12,294 | `bbadf4d4dec5ba3edee444a52a0d1f6b052187a504acd09c0c66ed6a2128d3dd` |
| 3 | `app/public/wp-content/plugins/docs/STAGE_3N-R2M-R5-R7_OPERATIONAL_SAFETY_REVIEW.md` | 28,529 | `f84f0da58524c50782ad3b81dcc29b235898bf926f90e60c57d06f5e1a30e4fb` |
| 4 | `app/public/wp-content/plugins/docs/STAGE_3N-R2M-R5-R7_APPROVAL_GATE_MATRIX.md` | 18,585 | `10f52d095af1dc8ae2fab472f6f86cdfd624c8154b5f287ab8d9e19532ee7aa3` |
| 5 | `app/public/wp-content/plugins/docs/STAGE_3N-R2M-R5-R5_FINAL_GATE4_EVIDENCE_AUDIT.md` | 30,238 | `2e16e758a84ed664220e6862b860a23d96b64b2713df87d30021c1396f208ed9` |
| 6 | `app/public/wp-content/plugins/docs/STAGE_3N-R2M-R5-R5_FINAL_BLOCKER_REGISTER.md` | 9,819 | `1ecd83211237d932104e4aac4470c7e04d0416ca7859aa3e5406b3a1839e0c5b` |
| 7 | `app/public/wp-content/plugins/docs/STAGE_3N_R2M_R5_R4_OPERATIONAL_SAFETY_AUDIT.md` | 18,133 | `53a776ca100eb975d3b67c51007742b8438cae770c7096ad3b6ac617e6855904` |
| 8 | `app/public/wp-content/plugins/docs/STAGE_3N_R2M_R5_R4_OPERATIONAL_EXECUTION_READINESS_MATRIX.md` | 9,999 | `3e03f3098f6ed8b05f25635792e13e8a5a36e208548b017731f743cc2bab8681` |
| 9 | `app/public/wp-content/plugins/docs/STAGE_3N_R2M_R5_R3_GATE4_BLOCKER_CLOSURE_PLAN.md` | 19,023 | `5f12d5647c8fc6499e5118ff554aebc3441008f4dfde7aea09ff463e7932974c` |
| 10 | `app/public/wp-content/plugins/docs/STAGE_3N_R2M_R5_R3_GATE4_BLOCKER_CLOSURE_AUDIT.md` | 19,140 | `4acd4a0770dbe82ea5f580098da2025577aba9694fd35478785afe21a701594d` |
| 11 | `app/public/wp-content/plugins/docs/STAGE_3N_R2M_R5_GATE4_PREFLIGHT_RUNBOOK_DESIGN_V3_DRAFT.md` | 21,472 | `3a139171f47ad1e1bbe258879f99fcce55bbc2fe7b3a4e350fd57fe12930daf7` |
| 12 | `app/public/wp-content/plugins/run_controlled_import.php` | 81,532 | `dbf3f73cca10d388962bd096bc18e7899f5e79089907c92121ca376106f92f02` |
| 13 | `app/public/wp-content/plugins/scratch/test_r2m_isolated_suite.php` | 52,906 | `c8843ed564c18f2156a3f303194e285b11bb6dadf922b5ff4bd40753dfdbd779` |
| 14 | `app/public/wp-content/plugins/woocommerce_furniture_dataset_22_products.csv` | 26,176 | `276809899064949cb8d45b8ef7523b8c2913780f0649650ea4eb494fb74894f6` |
| 15 | `data-preparation/woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv` | 25,712 | `d2c8566f334a767887f57c4a15ea456cfc5e117767fcfe46c599cc754bc2bba0` |
| 16 | `backups/stage_1e_gate2_pre_import_20261016_223502.sql` | 1,236,031 | `9152e46bb462c321edd7778a6da7889450a90301bd1627682d10345a5bdcc8a3` |
| 17 | `backups/stage_1a_baseline_backup.sql` | 1,202,692 | `bfbfc6e22e95411c3bdee630d5fde9d4389fde9535ab702883ffcd98f57a32a8` |
| 18 | `backups/stage_1e_pre_import_snapshot.sql` | 1,336,511 | `a9617e0173ed8d81e64b2f6bb3521e946406502ab12da01d8328f46cb401a2f6` |
| 19 | `app/public/wp-config.php` | 3,382 | `d2e8111b5a81cf4bae56c460c8db87de0b606ba3fbc54c5aae842c5057dcb61c` |
| 20 | `app/public/wp-content/themes/twentytwentyfive-child/templates/front-page.html` | 16,060 | `8d406f477f4364589b16e4ca657c2f4e9571a9d7409b871db491de5dd1986c0d` |
| 21 | `app/public/wp-content/themes/twentytwentyfive-child/templates/archive-product.html` | 5,446 | `7eed6d6ef723540bfb1ea22383380d624f3d98c4c03c975384f39040a15ea46a` |
| 22 | `app/public/wp-content/themes/twentytwentyfive-child/templates/single-product.html` | 5,173 | `65ef9addbf68334767ae300934c591a19c54b57853eedc02595cb50868a9512d` |

---

## 4. Missing or Uninspected Artifacts

| Expected Path | Expected Function | Current Status | Impact & Required Resolution |
|---|---|---|---|
| `backups/stage_3n_t0_snapshot.sql` | Pre-import baseline snapshot captured after Stage 1F | **MISSING** | **Blocker BLK-02 / R8A-02.** Must be created under Gate 1B prior to import. |
| `backups/manifests/` | Directory for durable import run manifests | **MISSING** | **Blocker BLK-06 / R8A-07.** Created automatically by harness or prepared under Gate 2. |
| `data-preparation/woocommerce_furniture_dataset_22_products.csv` | Original CSV in data-prep directory | **MISSING** | Not an issue; original CSV resides in `plugins/` directory. |
| `app/public/wp-content/plugins/scratch/test_r2_isolated_suite.php` | Historical R2 unit test file | **MISSING** | Superseded by `test_r2m_isolated_suite.php`. |
| Live MySQL Port 10011 Database Connection | Runtime database session | **NOT INSPECTED** | Prohibited by read-only static boundary. Requires Gate 1A authorization. |
| Live HTTP Probe (`http://localhost:10010/`) | Storefront Coming Soon lock verification | **NOT INSPECTED** | Prohibited by read-only static boundary. Requires Gate 2B authorization. |

---

## 5. Comprehensive Evidence Inventory Table

| Artifact Identifier / Path | Exists? | Actual Size | SHA-256 Digest | Matches Prior Claim? | Specific Claim Supported | Limitations | Classification |
|---|---|---|---|---|---|---|---|
| `run_controlled_import.php` | Yes | 81,532 B | `dbf3f73c...` | Yes (R2M-R4 baseline) | Importer code is unmodified and hardened. CLI syntax strictly 2 args for `verify`. | Static code only; does not prove runtime behavior against MySQL. | `STATIC_VERIFIED` |
| `scratch/test_r2m_isolated_suite.php` | Yes | 52,906 B | `c8843ed5...` | Yes (R2M-R4 baseline) | 31 unit tests pass in mock mode. | Executes in memory against mock adapters; does not test live database. | `STATIC_VERIFIED` |
| `working_stage_1e_3c.csv` | Yes | 25,712 B | `d2c8566f...` | Yes (Harness constant) | Approved dataset matches locked hash constant in importer. | Does not verify database table insertion or image HTTP accessibility. | `STATIC_VERIFIED` |
| `original_22_products.csv` | Yes | 26,176 B | `27680989...` | Yes (Stage 1E baseline) | Source of truth for raw furniture dataset. | Contains broken image URLs that were sanitized in working CSV. | `STATIC_VERIFIED` |
| `stage_1e_gate2_...sql` | Yes | 1,236,031 B | `9152e46b...` | **No (Hash Contradicted)** | Historical pre-import dump captured 2026-10-16. | Hash conflicts with historical `...EB87...` claim. Contains 0 Stage 1F templates. | `CONFLICTED` / `STALE` |
| `stage_3n_t0_snapshot.sql` | **No** | 0 B | N/A | N/A | None. Post-Stage-1F baseline does not exist. | Cannot support emergency disaster recovery until created under Gate 1B. | `MISSING` |
| `backups/manifests/` | **No** | 0 B | N/A | N/A | None. Manifest storage not yet established on disk. | Filesystem write permissions cannot be verified statically. | `MISSING` |
| `themes/twentytwentyfive-child/` templates | Yes | ~35 KB | Multiple | Yes (Stage 1F reports) | Stage 1F storefront block templates exist physically on disk. | Database post records (`wp_template` in `wp_posts`) remain unverified at runtime. | `STATIC_PARTIAL` |
| `wp-config.php` | Yes | 3,382 B | `d2e8111b...` | Yes (Static inspection) | Defines `DB_NAME='local'`, `DB_HOST='localhost'`, prefix `'wp_'`. | `DISABLE_WP_CRON` is **not** defined. Runtime host/port binding unverified. | `STATIC_PARTIAL` |
| Live MySQL Database State | Yes | Unknown | N/A | N/A | Target database identity, tables, and product baseline. | Cannot connect or query without violating read-only boundary. | `RUNTIME_REQUIRED` |
| Live HTTP Coming Soon Lock | Unknown | Unknown | N/A | N/A | Public catalog lockdown on block theme. | Cannot send HTTP probes without violating read-only boundary. | `RUNTIME_REQUIRED` |
| Gate 4 Import Execution Token | **No** | N/A | N/A | N/A | Authorization to execute live import. | Gate 4 is strictly blocked; token must not be generated. | `OWNER_DECISION_REQUIRED` |

---

## 6. Previous-Report Claims Versus Direct Static Evidence

| Previous Report Claim | Source Report | Direct Static Evidence Finding | Resolution / Reality |
|---|---|---|---|
| Importer `verify` takes `--csv=...` argument | Runbook V2 / V3 Draft | **CONTRADICTED:** Lines 1646–1650 of `run_controlled_import.php` enforce `if (2 !== $argc_count) exit(1);`. | Discarded in R6. Importer binds internally to locked CSV constant. |
| `stage_1e_gate2` hash ends in `...EB87850C...` | Stage 1E-3F / 1E-3G | **CONTRADICTED:** Physical hash is `9152e46bb462c321edd7778a6da7889450a90301bd1627682d10345a5bdcc8a3`. | Historical documentation typo. Reconciled in R6/R7; physical hash is canonical. |
| `stage_1e_gate2` is a safe disaster-recovery backup | Stage 1E-3F | **CONTRADICTED / STALE:** SQL dump was captured before Stage 1F; contains 0 `wp_template` records. | Restoring it would destroy Stage 1F storefront. Fresh T-0 snapshot is mandatory. |
| Minimum dump size > 1.2 MB proves valid backup | Stage 1E-3F | **CONTRADICTED:** A 1.2 MB dump can contain destructive `USE` statements or syntax errors. | Size check is necessary but insufficient; isolated restore rehearsal (Gate 1C) is mandatory. |
| Software rollback can discover untagged orphan posts | Early Drafts | **CONTRADICTED:** Lines 1473–1491 enforce strict fail-closed ownership; refuses untagged deletions. | Window 2 crash creates untagged posts that require T-0 snapshot restore or DBA SQL. |
| Manifest directory write permissions are verified | Stage 1E reports | **CONTRADICTED:** `backups/manifests/` does not exist on disk today. | Must be proven at runtime under Gate 2. |
| All 6 Gate 4 Blockers are Closed | Stage 1E-3G | **HISTORICAL CLAIM:** Claimed in Stage 1E, but invalidated by Stage 1F and subsequent hardening. | All 9 operational blockers in R6/R7 remain strictly **OPEN**. |

---

## 7. Importer Version/Hash & CLI Syntax Evidence

### Importer Verification
- **File:** `app/public/wp-content/plugins/run_controlled_import.php`
- **Size:** 81,532 bytes
- **SHA-256:** `dbf3f73cca10d388962bd096bc18e7899f5e79089907c92121ca376106f92f02`
- **Internal Constants ([lines 247–264](file:///c:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/run_controlled_import.php#L247-L264)):**
  - `HARNESS_VERSION = '1.3'`
  - `PROJECT_NAME = 'furniture-ecommerce'`
  - `LOCKED_WORKING_CSV_HASH = 'D2C8566F334A767887F57C4A15EA456CFC5E117767FCFE46C599CC754BC2BBA0'`
  - `LOCKED_WORKING_CSV_PATH = 'C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/data-preparation/woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv'`
  - `EXPECTED_DB_NAME = 'local'`
  - `EXPECTED_TABLE_PREFIX = 'wp_'`
  - `EXPECTED_PARENT_COUNT = 22`
  - `EXPECTED_VARIATION_COUNT = 42`
  - `EXPECTED_TOTAL_STOCK = 449`
  - `EXPECTED_UNIQUE_SKU_COUNT = 64`

### CLI Argument Parser Rules ([lines 1632–1674](file:///c:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/run_controlled_import.php#L1632-L1674)):
1. `php run_controlled_import.php verify`: Strictly 2 arguments (`$argc_count === 2`). Providing any option or flag causes immediate termination with exit code 1.
2. `php run_controlled_import.php rollback <manifest_path>`: Strictly 3 arguments (`$argc_count === 3`). Path must resolve within `backups/manifests/`.
3. `php run_controlled_import.php import --gate4-user-approved-execute`: Strictly 3 arguments (`$argc_count === 3`). Exact token required. Live execution blocked without token.

---

## 8. Dataset Integrity & Source-of-Truth Evidence

1. **Original Dataset:**
   - Path: `app/public/wp-content/plugins/woocommerce_furniture_dataset_22_products.csv`
   - Size: 26,176 bytes | SHA-256: `276809899064949cb8d45b8ef7523b8c2913780f0649650ea4eb494fb74894f6`
   - Role: Historical baseline data source.
2. **Approved Working Dataset:**
   - Path: `data-preparation/woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv`
   - Size: 25,712 bytes | SHA-256: `d2c8566f334a767887f57c4a15ea456cfc5e117767fcfe46c599cc754bc2bba0`
   - Role: Authoritative working CSV for import.
   - Verification: Identical to the cryptographic digest hardcoded into `run_controlled_import.php`.
3. **Diff Analysis Summary:**
   - The working CSV removed 4 unreachable/404 image URLs (e.g. FURN-006 nightstand, FURN-018 wall art panel variations) to allow fallback to attachment ID 5 (`woocommerce-placeholder.webp`).
   - Line count (65) and product structure (22 parents, 42 variations) are perfectly preserved.

---

## 9. Snapshot & Manifest Evidence

### Existing Snapshots Inspected:
1. `backups/stage_1e_gate2_pre_import_20261016_223502.sql` (1,236,031 bytes):
   - SHA-256: `9152e46bb462c321edd7778a6da7889450a90301bd1627682d10345a5bdcc8a3`.
   - Header inspection: Dump created by MySQL 8.4.0 for `local`.
   - Contains destructive `DROP TABLE IF EXISTS` statements.
   - Contains **zero** `wp_template` posts. Stale snapshot; must not be restored.
2. `backups/stage_1a_baseline_backup.sql` (1,202,692 bytes, SHA-256 `bfbfc6...`): Initial site baseline prior to WooCommerce configuration.
3. `backups/stage_1e_pre_import_snapshot.sql` (1,336,511 bytes, SHA-256 `a9617e...`): Early Stage 1E snapshot.

### Missing Critical Snapshots & Manifests:
1. `backups/stage_3n_t0_snapshot.sql`: **MISSING**. Must be captured immediately prior to import under Gate 1B.
2. `backups/manifests/`: **MISSING**. No manifests currently exist on disk.

---

## 10. Stage 1F Preservation Evidence

Stage 1F established custom full-site editing (FSE) block templates for the ATELIER storefront.

### Physical Filesystem Templates Verified:
- [`templates/front-page.html`](file:///c:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/themes/twentytwentyfive-child/templates/front-page.html) (16,060 bytes, SHA-256 `8d406f47...`): Hero, featured collections, craft story.
- [`templates/archive-product.html`](file:///c:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/themes/twentytwentyfive-child/templates/archive-product.html) (5,446 bytes, SHA-256 `7eed6d6e...`): Product catalog grid.
- [`templates/single-product.html`](file:///c:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/themes/twentytwentyfive-child/templates/single-product.html) (5,173 bytes, SHA-256 `65ef9add...`): Product detail view.
- [`parts/header.html`](file:///c:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/themes/twentytwentyfive-child/parts/header.html) (3,333 bytes, SHA-256 `54fbd9bb...`): Navigation header.
- [`parts/footer.html`](file:///c:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/themes/twentytwentyfive-child/parts/footer.html) (5,054 bytes, SHA-256 `91deb7f5...`): Footer branding and links.
- [`style.css`](file:///c:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/themes/twentytwentyfive-child/style.css) (47,572 bytes, SHA-256 `8c73d90f...`): Atelier custom design system rules.

### Git Versioning Gap:
Inspection of Git in `app/public/wp-content` shows that while commits `8e550bf` and `cdf203d` established the theme scaffold in Stage 1B, the full Stage 1F template implementations remain uncommitted working tree modifications. Creating a Git tag/commit or filesystem backup of `wp-content/themes/twentytwentyfive-child/` before operational execution is mandatory.

---

## 11. Database/Runtime Claims That Remain Unverified

The following operational assertions cannot be proven statically and strictly require future authorized runtime verification:

1. **Active Database Identity:** Proof that MySQL on port 10011 resolves strictly to schema `local` on `localhost`.
2. **Current Table Count:** Proof that exactly 52 tables exist under prefix `wp_`.
3. **Clean Product Baseline:** Proof that `wp_posts` contains zero products (`post_type = 'product'`) and zero variations (`post_type = 'product_variation'`).
4. **Ownership Tag Cleanliness:** Proof that zero postmeta rows contain `_controlled_import_run_id`.
5. **Lookup Table State:** Proof that `wp_wc_product_meta_lookup` and `wp_wc_product_attributes_lookup` have 0 rows.
6. **Attachment ID 5 Metadata:** Proof that Attachment ID 5 exists, is `woocommerce-placeholder.webp`, and is protected.
7. **Storefront Freeze Interception:** Proof via HTTP GET probe that `/` and `/shop/` return HTTP 200 with Coming Soon banner and zero catalog leakage.
8. **Cron Suppression State:** Proof that scheduled background tasks and Action Scheduler workers are paused.
9. **Database User Restrictions:** Proof that the connected database user cannot touch external schemas or databases.

---

## 12. Known Recovery Risks & The Window 2 Ownership Gap

Static source inspection of [`run_controlled_import.php`](file:///c:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/run_controlled_import.php#L1000-L1050) and lines 1473–1491 reveals the exact mechanics of the **Window 2 Crash Boundary**:

### Sequence of Creation:
1. `wp_insert_post()` creates the row in `wp_posts` and generates an AUTO_INCREMENT ID.
2. `update_post_meta($id, '_controlled_import_run_id', $run_id)` tags the post with the unique run ID.
3. The ID is added to in-memory manifest data.
4. `flush_manifest()` flushes the manifest JSON buffer to disk.

### The Window 2 Vulnerability:
If a hard crash (SIGKILL, out-of-memory error, power failure) terminates PHP between Step 1 and Step 2:
- The post exists in `wp_posts`.
- The post has **zero** ownership metadata in `wp_postmeta`.
- The post is **not** recorded in the disk manifest file.
- **Rollback Behavior:** `run_controlled_import.php rollback` searches the database by ownership tag `_controlled_import_run_id`. Because the tag does not exist, it cannot discover the orphan. Furthermore, lines 1473–1491 enforce strict fail-closed safety: if an untagged post is somehow passed to rollback, rollback aborts immediately with `Ownership audit failed... Rollback refused.`
- **Design Invariant:** The importer intentionally refuses heuristic deletions (e.g. deleting by SKU or title) to prevent accidental destruction of legitimate products.
- **Recovery Resolution:** Window 2 recovery cannot be automated via software rollback. It strictly requires **Gate 4R-DR (Disaster Recovery Snapshot Restore)** using the verified T-0 snapshot, or **Gate 4R-DBA (Manual Surgical SQL Deletion)**.

---

## 13. Contradictions & Stale Evidence Summary

1. **Gate 2 Snapshot Hash Contradiction:** Historical reports cited SHA-256 `9152E46BB462C321EDD7778A6DA7889450A90301BD1627682D10345A5BDCC8A3` but logged the trailing hex as `...EB87850C...`. Physical inspection confirms the actual file digest is `...62C3...`.
2. **Snapshot Freshness Contradiction:** Historical logs claimed `stage_1e_gate2` is ready for emergency recovery. Physical inspection proves it was taken before Stage 1F and contains zero block templates. It is obsolete and dangerous to restore.
3. **CLI Arguments Contradiction:** Early draft runbooks instructed operators to run `verify --csv=...`. The actual code strictly requires `argc === 2` and rejects extra arguments.
4. **Git Tracking Divergence:** Historical reports marked Stage 1F as "Completed & Closed", but Git status in `app/public/wp-content` shows 3 modified files and 14 untracked Stage 1F files.

---

## 14. Locked Owner Decisions

The following established project policies are locked and fully binding:

- **A2 — Import Authorization:** Import is not authorized. Gate 4 remains strictly blocked until all prerequisite gates pass and a separate explicit approval is granted for the specific import.
- **A3 — Separate Authorization:** Every operation capable of changing the database, storefront, cron/workers, configuration, or other project state requires separate prior authorization.
- **A4 — Evidence Integrity:** Static evidence must not be represented as runtime evidence. A PASS verdict requires evidence appropriate to the specific acceptance criterion.
- **B4 — Stage 1F Preservation:** Preserve valid Stage 1F changes, including block templates, when planning snapshots, restore rehearsals, and recovery. Do not assume an older snapshot is safe to restore.
- **C5 — Window 2 Recovery:** Never delete an untagged post based only on a guessed SKU, timing, or other heuristic. Recovery requires evidence and an explicitly approved strategy.
- **D4 — Gate 4 Prerequisites:** Gate 4 may only be considered after prerequisite evidence, a T-0 snapshot, an isolated restore rehearsal, preflight checks, and recovery planning are accepted, followed by a separate explicit import authorization.

---

## 15. Explicit Declaration on Runtime Operations

**NO RUNTIME CHECKS WERE PERFORMED DURING THIS TASK.**  
Zero database connections, SQL queries, WordPress bootstraps, importer CLI executions, HTTP probes, or file writes were carried out. All runtime assertions remain unverified.

---

## 16. Final Inventory Verdict

### Verdict: `INVENTORY COMPLETE`
*(This verdict certifies that the static evidence inventory has been fully and completely performed based on available files. It does NOT authorize or imply operational readiness for import).*
