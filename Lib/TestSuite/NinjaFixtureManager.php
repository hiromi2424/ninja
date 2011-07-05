<?php

App::uses('CakeFixtureManager', 'TestSuite');

class NinjaFixtureManager extends CakeFixtureManager {

	protected function _initDb() {
		parent::_initDb();
	}

	public function fixturize(CakeTestCase $test) {

		$this->_autoLoadFixtures($test);

		return parent::fixturize($test);

	}

	protected function _autoLoadFixtures(CakeTestCase $test) {

		if (empty($test->fixtures)) {
			$test->fixtures = array();
		}

		$exclude = array();
		if (isset($test->fixtures['EXCLUDE'])) {
			$exclude = $test->fixtures['EXCLUDE'];
			unset($test->fixtures['EXCLUDE']);
		}

		if (isset($test->fixtures['IMPORT'])) {
			$from = $test->fixtures['IMPORT'];
			unset($test->fixtures['IMPORT']);
			$this->_importTables($test, $from);
		}

		$test->fixtures = array_diff(array_unique($test->fixtures), $exclude);

	}

	protected function _importTables(CakeTestCase $test, $from) {

		if (empty($test->fixtures)) {
			$test->fixtures = array();
		}

		$defaultDb = ConnectionManager::getDataSource($from);
		foreach($defaultDb->listSources() as $table) {
			$test->fixtures[] = "app." . Inflector::singularize($table);
		}

	}

	protected function _loadFixtures($fixtures) {

		parent::_loadFixtures($fixtures);

		foreach($fixtures as $fixture) {

			if (isset($this->_loaded[$fixture])) {
				continue;
			}

			if (preg_match('/^app\.(\w+)$/', $fixture, $m)) {
				$this->_generateFixture($fixture, $m[1]);
			}
		}

	}

	protected function _generateFixture($fixture, $name) {

		$Name = Inflector::camelize($name);
		$table = Inflector::tableize($Name);

		$fixtureClass = $Name . 'Fixture';
		$schemaVar = '$import';
		$schema = array('table' => $table);

		if (!class_exists($fixtureClass)) {
			$code = 'class '. $fixtureClass .' extends CakeTestFixture {';
			$code .= 'var $name = "' . $Name . '"; ';
			$code .= 'var '. $schemaVar . ' = '. var_export($schema, true) . ';}';
			eval($code);
		}

		$this->_loaded[$fixture] = new $fixtureClass($this->_db);
		$this->_fixtureMap[$fixtureClass] = $this->_loaded[$fixture];

	}
}