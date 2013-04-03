<?php

require_once dirname(__FILE__) . DS . 'NinjaGroupTest.php';

/**
 * Group Test for Libs of Ninja
 */

class AllNinjaLibTest extends NinjaGroupTest {

	public $plugin = 'Ninja';

/**
 *
 *
 * @return PHPUnit_Framework_TestSuite the instance of PHPUnit_Framework_TestSuite
 */
	public static function suite() {
		$suite = new self;
		$files = $suite->getTestFiles('Lib');
		$suite->addTestFiles($files);

		return $suite;
	}
}