<?php
class StatisticsControllerWpf extends ControllerWpf {
	public function enableStats() {
		$res      = new ResponseWpf();
		$id   = ReqWpf::getVar('id');
		if ($this->getModel()->enableStatistics($id, 1)) {
			$res->addMessage(esc_html__('Done', 'woo-product-tables'));
		} else {
			$res->pushError($this->getModel()->getErrors());
		}
		$res->ajaxExec(true);
	}
	public function disableStats() {
		$res      = new ResponseWpf();
		$id   = ReqWpf::getVar('id');
		if ($this->getModel()->enableStatistics($id, 0)) {
			$res->addMessage(esc_html__('Done', 'woo-product-tables'));
		} else {
			$res->pushError($this->getModel()->getErrors());
		}
		$res->ajaxExec(true);
	}
	public function saveStatistics() {
		$res      = new ResponseWpf();
		$statistics = ReqWpf::getVar('statistics');
		$statistics = UtilsWpf::jsonDecode(stripslashes($statistics));
		
		if ($this->getModel()->saveStatistics($statistics)) {
			$res->addMessage(esc_html__('Done', 'woo-product-tables'));
		} else {
			$res->pushError($this->getModel()->getErrors());
		}
		$res->ajaxExec(true);
	}
	public function getDiagramData() {
		$res = new ResponseWpf();
		$res->ignoreShellData();

		$params = ReqWpf::get('post');
		$result = $this->getModel()->getDiagramData($params);

		if ($result) {
			$res->values = $result;
		} else {
			$res->pushError($this->getModel()->getErrors());
		}
		return $res->ajaxExec();
	}
	public function getTableData() {
		$res = new ResponseWpf();
		$res->ignoreShellData();

		$params = array();
		parse_str(ReqWpf::getVar('params'), $params);
		if (!is_array($params)) {
			$params = array();
		}
		$params['order'] = ReqWpf::getVar('sidx');
		$params['dir'] = ReqWpf::getVar('sord');
		//$params = ReqWpf::get('post');
		$result = $this->getModel()->getTableData($params);

		if ($result) {
			$res->addData('page', 1);
			$res->addData('total', $result['total']);
			$res->addData('rows', $result['rows']);
			$res->addData('records', $result['total']);
		} else {
			$res->pushError($this->getModel()->getErrors());
		}
		return $res->ajaxExec();
	}
}
