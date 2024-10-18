<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Reference;

use Contao\ArticleModel;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Model\Registry;
use Hofff\Contao\Content\Renderer\ArticleRenderer;
use Hofff\Contao\Content\Renderer\Renderer;

trait CreateArticleRender
{
    use ConfigureRenderer;

    private readonly TokenChecker $tokenChecker;

    /** {@inheritDoc} */
    public function createRenderer(array $reference, array $config): Renderer
    {
        $model = Registry::getInstance()->fetch('tl_article', $reference['id']);
        if (! $model instanceof ArticleModel) {
            $model = new ArticleModel();
            $model->setRow($reference);
        }

        $renderer = new ArticleRenderer($this->tokenChecker);
        $renderer->setArticle($model);
        $renderer->setRenderContainer($config['render_container']);

        $this->configureRenderer($renderer, $config);

        return $renderer;
    }
}
