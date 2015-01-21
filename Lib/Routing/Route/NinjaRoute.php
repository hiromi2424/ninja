<?php

App::uses('String', 'Utility');

abstract class NinjaRoute extends CakeRoute {

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

		$defaults = $this->defaults;
		if (isset($defaults['action']) && $defaults['action'] === 'index') {
			unset($defaults['action']);
		}
		foreach ($defaults as $key => $value) {
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

	protected function _validateType($model, $id) {
		$model = $this->_getModel();
		$primaryType = $model->getColumnType($model->primaryKey);
		if ($primaryType === 'integer') {
			if (!is_numeric($id)) {
				return false;
			}
		}

		return true;
	}

}
