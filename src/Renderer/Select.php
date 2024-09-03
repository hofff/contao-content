<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Renderer;

final readonly class Select
{
    /** @param list<mixed> $params */
    public function __construct(public string $type, public string $sql, public array $params)
    {
    }
}
