<?php

/**
 * Backend Modules
 */

$GLOBALS['BE_MOD']['content']['ccol'] = [
    'tables' => ['tl_custom_collection_archive', 'tl_custom_collection'],
    'icon' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAl0lEQVQ4T93TzQkCURAD4G/7UOvwpwwV7ELFNgTFJlSwDHGxDLEQZeAtDnv14GJOjxDCy0ymwgBjHzxQY5G4eJ4wQT/x16oIj4k8F+7VMgjtBdPEz7thMMIyfeuGQ8mcU8RMVhgmchcRvkI3ZhDZfrvGr4cY9VznemJfWpfNZ9gg1t5g+ydb6LXq+cQdkTkjLjFqHPoG9RtpATv/Wdvs3QAAAABJRU5ErkJggg==',
];

/**
 * Content Elements
 */

// Frontend modules
$GLOBALS['FE_MOD']['miscellaneous']['ccol'] = 'CustomCollection\Module\CustomCollectionModule';

// Models
$GLOBALS['TL_MODELS']['tl_custom_collection'] = 'CustomCollection\Model\CustomCollectionModel';
$GLOBALS['TL_MODELS']['tl_custom_collection_archive'] = 'CustomCollection\Model\CustomCollectionArchiveModel';


/**
 * Hooks
 */
