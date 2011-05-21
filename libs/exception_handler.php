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
			"** 予期しない例外::class\n#########\n:message\n#########\n\n:file, :line 行目\n\n* トレース：\n:trace",
			$params
		);
		return $error;

	}

	protected static function _html($params) {

		$params['message'] = nl2br($params['message']);
		$params['trace'] = nl2br($params['trace']);
		$error = String::insert(
			"<h3>予期しない例外::class</h3><p><hr /><strong>:message</strong><hr /><br />:file, :line 行目</p><h4>トレース：</h4><p>:trace</p>",
			$params
		);
		return $error;

	}

}

class NinjaException extends Exception {

	public $defaultMessage = null;

	public function __construct($message = null, $code = 0, Exception $previous = null) {
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
}

class ParseError extends NinjaException {
	public $defaultMessage = '解析エラー:';
}

class LogicError extends NinjaException {
	public $defaultMessage = '入力情報が正しくありません';
}

