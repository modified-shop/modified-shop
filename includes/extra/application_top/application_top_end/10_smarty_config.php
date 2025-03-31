<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  // listing
  if (isset($_GET['show'])) {
    $_SESSION['listbox'] = (($_GET['show'] == 'box') ? 'true' : 'false');
  }
  
  // load Template config
  if (defined('CURRENT_TEMPLATE') 
      && file_exists(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/config/config.php')
      )
  {
    require_once(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/config/config.php');
  }
