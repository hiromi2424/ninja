<?php

class AllowDenyComponent extends Object {

	public $auth = 'Auth';
	public $allowNonPrefix = true;
	public $allowNonPrefixProperty = 'allowNonPrefix';
	public $allowProperty = 'allowActions';
	public $denyProperty = 'loginRequired';

	public $Controller;
	public $Auth;

	public function initialize($Controller, $settings = array()) {
		$this->_set($settings);
		$this->Controller = $Controller;
		$this->Auth = $this->Controller->{$this->auth};

		$allowNonPrefix = isset($this->Controller->{$this->allowNonPrefixProperty}) ? $this->Controller->{$this->allowNonPrefixProperty} : $this->allowNonPrefix;

		if ($allowNonPrefix && empty($this->Controller->params['prefix'])) {
			$this->Auth->allow('*');
		}

		if (!empty($this->Controller->{$this->denyProperty})) {
			$this->Auth->deny($this->Controller->{$this->denyProperty});
		}

		if (!empty($this->Controller->{$this->allowProperty})) {
			$this->Auth->allow($this->Controller->{$this->allowProperty});
		}
	}
}