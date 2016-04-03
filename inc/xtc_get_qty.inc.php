<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_get_qty.inc.php 899 2005-04-29 02:40:57Z hhgag $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function xtc_get_qty($products_id)  {
    $act_id = xtc_get_prid($products_id);
    if (isset($_SESSION['actual_content'][$act_id]['qty'])) {
      return $_SESSION['actual_content'][$act_id]['qty'];
    }
    return 0;
  }
?>