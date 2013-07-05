<?php

class RandomGenerator {

	const ALPHABET = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	const ALPHABET_SMALL = 'abcdefghijklmnopqrstuvwxyz';
	const ALPHABET_CAPITAL = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	const NUMBER = '0123456789';
	const SYMBOL = '!"#$%&\'()-=^~\\|@`[{;+*:}],./_<>?';
	const ALPHABET_NUMBER = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	const ALPHABET_SYMBOL = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!"#$%&\'()-=^~\\|@`[{;+*:}],./_<>?';
	const HALF_SIZE = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!"#$%&\'()-=^~\\|@`[{;+*:}],./_<>?';

	public static $defaultOptions = array(
		'type' => 'f',
		'possible' => self::ALPHABET_NUMBER,
		'length' => 40,
		'generator' => 'mt_rand',
		'random_length' => false,
	);

	protected static $_options = array();

	public static function generateCharacter($possible = self::ALPHABET_NUMBER, $options = array()) {
		$generator = 'mt_rand';
		extract($options);

		return $possible{$generator(0, strlen($possible) - 1)};

	}

	public static function generate($options = array()) {

		$options = self::_normalizeOptions($options);
		extract(array_merge(self::$defaultOptions, self::$_options, $options));
		self::$_options = array();

		$unique = false;
		$first_alaphabet = false;
		for ($i = 0; $i < strlen($type); $i++) {
			switch($type[$i]) {
				case 'u':
					$unique = true;
					break;
				case 'f':
					$first_alaphabet = true;
			}
		}

		$result = '';
		$result_length = 0;
		do {

			$c = self::generateCharacter($possible, $options);
			if ($first_alaphabet && $result_length == 0 && preg_match('/[a-z]/i', $possible)) {
				if (!preg_match('/[a-z]/', strtolower($c))) {
					continue;
				}
			}
			if ($unique && strstr($result, $c)) {
				if ($result_length === strlen($possible)) {
					trigger_error('Possible characters length is less than max length');
				}
				continue;
			}

			$result .= $c;
			$result_length ++;
			if ($random_length) {
				$minLength = isset($random_length['min']) ? $random_length['min'] : 1;
				if ($minLength <= $result_length) {
					$rand = $generator(1, $length);
					if ($result_length < $rand) {
						return $result;
					}
				}
			}

		} while ($result_length < $length);

		return $result;

	}

	public static function alphabet($options = array()) {
		self::$_options['possible'] = self::ALPHABET;
		return self::generate($options);
	}

	public static function alphabetSmall($options = array()) {
		self::$_options['possible'] = self::ALPHABET_SMALL;
		return self::generate($options);
	}

	public static function alphabetCapital($options = array()) {
		self::$_options['possible'] = self::ALPHABET_CAPITAL;
		return self::generate($options);
	}

	public static function number($options = array()) {
		self::$_options['possible'] = self::NUMBER;
		return self::generate($options);
	}

	public static function symbol($options = array()) {
		self::$_options['possible'] = self::SYMBOL;
		return self::generate($options);
	}

	public static function alphabetNumber($options = array()) {
		self::$_options['possible'] = self::ALPHABET_NUMBER;
		return self::generate($options);
	}

	public static function alphabetSymbol($options = array()) {
		self::$_options['possible'] = self::ALPHABET_SYMBOL;
		return self::generate($options);
	}

	public static function halfSize($options = array()) {
		self::$_options['possible'] = self::HALF_SIZE;
		return self::generate($options);
	}

	protected static function _normalizeOptions($options) {

		if (is_numeric($options)) {
			$options = array('length' => intval($options));
		} elseif (is_string($options)) {
			$options = array('type' => $options);
		} elseif ($options === null) {
			$options = array();
		}

		if (!is_array($options)) {
			throw new InvalidArgumentException('Only array, string and numeric was expected for random generate options');
		}

		return $options;

	}

}
