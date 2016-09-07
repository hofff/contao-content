<?php

$GLOBALS['BE_MOD']['content']['article']['stylesheet'][]
	= 'system/modules/hofff_content/assets/css/hofff_content.css';
$GLOBALS['BE_MOD']['design']['themes']['stylesheet'][]
	= 'system/modules/hofff_content/assets/css/hofff_content.css';
$GLOBALS['BE_MOD']['content']['article']['javascript'][]
	= 'system/modules/hofff_content/assets/js/hofff_content.js';
$GLOBALS['BE_MOD']['design']['themes']['javascript'][]
	= 'system/modules/hofff_content/assets/js/hofff_content.js';

$GLOBALS['TL_CTE']['includes']['hofff_content_references']
	= 'Hofff\\Contao\\Content\\Frontend\\ContentReferences';
$GLOBALS['FE_MOD']['miscellaneous']['hofff_content_references']
	= 'Hofff\\Contao\\Content\\Frontend\\ModuleReferences';

$GLOBALS['TL_HOOKS']['sqlCompileCommands']['hofff_content']
	= [ 'Hofff\\Contao\\Content\\Database\\Installer', 'hookSQLCompileCommands' ];
$GLOBALS['TL_HOOKS']['isVisibleElement']['hofff_content']
	= [ 'Hofff\\Contao\\Content\\Hooks', 'isVisibleElement' ];
