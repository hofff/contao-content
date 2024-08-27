<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Util;

use function array_map;
use function preg_replace_callback;
use function str_repeat;
use function strlen;

class StringUtil
{
    public static function tabsToSpaces(string $string, int $width = 4): string
    {
        return preg_replace_callback('/((?>[^\t\n\r]*))((?>\t+))/m', static function ($matches) use ($width) {
            $align  = strlen($matches[1]) % $width;
            $spaces = strlen($matches[2]) * $width;

            return $matches[1] . str_repeat(' ', $spaces - $align);
        }, $string);
    }

    /**
     * @param array<string> $strings
     *
     * @return array<string>
     */
    public static function prefixEach(array $strings, string $prefix): array
    {
        return array_map(static function ($string) use ($prefix) {
            return $prefix . $string;
        }, $strings);
    }
}
