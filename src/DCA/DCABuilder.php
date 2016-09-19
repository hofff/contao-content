<?php

namespace Hofff\Contao\Content\DCA;

use Contao\BackendUser;
use Contao\Controller;
use Contao\Database;
use Contao\Image;
use Contao\System;
use Contao\User;
use Contao\Widget;
use Hofff\Contao\Content\Util\ContaoUtil;
use Hofff\Contao\Content\Util\QueryUtil;
use Hofff\Contao\Content\Util\StringUtil;
use Hofff\Contao\Selectri\Model\Data;
use Hofff\Contao\Selectri\Model\Node;
use Hofff\Contao\Selectri\Model\Suggest\SuggestDataDecoratorFactory;
use Hofff\Contao\Selectri\Model\Tree\SQLAdjacencyTreeDataFactory;
use Hofff\Contao\Selectri\Util\Icons;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class DCABuilder {

	/**
	 * @var string
	 */
	protected $paletteTemplate;

	/**
	 */
	public function __construct() {
	}

	/**
	 * @param string $template
	 * @return void
	 */
	public function setPaletteTemplate($template) {
		$this->paletteTemplate = $template;
	}

	/**
	 * @param array $dca
	 * @param string $fieldName
	 * @return void
	 */
	public function build(array &$dca) {
		System::loadLanguageFile('hofff_content');
		Controller::loadDataContainer('hofff_content');

		$dca['palettes']['hofff_content_references'] = sprintf(
			$this->paletteTemplate,
			$GLOBALS['TL_DCA']['hofff_content']['palettes']['default']
		);

		$dca['fields'] = array_replace(
			$dca['fields'],
			$GLOBALS['TL_DCA']['hofff_content']['fields']
		);

		$dca['fields']['hofff_content_references']['eval']['data'] = $this->createFactory();
	}

	/**
	 * @return SQLAdjacencyTreeDataFactory
	 */
	protected function createFactory() {
		$factory = new SQLAdjacencyTreeDataFactory;
		$factory->getConfig()->setTable('hofff_content_tree');
		$factory->getConfig()->setKeyColumn('tid');
		$factory->getConfig()->setParentKeyColumn('tpid');
		$factory->getConfig()->setRootValue('page.0');
		$factory->getConfig()->addSearchColumns('title');
		$factory->getConfig()->setOrderByExpr('node_type = \'article\', sorting, title');
		$factory->getConfig()->setIconCallback([ $this, 'generateNodeIcon' ]);
		$factory->getConfig()->setLabelCallback([ $this, 'generateNodeLabel' ]);
		$factory->getConfig()->setContentCallback([ $this, 'generateNodeContent' ]);
		$factory->getConfig()->setSelectableExpr('selectable');

		$factory->getConfig()->addColumns([
			'node_type',
			'id',
			'title',
			'type',
			'published',
			'start',
			'stop',
			'hide',
			'protected',
			'in_column',
		]);

		if(BE_USER_LOGGED_IN) {
			$user = BackendUser::getInstance();
			$user->isAdmin || $factory->getConfig()->setRoots(StringUtil::prefixEach($user->pagemounts, 'page.'));

			if(!$this->hasAccessToFrontendModules()) {
				$factory->getConfig()->setConditionExpr('node_type NOT IN (\'theme\', \'module\')');
			}
		}


		$factory = new SuggestDataDecoratorFactory($factory);
		$factory->setSuggestionCallback([ $this, 'fetchSuggestions' ]);

		return $factory;
	}

	/**
	 * @return array
	 */
	public function fetchSuggestions() {
		$params = [];

		$selects = [];
		$selects[] = $this->getRecentlyEditedArticlesSelect($params);
		$selects[] = $this->getRecentlyEditedModulesSelect($params);

		$selects = array_filter($selects);
		if(!$selects) {
			return [];
		}

		$sql = '(' . implode(') UNION (', $selects) . ') ORDER BY tstamp DESC LIMIT 20';
		$ids = Database::getInstance()->prepare($sql)->execute($params)->fetchEach('id');

		return $ids;
	}

	/**
	 * @param array $params
	 * @return string|null
	 */
	protected function getRecentlyEditedArticlesSelect(array &$params) {
		$condition = '';

		if(BE_USER_LOGGED_IN && ($user = BackendUser::getInstance()) && !$user->isAdmin) {
			$pages = $user->pagemounts;

			if(!$pages) {
				return null;
			}

			$pages = Database::getInstance()->getChildRecords($pages, 'tl_page', false, $pages);
			$wildcards = QueryUtil::wildcards($pages);
			$condition = 'WHERE pid IN (' . $wildcards . ')';
			$params = array_merge($params, $pages);
		}

		$sql = <<<SQL
SELECT
	CONCAT('article.', id)	AS id,
	tstamp					AS tstamp
FROM
	tl_article
$condition
ORDER BY
	tstamp DESC
LIMIT
	20
SQL;
		return $sql;
	}

	/**
	 * @param array $params
	 * @return string|null
	 */
	protected function getRecentlyEditedModulesSelect(array &$params) {
		if(BE_USER_LOGGED_IN && !$this->hasAccessToFrontendModules()) {
			return null;
		}

		$sql = <<<SQL
SELECT
	CONCAT('module.', id)	AS id,
	tstamp					AS tstamp
FROM
	tl_module
ORDER BY
	tstamp DESC
LIMIT
	20
SQL;
		return $sql;
	}

	/**
	 * @param Node $node
	 * @return string
	 */
	public function generateNodeIcon(Node $node, Data $data) {
		$type = $node->getData()['node_type'];
		$config = $data->getWidget()->getFieldDCA()['nodeTypes'][$type];

		if(isset($config['icon_callback'])) {
			return call_user_func($config['icon_callback'], $node, $data);
		}

		$icon = isset($config['icon']) ? $config['icon'] : Icons::DEFAULT_ICON;

		return Image::getPath($icon);
	}

	/**
	 * @param Node $node
	 * @return string
	 */
	public function generateNodeLabel(Node $node, Data $data) {
		$type = $node->getData()['node_type'];
		$config = $data->getWidget()->getFieldDCA()['nodeTypes'][$type];

		if(isset($config['label_callback'])) {
			return call_user_func($config['label_callback'], $node, $data);
		}

		$data = $node->getData();
		$label = sprintf(
			'%s <span class="hofff-content-label">(ID %s)</span>',
			$data['title'],
			$data['id']
		);

		return $label;
	}

	/**
	 * @param Node $node
	 * @param Data $data
	 * @return string
	 */
	public function generateNodeContent(Node $node, Data $data) {
		if(!$node->isSelectable()) {
			return '';
		}

		$type = $node->getData()['node_type'];
		$config = $data->getWidget()->getFieldDCA()['nodeTypes'][$type];
		$tpl = isset($config['template']) ? $config['template'] : 'hofff_content_node';
		$tpl = ContaoUtil::createTemplate($tpl, $node->getData());

		if(!empty($config['fields'])) {
			$tpl->widgets = $this->createWidgets($config['fields'], $node, $data);
		}

		if(isset($config['content_callback'])) {
			$content = call_user_func($config['content_callback'], $node, $data, $tpl);
		} else {
			$content = $tpl->parse();
		}

		return sprintf(
			'<div class="hofff-content-html" data-hofff-content-html="%s"></div>',
			specialchars($content, false, true)
		);
	}

	/**
	 * @param array $fields
	 * @param Node $node
	 * @param Data $data
	 * @return array<Widget>
	 */
	protected function createWidgets(array $fields, Node $node, Data $data) {
		$value = $data->getWidget()->getValue();

		$widgets = [];
		foreach($fields as $name => $config) {
			$attributes = Widget::getAttributesFromDca(
				$config,
				$node->getAdditionalInputName($name),
				$value[$node->getKey()][$name]
			);

			$class = $GLOBALS['BE_FFL'][$config['inputType']];

			$widgets[$name] = new $class($attributes);
		}

		return $widgets;
	}

	/**
	 * @return boolean
	 */
	protected function hasAccessToFrontendModules() {
		$user = BackendUser::getInstance();
		return $user->hasAccess('themes', 'modules') && $user->hasAccess('modules', 'themes');
	}

}
