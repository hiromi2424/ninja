<?php

App::import('Lib', 'Ninja.test' . DS . 'FixtureLoaderTestCase');
App::import('Lib', 'Ninja.test' . DS . 'NinjaTestFixture');

abstract class NinjaTestCase extends FixtureLoaderTestCase {

	public function _testFile($name) {
		return TESTS . 'files' . DS . $name;
	}

}