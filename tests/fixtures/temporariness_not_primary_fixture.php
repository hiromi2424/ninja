<?php


class TemporarinessNotPrimaryFixture extends NinjaTestFixture {

	public $name = 'TemporarinessNotPrimary';

	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'key' => array('type' => 'string', 'null' => false, 'default' => NULL, 'key' => 'index'),
		'value' => array('type' => 'binary', 'null' => false, 'default' => NULL),
		'expires' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'index'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'key' => array('column' => 'key', 'unique' => 0), 'expires' => array('column' => 'expires', 'unique' => 0))
	);

}
