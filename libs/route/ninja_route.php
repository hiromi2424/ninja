<?php

abstract class NinjaRoute extends CakeRoute {

	public function __construct($route, $defaults = array(), $options = array()) {
		parent::CakeRoute($route, $defaults, $options);
	}

	protected function _insertParams($string, $model) {
		$params = array(
			'name' => $model->name,
			'primaryKey' => $model->primaryKey,
			'displayField' => $model->displayField,
			'alias' => $model->alias,
		);

		return String::insert($string, $params);
	}

	protected function _match($params) {

		foreach ($this->defaults as $key => $value) {
			if (!array_key_exists($key, $params) || $params[$key] !== $value) {
				return false;
			}
		}

		return true;

	}

	protected function _normalizePass($params) {

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

}