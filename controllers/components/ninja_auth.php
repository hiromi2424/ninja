<?php

App::import('Component', 'Auth');

class NinjaAuthComponent extends AuthComponent {

	public function initialize($controller, $settings = array()){
		$this->Controller = $controller;
		return parent::initialize($controller, $settings);
	}

	public function identify($data = null, $conditions = null) {
		if (empty($conditions)) {
			$conditions = $this->userScope;
		} else {
			$conditions += $this->userScope;
		}

		if ($data === null) {
			$id = $this->user('id');
		} elseif (!is_array($data)) {
			$id = $data;
		} else {
			$conditions += $data;
		}

		$model = $this->getModel();

		if (isset($id)) {
			$conditions = array($model->escapeField() => $id);
		}

		$user = $this->getModel()->find('first', array('recursive' => 0) + compact('conditions'));

		if (empty($user)) {
			return null;
		}

		$user = Set::merge($user, $user[$this->userModel]);
		unset($user[$this->userModel]);

		return $user;
	}

	function userdata() {
		$model = $this->getModel();
		$data = $model->find('first', array('conditions' => array($model->escapeField($model->primaryKey) => $this->user('id')), 'recursive' => 0));
		$data = Set::merge($data, $data[$this->userModel]);
		unset($data[$this->userModel]);
		$this->Session->write($this->sessionKey, $data);
		return $data;
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

}