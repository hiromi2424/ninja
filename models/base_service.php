<?php

abstract class BaseService {

	public $catchTry = true;
	public $logException = true;

	public function __construct() {
		ClassRegistry::addObject(__CLASS__, $this);
	}

	public function __get($name) {

		$class = Inflector::classify($name);
		if ($name === 'Transaction') {
			return $this->$class = ClassRegistry::init('Ninja.TransactionService');
		}

		return $this->$class = ClassRegistry::init($class);

	}

	public function simpleTransaction($lambda, $options = array()) {
		$options += array(
			'catchTry' => $this->catchTry,
			'logException' => $this->logException,
		);

		return $this->Transaction->setService($this)->lambda($lambda, $options);

	}

	public function transaction($datasources = null) {
		return $this->Transaction->setService($this)->setDataSources($datasources);
	}

}