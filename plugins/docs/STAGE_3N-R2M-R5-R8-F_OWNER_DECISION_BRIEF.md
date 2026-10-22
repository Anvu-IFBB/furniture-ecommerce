# STAGE 3N-R2M-R5-R8-F: PROJECT OWNER DECISION BRIEF
## NEUTRAL GOVERNANCE, RISK, AND POLICY BRIEFING

**Project:** ATELIER — WordPress / WooCommerce Furniture E-commerce  
**Workspace:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\`  
**WordPress Root:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\`  
**Canonical Governance Root:** `app/public/wp-content/plugins/docs/`  
**Document Path:** `app/public/wp-content/plugins/docs/STAGE_3N-R2M-R5-R8-F_OWNER_DECISION_BRIEF.md`  
**Mode:** STRICTLY STATIC DOCUMENTATION & DECISION BRIEFING ONLY  
**Current Gate 1A Status:** **NOT AUTHORIZED**  
**Current Gate 4 Status:** **STRICTLY BLOCKED**  
**Briefing Date:** 2026-10-21  

---

## 1. Purpose & How to Read This Brief

This briefing is prepared exclusively for the **Project Owner**. Its purpose is to present the four required governance decisions in a neutral, objective manner, clearly outlining the options, trade-offs, consequences, and strict non-authorization boundaries for each.

### 1.1 Fundamental Governance Principles
1. **AI Proposes; Owner Decides:** The technical team and safety reviewers have resolved all 20 documentation design defects. However, release engineering policies require explicit Owner ratification before operational gates can be requested.
2. **A Decision is NOT an Execution Authorization:** Formally approving a policy, framework, or gate sequence **DOES NOT** authorize running any command, connecting to any database, making any network request, or importing any data.
3. **No Automatic Progression:** Each operational step requires separate, prior, explicit written authorization.

---

## 2. Executive Decision Ledger

```
┌───────────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                      EXECUTIVE OWNER DECISION SUMMARY                                 │
├────────────────────┬──────────────────────────────────────┬───────────────────────┬───────────────────┤
│ Decision ID        │ Title                                │ Nature of Decision    │ Downstream Effect │
├────────────────────┼──────────────────────────────────────┼───────────────────────┼───────────────────┤
│ OWNER-DECISION-01  │ Recovery Protocol Formal Ratification│ Formal Policy Adoption│ Unblocks Gate 3   │
│ OWNER-DECISION-02  │ Lost-Change Recovery Triage Framework│ Risk & Data Governance│ Unblocks Gate 4R  │
│ OWNER-DECISION-03  │ Gate 1A / Gate 0-GIT Decoupling      │ Technical Sequencing  │ Unblocks Gate 1A  │
│ OWNER-DECISION-04  │ Gate 2D Branch Selection             │ Verification Scope    │ Defines Gate 3 Pre│
└────────────────────┴──────────────────────────────────────┴───────────────────────┴───────────────────┘
```

---

## 3. Detailed Decision Briefs

---

### Decision 1: Recovery Protocol Formal Ratification (`OWNER-DECISION-01`)
- **Associated Defect:** `R8C-D01` (Recovery protocol status integrity).
- **Current Status:** `OWNER_DECISION_REQUIRED`.
- **Nature of Decision:** Formal Policy Choice (Governance Protocol).

#### What the Owner is Being Asked to Decide
The Owner is asked to formally ratify the lifecycle and specification of the recovery protocol governing catalog import failures, advancing its governance status from `PROTOCOL DRAFTED / TECHNICALLY REVIEWED` to `OWNER APPROVED`. The protocol is fully specified in Section 7 of [`STAGE_3N-R2M-R5-R8-D_CORRECTED_PREREQUISITE_CLOSURE_PLAN.md`](file:///c:/Users/Vu%20Cong%20Minh/Local%20Sites/furniture-ecommerce/app/public/wp-content/plugins/docs/STAGE_3N-R2M-R5-R8-D_CORRECTED_PREREQUISITE_CLOSURE_PLAN.md).

#### Available Options & Consequences
- **Option 1A: Ratify the Proposed 4-Pathway Recovery Protocol (Recommended):**
  - *Consequence:* Formally adopts the 5-stage lifecycle (`POLICY LOCKED` → `PROTOCOL DRAFTED` → `TECHNICALLY REVIEWED` → `OWNER APPROVED` → `OPERATIONALLY EXECUTED`) and codifies the four recovery pathways (Software Rollback, Forensic Read-Only, DBA SQL, and Snapshot Restore). Satisfies Policy D4 precondition for Gate 3.
  - *Risk:* None. Codifies conservative incident response without executing anything.
- **Option 1B: Reject or Request Amendments to the Protocol:**
  - *Consequence:* The recovery protocol remains `OPERATIONAL PROTOCOL OPEN`. 
  - *Risk:* Gate 3 remains strictly blocked. Gate 4 import cannot be considered until a ratified recovery protocol exists.

#### Dependencies & Blocked States
- **Blocks:** Gate 3 (Pre-Import Readiness Checkpoint) and Gate 4 (Live Import).
- **Does NOT Block:** Gate 1A (Read-Only DB Identity) or Gate 0-GIT (Asset Locking).

#### Strict Non-Authorization Boundary
- **What this decision does NOT authorize:** Ratifying this protocol **DOES NOT** authorize taking a database snapshot, running an import, performing a rollback, executing SQL queries, or restoring any database.

---

### Decision 2: Window 2 Lost-Change Recovery Loss Framework (`OWNER-DECISION-02`)
- **Associated Defect:** `R8C-D02` (Unsafe assumption about T-0 snapshot restore).
- **Current Status:** `OWNER_DECISION_REQUIRED`.
- **Nature of Decision:** Risk & Data Governance Policy.

#### What the Owner is Being Asked to Decide
The Owner is asked to formally adopt the policy rule that **restoring a T-0 database snapshot is NOT an unconditional default or harmless operation**, and to require a mandatory **Lost-Change Impact Analysis** before any destructive recovery is ever authorized.

#### Available Options & Consequences
- **Option 2A: Adopt the Lost-Change Recovery Triage Framework (Recommended):**
  - *Consequence:* In the event of an import interruption or crash, the team is forbidden from blindly overwriting the database with the pre-import snapshot. The team must first: (1) Preserve incident crash logs, (2) Query the database to identify legitimate changes that occurred since T-0 (e.g. Action Scheduler jobs, user options, customer sessions, admin edits), (3) Evaluate non-destructive Software Rollback and DBA SQL first, and (4) Obtain separate, incident-specific Owner authorization before any snapshot restore.
  - *Risk:* Requires structured triage during an incident rather than immediate one-click restore. However, it guarantees zero accidental data loss of legitimate changes.
- **Option 2B: Retain Automatic Full T-0 Restore as Primary Default:**
  - *Consequence:* An import failure immediately triggers a full database wipe and restore of the pre-import snapshot.
  - *Risk:* **CRITICAL / IRREVERSIBLE DATA LOSS**. All concurrent orders, background jobs, user preferences, and admin edits created between T-0 and the incident are permanently destroyed without prior analysis. Violates Policy B4 and C5 principles.

#### Dependencies & Blocked States
- **Blocks:** Gate 4R (Recovery Governance) and Gate 3.
- **Does NOT Block:** Gate 1A or Gate 0-GIT.

#### Strict Non-Authorization Boundary
- **What this decision does NOT authorize:** Adopting this framework **DOES NOT** authorize modifying, dumping, restoring, or dropping any database.

---

### Decision 3: Gate 1A / Gate 0-GIT Technical Sequencing (`OWNER-DECISION-03`)
- **Associated Defect:** `R8C-D04` (State-changing Gate 0-GIT gating read-only Gate 1A).
- **Current Status:** `OWNER_DECISION_REQUIRED`.
- **Nature of Decision:** Technical Workflow Sequencing Choice.

#### What the Owner is Being Asked to Decide
The Owner is asked to approve decoupling **Gate 1A** (a read-only database identity query) from **Gate 0-GIT** (a state-changing Git commit and tag), allowing the team to inspect database connection parameters before committing theme files to Git.

#### Available Options & Consequences
- **Option 3A: Approve Decoupling (Sequence: Gate 1A → Gate 0-GIT → Gate 1B) (Recommended):**
  - *Consequence:* Gate 1A is treated as purely read-only observation (`SELECT DATABASE()`, port, socket). It requires zero Git changes. Gate 0-GIT remains an absolute mandatory prerequisite before any *state-changing* operations (Gate 1B dump, Gate 1C rehearsal, Gate 4 import).
  - *Risk:* None. Read-only database queries cannot mutate filesystem theme files. Aligns with standard release engineering principles.
- **Option 3B: Retain Strict Coupling (Sequence: Gate 0-GIT → Gate 1A → Gate 1B):**
  - *Consequence:* The operator is forced to execute a Git commit and tag (`Gate 0-GIT`) before even verifying whether the local MySQL database is reachable or has the correct name.
  - *Risk:* Forces unnecessary Git mutations and repository commits if the database environment is down or improperly configured, creating orphaned commits.

#### Dependencies & Blocked States
- **Blocks:** Requesting authorization for Gate 1A.
- **Does NOT Block:** Review of documentation or static evidence.

#### Strict Non-Authorization Boundary
- **What this decision does NOT authorize:** Approving the sequence (Option 3A) **DOES NOT** authorize executing Gate 1A. A separate, formal execution request (`OWNER_GATE_1A_READONLY_AUTH`) must be presented to and signed by the Owner before any database connection is initiated.

---

### Decision 4: Gate 2D Verification Branch Selection (`OWNER-DECISION-04`)
- **Associated Defect:** `R8C-D10` (Gate 2D conditional vs Gate 3 prerequisite contradiction).
- **Current Status:** `OWNER_DECISION_REQUIRED`.
- **Nature of Decision:** Scope & Verification Methodology Selection.

#### What the Owner is Being Asked to Decide
The Owner is asked to choose the verification pathway between the static/dry-run checks (Gate 2B/2C) and the final readiness review (Gate 3).

#### Available Options & Consequences
- **Branch A: Headless / Direct SQL Preflight Path (Skip Gate 2D) (Recommended):**
  - *Specification:* Pre-import verification is satisfied entirely through CLI dry-run ingestion (`Gate 2B`) and the 11-query non-destructive SQL diagnostic suite (`Gate 2C`). Gate 2D (live HTTP storefront smoke test) is formally bypassed via Owner waiver.
  - *Consequence:* The web server and PHP-FPM are not probed over HTTP prior to import. Eliminates risks of frontend cache warming or session churn. Gate 3 requires: `PASS(2B) + PASS(2C) + OWNER_BRANCH_A_WAIVER`.
  - *Trade-off:* Does not verify storefront HTTP 200 responses until post-import Gate 5.
- **Branch B: Full End-to-End Storefront Verification (Mandate Gate 2D):**
  - *Specification:* Prior to Gate 3, the team must execute read-only HTTP GET requests against `/shop/` and the WooCommerce REST API (`Gate 2D`).
  - *Consequence:* Confirms web server and REST routing are functional before catalog import. Gate 3 requires: `PASS(2B) + PASS(2C) + PASS(2D)`.
  - *Trade-off:* Requires running web server processes and network probes prior to import authorization.

#### Dependencies & Blocked States
- **Blocks:** Definition of Gate 3 prerequisite dossier requirements.
- **Does NOT Block:** Gates 1A, 0-GIT, 1B, 1C, 2A, 2B, or 2C.

#### Strict Non-Authorization Boundary
- **What this decision does NOT authorize:** Selecting Branch A or Branch B **DOES NOT** authorize sending HTTP requests, invoking WP-CLI, executing SQL queries, or running the importer.

---

## 4. Summary Table of Decision Parameters

| Decision ID | Recommended Option | Nature of Action | Requires Code Changes? | Authorizes Operational Execution? |
|:---|:---:|:---:|:---:|:---:|
| **OWNER-DECISION-01** | Option 1A (Ratify Protocol) | Policy Ratification | **NO** | **STRICTLY NO** |
| **OWNER-DECISION-02** | Option 2A (Adopt Lost-Change) | Risk Policy | **NO** | **STRICTLY NO** |
| **OWNER-DECISION-03** | Option 3A (Decouple 1A/0-GIT) | Sequence Approval | **NO** | **STRICTLY NO** |
| **OWNER-DECISION-04** | Branch A (Skip 2D / SQL Only) | Scope Selection | **NO** | **STRICTLY NO** |

---

## 5. Formal Project Owner Action Block

The Project Owner may indicate decisions using the following neutral response template:

```markdown
### PROJECT OWNER GOVERNANCE RECORD
1. OWNER-DECISION-01 (Recovery Protocol): [OPTION 1A - RATIFY] / [OPTION 1B - AMEND]
2. OWNER-DECISION-02 (Lost-Change Framework): [OPTION 2A - ADOPT] / [OPTION 2B - DEFAULT T0]
3. OWNER-DECISION-03 (Gate 1A Sequencing): [OPTION 3A - DECOUPLE] / [OPTION 3B - STRICT COUPLING]
4. OWNER-DECISION-04 (Gate 2D Verification Scope): [BRANCH A - SQL/CLI ONLY] / [BRANCH B - MANDATE 2D HTTP]

NOTE: Signing this record ratifies policy and governance only.
It does NOT authorize Gate 1A, Gate 1B, Gate 1C, Gate 2, Gate 3, or Gate 4.
```

---
*End of Stage 3N-R2M-R5-R8-F Project Owner Decision Brief.*
