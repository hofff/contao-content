<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

(function () {
    PaletteManipulator::create()
        ->addField('hofff_content_hide', 'publish_legend', PaletteManipulator::POSITION_APPEND)
        ->applyToPalette('default', 'tl_article');

    $GLOBALS['TL_DCA']['tl_article']['palettes']['__selector__'][] = 'hofff_content_hide';

    $GLOBALS['TL_DCA']['tl_article']['subpalettes']['hofff_content_hide'] = 'hofff_content_page_filter,hofff_content_page_filter_strategy,hofff_content_page_filter_inheritance';

    $label                                 = &$GLOBALS['TL_DCA']['tl_article']['list']['label'];
    $label['label_callback_hofff_content'] = $label['label_callback'];
    $label['label_callback']               = ['Hofff\\Contao\\Content\\DCA\\ArticleDCA', 'labelCallback'];
    unset($label);
}
)();

$GLOBALS['TL_DCA']['tl_article']['fields']['hofff_content_hide'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_article']['hofff_content_hide'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => [
        'submitOnChange' => true,
        'tl_class'       => 'clr w50 cbx',
    ],
    'sql'       => 'char(1) NOT NULL default \'\'',
];

$GLOBALS['TL_DCA']['tl_article']['fields']['hofff_content_page_filter'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_article']['hofff_content_page_filter'],
    'exclude'   => true,
    'inputType' => 'pageTree',
    'eval'      => ['multiple' => true, 'fieldType' => 'checkbox'],
    'sql'       => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_article']['fields']['hofff_content_page_filter_strategy'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_article']['hofff_content_page_filter_strategy'],
    'exclude'          => true,
    'default'          => 'blacklist',
    'inputType'        => 'select',
    'filter'           => true,
    'options'          => ['blacklist', 'whitelist'],
    'references'       => &$GLOBALS['TL_LANG']['tl_article']['hofff_content_page_filter_strategies'],
    'eval'             => ['tl_class' => 'w50'],
    'sql'              => "varchar(10) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_article']['fields']['hofff_content_page_filter_inheritance'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_article']['hofff_content_page_filter_inheritance'],
    'exclude'          => true,
    'default'          => '',
    'inputType'        => 'checkbox',
    'eval'             => ['tl_class' => 'w50 m12'],
    'sql'              => "char(1) NOT NULL default ''",
];