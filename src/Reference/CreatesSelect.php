<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Reference;

use Hofff\Contao\Content\Renderer\Select;

interface CreatesSelect
{
    /** @param array<string, mixed> $config */
    public function createSelect(array $config, int $index, string $column): Select;
}
