<?php

App::uses('NinjaRoute', 'Ninja.Routing/Route');

class AutoSlugRoute extends NinjaRoute {

	public static $defaultOptions = array(
		'model' => null,
		'display' => ':displayField',
		'lookup' => ':primaryKey',
		'cacheConfig' => 'default',
		'recursive' => -1,
		'named' => ':displayField',
		'identity' => ':name',
		'urlencode' => true,
	);

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

		parent::__construct($route, $defaults, $options);
		$this->_setOptions();

		self::$_instances[$this->identity] = $this;

	}

	public function __get($name) {
		$property = '_' . $name;
		if (isset($this->$property)) {
			if (is_string($this->$property)) {
				return $this->$name = $this->_insertParams($this->$property, $this->_getModel());
			} else {
				return $this->$property;
			}
		}
		return null;
	}

	protected function _setOptions() {

		$defaults = array(
			'model' => isset($this->defaults['controller']) ? $this->defaults['controller'] : null,
		) + self::$defaultOptions;

		if (isset($this->options['lookup']) && !isset($this->options['named'])) {
			$this->options['named'] = $this->options['lookup'];
		}

		foreach (array_merge($defaults, array_intersect_key($this->options, $defaults)) as $name => $config) {
			$this->{"_$name"} = $config;
		}

	}

	protected function _getModel() {
		return ClassRegistry::init(Inflector::classify($this->_model));
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

		array_unshift($params['pass'], $id);
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
			} elseif ($this->_validateType($this->_getModel(), $id) && $result = $this->_lookup(array($this->lookup => $id))) {
				$slug = $result[$this->_getModel()->alias][$this->display];
			}

			if (isset($slug)) {
				$params[$this->named] = $this->_urlencode ? rawurlencode($slug) : $slug;
				unset($params[0]);
				$params = $this->_normalizePass($params);

				return parent::match($params);

			}
		}

		return false;

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
				Cache::write($this->cacheName(), $slugs, $this->cacheConfig);
			}
		}

		return $result;

	}

	protected function _read() {

		if ($this->__cached) {
			return $this->__cached;
		}

		if ($this->cacheConfig !== false && ($this->__cached = Cache::read($this->cacheName(), $this->cacheConfig))) {
			return $this->__cached;
		}

		$result = $this->_getModel()->find('list', array('fields' => array($this->lookup, $this->display), 'recursive' => $this->recursive));

		if ($this->cacheConfig !== false) {
			Cache::write($this->cacheName(), $result, $this->cacheConfig);
			$this->__cached = $result;
		}

		return $result;

	}

	public function cacheName() {
		return sprintf('%s_%s', Inflector::underscore($this->_getModel()->alias), strtolower($this->display));
	}

	public static function getInstance($identity) {
		if (isset(self::$_instances[$identity])) {
			return self::$_instances[$identity];
		}
	}


	public static function getCacheName($identity) {
		if (isset(self::$_instances[$identity])) {
			return self::$_instances[$identity]->cacheName();
		}
	}

	public static function clearCache($identity) {
		if (isset(self::$_instances[$identity])) {
			$instance = self::$_instances[$identity];
			return Cache::delete($instance->cacheName(), $instance->cacheConfig);
		}
	}

	public static function readCache($identity) {
		if (isset(self::$_instances[$identity])) {
			$instance = self::$_instances[$identity];
			return Cache::read($instance->cacheName(), $instance->cacheConfig);
		}
	}

	public static function storeCache($identity, $data) {
		if (isset(self::$_instances[$identity])) {
			$instance = self::$_instances[$identity];
			return Cache::write($instance->cacheName(), $data, $instance->cacheConfig);
		}
	}

}