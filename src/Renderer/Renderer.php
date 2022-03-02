<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Renderer;

interface Renderer
{
    /**
     * @return string
     */
    public function render();

    public function isValid(): bool;

    /**
     * @deprecated Use the render method instead
     *
     * @return string
     */
    public function __toString();

    /**
     * @return string
     */
    public function getColumn();

    /**
     * @param string $column
     *
     * @return void
     */
    public function setColumn($column);

    /**
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getExcludeFromSearch();

    /**
     * @param bool $exclude
     *
     * @return void
     */
    public function setExcludeFromSearch($exclude);

    /**
     * @return string|null
     */
    public function getCSSClasses();

    /**
     * @param string|null $classes
     *
     * @return void
     */
    public function setCSSClasses($classes);

    /**
     * @param string|null $classes
     *
     * @return void
     */
    public function addCSSClasses($classes);

    /**
     * @return string|null
     */
    public function getCSSID();

    /**
     * @param string|null $cssId
     *
     * @return void
     */
    public function setCSSID($cssId);
}
