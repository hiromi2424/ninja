<?php
/**
 * Database engine for cache.
 *
 * 
 */

App::uses('ClassRegistry', 'Utility');
class DatabaseEngine extends CacheEngine {

/**
 * Settings
 *
 * - model = model name to be used for store, default => 'Temporariness'
 * - prefix = string prefix for filename, default => ''
 * - serialize = serialize the data, default => true
 * - fields = fields definition.
 *   - key = cache key name, default => 'id'
 *   - value = the value, serialized by default, default => 'value'
 *   - expires = limit time that cache key will be expired, default => 'expires'
 *
 * @var array
 */
	public $settings = array();

/**
 * The model instance to use.
 *
 * @var Model
 */
	protected $_model;

/**
 * Whether the model instance was initialized or not.
 *
 * @var boolean
 */
	protected $_initialized = false;

/**
 * Initialize the Cache Engine
 *
 * Called automatically by the cache frontend
 * To reinitialize the settings call Cache::config('EngineName', [optional] settings = array());
 *
 * @param array $setting array of setting for the engine
 * @return boolean True if the engine has been successfully initialized, false if not
 */
	public function init($settings = array()) {
		$defaults = array(
			'engine' => 'Ninja.Database',
			'model' => 'Temporariness',
			'prefix' => '',
			'serialize' => true,
			'fields' => array(
				'key' => 'id',
				'value' => 'value',
				'expires' => 'expires',
			),
		);
		parent::init(Set::merge($defaults, $settings));

		return true;
	}

/**
 * Getting model and initialize instance if not yet.
 *
 * @return Model the model instance
 */
	public function getModel() {
		if (!$this->_initialized) {
			if (!class_exists('ClassRegistry')) {
				App::import('Core', 'ClassRegistry');
			}
			$this->_model = ClassRegistry::init($this->settings['model']);
			$this->_initialized = true;
		}
		return $this->_model;
	}

/**
 * Write data for key into cache.
 *
 * @param string $key Identifier for the data
 * @param mixed $value Data to be cached
 * @param integer $duration How long to cache the data, in seconds
 * @return boolean True if the data was succesfully cached, false on failure
 */
	public function write($key, $value, $duration) {
		$model = $this->getModel();

		if (method_exists($model, 'writeCache')) {
			return $model->writeCache($engine, $key, $value, $duration);
		}

		$model->create(array(
			$this->settings['fields']['value'] => $this->serialize($value),
			$this->settings['fields']['expires'] => $duration == 0 ? 0 : $duration + time(),
		));
		if ($this->settings['fields']['key'] === $model->primaryKey) {
			$id = $key;
		} else {
			$id = $model->field($model->primaryKey, array(
				$this->settings['fields']['key'] => $key,
			));
			$model->set($this->settings['fields']['key'], $key);
		}

		if (!empty($id)) {
			$model->id = $id;
			$model->set($model->primaryKey, $id);
		}

		return !!$model->save();
	}

/**
 * Read a key from the cache
 *
 * @param string $key Identifier for the data
 * @return mixed The cached data, or false if the data doesn't exist, has expired, or if there was an error fetching it
 */
	public function read($key) {
		$model = $this->getModel();

		if (method_exists($model, 'readCache')) {
			$data = $model->readCache($engine, $key);
		} else {
			$record = $model->find('first', array(
				'fields' => array($this->settings['fields']['value']),
				'conditions' => array(
					$this->settings['fields']['key'] => $key,
					'OR' => array(
						$this->settings['fields']['expires'] => 0,
						$this->settings['fields']['expires'] . ' >= ' => time(),
					),
				),
				'recursive' => -1
			));

			if (!$record) {
				return false;
			}
			$data = $record[$model->alias][$this->settings['fields']['value']];
		}

		if (empty($data)) {
			return false;
		}

		return $this->unserialize($data);
	}

/**
 * Serializes data to store.
 *
 * @param mixed $value Data to be cached
 * @return string string serialized
 */
	public function serialize($value) {
		if (!empty($this->settings['serialize'])) {
			$value = serialize($value);
		}
		return $value;
	}

/**
 * Unserializes data come from the cache.
 *
 * @param mixed $value Data to be cached
 * @return string string serialized
 */
	public function unserialize($value) {
		if (!empty($this->settings['serialize'])) {
			$value = unserialize($value);
		}
		return $value;
	}

/**
 * Delete a key from the cache
 *
 * @param string $key Identifier for the data
 * @return boolean True if the value was succesfully deleted, false if it didn't exist or couldn't be removed
 */
	public function delete($key) {
		$model = $this->getModel();

		if (method_exists($model, 'deleteCache')) {
			return $model->deleteCache($this, $key);
		}

		if ($model->primaryKey === $this->settings['fields']['key']) {
			return $model->delete($key);
		}

		if ($id = $model->field($model->primaryKey, array(
			$this->settings['fields']['key'] => $key,
		)))  {
			return $model->delete($id);
		}

		return false;
	}

/**
 * Delete all keys from the cache
 *
 * @param boolean $check Optional - only delete expired cache items
 * @return boolean True if the cache was succesfully cleared, false otherwise
 */
	public function clear($check) {
		$model = $this->getModel();

		if (method_exists($model, 'clearCache')) {
			return $model->clearCache($this, $check);
		}

		if ($check) {
			$conditions = array(
				$this->settings['fields']['expires'] . ' != ' => 0,
				$this->settings['fields']['expires'] . ' < ' => time(),
			);
		}
		$records = $model->find('all', array(
			'fields' => array($model->primaryKey, $this->settings['fields']['key']),
			'recursive' => -1,
		) + compact('conditions'));

		$prefixLength = strlen($this->settings['prefix']);
		if (!empty($records)) {
			foreach ($records as $record) {
				if (!empty($this->settings['prefix'])) {
					if (substr($record[$model->alias][$this->settings['fields']['key']], 0, $prefixLength) !== $this->settings['prefix']) {
						continue;
					}
				}

				if (!$model->delete($record[$model->alias][$model->primaryKey])) {
					return false;
				}
			}
		}

		return true;
	}

/**
 * Garbage collection
 *
 * Permanently remove all expired and deleted data
 *
 */
	public function gc($expires = null) {
		return $this->clear(true);
	}

/**
 * Not implemented
 *
 * @param string $key
 * @param integer $offset
 * @return void
 * @throws CacheException
 */
	public function decrement($key, $offset = 1) {
		throw new CacheException(__d('cake_dev', 'Files cannot be atomically decremented.'));
	}

/**
 * Not implemented
 *
 * @param string $key
 * @param integer $offset
 * @return void
 * @throws CacheException
 */
	public function increment($key, $offset = 1) {
		throw new CacheException(__d('cake_dev', 'Files cannot be atomically incremented.'));
	}


}
