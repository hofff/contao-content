<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\DCA;

use Contao\System;

use function call_user_func_array;
use function func_get_args;
use function sprintf;

class ArticleDCA
{
    /**
     * @param array<string,mixed> $row
     * @param string              $label
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function labelCallback($row, $label)
    {
        $callback = $GLOBALS['TL_DCA']['tl_article']['list']['label']['label_callback_hofff_content'];
        $label    = call_user_func_array(
            [System::importStatic($callback[0]), $callback[1]],
            func_get_args()
        );

        if ($row['hofff_content_hide']) {
            $label .= sprintf(
                ' <span style="color:#4b85ba;padding-left:3px">[%s]</span>',
                $GLOBALS['TL_LANG']['tl_article']['hofff_content_hide'][0]
            );
        }

        return $label;
    }
}
