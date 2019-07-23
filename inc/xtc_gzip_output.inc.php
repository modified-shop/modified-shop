<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(gzip_compression.php,v 1.3 2003/02/11); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_gzip_output.inc.php,v 1.3 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
/* $level = compression level 0-9, 0=none, 9=max */
  function xtc_gzip_output($level = 5) {
    if (xtc_check_gzip() !== false) {
      $gzip_size        = ob_get_length();
      $gzip_contents    = ob_get_clean();
   
      echo "\x1f\x8b\x08\x00\x00\x00\x00\x00",
           substr(gzcompress($gzip_contents, (int)$level), 0, - 4),
           pack('V', crc32($gzip_contents)),
           pack('V', $gzip_size);
    } else {
      ob_end_flush();
    }
  }
 ?>