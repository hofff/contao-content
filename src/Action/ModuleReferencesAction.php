<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Action;

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
        return $this->createResponse($model, $section, $pageModel, (array) $classes);
    }
}
