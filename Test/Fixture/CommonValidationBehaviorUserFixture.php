<?php

class CommonValidationBehaviorUserFixture extends CakeTestFixture {
	public $name = 'CommonValidationBehaviorUser';

	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci')
	);

	public $records = array(
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

