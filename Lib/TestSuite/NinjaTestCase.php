<?php

App::uses('AppTestCase', 'TestSuite');
if (!class_exists('AppTestCase')) {
	App::uses('AppTestCase', 'Ninja.TestSuite');
}

abstract class NinjaTestCase extends AppTestCase {

	public $plugin;

	public $runOnly = null;
	public $excludeCase = null;

	public function __construct() {
		if (!empty($_GET['plugin'])) {
			$this->plugin = Inflector::humanize($_GET['plugin']) . '.';
		}
	}

	public function _testFile($name) {
		return TESTS . 'files' . DS . $name;
	}

	public function startTest($method) {
		if (!$this->_isTest($method)) {
			$this->markTestSkipped();
		}

		parent::startTest($method);
	}

	protected function _isTest($method) {
		if ($this->runOnly !== null) {
			$candidates = (array)$this->runOnly;
		}

		if ($this->excludeCase !== null) {
			$excludes = (array)$this->excludeCase;
		}

		switch (true) {
			case isset($candidates, $excludes):
				return in_array($method, array_diff($candidates, $excludes));
			case isset($candidates):
				return in_array($method, $candidates);
			case isset($excludes):
				return !in_array($method, $excludes);
		}

		return true;
	}

	public function setUp() {
		$this->_determineClassName();
		$this->_instantiate();

		parent::setUp();
	}

	protected function _determineClassName() {
	}

	protected function _instantiate() {
	}

}