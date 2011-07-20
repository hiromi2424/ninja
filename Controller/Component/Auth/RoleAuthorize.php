<?php

class RoleAuthorizeComponent extends Object {

	public function authorize($user, $controller, $action) {
		if (!empty($controller->params['prefix'])) {
			$requireAuth = $controller->params['prefix'];
		} elseif (isset($controller->requireAuth[$this->action]) && is_array($controller->requireAuth)) {
			$requireAuth = $this->requireAuth[$this->action];
		} elseif (isset($controller->requireAuth)) {
			$requireAuth = $controller->requireAuth;
		}

		if (isset($requireAuth) && Configure::read('Role.level.' . $requireAuth) > $controller->Auth->user('Group.level')) {
			return false;

		}

		return true;
	}

}