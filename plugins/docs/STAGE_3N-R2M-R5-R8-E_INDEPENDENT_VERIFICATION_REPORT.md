# STAGE 3N-R2M-R5-R8-E: INDEPENDENT VERIFICATION OF R8-D CORRECTIONS & SAFETY GATE READINESS

**Project:** ATELIER — WordPress / WooCommerce Furniture E-commerce  
**Workspace:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\`  
**WordPress Root:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\`  
**Canonical Governance Root:** `app/public/wp-content/plugins/docs/`  
**Document Path:** `app/public/wp-content/plugins/docs/STAGE_3N-R2M-R5-R8-E_INDEPENDENT_VERIFICATION_REPORT.md`  
**Mode:** STRICTLY STATIC DOCUMENT INSPECTION & DOCUMENTATION ONLY  
**Gate 1A Status:** **NOT AUTHORIZED**  
**Gate 4 Status:** **STRICTLY BLOCKED**  
**Verification Date:** 2026-10-21  

---

## 1. Executive Summary & Verdict

### 1.1 Executive Verdict
**VERDICT: `PASS_WITH_OPEN_DEFECTS` (Documentation Design Corrected / Operational Gate Prerequisites Blocked)**

The independent audit of the Stage 3N-R2M-R5-R8-D deliverables confirms that the 20 defects identified in R8-C (`R8C-D01` through `R8C-D20`) have been substantively addressed in the planning specifications without cosmetic evasion or hand-waving. 

Crucially, **Documentation Correctness does not equal Operational Authorization**. While the technical designs, dependency trees, isolation safeguards, and metric definitions have been corrected to full rigor:
- **0 Operational Gates are Authorized:** Gate 1A remains **NOT AUTHORIZED**; Gate 4 remains **STRICTLY BLOCKED**.
- **4 Owner Decisions Remain Open:** R8C-D01 (Recovery Protocol Ratification), R8C-D02 (Window 2 Recovery Loss Framework Adoption), R8C-D04 (Gate 1A/0-GIT Decoupling Approval), and R8C-D10 (Gate 2D Branch A vs Branch B Selection).
- **2 Items Require Runtime Evidence:** R8C-D11 (Runtime Disk Free Space > 2.0 GB) and R8C-D12 (Dynamic Live Database Baseline Table Count).
- **Zero Runtime Mutations Occurred:** No SQL queries, database connections, WP-CLI executions, WordPress bootstraps, file modifications, git commits, or HTTP requests were executed during this audit.

### 1.2 Separation of Governance Dimensions

| Governance Dimension | Status | Meaning & Boundary |
|:---|:---:|:---|
| **Documentation Correctness** | **`VERIFIED PASS`** | The text of the Plan and Matrix resolves all 20 R8-C defect root causes. |
| **Static Evidence Verified** | **`VERIFIED PASS`** | Physical files, checksums, CSV metrics, and theme manifests match static facts. |
| **Owner Authorization** | **`PENDING`** | The 4 formal owner policy decisions have not yet been signed by the Owner. |
| **Runtime Evidence Required** | **`UNVERIFIED / BLOCKED`** | Live DB state, server identity, socket, disk space, and table counts await runtime gates. |
| **Operational Readiness** | **`NOT AUTHORIZED`** | Gate 1A is NOT AUTHORIZED; Gate 4 is STRICTLY BLOCKED; all state changes prohibited. |

---

## 2. Source Document Identification & Integrity Audit

Direct physical inspection of the governance documents in `app/public/wp-content/plugins/docs/` was performed using read-only filesystem commands. Checksums and sizes were verified:

| Document Role | Relative File Path | Byte Size | SHA-256 Digest | Status |
|:---|:---|:---:|:---:|:---:|
| **R8-A Inventory** | `plugins/docs/STAGE_3N-R2M-R5-R8-A_STATIC_EVIDENCE_INVENTORY.md` | 25,246 B | `B9B331CC51216FB9422CC6BDC61ACA85C7B56CDE71661DB81EE6BB8B973406E6` | Verified Unaltered |
| **R8-A Register** | `plugins/docs/STAGE_3N-R2M-R5-R8-A_OPEN_ITEM_REGISTER.md` | 25,381 B | `F0C8DD66DA27EC460AD76921C2C0761DD3A2EC0EDF7DF82FD01476616A61658E` | Verified Unaltered |
| **R8-B Plan (Superseded)** | `plugins/docs/STAGE_3N-R2M-R5-R8-B_READ_ONLY_PREREQUISITE_CLOSURE_PLAN.md` | 29,393 B | `06D4D448D8B8DA2568AED711E0E7ACA5A999FC59F796454ED6F5967230C46F9E` | Verified Historical |
| **R8-B Matrix (Superseded)** | `plugins/docs/STAGE_3N-R2M-R5-R8-B_GATE_DEPENDENCY_AND_APPROVAL_MATRIX.md` | 12,180 B | `582141B2B2236EA5BF05E933F34E97611CC86FF89BC2BE91439AFEF58A05E1C7` | Verified Historical |
| **R8-C Review** | `plugins/docs/STAGE_3N-R2M-R5-R8-C_INDEPENDENT_SAFETY_REVIEW.md` | 25,156 B | `79F668B1ED869CE8E6FA24BDF1CDFD257237C43973AF199A2A1621B84A72595B` | Verified Unaltered |
| **R8-C Register** | `plugins/docs/STAGE_3N-R2M-R5-R8-C_PLAN_DEFECT_REGISTER.md` | 27,387 B | `C8A0D826E06776BC8F3012F6C98BE47A0328D292789BF174146DC6D5B2BFE311` | Verified Unaltered |
| **R8-D Corrected Plan** | `plugins/docs/STAGE_3N-R2M-R5-R8-D_CORRECTED_PREREQUISITE_CLOSURE_PLAN.md` | 28,880 B | `815FC5C3739036DACD892684FF71DA2D8B17971E8ED11156B617166D27086805` | Verified Present |
| **R8-D Corrected Matrix** | `plugins/docs/STAGE_3N-R2M-R5-R8-D_CORRECTED_GATE_DEPENDENCY_AND_APPROVAL_MATRIX.md` | 43,254 B | `BD8ACAFED16EADDACDCBCFC2475C6A09A3A9B1A52E3133A37875A64D49A9EC23` | Verified Present |

---

## 3. Mandatory Technical Audit Challenges

### Challenge A: Recovery Protocol Integrity & T-0 Restore De-Escalation (Defects D01, D02)
- **R8-C Defect Finding:** R8-B falsely claimed that R8A-10 was `POLICY LOCKED / PROTOCOL APPROVED` without any ratified artifact on disk, and promoted full T-0 snapshot restore as an unconditional, harmless primary recovery method.
- **R8-D Inspection Finding:**
  - In R8-D Plan §3.1 and Matrix §6, R8A-10 status was explicitly restored to `OPERATIONAL PROTOCOL OPEN / PROPOSED IN R8-D`.
  - The formal 5-state lifecycle was established: `POLICY LOCKED` → `PROTOCOL DRAFTED` → `TECHNICALLY REVIEWED` → `OWNER APPROVED` → `OPERATIONALLY EXECUTED`.
  - Section 7.2 of the Plan and Gate 4R of the Matrix codify the **Window 2 Lost-Change Analysis Protocol**, defining 4 distinct triage pathways:
    1. *Pathway 1 (Gate 4R-SW):* Software Rollback via reverse import script (zero data loss).
    2. *Pathway 2 (Gate 4R-DR):* Forensic Read-Only Investigation (zero data loss).
    3. *Pathway 3 (Gate 4R-DBA):* Surgical DBA SQL script targeting specific untagged records.
    4. *Pathway 4 (Gate 4R-T0):* Full T-0 Restore, which is explicitly classified as **CRITICAL / IRREVERSIBLE DATA LOSS** requiring written Owner approval after analyzing post-T-0 delta (Action Scheduler rows, customer sessions, live admin edits).
- **Audit Assessment:** The technical design fully corrects the defect. However, because formal Owner sign-off is pending, D01 and D02 are correctly classified as `PARTIALLY_CLOSED` / `OWNER_DECISION_REQUIRED`.

### Challenge B: Database Target Isolation & Least Privilege (Defect D03, D09)
- **R8-C Defect Finding:** R8-B relied solely on `SELECT DATABASE()` while running as `root`, meaning any mistake during rehearsal would drop all 52 live tables on the shared LocalWP MySQL server.
- **R8-D Inspection Finding:**
  - R8-D partitioned Gate 1C into four discrete, authorized sub-phases: `1C-1` (Auxiliary DB & User Creation), `1C-2` (Restore Execution), `1C-3` (Fidelity Audit), and `1C-4` (Auxiliary DB Drop & Teardown).
  - Gate 1C-1 mandates provisioning a dedicated `rehearsal_user` with privileges granted *strictly* on `local_isolated_rehearsal` and **zero grants** on database `local`.
  - Gate 1C-2 mandates passing `--one-database local_isolated_rehearsal` to the `mysql` client. If statement redirection occurs, the restricted user fail-closes with `Access Denied`.
  - Server and target identity queries mandate checking `@@hostname`, `@@port`, `@@socket`, `@@datadir`, and `@@server_uuid`.
- **Audit Assessment:** Substantive root-cause resolution. Does not rely on wording. Status: `VERIFIED_CLOSED` at documentation design level.

### Challenge C: Dump Consistency, Lock Sequencing & Evidence Ladder (Defect D07, D13, D14)
- **R8-C Defect Finding:** Incomplete `mysqldump` flags (omitted routines, events, GTID), relative output path, and treating dump file size (> 1.2 MB) as proof of restorability.
- **R8-D Inspection Finding:**
  - Corrected command in Plan §5.2 and Matrix Gate 1B incorporates `--single-transaction --routines --events --quick --quote-names --set-gtid-purged=OFF --no-tablespaces --databases local` with absolute path `C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/app/public/wp-content/backups/t0_pre_import_backup.sql`.
  - Operational lock sequencing addresses concurrent-write risk during capture (mitigated by all-InnoDB engine verified in static audit).
  - Plan §5.1 establishes the 4-Level Dump Evidence Ladder:
    - `Level 1 (STATIC_CONTENT_REVIEW)`: Syntax audit, 0 DROP DATABASE, template presence.
    - `Level 2 (CAPTURE_RESULT_VERIFIED)`: Exit code 0, absolute path, hash, table count match.
    - `Level 3 (ISOLATED_RESTORE_VERIFIED)`: Clean isolated restore into `local_rehearsal`, table count match, template fidelity verified.
    - `Level 4 (RECOVERY_READINESS_ACCEPTED)`: Formal DBA and Owner acceptance dossier.
- **Audit Assessment:** Technically comprehensive and sound. File size is properly demoted to an advisory sanity check. Status: `VERIFIED_CLOSED`.

### Challenge D: Structural Template Verification vs String Matching (Defect D06)
- **R8-C Defect Finding:** R8-B's string grep (`Select-String "wp_template|front-page"`) matched serialized transient feeds in `wp_options` line 273, giving a false-positive pass even when `wp_posts` had zero templates.
- **R8-D Inspection Finding:**
  - String grep is completely removed.
  - Plan §3.2 and Matrix Gate 2C Query 10 mandate structural verification querying `wp_posts`:
    ```sql
    SELECT post_type, post_name, post_status FROM wp_posts WHERE post_type IN ('wp_template', 'wp_template_part');
    ```
    Requiring exact counts of `>= 3` templates and `>= 2` template parts.
- **Audit Assessment:** Eliminates false positives from serialized options. Status: `VERIFIED_CLOSED`.

### Challenge E: Decoupled Gate Dependencies & Explicit Authorizations (Defects D04, D08, D10)
- **R8-C Defect Finding:** Gate 0-GIT (state-changing commit) was an irrational prerequisite for Gate 1A (read-only query). R8A-13 had conflicting mappings. Gate 2D was marked "Conditional" but Gate 3 required it, causing deadlock.
- **R8-D Inspection Finding:**
  - Gate 1A is decoupled from Gate 0-GIT. Gate 1A is purely read-only observation. Gate 0-GIT remains mandatory before state-changing Gate 1B.
  - R8A-13 is tripartite: Filesystem assets (Gate 0-GIT), DB templates (Gate 1B/1C/2C), Visual acceptance (Gate 5).
  - Gate 2D branching is formally defined: Branch A (Skip 2D with Owner waiver; Gate 3 requires 2B + 2C) vs Branch B (Execute 2D; Gate 3 requires 2B + 2C + 2D).
- **Audit Assessment:** Gate logic is strictly coherent with zero circular dependencies or deadlocks. D04 and D10 require Owner ratification (`OWNER_DECISION_REQUIRED`).

### Challenge F: Acceptance Metric Disambiguation (Defect D05)
- **R8-C Defect Finding:** Ambiguous "64 products" metric in Gate 5.
- **R8-D Inspection Finding:**
  - Plan §3.2 and Matrix Gate 5 decompose the metrics cleanly:
    - 22 Parent Products (12 Simple, 10 Variable).
    - 42 Product Variations.
    - 64 Total Product-Related Posts in `wp_posts`.
    - 64 Unique SKUs in `wp_postmeta`.
    - 449 Total Stock Units (248 Simple + 201 Variations; 0 on Variable Parents).
    - 7 Product Categories; 27 Category Bindings.
    - 5 Brands, 3 Colors, 3 Sizes.
    - 4 Rows with blank images linked to placeholder Attachment ID 5.
    - 64 Ownership postmeta records (`_controlled_import_run_id`).
- **Audit Assessment:** Full alignment with WooCommerce database architecture. Status: `VERIFIED_CLOSED`.

### Challenge G: Disk Space & Live Table Count Realism (Defects D11, D12)
- **R8-C Defect Finding:** Contradictory free space thresholds (1 GB vs 2 GB) mislabeled as static evidence; hardcoded 52 tables.
- **R8-D Inspection Finding:**
  - Threshold unified to `> 2.0 GB` free disk space.
  - Free space and live table count explicitly classified as `RUNTIME_REQUIRED`.
  - Rehearsal table count derived dynamically from Gate 1A live baseline and T-0 dump `CREATE TABLE` count.
- **Audit Assessment:** Fully adheres to Policy A4. Status: `PARTIALLY_CLOSED` / `RUNTIME_REQUIRED`.

### Challenge H: Post-Recovery Pipeline Re-Entry (Defect D15)
- **R8-C Defect Finding:** Unclear pipeline re-entry following rollback or restore.
- **R8-D Inspection Finding:**
  - Section 4 (Gate 4R) of the Matrix explicitly dictates: following any recovery event, the pipeline cannot resume at Gate 4. The environment must re-enter at **Gate 1A**, capture a fresh T-0 snapshot, re-run isolated rehearsal, and re-execute all preflight gates.
- **Audit Assessment:** Eliminates risk of operating on consumed or stale recovery points. Status: `VERIFIED_CLOSED`.

---

## 4. Complete 20-Defect Verification Register

| Defect ID | Sev | R8-D Plan / Matrix Location | Concrete Correction Applied | Root Cause Resolved? | New Risk or Contradiction? | Closure Evidence Required | Verification Status |
|:---|:---:|:---|:---|:---:|:---:|:---|:---:|
| **R8C-D01** | P0 | Plan §3.1, §7; Matrix §4 (4R), §6 | Restored status to OPEN/PROPOSED; codified 5-stage lifecycle; drafted 4-pathway recovery protocol. | Yes | None | Owner signature on recovery protocol document. | `PARTIALLY_CLOSED` / `OWNER_DECISION_REQUIRED` |
| **R8C-D02** | P0 | Plan §3.1, §7.2; Matrix §4 (4R) | Removed automatic T-0 restore default; codified Lost-Change Analysis Protocol and 4 triage options. | Yes | None | Owner signature on Gate 4R triage framework. | `PARTIALLY_CLOSED` / `OWNER_DECISION_REQUIRED` |
| **R8C-D03** | P0 | Plan §3.1, §6; Matrix §4 (1C-1..4) | 4 sub-phases; dedicated `rehearsal_user` with 0 grants on `local`; `--one-database` flag; server identity check. | Yes | None | Gate 1C execution logs under restricted user. | `VERIFIED_CLOSED` (Design Level) |
| **R8C-D04** | P1 | Plan §3.2, §4.2; Matrix §3, §4 (1A, 0-GIT) | Decoupled Gate 1A (read-only) from Gate 0-GIT. 0-GIT mandatory before state-changing Gate 1B. | Yes | None | Owner approval of updated gate sequence. | `PARTIALLY_CLOSED` / `OWNER_DECISION_REQUIRED` |
| **R8C-D05** | P1 | Plan §3.2, §8; Matrix §4 (2C, 5) | Metric set decomposed: 22 parents, 42 variations, 64 posts, 64 SKUs, 449 stock (248+201), 7 categories, etc. | Yes | None | Static derivation verified; live verification at Gate 5. | `VERIFIED_CLOSED` (Static Level) |
| **R8C-D06** | P1 | Plan §3.2; Matrix §4 (2C Q10, 5) | Replaced raw string grep with structural SQL query for `wp_template` (>= 3) and `wp_template_part` (>= 2). | Yes | None | SQL query output at Gate 2C / Gate 5. | `VERIFIED_CLOSED` (Design Level) |
| **R8C-D07** | P1 | Plan §3.2, §5.2; Matrix §4 (1B) | Added `--routines --events --set-gtid-purged=OFF`; absolute output path; table count check. | Yes | None | Verified T-0 dump file header, tail, and checksum. | `VERIFIED_CLOSED` (Design Level) |
| **R8C-D08** | P1 | Plan §3.2, §4; Matrix §4 (0-GIT, 1C, 5) | Reconciled R8A-13 into 3 distinct gates: Filesystem (0-GIT), DB templates (1B/1C/2C), Storefront (5). | Yes | None | Verified gate outputs at respective stages. | `VERIFIED_CLOSED` |
| **R8C-D09** | P1 | Plan §3.2, §6; Matrix §4 (1C-1, 1C-4) | Rehearsal DB creation and drop separated into discrete sub-gates with separate authorization and logging. | Yes | None | Audit evidence and drop verification logs. | `VERIFIED_CLOSED` (Design Level) |
| **R8C-D10** | P1 | Plan §3.2, §5; Matrix §3.2, §4 (2D, 3) | Codified explicit branching: Branch A (Skip 2D via Owner waiver) vs Branch B (Execute 2D). | Yes | None | Owner decision selecting Branch A or Branch B. | `PARTIALLY_CLOSED` / `OWNER_DECISION_REQUIRED` |
| **R8C-D11** | P2 | Plan §3.3; Matrix §4 (1B, 1C) | Harmonized threshold to > 2.0 GB free disk space; classified strictly as runtime evidence. | Yes | None | Live volume free space query at Gate 1B. | `PARTIALLY_CLOSED` / `RUNTIME_REQUIRED` |
| **R8C-D12** | P2 | Plan §3.3; Matrix §4 (1A, 1B, 1C) | Demoted 52/91 tables from fixed assertion to dynamic baseline derived from Gate 1A live query. | Yes | None | Live query result from `information_schema.tables`. | `PARTIALLY_CLOSED` / `RUNTIME_REQUIRED` |
| **R8C-D13** | P2 | Plan §3.3, §5.1; Matrix §4 (1B, 1C) | Demoted dump file size to advisory check; codified 4-Level Evidence Ladder (L1 to L4). | Yes | None | Ladder milestone progression documented. | `VERIFIED_CLOSED` |
| **R8C-D14** | P2 | Plan §3.3, §5.2; Matrix §4 (1B) | Evaluated concurrent-write window and flush sequencing; confirmed InnoDB ACID consistency. | Yes | None | Verified mysqldump execution without lock conflicts. | `VERIFIED_CLOSED` |
| **R8C-D15** | P2 | Plan §3.3; Matrix §4 (4R, §6) | Codified mandatory re-entry at Gate 1A following any recovery event before any Gate 4 retry. | Yes | None | Gate re-entry log in incident dossier if triggered. | `VERIFIED_CLOSED` |
| **R8C-D16** | P2 | Plan §3.3; Matrix §4 (Pre-Gate 0) | Scoped Pre-Gate PASS strictly to static code and dataset files; non-code artifacts remain MISSING. | Yes | None | Pre-flight review summary verified. | `VERIFIED_CLOSED` |
| **R8C-D17** | P2 | Plan §3.3, §4.1; Matrix §4 (0-GIT) | Pinned exact 12 Stage 1F theme files (3 modified + 9 untracked) with SHA-256 digests. | Yes | None | Working tree file manifest verified against Git. | `VERIFIED_CLOSED` |
| **R8C-D18** | P2 | Plan §3.3; Matrix §1 | Normalized all governance artifact references to `app/public/wp-content/plugins/docs/`. | Yes | None | Canonical filesystem paths verified on disk. | `VERIFIED_CLOSED` |
| **R8C-D19** | P2 | Plan §3.3, §8.1; Matrix §4 (2C) | Fully enumerated all 11 non-destructive diagnostic SQL queries (Q1 to Q11) with schemas and stop criteria. | Yes | None | SQL query suite documentation inspected. | `VERIFIED_CLOSED` |
| **R8C-D20** | P2 | Plan §3.3; Matrix §1, §7 | Preserved historical audit trail; purged all unverified claims of runtime readiness. | Yes | None | Text audit confirms zero false claims. | `VERIFIED_CLOSED` |

---

## 5. Pending Owner Decisions & Missing Runtime Evidence

### 5.1 Owner Decisions Required Prior to Operational Work
1. **Decision 1: Recovery Protocol Ratification (R8C-D01):**
   - Formal owner approval of the 4-pathway recovery specification drafted in Section 7 of the Plan.
2. **Decision 2: Lost-Change Recovery Triage Framework Adoption (R8C-D02):**
   - Formal owner adoption of the Window 2 Lost-Change Protocol, affirming that T-0 snapshot restore will never be executed without prior post-T-0 delta analysis and explicit written sign-off.
3. **Decision 3: Gate 1A / Gate 0-GIT Decoupling Approval (R8C-D04):**
   - Approval to execute Gate 1A (read-only database identity query) prior to executing Gate 0-GIT (theme asset commit and tag).
4. **Decision 4: Gate 2D Branch Selection (R8C-D10):**
   - Formal selection between **Branch A** (Skip Gate 2D live storefront smoke testing, relying on Gate 2C SQL preflight) and **Branch B** (Mandate Gate 2D storefront HTTP smoke testing).

### 5.2 Missing Runtime Evidence (Cannot Be Statically Verified)
1. **Live MySQL Identity & Server Environment (Gate 1A):** `@@hostname`, `@@port`, `@@socket`, `@@datadir`, `DATABASE()`, `CURRENT_USER()`, and baseline table count.
2. **Runtime Free Disk Space (Gate 1B):** Verification that target disk volume has `> 2.0 GB` free space immediately prior to dump capture.
3. **Restricted Rehearsal Environment (Gate 1C):** Verification of `rehearsal_user` privilege grants and ingestion of T-0 snapshot with exit code 0.
4. **Diagnostic SQL Suite Execution (Gate 2C):** Output of queries Q1 through Q11 against the live database instance.

---

## 6. Strict Non-Execution & Safety Invariants Affirmation

This audit independently affirms:
- **Zero Database Operations:** No MySQL connections were opened; no SQL queries (read-only or state-changing) were run.
- **Zero Application Operations:** WordPress core was not bootstrapped; WP-CLI was not invoked; `run_controlled_import.php` was not executed.
- **Zero HTTP Probes:** No web server requests or storefront URL inspections were performed.
- **Zero Git State Changes:** No files were staged, committed, tagged, stashed, reset, or checked out.
- **Zero Snapshot Mutations:** No database dumps or snapshots were created, restored, or deleted.
- **Gate 1A Invariant:** Gate 1A is **NOT AUTHORIZED**.
- **Gate 4 Invariant:** Gate 4 is **STRICTLY BLOCKED**.

---
*End of Stage 3N-R2M-R5-R8-E Independent Verification Report.*
