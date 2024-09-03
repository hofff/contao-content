<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Action;

use Contao\CoreBundle\Fragment\FragmentConfig;
use Contao\CoreBundle\Fragment\FragmentPreHandlerInterface;
use Contao\CoreBundle\Fragment\Reference\FragmentReference;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\FrontendTemplate;
use Contao\Model;
use Contao\PageModel;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use FOS\HttpCacheBundle\Http\SymfonyResponseTagger;
use Hofff\Contao\Content\Renderer\Renderer;
use Hofff\Contao\Content\Renderer\RendererFactory;
use Hofff\Contao\Content\Util\ContaoUtil;
use Netzmacht\Contao\PageContext\Request\PageContextFactory;
use Netzmacht\Contao\PageContext\Request\PageContextInitializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

use function count;
use function implode;
use function is_array;
use function trim;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.LongVariable)
 */
abstract class ReferencesAction implements FragmentPreHandlerInterface
{
    /** @SuppressWarnings(PHPMD.LongVariable) */
    public function __construct(
        private readonly TokenChecker $tokenChecker,
        protected readonly ContaoFramework $contaoFramework,
        private readonly PageContextFactory $pageContextFactory,
        private readonly PageContextInitializer $pageContextInitializer,
        private readonly SymfonyResponseTagger|null $responseTagger,
        private readonly RequestStack $requestStack,
        private readonly RendererFactory $rendererFactory,
        private readonly Connection $connection,
        private readonly InsertTagParser $insertTagParser,
    ) {
    }

    public function preHandleFragment(FragmentReference $uri, FragmentConfig $config): void
    {
        $model = $this->loadModel($uri->attributes);
        if (! $model) {
            return;
        }

        if (! $model->hofff_content_bypass_cache) {
            return;
        }

        $config->setRenderer('esi');
    }

    /** @param array<string,mixed> $attributes */
    abstract protected function loadModel(array $attributes): Model|null;

    /**
     * @param list<string> $classes
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function createResponse(
        Model $model,
        string $section,
        PageModel|null $pageModel,
        array $classes,
    ): Response {
        $GLOBALS['objPage'] ??= $pageModel;
        $renderers            = $this->createRenderer($model, $section);

        if ($model->hofff_content_template) {
            $content = $this->parseTemplate($model, $renderers, $section, $classes);
        } else {
            $content = [];
            foreach ($renderers as $renderer) {
                $content[] = $renderer->render();
            }

            $content = implode("\n", $content);
        }

        // Trim whitespaces before ongoing processing.
        $content = trim($content);

        if ($model->hofff_content_exclude_from_search) {
            $content = ContaoUtil::excludeFromSearch($content);
        }

        $content  = $this->replaceInsertTags($model, $content);
        $response = new Response($content);
        $this->setCacheHeaders($response, $model, $pageModel);
        $this->tagResponse(['contao.db.' . $model::getTable() . '.' . $model->id]);

        return $response;
    }

    protected function initializePageContext(Request $request, Model $model, PageModel|null $pageModel): void
    {
        if ($this->requestStack->getMainRequest() !== $request) {
            return;
        }

        if (! $model->hofff_content_bypass_cache || ! $pageModel) {
            return;
        }

        $pageContext = ($this->pageContextFactory)((int) $pageModel->id);
        $this->pageContextInitializer->initialize($pageContext, $request);
    }

    /** @return Renderer[] */
    private function createRenderer(Model $model, string $section): array
    {
        $result = $this->connection->executeQuery(
            'SELECT * FROM tl_hofff_content WHERE pid=:pid AND ptable=:ptable ORDER BY sorting',
            [
                'pid' => $model->id,
                'ptable' => $model::getTable(),
            ],
        );

        $renderers = $this->rendererFactory->createAll($result->fetchAllAssociative(), $section);

        if ($model->hofff_content_exclude_from_search) {
            foreach ($renderers as $renderer) {
                $renderer->setExcludeFromSearch(false);
            }
        }

        return $renderers;
    }

    /**
     * Parse the template.
     *
     * @param Model             $model     Content model.
     * @param Renderer[]        $renderers Renderer.
     * @param string            $section   Section name.
     * @param list<string>|null $classes   Additional classes.
     */
    private function parseTemplate(
        Model $model,
        array $renderers,
        string $section,
        array|null $classes = null,
    ): string {
        $template = new FrontendTemplate($model->hofff_content_template);

        $data     = StringUtil::deserialize($model->headline);
        $headline = is_array($data) ? $data['value'] : $data;
        $level    = is_array($data) ? $data['unit'] : 'h1';

        $data  = StringUtil::deserialize($model->cssID, true);
        $class = trim($model->hofff_content_template . ' ' . ($data[1] ?? ''));
        $cssID = ! empty($data[0]) ? ' id="' . $data[0] . '"' : '';

        if (is_array($classes) && count($classes) > 0) {
            $template->class .= ' ' . implode(' ', $classes);
        }

        $template->setData(
            [
                'renderers' => $renderers,
                'column'    => $section,
                'headline'  => $headline,
                'hl'        => $level,
                'class'     => $class,
                'cssID'     => $cssID,
            ],
        );

        return $template->parse();
    }

    /** @SuppressWarnings(PHPMD.CyclomaticComplexity) */
    private function setCacheHeaders(Response $response, Model $model, PageModel|null $pageModel): void
    {
        if (
            $model->hofff_content_bypass_cache
            || ! $pageModel
            || (
                ($pageModel->cache === false || $pageModel->cache < 1)
                && ($pageModel->clientCache === false || $pageModel->clientCache < 1)
            )
        ) {
            $response->headers->addCacheControlDirective('no-cache');
            $response->headers->addCacheControlDirective('no-store');
            $response->setPrivate();

            return;
        }

        // Do not cache the response if a user is logged in or the page is protected
        // TODO: Add support for proxies so they can vary on member context
        if (
            (bool) $pageModel->protected
            || $this->tokenChecker->hasFrontendUser()
            || $this->tokenChecker->hasBackendUser()
        ) {
            $response->headers->addCacheControlDirective('no-cache');
            $response->headers->addCacheControlDirective('no-store');
            $response->setPrivate();

            return;
        }

        if ($pageModel->clientCache > 0) {
            $response->setMaxAge((int) $pageModel->clientCache);
        }

        if ($pageModel->cache <= 0) {
            return;
        }

        $response->setSharedMaxAge((int) $pageModel->cache);
    }

    /** @param list<string> $tags */
    protected function tagResponse(array $tags): void
    {
        if (! $this->responseTagger) {
            return;
        }

        $this->responseTagger->addTags($tags);
    }

    protected function replaceInsertTags(Model $model, string $content): string
    {
        if ($model->hofff_content_bypass_cache) {
            return $this->insertTagParser->replace($content);
        }

        return $content;
    }
}
