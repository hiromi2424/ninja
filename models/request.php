<?php

App::import('Core', 'HttpSocket');

class Request extends HttpSocket {

	public $retryLimit = 3;
	public $RequestLog;

	public $logId;

	public function get($url, $query = array(), $request = array()) {
		$log = false;
		if (array_key_exists('_log', $request)) {
			$log = $request['_log'];
			unset($request['_log']);
		}

		$i = 0;
		do {
			$response = parent::get($url, $query, $request);
		} while ($this->_shouldRetry($response) && ($i++ < $this->retryLimit));

		if ($log) {
			if ($this->RequestLog === null) {
				$this->RequestLog = ClassRegistry::init('Ninja.RequestLog');
			}
			$this->logId = $this->RequestLog->write($url, $this->request, $this->response);
		}
		return $response;
	}

	public function post($url, $query = array(), $request = array()) {
		$log = false;
		if (array_key_exists('_log', $request)) {
			$log = $request['_log'];
			unset($request['_log']);
		}

		$response = parent::post($url, $query, $request);

		if ($log) {
			if ($this->RequestLog === null) {
				$this->RequestLog = ClassRegistry::init('Ninja.RequestLog');
			}
			$this->logId = $this->RequestLog->write($url, $this->request, $this->response);
		}
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