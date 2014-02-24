<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_set_specials_status.inc.php 899 2005-04-29 02:40:57Z hhgag $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(specials.php,v 1.5 2003/02/11); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_set_specials_status.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2003 XT-Commerce
   
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  // Sets the status of a special product
  function xtc_set_specials_status($specials_id, $status) {
    if ($status != '1') {
      $status = 0;
    }
    xtc_db_query("UPDATE " . TABLE_SPECIALS . " 
                     SET status = '" .(int)$status . "', 
                         date_status_change = now() 
                   WHERE specials_id = '" . (int)$specials_id . "'");
  }
?>