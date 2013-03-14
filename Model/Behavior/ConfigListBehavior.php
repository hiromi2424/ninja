<?php

class ConfigListBehavior extends ModelBehavior {

	public $settings = array();
	public static $defaultSettings = array(
		'separator' => '{{,}}',
		'fields' => true,
		'config' => ':class.Lists',
	);

	private $__lists;

	public function setup(Model $Model, $settings = array()) {
		$this->settings[$Model->name] = array_merge(self::$defaultSettings, (array)$settings);
	}

	public function afterFind(Model $Model, $results, $primary) {

		if (!empty($results[0][$Model->alias])) {

			extract($this->settings[$Model->name]);
			$this->__lists = Configure::read($this->_compileConfigureName($Model, $config));
			foreach ($results as $index => $result) {
				if (isset($result[$Model->alias]) && is_array($result[$Model->alias])) {
					$results[$index][$Model->alias] = $this->_convertResult($Model, $results[$index][$Model->alias]);
				}
			}
			$this->__lists = null;
		}
		return $results;
	}

	public function beforeSave(Model $Model) {
		if (isset($Model->data[$Model->alias])) {
			$this->__lists = Configure::read($this->_compileConfigureName($Model, $this->settings[$Model->name]['config']));
			$Model->data[$Model->alias] = $this->_convertData($Model, $Model->data[$Model->alias]);
			$this->__lists = null;
		}
		return true;
	}

	public function inConfigList(Model $Model, $check) {
		extract($this->settings[$Model->name]);
		$this->__lists = Configure::read($this->_compileConfigureName($Model, $config));

		list($field, $values) = each($check);
		foreach ((array)$values as $index => $value) {
			if (!isset($this->__lists[self::_listName($field)][$value])) {
				return false;
			} 
		}
		return true;
	}

	protected function _convertResult(Model $Model, $result) {
		extract($this->settings[$Model->name]);

		foreach ($result as $field => $row) {
			if ($this->__isTarget($field, $row, $this->settings[$Model->name], 'result')) {
				$result[$field] = $this->_value2array($field, $row, $this->settings[$Model->name]);
			}
		}
		return $result;
	}

	protected function _convertData(Model $Model, $data) {
		extract($this->settings[$Model->name]);

		foreach ($data as $field => $row) {
			if ($this->__isTarget($field, $row, $this->settings[$Model->name], 'data')) {
				$data[$field] = $this->_array2value($field, $row, $this->settings[$Model->name]);
			}
		}
		return $data;
	}

	private function __isTarget($field, $row, $config, $type) {
		extract($config);

		if (!isset($this->__lists[self::_listName($field)])) {
			return false;
		}

		if ($fields === true) {
			if ($type === 'result') {
				if (!is_string($row)) {
					return false;
				}

				return (false !== strpos($row, $separator)) || (in_array($row, $this->__lists[self::_listName($field)]));
			} else {
				if (!is_array($row)) {
					return false;
				}

				return count(array_intersect($row, array_keys($this->__lists[self::_listName($field)]))) !== 0;
			}
		} else {
			return in_array($field, (array)$fields);
		}
	}

	protected static function _listName($field) {
		return Inflector::variable(Inflector::pluralize($field));
	}

	protected function _value2array($field, $value, $config) {
		$elements = explode($config['separator'], $value);
		foreach ($elements as $index => $element) {
			if (false !== ($key = array_search($element, $this->__lists[$this->_listName($field)]))) {
				$elements[$index] = $key;
			}
		}
		return $elements;
	}

	protected function _array2value($field, $array, $config) {
		foreach ($array as $index => $element) {
			if (isset($this->__lists[$this->_listName($field)][$element])) {
				$array[$index] = $this->__lists[$this->_listName($field)][$element];
			}
		}
		return implode($config['separator'], $array);
	}

	protected function _compileConfigureName(Model $Model, $config) {
		$params = array(
			'class' => get_class($Model),
			'alias' => $Model->alias,
		);
		return String::insert($config, $params);
	}

}