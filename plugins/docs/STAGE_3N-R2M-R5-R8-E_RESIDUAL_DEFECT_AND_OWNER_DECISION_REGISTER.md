# STAGE 3N-R2M-R5-R8-E: RESIDUAL DEFECT AND OWNER DECISION REGISTER

**Project:** ATELIER — WordPress / WooCommerce Furniture E-commerce  
**Workspace:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\`  
**WordPress Root:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\`  
**Canonical Governance Directory:** `app/public/wp-content/plugins/docs/`  
**Document Path:** `app/public/wp-content/plugins/docs/STAGE_3N-R2M-R5-R8-E_RESIDUAL_DEFECT_AND_OWNER_DECISION_REGISTER.md`  
**Mode:** STRICTLY STATIC DOCUMENT INSPECTION & DOCUMENTATION ONLY  
**Gate 1A Status:** **NOT AUTHORIZED**  
**Gate 4 Status:** **STRICTLY BLOCKED**  
**Verification Date:** 2026-10-21  

---

## 1. Governance Principles & Invariant Register Rules

This Residual Defect and Owner Decision Register acts as the authoritative tracking ledger following the completion of Stage 3N-R2M-R5-R8-D and independent verification in Stage 3N-R2M-R5-R8-E.

### 1.1 Invariant Rules of Status Separation
1. **Zero Blended Statuses:** A single status (e.g. `PASS`) must never conflate documentation correctness, static evidence, runtime evidence, and owner authorization.
2. **Defect Status Discipline:**
   - **`VERIFIED_CLOSED`**: The root cause of the documentation defect is completely eliminated by verified static text, verified hashes, or proven schema rules.
   - **`PARTIALLY_CLOSED` / `OWNER_DECISION_REQUIRED`**: The documentation design is corrected, but the policy or execution path is blocked pending formal Project Owner ratification.
   - **`PARTIALLY_CLOSED` / `RUNTIME_REQUIRED`**: The documentation specification is corrected, but final acceptance requires active runtime environment evidence.
   - **`OPEN`**: Defect remains unaddressed or unresolved. (Count in R8-E = 0).
   - **`REGRESSION`**: A proposed fix introduced a new defect or contradiction. (Count in R8-E = 0).

---

## 2. Global Status Breakdown

- **Total Defects Evaluated:** 20 (`R8C-D01` to `R8C-D20`)
- **Verified Closed (Static / Design Level):** 14 defects
  - P0: `R8C-D03` (Rehearsal Isolation & Least Privilege)
  - P1: `R8C-D05` (Acceptance Metrics Decomposition)
  - P1: `R8C-D06` (Structural Template Verification)
  - P1: `R8C-D07` (mysqldump Deterministic Flags)
  - P1: `R8C-D08` (R8A-13 Tripartite Gate Mapping)
  - P1: `R8C-D09` (Rehearsal Lifecycle Sub-Phases)
  - P2: `R8C-D13` (4-Level Evidence Ladder)
  - P2: `R8C-D14` (Concurrent-Write Sequencing)
  - P2: `R8C-D15` (Gate 1A Pipeline Re-Entry)
  - P2: `R8C-D16` (Pre-Gate PASS Scoping)
  - P2: `R8C-D17` (Stage 1F 12-File Manifest Pinning)
  - P2: `R8C-D18` (Governance Document Path Normalization)
  - P2: `R8C-D19` (11-Query Preflight Suite Enumeration)
  - P2: `R8C-D20` (Historical Audit Trail Integrity)
- **Partially Closed (Owner Decision Required):** 4 defects
  - P0: `R8C-D01` (Recovery Protocol Ratification)
  - P0: `R8C-D02` (Window 2 Lost-Change Recovery Framework Approval)
  - P1: `R8C-D04` (Gate 1A / Gate 0-GIT Decoupling Approval)
  - P1: `R8C-D10` (Gate 2D Branch A vs Branch B Selection)
- **Partially Closed (Runtime Evidence Required):** 2 defects
  - P2: `R8C-D11` (Live Volume Free Disk Space > 2.0 GB)
  - P2: `R8C-D12` (Dynamic Live Baseline Table Count)
- **Regressions / Unresolved Flaws:** 0 defects

---

## 3. Comprehensive Residual Defect & Decision Matrix

---

### Item 1: R8C-D01 — Recovery Protocol Status Integrity
- **Defect ID:** `R8C-D01`
- **Severity:** `P0`
- **Root Cause:** Documentation in R8-B prematurely promoted open item R8A-10 to `PROTOCOL APPROVED` without the required standalone recovery protocol artifact existing on disk.
- **Evidence of Correction in R8-D:**
  - Plan §3.1 and Matrix §6 restore status to `OPERATIONAL PROTOCOL OPEN / PROPOSED IN R8-D`.
  - 5-stage lifecycle established: `POLICY LOCKED` → `PROTOCOL DRAFTED` → `TECHNICALLY REVIEWED` → `OWNER APPROVED` → `OPERATIONALLY EXECUTED`.
  - Section 7 of Plan drafts the complete 4-pathway recovery specification.
- **Role / Owner:** Project Owner & Lead Release Engineer.
- **Dependencies:** Blocks Gate 3 and Gate 4.
- **Stop Condition:** Any attempt to enter Gate 3 or Gate 4 without signed owner ratification of the recovery protocol.
- **Required Approval:** Signed approval by Project Owner of `STAGE_3N_GATE4R_RECOVERY_PROTOCOL.md` (or adopted Section 7 of R8-D Plan).
- **Closure Criterion:** Dedicated signed protocol document archived in governance root with status `OWNER APPROVED`.
- **Current Classification:** `PARTIALLY_CLOSED` / `OWNER_DECISION_REQUIRED`.

---

### Item 2: R8C-D02 — Unsafe Assumption About T-0 Snapshot Restore
- **Defect ID:** `R8C-D02`
- **Severity:** `P0`
- **Root Cause:** Prior runbooks assumed that restoring the pre-import snapshot is harmless and automatically the default recovery action, ignoring the destruction of post-T-0 legitimate data (Action Scheduler churn, sessions, user updates).
- **Evidence of Correction in R8-D:**
  - Plan §3.1, §7.2, and Matrix Gate 4R de-escalate snapshot restore to an extreme fallback (`Gate 4R-T0`) requiring written data-loss waiver.
  - Codifies mandatory Lost-Change Analysis Protocol requiring: (1) Incident evidence lock, (2) Live query of post-T-0 delta, (3) Comparison of 4 recovery pathways (Software Rollback, Forensics, DBA SQL, Snapshot Restore), (4) Explicit per-event Owner authorization.
- **Role / Owner:** Project Owner & Senior Database Administrator (DBA).
- **Dependencies:** Blocks Gate 4R and Gate 3.
- **Stop Condition:** Restoring any snapshot without a documented lost-change impact analysis and written owner sign-off.
- **Required Approval:** Owner signature on Window 2 Recovery Loss Framework.
- **Closure Criterion:** Formal owner adoption of Lost-Change Analysis Protocol in Gate 3 dossier.
- **Current Classification:** `PARTIALLY_CLOSED` / `OWNER_DECISION_REQUIRED`.

---

### Item 3: R8C-D03 — Inadequate Gate 1C Isolation Design
- **Defect ID:** `R8C-D03`
- **Severity:** `P0`
- **Root Cause:** Relying solely on `SELECT DATABASE()` while restoring as `root` provided zero safeguard against accidental live table deletion (`DROP TABLE IF EXISTS`) on the shared LocalWP server.
- **Evidence of Correction in R8-D:**
  - Redesigned Gate 1C into four discrete sub-phases (`1C-1` to `1C-4`).
  - Mandated dedicated `rehearsal_user` with zero grants on `local` and grants strictly on `local_isolated_rehearsal`.
  - Mandated client parameter `--one-database local_isolated_rehearsal`.
  - Mandated server and target identity queries (`@@hostname`, `@@port`, `@@socket`, `@@datadir`, `@@server_uuid`).
- **Role / Owner:** Database Administrator & Safety Reviewer.
- **Dependencies:** Unlocks Gate 1C rehearsal execution once authorized.
- **Stop Condition:** Rehearsal attempted under `root` account or missing `--one-database` flag.
- **Required Approval:** Technical Lead approval of restricted user provisioning script.
- **Closure Criterion:** Design verified closed statically; operational closure upon Gate 1C execution report.
- **Current Classification:** `VERIFIED_CLOSED` (Documentation Design Level).

---

### Item 4: R8C-D04 — State-Changing Gate 0-GIT Gating Read-Only Gate 1A
- **Defect ID:** `R8C-D04`
- **Severity:** `P1`
- **Root Cause:** R8-B made Gate 0-GIT (a state-changing commit and tag) an irrational prerequisite for Gate 1A (a read-only database identity query), claiming theme assets could be lost during a database read.
- **Evidence of Correction in R8-D:**
  - Decoupled Gate 1A from Gate 0-GIT in Plan §3.2 and Matrix §3.
  - Gate 1A requires only read-only connection authorization. Gate 0-GIT remains mandatory before state-changing Gate 1B.
- **Role / Owner:** Project Owner.
- **Dependencies:** Unlocks Gate 1A authorization request.
- **Stop Condition:** Forcing Git state changes before read-only observation.
- **Required Approval:** Owner signature approving decoupled gate sequence.
- **Closure Criterion:** Formal owner sign-off on the corrected gate hierarchy in Matrix §3.
- **Current Classification:** `PARTIALLY_CLOSED` / `OWNER_DECISION_REQUIRED`.

---

### Item 5: R8C-D05 — Ambiguous Acceptance Metrics ("64 Products")
- **Defect ID:** `R8C-D05`
- **Severity:** `P1`
- **Root Cause:** Conflating parent products, variations, SKUs, and stock totals under the ambiguous phrase "64 products".
- **Evidence of Correction in R8-D:**
  - Plan §3.2 and Matrix Gate 5 fully decompose the metrics:
    - 22 Parent Products (12 Simple, 10 Variable).
    - 42 Product Variations.
    - 64 Total Product-Related Posts.
    - 64 Unique SKUs.
    - 449 Total Stock Units (248 Simple + 201 Variations; 0 on Variable Parents).
    - 7 Categories, 27 Category Associations.
    - 5 Brands, 3 Colors, 3 Sizes.
    - 4 Blank Image fallbacks to placeholder Attachment ID 5.
    - 64 Ownership postmeta rows (`_controlled_import_run_id`).
- **Role / Owner:** QA Lead & Database Analyst.
- **Dependencies:** Governs Gate 2B dry-run parse and Gate 5 live acceptance.
- **Stop Condition:** Acceptance computed against ambiguous product counts.
- **Required Approval:** Technical Lead sign-off on metric specification.
- **Closure Criterion:** 100% static derivation verified; runtime match verified at Gate 5.
- **Current Classification:** `VERIFIED_CLOSED` (Static Level).

---

### Item 6: R8C-D06 — Unreliable Template Preservation Proof Method
- **Defect ID:** `R8C-D06`
- **Severity:** `P1`
- **Root Cause:** Using `Select-String "wp_template|front-page"` on the SQL dump returned false positives by matching serialized RSS feeds in `wp_options` line 273 even when `wp_posts` had zero templates.
- **Evidence of Correction in R8-D:**
  - Removed raw substring grep.
  - Mandated structural SQL query in Gate 2C (Query 10) and Gate 5:
    `SELECT post_type, post_name, post_status FROM wp_posts WHERE post_type IN ('wp_template', 'wp_template_part');`
    with thresholds: `>= 3` templates and `>= 2` parts.
- **Role / Owner:** Release Engineer & QA Engineer.
- **Dependencies:** Governs Gate 1B/1C dump verification and Gate 5 acceptance.
- **Stop Condition:** Verification attempted using grep or substring matching.
- **Required Approval:** Technical Lead verification sign-off.
- **Closure Criterion:** Structural SQL query executed and documented at Gate 2C.
- **Current Classification:** `VERIFIED_CLOSED` (Documentation Design Level).

---

### Item 7: R8C-D07 — mysqldump Specification Gaps
- **Defect ID:** `R8C-D07`
- **Severity:** `P1`
- **Root Cause:** Omission of `--routines`, `--events`, `--set-gtid-purged=OFF`, relative output path, and unproven consistency claim.
- **Evidence of Correction in R8-D:**
  - Plan §5.2 and Matrix Gate 1B add all required flags, specify absolute output path `C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/app/public/wp-content/backups/t0_pre_import_backup.sql`, and add table count cross-check.
- **Role / Owner:** Database Administrator.
- **Dependencies:** Prerequisite for Gate 1B execution.
- **Stop Condition:** Invocation of `mysqldump` without required flags or using relative paths.
- **Required Approval:** DBA sign-off.
- **Closure Criterion:** Output dump verified at absolute path with matching table definitions.
- **Current Classification:** `VERIFIED_CLOSED` (Documentation Design Level).

---

### Item 8: R8C-D08 — R8A-13 Gate Mapping Contradiction
- **Defect ID:** `R8C-D08`
- **Severity:** `P1`
- **Root Cause:** R8-A mapped Stage 1F preservation to Gate 1B/5, while R8-B mapped it to Gate 0-GIT.
- **Evidence of Correction in R8-D:**
  - Reconciled into three distinct phases: Filesystem assets (Gate 0-GIT), Database templates (Gate 1B/1C/2C), and Final release acceptance (Gate 5).
- **Role / Owner:** Documentation Lead.
- **Dependencies:** Resolves workflow ambiguity.
- **Stop Condition:** Conflicting gate mappings circulating in project docs.
- **Required Approval:** Documentation Lead sign-off.
- **Closure Criterion:** Reconciled mapping reflected across Plan and Matrix.
- **Current Classification:** `VERIFIED_CLOSED`.

---

### Item 9: R8C-D09 — Rehearsal Database Lifecycle Governance
- **Defect ID:** `R8C-D09`
- **Severity:** `P1`
- **Root Cause:** Rehearsal creation and dropping were performed without separate authorization or drop-evidence logging, violating Policy A3.
- **Evidence of Correction in R8-D:**
  - Gate 1C subdivided into 1C-1 (Creation), 1C-2 (Restore), 1C-3 (Audit), and 1C-4 (Drop), each requiring explicit authorization and audit logs.
- **Role / Owner:** Project Owner & DBA.
- **Dependencies:** Governs Gate 1C execution.
- **Stop Condition:** Rehearsal DB dropped without recorded evidence and authorization.
- **Required Approval:** Owner authorization for 1C-1 and 1C-4.
- **Closure Criterion:** Discrete logs and authorization records generated for each sub-phase.
- **Current Classification:** `VERIFIED_CLOSED` (Documentation Design Level).

---

### Item 10: R8C-D10 — Gate 2D Conditional vs Gate 3 Prerequisite Contradiction
- **Defect ID:** `R8C-D10`
- **Severity:** `P1`
- **Root Cause:** Matrix §2 listed Gate 2D as "Conditional" yet Gate 3 required Gate 2D to pass, causing deadlock if skipped.
- **Evidence of Correction in R8-D:**
  - Defined explicit branching in Matrix §3.2 and Plan §3.2:
    - **Branch A (Recommended):** Gate 2D skipped via formal Owner waiver; Gate 3 requires 2B + 2C + Skip Waiver.
    - **Branch B:** Gate 2D executed under read-only HTTP constraints; Gate 3 requires 2B + 2C + 2D.
- **Role / Owner:** Project Owner.
- **Dependencies:** Governs pre-import verification path to Gate 3.
- **Stop Condition:** Entering Gate 3 without recorded Branch A or Branch B decision.
- **Required Approval:** Owner decision selecting Branch A or Branch B.
- **Closure Criterion:** Owner signature recorded on Branch Selection Form.
- **Current Classification:** `PARTIALLY_CLOSED` / `OWNER_DECISION_REQUIRED`.

---

### Item 11: R8C-D11 — Free Disk Space Contradiction & Evidence Classification
- **Defect ID:** `R8C-D11`
- **Severity:** `P2`
- **Root Cause:** Contradictory thresholds (1 GB vs 2 GB) and mislabeling free space as static evidence.
- **Evidence of Correction in R8-D:**
  - Threshold standardized to `> 2.0 GB` free disk space.
  - Mislabeled "static" claim removed; classified strictly as `RUNTIME_REQUIRED`.
- **Role / Owner:** System Administrator / DBA.
- **Dependencies:** Gate 1B prerequisite.
- **Stop Condition:** Free disk space `< 2.0 GB` at runtime.
- **Required Approval:** Operator verification at Gate 1B.
- **Closure Criterion:** Real-time volume query showing `> 2.0 GB` free space prior to dump.
- **Current Classification:** `PARTIALLY_CLOSED` / `RUNTIME_REQUIRED`.

---

### Item 12: R8C-D12 — Hardcoded Baseline Table Count Stop Condition
- **Defect ID:** `R8C-D12`
- **Severity:** `P2`
- **Root Cause:** Fixed assertions of 52 or 91 tables used as stop conditions without runtime baseline verification.
- **Evidence of Correction in R8-D:**
  - Plan §3.3 and Matrix Gate 1A/1C derive expected table count dynamically from live query against `information_schema.tables` and T-0 dump `CREATE TABLE` count.
- **Role / Owner:** Database Administrator.
- **Dependencies:** Gate 1A and Gate 1C acceptance criteria.
- **Stop Condition:** Discrepancy between live baseline table count and rehearsal table count.
- **Required Approval:** DBA sign-off on baseline count.
- **Closure Criterion:** Live query output recorded at Gate 1A.
- **Current Classification:** `PARTIALLY_CLOSED` / `RUNTIME_REQUIRED`.

---

### Item 13: R8C-D13 — Dump File Size & Evidence Ladder Progression
- **Defect ID:** `R8C-D13`
- **Severity:** `P2`
- **Root Cause:** Treating dump file size (> 1.2 MB) as proof of restorability.
- **Evidence of Correction in R8-D:**
  - File size demoted to advisory sanity check.
  - Codified 4-Level Evidence Ladder (L1 Static Review → L2 Capture Verified → L3 Isolated Restore Verified → L4 Recovery Accepted).
- **Role / Owner:** Safety Reviewer & DBA.
- **Dependencies:** Governs T-0 backup acceptance.
- **Stop Condition:** Treating a Level 2 capture as ready for production import without Level 3 rehearsal.
- **Required Approval:** DBA sign-off on Level 3 evidence.
- **Closure Criterion:** Evidence ladder progression documented in Gate 1B/1C dossier.
- **Current Classification:** `VERIFIED_CLOSED`.

---

### Item 14: R8C-D14 — Concurrent-Write Window & Lock Sequencing
- **Defect ID:** `R8C-D14`
- **Severity:** `P2`
- **Root Cause:** Unaddressed concurrent-write risk and background cron churn during T-0 capture.
- **Evidence of Correction in R8-D:**
  - Evaluated `--single-transaction` against verified all-InnoDB engine tables; documented lock sequencing and cron pause mechanisms.
- **Role / Owner:** Database Administrator.
- **Dependencies:** Gate 1B execution.
- **Stop Condition:** Dump capture attempted during active cron jobs.
- **Required Approval:** DBA sign-off.
- **Closure Criterion:** Documented lock sequence executed during Gate 1B.
- **Current Classification:** `VERIFIED_CLOSED`.

---

### Item 15: R8C-D15 — Post-Recovery Pipeline Re-Entry
- **Defect ID:** `R8C-D15`
- **Severity:** `P2`
- **Root Cause:** Unclear pipeline re-entry rules following rollback or restore.
- **Evidence of Correction in R8-D:**
  - Explicit rule established in Matrix §4 (Gate 4R): after any recovery event, the pipeline must re-enter at **Gate 1A**, capture a fresh T-0 snapshot, and re-execute all preflight gates.
- **Role / Owner:** Lead Release Engineer.
- **Dependencies:** Governs incident recovery workflow.
- **Stop Condition:** Retrying Gate 4 after recovery without returning to Gate 1A.
- **Required Approval:** Owner approval of recovery dossier.
- **Closure Criterion:** Re-entry rule codified in Matrix and incident runbooks.
- **Current Classification:** `VERIFIED_CLOSED`.

---

### Item 16: R8C-D16 — Pre-Gate PASS Scope Ambiguity
- **Defect ID:** `R8C-D16`
- **Severity:** `P2`
- **Root Cause:** Pre-Gate PASS wording could be misread as certifying that all non-code artifacts (dumps, manifests) were present.
- **Evidence of Correction in R8-D:**
  - Wording scoped explicitly to `Pre-Gate PASS (Code & Dataset Artifacts Only)`. Non-code artifacts clearly marked `MISSING`.
- **Role / Owner:** Governance Lead.
- **Dependencies:** Pre-flight review clarity.
- **Stop Condition:** Misrepresenting non-code artifact presence.
- **Required Approval:** Governance Lead sign-off.
- **Closure Criterion:** Pre-Gate 0 scope text verified in Plan and Matrix.
- **Current Classification:** `VERIFIED_CLOSED`.

---

### Item 17: R8C-D17 — Stage 1F Working Tree File Count Discrepancy
- **Defect ID:** `R8C-D17`
- **Severity:** `P2`
- **Root Cause:** R8-A claimed "14 untracked Stage 1F files"; physical inspection confirmed exactly 12 theme asset files (3 modified + 9 untracked).
- **Evidence of Correction in R8-D:**
  - Plan §4.1 pinned the exact 12-file Stage 1F manifest with byte sizes and SHA-256 digests.
- **Role / Owner:** Release Engineer.
- **Dependencies:** Gate 0-GIT asset locking.
- **Stop Condition:** Staging files outside the 12-file manifest without explanation.
- **Required Approval:** Technical Lead sign-off.
- **Closure Criterion:** 12-file manifest verified against working tree.
- **Current Classification:** `VERIFIED_CLOSED`.

---

### Item 18: R8C-D18 — Governance Document Path Normalization
- **Defect ID:** `R8C-D18`
- **Severity:** `P2`
- **Root Cause:** Ambiguity between `docs/` and `plugins/docs/` paths across earlier registers.
- **Evidence of Correction in R8-D:**
  - Standardized all canonical document references to `app/public/wp-content/plugins/docs/`.
- **Role / Owner:** Documentation Lead.
- **Dependencies:** Corpus path consistency.
- **Stop Condition:** Filing governance documents outside canonical directory.
- **Required Approval:** Documentation Lead sign-off.
- **Closure Criterion:** All references in R8-D and R8-E point to `plugins/docs/`.
- **Current Classification:** `VERIFIED_CLOSED`.

---

### Item 19: R8C-D19 — Enumeration of Gate 2C Preflight SQL Suite
- **Defect ID:** `R8C-D19`
- **Severity:** `P2`
- **Root Cause:** Asserting "all 11 queries pass" without publishing query text or expected outputs.
- **Evidence of Correction in R8-D:**
  - Plan §8.1 and Matrix Gate 2C fully enumerate all 11 diagnostic SQL queries with SQL text, purpose, schemas, expected outputs, and stop conditions.
- **Role / Owner:** Database Analyst.
- **Dependencies:** Governs Gate 2C preflight execution.
- **Stop Condition:** Executing an unreviewed or unlisted diagnostic query.
- **Required Approval:** DBA sign-off on query suite.
- **Closure Criterion:** 11-query table inspected and verified in Plan §8.1.
- **Current Classification:** `VERIFIED_CLOSED`.

---

### Item 20: R8C-D20 — Historical Audit Trail Integrity
- **Defect ID:** `R8C-D20`
- **Severity:** `P2`
- **Root Cause:** Unverified claims of runtime readiness in earlier summaries.
- **Evidence of Correction in R8-D:**
  - Preserved historical audit trail while purging unverified claims of runtime readiness from R8-D planning documents.
- **Role / Owner:** Governance Lead.
- **Dependencies:** Historical integrity.
- **Stop Condition:** Overwriting historical audit records.
- **Required Approval:** Governance Lead sign-off.
- **Closure Criterion:** Historical R8-A, R8-B, and R8-C documents remain untouched on disk.
- **Current Classification:** `VERIFIED_CLOSED`.

---

## 4. Formal Action Ledger for the Project Owner

To progress from documentation verification to operational authorization readiness, the Project Owner must execute the following four decisions:

```
┌──────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                   PROJECT OWNER ACTION LEDGER                                    │
├────────────────────┬──────────────────────────────────────────────────────┬──────────────────────┤
│ Decision Reference │ Action Description                                   │ Required Form        │
├────────────────────┼──────────────────────────────────────────────────────┼──────────────────────┤
│ OWNER-DECISION-01  │ Ratify Recovery Protocol Lifecycle & 4-Pathway Spec  │ Signed Approval Form │
│ OWNER-DECISION-02  │ Adopt Window 2 Lost-Change Triage & Analysis Protocol│ Signed Waiver Form   │
│ OWNER-DECISION-03  │ Approve Gate 1A (Read-Only) / Gate 0-GIT Decoupling  │ Signed Sequence Form │
│ OWNER-DECISION-04  │ Select Gate 2D Branch (Branch A vs Branch B)         │ Signed Branch Form   │
└────────────────────┴──────────────────────────────────────────────────────┴──────────────────────┘
```

**CRITICAL SAFEGUARD REMINDER:**  
Executing these decisions unlocks the ability to request **Gate 1A (Read-Only Database Identity Verification)**. It **DOES NOT** authorize Gate 4. Gate 4 remains **STRICTLY BLOCKED** until all intermediate gates achieve Level 3 evidence and a dedicated import warrant is signed.

---
*End of Stage 3N-R2M-R5-R8-E Residual Defect and Owner Decision Register.*
