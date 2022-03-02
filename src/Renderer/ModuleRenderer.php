<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Renderer;

use Contao\ModuleModel;

class ModuleRenderer extends AbstractRenderer
{
    /** @var ModuleModel|null */
    private $module;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return ModuleModel|null
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @return void
     */
    public function setModule(ModuleModel $module)
    {
        $this->module = $module;
    }

    public function isValid(): bool
    {
        return (bool) $this->getModule();
    }

    /**
     * @return string
     */
    protected function getCacheKey()
    {
        if ($this->module === null) {
            return self::class;
        }

        return self::class . $this->module->id;
    }

    /**
     * @return string
     */
    protected function doRender()
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    protected function isProtected()
    {
        if ($this->module === null) {
            return false;
        }

        return (bool) $this->module->protected;
    }
}
