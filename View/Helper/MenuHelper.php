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
		'current' => false,
	);

	public $defaultItemOptions = array(
		'id' => false,
		'class' => false,
		'before' => '',
		'after' => '',
		'link_options' => array(),
	);

	public $sectionSettings = array();

	public function configure($section, $settings = array(), $return = false) {

		if (!isset($this->sectionSettings[$section])) {
			$this->sectionSettings[$section] = $this->defaultSettings;
		}
		$settings = Set::merge($this->sectionSettings[$section], $settings);

		if ($return) {
			return $settings;
		} else {
			$this->sectionSettings[$section] = $settings;
		}
	}

	public function push($section, $title, $url = false, $options = array()) {

		$this->_initSection($section);
		array_push($this->_data[$section], $this->_parseArgs($title, $url, $options));

		return $this;
	}

	public function pop($section) {

		$this->_initSection($section);

		if (!empty($this->_data[$section])) {
			array_pop($this->_data[$section]);
		}

		return $this;

	}

	public function unshift($section, $title, $url = false, $options = array()) {

		$this->_initSection($section);

		array_unshift($this->_data[$section], $this->_parseArgs($title, $url, $options));

		return $this;

	}

	public function shift($section) {

		$this->_initSection($section);

		if (!empty($this->_data[$section])) {
			array_shift($this->_data[$section]);
		}

		return $this;

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
		$result = "$before$result$after";

		if ($wrapper_tag !== false) {
			$id = $this->_attribute(compact('id'));
			$class = $this->_attribute(compact('class'));
			$result = "<$wrapper_tag$id$class>$result</$wrapper_tag>";
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

		if ($current && ($this->Html->url($url) === $this->here)) {
			$this->_processCurrent($current, $item, $url, $itemOptions['link_options']);
		}

		if ($url !== false) {
			$item = $this->Html->link($item, $url, $item_options['link_options']);
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

	protected function _processCurrent($current, &$item, &$url, &$options) {
		if ($current === true) {
			$current = 'disable';
		}

		if (is_callable($current)) {
			$current($item, $url, $options);
		} elseif (is_array($current)) {
			$options = Set::merge($options, $current);
		} elseif ($current === 'disable') {
			$url = false;
		} elseif ($current === 'class') {
			if (isset($options['class'])) {
				$options['class'] = (array)$options['class'];
				$options['class'][] = 'current';
			} else {
				$options['class'] = array('current');
			}
		}
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