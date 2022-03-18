<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  
  // include needed classes
  require_once(DIR_FS_CATALOG.'includes/classes/modified_cache.php');
  
  if (!is_object($modified_cache)) {
    $_mod_cache_class = strtolower(DB_CACHE_TYPE).'_cache';
    if (!class_exists($_mod_cache_class)) {
      $_mod_cache_class = 'modified_cache';
    }
    $modified_cache = $_mod_cache_class::getInstance();
  }
