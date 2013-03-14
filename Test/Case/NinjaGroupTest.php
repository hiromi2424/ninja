<?php

abstract class NinjaGroupTest extends PHPUnit_Framework_TestSuite {

	public $plugin = null;

	public function __construct() {
		$label = Inflector::humanize(Inflector::underscore(get_class($this)));
		parent::__construct($label);
	}

	public function getTestFiles($directory = null, $excludes = null) {

		if (is_array($directory)) {
			$files = array();
			foreach ($directory as $d) {
				$files = array_merge($files, $this->getTestFiles($d, $excludes));
			}
			return array_unique($files);
		}

		if ($excludes !== null) {
			$excludes = $this->getTestFiles((array)$excludes);
		}

		if ($directory === null || $directory !== realpath($directory)) {

			$basePath = $this->plugin ? App::pluginPath($this->plugin) . 'Test' . DS . 'Case' . DS : APP . 'Test' . DS . 'Case' . DS;
			$directory = str_replace(DS . DS, DS, $basePath . $directory);

		}

		$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

		$files = array();
		while ($it->valid()) {

			if (!$it->isDot()) {
				$file = $it->key();

				if (
					preg_match('|Test\.php$|', $file) &&
					$file !== __FILE__ &&
					!preg_match('|^All.+?\.php$|', basename($file)) &&
					($excludes === null || !in_array($file, $excludes))
				) {

					$files[] = $file;
				}
			}

			$it->next();
		}

		return $files;

	}

}