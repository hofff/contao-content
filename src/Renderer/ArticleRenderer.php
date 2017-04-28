<?php

namespace Hofff\Contao\Content\Renderer;

use Contao\ArticleModel;
use Contao\ModuleArticle;
use Contao\System;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class ArticleRenderer extends AbstractRenderer {

	/**
	 * @var ArticleModel
	 */
	private $article;

	/**
	 * @var boolean
	 */
	private $renderContainer;

	/**
	 */
	public function __construct() {
		parent::__construct();
		$this->renderContainer = false;
	}

	/**
	 * @return ArticleModel
	 */
	public function getArticle() {
		return $this->article;
	}

	/**
	 * @param ArticleModel $article
	 * @return void
	 */
	public function setArticle(ArticleModel $article) {
		$this->article = $article;
	}

	/**
	 * @return boolean
	 */
	public function getRenderContainer() {
		return $this->renderContainer;
	}

	/**
	 * @param boolean $renderContainer
	 * @return void
	 */
	public function setRenderContainer($renderContainer) {
		$this->renderContainer = (bool) $renderContainer;
	}

	/**
	 * @see \Hofff\Contao\Content\Renderer\AbstractRenderer::isValid()
	 */
	protected function isValid() {
		return (bool) $this->getArticle();
	}

	/**
	 * @see \Hofff\Contao\Content\Renderer\AbstractRenderer::getCacheKey()
	 */
	protected function getCacheKey() {
		return ArticleRenderer::class . $this->getArticle()->id;
	}

	/**
	 * @see \Hofff\Contao\Content\Renderer\AbstractRenderer::doRender()
	 */
	protected function doRender() {
		$article = $this->getArticle();
		$article->headline = $article->title;
		$article->multiMode = false;

		$this->executeGetArticleHook($article);

		$module = new ModuleArticle($article, $this->getColumn());

		$this->applyCSSClassesAndID($module);

		$content = $module->generate(!$this->getRenderContainer());

		return $content;
	}

	/**
	 * @see \Hofff\Contao\Content\Renderer\AbstractRenderer::isProtected()
	 */
	protected function isProtected() {
		return $this->getArticle()->protected;
	}

	/**
	 * @param ArticleModel $article
	 * @return void
	 */
	protected function executeGetArticleHook(ArticleModel &$article) {
		if(!isset($GLOBALS['TL_HOOKS']['getArticle'])) {
			return;
		}
		if(!is_array($GLOBALS['TL_HOOKS']['getArticle'])) {
			return;
		}

		foreach($GLOBALS['TL_HOOKS']['getArticle'] as $callback) {
			call_user_func(
				[ System::importStatic($callback[0]), $callback[1] ],
				$article
			);
		}
	}

}
