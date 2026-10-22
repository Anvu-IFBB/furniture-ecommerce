# STAGE 1E.3G.7 — GATE 3G: MANIFEST OWNERSHIP, PATH BOUNDARY & RECONCILIATION HARDENING REPORT

**Author:** Senior WooCommerce/PHP Security Engineer, Database Integrity Engineer, and QA Auditor  
**Date:** 2026-10-18  
**Project:** `furniture-ecommerce`  
**Execution Context:** Local by Flywheel (Windows 11)  
**WordPress Target:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public`  
**PHP Version:** `8.2.29` CLI  
**Database Identity:** `local` | Host: `127.0.0.1:10011` | Table Prefix: `wp_`  
**Harness File:** `plugins/run_controlled_import.php` (Hardened to Version 1.2)  
**Test Suite:** `scratch/test_harness_php.php` (Expanded to 52 Tests)  
**Gate Status:** **PASS** (Remediation & Regression Complete; Live DB Intact; Execution Barrier Intact)

---

## 1. EXECUTIVE SUMMARY

Gate 3F verified the v1.1 production import harness, achieving 32/32 PASS. However, four critical safety and audit properties required hardening before any authorized import execution:
1. **G3-01 (Path Boundary):** Manifest path containment relied on prefix validation without strict directory-boundary enforcement, permitting potential sibling-directory prefix confusion (e.g., `manifests_evil`) and directory targets.
2. **G3-02 (Object Ownership & Rollback Integrity):** Rollback verified environmental invariants (database name, table prefix, CSV hash, project name) but did not establish cryptographic or durable run-scoped proof of object ownership before issuing `wp_delete_post()`. An adversarial or cross-run manifest could instruct rollback to delete unrelated products, variations, or attachments.
3. **G3-03 (Category Association Reconciliation):** Post-import reconciliation verified taxonomy term count (7) and generic term existence, but did not assert the exact 27 product-to-category associations across the 22 imported parent products against the canonical category matrix.
4. **G3-04 (CLI Error Handling & Execution Barrier):** CLI command dispatch caught `Exception` rather than `Throwable`, failing to catch fatal `Error` types, and lacked strict exit-code 1 enforcement on missing or unknown actions.

### Summary of Remediations
In Gate 3G, the production harness (`run_controlled_import.php`) was updated from v1.1 to v1.2:
- **Canonical Directory Boundary:** Replaced prefix checks with strict canonical realpath containment using directory trailing separators, rejected null bytes, verified `is_file()`, and enforced `.json` file extensions.
- **Run-Scoped Postmeta Tagging & Pre-Deletion Ownership Audit:** Every imported product, variation, and attachment is tagged with `_controlled_import_run_id` upon creation. Rollback now conducts a mandatory pre-deletion audit verifying every object's post type, parent-child topological linkage, and exact run ownership. If any object is unowned, owned by another run, foreign, or pre-existing (such as attachment ID 5), rollback **fails closed** immediately without deleting any objects.
- **Canonical Category Matrix Reconciliation:** Hardened reconciliation to independently query actual taxonomy relationships on all 22 parent products, asserting exactly 27 associations across the 7 required categories, detecting missing, extra, or cross-mapped categories.
- **Throwable CLI Barrier:** Upgraded all CLI entry points to `catch ( Throwable $e )`, and forced exit code 1 on missing/unknown actions.

The regression test suite (`test_harness_php.php`) was expanded from 32 to **52 tests**, covering all adversarial boundary cases, sibling directory traversal, object injection, foreign parent linkage, category perturbation, and CLI barrier traps.

**All 52 tests PASSED (100% success rate).** The live database was verified via direct read-only inspection: **0 products, 0 variations, 0 SKUs, and attachment ID 5 remain completely untouched.**

---

## 2. BEFORE / AFTER HASHES AND AUDITABLE DIFF

### 2.1 File Hashes and Sizes

| Artifact | Gate 3E Baseline (Pre-Remediation) | Gate 3G Hardened (Post-Remediation) | Status |
| :--- | :--- | :--- | :--- |
| `run_controlled_import.php` | 37,946 bytes<br>`1B33667EBC1F79D90CBDBD43D64BECEA263DF9B39C6132880F802C2583089B3E` | 51,874 bytes<br>`4B953754BB20835A64E8E17E602DDD329A254877C741F8712E63570FC029A754` | Hardened (v1.2) |
| `test_harness_php.php` | 36,492 bytes<br>`09580AB9D4EA05070174C4EEC87687C81DD46012DAF67D387D1398A654986FD6` | 56,713 bytes<br>`416CBB232F43A78DD033A6B29BDDC55804209D09ACC16E55AE5A039466BD6A80` | Expanded (52 tests) |
| Working CSV (Canonical) | 25,712 bytes<br>`D2C8566F334A767887F57C4A15EA456CFC5E117767FCFE46C599CC754BC2BBA0` | 25,712 bytes<br>`D2C8566F334A767887F57C4A15EA456CFC5E117767FCFE46C599CC754BC2BBA0` | Unchanged / Locked |
| Gate 2 Snapshot (`.sql`) | 1,236,031 bytes<br>`9152E46BB462C321EDD7778A6DA7889450A90301BD1627682D10345A5BDCC8A3` | 1,236,031 bytes<br>`9152E46BB462C321EDD7778A6DA7889450A90301BD1627682D10345A5BDCC8A3` | Unchanged / Intact |
| Gate 3F Report | 41,195 bytes<br>`18582ADE5005ED9161346D0EA5266FB5E505E7D6D1C14E954960DA968E085DBC` | 41,195 bytes<br>`18582ADE5005ED9161346D0EA5266FB5E505E7D6D1C14E954960DA968E085DBC` | Unchanged / Intact |

### 2.2 Git Diff Summary

A non-destructive pre-remediation backup was saved at `scratch/run_controlled_import.php.gate3e.bak`. A line diff shows:
- **`+342` lines added, `-14` lines modified/removed**.
- Added `EXPECTED_CATEGORY_ASSOC_COUNT = 27`, `EXPECTED_DATASET_CATEGORIES`, and canonical `EXPECTED_PARENT_CATEGORIES` matrix.
- Implemented `get_post_ownership_tag()`, `set_post_ownership_tag()`, `get_post_type_safe()`, and `get_post_parent_id_safe()`.
- Added pre-deletion loop in `rollback()` verifying each variation, parent, and attachment against `$manifest['run_id']` and parent topological membership.
- Added strict directory traversal guard with `DIRECTORY_SEPARATOR` boundary checks in `validate_manifest_path()`.
- Converted all CLI `catch ( Exception $e )` dispatch blocks to `catch ( Throwable $e )`.

---

## 3. FINDINGS, REMEDIATIONS, AND VERIFICATION EVIDENCE

### 3.1 FINDING G3-01: Canonical Path Containment

#### Original Vulnerability
In v1.1, manifest validation checked:
```php
$norm_dir = rtrim( wp_normalize_path( $approved_dir ), '/' );
$norm_file = wp_normalize_path( $real_file );
if ( strpos( $norm_file, $norm_dir ) !== 0 ) {
    throw new RuntimeException("Path safety violation...");
}
```
**Flaws:**
1. If `$norm_dir` was `/path/to/backups/manifests`, an attacker could supply `/path/to/backups/manifests_evil/payload.json`. Because `strpos()` checks a raw string prefix, `/path/to/backups/manifests_evil` begins with `/path/to/backups/manifests` and passed validation.
2. If a path resolved to a directory rather than a regular file, it was not rejected before reading.
3. Null bytes were not explicitly guarded prior to filesystem syscalls.

#### Hardened Implementation (v1.2)
```php
public function validate_manifest_path( string $path ): string {
    if ( strpos( $path, "\0" ) !== false ) {
        throw new RuntimeException( "Path safety violation: Null byte detected in manifest path." );
    }

    $ext = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
    if ( 'json' !== $ext ) {
        throw new RuntimeException( "Invalid manifest file format: must be a .json file." );
    }

    $real_file = realpath( $path );
    if ( false === $real_file ) {
        throw new RuntimeException( "Manifest file not found: {$path}" );
    }

    if ( ! is_file( $real_file ) ) {
        throw new RuntimeException( "Path safety violation: Manifest path resolves to a directory, not a regular file: '{$path}'" );
    }

    $real_dir = realpath( $this->manifest_dir );
    if ( false === $real_dir ) {
        throw new RuntimeException( "Approved manifest directory does not exist: {$this->manifest_dir}" );
    }

    $canonical_dir  = rtrim( wp_normalize_path( $real_dir ), '/' ) . '/';
    $canonical_file = wp_normalize_path( $real_file );

    if ( 0 !== strpos( $canonical_file, $canonical_dir ) ) {
        throw new RuntimeException( "Path safety violation: Manifest path '{$canonical_file}' is outside approved directory '{$canonical_dir}'" );
    }

    return $real_file;
}
```

#### Evidence
- Test 16: Path traversal `..` rejected.
- Test 17: Non-existent file rejected.
- Test 18: Valid manifest inside directory accepted.
- Test 33: Directory path rejected with exit.
- Test 34: Sibling directory `manifests_evil` rejected (`0 !== strpos(..., canonical_dir)`).
- Test 35: Permitted subdirectory accepted.
- Test 36: File extension `.json.bak` rejected.
- Test 37: Null-byte injection rejected.

---

### 3.2 FINDING G3-02: Manifest Integrity and Object Ownership

#### Original Vulnerability
In v1.1, rollback accepted any valid JSON manifest matching the current database identity and CSV hash, then looped over `created_variations` and `created_parents` calling `wp_delete_post( $id, true )`.
If an adversary or stale cross-run manifest inserted ID `2` (e.g. an existing flagship product) into `created_parents`, rollback would delete that product without verifying whether the running harness created it.

#### Hardened Implementation (v1.2)
1. **Durable Postmeta Tagging:**
   During import, every created parent product, variation, and attachment is tagged with:
   `update_post_meta( $id, '_controlled_import_run_id', $this->run_id );`
2. **Schema & Collection Validation:**
   Before any deletion, all ID collections (`created_parents`, `created_variations`, `created_attachments`) are validated to be non-empty arrays containing strictly positive integers. Non-integer or negative IDs immediately abort rollback.
3. **Pre-Existing Object Guard:**
   Ensures no created ID is present in `pre_existing_attachments`.
4. **Pre-Deletion Ownership Audit:**
   Rollback executes a strict two-phase operation:
   - **Phase 1 (Audit Phase):** Every variation is inspected. Its post type must be `product_variation`, its parent must belong to `created_parents`, and its `_controlled_import_run_id` postmeta must match `$manifest['run_id']`. Every parent product is verified to be `post_type = 'product'` and owned by `$manifest['run_id']`. Every attachment is verified to be `post_type = 'attachment'` and owned by `$manifest['run_id']`.
   - **Phase 2 (Deletion Phase):** Only if **every single object** passes Phase 1 does rollback issue any deletion calls.
   - If any object has an unverified, missing, or mismatched owner, rollback **fails closed** throwing a `RuntimeException` and modifying zero records.
5. **Idempotent Rollback:**
   If a manifest has already transitioned to `ROLLED_BACK`, subsequent rollback calls safely return 0 additional deletions without throwing errors.

#### Evidence
- Test 38: Adversarially injected unowned product ID 2 rejected (`Ownership audit failed: Product ID 2 is not owned by run 'run_test_38'`). Zero deletions performed.
- Test 39: Product owned by a previous run (`run_previous_other_999`) protected against deletion.
- Test 40: Injected unowned variation ID rejected. Zero deletions performed.
- Test 41: Variation pointing to foreign parent ID 9999 rejected.
- Test 42: Malformed non-integer ID in collection rejected.
- Test 43: Ineligible manifest status rejected.
- Test 31: Attachment ID 5 preserved intact.
- Test 29: Repeated rollback execution is safely idempotent.

---

### 3.3 FINDING G3-03: Real Category Association Reconciliation

#### Original Vulnerability
In v1.1, reconciliation verified that `count( $found_categories ) === 7` and checked that categories existed, but did not query WooCommerce/WordPress taxonomy terms on the parent products to verify that each of the 22 parents was bound to its exact expected categories.

#### Hardened Implementation (v1.2)
1. **Canonical Matrix Definition:**
   Embedded the exact canonical dataset mapping:
   - 17 single-category parents (e.g. `FURN-001` -> `['Chairs']`).
   - 5 dual-category parents:
     - `FURN-006` -> `['Beds', 'Storage']`
     - `FURN-010` -> `['Storage', 'Decoration']`
     - `FURN-019` -> `['Storage', 'Beds']`
     - `FURN-021` -> `['Chairs', 'Sofas']`
     - `FURN-022` -> `['Beds', 'Storage']`
   - Total associations: **27**.
2. **Deep Taxonomy Verification:**
   Reconciliation queries `wp_get_post_terms( $parent_id, 'product_cat', ['fields' => 'names'] )`.
   - Verifies return is not a `WP_Error`.
   - Fails if any parent has 0 associations.
   - Sorts and compares actual names against the expected set for that SKU.
   - Sums all parent associations across the run and asserts `EXPECTED_CATEGORY_ASSOC_COUNT === 27`.
   - Fails if any missing, extra, or substituted category is detected.
3. **Empty SKU & Invariant Guard:**
   Reconciliation detects empty SKUs, duplicate SKUs, stock mismatches, orphan variations, and variable product attribute definitions. Any discrepancy halts status transition and leaves the run in `FAILED`.

#### Evidence
- Test 44: Exactly 27 valid associations across 7 categories verified.
- Test 45: Missing category association on `FURN-006` caught and rejected.
- Test 46: Extra category association on `FURN-001` caught and rejected.
- Test 47: Category substitution (`Tables` instead of `Chairs`) caught and rejected.
- Test 48: Empty SKU on imported product caught and rejected.
- Test 49: Parent with zero categories caught and rejected.
- Test 22: Duplicate SKU caught and rejected.
- Test 23: Stock sum discrepancy caught and rejected.
- Test 24: Orphan variation caught and rejected.

---

### 3.4 FINDING G3-04: CLI Error Handling and Execution Barrier

#### Original Vulnerability
The CLI dispatcher caught `Exception $e`. In PHP 8, critical runtime issues such as `TypeError`, `ParseError`, `ArgumentCountError`, and `AssertionError` extend `Error` (which implements `Throwable`), bypassing `catch ( Exception $e )` and resulting in an uncaught fatal crash rather than a controlled, logged fail-closed response.
Additionally, missing or unknown arguments defaulted to displaying the help message but returned exit code 0.

#### Hardened Implementation (v1.2)
1. Replaced all dispatcher catch blocks with `catch ( Throwable $e )`.
2. Changed the default/help branch to return `exit( 1 )` whenever an invalid or missing action is supplied, ensuring scripts calling the CLI fail closed if arguments are malformed.
3. Re-verified the execution barrier: `import` strictly requires `--gate4-user-approved-execute`. Without this flag, the harness prints a critical safety message and exits with code 1.

#### Evidence
- Test 50: CLI unknown action exits with code 1.
- Test 51: CLI import attempt without `--gate4-user-approved-execute` exits with code 1.
- Test 52: CLI rollback attempt without manifest path argument exits with code 1.

---

## 4. COMPLETE REGRESSION TEST SUITE (52 TESTS)

The test suite was executed using PHP 8.2.29 CLI in an isolated sandbox environment. All 52 tests executed cleanly.

```
Execution Command:
& "C:\Users\Vu Cong Minh\AppData\Roaming\Local\lightning-services\php-8.2.29+0\bin\win64\php.exe" "C:\Users\Vu Cong Minh\.gemini\antigravity-ide\brain\e07f6d66-66d4-4d43-be80-c303a02032b7\scratch\test_harness_php.php"

Exit Code: 0
Total Executed: 52
Passed: 52 (100%)
Failed: 0
```

### Complete Test Inventory

| # | Category | Test Name | Tested Method | Execution Type | Result |
| :--- | :--- | :--- | :--- | :--- | :--- |
| 01 | Preflight | Working CSV non-existent file failure | `preflight_verify()` | Real Production Code | PASS |
| 02 | Preflight | Working CSV valid hash verification | `preflight_verify()` | Real Production Code | PASS |
| 03 | Preflight | Real duplicate SKU detection in DB | `preflight_verify()` | Real Production Code | PASS |
| 04 | Preflight | Real case-variant SKU collision | `preflight_verify()` | Real Production Code | PASS |
| 05 | Preflight | DB query failure fails closed | `preflight_verify()` | Real Production Code | PASS |
| 06 | Barrier | No importer execution after failed preflight | `import()` | Real Production Code | PASS |
| 07 | Recovery | Exception during import recorded in manifest | `import()` | Real Production Code | PASS |
| 08 | Recovery | Automatic rollback cleans created objects | `import()` | Real Production Code | PASS |
| 09 | Recovery | Secondary rollback failure handling | `import()` | Real Production Code | PASS |
| 10 | Hooks | Hooks attached during run & detached in finally | `import()` | Real Production Code | PASS |
| 11 | Hooks | Hooks detached even on fatal exception | `import()` | Real Production Code | PASS |
| 12 | Manifest | Manifest target database mismatch rejected | `rollback()` | Real Production Code | PASS |
| 13 | Manifest | Manifest table prefix mismatch rejected | `rollback()` | Real Production Code | PASS |
| 14 | Manifest | Manifest CSV hash mismatch rejected | `rollback()` | Real Production Code | PASS |
| 15 | Manifest | Manifest project mismatch rejected | `rollback()` | Real Production Code | PASS |
| 16 | Path Guard | Manifest path traversal `..` rejected | `validate_manifest_path()` | Real Production Code | PASS |
| 17 | Path Guard | Non-existent manifest path rejected | `validate_manifest_path()` | Real Production Code | PASS |
| 18 | Path Guard | Valid isolated manifest path accepted | `validate_manifest_path()` | Real Production Code | PASS |
| 19 | Reconciliation | Valid fixture passes post-import reconciliation | `reconcile_import()` | Real Production Code | PASS |
| 20 | Reconciliation | Parent count mismatch (20 vs 22) rejected | `reconcile_import()` | Real Production Code | PASS |
| 21 | Reconciliation | Variation count mismatch (41 vs 42) rejected | `reconcile_import()` | Real Production Code | PASS |
| 22 | Reconciliation | Duplicate SKU rejected | `reconcile_import()` | Real Production Code | PASS |
| 23 | Reconciliation | Incorrect stock total (440 vs 449) rejected | `reconcile_import()` | Real Production Code | PASS |
| 24 | Reconciliation | Orphan variation rejected | `reconcile_import()` | Real Production Code | PASS |
| 25 | Reconciliation | Category count mismatch (6 vs 7) rejected | `reconcile_import()` | Real Production Code | PASS |
| 26 | Reconciliation | Invalid attribute mapping rejected | `reconcile_import()` | Real Production Code | PASS |
| 27 | Manifest | Corrupted JSON manifest rejection | `rollback()` | Real Production Code | PASS |
| 28 | Recovery | Interrupted process recovery from disk manifest | `rollback()` | Real Production Code | PASS |
| 29 | Idempotency | Rollback idempotency (called twice safely) | `rollback()` | Real Production Code | PASS |
| 30 | Recovery | Rollback resumption after partial crash | `rollback()` | Real Production Code | PASS |
| 31 | Attachment | Pre-existing attachment ID 5 protected | `rollback()` | Real Production Code | PASS |
| 32 | Filter | HTTP request timeout filter enforcement | `apply_request_timeout()` | Real Production Code | PASS |
| 33 | G3-01 Path | Directory path rejected | `validate_manifest_path()` | Real Production Code | PASS |
| 34 | G3-01 Path | Sibling directory `manifests_evil` rejected | `validate_manifest_path()` | Real Production Code | PASS |
| 35 | G3-01 Path | Permitted subdirectory valid JSON accepted | `validate_manifest_path()` | Real Production Code | PASS |
| 36 | G3-01 Path | Invalid extension `.json.bak` rejected | `validate_manifest_path()` | Real Production Code | PASS |
| 37 | G3-01 Path | Null byte in path rejected | `validate_manifest_path()` | Real Production Code | PASS |
| 38 | G3-02 Ownership| Injected unowned product ID rejected | `rollback()` | Real Production Code | PASS |
| 39 | G3-02 Ownership| Product owned by foreign run protected | `rollback()` | Real Production Code | PASS |
| 40 | G3-02 Ownership| Injected unowned variation ID rejected | `rollback()` | Real Production Code | PASS |
| 41 | G3-02 Ownership| Variation linked to foreign parent rejected | `rollback()` | Real Production Code | PASS |
| 42 | G3-02 Ownership| Malformed non-integer ID in collection rejected| `rollback()` | Real Production Code | PASS |
| 43 | G3-02 Ownership| Ineligible manifest status rejected | `rollback()` | Real Production Code | PASS |
| 44 | G3-03 Reconcile| Exactly 27 valid category associations verified| `reconcile_import()` | Real Production Code | PASS |
| 45 | G3-03 Reconcile| Missing category association on parent rejected | `reconcile_import()` | Real Production Code | PASS |
| 46 | G3-03 Reconcile| Extra category association on parent rejected | `reconcile_import()` | Real Production Code | PASS |
| 47 | G3-03 Reconcile| Wrong category mapping on parent rejected | `reconcile_import()` | Real Production Code | PASS |
| 48 | G3-03 Reconcile| Empty SKU on imported product rejected | `reconcile_import()` | Real Production Code | PASS |
| 49 | G3-03 Reconcile| Parent product with zero categories rejected | `reconcile_import()` | Real Production Code | PASS |
| 50 | G3-04 CLI | CLI unknown action exits with code 1 | CLI Dispatch Branch | Real Process Spawn | PASS |
| 51 | G3-04 CLI | CLI import without Gate 4 flag exits code 1 | CLI Dispatch Branch | Real Process Spawn | PASS |
| 52 | G3-04 CLI | CLI rollback without manifest exits code 1 | CLI Dispatch Branch | Real Process Spawn | PASS |

---

## 5. READ-ONLY LIVE DATABASE BASELINE

A direct query of the live MySQL database on `127.0.0.1:10011` was performed using PHP 8.2.29 with `mysqli`.

```
Execution Command:
& "C:\Users\Vu Cong Minh\AppData\Roaming\Local\lightning-services\php-8.2.29+0\bin\win64\php.exe" -d extension_dir="C:\Users\Vu Cong Minh\AppData\Roaming\Local\lightning-services\php-8.2.29+0\bin\win64\ext" -d extension=mysqli "C:\Users\Vu Cong Minh\.gemini\antigravity-ide\brain\e07f6d66-66d4-4d43-be80-c303a02032b7\scratch\check_live_db_baseline.php"

Exit Code: 0
```

### Live Database Verified State
- **Database:** `local` (MySQL 8.4.0)
- **Table Prefix:** `wp_`
- **Products (`post_type = 'product'`):** `0`
- **Variations (`post_type = 'product_variation'`):** `0`
- **SKU Metadata records (`_sku != ''`):** `0`
- **Attachments (`post_type = 'attachment'`):** `1`
  - Attachment ID: `5`
  - Post Title: `woocommerce-placeholder`
  - Post Name: `woocommerce-placeholder`
  - GUID: `http://localhost:10010/wp-content/uploads/2026/10/woocommerce-placeholder.webp`
- **Global Attributes (`wp_woocommerce_attribute_taxonomies`):**
  - ID 1: `color` (`Color`, select)
  - ID 2: `size` (`Size`, select)
  - ID 3: `brand` (`Brand`, select)
- **Taxonomy Term Counts:**
  - `pa_brand`: 5 terms
  - `pa_color`: 3 terms
  - `pa_size`: 3 terms
  - `product_cat`: 8 terms (7 dataset categories + 1 default `Uncategorized`)

**Conclusion:** Zero database mutations occurred during Gate 3G. The live database remains in its clean pre-import baseline.

---

## 6. SNAPSHOT INTEGRITY & RESTORABILITY STATUS

- **Snapshot File:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\backups\stage_1e_gate2_pre_import_20261016_223502.sql`
- **File Size:** `1,236,031 bytes`
- **SHA-256 Hash:** `9152E46BB462C321EDD7778A6DA7889450A90301BD1627682D10345A5BDCC8A3`
- **SHA-256 Integrity:** **VERIFIED** (matches baseline recorded at Gate 2 and Gate 3F).
- **Snapshot Restorability Classification:** **UNVERIFIED**.
  - In strict compliance with audit governance, the snapshot has not been restored into the live database.
  - Rollback safety in the application layer does not constitute proof of physical SQL snapshot restorability. Restorability can only be verified in a separate, explicitly authorized test utilizing an isolated auxiliary MySQL instance.

---

## 7. REMAINING RISKS AND UNVERIFIED PROPERTIES

1. **Dry-Run vs Real WP_Query Context:**
   Unit and adversarial tests were executed against an isolated in-memory WordPress mock to avoid mutating the live database. Although the production methods were exercised directly, WordPress/WooCommerce core interactions in the real web runtime (such as object cache invalidation and third-party plugin action hooks) will be exercised for the first time during authorized Gate 4 execution.
2. **Snapshot Restoration Auxiliary Proof:**
   Restorability of `stage_1e_gate2_pre_import_20261016_223502.sql` remains unverified on an auxiliary instance.
3. **Execution Dependency on Token:**
   Import execution is strictly blocked unless `--gate4-user-approved-execute` is supplied on the CLI. The barrier is proven intact.

---

## 8. FINAL VERDICT

### **VERDICT: PASS**

**Justification:**
- **G3-01 Resolved:** Manifest path containment strictly enforces directory boundary separators, realpath validation, null-byte rejection, and extension checks. Sibling directory prefix attacks (`manifests_evil`) are completely blocked.
- **G3-02 Resolved:** Run-scoped postmeta tagging (`_controlled_import_run_id`) is embedded in object creation. A mandatory pre-deletion ownership audit strictly rejects deletion of unowned, foreign, or pre-existing objects, failing closed.
- **G3-03 Resolved:** Category reconciliation verifies actual persisted parent product taxonomy relationships against the complete canonical 27-association matrix across 7 categories. Discrepancies immediately prevent `COMPLETED` status.
- **G3-04 Resolved:** CLI error handling catches `Throwable`, and malformed or missing commands exit closed with code 1.
- **Regression Suite:** 52/52 tests PASSED.
- **Live Database:** Completely untouched (0 products, 0 variations, 0 SKUs, attachment ID 5 intact).
- **Execution Barrier:** Intact.

---

## 9. REPORT METRICS & SIGN-OFF

- **Report Path:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\app\public\wp-content\plugins\docs\stage_1e_3g_7_gate3g_security_hardening_report.md`
- **Generated On:** 2026-10-18T20:37:00+07:00
- **Auditor Role:** Senior WooCommerce/PHP Security Engineer, Database Integrity Engineer, and QA Auditor
- **Mandatory Stop:** No further actions, imports, rollbacks, or snapshot restorations may be undertaken without explicit user authorization.
