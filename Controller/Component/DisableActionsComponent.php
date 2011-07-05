<?php

class DisableActionsComponent extends Component {

	public $auto = true;
	public $configName = 'DisableActions';
	public $components = array('Security');
	public $disableActions = array();

	public $controller;

	public function initialize($controller) {

		$this->controller = $controller;
		$settings = $this->settings;
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
		return $controller === '*' || Inflector::camelize($controller) === Inflector::camelize($this->controller->name);
	}

	protected function _judgeAction($actions) {

		$actions = array_map('strtolower', (array)$actions);
		return $actions === array('*') || in_array(strtolower($this->controller->action), $actions);

	}

	public function blackHole() {
		$this->Security->blackHole($this->controller, 'disableActions');
	}

}