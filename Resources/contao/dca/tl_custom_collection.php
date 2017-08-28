<?php

use Oneup\Bundle\CustomCollectionModel;
use Oneup\Bundle\ContaoCustomCollectionBundle\Model\CustomCollectionArchiveModel;

/**
 * Table tl_custom_collection
 */
$GLOBALS['TL_DCA']['tl_custom_collection'] = [
    // Config
    'config' => [
        'dataContainer'               => 'Table',
        'ptable'                      => 'tl_custom_collection_archive',
        'switchToEdit'                => true,
        'enableVersioning'            => true,
        'onload_callback'             => [
            ['tl_custom_collection', 'checkPermission'],
            ['tl_custom_collection', 'saveCollectionType']
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'alias' => 'index',
                'pid,start,stop,published' => 'index'
            ]
        ]
    ],

    // List
    'list' => [
        'sorting' => [
            'mode'                    => 4,
            'fields'                  => ['sorting'],
            'headerFields'            => ['coltype'],
            'panelLayout'             => '',
            'child_record_callback'   => ['tl_custom_collection', 'addItem']
        ],
        'global_operations' => [ 
            'all' => [
                'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'                => 'act=select',
                'class'               => 'header_edit_all',
                'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ]
        ],
        'operations' => [
            'edit' => [
                'label'               => &$GLOBALS['TL_LANG']['tl_custom_collection']['edit'],
                'href'                => 'act=edit',
                'icon'                => 'edit.gif'
            ],
            'copy' => [
                'label'               => &$GLOBALS['TL_LANG']['tl_custom_collection']['copy'],
                'href'                => 'act=paste&amp;mode=copy',
                'icon'                => 'copy.gif'
            ],
            'delete' => [
                'label'               => &$GLOBALS['TL_LANG']['tl_custom_collection']['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.gif',
                'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ],
            'toggle' => [
                'label'               => &$GLOBALS['TL_LANG']['tl_custom_collection']['toggle'],
                'icon'                => 'visible.gif',
                'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback'     => ['tl_custom_collection', 'toggleIcon']
            ]
        ]
    ],

    // Palettes
    'palettes' => [
        '__selector__'                => ['type','addImage','addImages','published'],
        'default'                     => '{title_legend},title,type;{expert_legend:hide},cssClass;{publish_legend},published',
    ],

    // Subpalettes
    /*
     * To add a custom subpalette, just add 'type_' and what ever the collection is called (in snake_case)
     */
    'subpalettes' => [
        'addImage'                    => 'singleSRC,size,alt,imageLink',
        'addImages'                   => 'multiSRC,sortBy,numberOfItems,size,alt,imageLink,galleryTpl',
        'published'                   => 'start,stop'
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
        ],
        'pid' => [
            'foreignKey'              => 'tl_custom_collection_archive.id',
            'sql'                     => "int(10) unsigned NOT NULL default '0'",
            'relation'                => ['type'=>'belongsTo', 'load'=>'eager']
        ],
        'tstamp' => [
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ],
        'sorting' => [
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ],
        'type' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_custom_collection']['type'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'text',
            'eval'                    => ['tl_class'=>'invisible'],
            'sql'                     => "varchar(64) NOT NULL default ''"
        ],
        'title' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_custom_collection']['title'],
            'exclude'                 => true,
            'search'                  => true,
            'sorting'                 => true,
            'flag'                    => 1,
            'inputType'               => 'text',
            'eval'                    => ['mandatory'=>true, 'maxlength'=>255],
            'sql'                     => "varchar(255) NOT NULL default ''"
        ],
        'alias' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_custom_collection']['alias'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => ['rgxp'=>'alias', 'unique'=>true, 'maxlength'=>128, 'tl_class'=>'w50'],
            'save_callback' => [
                ['tl_custom_collection', 'generateAlias']
            ],
            'sql'                     => "varchar(128) COLLATE utf8_bin NOT NULL default ''"
        ],
        'text' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_custom_collection']['text'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => ['mandatory'=>true, 'rte'=>'tinyMCE', 'helpwizard'=>true],
            'explanation'             => 'insertTags',
            'sql'                     => "mediumtext NULL"
        ],
        'addImage' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_custom_collection']['addImage'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => ['submitOnChange'=>true],
            'sql'                     => "char(1) NOT NULL default ''"
        ],
        'singleSRC' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_custom_collection']['singleSRC'],
            'exclude'                 => true,
            'inputType'               => 'fileTree',
            'eval'                    => ['filesOnly'=>true, 'extensions'=>Config::get('validImageTypes'), 'fieldType'=>'radio', 'mandatory'=>true],
            'save_callback' => [
                ['tl_custom_collection', 'storeFileMetaInformation']
            ],
            'sql'                     => "binary(16) NULL"
        ],
        'alt' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_custom_collection']['alt'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => ['maxlength'=>255, 'tl_class'=>'w50'],
            'sql'                     => "varchar(255) NOT NULL default ''"
        ],
        'size' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_custom_collection']['size'],
            'exclude'                 => true,
            'inputType'               => 'imageSize',
            'options'                 => System::getImageSizes(),
            'reference'               => &$GLOBALS['TL_LANG']['MSC'],
            'eval'                    => ['rgxp'=>'natural', 'includeBlankOption'=>true, 'nospace'=>true, 'helpwizard'=>true, 'tl_class'=>'w50'],
            'sql'                     => "varchar(64) NOT NULL default ''"
        ],
        'imageLink' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_custom_collection']['imageLink'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'sql'                     => "char(1) NOT NULL default ''"
        ],
        'addImages' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_custom_collection']['addImages'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => ['submitOnChange'=>true],
            'sql'                     => "char(1) NOT NULL default ''"
        ],
        'multiSRC' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_custom_collection']['multiSRC'],
            'exclude'                 => true,
            'inputType'               => 'fileTree',
            'eval'                    => ['multiple'=>true, 'fieldType'=>'checkbox', 'orderField'=>'orderSRC', 'files'=>true, 'mandatory'=>true],
            'sql'                     => "blob NULL",
            'load_callback' => [
                ['tl_custom_collection', 'setMultiSrcFlags']
            ]
        ],
        'orderSRC' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_custom_collection']['orderSRC'],
            'sql'                     => "blob NULL"
        ],
        'numberOfItems' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_custom_collection']['numberOfItems'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => ['rgxp'=>'natural', 'tl_class'=>'w50'],
            'sql'                     => "smallint(5) unsigned NOT NULL default '0'"
        ],
        'sortBy' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_custom_collection']['sortBy'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => ['custom', 'name_asc', 'name_desc', 'date_asc', 'date_desc', 'random'],
            'reference'               => &$GLOBALS['TL_LANG']['tl_custom_collection'],
            'eval'                    => ['tl_class'=>'w50'],
            'sql'                     => "varchar(32) NOT NULL default ''"
        ],
        'galleryTpl' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_custom_collection']['galleryTpl'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options_callback'        => ['tl_custom_collection', 'getGalleryTemplates'],
            'eval'                    => ['tl_class'=>'w50'],
            'sql'                     => "varchar(64) NOT NULL default ''"
        ],
        'html' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_custom_collection']['html'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => ['mandatory'=>true, 'allowHtml'=>true, 'class'=>'monospace', 'rte'=>'ace|html', 'helpwizard'=>true],
            'explanation'             => 'insertTags',
            'sql'                     => "mediumtext NULL"
        ],
        'listtype' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_custom_collection']['listtype'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => ['ordered', 'unordered'],
            'reference'               => &$GLOBALS['TL_LANG']['tl_custom_collection'],
            'sql'                     => "varchar(32) NOT NULL default ''"
        ],
        'listitems' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_custom_collection']['listitems'],
            'exclude'                 => true,
            'inputType'               => 'listWizard',
            'eval'                    => ['allowHtml'=>true],
            'xlabel' => [
                ['tl_custom_collection', 'listImportWizard']
            ],
            'sql'                     => "blob NULL"
        ],
        'url' => [
            'label'                   => &$GLOBALS['TL_LANG']['MSC']['url'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => ['mandatory'=>true, 'rgxp'=>'url', 'decodeEntities'=>true, 'maxlength'=>255, 'fieldType'=>'radio', 'filesOnly'=>true, 'tl_class'=>'w50 wizard'],
            'wizard' => [
                ['tl_custom_collection', 'pagePicker']
            ],
            'sql'                     => "varchar(255) NOT NULL default ''"
        ],
        'target' => [
            'label'                   => &$GLOBALS['TL_LANG']['MSC']['target'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => ['tl_class'=>'w50 m12'],
            'sql'                     => "char(1) NOT NULL default ''"
        ],
        'titleText' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_custom_collection']['titleText'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => ['maxlength'=>255, 'tl_class'=>'w50'],
            'sql'                     => "varchar(255) NOT NULL default ''"
        ],
        'linkTitle' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_custom_collection']['linkTitle'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => ['maxlength'=>255, 'tl_class'=>'w50'],
            'sql'                     => "varchar(255) NOT NULL default ''"
        ],
        'cssClass' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_custom_collection']['cssClass'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'sql'                     => "varchar(255) NOT NULL default ''"
        ],
        'published' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_custom_collection']['published'],
            'exclude'                 => true,
            'filter'                  => true,
            'flag'                    => 1,
            'inputType'               => 'checkbox',
            'eval'                    => ['submitOnChange'=>true, 'doNotCopy'=>true],
            'sql'                     => "char(1) NOT NULL default ''"
        ],
        'start' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_custom_collection']['start'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => ['rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'],
            'sql'                     => "varchar(10) NOT NULL default ''"
        ],
        'stop' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_custom_collection']['stop'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => ['rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'],
            'sql'                     => "varchar(10) NOT NULL default ''"
        ]
    ]
];


/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class tl_custom_collection extends Backend
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
     * Check permissions to edit table tl_custom_collection
     */
    public function checkPermission()
    {
        if ($this->User->isAdmin)
        {
            return;
        }

        // Check permissions to add items
        if (!$this->User->hasAccess('create', 'ccolp'))
        {
            $GLOBALS['TL_DCA']['tl_custom_collection']['config']['closed'] = true;
        }

        // Set the root IDs
        if (!is_array($this->User->ccol) || empty($this->User->ccol))
        {
            $root = [0];
        }
        else
        {
            $root = $this->User->ccol;
        }

        $id = strlen(Input::get('id')) ? Input::get('id') : CURRENT_ID;
        $method = strlen(Input::get('act')) ? Input::get('act') : 'none';

        // Check current action
        if ($method) {
            switch ($method)
            {
                case 'paste':
                    // Allow
                    break;

                case 'create':
                    if (!strlen(Input::get('pid')) || !in_array(Input::get('pid'), $root))
                    {
                        $this->log('Not enough permissions to create items in custom collection with ID "'.Input::get('pid').'"', __METHOD__, TL_ERROR);
                        $this->redirect('contao/main.php?act=error');
                    }
                    break;

                case 'cut':
                case 'copy':
                    if (!in_array(Input::get('pid'), $root))
                    {
                        $this->log('Not enough permissions to '.Input::get('act').' item ID "'.$id.'" to custom collection with ID "'.Input::get('pid').'"', __METHOD__, TL_ERROR);
                        $this->redirect('contao/main.php?act=error');
                    }
                    // NO BREAK STATEMENT HERE

                case 'edit':
                case 'delete':
                case 'toggle':
                    $objArchive = $this->Database->prepare("SELECT pid FROM tl_custom_collection WHERE id=?")
                                                 ->limit(1)
                                                 ->execute($id);

                    if ($objArchive->numRows < 1)
                    {
                        $this->log('Invalid item ID "'.$id.'"', __METHOD__, TL_ERROR);
                        $this->redirect('contao/main.php?act=error');
                    }

                    if (!in_array($objArchive->pid, $root))
                    {
                        $this->log('Not enough permissions to '.Input::get('act').' item ID "'.$id.'" of custom collection with ID "'.$objArchive->pid.'"', __METHOD__, TL_ERROR);
                        $this->redirect('contao/main.php?act=error');
                    }
                    break;

                case 'select':
                case 'editAll':
                case 'deleteAll':
                case 'overrideAll':
                case 'cutAll':
                case 'copyAll':
                    if (!in_array($id, $root))
                    {
                        $this->log('Not enough permissions to access custom collection with ID "'.$id.'"', __METHOD__, TL_ERROR);
                        $this->redirect('contao/main.php?act=error');
                    }

                    $objArchive = $this->Database->prepare("SELECT id FROM tl_custom_collection WHERE pid=?")
                                                 ->execute($id);

                    if ($objArchive->numRows < 1)
                    {
                        $this->log('Invalid custom collection with ID "'.$id.'"', __METHOD__, TL_ERROR);
                        $this->redirect('contao/main.php?act=error');
                    }

                    $session = $this->Session->getData();
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $objArchive->fetchEach('id'));
                    $this->Session->setData($session);
                    break;
                case 'none':
                    break;
                default:
                    if (!in_array($id, $root))
                    {
                        $this->log('Not enough permissions to access custom collection with ID ' . $id, __METHOD__, TL_ERROR);
                        $this->redirect('contao/main.php?act=error');
                    }
                    break;
            }
        }
    }

    public function saveCollectionType(DC_Table $dt)
    {
        $id = Input::get('id');
        $pid = CustomCollectionModel::findById($id)->pid;
        $type = CustomCollectionArchiveModel::findById($pid)->coltype;
        $type = strtolower(preg_replace('/\s/', '_', $type));

        $this->Database->prepare("UPDATE tl_custom_collection SET type=? WHERE id=?")->execute($type, $id);

        return true;
    }

    /**
     * Auto-generate the alias if it has not been set yet
     *
     * @param mixed         $varValue
     * @param DataContainer $dc
     *
     * @return string
     *
     * @throws Exception
     */
    public function generateAlias($varValue, DataContainer $dc)
    {
        $autoAlias = false;

        // Generate alias if there is none
        if ($varValue == '')
        {
            $autoAlias = true;
            $varValue = StringUtil::generateAlias($dc->activeRecord->title);
        }

        $objAlias = $this->Database->prepare("SELECT id FROM tl_custom_collection WHERE alias=?")
                                   ->execute($varValue);

        // Check whether the news alias exists
        if ($objAlias->numRows > 1 && !$autoAlias)
        {
            throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
        }

        // Add ID to alias
        if ($objAlias->numRows && $autoAlias)
        {
            $varValue .= '-' . $dc->id;
        }

        return $varValue;
    }

    /**
     * Pre-fill the "alt" and "caption" fields with the file meta data
     *
     * @param mixed         $varValue
     * @param DataContainer $dc
     *
     * @return mixed
     */
    public function storeFileMetaInformation($varValue, DataContainer $dc)
    {
        if ($dc->activeRecord->singleSRC != $varValue)
        {
            $this->addFileMetaInformationToRequest($varValue, 'tl_custom_collection_archive', $dc->activeRecord->pid);
        }

        return $varValue;
    }

    /**
     * Dynamically add flags to the "multiSRC" field
     *
     * @param mixed         $varValue
     * @param DataContainer $dc
     *
     * @return mixed
     */
    public function setMultiSrcFlags($varValue, DataContainer $dc)
    {
        if ($dc->activeRecord) {
            switch ($dc->activeRecord->type) {
                case 'gallery':
                    $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['isGallery'] = true;
                    $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['extensions'] = Config::get('validImageTypes');
                    break;

                case 'downloads':
                    $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['isDownloads'] = true;
                    $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['extensions'] = Config::get('allowedDownload');
                    break;
            }
        }

        return $varValue;
    }

    /**
     * Return the link picker wizard
     *
     * @param DataContainer $dc
     *
     * @return string
     */
    public function pagePicker(DataContainer $dc)
    {
        return ' <a href="' . (($dc->value == '' || strpos($dc->value, '{{link_url::') !== false) ? 'contao/page.php' : 'contao/file.php') . '?do=' . Input::get('do') . '&amp;table=' . $dc->table . '&amp;field=' . $dc->field . '&amp;value=' . rawurlencode(str_replace(array('{{link_url::', '}}'), '', $dc->value)) . '&amp;switch=1' . '" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['pagepicker']) . '" onclick="Backend.getScrollOffset();Backend.openModalSelector({\'width\':768,\'title\':\'' . specialchars(str_replace("'", "\\'", $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['label'][0])) . '\',\'url\':this.href,\'id\':\'' . $dc->field . '\',\'tag\':\'ctrl_'. $dc->field . ((Input::get('act') == 'editAll') ? '_' . $dc->id : '') . '\',\'self\':this});return false">' . Image::getHtml('pickpage.gif', $GLOBALS['TL_LANG']['MSC']['pagepicker'], 'style="vertical-align:top;cursor:pointer"') . '</a>';
    }

    /**
     * Add the type of content element
     *
     * @param array $arrRow
     *
     * @return string
     */
    public function addItem($arrRow)
    {
        return '<div class="tl_custom_collection_left">' . $arrRow['title'] . '</div>';
    }

    /**
     * Return all gallery templates as array
     *
     * @return array
     */
    public function getGalleryTemplates()
    {
        return $this->getTemplateGroup('gallery_');
    }

    /**
     * Add a link to the list items import wizard
     *
     * @return string
     */
    public function listImportWizard()
    {
        return ' <a href="' . $this->addToUrl('key=list') . '" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['lw_import'][1]) . '" onclick="Backend.getScrollOffset()">' . Image::getHtml('tablewizard.gif', $GLOBALS['TL_LANG']['MSC']['tw_import'][0], 'style="vertical-align:text-bottom"') . '</a>';
    }

    /**
     * Return the "toggle visibility" button
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (strlen(Input::get('tid')))
        {
            $this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }


        $href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

        if (!$row['published'])
        {
            $icon = 'invisible.gif';
        }

        return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label, 'data-state="' . ($row['published'] ? 1 : 0) . '"').'</a> ';
    }


    /**
     * Disable/enable a user group
     *
     * @param integer       $intId
     * @param boolean       $blnVisible
     * @param DataContainer $dc
     */
    public function toggleVisibility($intId, $blnVisible, DataContainer $dc=null)
    {
        // Set the ID and action
        Input::setGet('id', $intId);
        Input::setGet('act', 'toggle');
        if ($dc)
        {
            $dc->id = $intId; // see #8043
        }

        $objVersions = new Versions('tl_custom_collection', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_custom_collection']['fields']['published']['save_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_custom_collection']['fields']['published']['save_callback'] as $callback)
            {
                if (is_array($callback))
                {
                    $this->import($callback[0]);
                    $blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, ($dc ?: $this));
                }
                elseif (is_callable($callback))
                {
                    $blnVisible = $callback($blnVisible, ($dc ?: $this));
                }
            }
        }

        // Update the database
        $this->Database->prepare("UPDATE tl_custom_collection SET tstamp=". time() .", published='" . ($blnVisible ? '1' : '') . "' WHERE id=?")
                       ->execute($intId);

        $objVersions->create();
    }
}
