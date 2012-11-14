<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


  require_once(DIR_FS_INC . 'xtc_banner_exists.inc.php');
  require_once(DIR_FS_INC . 'xtc_display_banner.inc.php');
  require_once(DIR_FS_INC . 'xtc_update_banner_display_count.inc.php');


  if ($banner = xtc_banner_exists('dynamic', 'banner')) {
  $smarty->assign('BANNER',xtc_display_banner('static', $banner));

  }
?>