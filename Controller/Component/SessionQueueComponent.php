<?php

class SessionQueueComponent extends Component {

	public $sessionBaseKey = 'SessionQueue';
	public $sessionKey = 'default';

	public $components = array('Session');

	public function push($data) {
		$queue = $this->read();

		$queue[] = $data;
		$this->write($queue);
		return true;
	}

	public function pop() {
		$queue = $this->read();

		$data = array_pop($queue, $data);
		$this->write($queue);

		return $data;
	}

	public function read($index = null) {
		if ($index === null) {
			return $this->_init();
		}
		return $this->Session->read($this->sessionKey($index));
	}

	public function sessionKey($index = null) {
		$key = $this->sessionBaseKey . '.' . $this->sessionKey;
		if ($index !== null) {
			$key .= ".$index";
		}
		return $key;
	}

	public function write($queue) {
		if (empty($queue)) {
			$queue = array();
		}
		return $this->Session->write($this->sessionKey(), $queue);
	}

	public function delete($index = null) {
		if ($index === null) {
			return $this->clear();
		}
		return $this->Session->delete($this->sessionKey($index));
	}

	public function search($destination) {
		$queue = $this->read();

		foreach ($queue as $index => $value) {
			if ($value == $destination) {
				return $index;
			}
		}
		return null;
	}

	public function clear() {
		return $this->Session->delete($this->sessionKey());
	}

	protected function _init() {
		$queue = $this->Session->read($this->sessionKey());
		if (empty($queue)) {
			$queue = array();
		}

		return $queue;
	}
}