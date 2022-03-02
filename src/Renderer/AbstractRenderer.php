<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Renderer;

use Hofff\Contao\Content\Util\ContaoUtil;

use function strlen;
use function trim;

abstract class AbstractRenderer implements Renderer
{
    /** @var array<string,bool> */
    private static $renderStack = [];

    /** @var string */
    private $column;

    /** @var bool */
    private $excludeFromSearch;

    /** @var string|null */
    private $cssClasses;

    /** @var string|null */
    private $cssID;

    protected function __construct()
    {
        $this->column            = 'main';
        $this->excludeFromSearch = false;
    }

    /**
     * {@inheritDoc}
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress RedundantCastGivenDocblockType
     */
    public function setColumn($column)
    {
        $this->column = (string) $column;
    }

    /**
     * {@inheritDoc}
     */
    public function getExcludeFromSearch()
    {
        return $this->excludeFromSearch;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress RedundantCastGivenDocblockType
     */
    public function setExcludeFromSearch($exclude)
    {
        $this->excludeFromSearch = (bool) $exclude;
    }

    /**
     * {@inheritDoc}
     */
    public function getCSSClasses()
    {
        return $this->cssClasses;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress RedundantCastGivenDocblockType
     */
    public function setCSSClasses($classes)
    {
        $this->cssClasses = $classes === null || ! strlen($classes) ? null : (string) $classes;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress RedundantCastGivenDocblockType
     */
    public function addCSSClasses($classes)
    {
        if ($classes === null || ! strlen($classes)) {
            return;
        }

        if ($this->cssClasses === null) {
            $this->cssClasses = (string) $classes;

            return;
        }

        $this->cssClasses .= ' ' . $classes;
    }

    /**
     * {@inheritDoc}
     */
    public function getCSSID()
    {
        return $this->cssID;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress RedundantCastGivenDocblockType
     */
    public function setCSSID($cssId)
    {
        $this->cssID = $cssId === null || ! strlen($cssId) ? null : (string) $cssId;
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        if (! $this->isValid()) {
            return '';
        }

        if (! $this->pushStack()) {
            return '';
        }

        $content = $this->doRender();

        if ($this->shouldExcludeFromSearch()) {
            $content = ContaoUtil::excludeFromSearch($content);
        }

        $this->popStack();

        return $content;
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return $this->render();
    }

    public function isValid(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    abstract protected function getCacheKey();

    /**
     * @return string
     */
    abstract protected function doRender();

    /**
     * @return bool
     */
    protected function shouldExcludeFromSearch()
    {
        return $this->getExcludeFromSearch() || $this->isProtected();
    }

    /**
     * @return bool
     */
    protected function isProtected()
    {
        return false;
    }

    /**
     * @param object $element
     *
     * @return void
     */
    protected function applyCSSClassesAndID($element)
    {
        $classes = $this->getCSSClasses();
        $cssId   = $this->getCSSID();

        if ($classes === null && $cssId === null) {
            return;
        }

        $css = $element->cssID;

        $classes === null || $css[1] = trim($classes . ' ' . $css[1]);
        $cssId === null || $css[0]   = $cssId;

        $element->cssID = $css;
    }

    /**
     * @return bool
     */
    private function pushStack()
    {
        $key = $this->getCacheKey();

        if (isset(self::$renderStack[$key])) {
            return false;
        }

        return self::$renderStack[$key] = true;
    }

    /**
     * @return void
     */
    private function popStack()
    {
        unset(self::$renderStack[$this->getCacheKey()]);
    }
}
