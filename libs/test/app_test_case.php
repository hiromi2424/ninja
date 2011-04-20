<?php

App::import('Lib', 'Ninja.test/FixtureLoaderTestCase');

abstract class AppTestCase extends FixtureLoaderTestCase {

	public function _testFile($name) {
		return TESTS . 'files' . DS . $name;
	}

}