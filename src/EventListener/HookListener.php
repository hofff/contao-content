<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\EventListener;

class HookListener
{
    /**
     * @param object $row
     * @param bool   $visible
     *
     * @return bool
     */
    public function isVisibleElement($row, $visible)
    {
        return $visible && ! $row->hofff_content_hide;
    }
}
