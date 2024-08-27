<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\EventListener;

class HookListener
{
    public function isVisibleElement(object $row, bool $visible): bool
    {
        return $visible && ! $row->hofff_content_hide;
    }
}
