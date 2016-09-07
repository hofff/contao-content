<?php

namespace Hofff\Contao\Content\Frontend;

use Hofff\Contao\Content\Renderer\Renderer;
use Hofff\Contao\Content\Renderer\RendererFactory;
use Hofff\Contao\Content\Util\ContaoUtil;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
trait ReferencesTrait {

	/**
	 * @var array<Renderer>
	 */
	protected $renderers;

	/**
	 * @return string
	 */
	public function generate() {
		$this->strTemplate = $this->hofff_content_template;

		$this->renderers = RendererFactory::createAll(
			$this->hofff_content_references,
			$this->strColumn
		);

		if($this->hofff_content_exclude_from_search) {
			foreach($this->renderers as $renderer) {
				$renderer->setExcludeFromSearch(false);
			}
		}

		if(strlen($this->strTemplate)) {
			$content = parent::generate();
		} else {
			$content = implode("\n", $this->renderers);
		}

		if($this->hofff_content_exclude_from_search) {
			$content = ContaoUtil::excludeFromSearch($content);
		}

		return $content;
	}

	/**
	 * @return void
	 */
	protected function compile() {
		$this->Template->renderers = $this->renderers;
		$this->Template->column = $this->strColumn;
	}

}
