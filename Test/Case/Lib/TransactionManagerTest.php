<?php

App::uses('TransactionManager', 'Ninja.Lib');
App::import('TestSuite', 'Ninja.NinjaTestCase');
App::uses('ConnectionManager', 'Model');

$testDb = ConnectionManager::getDataSource('test');
$testDbClass = get_class($testDb);

eval( <<<EOC
	if (!class_exists('TransationManagerTestDatasource')) {
		class TransationManagerTestDatasource extends {$testDbClass} {

			public static \$begun = 0;
			public static \$commited = 0;
			public static \$rollbacked = 0;

			public function begin() {
				if (\$result = parent::begin()) {
					self::\$begun++;
				}
				return \$result;
			}

			public function commit() {
				if (\$result = parent::commit()) {
					self::\$commited++;
				}
				return \$result;
			}

			public function rollback() {
				if (\$result = parent::rollback()) {
					self::\$rollbacked++;
				}
				return \$result;
			}

			public static function resetTransationCounts() {
				self::\$begun = 0;
				self::\$rollbacked = 0;
				self::\$commited = 0;
			}

		}
	}

EOC
);

ConnectionManager::create('transaction_manager_test', array_merge($testDb->config, array(
	'datasource' => 'TransationManagerTestDatasource',
)));

unset($testDb, $testDbClass);

class TransactionManagerTestCase extends NinjaTestCase {

	public $datasource = 'transaction_manager_test';
	public $mockModel;

	public $fixtures = null;

	public function startTest($method) {

		TransactionManager::rollback($this->datasource);
		TransactionManager::destructs();
		TransationManagerTestDatasource::resetTransationCounts();

		parent::startTest($method);

	}

	public function endTest($method) {
		TransactionManager::destructs();
		parent::endTest($method);
	}

	public function testBasic() {
		$this->assertTrue(TransactionManager::begin($this->datasource));

		$this->assertTrue(TransactionManager::rollback($this->datasource));
		$this->assertfalse(TransactionManager::rollback($this->datasource));

		$this->assertFalse(TransactionManager::started($this->datasource));
		TransactionManager::begin($this->datasource);
		$this->assertTrue(TransactionManager::started($this->datasource));

		$this->assertTrue(TransactionManager::commit($this->datasource));
		$this->assertfalse(TransactionManager::commit($this->datasource));

		$this->assertEqual(TransationManagerTestDatasource::$begun, 2);
		$this->assertEqual(TransationManagerTestDatasource::$commited, 1);
		$this->assertEqual(TransationManagerTestDatasource::$rollbacked, 1);
	}

	public function testAutoCommit() {
		TransactionManager::begin($this->datasource);
		TransactionManager::destructs();
		$this->assertEqual(TransationManagerTestDatasource::$rollbacked, 1);

		TransactionManager::autoCommit(true, $this->datasource);
		TransactionManager::begin($this->datasource);
		TransactionManager::destructs();
		$this->assertEqual(TransationManagerTestDatasource::$commited, 1);
	}

	public function testInvalidDatasource() {
		$this->expectException('MissingDatasourceConfigException');
		TransactionManager::begin('InvalidDatasource!!!!!');
	}
}