<?php

/*
	for CakePHP <= 1.3
*/

require_once(CAKE_TESTS_LIB . 'cake_test_case.php');

abstract class FixtureLoaderTestCase extends CakeTestCase {

	function _initDb() {
		parent::_initDb();

		$exclude = array();
		if(isset($this->fixtures['EXCLUDE'])) {
			$exclude = $this->fixtures['EXCLUDE'];
			unset($this->fixtures['EXCLUDE']);
		}

		if(isset($this->fixtures['IMPORT'])) {
			$from = $this->fixtures['IMPORT'];
			unset($this->fixtures['IMPORT']);
			$this->_importTables($from);
		}

		$this->fixtures = array_diff(array_unique($this->fixtures), $exclude);
	}

	function _importTables($from) {
		$defaultDb = ConnectionManager::getDataSource($from);
		foreach($defaultDb->listSources() as $table) {
			$this->fixtures[] = "app.". Inflector::singularize($table);
		}
	}

	function _loadFixtures() {
		parent::_loadFixtures();

		if (empty($this->_fixtures)) {
			$this->_fixtures = array();
		}

		foreach($this->fixtures as $fixture) {
			if(isset($this->_fixtures[$fixture])) {
				continue;
			}
			if(preg_match('/^app\.(\w+)$/', $fixture, $m)) {
				$this->_generateFixture($fixture, $m[1]);
			}
		}

		if (empty($this->_fixtures)) {
			unset($this->_fixtures);
		}
	}

	function _generateFixture($fixture, $name) {
		$Name = Inflector::camelize($name);
		$table = Inflector::tableize($Name);

		$fixtureClass = $Name . 'Fixture';
		$schemaVar = '$import';
		$schema = array('table' => $table);

		if(!class_exists($fixtureClass)) {
			$code = 'class '. $fixtureClass .' extends CakeTestFixture {';
			$code .= 'var $name = "' . $Name . '"; ';
			$code .= 'var '. $schemaVar . ' = '. var_export($schema, true) . ';}';
			eval($code);
		}
		$this->_fixtures[$fixture] =& new $fixtureClass($this->db);
		$this->_fixtureClassMap[$Name] = $fixture;
	}
}