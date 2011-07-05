<?php

class ElapsedHelper extends AppHelper {

	public $settings = array(
		'separator' => ' ',
		'format' => 'HMS',
		'decimal' => 3,
		'msec' => true,
	);

	public function __construct(View $view, $settings = array()) {
		$this->settings = Set::merge($this->settings, (array)$settings);
		parent::__construct($view, $settings);
	}

	public function nice($seconds, $separator = null) {
		if (is_array($separator)) {
			$settings = $separator + $this->settings;
		} else {
			$settings = $this->settings;
			if ($separator !== null) {
				$settings['separator'] = $separator;
			}
		}

		if ($settings['msec'] && $seconds < 1.0) {
			return sprintf('%d ', (int)round(1000 * $seconds)) . __d('elapsed', 'msec', true);
		}

		$time = array();
		if ($seconds >= HOUR) {
			$hours = (int)floor($seconds / HOUR);
			$time['H'] = $hours . ' ' . __d('elapsed', 'hour', true);
			$seconds -= $hours * ((double)HOUR);
		}

		if ($seconds >= MINUTE) {
			$minutes = (int)floor($seconds / MINUTE);
			$time['M'] = $minutes . ' ' . __d('elapsed', 'min', true);
			$seconds -= $minutes * ((double)MINUTE);
		}

		$time['S'] = sprintf("%.{$settings['decimal']}f", $seconds) . ' ' . __d('elapsed', 'sec', true);

		$format = strtoupper($settings['format']);
		for ($i = 0, $result = array(), $l = strlen($format); $i < $l; $i++) {
			if (isset($time[$format{$i}])) {
				$result[] = $time[$format{$i}];
			}
		}

		return implode($settings['separator'], $result);
	}

}