<?php

App::import('TestSuite', 'Ninja.NinjaBehaviorTestCase');

class CurrentUserBehaviorMockModel extends Model {
	public $actsAs = array('Ninja.CurrentUser');
}

class CurrentUserBehaviorTest extends NinjaBehaviorTestCase {

	public $fixtures = array('plugin.Ninja.CurrentUserBehaviorMockModel');

	public function testSetCurrentUser() {
		Configure::delete('CurrentUser');
		$this->assertFalse($this->Model->setCurrentUser());

		Configure::write('CurrentUser.id', 1);
		$this->assertTrue($this->Model->setCurrentUser());
		$this->assertEquals(1, $this->Model->data['CurrentUserBehaviorMockModel']['user_id']);

		$this->Model->data = array();
		$data = array(
			$this->Model->alias => array(
				'hoge' => 'piyo',
			),
		);
		$this->Model->set($data);
		$this->Model->setCurrentUser();
		$this->assertEquals(1, $this->Model->data['CurrentUserBehaviorMockModel']['user_id']);

		$data[$this->Model->alias]['user_id'] = 2;
		$this->Model->data = array();
		$this->Model->set($data);
		$this->Model->setCurrentUser();
		$this->assertEquals(1, $this->Model->data['CurrentUserBehaviorMockModel']['user_id']);
	}

	public function testValidate() {
		Configure::write('CurrentUser.id', 1);
		$data = array(
			$this->Model->alias => array(
				'hoge' => 'piyo',
			),
		);
		$this->Model->set($data);
		$this->Model->validates($data);
		$this->assertEquals(1, $this->Model->data['CurrentUserBehaviorMockModel']['user_id']);

		$this->_reattach(array('auto' => 'set'));
		$this->Model->create(false);
		$this->Model->set($data);
		$this->Model->validates($data);
		$this->assertEquals(1, $this->Model->data['CurrentUserBehaviorMockModel']['user_id']);

		$this->_reattach(array('auto' => 'delete'));
		$data[$this->Model->alias]['user_id'] = 2;
		$this->Model->create(false);
		$this->Model->set($data);
		$this->Model->validates($data);
		$this->assertEquals(2, $this->Model->data['CurrentUserBehaviorMockModel']['user_id']);

		$this->_reattach(array('auto' => false));
		$data[$this->Model->alias]['user_id'] = 2;
		$this->Model->create(false);
		$this->Model->set($data);
		$this->Model->validates($data);
		$this->assertEquals(2, $this->Model->data['CurrentUserBehaviorMockModel']['user_id']);
	}

	public function testValidateCurrentUserDelete() {
		$this->loadFixtures('CurrentUserBehaviorMockModel');
		Configure::write('CurrentUser.id', 2);

		$this->Model->id = 1;
		$this->assertFalse($this->Model->validateCurrentUserDelete());

		Configure::write('CurrentUser.id', 1);
		$this->assertTrue($this->Model->validateCurrentUserDelete());
	}

	public function testBeforeDelete() {
		$this->loadFixtures('CurrentUserBehaviorMockModel');
		Configure::write('CurrentUser.id', 1);
		$this->Model->id = 1;
		$Behavior = new CurrentUserBehavior();

		$Behavior->auto = false;
		$this->assertNull($Behavior->beforeDelete($this->Model));

		$Behavior->auto = 'set';
		$this->assertNull($Behavior->beforeDelete($this->Model));

		$Behavior->auto = true;
		$this->assertTrue($Behavior->beforeDelete($this->Model));

		$Behavior->auto = 'delete';
		$this->assertTrue($Behavior->beforeDelete($this->Model));
	}
}