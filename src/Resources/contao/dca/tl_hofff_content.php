<?php

declare(strict_types=1);

use Contao\DC_Table;
use Doctrine\DBAL\Types\Types;

// Parent table configuration
$GLOBALS['TL_DCA']['tl_hofff_content'] = [
    'config'   => [
        'dataContainer' => DC_Table::class,
        'dynamicPtable' => true,
        'sql'           => [
            'keys' => [
                'id'         => 'primary',
                'pid,ptable' => 'index',
            ],
        ],
    ],
    'list'     => [
        'sorting'           => [
            'mode'        => 4,
            'fields'      => ['sorting'],
            'panelLayout' => 'filter;sort,search,limit',
        ],
        'label'             => [
            'fields' => ['id'],
            'format' => '%s',
        ],
        'global_operations' => [],
        'operations'        => [
            'edit'   => [
                'href' => 'act=edit',
                'icon' => 'edit.svg',
            ],
            'copy'   => [
                'href'       => 'act=paste&amp;mode=copy',
                'icon'       => 'copy.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
            ],
            'cut'    => [
                'href'       => 'act=paste&amp;mode=cut',
                'icon'       => 'cut.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
            ],
            'delete' => [
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''
                    . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null)
                    . '\'))return false;Backend.getScrollOffset()"',
            ],
            'toggle' => [
                'href' => 'act=toggle&amp;field=invisible',
                'icon' => 'visible.svg',
            ],
            'show'   => [
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],
    'palettes' => [
        '__selector__' => ['type'],
        'default'      => '{type_legend},type',
        'article'      => '{type_legend},type,article'
            . ';{config_legend},exclude_from_search,render_container,target_section_filter,css_classes,css_id',
        'page'         => '{type_legend},type,page'
            . ';{config_legend},exclude_from_search,render_container,target_section_filter,css_classes,css_id'
            . ',source_sections',
        'module'       => '{type_legend},type,module'
            . ';{config_legend},exclude_from_search,css_classes,css_id',
    ],
    'fields'   => [
        'id'                    => [
            'sql' => ['type' => Types::INTEGER, 'autoincrement' => true],
        ],
        'tstamp'                => [
            'sql' => ['type' => Types::INTEGER, 'default' => 0],
        ],
        'ptable'                => [
            'sql' => ['type' => Types::STRING, 'length' => 255],
        ],
        'pid'                   => [
            'sql' => ['type' => Types::INTEGER, 'default' => 0],
        ],
        'sorting'               => [
            'sql' => ['type' => Types::INTEGER, 'default' => 0],
        ],
        'type'                  => [
            'inputType' => 'radio',
            'eval'      => [
                'includeBlankOption' => true,
                'submitOnChange'     => true,
                'mandatory'          => true,
                'tl_class'           => 'w50',
            ],
            'reference' => &$GLOBALS['TL_LANG']['tl_hofff_content']['types'],
            'sql'       => ['type' => Types::STRING, 'length' => 16, 'default' => ''],
        ],
        'article'               => [
            'exclude'    => true,
            'inputType'  => 'picker',
            'foreignKey' => 'tl_article.title',
            'eval'       => ['mandatory' => true, 'tl_class' => 'clr'],
            'sql'        => ['type' => Types::INTEGER, 'default' => 0],
            'relation'   => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'page'                  => [
            'exclude'    => true,
            'inputType'  => 'picker',
            'foreignKey' => 'tl_page.title',
            'eval'       => ['mandatory' => true, 'tl_class' => 'clr'],
            'sql'        => ['type' => Types::INTEGER, 'default' => 0],
            'relation'   => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'module'               => [
            'exclude'    => true,
            'inputType'  => 'picker',
            'foreignKey' => 'tl_module.name',
            'eval'       => ['mandatory' => true, 'tl_class' => 'clr'],
            'sql'        => ['type' => Types::INTEGER, 'length' => 11, 'default' => 0],
            'relation'   => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'exclude_from_search'   => [
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => ['type' => Types::BOOLEAN, 'default' => false],
        ],
        'render_container'      => [
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => ['type' => Types::BOOLEAN, 'default' => false],
        ],
        'target_section_filter' => [
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => ['type' => Types::BOOLEAN, 'default' => false],
        ],
        'css_classes'           => [
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'clr w50'],
            'sql'       => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'css_id'                => [
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => ['type' => Types::STRING, 'length' => 255, 'default' => ''],
        ],
        'translate'             => [
            'inputType' => 'checkbox',
            'sql'       => ['type' => Types::BOOLEAN, 'default' => false],
        ],
        'source_sections'       => [
            'inputType' => 'checkbox',
            'eval'      => [
                'tl_class' => 'clr',
                'multiple' => true,
            ],
            'sql'       => ['type' => Types::BLOB, 'notnull' => false, 'default' => null],
        ],
    ],
];
