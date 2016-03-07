<?php
/* -----------------------------------------------------------------------------------------
   $Id: banners.php 899 2005-04-29 02:40:57Z hhgag $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


  require_once(DIR_FS_INC . 'xtc_banner_exists.inc.php');
  require_once(DIR_FS_INC . 'xtc_display_banner.inc.php');
  require_once(DIR_FS_INC . 'xtc_update_banner_display_count.inc.php');

  $groups_query = xtc_db_query("SELECT DISTINCT banners_group 
                                           FROM " . TABLE_BANNERS . " 
                                          WHERE banners_group != 'slider'
                                       ORDER BY banners_group");
  while ($groups = xtc_db_fetch_array($groups_query)) {
    if ($banner = xtc_banner_exists('dynamic', $groups['banners_group'])) {
      $smarty->assign(strtoupper($groups['banners_group']), xtc_display_banner('static', $banner));
    }
  }

  if ($banner = xtc_banner_exists('slider', 'slider')) {
    $smarty->assign('SLIDER', xtc_display_banner('slider', $banner));
  }
?>