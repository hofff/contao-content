<?php

namespace Hofff\Contao\Content\Renderer;

use Contao\ModuleModel;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class ModuleRenderer extends AbstractRenderer {

	/**
	 * @var ModuleModel
	 */
	private $module;

	/**
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * @return ModuleModel
	 */
	public function getModule() {
		return $this->module;
	}

	/**
	 * @param ModuleModel $module
	 * @return void
	 */
	public function setModule(ModuleModel $module) {
		$this->module = $module;
	}

	/**
	 * @return boolean
	 */
	protected function isValid() {
		return (bool) $this->getModule();
	}

	/**
	 * @return string
	 */
	protected function getCacheKey() {
		return self::class . $this->getModule()->id;
	}

	/**
	 * @return string
	 */
	protected function doRender() {
		return '';
	}

	/**
	 * @see \Hofff\Contao\Content\Renderer\AbstractRenderer::isProtected()
	 */
	protected function isProtected() {
		return $this->getModule()->protected;
	}

}
