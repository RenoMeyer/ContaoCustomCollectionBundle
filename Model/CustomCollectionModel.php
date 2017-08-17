<?php

namespace Oneup\Bundle\ContaoCustomCollectionBundle\Model;

class CustomCollectionModel extends \Model
{
  protected static $strTable = 'tl_custom_collection';

  public static function findPublishedByPid($pid, $order='sorting', $intLimit=0, $intOffset=0, array $arrOptions=[])
  {
    if (!$pid && '' == $pid)
    {
      return null;
    }

    $t = static::$strTable;
    $arrColumns = array("$t.pid=" . $pid. " AND $t.published='1'");

    $arrOptions['order']  = $order;
    $arrOptions['limit']  = $intLimit;
    $arrOptions['offset'] = $intOffset;

    return static::findBy($arrColumns, null, $arrOptions);
  }
}
