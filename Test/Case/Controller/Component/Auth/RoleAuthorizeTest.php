<?php
/* RoleAuthorize Test cases generated on: 2011-09-12 23:38:00 : 1315838280*/

App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('NinjaTestCase', 'Ninja.TestSuite');
App::import('Component', 'Auth');

App::uses('RoleAuthorize', 'Ninja.Controller/Component/Auth');
class_exists('RoleAuthorize'); // loading

class RoleAuthorizeTest extends NinjaTestCase {

	public $fixtures = false;

	protected $_configBackup;

	protected $_testConfigName = '__RoleAuthorizeTestConfig';
	protected $_currentLevel = 200;

	protected function _instantiate() {
		$this->Controller = new Controller(new CakeRequest());
		$this->Controller->constructClasses();
		$this->Controller->action = 'test_action';
		$this->RoleAuthorize = new RoleAuthorize($this->Controller->Components);
	}

	public function tearDown() {
		unset($this->RoleAuthorize);
		unset($this->Controller);
		ClassRegistry::flush();
		parent::tearDown();
	}

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
			->will($this->returnValue(1));

		$this->RoleAuthorize->configName = $this->_testConfigName;

		$Group = $this->getMock('Model');
		$Group->expects($this->any())
			->method('field')
			->will($this->returnCallback(array($this, 'currentLevel')));
		$Group->alias = 'Group';
		ClassRegistry::addObject('Group', $Group);
		ClassRegistry::map('Group', 'Group');
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
		$user = array(); // mock

		$this->assertTrue($this->RoleAuthorize->authorize($user, $this->Controller->request));

		$this->Controller->request->params['prefix'] = 'admin';
		$this->assertFalse($this->RoleAuthorize->authorize($user, $this->Controller->request));
		$this->Controller->request->params['prefix'] = 'member';
		$this->assertTrue($this->RoleAuthorize->authorize($user, $this->Controller->request));
		unset($this->Controller->request->params['prefix']);

		$this->Controller->requireAuth = 'admin';
		$this->assertFalse($this->RoleAuthorize->authorize($user, $this->Controller->request));
		$this->Controller->requireAuth = 'member';
		$this->assertTrue($this->RoleAuthorize->authorize($user, $this->Controller->request));
		unset($this->Controller->requireAuth);

		$this->Controller->requireAuth = array('test_action' => 'admin');
		$this->assertFalse($this->RoleAuthorize->authorize($user, $this->Controller->request));
		$this->Controller->requireAuth = array('test_action' => 'member');
		$this->assertTrue($this->RoleAuthorize->authorize($user, $this->Controller->request));

		$this->Controller->requireAuth = 'member';
		$this->_currentLevel = 199;
		$this->assertFalse($this->RoleAuthorize->authorize($user, $this->Controller->request));
		$this->_currentLevel = 200;
		$this->assertTrue($this->RoleAuthorize->authorize($user, $this->Controller->request));
		$this->_currentLevel = 200.0;
		$this->assertTrue($this->RoleAuthorize->authorize($user, $this->Controller->request));
		$this->_currentLevel = '200';
		$this->assertTrue($this->RoleAuthorize->authorize($user, $this->Controller->request));
		$this->_currentLevel = 'hoge';
		$this->assertFalse($this->RoleAuthorize->authorize($user, $this->Controller->request));

	}

}
