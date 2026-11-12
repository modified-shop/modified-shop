<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // export files land in a public directory, so only a plain name with a data extension is allowed
  function check_export_filename($filename, $extensions) {
    if (!is_string($filename)
        || !preg_match('/^[a-z0-9_-][a-z0-9._-]*$/iD', $filename)
        // an inner executable extension can still select a script handler on some servers
        || preg_match('/\.(php[0-9]*|pht|phtml|phar|pl|py|cgi|asp|aspx|jsp|sh|shtml|htaccess)\./i', $filename)
        ) {
      return false;
    }

    return in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), $extensions, true);
  }
