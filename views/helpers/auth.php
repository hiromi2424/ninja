<?php

class AuthHelper extends AppHelper {

	public $helpers = array('Session');

	public function loggedIn() {
		return $this->user('id') !== null;
	}

	public function user($name = null) {
		$key = 'Auth.User';
		if ($name !== null) {
			$key .= ".$name";
		}
		return $this->Session->read($key);
	}

}