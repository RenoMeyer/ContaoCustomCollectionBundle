<?php

namespace Oneup\Bundle\ContaoCustomCollectionBundle\Module;

use Oneup\Bundle\ContaoCustomCollectionBundle\Model\CustomCollectionModel;
/**
 * Parent class for news modules.
 *
 */
class CustomCollectionModule extends \Module
{
  protected static $strTable = 'tl_custom_collection';
  protected $strTemplate = 'mod_custom_collection';

  /**
   * Display a wildcard in the back end
   *
   * @return string
   */
  public function generate()
  {
    if (TL_MODE == 'BE')
    {
      /** @var \BackendTemplate|object $objTemplate */
      $objTemplate = new \BackendTemplate('be_wildcard');

      $objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['ccol'][0]) . ' ###';
      $objTemplate->title = $this->name;
      $objTemplate->id = $this->id;
      $objTemplate->link = $this->name;
      $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

      return $objTemplate->parse();
    }

    $this->collection = CustomCollectionModel::findPublishedByPid($this->collection);

    // Return if there are no archives
    if (!$this->collection)
    {
      return '';
    }

    return parent::generate();
  }


  /**
   * Generate the module
   */
  protected function compile()
  {
    $objCollection = $this->collection;
    $arrCollection = [];
    $count = 0;
    $limit = $objCollection->count();

    while ($objCollection->next())
    {
      /** @var \NewsModel $objArticle */
      $objItem = $objCollection->current();

      $arrCollection[] = $this->parseItem($objItem, ((++$count == 1) ? ' first' : '') . (($count == $limit) ? ' last' : ''), $count);
    }

    $this->Template->collection = $arrCollection;
  }

  /**
   * Parse an item and return it as string
   *
   * @param \NewsModel $objCollection
   * @param string     $strClass
   * @param integer    $intCount
   *
   * @return string
   */
  protected function parseItem($objItem, $strClass='', $intCount=0)
  {
    global $objPage;

    // Create template
    $objTemplate = new \FrontendTemplate($this->collection_template);
    $objTemplate->setData($objItem->row());
    $objTemplate->class = (($objItem->cssClass != '') ? ' ' . $objItem->cssClass : '') . $strClass;
    $objTemplate->archive = $objItem->getRelated('pid');
    $objTemplate->count = $intCount;
    
    // Text field
    if ($objItem->text && '' != $objItem->text) {
      // Clean the RTE output
      if ($objPage->outputFormat == 'xhtml')
      {
        $objItem->text = \StringUtil::toXhtml($objItem->text);
      }
      else
      {
        $objItem->text = \StringUtil::toHtml5($objItem->text);
      }

      // Add the static files URL to images
      if (TL_FILES_URL != '')
      {
        $path = \Config::get('uploadPath') . '/';
        $objItem->text = str_replace(' src="' . $path, ' src="' . TL_FILES_URL . $path, $objItem->text);
      }

      $objTemplate->text = \StringUtil::encodeEmail($objItem->text);
    }


    // Image field
    $objTemplate->addImage = false;

    if ($objItem->addImage && '' != $objItem->singleSRC) {
      $objModel = \FilesModel::findByUuid($objItem->singleSRC);

      if ($objModel === null) {
        if (!\Validator::isUuid($objItem->singleSRC))
        {
          $objTemplate->text = '<p class="error">'.$GLOBALS['TL_LANG']['ERR']['version2format'].'</p>';
        }
      }
      elseif (is_file(TL_ROOT . '/' . $objModel->path)) {
        // Do not override the field now that we have a model registry (see #6303)
        $arrItem = $objItem->row();

        // Override the default image size
        if ($objItem->imgSize != '') {
          $size = deserialize($objItem->imgSize);

          if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2])) {
            $arrItem['size'] = $objItem->imgSize;
          }
        }

        $arrItem['singleSRC'] = $objModel->path;
        $this->addImageToTemplate($objTemplate, $arrItem);
      }
    }


    // Multi-image field
    if ($objItem->addImages && '' != $objItem->multiSRC) {
      $objItem->multiSRC = deserialize($objItem->multiSRC);

      // Get the file entries from the database
      $objFiles = \FilesModel::findMultipleByUuids($objItem->multiSRC);

      if ($objFiles === null)
      {
        if (!\Validator::isUuid($objItem->multiSRC[0]))
        {
          return '<p class="error">'.$GLOBALS['TL_LANG']['ERR']['version2format'].'</p>';
        }

        return '';
      }

      $images = [];
      $auxDate = [];

      // Get all images
      while ($objFiles->next())
      {

        // Continue if the files has been processed or does not exist
        if (isset($images[$objFiles->path]) || !file_exists(TL_ROOT . '/' . $objFiles->path))
        {
          continue;
        }

        // Single files
        if ($objFiles->type == 'file')
        {
          $objFile = new \File($objFiles->path, true);

          if (!$objFile->isImage)
          {
            continue;
          }

          $arrMeta = $this->getMetaData($objFiles->meta, $objPage->language);

          if (empty($arrMeta))
          {
            if ($this->metaIgnore)
            {
              continue;
            }
            elseif ($objPage->rootFallbackLanguage !== null)
            {
              $arrMeta = $this->getMetaData($objFiles->meta, $objPage->rootFallbackLanguage);
            }
          }

          // Use the file name as title if none is given
          if ($arrMeta['title'] == '')
          {
            $arrMeta['title'] = specialchars($objFile->basename);
          }

          // Add the image
          $images[$objFiles->path] = array
          (
            'id'        => $objFiles->id,
            'uuid'      => $objFiles->uuid,
            'name'      => $objFile->basename,
            'singleSRC' => $objFiles->path,
            'alt'       => $arrMeta['title'],
            'imageUrl'  => $arrMeta['link'],
            'caption'   => $arrMeta['caption']
          );

          $auxDate[] = $objFile->mtime;
        }

        // Folders
        else
        {
          $objSubfiles = \FilesModel::findByPid($objFiles->uuid);

          if ($objSubfiles === null)
          {
            continue;
          }

          while ($objSubfiles->next())
          {
            // Skip subfolders
            if ($objSubfiles->type == 'folder')
            {
              continue;
            }

            $objFile = new \File($objSubfiles->path, true);

            if (!$objFile->isImage)
            {
              continue;
            }

            $arrMeta = $this->getMetaData($objSubfiles->meta, $objPage->language);

            if (empty($arrMeta))
            {
              if ($this->metaIgnore)
              {
                continue;
              }
              elseif ($objPage->rootFallbackLanguage !== null)
              {
                $arrMeta = $this->getMetaData($objSubfiles->meta, $objPage->rootFallbackLanguage);
              }
            }

            // Use the file name as title if none is given
            if ($arrMeta['title'] == '')
            {
              $arrMeta['title'] = specialchars($objFile->basename);
            }

            // Add the image
            $images[$objSubfiles->path] = array
            (
              'id'        => $objSubfiles->id,
              'uuid'      => $objSubfiles->uuid,
              'name'      => $objFile->basename,
              'singleSRC' => $objSubfiles->path,
              'alt'       => $arrMeta['title'],
              'imageUrl'  => $arrMeta['link'],
              'caption'   => $arrMeta['caption']
            );

            $auxDate[] = $objFile->mtime;
          }
        }
      }

      // Sort array
      switch ($objItem->sortBy)
      {
        default:
        case 'name_asc':
          uksort($images, 'basename_natcasecmp');
          break;

        case 'name_desc':
          uksort($images, 'basename_natcasercmp');
          break;

        case 'date_asc':
          array_multisort($images, SORT_NUMERIC, $auxDate, SORT_ASC);
          break;

        case 'date_desc':
          array_multisort($images, SORT_NUMERIC, $auxDate, SORT_DESC);
          break;

        case 'meta': // Backwards compatibility
        case 'custom':
          if ($objItem->orderSRC != '')
          {
            $tmp = deserialize($objItem->orderSRC);

            if (!empty($tmp) && is_array($tmp))
            {
              // Remove all values
              $arrOrder = array_map(function () {}, array_flip($tmp));

              // Move the matching elements to their position in $arrOrder
              foreach ($images as $k=>$v)
              {
                if (array_key_exists($v['uuid'], $arrOrder))
                {
                  $arrOrder[$v['uuid']] = $v;
                  unset($images[$k]);
                }
              }

              // Append the left-over images at the end
              if (!empty($images))
              {
                $arrOrder = array_merge($arrOrder, array_values($images));
              }

              // Remove empty (unreplaced) entries
              $images = array_values(array_filter($arrOrder));
              unset($arrOrder);
            }
          }
          break;

        case 'random':
          shuffle($images);
          break;
      }

      $images = array_values($images);

      // Limit the total number of items (see #2652)
      if ($objItem->numberOfItems > 0)
      {
        $images = array_slice($images, 0, $objItem->numberOfItems);
      }

      $arrImages = [];

      // Rows
      foreach ($images as $key => $image) {
        $image['size'] = $objItem->size;
        $objCell = new \stdClass();

        $this->addImageToTemplate($objCell, $image);

        $arrImages[$key] = $objCell;
      }

      $strTemplate = 'gallery_collection';

      // Use a custom template
      if (TL_MODE == 'FE' && $objItem->galleryTpl != '')
      {
        $strTemplate = $objItem->galleryTpl;
      }

      /** @var \FrontendTemplate|object $objTemplate */
      $objGalleryTemplate = new \FrontendTemplate($strTemplate);
      $objGalleryTemplate->setData($objItem->arrData);
      $objGalleryTemplate->images = $arrImages;
      $objGalleryTemplate->groupId = $objItem->id;
      $objGalleryTemplate->imageLink = $objItem->imageLink;

      $objTemplate->images = $objGalleryTemplate->parse();
    }


    // Html field
    if ($objItem->html && '' != $objItem->html) {
      if (TL_MODE == 'FE')
      {
        $objTemplate->html = $this->html;
      }
      else
      {
        $objTemplate->html = '<pre>' . htmlspecialchars($this->html) . '</pre>';
      }
    }


    // List field
    if ($objItem->listitems && '' != $objItem->listitems) {
      $arrItems = [];
      $items = deserialize($objItem->listitems);
      $limit = count($items) - 1;

      for ($i=0, $c=count($items); $i<$c; $i++)
      {
        $arrItems[] = array
        (
          'class' => (($i == 0) ? 'first' : (($i == $limit) ? 'last' : '')),
          'content' => $items[$i]
        );
      }
      $objTemplate->items = $arrItems;
    }


    // Link field
    if ($objItem->url && '' != $objItem->url) {
      if (substr($objItem->url, 0, 7) == 'mailto:')
      {
        $objItem->url = \StringUtil::encodeEmail($objItem->url);
      }
      else
      {
        $objItem->url = ampersand($objItem->url);
      }

      if ($objItem->linkTitle == '')
      {
        $objItem->linkTitle = $objItem->url;
      }
      $objTemplate->href = $objItem->url;
      $objTemplate->link = $objItem->linkTitle;
      $objTemplate->linkTitle = specialchars($objItem->titleText ?: $objItem->linkTitle);
    }


    // HOOK: add custom logic
    if (isset($GLOBALS['TL_HOOKS']['parseItem']) && is_array($GLOBALS['TL_HOOKS']['parseItem'])) {
      foreach ($GLOBALS['TL_HOOKS']['parseItem'] as $callback) {
        $this->import($callback[0]);
        $this->{$callback[0]}->{$callback[1]}($objTemplate, $objItem->row(), $this);
      }
    }

    return $objTemplate->parse();
  }
}