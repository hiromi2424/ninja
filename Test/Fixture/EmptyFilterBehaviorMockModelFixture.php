<?php

class EmptyFilterBehaviorMockModelFixture extends CakeTestFixture {

	public $name = 'EmptyFilterBehaviorMockModel';

	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'string' => array('type' => 'string'),
		'text' => array('type' => 'text'),
		'integer' => array('type' => 'integer'),
		'float' => array('type' => 'float'),
		'datetime' => array('type' => 'datetime'),
		'timestamp' => array('type' => 'timestamp'),
		'time' => array('type' => 'time'),
		'date' => array('type' => 'date'),
		'binary' => array('type' => 'binary'),
		'boolean' => array('type' => 'boolean'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci')
	);
}
