<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Util;

class Util
{
    public static function isLanguageRelationsLoaded(): bool
    {
        /** @psalm-suppress DeprecatedMethod */
        return ContaoUtil::isModuleLoaded('HofffContaoLanguageRelationsBundle')
            || ContaoUtil::isModuleLoaded('hofff_language_relations');
    }
}
