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
    /** {@inheritDoc} */
    public function __invoke(
        Request $request,
        ContentModel $model,
        string $section,
        PageModel|null $pageModel = null,
        array|null $classes = null,
    ): Response {
        $this->initializePageContext($request, $model, $pageModel);

        /** @psalm-suppress ArgumentTypeCoercion */
        return $this->createResponse($model, $section, $pageModel, (array) $classes);
    }

    /** @param array<string,mixed> $attributes */
    protected function loadModel(array $attributes): Model|null
    {
        if ($attributes['contentModel'] instanceof ContentModel) {
            return $attributes['contentModel'];
        }

        return $this->contaoFramework
            ->getAdapter(ContentModel::class)
            ->__call('findByPk', [$attributes['contentModel']]);
    }
}
