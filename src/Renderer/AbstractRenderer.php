<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Renderer;

use Hofff\Contao\Content\Util\ContaoUtil;
use Override;

use function trim;

// phpcs:ignore SlevomatCodingStandard.Classes.SuperfluousAbstractClassNaming.SuperfluousPrefix
abstract class AbstractRenderer implements Renderer
{
    /** @var array<string,bool> */
    private static array $renderStack = [];

    private string $column;

    private bool $excludeFromSearch;

    private string|null $cssClasses = null;

    private string|null $cssId = null;

    public function __construct()
    {
        $this->column            = 'main';
        $this->excludeFromSearch = false;
    }

    #[Override]
    public function getColumn(): string
    {
        return $this->column;
    }

    #[Override]
    public function setColumn(string $column): void
    {
        $this->column = $column;
    }

    #[Override]
    public function getExcludeFromSearch(): bool
    {
        return $this->excludeFromSearch;
    }

    #[Override]
    public function setExcludeFromSearch(bool $exclude): void
    {
        $this->excludeFromSearch = $exclude;
    }

    #[Override]
    public function getCssClasses(): string|null
    {
        return $this->cssClasses;
    }

    #[Override]
    public function setCssClasses(string|null $classes): void
    {
        $this->cssClasses = $classes === null || $classes === '' ? null : $classes;
    }

    #[Override]
    public function addCssClasses(string|null $classes): void
    {
        if ($classes === null || $classes === '') {
            return;
        }

        if ($this->cssClasses === null) {
            $this->cssClasses = $classes;

            return;
        }

        $this->cssClasses .= ' ' . $classes;
    }

    #[Override]
    public function getCssId(): string|null
    {
        return $this->cssId;
    }

    #[Override]
    public function setCssId(string|null $cssId): void
    {
        $this->cssId = $cssId === null || $cssId === '' ? null : $cssId;
    }

    #[Override]
    public function render(): string
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

    public function __toString(): string
    {
        return $this->render();
    }

    #[Override]
    public function isValid(): bool
    {
        return true;
    }

    abstract protected function getCacheKey(): string;

    abstract protected function doRender(): string;

    protected function shouldExcludeFromSearch(): bool
    {
        return $this->getExcludeFromSearch() || $this->isProtected();
    }

    protected function isProtected(): bool
    {
        return false;
    }

    protected function applyCSSClassesAndID(object $element): void
    {
        $classes = $this->getCssClasses();
        $cssId   = $this->getCssId();

        if ($classes === null && $cssId === null) {
            return;
        }

        $css = $element->cssID;

        $classes === null || $css[1] = trim($classes . ' ' . $css[1]);
        $cssId === null || $css[0]   = $cssId;

        $element->cssID = $css;
    }

    private function pushStack(): bool
    {
        $key = $this->getCacheKey();

        if (isset(self::$renderStack[$key])) {
            return false;
        }

        return self::$renderStack[$key] = true;
    }

    private function popStack(): void
    {
        unset(self::$renderStack[$this->getCacheKey()]);
    }
}
