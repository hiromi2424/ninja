<?php

class RoleAuthorizeComponent extends Component {

	public $auth = 'Auth';
	public $configName = 'Role.level';
	public $groupIdField = 'group_id';
	public $model = 'Group';
	public $usePrefix = true;

	public $controller;

	public function initialize($controller) {
		$this->controller = $controller;
	}

	public function authorize() {

		if (!empty($this->controller->requireAuth) && !is_array($this->controller->requireAuth)) {
			$requireAuth = $this->controller->requireAuth;
		} elseif (!empty($this->controller->requireAuth) && is_array($this->controller->requireAuth) && !empty($this->controller->requireAuth[$this->controller->action])) {
			$requireAuth = $this->controller->requireAuth[$this->controller->action];
		} elseif ($this->usePrefix && !empty($this->controller->request->params['prefix'])) {
			$requireAuth = $this->controller->request->params['prefix'];
		}

		if (isset($requireAuth)) {
			$config = Configure::read($this->configName);
			if (array_key_exists($requireAuth, $config)) {
				$userLevel = ClassRegistry::init($this->model)->field('level', array('id' => $this->controller->{$this->auth}->user($this->groupIdField)));
				return $config[$requireAuth] <= $userLevel;
			}

		}

		return true;
	}

	public function isAuthorized() {
		return $this->authorize();
	}

}