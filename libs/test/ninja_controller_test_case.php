<?php

App::import('Lib', 'Ninja.test' . DS . 'NinjaTestCase');

abstract class NinjaControllerTestCase extends NinjaTestCase {

	public $Controller;

	public $controllerClass;
	public $controllerName;

	public function startCase() {
		$this->controllerClass = preg_replace('/TestCase$/', '', get_class($this));
		$this->controllerName = preg_replace('/Controller$/', '', $this->controllerClass);

		if (!class_exists($this->controllerClass)) {
			App::import('Controller', $this->plugin . $this->controllerName);
		}
	}

	public function startTest($method = null) {
		parent::startTest($method);
		$this->loadController();
	}

	public function endTest($method = null) {
		$this->shutdownController();
		ClassRegistry::flush();

		parent::endTest($method);
	}

/**
 * Loads a Controller in order to use features what Controller has.
 *
 * @return Controller loaded controller object
 **/
	public function loadController($params = array()) {
		if (!class_exists('Controller')) {
			App::import('Controller', 'Controller', false);
		}

		if ($this->Controller !== null) {
			$this->shutdownController();
		}

		$controllerClass = $this->_guessControllerClass($params);
		if (!class_exists($controllerClass)) {
			$controllerClass = 'Controller';
		}

		$Controller = new $controllerClass();
		$Controller->params = array_merge(array(
			'controller' => $this->_guessControllerName($Controller, $params),
			'action' => 'test_action',
		), $params);
		$Controller->action = $Controller->params['action'];

		if ($controllerClass === 'Controller') {
			$Controller->uses = null;
		}

		$Controller->constructClasses();
		$Controller->Component->initialize($Controller);
		$Controller->beforeFilter();

		return $this->Controller = $Controller;
	}

	protected function _guessControllerClass($params) {
		$class = $this->controllerName . 'Controller';
		if (class_exists('Mock' . $class)) {
			$class = 'Mock' . $class;
		}

		return $class;
	}

	protected function _guessControllerName($Controller, $params) {
		$name = $Controller->name;
		if (!empty($params['controller'])) {
			$name = $params['controller'];
		}

		return Inflector::underscore($name);
	}

	public function shutdownController() {
		$this->Controller->Component->shutdown($this->Controller);
		$this->Controller = null;
	}

}