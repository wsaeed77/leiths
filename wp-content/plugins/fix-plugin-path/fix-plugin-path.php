<?php
/**
 * Plugin Name: Fix Plugin Path
 * Description: Fixes incorrect plugin path registration for School Finder Pro. Delete after use.
 * Version: 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fix plugin path on activation
 */
function fix_plugin_path_activate() {
    // Clear all caches
    wp_cache_delete('plugins', 'plugins');
    delete_transient('plugins');
    delete_site_transient('update_plugins');
    
    // Force WordPress to rescan plugins
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
    
    // Get all plugins
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    $all_plugins = get_plugins();
    
    // Find the incorrectly registered plugin
    $wrong_path = 'school-finder-pro/school-finder-pro/school-finder-pro.php';
    $correct_path = 'school-finder-pro/school-finder-pro.php';
    
    // Check if wrong path exists in plugin list
    if (isset($all_plugins[$wrong_path])) {
        // Get plugin data
        $plugin_data = $all_plugins[$wrong_path];
        
        // Check if correct path file exists
        $correct_file = WP_PLUGIN_DIR . '/' . $correct_path;
        if (file_exists($correct_file)) {
            // Clear cache again
            wp_cache_delete('plugins', 'plugins');
            delete_transient('plugins');
            
            // Force rescan
            wp_cache_flush();
        }
    }
    
    // Redirect to plugins page
    wp_redirect(admin_url('plugins.php?path_fixed=1'));
    exit;
}

register_activation_hook(__FILE__, 'fix_plugin_path_activate');

/**
 * Clear cache on every admin page load
 */
function fix_plugin_path_clear_cache() {
    if (is_admin()) {
        wp_cache_delete('plugins', 'plugins');
        delete_transient('plugins');
    }
}

add_action('admin_init', 'fix_plugin_path_clear_cache', 1);

/**
 * Show admin notice
 */
function fix_plugin_path_notice() {
    if (isset($_GET['path_fixed'])) {
        echo '<div class="notice notice-success is-dismissible"><p><strong>Cache cleared!</strong> Please refresh this page (F5), then try activating School Finder Pro. If it still shows the wrong path, delete School Finder Pro and reinstall it.</p></div>';
    }
}

add_action('admin_notices', 'fix_plugin_path_notice');
