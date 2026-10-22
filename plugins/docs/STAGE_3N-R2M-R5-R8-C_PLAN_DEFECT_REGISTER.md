# STAGE 3N-R2M-R5-R8-C — PLAN DEFECT REGISTER

**Project:** ATELIER — WordPress / WooCommerce Furniture E-commerce
**Workspace:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\`
**Mode:** STRICTLY READ-ONLY STATIC REVIEW AND DOCUMENTATION
**Gate 1A Status:** **NOT AUTHORIZED** | **Gate 4 Status:** **STRICTLY BLOCKED**
**Register Status:** **REGISTER COMPLETE — 20 DEFECTS (3 × P0, 7 × P1, 10 × P2)**

**Referenced documents (abbreviated):**
- **PLAN** = `plugins/docs/STAGE_3N-R2M-R5-R8-B_READ_ONLY_PREREQUISITE_CLOSURE_PLAN.md`
- **MATRIX** = `plugins/docs/STAGE_3N-R2M-R5-R8-B_GATE_DEPENDENCY_AND_APPROVAL_MATRIX.md`
- **INVENTORY** = `plugins/docs/STAGE_3N-R2M-R5-R8-A_STATIC_EVIDENCE_INVENTORY.md`
- **REGISTER** = `plugins/docs/STAGE_3N-R2M-R5-R8-A_OPEN_ITEM_REGISTER.md`
- **R7-MATRIX** = `plugins/docs/STAGE_3N-R2M-R5-R7_APPROVAL_GATE_MATRIX.md`
- **IMPORTER** = `plugins/run_controlled_import.php`
- **GATE2DUMP** = `backups/stage_1e_gate2_pre_import_20261016_223502.sql`

**Register policy:** No defect is marked `CLOSED_WITH_EVIDENCE` in this review — R8-C is a review stage and does not rewrite R8-B. A proposed correction is recorded as `CORRECTION PROPOSED`; the defect stays effectively open until R8-B is amended and the correction is re-reviewed. Defects needing owner ratification are `OWNER_DECISION_REQUIRED`.

---

## P0 — Critical Safety Blockers

### R8C-D01 — Open P0 item silently promoted to "PROTOCOL APPROVED" without artifact
- **Severity:** P0
- **Affected:** PLAN §10 (row R8A-10); cross-ref REGISTER §3 (R8A-10), INVENTORY §4/§5
- **Current statement:** PLAN §10 lists R8A-10 (Window 2 Recovery Protocol) as `POLICY LOCKED / PROTOCOL APPROVED` with owner "Project Owner & DBA".
- **Evidence:** REGISTER §3 R8A-10 states `POLICY LOCKED / OPERATIONAL PROTOCOL OPEN` and "What Remains Unknown: Whether the operator protocol designates Gate 4R-DR as primary…". The required artifact `docs/STAGE_3N_GATE4R_RECOVERY_PROTOCOL.md` does not exist on disk (verified). PLAN §9 presents the DR-primary hierarchy as "Established" without a ratified protocol.
- **Category:** Evidence-integrity violation (Policy A4 pattern: documentation standing in for accepted evidence).
- **Consequence:** Recovery governance appears settled when it is not; Gate 3 could accept an "approved" recovery plan that was never ratified, violating D4's "recovery planning … accepted" precondition.
- **Required correction:** Restore R8A-10 to `OPEN` status in R8-B's mapping; mark the hierarchy in PLAN §9 as `PROPOSED — AWAITING PROTOCOL RATIFICATION`; require the missing protocol artifact before Gate 3.
- **Owner approval required:** Yes.
- **Dependencies:** Blocks Gate 3; related to R8C-D02.
- **Stop condition:** Any reference to an "approved" recovery protocol without the artifact existing and being ratified.
- **Status:** `OWNER_DECISION_REQUIRED`

### R8C-D02 — T-0 snapshot restore assumed harmless; no lost-change analysis
- **Severity:** P0
- **Affected:** PLAN §9.3 (recovery hierarchy), §8 (Gate 4R); MATRIX §2 (Gate 4R row)
- **Current statement:** "Primary Recovery (Gate 4R-DR): Restore the verified pre-import baseline `stage_3n_t0_snapshot.sql`. This completely eliminates the untagged orphan post and restores the exact clean pre-import baseline."
- **Evidence:** No step in PLAN or MATRIX identifies the exact affected database before restore, inventories post-T-0 changes that would be erased, or chooses among recovery modes by impact. Restoring an earlier snapshot may erase legitimate changes created after T-0 (Action Scheduler rows, sessions, option churn, any legitimate admin edits). Crash-state evidence archiving exists (PLAN §9.3) but no analysis step follows it.
- **Category:** Unsafe future operational instruction / recovery-planning gap (Audit E).
- **Consequence:** Silent destruction of legitimate post-T-0 data during recovery; policy conflict with the spirit of B4/C5 (preserve legitimate state; never destroy on assumption).
- **Required correction:** For every recovery path require, before any mutation: (1) evidence preservation completed and verified; (2) explicit identification of the target database via runtime identity check; (3) a documented lost-change analysis enumerating what post-T-0 data the restore would erase; (4) an explicit decision record selecting software rollback / isolated forensic investigation / snapshot restore / DBA remediation; (5) separate per-event approval; (6) prohibition of heuristic deletion of untagged posts (reaffirm C5).
- **Owner approval required:** Yes.
- **Dependencies:** R8C-D01; Gate 4R design.
- **Stop condition:** Execution of any restore or deletion without the completed analysis and per-event approval.
- **Status:** `OWNER_DECISION_REQUIRED`

### R8C-D03 — Gate 1C isolation proof inadequate; restore runs as root
- **Severity:** P0
- **Affected:** PLAN §7.4 (isolated rehearsal design); MATRIX §2 (Gate 1C row)
- **Current statement:** "Pre-Restore Isolation Proof: Execute `SELECT DATABASE();` … Restore Command: `mysql -h 127.0.0.1 -P 10011 -u root -p local_isolated_rehearsal < …`"
- **Evidence:** A single database-name result does not identify the server or constrain statement execution. The dump contains `DROP TABLE IF EXISTS` for all 52 tables (statically verified in all three historical dumps; the T-0 dump will share this structure). As `root`, any redirection or target mistake executes against any schema on the shared LocalWP server. No least-privilege credential, no `--one-database` guard, no independent server identification (`@@hostname`, `@@datadir`, server UUID), no pre-restore precondition checklist, no protection against reuse of live connection settings, and no fail-closed rule if isolation cannot be demonstrated. R8A-08 (grant restrictions) remains OPEN.
- **Category:** Isolation/target-safety design gap (Audit C).
- **Consequence:** Accidental restore into the live `local` database — the single most destructive accident available in this workflow (would drop all 52 live tables).
- **Required correction:** (1) Dedicated least-privilege MySQL account for the rehearsal with privileges only on `local_isolated_rehearsal`; (2) full server/schema identification before restore (`SELECT DATABASE(), CURRENT_USER(), @@hostname, @@port, @@datadir`) recorded as preconditions; (3) `--one-database` on the restore client; (4) a written precondition checklist immediately before the command; (5) an explicit fail-closed rule: if isolation cannot be demonstrated, the rehearsal aborts and Gate 1C is marked BLOCKED; (6) documented procedure preventing reuse of live connection settings.
- **Owner approval required:** Correction itself no; new credential provisioning Yes (credential management).
- **Dependencies:** R8A-08; Gate 1B dump; R8C-D09.
- **Stop condition:** Any restore executed without the recorded preconditions or with a non-restricted credential.
- **Status:** `CORRECTION PROPOSED`

---

## P1 — Significant Defects

### R8C-D04 — State-changing Gate 0-GIT is a hard prerequisite for read-only Gate 1A
- **Severity:** P1
- **Affected:** MATRIX §2 (Gate 1A row: "Preconditions: Gate 0-GIT completed"), MATRIX §4.1; PLAN §7.1 (precondition 2)
- **Current statement:** MATRIX §4.1: "Gate 1A cannot be authorized until Stage 1F assets are preserved under Gate 0-GIT, ensuring that if any database connection issue requires environment inspection, theme assets cannot be lost."
- **Evidence:** Gate 1A is classified `READ-ONLY RUNTIME` and cannot lose theme assets; the stated justification does not hold. R7-MATRIX line 323 previously defined Gate 1A with prerequisites "None". Stage 1F preservation remains mandatory (B4) but logically gates state-changing operations (1B+), not a read-only identity check.
- **Category:** Gate-dependency logic defect (Audit A item 4).
- **Consequence:** Pressures a state-changing commit/tag under time pressure before any runtime verification; conflates preservation (valid) with an unnecessary sequencing constraint; diverges from R7 without recorded rationale.
- **Required correction:** Decouple: Gate 1A requires only its own authorization; Gate 0-GIT remains a mandatory precondition for Gate 1B and all subsequent state-changing gates. Record the rationale change.
- **Owner approval required:** Yes (changes the approved gate sequence).
- **Dependencies:** R8A-13; Gate 0-GIT design.
- **Stop condition:** Any state-changing Git operation executed solely to unlock a read-only gate.
- **Status:** `OWNER_DECISION_REQUIRED`

### R8C-D05 — "64 products" acceptance metric ambiguous; metric set incomplete
- **Severity:** P1
- **Affected:** MATRIX §2 Gate 4/Gate 5 rows; PLAN §8 Gate 5
- **Current statement:** Gate 5: "Acceptance queries verify 64 products, 449 stock, 64 SKUs."
- **Evidence:** WooCommerce does not report "64 products" as a product count. Static decomposition of the SHA-verified working CSV: 12 simple + 10 variable = 22 parents; 42 variations; 64 total product posts; 64 unique SKUs; 449 stock = 248 (simple) + 201 (variations); 7 categories; 27 category associations (IMPORTER line 264); 5 brands, 3 colors, 3 sizes (no importer constant, no gate criterion); 4 rows without images → placeholder attachment ID 5; total attachment count undefined; ownership-tag counts and manifest `COMPLETED` status absent from Gate 5.
- **Category:** Ambiguous acceptance criteria (Audit E).
- **Consequence:** Spurious Gate 4R rollback (safe direction) or false acceptance by counting the wrong rows (unsafe direction).
- **Required correction:** Gate 5 must enumerate separate expected metrics: parents=22, simple=12, variable=10, variations=42, total product posts=64 (labeled as such), unique SKUs=64, total stock=449 (248+201), categories=7, category associations=27, attribute term counts (brands=5, colors=3, sizes=3) added to importer constants, expected attachment count defined, ownership-tag counts, manifest `COMPLETED`.
- **Owner approval required:** No (technical correction), but new metric constants should be owner-acknowledged.
- **Dependencies:** Importer constants (frozen by hash); Gate 2C suite.
- **Stop condition:** Any acceptance verdict computed against the ambiguous "64 products" formulation.
- **Status:** `CORRECTION PROPOSED`

### R8C-D06 — Template-preservation proof method unreliable; DB-resident fidelity never verified
- **Severity:** P1
- **Affected:** PLAN §7.3 (check 5); PLAN §5.1; REGISTER R8A-13
- **Current statement:** "MUST RETURN MATCHES IN wp_posts DATA — Select-String -Pattern 'wp_template|front-page|archive-product'"
- **Evidence:** The same pattern statically **matches GATE2DUMP line 273** — a serialized RSS-feed transient inside `wp_options` — in a dump that contains **zero** rows with `post_type = 'wp_template'` (verified: 0 matches for `'wp_template'`/`'wp_template_part'` as data values). The check returns false positives and cannot prove template preservation. Separately, R8A-13's unknown ("exact diff between disk templates and active database template records") is assigned to no gate.
- **Category:** Verification-method defect (Audit B/D).
- **Consequence:** A T-0 dump missing Stage 1F DB-resident templates could pass "template preservation", defeating B4 at the exact moment the recovery point is created.
- **Required correction:** Replace raw string matching with structural verification: parse `wp_posts` INSERT rows and count rows with `post_type='wp_template'` / `'wp_template_part'`; require counts ≥ 3 templates / ≥ 2 parts; reconcile DB-resident template content against on-disk files (close R8A-13's unknown) before Gate 1B acceptance.
- **Owner approval required:** No.
- **Dependencies:** Gate 1B; R8A-13; R8C-D04.
- **Stop condition:** Template-preservation verdict based on raw string search.
- **Status:** `CORRECTION PROPOSED`

### R8C-D07 — mysqldump specification gaps threaten T-0 completeness
- **Severity:** P1
- **Affected:** PLAN §7.2, §7.3
- **Current statement:** Proposed dump command with `--single-transaction --quick --hex-blob --default-character-set=utf8mb4 --no-tablespaces --result-file="backups/stage_3n_t0_snapshot.sql"`.
- **Evidence:** `--routines` and `--events` default OFF in mysqldump (silent omission if any exist — live state runtime-unverified); `--result-file` is a CWD-relative path (wrong working directory silently misplaces the recovery point); "--single-transaction: Guarantees ACID snapshot consistency" is overstated in general (safe here only because all 52 tables are statically `ENGINE=InnoDB`); no `--set-gtid-purged=OFF` for MySQL 8.4; no post-capture check that the dump's `CREATE TABLE` count equals the live table count.
- **Category:** Incomplete operational specification (Audit B).
- **Consequence:** T-0 recovery point silently incomplete or misplaced; restore failure or data loss discovered only at the worst possible time.
- **Required correction:** Add `--routines --events --set-gtid-purged=OFF`; use an absolute `--result-file` path plus a post-capture existence check at that exact path; qualify the `--single-transaction` claim (all-InnoDB verified); add a CREATE TABLE-count reconciliation check to Gate 1B acceptance.
- **Owner approval required:** No.
- **Dependencies:** Gate 1B; R8C-D12.
- **Stop condition:** Capture executed without the corrected flag set and absolute path.
- **Status:** `CORRECTION PROPOSED`

### R8C-D08 — R8A-13 mapped to different gates in R8-A and R8-B
- **Severity:** P1
- **Affected:** REGISTER §4 (Gate 1B / Gate 5) vs MATRIX §3 (Gate 0-GIT)
- **Current statement:** Two governing documents assign the Stage 1F preservation item to different gates.
- **Evidence:** Direct text comparison of the two mapping tables.
- **Category:** Contradictory gate mapping (Audit F).
- **Consequence:** Ambiguity about when preservation is mandatory; an operator could treat 0-GIT as skippable or as blocking the wrong gate.
- **Required correction:** Single authoritative mapping (recommend: preservation mandatory before Gate 1B; 0-GIT executes before 1B per R8C-D04).
- **Owner approval required:** No (once D04 is decided).
- **Dependencies:** R8C-D04.
- **Stop condition:** Conflicting mappings in circulation.
- **Status:** `CORRECTION PROPOSED`

### R8C-D09 — Rehearsal database creation/cleanup not separately authorized or evidenced
- **Severity:** P1
- **Affected:** PLAN §7.4 (criteria 1 and 5); MATRIX §2 Gate 1C
- **Current statement:** "Temporary schema is cleanly dropped after audit" is an acceptance criterion inside Gate 1C.
- **Evidence:** `CREATE DATABASE` and `DROP DATABASE` on the shared MySQL server are state-changing operations; Policy A3 requires separate authorization; no drop-evidence (post-drop `SHOW DATABASES`-class verification) is required; no record of rehearsal-DB contents before drop.
- **Category:** Authorization-gap / cleanup governance (Audit C).
- **Consequence:** Untracked state changes on the shared server; no evidence trail for the rehearsal lifecycle.
- **Required correction:** Split Gate 1C into sub-authorizations (create / restore / audit / drop), each with recorded approval and evidence; require archived audit results before the drop is authorized.
- **Owner approval required:** Yes (authorization structure change).
- **Dependencies:** R8C-D03.
- **Stop condition:** Rehearsal DB dropped without recorded evidence and separate authorization.
- **Status:** `CORRECTION PROPOSED`

### R8C-D10 — Gate 2D "Conditional" yet listed as required for Gate 3
- **Severity:** P1
- **Affected:** MATRIX §2 (Gate 2D row "Conditional"; Gate 3 row dependencies "…2C, 2D", preconditions "All prior gates (1A through 2D) PASSED"); REGISTER R8A-09 (decision pending)
- **Current statement:** Gate 3 cannot pass unless Gate 2D passed, but Gate 2D execution is an unresolved owner decision.
- **Evidence:** Direct text contradiction; R8A-09 status `RESOLVED STATICALLY / DECISION PENDING`.
- **Category:** Contradictory dependencies (Audit A item 7).
- **Consequence:** Deadlock (owner skips 2D → Gate 3 unreachable) or implicit pressure to run a WP-bootstrap execution that was meant to be optional.
- **Required correction:** Define the 2D-skipped path explicitly: Gate 3 dependencies become "1A, 1B, 1C, 2A, 2B, 2C + (2D if mandated by owner decision per R8A-09)".
- **Owner approval required:** Yes (this is exactly the pending R8A-09 decision).
- **Dependencies:** R8A-09.
- **Stop condition:** Gate 3 review conducted with ambiguous 2D status.
- **Status:** `OWNER_DECISION_REQUIRED`

---

## P2 — Documentation and Consistency Defects

### R8C-D11 — Disk-space threshold contradiction (1 GB vs 2 GB) and "static" mislabel
- **Severity:** P2
- **Affected:** REGISTER §3 R8A-02 ("disk space < 1 GB free") vs PLAN §7.1 ("> 2 GB") vs R7-MATRIX line 112 ("minimum 1 GB"); PLAN §7.1 ("statically confirmed")
- **Evidence:** Three documents, two thresholds; free disk space is a runtime measurement, not static evidence.
- **Category:** Contradiction / evidence-classification error.
- **Consequence:** Operator applies the wrong threshold; A4 tension ("statically confirmed" runtime value).
- **Required correction:** One authoritative threshold (recommend > 2 GB), recorded once, measured at Gate 1B as runtime evidence.
- **Owner approval required:** No.
- **Stop condition:** Two thresholds in force simultaneously.
- **Status:** `CORRECTION PROPOSED`

### R8C-D12 — Hardcoded "exactly 52 tables" stop condition
- **Severity:** P2
- **Affected:** PLAN §7.4 criterion 1, §8 Gate 1C; MATRIX §2 Gates 1C/2C
- **Evidence:** 52 has static provenance (52 `CREATE TABLE` statements in each historical dump — verified) but the live count is `RUNTIME_REQUIRED` (INVENTORY §11.2). A fixed constant fails spuriously if the schema legitimately changes.
- **Category:** Unsupported fixed threshold (Audit F).
- **Consequence:** False gate failure (safe direction) or, if the live schema lost tables, a matching-count false pass; either way the criterion is not derived from evidence.
- **Required correction:** Derive expected table count from the T-0 dump's own `CREATE TABLE` count at capture time; cross-check against the live count at Gate 2C; use the derived value as the 1C criterion.
- **Owner approval required:** No.
- **Dependencies:** R8C-D07.
- **Status:** `CORRECTION PROPOSED`

### R8C-D13 — Size threshold not demoted; text-search limits; evidence ladder undefined
- **Severity:** P2
- **Affected:** PLAN §7.3 checks 3–5; MATRIX §2 Gates 1B/1C
- **Evidence:** R8-A §6 itself found "minimum dump size > 1.2 MB proves valid backup — CONTRADICTED", yet R8-B retains "> 1.2 MB" as a gate criterion without the necessary-but-insufficient caveat. The `USE |CREATE DATABASE ` scan does not cover `DROP DATABASE`, `RENAME`, `GRANT`, `SET GLOBAL`, `DEFINER`, or whitespace/backtick variants. The ladder `STATIC_CONTENT_REVIEW → CAPTURE_RESULT_VERIFIED → ISOLATED_RESTORE_VERIFIED → RECOVERY_READINESS_ACCEPTED` appears nowhere; nothing states that only Gate 1C completion plus owner sign-off yields `RECOVERY_READINESS_ACCEPTED`.
- **Category:** Weak verification criteria / missing evidence-level discipline (Audit B).
- **Consequence:** A captured dump could be treated as recovery-ready without a rehearsal; destructive statements other than `USE` could evade inspection.
- **Required correction:** Demote size check to advisory; extend the safety scan pattern set; define the four evidence levels and map each gate output to exactly one level; state that a SHA-256 digest + successful exit code prove capture, never restorability.
- **Owner approval required:** No.
- **Dependencies:** Gate 1B/1C; R8C-D07.
- **Status:** `CORRECTION PROPOSED`

### R8C-D14 — `docs/` vs `plugins/docs/` path ambiguity
- **Severity:** P2
- **Affected:** Mission file list; all "Expected Artifact" paths in REGISTER/PLAN/MATRIX; INVENTORY §2.2
- **Evidence:** No R8-A/R8-B documents exist under `wp-content/docs/`; the governing corpus lives in `app/public/wp-content/plugins/docs/` (81 files verified). Both R8-C outputs were therefore written to `plugins/docs/`.
- **Category:** Path convention defect.
- **Consequence:** Future gate artifacts could be filed in the wrong directory and be unfindable at review time.
- **Required correction:** Declare `plugins/docs/` the single authoritative location and normalize all artifact paths.
- **Owner approval required:** No.
- **Status:** `CORRECTION PROPOSED`

### R8C-D15 — Post-recovery gate re-entry undefined
- **Severity:** P2
- **Affected:** PLAN §8 (Mermaid: `G4R_SW → G1A`, `G4R_DR → G1A`)
- **Evidence:** Nothing specifies whether Gates 1B–2D — including a fresh T-0, since a 4R-DR restore consumes the original — must be re-executed before a Gate 4 retry.
- **Category:** Ambiguous workflow semantics.
- **Consequence:** A retry could proceed against a stale or consumed recovery point.
- **Required correction:** State explicitly: after any 4R pathway, the pipeline re-enters at Gate 1A and **all** of 1B–3 must be re-executed, including fresh T-0 capture and rehearsal.
- **Owner approval required:** No.
- **Status:** `CORRECTION PROPOSED`

### R8C-D16 — Cron-pause mechanism unspecified; T-0 captured before freeze
- **Severity:** P2
- **Affected:** MATRIX §2 Gate 2A ("pause WordPress cron"); PLAN §7.1 ordering (T-0 before freeze); REGISTER R8A-06
- **Evidence:** `wp-config.php` does not define `DISABLE_WP_CRON` (verified via INVENTORY §5 and hash match); the mechanism (option vs config constant) is unspecified — a `wp-config.php` edit is itself an A3-authorized configuration change. T-0 (1B) precedes freeze (2A), so concurrent writers are active during capture.
- **Category:** Unspecified operational mechanism.
- **Consequence:** Ambiguous authorization scope; background writes during dump capture (mitigated by `--single-transaction` on all-InnoDB but never discussed).
- **Required correction:** Specify the cron-pause mechanism and its authorization path; document concurrent-write risk during T-0 capture.
- **Owner approval required:** No.
- **Status:** `CORRECTION PROPOSED`

### R8C-D17 — Pre-Gate PASS wording could be misread as full-inventory PASS
- **Severity:** P2
- **Affected:** MATRIX §2 Pre-Gate row ("zero missing code artifacts"; status `PASS`) vs PLAN §4 (lists `MISSING` artifacts)
- **Evidence:** The PASS is scoped to code artifacts, but the same plan lists missing non-code artifacts; the scoping is not labeled.
- **Category:** Ambiguous status wording.
- **Consequence:** Misreading that the evidence inventory is fully complete.
- **Required correction:** Rename scope: "Pre-Gate (code artifacts only) — PASS; non-code artifacts (T-0 dump, manifests) MISSING."
- **Owner approval required:** No.
- **Status:** `CORRECTION PROPOSED`

### R8C-D18 — "14 untracked Stage 1F files" not reproducible
- **Severity:** P2
- **Affected:** INVENTORY §13 item 4
- **Evidence:** Read-only `git status` (this review): 3 modified theme files + **9 untracked theme files** (3 templates, 2 parts, 4 images) = 12 Stage 1F files; the "14" figure is not reproducible from the working tree. (Many untracked `docs/` files exist but are not Stage 1F theme assets.)
- **Category:** Documentation count discrepancy.
- **Consequence:** Future preservation work could target an incorrect file list.
- **Required correction:** Recount and pin the exact 12-file Stage 1F list (with digests — supplied by R8-C review §2.3) as the authoritative preservation manifest.
- **Owner approval required:** No.
- **Status:** `CORRECTION PROPOSED`

### R8C-D19 — Proposed commands embed unverified connection assumptions
- **Severity:** P2
- **Affected:** PLAN §7.2, §7.4 (`-u root`, `127.0.0.1:10011` hardcoded)
- **Evidence:** R8A-01 (identity) and R8A-08 (privileges) are OPEN; no runtime evidence yet supports the embedded assumptions.
- **Category:** Premature command specification (Audit A item 6).
- **Consequence:** Commands could be copy-executed before target/privileges are established.
- **Required correction:** Mark all commands `PROPOSED SPECIFICATION — PARAMETERS UNVERIFIED UNTIL GATE 1A`; parameterize host/port/credentials; resolve from Gate 1A evidence.
- **Owner approval required:** No.
- **Dependencies:** R8A-01, R8A-08, R8C-D03.
- **Status:** `CORRECTION PROPOSED`

### R8C-D20 — "11 queries" suite never enumerated
- **Severity:** P2
- **Affected:** MATRIX §2 Gate 2C ("All 11 queries pass"); REGISTER R8A-04
- **Evidence:** No document lists the 11 queries; the count is asserted without definition.
- **Category:** Unverifiable acceptance specification.
- **Consequence:** An uninspectable suite cannot be reviewed or approved; the count could drift.
- **Required correction:** Publish the exact 11 queries with per-query expected results in the Gate 2C runbook; include the metric set from R8C-D05.
- **Owner approval required:** No.
- **Status:** `CORRECTION PROPOSED`

---

## Summary Table

| ID | Severity | Category | Document/Section | Status |
|---|---|---|---|---|
| R8C-D01 | P0 | Evidence integrity (A4 pattern) | PLAN §10 vs REGISTER R8A-10 | `OWNER_DECISION_REQUIRED` |
| R8C-D02 | P0 | Unsafe recovery instruction | PLAN §9.3, §8 | `OWNER_DECISION_REQUIRED` |
| R8C-D03 | P0 | Isolation/target safety | PLAN §7.4 | `CORRECTION PROPOSED` |
| R8C-D04 | P1 | Gate dependency logic | MATRIX §2, §4.1 | `OWNER_DECISION_REQUIRED` |
| R8C-D05 | P1 | Ambiguous acceptance criteria | MATRIX Gate 4/5; PLAN §8 | `CORRECTION PROPOSED` |
| R8C-D06 | P1 | Verification-method defect | PLAN §7.3, §5.1 | `CORRECTION PROPOSED` |
| R8C-D07 | P1 | Incomplete dump specification | PLAN §7.2–7.3 | `CORRECTION PROPOSED` |
| R8C-D08 | P1 | Contradictory gate mapping | REGISTER §4 vs MATRIX §3 | `CORRECTION PROPOSED` |
| R8C-D09 | P1 | Authorization gap (A3) | PLAN §7.4 | `CORRECTION PROPOSED` |
| R8C-D10 | P1 | Contradictory dependencies | MATRIX §2 Gate 2D/3 | `OWNER_DECISION_REQUIRED` |
| R8C-D11 | P2 | Contradiction / classification | REGISTER vs PLAN §7.1 | `CORRECTION PROPOSED` |
| R8C-D12 | P2 | Unsupported fixed threshold | PLAN §7.4; MATRIX 1C/2C | `CORRECTION PROPOSED` |
| R8C-D13 | P2 | Weak verification criteria | PLAN §7.3 | `CORRECTION PROPOSED` |
| R8C-D14 | P2 | Path convention | Corpus-wide | `CORRECTION PROPOSED` |
| R8C-D15 | P2 | Ambiguous workflow semantics | PLAN §8 | `CORRECTION PROPOSED` |
| R8C-D16 | P2 | Unspecified mechanism | MATRIX Gate 2A; PLAN §7.1 | `CORRECTION PROPOSED` |
| R8C-D17 | P2 | Ambiguous status wording | MATRIX Pre-Gate | `CORRECTION PROPOSED` |
| R8C-D18 | P2 | Count discrepancy | INVENTORY §13 | `CORRECTION PROPOSED` |
| R8C-D19 | P2 | Premature command spec | PLAN §7.2, §7.4 | `CORRECTION PROPOSED` |
| R8C-D20 | P2 | Unverifiable acceptance spec | MATRIX Gate 2C | `CORRECTION PROPOSED` |

**Counts by severity:** P0 = 3 · P1 = 7 · P2 = 10 · Total = 20
**Counts by status:** `OWNER_DECISION_REQUIRED` = 4 · `CORRECTION PROPOSED` = 16 · `OPEN` = 0 · `CLOSED_WITH_EVIDENCE` = 0

**Invariant declaration:** No defect in this register has been marked closed. R8-C is a review stage; R8-B documents were not modified. All P0 defects remain unresolved.

---

**GATE 1A REMAINS NOT AUTHORIZED. GATE 4 REMAINS STRICTLY BLOCKED. NO LATER GATE MAY START AUTOMATICALLY.**
