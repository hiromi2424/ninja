<?php

class CommonValidationBehaviorMockModelFixture extends NinjaTestFixture {
	public $name = 'CommonValidationBehaviorMockModel';

	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'common_validation_behavior_user_id' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 10, 'key' => 'index'),
		'common_validation_behavior_belonged_id' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 10, 'key' => 'index'),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'common_validation_behavior_user_id' => array('column' => 'common_validation_behavior_user_id', 'unique' => 0),
			'common_validation_behavior_belonged_id' => array('column' => 'common_validation_behavior_belonged_id', 'unique' => 0),
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci')
	);

	public $records = array(
		array(
			'id' => 1,
			'common_validation_behavior_user_id' => 1,
			'common_validation_behavior_belonged_id' => null,
			'created' => '2010-10-1 00:00:00',
		),
		array(
			'id' => 2,
			'common_validation_behavior_user_id' => 2,
			'common_validation_behavior_belonged_id' => 1,
			'created' => '2010-10-1 00:00:00',
		),
		array(
			'id' => 3,
			'common_validation_behavior_user_id' => null,
			'common_validation_behavior_belonged_id' => null,
			'created' => '2010-10-1 00:00:00',
		),
		array(
			'id' => 4,
			'common_validation_behavior_user_id' => 4,
			'common_validation_behavior_belonged_id' => 2,
			'created' => '2010-10-1 00:00:00',
		),
		array(
			'id' => 5,
			'common_validation_behavior_user_id' => 5,
			'common_validation_behavior_belonged_id' => 5,
			'created' => '2010-10-1 00:00:00',
		),
		array(
			'id' => 6,
			'common_validation_behavior_user_id' => null,
			'common_validation_behavior_belonged_id' => 5,
			'created' => '2010-10-1 00:00:00',
		),
	);
}
