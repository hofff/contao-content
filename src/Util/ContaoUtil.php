<?php

namespace Hofff\Contao\Content\Util;

use Contao\BackendTemplate;
use Contao\Date;
use Contao\FrontendTemplate;
use Contao\Image;
use Contao\Template;
use Contao\Widget;
use Contao\ModuleLoader;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class ContaoUtil {

	/**
	 * @var string
	 */
	const INDEXER_STOP		= '<!-- indexer::stop -->';
	/**
	 * @var string
	 */
	const INDEXER_CONTINUE	= '<!-- indexer::continue -->';

	/**
	 * @var array
	 */
	private static $indexerTokens = [ self::INDEXER_STOP, self::INDEXER_CONTINUE ];

	/**
	 * @param object $model
	 * @param boolean $checkBackendUser
	 * @return boolean
	 */
	public static function isPublished($model, $checkBackendUser = true) {
		if($checkBackendUser && BE_USER_LOGGED_IN) {
			return true;
		}

		$time = Date::floorToMinute();

		return $model->published
			&& (!$model->start || $model->start <= $time)
			&& (!$model->stop || $model->stop > $time + 60);
	}

	/**
	 * @param string $content
	 * @return string
	 */
	public static function excludeFromSearch($content) {
		if(!strlen($content)) {
			return $content;
		}

		$content = str_replace(self::$indexerTokens, '', $content);
		$content = self::INDEXER_STOP . $content . self::INDEXER_CONTINUE;

		return $content;
	}

	/**
	 * @param string $tpl
	 * @param array $data
	 * @return Template
	 */
	public static function createTemplate($tpl, array $data = null) {
		$class = TL_MODE == 'FE' ? FrontendTemplate::class : BackendTemplate::class;
		/* @var $tpl Template */
		$tpl = new $class($tpl);
		$data && $tpl->setData($data);
		return $tpl;
	}

	/**
	 * @param Widget $widget
	 * @return string
	 */
	public static function renderBackendWidget(Widget $widget) {
		$description = '';

		if(!$widget->hasErrors() && strlen($widget->description)) {
			$description = sprintf(
				'<p class="tl_help tl_tip">%s</p>',
				$widget->description
			);
		}

		$content = sprintf(
			'<div class="%s">%s%s</div>',
			$widget->tl_class,
			$widget->parse(),
			$description
		);

		return $content;
	}

	/**
	 * @param array $query
	 * @param string $image
	 * @param string $label
	 * @return string
	 */
	public static function generateBackendIconLink(array $query, $image, $label) {
		$title = sprintf($label[1], isset($query['id']) ? $query['id'] : $query['pn']);

		$query['rt'] = REQUEST_TOKEN;
		$query = http_build_query($query, null, '&amp;');

		return sprintf(
			'<a href="contao/main.php?%s" title="%s" data-title="%s" class="hofff-content-edit">%s %s</a>',
			$query,
			$title,
			$title,
			Image::getHtml($image, $title),
			$label[0]
		);
	}

	/**
	 * @param string $module
	 * @return boolean
	 */
	public static function isModuleLoaded($module) {
		return in_array($module, ModuleLoader::getActive());
	}

}
