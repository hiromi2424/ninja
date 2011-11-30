<?php

class PageTitleComponent extends Object {

	public $controller;

	public function initialize($controller, $settings = array()) {
		$this->controller = $controller;
	}

	public function beforeRender($controller) {

		$title = $this->getTitle();
		if (!$title) {
			return;
		}

		$this->controller->set('title_for_layout', $title);

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