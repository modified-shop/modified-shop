<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_update_banner_click_count.inc.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(banner.php,v 1.10 2003/02/11); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_update_banner_click_count.inc.php,v 1.4 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  // Update the banner click statistics
  function xtc_update_banner_click_count($banner_id) {
    xtc_db_query("UPDATE " . TABLE_BANNERS_HISTORY . " SET banners_clicked = banners_clicked + 1 WHERE banners_id = '" . (int)$banner_id . "' AND date_format(banners_history_date, '%Y%m%d') = date_format(now(), '%Y%m%d')");
  }
?>