<?php

class ConfigViewCacheComponent extends Object {

	public $configName = 'ViewCache';

	public function initialize($Controller, $settings = array()) {

		$this->_set($settings);

		return true;

	}

	public function startup($Controller) {

		$config = Configure::read($this->configName . '.' . $Controller->name);
		if ($config) {

			if (empty($Controller->cacheAction)) {
				$Controller->cacheAction = array();
			}

			$Controller->cacheAction = array_merge($config, $Controller->cacheAction);

		}

		return true;

	}

}