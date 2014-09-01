<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_banner_exists.inc.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(banner.php,v 1.10 2003/02/11); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_banner_exists.inc.php,v 1.4 2003/08/13); www.nextcommerce.org 

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
require_once(DIR_FS_INC.'xtc_random_select.inc.php');   
  function xtc_banner_exists($action, $identifier) {
    if ($action == 'dynamic') {
      return xtc_random_select("select banners_id, banners_title, banners_image, banners_html_text from " . TABLE_BANNERS . " where status = '1' and banners_group = '" . $identifier . "'");
    } elseif ($action == 'static') {
      $banner_query = xtc_db_query("select banners_id, banners_title, banners_image, banners_html_text from " . TABLE_BANNERS . " where status = '1' and banners_id = '" . $identifier . "'");
      return xtc_db_fetch_array($banner_query);
    } else {
      return false;
    }
  }
 ?>