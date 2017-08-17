<?php

/**
 * Add palettes to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['ccol']    = '{title_legend},name,type;{config_legend},collection;{template_legend:hide},collection_template,customTpl';

/**
 * Add fields to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['collection'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['collection'],
    'exclude'                 => true,
    'inputType'               => 'radio',
    'options_callback'        => ['tl_module_collection', 'getCollections'],
    'eval'                    => ['multiple'=>false, 'mandatory'=>true],
    'sql'                     => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['collection_template'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['collection_template'],
    'default'                 => 'collection_default',
    'exclude'                 => true,
    'inputType'               => 'select',
    'options_callback'        => ['tl_module_collection', 'getCollectionTemplates'],
    'eval'                    => ['tl_class'=>'w50'],
    'sql'                     => "varchar(64) NOT NULL default ''"
];


/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 */
class tl_module_collection extends Backend
{

    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }


    /**
     * Get all news archives and return them as array
     *
     * @return array
     */
    public function getCollections()
    {
        if (!$this->User->isAdmin && !is_array($this->User->news))
        {
            return [];
        }

        $arrArchives = [];
        $objArchives = $this->Database->execute("SELECT id, colType FROM tl_custom_collection_archive ORDER BY colType");

        while ($objArchives->next())
        {
            $arrArchives[$objArchives->id] = $objArchives->colType;
        }

        return $arrArchives;
    }


    /**
     * Return all news templates as array
     *
     * @return array
     */
    public function getCollectionTemplates()
    {
        return $this->getTemplateGroup('collection_');
    }
}
