<?php

App::import('Lib', 'Ninja.test' . DS . 'NinjaControllerTestCase');

abstract class NinjaComponentTestCase extends NinjaControllerTestCase {

	public $Controller;

	public $componentClass;
	public $componentName;

	public function startCase() {
		$this->componentClass = preg_replace('/TestCase$/', '', get_class($this));
		$this->componentName = preg_replace('/Component$/', '', $this->componentClass);

		if (!class_exists($this->componentClass)) {
			App::import('Component', $this->plugin . $this->componentName);
		}

		if (class_exists('Test' . $this->componentClass)) {
			$this->componentClass = 'Test' . $this->componentClass;
		} elseif (class_exists('Mock' . $this->componentClass)) {
			$this->componentClass = 'Mock' . $this->componentClass;
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

		$component = new $this->componentClass(null);
		if (method_exists($component, 'initialize')) {
			$component->initialize($this->Controller, $settings);
		}

		if (isset($component->components) && is_array($component->components)) {
			$components = (array)Set::normalize($component->components);
			foreach (array_keys($components) as $c) {
				$this->Controller->Component->_loadComponents($component, $c);
			}

			foreach ($components as $c => $config) {
				list($plugin, $c) = pluginSplit($c, true, null);

				if (method_exists($component->{$c}, 'initialize')) {
					$component->{$c}->initialize($this->Controller, $config);
				}
				if (method_exists($component->{$c}, 'startup')) {
					$component->{$c}->startup($this->Controller);
				}
			}
		}

		$this->{$this->componentName} = $this->Controller->{$this->componentName} = $component;
		return $this->{$this->componentName};
	}

}