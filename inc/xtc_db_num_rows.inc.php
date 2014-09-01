<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_db_num_rows.inc.php 1212 2005-09-12 07:46:37Z novalis $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(database.php,v 1.19 2003/03/22); www.oscommerce.com
   (c) 2003	 nextcommerce (xtc_db_num_rows.inc.php,v 1.3 2003/08/13); www.nextcommerce.org 

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  function xtc_db_num_rows($db_query,$cq=false) {
      if (DB_CACHE=='true' && $cq) {
         if (!count($db_query)) return false;
     return count($db_query);
      } else {

         if (!is_array($db_query)) return mysql_num_rows($db_query);

      }
      /*
    if (!is_array($db_query)) return mysql_num_rows($db_query);
    if (!count($db_query)) return false;
     return count($db_query);
     */
  }

 ?>