<?php

class AutoSlugRoute extends CakeRoute {

	protected $_model;
	protected $_display;
	protected $_lookup;
	protected $_cacheConfig;
	protected $_recursive;
	protected $_named;
	protected $_identity;
	protected $_urlencode;

	private $__cached;

	protected static $_instances = array();

	public function __construct($route, $defaults = array(), $options = array()) {

		parent::CakeRoute($route, $defaults, $options);
		$this->_setOptions();

		self::$_instances[$this->identity] = $this;

	}

	public function __get($name) {
		$property = '_' . $name;
		if (isset($this->$property)) {
			if (is_string($this->$property)) {
				$model = $this->_getModel();
				$params = array(
					'name' => $model->name,
					'primaryKey' => $model->primaryKey,
					'displayField' => $model->displayField,
					'alias' => $model->alias,
				);
				return $this->$name = String::insert($this->$property, $params);
			} else {
				return $this->$property;
			}
		}
		return null;
	}

	protected function _setOptions() {

		$defaults = array(
			'model' => isset($this->defaults['controller']) ? $this->defaults['controller'] : null,
			'display' => ':displayField',
			'lookup' => ':primaryKey',
			'cacheConfig' => 'default',
			'recursive' => -1,
			'named' => 'slug',
			'identity' => ':name',
			'urlencode' => true,
		);

		foreach (array_merge($defaults, array_intersect_key($this->options, $defaults)) as $name => $config) {
			$this->{"_$name"} = $config;
		}

	}

	protected function _getModel() {
		return ClassRegistry::init(Inflector::classify($this->_model));
	}

	/*
	protected function _isTest() {
		return basename(ltrim(env('PHP_SELF'), '/') === 'test.php' || env('argc') > 1);
	}
	*/

	// for debug
	function _log($var) {
		Object::log(var_export($var, true), 'debug');
	}

	public function parse($url) {

		$params = parent::parse($url);
		if (empty($params[$this->named]) || !$this->_match($params)) {
			return false;
		}

		$slugs = $this->_read();
		if (false === ($id = array_search($params[$this->named], $slugs))) {

			if (!($result = $this->_lookup(array($this->display => $params[$this->named])))) {
				return false;
			}

			$id = $result[$this->_getModel()->alias][$this->lookup];

		}

		$params['pass'][0] = $id;
		unset($params[$this->named]);

		return $params;

	}

	public function match($params) {

		if (!$this->_match($params)) {
			return false;
		}

		if (isset($params[0])) {

			$id = $params[0];
			$slugs = $this->_read();

			if (isset($slugs[$id])) {
				$slug = $slugs[$id];
			} elseif ($result = $this->_lookup(array($this->lookup => $id))) {
				$slug = $result[$this->_getModel()->alias][$this->display];
			}

			if (isset($slug)) {
				$params[$this->named] = $this->_urlencode ? rawurlencode($slug) : $slug;
				unset($params[0]);
				$params = $this->__normalizePass($params);

				return parent::match($params);

			}
		}

		return false;

	}

	protected function _match($params) {

		foreach ($this->defaults as $key => $value) {
			if (!array_key_exists($key, $params) || $params[$key] !== $value) {
				return false;
			}
		}

		return true;

	}

	protected function _lookup($scope) {

		$slugs = $this->_read();
		$result = $this->_getModel()->find('first', array('conditions' => $scope, 'fields' => array($this->lookup, $this->display), 'recursive' => $this->_recursive));
		if (!empty($result)) {
			if ($slugs === null) {
				$slugs = array();
			}

			$slugs[$result[$this->_getModel()->alias][$this->lookup]] = $result[$this->_getModel()->alias][$this->display];
			if ($this->cacheConfig !== false) {
				Cache::write($this->__cacheName(), $slugs, $this->cacheConfig);
			}
		}

		return $result;

	}

	private function __normalizePass($params) {

		$result = array();
		$passes = array();

		foreach ($params as $key => $value) {
			if (is_numeric($key)) {
				$passes[] = $value;
			} else {
				$result[$key] = $value;
			}
		}

		return array_merge($result, $passes);

	}

	protected function _read() {

		if ($this->__cached) {
			return $this->__cached;
		}

		if ($this->cacheConfig !== false && ($this->__cached = Cache::read($this->__cacheName(), $this->cacheConfig))) {
			return $this->__cached;
		}

		$result = $this->_getModel()->find('list', array('fields' => array($this->lookup, $this->display), 'recursive' => $this->recursive));

		if ($this->cacheConfig !== false) {
			Cache::write($this->__cacheName(), $result, $this->cacheConfig);
			$this->__cached = $result;
		}

		return $result;

	}

	private function __cacheName() {
		return sprintf('%s_%s', $this->_getModel()->name, $this->display);
	}

	public static function cacheName($identity) {

		if (isset(self::$_instances[$identity])) {
			return self::$_instances[$identity]->__cacheName();
		}

	}

}