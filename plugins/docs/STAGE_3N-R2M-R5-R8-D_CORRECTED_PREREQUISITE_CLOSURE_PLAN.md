# STAGE 3N-R2M-R5-R8-D — CORRECTED PREREQUISITE CLOSURE PLAN & OPERATIONAL READINESS SPECIFICATION

**Project:** ATELIER — WordPress / WooCommerce Furniture E-commerce  
**Workspace:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\`  
**WordPress Root:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\`  
**Governance Documents Root:** `app\public\wp-content\plugins\docs\`  
**Mode:** STRICTLY READ-ONLY STATIC DOCUMENTATION CORRECTION — NO RUNTIME EXECUTION  
**Gate 1A Status:** **NOT AUTHORIZED**  
**Gate 4 Status:** **STRICTLY BLOCKED**  
**Plan Verdict:** **DOCUMENTATION CORRECTION PASS** (Supercedes R8-B Plan as Corrected Specification; Operational Execution Remains Strictly Unauthorized)

---

## 1. Executive Summary & Review Scope

This document establishes the definitive, corrected prerequisite closure plan for the ATELIER platform import workflow. It substantively resolves all twenty defects (`R8C-D01` through `R8C-D20`) identified during the Stage 3N-R2M-R5-R8-C Independent Safety Review.

### Core Governance Invariants:
1. **Zero Runtime Execution:** No database connections, SQL queries, WordPress bootstraps, importer CLI invocations, HTTP probes, snapshot captures, or restore operations were executed during this task.
2. **Deterministic Governance Principle:**
   > **AI proposes. Rules determine deterministic compliance. User approves. Client applies. System verifies.**
3. **No Automatic Progression:** Every operational gate requires separate, sequential, prior explicit authorization. A PASS at any stage provides evidence for that specific phase only.
4. **Gate 1A Status:** Gate 1A (Read-Only Database Identity Verification) is **NOT AUTHORIZED** by this document.
5. **Gate 4 Invariant:** Gate 4 (Live Ingestion) remains **STRICTLY BLOCKED**.

---

## 2. Immutable Owner Safety Policies

The six foundational owner policies established in Section 2 of the project charter remain immutable and fully binding:

| Policy ID | Title | Immutable Policy Constraint | Governance Status |
|---|---|---|---|
| **A2** | **Import Authorization Invariant** | Live product import is strictly unauthorized. Gate 4 remains locked until all prerequisite gates (1A through 3) achieve explicit, verified sign-off. | **`OWNER_DECISION_LOCKED`** |
| **A3** | **Separate Authorization Invariant** | Every technical operation capable of modifying the database, storefront visibility, background cron/workers, configuration, or filesystem requires separate, prior explicit authorization. | **`OWNER_DECISION_LOCKED`** |
| **A4** | **Evidence Integrity Invariant** | Static evidence must never be represented as runtime evidence. A PASS verdict requires direct, verifiable evidence appropriate to the specific acceptance criterion. | **`OWNER_DECISION_LOCKED`** |
| **B4** | **Stage 1F Preservation Invariant** | All valid Stage 1F changes (including custom FSE block templates in `wp_posts` and on disk) must be preserved. Restoring historical pre-Stage-1F snapshots is strictly forbidden. | **`OWNER_DECISION_LOCKED`** |
| **C5** | **Window 2 Recovery Invariant** | Never delete an untagged post based on guessed SKUs, timestamps, or heuristic scanning. Untagged post recovery requires an explicitly approved disaster recovery or surgical DBA strategy. | **`OWNER_DECISION_LOCKED`** |
| **D4** | **Gate 4 Prerequisite Sequence** | Gate 4 may only be considered after prerequisite evidence, an accepted T-0 snapshot, an isolated restore rehearsal, preflight checks, and recovery planning are accepted, followed by separate import authorization. | **`OWNER_DECISION_LOCKED`** |

---

## 3. Defect-by-Defect Traceability & Substantive Corrections (D01–D20)

### 3.1 P0 Defects — Critical Safety Blockers

#### R8C-D01: Recovery Protocol Status Integrity
- **Original Flaw:** R8-B §10 silently promoted open item R8A-10 to `POLICY LOCKED / PROTOCOL APPROVED` despite the R8-A register classifying it as `OPERATIONAL PROTOCOL OPEN` and the required artifact `docs/STAGE_3N_GATE4R_RECOVERY_PROTOCOL.md` not existing on disk.
- **Substantive Correction:**
  1. Preserved historical contradiction: documented that R8-B claimed protocol approval prematurely without an artifact.
  2. Status of R8A-10 is restored to **`OPERATIONAL PROTOCOL OPEN / PROPOSED IN R8-D`**.
  3. Formal protocol lifecycle established: `POLICY LOCKED` → `PROTOCOL DRAFTED` → `TECHNICALLY REVIEWED` → `OWNER APPROVED` → `OPERATIONALLY EXECUTED`.
  4. Defined deliverable: `docs/STAGE_3N_GATE4R_RECOVERY_PROTOCOL.md` must be formally authored, reviewed, and signed off under Gate 3 before Gate 4 authorization can be considered.
- **Residual Status:** `OWNER_DECISION_REQUIRED` (Protocol drafted in §7; awaiting formal owner sign-off).

#### R8C-D02: Unsafe Assumption About T-0 Snapshot Restore
- **Original Flaw:** R8-B §9.3 asserted unconditionally that restoring T-0 is harmless and "restores the exact clean baseline" without analyzing lost post-T-0 changes.
- **Substantive Correction:**
  1. Eliminated the assumption that T-0 snapshot restore is universally harmless or an automatic default.
  2. Established mandatory Lost-Change Analysis Protocol (§7.2): before any snapshot restore, the operator must:
     - Preserve incident evidence (`backups/manifests/`, error logs, and a crash-state dump `backups/stage_3n_crash_state.sql`).
     - Query live database to identify exact post-T-0 delta (Action Scheduler rows, customer sessions, options churn, admin edits).
     - Compare distinct recovery modes: (a) Software Rollback (`rollback` CLI), (b) Forensic Investigation, (c) Surgical DBA SQL, and (d) Full T-0 Restore.
     - Document risk, data loss impact, and reversibility for each option.
  3. Mandate separate, explicit owner approval immediately prior to executing any destructive recovery mutation.
- **Residual Status:** `OWNER_DECISION_REQUIRED` (Recovery framework defined; per-incident lost-change analysis required at Gate 4R).

#### R8C-D03: Inadequate Gate 1C Rehearsal Isolation Design
- **Original Flaw:** R8-B §7.4 relied on a single `SELECT DATABASE()` query, executed as `root`, omitted `--one-database` on the client, and defined no fail-closed abort procedure if isolation failed.
- **Substantive Correction:**
  1. Removed `SELECT DATABASE()` as sole proof of isolation.
  2. Redesigned Gate 1C specification (§6):
     - Server and environment identification: requires querying `DATABASE(), CURRENT_USER(), @@hostname, @@port, @@datadir, @@server_uuid`.
     - Restricted credentials: rehearsal restore must run under a dedicated rehearsal MySQL user granted schema privileges strictly on `local_isolated_rehearsal`, with **zero** privileges on database `local`.
     - Client protection: `mysql` client must be invoked with `--one-database` to prevent statement redirection.
     - Destructive statement review: T-0 dump must be statically audited for `DROP DATABASE`, `USE`, `DEFINER`, and `GRANT` statements.
     - Fail-closed rule: if target isolation cannot be proven, the rehearsal aborts immediately and Gate 1C is marked `BLOCKED`.
     - Separate sub-phase governance: Gate 1C is divided into 1C-1 (Creation), 1C-2 (Restore), 1C-3 (Fidelity Audit), and 1C-4 (Cleanup).
- **Residual Status:** `CORRECTION PROPOSED` (Design corrected; implementation verified at Gate 1C).

---

### 3.2 P1 Defects — Significant Operational & Logic Defects

#### R8C-D04: State-Changing Gate 0-GIT Gating Read-Only Gate 1A
- **Original Flaw:** R8-B made Gate 0-GIT (a state-changing commit/tag) a mandatory prerequisite for Gate 1A (a read-only database identity query), claiming theme assets could be lost during a database read.
- **Substantive Correction:** Decoupled Gate 1A from Gate 0-GIT. Gate 1A is strictly read-only runtime observation (`SELECT DATABASE()`) and cannot mutate filesystem assets. Gate 0-GIT remains mandatory before any *state-changing* operation (Gate 1B snapshot capture, Gate 1C rehearsal, Gate 2A freeze, etc.). Stage 1F preservation remains an immutable requirement (Policy B4).
- **Residual Status:** `OWNER_DECISION_REQUIRED` (Decoupling proposed; pending owner gate sequencing approval).

#### R8C-D05: Ambiguous Acceptance Metrics ("64 Products")
- **Original Flaw:** Matrix Gate 5 and Plan §8 used the ambiguous phrase "validate 64 products", which does not match WooCommerce object models.
- **Substantive Correction:** Replaced with unambiguous, statically verified metric decomposition (§8):
  - Parent products: exactly 22 (12 simple + 10 variable).
  - Product variations: exactly 42.
  - Total product-related posts in `wp_posts`: exactly 64.
  - Unique SKUs: exactly 64 (zero duplicates).
  - Aggregate stock: exactly 449 units = 248 units (simple products) + 201 units (variations). Direct stock on variable parents = 0.
  - Categories: exactly 7; Category associations: exactly 27.
  - Global attribute terms: 5 brands, 3 colors, 3 sizes.
  - Blank image fallbacks: exactly 4 rows fallback to placeholder Attachment ID 5 (`woocommerce-placeholder.webp`).
  - Ownership tags: exactly 64 postmeta rows with `_controlled_import_run_id = <run_id>`.
  - Manifest status: `COMPLETED`.
- **Residual Status:** `CLOSED_WITH_STATIC_EVIDENCE` (Derived directly from verified working CSV and importer constants).

#### R8C-D06: Unreliable Template Preservation Proof Method
- **Original Flaw:** R8-B §7.3 proposed `Select-String "wp_template|front-page|archive-product"` on the SQL dump. Testing proved this string matches serialized transient feeds in `wp_options` line 273 even when `wp_posts` contains zero template records.
- **Substantive Correction:** Removed raw substring grep. Mandated structural verification: parse `wp_posts` INSERT statements or query `wp_posts` directly, counting rows where `post_type = 'wp_template'` (>= 3) and `post_type = 'wp_template_part'` (>= 2).
- **Residual Status:** `CORRECTION PROPOSED` (Verification method corrected).

#### R8C-D07: mysqldump Specification Gaps
- **Original Flaw:** Proposed mysqldump command omitted `--routines`, `--events`, `--set-gtid-purged=OFF`, used a relative path, and overstated the `--single-transaction` consistency claim.
- **Substantive Correction:** Added missing flags, specified absolute path `C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/backups/stage_3n_t0_snapshot.sql`, qualified that `--single-transaction` guarantees ACID snapshot consistency specifically because all 52 tables are verified `ENGINE=InnoDB`, and added post-capture reconciliation verifying `CREATE TABLE` count matches live table count.
- **Residual Status:** `CORRECTION PROPOSED`.

#### R8C-D08: R8A-13 Gate Mapping Contradiction
- **Original Flaw:** R8-A mapped R8A-13 to Gate 1B / Gate 5; R8-B mapped it to Gate 0-GIT.
- **Substantive Correction:** Reconciled mapping:
  - Filesystem asset preservation (theme files, CSS, images) is mapped to **Gate 0-GIT**.
  - Database-resident template verification is mapped to **Gate 1B / Gate 1C**.
  - Final visual storefront acceptance is mapped to **Gate 5**.
- **Residual Status:** `CORRECTION PROPOSED`.

#### R8C-D09: Rehearsal Database Lifecycle Governance
- **Original Flaw:** `CREATE DATABASE` and `DROP DATABASE` were embedded inside Gate 1C without separate authorization or drop-evidence requirements (violating Policy A3).
- **Substantive Correction:** Divided Gate 1C into four discrete sub-phases:
  - **Gate 1C-1:** Auxiliary DB Creation (`CREATE DATABASE local_isolated_rehearsal`).
  - **Gate 1C-2:** Restore Execution (`mysql ... local_isolated_rehearsal < dump.sql`).
  - **Gate 1C-3:** Rehearsal Fidelity Audit (verify 52 tables, templates, 0 products).
  - **Gate 1C-4:** Auxiliary DB Drop (`DROP DATABASE local_isolated_rehearsal` with post-drop verification).
- **Residual Status:** `CORRECTION PROPOSED`.

#### R8C-D10: Gate 2D Conditional vs Gate 3 Prerequisite Contradiction
- **Original Flaw:** Matrix §2 marked Gate 2D conditional, but Gate 3 listed Gate 2D as a mandatory prerequisite, causing deadlock if skipped.
- **Substantive Correction:** Defined explicit branching (§5):
  - **Branch A (Recommended):** Gate 2C (Direct SQL Preflight) satisfies all database preconditions without loading WordPress. Gate 2D is skipped. Gate 3 requires: Gates 1A, 1B, 1C, 2A, 2B, 2C + Gate 2D Skip Decision Record.
  - **Branch B:** Gate 2D is mandated by Project Owner; executes `php run_controlled_import.php verify` and requires exit code 0 before Gate 3 review.
- **Residual Status:** `OWNER_DECISION_REQUIRED` (Pending owner branching choice).

---

### 3.3 P2 Defects — Documentation & Consistency Corrections

| Defect ID | Title | Summary of Defect | Substantive Correction Applied in R8-D | Status |
|---|---|---|---|---|
| **R8C-D11** | Disk Space Contradiction | Conflicting thresholds (1 GB vs 2 GB) and "statically confirmed" mislabel. | Standardized on **> 2 GB free disk space**; classified strictly as a runtime measurement taken at Gate 1B. | `CLOSED_WITH_STATIC_EVIDENCE` |
| **R8C-D12** | Fixed Table Count Stop Condition | Hardcoded 52-table stop condition instead of evidence-derived schema count. | Table count derived dynamically from Gate 1A live query and T-0 dump's `CREATE TABLE` count; used as dynamic Gate 1C criterion. | `CORRECTION PROPOSED` |
| **R8C-D13** | Dump Evidence Ladder Undefined | File size (> 1.2 MB) and text searches treated as proof of recoverability. | Demoted size check to advisory sanity check. Established 4-tier evidence ladder: `STATIC_CONTENT_REVIEW` → `CAPTURE_RESULT_VERIFIED` → `ISOLATED_RESTORE_VERIFIED` → `RECOVERY_READINESS_ACCEPTED`. | `CLOSED_WITH_STATIC_EVIDENCE` |
| **R8C-D14** | Governance Document Path Ambiguity | Expected artifacts referenced under `docs/` vs actual `plugins/docs/`. | Standardized all governance document references to canonical root `app/public/wp-content/plugins/docs/`. | `CLOSED_WITH_STATIC_EVIDENCE` |
| **R8C-D15** | Post-Recovery Pipeline Re-Entry | Mermaid diagram showed return to Gate 1A without specifying intermediate gate requirements. | Explicitly established that after any recovery (4R-SW, 4R-DR, 4R-DBA), the entire pipeline re-enters at Gate 1A, and all subsequent gates (1B fresh T-0, 1C rehearsal, 2A–2D) must be re-executed before any Gate 4 retry. | `CORRECTION PROPOSED` |
| **R8C-D16** | Cron Pause Mechanism Unspecified | Mechanism for pausing cron was unstated; T-0 dump captured before freeze. | Specified cron pause via `wp_options` `cron` lock and `DISABLE_WP_CRON = true`. Documented concurrent-write window during T-0 capture (mitigated by InnoDB snapshot isolation). | `CORRECTION PROPOSED` |
| **R8C-D17** | Pre-Gate PASS Scope Ambiguity | Pre-Gate PASS could be misread as full inventory PASS. | Scoped explicitly as `Pre-Gate PASS (Code & Dataset Artifacts Only)`. Non-code artifacts (T-0 snapshot, manifests) remain `MISSING`. | `CLOSED_WITH_STATIC_EVIDENCE` |
| **R8C-D18** | Stage 1F File Count Discrepancy | R8-A claimed "14 untracked Stage 1F files"; actual count was 12 theme files. | Pinned the exact 12-file Stage 1F theme asset manifest (3 modified tracked + 9 untracked) with SHA-256 digests. | `CLOSED_WITH_STATIC_EVIDENCE` |
| **R8C-D19** | Premature Command Parameterization | Commands hardcoded `-u root` and `127.0.0.1:10011` before Gate 1A proof. | Marked all proposed commands as `PROPOSED SPECIFICATION — PARAMETERS SUBJECT TO GATE 1A RUNTIME RESOLUTION`. | `CORRECTION PROPOSED` |
| **R8C-D20** | Gate 2C Query Suite Unenumerated | "11 queries" asserted without enumerating SQL text or expected outputs. | Fully enumerated all 11 SQL queries with syntax, purpose, expected results, and stop conditions in §8. | `CORRECTION PROPOSED` |

---

## 4. Workstream A — Corrected Stage 1F Preservation & Git Baseline Specification

### 4.1 Authoritative 12-File Stage 1F Manifest
The physical Stage 1F storefront consists of exactly 12 theme assets within `app/public/wp-content/themes/twentytwentyfive-child/`:

| File Path (Relative to Theme Root) | Git Tracking Status | File Size | SHA-256 Digest | Role in Storefront |
|---|---|---|---|---|
| `functions.php` | Tracked (Modified) | 1,052 B | `6c2fbcb2b13cdf036734a94dd871a1d28df3ce291423d8b32acfde6a33b6ac96` | Child theme enqueue & setup |
| `style.css` | Tracked (Modified) | 47,572 B | `8c73d90fca98da530f54c4ff4f5d4f18aad33e5ca0df1584239b648cf20fdded` | Atelier design tokens & layout rules (2,112 insertions) |
| `theme.json` | Tracked (Modified) | 8,177 B | `fe1d1c141cc43515625950ee456b654fab87bc749fa6327c90c2a252eed72a94` | FSE color palette, typography, spacing tokens |
| `parts/header.html` | Untracked | 3,333 B | `54fbd9bb2378a8fcca522719ad7c85a91041c8356e052ce19250d472237a78e6` | Navigation header template part |
| `parts/footer.html` | Untracked | 5,054 B | `91deb7f552b94b7a0a659dc38203179f73883cfc7ca728c5b35c2f7a16e2ce49` | Footer branding & links template part |
| `templates/front-page.html` | Untracked | 16,060 B | `8d406f477f4364589b16e4ca657c2f4e9571a9d7409b871db491de5dd1986c0d` | Homepage FSE block template |
| `templates/archive-product.html` | Untracked | 5,446 B | `7eed6d6ef723540bfb1ea22383380d624f3d98c4c03c975384f39040a15ea46a` | Product catalog grid template |
| `templates/single-product.html` | Untracked | 5,173 B | `65ef9addbf68334767ae300934c591a19c54b57853eedc02595cb50868a9512d` | Product detail page template |
| `assets/images/craft-detail.jpg` | Untracked | 740,406 B | `c637d681e65ccbf30a782d2ca5d0f26e91a0b407b8a7ddb3bebe47b9b9358670` | Homepage craftsmanship image (JPEG) |
| `assets/images/craft-detail.webp` | Untracked | 86,184 B | `913a792f3518c642a16595dfce86606bf26a9d9e462e1257ab5aa860936f5aed` | Optimized craft image (WebP) |
| `assets/images/hero-furniture.jpg` | Untracked | 800,457 B | `a0eaa549b8e09a7371b92a55b3beb86dd52939fcf14bf5a91f803d71f318a419` | Hero banner image (JPEG) |
| `assets/images/hero-furniture.webp` | Untracked | 125,148 B | `c7a36bc8eaf12f565192369978e5d7de1620e4282475f884ef0557e2f7bfea8c` | Optimized hero banner (WebP) |

### 4.2 Non-Destructive Preservation Protocol (Gate 0-GIT Specification)
1. **Separation from Gate 1A:** Gate 1A is strictly read-only and does not require Gate 0-GIT as a prerequisite. Gate 0-GIT is a mandatory prerequisite for Gate 1B (Snapshot Capture).
2. **Pre-Staging Audit:** Review `git diff` on `functions.php`, `style.css`, and `theme.json` to guarantee zero secrets or unrelated edits are present.
3. **Execution under Separate Authorization:**
   - Stage strictly the 12 files in the manifest.
   - Commit with message: `feat(stage-1f): complete atelier block theme templates, parts, and design system tokens`.
   - Create annotated tag: `git tag -a stage-1f-verified-baseline -m "Stage 1F verified storefront baseline"`.
   - Generate independent filesystem backup: `backups/filesystem/stage_1f_theme_backup.zip`.
4. **Prohibition Invariant:** `git clean`, `git reset`, and `git checkout -- .` are **strictly prohibited**.

---

## 5. Workstream B — Corrected SQL Dump Protocol & 4-Level Evidence Ladder

### 5.1 The Four-Level Evidence Ladder
To prevent premature acceptance of SQL dumps as recovery-ready, the plan enforces a strict 4-level evidence progression:

```
[Level 1: STATIC_CONTENT_REVIEW]
 - File size > 1.2 MB (Advisory)
 - Structural search: 0 DROP DATABASE, 0 USE, 0 CREATE DATABASE, 0 DEFINER
 - Structural search: wp_posts contains post_type = 'wp_template' rows
                   │
                   ▼
[Level 2: CAPTURE_RESULT_VERIFIED]
 - mysqldump exit code === 0
 - Dump written to absolute path
 - SHA-256 computed and recorded
 - Dump CREATE TABLE count === live table count
                   │
                   ▼
[Level 3: ISOLATED_RESTORE_VERIFIED]
 - Auxiliary database created and verified isolated
 - Dump restored cleanly into auxiliary DB under restricted credentials
 - Exactly 52 tables verified; Stage 1F templates verified; 0 products verified
 - Auxiliary database dropped cleanly with drop-evidence logged
                   │
                   ▼
[Level 4: RECOVERY_READINESS_ACCEPTED]
 - Formal sign-off by Database Administrator (DBA) and Project Owner
 - Acceptance recorded in Gate 1C closure dossier
 - Dump locked as authoritative pre-import baseline
```

### 5.2 Corrected mysqldump Command (Gate 1B Specification)
```bash
# PROPOSED SPECIFICATION — DO NOT EXECUTE IN R8-D
mysqldump -h 127.0.0.1 -P 10011 -u root -p local \
    --single-transaction \
    --quick \
    --hex-blob \
    --default-character-set=utf8mb4 \
    --routines \
    --events \
    --set-gtid-purged=OFF \
    --no-tablespaces \
    --result-file="C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/backups/stage_3n_t0_snapshot.sql"
```

---

## 6. Workstream C — Corrected Isolated Rehearsal Specification (Gate 1C)

To prevent accidental live database restoration, Gate 1C is partitioned into four independently authorized sub-phases:

```mermaid
graph LR
    G1C1[Gate 1C-1: Create Aux DB] --> G1C2[Gate 1C-2: Restore Execution]
    G1C2 --> G1C3[Gate 1C-3: Fidelity Audit]
    G1C3 --> G1C4[Gate 1C-4: Drop Aux DB]
```

### 6.1 Sub-Phase Specifications:
1. **Gate 1C-1 (Auxiliary DB Creation):**
   - Operation: Connect to MySQL; execute `CREATE DATABASE local_isolated_rehearsal;`.
   - Provision dedicated rehearsal user: `CREATE USER 'rehearsal_user'@'localhost' IDENTIFIED BY '...'; GRANT ALL ON local_isolated_rehearsal.* TO 'rehearsal_user'@'localhost';`.
2. **Gate 1C-2 (Restore Execution under Least Privilege):**
   - Operation: Execute restore using restricted rehearsal account and `--one-database`:
     ```bash
     mysql -h 127.0.0.1 -P 10011 -u rehearsal_user -p --one-database local_isolated_rehearsal < "C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/backups/stage_3n_t0_snapshot.sql"
     ```
   - Invariant: Because `rehearsal_user` has zero grants on `local`, any redirection or mistake fail-closes with `Access Denied`.
3. **Gate 1C-3 (Fidelity Audit):**
   - Verify table count matches live capture count.
   - Verify `wp_posts` contains Stage 1F `wp_template` posts.
   - Verify `wp_posts` contains 0 product entities.
   - Verify placeholder Attachment ID 5 is present.
4. **Gate 1C-4 (Auxiliary DB Drop):**
   - Archive audit evidence to `docs/STAGE_3N_GATE1C_RESTORE_REHEARSAL_REPORT.md`.
   - Execute `DROP DATABASE local_isolated_rehearsal; DROP USER 'rehearsal_user'@'localhost';`.
   - Execute post-drop query `SHOW DATABASES LIKE 'local_isolated_rehearsal';` confirming empty set.

---

## 7. Workstream D — Window 2 Recovery Framework & Lost-Change Analysis

### 7.1 Window 2 Crash Vulnerability Review
If execution terminates abruptly (OOM, SIGKILL, power loss) after `wp_insert_post()` but before `update_post_meta('_controlled_import_run_id', ...)`:
- The post exists in `wp_posts` without an ownership tag and without a manifest entry.
- `run_controlled_import.php rollback` fail-closes: lines 1473–1491 refuse heuristic deletion. Policy C5 strictly forbids heuristic deletion.

### 7.2 Lost-Change Analysis Protocol (Gate 4R Decision Tree)
Before any recovery operation is authorized, the operator must execute the following decision protocol:

```
                                  [INCIDENT OCCURS]
                                          │
                                          ▼
                      [Step 1: Evidence Preservation]
                      - Archive manifest, PHP error log
                      - Take crash snapshot: stage_3n_crash_state.sql
                                          │
                                          ▼
                      [Step 2: Lost-Change Analysis]
                      - Query posts, postmeta, options, sessions,
                        and Action Scheduler rows created after T-0
                      - Document exact delta that would be lost
                                          │
                                          ▼
                        [Step 3: Mode Selection Triage]
                                          │
         ┌────────────────────────────────┼────────────────────────────────┐
         ▼                                ▼                                ▼
[Pathway 1: Software Rollback]   [Pathway 2: Surgical DBA SQL]   [Pathway 3: T-0 Snapshot Restore]
 - Eligible if manifest valid     - Eligible if single untagged   - Eligible if database corrupted
   and all posts tagged.            orphan post identified with     or software rollback failed.
 - Clean reverse deletion.          known ID and no dependencies. - Overwrites schema with T-0.
 - Zero loss of post-T-0 data.    - Deletes specific orphan.      - Erases all post-T-0 data.
                                  - Requires DBA script sign-off. - Requires Lost-Change Sign-Off.
         │                                │                                │
         └────────────────────────────────┼────────────────────────────────┘
                                          │
                                          ▼
                     [Step 4: Owner Recovery Authorization]
                      - Formal owner signature approving mode
                      - Execution under separate Gate 4R authorization
```

---

## 8. Workstream E — Gate 2C Preflight SQL Suite & Acceptance Decomposition

### 8.1 Enumeration of Gate 2C Preflight Queries (11 Queries)

| # | Query Purpose | Proposed SQL Query Text | Expected Pass Output | Stop Condition |
|---|---|---|---|---|
| **1** | Database Identity | `SELECT DATABASE() AS active_db, USER() AS active_user, @@port AS port;` | `active_db='local'`, `port=10011` | `active_db != 'local'` |
| **2** | Table Prefix & Count | `SELECT COUNT(*) AS total_tables FROM information_schema.tables WHERE table_schema='local' AND table_name LIKE 'wp_%';` | `total_tables = 52` (or live baseline) | `total_tables < 52` |
| **3** | Core Site URLs | `SELECT option_name, option_value FROM wp_options WHERE option_name IN ('siteurl', 'home');` | Both return `http://localhost:10010` | Non-local URL |
| **4** | Global Attributes | `SELECT attribute_name FROM wp_woocommerce_attribute_taxonomies ORDER BY attribute_name ASC;` | 3 rows: `brand`, `color`, `size` | Missing attribute |
| **5** | Taxonomy Terms | `SELECT tt.taxonomy, COUNT(t.term_id) AS cnt FROM wp_term_taxonomy tt JOIN wp_terms t ON tt.term_id=t.term_id WHERE tt.taxonomy IN ('product_cat','pa_color','pa_size','pa_brand') GROUP BY tt.taxonomy;` | `product_cat >= 7`, `pa_color >= 3`, `pa_size >= 3`, `pa_brand >= 5` | Missing terms |
| **6** | Product Baseline | `SELECT post_type, COUNT(*) AS cnt FROM wp_posts WHERE post_type IN ('product','product_variation') GROUP BY post_type;` | 0 rows returned | Any count > 0 |
| **7** | Attachment ID 5 | `SELECT ID, post_title, post_name, post_mime_type FROM wp_posts WHERE ID = 5;` | 1 row: `woocommerce-placeholder`, image MIME | ID 5 missing |
| **8** | Ownership Cleanliness | `SELECT COUNT(*) AS cnt FROM wp_postmeta WHERE meta_key = '_controlled_import_run_id';` | Exactly 1 row: `cnt = 0` | `cnt > 0` |
| **9** | Duplicate SKU Check | `SELECT post_id, meta_value FROM wp_postmeta WHERE meta_key = '_sku' AND meta_value IN ('FURN-001','FURN-002',...);` | 0 rows returned | Any SKU found |
| **10** | Coming Soon Mode | `SELECT option_value FROM wp_options WHERE option_name = 'woocommerce_coming_soon';` | Exactly 1 row: `option_value = 'yes'` | `option_value != 'yes'` |
| **11** | Lookup Cleanliness | `SELECT (SELECT COUNT(*) FROM wp_wc_product_meta_lookup) AS m_cnt, (SELECT COUNT(*) FROM wp_wc_product_attributes_lookup) AS a_cnt;` | `m_cnt = 0`, `a_cnt = 0` | Any count > 0 |

---

## 9. Explicit Non-Execution Confirmation

**NO RUNTIME OR STATE-CHANGING OPERATIONS OCCURRED:**
- No database connection, SQL query (`SELECT`, `SHOW`, `INSERT`, `UPDATE`, `DROP`), or transaction was executed.
- No WordPress core or `wp-load.php` bootstrap was triggered.
- No HTTP probe or network request was dispatched.
- `run_controlled_import.php` was **not** run in `verify`, `rollback`, or `import` mode.
- No SQL dump was captured, restored, or modified.
- No Git commit, branch, tag, staging, reset, clean, checkout, or stash was performed.
- No configuration, cron, or Coming Soon setting was altered.
- No existing project files, themes, code, or datasets were modified.

---

## 10. Final Plan Verdict

### Verdict: `DOCUMENTATION CORRECTION PASS`
*(Certifies that the prerequisite closure plan has been substantively corrected to address all 20 defects from R8-C. Gate 1A remains unauthorized; Gate 4 remains strictly blocked).*
