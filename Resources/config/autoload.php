<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * NamespaceClassLoader
 */
NamespaceClassLoader::add('CustomCollection', 'system/modules/custom-collection/src');

TemplateLoader::addFiles([
    // ContentElements
    'collection_default' => 'system/modules/custom-collection/templates',
    'gallery_collection' => 'system/modules/custom-collection/templates',
    'mod_custom_collection' => 'system/modules/custom-collection/templates',
]);