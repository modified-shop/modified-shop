<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(specials.php,v 1.5 2003/02/11); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_expire_specials.inc.php,v 1.5 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  require_once(DIR_FS_INC . 'xtc_set_specials_status.inc.php');

  // Auto expire products on special
  function xtc_expire_specials() {
    xtc_db_query("UPDATE ".TABLE_SPECIALS." 
                     SET status = '0', 
                         date_status_change = now() 
                   WHERE expires_date <= now() 
                     AND expires_date > 0");
  }
?>