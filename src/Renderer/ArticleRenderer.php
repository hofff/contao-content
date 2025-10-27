<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Renderer;

use Contao\ArticleModel;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\Date;
use Contao\ModuleArticle;
use Contao\StringUtil;
use Contao\System;
use Override;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Security as CoreSecurity;

use function array_intersect;
use function call_user_func;
use function in_array;
use function is_array;

final class ArticleRenderer extends AbstractRenderer
{
    private ArticleModel|null $article = null;

    private bool $renderContainer;

    public function __construct(
        private readonly TokenChecker $tokenChecker,
        private readonly CoreSecurity|Security $security,
    ) {
        parent::__construct();

        $this->renderContainer = false;
    }

    public function getArticle(): ArticleModel|null
    {
        return $this->article;
    }

    public function setArticle(ArticleModel $article): void
    {
        $this->article = $article;
    }

    /** @SuppressWarnings(PHPMD.BooleanGetMethodName) */
    public function getRenderContainer(): bool
    {
        return $this->renderContainer;
    }

    public function setRenderContainer(mixed $renderContainer): void
    {
        $this->renderContainer = (bool) $renderContainer;
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    #[Override]
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
        if ($this->article->guests && $this->tokenChecker->hasFrontendUser()) {
            return false;
        }

        if (! $this->tokenChecker->isPreviewMode()) {
            if ($this->article->protected) {
                $groups = StringUtil::deserialize($this->article->groups, true);

                if (! $this->security->isGranted(ContaoCorePermissions::MEMBER_IN_GROUPS, $groups)) {
                    return false;
                }
            } elseif ($this->article->guests && ! $this->security->isGranted('ROLE_MEMBER')) {
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

    #[Override]
    protected function getCacheKey(): string
    {
        if ($this->article === null) {
            return self::class;
        }

        return self::class . $this->article->id;
    }

    #[Override]
    protected function doRender(): string
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

    #[Override]
    protected function isProtected(): bool
    {
        return $this->article && $this->article->protected;
    }

    /** @SuppressWarnings(PHPMD.Superglobals) */
    protected function executeGetArticleHook(ArticleModel $article): void
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
                $article,
            );
        }
    }
}
