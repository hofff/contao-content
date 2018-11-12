<?php

call_user_func(function() {
	$palette = &$GLOBALS['TL_DCA']['tl_article']['palettes']['default'];
	$palette = str_replace(',published', ',published,hofff_content_hide', $palette);
	unset($palette);

	$label = &$GLOBALS['TL_DCA']['tl_article']['list']['label'];
	$label['label_callback_hofff_content'] = $label['label_callback'];
	$label['label_callback'] = [ 'Hofff\\Contao\\Content\\DCA\\ArticleDCA', 'labelCallback' ];
	unset($label);
});

$GLOBALS['TL_DCA']['tl_article']['fields']['hofff_content_hide'] = [
	'label'		=> &$GLOBALS['TL_LANG']['tl_article']['hofff_content_hide'],
	'exclude'	=> true,
	'inputType'	=> 'checkbox',
	'eval'		=> [
		'tl_class'	=> 'clr w50 cbx',
	],
	'sql'		=> 'char(1) NOT NULL default \'\'',
];
