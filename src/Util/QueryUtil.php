<?php

namespace Hofff\Contao\Content\Util;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class QueryUtil {

	/**
	 * @param string $sql
	 * @param array|null $placeholders
	 * @param array|null $params
	 * @return Result
	 */
	public static function query($sql, array $placeholders = null, array $params = null) {
		$placeholders === null || $sql = vsprintf($sql, $placeholders);
		return \Database::getInstance()->prepare($sql)->executeUncached($params);
	}

	/**
	 * @param mixed $params
	 * @param string $wildcard
	 * @return string
	 */
	public static function wildcards($params, $wildcard = '?') {
		return rtrim(str_repeat($wildcard . ',', count((array) $params)), ',');
	}

	/**
	 * @param integer|array<integer> $ids
	 * @return array<integer>
	 */
	public static function ids($ids) {
		return array_filter((array) $ids, function($id) { return $id >= 1; });
	}

}
