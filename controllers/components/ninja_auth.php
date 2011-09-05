<?php

App::import('Component', 'Auth');

class NinjaAuthComponent extends AuthComponent {

	public $recursiveForUserdata = 0;

	public $controller;

	public function initialize($controller, $settings = array()){
		$this->controller = $controller;
		return parent::initialize($controller, $settings);
	}

	public function identify($data = null, $conditions = null) {
		if (empty($conditions)) {
			$scope = $this->userScope;
		} else {
			$scope = (array)$conditions + $this->userScope;
		}

		if (empty($data)) {
			$id = $this->user('id');
		} elseif (!is_array($data)) {
			$id = $data;
		} else {
			$scope += $data;
		}

		$model = $this->getModel();

		if (!empty($id)) {
			$scope = array($model->escapeField() => $id) + $scope;
		}

		if (empty($scope)) {
			return null;
		}

		$user = $model->find('first', array(
			'recursive' => $this->recursiveForUserdata,
			'conditions' => $scope,
		));

		if (empty($user)) {
			return null;
		}

		return $this->adjustUserdataForStore($user);
	}

	public function adjustUserdataForStore($user) {
		if (!empty($user)) {
			$model = $this->getModel();
			if (!empty($user[$model->alias][$this->fields['password']])) {
				unset($user[$model->alias][$this->fields['password']]);
			}
			$user = Set::merge($user, $user[$model->alias]);
			unset($user[$model->alias]);
		}

		return $user;
	}

	public function user($key = null) {
		$user = parent::user($key);
		if ($user) {
			return $user;
		}
		if ($key !== null && $this->Session->check($this->sessionKey . '.' . $key)) {
			return $this->Session->read($this->sessionKey . '.' . $key);
		}
		return null;
	}

	public function reLogin() {
		if (!($user_id = $this->user('id'))) {
			return false;
		}

		$this->logout();
		return $this->login($user_id);
	}

}