<?php

class MagickMethodBehavior extends ModelBehavior {

	public $mapMethods = array();

	public static $_mapMethodsDef = array(
		'find' => array('/^find(.+)$/' => '_findMagick'),
		'scope' => array('/^scope(.+)$/' => '_scopeMagick'),
		'conditions' => array('/^conditionsBy(.+)$/' => '_conditionsMagick'),
		'field' => array('/^fieldBy(.+)$/' => '_fieldMagick'),
		'saveField' => array('/^saveFieldBy(.+)$/' => '_saveFieldMagick'),
		'deleteAll' => array('/^deleteAllBy(.+)$/' => '_deleteAllMagick'),
		'updateAll' => array('/^updateAllBy(.+)$/' => '_updateAllMagick'),
	);

	public static $defaultSettings = array(
		'callbackPrefix' => 'by',
		'associations' => array(
			'hasOne',
			'belongsTo',
			'hasMany',
			'hasAndBelongsToMany',
		),
	);

	public function setup(Model $Model, $config = array()) {
		if (!isset(static::$defaultSettings['map'])) {
			$mapKeys = array_keys(static::$_mapMethodsDef);
			static::$defaultSettings['map'] = array_combine($mapKeys, array_fill(0, count($mapKeys), true));
		}

		$this->settings[$Model->alias] = Set::merge(self::$defaultSettings, $config);
		$mapDefs = array_intersect_key(static::$_mapMethodsDef, array_filter($this->settings[$Model->alias]['map']));
		$this->mapMethods = call_user_func_array('array_merge', $mapDefs);
	}

	protected function _matched($regex, $method) {
		if (preg_match($regex . 'i', $method, $matched)) {
			return $matched[1];
		}
		throw new RuntimeException(__d('ninja', 'Retrieving method name failed.'));
	}

	protected function _parse($matched) {
		$parts = null;
		if (strpos($matched, 'By') !== false) {
			list($type, $parts) = explode('By', $matched);
		} else {
			$type = $matched;
		}

		if (empty($type)) {
			$type = 'first';
		} else {
			$type{0} = strtolower($type{0});
		}

		return array($type, $parts);
	}

	public function _findMagick($Model) {

		$args = func_get_args();
		/* $Model = */ array_shift($args);
		$method = array_shift($args);
		$args = array_values($args);

		$matched = $this->_matched(key(static::$_mapMethodsDef['find']), $method);
		list($type, $parts) = $this->_parse($matched);

		list($fields, $operators) = $this->_extract($parts);

		if (empty($fields)) {
			$query = array();
			if (!empty($args)) {
				$query = array_shift($args);
			}
		} else {
			list($conditions, $query) = $this->_findParams(compact('Model', 'fields', 'operators', 'args', 'method'));
			$query = Set::merge(compact('conditions'), $query);
		}

		return $Model->find($type, $query);

	}

	public function _scopeMagick($Model) {

		$args = func_get_args();
		/* $Model = */ array_shift($args);
		$method = array_shift($args);
		$args = array_values($args);

		$matched = $this->_matched(key(static::$_mapMethodsDef['scope']), $method);

		list($fields, $operators) = $this->_extract($matched);

		list($conditions, $query) = $this->_findParams(compact('Model', 'fields', 'operators', 'args', 'method'));
		$query = Set::merge(compact('conditions'), $query);

		return $query;

	}

	public function _conditionsMagick($Model) {

		$args = func_get_args();
		/* $Model = */ array_shift($args);
		$method = array_shift($args);
		$args = array_values($args);

		$matched = $this->_matched(key(static::$_mapMethodsDef['conditions']), $method);

		list($fields, $operators) = $this->_extract($matched);

		list($conditions) = $this->_findParams(compact('Model', 'fields', 'operators', 'args', 'method'));

		return $conditions;

	}

	public function _fieldMagick($Model) {

		$args = func_get_args();
		/* $Model = */ array_shift($args);
		$method = array_shift($args);
		$field = array_shift($args);
		$args = array_values($args);

		$matched = $this->_matched(key(static::$_mapMethodsDef['field']), $method);
		list($fields, $operators) = $this->_extract($matched);

		list($generatedConditions, $conditions) = $this->_findParams(compact('Model', 'fields', 'operators', 'args', 'method'));
		$conditions = Set::merge($generatedConditions, $conditions);

		return $Model->field($field, $conditions);
	}

	public function _saveFieldMagick($Model) {

		$args = func_get_args();
		/* $Model = */ array_shift($args);
		$method = array_shift($args);
		$name = array_shift($args);
		$value = array_shift($args);
		$args = array_values($args);

		$matched = $this->_matched(key(static::$_mapMethodsDef['saveField']), $method);
		list($fields, $operators) = $this->_extract($matched);

		// saveFieldById($id);
		if (count($fields) === 1 && count($operators) === 0 && $fields === array($Model->primaryKey)) {
			$Model->id = array_shift($args);
			return $Model->dispatchMethod('saveField', array_merge(array($name, $value), $args));
		}

		$passArguments = true;
		$args = $this->_findParams(compact('Model', 'fields', 'operators', 'args', 'method', 'passArguments'));
		$conditions = array_shift($args);

		$Model->id = $Model->field($Model->primaryKey, $conditions);
		array_unshift($args, $value);
		array_unshift($args, $name);
		return $Model->dispatchMethod('saveField', $args);
	}

	public function _deleteAllMagick($Model) {

		$args = func_get_args();
		/* $Model = */ array_shift($args);
		$method = array_shift($args);
		$args = array_values($args);

		$matched = $this->_matched(key(static::$_mapMethodsDef['deleteAll']), $method);
		list($fields, $operators) = $this->_extract($matched);

		$passArguments = true;
		$args = $this->_findParams(compact('Model', 'fields', 'operators', 'args', 'method', 'passArguments'));

		return $Model->dispatchMethod('deleteAll', $args);

	}

	public function _updateAllMagick($Model) {

		$args = func_get_args();
		/* $Model = */ array_shift($args);
		$method = array_shift($args);
		$fieldsArg = array_shift($args);
		$args = array_values($args);

		$matched = $this->_matched(key(static::$_mapMethodsDef['updateAll']), $method);
		list($fields, $operators) = $this->_extract($matched);

		list($generatedConditions, $conditions) = $this->_findParams(compact('Model', 'fields', 'operators', 'args', 'method'));
		$conditions = Set::merge($generatedConditions, $conditions);

		return $Model->updateAll($fieldsArg, $conditions);
	}

	protected function _extract($parts) {

		if (empty($parts)) {
			return array(array(), array());
		}

		$parts = Inflector::underscore($parts);
		$parts = explode('_', $parts);

		$elements = $this->_extractElements($parts);

		return $elements;

	}

	protected function _extractElements($parts) {

		$fields = array();
		$operators = array();

		while ($element = current($parts)) {
			if (in_array($element, array('and', 'or'))) {
				$operators[] = $element;
				next($parts);
			} else {
				$field = array();
				do {
					$field[] = $element;
					$element = next($parts);
				} while ($element && !in_array($element, array('and', 'or')));
				$fields[] = implode('_', $field);
			}
		}

		if (count($fields) - 1 !== count($operators)) {
			throw new BadMethodCallException(__d('ninja', "Trailing 'or', 'and' is not expected"));
		} elseif (count(array_unique($fields)) !== count($fields)) {
			throw new BadMethodCallException(__d('ninja', 'Scope name must be used at once'));
		}

		return array($fields, $operators);

	}

	protected function _findParams($params) {
		extract($params);

		$scopes = array();
		$offset = 0;
		foreach ($fields as $field) {

			$Field = Inflector::camelize($field);
			$callback = $this->settings[$Model->alias]['callbackPrefix'] . $Field;

			if ($Model->hasMethod($callback)) {
				$scopes = Set::merge($scopes, $Model->$callback());
			} else {
				if (!array_key_exists($offset, $args)) {
					throw new BadMethodCallException(__d('ninja', 'Missing argument %d for %s', $offset + 1, $method));
				}

				$value = $args[$offset];
				$offset++;

				if ($Model->hasField($field, true) || !($assocField = $this->_retrieveAssociatedField($Model, $field))) {
					$scopes[$Model->escapeField($field)] = $value;
				} else {
					$scopes[$assocField] = $value;
				}

			}

		}

		if (empty($operators) || !in_array('or', $operators)) {
			$criteria = $scopes;
		} else {
			$criteria = $this->_generatesOrCreteria($scopes, $operators);
		}

		$return = array($criteria);
		if (!empty($passArguments)) {
			for ($i = $offset; array_key_exists($i, $args); $i++) {
				$return[] = $args[$i];
			}
		} else {
			$return[] = isset($args[$offset]) ? $args[$offset] : array();
		}
		return $return;

	}

	protected function _retrieveAssociatedField($Model, $field) {
		$parts = explode('_', $field);
		$partsCount = count($parts);

		foreach ($Model->getAssociated() as $alias => $assoc) {

			if (in_array($assoc, $this->settings[$Model->alias]['associations'])) {
				for ($i = 1; $i <= $partsCount; $i++) {
					$search = Inflector::camelize(implode('_', array_slice($parts, 0, $i)));

					if ($alias === $search) {
						$field = implode('_', array_slice($parts, $i));
						if ($Model->$alias->hasField($field, true)) {
							return $Model->$alias->escapeField($field);
						}
					}
				}
			}

		}

		return false;
	}

	protected function _generatesOrCreteria($scopes, $operators) {

		$criteria = $scopeParts = $parts = array();

		$operator = current($operators);
		foreach ($scopes as $field => $value) {
			$parts = array_merge($parts, array($field => $value));

			if ($operator === 'and') {
				$scopeParts[] = $parts;
				$parts = array();
			}

			$operator = next($operators);
		}

		if (!empty($parts)) {
			$scopeParts[] = $parts;
		}

		foreach ($scopeParts as $parts) {
			if (count($parts) > 1) {
				$criteria[] = array('OR' => $parts);
			} else {
				$criteria[key($parts)] = current($parts);
			}
		}
		return $criteria;

	}

	public function byInsertId($Model) {
		return array($Model->escapeField($Model->primaryKey) => $Model->getInsertId());
	}

}
