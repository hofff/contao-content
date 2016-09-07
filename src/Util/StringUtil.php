<?php

namespace Hofff\Contao\Content\Util;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class StringUtil {

	/**
	 * @param string $string
	 * @param integer $width
	 * @return string
	 */
	public static function tabsToSpaces($string, $width = 4) {
		return preg_replace_callback('/((?>[^\t\n\r]*))((?>\t+))/m', function($matches) use($width) {
			$align = strlen($matches[1]) % $width;
			$spaces = strlen($matches[2]) * $width;
			return $matches[1] . str_repeat(' ', $spaces - $align);
		}, $string);
	}

	/**
	 * @param array<string> $strings
	 * @param string $prefix
	 * @return array<string>
	 */
	public static function prefixEach(array $strings, $prefix) {
		return array_map(function($string) use($prefix) {
			return $prefix . $string;
		}, $strings);
	}

}
