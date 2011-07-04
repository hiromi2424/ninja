<?php
/* AllowDeny Test cases generated on: 2011-05-22 14:43:09 : 1306042989*/

App::import('Lib', 'Ninja.test' . DS . 'NinjaComponentTestCase');

class AllowDenyTestController extends Controller {

	public $components = array(
		'Auth',
	);

	public $uses = null;

	public $loginRequired = array();
	public $allowedActions = array();

	// disable directing for test
	public function redirect(){
	}

	public function test_action() {}
	public function test_action2() {}

}

class AllowDenyComponentTestCase extends NinjaComponentTestCase {

	protected function _allowed($action) {
		$allowedActions = array_map('strtolower', $this->Controller->Auth->allowedActions);
		$isAllowed = (
			$this->Controller->Auth->allowedActions == array('*') ||
			in_array($action, $allowedActions)
		);
		return $isAllowed;
	}

	public function testInitialize() {
		$this->AllowDeny->initialize($this->Controller);
		$this->assertTrue($this->_allowed('test_action'));
		$this->assertTrue($this->_allowed('test_action2'));

		$this->loadController(array('prefix' => 'test'));
		$this->AllowDeny->initialize($this->Controller);
		$this->assertFalse($this->_allowed('test_action'));
		$this->assertFalse($this->_allowed('test_action2'));

		$this->loadController();
		$this->Controller->loginRequired = 'test_action';
		$this->AllowDeny->initialize($this->Controller);
		$this->assertFalse($this->_allowed('test_action'));
		$this->assertTrue($this->_allowed('test_action2'));

		$this->loadController();
		$this->AllowDeny->allowNonPrefix = false;
		$this->AllowDeny->initialize($this->Controller);
		$this->assertFalse($this->_allowed('test_action'));
		$this->assertFalse($this->_allowed('test_action2'));

		$this->loadController();
		$this->Controller->allowActions = array('test_action2');
		$this->AllowDeny->initialize($this->Controller);
		$this->assertFalse($this->_allowed('test_action'));
		$this->assertTrue($this->_allowed('test_action2'));

		$this->loadController();
		$this->Controller->allowNonPrefix = false;
		$this->AllowDeny->initialize($this->Controller);
		$this->assertFalse($this->_allowed('test_action'));
		$this->assertFalse($this->_allowed('test_action2'));

		$this->loadController();
		$this->Controller->allowNonPrefix = false;
		$this->AllowDeny->allowNonPrefix = true;
		$this->AllowDeny->initialize($this->Controller);
		$this->assertFalse($this->_allowed('test_action'));
		$this->assertFalse($this->_allowed('test_action2'));
	}

}
