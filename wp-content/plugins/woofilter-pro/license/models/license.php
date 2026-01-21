<?php
class LicenseModelWpf extends ModelWpf {
	private $_apiUrl = '';
	public function __construct() {
		$this->_initApiUrl();
	}
	public function check() {
		$time = time();
		$lastCheck = (int) get_option('_last_important_check_' . WPF_CODE);
		if (!$lastCheck || ( $time - $lastCheck ) >= 5 * 24 * 3600) {
			$resData = $this->_req('check', array_merge(array(
				'url' => WPF_SITE_URL,
				'plugin_code' => $this->_getPluginCode(),
			), $this->getCredentials()));
			$resData = DispatcherWpf::applyFilters( 'getPluginLicenseData', $resData, 'check' );
			if ($resData) {
				$this->_updateLicenseData( $resData['data']['save_data'] );
			} else {
				$this->_setExpired();
			}
			update_option('_last_important_check_' . WPF_CODE, $time);
			update_option('_last_check_errors_' . WPF_CODE, $this->haveErrors() ? $this->getErrors() : '');
		} else {
			$daysLeft = (int) FrameWpf::_()->getModule('options')->getModel()->get('license_days_left');
			if ($daysLeft) {
				$lastServerCheck = (int) FrameWpf::_()->getModule('options')->getModel()->get('license_last_check');
				$day = 24 * 3600;
				$daysPassed = floor(( $time - $lastServerCheck ) / $day);
				if ($daysPassed > 0) {
					$daysLeft -= $daysPassed;
					FrameWpf::_()->getModule('options')->getModel()->save('license_days_left', $daysLeft);
					FrameWpf::_()->getModule('options')->getModel()->save('license_last_check', time());
					if ($daysLeft < 0) {
						$this->_setExpired();
					}
				}
			}
		}
		return true;
	}
	public function activate( $d = array() ) {
		$d['email'] = isset($d['email']) ? trim($d['email']) : '';
		$d['key'] = isset($d['key']) ? trim($d['key']) : '';
		$d['type'] = isset($d['type']) ? trim($d['type']) : '';
		$d['name'] = isset($d['name']) ? trim($d['name']) : '';
		if (!empty($d['email'])) {
			if (!empty($d['key'])) {
				$this->setCredentials($d['email'], $d['key'], $d['type'], $d['name']);
				$resData = $this->_req('activate', array_merge(array(
					'url' => WPF_SITE_URL,
					'plugin_code' => $this->_getPluginCode(),
				), $this->getCredentials()));
				$resData = DispatcherWpf::applyFilters( 'getPluginLicenseData', $resData, 'activate' );

				if (false != $resData) {
					if ($this->_updateLicenseData( $resData['data']['save_data'] )) {
						$this->_setActive();
						update_option('_last_check_errors_' . WPF_CODE, '');
						return true;
					}
				}
			} else {
				$this->pushError(esc_html__('Please enter your License Key', 'woo-product-filter'), 'key');
			}
		} else {
			$this->pushError(esc_html__('Please enter your Email address', 'woo-product-filter'), 'email');
		}
		$this->_removeActive();
		update_option('_last_check_errors_' . WPF_CODE, $this->haveErrors() ? $this->getErrors() : '');
		return false;
	}
	private function _updateLicenseData( $saveData ) {
		if (!isset($saveData['days_left'])) {
			if (!isset($saveData['license_save_name']) || $saveData['license_save_name'] !== $this->getEmail()
				|| !isset($saveData['license_save_val']) || $saveData['license_save_val'] !== $this->getLicenseKey()) {
				$this->pushError(esc_html__('There was a problem sending the request to our authentication server.', 'woo-product-filter'), 'key');
				return false;
			}
			$saveData['days_left'] = 300;
		}
		FrameWpf::_()->getModule('options')->getModel()->save('license_save_name', $saveData['license_save_name']);
		FrameWpf::_()->getModule('options')->getModel()->save('license_save_val', $saveData['license_save_val']);
		FrameWpf::_()->getModule('options')->getModel()->save('license_days_left', $saveData['days_left']);
		FrameWpf::_()->getModule('options')->getModel()->save('license_last_check', time());
		if (isset($saveData['license_type'])) {
			$this->setLicenseType($saveData['license_type']);
		}
		$this->updateDbTables(true);
		if (isset($saveData['add_data']) && !empty($saveData['add_data'])) {
			$this->_processUpdateDbData( $saveData['add_data'] );
		}
		return true;
	}
	private function _processUpdateDbData( $addData ) {
		$actionData = explode('=>', trim($addData));
		switch ($actionData[ 0 ]) {
			case 'db_install':	// Only database install for now
				$tblsData = explode('|', $actionData[ 1 ]);
				$cntData = count( $tblsData );
				for ($i = 0; $i < $cntData; $i += 2) {
					$tbl = '@__' . $tblsData[ $i ];
					$data = UtilsWpf::unserialize( base64_decode($tblsData[$i + 1]) );
					foreach ($data as $uid => $d) {
						InstallerWpf::installDataByUid($tbl, $uid, $d);
					}
				}
				break;
		}
	}
	private function _setExpired() {
		update_option('_last_expire_' . WPF_CODE, 1);
		$this->_removeActive();
		if ($this->enbOptimization()) {
			$this->updateDbTables(false);
		}
	}
	public function isExpired() {
		return (int) get_option('_last_expire_' . WPF_CODE);
	}
	public function isExpiredWC( $data ) {
		$isExpired = !isset($data['expired']) || $data['expired'];
		if ($isExpired) {
			update_option('_last_expire_wc_' . WPF_CODE, 1);
			$this->_setExpired();
		}
		return $isExpired;
	}
	public function isActive() {
		$option = get_option(FrameWpf::_()->getModule('options')->get('license_save_name'));
		$license = FrameWpf::_()->getModule('options')->get('license_save_val');
		return ( $option && $option == $license );
	}
	public function _setActive() {
		update_option('_site_transient_update_plugins', ''); // Trigger plugins updates check
		update_option(FrameWpf::_()->getModule('options')->get('license_save_name'), FrameWpf::_()->getModule('options')->get('license_save_val'));
		delete_option('_last_expire_' . WPF_CODE);
	}
	public function _removeActive() {
		$name = FrameWpf::_()->getModule('options')->get('license_save_name');
		if (!empty($name)) {
			delete_option($name);
		}
	}
	public function setCredentials( $email, $key, $type = '', $name = '' ) {
		$this->setLicenseType($type);
		$this->setEmail($email);
		$this->setLicenseKey($key);
		$this->setLicenseName($name);
	}
	public function setLicenseType( $type ) {
		FrameWpf::_()->getModule('options')->getModel()->save('license_type', $type);
	}
	public function setEmail( $email ) {
		FrameWpf::_()->getModule('options')->getModel()->save('license_email', base64_encode( $email ));
	}
	public function setLicenseKey( $key ) {
		FrameWpf::_()->getModule('options')->getModel()->save('license_key', base64_encode( $key ));
	}
	public function setLicenseName( $name ) {
		FrameWpf::_()->getModule('options')->getModel()->save('license_name', base64_encode( $name ));
	}
	public function getLicenseType( $full = true ) {
		$type = FrameWpf::_()->getModule('options')->get('license_type');
		return false === $type ? '' : ( $full ? $type : substr($type, 0, 2) );
	}
	public function getEmail() {
		return base64_decode( FrameWpf::_()->getModule('options')->get('license_email') );
	}
	public function getLicenseKey() {
		return base64_decode( FrameWpf::_()->getModule('options')->get('license_key') );
	}
	public function getLicenseName() {
		return base64_decode( FrameWpf::_()->getModule('options')->get('license_name') );
	}
	public function getCredentials() {
		return array(
			'type' => $this->getLicenseType(),
			'email' => $this->getEmail(),
			'key' => $this->getLicenseKey(),
			'name' => $this->getLicenseName(),
		);
	}
	private function _req( $action, $data = array() ) {
		add_filter( 'http_api_curl', array($this->getModule(), 'licenseHttpRequestTimeout'), 100, 1 );
		
		$data = array_merge($data, array(
			'mod' => 'manager',
			'pl' => 'lms',
			'action' => $action,
		));

		if ( isset( $data['site2'] ) ) {
			$this->_apiUrl = $data['site2'];
		}

		$response = wp_remote_post($this->_apiUrl, array(
			'body' => $data,
			'timeout' => 30,
		));

		remove_filter( 'http_api_curl', array($this->getModule(), 'licenseHttpRequestTimeout') );
		if (!is_wp_error($response)) {
			$resArr = UtilsWpf::jsonDecode($response['body']);
			if ( isset($response['body']) && !empty($response['body']) && $resArr ) {
				if (!$resArr['error']) {
					return $resArr;
				} else {
					if ( ! isset( $data['site2'] ) && in_array( 'Can not detect your license. Make sure you entered correct data in admin area...', $resArr['errors'] ) ) {
						$data['site2'] = 'https://woobwp.com/';

						return $this->_req( $action, $data );
					}

					$this->pushError($resArr['errors']);
				}
			} else {
				$this->pushError(esc_html__('There was a problem with sending request to our autentification server. Please try latter.', 'woo-product-filter'));
			}
		} else {
			$this->pushError( $response->get_error_message() );
		}
		return false;
	}
	private function _initApiUrl() {
		if (empty($this->_apiUrl)) {
			// TODO: Replace this back to production
			$this->_apiUrl = 'https://woobewoo.com/';
		}
	}
	public function enbOptimization() {
		return false;
	}
	public function checkPreDeactivateNotify() {
		$daysLeft = (int) FrameWpf::_()->getModule('options')->getModel()->get('license_days_left');
		if ( $daysLeft > 0 && $daysLeft <= 3 ) {	// Notify before 3 days
			add_action('admin_notices', array($this, 'showPreDeactivationNotify'));
		}
	}
	public function showPreDeactivationNotify() {
		$daysLeft = (int) FrameWpf::_()->getModule('options')->getModel()->get('license_days_left');
		$msg = '';
		if (0 == $daysLeft) {
			/* translators: %s: plugin name */
			$msg = esc_html(sprintf(__('License for plugin %s will expire today.', 'woo-product-filter'), WPF_WP_PLUGIN_NAME));
		} elseif (1 == $daysLeft) {
			/* translators: %s: plugin name */
			$msg = esc_html(sprintf(__('License for plugin %s will expire tomorrow.', 'woo-product-filter'), WPF_WP_PLUGIN_NAME));
		} else {
			/* translators: %1: plugin name 2: count days */
			$msg = esc_html(sprintf(__('License for plugin %1$s will expire in %2$d days.', 'woo-product-filter'), WPF_WP_PLUGIN_NAME, $daysLeft));
		}
		echo '<div class="notice error is-dismissible">' . esc_html($msg) . '</div>';
	}
	public function updateDb() {
		if (!$this->enbOptimization()) {
			return;
		}
		$time = time();
		$lastCheck = (int) get_option('_last_wp_check_imp_' . WPF_CODE);
		if (!$lastCheck || ( $time - $lastCheck ) >= 5 * 24 * 3600) {
			$this->updateDbTables($this->isActive());
			update_option('_last_wp_check_imp_' . WPF_CODE, $time);
		}
	}
	public function updateDbTables( $activate ) {
		$active = ( $activate ? 1 : 0 );
		if (function_exists('is_multisite') && is_multisite()) {
			global $wpdb;
			$blog_id = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
			foreach ($blog_id as $id) {
				if (switch_to_blog($id)) {
					DbWpf::query('UPDATE @__modules SET active = ' . $active . ' WHERE ex_plug_dir IS NOT NULL AND ex_plug_dir != "" AND code != "license"');
					restore_current_blog();
				}
			}
		} else {
			DbWpf::query('UPDATE @__modules SET active = ' . $active . ' WHERE ex_plug_dir IS NOT NULL AND ex_plug_dir != "" AND code != "license"');
		}
	}
	private function _getPluginCode() {
		return 'woofilter_pro';
	}
	public function getExtendUrl() {
		$license = $this->getCredentials();
		$license['key'] = md5($license['key']);
		$license = urlencode(base64_encode(implode('|', $license)));
		return $this->_apiUrl . '?mod=manager&pl=lms&action=extend&plugin_code=' . $this->_getPluginCode() . '&lic=' . $license;
	}
}
