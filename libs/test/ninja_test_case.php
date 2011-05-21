<?php

App::import('Lib', 'Ninja.test' . DS . 'FixtureLoaderTestCase');

abstract class NinjaTestCase extends FixtureLoaderTestCase {

	public function _testFile($name) {
		return TESTS . 'files' . DS . $name;
	}

}