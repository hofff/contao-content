<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Reference;

use Hofff\Contao\Content\Renderer\Renderer;

interface CreatesRenderer
{
    /**
     * @param array<string, mixed> $reference
     * @param array<string, mixed> $config
     */
    public function createRenderer(array $reference, array $config): Renderer;
}
