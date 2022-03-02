<?php

declare(strict_types=1);

use Hofff\Contao\Content\Database\Installer;
use Hofff\Contao\Content\EventListener\HookListener;

$GLOBALS['BE_MOD']['content']['article']['stylesheet'][] = 'bundles/hofffcontaocontent/css/hofff_content.css';
$GLOBALS['BE_MOD']['design']['themes']['stylesheet'][]   = 'bundles/hofffcontaocontent/css/hofff_content.css';
$GLOBALS['BE_MOD']['content']['article']['javascript'][] = 'bundles/hofffcontaocontent/js/hofff_content.js';
$GLOBALS['BE_MOD']['design']['themes']['javascript'][]   = 'bundles/hofffcontaocontent/js/hofff_content.js';

$GLOBALS['TL_HOOKS']['sqlCompileCommands']['hofff_content'] = [Installer::class, 'hookSQLCompileCommands'];
$GLOBALS['TL_HOOKS']['isVisibleElement']['hofff_content']   = [HookListener::class, 'isVisibleElement'];
