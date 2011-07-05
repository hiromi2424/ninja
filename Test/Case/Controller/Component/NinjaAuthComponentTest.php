<?php
/* AllowDeny Test cases generated on: 2011-05-22 14:43:09 : 1306042989*/

App::uses('Model', 'Model');
App::uses('AppModel', 'Model');
App::uses('NinjaAuthComponent', 'Ninja.Controller/Component');
App::import('TestSuite', 'Ninja.NinjaComponentTestCase');

class NinjaAuthTestController extends Controller {

	public $uses = null;

	// disable directing for test
	public function redirect(){
	}

}

class TestNinjaAuthComponent extends NinjaAuthComponent {

	public $userModel = 'NinjaAuthComponentMockUser';

}

class NinjaAuthComponentMockUser extends AppModel {

	public $belongsTo = array(
		'Group' => array(
			'className' => 'NinjaAuthComponentMockGroup',
			'foreignKey' => 'ninja_auth_component_mock_group_id',
		),
	);

	public $hasOne = array(
		'Profile' => array(
			'className' => 'NinjaAuthComponentMockProfile',
			'foreignKey' => 'ninja_auth_component_mock_user_id',
		),
	);

}

class NinjaAuthComponentTestCase extends NinjaComponentTestCase {

	public $fixtures = array(
		'plugin.ninja.ninja_auth_component_mock_user',
		'plugin.ninja.ninja_auth_component_mock_group',
		'plugin.ninja.ninja_auth_component_mock_profile',
	);

	protected $_sessionBackup;

	public $User;

	public function startTest($method = null) {
		parent::startTest($method);
		$this->_sessionBackup = $this->NinjaAuth->Session->read('Auth');
		$this->NinjaAuth->Session->delete('Auth');

		$this->User = ClassRegistry::init('NinjaAuthComponentMockUser');
		$this->User->updateAll(array(
			'password' => $this->User->getDataSource()->value($this->NinjaAuth->password('testpassword')),
		));
	}

	public function endTest($method = null) {
		$this->NinjaAuth->Session->delete('Auth');
		$this->NinjaAuth->Session->write('Auth', $this->_sessionBackup);
		parent::endTest($method);
	}

	public function testLoginAndIdentify() {
		$this->assertFalse($this->NinjaAuth->user());
		$this->assertTrue($this->NinjaAuth->login(2));
		$this->assertEqual(2, $this->NinjaAuth->user('id'));

		$this->assertTrue($this->NinjaAuth->login());
		$this->assertEqual(2, $this->NinjaAuth->user('id'));

		$this->NinjaAuth->logout();
		$this->assertTrue($this->NinjaAuth->login(array(
			$this->User->escapeField('username') => 'test user2',
			$this->User->escapeField('password') => $this->NinjaAuth->password('testpassword'),
		)));
		$this->assertEqual(2, $this->NinjaAuth->user('id'));

		$this->NinjaAuth->logout();
		$this->assertFalse($this->NinjaAuth->login(array(
			$this->User->escapeField('username') => 'test user2',
			$this->User->escapeField('password') => $this->NinjaAuth->password('wrong password'),
		)));
		$this->assertEqual(null, $this->NinjaAuth->user('id'));


		$this->assertTrue($this->NinjaAuth->identify(2, array(
			'ninja_auth_component_mock_group_id' => 2,
		)));

		$this->assertFalse($this->NinjaAuth->identify(2, array(
			'ninja_auth_component_mock_group_id' => 1,
		)));

		$this->assertTrue($this->NinjaAuth->identify(array(
			$this->User->escapeField('username') => 'test user2',
			$this->User->escapeField('password') => $this->NinjaAuth->password('testpassword'),
		), array(
			'ninja_auth_component_mock_group_id' => 2,
		)));

		$this->assertFalse($this->NinjaAuth->identify(array(
			$this->User->escapeField('username') => 'test user2',
			$this->User->escapeField('password') => $this->NinjaAuth->password('testpassword'),
		), array(
			'ninja_auth_component_mock_group_id' => 1,
		)));

		$this->NinjaAuth->userScope = array(
			'username' => 'test user1',
		);
		$this->assertFalse($this->NinjaAuth->identify(2, array(
			'ninja_auth_component_mock_group_id' => 2,
		)));
		$this->assertFalse($this->NinjaAuth->identify(1, array(
			'ninja_auth_component_mock_group_id' => 2,
		)));
		$this->assertTrue($this->NinjaAuth->identify(1, array(
			'ninja_auth_component_mock_group_id' => 1,
		)));

		$this->assertTrue($this->NinjaAuth->identify(array(
			'Profile.id' => 1,
			'Group.id' => 1,
		)));

		$this->NinjaAuth->logout();
		$this->NinjaAuth->userScope = array();
		$this->assertFalse($this->NinjaAuth->identify());
	}

	public function testAdjustUserdataForStore() {
		$identified = $this->NinjaAuth->identify(1);
		$user = $this->User->find('first', array(
			'recursive' => 0,
			'conditions' => array(
				$this->User->escapeField('id') => 1,
			),
		));
		$result = $this->NinjaAuth->adjustUserdataForStore($user);
		$this->assertEqual($identified, $result);

		$user = array(
			'NinjaAuthComponentMockUser' => array(
				'username' => 'test user1',
			),
			'Group' => array(
				'name' => 'test group1',
			),
			'Profile' => array(
				'nickname' => 'test nick',
			),
		);
		$expected = array(
			'username' => 'test user1',
			'Group' => array(
				'name' => 'test group1',
			),
			'Profile' => array(
				'nickname' => 'test nick',
			),
		);
		$this->assertEqual($expected, $this->NinjaAuth->adjustUserdataForStore($user));
	}

	public function testUser() {
		$user = array(
			'NinjaAuthComponentMockUser' => array(
				'username' => 'test user1',
				'Group' => array(
					'name' => 'test group1',
				),
				'Profile' => array(
					'nickname' => 'test nick',
				),
			),
		);
		$this->NinjaAuth->Session->write('Auth', $user);

		$this->assertNull($this->NinjaAuth->user('undefined'));
		$this->assertEqual('test user1', $this->NinjaAuth->user('username'));
		$this->assertEqual('test group1', $this->NinjaAuth->user('Group.name'));

		$result = $this->NinjaAuth->user();
		$this->assertTrue(Set::check($result, 'NinjaAuthComponentMockUser.Profile.nickname'));
		$this->assertTrue(Set::check($result, 'NinjaAuthComponentMockUser.username'));
	}

	public function testRelogin() {
		$this->assertFalse($this->NinjaAuth->reLogin());
		$this->NinjaAuth->login(1);
		$this->assertTrue($this->NinjaAuth->reLogin());

		$this->assertEqual('test user1', $this->NinjaAuth->user('username'));
		$this->User->save(array('id' => 1, 'username' => 'changed'));
		$this->assertEqual('test user1', $this->NinjaAuth->user('username'));
		$this->assertTrue($this->NinjaAuth->reLogin());
		$this->assertEqual('changed', $this->NinjaAuth->user('username'));
	}

}
