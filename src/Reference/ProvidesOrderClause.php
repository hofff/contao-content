<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Reference;

interface ProvidesOrderClause
{
    public function orderClause(): string;
}
