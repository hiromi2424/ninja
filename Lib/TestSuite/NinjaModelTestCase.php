<?php

App::uses('NinjaTestCase', 'Ninja.TestSuite');
App::uses('Model', 'Model');
App::uses('AppModel', 'Model');

abstract class NinjaModelTestCase extends NinjaTestCase {

	public $fixtures = array('IMPORT' => 'default');
	public $modelName;
	public $modelClass;

	protected function _determineClassName() {
		$this->modelName = str_replace('TestCase', '', get_class($this));
		$this->modelClass = $this->modelName;

		if (class_exists('Test' . $this->modelClass)) {
			$this->modelClass = 'Test' . $this->modelClass;
		}
	}

	protected function _instantiate() {
		$this->{$this->modelName} = ClassRegistry::init($this->plugin . $this->modelClass);
	}

	public function tearDown() {
		unset($this->{$this->modelName});
		ClassRegistry::flush();

		parent::tearDown();
	}

	public function _repeat($field, $name = 'maxlength', $additional = 0, $char = 'a') {
		return $this->_repeatInternal($this->{$this->modelName}, $field, $name, $additional, $char);
	}

	protected function _repeatInternal($model, $field, $name, $additional, $char) {
		return str_repeat($char, $model->getValidationParameter($field, $name) + $additional);
	}

	protected function _assertValidation($field, $rule, $model = null) {
		if ($model === null) {
			$model = $this->{$this->modelName};
		} else {
			if (is_string($model)) {
				if (isset($this->{$this->modelName}->$model)) {
					$model = $this->{$this->modelName}->$model;
				} else {
					$model = ClassRegistry::init($model);
				}
			}
		}

		$trace = debug_backtrace(false);
		$this->assertEqual($model->validationErrors[$field], $model->getValidationMessage($field, $rule), '%s: at ' . $trace[0]['file'] . ' line ' . $trace[0]['line']);
	}
}