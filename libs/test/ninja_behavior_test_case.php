<?php

App::import('Lib', 'Ninja.test' . DS . 'NinjaTestCase');

abstract class NinjaBehaviorTestCase extends NinjaTestCase {

	public $Model;
	public $Behavior;
	public $behaviorName;
	public $behaviorClass;
	public $modelName;

	public function startCase() {
		$this->behaviorName = str_replace('BehaviorTestCase', '', get_class($this));
		$this->behaviorClass = $this->behaviorName . 'Behavior';

		if (class_exists($this->behaviorClass . 'MockModel')) {
			$this->modelName = $this->behaviorClass . 'MockModel';
		}

		if (class_exists('Mock' . $this->behaviorClass)) {
			$this->behaviorClass = 'Mock' . $this->behaviorClass;
			$this->behaviorName = 'Mock' . $this->behaviorName;
		} elseif (!class_exists($this->behaviorClass)) {
			App::import('Behavior', $this->plugin . $this->behaviorName);
		}


		parent::startCase();
	}

	public function startTest($method = null) {
		parent::startTest($method);

		if ($this->modelName) {
			$this->Model = ClassRegistry::init($this->modelName);
		}

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