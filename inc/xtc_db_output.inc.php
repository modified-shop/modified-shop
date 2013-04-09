<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_db_output.inc.php 4256 2013-01-11 16:23:35Z web28 $   

    modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(database.php,v 1.19 2003/03/22); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_db_output.inc.php,v 1.3 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  //fix for conectors like facturama
  if (!function_exists('encode_htmlspecialchars')) {
    require_once (DIR_FS_INC.'html_encoding.php'); //new function for PHP5.4
  }
   
  function xtc_db_output($string) {
    return encode_htmlspecialchars($string);
  }
 ?>