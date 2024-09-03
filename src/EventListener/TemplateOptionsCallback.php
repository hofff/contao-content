<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\EventListener;

use Contao\Controller;

final class TemplateOptionsCallback
{
    /** @return array<string, string> */
    public function templateOptions(): array
    {
        return Controller::getTemplateGroup('hofff_content_references_');
    }
}
