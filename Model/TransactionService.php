<?php

class TransactionService {

	protected $_ds = array('default');

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

}