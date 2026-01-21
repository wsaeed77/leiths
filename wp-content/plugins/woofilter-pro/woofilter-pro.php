<?php
/**
 * Plugin Name: WBW Product Filter PRO
 * Description: Product Filter by WBW PRO. Best plugins from WBW!
 * Plugin URI: https://woobewoo.com/product/woocommerce-filter/
 * Author: WBW
 * Author URI: https://woobewoo.com/
 * Version: 2.6.5
 * WC requires at least: 3.4.0
 * WC tested up to: 9.0.2
 **/
define('WPF_FREE_REQUIRES', '2.6.5');
// we use it as fallback for a cases when we cant parse install.xml fale
define('WPF_PRO_MODULES',
	serialize(
		array(
			array (
				'code' =>  'license',
				'active' =>  '1',
				'type_id' =>  '6',
				'label' =>  'license',
			),
			array (
				'code' =>  'access',
				'active' =>  '1',
				'type_id' =>  '6',
				'label' =>  'access',
			),
			array (
				'code' =>  'woofilterpro',
				'active' =>  '1',
				'type_id' =>  '6',
				'label' =>  'woofilterpro',
			),
			array (
				'code' =>  'statistics',
				'active' =>  '1',
				'type_id' =>  '6',
				'label' =>  'Statistics',
			)
		)
	)
);
if (!defined('WPF_SITE_URL')) {
	define('WPF_SITE_URL', get_bloginfo('wpurl') . '/');
}

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'wpUpdater.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'dbUpdater.php');

register_activation_hook(__FILE__, 'woofilterProActivateCallback');
register_deactivation_hook(__FILE__, 'woofilterProDeactivateCallback');
register_uninstall_hook(__FILE__, 'woofilterProUninstallCallback');

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

add_filter('pre_set_site_transient_update_plugins', 'checkForPluginUpdatewoofilterPro');
add_filter('plugins_api', 'myPluginApiCallwoofilterPro', 10, 3);
if (!function_exists('getProPlugCodeWpf')) {
	function getProPlugCodeWpf() {
		return 'woofilter_pro';
	}
}
if (!function_exists('getProPlugDirWpf')) {
	function getProPlugDirWpf() {
		return basename(dirname(__FILE__));
	}
}
if (!function_exists('getProPlugFileWpf')) {
	function getProPlugFileWpf() {
		return basename(__FILE__);
	}
}
if (!function_exists('getProPlugFullPathWpf')) {
	function getProPlugFullPathWpf() {
		return __FILE__;
	}
}
if (!defined('S_YOUR_SECRET_HASH_' . getProPlugCodeWpf())) {
	define('S_YOUR_SECRET_HASH_' . getProPlugCodeWpf(), 'ng93#g3j9g#R#E)@KDPWKOK)Fkvvk#f30f#KF');
}

if (!function_exists('checkForPluginUpdatewoofilterPro')) {
	function checkForPluginUpdatewoofilterPro( $checkedData ) {
		if (class_exists('WpUpdaterWpf')) {
			return WpUpdaterWpf::getInstance( getProPlugDirWpf(), getProPlugFileWpf(), getProPlugCodeWpf(), getProPlugFullPathWpf() )->checkForPluginUpdate($checkedData);
		}
		return $checkedData;
	}
}
if (!function_exists('myPluginApiCallwoofilterPro')) {
	function myPluginApiCallwoofilterPro( $def, $action, $args ) {
		if (class_exists('WpUpdaterWpf')) {
			return WpUpdaterWpf::getInstance( getProPlugDirWpf(), getProPlugFileWpf(), getProPlugCodeWpf(), getProPlugFullPathWpf() )->myPluginApiCall($def, $action, $args);
		}
		return $def;
	}
}
/**
 * Check if there are base (free) version installed
 */
if (!function_exists('woofilterProActivateCallback')) {
	function woofilterProActivateCallback() {
		if (class_exists('DbUpdaterWpf')) {
			DbUpdaterWpf::runUpdate();
		}
		if (class_exists('FrameWpf')) {
			$arguments = array('extPlugName' => getProPlugDirWpf() . DS . getProPlugFileWpf());
			call_user_func_array(array('ModInstallerWpf', 'check'), $arguments);
		}
	}
}
if (!function_exists('woofilterProDeactivateCallback')) {
	function woofilterProDeactivateCallback() {
		if (class_exists('FrameWpf')) {
			call_user_func_array(array('ModInstallerWpf', 'deactivate'), array());
		}
	}
}
if (!function_exists('woofilterProUninstallCallback')) {
	function woofilterProUninstallCallback() {
		if (class_exists('FrameWpf')) {
			call_user_func_array(array('ModInstallerWpf', 'uninstall'), array());
		}
	}
}
add_action('admin_notices', 'woofilterProInstallBaseMsg');
if (!function_exists('woofilterProInstallBaseMsg')) {
	function woofilterProInstallBaseMsg() {
		if ( !get_option('wpf_full_installed') || !class_exists('FrameWpf') ) {
			$plugName = __('Product Filter by WBW', 'woo-product-filter');
			$plugWpUrl = 'https://wordpress.org/plugins/woo-product-filter/';
			echo '<div class="notice error is-dismissible"><p><strong>';
			/* translators: 1: plugin name 2: plugin url 3: plugin name */
			echo sprintf(esc_html__('Please install Free (Base) version of %1$s plugin, you can get it %2$s or use Wordpress plugins search functionality, activate it, then deactivate and activate again PRO version of %3$s. ', 'woo-product-filter'), 
				esc_html($plugName), '<a target="_blank" href="' . esc_url($plugWpUrl) . '">here</a>', esc_html($plugName));
			/* translators: %s: plugin name */
			echo sprintf(esc_html__('In this way you will have full and upgraded PRO version of %s.', 'woo-product-filter'), esc_html($plugName)) . 
				'</strong></p></div>';
		} else if (version_compare(WPF_VERSION, WPF_FREE_REQUIRES, '<')) {
			$plugName = __('Product Filter by WBW', 'woo-product-filter');
			$plugWpUrl = 'https://wordpress.org/plugins/woo-product-filter/';
			echo '<div class="notice error is-dismissible"><p><strong>';
			/* translators: 1: plugin name 2: plugin version 3: plugin url 4: plugin name */
			echo sprintf(esc_html__('Please install latest Free (Base) version of %1$s plugin (requires at least %2$s), you can get it %3$s or use Wordpress plugins search functionality, activate it, then deactivate and activate again PRO version of %4$s. ', 'woo-product-filter'), 
				esc_html($plugName), esc_html(WPF_FREE_REQUIRES), '<a target="_blank" href="' . esc_url($plugWpUrl) . '">here</a>', esc_html($plugName));
			/* translators: %s: plugin name */
			echo sprintf(esc_html__('In this way you will have full and upgraded PRO version of %s.', 'woo-product-filter'), esc_html($plugName)) .
				'</strong></p></div>';
		}
	}
}
