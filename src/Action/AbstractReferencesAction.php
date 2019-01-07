<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Action;

use Contao\Controller;
use Contao\CoreBundle\Fragment\FragmentConfig;
use Contao\CoreBundle\Fragment\FragmentPreHandlerInterface;
use Contao\CoreBundle\Fragment\Reference\FragmentReference;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\FrontendTemplate;
use Contao\Model;
use Contao\PageModel;
use Contao\StringUtil;
use FOS\HttpCacheBundle\Http\SymfonyResponseTagger;
use Hofff\Contao\Content\Renderer\Renderer;
use Hofff\Contao\Content\Renderer\RendererFactory;
use Hofff\Contao\Content\Util\ContaoUtil;
use Netzmacht\Contao\PageContext\Request\PageContextFactory;
use Netzmacht\Contao\PageContext\Request\PageContextInitializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function count;
use function implode;
use function is_array;

abstract class AbstractReferencesAction implements FragmentPreHandlerInterface
{
    /**
     * @var TokenChecker
     */
    private $tokenChecker;

    /**
     * @var SymfonyResponseTagger|null
     */
    private $responseTagger;

    /**
     * @var ContaoFrameworkInterface
     */
    protected $contaoFramework;

    /**
     * @var PageContextFactory
     */
    private $pageContextFactory;

    /**
     * @var PageContextInitializer
     */
    private $pageContextInitializer;

    /**
     * AbstractReferencesAction constructor.
     *
     * @param TokenChecker               $tokenChecker
     * @param ContaoFrameworkInterface   $contaoFramework
     * @param PageContextFactory         $pageContextFactory
     * @param PageContextInitializer     $pageContextInitializer
     * @param SymfonyResponseTagger|null $responseTagger
     */
    public function __construct(
        TokenChecker $tokenChecker,
        ContaoFrameworkInterface $contaoFramework,
        PageContextFactory $pageContextFactory,
        PageContextInitializer $pageContextInitializer,
        ?SymfonyResponseTagger $responseTagger = null
    ) {
        $this->tokenChecker   = $tokenChecker;
        $this->responseTagger = $responseTagger;
        $this->contaoFramework = $contaoFramework;
        $this->pageContextFactory = $pageContextFactory;
        $this->pageContextInitializer = $pageContextInitializer;
    }

    public function preHandleFragment(FragmentReference $uri, FragmentConfig $config): void
    {
        $model = $this->loadModel($uri->attributes);
        if (!$model) {
            return;
        }

        if ($model->hofff_content_bypass_cache) {
            $config->setRenderer('esi');
        }
    }

    abstract protected function loadModel(array $attributes): ?Model;

    /**
     * @param Model          $model
     * @param string         $section
     * @param PageModel|null $pageModel
     * @param array          $classes
     *
     * @return Response
     */
    protected function createResponse(Model $model, string $section, ?PageModel $pageModel, array $classes): Response
    {
        $GLOBALS['objPage'] = $GLOBALS['objPage'] ?? $pageModel;
        $renderers          = $this->createRenderer($model, $section);

        if ($model->hofff_content_template) {
            $content = $this->parseTemplate($model, $renderers, $section, $classes);
        } else {
            $content = [];
            foreach ($renderers as $renderer) {
                $content[] = $renderer->render();
            }

            $content = trim(implode("\n", $content));
        }

        if ($model->hofff_content_exclude_from_search) {
            $content = ContaoUtil::excludeFromSearch($content);
        }

        if ($model->hofff_content_bypass_cache) {
            $controllerAdapter = $this->contaoFramework->getAdapter(Controller::class);
            $controllerAdapter->replaceInsertTags($content);
            $controllerAdapter->replaceInsertTags($content, false);
        }

        $response = new Response($content);
        $this->setCacheHeaders($response, $model, $pageModel);
        $this->tagResponse(['contao.db.' . $model::getTable() . '.' . $model->id]);

        return $response;
    }

    protected function initializePageContext(Request $request, Model $model, ?PageModel $pageModel): void
    {
        if (!$model->hofff_content_bypass_cache || !$pageModel) {
            return;
        }

        $pageContext = ($this->pageContextFactory)((int) $pageModel->id);
        $this->pageContextInitializer->initialize($pageContext, $request);
    }

    /**
     * @param Model  $model
     * @param string $section
     *
     * @return array|Renderer[]
     */
    private function createRenderer(Model $model, string $section): array
    {
        $renderers = RendererFactory::createAll($model->hofff_content_references, $section);

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
     * @param Model            $model     Content model.
     * @param array|Renderer[] $renderers Renderer.
     * @param string           $section   Section name.
     * @param array|null       $classes   Additional classes.
     *
     * @return string
     */
    private function parseTemplate(
        Model $model,
        array $renderers,
        string $section,
        ?array $classes = null
    ): string {
        $template = new FrontendTemplate($model->hofff_content_template);

        $data     = StringUtil::deserialize($model->headline);
        $headline = is_array($data) ? $data['value'] : $data;
        $level    = is_array($data) ? $data['unit'] : 'h1';

        $data  = StringUtil::deserialize($model->cssID, true);
        $class = trim($model->hofff_content_template . ' ' . ($data[1] ?? ''));
        $cssID = !empty($data[0]) ? ' id="' . $data[0] . '"' : '';

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
            ]
        );

        return $template->parse();
    }

    private function setCacheHeaders(Response $response, Model $model, ?PageModel $pageModel): void
    {
        if ($model->hofff_content_bypass_cache
            || !$pageModel
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
        if (FE_USER_LOGGED_IN === true
            || BE_USER_LOGGED_IN === true
            || $pageModel->protected
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

        if ($pageModel->cache > 0) {
            $response->setSharedMaxAge((int) $pageModel->cache);
        }
    }

    protected function tagResponse(array $tags): void
    {
        if (!$this->responseTagger) {
            return;
        }

        $this->responseTagger->addTags($tags);
    }
}
