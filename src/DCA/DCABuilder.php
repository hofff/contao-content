<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\DCA;

use Contao\BackendUser;
use Contao\Controller;
use Contao\Database;
use Contao\Image;
use Contao\StringUtil as ContaoStringUtil;
use Contao\System;
use Contao\Widget;
use Hofff\Contao\Content\Util\ContaoUtil;
use Hofff\Contao\Content\Util\QueryUtil;
use Hofff\Contao\Content\Util\StringUtil;
use Hofff\Contao\Selectri\Model\Data;
use Hofff\Contao\Selectri\Model\DataFactory;
use Hofff\Contao\Selectri\Model\Node;
use Hofff\Contao\Selectri\Model\Suggest\SuggestDataDecoratorFactory;
use Hofff\Contao\Selectri\Model\Tree\SQLAdjacencyTreeDataFactory;
use Hofff\Contao\Selectri\Util\Icons;

use function array_filter;
use function array_merge;
use function array_replace;
use function call_user_func;
use function defined;
use function implode;
use function sprintf;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DCABuilder
{
    /** @var string */
    protected $paletteTemplate = '';

    public function __construct()
    {
    }

    /**
     * @param string $template
     *
     * @return void
     */
    public function setPaletteTemplate($template)
    {
        $this->paletteTemplate = $template;
    }

    /**
     * @param array<string,mixed> $dca
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function build(array &$dca)
    {
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
     * @return DataFactory
     */
    protected function createFactory()
    {
        $factory = new SQLAdjacencyTreeDataFactory();
        $factory->getConfig()->setTable('hofff_content_tree');
        $factory->getConfig()->setKeyColumn('tid');
        $factory->getConfig()->setParentKeyColumn('tpid');
        $factory->getConfig()->setRootValue('page.0');
        $factory->getConfig()->addSearchColumns('title');
        $factory->getConfig()->setOrderByExpr('node_type = \'article\', sorting, title');
        $factory->getConfig()->setIconCallback([$this, 'generateNodeIcon']);
        $factory->getConfig()->setLabelCallback([$this, 'generateNodeLabel']);
        $factory->getConfig()->setContentCallback([$this, 'generateNodeContent']);
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

        if (defined('BE_USER_LOGGED_IN') && BE_USER_LOGGED_IN) {
            $user = BackendUser::getInstance();

            /** @psalm-suppress RedundantConditionGivenDocblockType - Contao docs misses false return value */
            if ($user instanceof BackendUser && ! $user->isAdmin && $user->pagemounts !== false) {
                $factory->getConfig()->setRoots(StringUtil::prefixEach($user->pagemounts, 'page.'));
            }

            if (! $this->hasAccessToFrontendModules()) {
                $factory->getConfig()->setConditionExpr('node_type NOT IN (\'theme\', \'module\')');
            }
        }

        $factory = new SuggestDataDecoratorFactory($factory);
        $factory->setSuggestionCallback([$this, 'fetchSuggestions']);

        return $factory;
    }

    /**
     * @return list<string|int>
     *
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     */
    public function fetchSuggestions()
    {
        $params = [];

        $selects   = [];
        $selects[] = $this->getRecentlyEditedArticlesSelect($params);
        $selects[] = $this->getRecentlyEditedModulesSelect();

        $selects = array_filter($selects);
        if (! $selects) {
            return [];
        }

        $sql = '(' . implode(') UNION (', $selects) . ') ORDER BY tstamp DESC LIMIT 20';

        return Database::getInstance()->prepare($sql)->execute($params)->fetchEach('id');
    }

    /**
     * @param array<int,string|int> $params
     *
     * @return string|null
     */
    protected function getRecentlyEditedArticlesSelect(array &$params)
    {
        $condition = '';
        $user      = BackendUser::getInstance();
        if (! $user instanceof BackendUser) {
            return null;
        }

        if (defined('BE_USER_LOGGED_IN') && BE_USER_LOGGED_IN && ! $user->isAdmin) {
            $pages = $user->pagemounts;

            if (! $pages) {
                return null;
            }

            /** @psalm-var list<string> $pages */
            $pages     = Database::getInstance()->getChildRecords($pages, 'tl_page', false, $pages);
            $wildcards = QueryUtil::wildcards($pages);
            $condition = 'WHERE pid IN (' . $wildcards . ')';
            $params    = array_merge($params, $pages);
        }

        return <<<SQL
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
    }

    /**
     * @return string|null
     */
    protected function getRecentlyEditedModulesSelect()
    {
        if (defined('BE_USER_LOGGED_IN') && BE_USER_LOGGED_IN && ! $this->hasAccessToFrontendModules()) {
            return null;
        }

        return <<<SQL
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
    }

    /**
     * @return string
     */
    public function generateNodeIcon(Node $node, Data $data)
    {
        $type   = $node->getData()['node_type'];
        $config = $data->getWidget()->getFieldDCA()['nodeTypes'][$type];

        if (isset($config['icon_callback'])) {
            return call_user_func($config['icon_callback'], $node, $data);
        }

        $icon = $config['icon'] ?? Icons::DEFAULT_ICON;

        return Image::getPath($icon);
    }

    /**
     * @return string
     */
    public function generateNodeLabel(Node $node, Data $data)
    {
        $type   = $node->getData()['node_type'];
        $config = $data->getWidget()->getFieldDCA()['nodeTypes'][$type];

        if (isset($config['label_callback'])) {
            return call_user_func($config['label_callback'], $node, $data);
        }

        $data = $node->getData();

        return sprintf(
            '%s <span class="hofff-content-label">(ID %s)</span>',
            $data['title'],
            $data['id']
        );
    }

    /**
     * @return string
     */
    public function generateNodeContent(Node $node, Data $data)
    {
        if (! $node->isSelectable()) {
            return '';
        }

        $type   = $node->getData()['node_type'];
        $config = $data->getWidget()->getFieldDCA()['nodeTypes'][$type];
        $tpl    = $config['template'] ?? 'hofff_content_node';
        $tpl    = ContaoUtil::createTemplate($tpl, $node->getData());

        if (! empty($config['fields'])) {
            $tpl->widgets = $this->createWidgets($config['fields'], $node, $data);
        }

        if (isset($config['content_callback'])) {
            $content = call_user_func($config['content_callback'], $node, $data, $tpl);
        } else {
            $content = $tpl->parse();
        }

        return sprintf(
            '<div class="hofff-content-html" data-hofff-content-html="%s"></div>',
            ContaoStringUtil::specialchars($content, false, true)
        );
    }

    /**
     * @param array<string,array<string,mixed>> $fields
     *
     * @return array<Widget>
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function createWidgets(array $fields, Node $node, Data $data)
    {
        $value = $data->getWidget()->getValue();

        $widgets = [];
        foreach ($fields as $name => $config) {
            $attributes = Widget::getAttributesFromDca(
                $config,
                $node->getAdditionalInputName($name),
                $value[$node->getKey()][$name]
            );

            /** @psalm-var class-string<Widget> $class */
            $class = $GLOBALS['BE_FFL'][$config['inputType']];

            /** @psalm-suppress UnsafeInstantiation */
            $widgets[$name] = new $class($attributes);
        }

        return $widgets;
    }

    /**
     * @return bool
     */
    protected function hasAccessToFrontendModules()
    {
        $user = BackendUser::getInstance();
        if (! $user instanceof BackendUser) {
            return false;
        }

        return $user->hasAccess('themes', 'modules') && $user->hasAccess('modules', 'themes');
    }
}
