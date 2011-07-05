<?php

class NinjaAuthComponentMockUserFixture extends CakeTestFixture {

	public $name = 'NinjaAuthComponentMockUser';

	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'username' => array('type' => 'string'),
		'password' => array('type' => 'string'),
		'ninja_auth_component_mock_group_id' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 10, 'key' => 'index'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'ninja_auth_component_mock_group_id' => array('column' => 'ninja_auth_component_mock_group_id', 'unique' => 0),
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci')
	);

	public $records = array(
		array(
			'id' => 1,
			'username' => 'test user1',
			'password' => 'non-hashed password(will be overwritten in test)',
			'ninja_auth_component_mock_group_id' => 1,
		),
		array(
			'id' => 2,
			'username' => 'test user2',
			'password' => 'non-hashed password(will be overwritten in test)',
			'ninja_auth_component_mock_group_id' => 2,
		),
	);

}