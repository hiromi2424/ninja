<?php

App::import('Lib', 'Ninja.test' . DS . 'NinjaTestCase');

abstract class NinjaModelTestCase extends NinjaTestCase {

	public $fixtures = array('IMPORT' => 'default');
	public $modelName;

	public function setUp() {
		$this->modelName = str_replace('TestCase', '', get_class($this));
		parent::setUp();
	}

	public function startTest($method = null) {
		parent::startTest($method);

		$this->{$this->modelName} = ClassRegistry::init($this->modelName);
	}

	public function endTest($method = null) {
		unset($this->{$this->modelName});
		ClassRegistry::flush();

		parent::endTest($method);
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