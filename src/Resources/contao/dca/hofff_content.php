<?php

declare(strict_types=1);

use Hofff\Contao\Content\DCA\DCA;
use Hofff\Contao\Content\Util\Util;

$GLOBALS['TL_DCA']['hofff_content']['palettes']['default']
    = ';{hofff_content_legend},hofff_content_references'
    . ';{template_legend},hofff_content_template,hofff_content_exclude_from_search,hofff_content_bypass_cache';

call_user_func(static function (): void {
    $excludeFromSearch   = [
        'label'     => &$GLOBALS['TL_LANG']['hofff_content']['exclude_from_search'],
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'clr w50 cbx'],
    ];
    $renderContainer     = [
        'label'     => &$GLOBALS['TL_LANG']['hofff_content']['render_container'],
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'clr w50 cbx'],
    ];
    $targetSectionFilter = [
        'label'     => &$GLOBALS['TL_LANG']['hofff_content']['target_section_filter'],
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50 cbx'],
    ];
    $cssClasses          = [
        'label'     => &$GLOBALS['TL_LANG']['hofff_content']['css_classes'],
        'inputType' => 'text',
        'eval'      => ['tl_class' => 'clr w50'],
    ];
    $cssID               = [
        'label'     => &$GLOBALS['TL_LANG']['hofff_content']['css_id'],
        'inputType' => 'text',
        'eval'      => ['tl_class' => 'w50'],
    ];
    $translate           = [
        'label'     => &$GLOBALS['TL_LANG']['hofff_content']['translate'],
        'inputType' => 'checkbox',
        'eval'      => [
            'tl_class'  => Util::isLanguageRelationsLoaded()
                ? 'w50 cbx m12'
                : 'hidden',
        ],
    ];
    $sourceSections      = [
        'label'             => &$GLOBALS['TL_LANG']['hofff_content']['source_sections'],
        'inputType'         => 'checkbox',
        'options_callback'  => DCA::getLayoutSectionOptionsCallback(),
        'eval'              => [
            'multiple'          => true,
            'tl_class'          => 'clr',
        ],
    ];

    $GLOBALS['TL_DCA']['hofff_content']['fields']['hofff_content_references'] = [
        'label'             => &$GLOBALS['TL_LANG']['hofff_content']['references'],
        'exclude'           => true,
        'inputType'         => 'selectri',
        'eval'              => [
            'doNotSaveEmpty'    => true,
            'min'               => 0,
            'max'               => PHP_INT_MAX,
            'sort'              => true,
            'canonical'         => true,
            'class'             => 'hofff-content',
            'suggestLimit'      => 10,
            'suggestionsLabel'  => &$GLOBALS['TL_LANG']['hofff_content']['suggestions'],
            'contentToggleable' => true,
            'data'              => null, // gets set by DCA builder
        ],
        'sql'               => 'blob NULL',
        'nodeTypes'         => [
            'article'           => [
                'icon_callback'     => [DCA::class, 'getArticleIcon' ],
                'label_callback'    => [DCA::class, 'getArticleLabel' ],
                'template'          => 'hofff_content_node_article',
                'fields'            => [
                    'exclude_from_search'   => $excludeFromSearch,
                    'render_container'      => $renderContainer,
                    'target_section_filter' => $targetSectionFilter,
                    'css_classes'           => $cssClasses,
                    'css_id'                => $cssID,
                ],
            ],
            'page'              => [
                'icon_callback'     => [DCA::class, 'getPageIcon' ],
                'template'          => 'hofff_content_node_page',
                'fields'            => [
                    'exclude_from_search'   => $excludeFromSearch,
                    'render_container'      => $renderContainer,
                    'target_section_filter' => $targetSectionFilter,
                    'css_classes'           => $cssClasses,
                    'translate'             => $translate,
                    'source_sections'       => $sourceSections,
                ],
            ],
            'theme'             => ['icon' => 'themes.gif'],
            'module'            => [
                'icon'              => 'modules.gif',
                'label_callback'    => [DCA::class, 'getModuleLabel' ],
                'template'          => 'hofff_content_node_module',
                'fields'            => [
                    'exclude_from_search'   => $excludeFromSearch,
                    'css_classes'           => $cssClasses,
                    'css_id'                => $cssID,
                ],
            ],
        ],
    ];
});

$GLOBALS['TL_DCA']['hofff_content']['fields']['hofff_content_template'] = [
    'label'                 => &$GLOBALS['TL_LANG']['hofff_content']['template'],
    'exclude'               => true,
    'inputType'             => 'select',
    'options_callback'      => DCA::getTemplateGroupOptionsCallback(
        'hofff_content_template_prefixes',
    ),
    'eval'                  => [
        'includeBlankOption'    => true,
        'tl_class'              => 'clr w50',
    ],
    'sql'                   => 'varchar(255) NOT NULL default \'\'',
    'hofff_content_template_prefixes' => ['hofff_content_references_'],
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
