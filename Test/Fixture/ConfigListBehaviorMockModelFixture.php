<?php

class ConfigListBehaviorMockModelFixture extends CakeTestFixture {

	public $name = 'ConfigListBehaviorMockModel';

	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 10, 'key' => 'primary'),
		'normal' => array('type' => 'string'),
		'list' => array('type' => 'string', 'null' => true, 'default' => null),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci')
	);

	public $records = array(
		array(
			'id' => 1,
			'normal' => 'normal string',
			'list' => 'hoge{{,}}fuga',
		),
	);
}