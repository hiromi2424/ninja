<?php

App::uses('ConnectionManager', 'Model');

class TransactionManager extends Object {

	private static $__instances = array();

	public $autoCommit = false;
	public $started = false;

	protected $_datasource = 'default';
	protected $_db;

	private static function __getInstance($datasource = 'default') {
		if (!self::_instantiated($datasource)) {
			self::$__instances[$datasource] = new TransactionManager;
			self::$__instances[$datasource]->setDataSource($datasource);
		}
		return self::$__instances[$datasource];
	}

	public function setDataSource($datasource = 'default') {
		if (!self::_isDatasourceAvailable($datasource)) {
			throw new MissingDatasourceConfigException(array('config' => $datasource));
		}
		$this->_datasource = $datasource;
		$this->_db = ConnectionManager::getDataSource($datasource);
	}

	public static function begin($datasource = 'default') {
		$_this = self::__getInstance($datasource);
		$_this->started = true;
		return $_this->_db->begin();
	}

	public static function rollback($datasource = 'default') {
		$_this = self::__getInstance($datasource);
		$_this->started = false;
		return $_this->_db->rollback();
	}

	public static function commit($datasource = 'default') {
		$_this = self::__getInstance($datasource);
		$_this->started = false;
		return $_this->_db->commit();
	}

	public static function autoCommit($autoCommit, $datasource = 'default') {
		self::__getInstance($datasource)->autoCommit = $autoCommit;
	}

	protected function _instantiated($datasource) {
		return isset(self::$__instances[$datasource]->_db);
	}

	protected static function _isDatasourceAvailable($datasource = 'default') {
		if (self::_instantiated($datasource)) {
			return true;
		}
		$sourceList = ConnectionManager::enumConnectionObjects();
		return array_key_exists($datasource, $sourceList);
	}

	public function __destruct() {
		if ($this->started) {
			$this->autoCommit ? $this->commit($this->_datasource) : $this->rollback($this->_datasource);
		}
	}

	public static function destructs() {
		self::$__instances = array();
	}

	public static function started($datasource = 'default') {
		return self::__getInstance($datasource)->started;
	}
}
