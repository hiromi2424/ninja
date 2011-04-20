<?php

class AllowDenyComponent extends Object {
	public $auth = 'Auth';
	public $allowNonPrefix = true;

	public function initialize($Controller, $settings = array()) {
		$this->_set($settings);

		if (isset($Controller->{$this->auth})) {
			$Auth = $Controller->{$this->auth};
			if ($this->allowNonPrefix && empty($Controller->params['prefix'])) {
				$Auth->allow('*');
			}

			if (!empty($Controller->loginRequired)) {
				$Auth->deny($Controller->loginRequired);
			}
		}
		return true;
	}
}