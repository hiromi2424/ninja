<?php

class BaseService {

	public function __get($name) {

		$class = Inflector::classify($name);
		if ($name === 'Transaction') {
			return $this->$class = ClassRegistry::init('Ninja.TransactionService');
		}

		return $this->$class = ClassRegistry::init($class);

	}

	public function simpleTransaction($lambda) {
		$this->Transaction->begin();
		$result = $lambda($this);
		if (!$result) {
			$this->Transaction->rollback();
		} else {
			$this->Transaction->commit();
		}
		return $result;
	}

	public function transaction($datasources = null) {
		return $this->Transaction->setDataSources($datasources);
	}

}