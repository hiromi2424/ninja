<?php

class PageTitleComponent extends Component {

	public function beforeRender($controller) {

		if (isset($controller->pageTitle)) {
			$title = $controller->pageTitle;
		} elseif (isset($controller->pageTitles) && array_key_exists($controller->action, $controller->pageTitles)) {
			$title = $controller->pageTitles[$controller->action];
		} elseif (isset($controller->defaultTitle)) {
			$title = $controller->defaultTitle;
		} else {
			return;
		}

		if (isset($controller->titlePrefix)) {
			$title = $controller->titlePrefix . $title;
		}

		if (isset($controller->titleSuffix)) {
			$title .= $controller->titleSuffix;
		}

		$controller->set('title_for_layout', $title);

	}
}