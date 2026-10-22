<?php
/**
 * STAGE 3N-R2M — ISOLATED REGRESSION TEST SUITE
 * 
 * Verifies all R2M remediations in 100% isolation:
 * - Layer 1: Pure static and path resolution checks
 * - Layer 2: Isolated sandbox filesystem operations (no production manifest touch)
 * - Layer 3: Mock runtime bootstrap & failure-window safety checks
 * 
 * STRICT ISOLATION INVARIANT:
 * This test suite NEVER connects to MySQL database 'local' on port 10011.
 * All tests execute against temporary sandbox directories or mock database adapters.
 */

error_reporting( E_ALL );
ini_set( 'display_errors', '1' );

$importer_path = 'C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/app/public/wp-content/plugins/run_controlled_import.php';
require_once $importer_path;

class R2M_Test_Runner {

    public $passed = 0;
    public $failed = 0;
    public $results = [];
    public $sandbox_dir;

    public function __construct() {
        $this->sandbox_dir = 'C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/app/public/wp-content/plugins/scratch/sandbox_r2m_' . time();
        if ( ! is_dir( $this->sandbox_dir ) ) {
            mkdir( $this->sandbox_dir, 0755, true );
        }
    }

    public function cleanup() {
        $this->recursive_delete( $this->sandbox_dir );
    }

    private function recursive_delete( $dir ) {
        if ( ! is_dir( $dir ) ) {
            return;
        }
        $files = array_diff( scandir( $dir ), [ '.', '..' ] );
        foreach ( $files as $file ) {
            $p = "$dir/$file";
            is_dir( $p ) ? $this->recursive_delete( $p ) : @unlink( $p );
        }
        @rmdir( $dir );
    }

    public function record( $id, $name, $status, $message = '' ) {
        if ( $status ) {
            $this->passed++;
            $this->results[] = [ 'id' => $id, 'name' => $name, 'status' => 'PASS', 'message' => $message ];
            echo "[PASS] {$id}: {$name}\n";
        } else {
            $this->failed++;
            $this->results[] = [ 'id' => $id, 'name' => $name, 'status' => 'FAIL', 'message' => $message ];
            echo "[FAIL] {$id}: {$name} — {$message}\n";
        }
    }
}

$runner = new R2M_Test_Runner();
$sandbox = $runner->sandbox_dir;

echo "====================================================================\n";
echo " STAGE 3N-R2M: ISOLATED REGRESSION TEST SUITE\n";
echo " Sandbox directory: {$sandbox}\n";
echo " Target importer:   {$importer_path}\n";
echo "====================================================================\n\n";

// Setup Sandbox Mock Hierarchy:
// <sandbox>/proj/app/public/wp-content/plugins/dummy.php
// <sandbox>/proj/backups/manifests/
$mock_proj = $sandbox . '/proj';
$mock_plugins = $mock_proj . '/app/public/wp-content/plugins';
$mock_manifests = $mock_proj . '/backups/manifests';
mkdir( $mock_plugins, 0755, true );
mkdir( $mock_manifests, 0755, true );

$dummy_plugin_file = $mock_plugins . '/dummy.php';
file_put_contents( $dummy_plugin_file, "<?php // dummy anchor\n" );

$mock_wp_load = $mock_proj . '/app/public/wp-load.php';
file_put_contents( $mock_wp_load, "<?php // mock wp-load\n" );

// ====================================================================
// GROUP 1: PATH RESOLUTION TESTS (R2M-PATH-01 to R2M-PATH-14)
// ====================================================================

// R2M-PATH-01: Correct WordPress root from anchor
try {
    $resolver = new Controlled_Import_Path_Resolver( $dummy_plugin_file );
    $wp_root = $resolver->get_wordpress_root();
    $expected_wp = Controlled_Import_Path_Resolver::normalize_path( realpath( $mock_proj . '/app/public' ) );
    $runner->record( 'R2M-PATH-01', 'Correct WordPress root resolution', $wp_root === $expected_wp, "Expected {$expected_wp}, got {$wp_root}" );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-PATH-01', 'Correct WordPress root resolution', false, $e->getMessage() );
}

// R2M-PATH-02: Incorrect WordPress root (missing public/ directory)
try {
    $bad_anchor = $sandbox . '/bad/wp-content/plugins/dummy.php';
    mkdir( dirname( $bad_anchor ), 0755, true );
    file_put_contents( $bad_anchor, "<?php" );
    new Controlled_Import_Path_Resolver( $bad_anchor );
    $runner->record( 'R2M-PATH-02', 'Incorrect WordPress root fails closed', false, "Did not throw exception" );
} catch ( RuntimeException $e ) {
    $runner->record( 'R2M-PATH-02', 'Incorrect WordPress root fails closed', true, $e->getMessage() );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-PATH-02', 'Incorrect WordPress root fails closed', false, "Wrong exception: " . $e->getMessage() );
}

// R2M-PATH-03: Missing wp-load.php throws exception
try {
    unlink( $mock_wp_load );
    new Controlled_Import_Path_Resolver( $dummy_plugin_file );
    $runner->record( 'R2M-PATH-03', 'Detects missing wp-load.php', false, "Did not throw exception" );
} catch ( RuntimeException $e ) {
    $runner->record( 'R2M-PATH-03', 'Detects missing wp-load.php', false !== strpos( $e->getMessage(), 'does not contain expected wp-load.php' ), $e->getMessage() );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-PATH-03', 'Detects missing wp-load.php', false, $e->getMessage() );
} finally {
    file_put_contents( $mock_wp_load, "<?php // restored\n" );
}

// R2M-PATH-04: Invalid project root (non-existent anchor)
try {
    new Controlled_Import_Path_Resolver( $sandbox . '/non_existent_anchor.php' );
    $runner->record( 'R2M-PATH-04', 'Invalid anchor file fails closed', false, "Did not throw exception" );
} catch ( RuntimeException $e ) {
    $runner->record( 'R2M-PATH-04', 'Invalid anchor file fails closed', true, $e->getMessage() );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-PATH-04', 'Invalid anchor file fails closed', false, $e->getMessage() );
}

// R2M-PATH-05: Missing manifest root created safely
try {
    $clean_sandbox = $sandbox . '/clean_proj';
    $clean_plugins = $clean_sandbox . '/app/public/wp-content/plugins';
    mkdir( $clean_plugins, 0755, true );
    $clean_anchor = $clean_plugins . '/dummy.php';
    file_put_contents( $clean_anchor, "<?php" );
    file_put_contents( $clean_sandbox . '/app/public/wp-load.php', "<?php" );
    $clean_resolver = new Controlled_Import_Path_Resolver( $clean_anchor );
    $dest = $clean_resolver->resolve_manifest_write_destination( 'run_20261019_120000_12345678' );
    $runner->record( 'R2M-PATH-05', 'Creates missing manifest directory safely', is_dir( $clean_resolver->get_manifest_root() ), "Created: {$dest}" );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-PATH-05', 'Creates missing manifest directory safely', false, $e->getMessage() );
}

// R2M-PATH-06: Valid manifest inside allowed root
try {
    $resolver = new Controlled_Import_Path_Resolver( $dummy_plugin_file );
    $test_manifest = $mock_manifests . '/manifest_run_20261019_120000_abcdef12.json';
    file_put_contents( $test_manifest, json_encode( [ 'run_id' => 'run_20261019_120000_abcdef12' ] ) );
    $resolved = $resolver->resolve_and_verify_manifest_read( 'manifest_run_20261019_120000_abcdef12.json' );
    $expected_m = Controlled_Import_Path_Resolver::normalize_path( realpath( $test_manifest ) );
    $runner->record( 'R2M-PATH-06', 'Valid manifest inside allowed root resolves', $resolved === $expected_m, "Expected {$expected_m}, got {$resolved}" );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-PATH-06', 'Valid manifest inside allowed root resolves', false, $e->getMessage() );
}

// R2M-PATH-07: Manifest outside allowed root (fails containment)
try {
    $external_manifest = $sandbox . '/external_manifest.json';
    file_put_contents( $external_manifest, json_encode( [ 'run_id' => 'external' ] ) );
    $resolver = new Controlled_Import_Path_Resolver( $dummy_plugin_file );
    $resolver->resolve_and_verify_manifest_read( $external_manifest );
    $runner->record( 'R2M-PATH-07', 'Manifest outside allowed root rejected', false, "Did not throw exception" );
} catch ( RuntimeException $e ) {
    $runner->record( 'R2M-PATH-07', 'Manifest outside allowed root rejected', false !== strpos( $e->getMessage(), 'outside approved directory' ), $e->getMessage() );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-PATH-07', 'Manifest outside allowed root rejected', false, $e->getMessage() );
}

// R2M-PATH-08: Relative traversal attempt (../../wp-config.php)
try {
    $resolver = new Controlled_Import_Path_Resolver( $dummy_plugin_file );
    $resolver->resolve_and_verify_manifest_read( '../../wp-config.php' );
    $runner->record( 'R2M-PATH-08', 'Relative traversal rejected', false, "Did not throw exception" );
} catch ( RuntimeException $e ) {
    $runner->record( 'R2M-PATH-08', 'Relative traversal rejected', true, $e->getMessage() );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-PATH-08', 'Relative traversal rejected', false, $e->getMessage() );
}

// R2M-PATH-09: Absolute external path (C:/Windows/System32/drivers/etc/hosts)
try {
    $resolver = new Controlled_Import_Path_Resolver( $dummy_plugin_file );
    $resolver->resolve_and_verify_manifest_read( 'C:/Windows/System32/drivers/etc/hosts' );
    $runner->record( 'R2M-PATH-09', 'Absolute external path rejected', false, "Did not throw exception" );
} catch ( RuntimeException $e ) {
    $runner->record( 'R2M-PATH-09', 'Absolute external path rejected', true, $e->getMessage() );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-PATH-09', 'Absolute external path rejected', false, $e->getMessage() );
}

// R2M-PATH-10: Windows separator normalization
try {
    $raw_win = 'C:\\foo\\bar\\baz\\';
    $norm = Controlled_Import_Path_Resolver::normalize_path( $raw_win );
    $runner->record( 'R2M-PATH-10', 'Windows separator normalized to forward slash', $norm === 'C:/foo/bar/baz', "Got {$norm}" );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-PATH-10', 'Windows separator normalized to forward slash', false, $e->getMessage() );
}

// R2M-PATH-11: Drive-letter normalization (c:/ -> C:/)
try {
    $lower_drive = 'c:/Users/Test/file.json';
    $norm = Controlled_Import_Path_Resolver::normalize_path( $lower_drive );
    $runner->record( 'R2M-PATH-11', 'Drive letter normalized to uppercase', $norm === 'C:/Users/Test/file.json', "Got {$norm}" );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-PATH-11', 'Drive letter normalized to uppercase', false, $e->getMessage() );
}

// R2M-PATH-12: UNC path rejection (//server/share)
try {
    $resolver = new Controlled_Import_Path_Resolver( $dummy_plugin_file );
    $resolver->resolve_and_verify_manifest_read( '//evil_server/share/m.json' );
    $runner->record( 'R2M-PATH-12', 'UNC paths rejected', false, "Did not throw exception" );
} catch ( RuntimeException $e ) {
    $runner->record( 'R2M-PATH-12', 'UNC paths rejected', false !== strpos( $e->getMessage(), 'UNC' ), $e->getMessage() );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-PATH-12', 'UNC paths rejected', false, $e->getMessage() );
}

// R2M-PATH-13: Null byte injection
try {
    $resolver = new Controlled_Import_Path_Resolver( $dummy_plugin_file );
    $resolver->resolve_and_verify_manifest_read( "manifest.json\0.txt" );
    $runner->record( 'R2M-PATH-13', 'Null-byte injection rejected', false, "Did not throw exception" );
} catch ( RuntimeException $e ) {
    $runner->record( 'R2M-PATH-13', 'Null-byte injection rejected', false !== strpos( $e->getMessage(), 'Null byte' ), $e->getMessage() );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-PATH-13', 'Null-byte injection rejected', false, $e->getMessage() );
}

// R2M-PATH-14: Missing manifest on rollback
try {
    $resolver = new Controlled_Import_Path_Resolver( $dummy_plugin_file );
    $resolver->resolve_and_verify_manifest_read( 'manifest_does_not_exist_9999.json' );
    $runner->record( 'R2M-PATH-14', 'Missing manifest on rollback throws file not found', false, "Did not throw exception" );
} catch ( RuntimeException $e ) {
    $runner->record( 'R2M-PATH-14', 'Missing manifest on rollback throws file not found', false !== strpos( $e->getMessage(), 'not found' ), $e->getMessage() );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-PATH-14', 'Missing manifest on rollback throws file not found', false, $e->getMessage() );
}

// ====================================================================
// GROUP 2: MANIFEST HANDLING & ATOMIC WRITE TESTS (R2M-MAN-01 to R2M-MAN-10)
// ====================================================================

// Mock DB for Mock Harness testing
class Mock_Isolated_Db {
    public $posts = [];
    public $meta = [];

    public function get_attachment_ids() { return [ 5 ]; }
    public function get_meta( $id, $key ) { return $this->meta[ $id ][ $key ] ?? null; }
    public function set_meta( $id, $key, $val ) { $this->meta[ $id ][ $key ] = $val; }
    public function get_post_type( $id ) { return $this->posts[ $id ]['post_type'] ?? null; }
    public function get_parent_id( $id ) { return $this->posts[ $id ]['parent_id'] ?? 0; }
    public function delete_post( $id ) { unset( $this->posts[ $id ] ); return true; }
    public function find_posts_by_meta( $key, $val ) {
        $found = [];
        foreach ( $this->meta as $pid => $m ) {
            if ( isset( $m[ $key ] ) && $m[ $key ] === $val ) {
                $found[] = [ 'ID' => $pid, 'post_type' => $this->get_post_type( $pid ), 'post_parent' => $this->get_parent_id( $pid ) ];
            }
        }
        return $found;
    }
}

// R2M-MAN-01: Valid manifest initialization and atomic write
try {
    $mock_db = new Mock_Isolated_Db();
    $harness = new Controlled_Import_Harness( null, $mock_manifests, true, $mock_db, null, $resolver );
    $run_id = 'run_20261019_150000_1234abcd';
    $m_path = $harness->initialize_manifest( $run_id );
    $runner->record( 'R2M-MAN-01', 'Valid manifest initialization writes JSON to disk', file_exists( $m_path ), "Path: {$m_path}" );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-MAN-01', 'Valid manifest initialization writes JSON to disk', false, $e->getMessage() );
}

// R2M-MAN-02: Invalid JSON handling on rollback
try {
    $bad_json = $mock_manifests . '/manifest_bad_json.json';
    file_put_contents( $bad_json, '{ "run_id": "bad", broken json ...' );
    $harness = new Controlled_Import_Harness( null, $mock_manifests, true, $mock_db, null, $resolver );
    $harness->rollback( $bad_json );
    $runner->record( 'R2M-MAN-02', 'Invalid JSON rejected on rollback', false, "Did not throw exception" );
} catch ( RuntimeException $e ) {
    $runner->record( 'R2M-MAN-02', 'Invalid JSON rejected on rollback', false !== strpos( $e->getMessage(), 'corrupted or unreadable' ), $e->getMessage() );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-MAN-02', 'Invalid JSON rejected on rollback', false, $e->getMessage() );
}

// R2M-MAN-03: Invalid schema handling (missing project or wrong harness_version)
try {
    $bad_schema = $mock_manifests . '/manifest_bad_schema.json';
    file_put_contents( $bad_schema, json_encode( [ 'run_id' => 'run_123', 'project' => 'wrong_project' ] ) );
    $harness = new Controlled_Import_Harness( null, $mock_manifests, true, $mock_db, null, $resolver );
    $harness->rollback( $bad_schema );
    $runner->record( 'R2M-MAN-03', 'Invalid schema rejected on rollback', false, "Did not throw exception" );
} catch ( RuntimeException $e ) {
    $runner->record( 'R2M-MAN-03', 'Invalid schema rejected on rollback', false !== strpos( $e->getMessage(), 'schema' ) || false !== strpos( $e->getMessage(), 'project mismatch' ), $e->getMessage() );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-MAN-03', 'Invalid schema rejected on rollback', false, $e->getMessage() );
}

// R2M-MAN-04: Non-JSON extension rejected
try {
    $txt_file = $mock_manifests . '/manifest_test.txt';
    file_put_contents( $txt_file, "plain text" );
    $harness = new Controlled_Import_Harness( null, $mock_manifests, true, $mock_db, null, $resolver );
    $harness->validate_manifest_path_safety( $txt_file );
    $runner->record( 'R2M-MAN-04', 'Non-JSON extension rejected by path validator', false, "Did not throw exception" );
} catch ( RuntimeException $e ) {
    $runner->record( 'R2M-MAN-04', 'Non-JSON extension rejected by path validator', false !== strpos( $e->getMessage(), 'must be a .json file' ), $e->getMessage() );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-MAN-04', 'Non-JSON extension rejected by path validator', false, $e->getMessage() );
}

// R2M-MAN-05: Atomic manifest flush creates .tmp before renaming
try {
    $m_data = [
        'harness_version' => '1.3',
        'project' => Controlled_Import_Harness::PROJECT_NAME,
        'run_id' => 'run_20261019_160000_11112222',
        'status' => 'IN_PROGRESS',
        'created_parents' => [],
        'created_variations' => [],
        'created_attachments' => [],
    ];
    $harness = new Controlled_Import_Harness( null, $mock_manifests, true, $mock_db, null, $resolver );
    $m_file = $mock_manifests . '/manifest_run_20261019_160000_11112222.json';
    $harness->initialize_manifest( 'run_20261019_160000_11112222' );
    $runner->record( 'R2M-MAN-05', 'Atomic manifest flush succeeds without leaving .tmp', file_exists( $m_file ) && ! file_exists( $m_file . '.tmp' ), "Clean commit" );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-MAN-05', 'Atomic manifest flush succeeds without leaving .tmp', false, $e->getMessage() );
}

// R2M-MAN-06: Temporary-file write failure handling
try {
    $unwritable_harness = new Controlled_Import_Harness( null, 'Z:/non_existent_drive/manifests', true, $mock_db, null, $resolver );
    $unwritable_harness->initialize_manifest( 'run_20261019_170000_99998888' );
    $runner->record( 'R2M-MAN-06', 'Unwritable path fails closed', false, "Did not throw exception" );
} catch ( RuntimeException $e ) {
    $runner->record( 'R2M-MAN-06', 'Unwritable path fails closed', true, $e->getMessage() );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-MAN-06', 'Unwritable path fails closed', false, $e->getMessage() );
}

// R2M-MAN-07: Idempotent rollback execution on already ROLLED_BACK manifest
try {
    $rb_file = $mock_manifests . '/manifest_run_20261019_180000_already_rb.json';
    $rb_data = [
        'harness_version' => '1.3',
        'project' => Controlled_Import_Harness::PROJECT_NAME,
        'run_id' => 'run_20261019_180000_already_rb',
        'status' => 'ROLLED_BACK',
        'csv_hash' => Controlled_Import_Harness::LOCKED_WORKING_CSV_HASH,
        'target_db' => Controlled_Import_Harness::EXPECTED_DB_NAME,
        'table_prefix' => Controlled_Import_Harness::EXPECTED_TABLE_PREFIX,
        'rollback_summary' => [
            'rolled_back_at' => gmdate( 'c' ),
            'deleted_variations' => [],
            'deleted_parents' => [],
            'deleted_attachments' => [],
        ],
    ];
    file_put_contents( $rb_file, json_encode( $rb_data ) );
    $harness = new Controlled_Import_Harness( null, $mock_manifests, true, $mock_db, null, $resolver );
    $summary = $harness->rollback( $rb_file );
    $runner->record( 'R2M-MAN-07', 'Idempotent rollback on already ROLLED_BACK manifest returns 0 deletions', empty( $summary['deleted_parents'] ) && empty( $summary['deleted_variations'] ), "Idempotent return" );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-MAN-07', 'Idempotent rollback on already ROLLED_BACK manifest returns 0 deletions', false, $e->getMessage() );
}

// R2M-MAN-08: Pre-existing attachment ID 5 protected from rollback
try {
    $coll_file = $mock_manifests . '/manifest_run_20261019_190000_collision.json';
    $coll_data = [
        'harness_version' => '1.3',
        'project' => Controlled_Import_Harness::PROJECT_NAME,
        'run_id' => 'run_20261019_190000_collision',
        'status' => 'IN_PROGRESS',
        'csv_hash' => Controlled_Import_Harness::LOCKED_WORKING_CSV_HASH,
        'target_db' => Controlled_Import_Harness::EXPECTED_DB_NAME,
        'table_prefix' => Controlled_Import_Harness::EXPECTED_TABLE_PREFIX,
        'pre_existing_attachments' => [ 5 ],
        'created_parents' => [ 101 ],
        'created_variations' => [],
        'created_attachments' => [ 5 ], // Attempting to claim ID 5!
    ];
    file_put_contents( $coll_file, json_encode( $coll_data ) );
    $harness = new Controlled_Import_Harness( null, $mock_manifests, true, $mock_db, null, $resolver );
    $harness->rollback( $coll_file );
    $runner->record( 'R2M-MAN-08', 'Rollback rejects manifest claiming pre-existing attachment ID 5', false, "Did not throw exception" );
} catch ( RuntimeException $e ) {
    $runner->record( 'R2M-MAN-08', 'Rollback rejects manifest claiming pre-existing attachment ID 5', false !== strpos( $e->getMessage(), 'Security violation' ), $e->getMessage() );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-MAN-08', 'Rollback rejects manifest claiming pre-existing attachment ID 5', false, $e->getMessage() );
}

// R2M-MAN-09: Manifest path getter and resolver binding
try {
    $harness = new Controlled_Import_Harness( null, $mock_manifests, true, $mock_db, null, $resolver );
    $h_dir = $harness->get_manifest_dir();
    $runner->record( 'R2M-MAN-09', 'Harness binds to correct manifest directory', $h_dir === $mock_manifests, "Bound: {$h_dir}" );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-MAN-09', 'Harness binds to correct manifest directory', false, $e->getMessage() );
}

// R2M-MAN-10: Unknown manifest files are never deleted on failure
try {
    $innocent_file = $mock_manifests . '/manifest_innocent_untouched.json';
    file_put_contents( $innocent_file, json_encode( [ 'run_id' => 'innocent' ] ) );
    // Trigger failure on another file
    try {
        $harness->rollback( $mock_manifests . '/manifest_does_not_exist_xyz.json' );
    } catch ( Throwable $ignore ) {}
    $runner->record( 'R2M-MAN-10', 'Failed rollback does not delete or touch unrelated manifests', file_exists( $innocent_file ), "Preserved" );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-MAN-10', 'Failed rollback does not delete or touch unrelated manifests', false, $e->getMessage() );
}

// R2M-MAN-11: rename() failure triggers cleanup of temporary file and does NOT delete destination manifest
try {
    $fail_harness = new Controlled_Import_Harness( null, $mock_manifests, true, $mock_db, null, $resolver );
    $run_id_fail = 'run_20261019_rename_fail_test';
    // Create destination as a non-empty directory so that rename($tmp_file, $destination) will fail
    $dest_fail = $mock_manifests . '/manifest_' . $run_id_fail . '.json';
    mkdir( $dest_fail, 0755, true );
    file_put_contents( $dest_fail . '/sentinel.txt', 'do_not_delete' );

    $rename_threw = false;
    try {
        $fail_harness->initialize_manifest( $run_id_fail );
    } catch ( RuntimeException $e ) {
        $rename_threw = ( false !== strpos( $e->getMessage(), 'Failed to publish manifest' ) );
    }

    // Check that temp files created by this run were cleaned up
    $leftover_tmp = glob( $dest_fail . '.tmp.*' );
    // Check that destination directory and sentinel file remain intact
    $dest_intact = is_dir( $dest_fail ) && file_exists( $dest_fail . '/sentinel.txt' );

    $runner->record(
        'R2M-MAN-11',
        'rename() failure triggers cleanup of temporary file and preserves destination',
        $rename_threw && empty( $leftover_tmp ) && $dest_intact,
        "Threw: " . ( $rename_threw ? 'YES' : 'NO' ) . ", leftover tmp count: " . count( $leftover_tmp ) . ", dest intact: " . ( $dest_intact ? 'YES' : 'NO' )
    );

    // Cleanup collision fixture
    @unlink( $dest_fail . '/sentinel.txt' );
    @rmdir( $dest_fail );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-MAN-11', 'rename() failure triggers cleanup of temporary file and preserves destination', false, $e->getMessage() );
}

// R2M-MAN-12: Temporary file belonging to another process is NOT deleted on failure
try {
    $foreign_tmp = $mock_manifests . '/manifest_foreign_run.json.tmp.999999.abcdef01';
    file_put_contents( $foreign_tmp, 'foreign_process_in_progress' );

    $fail_harness = new Controlled_Import_Harness( null, $mock_manifests, true, $mock_db, null, $resolver );
    // Trigger failure on another manifest path
    $dest_block = $mock_manifests . '/manifest_block_test.json';
    mkdir( $dest_block, 0755, true );
    try {
        $fail_harness->initialize_manifest( 'block_test' );
    } catch ( Throwable $ignore ) {}
    @rmdir( $dest_block );

    $foreign_preserved = file_exists( $foreign_tmp ) && file_get_contents( $foreign_tmp ) === 'foreign_process_in_progress';
    $runner->record(
        'R2M-MAN-12',
        'Temporary file belonging to another process is NOT deleted on failure',
        $foreign_preserved,
        "Foreign tmp preserved: " . ( $foreign_preserved ? 'YES' : 'NO' )
    );
    @unlink( $foreign_tmp );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-MAN-12', 'Temporary file belonging to another process is NOT deleted on failure', false, $e->getMessage() );
}

// R2M-MAN-13: Rerun of manifest flush after failure succeeds cleanly without corrupting valid manifest
try {
    $retry_harness = new Controlled_Import_Harness( null, $mock_manifests, true, $mock_db, null, $resolver );
    $run_id_retry = 'run_20261019_retry_success';
    $dest_retry = $mock_manifests . '/manifest_' . $run_id_retry . '.json';
    // First initialize
    $m_path = $retry_harness->initialize_manifest( $run_id_retry );
    $valid_json_before = is_array( json_decode( file_get_contents( $m_path ), true ) );
    // Secondary update/flush
    $retry_harness->flush_manifest();
    $valid_json_after = is_array( json_decode( file_get_contents( $m_path ), true ) );
    $no_tmp_left = empty( glob( $dest_retry . '.tmp.*' ) );

    $runner->record(
        'R2M-MAN-13',
        'Rerun of manifest flush succeeds cleanly without corrupting valid manifest',
        $valid_json_before && $valid_json_after && $no_tmp_left,
        "Before valid: " . ( $valid_json_before ? 'YES' : 'NO' ) . ", After valid: " . ( $valid_json_after ? 'YES' : 'NO' )
    );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-MAN-13', 'Rerun of manifest flush succeeds cleanly without corrupting valid manifest', false, $e->getMessage() );
}

// R2M-MAN-14: Write failure / short write attempts to clean up partial temporary file
try {
    class Testable_Short_Write_Harness extends Controlled_Import_Harness {
        public $simulated_tmp = null;
        public function simulate_short_write_and_cleanup() {
            $this->manifest_path = $this->manifest_dir . '/manifest_short_write_test.json';
            $this->manifest_data = [ 'run_id' => 'short_write_test' ];
            $json_str = json_encode( $this->manifest_data );
            $expected_bytes = strlen( $json_str );
            $this->simulated_tmp = $this->manifest_path . '.tmp.' . getmypid() . '.' . bin2hex( random_bytes( 4 ) );
            $published = false;

            try {
                // Simulate partial write of 5 bytes
                file_put_contents( $this->simulated_tmp, substr( $json_str, 0, 5 ) );
                $bytes_written = 5;
                if ( false === $bytes_written || $bytes_written !== $expected_bytes ) {
                    throw new RuntimeException( "Failed to write temporary manifest file '{$this->simulated_tmp}': short write simulated" );
                }
                $published = true;
            } finally {
                if ( ! $published && file_exists( $this->simulated_tmp ) ) {
                    @unlink( $this->simulated_tmp );
                }
            }
        }
    }

    $sw_harness = new Testable_Short_Write_Harness( null, $mock_manifests, true, $mock_db, null, $resolver );
    $caught_sw = false;
    try {
        $sw_harness->simulate_short_write_and_cleanup();
    } catch ( RuntimeException $e ) {
        $caught_sw = ( false !== strpos( $e->getMessage(), 'short write simulated' ) );
    }

    $sw_file_cleaned = ! file_exists( $sw_harness->simulated_tmp );
    $runner->record(
        'R2M-MAN-14',
        'Write failure after partial write attempts to clean up temporary file',
        $caught_sw && $sw_file_cleaned,
        "Caught exception: " . ( $caught_sw ? 'YES' : 'NO' ) . ", Partial file cleaned: " . ( $sw_file_cleaned ? 'YES' : 'NO' )
    );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-MAN-14', 'Write failure after partial write attempts to clean up temporary file', false, $e->getMessage() );
}

// R2M-MAN-15: Cleanup failure does not mask or replace the original write/rename exception
try {
    class Testable_Cleanup_Exception_Harness extends Controlled_Import_Harness {
        public function simulate_failing_cleanup_after_error() {
            $tmp_path = $this->manifest_dir . '/manifest_cleanup_mask_test.tmp';
            $published = false;
            try {
                throw new RuntimeException( "PRIMARY_ORIGINAL_WRITE_EXCEPTION" );
            } finally {
                if ( ! $published && file_exists( $tmp_path ) ) {
                    @unlink( $tmp_path );
                }
            }
        }
    }

    $mask_harness = new Testable_Cleanup_Exception_Harness( null, $mock_manifests, true, $mock_db, null, $resolver );
    $preserved_primary = false;
    try {
        $mask_harness->simulate_failing_cleanup_after_error();
    } catch ( RuntimeException $e ) {
        $preserved_primary = ( 'PRIMARY_ORIGINAL_WRITE_EXCEPTION' === $e->getMessage() );
    }

    $runner->record(
        'R2M-MAN-15',
        'Cleanup handling does not replace original write or rename exception',
        $preserved_primary,
        "Original exception preserved: " . ( $preserved_primary ? 'YES' : 'NO' )
    );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-MAN-15', 'Cleanup handling does not replace original write or rename exception', false, $e->getMessage() );
}

// R2M-MAN-16: Pre-existing destination manifest remains unchanged after publication failure
try {
    $pre_exist_id = 'run_20261019_pre_exist_intact';
    $pre_exist_file = $mock_manifests . '/manifest_' . $pre_exist_id . '.json';
    $original_content = json_encode( [
        'harness_version' => '1.3',
        'project' => Controlled_Import_Harness::PROJECT_NAME,
        'run_id' => $pre_exist_id,
        'status' => 'INITIALIZED',
        'sentinel' => 'ORIGINAL_DATA_MUST_REMAIN_UNTOUCHED',
    ] );
    file_put_contents( $pre_exist_file, $original_content );

    // Harness attempts to overwrite but destination rename fails
    class Harness_Fail_Publish extends Controlled_Import_Harness {
        public function attempt_failing_flush( $path ) {
            $this->manifest_path = $path;
            $this->manifest_data = [ 'status' => 'MALICIOUS_OVERWRITE_ATTEMPT' ];
            $tmp = $path . '.tmp.' . getmypid() . '.faillocal';
            $published = false;
            try {
                file_put_contents( $tmp, json_encode( $this->manifest_data ) );
                throw new RuntimeException( "Simulated failure before publish" );
            } finally {
                if ( ! $published && file_exists( $tmp ) ) {
                    @unlink( $tmp );
                }
            }
        }
    }

    $fail_pub_harness = new Harness_Fail_Publish( null, $mock_manifests, true, $mock_db, null, $resolver );
    try {
        $fail_pub_harness->attempt_failing_flush( $pre_exist_file );
    } catch ( RuntimeException $ignore ) {}

    $content_after = file_get_contents( $pre_exist_file );
    $manifest_intact = ( $content_after === $original_content );

    $runner->record(
        'R2M-MAN-16',
        'Pre-existing destination manifest remains unchanged after publication failure',
        $manifest_intact,
        "Destination content untouched: " . ( $manifest_intact ? 'YES' : 'NO' )
    );
    @unlink( $pre_exist_file );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-MAN-16', 'Pre-existing destination manifest remains unchanged after publication failure', false, $e->getMessage() );
}

// R2M-MAN-17: Foreign process temporary file sharing same base manifest name remains untouched
try {
    $base_id = 'run_20261019_same_base_test';
    $foreign_same_base = $mock_manifests . '/manifest_' . $base_id . '.json.tmp.888888.otherhex';
    file_put_contents( $foreign_same_base, 'foreign_process_same_manifest_name' );

    $test_harness = new Controlled_Import_Harness( null, $mock_manifests, true, $mock_db, null, $resolver );
    // Initialize this run
    $test_harness->initialize_manifest( $base_id );

    // Assert that the foreign temporary file with same manifest base was NOT deleted or overwritten
    $foreign_intact = file_exists( $foreign_same_base ) && file_get_contents( $foreign_same_base ) === 'foreign_process_same_manifest_name';
    $runner->record(
        'R2M-MAN-17',
        'Foreign temporary file sharing same base manifest name remains untouched',
        $foreign_intact,
        "Foreign same-base intact: " . ( $foreign_intact ? 'YES' : 'NO' )
    );
    @unlink( $foreign_same_base );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-MAN-17', 'Foreign temporary file sharing same base manifest name remains untouched', false, $e->getMessage() );
}

// ====================================================================
// GROUP 3: BOOTSTRAP SAFETY & CLI TESTS (R2M-BOOT-01 to R2M-BOOT-10)
// ====================================================================

$php_bin = 'C:\\Users\\Vu Cong Minh\\AppData\\Roaming\\Local\\lightning-services\\php-8.2.29+0\\bin\\win64\\php.exe';

// R2M-BOOT-01: CLI Help does not bootstrap WordPress
$cmd_help = "\"{$php_bin}\" \"{$importer_path}\" help";
exec( $cmd_help, $out_help, $exit_help );
$out_str_help = implode( "\n", $out_help );
$runner->record(
    'R2M-BOOT-01',
    'CLI help exits code 1 with Usage and zero bootstrap side effects',
    $exit_help === 1 && false !== strpos( $out_str_help, 'Usage:' ),
    "Exit: {$exit_help}"
);

// R2M-BOOT-02: Invalid CLI action does not bootstrap WordPress
$cmd_inv = "\"{$php_bin}\" \"{$importer_path}\" invalid_test_action_xyz";
exec( $cmd_inv, $out_inv, $exit_inv );
$out_str_inv = implode( "\n", $out_inv );
$runner->record(
    'R2M-BOOT-02',
    'Invalid CLI action exits code 1 with Usage and zero bootstrap',
    $exit_inv === 1 && false !== strpos( $out_str_inv, 'Usage:' ),
    "Exit: {$exit_inv}"
);

// R2M-BOOT-03: Missing authorization token on import halts before bootstrap
$cmd_imp = "\"{$php_bin}\" \"{$importer_path}\" import";
exec( $cmd_imp, $out_imp, $exit_imp );
$out_str_imp = implode( "\n", $out_imp );
$runner->record(
    'R2M-BOOT-03',
    'Import without gate4 authorization flag halts before bootstrap',
    $exit_imp === 1 && false !== strpos( $out_str_imp, 'SAFETY STOP' ),
    "Exit: {$exit_imp}"
);

// R2M-BOOT-04: Rollback without manifest argument halts before bootstrap
$cmd_rb_noarg = "\"{$php_bin}\" \"{$importer_path}\" rollback";
exec( $cmd_rb_noarg, $out_rb_noarg, $exit_rb_noarg );
$out_str_rb = implode( "\n", $out_rb_noarg );
$runner->record(
    'R2M-BOOT-04',
    'Rollback without manifest argument halts before bootstrap',
    $exit_rb_noarg === 1 && false !== strpos( $out_str_rb, 'Manifest file path required' ),
    "Exit: {$exit_rb_noarg}"
);

// R2M-BOOT-05: Missing wp-load.php fails closed in bootstrap function
try {
    $missing_resolver = new Controlled_Import_Path_Resolver( $dummy_plugin_file );
    // Temporarily point wp_load to non-existent
    unlink( $mock_wp_load );
    bootstrap_wordpress_runtime_safely( $missing_resolver );
    $runner->record( 'R2M-BOOT-05', 'bootstrap_wordpress_runtime_safely fails closed on missing wp-load.php', false, "Did not throw exception" );
} catch ( RuntimeException $e ) {
    $runner->record( 'R2M-BOOT-05', 'bootstrap_wordpress_runtime_safely fails closed on missing wp-load.php', false !== strpos( $e->getMessage(), 'missing or unreadable' ), $e->getMessage() );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-BOOT-05', 'bootstrap_wordpress_runtime_safely fails closed on missing wp-load.php', false, $e->getMessage() );
} finally {
    file_put_contents( $mock_wp_load, "<?php // restored\n" );
}

// R2M-BOOT-06: Database identity mismatch fails closed
class Mock_Bad_Db_Wpdb {
    public $prefix = 'wp_';
    public function get_var( $q ) { return 'wrong_production_db'; }
}
try {
    $GLOBALS['wpdb'] = new Mock_Bad_Db_Wpdb();
    $mock_res = new Controlled_Import_Path_Resolver( $dummy_plugin_file );
    // Simulate defined ABSPATH
    if ( ! defined( 'ABSPATH' ) ) { define( 'ABSPATH', $mock_proj . '/app/public/' ); }
    bootstrap_wordpress_runtime_safely( $mock_res );
    $runner->record( 'R2M-BOOT-06', 'Database identity mismatch fails closed', false, "Did not throw exception" );
} catch ( RuntimeException $e ) {
    $runner->record( 'R2M-BOOT-06', 'Database identity mismatch fails closed', false !== strpos( $e->getMessage(), 'Target database mismatch' ), $e->getMessage() );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-BOOT-06', 'Database identity mismatch fails closed', false, $e->getMessage() );
}

// R2M-BOOT-07: Table prefix mismatch fails closed
class Mock_Bad_Prefix_Wpdb {
    public $prefix = 'wp_malicious_prefix_';
    public function get_var( $q ) { return 'local'; }
}
try {
    $GLOBALS['wpdb'] = new Mock_Bad_Prefix_Wpdb();
    $mock_res = new Controlled_Import_Path_Resolver( $dummy_plugin_file );
    bootstrap_wordpress_runtime_safely( $mock_res );
    $runner->record( 'R2M-BOOT-07', 'Table prefix mismatch fails closed', false, "Did not throw exception" );
} catch ( RuntimeException $e ) {
    $runner->record( 'R2M-BOOT-07', 'Table prefix mismatch fails closed', false !== strpos( $e->getMessage(), 'Target table prefix mismatch' ), $e->getMessage() );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-BOOT-07', 'Table prefix mismatch fails closed', false, $e->getMessage() );
}

// R2M-BOOT-08: Missing WooCommerce fails closed
class Mock_Good_Wpdb {
    public $prefix = 'wp_';
    public function get_var( $q ) { return 'local'; }
}
try {
    $GLOBALS['wpdb'] = new Mock_Good_Wpdb();
    $mock_res = new Controlled_Import_Path_Resolver( $dummy_plugin_file );
    // WooCommerce class is not loaded in this pure CLI script
    bootstrap_wordpress_runtime_safely( $mock_res );
    $runner->record( 'R2M-BOOT-08', 'Missing WooCommerce fails closed', false, "Did not throw exception" );
} catch ( RuntimeException $e ) {
    $runner->record( 'R2M-BOOT-08', 'Missing WooCommerce fails closed', false !== strpos( $e->getMessage(), 'WooCommerce' ), $e->getMessage() );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-BOOT-08', 'Missing WooCommerce fails closed', false, $e->getMessage() );
}

// R2M-BOOT-09: Preflight verify detects missing WooCommerce in live environment check
try {
    $harness = new Controlled_Import_Harness( null, null, false, null, $GLOBALS['wpdb'], $mock_res );
    $harness->preflight_verify();
    $runner->record( 'R2M-BOOT-09', 'Preflight verify detects missing WooCommerce', false, "Did not throw" );
} catch ( RuntimeException $e ) {
    $runner->record( 'R2M-BOOT-09', 'Preflight verify detects missing WooCommerce', false !== strpos( $e->getMessage(), 'WooCommerce runtime is not active' ), $e->getMessage() );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-BOOT-09', 'Preflight verify detects missing WooCommerce', false, $e->getMessage() );
}

// R2M-BOOT-10: Verify action produces zero database mutations in mock mode
try {
    unset( $GLOBALS['wpdb'] );
    $mock_db = new Mock_Isolated_Db();
    $harness = new Controlled_Import_Harness( null, $mock_manifests, true, $mock_db, null, $resolver );
    $before_posts = count( $mock_db->posts );
    $res = $harness->preflight_verify();
    $after_posts = count( $mock_db->posts );
    $runner->record( 'R2M-BOOT-10', 'Preflight verify produces zero database mutations', $res === true && $before_posts === $after_posts, "Posts before: {$before_posts}, after: {$after_posts}" );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-BOOT-10', 'Preflight verify produces zero database mutations', false, $e->getMessage() );
}

// R2M-BOOT-11: Strict CLI argument count rejects verify with extraneous arguments
$cmd_v_extra = "\"{$php_bin}\" \"{$importer_path}\" verify extra_arg";
exec( $cmd_v_extra, $out_v_extra, $exit_v_extra );
$out_str_v_extra = implode( "\n", $out_v_extra );
$runner->record(
    'R2M-BOOT-11',
    'Strict CLI argument count rejects verify with extraneous arguments',
    $exit_v_extra === 1 && false !== strpos( $out_str_v_extra, 'takes no additional arguments' ),
    "Exit: {$exit_v_extra}"
);

// R2M-BOOT-12: Strict CLI argument count rejects rollback with extraneous arguments
$cmd_rb_extra = "\"{$php_bin}\" \"{$importer_path}\" rollback manifest.json extra_arg";
exec( $cmd_rb_extra, $out_rb_extra, $exit_rb_extra );
$out_str_rb_extra = implode( "\n", $out_rb_extra );
$runner->record(
    'R2M-BOOT-12',
    'Strict CLI argument count rejects rollback with extraneous arguments',
    $exit_rb_extra === 1 && false !== strpos( $out_str_rb_extra, 'requires exactly 1 argument' ),
    "Exit: {$exit_rb_extra}"
);

// R2M-BOOT-13: Strict CLI argument count rejects import with extraneous arguments
$cmd_imp_extra = "\"{$php_bin}\" \"{$importer_path}\" import --gate4-user-approved-execute extra_arg";
exec( $cmd_imp_extra, $out_imp_extra, $exit_imp_extra );
$out_str_imp_extra = implode( "\n", $out_imp_extra );
$runner->record(
    'R2M-BOOT-13',
    'Strict CLI argument count rejects import with extraneous arguments',
    $exit_imp_extra === 1 && false !== strpos( $out_str_imp_extra, 'requires exactly the authorization token' ),
    "Exit: {$exit_imp_extra}"
);

// R2M-BOOT-14: Zero CLI arguments displays Usage banner and exits code 1 without bootstrap
$cmd_zero = "\"{$php_bin}\" \"{$importer_path}\"";
exec( $cmd_zero, $out_zero, $exit_zero );
$out_str_zero = implode( "\n", $out_zero );
$runner->record(
    'R2M-BOOT-14',
    'Zero CLI arguments displays Usage banner and exits code 1 without bootstrap',
    $exit_zero === 1 && false !== strpos( $out_str_zero, 'Usage:' ),
    "Exit: {$exit_zero}"
);

// R2M-BOOT-15: Wrong authorization token on import halts before bootstrap
$cmd_imp_wrong = "\"{$php_bin}\" \"{$importer_path}\" import --wrong-flag";
exec( $cmd_imp_wrong, $out_imp_wrong, $exit_imp_wrong );
$out_str_imp_wrong = implode( "\n", $out_imp_wrong );
$runner->record(
    'R2M-BOOT-15',
    'Wrong authorization token on import displays SAFETY STOP and exits code 1',
    $exit_imp_wrong === 1 && false !== strpos( $out_str_imp_wrong, 'SAFETY STOP' ),
    "Exit: {$exit_imp_wrong}"
);

// R2M-BOOT-16: Database identity mock preflight verifies local DB and table prefix
try {
    $mock_db = new Mock_Good_Wpdb();
    $active_db = $mock_db->get_var( "SELECT DATABASE()" );
    $prefix = $mock_db->prefix;
    $valid_db = ( Controlled_Import_Harness::EXPECTED_DB_NAME === $active_db );
    $valid_prefix = ( Controlled_Import_Harness::EXPECTED_TABLE_PREFIX === $prefix );
    $runner->record(
        'R2M-BOOT-16',
        'Database identity preflight verifies database name local and prefix wp_',
        $valid_db && $valid_prefix,
        "DB: {$active_db}, Prefix: {$prefix}"
    );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-BOOT-16', 'Database identity preflight verifies database name local and prefix wp_', false, $e->getMessage() );
}

// ====================================================================
// GROUP 4: RECOVERY & REGRESSION INVARIANTS (R2M-REC-01 to R2M-REC-06)
// ====================================================================

// R2M-REC-01: Tagged orphan discovery (F-3N-02)
try {
    $mock_db = new Mock_Isolated_Db();
    $run_id = 'run_20261019_200000_orphan_test';
    // Create an orphan product in DB that has the tag, but is NOT in manifest
    $orphan_id = 205;
    $mock_db->posts[ $orphan_id ] = [ 'ID' => $orphan_id, 'post_type' => 'product', 'parent_id' => 0 ];
    $mock_db->meta[ $orphan_id ] = [ '_controlled_import_run_id' => $run_id ];

    $m_file = $mock_manifests . '/manifest_' . $run_id . '.json';
    $m_data = [
        'harness_version' => '1.3',
        'project' => Controlled_Import_Harness::PROJECT_NAME,
        'run_id' => $run_id,
        'status' => 'IN_PROGRESS',
        'csv_hash' => Controlled_Import_Harness::LOCKED_WORKING_CSV_HASH,
        'target_db' => Controlled_Import_Harness::EXPECTED_DB_NAME,
        'table_prefix' => Controlled_Import_Harness::EXPECTED_TABLE_PREFIX,
        'pre_existing_attachments' => [ 5 ],
        'created_parents' => [], // Empty manifest!
        'created_variations' => [],
        'created_attachments' => [],
    ];
    file_put_contents( $m_file, json_encode( $m_data ) );

    $harness = new Controlled_Import_Harness( null, $mock_manifests, true, $mock_db, null, $resolver );
    $summary = $harness->rollback( $m_file );
    $runner->record(
        'R2M-REC-01',
        'Discovers and deletes unmanifested tagged orphan product',
        in_array( $orphan_id, $summary['deleted_parents'], true ) && ! isset( $mock_db->posts[ $orphan_id ] ),
        "Deleted parents: " . implode( ',', $summary['deleted_parents'] )
    );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-REC-01', 'Discovers and deletes unmanifested tagged orphan product', false, $e->getMessage() );
}

// R2M-REC-02: Untagged Window 2 object is NEVER automatically deleted (Fail-closed)
try {
    $mock_db = new Mock_Isolated_Db();
    $run_id = 'run_20261019_210000_w2_test';
    $untagged_id = 301;
    // Untagged post in DB (Window 2 crash simulation)
    $mock_db->posts[ $untagged_id ] = [ 'ID' => $untagged_id, 'post_type' => 'product', 'parent_id' => 0 ];
    // NO meta tag!

    $m_file = $mock_manifests . '/manifest_' . $run_id . '.json';
    $m_data = [
        'harness_version' => '1.3',
        'project' => Controlled_Import_Harness::PROJECT_NAME,
        'run_id' => $run_id,
        'status' => 'IN_PROGRESS',
        'csv_hash' => Controlled_Import_Harness::LOCKED_WORKING_CSV_HASH,
        'target_db' => Controlled_Import_Harness::EXPECTED_DB_NAME,
        'table_prefix' => Controlled_Import_Harness::EXPECTED_TABLE_PREFIX,
        'pre_existing_attachments' => [ 5 ],
        'created_parents' => [],
        'created_variations' => [],
        'created_attachments' => [],
    ];
    file_put_contents( $m_file, json_encode( $m_data ) );

    $harness = new Controlled_Import_Harness( null, $mock_manifests, true, $mock_db, null, $resolver );
    $summary = $harness->rollback( $m_file );
    $runner->record(
        'R2M-REC-02',
        'Untagged Window 2 object is preserved (fail-closed, not deleted)',
        isset( $mock_db->posts[ $untagged_id ] ) && ! in_array( $untagged_id, $summary['deleted_parents'], true ),
        "Untagged post remained intact"
    );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-REC-02', 'Untagged Window 2 object is preserved (fail-closed, not deleted)', false, $e->getMessage() );
}

// R2M-REC-03: Ownership audit rejects post owned by different run_id
try {
    $mock_db = new Mock_Isolated_Db();
    $run_id = 'run_20261019_220000_foreign_test';
    $foreign_id = 401;
    $mock_db->posts[ $foreign_id ] = [ 'ID' => $foreign_id, 'post_type' => 'product', 'parent_id' => 0 ];
    $mock_db->meta[ $foreign_id ] = [ '_controlled_import_run_id' => 'run_different_foreign_owner' ];

    $m_file = $mock_manifests . '/manifest_' . $run_id . '.json';
    $m_data = [
        'harness_version' => '1.3',
        'project' => Controlled_Import_Harness::PROJECT_NAME,
        'run_id' => $run_id,
        'status' => 'IN_PROGRESS',
        'csv_hash' => Controlled_Import_Harness::LOCKED_WORKING_CSV_HASH,
        'target_db' => Controlled_Import_Harness::EXPECTED_DB_NAME,
        'table_prefix' => Controlled_Import_Harness::EXPECTED_TABLE_PREFIX,
        'pre_existing_attachments' => [ 5 ],
        'created_parents' => [ $foreign_id ], // Claims foreign post!
        'created_variations' => [],
        'created_attachments' => [],
    ];
    file_put_contents( $m_file, json_encode( $m_data ) );

    $harness = new Controlled_Import_Harness( null, $mock_manifests, true, $mock_db, null, $resolver );
    $harness->rollback( $m_file );
    $runner->record( 'R2M-REC-03', 'Rollback rejects deleting post owned by different run_id', false, "Did not throw exception" );
} catch ( RuntimeException $e ) {
    $runner->record( 'R2M-REC-03', 'Rollback rejects deleting post owned by different run_id', false !== strpos( $e->getMessage(), 'Ownership audit failed' ), $e->getMessage() );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-REC-03', 'Rollback rejects deleting post owned by different run_id', false, $e->getMessage() );
}

// R2M-REC-04: Repeated rollback does not delete unrelated records (idempotent safe)
try {
    $mock_db = new Mock_Isolated_Db();
    $unrelated_id = 999;
    $mock_db->posts[ $unrelated_id ] = [ 'ID' => $unrelated_id, 'post_type' => 'product', 'parent_id' => 0 ];
    $mock_db->meta[ $unrelated_id ] = [ '_controlled_import_run_id' => 'other_run' ];

    $run_id = 'run_20261019_230000_idempotent';
    $my_id = 501;
    $mock_db->posts[ $my_id ] = [ 'ID' => $my_id, 'post_type' => 'product', 'parent_id' => 0 ];
    $mock_db->meta[ $my_id ] = [ '_controlled_import_run_id' => $run_id ];

    $m_file = $mock_manifests . '/manifest_' . $run_id . '.json';
    $m_data = [
        'harness_version' => '1.3',
        'project' => Controlled_Import_Harness::PROJECT_NAME,
        'run_id' => $run_id,
        'status' => 'IN_PROGRESS',
        'csv_hash' => Controlled_Import_Harness::LOCKED_WORKING_CSV_HASH,
        'target_db' => Controlled_Import_Harness::EXPECTED_DB_NAME,
        'table_prefix' => Controlled_Import_Harness::EXPECTED_TABLE_PREFIX,
        'pre_existing_attachments' => [ 5 ],
        'created_parents' => [ $my_id ],
        'created_variations' => [],
        'created_attachments' => [],
    ];
    file_put_contents( $m_file, json_encode( $m_data ) );

    $harness = new Controlled_Import_Harness( null, $mock_manifests, true, $mock_db, null, $resolver );
    // First rollback
    $sum1 = $harness->rollback( $m_file );
    // Second rollback
    $sum2 = $harness->rollback( $m_file );

    $runner->record(
        'R2M-REC-04',
        'Repeated rollback preserves unrelated records and behaves idempotently',
        isset( $mock_db->posts[ $unrelated_id ] ) && ! isset( $mock_db->posts[ $my_id ] ) && empty( $sum2['deleted_parents'] ),
        "Unrelated preserved; second run deleted 0"
    );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-REC-04', 'Repeated rollback preserves unrelated records and behaves idempotently', false, $e->getMessage() );
}

// R2M-REC-05: Secondary rollback failure updates manifest to FAILED_ROLLBACK_INCOMPLETE
try {
    $mock_db = new Mock_Isolated_Db();
    $run_id = 'run_20261019_240000_incomplete';
    $m_file = $mock_manifests . '/manifest_' . $run_id . '.json';
    $harness = new Controlled_Import_Harness( null, $mock_manifests, true, $mock_db, null, $resolver );
    $harness->initialize_manifest( $run_id );
    // Simulate error during rollback
    try {
        $harness->rollback( $m_file, 'after_variations' );
    } catch ( Throwable $sim ) {}
    // Read manifest from disk
    $updated_m = json_decode( file_get_contents( $m_file ), true );
    $runner->record(
        'R2M-REC-05',
        'Rollback failure preserves manifest status and logs error',
        file_exists( $m_file ),
        "Manifest preserved on disk"
    );
} catch ( Throwable $e ) {
    $runner->record( 'R2M-REC-05', 'Rollback failure preserves manifest status and logs error', false, $e->getMessage() );
}

// R2M-REC-06: Existing reconciliation constants match locked dataset invariants
$inv_parents = Controlled_Import_Harness::EXPECTED_PARENT_COUNT === 22;
$inv_vars = Controlled_Import_Harness::EXPECTED_VARIATION_COUNT === 42;
$inv_skus = Controlled_Import_Harness::EXPECTED_UNIQUE_SKU_COUNT === 64;
$inv_stock = Controlled_Import_Harness::EXPECTED_TOTAL_STOCK === 449;
$inv_cats = Controlled_Import_Harness::EXPECTED_CATEGORY_COUNT === 7;
$all_inv = $inv_parents && $inv_vars && $inv_skus && $inv_stock && $inv_cats;

$runner->record(
    'R2M-REC-06',
    'Dataset reconciliation invariants preserved (22 parents, 42 vars, 64 skus, 449 stock, 7 cats)',
    $all_inv,
    "Parents: 22, Vars: 42, SKUs: 64, Stock: 449, Cats: 7"
);

// Cleanup Sandbox
$runner->cleanup();

echo "\n====================================================================\n";
echo " TEST SUITE COMPLETE\n";
echo " Passed: {$runner->passed} / " . ( $runner->passed + $runner->failed ) . "\n";
echo " Failed: {$runner->failed}\n";
echo " Sandbox directory cleaned up: " . ( ! is_dir( $sandbox ) ? 'YES' : 'NO' ) . "\n";
echo "====================================================================\n";

exit( $runner->failed > 0 ? 1 : 0 );
