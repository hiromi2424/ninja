<?php

App::import('Helper', 'Html');

class NinjaHtmlHelper extends HtmlHelper {

	public $settings = array();
	public $defaultSettings = array(
		'altEqualTitle' => true,
	);

	public function __construct(View $view, $settings = array()) {
		$this->settings = (array)$settings + $this->defaultSettings;
		parent::__construct($view, $settings);
	}

	public function image($path, $options = array()) {
		if ($this->settings['altEqualTitle']) {
			if (isset($options['alt']) && !isset($options['title'])) {
				$options['title'] = $options['alt'];
			} elseif (isset($options['title']) && !isset($options['alt'])) {
				$options['alt'] = $options['title'];
			}
		}
		return parent::image($path, $options);
	}

	public function css($path, $rel = null, $options = array()) {
		$options += array('assetFilter' => true);
		if (!$options['assetFilter']) {
			$__backup = Configure::read('Asset.filter.css');
			Configure::write('Asset.filter.css', null);
		}
		unset($options['assetFilter']);

		$result = parent::css($path, $rel, $options);

		if (isset($__backup)) {
			Configure::write('Asset.filter.css', $__backup);
		}
		return $result;
	}

	public function script($url, $options = array()) {
		$options += array('assetFilter' => true);
		if (!$options['assetFilter']) {
			$__backup = Configure::read('Asset.filter.js');
			Configure::write('Asset.filter.js', null);
		}
		unset($options['assetFilter']);

		$result = parent::script($url, $options);

		if (isset($__backup)) {
			Configure::write('Asset.filter.js', $__backup);
		}
		return $result;
	}

}