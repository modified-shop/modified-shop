<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2009 - 2012 xtcModified
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

function xtc_get_qty($products_id)  {
/*
    if (strpos($products_id,'{'))  {
      $act_id=substr($products_id,0,strpos($products_id,'{'));
    } else {
      $act_id=$products_id;
    }

    //BOF - Dokuman - 2010-02-26 - set Undefined index
    //return $_SESSION['actual_content'][$act_id]['qty'];
    if (isset($_SESSION['actual_content'][$act_id]['qty']))
      return $_SESSION['actual_content'][$act_id]['qty'];
    return 0;
    //EOF - Dokuman - 2010-02-26 - set Undefined index
*/
  $result = NULL;
  $act_id = strtok($products_id, '{');

  if (array_key_exists('actual_content', $_SESSION) && array_key_exists($act_id, $_SESSION['actual_content'])) {
    $result = $_SESSION['actual_content'][$act_id]['qty'];
  }

  return $result;
}
?>