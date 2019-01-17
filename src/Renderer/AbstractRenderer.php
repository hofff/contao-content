<?php

namespace Hofff\Contao\Content\Renderer;

use Hofff\Contao\Content\Util\ContaoUtil;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
abstract class AbstractRenderer implements Renderer {

	/**
	 * @var array
	 */
	private static $renderStack = [];

	/**
	 * @var string
	 */
	private $column;

	/**
	 * @var boolean
	 */
	private $excludeFromSearch;

	/**
	 * @var string|null
	 */
	private $cssClasses;

	/**
	 * @var string|null
	 */
	private $cssID;

	/**
	 */
	protected function __construct() {
		$this->column = 'main';
		$this->excludeFromSearch = false;
	}

	/**
	 * @see \Hofff\Contao\Content\Renderer\Renderer::getColumn()
	 */
	public function getColumn() {
		return $this->column;
	}

	/**
	 * @see \Hofff\Contao\Content\Renderer\Renderer::setColumn()
	 */
	public function setColumn($column) {
		$this->column = (string) $column;
	}

	/**
	 * @see \Hofff\Contao\Content\Renderer\Renderer::getExcludeFromSearch()
	 */
	public function getExcludeFromSearch() {
		return $this->excludeFromSearch;
	}

	/**
	 * @see \Hofff\Contao\Content\Renderer\Renderer::setExcludeFromSearch()
	 */
	public function setExcludeFromSearch($exclude) {
		$this->excludeFromSearch = (bool) $exclude;
	}

	/**
	 * @see \Hofff\Contao\Content\Renderer\Renderer::getCSSClasses()
	 */
	public function getCSSClasses() {
		return $this->cssClasses;
	}

	/**
	 * @see \Hofff\Contao\Content\Renderer\Renderer::setCSSClasses()
	 */
	public function setCSSClasses($classes) {
		$this->cssClasses = $classes === null || !strlen($classes) ? null : (string) $classes;
	}

	/**
	 * @see \Hofff\Contao\Content\Renderer\Renderer::addCSSClasses()
	 */
	public function addCSSClasses($classes) {
		if($classes === null || !strlen($classes)) {
			return;
		}

		if($this->cssClasses === null) {
			$this->cssClasses = (string) $classes;
			return;
		}

		$this->cssClasses .= ' ' . $classes;
	}

	/**
	 * @see \Hofff\Contao\Content\Renderer\Renderer::getCSSID()
	 */
	public function getCSSID() {
		return $this->cssID;
	}

	/**
	 * @see \Hofff\Contao\Content\Renderer\Renderer::setCSSID()
	 */
	public function setCSSID($id) {
		$this->cssID = $id === null || !strlen($id) ? null : (string) $id;
	}

	/**
	 * @see \Hofff\Contao\Content\Renderer\Renderer::render()
	 */
	public function render() {
		if(!$this->isValid()) {
			return '';
		}

		if(!$this->pushStack()) {
			return '';
		}

		$content = $this->doRender();

		if($this->shouldExcludeFromSearch()) {
			$content = ContaoUtil::excludeFromSearch($content);
		}

		$this->popStack();

		return $content;
	}

	/**
	 * @see \Hofff\Contao\Content\Renderer\Renderer::__toString()
	 */
	public function __toString() {
		return $this->render();
	}

	/**
	 * @return boolean
	 */
	public function isValid(): bool {
		return true;
	}

	/**
	 * @return string
	 */
	protected abstract function getCacheKey();

	/**
	 * @return string
	 */
	protected abstract function doRender();

	/**
	 * @return boolean
	 */
	protected function shouldExcludeFromSearch() {
		return $this->getExcludeFromSearch() || $this->isProtected();
	}

	/**
	 * @return boolean
	 */
	protected function isProtected() {
		return false;
	}

	/**
	 * @param object $element
	 * @return void
	 */
	protected function applyCSSClassesAndID($element) {
		$classes = $this->getCSSClasses();
		$id = $this->getCSSID();

		if($classes === null && $id === null) {
			return;
		}

		$css = $element->cssID;

		$classes === null || $css[1] = trim($classes . ' ' . $css[1]);
		$id === null || $css[0] = $id;

		$element->cssID = $css;
	}

	/**
	 * @return boolean
	 */
	private function pushStack() {
		$key = $this->getCacheKey();

		if(isset(self::$renderStack[$key])) {
			return false;
		}

		return self::$renderStack[$key] = true;
	}

	/**
	 * @return void
	 */
	private function popStack() {
		unset(self::$renderStack[$this->getCacheKey()]);
	}

}
