<?php

declare(strict_types=1);

use Hofff\Contao\Content\DCA\DCABuilder;

$GLOBALS['TL_DCA']['tl_content']['list']['sorting']['headerFields'][] = 'hofff_content_hide';

call_user_func(static function (): void {
    $builder = new DCABuilder();
    $builder->setPaletteTemplate(
        '{type_legend},type'
        . '%s'
        . ';{protected_legend:hide},protected'
        . ';{expert_legend:hide},guests,cssID,space'
        . ';{invisible_legend:hide},invisible,start,stop'
    );
    $builder->build($GLOBALS['TL_DCA']['tl_content']);
});
