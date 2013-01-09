<?php
/* RoleAuthorize Test cases generated on: 2011-09-12 23:38:00 : 1315838280*/

App::uses('NinjaComponentTestCase', 'Ninja.TestSuite');
App::import('Component', 'Auth');

App::uses('RoleAuthorizeComponent', 'Ninja.Controller/Component/Auth');
class_exists('RoleAuthorizeComponent'); // loading

class RoleAuthorizeComponentTestCase extends NinjaComponentTestCase {

	public $fixtures = false;

	protected $_configBackup;

	protected $_testConfigName = '__RoleAuthorizeTestConfig';
	protected $_currentLevel = 200;

	public function startTest($method = null) {
		parent::startTest($method);
		$this->_configBackup = Configure::read($this->_testConfigName);
		Configure::write($this->_testConfigName, array(
			'member' => 200,
			'moderator' => 400,
			'admin' => 800,
		));
		$this->Controller->Auth = $this->getMock('AuthComponent', array('user'), array(new ComponentCollection));
		$this->Controller->Auth->staticExpects($this->any())
			->method('user')
			->will($this->returnCallback(array($this, 'currentLevel')));

		$this->RoleAuthorize->configName = $this->_testConfigName;
	}

	public function currentLevel() {
		return $this->_currentLevel;
	}

	public function endTest($method = null) {
		Configure::delete($this->_testConfigName);
		if ($this->_configBackup !== false) {
			Configure::write($this->_testConfigName, $this->_configBackup);
		}
		parent::endTest($method);
	}

	public function testAuthorize() {
		$this->assertTrue($this->RoleAuthorize->authorize());

		$this->Controller->request->params['prefix'] = 'admin';
		$this->assertFalse($this->RoleAuthorize->authorize());
		$this->Controller->request->params['prefix'] = 'member';
		$this->assertTrue($this->RoleAuthorize->authorize());
		unset($this->Controller->request->params['prefix']);

		$this->Controller->requireAuth = 'admin';
		$this->assertFalse($this->RoleAuthorize->authorize());
		$this->Controller->requireAuth = 'member';
		$this->assertTrue($this->RoleAuthorize->authorize());
		unset($this->Controller->requireAuth);

		$this->Controller->requireAuth = array('test_action' => 'admin');
		$this->assertFalse($this->RoleAuthorize->authorize());
		$this->Controller->requireAuth = array('test_action' => 'member');
		$this->assertTrue($this->RoleAuthorize->authorize());

		$this->Controller->requireAuth = 'member';
		$this->_currentLevel = 199;
		$this->assertFalse($this->RoleAuthorize->authorize());
		$this->_currentLevel = 200;
		$this->assertTrue($this->RoleAuthorize->authorize());
		$this->_currentLevel = 200.0;
		$this->assertTrue($this->RoleAuthorize->authorize());
		$this->_currentLevel = '200';
		$this->assertTrue($this->RoleAuthorize->authorize());
		$this->_currentLevel = 'hoge';
		$this->assertFalse($this->RoleAuthorize->authorize());

	}

}
