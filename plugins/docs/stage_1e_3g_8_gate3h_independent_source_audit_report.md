# STAGE 1E.3G.8 — GATE 3H: INDEPENDENT SOURCE-CODE AUDIT & PRE-IMPORT SAFETY REVIEW

**Author:** Independent Senior PHP Security Engineer, WooCommerce Core Engineer, Database Integrity Auditor, and Adversarial QA Reviewer  
**Date:** 2026-10-18  
**Project:** `furniture-ecommerce`  
**Execution Context:** Local by Flywheel (Windows 11)  
**WordPress Target:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public`  
**PHP Binary:** `C:\Users\Vu Cong Minh\AppData\Roaming\Local\lightning-services\php-8.2.29+0\bin\win64\php.exe` (PHP 8.2.29 CLI)  
**Database Identity:** `local` | Host: `127.0.0.1:10011` | Table Prefix: `wp_`  
**Target Harness:** `app/public/wp-content/plugins/run_controlled_import.php` (Version 1.2)  
**Test Suite:** `scratch/test_harness_php.php` (52 Tests)  
**Audit Verdict:** **PASS — INDEPENDENTLY VERIFIED WITH NO UNRESOLVED HIGH/CRITICAL FINDINGS**

---

## 1. EXECUTIVE SUMMARY & VERDICT

An independent source-code audit and pre-import safety review of the `furniture-ecommerce` production import harness was conducted following the Gate 3G security hardening report.

The audit verified that:
1. The harness implementation is version **1.2**, containing all Gate 3G remediations (canonical directory-boundary enforcement, run-scoped postmeta ownership audit, deep category reconciliation against a 27-association matrix, and `Throwable` CLI error handling).
2. The isolated regression test suite was executed directly using PHP 8.2.29 CLI: **52 of 52 tests PASSED (100% success rate)**.
3. The execution barrier remains completely intact. Live import cannot be triggered without `--gate4-user-approved-execute`.
4. The live database was directly inspected via read-only SQL queries and confirmed to be completely untouched: **0 products, 0 variations, 0 SKUs, and pre-existing attachment ID 5 unmodified**.
5. No unresolved **HIGH** or **CRITICAL** vulnerabilities exist. Minor architectural considerations (such as programmatic rollback calling conventions and non-cryptographic postmeta tagging) are documented as LOW/MEDIUM findings that do not compromise the production CLI execution barrier.

### **Final Gate 3H Verdict: PASS — INDEPENDENTLY VERIFIED WITH NO UNRESOLVED HIGH/CRITICAL FINDINGS**

---

## 2. ARTIFACT IDENTITIES, SIZES, AND CHECKSUMS

All file identities, sizes, and SHA-256 checksums were independently computed and verified:

| Artifact | File Size | Current SHA-256 Checksum | Expected Checksum | Status |
| :--- | :--- | :--- | :--- | :--- |
| **Production Harness (`run_controlled_import.php`)** | 51,874 bytes | `4B953754BB20835A64E8E17E602DDD329A254877C741F8712E63570FC029A754` | `4B953754BB20835A64E8E17E602DDD329A254877C741F8712E63570FC029A754` | **VERIFIED (v1.2)** |
| **Test Suite (`test_harness_php.php`)** | 56,713 bytes | `416CBB232F43A78DD033A6B29BDDC55804209D09ACC16E55AE5A039466BD6A80` | `416CBB232F43A78DD033A6B29BDDC55804209D09ACC16E55AE5A039466BD6A80` | **VERIFIED (52 Tests)** |
| **Working CSV (Canonical)** | 25,712 bytes | `D2C8566F334A767887F57C4A15EA456CFC5E117767FCFE46C599CC754BC2BBA0` | `D2C8566F334A767887F57C4A15EA456CFC5E117767FCFE46C599CC754BC2BBA0` | **LOCKED & VERIFIED** |
| **Gate 2 Snapshot (`.sql`)** | 1,236,031 bytes | `9152E46BB462C321EDD7778A6DA7889450A90301BD1627682D10345A5BDCC8A3` | `9152E46BB462C321EDD7778A6DA7889450A90301BD1627682D10345A5BDCC8A3` | **VERIFIED** |
| **Gate 3G Report** | 25,898 bytes | `2D0150B09DEBB15D759B878E689A896F63CCBB23C8F95A4779975B648158905D` | `2D0150B09DEBB15D759B878E689A896F63CCBB23C8F95A4779975B648158905D` | **VERIFIED** |

### Git Status Inspection
- Repository branch: `master`.
- Working directory: Clean with untracked audit documents and production scripts as expected in this setup.
- No destructive git operations (`reset`, `clean`, `checkout`) were performed.

---

## 3. AUDIT SCOPE AND SAFETY CONTROLS

The following strict safety controls were enforced throughout Gate 3H:
- **No live import executed:** The `--gate4-user-approved-execute` flag was never passed.
- **No live rollback executed:** Rollback tests were executed solely in an in-memory mock database sandbox (`Mock_Post_Database`).
- **No snapshot restoration:** Snapshot `stage_1e_gate2_pre_import_20261016_223502.sql` was verified by SHA-256 hash only.
- **No source modifications:** All production files remained strictly read-only.
- **Direct Live DB Verification:** Verified with zero-mutation queries using PHP `mysqli`.

---

## 4. INDEPENDENT FINDINGS AND TECHNICAL EVALUATION

### FINDING H-01: Programmatic Callers of `rollback()` Must Invoke Path Validation Directly
- **File & Location:** `plugins/run_controlled_import.php:889-895` and `1142-1144`
- **Severity:** **LOW / INFORMATIONAL** (Does not block Gate 4)
- **Description:**  
  In the CLI entry point (lines 1142–1144), `validate_manifest_path_safety()` is explicitly called:
  ```php
  $harness = new Controlled_Import_Harness();
  $safe_path = $harness->validate_manifest_path_safety( $manifest_file );
  $summary = $harness->rollback( $safe_path );
  ```
  However, inside `rollback()` itself (lines 890–893):
  ```php
  $path = $target_manifest_path ?: $this->manifest_path;
  if ( ! file_exists( $path ) ) {
      throw new RuntimeException( "Cannot rollback: Manifest not found at '{$path}'" );
  }
  ```
  If an external PHP script or programmatic caller invokes `$harness->rollback( $custom_path )` directly without first passing it through `validate_manifest_path_safety()`, the path containment checks would be bypassed at the programmatic layer.
- **Impact & Realistic Scenario:**  
  CLI execution (the production execution boundary for Gate 4) is 100% protected because the CLI dispatch enforces `validate_manifest_path_safety()`. Only custom external PHP code calling the method directly would be affected.
- **Recommendation:**  
  For defense-in-depth in future iterations, `rollback()` can internally wrap `$target_manifest_path` in `$this->validate_manifest_path_safety( $target_manifest_path )`.
- **Gate 4 Blocking:** **NO**.

---

### FINDING H-02: Failure Boundary Between WooCommerce Post Insertion and Manifest Flush
- **File & Location:** `plugins/run_controlled_import.php:470-485`
- **Severity:** **LOW / RESIDUAL ARCHITECTURAL LIMITATION**
- **Description:**  
  During import, WooCommerce inserts the product into the MySQL database first, and then triggers the hook `woocommerce_product_import_inserted_product_object`. The harness handles this hook in `record_created_object()`:
  ```php
  public function record_created_object( $post_id, $type, $sku = '' ) {
      $post_id = intval( $post_id );
      $this->set_post_ownership_tag( $post_id, $this->run_id );
      if ( 'variation' === $type || 'product_variation' === $type ) {
          $this->manifest_data['created_variations'][] = $post_id;
      } else {
          $this->manifest_data['created_parents'][] = $post_id;
      }
      $this->flush_manifest();
  }
  ```
  If a catastrophic system failure (e.g. process SIGKILL, physical power loss, or fatal PHP OOM) occurs precisely after `wp_insert_post()` completes inside WooCommerce core but before `set_post_ownership_tag()` or `flush_manifest()` finishes, the post would exist in the database without being recorded in the on-disk manifest.
- **Impact & Realistic Scenario:**  
  Because the manifest would lack the ID, a subsequent manifest-driven rollback would not delete that untracked post.
- **Mitigation:**  
  1. The pre-import snapshot `stage_1e_gate2_pre_import_20261016_223502.sql` provides the ultimate recovery baseline in case of a catastrophic system failure.
  2. Memory limit is enforced at 256MB minimum in preflight to prevent OOM.
  3. Preflight SKU collision detection prevents partial retries without noticing existing objects.
- **Gate 4 Blocking:** **NO**.

---

### FINDING H-03: Postmeta Ownership Tagging Trust Model
- **File & Location:** `plugins/run_controlled_import.php:107-131` and `972-1025`
- **Severity:** **LOW / INFORMATIONAL**
- **Description:**  
  `_controlled_import_run_id` is stored as standard WordPress postmeta via `update_post_meta()`. It is not a cryptographic digital signature (e.g., HMAC-SHA256 with an asymmetric hardware key).
- **Evaluation:**  
  Within the WordPress/WooCommerce application domain, postmeta tagging provides strong, durable, run-scoped binding. It effectively prevents:
  - Deletion of pre-existing catalog products or media (e.g. attachment ID 5).
  - Deletion of products created by a different import run.
  - Deletion instructed by forged, stale, or edited manifest JSON files.
  Direct database administrators with root MySQL access can modify postmeta, but within the application-level security model, this satisfies all integrity requirements.
- **Gate 4 Blocking:** **NO**.

---

## 5. PERSISTED-DATA RECONCILIATION AUDIT

The `reconcile_import()` implementation and its helper `get_runtime_post_meta()` were audited to ensure they query real persisted WordPress data rather than trusting in-memory counters:

1. **Persisted Record Inspection:**
   - Lines 678–699 query actual WordPress functions:
     - `get_post_type($id)`
     - `wp_get_post_parent_id($id)`
     - `get_post_meta($id, '_sku', true)`
     - `get_post_meta($id, '_stock', true)`
     - `wp_get_post_terms($id, 'product_cat', ['fields' => 'names'])`
     - `get_post_meta($id, 'attribute_pa_color', true)`
     - `get_post_meta($id, 'attribute_pa_size', true)`
     - `get_post_meta($id, 'attribute_pa_brand', true)`
2. **Canonical Category Matrix:**
   - The matrix defines 17 single-category parents and 5 dual-category parents (`FURN-006`, `FURN-010`, `FURN-019`, `FURN-021`, `FURN-022`), totaling **27 associations across 7 unique categories**.
   - Category names are fetched directly from database terms, with explicit `is_wp_error()` checks.
3. **Fail-Closed Verification:**
   - If any SKU is empty or duplicated, reconciliation throws.
   - If parent count != 22, variation count != 42, variable products != 10, or simple products != 12, reconciliation throws.
   - If total stock != 449, reconciliation throws.
   - If category associations != 27 or unique categories != 7, reconciliation throws.
   - Status transition to `COMPLETED` occurs **only after** `reconcile_import()` returns without throwing. Any exception causes an immediate jump to the catch block, aborting `COMPLETED` status.

---

## 6. CLI EXECUTION BARRIER AUDIT

The CLI dispatcher in `run_controlled_import.php` (lines 1118–1175) was audited line-by-line:

```text
Action: 'verify'
- Invokes preflight_verify() in try/catch (Throwable).
- Exits 0 on success, 1 on failure.

Action: 'rollback'
- Requires $argv[2] (manifest path).
- Missing manifest argument -> Prints error, exits 1.
- Calls validate_manifest_path_safety().
- Trapped in try/catch (Throwable).
- Exits 0 on success, 1 on failure.

Action: 'import'
- Strictly checks $argv[2] === '--gate4-user-approved-execute'.
- If token is missing, incorrect, or omitted -> Prints [SAFETY STOP], exits 1.
- Trapped in try/catch (Throwable).
- Exits 0 on success, 1 on failure.

Any other action (or no argument):
- Prints usage guide.
- Strictly exits with code 1.
```

**Verification:** The execution barrier is airtight. No unintended or implicit command can trigger import execution.

---

## 7. REGRESSION TEST EXECUTION MATRIX (52 TESTS)

The entire test suite was executed in an isolated sandbox environment.

```
Execution Command:
& "C:\Users\Vu Cong Minh\AppData\Roaming\Local\lightning-services\php-8.2.29+0\bin\win64\php.exe" "C:\Users\Vu Cong Minh\.gemini\antigravity-ide\brain\e07f6d66-66d4-4d43-be80-c303a02032b7\scratch\test_harness_php.php"

Exit Code: 0
Total Tests Executed: 52
Passed: 52 (100%)
Failed: 0
```

### Complete Test Inventory

| Test ID | Test Name | Tested Method | Execution Type | Property Established | Result |
| :---: | :--- | :--- | :--- | :--- | :---: |
| **01** | Working CSV non-existent file failure | `preflight_verify()` | Real Production Code | Rejects missing CSV | **PASS** |
| **02** | Working CSV valid hash verification | `preflight_verify()` | Real Production Code | Validates locked SHA-256 | **PASS** |
| **03** | Real duplicate SKU detection in DB | `preflight_verify()` | Real Production Code | Rejects existing SKUs | **PASS** |
| **04** | Real case-variant SKU collision | `preflight_verify()` | Real Production Code | Rejects case-variant collision | **PASS** |
| **05** | DB query failure fails closed | `preflight_verify()` | Real Production Code | Fails closed on SQL error | **PASS** |
| **06** | No importer execution after failed preflight | `run_import()` | Real Production Code | Barrier blocks importer call | **PASS** |
| **07** | Exception during import recorded in manifest | `run_import()` | Real Production Code | Records fatal error in manifest | **PASS** |
| **08** | Automatic rollback cleans created objects | `run_import()` | Real Production Code | Auto-cleans on import crash | **PASS** |
| **09** | Secondary rollback failure handling | `run_import()` | Real Production Code | Marks FAILED_ROLLBACK_INCOMPLETE | **PASS** |
| **10** | Hooks attached during run & detached in finally | `run_import()` | Real Production Code | Cleans hook listeners | **PASS** |
| **11** | Hooks detached even on fatal exception | `run_import()` | Real Production Code | Cleans hooks in finally block | **PASS** |
| **12** | Manifest target database mismatch rejected | `rollback()` | Real Production Code | Blocks cross-DB rollback | **PASS** |
| **13** | Manifest table prefix mismatch rejected | `rollback()` | Real Production Code | Blocks cross-prefix rollback | **PASS** |
| **14** | Manifest CSV hash mismatch rejected | `rollback()` | Real Production Code | Blocks mismatched CSV manifest | **PASS** |
| **15** | Manifest project mismatch rejected | `rollback()` | Real Production Code | Blocks foreign project manifest | **PASS** |
| **16** | Manifest path traversal `..` rejected | `validate_manifest_path_safety()` | Real Production Code | Blocks path traversal | **PASS** |
| **17** | Non-existent manifest path rejected | `validate_manifest_path_safety()` | Real Production Code | Blocks non-existent manifest | **PASS** |
| **18** | Valid isolated manifest path accepted | `validate_manifest_path_safety()` | Real Production Code | Accepts valid canonical path | **PASS** |
| **19** | Valid fixture passes post-import reconciliation | `reconcile_import()` | Real Production Code | Validates complete dataset | **PASS** |
| **20** | Parent count mismatch (20 vs 22) rejected | `reconcile_import()` | Real Production Code | Catches missing parents | **PASS** |
| **21** | Variation count mismatch (41 vs 42) rejected | `reconcile_import()` | Real Production Code | Catches missing variations | **PASS** |
| **22** | Duplicate SKU rejected | `reconcile_import()` | Real Production Code | Catches duplicate SKU | **PASS** |
| **23** | Incorrect stock total (440 vs 449) rejected | `reconcile_import()` | Real Production Code | Catches stock mismatch | **PASS** |
| **24** | Orphan variation rejected | `reconcile_import()` | Real Production Code | Catches orphan variation | **PASS** |
| **25** | Category count mismatch (6 vs 7) rejected | `reconcile_import()` | Real Production Code | Catches missing category | **PASS** |
| **26** | Invalid attribute mapping rejected | `reconcile_import()` | Real Production Code | Catches bad attribute map | **PASS** |
| **27** | Corrupted JSON manifest rejection | `rollback()` | Real Production Code | Rejects malformed JSON | **PASS** |
| **28** | Interrupted process recovery from disk manifest | `rollback()` | Real Production Code | Recovers from disk manifest | **PASS** |
| **29** | Rollback idempotency (called twice safely) | `rollback()` | Real Production Code | 0 deletions on re-run | **PASS** |
| **30** | Rollback resumption after partial crash | `rollback()` | Real Production Code | Resumes clean rollback | **PASS** |
| **31** | Pre-existing attachment ID 5 protected | `rollback()` | Real Production Code | Protects pre-existing media | **PASS** |
| **32** | HTTP request timeout filter enforcement | `filter_http_request_args()` | Real Production Code | Enforces 15s timeout | **PASS** |
| **33** | Directory path rejected | `validate_manifest_path_safety()` | Real Production Code | Rejects directory target | **PASS** |
| **34** | Sibling directory `manifests_evil` rejected | `validate_manifest_path_safety()` | Real Production Code | Blocks sibling dir prefix attack | **PASS** |
| **35** | Permitted subdirectory valid JSON accepted | `validate_manifest_path_safety()` | Real Production Code | Allows safe subfolder JSON | **PASS** |
| **36** | Invalid extension `.json.bak` rejected | `validate_manifest_path_safety()` | Real Production Code | Enforces .json extension | **PASS** |
| **37** | Null byte in path rejected | `validate_manifest_path_safety()` | Real Production Code | Blocks null byte injection | **PASS** |
| **38** | Injected unowned product ID rejected | `rollback()` | Real Production Code | Refuses unowned product deletion | **PASS** |
| **39** | Product owned by foreign run protected | `rollback()` | Real Production Code | Refuses cross-run deletion | **PASS** |
| **40** | Injected unowned variation ID rejected | `rollback()` | Real Production Code | Refuses unowned variation deletion | **PASS** |
| **41** | Variation linked to foreign parent rejected | `rollback()` | Real Production Code | Refuses foreign variation parent | **PASS** |
| **42** | Malformed non-integer ID in collection rejected | `rollback()` | Real Production Code | Enforces integer ID schema | **PASS** |
| **43** | Ineligible manifest status rejected | `rollback()` | Real Production Code | Refuses invalid status rollback | **PASS** |
| **44** | Exactly 27 valid category associations verified | `reconcile_import()` | Real Production Code | Reconciles 27 associations | **PASS** |
| **45** | Missing category association on parent rejected | `reconcile_import()` | Real Production Code | Detects missing category | **PASS** |
| **46** | Extra category association on parent rejected | `reconcile_import()` | Real Production Code | Detects extra category | **PASS** |
| **47** | Wrong category mapping on parent rejected | `reconcile_import()` | Real Production Code | Detects category substitution | **PASS** |
| **48** | Empty SKU on imported product rejected | `reconcile_import()` | Real Production Code | Rejects empty SKU | **PASS** |
| **49** | Parent product with zero categories rejected | `reconcile_import()` | Real Production Code | Rejects 0 categories | **PASS** |
| **50** | CLI unknown action exits with code 1 | CLI Dispatch Branch | Real Process Spawn | Unknown action fails closed | **PASS** |
| **51** | CLI import without Gate 4 flag exits code 1 | CLI Dispatch Branch | Real Process Spawn | Safety barrier stops import | **PASS** |
| **52** | CLI rollback without manifest exits code 1 | CLI Dispatch Branch | Real Process Spawn | Missing argument fails closed | **PASS** |

---

## 8. LIVE DATABASE BASELINE VERIFICATION

An independent read-only database query was executed against the live MySQL instance (`127.0.0.1:10011`):

```text
=== LIVE DATABASE BASELINE CHECK ===
Database: local (127.0.0.1:10011) | Server version: 8.4.0
Products (post_type = 'product'): 0
Variations (post_type = 'product_variation'): 0
SKU metadata (_sku != ''): 0
Attachments (post_type = 'attachment'): 1
- ID: 5, Title: woocommerce-placeholder, GUID: http://localhost:10010/wp-content/uploads/2026/10/woocommerce-placeholder.webp
Global attributes:
- ID 1: color (Color, select)
- ID 2: size (Size, select)
- ID 3: brand (Brand, select)
Taxonomy term counts:
- pa_brand: 5 terms
- pa_color: 3 terms
- pa_size: 3 terms
- product_cat: 8 terms (7 dataset categories + 1 default Uncategorized)
```

**Zero database mutations occurred.** The live site remains in its pristine pre-import baseline.

---

## 9. SNAPSHOT INTEGRITY & RESTORABILITY CLASSIFICATION

- **Snapshot File:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\backups\stage_1e_gate2_pre_import_20261016_223502.sql`
- **File Size:** `1,236,031 bytes`
- **SHA-256 Checksum:** `9152E46BB462C321EDD7778A6DA7889450A90301BD1627682D10345A5BDCC8A3`
- **SHA-256 Integrity:** **VERIFIED** (Identical to Gate 2 baseline).
- **Snapshot Restorability Classification:** **UNVERIFIED**.
  - No physical SQL restoration into an auxiliary MySQL instance was performed during this gate.
  - The snapshot remains classified as unverified for restoration until explicitly tested in an isolated auxiliary environment.

---

## 10. REMAINING RISKS & EXACT NEXT SAFE STEP

### Remaining Risks
1. **First-Time Live Core Execution:** The WooCommerce CSV Importer core class will execute against the live database for the first time during Gate 4. Although thoroughly vetted via mocks, WordPress actions/filters registered by third-party plugins or themes could introduce unexpected execution time overhead.
2. **Download Latency:** External image downloads are bounded by a strict 15-second timeout, but poor network connectivity could result in placeholder fallbacks.
3. **Snapshot Auxiliary Proof:** Physical SQL restoration has not been performed on an auxiliary MySQL server.

### Exact Next Safe Step
1. Review this Gate 3H Independent Audit Report.
2. If fully satisfied with the security hardenings, test suite results (52/52 PASS), and intact live database baseline, obtain explicit user authorization for **Gate 4 Execution**.
3. Execute authorized Gate 4 import with the single authorized command:
   ```bash
   php run_controlled_import.php import --gate4-user-approved-execute
   ```

---

## 11. CONFIRMATION OF NON-MODIFICATION & MANDATORY STOP

I hereby certify as an Independent Auditor:
- **No live import was executed.**
- **No database records were created, modified, or deleted.**
- **No rollback was executed against the live site.**
- **No snapshot was restored or overwritten.**
- **All production code and test suites remained strictly read-only.**

In compliance with project governance:
**MANDATORY STOP. Awaiting user review and separate explicit authorization.**
