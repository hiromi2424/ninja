<?php

class TransactionService {

	protected $_ds = array('default');

	protected $_service = null;

	public $catchTry = true;
	public $logException = true;

	public function __construct() {
		ClassRegistry::addObject(__CLASS__, $this);
	}

	public function __call($methodName, $params) {
		if (!method_exists($this->_service, $methodName)) {
			throw new BadMethodCallException(sprintf(__d('ninja', 'The method %s is not defined in %s class.', true), $methodName, get_class($this->_service)));
		}

		return $this->_dispatchService(array($this->_service, $methodName), $params);
	}

	public function setService($service) {
		$this->_service = $service;
		return $this;
	}

	public function setDataSources($ds) {
		if ($ds !== null) {
			$this->_ds = (array)$ds;
		}
		return $this;
	}

	public function begin() {
		$this->_transaction(__FUNCTION__);
	}

	public function commit() {
		$this->_transaction(__FUNCTION__);
	}

	public function rollback() {
		$this->_transaction(__FUNCTION__);
	}

	protected function  _transaction($method) {
		foreach ($this->_ds as $ds) {
			ConnectionManager::getDataSource($ds)->$method($this);
		}
	}

	public function lambda($lambda, $options = array()) {
		return $this->_dispatchService($lambda, array($this->_service), $options);
	}

	protected function _dispatchService($callback, $params, $options = array()) {
		$options += array(
			'catchTry' => $this->catchTry,
			'logException' => $this->logException,
		);

		$this->begin();

		if ($options['catchTry']) {
			try {
				$result = call_user_func_array($callback, $params);
			} catch (Exception $e) {
				if ($options['logException']) {
					Object::log($e->__toString());
				}
				$result = false;
			}
		} else {
			$result = call_user_func_array($callback, $params);
		}

		if (!$result) {
			$this->rollback();
		} else {
			$this->commit();
		}

		return $result;
	}

}