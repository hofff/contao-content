<?php

namespace Hofff\Contao\Content\DCA;

use Contao\Controller;
use Contao\Database;
use Contao\Image;
use Hofff\Contao\Content\Util\ContaoUtil;
use Hofff\Contao\Selectri\Model\Node;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class DCA {

	/**
	 * @param string $prefixesKey
	 * @return callable
	 */
	public static function getTemplateGroupOptionsCallback($prefixesKey) {
		return function($dc) use($prefixesKey) {
			if(!$dc) {
				return [];
			}

			$prefixes = &$GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field][$prefixesKey];
			if(!is_array($prefixes)) {
				return [];
			}

			$templates = [];
			foreach($prefixes as $prefix) {
				$templates[] = Controller::getTemplateGroup($prefix);
			}

			return call_user_func_array('array_replace', $templates);
		};
	}

	/**
	 * @return callable
	 */
	public static function getLayoutSectionOptionsCallback() {
		static $sections = null;
		return function() use(&$sections) {
			if(isset($sections)) {
				return $sections;
			}

			$defaultSections = [];
			foreach([
				'header',
				'left',
				'right',
				'main',
				'footer',
			] as $section) {
				$defaultSections[$section] = $GLOBALS['TL_LANG']['COLS'][$section];
			}

			$sections = [];
			$sql = 'SELECT sections FROM tl_layout WHERE sections != \'\'';
			$layout = Database::getInstance()->query($sql);
			while($layout->next()) {
				$custom = deserialize($layout->sections, true);

				foreach($custom as $section) {
					$sections[$section['id']] = $section['title'];
				}
			}
			asort($sections, SORT_LOCALE_STRING);

			$sections = array_merge($defaultSections, $sections);

			return $sections;
		};
	}

	/**
	 * @param Node $node
	 * @return string
	 */
	public static function getArticleIcon(Node $node) {
		$published = ContaoUtil::isPublished((object) $node->getData());
		return Image::getPath($published ? 'articles.gif' : 'articles_.gif');
	}

	/**
	 * @param Node $node
	 * @return string
	 */
	public static function getArticleLabel(Node $node) {
		$data = $node->getData();

		$column = $data['in_column'];
		if(isset($GLOBALS['TL_LANG']['COLS'][$column])) {
			$column = $GLOBALS['TL_LANG']['COLS'][$column];
		}

		$label = sprintf(
			'%s <span class="hofff-content-label">[%s] (ID %s)</span>',
			$data['title'],
			$column,
			$data['id']
		);

		return $label;
	}

	/**
	 * @param Node $node
	 * @return string
	 */
	public static function getPageIcon(Node $node) {
		return Image::getPath(Controller::getPageStatusIcon((object) $node->getData()));
	}

	/**
	 * @param Node $node
	 * @return string
	 */
	public static function getModuleLabel(Node $node) {
		$data = $node->getData();

		$type = $data['type'];
		if(isset($GLOBALS['TL_LANG']['FMD'][$type][0])) {
			$type = $GLOBALS['TL_LANG']['FMD'][$type][0];
		}

		$label = sprintf(
			'%s <span class="hofff-content-label">[%s] (ID %s)</span>',
			$data['title'],
			$type,
			$data['id']
		);

		return $label;
	}

}
