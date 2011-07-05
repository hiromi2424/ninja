<?php

class EmptyFilterBehavior extends ModelBehavior {

	public $settings = array();
	public static $defaultSettings = array(
		'space' => '[\s\p{Zs}\p{Zl}\p{Zp}]',
		'fields' => array(),
		'autoDetect' => true,
		'autoTrim' => true,
		'autoFilter' => true,
	);

	public static $trimTypes = array(
		'string',
		'text',
		'integer',
		'float',
		'datetime',
		'timestamp',
		'time',
		'date',
	);

	public function setup($Model, $settings = array()) {
		$this->settings[$Model->alias] = array_merge(self::$defaultSettings, (array)$settings);
	}

	public function trimSpaces($Model, $data, $options = array()) {
		extract(array_merge($this->settings[$Model->alias], $options));
		if (is_string($data)) {
			return $this->_trim($data, $space);
		} elseif (is_array($data)) {
			if (empty($fields) && $autoDetect) {
				$fields = array();
				foreach ($Model->getColumnTypes() as $field => $type) {
					if (in_array($type, self::$trimTypes)) {
						$fields[] = $field;
					}
				}
			}

			foreach ($data as $key => $value) {
				if (is_array($value)) {
					if ($key === $Model->alias) {
						$data[$key] = $this->trimSpaces($Model, $value, array_merge($options, compact('fields')));
					}
					continue;
				} elseif (in_array($key, $fields, true)) {
					$data[$key] = $this->_trim($value, $space);
				}
			}
			return $data;
		}
		trigger_error(sprintf(__d('ninja', 'Could not trim %s type', true), gettype($data)));
	}

	protected function _trim($data, $space) {
		return preg_replace("/(^$space+|$space+$)/mu", '', $data);
	}

	public function beforeValidate($Model) {
		extract($this->settings[$Model->alias]);
		if ($autoTrim) {
			$Model->data = $this->trimSpaces($Model, $Model->data);
		}

		if ($autoFilter) {
			$Model->data = $this->filterEmpty($Model, $Model->data);
		}

		return true;
	}

	public function filterEmpty($Model, $data) {
		if (!is_array($data)) {
			return $data;
		}
		foreach ($data as $key => $value) {
			if (is_array($value)) {
				$data[$key] = $this->filterEmpty($Model, $value);
			}
		}
		return Set::filter($data, true);
	}
}