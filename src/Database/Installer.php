<?php

namespace Hofff\Contao\Content\Database;

use Contao\Database;
use Hofff\Contao\Content\Util\StringUtil;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class Installer {

	/**
	 * @param array $queries
	 * @return void
	 */
	public function hookSQLCompileCommands($queries) {
		$tables = array_flip(Database::getInstance()->listTables(null, true));

		if(!isset($tables['hofff_content_tree'])) {
			$queries['ALTER_CHANGE'][] = StringUtil::tabsToSpaces($this->getTreeView());
		}

		return $queries;
	}

	/**
	 * @return string
	 */
	protected function getTreeView() {
		return <<<SQL
CREATE OR REPLACE VIEW hofff_content_tree AS

SELECT
	CONCAT('page.', page.pid)						AS tpid,
	CONCAT('page.', page.id)						AS tid,
	page.type != 'root'								AS selectable,
	'page'											AS node_type,

	page.pid										AS pid,
	page.id											AS id,
	page.sorting									AS sorting,
	page.title										AS title,
	page.type										AS type,
	page.published									AS published,
	page.start										AS start,
	page.stop										AS stop,
	page.hide										AS hide,
	page.protected									AS protected,
	NULL											AS in_column
FROM
	tl_page
	AS page

UNION SELECT
	CONCAT('page.', article.pid)					AS tpid,
	CONCAT('article.', article.id)					AS tid,
	1												AS selectable,
	'article'										AS node_type,

	article.pid										AS pid,
	article.id										AS id,
	article.sorting									AS sorting,
	article.title									AS title,
	NULL											AS type,
	article.published								AS published,
	article.start									AS start,
	article.stop									AS stop,
	NULL											AS hide,
	article.protected								AS protected,
	article.inColumn								AS in_column
FROM
	tl_article
	AS article

UNION SELECT
	'page.0'										AS tpid,
	CONCAT('theme.', theme.id)						AS tid,
	0												AS selectable,
	'theme'											AS node_type,

	NULL											AS pid,
	theme.id										AS id,
	-100											AS sorting,
	theme.name										AS title,
	NULL											AS type,
	NULL											AS published,
	NULL											AS start,
	NULL											AS stop,
	NULL											AS hide,
	NULL											AS protected,
	NULL											AS in_column
FROM
	tl_theme
	AS theme

UNION SELECT
	CONCAT('theme.', module.pid)					AS tpid,
	CONCAT('module.', module.id)					AS tid,
	1												AS selectable,
	'module'										AS node_type,

	module.pid										AS pid,
	module.id										AS id,
	0												AS sorting,
	module.name										AS title,
	module.type										AS type,
	NULL											AS published,
	NULL											AS start,
	NULL											AS stop,
	NULL											AS hide,
	module.protected								AS protected,
	NULL											AS in_column
FROM
	tl_module
	AS module

SQL;
	}

}
