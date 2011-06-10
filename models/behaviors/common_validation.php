<?php

class CommonValidationBehavior extends ModelBehavior {

	public $user_class = 'User';
	public $current_user_config = 'CurrentUser.id';
	public $wait_double_check = 1;
	public $auto_set_created = true;

	public function setup($model, $config = array()) {
		$this->_set($config);
		return true;
	}

	public function isCurrentUser($model, $check) {
		$user_id = current($check);
		if ($user_id  !== Configure::read($this->current_user_config)) {
			return false;
		}

		$User = ClassRegistry::init($this->user_class);
		$User->id = $user_id;
		if (!$User->exists()) {
			return false;
		}
		return true;
	}

	public function isNotCurrentUser($model, $check) {
		return !$this->isCurrentUser($model, $check);
	}

	public function currentUserHas($model, $check, $class_name = false) {
		if (!Configure::read($this->current_user_config)) {
			return false;
		}
		list($field_name, $foreign_id) = each($check);

		$foreign = $this->_detectBelongingModel($model, $field_name, $class_name);
		if ($foreign === false) {
			return false;
		}
		
		$foreign->id = $foreign_id;
		foreach ($foreign->belongsTo as $alias => $assoc) {
			if ($this->user_class == $assoc['className']) {
				if( $foreign->field($assoc['foreignKey']) == Configure::read($this->current_user_config)) {
					return true;
				}
			}
		}
		return false;
	}

	public function currentUserHasNot($model, $check, $class_name = false) {
		return !$this->currentUserHas($model, $check, $class_name);
	}

	public function hasThis($model, $check) {
		if (!$model->id || is_array($model->id)) {
			return false;
		}
		list($field_name, $id) = each($check);

		if (!$id || is_array($id) || !$model->hasField($field_name)) {
			return false;
		}
		if ($model->field($field_name) != $id) {
			return false;
		}
		return true;
	}

	public function hasNotThis($model, $check) {
		if (!$model->id || is_array($model->id)) {
			return false;
		}
		list($field_name, $id) = each($check);

		if (!$id || is_array($id) || !$model->hasField($field_name)) {
			return false;
		}
		if ($model->field($field_name) == $id) {
			return false;
		}
		return true;
	}

	public function thisHas($model, $check, $class_name = false) {
		list($field_name, $foreign_id) = each($check);

		$foreign = $this->_detectBelongingModel($model, $field_name, $class_name);
		if ($foreign === false) {
			return false;
		}

		$foreignKey = Inflector::singularize($model->table) . '_id';
		if ($foreign->hasField($foreignKey)) {
			$foreign->id = $foreign_id;
			if ($foreign->field($foreignKey) == $model->id) {
				return true;
			}
		}
		return false;
	}

	public function thisHasNot($model, $check, $class_name = false) {
		list($field_name, $foreign_id) = each($check);
		if (!$foreign_id || is_array($foreign_id) || !$model->id || is_array($model->id)) {
			return false;
		}

		$foreign = $this->_detectBelongingModel($model, $field_name, $class_name);
		if ($foreign === false) {
			return false;
		}

		$foreignKey = Inflector::singularize($model->table) . '_id';
		if ($foreign->hasField($foreignKey)) {
			$foreign->id = $foreign_id;
			if ($foreign->field($foreignKey) != $model->id) {
				return true;
			}
		}
		return false;
	}

	public function notExists($Model) {
		return !$Model->exists();
	}

	public function existsForeign($model, $check, $class_name = false) {
		list($field_name, $foreign_id) = each($check);

		$foreign = $this->_detectBelongingModel($model, $field_name, $class_name);
		if ($foreign === false) {
			return false;
		}
		$foreign->id = $foreign_id;
		return $foreign->exists();
	}

	public function notExistsForeign($model, $check, $class_name = false) {
		list($field_name, $foreign_id) = each($check);
		if (!$foreign_id || is_array($foreign_id)) {
			return false;
		}


		$foreign = $this->_detectBelongingModel($model, $field_name, $class_name);
		if ($foreign === false) {
			return false;
		}
		$foreign->id = $foreign_id;
		return !$foreign->exists();
	}

	protected function _detectBelongingModel($model, $field_name, $class_name) {
		if (is_array($class_name)) {
			$class_name = false;
		}
		if ($class_name === false) {
			foreach ($model->belongsTo as $alias => $assoc) {
				if ($field_name == $assoc['foreignKey']) {
					$class_name = $assoc['className'];
					break;
				}
			}
		}
		if ($class_name === false) {
			// "アソシエーションの設定がおかしいか足りません。\$field_name = '$field_name', \$class_name = '$class_name'"
			trigger_error(String::insert(__d('ninja', 'Detecting associated model and field failed: field name = :field_name, class name = :class_name', true), compact('field_name', 'class_name')));
			return false;
		}
		$foreign = ClassRegistry::init(array('class' => $class_name));
		return $foreign;
	}

	public function maxCount($model, $check, $count = false) {
		if (is_array($count)) {
			$count = false;
		}
		if ($count === false) {
			return false;
		}

		if ($count <= $model->find('count', array('conditions' => $check, 'recursive' => -1))) {
			return false;
		}
		return true;
	}

	public function beforeValidate($model) {
		$create =
			$this->auto_set_created &&
			$model->hasField('created') &&
			empty($model->data[$model->alias]['created']) &&
			empty($model->id) &&
			empty($model->data[$model->alias][$model->primaryKey])
		;
		if ($create) {
			$model->set('created', $this->generateFormatedDatetime());
			if (!empty($model->whitelist) && !empty($model->validate['created'])) {
				$model->whitelist[] = 'created';
			}
		}
		return true;
	}

	public function checkDoubleSave($model, $check) {
		list($field, $created) = each($check);

		$args = func_get_args();
		array_shift($args);
		array_shift($args);
		if (is_array(end($args))) {
			array_pop($args);
		}
		$wait = $this->wait_double_check;
		if (is_numeric(end($args))) {
			$wait = intval(end($args));
		}

		$fields = $args;
		if (empty($fields)) {
			$fields = array_keys($model->schema());
			$fields = array_diff($fields, array($model->primaryKey, $field));
		}
		$ok = $this->generateFormatedDatetime(strtotime($created) - $wait);
		$ok = $model->deconstruct($field, $ok);

		$data = array_intersect_key($model->data[$model->alias], array_flip($fields));
		foreach ($data as $k => $v) {
			unset($data[$k]);
			$data[$model->escapeField($k)] = $v;
		}
		$data += array($model->escapeField($field) . ' >' => $ok);
		if (0 < $model->find('count', array('conditions' => $data))) {
			return false;
		}
		return true;
	}

	public function generateFormatedDatetime($now = null) {
		if ($now === null) {
			$now = time();
		}
		$time = array(
			'year' => date('Y', $now),
			'month' => date('m', $now),
			'day' => date('d', $now),
			'hour' => date('H', $now),
			'min' => date('i', $now),
			'sec' => date('s', $now),
		);
		return $time;
	}

	public function datetime($model, $check) {
		$datetime = current($check);
		$result = $model->convertDatetime($datetime);
		if ($result === false) {
			return false;
		}
		return true;
	}

	public function convertDatetime($model, $datetime) {
		return strtotime(mb_convert_kana(trim($datetime), 'a'));
	}
}