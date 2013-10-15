<?php

class CommonValidationBehavior extends ModelBehavior {

	public $defaultSettings = array(
		'userModel' => 'User',
		'currentUserConfig' => 'CurrentUser.id',
		'waitDoubleCheck' => 1,
		'autoSetCreated' => true,
	);

	public static $errorMessages = array();

	public function setup(Model $model, $config = array()) {
		if (empty(self::$errorMessages)) {
			self::$errorMessages = $this->_buildDefaultErrorMessages($model);
		}

		$this->settings[$model->alias] = Set::merge($this->defaultSettings, $config);
		return true;
	}

	protected function _buildDefaultErrorMessages($model) {
		return array(
			'currentUser' => __d('ninja', 'Current user was not configured. To use %s(), make sure to write configration with "%s" key.'),
			'emptyModelId' => __d('ninja', 'The id of model %s was empty. To use %s(), make sure validator is on update'),
			'modelHasNotField' => __d('ninja', 'The %s model has not %s field'),
			'belongingModelNotFound' => __d('ninja', 'Detecting associated model and field failed: field name = :fieldName, class name = :className'),
			'emptyValue' => __d('ninja', 'The value of %s field was empty. To use %s(), make sure validator allows empty value.'),
			'foreignHasNotBelongsTo' => __d('ninja', 'The foreign model %s has not belongsTo association for %s model. To use %s(), make sure to create association for it.'),
		);
	}

	protected function _getCurrentUserId($model, $methodName) {
		$id = Configure::read($this->settings[$model->alias]['currentUserConfig']);
		if (empty($id)) {
			throw new BadMethodCallException(sprintf(self::$errorMessages['currentUser'], $methodName, $this->settings[$model->alias]['currentUserConfig']));
		}
		return $id;
	}

	public function isCurrentUser($model, $check) {
		return $this->_isCurrentUser($model, $check, __FUNCTION__);
	}

	public function isNotCurrentUser($model, $check) {
		return !$this->_isCurrentUser($model, $check, __FUNCTION__);
	}

	protected function _isCurrentUser($model, $check, $methodName) {
		list($fieldName, $user_id) = each($check);
		if ($user_id === '') {
			return true;
		} elseif (empty($user_id) || is_array($user_id)) {
			throw new BadMethodCallException(sprintf(self::$errorMessages['emptyValue'], $fieldName, $methodName));
		}

		return $user_id == $this->_getCurrentUserId($model, $methodName);
	}

	public function currentUserHas($model, $check, $className = false) {
		return $this->_currentUserHas($model, $check, $className, __FUNCTION__);
	}

	public function currentUserHasNot($model, $check, $className = false) {
		return !$this->_currentUserHas($model, $check, $className, __FUNCTION__);
	}

	protected function _currentUserHas($model, $check, $className, $methodName) {
		list($fieldName, $foreignId) = each($check);

		$foreign = $this->_detectBelongingModel($model, $fieldName, $className);

		$currentUserId = $this->_getCurrentUserId($model, $methodName);
		$userClass = get_class(ClassRegistry::init($this->settings[$model->alias]['userModel']));
		$foreign->id = $foreignId;
		foreach ($foreign->belongsTo as $alias => $assoc) {
			if ($userClass === $assoc['className']) {
				return $foreign->field($assoc['foreignKey']) == $currentUserId;
			}
		}

		throw new BadMethodCallException(sprintf(self::$errorMessages['foreignHasNotBelongsTo'], $foreign->alias, $userClass, $methodName));
	}

	protected function _getModelId($model, $methodName) {
		$modelId = $model->getID();
		if (empty($modelId)) {
			throw new BadMethodCallException(sprintf(self::$errorMessages['emptyModelId'], $model->alias, $methodName));
		}
		return $modelId;
	}

	public function hasThis($model, $check) {
		return $this->_hasThis($model, $check, __FUNCTION__);
	}

	public function hasNotThis($model, $check) {
		return !$this->_hasThis($model, $check, __FUNCTION__);
	}

	protected function _hasThis($model, $check, $methodName) {
		$modelId = $this->_getModelId($model, $methodName);

		list($fieldName, $id) = each($check);
		if (!$model->hasField($fieldName)) {
			throw new BadMethodCallException(sprintf(self::$errorMessages['modelHasNotField'], $model->alias, $fieldName));
		}

		if (!$id || is_array($id)) {
			throw new BadMethodCallException(sprintf(self::$errorMessages['emptyValue'], $fieldName, $methodName));
		}

		return $model->field($fieldName) == $id;
	}

	public function thisHas($model, $check, $className = false, $foreignKey = false) {
		return $this->_thisHas($model, $check, $className, $foreignKey, __FUNCTION__);
	}

	public function thisHasNot($model, $check, $className = false, $foreignKey = false) {
		return !$this->_thisHas($model, $check, $className, $foreignKey, __FUNCTION__);
	}

	protected function _thisHas($model, $check, $className, $foreignKey, $methodName) {
		list($fieldName, $foreignId) = each($check);
		$modelId = $this->_getModelId($model, $methodName);
		if ($foreignId === '') {
			return true;
		} elseif (empty($foreignId) || is_array($foreignId)) {
			throw new BadMethodCallException(sprintf(self::$errorMessages['emptyValue'], $fieldName, $methodName));
		}

		$foreign = $this->_detectBelongingModel($model, $fieldName, $className);

		if (empty($foreignKey)) {
			$foreignKey = Inflector::singularize($model->table) . '_id';
		}

		if (!$foreign->hasField($foreignKey)) {
			throw new BadMethodCallException(sprintf(self::$errorMessages['modelHasNotField'], $foreign->alias, $foreignKey));
		}

		$foreign->id = $foreignId;
		return $foreign->field($foreignKey) == $modelId;
	}

	public function notExists($Model) {
		return !$Model->exists();
	}

	public function existsForeign($model, $check, $className = false) {
		return $this->_existsForeign($model, $check, $className, __FUNCTION__);
	}

	public function notExistsForeign($model, $check, $className = false) {
		return !$this->_existsForeign($model, $check, $className, __FUNCTION__);
	}

	protected function _existsForeign($model, $check, $className, $methodName) {
		list($fieldName, $foreignId) = each($check);
		if ($foreignId === '') {
			return true;
		} elseif (empty($foreignId) || is_array($foreignId)) {
			throw new BadMethodCallException(sprintf(self::$errorMessages['emptyValue'], $fieldName, $methodName));
		}

		$foreign = $this->_detectBelongingModel($model, $fieldName, $className);
		$foreign->id = $foreignId;
		return $foreign->exists();
	}

	protected function _detectBelongingModel($model, $fieldName, $className) {
		if (is_array($className)) {
			$className = false;
		}

		if ($className === false) {
			foreach ($model->belongsTo as $alias => $assoc) {
				if ($fieldName == $assoc['foreignKey']) {
					$className = $assoc['className'];
					break;
				}
			}
		}

		if ($className === false || !($foreignModel = ClassRegistry::init(array('class' => $className)))) {
			throw new BadMethodCallException(String::insert(self::$errorMessages['belongingModelNotFound'], compact('fieldName', 'className')));
		}

		return $foreignModel;
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

	public function beforeValidate(Model $model, $options = array()) {
		$create =
			$this->settings[$model->alias]['autoSetCreated'] &&
			$model->hasField('created') &&
			empty($model->data[$model->alias]['created']) &&
			empty($model->id) &&
			empty($model->data[$model->alias][$model->primaryKey])
		;
		if ($create) {
			$model->set('created', $model->generateFormatedDatetime());
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
		$wait = $this->settings[$model->alias]['waitDoubleCheck'];
		if (is_numeric(end($args))) {
			$wait = intval(array_pop($args));
		}

		$fields = $args;
		if (empty($fields)) {
			$fields = array_keys($model->schema());
			$fields = array_diff($fields, array($model->primaryKey, $field));
		}
		$ok = $model->generateFormatedDatetime(strtotime($created) - $wait);
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

	public function generateFormatedDatetime($model, $now = null) {
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

	public function splitAlias($model, $field) {
		if (strpos($field, '.') === false) {
			$alias = $model->alias;
		} else {
			list($alias, $field) = explode('.', $field);
		}

		return array($alias, $field);
	}

	public function sameInput($model, $check, $field, $hash = false) {
		$value = current((array)$check);
		list($alias, $field) = $model->splitAlias($field);

		if (!isset($model->data[$alias][$field])) {
			return false;
		}

		if ($hash === true) {
			$value = AuthComponent::password($value);
		}

		return $value === $model->data[$alias][$field];
	}

	public function sameAsSavedValue($model, $check, $field, $hash = false) {
		$value = current((array)$check);

		$savedValue = $model->field($field, array($model->alias . '.id' => $model->id));

		if ($hash === true) {
			$value = AuthComponent::password($value);
		}

		return $value === $savedValue;
	}

}
