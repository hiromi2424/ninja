<?php
if (!class_exists('Controller')) {
	App::import('Controller', 'Controller', false);
}

class LogMailController extends Controller {
	public $uses = array();
	public $components = array('ErrorMailer');
}

class MailLog {
	protected $_fatalTypes = array();

	public function __construct($options = array()) {
		$options += array('fatalTypes' => array('error'));
		$this->_fatalTypes = $options['fatalTypes'];
	}

	public function write($type, $message) {
		if (in_array($type, $this->_fatalTypes)) {
			$output = 'Error type:[' . ucfirst($type) . "]\n\n" . $message;

			$Controller =& new LogMailController;
			$Controller->constructClasses();
			$Controller->startupProcess();

			$Controller->ErrorMailer->send(array('message' => $output));
		}
	}
}