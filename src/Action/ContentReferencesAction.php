<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Action;

use Contao\ContentModel;
use Contao\Model;
use Contao\PageModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ContentReferencesAction extends AbstractReferencesAction
{
    public function __invoke(
        Request $request,
        ContentModel $model,
        string $section,
        ?PageModel $pageModel = null,
        ?array $classes = null
    ): Response {
        $this->initializePageContext($request, $model, $pageModel);

        return $this->createResponse($model, $section, $pageModel, (array) $classes);
    }

    protected function loadModel(array $attributes): ?Model
    {
        return $this->contaoFramework
            ->getAdapter(ContentModel::class)
            ->__call('findByPk', [$attributes['contentModel']]);
    }
}
