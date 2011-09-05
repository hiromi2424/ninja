<?php

class DisableActionsComponent extends Object {

	public $auto = true;
	public $configName = 'DisableActions';
	public $components = array('Security');
	public $disableActions = array();

	protected $_Controller;

	public function initialize($Controller, $settings = array()) {

		$this->_Controller = $Controller;

		$this->_set($settings);
		unset($settings['auto']);
		unset($settings['configName']);

		$disableActions = Configure::read($this->configName);

		if (!is_array($disableActions)) {
			$disableActions = array();
		}

		$this->disableActions = Set::merge($disableActions, $settings);

		if ($this->auto && $this->judge($this->disableActions)) {
			$this->blackHole();
		}

	}

	public function judge($disableActions) {

		foreach ($disableActions as $controller => $actions) {
			if ($this->_judgeController($controller) && $this->_judgeAction($actions)) {
				return true;
			}
		}

		return false;

	}

	protected function _judgeController($controller) {
		return $controller === '*' || Inflector::camelize($controller) === Inflector::camelize($this->_Controller->name);
	}

	protected function _judgeAction($actions) {

		$actions = array_map('strtolower', (array)$actions);
		return $actions === array('*') || in_array(strtolower($this->_Controller->action), $actions);

	}

	public function blackHole() {
		$this->Security->blackHole($this->_Controller, 'disableActions');
	}

}