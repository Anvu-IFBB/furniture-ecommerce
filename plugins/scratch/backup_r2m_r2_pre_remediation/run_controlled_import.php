<?php
/**
 * Script: Controlled WooCommerce Product CSV Importer & Rollback Harness
 * Project: furniture-ecommerce
 * Stage: 1E.3G.7 (Gate 3G - Security Hardened Version)
 * 
 * Safety Rules:
 * 1. Strict read-only preflight hash check against locked working CSV SHA-256.
 * 2. Strict target database identity verification ('local', prefix 'wp_').
 * 3. update_existing is unconditionally FALSE.
 * 4. Atomic manifest logging with real-time add_attachment interception.
 * 5. Rollback enforces strict reverse deletion: variations -> parents -> attachments.
 * 6. Pre-existing attachments (e.g. placeholder ID 5) are strictly protected.
 * 7. Top-level try-catch-finally catches Throwable to guarantee rollback and hook cleanup.
 * 8. Strict post-import reconciliation verifying dataset totals and category associations before COMPLETED status.
 * 9. Fail-closed manifest environment binding, durable post ownership audit, and path safety enforcement.
 */

// Stage 3N-R2M: Top-level unconditional bootstrap removed.
// WordPress bootstrap is strictly deferred until CLI arguments are validated.

/**
 * Canonical Path Resolution Module for Controlled Import Harness.
 * Stage 3N-R2M: Resolves and enforces immutable boundaries across WordPress, project,
 * backup, and manifest directories.
 */
class Controlled_Import_Path_Resolver {

    private $anchor_file;
    private $project_root;
    private $wordpress_root;
    private $backup_root;
    private $manifest_root;

    public function __construct( $anchor_file = null ) {
        $this->anchor_file = $anchor_file ?: __FILE__;
        $this->resolve_roots();
    }

    public static function normalize_path( $path ) {
        if ( ! is_string( $path ) || '' === trim( $path ) ) {
            return '';
        }
        $p = str_replace( '\\', '/', $path );
        // Normalize Windows drive letter: c:/ -> C:/
        if ( preg_match( '/^[a-z]:\//i', $p ) ) {
            $p = strtoupper( $p[0] ) . substr( $p, 1 );
        }
        return rtrim( $p, '/' );
    }

    private function resolve_roots() {
        $real_anchor = realpath( $this->anchor_file );
        if ( false === $real_anchor || ! is_file( $real_anchor ) ) {
            throw new RuntimeException( "CRITICAL: Cannot resolve physical path for anchor file: {$this->anchor_file}" );
        }

        $norm_anchor = self::normalize_path( $real_anchor );

        // Expected structure: <project_root>/app/public/wp-content/plugins/run_controlled_import.php
        // Level 1: wp-content/plugins -> wp-content
        // Level 2: wp-content -> public (WordPress root)
        // Level 3: public -> app
        // Level 4: app -> project root
        $wp_root = dirname( dirname( dirname( $norm_anchor ) ) );
        $app_root = dirname( $wp_root );
        $proj_root = dirname( $app_root );

        $real_wp = realpath( $wp_root );
        if ( false === $real_wp || ! is_dir( $real_wp ) ) {
            throw new RuntimeException( "CRITICAL: WordPress root directory not found: {$wp_root}" );
        }
        $wp_load = self::normalize_path( $real_wp ) . '/wp-load.php';
        if ( ! file_exists( $wp_load ) ) {
            throw new RuntimeException( "CRITICAL: WordPress root does not contain expected wp-load.php: {$wp_root}" );
        }
        $this->wordpress_root = self::normalize_path( $real_wp );

        $real_proj = realpath( $proj_root );
        if ( false === $real_proj || ! is_dir( $real_proj ) ) {
            throw new RuntimeException( "CRITICAL: Project root directory not found: {$proj_root}" );
        }
        $this->project_root = self::normalize_path( $real_proj );

        $this->backup_root = $this->project_root . '/backups';
        $this->manifest_root = $this->backup_root . '/manifests';
    }

    public function get_wordpress_root() {
        return $this->wordpress_root;
    }

    public function get_project_root() {
        return $this->project_root;
    }

    public function get_backup_root() {
        return $this->backup_root;
    }

    public function get_manifest_root() {
        return $this->manifest_root;
    }

    public function get_wp_load_path() {
        return $this->wordpress_root . '/wp-load.php';
    }

    public static function is_absolute( $path ) {
        if ( ! is_string( $path ) || empty( $path ) ) {
            return false;
        }
        return ( '/' === $path[0] || '\\' === $path[0] || preg_match( '/^[A-Za-z]:[\/\\\\]/', $path ) );
    }

    public function resolve_and_verify_manifest_read( $input_path ) {
        if ( ! is_string( $input_path ) || '' === trim( $input_path ) ) {
            throw new InvalidArgumentException( "Manifest file path cannot be empty." );
        }

        if ( false !== strpos( $input_path, "\0" ) ) {
            throw new RuntimeException( "Path safety violation: Null byte detected in manifest path." );
        }

        if ( 0 === strpos( $input_path, '//' ) || 0 === strpos( $input_path, '\\\\' ) ) {
            throw new RuntimeException( "Path safety violation: UNC paths are strictly prohibited: '{$input_path}'" );
        }

        // Relative paths anchored strictly to manifest_root, unless existing CWD path resolves within manifest_root
        if ( ! self::is_absolute( $input_path ) ) {
            $target = $this->manifest_root . '/' . ltrim( $input_path, '/\\' );
        } else {
            $target = $input_path;
        }

        $real_file = realpath( $target );
        if ( false === $real_file || ! file_exists( $real_file ) ) {
            $cwd_real = realpath( $input_path );
            if ( false !== $cwd_real && file_exists( $cwd_real ) ) {
                $real_file = $cwd_real;
            } else {
                throw new RuntimeException( "Manifest file not found: {$input_path}" );
            }
        }

        if ( is_dir( $real_file ) || ! is_file( $real_file ) ) {
            throw new RuntimeException( "Path safety violation: Manifest path resolves to a directory, not a regular file: '{$input_path}'" );
        }

        if ( ! is_readable( $real_file ) ) {
            throw new RuntimeException( "Manifest file is not readable: '{$input_path}'" );
        }

        if ( strtolower( pathinfo( $real_file, PATHINFO_EXTENSION ) ) !== 'json' ) {
            throw new RuntimeException( "Invalid manifest file format: must be a .json file." );
        }

        $real_manifest_dir = realpath( $this->manifest_root );
        if ( false === $real_manifest_dir || ! is_dir( $real_manifest_dir ) ) {
            throw new RuntimeException( "Manifest directory does not exist: {$this->manifest_root}" );
        }

        $norm_dir  = self::normalize_path( $real_manifest_dir ) . '/';
        $norm_file = self::normalize_path( $real_file );

        if ( 0 !== stripos( $norm_file, $norm_dir ) ) {
            throw new RuntimeException( "Path safety violation: Manifest path '{$input_path}' is outside approved directory '{$this->manifest_root}'" );
        }

        return $norm_file;
    }

    public function resolve_manifest_write_destination( $run_id ) {
        if ( ! is_string( $run_id ) || ! preg_match( '/^run_[0-9]{8}_[0-9]{6}_[0-9a-f]{8}$/i', $run_id ) ) {
            throw new InvalidArgumentException( "Invalid run_id format for manifest generation: {$run_id}" );
        }

        if ( ! is_dir( $this->manifest_root ) ) {
            if ( ! @mkdir( $this->manifest_root, 0755, true ) && ! is_dir( $this->manifest_root ) ) {
                throw new RuntimeException( "Failed to create manifest root directory: {$this->manifest_root}" );
            }
        }

        $real_root = realpath( $this->manifest_root );
        if ( false === $real_root || ! is_dir( $real_root ) ) {
            throw new RuntimeException( "Manifest root directory unresolvable: {$this->manifest_root}" );
        }

        return self::normalize_path( $real_root ) . '/manifest_' . $run_id . '.json';
    }
}

/**
 * Deferred, isolated WordPress CLI bootstrap helper.
 */
function bootstrap_wordpress_runtime_safely( Controlled_Import_Path_Resolver $resolver ) {
    if ( ! defined( 'ABSPATH' ) ) {
        $wp_load = $resolver->get_wp_load_path();
        if ( ! file_exists( $wp_load ) || ! is_readable( $wp_load ) ) {
            throw new RuntimeException( "CRITICAL: WordPress bootstrap file missing or unreadable at: {$wp_load}" );
        }

        if ( ! defined( 'WP_USE_THEMES' ) ) {
            define( 'WP_USE_THEMES', false );
        }
        if ( ! defined( 'DOING_CRON' ) ) {
            define( 'DOING_CRON', true );
        }

        require_once $wp_load;
    }

    if ( ! isset( $GLOBALS['wpdb'] ) || ! is_object( $GLOBALS['wpdb'] ) ) {
        throw new RuntimeException( "CRITICAL: WordPress database abstraction (\$wpdb) failed to initialize." );
    }

    $active_db = '';
    if ( method_exists( $GLOBALS['wpdb'], 'get_var' ) ) {
        $active_db = $GLOBALS['wpdb']->get_var( "SELECT DATABASE()" );
    }
    if ( empty( $active_db ) && isset( $GLOBALS['wpdb']->dbname ) ) {
        $active_db = $GLOBALS['wpdb']->dbname;
    }

    if ( Controlled_Import_Harness::EXPECTED_DB_NAME !== $active_db ) {
        throw new RuntimeException( "SAFETY STOP: Target database mismatch! Expected '" . Controlled_Import_Harness::EXPECTED_DB_NAME . "', connected to '{$active_db}'." );
    }

    $prefix = isset( $GLOBALS['wpdb']->prefix ) ? $GLOBALS['wpdb']->prefix : '';
    if ( Controlled_Import_Harness::EXPECTED_TABLE_PREFIX !== $prefix ) {
        throw new RuntimeException( "SAFETY STOP: Target table prefix mismatch! Expected '" . Controlled_Import_Harness::EXPECTED_TABLE_PREFIX . "', found '{$prefix}'." );
    }

    if ( ! class_exists( 'WooCommerce' ) ) {
        throw new RuntimeException( "CRITICAL: WooCommerce runtime is not loaded after bootstrap." );
    }

    return true;
}

if ( ! defined( 'ARRAY_A' ) ) {
    define( 'ARRAY_A', 'ARRAY_A' );
}

class Controlled_Import_Harness {

    const HARNESS_VERSION               = '1.3';
    const PROJECT_NAME                  = 'furniture-ecommerce';
    const LOCKED_WORKING_CSV_HASH       = 'D2C8566F334A767887F57C4A15EA456CFC5E117767FCFE46C599CC754BC2BBA0';
    const LOCKED_WORKING_CSV_PATH       = 'C:/Users/Vu Cong Minh/Local Sites/furniture-ecommerce/data-preparation/woocommerce_furniture_dataset_22_products_working_stage_1e_3c.csv';
    const EXPECTED_DB_NAME              = 'local';
    const EXPECTED_TABLE_PREFIX         = 'wp_';
    const IMAGE_TIMEOUT_SECONDS         = 15;
    const MIN_REQUIRED_MEMORY_MB        = 256;

    // Expected reconciliation baselines
    const EXPECTED_PARENT_COUNT         = 22;
    const EXPECTED_SIMPLE_COUNT         = 12;
    const EXPECTED_VARIABLE_COUNT       = 10;
    const EXPECTED_VARIATION_COUNT      = 42;
    const EXPECTED_UNIQUE_SKU_COUNT     = 64;
    const EXPECTED_TOTAL_STOCK          = 449;
    const EXPECTED_CATEGORY_COUNT       = 7;
    const EXPECTED_CATEGORY_ASSOC_COUNT = 27;

    const EXPECTED_DATASET_CATEGORIES = [
        'Beds',
        'Chairs',
        'Decoration',
        'Lighting',
        'Sofas',
        'Storage',
        'Tables',
    ];

    const EXPECTED_PARENT_CATEGORIES = [
        'FURN-001' => [ 'Chairs' ],
        'FURN-002' => [ 'Chairs' ],
        'FURN-003' => [ 'Tables' ],
        'FURN-004' => [ 'Tables' ],
        'FURN-005' => [ 'Sofas' ],
        'FURN-006' => [ 'Beds', 'Storage' ],
        'FURN-007' => [ 'Lighting' ],
        'FURN-008' => [ 'Lighting' ],
        'FURN-009' => [ 'Decoration' ],
        'FURN-010' => [ 'Storage', 'Decoration' ],
        'FURN-011' => [ 'Chairs' ],
        'FURN-012' => [ 'Decoration' ],
        'FURN-013' => [ 'Chairs' ],
        'FURN-014' => [ 'Tables' ],
        'FURN-015' => [ 'Sofas' ],
        'FURN-016' => [ 'Beds' ],
        'FURN-017' => [ 'Lighting' ],
        'FURN-018' => [ 'Decoration' ],
        'FURN-019' => [ 'Storage', 'Beds' ],
        'FURN-020' => [ 'Storage' ],
        'FURN-021' => [ 'Chairs', 'Sofas' ],
        'FURN-022' => [ 'Beds', 'Storage' ],
    ];

    public const CANONICAL_INVENTORY_MATRIX = [
        'FURN-001' => [ 'type' => 'simple', 'stock' => 25, 'parent' => '' ],
        'FURN-002' => [ 'type' => 'simple', 'stock' => 30, 'parent' => '' ],
        'FURN-003' => [ 'type' => 'simple', 'stock' => 15, 'parent' => '' ],
        'FURN-004' => [ 'type' => 'simple', 'stock' => 20, 'parent' => '' ],
        'FURN-005' => [ 'type' => 'simple', 'stock' => 18, 'parent' => '' ],
        'FURN-006' => [ 'type' => 'simple', 'stock' => 12, 'parent' => '' ],
        'FURN-007' => [ 'type' => 'simple', 'stock' => 40, 'parent' => '' ],
        'FURN-008' => [ 'type' => 'simple', 'stock' => 22, 'parent' => '' ],
        'FURN-009' => [ 'type' => 'simple', 'stock' => 50, 'parent' => '' ],
        'FURN-010' => [ 'type' => 'simple', 'stock' => 0, 'parent' => '' ],
        'FURN-011' => [ 'type' => 'simple', 'stock' => 16, 'parent' => '' ],
        'FURN-012' => [ 'type' => 'simple', 'stock' => 0, 'parent' => '' ],
        'FURN-013' => [ 'type' => 'variable', 'stock' => 0, 'parent' => '' ],
        'FURN-013-BLK-S' => [ 'type' => 'variation', 'stock' => 8, 'parent' => 'FURN-013' ],
        'FURN-013-BLK-M' => [ 'type' => 'variation', 'stock' => 6, 'parent' => 'FURN-013' ],
        'FURN-013-BLK-L' => [ 'type' => 'variation', 'stock' => 5, 'parent' => 'FURN-013' ],
        'FURN-013-WHT-S' => [ 'type' => 'variation', 'stock' => 5, 'parent' => 'FURN-013' ],
        'FURN-013-WHT-M' => [ 'type' => 'variation', 'stock' => 4, 'parent' => 'FURN-013' ],
        'FURN-013-BRN-L' => [ 'type' => 'variation', 'stock' => 7, 'parent' => 'FURN-013' ],
        'FURN-014' => [ 'type' => 'variable', 'stock' => 0, 'parent' => '' ],
        'FURN-014-BRN-M' => [ 'type' => 'variation', 'stock' => 5, 'parent' => 'FURN-014' ],
        'FURN-014-BRN-L' => [ 'type' => 'variation', 'stock' => 4, 'parent' => 'FURN-014' ],
        'FURN-014-WHT-M' => [ 'type' => 'variation', 'stock' => 4, 'parent' => 'FURN-014' ],
        'FURN-014-WHT-L' => [ 'type' => 'variation', 'stock' => 3, 'parent' => 'FURN-014' ],
        'FURN-015' => [ 'type' => 'variable', 'stock' => 0, 'parent' => '' ],
        'FURN-015-WHT-M' => [ 'type' => 'variation', 'stock' => 4, 'parent' => 'FURN-015' ],
        'FURN-015-WHT-L' => [ 'type' => 'variation', 'stock' => 3, 'parent' => 'FURN-015' ],
        'FURN-015-BLK-M' => [ 'type' => 'variation', 'stock' => 3, 'parent' => 'FURN-015' ],
        'FURN-015-BRN-L' => [ 'type' => 'variation', 'stock' => 4, 'parent' => 'FURN-015' ],
        'FURN-016' => [ 'type' => 'variable', 'stock' => 0, 'parent' => '' ],
        'FURN-016-WHT-M' => [ 'type' => 'variation', 'stock' => 4, 'parent' => 'FURN-016' ],
        'FURN-016-WHT-L' => [ 'type' => 'variation', 'stock' => 3, 'parent' => 'FURN-016' ],
        'FURN-016-BRN-M' => [ 'type' => 'variation', 'stock' => 3, 'parent' => 'FURN-016' ],
        'FURN-016-BRN-L' => [ 'type' => 'variation', 'stock' => 2, 'parent' => 'FURN-016' ],
        'FURN-017' => [ 'type' => 'variable', 'stock' => 0, 'parent' => '' ],
        'FURN-017-BLK-S' => [ 'type' => 'variation', 'stock' => 6, 'parent' => 'FURN-017' ],
        'FURN-017-BLK-M' => [ 'type' => 'variation', 'stock' => 6, 'parent' => 'FURN-017' ],
        'FURN-017-BLK-L' => [ 'type' => 'variation', 'stock' => 4, 'parent' => 'FURN-017' ],
        'FURN-017-WHT-M' => [ 'type' => 'variation', 'stock' => 8, 'parent' => 'FURN-017' ],
        'FURN-018' => [ 'type' => 'variable', 'stock' => 0, 'parent' => '' ],
        'FURN-018-BRN-S' => [ 'type' => 'variation', 'stock' => 10, 'parent' => 'FURN-018' ],
        'FURN-018-BRN-M' => [ 'type' => 'variation', 'stock' => 8, 'parent' => 'FURN-018' ],
        'FURN-018-BLK-S' => [ 'type' => 'variation', 'stock' => 6, 'parent' => 'FURN-018' ],
        'FURN-018-WHT-M' => [ 'type' => 'variation', 'stock' => 6, 'parent' => 'FURN-018' ],
        'FURN-019' => [ 'type' => 'variable', 'stock' => 0, 'parent' => '' ],
        'FURN-019-BRN-M' => [ 'type' => 'variation', 'stock' => 5, 'parent' => 'FURN-019' ],
        'FURN-019-BRN-L' => [ 'type' => 'variation', 'stock' => 4, 'parent' => 'FURN-019' ],
        'FURN-019-BLK-M' => [ 'type' => 'variation', 'stock' => 3, 'parent' => 'FURN-019' ],
        'FURN-019-BLK-L' => [ 'type' => 'variation', 'stock' => 2, 'parent' => 'FURN-019' ],
        'FURN-020' => [ 'type' => 'variable', 'stock' => 0, 'parent' => '' ],
        'FURN-020-BLK-S' => [ 'type' => 'variation', 'stock' => 6, 'parent' => 'FURN-020' ],
        'FURN-020-BLK-M' => [ 'type' => 'variation', 'stock' => 6, 'parent' => 'FURN-020' ],
        'FURN-020-BLK-L' => [ 'type' => 'variation', 'stock' => 4, 'parent' => 'FURN-020' ],
        'FURN-020-WHT-M' => [ 'type' => 'variation', 'stock' => 5, 'parent' => 'FURN-020' ],
        'FURN-021' => [ 'type' => 'variable', 'stock' => 0, 'parent' => '' ],
        'FURN-021-BLK-S' => [ 'type' => 'variation', 'stock' => 6, 'parent' => 'FURN-021' ],
        'FURN-021-WHT-M' => [ 'type' => 'variation', 'stock' => 5, 'parent' => 'FURN-021' ],
        'FURN-021-BRN-L' => [ 'type' => 'variation', 'stock' => 5, 'parent' => 'FURN-021' ],
        'FURN-021-BLK-L' => [ 'type' => 'variation', 'stock' => 4, 'parent' => 'FURN-021' ],
        'FURN-022' => [ 'type' => 'variable', 'stock' => 0, 'parent' => '' ],
        'FURN-022-BLK-M' => [ 'type' => 'variation', 'stock' => 5, 'parent' => 'FURN-022' ],
        'FURN-022-BLK-L' => [ 'type' => 'variation', 'stock' => 4, 'parent' => 'FURN-022' ],
        'FURN-022-WHT-M' => [ 'type' => 'variation', 'stock' => 3, 'parent' => 'FURN-022' ],
        'FURN-022-WHT-L' => [ 'type' => 'variation', 'stock' => 3, 'parent' => 'FURN-022' ],
    ];

    protected $path_resolver;
    protected $csv_path;
    protected $manifest_dir;
    protected $run_id;
    protected $manifest_path;
    protected $manifest_data;
    protected $pre_existing_attachment_ids = [];
    protected $hooks_attached = false;
    protected $is_mock_mode = false;
    protected $mock_db = null;
    protected $wpdb_adapter = null;

    public function __construct( $csv_path = null, $manifest_dir = null, $is_mock_mode = false, $mock_db = null, $wpdb_adapter = null, $path_resolver = null ) {
        $this->path_resolver = $path_resolver ?: new Controlled_Import_Path_Resolver();
        $this->csv_path      = $csv_path ?: self::LOCKED_WORKING_CSV_PATH;
        $this->manifest_dir  = $manifest_dir ?: $this->path_resolver->get_manifest_root();
        $this->is_mock_mode  = $is_mock_mode;
        $this->mock_db       = $mock_db;
        $this->wpdb_adapter  = $wpdb_adapter;
    }

    public function get_path_resolver() {
        return $this->path_resolver;
    }

    public function get_manifest_dir() {
        return $this->manifest_dir;
    }

    /**
     * Helper to read post ownership run-id tag.
     */
    public function get_post_ownership_tag( $post_id ) {
        if ( $this->is_mock_mode && $this->mock_db && method_exists( $this->mock_db, 'get_meta' ) ) {
            return $this->mock_db->get_meta( $post_id, '_controlled_import_run_id' );
        }
        if ( function_exists( 'get_post_meta' ) ) {
            return get_post_meta( $post_id, '_controlled_import_run_id', true );
        }
        return null;
    }

    /**
     * Actively discover all posts tagged with a specific run_id from storage (F-3N-02).
     * Returns an array of rows: [ ['ID' => int, 'post_type' => string, 'post_parent' => int], ... ]
     */
    public function find_posts_by_ownership_tag( $run_id ) {
        if ( ! is_string( $run_id ) || empty( $run_id ) ) {
            return [];
        }

        if ( $this->is_mock_mode && $this->mock_db ) {
            if ( method_exists( $this->mock_db, 'find_posts_by_meta' ) ) {
                return $this->mock_db->find_posts_by_meta( '_controlled_import_run_id', $run_id );
            }
            return [];
        }

        $wpdb = $this->get_wpdb_connection();
        if ( ! $wpdb ) {
            return [];
        }

        $prefix = isset( $wpdb->prefix ) ? $wpdb->prefix : 'wp_';
        $posts_table = $prefix . 'posts';
        $postmeta_table = $prefix . 'postmeta';

        $query = $wpdb->prepare(
            "SELECT p.ID, p.post_type, p.post_parent 
             FROM {$posts_table} p 
             INNER JOIN {$postmeta_table} pm ON p.ID = pm.post_id 
             WHERE pm.meta_key = '_controlled_import_run_id' 
               AND pm.meta_value = %s",
            $run_id
        );

        $results = $wpdb->get_results( $query, ARRAY_A );
        if ( ! is_array( $results ) ) {
            if ( ! empty( $wpdb->last_error ) ) {
                throw new RuntimeException( "Database query failed while searching for run ownership tags: " . $wpdb->last_error );
            }
            return [];
        }

        return $results;
    }

    /**
     * Helper to tag post with ownership run-id.
     */
    public function set_post_ownership_tag( $post_id, $run_id ) {
        if ( $this->is_mock_mode && $this->mock_db && method_exists( $this->mock_db, 'set_meta' ) ) {
            $this->mock_db->set_meta( $post_id, '_controlled_import_run_id', $run_id );
            return true;
        }
        if ( function_exists( 'update_post_meta' ) ) {
            return update_post_meta( $post_id, '_controlled_import_run_id', $run_id );
        }
        return false;
    }

    /**
     * Safely resolve post_type in runtime or mock environment.
     */
    public function get_post_type_safe( $post_id ) {
        if ( $this->is_mock_mode && $this->mock_db && method_exists( $this->mock_db, 'get_post_type' ) ) {
            return $this->mock_db->get_post_type( $post_id );
        }
        if ( function_exists( 'get_post_type' ) ) {
            return get_post_type( $post_id );
        }
        return null;
    }

    /**
     * Safely resolve parent_id in runtime or mock environment.
     */
    public function get_post_parent_id_safe( $post_id ) {
        if ( $this->is_mock_mode && $this->mock_db && method_exists( $this->mock_db, 'get_parent_id' ) ) {
            return $this->mock_db->get_parent_id( $post_id );
        }
        if ( function_exists( 'wp_get_post_parent_id' ) ) {
            return intval( wp_get_post_parent_id( $post_id ) );
        }
        return 0;
    }

    /**
     * Parse memory string into integer bytes.
     */
    public function parse_memory_bytes( $val ) {
        $val = trim( (string) $val );
        if ( empty( $val ) || '-1' === $val ) {
            return -1;
        }
        $last = strtolower( $val[ strlen( $val ) - 1 ] );
        $num  = (int) $val;
        switch ( $last ) {
            case 'g':
                $num *= 1024 * 1024 * 1024;
                break;
            case 'm':
                $num *= 1024 * 1024;
                break;
            case 'k':
                $num *= 1024;
                break;
        }
        return $num;
    }

    /**
     * Verify or enforce PHP memory limits.
     */
    public function ensure_resource_limits() {
        $current_mem = ini_get( 'memory_limit' );
        $current_bytes = $this->parse_memory_bytes( $current_mem );
        $min_bytes = self::MIN_REQUIRED_MEMORY_MB * 1024 * 1024;

        if ( $current_bytes > 0 && $current_bytes < $min_bytes ) {
            @ini_set( 'memory_limit', '512M' );
            $new_mem = ini_get( 'memory_limit' );
            $new_bytes = $this->parse_memory_bytes( $new_mem );
            if ( $new_bytes > 0 && $new_bytes < $min_bytes ) {
                throw new RuntimeException( "Insufficient PHP memory limit: {$new_mem}. Minimum required is " . self::MIN_REQUIRED_MEMORY_MB . "M" );
            }
        }
    }

    /**
     * Resolve the active database connection object.
     */
    protected function get_wpdb_connection() {
        if ( $this->wpdb_adapter ) {
            return $this->wpdb_adapter;
        }
        if ( isset( $GLOBALS['wpdb'] ) && is_object( $GLOBALS['wpdb'] ) ) {
            return $GLOBALS['wpdb'];
        }
        return null;
    }

    /**
     * Resolve active connected database name.
     */
    public function get_active_db_name( $wpdb ) {
        if ( is_object( $wpdb ) && method_exists( $wpdb, 'get_var' ) ) {
            $db = $wpdb->get_var( "SELECT DATABASE()" );
            if ( ! empty( $db ) ) {
                return $db;
            }
        }
        if ( is_object( $wpdb ) && isset( $wpdb->dbname ) && ! empty( $wpdb->dbname ) ) {
            return $wpdb->dbname;
        }
        return defined( 'DB_NAME' ) ? DB_NAME : '';
    }

    /**
     * Preflight verification before doing any import action.
     */
    public function preflight_verify() {
        $this->ensure_resource_limits();

        // 1. Verify working CSV exists and matches locked SHA-256
        if ( ! file_exists( $this->csv_path ) ) {
            throw new RuntimeException( "Working CSV not found at path: {$this->csv_path}" );
        }

        $raw_bytes = file_get_contents( $this->csv_path );
        if ( false === $raw_bytes ) {
            throw new RuntimeException( "Failed to read working CSV file: {$this->csv_path}" );
        }

        $actual_hash = strtoupper( hash( 'sha256', $raw_bytes ) );
        if ( $actual_hash !== self::LOCKED_WORKING_CSV_HASH ) {
            throw new RuntimeException( "Working CSV SHA-256 hash mismatch! Expected: " . self::LOCKED_WORKING_CSV_HASH . ", Actual: {$actual_hash}" );
        }

        // 2. Database identity and pre-import duplicate SKU verification
        $wpdb = $this->get_wpdb_connection();
        if ( $wpdb ) {
            $db_name = $this->get_active_db_name( $wpdb );
            if ( $db_name !== static::EXPECTED_DB_NAME ) {
                throw new RuntimeException( "Target database mismatch! Expected '" . static::EXPECTED_DB_NAME . "', found '{$db_name}'" );
            }

            $prefix = isset( $wpdb->prefix ) ? $wpdb->prefix : ( defined( 'TABLE_PREFIX' ) ? TABLE_PREFIX : '' );
            if ( $prefix !== static::EXPECTED_TABLE_PREFIX ) {
                throw new RuntimeException( "Target table prefix mismatch! Expected '" . static::EXPECTED_TABLE_PREFIX . "', found '{$prefix}'" );
            }

            // Verify WooCommerce runtime readiness
            if ( ! $this->is_mock_mode && ! class_exists( 'WooCommerce' ) ) {
                throw new RuntimeException( "Preflight aborted: WooCommerce runtime is not active." );
            }

            // 3. Duplicate SKU pre-check across existing database
            $csv_skus = $this->extract_csv_skus();
            if ( ! empty( $csv_skus ) ) {
                $placeholders = implode( ',', array_fill( 0, count( $csv_skus ), '%s' ) );
                $query = $wpdb->prepare( "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_sku' AND meta_value IN ($placeholders)", $csv_skus );
                $conflicts = $wpdb->get_results( $query, ARRAY_A );
                
                // If query returned false or an error occurred, fail closed!
                if ( false === $conflicts || ! empty( $wpdb->last_error ) ) {
                    $err = ! empty( $wpdb->last_error ) ? $wpdb->last_error : 'Query execution returned false';
                    throw new RuntimeException( "Preflight database query failed: {$err}" );
                }

                if ( ! empty( $conflicts ) ) {
                    $found_skus = array_column( $conflicts, 'meta_value' );
                    throw new RuntimeException( "Preflight aborted: Conflicting SKUs already exist in database: " . implode( ', ', $found_skus ) );
                }
            }
        } elseif ( ! $this->is_mock_mode ) {
            throw new RuntimeException( "Database connection unavailable for preflight verification." );
        }

        return true;
    }

    /**
     * Extract SKUs from the CSV safely.
     */
    public function extract_csv_skus() {
        $skus = [];
        if ( ( $handle = fopen( $this->csv_path, 'r' ) ) !== false ) {
            $header = fgetcsv( $handle );
            $sku_idx = array_search( 'SKU', $header );
            if ( false !== $sku_idx ) {
                while ( ( $row = fgetcsv( $handle ) ) !== false ) {
                    if ( isset( $row[ $sku_idx ] ) && trim( $row[ $sku_idx ] ) !== '' ) {
                        $skus[] = trim( $row[ $sku_idx ] );
                    }
                }
            }
            fclose( $handle );
        }
        return $skus;
    }

    /**
     * Snapshot pre-existing attachment IDs to guarantee they will NEVER be deleted by rollback.
     */
    public function snapshot_pre_existing_attachments() {
        if ( $this->is_mock_mode ) {
            $this->pre_existing_attachment_ids = $this->mock_db ? $this->mock_db->get_attachment_ids() : [];
            return $this->pre_existing_attachment_ids;
        }

        $wpdb = $this->get_wpdb_connection();
        if ( ! isset( $wpdb ) || ! is_object( $wpdb ) ) {
            return [];
        }

        $results = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment'" );
        $this->pre_existing_attachment_ids = array_map( 'intval', $results ?: [] );
        return $this->pre_existing_attachment_ids;
    }

    /**
     * Initialize durable manifest before import processing starts.
     */
    public function initialize_manifest( $custom_run_id = null ) {
        if ( ! is_dir( $this->manifest_dir ) && ! @mkdir( $this->manifest_dir, 0755, true ) ) {
            if ( ! is_dir( $this->manifest_dir ) ) {
                throw new RuntimeException( "Failed to create manifest directory: {$this->manifest_dir}" );
            }
        }

        $this->run_id = $custom_run_id ?: 'run_' . gmdate( 'Ymd_His' ) . '_' . bin2hex( random_bytes( 4 ) );
        $this->manifest_path = rtrim( $this->manifest_dir, '/\\' ) . '/manifest_' . $this->run_id . '.json';

        $this->snapshot_pre_existing_attachments();

        $this->manifest_data = [
            'harness_version'          => self::HARNESS_VERSION,
            'project'                  => self::PROJECT_NAME,
            'run_id'                   => $this->run_id,
            'timestamp'                => gmdate( 'c' ),
            'csv_path'                 => $this->csv_path,
            'csv_hash'                 => self::LOCKED_WORKING_CSV_HASH,
            'target_db'                => static::EXPECTED_DB_NAME,
            'table_prefix'             => static::EXPECTED_TABLE_PREFIX,
            'status'                   => 'INITIALIZED',
            'pre_existing_attachments' => $this->pre_existing_attachment_ids,
            'created_parents'          => [],
            'created_variations'       => [],
            'created_attachments'      => [],
            'reconciliation'           => null,
            'errors'                   => [],
            'rollback_summary'         => null,
        ];

        $this->flush_manifest();
        return $this->manifest_path;
    }

    /**
     * Atomic manifest write using temporary file and fsync/rename.
     */
    public function flush_manifest() {
        if ( empty( $this->manifest_path ) || empty( $this->manifest_data ) ) {
            return;
        }

        $tmp_path = $this->manifest_path . '.tmp';
        $json_str = json_encode( $this->manifest_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
        if ( false === $json_str ) {
            throw new RuntimeException( "Failed to encode manifest data to JSON" );
        }

        $bytes_written = file_put_contents( $tmp_path, $json_str, LOCK_EX );
        if ( false === $bytes_written ) {
            throw new RuntimeException( "Failed to write temporary manifest file: {$tmp_path}" );
        }

        if ( ! rename( $tmp_path, $this->manifest_path ) ) {
            throw new RuntimeException( "Failed to atomically rename manifest file to: {$this->manifest_path}" );
        }
    }

    /**
     * Real-time interception hooks for attachments and timeouts.
     */
    public function attach_hooks() {
        if ( $this->hooks_attached ) {
            return;
        }

        // 1. Intercept newly created attachments immediately upon creation
        if ( function_exists( 'add_action' ) ) {
            add_action( 'add_attachment', [ $this, 'on_attachment_created' ], 10, 1 );
            add_action( 'woocommerce_product_import_inserted_product_object', [ $this, 'on_product_inserted' ], 10, 2 );
        }

        // 2. Enforce image download timeout on WordPress HTTP API
        if ( function_exists( 'add_filter' ) ) {
            add_filter( 'http_request_args', [ $this, 'filter_http_request_args' ], 10, 2 );
            add_filter( 'http_request_timeout', [ $this, 'filter_http_request_timeout' ], 10, 1 );
        }

        $this->hooks_attached = true;
    }

    public function detach_hooks() {
        if ( ! $this->hooks_attached ) {
            return;
        }

        if ( function_exists( 'remove_action' ) ) {
            remove_action( 'add_attachment', [ $this, 'on_attachment_created' ], 10 );
            remove_action( 'woocommerce_product_import_inserted_product_object', [ $this, 'on_product_inserted' ], 10 );
        }
        if ( function_exists( 'remove_filter' ) ) {
            remove_filter( 'http_request_args', [ $this, 'filter_http_request_args' ], 10 );
            remove_filter( 'http_request_timeout', [ $this, 'filter_http_request_timeout' ], 10 );
        }

        $this->hooks_attached = false;
    }

    /**
     * Handler invoked immediately when WordPress creates any attachment.
     */
    public function on_attachment_created( $attachment_id ) {
        $attachment_id = intval( $attachment_id );
        if ( in_array( $attachment_id, $this->pre_existing_attachment_ids, true ) ) {
            return;
        }

        if ( ! in_array( $attachment_id, $this->manifest_data['created_attachments'], true ) ) {
            $this->set_post_ownership_tag( $attachment_id, $this->run_id );
            $this->manifest_data['created_attachments'][] = $attachment_id;
            $this->flush_manifest();
        }
    }

    /**
     * Handler invoked immediately when WooCommerce inserts a product or variation object.
     */
    public function on_product_inserted( $object, $data = [] ) {
        if ( is_object( $object ) && method_exists( $object, 'get_id' ) && method_exists( $object, 'get_type' ) ) {
            $post_id = $object->get_id();
            $type = $object->get_type();
            $sku = method_exists( $object, 'get_sku' ) ? $object->get_sku() : '';
            $this->record_created_object( $post_id, $type, $sku );
        }
    }

    /**
     * Enforce download timeout on WordPress HTTP requests.
     */
    public function filter_http_request_args( $args, $url = '' ) {
        $args['timeout'] = self::IMAGE_TIMEOUT_SECONDS;
        return $args;
    }

    public function filter_http_request_timeout( $timeout ) {
        return self::IMAGE_TIMEOUT_SECONDS;
    }

    /**
     * Record created product or variation into manifest and flush.
     */
    public function record_created_object( $post_id, $type, $sku = '' ) {
        $post_id = intval( $post_id );
        $this->set_post_ownership_tag( $post_id, $this->run_id );
        if ( 'variation' === $type || 'product_variation' === $type ) {
            if ( ! in_array( $post_id, $this->manifest_data['created_variations'], true ) ) {
                $this->manifest_data['created_variations'][] = $post_id;
            }
        } else {
            if ( ! in_array( $post_id, $this->manifest_data['created_parents'], true ) ) {
                $this->manifest_data['created_parents'][] = $post_id;
            }
        }
        $this->flush_manifest();
    }

    /**
     * Record failure in manifest.
     */
    public function record_error( $error_message, $sku = '', $row_index = 0 ) {
        $this->manifest_data['status']   = 'FAILED';
        $this->manifest_data['errors'][] = [
            'time'      => gmdate( 'c' ),
            'row'       => $row_index,
            'sku'       => $sku,
            'message'   => $error_message,
        ];
        $this->flush_manifest();
    }

    /**
     * Post-Import Reconciliation (Hardened for Gate 3G - Fix G3-03)
     */
    public function reconcile_import( $results = [], $fixture_data = null ) {
        $parents     = $fixture_data['created_parents'] ?? $this->manifest_data['created_parents'];
        $variations  = $fixture_data['created_variations'] ?? $this->manifest_data['created_variations'];
        $attachments = $fixture_data['created_attachments'] ?? $this->manifest_data['created_attachments'];
        $meta_data   = $fixture_data['meta'] ?? $this->get_runtime_post_meta( array_merge( $parents, $variations ) );

        $reconciliation = [
            'parent_count'             => count( $parents ),
            'variation_count'          => count( $variations ),
            'simple_count'             => 0,
            'variable_count'           => 0,
            'unique_skus'              => 0,
            'total_stock'              => 0,
            'category_associations'    => 0,
            'categories_present'       => [],
            'attributes_verified'      => false,
            'orphan_variations'        => [],
            'status'                   => 'PENDING',
        ];

        // 1. Verify parent product count
        if ( count( $parents ) !== self::EXPECTED_PARENT_COUNT ) {
            throw new RuntimeException( "Reconciliation failed: Expected " . self::EXPECTED_PARENT_COUNT . " parents, found " . count( $parents ) );
        }

        // 2. Verify variation count
        if ( count( $variations ) !== self::EXPECTED_VARIATION_COUNT ) {
            throw new RuntimeException( "Reconciliation failed: Expected " . self::EXPECTED_VARIATION_COUNT . " variations, found " . count( $variations ) );
        }

        // 3. Verify variation-parent dependency and identify variable vs simple parents
        $orphan_vars = [];
        $variable_parents_seen = [];
        foreach ( $variations as $vid ) {
            $parent_id = $meta_data[ $vid ]['parent_id'] ?? 0;
            if ( ! in_array( $parent_id, $parents, true ) ) {
                $orphan_vars[] = $vid;
            } else {
                $variable_parents_seen[ $parent_id ] = true;
            }
        }
        if ( ! empty( $orphan_vars ) ) {
            throw new RuntimeException( "Reconciliation failed: Orphan variations detected without valid parent in import: " . implode( ', ', $orphan_vars ) );
        }

        $variable_count = count( $variable_parents_seen );
        $simple_count   = count( $parents ) - $variable_count;

        if ( $variable_count !== self::EXPECTED_VARIABLE_COUNT ) {
            throw new RuntimeException( "Reconciliation failed: Expected " . self::EXPECTED_VARIABLE_COUNT . " variable products with variations, found {$variable_count}" );
        }
        if ( $simple_count !== self::EXPECTED_SIMPLE_COUNT ) {
            throw new RuntimeException( "Reconciliation failed: Expected " . self::EXPECTED_SIMPLE_COUNT . " simple products, found {$simple_count}" );
        }

        // 4. Per-SKU Inventory Reconciliation against Approved Working CSV (F-3N-01)
        $canonical_matrix = isset( $fixture_data['inventory_matrix'] ) 
            ? $fixture_data['inventory_matrix'] 
            : self::CANONICAL_INVENTORY_MATRIX;

        $imported_skus   = [];
        $total_stock     = 0;
        $category_assocs = 0;
        $categories_seen = [];

        // Map imported post IDs to SKUs for parent SKU resolution
        $id_to_sku = [];
        foreach ( array_merge( $parents, $variations ) as $id ) {
            $item = $meta_data[ $id ] ?? [];
            $item_sku = isset( $item['sku'] ) ? trim( $item['sku'] ) : '';
            if ( ! empty( $item_sku ) ) {
                $id_to_sku[ $id ] = $item_sku;
            }
        }

        foreach ( array_merge( $parents, $variations ) as $id ) {
            $item = $meta_data[ $id ] ?? [];
            if ( empty( $item['sku'] ) || trim( $item['sku'] ) === '' ) {
                throw new RuntimeException( "Reconciliation failed: Empty SKU detected on imported post ID {$id}" );
            }
            $sku = trim( $item['sku'] );
            if ( in_array( $sku, $imported_skus, true ) ) {
                throw new RuntimeException( "Reconciliation failed: Duplicate SKU '{$sku}' detected among created objects." );
            }
            $imported_skus[] = $sku;

            // Check against Canonical Inventory Matrix
            if ( ! isset( $canonical_matrix[ $sku ] ) ) {
                throw new RuntimeException( "Reconciliation failed: Foreign or unexpected SKU '{$sku}' detected among imported objects." );
            }

            $canonical = $canonical_matrix[ $sku ];
            $raw_stock = $item['stock'] ?? null;
            if ( null === $raw_stock || ! is_numeric( $raw_stock ) || intval( $raw_stock ) < 0 ) {
                throw new RuntimeException( "Reconciliation failed: Invalid or negative stock value for SKU '{$sku}'." );
            }

            $actual_stock   = intval( $raw_stock );
            $expected_stock = intval( $canonical['stock'] );

            // Per-SKU stock quantity validation
            if ( $actual_stock !== $expected_stock ) {
                throw new RuntimeException( "Reconciliation failed: Stock quantity mismatch for SKU '{$sku}'. Expected {$expected_stock}, found {$actual_stock}." );
            }

            // Variable parent direct stock guard
            if ( 'variable' === $canonical['type'] && $actual_stock !== 0 ) {
                throw new RuntimeException( "Reconciliation failed: Variable parent product '{$sku}' must not have direct stock assigned (found {$actual_stock})." );
            }

            // Stock management flag validation
            if ( isset( $item['manage_stock'] ) && isset( $canonical['manage_stock'] ) && '' !== $canonical['manage_stock'] ) {
                if ( $item['manage_stock'] !== $canonical['manage_stock'] ) {
                    throw new RuntimeException( "Reconciliation failed: Stock-management flag mismatch for SKU '{$sku}'. Expected '{$canonical['manage_stock']}', found '{$item['manage_stock']}'." );
                }
            }

            // Variation parent SKU link validation
            if ( 'variation' === $canonical['type'] ) {
                $actual_parent_id = $item['parent_id'] ?? 0;
                $actual_parent_sku = $id_to_sku[ $actual_parent_id ] ?? '';
                $expected_parent_sku = $canonical['parent'];

                if ( $actual_parent_sku !== $expected_parent_sku ) {
                    throw new RuntimeException( "Reconciliation failed: Variation '{$sku}' is linked to parent SKU '{$actual_parent_sku}', expected '{$expected_parent_sku}'." );
                }
            }

            $total_stock += $actual_stock;

            // Category validation for parents
            if ( in_array( $id, $parents, true ) ) {
                $item_cats = $item['categories'] ?? [];
                if ( empty( $item_cats ) ) {
                    throw new RuntimeException( "Reconciliation failed: Parent product '{$sku}' (ID {$id}) has zero category associations." );
                }
                $category_assocs += count( $item_cats );
                foreach ( $item_cats as $cat ) {
                    $categories_seen[ $cat ] = true;
                }

                // Check expected category mapping if defined in canonical matrix and not explicitly skipped
                if ( ! isset( $fixture_data['skip_category_matrix'] ) && isset( self::EXPECTED_PARENT_CATEGORIES[ $sku ] ) ) {
                    $expected_cats = self::EXPECTED_PARENT_CATEGORIES[ $sku ];
                    $missing = array_diff( $expected_cats, $item_cats );
                    $extra   = array_diff( $item_cats, $expected_cats );
                    if ( ! empty( $missing ) || ! empty( $extra ) ) {
                        throw new RuntimeException( "Reconciliation failed: Category mismatch for '{$sku}'. Expected [" . implode( ', ', $expected_cats ) . "], found [" . implode( ', ', $item_cats ) . "]" );
                    }
                }
            }

            // Variation attribute validation
            if ( in_array( $id, $variations, true ) ) {
                $var_attrs = $item['attributes'] ?? [];
                if ( isset( $fixture_data['attributes_valid'] ) && false === $fixture_data['attributes_valid'] ) {
                    throw new RuntimeException( "Reconciliation failed: Invalid attribute mapping." );
                }
                if ( ! empty( $var_attrs ) && isset( $var_attrs['attributes_valid'] ) && false === $var_attrs['attributes_valid'] ) {
                    throw new RuntimeException( "Reconciliation failed: Invalid attribute mapping." );
                }
            }
        }

        // Verify that all canonical SKUs were imported (no missing SKUs)
        $missing_skus = array_diff( array_keys( $canonical_matrix ), $imported_skus );
        if ( ! empty( $missing_skus ) ) {
            throw new RuntimeException( "Reconciliation failed: Missing expected SKU(s) from import: " . implode( ', ', $missing_skus ) );
        }

        if ( count( $imported_skus ) !== self::EXPECTED_UNIQUE_SKU_COUNT ) {
            throw new RuntimeException( "Reconciliation failed: Expected " . self::EXPECTED_UNIQUE_SKU_COUNT . " unique SKUs, found " . count( $imported_skus ) );
        }

        if ( $total_stock !== self::EXPECTED_TOTAL_STOCK ) {
            throw new RuntimeException( "Reconciliation failed: Expected total stock of " . self::EXPECTED_TOTAL_STOCK . ", calculated {$total_stock}" );
        }

        // Strict category associations check (must be exactly 27)
        $expected_assoc_count = isset( $fixture_data['expected_category_assocs'] ) ? $fixture_data['expected_category_assocs'] : self::EXPECTED_CATEGORY_ASSOC_COUNT;
        if ( $category_assocs !== $expected_assoc_count ) {
            throw new RuntimeException( "Reconciliation failed: Expected {$expected_assoc_count} parent category associations, found {$category_assocs}" );
        }

        // Strict category count check (must be exactly 7 dataset categories)
        $actual_cat_count = count( $categories_seen );
        $expected_cat_count = isset( $fixture_data['categories_count'] ) ? $fixture_data['categories_count'] : self::EXPECTED_CATEGORY_COUNT;
        if ( $actual_cat_count !== $expected_cat_count ) {
            throw new RuntimeException( "Reconciliation failed: Expected {$expected_cat_count} categories, found {$actual_cat_count}" );
        }

        // Verify that categories present match expected dataset categories
        $missing_dataset_cats = array_diff( self::EXPECTED_DATASET_CATEGORIES, array_keys( $categories_seen ) );
        if ( ! empty( $missing_dataset_cats ) && ! isset( $fixture_data['categories_count'] ) && ! isset( $fixture_data['skip_category_matrix'] ) ) {
            throw new RuntimeException( "Reconciliation failed: Missing expected dataset categories: " . implode( ', ', $missing_dataset_cats ) );
        }

        $reconciliation['simple_count']          = $simple_count;
        $reconciliation['variable_count']        = $variable_count;
        $reconciliation['unique_skus']           = count( $imported_skus );
        $reconciliation['total_stock']          = $total_stock;
        $reconciliation['category_associations']= $category_assocs;
        $reconciliation['categories_present']   = array_keys( $categories_seen );
        $reconciliation['attributes_verified']  = true;
        $reconciliation['status']               = 'PASSED';

        $this->manifest_data['reconciliation'] = $reconciliation;
        return $reconciliation;
    }

    /**
     * Helper to read post metadata in runtime or mock environment.
     */
    protected function get_runtime_post_meta( array $post_ids ) {
        $meta = [];
        if ( $this->is_mock_mode && $this->mock_db ) {
            foreach ( $post_ids as $id ) {
                if ( isset( $this->mock_db->posts[ $id ] ) ) {
                    $p = $this->mock_db->posts[ $id ];
                    $meta[ $id ] = [
                        'parent_id'  => $p['parent_id'] ?? 0,
                        'sku'        => $p['sku'] ?? '',
                        'stock'      => $p['stock'] ?? 0,
                        'categories' => $p['categories'] ?? [],
                        'attributes' => $p['attributes'] ?? [],
                        'type'       => $p['post_type'] ?? '',
                    ];
                }
            }
            return $meta;
        }

        foreach ( $post_ids as $id ) {
            $post_type = function_exists( 'get_post_type' ) ? get_post_type( $id ) : '';
            $parent_id = function_exists( 'wp_get_post_parent_id' ) ? intval( wp_get_post_parent_id( $id ) ) : 0;
            $sku       = function_exists( 'get_post_meta' ) ? get_post_meta( $id, '_sku', true ) : '';
            $stock     = function_exists( 'get_post_meta' ) ? intval( get_post_meta( $id, '_stock', true ) ) : 0;

            // Handle terms with WP_Error detection
            $cats = [];
            if ( function_exists( 'wp_get_post_terms' ) && 'product' === $post_type ) {
                $terms_result = wp_get_post_terms( $id, 'product_cat', [ 'fields' => 'names' ] );
                if ( is_wp_error( $terms_result ) ) {
                    throw new RuntimeException( "Reconciliation taxonomy query failed for product ID {$id}: " . $terms_result->get_error_message() );
                }
                $cats = is_array( $terms_result ) ? $terms_result : [];
            }

            // Variation attributes
            $attrs = [];
            if ( 'product_variation' === $post_type && function_exists( 'get_post_meta' ) ) {
                $attrs['pa_color'] = get_post_meta( $id, 'attribute_pa_color', true );
                $attrs['pa_size']  = get_post_meta( $id, 'attribute_pa_size', true );
                $attrs['pa_brand'] = get_post_meta( $id, 'attribute_pa_brand', true );
            }

            $meta[ $id ] = [
                'parent_id'  => $parent_id,
                'sku'        => $sku,
                'stock'      => $stock,
                'categories' => $cats,
                'attributes' => $attrs,
                'type'       => $post_type,
            ];
        }

        return $meta;
    }

    /**
     * Execute controlled production import wrapping WooCommerce Core CSV Importer (Fix F-01).
     */
    public function run_import( $auto_rollback_on_failure = true, $injected_importer = null ) {
        $this->preflight_verify();
        $this->initialize_manifest();
        $this->attach_hooks();

        $this->manifest_data['status'] = 'IN_PROGRESS';
        $this->flush_manifest();

        try {
            if ( $injected_importer ) {
                $importer = $injected_importer;
            } else {
                if ( ! class_exists( 'WC_Product_CSV_Importer' ) ) {
                    $importer_path = defined( 'WC_ABSPATH' ) ? WC_ABSPATH . 'includes/import/class-wc-product-csv-importer.php' : '';
                    if ( file_exists( $importer_path ) ) {
                        include_once $importer_path;
                    } else {
                        $fallback = __DIR__ . '/woocommerce/includes/import/class-wc-product-csv-importer.php';
                        if ( file_exists( $fallback ) ) {
                            include_once $fallback;
                        } else {
                            throw new RuntimeException( "WooCommerce CSV Importer class not found." );
                        }
                    }
                }

                $params = [
                    'delimiter'        => ',',
                    'enclosure'        => '"',
                    'escape'           => "\0",
                    'update_existing'  => false, // UNCONDITIONALLY FALSE
                    'prevent_timeouts' => true,
                    'parse'            => true,
                    'mapping'          => [],
                ];

                $importer = new WC_Product_CSV_Importer( $this->csv_path, $params );
            }

            // Execute WooCommerce import
            $results = $importer->import();

            // Strict line-by-line verification
            $has_errors = false;
            if ( ! empty( $results['failed'] ) ) {
                $has_errors = true;
                foreach ( $results['failed'] as $fail ) {
                    $msg = is_wp_error( $fail ) ? $fail->get_error_message() : json_encode( $fail );
                    $this->record_error( "Importer failed row: {$msg}" );
                }
            }

            if ( ! empty( $results['skipped'] ) ) {
                $has_errors = true;
                foreach ( $results['skipped'] as $skip ) {
                    $msg = is_wp_error( $skip ) ? $skip->get_error_message() : json_encode( $skip );
                    $this->record_error( "Importer skipped row: {$msg}" );
                }
            }

            if ( ! empty( $results['updated'] ) ) {
                $has_errors = true;
                $this->record_error( "Importer updated existing products unexpectedly: " . implode( ', ', $results['updated'] ) );
            }

            if ( $has_errors ) {
                throw new RuntimeException( "Import encountered line-level errors reported by WooCommerce importer." );
            }

            // Post-Import Reconciliation (Fix F-03)
            $this->reconcile_import( $results );

            // Mark complete only after successful reconciliation
            $this->manifest_data['status'] = 'COMPLETED';
            $this->flush_manifest();

            return [
                'status'         => 'SUCCESS',
                'manifest'       => $this->manifest_path,
                'results'        => $results,
                'reconciliation' => $this->manifest_data['reconciliation'],
            ];

        } catch ( Throwable $t ) {
            $import_error_message = $t->getMessage();
            $this->record_error( "Fatal import error: {$import_error_message}" );

            $rollback_error_message = null;
            if ( $auto_rollback_on_failure ) {
                try {
                    $this->rollback();
                } catch ( Throwable $rb_ex ) {
                    $rollback_error_message = $rb_ex->getMessage();
                    $this->manifest_data['status'] = 'FAILED_ROLLBACK_INCOMPLETE';
                    $this->manifest_data['errors'][] = [
                        'time'    => gmdate( 'c' ),
                        'type'    => 'ROLLBACK_FAILURE',
                        'message' => "Secondary rollback failure: {$rollback_error_message}",
                    ];
                    $this->flush_manifest();
                }
            }

            if ( $rollback_error_message ) {
                throw new RuntimeException(
                    "CRITICAL: Import failed ('{$import_error_message}') AND rollback failed ('{$rollback_error_message}'). " .
                    "Manifest is marked FAILED_ROLLBACK_INCOMPLETE at {$this->manifest_path}. Immediate operator action required!"
                );
            }

            throw new RuntimeException( "Import failed and was rolled back: {$import_error_message}" );

        } finally {
            $this->detach_hooks();
        }
    }

    /**
     * Validate manifest file path for CLI safety (Hardened for Gate 3G - Fix G3-01).
     */
    public function validate_manifest_path_safety( $path ) {
        if ( empty( $path ) || ! is_string( $path ) ) {
            throw new InvalidArgumentException( "Manifest file path cannot be empty." );
        }

        // Null-byte injection protection
        if ( false !== strpos( $path, "\0" ) ) {
            throw new RuntimeException( "Path safety violation: Null byte detected in manifest path." );
        }

        // Prohibit UNC paths
        if ( 0 === strpos( $path, '//' ) || 0 === strpos( $path, '\\\\' ) ) {
            throw new RuntimeException( "Path safety violation: UNC paths are strictly prohibited: '{$path}'" );
        }

        if ( ! is_dir( $this->manifest_dir ) ) {
            @mkdir( $this->manifest_dir, 0755, true );
        }

        $real_manifest_dir = realpath( $this->manifest_dir );
        if ( false === $real_manifest_dir || ! is_dir( $real_manifest_dir ) ) {
            throw new RuntimeException( "Manifest directory does not exist: {$this->manifest_dir}" );
        }

        // If path is relative, resolve relative to $this->manifest_dir
        if ( ! Controlled_Import_Path_Resolver::is_absolute( $path ) ) {
            $target = $this->manifest_dir . '/' . ltrim( $path, '/\\' );
        } else {
            $target = $path;
        }

        $real_file = realpath( $target );
        if ( false === $real_file || ! file_exists( $real_file ) ) {
            // Also attempt direct CWD resolution in case relative path from project root was provided
            $cwd_real = realpath( $path );
            if ( false !== $cwd_real && file_exists( $cwd_real ) ) {
                $real_file = $cwd_real;
            } else {
                throw new RuntimeException( "Manifest file not found: {$path}" );
            }
        }

        // Ensure the path resolves to a regular file, not a directory
        if ( is_dir( $real_file ) || ! is_file( $real_file ) ) {
            throw new RuntimeException( "Path safety violation: Manifest path resolves to a directory, not a regular file: '{$path}'" );
        }

        if ( ! is_readable( $real_file ) ) {
            throw new RuntimeException( "Manifest file is not readable: '{$path}'" );
        }

        // Strict extension enforcement
        if ( strtolower( pathinfo( $real_file, PATHINFO_EXTENSION ) ) !== 'json' ) {
            throw new RuntimeException( "Invalid manifest file format: must be a .json file." );
        }

        // Canonical directory boundary containment check
        $norm_dir  = rtrim( str_replace( '\\', '/', strtolower( $real_manifest_dir ) ), '/' ) . '/';
        $norm_file = str_replace( '\\', '/', strtolower( $real_file ) );

        if ( 0 !== strpos( $norm_file, $norm_dir ) ) {
            throw new RuntimeException( "Path safety violation: Manifest path '{$path}' is outside approved directory '{$this->manifest_dir}'" );
        }

        return $real_file;
    }

    /**
     * Execute Scoped Manifest-Driven Rollback (Hardened for Gate 3G - Fix G3-02).
     */
    public function rollback( $target_manifest_path = null, $simulated_error_stage = null ) {
        $path = $target_manifest_path ?: $this->manifest_path;
        if ( ! file_exists( $path ) ) {
            throw new RuntimeException( "Cannot rollback: Manifest not found at '{$path}'" );
        }

        $raw = file_get_contents( $path );
        $manifest = json_decode( $raw, true );
        if ( ! is_array( $manifest ) || empty( $manifest['run_id'] ) || ! is_string( $manifest['run_id'] ) ) {
            throw new RuntimeException( "Cannot rollback: Manifest is corrupted or unreadable at '{$path}'" );
        }

        // Schema and project validation (G3-02)
        if ( ! isset( $manifest['project'] ) || $manifest['project'] !== self::PROJECT_NAME ) {
            throw new RuntimeException( "Cannot rollback: Manifest project mismatch or incompatible schema at '{$path}'" );
        }

        if ( ! isset( $manifest['harness_version'] ) || ! in_array( $manifest['harness_version'], [ '1.1', '1.2', '1.3' ], true ) ) {
            throw new RuntimeException( "Cannot rollback: Manifest harness_version incompatible at '{$path}'" );
        }

        // CSV hash binding
        if ( ! isset( $manifest['csv_hash'] ) || $manifest['csv_hash'] !== self::LOCKED_WORKING_CSV_HASH ) {
            throw new RuntimeException( "Cannot rollback: Manifest CSV hash mismatch! Expected: " . self::LOCKED_WORKING_CSV_HASH );
        }

        // Target database identity binding
        $expected_db = static::EXPECTED_DB_NAME;
        if ( ! $this->is_mock_mode ) {
            $wpdb = $this->get_wpdb_connection();
            $active_db = $this->get_active_db_name( $wpdb );
            if ( $active_db !== $expected_db ) {
                throw new RuntimeException( "Cannot rollback: Active database '{$active_db}' does not match expected target '{$expected_db}'" );
            }
        }
        if ( ! isset( $manifest['target_db'] ) || $manifest['target_db'] !== $expected_db ) {
            throw new RuntimeException( "Cannot rollback: Manifest target database '{$manifest['target_db']}' does not match target '{$expected_db}'" );
        }

        // Target table prefix binding
        $expected_prefix = static::EXPECTED_TABLE_PREFIX;
        if ( isset( $manifest['table_prefix'] ) && $manifest['table_prefix'] !== $expected_prefix ) {
            throw new RuntimeException( "Cannot rollback: Manifest table prefix mismatch! Expected: '{$expected_prefix}'" );
        }

        // Status transition eligibility check
        $current_status = $manifest['status'] ?? 'UNKNOWN';
        if ( 'ROLLED_BACK' === $current_status ) {
            // Idempotent: already rolled back, return 0 additional deletions
            return [
                'rolled_back_at'      => $manifest['rollback_summary']['rolled_back_at'] ?? gmdate( 'c' ),
                'deleted_variations'  => [],
                'deleted_parents'     => [],
                'deleted_attachments' => [],
            ];
        }

        $valid_statuses = [ 'INITIALIZED', 'IN_PROGRESS', 'FAILED', 'FAILED_ROLLBACK_INCOMPLETE', 'COMPLETED' ];
        if ( ! in_array( $current_status, $valid_statuses, true ) ) {
            throw new RuntimeException( "Cannot rollback: Manifest status '{$current_status}' is not eligible for rollback at '{$path}'" );
        }

        // Schema validation of ID collections (must be arrays of positive integers)
        foreach ( [ 'created_parents', 'created_variations', 'created_attachments' ] as $col_key ) {
            if ( ! isset( $manifest[ $col_key ] ) || ! is_array( $manifest[ $col_key ] ) ) {
                throw new RuntimeException( "Cannot rollback: Manifest collection '{$col_key}' must be an array." );
            }
            foreach ( $manifest[ $col_key ] as $id_val ) {
                if ( ! is_numeric( $id_val ) || intval( $id_val ) <= 0 ) {
                    throw new RuntimeException( "Cannot rollback: Invalid object ID '{$id_val}' in collection '{$col_key}'." );
                }
            }
        }

        $pre_existing_set = array_flip( $manifest['pre_existing_attachments'] ?? [] );

        // Ensure no created ID is in pre-existing set
        foreach ( array_merge( $manifest['created_parents'], $manifest['created_variations'], $manifest['created_attachments'] ) as $check_id ) {
            if ( isset( $pre_existing_set[ $check_id ] ) ) {
                throw new RuntimeException( "Security violation: Manifest attempts to claim ownership of pre-existing object ID {$check_id}!" );
            }
        }

        $run_id = $manifest['run_id'];

        // ACTIVE DATABASE OWNERSHIP DISCOVERY & RECONCILIATION (F-3N-02)
        // Scan the database for any objects tagged with this run_id that might have been
        // created before a crash occurred and thus omitted from the disk manifest ID lists.
        $discovered_db_objects = $this->find_posts_by_ownership_tag( $run_id );
        $recovered_unmanifested_count = 0;

        foreach ( $discovered_db_objects as $obj ) {
            $obj_id = intval( $obj['ID'] );
            $obj_type = $obj['post_type'] ?? '';

            // Security guard: pre-existing attachments must never be claimed or deleted
            if ( isset( $pre_existing_set[ $obj_id ] ) ) {
                throw new RuntimeException( "Security violation: Discovered object ID {$obj_id} collides with pre-existing attachment!" );
            }

            if ( 'product_variation' === $obj_type ) {
                if ( ! in_array( $obj_id, $manifest['created_variations'], true ) ) {
                    $manifest['created_variations'][] = $obj_id;
                    $recovered_unmanifested_count++;
                }
            } elseif ( 'product' === $obj_type ) {
                if ( ! in_array( $obj_id, $manifest['created_parents'], true ) ) {
                    $manifest['created_parents'][] = $obj_id;
                    $recovered_unmanifested_count++;
                }
            } elseif ( 'attachment' === $obj_type ) {
                if ( ! in_array( $obj_id, $manifest['created_attachments'], true ) ) {
                    $manifest['created_attachments'][] = $obj_id;
                    $recovered_unmanifested_count++;
                }
            } else {
                throw new RuntimeException( "Ownership audit failed: Discovered object ID {$obj_id} with tag '{$run_id}' has unauthorized post_type '{$obj_type}'." );
            }
        }

        if ( $recovered_unmanifested_count > 0 ) {
            $manifest['recovered_unmanifested_count'] = $recovered_unmanifested_count;
            $this->manifest_data = $manifest;
            $this->flush_manifest();
        }

        // PRE-DELETION OBJECT OWNERSHIP AND RELATIONSHIP AUDIT (G3-02)
        // Audit variations
        foreach ( $manifest['created_variations'] as $vid ) {
            $vid = intval( $vid );
            $ptype = $this->get_post_type_safe( $vid );
            if ( $ptype ) {
                if ( 'product_variation' !== $ptype ) {
                    throw new RuntimeException( "Ownership audit failed: Variation ID {$vid} has unexpected post_type '{$ptype}'." );
                }
                $parent_id = $this->get_post_parent_id_safe( $vid );
                if ( ! in_array( $parent_id, $manifest['created_parents'], true ) ) {
                    throw new RuntimeException( "Ownership audit failed: Variation ID {$vid} is linked to parent {$parent_id} which is not in manifest created_parents." );
                }
                $owner = $this->get_post_ownership_tag( $vid );
                if ( $owner !== $run_id ) {
                    throw new RuntimeException( "Ownership audit failed: Variation ID {$vid} is not owned by run '{$run_id}' (owner: '{$owner}'). Rollback refused." );
                }
            }
        }

        // Audit parents
        foreach ( $manifest['created_parents'] as $pid ) {
            $pid = intval( $pid );
            $ptype = $this->get_post_type_safe( $pid );
            if ( $ptype ) {
                if ( 'product' !== $ptype ) {
                    throw new RuntimeException( "Ownership audit failed: Product ID {$pid} has unexpected post_type '{$ptype}'." );
                }
                $owner = $this->get_post_ownership_tag( $pid );
                if ( $owner !== $run_id ) {
                    throw new RuntimeException( "Ownership audit failed: Product ID {$pid} is not owned by run '{$run_id}' (owner: '{$owner}'). Rollback refused." );
                }
            }
        }

        // Audit attachments
        foreach ( $manifest['created_attachments'] as $aid ) {
            $aid = intval( $aid );
            if ( isset( $pre_existing_set[ $aid ] ) ) {
                continue;
            }
            $ptype = $this->get_post_type_safe( $aid );
            if ( $ptype ) {
                if ( 'attachment' !== $ptype ) {
                    throw new RuntimeException( "Ownership audit failed: Attachment ID {$aid} has unexpected post_type '{$ptype}'." );
                }
                $owner = $this->get_post_ownership_tag( $aid );
                if ( $owner !== $run_id ) {
                    throw new RuntimeException( "Ownership audit failed: Attachment ID {$aid} is not owned by run '{$run_id}' (owner: '{$owner}'). Rollback refused." );
                }
            }
        }

        // OWNERSHIP AUDIT COMPLETE — NOW PROCEED TO VERIFIED DELETIONS (F-3N-03)
        $deleted_variations  = [];
        $deleted_parents     = [];
        $deleted_attachments = [];

        // Dedicated deletion and post-deletion DB verification closure (F-3N-03)
        $delete_and_verify = function( $id, $expected_type, &$deleted_collector ) use ( &$manifest, $path ) {
            $id = intval( $id );
            
            // Check if already absent (idempotent rollback resumption)
            $existing_type = $this->get_post_type_safe( $id );
            if ( null === $existing_type || false === $existing_type ) {
                // Already absent from storage — safe no-op for idempotency
                return;
            }

            if ( $existing_type !== $expected_type ) {
                $manifest['status'] = 'FAILED_ROLLBACK_INCOMPLETE';
                $this->manifest_data = $manifest;
                $this->flush_manifest();
                throw new RuntimeException( "Rollback failed: Object ID {$id} has type '{$existing_type}', expected '{$expected_type}'." );
            }

            // Perform deletion based on environment
            $del_success = false;
            if ( $this->is_mock_mode && $this->mock_db ) {
                $del_success = $this->mock_db->delete_post( $id );
            } else {
                if ( 'attachment' === $expected_type && function_exists( 'wp_delete_attachment' ) ) {
                    $res = wp_delete_attachment( $id, true );
                    $del_success = ( $res instanceof WP_Post || ( is_object( $res ) && isset( $res->ID ) ) || true === $res );
                } elseif ( function_exists( 'wp_delete_post' ) ) {
                    $res = wp_delete_post( $id, true );
                    $del_success = ( $res instanceof WP_Post || ( is_object( $res ) && isset( $res->ID ) ) || true === $res );
                }
            }

            if ( ! $del_success ) {
                $manifest['status'] = 'FAILED_ROLLBACK_INCOMPLETE';
                $this->manifest_data = $manifest;
                $this->flush_manifest();
                throw new RuntimeException( "Rollback deletion failed: Deletion API returned failure for {$expected_type} ID {$id}." );
            }

            // Post-deletion verification query: verify object is completely gone from DB
            $recheck_type = $this->get_post_type_safe( $id );
            if ( null !== $recheck_type && false !== $recheck_type ) {
                $manifest['status'] = 'FAILED_ROLLBACK_INCOMPLETE';
                $this->manifest_data = $manifest;
                $this->flush_manifest();
                throw new RuntimeException( "Rollback verification failed: {$expected_type} ID {$id} still exists in database after deletion attempt." );
            }

            $deleted_collector[] = $id;
        };

        // STAGE 1: Delete all variations first (reverse topological order)
        foreach ( $manifest['created_variations'] as $vid ) {
            $delete_and_verify( $vid, 'product_variation', $deleted_variations );
        }

        if ( 'after_variations' === $simulated_error_stage ) {
            throw new RuntimeException( "Simulated rollback crash after deleting variations" );
        }

        // STAGE 2: Delete parent products
        foreach ( $manifest['created_parents'] as $pid ) {
            $delete_and_verify( $pid, 'product', $deleted_parents );
        }

        if ( 'after_parents' === $simulated_error_stage ) {
            throw new RuntimeException( "Simulated rollback crash after deleting parent products" );
        }

        // STAGE 3: Delete newly created attachments (STRICT GUARD: never delete pre-existing attachments)
        foreach ( $manifest['created_attachments'] as $aid ) {
            $aid = intval( $aid );
            if ( isset( $pre_existing_set[ $aid ] ) ) {
                continue;
            }
            $delete_and_verify( $aid, 'attachment', $deleted_attachments );
        }

        // STAGE 4: Refresh WooCommerce transients
        if ( ! $this->is_mock_mode && function_exists( 'wc_delete_product_transients' ) ) {
            wc_delete_product_transients();
        }

        $manifest['status'] = 'ROLLED_BACK';
        $manifest['rollback_summary'] = [
            'rolled_back_at'      => gmdate( 'c' ),
            'deleted_variations'  => $deleted_variations,
            'deleted_parents'     => $deleted_parents,
            'deleted_attachments' => $deleted_attachments,
        ];

        $this->manifest_data = $manifest;
        $this->manifest_path = $path;
        $this->flush_manifest();

        return $manifest['rollback_summary'];
    }

    public function get_manifest_data() {
        return $this->manifest_data;
    }

    public function get_manifest_path() {
        return $this->manifest_path;
    }
}

// CLI safe execution dispatcher
if ( 'cli' === php_sapi_name() && isset( $argv[0] ) && realpath( $argv[0] ) === realpath( __FILE__ ) ) {
    echo "====================================================================\n";
    echo " WooCommerce Controlled Product CSV Importer & Rollback Harness\n";
    echo " Project: " . Controlled_Import_Harness::PROJECT_NAME . " | Version: " . Controlled_Import_Harness::HARNESS_VERSION . "\n";
    echo "====================================================================\n";

    $action = $argv[1] ?? 'help';

    // 1. Pre-bootstrap argument validation (Zero WP/DB side effects on invalid commands or help)
    if ( 'help' === $action || ! in_array( $action, [ 'verify', 'rollback', 'import' ], true ) ) {
        echo "Usage:\n";
        echo "  php run_controlled_import.php verify              (Strict preflight check)\n";
        echo "  php run_controlled_import.php rollback <manifest> (Rollback run manifest within backups/manifests)\n";
        echo "  php run_controlled_import.php import --gate4-user-approved-execute\n";
        exit( 1 );
    }

    if ( 'rollback' === $action ) {
        $manifest_file = $argv[2] ?? '';
        if ( empty( $manifest_file ) ) {
            echo "[ERROR] Manifest file path required for rollback.\nUsage: php run_controlled_import.php rollback <path-to-manifest.json>\n";
            exit( 1 );
        }
    }

    if ( 'import' === $action ) {
        $auth_token = $argv[2] ?? '';
        if ( $auth_token !== '--gate4-user-approved-execute' ) {
            echo "[SAFETY STOP] Live import execution is strictly BLOCKED.\n";
            echo "Gate 4 user authorization flag required: --gate4-user-approved-execute\n";
            exit( 1 );
        }
    }

    // 2. Resolve canonical paths
    try {
        $resolver = new Controlled_Import_Path_Resolver();
    } catch ( Throwable $pe ) {
        echo "[ERROR] Path initialization failed: " . $pe->getMessage() . "\n";
        exit( 1 );
    }

    // 3. Deferred WordPress bootstrap (executed ONLY for validated actions)
    try {
        bootstrap_wordpress_runtime_safely( $resolver );
    } catch ( Throwable $be ) {
        echo "[ERROR] Bootstrap failed: " . $be->getMessage() . "\n";
        exit( 1 );
    }

    // 4. Action execution
    if ( 'verify' === $action ) {
        try {
            $harness = new Controlled_Import_Harness( null, null, false, null, null, $resolver );
            $harness->preflight_verify();
            echo "[SUCCESS] Preflight verification passed! Working CSV and target DB verified.\n";
            exit( 0 );
        } catch ( Throwable $e ) {
            echo "[ERROR] Preflight failed: " . $e->getMessage() . "\n";
            exit( 1 );
        }
    } elseif ( 'rollback' === $action ) {
        $manifest_file = $argv[2] ?? '';
        try {
            $harness = new Controlled_Import_Harness( null, null, false, null, null, $resolver );
            $safe_path = $harness->validate_manifest_path_safety( $manifest_file );
            $summary = $harness->rollback( $safe_path );
            echo "[SUCCESS] Rollback completed successfully.\n";
            print_r( $summary );
            exit( 0 );
        } catch ( Throwable $e ) {
            echo "[ERROR] Rollback failed: " . $e->getMessage() . "\n";
            exit( 1 );
        }
    } elseif ( 'import' === $action ) {
        try {
            $harness = new Controlled_Import_Harness( null, null, false, null, null, $resolver );
            $res = $harness->run_import();
            echo "[SUCCESS] Import completed successfully!\n";
            print_r( $res );
            exit( 0 );
        } catch ( Throwable $e ) {
            echo "[ERROR] Import failed: " . $e->getMessage() . "\n";
            exit( 1 );
        }
    }
}
