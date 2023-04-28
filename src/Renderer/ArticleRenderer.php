<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Renderer;

use Contao\ArticleModel;
use Contao\Date;
use Contao\FrontendUser;
use Contao\ModuleArticle;
use Contao\StringUtil;
use Contao\System;

use function array_intersect;
use function call_user_func;
use function count;
use function defined;
use function in_array;
use function is_array;

class ArticleRenderer extends AbstractRenderer
{
    /** @var ArticleModel|null */
    private $article;

    /** @var bool */
    private $renderContainer;

    public function __construct()
    {
        parent::__construct();
        $this->renderContainer = false;
    }

    /**
     * @return ArticleModel|null
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * @return void
     */
    public function setArticle(ArticleModel $article)
    {
        $this->article = $article;
    }

    /**
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getRenderContainer()
    {
        return $this->renderContainer;
    }

    /**
     * @param bool|mixed $renderContainer
     *
     * @return void
     */
    public function setRenderContainer($renderContainer)
    {
        $this->renderContainer = (bool) $renderContainer;
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function isValid(): bool
    {
        if (! $this->article) {
            return false;
        }

        $now = Date::floorToMinute();
        if (
            ! $this->article->published
            || ($this->article->start && $this->article->start > $now)
            || ($this->article->stop && $this->article->stop <= $now)
        ) {
            return false;
        }

        /** @psalm-suppress UndefinedConstant */
        if ($this->article->guests && defined('FE_USER_LOGGED_IN') && FE_USER_LOGGED_IN) {
            return false;
        }

        if ($this->article->protected) {
            if (defined('FE_USER_LOGGED_IN') && ! FE_USER_LOGGED_IN) {
                return false;
            }

            $user = FrontendUser::getInstance();
            if (! is_array($user->groups)) {
                return false;
            }

            $groups = StringUtil::deserialize($this->article->groups);
            if (empty($groups) || ! is_array($groups) || ! count(array_intersect($groups, $user->groups))) {
                return false;
            }
        }

        if (! $GLOBALS['objPage'] || ! $this->article->hofff_content_hide) {
            return true;
        }

        $pageFilter = StringUtil::deserialize($this->article->hofff_content_page_filter, true);
        if (! $pageFilter) {
            return true;
        }

        $strategy = $this->article->hofff_content_page_filter_strategy;

        if ($this->article->hofff_content_page_filter_inheritance) {
            if (array_intersect($GLOBALS['objPage']->trail, $pageFilter)) {
                return $strategy === 'whitelist';
            }

            return $strategy !== 'whitelist';
        }

        if (in_array($GLOBALS['objPage']->id, $pageFilter)) {
            return $strategy === 'whitelist';
        }

        return $strategy !== 'whitelist';
    }

    /**
     * {@inheritDoc}
     */
    protected function getCacheKey()
    {
        if ($this->article === null) {
            return self::class;
        }

        return self::class . $this->article->id;
    }

    /**
     * {@inheritDoc}
     */
    protected function doRender()
    {
        $article = $this->getArticle();
        if ($article === null) {
            return '';
        }

        $article->headline  = $article->title;
        $article->multiMode = false;

        $this->executeGetArticleHook($article);

        /** @psalm-suppress InvalidArgument - Contao docs is not correct */
        $module = new ModuleArticle($article, $this->getColumn());

        $this->applyCSSClassesAndID($module);

        return $module->generate(! $this->getRenderContainer());
    }

    /**
     * {@inheritDoc}
     */
    protected function isProtected()
    {
        return $this->article && $this->article->protected;
    }

    /**
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function executeGetArticleHook(ArticleModel $article)
    {
        if (! isset($GLOBALS['TL_HOOKS']['getArticle'])) {
            return;
        }

        if (! is_array($GLOBALS['TL_HOOKS']['getArticle'])) {
            return;
        }

        foreach ($GLOBALS['TL_HOOKS']['getArticle'] as $callback) {
            call_user_func(
                [System::importStatic($callback[0]), $callback[1]],
                $article
            );
        }
    }
}
