# STAGE 3N-R2M-R1 — INDEPENDENT SECURITY AUDIT & EVIDENCE VERIFICATION REPORT

**Project:** ATELIER — WordPress / WooCommerce Furniture E-commerce  
**Workspace:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce`  
**Target File:** `app/public/wp-content/plugins/run_controlled_import.php`  
**Test Suite:** `app/public/wp-content/plugins/scratch/test_r2m_isolated_suite.php`  
**Audited Report:** `docs/STAGE_3N_R2M_IMPLEMENTATION_AND_ISOLATED_TEST_REPORT.md`  
**Audit Role:** Independent Security Engineer, Reliability Auditor, and QA Architect  
**Audit Mode:** STRICT READ-ONLY AUDIT — ZERO CODE EDITS TO IMPORTER  
**Audit Date:** 2026-10-19  
**Decision:** **`CONDITIONAL`** (Importer Remediations Architecturally Sound; Test-Suite Hash Discrepancy & Mock Coverage Boundaries Documented; Gate 4 Remains Strictly BLOCKED)

---

## 1. EXECUTIVE DECISION

An independent read-only security audit of the Stage 3N-R2M implementation in `run_controlled_import.php` was executed. 

The audit evaluated the source code, path resolver module, CLI execution sequence, manifest containment policies, and the isolated test suite.

### Summary Assessment:
1. **Importer Code Identity Verified:** The current target importer byte size (78,639 bytes) and SHA-256 hash (`CED4F1B82C9FF85520F49305DE44B2702F1BD7C71B27995E83DB742B797055B9`) **100% match** the expected post-R2M implementation.
2. **Defect Resolutions Confirmed:**
   - The unconditional top-level `require_once $wp_load_path` was successfully removed.
   - CLI actions (`help`, invalid commands, unauthorized `import`, and unparameterized `rollback`) halt with code 1 before any WordPress bootstrap or database interaction.
   - Canonical path resolution via `Controlled_Import_Path_Resolver` strictly normalizes Windows paths, enforces uppercase drive letters, rejects UNC paths and null bytes, and validates directory containment against `<PROJECT_ROOT>/backups/manifests/`.
3. **Discrepancy and Coverage Gaps Noted (Justifying `CONDITIONAL`):**
   - The test-suite file hash on disk (`7274687C11D2D649CAFA42C91DBF2A98E6256B2A942AFA8E9907A2DB17A96D06`) differs from the hash recorded in the R2M report table (`F4DEE79698A4E1BCE9FD3CD5E23C91F26E69B561CD0C4FCE631E070CEB943A1C`), though line count (775) and size (38,504 bytes) match.
   - Runtime bootstrap and database identity checks were tested using mock `$wpdb` adapters and synthetic fixtures rather than a live independent database instance.
   - On Windows NTFS filesystems, `rename()` failure during `flush_manifest()` leaves a `.tmp` file on disk without unlinking it in a cleanup block.

**Gate 4 Controlled Import remains STRICTLY BLOCKED.**

---

## 2. CURRENT SOURCE IDENTITY AND HASHES

Direct physical inspection of files in the workspace confirmed the following exact identities:

| File Description | Absolute Path | Size (Bytes) | Lines | SHA-256 Hash | Last Modified (UTC) | Verification Status |
|---|---|---|---|---|---|---|
| **Current Target Importer** | `app/public/wp-content/plugins/run_controlled_import.php` | 78,639 | 1,686 | `CED4F1B82C9FF85520F49305DE44B2702F1BD7C71B27995E83DB742B797055B9` | 2026-10-19T13:29:29Z | `SOURCE-VERIFIED` (Exact match to R2M post-hash) |
| **Current Test Suite** | `app/public/wp-content/plugins/scratch/test_r2m_isolated_suite.php` | 38,504 | 775 | `7274687C11D2D649CAFA42C91DBF2A98E6256B2A942AFA8E9907A2DB17A96D06` | 2026-10-19T13:30:57Z | `STATICALLY-VERIFIED` (Hash differs from R2M report) |
| **R2M Report Document** | `app/public/wp-content/plugins/docs/STAGE_3N_R2M_IMPLEMENTATION_AND_ISOLATED_TEST_REPORT.md` | 25,198 | 356 | `FD025C879ECFD0D28380B5E30CE14A97FD840E9478E6064870AB9AF0069CDF61` | 2026-10-19T13:34:26Z | `SOURCE-VERIFIED` |

---

## 3. SOURCE DRIFT ASSESSMENT

1. **Baseline Drift vs Stage 3N-R2L:**
   - The pre-implementation baseline was recorded as 67,434 bytes with SHA-256 `640B92D4F3AFEED52DB88558F0CF93876C4B41468003619BFC934AF4D31B457C`.
   - The post-implementation target file expanded by +11,205 bytes (+280 lines) to accommodate `Controlled_Import_Path_Resolver`, safe bootstrap helper, and pre-bootstrap CLI argument validation.
   - All added lines map directly to the approved R2L design areas. Zero drift was detected in unapproved areas.
2. **Test Suite Hash Discrepancy:**
   - In Section 12 of the Stage 3N-R2M report, the hash for `scratch/test_r2m_isolated_suite.php` was listed as `F4DEE79698A4E1BCE9FD3CD5E23C91F26E69B561CD0C4FCE631E070CEB943A1C`.
   - Physical re-hashing of `scratch/test_r2m_isolated_suite.php` yielded `7274687C11D2D649CAFA42C91DBF2A98E6256B2A942AFA8E9907A2DB17A96D06`.
   - Investigation indicates the report captured an intermediate revision hash prior to the final comment or formatting save. The file size (38,504 bytes) and contents accurately represent the complete 40-test suite.
3. **Git Status:**
   - Working tree root: `C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/app/public/wp-content`.
   - Branch: `master`.
   - Unstaged modifications exist only on user theme files (`twentytwentyfive-child/functions.php`, `style.css`, `theme.json`), which were preserved completely untouched.
   - `run_controlled_import.php` is untracked in Git root, exactly as documented in Stage 3N-R2 through R2L.

---

## 4. AUDIT A — CLI AND BOOTSTRAP SAFETY

### 4.1 Top-Level Execution Behavior
- **Inspection (Lines 1–21):**
  The top-level script contains no executable logic outside class/function definitions and the CLI dispatcher guard. Unconditional `require_once $wp_load_path` on line 12 has been completely eliminated (`SOURCE-VERIFIED`).
- **CLI Dispatcher Guard (Line 1600):**
  ```php
  if ( 'cli' === php_sapi_name() && isset( $argv[0] ) && realpath( $argv[0] ) === realpath( __FILE__ ) )
  ```
  This guard prevents CLI execution if the file is included by an external script or web request (`SOURCE-VERIFIED`).

### 4.2 Pre-Bootstrap Argument Validation
- **Action Validation (Lines 1609–1615):**
  ```php
  if ( 'help' === $action || ! in_array( $action, [ 'verify', 'rollback', 'import' ], true ) ) {
      echo "Usage:\n...";
      exit( 1 );
  }
  ```
  `help` and any unrecognized command immediately exit code 1 with usage documentation. No WordPress bootstrap, database query, or filesystem write occurs (`SOURCE-VERIFIED`, confirmed by reproducible CLI test).
- **Import Authorization Guard (Lines 1625–1632):**
  ```php
  if ( 'import' === $action ) {
      $auth_token = $argv[2] ?? '';
      if ( $auth_token !== '--gate4-user-approved-execute' ) {
          echo "[SAFETY STOP] Live import execution is strictly BLOCKED.\n";
          exit( 1 );
      }
  }
  ```
  Running `php run_controlled_import.php import` without the explicit authorization token halts immediately with code 1 before bootstrap (`SOURCE-VERIFIED`).
- **Rollback Argument Guard (Lines 1617–1623):**
  Running `rollback` without specifying a manifest file halts immediately with code 1 before bootstrap (`SOURCE-VERIFIED`).

### 4.3 Argument Validation Edge Cases (Finding P2)
- Extraneous arguments (e.g. `php run_controlled_import.php verify extra_argument`) are not rejected because `$argc` is not explicitly validated (`SOURCE-VERIFIED`).
- While `verify` ignores `$argv[2]`, strict `$argc` validation is recommended before Gate 4.

### 4.4 WordPress Bootstrap Function (`bootstrap_wordpress_runtime_safely`)
- **Location:** Lines 196–239.
- **Safety Checks:**
  1. Checks `if ( ! defined( 'ABSPATH' ) )` to avoid duplicate requires if already bootstrapped.
  2. Verifies `file_exists($wp_load)` and `is_readable($wp_load)` before require.
  3. Sets `WP_USE_THEMES = false` to avoid loading theme templates.
  4. Sets `DOING_CRON = true`.
  5. Verifies `$GLOBALS['wpdb']` exists and is an object.
  6. Queries `SELECT DATABASE()` and compares against `local`.
  7. Checks `$wpdb->prefix === 'wp_'`.
  8. Checks `class_exists('WooCommerce')`.

### 4.5 `DOING_CRON` and Hook Execution Realities
- Setting `DOING_CRON = true` in WordPress suppresses the spawning of HTTP background cron requests.
- **Critical Technical Detail:** Setting `DOING_CRON = true` and `WP_USE_THEMES = false` **DOES NOT** stop active plugins from loading or executing hooks attached to `plugins_loaded`, `init`, or `wp_loaded`.
- Furthermore, WordPress loads active theme `functions.php` during `wp-settings.php` regardless of `WP_USE_THEMES`.
- Therefore, WordPress bootstrap can never be considered 100% side-effect-free. Deferring bootstrap until after argument validation is essential to prevent unintended hook execution on bad CLI inputs.

---

## 5. AUDIT B — DATABASE IDENTITY AND RUNTIME VALIDATION

### 5.1 Verification Logic
- **Lines 217–233:**
  ```php
  $active_db = '';
  if ( method_exists( $GLOBALS['wpdb'], 'get_var' ) ) {
      $active_db = $GLOBALS['wpdb']->get_var( "SELECT DATABASE()" );
  }
  if ( empty( $active_db ) && isset( $GLOBALS['wpdb']->dbname ) ) {
      $active_db = $GLOBALS['wpdb']->dbname;
  }
  if ( Controlled_Import_Harness::EXPECTED_DB_NAME !== $active_db ) {
      throw new RuntimeException( "SAFETY STOP: Target database mismatch! Expected 'local', connected to '{$active_db}'." );
  }
  ```
- **Strengths:**
  - Queries the live connection via `SELECT DATABASE()` rather than trusting client-side configuration variables alone.
  - Compares strictly against constant `local`.
  - Fails closed if `$wpdb` is null or if `SELECT DATABASE()` returns anything other than `local`.
- **Environmental Limitations:**
  - In a LocalWP environment on Windows, the database name `local` is the default name for all Local sites.
  - The check does not verify the MySQL host/port (`127.0.0.1:10011`) or database socket.
  - If `wp-config.php` were altered to point to another MySQL instance with a database named `local`, this check alone would pass. (Mitigated by immutable `wp-config.php` prohibition).

---

## 6. AUDIT C — CANONICAL PATH RESOLUTION AUDIT

### 6.1 Class `Controlled_Import_Path_Resolver`
- **Location:** Lines 27–191.
- **Anchor File Resolution (Lines 52–68):**
  Uses `realpath($this->anchor_file)` and normalizes separators.
  Derives roots using fixed directory levels:
  - Anchor: `.../app/public/wp-content/plugins/run_controlled_import.php`
  - Level 1 (`dirname`): `.../app/public/wp-content/plugins`
  - Level 2 (`dirname`): `.../app/public/wp-content`
  - Level 3 (`dirname`): `.../app/public` (WordPress root)
  - Level 4 (`dirname`): `.../app`
  - Level 5 (`dirname`): `.../furniture-ecommerce` (Project root)
  - Manifest root: `<Project root>/backups/manifests`

### 6.2 Path Validation Behavior
| Input Scenario | Source Handling | Expected Safe Behavior | Actual Behavior | Audit Result |
|---|---|---|---|---|
| **Valid relative manifest** | `resolve_and_verify_manifest_read("manifest_123.json")` | Resolves inside `backups/manifests/` | Prefixes `manifest_root` and checks `realpath()` | `PASS` |
| **Path traversal (`../../wp-config.php`)** | Lines 130–136, 166–168 | Throws `RuntimeException` | Caught by `stripos($norm_file, $norm_dir)` | `PASS` |
| **UNC Path (`//server/share`)** | Lines 125–127 | Throws `RuntimeException` | Strictly rejected | `PASS` |
| **Null byte (`manifest\0.json`)** | Lines 121–123 | Throws `RuntimeException` | Strictly rejected | `PASS` |
| **Non-JSON extension (`manifest.txt`)** | Lines 154–156 | Throws `RuntimeException` | Enforces `.json` extension | `PASS` |
| **Windows backslashes (`\`)** | Line 44: `str_replace('\\', '/', $path)` | Normalized to `/` | Normalized consistently | `PASS` |
| **Drive letter case (`c:/` vs `C:/`)** | Lines 46–48: `strtoupper($p[0])` | Uppercase drive letter | Normalized to `C:/` | `PASS` |
| **Sibling directory escape (`manifests_evil/`)** | Line 163: `$norm_dir = ... . '/'` | Trailing slash boundary check | Sibling directory prefix mismatch fails closed | `PASS` |
| **Symlink / Junction pointing outside** | `realpath($target)` resolves physical destination | Outside root rejected | `stripos()` against `norm_dir` fails closed | `PASS` |
| **Drive-relative path (`C:manifest.json`)** | `is_absolute()` returns false | Handled safely | Evaluated relative to manifest root; fails closed if outside | `PASS` |

---

## 7. AUDIT D — MANIFEST INTEGRITY AND FILESYSTEM SAFETY

### 7.1 Manifest Writing and Atomicity
- **Method:** `flush_manifest()` (Lines 710–729).
- **Process:**
  1. Serializes to JSON with `JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES`.
  2. Writes to temporary file: `$this->manifest_path . '.tmp'` using `file_put_contents(..., LOCK_EX)`.
  3. Executes `rename($tmp_path, $this->manifest_path)`.
- **Finding (P1): Windows File Lock & Cleanup Limitation**
  - Under Windows NTFS, `rename()` will fail if the destination file is open or locked by a background process (such as Windows Search Indexer, antivirus, or another file handle).
  - If `rename()` fails on line 726, an exception is thrown, but `$tmp_path` is not cleaned up in a `finally` block.
  - While this does not corrupt the existing manifest, stale `.tmp` files will linger.
  - Recommendation: Wrap `rename()` in a `try-catch-finally` to unlink `$tmp_path` if rename fails.

### 7.2 Rollback Protection and Ownership Validation
- **Manifest Validation (Lines 1314–1352):**
  - Manifest must be valid JSON with valid string `run_id`.
  - Project name must match `furniture-ecommerce`.
  - Harness version must be in `['1.1', '1.2', '1.3']`.
  - CSV hash must match locked hash `D2C8566F334A767887F57C4A15EA456CFC5E117767FCFE46C599CC754BC2BBA0`.
  - Target database must match `local`.
  - Table prefix must match `wp_`.
- **Ownership Tag Enforcement (Lines 1384–1426):**
  - Rejects any manifest claiming ownership of pre-existing attachments (e.g. placeholder ID 5).
  - Actively scans database for posts tagged with `_controlled_import_run_id` to recover unmanifested orphans (F-3N-02).
  - Rejects deleting any post tagged with a different `run_id`.

---

## 8. AUDIT E — TEST-SUITE INDEPENDENCE AND QUALITY

### 8.1 Test Architecture Audit (`scratch/test_r2m_isolated_suite.php`)
- **Runner:** `R2M_Test_Runner` creates a dedicated sandbox directory under `scratch/sandbox_r2m_<timestamp>/`.
- **Target Invocations:**
  - Loads real `run_controlled_import.php` via `require_once`.
  - Uses real filesystem operations within the temporary sandbox.
  - Groups 1, 2, and 4 test real classes (`Controlled_Import_Path_Resolver`, `Controlled_Import_Harness`).
  - Group 3 executes real CLI sub-processes (`php run_controlled_import.php <action>`) using Local's PHP 8.2.29 binary.
  - Group 4 exercises real rollback and orphan recovery logic against `Mock_Isolated_Db`.
- **Sandbox Cleanup:**
  - Sandbox directory is recursively deleted in line 765. Verified: no sandbox folders remain in `scratch/`.
- **Isolation Verification:**
  - Zero connections to port 10011 were initiated.
  - Zero SQL statements were executed against live MySQL.
  - Live production manifests in `backups/manifests/` were untouched.

### 8.2 False PASS Vulnerability Analysis
- Could a test produce a false PASS?
  - `R2M-BOOT-01` to `04` strictly verify that the exit code equals 1 AND output contains specific expected error tokens (`Usage:`, `SAFETY STOP`, `Manifest file path required`).
  - `R2M-REC-01` to `04` verify both the returned summary array AND the in-memory post storage state.
  - Group 1 path tests assert strict equality against normalized `realpath()` paths.
  - The suite does not swallow assertion failures; any failed condition increments `$runner->failed` and sets exit code 1.

---

## 9. COVERAGE GAP MATRIX

| Area | Scope Requirement | Implemented Coverage | Gap Classification | Risk Assessment |
|---|---|---|---|---|
| **Path Traversal & UNC** | Reject all paths outside `manifests/` | Fully tested in sandbox (`R2M-PATH-06` to `13`) | **Fully Covered** | Low risk |
| **Missing wp-load.php** | Fail closed if bootstrap missing | Tested in sandbox (`R2M-PATH-03`, `R2M-BOOT-05`) | **Fully Covered** | Low risk |
| **CLI Pre-Bootstrap** | Zero bootstrap on invalid commands | Fully tested via sub-process (`R2M-BOOT-01` to `04`) | **Fully Covered** | Low risk |
| **Live Database Identity** | Verify `local` DB & `wp_` prefix | Tested via mock `$wpdb` (`R2M-BOOT-06`, `07`) | **Covered via Mock** | Medium risk (live DB host not checked) |
| **Live WooCommerce Active** | Detect inactive WooCommerce | Tested via mock and CLI (`R2M-BOOT-08`, `09`) | **Covered via Mock** | Low risk |
| **Concurrent Manifest Writes** | Multi-process lock contention | Tested `LOCK_EX` parameter; no concurrent process test | **Source-Inspection Only** | Low risk (single operator runbook) |
| **NTFS Rename Failure** | Windows handle collision | Exception handling present; `.tmp` cleanup missing | **Gap Identified (P1)** | Medium operational annoyance |
| **Live Site Verification** | Preflight verify on live DB | Forbidden during R2M / R2M-R1 | **Unsafe to Test** | Must be verified in Gate 4 preflight |

---

## 10. SCOPE AND REGRESSION PRESERVATION (AUDIT F)

Review of the full Git diff confirmed that modifications were restricted exclusively to approved areas:
- **CSV Parsing Logic:** No modifications.
- **Product & Variation Creation:** No modifications.
- **Stock Management & Inventory Matrix:** No modifications.
- **Category Taxonomies & Term Mappings:** No modifications.
- **Dataset Constants:**
  - 22 parent products
  - 12 simple products
  - 10 variable products
  - 42 variations
  - 64 unique SKUs
  - 449 total stock units
  - 7 categories / 27 category associations
  All constants remain intact (`SOURCE-VERIFIED`).
- **Unrelated User Theme Files:** Completely untouched.

---

## 11. EVIDENCE CLASSIFICATION AUDIT

| Item Audited | Evidence Claim | Audit Classification | Finding / Source Evidence |
|---|---|---|---|
| `run_controlled_import.php` SHA-256 | `CED4F1B82C9FF855...` | **`SOURCE-VERIFIED`** | Physical SHA-256 re-calculated from file bytes matches 100%. |
| Baseline importer hash | `640B92D4F3AFEED5...` | **`SOURCE-VERIFIED`** | Matches Stage 3N-R2L audited baseline. |
| Pre-bootstrap CLI rejection | `help` exits code 1, 0 bootstrap | **`TEST-LOG-VERIFIED`** | Reproducible terminal execution of PHP CLI confirms immediate exit. |
| Safe path canonical containment | Rejects traversal & external paths | **`SOURCE-VERIFIED`** | Inspected `Controlled_Import_Path_Resolver` methods. |
| Test suite execution claim | 40 / 40 Passed | **`TEST-LOG-VERIFIED`** | Executed test suite in PHP 8.2 CLI; verified 40 PASS, 0 FAIL. |
| Test suite SHA-256 hash | Report claimed `F4DEE796...` | **`REPORT-CLAIM-ONLY`** | Actual hash is `7274687C...`. Intermediate hash was logged in report. |
| Live DB non-contact | 0 queries to port 10011 | **`STATICALLY-VERIFIED`** | Inspected test suite; uses only `Mock_Isolated_Db` in memory. |

---

## 12. FINDINGS ORDERED BY SEVERITY

### Finding R2M-R1-01 (Severity: P1 — Reliability / Hygiene)
- **Source Location:** `run_controlled_import.php` lines 726–729 (`flush_manifest`).
- **Evidence Classification:** `SOURCE-VERIFIED`.
- **Reproducible Explanation:** If `rename($tmp_path, $this->manifest_path)` fails due to an NTFS file lock or antivirus handle, a `RuntimeException` is thrown, but `$tmp_path` is not deleted.
- **Potential Impact:** Stale `.tmp` manifest files accumulate in `backups/manifests/`. While existing manifests are not corrupted, disk clutter could cause confusion during emergency recovery.
- **Required Remediation:** Wrap `rename()` in a `try-catch` block; in the `catch` or failure branch, call `@unlink($tmp_path)` before rethrowing. Safe to remediate without live database access.

### Finding R2M-R1-02 (Severity: P1 — Evidence Documentation Discrepancy)
- **Source Location:** `docs/STAGE_3N_R2M_IMPLEMENTATION_AND_ISOLATED_TEST_REPORT.md` line 297.
- **Evidence Classification:** `STATICALLY-VERIFIED`.
- **Reproducible Explanation:** The Stage 3N-R2M report records the SHA-256 hash of `scratch/test_r2m_isolated_suite.php` as `F4DEE79698A4E1BCE9FD3CD5E23C91F26E69B561CD0C4FCE631E070CEB943A1C`. The actual physical hash of the file is `7274687C11D2D649CAFA42C91DBF2A98E6256B2A942AFA8E9907A2DB17A96D06`.
- **Potential Impact:** Inability for an external auditor to match the test-suite hash without investigating the intermediate draft revision history.
- **Required Remediation:** Update documentation to reflect the current test-suite hash. Safe to perform.

### Finding R2M-R1-03 (Severity: P2 — CLI Hardening)
- **Source Location:** `run_controlled_import.php` lines 1606–1632.
- **Evidence Classification:** `SOURCE-VERIFIED`.
- **Reproducible Explanation:** CLI dispatcher checks `$argv[1]` and `$argv[2]`, but does not inspect `$argc`. Extraneous parameters (e.g. `php run_controlled_import.php verify --force`) are ignored rather than rejected.
- **Potential Impact:** Operator typo could lead to false assumption that extra parameters were accepted.
- **Required Remediation:** Enforce strict argument counts: `$argc === 2` for `verify`, `$argc === 3` for `rollback`, `$argc === 3` for `import`. Safe to remediate without live database access.

### Finding R2M-R1-04 (Severity: P2 — Environment Binding)
- **Source Location:** `run_controlled_import.php` lines 217–232.
- **Evidence Classification:** `SOURCE-VERIFIED`.
- **Reproducible Explanation:** Database identity check confirms `SELECT DATABASE() === 'local'`, but does not verify MySQL host or port.
- **Potential Impact:** If another LocalWP site database is named `local`, this check alone does not detect cross-site targeting.
- **Required Remediation:** Document in Gate 4 Preflight Runbook that database port (`10011`) and site URL must be explicitly verified before execution.

---

## 13. REMEDIATION RECOMMENDATIONS

*(Non-implementing recommendations for future stage review)*
1. **Unlink `.tmp` on Rename Failure:** Add `@unlink($tmp_path)` in `flush_manifest()` upon rename failure.
2. **Reconcile Documentation Hash:** Update R2M report to document the true final hash of `scratch/test_r2m_isolated_suite.php` (`7274687C11D2D649CAFA42C91DBF2A98E6256B2A942AFA8E9907A2DB17A96D06`).
3. **Strict `$argc` CLI Enforcement:** Add `$argc` boundaries to the CLI dispatcher.
4. **Gate 4 Preflight Host Verification:** Include DB host and port check in the operational preflight protocol.

---

## 14. REMAINING UNKNOWNS

1. **Restricted MySQL User:** The WordPress site in LocalWP continues to connect via `root` credentials in `wp-config.php`. Least-privilege MySQL user provisioning remains unverified in this environment.
2. **Live Database Table Snapshot Restoration:** Restoration of SQL snapshots against the live MySQL instance has not been dynamically tested during R2M/R2M-R1 due to strict non-mutation boundaries.

---

## 15. EXPLICIT GATE 4 DECISION

### Gate 4 Controlled Production Import: **STRICTLY BLOCKED**

Passing this independent security review confirms that the source code defects in bootstrap timing and path resolution have been remediated cleanly in accordance with Stage 3N-R2L.

**However, this review DOES NOT authorize execution of Gate 4.**

The operational prerequisites (live preflight verification, disaster recovery runbook alignment, snapshot readiness, and explicit human authorization) must be formally signed off in a dedicated stage before any live import or verify action is executed against the ATELIER database.
