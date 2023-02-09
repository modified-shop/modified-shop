<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  function cron_logs_maintenance() {
    $exts = array("log","log\.zip","log\.gz", "log\.[0-9]+");
    
    $dir = dir(DIR_FS_LOG);
    $contents_array = array();
    while ($file = $dir->read()) {
      if (!is_dir(DIR_FS_LOG . $file) && $file != 'xss_blacklist.log') {
        foreach ($exts as $ext) {
          if (preg_match('/\.'.$ext.'$/i', $file)
              && filectime(DIR_FS_LOG . $file) < (time() - (86400 * 7))
              ) 
          {
            unlink(DIR_FS_LOG . $file);
          }          
        }
      }
    }
    
    return true;
  }
