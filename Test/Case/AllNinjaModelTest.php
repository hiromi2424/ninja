<?php

require_once dirname(__FILE__) . DS . 'NinjaGroupTest.php';

/**
 * Group Test for Models of Ninja
 */

class AllNinjaModelTest extends NinjaGroupTest {

	public $plugin = 'Ninja';

/**
 *
 *
 * @return PHPUnit_Framework_TestSuite the instance of PHPUnit_Framework_TestSuite
 */
	public static function suite() {
		$suite = new self;
		$files = $suite->getTestFiles('Model', 'Model' . DS . 'Behavior');
		$suite->addTestFiles($files);

		return $suite;
	}
}