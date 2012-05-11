<?php

class PageTitleComponent extends Component {

	public $controller;
	public $autoSet = true;

	public function initialize($controller, $settings = array()) {
		$this->controller = $controller;
		$this->_set($settings);
	}

	public function beforeRender($controller) {

		if (!$this->autoSet || null === ($title = $this->getTitle())) {
			return;
		}

		$varName = $this->autoSet === true ? 'title_for_layout' : $this->autoSet;

		$this->controller->set($varName, $title);

	}

	public function getTitle($options = array()) {
		$options += array(
			'action' => $this->controller->action,
			'prefix' => true,
			'suffix' => true,
		);

		if (isset($this->controller->pageTitle)) {
			$title = $this->controller->pageTitle;
		} elseif (isset($this->controller->pageTitles) && array_key_exists($options['action'], $this->controller->pageTitles)) {
			$title = $this->controller->pageTitles[$options['action']];
		} elseif (isset($this->controller->defaultTitle)) {
			$title = $this->controller->defaultTitle;
		} else {
			return;
		}

		if ($options['prefix'] && isset($this->controller->titlePrefix)) {
			$title = $this->controller->titlePrefix . $title;
		}

		if ($options['suffix'] && isset($this->controller->titleSuffix)) {
			$title .= $this->controller->titleSuffix;
		}

		return $title;

	}

}