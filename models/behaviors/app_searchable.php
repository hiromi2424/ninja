<?php

// requires Search plugin.
App::import('Behavior', 'Search.Searchable');

class AppSearchableBehavior extends SearchableBehavior {

/**
 * $field might be useful for considering to create conditions.
 *
 * @Override
 */
	protected function _addCondQuery(Model $model, &$conditions, $data, $field) {
		if ((method_exists($model, $field['method']) || $this->__checkBehaviorMethods($model, $field['method'])) && !empty($data[$field['name']])) {
			$conditionsAdd = $model->{$field['method']}($data, $field);
			$conditions = array_merge($conditions, (array)$conditionsAdd);
		}
		return $conditions;
	}


}