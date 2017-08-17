<?php

/**
 * Extend default palette
 */
$GLOBALS['TL_DCA']['tl_user']['palettes']['extend'] = str_replace('fop;', 'fop;{ccol_legend},ccol,ccolp;', $GLOBALS['TL_DCA']['tl_user']['palettes']['extend']);
$GLOBALS['TL_DCA']['tl_user']['palettes']['custom'] = str_replace('fop;', 'fop;{ccol_legend},ccol,ccolp;', $GLOBALS['TL_DCA']['tl_user']['palettes']['custom']);


/**
 * Add fields to tl_user_group
 */
$GLOBALS['TL_DCA']['tl_user']['fields']['ccol'] = [
	'label'                   => &$GLOBALS['TL_LANG']['tl_user']['ccol'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'foreignKey'              => 'tl_custom_collection_archive.coltype',
	'eval'                    => ['multiple'=>true],
	'sql'                     => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_user']['fields']['ccolp'] = [
	'label'                   => &$GLOBALS['TL_LANG']['tl_user']['ccolp'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'options'                 => ['create', 'delete'],
	'reference'               => &$GLOBALS['TL_LANG']['MSC'],
	'eval'                    => ['multiple'=>true],
	'sql'                     => "blob NULL"
];
