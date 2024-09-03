<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Util;

final readonly class LanguageRelationDetector
{
    /** @param array<string, mixed> $bundles */
    public function __construct(private array $bundles)
    {
    }

    public function isActive(): bool
    {
        return isset($this->bundles['HofffContaoLanguageRelationsBundle']);
    }
}
