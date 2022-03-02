<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Util;

use Contao\Database;
use Contao\Database\Result;

use function array_filter;
use function count;
use function rtrim;
use function str_repeat;
use function vsprintf;

class QueryUtil
{
    /**
     * @param string            $sql
     * @param array<mixed>|null $placeholders
     * @param array<mixed>|null $params
     *
     * @return Result
     */
    public static function query($sql, ?array $placeholders = null, ?array $params = null)
    {
        $placeholders === null || $sql = vsprintf($sql, $placeholders);

        return Database::getInstance()->prepare($sql)->execute($params);
    }

    /**
     * @param mixed  $params
     * @param string $wildcard
     *
     * @return string
     */
    public static function wildcards($params, $wildcard = '?')
    {
        return rtrim(str_repeat($wildcard . ',', count((array) $params)), ',');
    }

    /**
     * @param int|array<int> $ids
     *
     * @return array<int>
     */
    public static function ids($ids)
    {
        return array_filter((array) $ids, static function ($rowId) {
            return $rowId >= 1;
        });
    }
}
