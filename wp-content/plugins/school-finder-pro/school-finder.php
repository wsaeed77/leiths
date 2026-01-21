<?php
/**
 * Plugin Name: School Finder Pro
 * Plugin URI: https://example.com/school-finder-pro
 * Description: A plugin to manage and search through a database of schools. Upload CSV files with school data and provide a searchable dropdown on the frontend.
 * Version: 1.0.3
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: school-finder-pro
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SCHOOL_FINDER_PRO_VERSION', '1.0.5');
define('SCHOOL_FINDER_PRO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SCHOOL_FINDER_PRO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SCHOOL_FINDER_PRO_PLUGIN_FILE', __FILE__);

/**
 * Main plugin class
 */
class School_Finder_Pro {
    
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
        $this->load_dependencies();
        
        if (is_admin()) {
            new School_Finder_Pro_Admin();
        }
        
        new School_Finder_Pro_Frontend();
        
        add_action('wp_ajax_school_finder_pro_search', array($this, 'ajax_search_schools'));
        add_action('wp_ajax_nopriv_school_finder_pro_search', array($this, 'ajax_search_schools'));
    }
    
    private function load_dependencies() {
        $db_file = SCHOOL_FINDER_PRO_PLUGIN_DIR . 'includes/class-school-finder-pro-database.php';
        $admin_file = SCHOOL_FINDER_PRO_PLUGIN_DIR . 'includes/class-school-finder-pro-admin.php';
        $frontend_file = SCHOOL_FINDER_PRO_PLUGIN_DIR . 'includes/class-school-finder-pro-frontend.php';
        $gravity_forms_file = SCHOOL_FINDER_PRO_PLUGIN_DIR . 'includes/class-school-finder-pro-gravity-forms.php';
        
        // Check all files exist
        $missing_files = array();
        if (!file_exists($db_file)) {
            $missing_files[] = $db_file;
        }
        if (!file_exists($admin_file)) {
            $missing_files[] = $admin_file;
        }
        if (!file_exists($frontend_file)) {
            $missing_files[] = $frontend_file;
        }
        
        if (!empty($missing_files)) {
            add_action('admin_notices', function() use ($missing_files) {
                echo '<div class="error"><p><strong>School Finder Pro Error:</strong> Required files not found:</p><ul>';
                foreach ($missing_files as $file) {
                    echo '<li>' . esc_html($file) . '</li>';
                }
                echo '</ul><p>Plugin directory: ' . esc_html(SCHOOL_FINDER_PRO_PLUGIN_DIR) . '</p><p>Please re-upload the plugin zip file or extract it manually.</p></div>';
            });
            return;
        }
        
        // Load all files
        require_once $db_file;
        require_once $admin_file;
        require_once $frontend_file;
        
        // Load Gravity Forms integration if Gravity Forms is active
        // Check for GFForms instead of GF_Field as it's loaded earlier
        if (class_exists('GFForms') && file_exists($gravity_forms_file)) {
            require_once $gravity_forms_file;
        }
        
        // Verify classes exist after loading
        if (!class_exists('School_Finder_Pro_Database')) {
            wp_die('Error: School_Finder_Pro_Database class not found after loading file: ' . $db_file);
        }
        if (!class_exists('School_Finder_Pro_Admin')) {
            wp_die('Error: School_Finder_Pro_Admin class not found after loading file: ' . $admin_file);
        }
        if (!class_exists('School_Finder_Pro_Frontend')) {
            wp_die('Error: School_Finder_Pro_Frontend class not found after loading file: ' . $frontend_file);
        }
    }
    
    public function ajax_search_schools() {
        check_ajax_referer('school_finder_pro_search', 'nonce');
        
        $search_term = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 10;
        
        if (empty($search_term)) {
            wp_send_json_error(array('message' => 'Search term is required'));
            return;
        }
        
        $schools = School_Finder_Pro_Database::search_schools($search_term, $limit);
        
        wp_send_json_success(array('schools' => $schools));
    }
}

function school_finder_pro_init() {
    return School_Finder_Pro::get_instance();
}

function school_finder_pro_activate() {
    $db_file = SCHOOL_FINDER_PRO_PLUGIN_DIR . 'includes/class-school-finder-pro-database.php';
    
    if (!file_exists($db_file)) {
        wp_die('Error: Database class file not found at: ' . $db_file);
    }
    
    require_once $db_file;
    
    if (!class_exists('School_Finder_Pro_Database')) {
        wp_die('Error: School_Finder_Pro_Database class not found after including file.');
    }
    
    School_Finder_Pro_Database::create_table();
    flush_rewrite_rules();
}

function school_finder_pro_deactivate() {
    flush_rewrite_rules();
}

register_activation_hook(__FILE__, 'school_finder_pro_activate');
register_deactivation_hook(__FILE__, 'school_finder_pro_deactivate');

school_finder_pro_init();
