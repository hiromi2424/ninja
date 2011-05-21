<?php

App::import('Lib', 'Ninja.test/FixtureLoaderTestCase');

abstract class NinjaTestCase extends FixtureLoaderTestCase {

	public function _testFile($name) {
		return TESTS . 'files' . DS . $name;
	}

}