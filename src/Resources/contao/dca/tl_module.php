<?php

declare(strict_types=1);

call_user_func(static function (): void {
    $builder = new Hofff\Contao\Content\DCA\DCABuilder();
    $builder->setPaletteTemplate(
        '{title_legend},name,type'
        . '%s'
        . ';{protected_legend:hide},protected'
        . ';{expert_legend:hide},guests,cssID,space'
    );
    $builder->build($GLOBALS['TL_DCA']['tl_module']);
});
