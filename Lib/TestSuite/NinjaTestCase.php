<?php

App::import('Lib', 'Ninja.test' . DS . 'FixtureLoaderTestCase');
App::import('Lib', 'Ninja.test' . DS . 'NinjaTestFixture');

if (!App::import('Lib', 'test' . DS . 'AppTestCase')) {
	App::import('Lib', 'Ninja.test' . DS . 'AppTestCase');
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

	function _isTest($method) {
		if ($isTest = parent::_isTest($method)) {
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
		}

		return $isTest;
	}

}