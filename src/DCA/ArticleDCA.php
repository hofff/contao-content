<?php

namespace Hofff\Contao\Content\DCA;

use Contao\System;

/**
 * @author Oliver Hoff <oliver@hofff.com>
 */
class ArticleDCA {

	/**
	 * @param array $row
	 * @param string $label
	 * @return string
	 */
	public function labelCallback($row, $label) {
		$callback = $GLOBALS['TL_DCA']['tl_article']['list']['label']['label_callback_hofff_content'];
		$label = call_user_func_array(
			[ System::importStatic($callback[0]), $callback[1] ],
			func_get_args()
		);

		if($row['hofff_content_hide']) {
			$label .= sprintf(
				' <span style="color:#4b85ba;padding-left:3px">[%s]</span>',
				$GLOBALS['TL_LANG']['tl_article']['hofff_content_hide'][0]
			);
		}

		return $label;
	}

}
