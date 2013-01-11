<?php

App::uses('AutoTransactionBehavior', 'Ninja.Model/Behavior');
App::import('TestSuite', 'Ninja.NinjaBehaviorTestCase');

class MockAutoTransactionBehavior extends AutoTransactionBehavior {

	public $log = array();

	protected function _transaction($model, $start = true) {
		$result = parent::_transaction($model, $start);
		if ($result) {
			$this->log[] = $start ? 'begin' : 'commit';
		}
		return $result;
	}
}

class AutoTransactionBehaviorMockModel extends Model {
	public $actsAs = array('MockAutoTransaction');
}

class AutoTransactionBehaviorTest extends NinjaBehaviorTestCase {
	public $fixtures = array('plugin.ninja.auto_transaction_behavior_mock_model');

	protected function _reset($auto = true) {
		$this->Model->autoTransaction($auto);
		$this->_getBehavior()->log = array();
	}

	public function testTransaction() {
		$this->Model->save($this->Model->create());
		$this->assertEqual($this->_getBehavior()->log, array('begin', 'commit'));

		$this->_reset(false);
		$this->Model->save($this->Model->create());
		$this->assertEqual($this->_getBehavior()->log, array());

		$this->_reset();
		$this->Model->delete(2);
		$this->assertEqual($this->_getBehavior()->log, array('begin', 'commit'));

		$this->_reset(false);
		$this->Model->delete(1);
		$this->assertEqual($this->_getBehavior()->log, array());
	}

	public function testStartedByOther() {

		TransactionManager::begin($this->Model->useDbConfig);
		$this->Model->save($this->Model->create());
		$this->assertEqual($this->_getBehavior()->log, array());

		TransactionManager::rollback($this->Model->useDbConfig);
	}

	public function testMultiAttached() {

		$this->Model->Behaviors->MockAutoTransaction->beforeDelete($this->Model);
		$this->assertTrue($this->Model->started());

		$this->Model->Behaviors->detach('MockAutoTransaction');
		$this->Model->Behaviors->attach('MockAutoTransaction');

		$this->assertTrue($this->Model->started());

	}
}