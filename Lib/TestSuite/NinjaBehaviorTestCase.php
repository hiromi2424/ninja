<?php

App::uses('NinjaTestCase', 'Ninja.TestSuite');
App::uses('Model', 'Model');
App::uses('AppModel', 'Model');
App::uses('ModelBehavior', 'Model');

abstract class NinjaBehaviorTestCase extends NinjaTestCase {

	public $Model;
	public $Behavior;
	public $behaviorName;
	public $behaviorClass;
	public $modelName;

	public $behaviorSettings = array();

	public function _determineClassName() {
		$this->behaviorName = preg_replace('/BehaviorTest(Case)?$/', '', get_class($this));
		$this->behaviorClass = $this->behaviorName . 'Behavior';

		if (class_exists($this->behaviorClass . 'MockModel')) {
			$this->modelName = $this->behaviorClass . 'MockModel';
		}

		if (class_exists('Mock' . $this->behaviorClass)) {
			$this->behaviorClass = 'Mock' . $this->behaviorClass;
			$this->behaviorName = 'Mock' . $this->behaviorName;
		} elseif (!class_exists($this->behaviorClass)) {
			App::uses($this->behaviorClass, $this->plugin . 'Model/Behavior');
		}

	}

	public function _instantiate() {
		if ($this->modelName) {
			$this->Model = ClassRegistry::init($this->modelName);
			if (!$this->Model->Behaviors->attached($this->behaviorName)) {
				$this->Model->Behaviors->load($this->plugin . $this->behaviorName, $this->behaviorSettings);
			}
			$this->Behavior = $this->_getBehavior();
		} else {
			$this->Behavior = new $this->behaviorClass;
		}
	}

	public function tearDown() {
		$this->Model = null;
		$this->Behavior = null;
		ClassRegistry::flush();

		parent::tearDown();
	}

	protected function _getBehavior() {
		return $this->Model->Behaviors->{$this->behaviorName};
	}

	protected function _reattach($config = array()) {
		$config = Hash::merge($this->behaviorSettings, $config);
		$this->Model->Behaviors->detach($this->behaviorName);
		$this->Model->Behaviors->attach($this->behaviorName, $config);
	}

}
