<?php

class CurrentUserBehavior extends ModelBehavior {

	public $auto = true;
	public $config_name = 'CurrentUser.id';
	public $foreign_key = 'user_id';

	public function setup($model, $config = array()) {
		$this->_set($config);
		return true;
	}

	public function beforeValidate($model) {
		if ($this->auto === true || $this->auto === 'set') {
			$model->setCurrentUser();
		}
		return true;
	}

	public function setCurrentUser($model) {
		$user_id = Configure::read($this->config_name);
		if (!$user_id) {
			return false;
		}
		$model->data[$model->alias][$this->foreign_key] = $user_id;
		return true;
	}

	public function beforeDelete($model, $cascade = true) {
		if ($this->auto === true || $this->auto === 'delete') {
			return $model->validateCurrentUserDelete();
		}
	}

	public function validateCurrentUserDelete($model) {
		$id = $model->id;
		$result = $model->field($this->foreign_key) == Configure::read($this->config_name);
		$model->id = $id;
		return $result;
	}
}
