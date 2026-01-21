<?php
/**
 * Plugin Name: School Search
 * Plugin URI: https://example.com/school-search
 * Description: A plugin to manage and search through a database of schools. Upload CSV files with school data and provide a searchable dropdown on the frontend.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: school-search
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SCHOOL_SEARCH_VERSION', '1.0.0');
define('SCHOOL_SEARCH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SCHOOL_SEARCH_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SCHOOL_SEARCH_PLUGIN_FILE', __FILE__);

// Load plugin files
require_once SCHOOL_SEARCH_PLUGIN_DIR . 'includes/class-school-search-database.php';
require_once SCHOOL_SEARCH_PLUGIN_DIR . 'includes/class-school-search-admin.php';
require_once SCHOOL_SEARCH_PLUGIN_DIR . 'includes/class-school-search-frontend.php';

/**
 * Main plugin class
 */
class School_Search {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init();
    }
    
    private function init() {
        if (is_admin()) {
            new School_Search_Admin();
        }
        
        new School_Search_Frontend();
        
        add_action('wp_ajax_school_search_search', array($this, 'ajax_search_schools'));
        add_action('wp_ajax_nopriv_school_search_search', array($this, 'ajax_search_schools'));
    }
    
    public function ajax_search_schools() {
        check_ajax_referer('school_search_search', 'nonce');
        
        $search_term = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 10;
        
        if (empty($search_term)) {
            wp_send_json_error(array('message' => 'Search term is required'));
            return;
        }
        
        $schools = School_Search_Database::search_schools($search_term, $limit);
        
        wp_send_json_success(array('schools' => $schools));
    }
}

/**
 * Activation hook
 */
function school_search_activate() {
    require_once SCHOOL_SEARCH_PLUGIN_DIR . 'includes/class-school-search-database.php';
    School_Search_Database::create_table();
    flush_rewrite_rules();
}

/**
 * Deactivation hook
 */
function school_search_deactivate() {
    flush_rewrite_rules();
}

// Register hooks
register_activation_hook(__FILE__, 'school_search_activate');
register_deactivation_hook(__FILE__, 'school_search_deactivate');

// Start the plugin
School_Search::get_instance();
