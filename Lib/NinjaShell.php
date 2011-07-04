<?php

abstract class NinjaShell extends Shell {

	public $Controller;

/**
 * Loads a Controller in order to use features what Controller has.
 *
 * @return Controller loaded controller object
 **/
	public function loadController() {
		if (isset($this->Controller)) {
			return $this->Controller;
		}

		if (!class_exists('Controller')) {
			App::import('Controller', 'Controller', false);
		}
		$this->Controller = new Controller();
		$this->Controller->uses = array();

		$this->Controller->constructClasses();
		$this->Controller->startupProcess();

		return $this->Controller;
	}



/**
* Loads a Component
 *
 * @param string $component Name of component to load
 * @return Object loaded component
 **/
	public function loadComponent($component, $settings = array()) {
		if (!isset($this->Controller)) {
			$this->loadController();
		}

		list($plugin, $component) = pluginSplit($component, true, null);
		if (isset($this->Controller->$component)) {
			return $this->Controller->$component;
		}

		$componentClass = $component . 'Component';
		if (!class_exists($componentClass)) {
			App::import('Component', $plugin . $component);
		}

		$object = new $componentClass(null);

		if (method_exists($object, 'initialize')) {
			$object->initialize($this->Controller, $settings);
		}

		if (isset($object->components) && is_array($object->components)) {
			$components = (array)Set::normalize($object->components);
			foreach (array_keys($components) as $c) {
				$this->Controller->Component->_loadComponents($object, $c);
			}

			foreach ($components as $c => $config) {
				list($plugin, $c) = pluginSplit($c, true, null);

				if (method_exists($object->{$c}, 'initialize')) {
					$object->{$c}->initialize($this->Controller, $config);
				}
				if (method_exists($object->{$c}, 'startup')) {
					$object->{$c}->startup($this->Controller);
				}
			}
		}

		$this->$component = $this->Controller->$component = $object;
		return $this->$component;
	}

	public function loadRouter($base = null) {
		if ($base === null) {
			$base = Configure::read('App.base');
		}

		if (!class_exists('Router')) {
			App::import('Core', 'Router');
		}

		Router::setRequestInfo(array(
			array(),
			array(
				'base' => $base,
				'webroot' => $base,
			)
		));

		if (!defined('FULL_BASE_URL')) {
			$s = null;
			if (env('HTTPS')) {
				$s ='s';
			}

			$httpHost = env('HTTP_HOST');

			if (empty($httpHost)) {
				$httpHost = env('SERVER_NAME');
				if (empty($httpHost)) {
					if (class_exists('Environment')) {
						$httpHost = Environment::getHostName();
					}
				}
			}

			if (!empty($httpHost)) {
				define('FULL_BASE_URL', 'http' . $s . '://' . $httpHost);
			}
		}

	}

}