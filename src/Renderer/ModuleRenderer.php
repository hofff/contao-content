<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Renderer;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\ModuleModel;

final class ModuleRenderer extends AbstractRenderer
{
    private ModuleModel|null $module = null;

    public function __construct(private ContaoFramework $contaoFramework)
    {
        parent::__construct();
    }

    public function getModule(): ModuleModel|null
    {
        return $this->module;
    }

    public function setModule(ModuleModel $module): void
    {
        $this->module = $module;
    }

    public function isValid(): bool
    {
        return (bool) $this->getModule();
    }

    protected function getCacheKey(): string
    {
        if ($this->module === null) {
            return self::class;
        }

        return self::class . $this->module->id;
    }

    protected function doRender(): string
    {
        if ($this->module === null) {
            return '';
        }

        return $this->contaoFramework
            ->getAdapter(Controller::class)
            ->getFrontendModule($this->module->id, $this->getColumn());
    }

    protected function isProtected(): bool
    {
        if ($this->module === null) {
            return false;
        }

        return (bool) $this->module->protected;
    }
}
