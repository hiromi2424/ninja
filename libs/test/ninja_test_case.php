<?php

App::import('Lib', 'Ninja.test' . DS . 'FixtureLoaderTestCase');
App::import('Lib', 'Ninja.test' . DS . 'NinjaTestFixture');

abstract class NinjaTestCase extends FixtureLoaderTestCase {

	public $plugin;

	public function __construct() {
		if (!empty($_GET['plugin'])) {
			$this->plugin = Inflector::humanize($_GET['plugin']) . '.';
		}
	}

	public function _testFile($name) {
		return TESTS . 'files' . DS . $name;
	}

}