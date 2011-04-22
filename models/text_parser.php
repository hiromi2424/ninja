<?php

class TextParser {

	public static function parseHtml($html) {
		$document = new DOMDocument('1.0', 'UTF-8');
		@$document->loadHTML('<?xml encoding="UTF-8">' . $html);
		return $document;
	}

	public static function parseTags($html, $tag) {
		$document = self::parseHtml($html);

		$result = array();
		foreach ($document->getElementsByTagName($tag) as $node) {
			$block = array();
			if ($node->hasAttributes()) {
				$length = $node->attributes->length;
				for ($i = 0; $i < $length; $i++) {
					$attribute = $node->attributes->item($i);
					if (isset($attribute->name)) {
						$block[$attribute->name] = $attribute->value;
					} else {
						$block[] = $attribute->value;
					}
				}
			}
			$result[] = $block;
		}
		return $result;
	}

	public static function parseElementTag($html, $tag) {
		$document = self::parseHtml($html);

		$nodes = $document->getElementsByTagName($tag);
		if ($nodes->length === 0) {
			return null;
		}

		return $nodes->item(0)->nodeValue;
	}

	public static function match($string, $pattern) {
		$args = func_get_args();
		array_unshift($args, 'preg_match');
		return call_user_func_array('self::_match', $args);
	}

	public static function matchAll($string, $pattern) {
		$args = func_get_args();
		array_unshift($args, 'preg_match_all');
		return call_user_func_array('self::_match', $args);
	}

	public static function replace($string, $pattern) {
		$args = func_get_args();
		array_unshift($args, 'preg_replace');
		return call_user_func_array('self::_match', $args);
	}

	protected static function _match($method, $string, $pattern) {
		if (func_num_args() > 3) {
			$args = func_get_args();
			$quotes = array_slice($args, 3);
			foreach ($quotes as $index => $quote) {
				$quotes[$index] = preg_quote($quote, '|');
			}
			$pattern = vsprintf($pattern, $quotes);
		}

		if ($method === 'preg_replace') {
			return $method($pattern, '', $string);
		} else {
			$method($pattern, $string, $matches);
			if ($matches) {
				array_shift($matches);
			}
			return $matches;
		}
	}

	protected static function _empty() {
		$args = func_get_args();
		foreach ($args as $arg) {
			if (empty($arg)) {
				return true;
			}
		}
		return false;
	}

	protected function _trim($data) {
		$space = '[\s\p{Zs}\p{Zl}\p{Zp}]';
		return preg_replace("/(^$space+|$space+$)/mu", '', $data);
	}

}