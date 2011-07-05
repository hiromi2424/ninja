<?php

App::uses('NinjaControllerTestCase', 'Ninja.TestSuite');
App::uses('Component', 'Controller');

abstract class NinjaComponentTestCase extends NinjaControllerTestCase {

	public $Controller;

	public $componentClass;
	public $componentName;
	public $testComponent;

	public function setUp() {
		$this->componentClass = preg_replace('/TestCase$/', '', get_class($this));
		$this->componentName = preg_replace('/Component$/', '', $this->componentClass);

		if (class_exists('Test' . $this->componentClass)) {
			$this->testComponent = 'Test' . $this->componentName;
		} elseif (class_exists('Mock' . $this->componentClass)) {
			$this->testComponent = 'Mock' . $this->componentName;
		}
	}

	public function startTest($method = null) {
		parent::startTest($method);
		$this->loadComponent();
	}

	public function endTest($method = null) {
		unset($this->{$this->componentName});
		parent::endTest($method);
	}

	protected function _guessControllerClass($params) {
		$name = 'Test';
		if (!empty($params['controller'])) {
			$name = Inflector::camelize($params['controller']);
		}

		$class = $this->componentName . $name . 'Controller';

		return $class;
	}

/**
 * Loads a Component
 *
 * @param string $component Name of component to load
 * @return Object loaded component
 **/
	public function loadComponent($settings = array()) {
		if (!isset($this->Controller)) {
			$this->loadController();
		}

		$componentToLoad = $this->testComponent ? $this->testComponent : $this->plugin . $this->componentName;
		$component = $this->Controller->Components->load($componentToLoad, $settings);
		$this->{$this->componentName} = $this->Controller->{$this->componentName} = $component;

		if (method_exists($component, 'initialize')) {
			$component->initialize($this->Controller);
		}
		return $this->{$this->componentName};
	}

}