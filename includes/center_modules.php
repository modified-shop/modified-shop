<?php
/* -----------------------------------------------------------------------------------------
   $Id: center_modules.php 2666 2012-02-23 11:38:17Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommercebased on original files from OSCommerce CVS 2.2 2002/08/28 02:14:35 www.oscommerce.com
   (c) 2003 nextcommerce (center_modules.php,v 1.5 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (center_modules.php 899 2005-04-29)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  require(DIR_WS_MODULES . FILENAME_NEW_PRODUCTS);
  require(DIR_WS_MODULES . FILENAME_UPCOMING_PRODUCTS);

  //BOF - DokuMan - 2011-01-21 - Fix a notice when there is no content found in center_modules
  //return $module;
  if(isset($module)) {
     return $module;
  }
  return '';
  //EOF - DokuMan - 2011-01-21 - Fix a notice when there is no content found in center_modules
?>