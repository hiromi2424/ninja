<?php

class SessionQueueComponent extends Component implements ArrayAccess, IteratorAggregate, Countable {

	public $sessionBaseKey = 'SessionQueue';
	public $sessionKey = 'default';

	public $components = array('Session');

	public function push($data) {
		$queue = $this->read();

		$queue[] = $data;
		return $this->write($queue);
	}

	public function unshift($data) {
		$queue = $this->read();

		array_unshift($queue, $data);
		return $this->write($queue);
	}

	public function pop() {
		$queue = $this->read();

		$data = array_pop($queue);
		$this->write($queue);

		return $data;
	}

	public function shift() {
		$queue = $this->read();

		$data = array_shift($queue);
		$this->write($queue);

		return $data;
	}

	public function read($index = null) {
		$data = $this->Session->read($this->sessionKey($index));

		if ($index === null && !is_array($data)) {
			$data = array();
		}

		return $data;
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

	protected function _delete($key) {
		return !$this->Session->check($key) || $this->Session->delete($key);
	}

	public function delete($index) {
		return $this->_delete($this->sessionKey($index));
	}

	public function search($destination, $options = array()) {
		$queue = $this->read();

		$options += array(
			'strict' => false,
			'multiple' => false,
		);

		if ($options['multiple']) {
			return array_keys($queue, $destination, $options['strict']);
		} else {
			return array_search($destination, $queue, $options['strict']);
		}

	}

	public function extract($path) {
		$queue = $this->read();
		return Set::extract($path, $queue);
	}

	public function clear() {
		return $this->delete(null);
	}

	public function purge() {
		return $this->_delete($this->sessionBaseKey);
	}

	public function isEmpty() {
		return count($this) === 0;
	}

	public function count() {
		$queue = $this->read();
		return count($queue);
	}

	public function getIterator() {
		return new ArrayIterator($this->read());
	}

	public function offsetExists($offset) {
		$queue = $this->read();
		return array_key_exists($offset, $queue);
	}

	public function offsetGet($offset) {
		return $this->read($offset);
	}

	public function offsetSet($offset, $value) {
		$queue = $this->read();
		if ($offset === null) {
			$queue[] = $value;
		} else {
			$queue[$offset] = $value;
		}
		$this->write($queue);
	}

	public function offsetUnset($offset) {
		$this->delete($offset);
	}

}