<?php

class NinjaAuthComponentMockGroupFixture extends NinjaTestFixture {

	public $name = 'NinjaAuthComponentMockGroup';

	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'name' => array('type' => 'string'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci')
	);

	public $records = array(
		array(
			'id' => 1,
			'name' => 'test group1',
		),
		array(
			'id' => 2,
			'name' => 'test group2',
		),
	);

}