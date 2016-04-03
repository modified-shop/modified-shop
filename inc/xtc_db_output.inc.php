<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_db_output.inc.php 5463 2013-09-03 13:52:45Z GTB $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(database.php,v 1.19 2003/03/22); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_db_output.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  die('Deprecated File: '.basename(__FILE__).'. Use db_functions_mysql(i) instead.');
/*   
  //fix for conectors like facturama
  if (!function_exists('encode_htmlspecialchars')) {
    require_once (DIR_FS_INC.'html_encoding.php'); //new function for PHP5.4
  }

  function xtc_db_output($string) {
    return encode_htmlspecialchars($string);
  }
*/
 ?>