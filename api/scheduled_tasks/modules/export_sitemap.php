<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  function cron_export_sitemap() {
    if (defined('MODULE_SITEMAPORG_STATUS') 
        && MODULE_SITEMAPORG_STATUS == 'True'
        )
    {
      // content, product, category - sql group_check/fsk_lock
      require_once (DIR_WS_INCLUDES.'define_conditions.php');

      // include needed classes
      require_once(DIR_FS_EXTERNAL.'sitemap/sitemap.php');
      
      $sitemap = new sitemap();
      $sitemap->export();
    }
    
    return true;
  }