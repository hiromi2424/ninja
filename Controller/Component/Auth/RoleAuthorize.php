<?php

App::uses('BaseAuthorize', 'Controller/Component/Auth');

class RoleAuthorize extends BaseAuthorize {

	public $auth = 'Auth';
	public $configName = 'Role.level';
	public $groupIdField = 'group_id';
	public $model = 'Group';
	public $usePrefix = true;
	public $actionIsLowerCase = true;

	public function authorize($user, CakeRequest $request) {
		$action = $this->_Controller->action;
		if ($this->actionIsLowerCase) {
			$action = strtolower($action);
		}

		if (!empty($this->_Controller->requireAuth) && !is_array($this->_Controller->requireAuth)) {
			$requireAuth = $this->_Controller->requireAuth;
		} elseif (
			!empty($this->_Controller->requireAuth) &&
			is_array($this->_Controller->requireAuth) &&
			!empty($this->_Controller->requireAuth[$action])
		) {
			$requireAuth = $this->_Controller->requireAuth[$action];
		} elseif ($this->usePrefix && !empty($request->params['prefix'])) {
			$requireAuth = $request->params['prefix'];
		}

		if (isset($requireAuth)) {
			$config = Configure::read($this->configName);
			if (array_key_exists($requireAuth, $config)) {
				$userLevel = ClassRegistry::init($this->model)->field('level', array('id' => $this->_Controller->{$this->auth}->user($this->groupIdField)));
				return $config[$requireAuth] <= $userLevel;
			}

		}

		return true;
	}

}
