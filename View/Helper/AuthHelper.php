<?php

class AuthHelper extends AppHelper {

	public function loggedIn() {
		return $this->user() !== null;
	}

	public function user($name = null) {
		return AuthComponent::user($name);
	}

}