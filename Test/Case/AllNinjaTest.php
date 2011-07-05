<?php

require_once dirname(__FILE__) . DS . 'NinjaGroupTestCase.php';

/**
 * Group Test for Ninja
 */

class AllNinjaTest extends NinjaGroupTestCase {

	public $plugin = 'Ninja';

/**
 *
 *
 * @return PHPUnit_Framework_TestSuite the instance of PHPUnit_Framework_TestSuite
 */
	public static function suite() {
		$suite = new self;
		$files = $suite->getTestFiles();
		$suite->addTestFiles($files);

		return $suite;
	}
}