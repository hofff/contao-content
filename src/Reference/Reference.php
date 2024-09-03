<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Reference;

interface Reference
{
    public function name(): string;

    /** @param array<string, mixed> $row */
    public function backendIcon(array $row): string;

    /** @param array<string, mixed> $row */
    public function backendLabel(array $row): string;
}
