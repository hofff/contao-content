<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Reference;

use Hofff\Contao\Content\Renderer\AbstractRenderer;

use function trim;

trait ConfigureRenderer
{
    /** @param array<string,mixed> $config  */
    private function configureRenderer(AbstractRenderer $renderer, array $config): void
    {
        $renderer->setExcludeFromSearch((bool) ($config['exclude_from_search'] ?? ''));
        $renderer->setCssClasses(trim($config['css_classes'] ?? ''));
        $renderer->setCssId(trim($config['css_id'] ?? ''));
    }
}
