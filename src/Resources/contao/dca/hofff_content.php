<?php

declare(strict_types=1);

use Hofff\Contao\Content\EventListener\ReferencesDcaListener;
use Hofff\Contao\Content\EventListener\TemplateOptionsCallback;

$GLOBALS['TL_DCA']['hofff_content']['palettes']['default']
    = ';{hofff_content_legend},hofff_content_references'
    . ';{template_legend},hofff_content_template,hofff_content_exclude_from_search,hofff_content_bypass_cache';

$GLOBALS['TL_DCA']['hofff_content']['fields']['hofff_content_references'] = [
    'label'             => &$GLOBALS['TL_LANG']['hofff_content']['references'],
    'exclude'           => true,
    'inputType'         => 'dcaWizard',
    'foreignTable'      => 'tl_hofff_content',
    'eval' => [
        'orderField' => 'sorting',
        'operations' => ['edit', 'delete', 'new'],
        'global_operations' => ['new'],
        'showOperations' => true,
        'list_callback' => [ReferencesDcaListener::class, 'referencesList'],
    ],
];

$GLOBALS['TL_DCA']['hofff_content']['fields']['hofff_content_template'] = [
    'label'                 => &$GLOBALS['TL_LANG']['hofff_content']['template'],
    'exclude'               => true,
    'inputType'             => 'select',
    'options_callback'      => [TemplateOptionsCallback::class, 'templateOptions'],
    'eval'                  => [
        'includeBlankOption'    => true,
        'tl_class'              => 'clr w50',
    ],
    'sql'                   => 'varchar(255) NOT NULL default \'\'',
];

$GLOBALS['TL_DCA']['hofff_content']['fields']['hofff_content_exclude_from_search'] = [
    'label'                 => &$GLOBALS['TL_LANG']['hofff_content']['exclude_from_search'],
    'exclude'               => true,
    'inputType'             => 'checkbox',
    'eval'                  => ['tl_class' => 'clr w50 cbx m12'],
    'sql'                   => 'char(1) NOT NULL default \'\'',
];

$GLOBALS['TL_DCA']['hofff_content']['fields']['hofff_content_bypass_cache'] = [
    'label'                 => &$GLOBALS['TL_LANG']['hofff_content']['bypass_cache'],
    'exclude'               => true,
    'inputType'             => 'checkbox',
    'eval'                  => ['tl_class' => 'w50 cbx m12'],
    'sql'                   => 'char(1) NOT NULL default \'\'',
];
