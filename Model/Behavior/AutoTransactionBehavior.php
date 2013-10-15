<?php

App::uses('TransactionManager', 'Ninja.Lib');

class AutoTransactionBehavior extends ModelBehavior {

	public $started = array();
	public $settings = array();
	public $defaultSettings = array(
		'auto' => true,
	);

	public function autoTransaction($model, $auto = true) {
		$this->settings[$model->name]['auto'] = $auto;
	}

	public function setup(Model $model, $settings = array()) {
		if (!isset($this->started[$model->useDbConfig])) {
			$this->started[$model->useDbConfig] = false;
		}

		$this->settings[$model->name] = Set::merge($this->defaultSettings, $settings);
	}

	public function started($model) {
		return !empty($this->started[$model->useDbConfig]);
	}

	protected function _startedByOther($model) {
		return !$this->started($model) && TransactionManager::started($model->useDbConfig);
	}

	protected function _determineAutoTransaction($model, $start = true) {
		if ($this->settings[$model->name]['auto']) {
			if ($start) {
				return !TransactionManager::started($model->useDbConfig);
			} else {
				return !$this->_startedByOther($model);
			}
		}
		return false;
	}

	protected function _transaction($model, $start = true) {
		$this->started[$model->useDbConfig] = $start;
		return $start ? TransactionManager::begin($model->useDbConfig) : TransactionManager::commit($model->useDbConfig);
	}

	public function beforeSave(Model $model, $options = array()) {
		if ($this->_determineAutoTransaction($model, true)) {
			$this->_transaction($model);
		}
		return true;
	}

	public function afterSave(Model $model, $created, $options = array()) {
		if ($this->_determineAutoTransaction($model, false)) {
			$this->_transaction($model, false);
		}
		return true;
	}

	public function beforeDelete(Model $model, $cascade = true) {
		if ($this->_determineAutoTransaction($model, true)) {
			$this->_transaction($model);
		}
		return true;
	}

	public function afterDelete(Model $model) {
		if ($this->_determineAutoTransaction($model, false)) {
			$this->_transaction($model, false);
		}
		return true;
	}
}
