<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Action;

use Contao\Model;
use Contao\ModuleModel;
use Contao\PageModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ModuleReferencesAction extends AbstractReferencesAction
{
    public function __invoke(
        Request $request,
        ModuleModel $model,
        string $section,
        ?PageModel $pageModel = null,
        ?array $classes = null
    ): Response {
        $this->initializePageContext($request, $model, $pageModel);

        return $this->createResponse($model, $section, $pageModel, (array) $classes);
    }

    /** @param array<string,mixed> $attributes */
    protected function loadModel(array $attributes): ?Model
    {
        return $this->contaoFramework
            ->getAdapter(ModuleModel::class)
            ->__call('findByPk', [$attributes['moduleModel']]);
    }
}
