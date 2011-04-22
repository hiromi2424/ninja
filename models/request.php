<?php

App::import('Core', 'HttpSocket');

class Request extends HttpSocket {

	public $retryLimit = 3;
	public $RequestLog;

	public $logId;

	public function __construct() {
		parent::__construct();
		$this->RequestLog = ClassRegistry::init('Ninja.RequestLog');
	}

	public function get($url, $query = array(), $request = array()) {
		$log = true;
		if (array_key_exists('_log', $query)) {
			$log = $query['_log'];
			unset($query['_log']);
		}

		$response = parent::get($url, $query, $request);
		if ($this->_shouldRetry($response)) {
			$i = 1;
			do {
				$response = parent::get($url, $query, $request);
			} while ($this->_shouldRetry($response) && ($i++ < $this->retryLimit));
		}
		if ($log) {
			$this->logId = $this->RequestLog->write($url, $this->request, $this->response);
		}
		return $response;
	}

	public function post($url, $query = array(), $request = array()) {
		$response = parent::post($url, $query, $request);
		$this->logId = $this->RequestLog->write($url, $this->request, $this->response);
		return $response;
	}

	protected function _shouldRetry($response) {
		return false;
	}

	protected static function _addBaseUrl($uri, $base) {
		if (is_array($uri)) {
			foreach ($uri as $key => $u) {
				$uri[$key] = self::_addBaseUrl($u, $base);
			}
			return $uri;
		}
		return $base . str_replace('//', '/', '/' . $uri);
	}

}