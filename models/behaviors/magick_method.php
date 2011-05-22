<?php

class MagickMethodBehavior extends ModelBehavior {
	public $regex = '/^find(.+)$/';
	protected $_method = '__findMagick';

	public $callbackPrefix = 'by';

	// method was strtolower'd so this cannot handle camel cased.
	public function setup($model, $config = array()) {
		$this->_set($config);
		$this->mapMethods = array(
			$this->regex => $this->_method,
		);
	}

	protected function _matched($trace) {
		for ($i = 4; $i <= 6; $i++) {
			if (preg_match($this->regex . 'i', $trace[$i]['function'], $matched)) {
				return $matched;
			}
		}
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

	public function __findMagick(&$model) {
		$args = func_get_args();
		/* $model =& */ array_shift($args);
		/* $method = */ array_shift($args);
		$args = array_values($args);

		$matched = $this->_matched(debug_backtrace(false));
		if (empty($matched)) {
			trigger_error(__('Retriving method name failed.', true));
			return null;
		}

		list($type, $parts) = $this->_parse($matched[1]);
		if ($parts) {
			$fields = Inflector::underscore($parts);
			$or = strpos($fields, '_or_') !== false;
			$fields = explode($or ? '_or_' : '_and_', $fields);
		} else {
			$fields = array();
			$or = false;
		}
		return $this->__dispatch($model, compact('type', 'fields', 'args', 'or'));
	}

	private function __dispatch(&$model, $params) {
		extract($params);

		$scope = array();
		$offset = 0;
		foreach ($fields as $field) {
			$Field = Inflector::camelize($field);
			$callback = '__by' . $Field;
			$modelCallback = $this->callbackPrefix . $Field;
			if (method_exists($this, $callback)) {
				list($field, $value) = $this->$callback($model);
			} elseif (method_exists($model, $modelCallback)) {
				list($field, $value) = $model->$modelCallback();
			} else {
				if (!isset($args[$offset])) {
					trigger_error(sprintf(__('Missing argument %d for %s', true), $offset + 1, __METHOD__), E_USER_WARNING);
					return null;
				}
				$value = $args[$offset];
				$offset++;
			}
			$scope[$model->escapeField($field)] = $value;
		}
		$query = isset($args[$offset]) ? $args[$offset] : array();
		if ($or) {
			$scope = array('OR' => $scope);
		}
		if (!empty($scope)) {
			$query = Set::merge($query, array('conditions' => $scope));
		}
		return $model->find($type, $query);
	}

	private function __byInsertId(&$model) {
		return array($model->primaryKey, $model->getInsertId());
	}
}