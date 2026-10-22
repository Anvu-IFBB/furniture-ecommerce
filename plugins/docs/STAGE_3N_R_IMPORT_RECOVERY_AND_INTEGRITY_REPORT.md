# STAGE 3N-R — IMPORT RECOVERY, PER-SKU RECONCILIATION & ROLLBACK INTEGRITY REPORT

**Project:** `furniture-ecommerce` (ATELIER)  
**Execution Timestamp:** 2026-10-19T11:25:00+07:00  
**WordPress Core:** 7.1.3 | **WooCommerce:** 11.2.1 | **PHP:** 8.2.29 | **MySQL:** 8.4.0 (`127.0.0.1:10011`, DB: `local`)  
**Active Theme:** Twenty Twenty-Five Child (`twentytwentyfive-child`)  
**Operating Mode:** Controlled Remediation & Isolated Regression Testing (Zero Live DB Mutations)  
**Verdict:** `PASS` (Remediation & Isolated Test Scope; Production Import Remains Strictly Unauthorized)

---

## 1. Baseline & Git Status

Prior to any modifications, project baseline and Git working tree status were audited. In strict adherence to Safety Rule #6, **zero git commit, reset, checkout, stash, or clean** operations were executed.

### Git Status Output
```text
On branch master
Changes not staged for commit:
	modified:   ../themes/twentytwentyfive-child/functions.php
	modified:   ../themes/twentytwentyfive-child/style.css
	modified:   ../themes/twentytwentyfive-child/theme.json
Untracked files:
	run_controlled_import.php
	docs/...
```

---

## 2. Files Read, Backed Up & Modified

### 2.1 File Inventory
1. **Importer Harness:**  
   `C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/app/public/wp-content/plugins/run_controlled_import.php`
2. **Automated Test Harness:**  
   `C:/Users/Vu Cong Minh/.gemini/antigravity-ide/brain/e07f6d66-66d4-4d43-be80-c303a02032b7/scratch/test_harness_php.php`
3. **Approved Working CSV (Locked Source of Truth):**  
   `C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/data-preparation/woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv`

### 2.2 Backup Integrity & Post-Edit Hashes

All preflight backups were written to the persistent session directory:  
`C:/Users/Vu Cong Minh/.gemini/antigravity-ide/brain/e07f6d66-66d4-4d43-be80-c303a02032b7/backups_stage_3nr_20261019_110000/`

| File | Baseline Pre-Edit Hash (SHA-256) | Post-Edit Hash (SHA-256) | Status |
| :--- | :--- | :--- | :--- |
| `woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv` | `D2C8566F334A767887F57C4A15EA456CFC5E117767FCFE46C599CC754BC2BBA0` | `D2C8566F334A767887F57C4A15EA456CFC5E117767FCFE46C599CC754BC2BBA0` | **UNTOUCHED (100% Locked)** |
| `run_controlled_import.php` | `4B953754BB20835A64E8E17E602DDD329A254877C741F8712E63570FC029A754` (51,874 bytes) | `C3B1BF769CFF9127D5F7FA5B0DF23E798A36C0F5524E39C8CBC90102F03E66C3` (67,418 bytes) | **Hardened (v1.3)** |
| `test_harness_php.php` | `416CBB232F43A78DD033A6B29BDDC55804209D09ACC16E55AE5A039466BD6A80` (56,713 bytes) | `05066E55CA4D508D3A9A27994697F6BCE63148D71ADA9B8354A26930C26A39B8` (89,686 bytes) | **Expanded (85 Tests)** |

---

## 3. Findings Remediation Summary

### F-3N-02 (HIGH PRIORITY) — Crash Recovery & Unmanifested Object Ownership
- **Problem:** When an import crashed after object creation but before the object ID was appended to the disk manifest, rollback relying strictly on manifest ID arrays would leave behind orphan products, variations, or attachments.
- **Remediation Implemented:**
  1. Implemented `find_posts_by_ownership_tag( $run_id )` to actively query the database for all posts, variations, and attachments tagged with meta `_controlled_import_run_id = $run_id`.
  2. Integrated database discovery into `rollback()`. Any object with a verified tag matching the run ID is assimilated into the rollback queue before deletion begins.
  3. Pre-existing attachment guard: Objects in `pre_existing_attachments` (e.g. placeholder ID 5) are strictly protected and never assimilated or deleted.
  4. Enforced strict fail-closed crash boundary: Objects created prior to receiving their ownership tag (Window 2 crash) cannot be distinguished from user-created content without query ambiguity. The design fails closed, preserves the object, and specifies snapshot recovery/manual reconciliation.

### F-3N-01 (MEDIUM PRIORITY) — Exact Per-SKU Inventory Reconciliation
- **Problem:** Previous reconciliation only verified aggregate catalog stock (449) and overall post counts. Compensating errors (e.g. SKU A +5, SKU B -5) would pass undetected.
- **Remediation Implemented:**
  1. Extracted and embedded the exact canonical inventory matrix (`CANONICAL_INVENTORY_MATRIX`) containing all 64 SKUs, product types, parent linkages, and expected stock quantities directly verified from the approved CSV.
  2. Implemented strict line-by-line reconciliation in `reconcile_import()`:
     - Detects unrecognized/foreign SKUs.
     - Validates exact stock quantity per SKU before aggregate summation.
     - Enforces variable parent direct stock rule (direct stock must equal 0).
     - Validates parent SKU linkage for all 42 variations.
     - Verifies non-negative, numeric integer stock values.
     - Validates uniqueness across all imported SKUs.
     - Reconciles full 64 canonical SKUs and 449 catalog stock.

### F-3N-03 (LOW PRIORITY) — Rollback Integrity & Post-Deletion Verification
- **Problem:** Rollback previously called `wp_delete_post()` without verifying return value truthiness, without validating post-deletion database state, and without distinguishing objects that were already deleted from objects where deletion failed.
- **Remediation Implemented:**
  1. Encapsulated deletion into a verified `$delete_and_verify` routine within `rollback()`.
  2. Checks deletion API return value (`wp_delete_post` / `wp_delete_attachment` must return `WP_Post` or `true`).
  3. Executes an immediate post-deletion verification query. If the object still exists in the database, rollback halts immediately, sets manifest status `FAILED_ROLLBACK_INCOMPLETE`, and throws an explicit RuntimeException.
  4. Distinguishes `already_absent` objects for safe, clean idempotency across repeated rollback executions.
  5. Enforces reverse topological deletion order: variations must be deleted and verified gone before parent products are deleted.

---

## 4. Recovery Design & Crash Window Analysis

### 4.1 Chronological Operation Flow
```text
[Preflight Verification]
       │
       ▼
[Initialize Manifest (status: INITIALIZED)]
       │
       ▼
[Attach Real-Time Creation Hooks]
       │
       ▼
[Process Row in WooCommerce Importer]
  ├── (Window 1 Crash) ---> Before object creation
  ├── WC creates post in DB
  ├── (Window 2 Crash) ---> Post exists in DB, NO ownership tag yet
  ├── update_post_meta(_controlled_import_run_id, run_id)
  ├── (Window 3 Crash) ---> Post exists in DB WITH tag, NOT in manifest
  ├── Manifest in-memory array updated
  ├── (Window 4 Crash) ---> Manifest updated in memory, NOT flushed to disk
  ├── flush_manifest() to disk
  └── (Steady State)   ---> Fully recorded in manifest and DB
```

### 4.2 Crash Window Invariant Table

| Crash Window | State of Object | Recovery Behavior | Safety Classification |
| :--- | :--- | :--- | :--- |
| **Window 1 (Pre-creation)** | Object does not exist. | Manifest contains 0 objects; rollback executes as clean 0-deletion no-op. | Fully Automated Recovery |
| **Window 2 (Pre-tag)** | Object exists in DB without `_controlled_import_run_id` meta. | **Fail-closed boundary:** Recovery CANNOT prove ownership without risking deletion of user posts. Rollback preserves the object, reports missing ownership evidence, and stops safely. | **Requires Snapshot Restore or Manual Reconciliation** |
| **Window 3 (Post-tag, Pre-manifest)** | Object exists in DB with valid run ID tag, but absent from disk manifest. | **Automated DB Tag Discovery:** `find_posts_by_ownership_tag()` discovers the object, adds it to the rollback queue, validates type, and deletes it cleanly. | Fully Automated Recovery |
| **Window 4 (Post-manifest, Pre-flush)** | Object exists in DB with tag; manifest updated in memory or partially on disk. | **Merged Recovery:** DB discovery merges unflushed tagged objects with manifest records; deletes variations before parents. | Fully Automated Recovery |

---

## 5. Canonical Per-SKU Inventory Reconciliation Matrix

Ground truth source: `woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv` (SHA-256: `D2C8566F334A767887F57C4A15EA456CFC5E117767FCFE46C599CC754BC2BBA0`).

### 5.1 Simple Products (12 Products — Total Stock: 248)

| SKU | Type | Parent SKU | Stock Quantity | Stock Management | Verified Categories |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `FURN-001` | simple | *(none)* | 25 | yes | Chairs |
| `FURN-002` | simple | *(none)* | 30 | yes | Chairs |
| `FURN-003` | simple | *(none)* | 15 | yes | Tables |
| `FURN-004` | simple | *(none)* | 20 | yes | Tables |
| `FURN-005` | simple | *(none)* | 18 | yes | Sofas |
| `FURN-006` | simple | *(none)* | 12 | yes | Beds, Storage |
| `FURN-007` | simple | *(none)* | 40 | yes | Lighting |
| `FURN-008` | simple | *(none)* | 22 | yes | Lighting |
| `FURN-009` | simple | *(none)* | 50 | yes | Decoration |
| `FURN-010` | simple | *(none)* | 0 | yes | Storage, Decoration |
| `FURN-011` | simple | *(none)* | 16 | yes | Chairs |
| `FURN-012` | simple | *(none)* | 0 | yes | Decoration |

### 5.2 Variable Parent Products (10 Products — Direct Stock: 0)

| SKU | Type | Direct Stock Rule | Expected Child Variations | Verified Categories |
| :--- | :--- | :--- | :--- | :--- |
| `FURN-013` | variable | 0 (No direct stock) | 6 variations | Chairs |
| `FURN-014` | variable | 0 (No direct stock) | 4 variations | Tables |
| `FURN-015` | variable | 0 (No direct stock) | 4 variations | Sofas |
| `FURN-016` | variable | 0 (No direct stock) | 4 variations | Beds |
| `FURN-017` | variable | 0 (No direct stock) | 4 variations | Lighting |
| `FURN-018` | variable | 0 (No direct stock) | 4 variations | Decoration |
| `FURN-019` | variable | 0 (No direct stock) | 4 variations | Storage, Beds |
| `FURN-020` | variable | 0 (No direct stock) | 4 variations | Storage |
| `FURN-021` | variable | 0 (No direct stock) | 4 variations | Chairs, Sofas |
| `FURN-022` | variable | 0 (No direct stock) | 4 variations | Beds, Storage |

### 5.3 Product Variations (42 Variations — Total Stock: 201)

| Variation SKU | Parent SKU | Stock Quantity | Attributes Verified |
| :--- | :--- | :--- | :--- |
| `FURN-013-BLK-S` | `FURN-013` | 8 | Color: Black, Size: S |
| `FURN-013-BLK-M` | `FURN-013` | 6 | Color: Black, Size: M |
| `FURN-013-BLK-L` | `FURN-013` | 5 | Color: Black, Size: L |
| `FURN-013-WHT-S` | `FURN-013` | 5 | Color: White, Size: S |
| `FURN-013-WHT-M` | `FURN-013` | 4 | Color: White, Size: M |
| `FURN-013-BRN-L` | `FURN-013` | 7 | Color: Brown, Size: L |
| `FURN-014-BRN-M` | `FURN-014` | 5 | Color: Brown, Size: M |
| `FURN-014-BRN-L` | `FURN-014` | 4 | Color: Brown, Size: L |
| `FURN-014-WHT-M` | `FURN-014` | 4 | Color: White, Size: M |
| `FURN-014-WHT-L` | `FURN-014` | 3 | Color: White, Size: L |
| `FURN-015-WHT-M` | `FURN-015` | 4 | Color: White, Size: M |
| `FURN-015-WHT-L` | `FURN-015` | 3 | Color: White, Size: L |
| `FURN-015-BLK-M` | `FURN-015` | 3 | Color: Black, Size: M |
| `FURN-015-BRN-L` | `FURN-015` | 4 | Color: Brown, Size: L |
| `FURN-016-WHT-M` | `FURN-016` | 4 | Color: White, Size: M |
| `FURN-016-WHT-L` | `FURN-016` | 3 | Color: White, Size: L |
| `FURN-016-BRN-M` | `FURN-016` | 3 | Color: Brown, Size: M |
| `FURN-016-BRN-L` | `FURN-016` | 2 | Color: Brown, Size: L |
| `FURN-017-BLK-S` | `FURN-017` | 6 | Color: Black, Size: S |
| `FURN-017-BLK-M` | `FURN-017` | 6 | Color: Black, Size: M |
| `FURN-017-BLK-L` | `FURN-017` | 4 | Color: Black, Size: L |
| `FURN-017-WHT-M` | `FURN-017` | 8 | Color: White, Size: M |
| `FURN-018-BRN-S` | `FURN-018` | 10 | Color: Brown, Size: S |
| `FURN-018-BRN-M` | `FURN-018` | 8 | Color: Brown, Size: M |
| `FURN-018-BLK-S` | `FURN-018` | 6 | Color: Black, Size: S |
| `FURN-018-WHT-M` | `FURN-018` | 6 | Color: White, Size: M |
| `FURN-019-BRN-M` | `FURN-019` | 5 | Color: Brown, Size: M |
| `FURN-019-BRN-L` | `FURN-019` | 4 | Color: Brown, Size: L |
| `FURN-019-BLK-M` | `FURN-019` | 3 | Color: Black, Size: M |
| `FURN-019-BLK-L` | `FURN-019` | 2 | Color: Black, Size: L |
| `FURN-020-BLK-S` | `FURN-020` | 6 | Color: Black, Size: S |
| `FURN-020-BLK-M` | `FURN-020` | 6 | Color: Black, Size: M |
| `FURN-020-BLK-L` | `FURN-020` | 4 | Color: Black, Size: L |
| `FURN-020-WHT-M` | `FURN-020` | 5 | Color: White, Size: M |
| `FURN-021-BLK-S` | `FURN-021` | 6 | Color: Black, Size: S |
| `FURN-021-WHT-M` | `FURN-021` | 5 | Color: White, Size: M |
| `FURN-021-BRN-L` | `FURN-021` | 5 | Color: Brown, Size: L |
| `FURN-021-BLK-L` | `FURN-021` | 4 | Color: Black, Size: L |
| `FURN-022-BLK-M` | `FURN-022` | 5 | Color: Black, Size: M |
| `FURN-022-BLK-L` | `FURN-022` | 4 | Color: Black, Size: L |
| `FURN-022-WHT-M` | `FURN-022` | 3 | Color: White, Size: M |
| `FURN-022-WHT-L` | `FURN-022` | 3 | Color: White, Size: L |

**Grand Totals:**
- Simple Stock: **248**
- Variable Parents Direct Stock: **0**
- Variations Stock: **201**
- **Catalog Total Stock: Exactly 449**
- **Unique Canonical SKUs: Exactly 64**

---

## 6. Comprehensive Test Execution Table (85 / 85 PASS)

All 85 tests were executed via PHP 8.2.29 CLI in 100% memory-isolated harness (`Mock_Post_Database`, `Mock_Wpdb`).

```text
Command: "C:\Users\Vu Cong Minh\AppData\Roaming\Local\lightning-services\php-8.2.29+0\bin\win64\php.exe" test_harness_php.php
Result: 85 executed, 85 passed, 0 failed. Exit code: 0.
```

| # | Test Name | Target Finding / Feature | Expected Result | Actual Result | Status |
| :-: | :--- | :--- | :--- | :--- | :-: |
| 01 | Import CSV file existence check | Baseline Safety | Passes on valid CSV path | Verified CSV exists | **PASS** |
| 02 | Working CSV valid hash verification | Baseline Safety | Locked SHA-256 matches | Successfully verified locked SHA-256 | **PASS** |
| 03 | Real `preflight_verify()` duplicate SKU detection | Baseline Preflight | Aborts on DB SKU conflict | Conflicting SKU aborted | **PASS** |
| 04 | Real `preflight_verify()` case-variant SKU collision | Baseline Preflight | Aborts on case-variant conflict | Caught case-variant collision | **PASS** |
| 05 | Real `preflight_verify()` query failure fails closed | Baseline Preflight | Aborts on MySQL error | Caught query failure exception | **PASS** |
| 06 | No importer execution after failed preflight | Baseline Preflight | Importer call count remains 0 | Call count 0 | **PASS** |
| 07 | Exception during import recorded in manifest | Error Handling | Manifest marked FAILED | Recorded error in manifest | **PASS** |
| 08 | Automatic rollback cleans objects after exception | Auto Rollback | Created posts removed | Rollback removed created objects | **PASS** |
| 09 | Secondary rollback failure handling | Error Handling | Marked FAILED_ROLLBACK_INCOMPLETE | Preserved errors and updated status | **PASS** |
| 10 | Hooks attached and detached in finally block | Hook Lifecycle | Detached after success | Hooks confirmed detached | **PASS** |
| 11 | Hooks detached after fatal exception | Hook Lifecycle | Detached after throw | Hooks confirmed detached in finally | **PASS** |
| 12 | Manifest target database mismatch rejected | Environment Binding | Refuses rollback on bad DB | Caught DB mismatch | **PASS** |
| 13 | Manifest table prefix mismatch rejected | Environment Binding | Refuses rollback on bad prefix | Caught table prefix mismatch | **PASS** |
| 14 | Manifest CSV hash mismatch rejected | Environment Binding | Refuses rollback on bad CSV hash | Caught CSV hash mismatch | **PASS** |
| 15 | Manifest project mismatch rejected | Environment Binding | Refuses rollback on bad project | Caught incompatible project | **PASS** |
| 16 | Manifest path traversal rejected | Path Safety | Refuses path traversal | Caught path safety violation | **PASS** |
| 17 | Non-existent manifest path rejected | Path Safety | Refuses missing file | Caught missing file | **PASS** |
| 18 | Valid isolated manifest path accepted | Path Safety | Accepts path in test directory | Correctly validated safe path | **PASS** |
| 19 | Valid fixture passes post-import reconciliation | Baseline Reconciliation | Full reconciliation passes | Reconciled 64 SKUs, 449 stock, 27 assocs | **PASS** |
| 20 | Parent count mismatch (20 parents) rejected | Baseline Reconciliation | Fails if parents != 22 | Caught parent mismatch | **PASS** |
| 21 | Variation count mismatch (41 vars) rejected | Baseline Reconciliation | Fails if variations != 42 | Caught variation mismatch | **PASS** |
| 22 | Duplicate SKU fails reconciliation | Baseline Reconciliation | Rejects duplicate SKU | Caught duplicate SKU | **PASS** |
| 23 | Incorrect stock total fails reconciliation | Baseline Reconciliation | Rejects wrong stock total | Caught stock mismatch | **PASS** |
| 24 | Orphan variation fails reconciliation | Baseline Reconciliation | Rejects variation without parent | Caught orphan variation | **PASS** |
| 25 | Category count mismatch fails reconciliation | Baseline Reconciliation | Fails if categories != 7 | Caught category mismatch | **PASS** |
| 26 | Invalid attribute mapping fails reconciliation | Baseline Reconciliation | Rejects invalid attributes | Caught invalid attributes | **PASS** |
| 27 | Corrupted manifest rejection | Manifest Integrity | Fails closed on malformed JSON | Caught corrupt manifest exception | **PASS** |
| 28 | Interrupted process recovery from disk manifest | Disk Recovery | Recovers and cleans 2 parents | Recovered and deleted parents | **PASS** |
| 29 | Rollback idempotency (called twice) | Idempotency | Second call returns 0 deletions | Safely returned 0 extra deletions | **PASS** |
| 30 | Rollback resumption after partial crash | Resumption | Resumes and finishes parent cleanup | Resumed rollback successfully | **PASS** |
| 31 | Pre-existing attachment protection | Media Safety | Placeholder ID 5 preserved | ID 5 preserved, ID 6 deleted | **PASS** |
| 32 | HTTP request timeout filter enforcement | Timeout Safety | Enforces 15s timeout | Enforced 15 seconds timeout | **PASS** |
| 33 | Directory path rejected as manifest | Path Safety | Rejects directory path | Caught directory rejection | **PASS** |
| 34 | Sibling directory traversal rejected | Path Safety | Rejects sibling directory | Caught sibling directory violation | **PASS** |
| 35 | Permitted subdirectory valid JSON accepted | Path Safety | Accepts valid subfolder manifest | Subfolder manifest accepted | **PASS** |
| 36 | Invalid extension (.json.bak) rejected | Path Safety | Rejects non-JSON extension | Caught invalid extension | **PASS** |
| 37 | Null byte in path rejected | Path Safety | Rejects null byte injection | Caught null byte rejection | **PASS** |
| 38 | Injected unowned product ID rejected | Ownership Audit | Rejects unowned post ID | Rollback refused unowned product | **PASS** |
| 39 | Product owned by another run protected | Ownership Audit | Rejects foreign run product | Protected foreign run post | **PASS** |
| 40 | Unowned variation ID rejected | Ownership Audit | Rejects unowned variation | Rollback refused unowned variation | **PASS** |
| 41 | Variation linked to foreign parent rejected | Ownership Audit | Rejects foreign parent variation | Refused foreign variation | **PASS** |
| 42 | Malformed non-integer ID rejected | Schema Audit | Rejects string/negative ID | Caught malformed ID rejection | **PASS** |
| 43 | Ineligible manifest status rejected | State Machine | Rejects corrupted status | Caught ineligible status rejection | **PASS** |
| 44 | Exactly 27 valid category associations verified | Taxonomy Baseline | Validates 27 associations | Reconciled 27 associations across 7 cats | **PASS** |
| 45 | Missing category association detected | Taxonomy Baseline | Fails if FURN-006 missing Storage | Caught missing association | **PASS** |
| 46 | Extra category association detected | Taxonomy Baseline | Fails if FURN-001 has Lighting | Caught extra association | **PASS** |
| 47 | Wrong category mapping detected | Taxonomy Baseline | Fails if FURN-001 mapped to Tables | Caught wrong mapping | **PASS** |
| 48 | Empty SKU on imported product rejected | SKU Baseline | Fails if SKU is empty string | Caught empty SKU detected | **PASS** |
| 49 | Zero categories on parent rejected | Taxonomy Baseline | Fails if parent has 0 categories | Caught zero categories on parent | **PASS** |
| 50 | CLI unknown action fails closed (exit code 1) | CLI Safety Barrier | Process exits with code 1 | Verified exit code 1 | **PASS** |
| 51 | CLI import without Gate 4 token blocked | CLI Safety Barrier | Safety stop with exit code 1 | Safety stop verified, exit code 1 | **PASS** |
| 52 | CLI rollback without manifest arg exits code 1 | CLI Safety Barrier | Rejects missing argument | Missing arg rejected, exit code 1 | **PASS** |
| 53 | Crash recovery Window 1 (pre-creation no-op) | **F-3N-02** | 0 deleted, manifest ROLLED_BACK | 0 objects deleted, status ROLLED_BACK | **PASS** |
| 54 | Crash recovery Window 2 (pre-tag object preserved) | **F-3N-02** | Untagged object kept at boundary | Untagged preserved; requires snapshot | **PASS** |
| 55 | Crash recovery Window 3 (tagged unmanifested found) | **F-3N-02** | Tag query finds & deletes object | Product ID 1 found and deleted | **PASS** |
| 56 | Crash recovery Window 4 (flushed + unflushed merged) | **F-3N-02** | Merges DB discovery with manifest | Both flushed and unflushed deleted | **PASS** |
| 57 | Corrupt manifest during recovery rejected | **F-3N-02** | Fails closed on unreadable file | Caught: Manifest corrupted/unreadable | **PASS** |
| 58 | Recovery/rollback idempotency verified | **F-3N-02** | 2nd run safely deletes 0 objects | 1st deleted 1, 2nd safely deleted 0 | **PASS** |
| 59 | Foreign run object protected during recovery | **F-3N-02** | Ignores foreign run tag | Foreign object preserved intact | **PASS** |
| 60 | Object without ownership tag preserved | **F-3N-02** | Untagged object untouched | Untagged object safely untouched | **PASS** |
| 61 | Unmanifested parent & variation reverse order | **F-3N-02** | Variation deleted before parent | Variation 2 deleted before parent 1 | **PASS** |
| 62 | Ownership query DB failure fails closed | **F-3N-02** | Aborts immediately on DB error | Caught simulated DB query drop | **PASS** |
| 63 | Canonical dataset reconciles 64 SKUs & 449 stock | **F-3N-01** | Matches canonical matrix 100% | 64 SKUs, 449 stock, status PASSED | **PASS** |
| 64 | Missing expected canonical SKU detected | **F-3N-01** | Fails if canonical SKU missing | Caught missing/foreign SKU | **PASS** |
| 65 | Foreign/unrecognized SKU rejected | **F-3N-01** | Fails on unauthorized SKU | Caught foreign SKU detected | **PASS** |
| 66 | Duplicate SKU rejected | **F-3N-01** | Fails on duplicate SKU | Caught duplicate SKU FURN-001 | **PASS** |
| 67 | Compensating stock error caught (sum = 449) | **F-3N-01** | Fails on individual SKU mismatch | Caught: Stock mismatch for FURN-001 | **PASS** |
| 68 | Variation stock mismatch detected | **F-3N-01** | Fails if variation stock wrong | Caught: Stock mismatch for variation | **PASS** |
| 69 | Variation linked to wrong parent SKU detected | **F-3N-01** | Fails on incorrect parent linkage | Caught: Variation linked to wrong parent | **PASS** |
| 70 | Variable parent direct stock > 0 rejected | **F-3N-01** | Rejects direct parent stock | Caught: Direct parent stock rejected | **PASS** |
| 71 | Negative stock quantity rejected | **F-3N-01** | Rejects negative stock | Caught: Invalid or negative stock | **PASS** |
| 72 | Non-numeric stock value rejected | **F-3N-01** | Rejects non-integer stock string | Caught: Invalid or negative stock | **PASS** |
| 73 | Tampered working CSV hash rejected | **F-3N-01** | Rejects hash change | Caught CSV hash mismatch | **PASS** |
| 74 | Working CSV schema verified (64 rows, SKU/Stock) | **F-3N-01** | Validates CSV columns & rows | CSV header and 64 rows verified | **PASS** |
| 75 | Deletion API success verified by post-delete query | **F-3N-03** | Confirms object absent from DB | Object deleted & confirmed absent | **PASS** |
| 76 | Deletion API failure triggers incomplete status | **F-3N-03** | Marks FAILED_ROLLBACK_INCOMPLETE | Caught failure, status updated | **PASS** |
| 77 | Post-deletion verification failure detected | **F-3N-03** | Halts if post still exists in DB | Caught object still in database | **PASS** |
| 78 | Already-absent object treated as safe no-op | **F-3N-03** | Skips without error, marks done | Marked ROLLED_BACK cleanly | **PASS** |
| 79 | Unexpected post_type triggers fail-closed error | **F-3N-03** | Halts on post_type mismatch | Caught unauthorized post_type | **PASS** |
| 80 | Manifest directory containment & flush failure | **F-3N-03** | Fails closed on write error | Fails closed on unwriteable path | **PASS** |
| 81 | Variation deletion failure halts before parent | **F-3N-03** | Parent preserved if var fails | Parent preserved intact | **PASS** |
| 82 | Parent deletion aborted if variation still in DB | **F-3N-03** | Prevents orphan variations | Parent not deleted; aborts cleanly | **PASS** |
| 83 | Foreign run object rejected by ownership audit | **F-3N-03** | Rejects object with other run ID | Refused foreign object deletion | **PASS** |
| 84 | Second rollback execution clean idempotent no-op | **F-3N-03** | 2nd call returns 0 deletions | 1st deleted 1, 2nd returned 0 | **PASS** |
| 85 | Mixed object deletion states handled accurately | **F-3N-03** | Deletes present, skips absent | ID 2 deleted, ID 1 skipped, ROLLED_BACK | **PASS** |

---

## 7. Test Isolation & Live Database Verification Evidence

### 7.1 Environment Isolation Proof
- An audit of the active MySQL server (`127.0.0.1:10011`) confirmed that only the single production database `local` exists. No secondary or staging database was provisioned.
- In strict conformance with Safety Invariant #10, **zero destructive tests were executed against the active MySQL database**.
- All 85 test scenarios were executed exclusively in PHP memory via `Mock_Post_Database` and `Mock_Wpdb`.

### 7.2 Post-Test Active Database Audit
Direct read-only inspection of the active WordPress database immediately following test execution:

```sql
SELECT post_type, COUNT(*) FROM wp_posts GROUP BY post_type;
SELECT option_name, option_value FROM wp_options WHERE option_name IN ('woocommerce_coming_soon');
```

**Results:**
```text
post_type       COUNT(*)
attachment      1 (Placeholder ID 5)
page            7
post            2
revision        1
wp_navigation   1
product         0
product_variation 0

option_name              option_value
woocommerce_coming_soon  yes
```

**Database Invariant Confirmed:** Zero products imported, zero variations created, zero options changed, WooCommerce Coming Soon mode remains strictly active (`yes`).

---

## 8. Remaining Limitations & Fail-Closed Boundaries

1. **Crash Window 2 (Post-Creation, Pre-Tag) Inherent Boundary:**
   If the PHP process crashes, memory runs out, or power is interrupted at the exact microsecond between WooCommerce creating a post in `wp_posts` and WordPress executing `update_post_meta()` with `_controlled_import_run_id`, the post exists in the database without any ownership evidence.
   - **Enforced Boundary:** The importer **refuses to guess** or delete untagged objects based on creation timestamps, SKU heuristics, or author IDs. Such speculative deletions could destroy legitimate user posts.
   - **Operational Requirement:** In the rare event of a Window 2 crash, recovery must be executed via database snapshot restore (Gate 3I / Gate 2 snapshot) or manual DBA audit.
2. **Transactional Limitations of WordPress/WooCommerce Core:**
   WordPress and WooCommerce core functions (`wc_create_product`, `wp_delete_post`) execute autocommit SQL transactions and fire multiple external actions. They do not execute within a single distributed atomic ACID transaction. The harness compensates for this via durable ownership tagging and pre/post verification gates.

---

## 9. Verdict & Sign-Off

### Verdict: `PASS` (Stage 3N-R Remediation & Isolated Verification Scope)

- **F-3N-02:** Resolved & Verified (Active DB ownership discovery, fail-closed boundaries).
- **F-3N-01:** Resolved & Verified (Exact 64 per-SKU inventory matrix, compensating error detection).
- **F-3N-03:** Resolved & Verified (API return value validation, post-deletion DB verification, idempotency).
- **Total Test Suite:** 85 executed, 85 passed (100%).
- **Live Database Impact:** 0 records modified, 0 products imported, Coming Soon preserved.

---

## 10. Mandatory Stop

> [!CAUTION]
> **MANDATORY STOP ENFORCED**
> - `run_controlled_import.php` was **NOT** executed on the active WordPress database.
> - Gate 4 has **NOT** been started.
> - Zero products have been imported into the live catalog.
> - WooCommerce Coming Soon mode remains **`yes`**.
> - All changes are strictly confined to the importer harness and isolated test suite.
> - Execution has halted completely, awaiting explicit human instruction.
