<?php

App::uses('ModelBehavior', 'Model');
App::uses('Cache', 'Cache');
App::uses('Hash', 'Utility');
App::uses('String', 'Utility');
App::uses('Security', 'Utility');

class TemporaryDataBehavior extends ModelBehavior {

	public static $defaultSettings = array(
		'tokenLength' => 40,
		'defaultCacheConfig' => 'default',
		'memoryCache' => true
	);

	protected $_generatedTokens = array();
	protected $_caches = array();

	public function setUp(Model $Model, $settings = array()) {
		$this->settings[$Model->alias] = Hash::merge(self::$defaultSettings, $settings);
	}

/*
 * setter/getter of token legnth
 */
	public function tokenLength($Model, $length = null) {
		if ($length !== null) {
			$this->settings[$Model->alias]['tokenLength'] = $length;
		}
		return $this->settings[$Model->alias]['tokenLength'];
	}

	public function generateToken(Model $Model) {
		return substr(Security::hash(String::uuid(), null, true), 0, $this->settings[$Model->alias]['tokenLength']);
	}

	public function storeTempData(Model $Model, $data, $configName = null) {
		$configName = $this->_getCacheConfig($Model, $configName);

		$token = $Model->generateToken();
		$this->_generatedTokens[$Model->alias] = $token;
		if ($this->settings[$Model->alias]['memoryCache']) {
			$this->_caches[$Model->alias][$configName][$token] = $data;
		}
		Cache::write($token, $data, $configName);
		return $token;
	}

	public function getGeneratedToken(Model $Model) {
		if (isset($this->_generatedTokens[$Model->alias])) {
			return $this->_generatedTokens[$Model->alias];
		}
		return null;
	}

	public function isTokenAvailable(Model $Model, $token, $configName = null) {
		$data = $Model->loadTempData($token, $configName);
		return $data !== null;
	}

	public function loadTempData(Model $Model, $token, $configName = null) {
		$configName = $this->_getCacheConfig($Model, $configName);

		if ($this->settings[$Model->alias]['memoryCache']) {
			if (isset($this->_caches[$Model->alias][$configName][$token])) {
				return $this->_caches[$Model->alias][$configName][$token];
			}
		}

		$data = Cache::read($token, $configName);
		if ($data === false || $data === '') {
			return null;
		}

		if ($this->settings[$Model->alias]['memoryCache']) {
			$this->_caches[$Model->alias][$configName][$token] = $data;
		}
		return $data;
	}

	public function deleteToken(Model $Model, $token, $configName = null) {
		$configName = $this->_getCacheConfig($Model, $configName);
		if (Cache::delete($token, $configName)) {
			if ($this->settings[$Model->alias]['memoryCache']) {
				if (isset($this->_caches[$Model->alias][$configName][$token])) {
					unset($this->_caches[$Model->alias][$configName][$token]);
				}
			}

			return true;
		}

		return false;
	}

	public function _getCacheConfig(Model $Model, $configName) {
		if ($configName === null) {
			$configName = $this->settings[$Model->alias]['defaultCacheConfig'];
		}

		if (Configure::read('debug') > 0 && !in_array($configName, Cache::configured())) {
			throw new Exception(__('Cache config %s is not cofigured', $configName));
		}

		return $configName;
	}
}
