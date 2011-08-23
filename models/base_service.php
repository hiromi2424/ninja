<?php

class BaseService {

	public $catchTry = true;

	public function __get($name) {

		$class = Inflector::classify($name);
		if ($name === 'Transaction') {
			return $this->$class = ClassRegistry::init('Ninja.TransactionService');
		}

		return $this->$class = ClassRegistry::init($class);

	}

	public function simpleTransaction($lambda, $options = array()) {
		$options += array('catchTry' => true);
		if ($options['catchTry']) {
			try {
				$result = $this->_dispatchLambda($lambda);
			} catch (Exception $e) {
				$this->Transaction->rollback();
				Object::log($e->getMessage());
				$result = false;
			}
		} else {
			$result = $this->_dispatchLambda($lambda);
		}

		return $result;
	}

	protected function _dispatchLambda($lambda) {
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