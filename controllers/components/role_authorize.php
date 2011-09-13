<?php

class RoleAuthorizeComponent extends Object {

	public $auth = 'Auth';
	public $configName = 'Role.level';
	public $levelField = 'Group.level';
	public $usePrefix = true;

	public $controller;

	public function initialize($controller, $settings = array()) {
		$this->controller = $controller;
		$this->_set($settings);
	}

	public function authorize() {

		if (!empty($this->controller->requireAuth) && !is_array($this->controller->requireAuth)) {
			$requireAuth = $this->controller->requireAuth;
		} elseif (!empty($this->controller->requireAuth) && is_array($this->controller->requireAuth) && !empty($this->controller->requireAuth[$this->controller->action])) {
			$requireAuth = $this->controller->requireAuth[$this->controller->action];
		} elseif ($this->usePrefix && !empty($this->controller->params['prefix'])) {
			$requireAuth = $this->controller->params['prefix'];
		}

		if (isset($requireAuth)) {
			$config = Configure::read($this->configName);
			if (array_key_exists($requireAuth, $config)) {
				return $config[$requireAuth] <= $this->controller->{$this->auth}->user($this->levelField);
			}

		}

		return true;
	}

	public function isAuthorized() {
		return $this->authorize();
	}

}