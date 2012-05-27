<?php

class AllowDenyComponent extends Component {

	public $auth = 'Auth';
	public $allowNonPrefix = true;
	public $allowNonPrefixProperty = 'allowNonPrefix';
	public $allowProperty = 'allowActions';
	public $denyProperty = 'loginRequired';

	public $controller;
	public $Auth;

	public function initialize($controller) {
		$this->controller = $controller;
		$this->Auth = $this->controller->{$this->auth};

		$allowNonPrefix = isset($this->controller->{$this->allowNonPrefixProperty}) ? $this->controller->{$this->allowNonPrefixProperty} : $this->allowNonPrefix;

		if ($allowNonPrefix && empty($this->controller->request->params['prefix'])) {
			$this->Auth->allow();
		}

		if (!empty($this->controller->{$this->denyProperty})) {
			if ($this->controller->{$this->denyProperty} === true) {
				$this->Auth->deny();
			} else {
				$this->Auth->deny($this->controller->{$this->denyProperty});
			}
		}

		if (!empty($this->controller->{$this->allowProperty})) {
			if ($this->controller->{$this->allowProperty} === true) {
				$this->Auth->allow();
			} else {
				$this->Auth->allow($this->controller->{$this->allowProperty});
			}
		}
	}
}