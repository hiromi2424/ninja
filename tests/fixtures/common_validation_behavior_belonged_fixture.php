<?php

class CommonValidationBehaviorBelongedFixture extends CakeTestFixture {
	var $name = 'CommonValidationBehaviorBelonged';

	var $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'common_validation_behavior_user_id' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 10, 'key' => 'index'),
		'common_validation_behavior_mock_model_id' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 10, 'key' => 'index'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'common_validation_behavior_user_id' => array('column' => 'common_validation_behavior_user_id', 'unique' => 0),
			'common_validation_behavior_mock_model_id' => array('column' => 'common_validation_behavior_mock_model_id', 'unique' => 0),
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	var $records = array(
		array(
			'id' => 1,
			'common_validation_behavior_mock_model_id' => 1,
			'common_validation_behavior_user_id' => 2,
		),
		array(
			'id' => 2,
			'common_validation_behavior_mock_model_id' => 2,
			'common_validation_behavior_user_id' => 3,
		),
		array(
			'id' => 3,
			'common_validation_behavior_mock_model_id' => 3,
			'common_validation_behavior_user_id' => null,
		),
		array(
			'id' => 4,
			'common_validation_behavior_mock_model_id' => null,
			'common_validation_behavior_user_id' => 5,
		),
		array(
			'id' => 5,
			'common_validation_behavior_mock_model_id' => 4,
			'common_validation_behavior_user_id' => null,
		),
	);
}

