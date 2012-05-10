<?php


class TemporarinessFixture extends CakeTestFixture {

	public $name = 'Temporariness';

	public $fields = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'value' => array('type' => 'binary', 'null' => false, 'default' => NULL),
		'expires' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'index'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'expires' => array('column' => 'expires', 'unique' => 0))
	);

}
