<?php

/**
 * Extend default palette
 */
$GLOBALS['TL_DCA']['tl_user_group']['palettes']['default'] = str_replace('fop;', 'fop;{ccol_legend},ccol,ccolp;', $GLOBALS['TL_DCA']['tl_user_group']['palettes']['default']);

/**
 * Add fields to tl_user_group
 */
$GLOBALS['TL_DCA']['tl_user_group']['fields']['ccol'] = [
	'label'                   => &$GLOBALS['TL_LANG']['tl_user_group']['ccol'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'foreignKey'              => 'tl_custom_collection_archive.coltype',
	'eval'                    => ['multiple'=>true],
	'sql'                     => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_user_group']['fields']['ccolp'] = [
	'label'                   => &$GLOBALS['TL_LANG']['tl_user_group']['ccolp'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'options'                 => ['create', 'delete'],
	'reference'               => &$GLOBALS['TL_LANG']['MSC'],
	'eval'                    => ['multiple'=>true],
	'sql'                     => "blob NULL"
];
