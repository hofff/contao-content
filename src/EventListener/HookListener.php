<?php

namespace Hofff\Contao\Content\EventListener;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class HookListener {

	/**
	 * @param object $row
	 * @param boolean $visible
	 * @return boolean
	 */
	public function isVisibleElement($row, $visible) {
		return $visible && !$row->hofff_content_hide;
	}

}
