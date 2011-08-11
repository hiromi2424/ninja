<?php

class ConfigViewCacheComponent extends Object {

	public $configName = 'ViewCache';
	public $useCallback = null;

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

			if (!is_null($this->useCallback) && is_array($Controller->cacheAction)) {
				foreach ($Controller->cacheAction as &$cacheSettings) {
					if (!is_array($cacheSettings)) {
						$cacheSettings = array(
							'duration' => $cacheSettings,
						);
					}
					$cacheSettings['callbacks'] = $this->useCallback;
				}
			}

		}

		return true;

	}

}