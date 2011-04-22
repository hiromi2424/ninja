<?php

App::import('Lib', 'Ninja.test/AppTestCase');

abstract class AppBehaviorTestCase extends AppTestCase {

	public $Model;
	public $Behavior;
	public $behaviorName;
	public $behaviorClass;
	public $modelName;

	public function setUp() {
		$this->behaviorName = str_replace('BehaviorTestCase', '', get_class($this));
		$this->behaviorClass = $this->behaviorName . 'Behavior';
		$this->modelName = $this->behaviorClass . 'MockModel';
		if (class_exists('Mock' . $this->behaviorClass)) {
			$this->behaviorClass = 'Mock' . $this->behaviorClass;
			$this->behaviorName = 'Mock' . $this->behaviorName;
		}
		parent::setUp();
	}

	public function startTest($method = null) {
		parent::startTest($method);

		$this->Model = ClassRegistry::init($this->modelName);
		$this->Behavior = new $this->behaviorClass;
	}

	public function endTest($method = null) {
		$this->Model = null;
		$this->Behavior = null;
		ClassRegistry::flush();

		parent::endTest($method);
	}

	protected function _getBehavior() {
		return $this->Model->Behaviors->{$this->behaviorName};
	}

	protected function _reattach($config = array()) {
		$this->Model->Behaviors->detach($this->behaviorName);
		$this->Model->Behaviors->attach($this->behaviorName, $config);
	}

}