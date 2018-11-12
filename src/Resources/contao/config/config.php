<?php

$GLOBALS['BE_MOD']['content']['article']['stylesheet'][]
	= 'bundles/hofffcontaocontent/css/hofff_content.css';
$GLOBALS['BE_MOD']['design']['themes']['stylesheet'][]
	= 'bundles/hofffcontaocontent/css/hofff_content.css';
$GLOBALS['BE_MOD']['content']['article']['javascript'][]
	= 'bundles/hofffcontaocontent/js/hofff_content.js';
$GLOBALS['BE_MOD']['design']['themes']['javascript'][]
	= 'bundles/hofffcontaocontent/js/hofff_content.js';

$GLOBALS['TL_CTE']['includes']['hofff_content_references']
	= \Hofff\Contao\Content\Frontend\ContentReferences::class;
$GLOBALS['FE_MOD']['miscellaneous']['hofff_content_references']
	= \Hofff\Contao\Content\Frontend\ModuleReferences::class;

$GLOBALS['TL_HOOKS']['sqlCompileCommands']['hofff_content']
	= [ \Hofff\Contao\Content\Database\Installer::class, 'hookSQLCompileCommands' ];
$GLOBALS['TL_HOOKS']['isVisibleElement']['hofff_content']
	= [ \Hofff\Contao\Content\EventListener\HookListener::class, 'isVisibleElement' ];
