<?php

class RoleWhitelistBehavior extends ModelBehavior {

	public $settings = array();

	protected $_defaultSetting = array(
		'fieldName' => 'whitelists',
		'default' => 'common',
		'auto' => true,
	);

	public function setup($Model, $settings = array()) {
		$this->settings[$Model->alias] = array_merge($this->_defaultSetting, (array)$settings);
		return true;
	}

	public function beforeValdiate($Model) {
		if ($this->settings[$Model->alias]['auto']) {
			$this->setWhitelistRole($Model);
		}
		return true;
	}

	public function setWhitelistRole($Model, $role = null) {
		if ($role === null) {
			if (!($role = Configure::read('CurrentUser.Group.role'))) {
				throw new Exception('ログインユーザ以外が編集しようとしました');
			}
		}

		extract($this->settings[$Model->alias]);

		if (isset($Model->{$fieldName}[$role])) {
			if (isset($Model->{$fieldName}[$default])) {
				$Model->whitelist = array_merge($Model->whitelist, $Model->{$fieldName}[$default]);
			}
			$Model->whitelist = array_merge($Model->whitelist, $Model->{$fieldName}[$role]);
			return true;
		}
		return false;
	}

}