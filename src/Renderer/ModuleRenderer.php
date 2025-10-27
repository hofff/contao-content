<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Renderer;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\ModuleModel;
use Override;

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

    #[Override]
    public function isValid(): bool
    {
        return (bool) $this->getModule();
    }

    #[Override]
    protected function getCacheKey(): string
    {
        if ($this->module === null) {
            return self::class;
        }

        return self::class . $this->module->id;
    }

    #[Override]
    protected function doRender(): string
    {
        if ($this->module === null) {
            return '';
        }

        return $this->contaoFramework
            ->getAdapter(Controller::class)
            ->getFrontendModule($this->module->id, $this->getColumn());
    }

    #[Override]
    protected function isProtected(): bool
    {
        if ($this->module === null) {
            return false;
        }

        /** @psalm-suppress RedundantCastGivenDocblockType */
        return (bool) $this->module->protected;
    }
}
