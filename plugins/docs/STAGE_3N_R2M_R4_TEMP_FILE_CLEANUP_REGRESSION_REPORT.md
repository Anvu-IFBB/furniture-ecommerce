# STAGE 3N-R2M-R4 — TARGETED TEMP-FILE CLEANUP FIX & REGRESSION REPORT

**Project:** ATELIER — WordPress / WooCommerce Furniture E-commerce  
**Workspace:** `C:\Users\Vu Cong Minh\Local Sites\furniture-ecommerce\`  
**Target Importer:** `app\public\wp-content\plugins\run_controlled_import.php`  
**Test Suite:** `app\public\wp-content\plugins\scratch\test_r2m_isolated_suite.php`  
**Audit Basis:** `ANTIGRAVITY PROMPT — STAGE 3N-R2M-R3 Independent Review`  
**Mode:** Targeted Remediation in Isolated Tests Only — ZERO Production Execution  
**Date:** 2026-10-19  
**Decision:** **`PASS`** (Targeted Temp-File Cleanup Fully Hardened & Validated Across 53/53 Isolated Tests; Gate 4 Remains Strictly BLOCKED)

---

## 1. EXECUTIVE DECISION

Stage 3N-R2M-R4 successfully resolved the subtle failure-path edge case identified during the R2M-R3 independent review regarding temporary manifest file handling in `flush_manifest()`.

### Summary of Completed Remediations:
1. **Unconditional Owned-Temp Cleanup:** In `flush_manifest()`, the `finally` block previously checked a boolean flag (`$tmp_created`) that was only set *after* `file_put_contents()` returned. If `file_put_contents()` partially wrote bytes or aborted midway, `$tmp_created` remained `false`, potentially leaving partial files uncleaned. In R2M-R4, cleanup checks `if ( ! $published && file_exists( $tmp_path ) )`, guaranteeing that any file created by the current invocation is unlinked upon write failure, short write, or rename failure.
2. **Short-Write Detection:** Added explicit byte length validation comparing `file_put_contents()` return value against `strlen($json_str)`.
3. **Stale Warning Prevention:** Added `error_clear_last()` immediately prior to `@rename()` so that `error_get_last()` strictly captures errors from the rename operation itself.
4. **Isolated Regression Verification:** 4 new focused tests (`R2M-MAN-14` through `R2M-MAN-17`) were added to the test suite. All **53 out of 53 tests passed (100%)** with zero regressions.

**Production import and rollback remain STRICTLY BLOCKED.**

---

## 2. PRE-CHANGE AND POST-CHANGE SOURCE IDENTITIES & HASHES

Prior to making any edits, byte-identical pre-change backups were archived in `scratch/backup_r2m_r4_pre_remediation/`. Physical byte-level hashing confirms:

| File Description | Relative Path | Pre-Change (Baseline) SHA-256 | Pre Size | Post-Change Final SHA-256 | Final Size | Final Lines |
|---|---|---|---|---|---|---|
| **Target Importer** | `run_controlled_import.php` | `3C532C46F919A82FB180917F3B8E1575E9E7177587498D4989DDA5028D90502E` | 80,937 B | `DBF3F73CCA10D388962BD096BC18E7899F5E79089907C92121CA376106F92F02` | 81,532 B | 1,728 |
| **Isolated Test Suite** | `scratch/test_r2m_isolated_suite.php` | `12EE2370F4BE6F55957F63832A3EC09353BE8654F9E4094F9C79109FE4898955` | 45,935 B | `C8843ED564C18F2156A3F303194E285B11BB6DADF922B5FF4BD40753DFDBD779` | 52,906 B | 1,084 |
| **Pre-Change Backup** | `scratch/backup_r2m_r4_pre_remediation/run_controlled_import.php` | `3C532C46F919A82FB180917F3B8E1575E9E7177587498D4989DDA5028D90502E` | 80,937 B | `3C532C46F919A82F...` | 80,937 B | 1,722 |
| **R2M-R2 Report** | `docs/STAGE_3N_R2M_R2_TARGETED_REMEDIATION_REPORT.md` | `0EFAD262FA06341B9C261075B08FFC66188DC74CFB412C3F5EF927FE563A4647` | 21,565 B | `0EFAD262FA06341B...` | 21,565 B | 363 |

---

## 3. EXACT SOURCE DIFF SUMMARY

The code changes were strictly confined to `flush_manifest()` in `run_controlled_import.php`:

```diff
--- a/scratch/backup_r2m_r4_pre_remediation/run_controlled_import.php
+++ b/run_controlled_import.php
@@ -706,10 +706,10 @@ class Controlled_Import_Harness {
 
     /**
      * Atomic manifest write using temporary file and rename with fail-closed cleanup.
-     * Stage 3N-R2M-R2 (Remediation A): Handles write, flush, and rename failures explicitly.
+     * Stage 3N-R2M-R4 (Remediation A): Handles write, flush, short writes, and rename failures explicitly.
      * Uses per-invocation unique temporary files to prevent cross-process collisions.
-     * In the event of rename failure, deletes the specific temporary file without affecting
-     * existing destination manifests or temporary files of other processes.
+     * In the event of any publication failure, cleans up the specific temporary file owned
+     * by this invocation, without affecting existing destination manifests or foreign temporary files.
      */
     public function flush_manifest() {
         if ( empty( $this->manifest_path ) || empty( $this->manifest_data ) ) {
@@ -721,16 +721,22 @@ class Controlled_Import_Harness {
             throw new RuntimeException( "Failed to encode manifest data to JSON: " . json_last_error_msg() );
         }
 
+        $expected_bytes = strlen( $json_str );
         // Generate unique per-call temporary file to prevent collision with other processes or existing runs
         $tmp_path = $this->manifest_path . '.tmp.' . getmypid() . '.' . bin2hex( random_bytes( 4 ) );
-        $tmp_created = false;
+        $published = false;
 
         try {
-            $bytes_written = file_put_contents( $tmp_path, $json_str, LOCK_EX );
-            if ( false === $bytes_written ) {
-                throw new RuntimeException( "Failed to write temporary manifest file: {$tmp_path}" );
+            // Write with exclusive lock to prevent local process race conditions
+            $bytes_written = @file_put_contents( $tmp_path, $json_str, LOCK_EX );
+            if ( false === $bytes_written || $bytes_written !== $expected_bytes ) {
+                $last_err = error_get_last();
+                $err_detail = isset( $last_err['message'] ) ? $last_err['message'] : ( false === $bytes_written ? 'file_put_contents returned false' : "short write: wrote {$bytes_written} of {$expected_bytes} bytes" );
+                throw new RuntimeException( "Failed to write temporary manifest file '{$tmp_path}': {$err_detail}" );
             }
-            $tmp_created = true;
+
+            // Clear previous PHP warnings so error_get_last() is strictly from rename()
+            error_clear_last();
 
             // Windows Filesystem Note: rename() can fail if destination is open or locked by an external process
             $renamed = @rename( $tmp_path, $this->manifest_path );
@@ -739,10 +745,10 @@ class Controlled_Import_Harness {
                 $err_msg = isset( $last_err['message'] ) ? $last_err['message'] : 'destination may be locked or unwritable';
                 throw new RuntimeException( "Failed to publish manifest file from '{$tmp_path}' to '{$this->manifest_path}': {$err_msg}" );
             }
-            $tmp_created = false; // Successfully promoted to target manifest
+            $published = true; // Successfully promoted to target manifest
         } finally {
-            // Clean up temporary file created by this call if publication failed
-            if ( $tmp_created && file_exists( $tmp_path ) ) {
+            // Clean up temporary file created by this invocation if publication did not complete
+            if ( ! $published && file_exists( $tmp_path ) ) {
                 @unlink( $tmp_path );
             }
         }
```

---

## 4. TEST EXECUTION & EVIDENCE

### 1. PHP Syntax Check
```powershell
& "C:\Users\Vu Cong Minh\AppData\Roaming\Local\lightning-services\php-8.2.29+0\bin\win64\php.exe" -l run_controlled_import.php
```
- **Exit Code:** `0`
- **Output:** `No syntax errors detected in run_controlled_import.php`

### 2. Isolated Regression Test Suite Execution
```powershell
& "C:\Users\Vu Cong Minh\AppData\Roaming\Local\lightning-services\php-8.2.29+0\bin\win64\php.exe" scratch/test_r2m_isolated_suite.php
```
- **Exit Code:** `0`
- **Output:**
```text
====================================================================
 STAGE 3N-R2M: ISOLATED REGRESSION TEST SUITE
 Sandbox directory: C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/app/public/wp-content/plugins/scratch/sandbox_r2m_1792419660
 Target importer:   C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/app/public/wp-content/plugins/run_controlled_import.php
====================================================================

[PASS] R2M-PATH-01: Correct WordPress root resolution
[PASS] R2M-PATH-02: Incorrect WordPress root fails closed
[PASS] R2M-PATH-03: Detects missing wp-load.php
[PASS] R2M-PATH-04: Invalid anchor file fails closed
[PASS] R2M-PATH-05: Creates missing manifest directory safely
[PASS] R2M-PATH-06: Valid manifest inside allowed root resolves
[PASS] R2M-PATH-07: Manifest outside allowed root rejected
[PASS] R2M-PATH-08: Relative traversal rejected
[PASS] R2M-PATH-09: Absolute external path rejected
[PASS] R2M-PATH-10: Windows separator normalized to forward slash
[PASS] R2M-PATH-11: Drive letter normalized to uppercase
[PASS] R2M-PATH-12: UNC paths rejected
[PASS] R2M-PATH-13: Null-byte injection rejected
[PASS] R2M-PATH-14: Missing manifest on rollback throws file not found
[PASS] R2M-MAN-01: Valid manifest initialization writes JSON to disk
[PASS] R2M-MAN-02: Invalid JSON rejected on rollback
[PASS] R2M-MAN-03: Invalid schema rejected on rollback
[PASS] R2M-MAN-04: Non-JSON extension rejected by path validator
[PASS] R2M-MAN-05: Atomic manifest flush succeeds without leaving .tmp
[PASS] R2M-MAN-06: Unwritable path fails closed
[PASS] R2M-MAN-07: Idempotent rollback on already ROLLED_BACK manifest returns 0 deletions
[PASS] R2M-MAN-08: Rollback rejects manifest claiming pre-existing attachment ID 5
[PASS] R2M-MAN-09: Harness binds to correct manifest directory
[PASS] R2M-MAN-10: Failed rollback does not delete or touch unrelated manifests
[PASS] R2M-MAN-11: rename() failure triggers cleanup of temporary file and preserves destination
[PASS] R2M-MAN-12: Temporary file belonging to another process is NOT deleted on failure
[PASS] R2M-MAN-13: Rerun of manifest flush succeeds cleanly without corrupting valid manifest
[PASS] R2M-MAN-14: Write failure after partial write attempts to clean up temporary file
[PASS] R2M-MAN-15: Cleanup handling does not replace original write or rename exception
[PASS] R2M-MAN-16: Pre-existing destination manifest remains unchanged after publication failure
[PASS] R2M-MAN-17: Foreign temporary file sharing same base manifest name remains untouched
[PASS] R2M-BOOT-01: CLI help exits code 1 with Usage and zero bootstrap side effects
[PASS] R2M-BOOT-02: Invalid CLI action exits code 1 with Usage and zero bootstrap
[PASS] R2M-BOOT-03: Import without gate4 authorization flag halts before bootstrap
[PASS] R2M-BOOT-04: Rollback without manifest argument halts before bootstrap
[PASS] R2M-BOOT-05: bootstrap_wordpress_runtime_safely fails closed on missing wp-load.php
[PASS] R2M-BOOT-06: Database identity mismatch fails closed
[PASS] R2M-BOOT-07: Table prefix mismatch fails closed
[PASS] R2M-BOOT-08: Missing WooCommerce fails closed
[PASS] R2M-BOOT-09: Preflight verify detects missing WooCommerce
[PASS] R2M-BOOT-10: Preflight verify produces zero database mutations
[PASS] R2M-BOOT-11: Strict CLI argument count rejects verify with extraneous arguments
[PASS] R2M-BOOT-12: Strict CLI argument count rejects rollback with extraneous arguments
[PASS] R2M-BOOT-13: Strict CLI argument count rejects import with extraneous arguments
[PASS] R2M-BOOT-14: Zero CLI arguments displays Usage banner and exits code 1 without bootstrap
[PASS] R2M-BOOT-15: Wrong authorization token on import displays SAFETY STOP and exits code 1
[PASS] R2M-BOOT-16: Database identity preflight verifies database name local and prefix wp_
[PASS] R2M-REC-01: Discovers and deletes unmanifested tagged orphan product
[PASS] R2M-REC-02: Untagged Window 2 object is preserved (fail-closed, not deleted)
[PASS] R2M-REC-03: Rollback rejects deleting post owned by different run_id
[PASS] R2M-REC-04: Repeated rollback preserves unrelated records and behaves idempotently
[PASS] R2M-REC-05: Rollback failure preserves manifest status and logs error
[PASS] R2M-REC-06: Dataset reconciliation invariants preserved (22 parents, 42 vars, 64 skus, 449 stock, 7 cats)

====================================================================
 TEST SUITE COMPLETE
 Passed: 53 / 53
 Failed: 0
 Sandbox directory cleaned up: YES
====================================================================
```

---

## 5. COMPLETE TEST MATRIX (53 TESTS)

| Group | Test IDs | Count | Scope | Result |
|---|---|---|---|---|
| **Path Resolution** | `R2M-PATH-01` to `14` | 14 | Roots, Traversal (`..`), UNC paths, Null bytes, Drive letters | **PASS** (14/14) |
| **Manifest Integrity & Lifecycle** | `R2M-MAN-01` to `10` | 10 | Schema, JSON validation, Pre-existing attachment ID 5, Idempotent rollback | **PASS** (10/10) |
| **Temp-File Cleanup Hardening** | `R2M-MAN-11` to `17` | 7 | Rename failure cleanup, Foreign PID temp preservation, Retry cleanly, Partial-write cleanup, Exception preservation, Pre-existing manifest untouched, Same-base temp file protection | **PASS** (7/7) |
| **CLI Dispatcher & Pre-Bootstrap** | `R2M-BOOT-01` to `04`, `R2M-BOOT-11` to `15` | 9 | Help, Invalid actions, Extraneous arguments, Zero arguments, Missing arguments, Wrong authorization token | **PASS** (9/9) |
| **Bootstrap Safety & Database Mock** | `R2M-BOOT-05` to `10`, `R2M-BOOT-16` | 7 | Missing wp-load, Bad DB name, Bad prefix, Missing WooCommerce, 0 DB mutations, Preflight DB structure | **PASS** (7/7) |
| **Recovery & Invariants** | `R2M-REC-01` to `06` | 6 | Tagged orphan discovery, Window 2 preservation, Foreign run rejection, Dataset reconciliation baselines | **PASS** (6/6) |

**Total:** 53 Tests | **Passed:** 53 (100%) | **Failed:** 0 | **Skipped:** 0

---

## 6. SCOPE VERIFICATION & ISOLATION EVIDENCE

1. **Strictly Confined Edits:** Only lines 708–752 of `run_controlled_import.php` and `scratch/test_r2m_isolated_suite.php` were modified.
2. **Untouched Systems:**
   - CSV parsing logic: Untouched.
   - Product & variation creation: Untouched.
   - Stock handling & canonical inventory matrix: Untouched.
   - Taxonomy & category mappings: Untouched.
   - Locked dataset reconciliation constants: Untouched.
   - Reversible rollback deletion order: Untouched.
   - User theme files (`twentytwentyfive-child`): Completely untouched.
3. **Isolation Evidence:**
   - Zero TCP socket connections were made to port `10011`.
   - Zero SQL statements were executed against live MySQL database `local`.
   - All tests ran inside temporary scratch sandbox directories that were verified deleted upon completion.
   - Production manifests in `backups/manifests/` were not accessed or altered.

---

## 7. TEST LIMITATIONS & SIMULATION NOTES

1. **Short Write Simulation:** In `R2M-MAN-14`, partial writes were deterministically simulated using a test subclass (`Testable_Short_Write_Harness`) to verify that the `finally` cleanup executes and unlinks partial files. Real physical disk-full exhaustion was not triggered on the host system to prevent OS destabilization.
2. **Rename Collision Simulation:** In `R2M-MAN-11`, destination directory collisions were used to trigger an infallible `rename()` failure on Windows NTFS, asserting that `@unlink()` successfully cleans up the temporary file while keeping the destination intact.
3. **Live Database Integration:** As required by safety invariants, real connection to MySQL on port `10011` was deliberately omitted from this stage. Live preflight checks remain formally deferred to a dedicated operational stage.

---

## 8. REMAINING RISKS & UNPROVISIONED ITEMS

1. **Restricted MySQL User:** The site in LocalWP continues to connect via `root` credentials in `wp-config.php`. A least-privilege user has not yet been provisioned.
2. **Live Database Snapshot Restoration:** Physical table restoration of the baseline SQL snapshot against MySQL has not been tested in R2M stages to avoid live database mutation.

---

## 9. FINAL GATE 4 DECISION

### Gate 4 Controlled Production Import: **STRICTLY BLOCKED & NOT AUTHORIZED**

Stage 3N-R2M-R4 has achieved a complete **PASS** on all isolated remediation objectives:
- Temporary file cleanup is fail-closed, byte-validated, and protected against cross-process interference.
- CLI argument count enforcement is strict and halts before WordPress bootstrap.
- All 53 regression tests pass in 100% isolation.

**However, this stage DOES NOT authorize production execution.**  
No CSV import, live rollback, snapshot restoration, or live site mutation occurred. All operations are paused pending explicit human review and separate authorization for the Gate 4 operational preflight runbook.
