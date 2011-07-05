<?php

App::uses('NinjaRoute', 'Ninja.Routing/Route');

class MultiSlugRoute extends NinjaRoute {

	public static $defaultOptions = array(
		'model' => null,
		'cacheConfig' => 'default',
		'identity' => ':alias',
		'urlencode' => true,
		'conditions' => array(),
	);

	public static $defaultSlugOptions = array(
		'callbacks' => array(
			'parse' => false,
			'match' => false,
		),
	);

	protected $_slugOptions = array();
	protected $_models = array();

	private $__map = null;
	private $__writeMap = false;

	protected static $_instances = array();

	protected $_identity;

	public function __construct($route, $defaults = array(), $options = array()) {

		$defaultOptions = array(
			'model' => isset($defaults['controller']) ? $defaults['controller'] : null,
		) + self::$defaultOptions;
		$options = Set::merge($defaultOptions, $options);

		parent::__construct($route, $defaults, $options);

		$this->_setSlugOptions($options);

		$this->_identity = $this->getOption('identity');
		self::$_instances[$this->_identity] = $this;

	}

	protected function _setSlugOptions() {
		foreach (Set::normalize($this->options['slugs']) as $slug => $options) {
			$defaultBySlug = array(
				'display' => $slug,
			);

			$this->_slugOptions[$slug] = Set::merge(
				array_intersect_key($this->options, array_flip(array('model', 'urlencode'))),
				$defaultBySlug,
				self::$defaultSlugOptions,
				$options
			);
		}
	}

	public function getOption($name, $options = null) {
		if ($options === null) {
			$options = $this->options;
		}

		if (!isset($options[$name])) {
			trigger_error(sprintf(__d('ninja', '%s option is not set'), $name));
			return null;
		}

		if (!is_string($options[$name]) || strpos($options[$name], ':') === false) {
			return $options[$name];
		}

		$model = $this->_getModel($options['model']);
		return $this->_insertParams($options[$name], $model);
	}

	protected function _getModel($model = null) {
		if ($model === null) {
			$model = $this->options['model'];
		}

		if ($model === null) {
			trigger_error(__d('ninja', 'Detecting model failed. you should specify the model'));
			return null;
		}

		$name = Inflector::classify($model);
		if (isset($this->_models[$name])) {
			return $this->_models[$name];
		}
		return $this->_models[$name] = ClassRegistry::init($name);
	}

	public function match($params) {
		if (!$this->_match($params)) {
			return false;
		}

		if (isset($params[0])) {
			$id = $params[0];
			if (!($slugs = $this->mapped($id))) {
				if (!$this->_validateType($this->_getModel(), $id)) {
					return false;
				}
				$data = $this->_lookup(array(
					$this->_getModel()->escapeField() => $id,
				));

				if (empty($data)) {
					return false;
				}

				$slugs = array();
				foreach ($this->_slugOptions as $slug => $options) {
					$alias = $this->_getModel($options['model'])->alias;
					$field = $this->getOption('display', $options);
					if (!isset($data[$alias][$field])) {
						return false;
					}
					$slugs[$slug] = $data[$alias][$field];
				}

				$this->map($id, $slugs);
			}

			foreach ($this->_slugOptions as $slug => $options) {
				$params[$slug] = $slugs[$slug];

				$callbacks = $this->getOption('callbacks', $options);
				if ($callbacks['match'] !== false) {
					$params[$slug] = $callbacks['match']($params[$slug]);
				}

				if ($urlencode = $this->getOption('urlencode', $options)) {
					$encodeMethod = $urlencode === true ? 'rawurlencode' : $urlencode;
					$params[$slug] = $encodeMethod($params[$slug]);
				}
			}

			unset($params[0]);
			$params = $this->_normalizePass($params);
			$url = parent::match($params);

			return $url;
		}

		return false;

	}

	public function parse($url) {
		$params = parent::parse($url); // get cake standard

		if (empty($params) || !$this->_match($params)) {
			return false;
		}

		$slugs = array();
		foreach ($this->_slugOptions as $slug => $options) {
			if (!isset($params[$slug])) {
				return false;
			}

			$slugs[$slug] = $params[$slug];
			unset($params[$slug]);

			$callbacks = $this->getOption('callbacks', $options);
			if ($callbacks['parse'] !== false) {
				$slugs[$slug] = $callbacks['parse']($slugs[$slug]);
			}
		}

		if (!($id = $this->mapped($slugs))) {
			$result = $this->_lookup($this->_generateScope($slugs));
			if (!$result) {
				return false;
			}

			$id = $result[$this->_getModel()->alias][$this->_getModel()->primaryKey];
			$this->map($id, $slugs);
		}

		if (empty($params['pass'])) {
			$params['pass'] = array();
		}
		array_unshift($params['pass'], $id);

		return $params;

	}

	protected function _generateScope($slugs) {
		$scope = array();
		foreach ($this->_slugOptions as $slug => $options) {
			$scope[$this->_getModel($options['model'])->escapeField($this->getOption('display', $options))] = $slugs[$slug];
		}
		return $scope;
	}

	protected function _lookup($scope = array()) {
		$query = Set::merge($this->generateQuery(), array('conditions' => $scope));
		$model = $this->_getModel();

		if (!($attached = $model->Behaviors->attached('Containable'))) {
			$model->Behaviors->attach('Containable');
		}

		$result = $model->find('first', $query);

		if (!$attached) {
			$model->Behaviors->detach('Containable');
		}

		return $result;
	}

	public function mapped($from) {
		$this->_loadMap();

		if (is_string($from) || is_int($from)) {
			if (array_key_exists($from, $this->__map)) {
				return $this->__map[$from];
			}
		} elseif (is_array($from)) {
			if ($id = array_search($from, $this->__map)) {
				return $id;
			}
		}

		return false;
	}

	public function map($id, $slugs) {
		$this->_loadMap();

		$this->__map[$id] = $slugs;
		$this->_writeMap();
	}

	protected function _writeMap() {
		// saves parameters for use in destruct(after model destructing, call of model might not work)
		if ($this->__writeMap === false) {
			$this->__writeMap = array(
				'cacheConfig' => $this->getOption('cacheConfig'),
				'cacheName' => $this->cacheName(),
			);
		}
	}

	protected function _loadMap() {
		if ($this->__map === null) {
			// $this->elapsed = microtime(true);
			$this->__map = Cache::read($this->cacheName(), $this->getOption('cacheConfig'));
			// $this->elapsed = microtime(true) - $this->elapsed;
			if (!$this->__map) {
				$this->__map = array();
			}
		}
	}

	public function generateQuery() {
		$baseModel = Inflector::classify($this->options['model']);
		$fields = array($this->_getModel($baseModel)->escapeField());
		$contain = array();
		foreach ($this->_slugOptions as $options) {
			$model = Inflector::classify($options['model']);
			if ($model !== $baseModel && !in_array($model, $contain)) {
				$contain[] = $model;
			}

			$model = $this->_getModel($model);
			$fields[] = $model->escapeField($this->getOption('display', $options));
		}

		$fields = array_values(array_unique($fields));

		$conditions = $this->getOption('conditions');

		return compact('fields', 'contain', 'conditions');
	}

	public function cacheName() {
		return Inflector::tableize($this->_identity);
	}

	public function __destruct() {
		if ($this->__map !== null && $this->__writeMap !== false) {
			Cache::write($this->__writeMap['cacheName'], $this->__map, $this->__writeMap['cacheConfig']);
		}

		if (isset(self::$_instances[$this->_identity]) && self::$_instances[$this->_identity] === $this) {
			unset(self::$_instances[$this->_identity]);
		}
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

	public static function readCache($identity) {
		if (isset(self::$_instances[$identity])) {
			$instance = self::$_instances[$identity];
			$instance->_loadMap();
			return $instance->__map;
		}
	}

	public static function storeCache($identity, $data) {
		if (isset(self::$_instances[$identity])) {
			$instance = self::$_instances[$identity];
			$instance->_loadMap();
			$instance->__map = $data;
			$instance->_writeMap();
			return true;
		}
	}

	public static function clearCache($identity) {
		if (isset(self::$_instances[$identity])) {
			$instance = self::$_instances[$identity];
			$instance->__map = array();
			$instance->__writeMap = false;
			return Cache::delete($instance->cacheName(), $instance->getOption('cacheConfig'));
		}
	}

	public static function clearCacheAll() {
		if (empty(self::$_instances)) {
			return null;
		}

		$result = true;
		foreach (array_keys(self::$_instances) as $identity) {
			if (!self::clearCache($identity)) {
				$result = false;
			}
		}

		return $result;
	}

	public static function flush() {
		self::$_instances = array();
	}

}