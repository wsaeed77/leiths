<?php
/**
 * Plugin Name: Fix Plugin Cache
 * Description: Clears WordPress plugin cache to fix incorrect plugin path detection. Delete this plugin after use.
 * Version: 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clear plugin cache immediately on activation
 */
function fix_plugin_cache_activate() {
    // Clear all plugin caches
    wp_cache_delete('plugins', 'plugins');
    delete_transient('plugins');
    delete_site_transient('update_plugins');
    
    // Force cache flush
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
    
    // Redirect to plugins page to see the fix
    wp_redirect(admin_url('plugins.php?cache_cleared=1'));
    exit;
}

register_activation_hook(__FILE__, 'fix_plugin_cache_activate');

/**
 * Clear cache on admin pages
 */
function fix_plugin_cache_clear() {
    if (is_admin()) {
        wp_cache_delete('plugins', 'plugins');
        delete_transient('plugins');
    }
}

add_action('admin_init', 'fix_plugin_cache_clear', 1);

/**
 * Show admin notice
 */
function fix_plugin_cache_notice() {
    if (isset($_GET['cache_cleared'])) {
        echo '<div class="notice notice-success is-dismissible"><p>Plugin cache cleared! Please try activating School Finder Pro now. You can delete this plugin after School Finder Pro is activated.</p></div>';
    }
}

add_action('admin_notices', 'fix_plugin_cache_notice');
