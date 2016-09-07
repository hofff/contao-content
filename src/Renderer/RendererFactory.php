<?php

namespace Hofff\Contao\Content\Renderer;

use Contao\ArticleModel;
use Contao\Database;
use Contao\Database\Result;
use Contao\Model\Registry;
use Contao\ModuleModel;
use Hofff\Contao\Content\Util\QueryUtil;
use Hofff\Contao\LanguageRelations\LanguageRelations;
use Hofff\Contao\Content\Util\Util;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class RendererFactory {

	/**
	 * @param array $configs
	 * @param string $column
	 * @return void
	 */
	public static function createAll($configs, $column) {
		$context = new \stdClass;
		$context->selects = [];
		$context->params = [];
		$context->column = $column;

		$configs = array_values(deserialize($configs, true));

		foreach($configs as $i => $config) {
			list($type) = explode('.', $config['_key'], 2);

			$method = 'add' . ucfirst($type) . 'Select';
			if(!method_exists(self::class, $method)) {
				continue;
			}

			call_user_func(
				[ self::class, $method ],
				$context,
				$config,
				$i
			);
		}

		$renderers = [];
		foreach($context->selects as $type => $selects) {
			$method = 'create' . ucfirst($type) . 'Renderer';
			if(!method_exists(self::class, $method)) {
				continue;
			}

			$selects = array_filter($selects);
			if(!$selects) {
				continue;
			}

			$sql = '(' . implode(') UNION ALL (', $selects) . ')';
			$result = Database::getInstance()->prepare($sql)->execute($context->params[$type]);

			while($result->next()) {
				$i = $result->hofff_content_index;

				$renderer = call_user_func(
					[ self::class, $method ],
					$result,
					$configs[$i]
				);
				$renderer->setColumn($column);

				$renderers[$i][] = $renderer;
			}
		}

		ksort($renderers, SORT_NUMERIC);
		$renderers = call_user_func_array('array_merge', $renderers);

		return $renderers;
	}

	/**
	 * @param Result $result
	 * @param array $config
	 * @return ArticleRenderer
	 */
	protected static function createArticleRenderer(Result $result, array $config) {
		$article = Registry::getInstance()->fetch('tl_article', $result->id);
		$article || $article = new ArticleModel($result);

		$renderer = new ArticleRenderer;
		$renderer->setArticle($article);
		$renderer->setRenderContainer($config['render_container']);

		self::configureAbstractRenderer($renderer, $config);

		return $renderer;
	}

	/**
	 * @param Result $result
	 * @param array $config
	 * @return ModuleRenderer
	 */
	protected static function createModuleRenderer(Result $result, array $config) {
		$module = Registry::getInstance()->fetch('tl_module', $result->id);
		$module || $module= new ModuleModel($result);

		$renderer = new ModuleRenderer;
		$renderer->setModule($module);

		self::configureAbstractRenderer($renderer, $config);

		return $renderer;
	}

	/**
	 * @param AbstractRenderer $renderer
	 * @param array $config
	 * @return void
	 */
	protected static function configureAbstractRenderer(AbstractRenderer $renderer, array $config) {
		$renderer->setExcludeFromSearch($config['exclude_from_search']);
		$renderer->setCSSClasses(trim($config['css_classes']));
		$renderer->setCSSID(trim($config['css_id']));
	}

	/**
	 * @param \stdClass $context
	 * @param array $config
	 * @param integer $i
	 * @return void
	 */
	protected static function addArticleSelect(\stdClass $context, array $config, $i) {
		list(, $id) = explode('.', $config['_key'], 2);

		$context->params['article'][] = $i;
		$context->params['article'][] = $id;

		$targetSectionCondition = '';
		if($config['target_section_filter']) {
			$targetSectionCondition = 'AND article.inColumn = ?';
			$context->params['article'][] = $context->column;
		}

		$context->selects['article'][] = <<<SQL
SELECT
	?			AS hofff_content_index,
	article.*
FROM
	tl_article
	AS article
WHERE
	article.id = ?
$targetSectionCondition
SQL;
	}

	/**
	 * @param \stdClass $context
	 * @param array $config
	 * @param integer $i
	 * @return void
	 */
	protected static function addPageSelect(\stdClass $context, array $config, $i) {
		list(, $id) = explode('.', $config['_key'], 2);

		if($config['translate'] && $GLOBALS['objPage'] && Util::isLanguageRelationsLoaded()) {
			$root = $GLOBALS['objPage']->rootId;
			$relations = LanguageRelations::getRelations($id);
			$id = $relations[$root] ?: $id;
		}

		$context->params['article'][] = $i;
		$context->params['article'][] = $id;

		$targetSectionCondition = '';
		if($config['target_section_filter']) {
			$targetSectionCondition = 'AND article.inColumn = ?';
			$context->params['article'][] = $context->column;
		}

		$sourceSectionCondition = '';
		if(!empty($config['source_sections'])) {
			$wildcards = QueryUtil::wildcards($config['source_sections']);
			$sourceSectionCondition = 'AND article.inColumn IN (' . $wildcards . ')';
			$context->params['article'] = array_merge($context->params['article'], $config['source_sections']);
		}

		$context->selects['article'][] = <<<SQL
SELECT
	?			AS hofff_content_index,
	article.*
FROM
	tl_article
	AS article
WHERE
	article.pid = ?
$targetSectionCondition
$sourceSectionCondition
ORDER BY
	article.sorting
SQL;
	}

	/**
	 * @param \stdClass $context
	 * @param array $config
	 * @param integer $i
	 * @return void
	 */
	protected static function addModuleSelect(\stdClass $context, array $config, $i) {
		list(, $id) = explode('.', $config['_key'], 2);

		$context->params['module'][] = $i;
		$context->params['module'][] = $id;

		$context->selects['module'][] = <<<SQL
SELECT
	?			AS hofff_content_index,
	module.*
FROM
	tl_module
	AS module
WHERE
	module.id = ?
SQL;
	}

}
