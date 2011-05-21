<?php

class NinjaTestFixture extends CakeTestFixture {

/**
 * Run before all tests execute, should return SQL statement to create table for this fixture could be executed successfully.
 *
 * @param object	$db	An instance of the database object used to create the fixture table
 * @return boolean True on success, false on failure
 * @access public
 * @ override
 */
	public function create(&$db) {
		if (isset($this->fields) && !empty($this->fields) && empty($this->fields['tableParameters']['engine'])) {
			$canUseMemory = true;
			foreach($this->fields as $field => $args) {

				if (is_string($args)) {
					$type = $args;
				} elseif (!empty($args['type'])) {
					$type = $args['type'];
				} else {
					continue;
				}

				if (in_array($type, array('blob', 'text', 'binary'))) {
					$canUseMemory = false;
					break;
				}
			}

			if ($canUseMemory) {
				$this->fields['tableParameters']['engine'] = 'MEMORY';
			}
		}

		return parent::create($db);
	}

}