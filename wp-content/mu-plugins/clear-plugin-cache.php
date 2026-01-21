<?php
/**
 * Plugin Name: Clear Plugin Cache
 * Description: Clears WordPress plugin cache to fix incorrect plugin path detection
 * Version: 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clear plugin cache on admin pages
 */
function clear_plugin_cache_on_admin() {
    if (is_admin()) {
        // Clear plugin cache
        wp_cache_delete('plugins', 'plugins');
        
        // Clear plugin transients
        delete_transient('plugins');
        delete_site_transient('update_plugins');
        
        // Force WordPress to rescan plugins
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
    }
}

// Run on admin init
add_action('admin_init', 'clear_plugin_cache_on_admin', 1);

/**
 * Clear plugin cache when accessing plugins page
 */
function clear_plugin_cache_on_plugins_page() {
    if (isset($_GET['page']) && $_GET['page'] === 'plugins.php') {
        wp_cache_delete('plugins', 'plugins');
        delete_transient('plugins');
    }
}

add_action('admin_init', 'clear_plugin_cache_on_plugins_page', 1);

/**
 * Force plugin rescan on plugins page load
 */
function force_plugin_rescan() {
    if (isset($_GET['page']) && $_GET['page'] === 'plugins.php') {
        // Clear all plugin-related caches
        wp_cache_delete('plugins', 'plugins');
        delete_transient('plugins');
        delete_site_transient('update_plugins');
        
        // Force get_plugins() to rescan
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
    }
}

add_action('load-plugins.php', 'force_plugin_rescan', 1);
