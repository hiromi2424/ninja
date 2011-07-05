<?php

App::uses('Router', 'Routing');

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

		$request = new CakeRequest;
		$this->Controller = new Controller($request);
		$this->Controller->uses = null;

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

		list($plugin, $componentName) = pluginSplit($component, true, null);
		if (isset($this->Controller->$componentName)) {
			return $this->Controller->$componentName;
		}

		$object = $this->Controller->Components->load($component, $settings);
		if (!$object || !is_object($object)) {
			return null;
		}

		if (method_exists($object, 'initialize')) {
			$object->initialize($this->Controller, $settings);
		}

		return $this->$componentName = $this->Controller->$componentName = $object;
	}

	public function loadRouter($base = null) {
		if ($base === null) {
			$base = Configure::read('App.base');
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