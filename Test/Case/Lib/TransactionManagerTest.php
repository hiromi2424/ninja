<?php

App::uses('TransactionManager', 'Ninja.Lib');
App::import('TestSuite', 'Ninja.NinjaTestCase');

class MockTransactionManager extends TransactionManager {
	public static $begun = array();
	public static $commited = array();
	public static $rollbacked = array();

	public static function begin($datasource = 'default') {
		$result = parent::begin($datasource);
		if ($result) {
			self::$begun[] = $datasource;
		}
		return $result;
	}

	public static function rollback($datasource = 'default') {
		$result = parent::rollback($datasource);
		if ($result) {
			self::$rollbacked[] = $datasource;
		}
		return $result;
	}

	public static function commit($datasource = 'default') {
		$result = parent::commit($datasource);
		if ($result) {
			self::$commited[] = $datasource;
		}
		return $result;
	}

	public static function reset() {
		self::$begun = array();
		self::$rollbacked = array();
		self::$commited = array();
	}
}

class TransactionManagerTestCase extends NinjaTestCase {

	public $datasource = 'test_suite';
	public $mockModel;

	public function startCase() {
		MockTransactionManager::begin($this->datasource);
		MockTransactionManager::rollback($this->datasource);
		MockTransactionManager::destructs();
		MockTransactionManager::reset();
		parent::startCase();
	}

	public function endCase() {
		MockTransactionManager::destructs();
		parent::endCase();
	}

	public function testBasic() {
		$this->assertTrue(MockTransactionManager::begin($this->datasource));
		$this->assertfalse(MockTransactionManager::begin($this->datasource));

		$this->assertTrue(MockTransactionManager::rollback($this->datasource));
		$this->assertfalse(MockTransactionManager::rollback($this->datasource));

		$this->assertFalse(MockTransactionManager::started($this->datasource));
		MockTransactionManager::begin($this->datasource);
		$this->assertTrue(MockTransactionManager::started($this->datasource));

		$this->assertTrue(MockTransactionManager::commit($this->datasource));
		$this->assertfalse(MockTransactionManager::commit($this->datasource));

		$this->assertEqual(count(MockTransactionManager::$begun), 2);
		$this->assertEqual(count(MockTransactionManager::$commited), 1);
		$this->assertEqual(count(MockTransactionManager::$rollbacked), 1);
	}

	public function testAutoCommit() {
		MockTransactionManager::begin($this->datasource);
		MockTransactionManager::destructs();
		$this->assertEqual(count(MockTransactionManager::$rollbacked), 1);

		MockTransactionManager::autoCommit(true, $this->datasource);
		MockTransactionManager::begin($this->datasource);
		MockTransactionManager::destructs();
		$this->assertEqual(count(MockTransactionManager::$commited), 1);
	}

	public function testInvalidDatasource() {
		$this->expectException();
		MockTransactionManager::begin('InvalidDatasource!!!!!');
	}
}