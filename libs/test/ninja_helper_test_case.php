<?php

App::import('Lib', 'Ninja.test' . DS . 'NinjaTestCase');

abstract class NinjaHelperTestCase extends NinjaTestCase {

	public $helperClass;
	public $helperName;

	public function startCase() {
		$this->helperClass = preg_replace('/TestCase$/', '', get_class($this));
		$this->helperName = preg_replace('/Helper$/', '', $this->helperClass);

		if (!class_exists($this->helperClass)) {
			App::import('Helper', $this->plugin . $this->helperName);
		}
		parent::setUp();
	}

	public function loadHelper($options = array()) {
		return $this->{$this->helperName} = new $this->helperClass($options);
	}

	public function startTest($method = null) {
		parent::startTest($method);
		$this->loadHelper();
	}

	public function endTest($method = null) {
		unset($this->{$this->helperName});
		ClassRegistry::flush();

		parent::endTest($method);
	}
}