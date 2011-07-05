<?php

class RoleWhitelistBehavior extends ModelBehavior {

	public $settings = array();

	protected $_defaultSetting = array(
		'fieldName' => 'whitelists',
		'default' => 'common',
		'clear' => true,
		'auto' => true,
		'configName' => 'CurrentUser.Group.role',
	);

	public function setup($Model, $settings = array()) {
		$this->settings[$Model->alias] = array_merge($this->_defaultSetting, (array)$settings);
		return true;
	}

	public function beforeValdiate($Model) {
		if ($this->settings[$Model->alias]['auto']) {
			if ($role = Configure::read($this->settings[$Model->alias]['configName'])) {
				$this->setWhitelistRole($Model, $role);
			}
		}
		return true;
	}

	public function setWhitelistRole($Model, $role = null) {
		extract($this->settings[$Model->alias]);

		if ($clear) {
			$Model->whitelist = array();
		}

		if (isset($Model->{$fieldName}[$role])) {
			if (isset($Model->{$fieldName}[$default])) {
				$Model->whitelist = array_merge($Model->whitelist, $Model->{$fieldName}[$default]);
			}
			if ($role !== null && array_key_exists($role, $Model->{$fieldName})) {
				$Model->whitelist = array_merge($Model->whitelist, $Model->{$fieldName}[$role]);
			}
			return true;
		}
		return false;
	}

}