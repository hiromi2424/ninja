<?php
/* 
 * copy and pase following lines to bootstrap or so
 *
App::import('Lib', 'Ninja.ExceptionHandler');
set_exception_handler(array('ExceptionHandler', 'process'));
 *
 */

class ExceptionHandler extends Object {

	protected static $_instance = null;

	public static function &getInstance() {
		if (self::$_instance === null) {
			self::$_instance = new ExceptionHandler;
		}
		return self::$_instance;
	}

	public static function process($exception) {
		if (class_exists('TransactionManager')) {
			TransactionManager::rollback();
		}

		$params = array(
			'class' => get_class($exception),
			'message' => $exception->getMessage(),
			'file' => str_replace(ROOT, 'ROOT', $exception->getFile()),
			'line' => $exception->getLine(),
			'trace' => $exception->getTraceAsString(),
		);

		$textError = self::_text($params);
		Object::log($textError);

		if (php_sapi_name() == 'cli') {
			echo $textError;
		} else {
			header('Content-Type: text/html; charset=UTF-8');
			echo self::_html($params);
		}
	}

	protected static function _text($params) {

		$error = String::insert(
			__d('ninja', "** Unexpected Exception::class\n#########\n:message\n#########\n\n:file at line :line\n\n* Stack Trace:\n:trace"),
			$params
		);
		return $error;

	}

	protected static function _html($params) {

		$params['message'] = nl2br($params['message']);
		$params['trace'] = nl2br($params['trace']);
		$error = String::insert(
			__d('ninja', '<h3>Unexpected Exception::class</h3><p><hr /><strong>:message</strong><hr /><br />:file at line :line</p><h4>Stack Trace:</h4><p>:trace</p>', true),
			$params
		);
		return $error;

	}

}

class NinjaException extends Exception {

	public $defaultMessage = null;

	public function __construct($message = null, $code = 0, Exception $previous = null) {
		$this->_constructDefaultMessage($message, $code, $previous);

		if ($this->defaultMessage !== null) {
			if ($message !== null) {
				$messages = (array)$message;
				foreach ($messages as $key => $val) {
					if (!is_numeric($key)) {
						$messages[$key] = $key . ' => ' . $val;
					}
				}
				$message = $this->defaultMessage . implode("\n\t", $messages);
			} else {
				$message = $this->defaultMessage;
			}
		}
		parent::__construct($message, $code, $previous);
	}

	protected function _constructDefaultMessage($message, $code, $previous) {
		if ($detected = $this->_defaultMessage($message, $code, $previous)) {
			$this->defaultMessage = $detected;
		}
	}

	protected function _defaultMessage() {}

}

class ParseError extends NinjaException {

	protected function _defaultMessage() {
		return __d('ninja', 'Parse error', true);
	}

}

class LogicError extends NinjaException {

	protected function _defaultMessage() {
		return __d('ninja', 'Your input is not valid', true);
	}

}

