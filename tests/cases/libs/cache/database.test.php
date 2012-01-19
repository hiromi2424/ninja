<?php
/**
 * DatabaseEngineTest file
 *
 */
if (!class_exists('Cache')) {
	require LIBS . 'cache.php';
}
App::import('Lib', 'Ninja.NinjaTestCase');

/**
 * DatabaseEngineTest class
 *
 */
class DatabaseEngineTest extends NinjaTestCase {

/**
 * fixtures property
 *
 * @var array
 */
	public $fixtures = array('plugin.ninja.temporariness', 'plugin.ninja.temporariness_not_primary');

/**
 * Backup of settings
 *
 * @var array
 */
	protected $_cacheDisable;

/**
 * setUp method
 *
 * @return void
 */
	public function startTest($method) {
		parent::startTest($method);
		$this->_cacheDisable = Configure::read('Cache.disable');
		Configure::write('Cache.disable', false);
		Cache::config('database_test', array('engine' => 'Ninja.Database', 'prefix' => 'database_test_'));
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function endTest($method) {
		Cache::clear(false, 'database_test');
		Configure::write('Cache.disable', $this->_cacheDisable);
		Cache::drop('database_test');
		Cache::config('default');
		parent::endTest($method);
	}

/**
 * testReadAndWriteCache method
 *
 * @return void
 */
	public function testReadAndWriteCache() {
		Cache::set(array('duration' => 1));

		$result = Cache::read('test');
		$expecting = '';
		$this->assertEqual($result, $expecting);

		$data = 'this is a test of the emergency broadcasting system';
		$result = Cache::write('test', $data);
		$this->assertTrue($result);

		$result = Cache::read('test');
		$expecting = $data;
		$this->assertEqual($result, $expecting);

		Cache::delete('test');
	}

/**
 * Writing cache entries with duration = 0 (forever) should work.
 *
 * @return void
 */
	public function testReadWriteDurationZero() {
		Cache::config('database_test', array('engine' => 'Ninja.Database', 'duration' => 0));
		Cache::write('zero', 'Should save', 'database_test');
		sleep(1);

		$result = Cache::read('zero', 'database_test');
		$this->assertEqual('Should save', $result);
	}

/**
 * testExpiry method
 *
 * @return void
 */
	public function testExpiry() {
		Cache::set(array('duration' => 1));

		$data = 'this is a test of the emergency broadcasting system';
		$result = Cache::write('other_test', $data);
		$this->assertTrue($result);

		sleep(2);
		$result = Cache::read('other_test');
		$this->assertFalse($result);

		Cache::set(array('duration' => 1));

		$data = 'this is a test of the emergency broadcasting system';
		$result = Cache::write('other_test', $data);
		$this->assertTrue($result);

		sleep(2);
		$result = Cache::read('other_test');
		$this->assertFalse($result);

		Cache::set(array('duration' => 0));

		$data = 'this is a test of the emergency broadcasting system';
		$result = Cache::write('persistent', $data);
		$this->assertTrue($result);

		sleep(1);
		$result = Cache::read('persistent');
		$this->assertEqual($result, $data);
	}

/**
 * testDeleteCache method
 *
 * @return void
 */
	public function testDeleteCache() {
		$data = 'this is a test of the emergency broadcasting system';
		$result = Cache::write('delete_test', $data);
		$this->assertTrue($result);

		$result = Cache::delete('delete_test');
		$this->assertTrue($result);

		$result = Cache::delete('delete_test');
		$this->assertFalse($result);
	}

/**
 * testClearCache method
 *
 * @return void
 */
	public function testClearCache() {
		$config = Cache::config('database_test', array(
			'duration' => 5,
			'prefix' => 'test_prefix_',
		));
		$settings = $config['settings'];
		$model = ClassRegistry::init($settings['model']);

		$this->_insertData($model, $settings);
		$this->assertTrue(Cache::clear(false, 'database_test'));
		$result = $model->find('count');
		$this->assertEqual($result, 1);
		$result = $model->field($settings['fields']['key']);
		$this->assertEqual($result, 'prefix_not_match');

		$this->_insertData($model, $settings);
		$this->assertTrue(Cache::clear(true, 'database_test'));
		$result = $model->find('list', array('fields' => array($settings['fields']['key']), 'order' => array($settings['fields']['key'] => 'ASC')));
		$this->assertEqual(array_values($result), array('prefix_not_match', 'test_prefix_persistent', 'test_prefix_survive'));
	}

/**
 * inserts test data for clearing cache
 *
 * @return void
 */
	protected function _insertData($model, $settings) {
		$model->deleteAll(true);
		$model->saveAll(array(
			array(
				$settings['fields']['key'] => 'test_prefix_expired',
				$settings['fields']['value'] => 'expired value',
				$settings['fields']['expires'] => time() - 5,
			),
			array(
				$settings['fields']['key'] => 'test_prefix_survive',
				$settings['fields']['value'] => 'survive value',
				$settings['fields']['expires'] => time() + 5,
			),
			array(
				$settings['fields']['key'] => 'test_prefix_persistent',
				$settings['fields']['value'] => 'persistent value',
				$settings['fields']['expires'] => 0,
			),
			array(
				$settings['fields']['key'] => 'prefix_not_match',
				$settings['fields']['value'] => 'prefix_not_match value',
				$settings['fields']['expires'] => time() - 5,
			),
		));
	}

/**
 * testKeyIsNotPrimary method
 *
 * @return void
 */
	public function testKeyIsNotPrimary() {
		Cache::config('database_test', array(
			'fields' => array(
				'key' => 'key',
				'value' => 'value',
				'expires' => 'expires',
			),
			'model' => 'TemporarinessNotPrimary',
		));

		$cases = array(
			'testReadAndWriteCache',
			'testReadWriteDurationZero',
			'testExpiry',
			'testDeleteCache',
			'testClearCache',
		);
		foreach ($cases as $case) {
			$this->$case();
			Cache::clear(false, 'database_test');
		}

		Cache::config('database_test', array(
			'fields' => array(
				'key' => 'id',
				'value' => 'value',
				'expires' => 'expires',
			),
			'model' => 'Temporariness',
		));
	}

}
