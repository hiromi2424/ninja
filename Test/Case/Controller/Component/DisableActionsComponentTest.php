<?php
/* AllowDeny Test cases generated on: 2011-05-22 14:43:09 : 1306042989*/

App::uses('DisableActionsComponent', 'Ninja.Controller/Component');
App::import('TestSuite', 'Ninja.NinjaComponentTestCase');

class DisableActionsTestController extends Controller {

	public $uses = null;

	public $blackHole = false;
	public $secureType = null;

	public function test_action() {}
	public function test_action2() {}

	public function blackHole($type) {
		$this->blackHole = true;
		$this->secureType = $type;
	}

}

class TestDisableActionsComponent extends DisableActionsComponent {

	public static $testConfigName = '__TestDisableActions';

	public function __construct(ComponentCollection $collection, $settings = array()) {
		$this->configName = self::$testConfigName;
		$args = func_get_args();
		call_user_func_array('parent::__construct', $args);
	}

	public function initialize(Controller $Controller) {
		$this->Security->blackHoleCallback = 'blackHole';
		$Controller->blackHole = false;
		$args = func_get_args();
		call_user_func_array('parent::initialize', $args);
	}

}

class DisableActionsComponentTest extends NinjaComponentTestCase {

	public $fixtures = false;

	protected $_configBackup;

	public function setUp() {
		parent::setUp();
		$this->_configBackup = Configure::read(TestDisableActionsComponent::$testConfigName);
	}

	public function tearDown() {
		Configure::delete(TestDisableActionsComponent::$testConfigName);
		if ($this->_configBackup !== false) {
			Configure::write(TestDisableActionsComponent::$testConfigName, $this->_configBackup);
		}
		parent::tearDown();
	}

	public function testInitialize() {
		Configure::write(TestDisableActionsComponent::$testConfigName, array(
			'*' => '*',
		));
		$this->DisableActions->initialize($this->Controller);
		$this->assertTrue($this->Controller->blackHole);
		$this->assertEquals('disableActions', $this->Controller->secureType);
	}

	public function testJudge() {
		$this->assertTrue($this->DisableActions->judge(array(
			'*' => '*',
		)));

		$this->assertTrue($this->DisableActions->judge(array(
			'*' => 'test_action',
		)));

		$this->assertFalse($this->DisableActions->judge(array(
			'*' => 'other_action',
		)));

		$this->assertTrue($this->DisableActions->judge(array(
			'disable_actions_test' => '*',
		)));

		$this->assertFalse($this->DisableActions->judge(array(
			'other_controller' => '*',
		)));

		$this->assertTrue($this->DisableActions->judge(array(
			'disable_actions_test' => 'test_action',
		)));

		$this->assertFalse($this->DisableActions->judge(array(
			'disable_actions_test' => 'test_action2',
		)));


		$this->assertTrue($this->DisableActions->judge(array(
			'*' => 'other_actions',
			'disable_actions_test' => '*',
		)));

		$this->assertTrue($this->DisableActions->judge(array(
			'disable_actions_test' => 'other_actions',
			'*' => '*',
		)));
	}

}
