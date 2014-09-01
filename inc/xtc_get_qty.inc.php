<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_get_qty.inc.php 899 2005-04-29 02:40:57Z hhgag $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

function xtc_get_qty($products_id)  {

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
}
?>