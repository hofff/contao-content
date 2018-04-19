<?php

namespace Hofff\Contao\Content\Renderer;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
interface Renderer {

	/**
	 * @return string
	 */
	public function render();

	/**
	 * @return string
	 * @deprecated Use the render method instead
	 */
	public function __toString();

	/**
	 * @return string
	 */
	public function getColumn();

	/**
	 * @param string $column
	 * @return void
	 */
	public function setColumn($column);

	/**
	 * @return boolean
	 */
	public function getExcludeFromSearch();

	/**
	 * @param boolean $exclude
	 * @return void
	 */
	public function setExcludeFromSearch($exclude);

	/**
	 * @return string|null
	 */
	public function getCSSClasses();

	/**
	 * @param string|null $classes
	 * @return void
	 */
	public function setCSSClasses($classes);

	/**
	 * @param string|null $classes
	 * @return void
	 */
	public function addCSSClasses($classes);

	/**
	 * @return string|null
	 */
	public function getCSSID();

	/**
	 * @param string|null $id
	 * @return void
	 */
	public function setCSSID($id);

}
