<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Renderer;

interface Renderer
{
    public function render(): string;

    public function isValid(): bool;

    /** @deprecated Use the render method instead */
    public function __toString(): string;

    public function getColumn(): string;

    public function setColumn(string $column): void;

    /** @SuppressWarnings(PHPMD.BooleanGetMethodName) */
    public function getExcludeFromSearch(): bool;

    public function setExcludeFromSearch(bool $exclude): void;

    public function getCssClasses(): string|null;

    public function setCssClasses(string|null $classes): void;

    public function addCssClasses(string|null $classes): void;

    public function getCssId(): string|null;

    public function setCssId(string|null $cssId): void;
}
