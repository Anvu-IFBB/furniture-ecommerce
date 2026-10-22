# STAGE 3N-R2M-R5-R6 — OPERATIONAL PREFLIGHT BLOCKER MATRIX

**Project:** ATELIER — WordPress / WooCommerce Furniture E-commerce  
**Workspace:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\`  
**Scope:** Definitive Blocker Assessment & Operational Gate Readiness  
**Audit Mode:** STRICTLY READ-ONLY STATIC AUDIT  
**Status:** **PREPARATION PASS — OPERATIONAL EXECUTION BLOCKED**  
**Gate Invariant:** GATE 4 REMAINS STRICTLY BLOCKED

---

## 1. Executive Summary & Evaluation Standards

This document establishes the definitive operational blocker matrix for the ATELIER WooCommerce furniture import workflow. It integrates findings from across Stage 3N-R2M-R5 (R5-R1 through R5-R5) and directly contrasts physical disk evidence against historical documentation claims.

### Evidentiary Classifications Enforced:
- **STATIC VERIFIED:** Directly confirmed by inspecting current physical files or source code on disk during this session.
- **HISTORICAL REPORTED:** Claimed in an earlier report or log, but not independently confirmed on the active runtime environment.
- **NOT VERIFIED:** Requires live runtime access (MySQL connection, HTTP probe, or live execution) that is prohibited in this static audit.
- **CONTRADICTED:** Conflicts with another observed physical artifact or authoritative source.
- **NOT APPLICABLE:** Where justified by scope or architectural boundaries.

---

## 2. Operational Blocker Matrix

| Blocker ID | Mô tả (Description) | Bằng chứng hiện có (Existing Evidence) | Phân loại (Classification) | Rủi ro (Risk) | Điều kiện đóng blocker (Closure Condition) | Có cần phê duyệt riêng không (Requires Separate Approval?) |
|---|---|---|---|---|---|---|
| **BLK-01** | **Gate 2 Snapshot Hash Discrepancy:** Historical reports recorded two conflicting hashes (`...EB87...` vs `...62C3...`) for `stage_1e_gate2_pre_import_20261016_223502.sql`. | File on disk is 1,236,031 bytes; computed SHA-256 is `9152E46BB462C321EDD7778A6DA7889450A90301BD1627682D10345A5BDCC8A3`. Conflicting hash shares first 10 hex chars. | **CONTRADICTED (Historical) / STATIC VERIFIED (Physical)** | Use of incorrect hash leads to false claims of corrupted or tampered backup files. | Reconciled statically: physical file hash documented as canonical `...62C3...`. Forbid using this snapshot for disaster recovery. | **No** (Resolved statically in Pre-Gate) |
| **BLK-02** | **Stale Pre-Stage-1F Snapshot (T-0 Missing):** `stage_1e_gate2` was captured on 2026-10-16, prior to Stage 1F storefront development. It contains only 12 posts and zero custom block templates. | Static dump inspection confirms zero `wp_template` posts. Physical file `stage_3n_t0_snapshot.sql` does not exist yet. | **STATIC VERIFIED (Dumps) / NOT VERIFIED (T-0)** | Restoring `stage_1e_gate2` in an emergency wipes all Stage 1F custom templates, destroying the storefront. | Capture fresh baseline snapshot `stage_3n_t0_snapshot.sql` capturing all 52 post-Stage-1F tables immediately prior to import. | **Yes — Gate 1 Approval** (DBA Authorization) |
| **BLK-03** | **Isolated Restoration of T-0 Undemonstrated:** The structural plausibility and restoration fidelity of the new T-0 dump have not been proven in an isolated auxiliary database. | Gate 3I report proved historical restore of `stage_1e_gate2`, but T-0 snapshot has not yet been created or restored. | **NOT VERIFIED** | Corrupted, truncated, or syntactically invalid dump fails during emergency recovery, resulting in unrecoverable database state. | Restore `stage_3n_t0_snapshot.sql` into isolated auxiliary database (`local_isolated_rehearsal`); verify 52 tables and Stage 1F templates. | **Yes — Gate 1 Approval** (QA Lead & DBA) |
| **BLK-04** | **Importer & Test Suite Code Integrity:** Current physical source code on disk must match the exact audited hashes from R2M-R4. | `run_controlled_import.php` (81,532 bytes, SHA-256 `DBF3F73C...`); `test_r2m_isolated_suite.php` (52,906 bytes, SHA-256 `C8843ED5...`). | **STATIC VERIFIED** | Unauthorized modifications, regressions, or syntax errors introduced since R2M-R4 audit. | Physical file hashes on disk match expected digests 100%. | **No** (Resolved statically in Pre-Gate) |
| **BLK-05** | **Live Database Identity & Product Baseline Unverified:** Current live database host, port (10011), database name (`local`), prefix (`wp_`), and product count (0) are not confirmed on active database. | `wp-config.php` defines DB_NAME `local` and prefix `wp_`. Static SQL dumps contain 0 products. Live database not queried. | **STATIC VERIFIED (Files) / NOT VERIFIED (Live)** | Importer connects to wrong database, wrong prefix, or finds existing products, causing primary key or SKU collisions. | Execute `php run_controlled_import.php verify` under Gate 2; verify exit code 0 and all 9 preflight checks pass. | **Yes — Gate 2 Approval** (Technical Lead) |
| **BLK-06** | **Live Manifest Directory & Writable Permissions Unverified:** `backups/manifests/` directory does not currently exist on disk and filesystem write permissions are unverified at runtime. | Filesystem audit confirms `backups/manifests/` does not exist. `Controlled_Import_Path_Resolver` contains directory creation logic. | **STATIC VERIFIED (Disk) / NOT VERIFIED (Runtime)** | Importer fails at runtime when writing manifest, triggering error state or unrecorded import run. | Importer preflight check 8 creates directory with 0750 permissions and proves write/read capability. | **Yes — Gate 2 Approval** (Technical Lead) |
| **BLK-07** | **Window 2 Crash Boundary & Untagged Orphans:** A crash between `wp_insert_post()` and `update_post_meta()` creates untagged posts that software rollback cannot identify or delete. | Static source code review of lines 396–430 confirms fail-closed deletion requires valid manifest AND matching meta tag. | **STATIC VERIFIED** | Untagged orphaned products remain in database after a hard process crash, polluting the catalog. | Enforce storefront freeze during import; designate T-0 snapshot restore as primary recovery, with manual DBA cleanup as fallback. | **Yes — Gate 3 Approval** (System Architect) |
| **BLK-08** | **Storefront Freeze & Background Processing Suspension:** Public shoppers or background cron events could insert orders, comments, or posts during import execution. | Static dump inspection confirms `woocommerce_coming_soon` exists in `wp_options`. Cron suppression verified in CLI code. | **STATIC VERIFIED (Code) / NOT VERIFIED (Live)** | Concurrent database writes during import produce inconsistent post IDs or are overwritten by emergency snapshot restore. | Enable Coming Soon mode; pause WordPress cron; invalidate active user sessions before preflight. | **Yes — Gate 2 Approval** (Operations Engineer) |
| **BLK-09** | **Effective Storefront Lock Verification (HTTP Probe):** Coming Soon mode must actually intercept unauthenticated web traffic on block themes. | Stage 1E reports indicate Coming Soon was previously active, but block theme routing behavior has not been tested via live HTTP probe. | **NOT VERIFIED** | Unauthenticated visitors bypass Coming Soon and browse unfinished catalog during import. | Automated cURL probe to `http://localhost:10010/` and `/shop/` confirms HTTP 200 with Coming Soon banner and zero products leaked. | **Yes — Gate 2 Approval** (QA Engineer) |
| **BLK-10** | **Restricted Database Privilege Verification:** Importer runs as MySQL `root` in LocalWP; operations must be strictly bounded to database `local`. | `wp-config.php` specifies DB_USER `root`. Importer lines 219–227 check `SELECT DATABASE() === 'local'`. | **STATIC VERIFIED (Code) / NOT VERIFIED (Live)** | Importer accidentally mutates system tables, auxiliary test databases, or external schemas. | Execute database verification query confirming connection is strictly bound to `local` on port 10011. | **Yes — Gate 1 Approval** (Database Administrator) |
| **BLK-11** | **Post-Import Acceptance & Rollback Authorization Gate:** Formal boundaries must separate import acceptance, emergency software rollback, and disaster recovery. | Importer contains automated rollback in `try-catch`, but manual Gate 4R invocation protocol must be formally established. | **STATIC VERIFIED** | Indecision or premature unfreezing after import failure exposes corrupted catalog to visitors. | Formal acceptance criteria (64 products, 449 stock, 64 SKUs, 7 categories); mandatory Gate 4R rollback if any metric fails. | **Yes — Gates 4, 4R, 5** (Project Owner & QA Lead) |

---

## 3. Discarded Assumptions & Lingering Unknowns

### A. Assumptions Discarded Based on Static Evidence
1. **Discarded: `--csv` CLI argument in runbook:**  
   *Early assumption:* Operators must run `php run_controlled_import.php verify --csv=...`.  
   *Static reality:* The CLI argument parser in `run_controlled_import.php` strictly takes 2 arguments for `verify` (`argc === 2`) and binds internally to `LOCKED_WORKING_CSV_PATH`. Supplying `--csv` causes immediate CLI rejection with exit code 1.
2. **Discarded: Reusing `stage_1e_gate2` as the Pre-Import Baseline:**  
   *Early assumption:* `stage_1e_gate2_pre_import_20261016_223502.sql` is a valid pre-import snapshot.  
   *Static reality:* This dump predates Stage 1F. Restoring it destroys all Stage 1F storefront block templates. A fresh post-Stage-1F snapshot (T-0) is mandatory.
3. **Discarded: Minimum File Size as Sole Proof of Valid Dump:**  
   *Early assumption:* Any SQL dump > 1.2 MB is valid.  
   *Static reality:* A dump could exceed 1.2 MB but contain syntax errors, missing tables, or embedded `USE` statements that target the wrong database. Complete structural inspection and isolated restoration rehearsal are required.
4. **Discarded: Heuristic Cleanup in Software Rollback:**  
   *Early assumption:* Software rollback should scan by SKU to find untagged orphans.  
   *Static reality:* Heuristic deletion violates fail-closed security and could delete legitimate pre-existing products. Untagged orphans must be recovered via snapshot restore or manual DBA script.

### B. Lingering Unknowns (Strictly NOT VERIFIED)
1. **Live Database Row Counts:** Live table rows in `wp_posts`, `wp_postmeta`, and lookup tables cannot be verified without connecting to MySQL on port 10011.
2. **Live Attachment ID 5 Metadata:** While SQL dumps confirm ID 5 is `woocommerce-placeholder.webp`, its active presence in the live WordPress instance is unverified.
3. **Block Theme Coming Soon Interception:** The exact HTML output of the active block theme when `woocommerce_coming_soon = 'yes'` must be confirmed via live cURL probe in Phase P-05.

---

## 4. Approval Gate Mapping & Closure Roadmap

```mermaid
graph TD
    subgraph Pre-Gate Static Audit: COMPLETED
        BLK01[BLK-01: Snapshot Hash - CLOSED]
        BLK04[BLK-04: Importer & Test Hashes - CLOSED]
    end

    subgraph Gate 1: Database Isolation & T-0 Baseline
        BLK02[BLK-02: Fresh T-0 Snapshot - OPEN]
        BLK03[BLK-03: T-0 Isolated Restore - OPEN]
        BLK10[BLK-10: DB Grants & Isolation - OPEN]
    end

    subgraph Gate 2: Storefront Freeze & Live Preflight
        BLK05[BLK-05: Live DB Identity & Preflight - OPEN]
        BLK06[BLK-06: Manifest Dir Write Perms - OPEN]
        BLK08[BLK-08: Storefront Freeze & Cron Pause - OPEN]
        BLK09[BLK-09: HTTP Coming Soon Probe - OPEN]
    end

    subgraph Gate 3: Execution Authorization
        BLK07[BLK-07: Window 2 Recovery Protocol - OPEN]
    end

    subgraph Gate 4 / 4R / 5: Execution, Acceptance & Release
        BLK11[BLK-11: Post-Import Acceptance - OPEN]
    end

    BLK01 & BLK04 --> Gate1[Gate 1 Approval]
    Gate1 --> BLK02 & BLK03 & BLK10
    BLK02 & BLK03 & BLK10 --> Gate2[Gate 2 Approval]
    Gate2 --> BLK05 & BLK06 & BLK08 & BLK09
    BLK05 & BLK06 & BLK08 & BLK09 --> Gate3[Gate 3 Approval]
    Gate3 --> BLK07
    BLK07 --> Gate4[Gate 4 Authorization]
    Gate4 --> BLK11
```

---

## 5. Mandatory Operational Invariant

**ALL OPERATIONAL BLOCKERS (BLK-02, BLK-03, BLK-05, BLK-06, BLK-07, BLK-08, BLK-09, BLK-10, BLK-11) REMAIN OPEN.**  
**NO BLOCKER MAY BE CLOSED BASED ON HISTORICAL CLAIMS OR STATIC INFERENCE.**  
**GATE 4 REMAINS STRICTLY BLOCKED. THIS DOCUMENT AUTHORIZES ZERO RUNTIME MUTATIONS.**
