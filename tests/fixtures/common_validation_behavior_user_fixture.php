<?php

class CommonValidationBehaviorUserFixture extends CakeTestFixture {
	var $name = 'CommonValidationBehaviorUser';

	var $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	var $records = array(
		array(
			'id' => 1,
		),
		array(
			'id' => 2,
		),
		array(
			'id' => 3,
		),
		array(
			'id' => 4,
		),
		array(
			'id' => 5,
		),
	);
}

