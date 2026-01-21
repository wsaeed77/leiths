<?php
/**
 * Plugin Name: School Lookup
 * Plugin URI: https://example.com/school-lookup
 * Description: A plugin to manage and search through a database of schools. Upload CSV files with school data and provide a searchable dropdown on the frontend.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: school-lookup
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SCHOOL_LOOKUP_VERSION', '1.0.0');
define('SCHOOL_LOOKUP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SCHOOL_LOOKUP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SCHOOL_LOOKUP_PLUGIN_FILE', __FILE__);

/**
 * Main plugin class
 */
class School_Lookup {
    
    /**
     * Instance of this class
     */
    private static $instance = null;
    
    /**
     * Get instance of this class
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Initialize plugin
     */
    private function init() {
        // Load plugin files
        $this->load_dependencies();
        
        // Register activation/deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Initialize admin
        if (is_admin()) {
            new School_Lookup_Admin();
        }
        
        // Initialize frontend
        new School_Lookup_Frontend();
        
        // Initialize AJAX handlers
        add_action('wp_ajax_school_lookup_search', array($this, 'ajax_search_schools'));
        add_action('wp_ajax_nopriv_school_lookup_search', array($this, 'ajax_search_schools'));
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        require_once SCHOOL_LOOKUP_PLUGIN_DIR . 'includes/class-school-lookup-database.php';
        require_once SCHOOL_LOOKUP_PLUGIN_DIR . 'includes/class-school-lookup-admin.php';
        require_once SCHOOL_LOOKUP_PLUGIN_DIR . 'includes/class-school-lookup-frontend.php';
    }
    
    /**
     * Activate plugin
     */
    public function activate() {
        School_Lookup_Database::create_table();
        flush_rewrite_rules();
    }
    
    /**
     * Deactivate plugin
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * AJAX handler for school search
     */
    public function ajax_search_schools() {
        check_ajax_referer('school_lookup_search', 'nonce');
        
        $search_term = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 10;
        
        if (empty($search_term)) {
            wp_send_json_error(array('message' => 'Search term is required'));
            return;
        }
        
        $schools = School_Lookup_Database::search_schools($search_term, $limit);
        
        wp_send_json_success(array('schools' => $schools));
    }
}

/**
 * Initialize the plugin
 */
function school_lookup_init() {
    return School_Lookup::get_instance();
}

// Start the plugin
school_lookup_init();
