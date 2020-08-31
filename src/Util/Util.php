<?php

namespace Hofff\Contao\Content\Util;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class Util {

	/**
	 * @return boolean
	 */
	public static function isLanguageRelationsLoaded() {
        return ContaoUtil::isModuleLoaded('HofffContaoContentBundle')
            || ContaoUtil::isModuleLoaded('hofff_language_relations');
	}

}
