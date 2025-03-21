<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Action;

use Contao\Model;
use Contao\ModuleModel;
use Contao\PageModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ModuleReferencesAction extends ReferencesAction
{
    /** {@inheritDoc} */
    public function __invoke(
        Request $request,
        ModuleModel $model,
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
        if ($attributes['moduleModel'] instanceof ModuleModel) {
            return $attributes['moduleModel'];
        }

        return $this->contaoFramework
            ->getAdapter(ModuleModel::class)
            ->__call('findByPk', [$attributes['moduleModel']]);
    }
}
