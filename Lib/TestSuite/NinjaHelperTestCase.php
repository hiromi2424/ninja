<?php

App::uses('NinjaTestCase', 'Ninja.TestSuite');
App::uses('View', 'View');
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('Helper', 'View');
App::uses('AppHelper', 'View/Helper');

abstract class NinjaHelperTestCase extends NinjaTestCase {

	public $helperClass;
	public $helperName;

	public $view;

	public function setUp() {
		$this->helperClass = preg_replace('/TestCase$/', '', get_class($this));
		$this->helperName = preg_replace('/Helper$/', '', $this->helperClass);
		$this->view = new View(new Controller(new CakeRequest));

		parent::setUp();
	}

	public function loadHelper($options = array()) {
		if ($this->view->Helpers->attached($this->helperName)) {
			$this->view->Helpers->unload($this->helperName);
		}
		return $this->{$this->helperName} = $this->view->loadHelper($this->plugin . $this->helperName, $options);
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