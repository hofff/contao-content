<?php

namespace Hofff\Contao\Content;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class Hooks {

	/**
	 * @param object $row
	 * @param boolean $visible
	 * @return boolean
	 */
	public function isVisibleElement($row, $visible) {
		return $visible && !$row->hofff_content_hide;
	}

}
