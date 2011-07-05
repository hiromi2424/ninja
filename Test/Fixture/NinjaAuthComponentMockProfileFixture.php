<?php

class NinjaAuthComponentMockProfileFixture extends CakeTestFixture {

	public $name = 'NinjaAuthComponentMockProfile';

	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'nickname' => array('type' => 'string'),
		'ninja_auth_component_mock_user_id' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 10, 'key' => 'index'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'ninja_auth_component_mock_user_id' => array('column' => 'ninja_auth_component_mock_user_id', 'unique' => 0),
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci')
	);

	public $records = array(
		array(
			'id' => 1,
			'nickname' => 'test nick',
			'ninja_auth_component_mock_user_id' => 1,
		),
	);

}