<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(gzip_compression.php,v 1.3 2003/02/11); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_gzip_output.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
/* $level = compression level 0-9, 0=none, 9=max */
  function xtc_gzip_output($level = 5) {
    if ($encoding = xtc_check_gzip()) {
      $contents = ob_get_contents();
      ob_end_clean();

      header('Content-Encoding: ' . $encoding);

      $size = strlen($contents);
      $crc = crc32($contents);

      $contents = gzcompress($contents, $level);
      $contents = substr($contents, 0, strlen($contents) - 4);

      echo "\x1f\x8b\x08\x00\x00\x00\x00\x00";
      echo $contents;
      echo pack('V', $crc);
      echo pack('V', $size);
    } else {
      ob_end_flush();
    }
  }
 ?>