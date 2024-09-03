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

final class QueryUtil
{
    /**
     * @param array<mixed>|null $placeholders
     * @param array<mixed>|null $params
     */
    public static function query(string $sql, array|null $placeholders = null, array|null $params = null): Result
    {
        $placeholders === null || $sql = vsprintf($sql, $placeholders);

        return Database::getInstance()->prepare($sql)->execute($params);
    }

    /** @param list<mixed> $params */
    public static function wildcards(array $params, string $wildcard = '?'): string
    {
        return rtrim(str_repeat($wildcard . ',', count($params)), ',');
    }

    /**
     * @param int|array<int> $ids
     *
     * @return array<int>
     */
    public static function ids(int|array $ids): array
    {
        return array_filter((array) $ids, static function ($rowId) {
            return $rowId >= 1;
        });
    }
}
