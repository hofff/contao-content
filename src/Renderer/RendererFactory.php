<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Renderer;

use Contao\ArticleModel;
use Contao\Database;
use Contao\Database\Result;
use Contao\Model\Registry;
use Contao\ModuleModel;
use Contao\StringUtil;
use Hofff\Contao\Content\Util\QueryUtil;
use Hofff\Contao\Content\Util\Util;
use Hofff\Contao\LanguageRelations\LanguageRelations;
use stdClass;

use function array_filter;
use function array_merge;
use function array_values;
use function call_user_func;
use function call_user_func_array;
use function explode;
use function implode;
use function ksort;
use function method_exists;
use function trim;
use function ucfirst;

use const SORT_NUMERIC;

/** @SuppressWarnings(PHPMD.CouplingBetweenObjects) */
class RendererFactory
{
    /**
     * @param list<array<string,mixed>> $configs
     *
     * @return Renderer[]
     */
    public static function createAll(array $configs, string $column): array
    {
        $context          = new stdClass();
        $context->selects = [];
        $context->params  = [];
        $context->column  = $column;

        $configs = array_values(StringUtil::deserialize($configs, true));

        foreach ($configs as $i => $config) {
            /** @psalm-suppress InvalidArrayAccess */
            [$type] = explode('.', $config['_key'], 2);

            $method = 'add' . ucfirst($type) . 'Select';
            if (! method_exists(self::class, $method)) {
                continue;
            }

            call_user_func(
                [self::class, $method],
                $context,
                $config,
                $i,
            );
        }

        $renderers = [];
        foreach ($context->selects as $type => $selects) {
            $method = 'create' . ucfirst($type) . 'Renderer';
            if (! method_exists(self::class, $method)) {
                continue;
            }

            $selects = array_filter($selects);
            if (! $selects) {
                continue;
            }

            $sql    = '(' . implode(') UNION ALL (', $selects) . ')';
            $result = Database::getInstance()->prepare($sql)->execute($context->params[$type] ?? []);

            while ($result->next()) {
                $i = $result->hofff_content_index;

                $renderer = call_user_func(
                    [self::class, $method],
                    $result,
                    $configs[$i],
                );
                $renderer->setColumn($column);

                if (! $renderer->isValid()) {
                    continue;
                }

                $renderers[$i][] = $renderer;
            }
        }

        if (! $renderers) {
            return [];
        }

        ksort($renderers, SORT_NUMERIC);
        $renderers = call_user_func_array('array_merge', $renderers);

        return $renderers;
    }

    /** @param array<string,mixed> $config */
    protected static function createArticleRenderer(Result $result, array $config): ArticleRenderer
    {
        $article = Registry::getInstance()->fetch('tl_article', $result->id);
        if (! $article instanceof ArticleModel) {
            $article = new ArticleModel($result);
        }

        $renderer = new ArticleRenderer();
        $renderer->setArticle($article);
        $renderer->setRenderContainer($config['render_container']);

        self::configureAbstractRenderer($renderer, $config);

        return $renderer;
    }

    /** @param array<string,mixed> $config */
    protected static function createModuleRenderer(Result $result, array $config): ModuleRenderer
    {
        $module = Registry::getInstance()->fetch('tl_module', $result->id);
        if (! $module instanceof ModuleModel) {
            $module = new ModuleModel($result);
        }

        $renderer = new ModuleRenderer();
        $renderer->setModule($module);

        self::configureAbstractRenderer($renderer, $config);

        return $renderer;
    }

    /** @param array<string,mixed> $config */
    protected static function configureAbstractRenderer(AbstractRenderer $renderer, array $config): void
    {
        $renderer->setExcludeFromSearch((bool) ($config['exclude_from_search'] ?? ''));
        $renderer->setCssClasses(trim($config['css_classes'] ?? ''));
        $renderer->setCssId(trim($config['css_id'] ?? ''));
    }

    /**
     * @param array<string,mixed> $config
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    protected static function addArticleSelect(stdClass $context, array $config, int $index): void
    {
        /** @psalm-suppress PossiblyUndefinedArrayOffset */
        [, $articleId] = explode('.', $config['_key'], 2);

        $context->params['article'][] = $index;
        $context->params['article'][] = $articleId;

        $targetSectionCondition = '';
        if ($config['target_section_filter']) {
            $targetSectionCondition       = 'AND article.inColumn = ?';
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
     * @param array<string,mixed> $config
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    protected static function addPageSelect(stdClass $context, array $config, int $index): void
    {
        /** @psalm-suppress PossiblyUndefinedArrayOffset */
        [, $pageId] = explode('.', $config['_key'], 2);

        if ($config['translate'] && $GLOBALS['objPage'] && Util::isLanguageRelationsLoaded()) {
            $root      = $GLOBALS['objPage']->rootId;
            $relations = LanguageRelations::getRelations((int) $pageId);
            $pageId    = $relations[$root] ?: $pageId;
        }

        $context->params['article'][] = $index;
        $context->params['article'][] = $pageId;

        $targetSectionCondition = '';
        if ($config['target_section_filter']) {
            $targetSectionCondition       = 'AND article.inColumn = ?';
            $context->params['article'][] = $context->column;
        }

        $sourceSectionCondition = '';
        if (! empty($config['source_sections'])) {
            $wildcards                  = QueryUtil::wildcards($config['source_sections']);
            $sourceSectionCondition     = 'AND article.inColumn IN (' . $wildcards . ')';
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

    /** @param array<string,mixed> $config */
    protected static function addModuleSelect(stdClass $context, array $config, int $index): void
    {
        /** @psalm-suppress PossiblyUndefinedArrayOffset */
        [, $moduleId] = explode('.', $config['_key'], 2);

        $context->params['module'][] = $index;
        $context->params['module'][] = $moduleId;

        $context->selects['module'][] = <<<'SQL'
SELECT
    ?           AS hofff_content_index,
    module.*
FROM
    tl_module
    AS module
WHERE
    module.id = ?
SQL;
    }
}
