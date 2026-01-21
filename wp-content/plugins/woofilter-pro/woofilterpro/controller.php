<?php
class WoofilterProControllerWpf extends ControllerWpf {
	public function autocompliteSearchText() {
		$res = new ResponseWpf();
		$keyword = ReqWpf::getVar('keyword');
		$filterId = ReqWpf::getVar('filterId');

		$autocomplete =
			FrameWpf::_()->
			getModule('woofilterpro')->
			getModel('autocomplete')->
			init($keyword, $filterId);

		$autocompleteData = $autocomplete->getData();

		$res->addData('autocompleteData', $autocompleteData);
		return $res->ajaxExec();
	}

	public function applyLoader() {
		$res = new ResponseWpf();
		$id = ReqWpf::getVar('id');
		$settings = ReqWpf::getVar('settings');

		$model = FrameWpf::_()->getModule('woofilters')->getModel('woofilters');
		$filters = $model->getFromTbl();
		$cnt = 0;
		foreach ($filters as $filter) {
			$current = unserialize($filter['setting_data']);
			if ( $filter['id'] != $id && isset($current['settings']) ) {
				$filter['settings'] = array_merge($current['settings'], $settings);
				unset($filter['setting_data']);
				$model->save($filter);
				$cnt++;
			}
		}
		$res->addData('message', $cnt . esc_html__(' filters have been changed', 'woo-product-filter'));
		return $res->ajaxExec();
	}

	public function exportGroup() {
		check_ajax_referer('wpf-save-nonce', 'wpfNonce');
		if (!current_user_can('manage_options')) {
			wp_die();
		}
		$res  = new ResponseWpf();
		$data = FrameWpf::_()->getModule('woofilterpro')->exportGroup(ReqWpf::getVar('listIds', 'post'));
		if ($data) {
			$res->addData('tables', $data);
			$res->addMessage(esc_html__('Done', 'woo-product-tables'));
		} else {
			$res->pushError($this->getModel('woofilters')->getErrors());
		}
		$res->ajaxExec();
	}

	public function importGroup() {
		if (!function_exists('check_ajax_referer')) {
			FrameWpf::_()->loadPlugins();
		}
		check_ajax_referer('wpf-save-nonce', 'wpfNonce');
		if (!current_user_can('manage_options')) {
			wp_die();
		}
		$res      = new ResponseWpf();
		$tables   = ReqWpf::getVar('import_file', 'files');
		$tables   = isset($tables['tmp_name']) ? file_get_contents($tables['tmp_name']) : '';
		$imported = false;
		if ($tables) {
			$imported = DbWpf::query($tables);
		}
		if ($imported) {
			$res->addData('tables', $tables);
			$res->addMessage(esc_html__('Done', 'woo-product-tables'));
		} else {
			$res->pushError($this->getModel()->getErrors());
		}

		$res->ajaxExec(true);
	}
}
