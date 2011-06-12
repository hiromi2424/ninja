<?php

class MenuHelper extends AppHelper {

	public $helpers = array('Html');

	protected $_data = array();

	public $defaultSettings = array(
		'wrapper_tag' => 'ul',
		'item_tag' => 'li',
		'id' => false,
		'separator' => '',
		'item_options' => null,
		'before' => '',
		'after' => '',
	);

	public $defaultItemOptions = array(
		'id' => false,
		'class' => false,
		'before' => '',
		'after' => '',
	);

	public $sectionSettings = array();

	public function configure($section, $settings = array(), $return = false) {

		$settings = Set::merge($this->defaultSettings, $settings);

		if ($return) {
			return $settings;
		} else {
			$this->sectionSettings[$section] = $settings;
		}
	}

	public function push($section, $title, $url = false, $options = array()) {

		$this->_initSection($section);
		array_push($this->_data[$section], $this->_parseArgs($title, $url, $options));

	}

	public function pop($section) {

		$this->_initSection($section);

		if (empty($this->_data[$section])) {
			return null;
		}

		return array_pop($this->_data[$section]);

	}

	public function unshift($section, $title, $url = false, $options = array()) {

		$this->_initSection($section);

		array_unshift($this->_data[$section], $this->_parseArgs($title, $url, $options));

	}

	public function shift($section) {

		$this->_initSection($section);

		if (empty($this->_data[$section])) {
			return null;
		}

		return array_shift($this->_data[$section]);

	}

	protected function _initSection($section) {

		if (!isset($this->_data[$section])) {
			$this->_data[$section] = array();
		}

	}

	protected function _parseArgs($title, $url, $options) {

		$options = Set::merge($this->defaultItemOptions, $options);
		$data = compact('title', 'url', 'options');

		return $data;

	}

	public function display($section, $settings = array()) {

		if (empty($this->_data[$section])) {
			return null;
		}

		$settings = $this->configure($section, $settings, true);
		extract($settings);

		$results = array();
		foreach ($this->_data[$section] as $data) {
			$results[] = $this->_generateItem($data['title'], $data['url'], $data['options'], $settings);
		}

		$result = implode($separator, $results);

		$id = $this->_attribute(compact('id'));
		$class = $this->_attribute(compact('class'));

		if ($wrapper_tag !== false) {
			$result = "<$wrapper_tag$id$class>$before$result$after</$wrapper_tag>";
		}

		return $result;

	}

	protected function _generateItem($title, $url, $item_options, $sectionSettings) {

		if (!empty($sectionSettings['item_options'])) {
			$item_options = Set::merge($item_options, $sectionSettings['item_options']);
			unset($sectionSettings['item_options']);
		}

		extract($sectionSettings);

		$item = $title;

		if ($url !== false) {
			$item = $this->Html->link($item, $url);
		}

		$item_before = $item_options['before'];
		$item_after = $item_options['after'];
		$item = "$item_before$item$item_after";

		if ($item_tag !== false) {
			$item_id = $this->_attribute(array('id' => $item_options['id']));
			$item_class = $this->_attribute(array('class' => $item_options['class']));
			$item = "<{$item_tag}>$item</$item_tag>";
		}

		return $item;

	}

	protected function _attribute($param) {

		if (empty($param)) {
			return null;
		}

		list($key, $value) = each($param);

		if ($value) {
			if (is_array($value)) {
				$value = implode(' ', $value);
			}
			return " $key=\"$value\"";
		}

		return null;

	}
}