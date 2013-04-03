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

App::uses('TransationManagerTestDatasource', 'Model/Datasource');
ConnectionManager::create('transaction_manager_test', array_merge($testDb->config, array(
	'datasource' => 'TransationManagerTestDatasource',
)));

unset($testDb, $testDbClass);

class TransactionManagerTest extends NinjaTestCase {

	public $datasource = 'transaction_manager_test';
	public $mockModel;

	public $fixtures = null;

	public function setUp() {

		TransactionManager::rollback($this->datasource);
		TransactionManager::destructs();
		TransationManagerTestDatasource::resetTransationCounts();

		parent::setUp();

	}

	public function tearDown() {
		TransactionManager::destructs();
		parent::tearDown();
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

		$this->assertEquals(2, TransationManagerTestDatasource::$begun);
		$this->assertEquals(1, TransationManagerTestDatasource::$commited);
		$this->assertEquals(1, TransationManagerTestDatasource::$rollbacked);
	}

	public function testAutoCommit() {
		TransactionManager::begin($this->datasource);
		TransactionManager::destructs();
		$this->assertEquals(1, TransationManagerTestDatasource::$rollbacked);

		TransactionManager::autoCommit(true, $this->datasource);
		TransactionManager::begin($this->datasource);
		TransactionManager::destructs();
		$this->assertEquals(1, TransationManagerTestDatasource::$commited);
	}

	public function testInvalidDatasource() {
		$this->expectException('MissingDatasourceConfigException');
		TransactionManager::begin('InvalidDatasource!!!!!');
	}
}