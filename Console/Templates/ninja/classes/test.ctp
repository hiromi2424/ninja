<?php
/**
 * Test Case bake template
 *
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.console.libs.templates.objects
 * @since         CakePHP(tm) v 1.3
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

$baseTestCaseExists = in_array($type, array('Controller', 'Component', 'Model', 'Behavior', 'Helper'));
$testClass = 'Ninja' . ($baseTestCaseExists ? $type : '') . 'TestCase';

echo "<?php\n";
?>
<?php if (!$baseTestCaseExists): ?>App::uses('<?php echo $className; ?>', '<?php echo $plugin . $type; ?>');<?php endif; ?>

App::uses('<?php echo $testClass; ?>', 'Ninja.TestSuite');

<?php if ($mock and strtolower($type) == 'controller'): ?>
class Test<?php echo $fullClassName; ?> extends <?php echo $fullClassName; ?> {
	public $autoRender = false;

	public function redirect($url, $status = null, $exit = true) {
		$this->redirectUrl = $url;
	}
}

<?php endif; ?>
/**
 * <?php echo $fullClassName; ?> Test Case
 *
 */
class <?php echo $fullClassName; ?>Test extends <?php echo $testClass; ?> {
<?php if (!empty($fixtures)): ?>

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
<?php
	foreach ($fixtures as $f) {
		echo "\t\t'$f',", PHP_EOL;
	}
?>
	);
<?php endif; ?>
<?php if (!$baseTestCaseExists && !empty($construction)): ?>

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		$this-><?php echo $className . ' = ' . $construction; ?>

		parent::setUp();
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this-><?php echo $className; ?>);
		ClassRegistry::flush();

		parent::tearDown();
	}
<?php endif; ?>
<?php foreach ($methods as $method): ?>

	public function test<?php echo Inflector::classify($method); ?>() {

	}
<?php endforeach; ?>
}
