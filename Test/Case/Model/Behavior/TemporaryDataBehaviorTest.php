<?php

App::import('TestSuite', 'Ninja.NinjaBehaviorTestCase');

class TemporaryDataBehaviorMockModel extends Model {
	public $useTable = false;
}

class TemporaryDataBehaviorTest extends NinjaBehaviorTestCase {

	public $behaviorSettings = array(
		'defaultCacheConfig' => 'testCacheConfig',
	);

/**
 * setupBeforeClass method
 *
 * @return void
 */
	public static function setupBeforeClass() {
		Cache::config('testCacheConfig', array(
			'engine' => 'File',
			'prefix' => '_test_cache_config',
		));
	}

/**
 * tearDownAfterClass method
 *
 * @return void
 */
	public static function tearDownAfterClass() {
		Cache::clear(false, 'testCacheConfig');
		Cache::drop('testCacheConfig');
	}

/**
 * testTokenLength method
 *
 * @return void
 */
	public function testTokenLength() {
		$result = $this->Model->tokenLength();
		$this->assertSame(40, $result);

		$result = $this->Model->tokenLength(60);
		$this->assertSame(60, $result);
		$this->assertSame(60, $this->Behavior->settings[$this->Model->alias]['tokenLength']);
	}

/**
 * testGenerateToken method
 *
 * @return void
 */
	public function testGenerateToken() {
		$result = $this->Model->generateToken();
		$this->assertTrue(is_string($result));
		$this->assertEquals($this->Behavior->settings[$this->Model->alias]['tokenLength'], strlen($result));
	}

/**
 * testStoreTempData method
 *
 * @return void
 * @return string $token
 */
	public function testStoreTempData() {
		$data = array('test' => 'data');
		$token = $this->Model->storeTempData($data);
		$this->assertTrue(is_string($token));
		$cached = Cache::read($token, $this->behaviorSettings['defaultCacheConfig']);
		$this->assertSame($data, $cached);
		return $token;
	}

/**
 * testGetGeneratedToken method
 *
 * @return void
 */
	public function testGetGeneratedToken() {
		$result = $this->Model->getGeneratedToken();
		$this->assertNull($result);

		$token = $this->Model->storeTempData('test data');
		$result = $this->Model->getGeneratedToken();
		$this->assertSame($token, $result);
	}

/**
 * testIsTokenAvailable method
 *
 * @depends testStoreTempData
 * @return string $token
 */
	public function testIsTokenAvailable($token) {
		$result = $this->Model->isTokenAvailable($token);
		$this->assertTrue($result);

		$result = $this->Model->isTokenAvailable(substr($token, 1));
		$this->assertFalse($result);
		return $token;
	}

/**
 * testLoadTempData method
 *
 * @depends testIsTokenAvailable
 * @return string $token
 */
	public function testLoadTempData($token) {
		$result = $this->Model->loadTempData($token);
		$expected = array('test' => 'data');
		$this->assertSame($expected, $result);
		return $token;
	}

/**
 * testDeleteToken method
 *
 * @depends testLoadTempData
 * @return void
 */
	public function testDeleteToken($token) {
		$result = $this->Model->deleteToken(substr($token, 1));
		$this->assertFalse($result);

		$result = $this->Model->deleteToken($token);
		$this->assertTrue($result);
		$cached = Cache::read($token, $this->behaviorSettings['defaultCacheConfig']);
		$this->assertEmpty($cached);
	}

}
