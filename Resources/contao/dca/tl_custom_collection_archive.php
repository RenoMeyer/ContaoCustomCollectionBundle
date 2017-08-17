<?php

/**
 * Table tl_custom_collection_archive
 */
$GLOBALS['TL_DCA']['tl_custom_collection_archive'] = [
    // Config
    'config' => [
        'dataContainer'               => 'Table',
        'ctable'                      => ['tl_custom_collection'],
        'switchToEdit'                => true,
        'enableVersioning'            => true,
        'onload_callback'             => [
            ['tl_custom_collection_archive', 'checkPermission']
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary'
            ]
        ]
    ],

    // List
    'list' => [
        'sorting' => [
            'mode'                    => 1,
            'fields'                  => ['coltype'],
            'flag'                    => 1,
            'panelLayout'             => 'filter;search,limit'
        ],
        'label' => [
            'fields'                  => ['coltype'],
            'format'                  => '%s'
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
                'label'               => &$GLOBALS['TL_LANG']['tl_custom_collection_archive']['edit'],
                'href'                => 'table=tl_custom_collection',
                'icon'                => 'edit.gif'
            ],
            'editheader' => [
                'label'               => &$GLOBALS['TL_LANG']['tl_custom_collection_archive']['editheader'],
                'href'                => 'act=edit',
                'icon'                => 'header.gif',
                'button_callback'     => ['tl_custom_collection_archive', 'editHeader']
            ],
            'copy' => [
                'label'               => &$GLOBALS['TL_LANG']['tl_custom_collection_archive']['copy'],
                'href'                => 'act=copy',
                'icon'                => 'copy.gif',
                'button_callback'     => ['tl_custom_collection_archive', 'copyArchive']
            ],
            'delete' => [
                'label'               => &$GLOBALS['TL_LANG']['tl_custom_collection_archive']['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.gif',
                'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
                'button_callback'     => ['tl_custom_collection_archive', 'deleteArchive']
            ]
        ]
    ],

    // Palettes
    'palettes' => [
        '__selector__'                => ['protected'],
        'default'                     => '{type_legend},coltype;'
    ],

    // Subpalettes
    'subpalettes' => [
        'protected'                   => 'groups'
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
        ],
        'tstamp' => [
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ],
        'coltype' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_custom_collection_archive']['coltype'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => ['mandatory'=>true, 'maxlength'=>255],
            'sql'                     => "varchar(255) NOT NULL default ''"
        ]
    ]
];


/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class tl_custom_collection_archive extends Backend
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
     * Check permissions to edit table tl_custom_collection_archive
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
            $GLOBALS['TL_DCA']['tl_custom_collection_archive']['config']['closed'] = true;
        }

        // Set root IDs
        if (!is_array($this->User->news) || empty($this->User->news))
        {
            $root = [0];
        }
        else
        {
            $root = $this->User->news;
        }
        
        // Check current action
        switch (Input::get('act'))
        {
            case 'create':
            case 'select':
                // Allow

                break;

            case 'edit':
                // Dynamically add the record to the user profile
                if (!in_array(Input::get('id'), $root))
                {
                    $arrNew = $this->Session->get('new_records');

                    if (is_array($arrNew['tl_custom_collection_archive']) && in_array(Input::get('id'), $arrNew['tl_custom_collection_archive']))
                    {
                        // Add the permissions on group level
                        if ($this->User->inherit != 'custom')
                        {
                            $objGroup = $this->Database->execute("SELECT id, ccol, ccolp FROM tl_user_group WHERE id IN(" . implode(',', array_map('intval', $this->User->groups)) . ")");

                            while ($objGroup->next())
                            {
                                $arrCcolp = deserialize($objGroup->ccolp);

                                if (is_array($arrCcolp) && in_array('create', $arrCcolp))
                                {
                                    $arrCcol = deserialize($objGroup->ccol, true);
                                    $arrCcol[] = Input::get('id');

                                    $this->Database->prepare("UPDATE tl_user_group SET ccol=? WHERE id=?")
                                                   ->execute(serialize($arrCcol), $objGroup->id);
                                }
                            }
                        }

                        // Add the permissions on user level
                        if ($this->User->inherit != 'group')
                        {
                            $objUser = $this->Database->prepare("SELECT ccol, ccolp FROM tl_user WHERE id=?")
                                                       ->limit(1)
                                                       ->execute($this->User->id);

                            $arrCcolp = deserialize($objUser->ccolp);

                            if (is_array($arrCcolp) && in_array('create', $arrCcolp))
                            {
                                $arrCcol = deserialize($objUser->ccol, true);
                                $arrCcol[] = Input::get('id');

                                $this->Database->prepare("UPDATE tl_user SET ccol=? WHERE id=?")
                                               ->execute(serialize($arrCcol), $this->User->id);
                            }
                        }

                        // Add the new element to the user object
                        $root[] = Input::get('id');
                        $this->User->ccol = $root;
                    }
                }
                // No break;

            case 'copy':
            case 'delete':
            case 'show':
                if (!in_array(Input::get('id'), $root) || (Input::get('act') == 'delete' && !$this->User->hasAccess('delete', 'ccolp')))
                {
                    $this->log('Not enough permissions to '.Input::get('act').' custom collection with ID "'.Input::get('id').'"', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = $this->Session->getData();
                if (Input::get('act') == 'deleteAll' && !$this->User->hasAccess('delete', 'ccolp'))
                {
                    $session['CURRENT']['IDS'] = [];
                }
                else
                {
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
                }
                $this->Session->setData($session);
                break;

            default:
                if (strlen(Input::get('act')))
                {
                    $this->log('Not enough permissions to '.Input::get('act').' news archives', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                break;
        }
    }

    /**
     * Return the edit header button
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
    public function editHeader($row, $href, $label, $title, $icon, $attributes)
    {
        return $this->User->canEditFieldsOf('tl_custom_collection_archive') ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
    }


    /**
     * Return the copy archive button
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
    public function copyArchive($row, $href, $label, $title, $icon, $attributes)
    {
        return $this->User->hasAccess('create', 'newp') ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
    }


    /**
     * Return the delete archive button
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
    public function deleteArchive($row, $href, $label, $title, $icon, $attributes)
    {
        return $this->User->hasAccess('delete', 'newp') ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ' : Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
    }
}
