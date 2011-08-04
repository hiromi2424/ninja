<?php

class RoleAuthorizeComponent extends Object {

	public $controller;

	public function initialize($controller, $settings) {
		$this->controller = $controller;
	}

	public function authorize() {

		if (!empty($this->controller->params['prefix'])) {
			$requireAuth = $this->controller->params['prefix'];
		} elseif (isset($this->controller->requireAuth[$this->controller->action]) && is_array($this->controller->requireAuth)) {
			$requireAuth = $this->controller->requireAuth[$this->controller->action];
		} elseif (isset($this->controller->requireAuth)) {
			$requireAuth = $this->controller->requireAuth;
		}

		if (isset($requireAuth) && Configure::read('Role.level.' . $requireAuth) > $this->controller->Auth->user('Group.level')) {
			return false;

		}

		return true;
	}

}