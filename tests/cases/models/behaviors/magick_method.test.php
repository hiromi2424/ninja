<?php

App::import('Lib', 'Ninja.test' . DS . 'NinjaBehaviorTestCase');

class MagickMethodBehaviorMockModel extends Model {
	public $useTable = false;
	public $actsAs = array('Ninja.MagickMethod');

	public function find() {
		$args = func_get_args();
		return $args;
	}

	public function getInsertId() {
		return 2;
	}

	public function byUserDefined() {
		return array('userdefined', 'value!');
	}
}

class MagickMethodBehaviorTestCase extends NinjaBehaviorTestCase {

	public function testFind() {
		$this->assertEqual($this->Model->findById(1), array('first', array('conditions' => array($this->Model->escapeField('id') => 1))));
		$this->assertEqual($this->Model->findAllById(1), array('all', array('conditions' => array($this->Model->escapeField('id') => 1))));
		$this->assertEqual($this->Model->findAllById(array(1, 2)), array('all', array('conditions' => array($this->Model->escapeField('id') => array(1, 2)))));

		$this->assertEqual($this->Model->findByUserId(1), array('first', array('conditions' => array($this->Model->escapeField('user_id') => 1))));
		$this->assertEqual($this->Model->findByInsertId(), array('first', array('conditions' => array($this->Model->escapeField('id') => 2))));
		$this->assertEqual($this->Model->findByUserIdAndInsertId(1), array('first', array('conditions' => array($this->Model->escapeField('user_id') => 1, $this->Model->escapeField('id') => 2))));
		$this->assertEqual($this->Model->findByIdAndId(1, 2), array('first', array('conditions' => array($this->Model->escapeField('id') => 2))));
		$this->assertEqual($this->Model->findByIdAndUserNameAndPassword(1, 'john', 'I love you'), array('first', array('conditions' => array($this->Model->escapeField('id') => 1, $this->Model->escapeField('user_name') => 'john', $this->Model->escapeField('password') => 'I love you'))));

		$this->assertEqual($this->Model->findByIdOrUserId(1, 2), array('first', array('conditions' => array('OR' => array($this->Model->escapeField('id') => 1, $this->Model->escapeField('user_id') => 2)))));
		$this->assertEqual($this->Model->findByUserDefined(), array('first', array('conditions' => array($this->Model->escapeField('userdefined') => 'value!'))));

		$this->assertEqual($this->Model->findAll(), array('all', array()));
		$this->assertEqual($this->Model->findList(), array('list', array()));
		$this->assertEqual($this->Model->findUserDefinedFindMethod(), array('userDefinedFindMethod', array()));
	}

	public function testError() {
		$this->expectError();
		$this->assertNull($this->Behavior->__findMagick($this->Model));

		$this->expectError();
		$this->assertNull($this->Model->findById());
	}
}