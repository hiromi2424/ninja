<?php 
class TemporarinessSchema extends CakeSchema {

	public $name = 'Temporariness';

	public $temporarinesses = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'key' => 'primary', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'value' => array('type' => 'binary', 'null' => false, 'default' => NULL),
		'expires' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'index'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'expires' => array('column' => 'expires', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

}
