<?php

class JsonView extends View {

	public function render() {
		if (isset($this->viewVars['json'])) {
			return $this->_renderJson($this->viewVars['json']);
		}
		return 'null';
	}

	protected function _renderJson($json) {
		$json = json_encode($json);
		//$json = JsonDebug::format($json);
		return $json;
	}

}

class JsonDebug {
	function format($json, $output = false) {
		$depth = 0;
		$length = strlen($json);
		$result = '';
		for ($n = 0; $n < $length; $n++) {
			$c = $json{$n};
			if ($c === '}' || $c === ']') {
				$depth--;
				$result .= self::__break($depth);
			}

			$result .= $c;

			if ($c === '{' || $c === '[') {
				$depth++;
				$result .= self::__break($depth);
			}
			if ($c === ',') {
				$result .= self::__break($depth);
			}
		}
		if ($output) {
			echo $result;
		}
		return $result;
	}

	function __break($depth) {
		return "\n" . str_repeat("\t", $depth);
	}
}