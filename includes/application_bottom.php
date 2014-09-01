<?php
/* -----------------------------------------------------------------------------------------
   $Id: application_bottom.php 3298 2012-07-26 09:41:18Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(application_bottom.php,v 1.14 2003/02/10); www.oscommerce.com
   (c) 2003  nextcommerce (application_bottom.php,v 1.6 2003/08/13); www.nextcommerce.org
   (c) 2003 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// page parse time
if (STORE_PAGE_PARSE_TIME == 'true') {
  $parse_time = number_format((microtime(true)-PAGE_PARSE_START_TIME), 3);
  error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' - ' . getenv('REQUEST_URI') . ' (' . $parse_time . 's)' . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
}
if (DISPLAY_PAGE_PARSE_TIME == 'true') {
  $parse_time = number_format((microtime(true)-PAGE_PARSE_START_TIME), 3);
  echo '<div class="parseTime">Parse Time: ' . $parse_time . 's</div>';
}

// gzip compression
if ((GZIP_COMPRESSION == 'true') && ($ext_zlib_loaded == true) && ($ini_zlib_output_compression < 1)) {
  if ((PHP_VERSION < '4.0.4') && (PHP_VERSION >= '4')) {
    xtc_gzip_output(GZIP_LEVEL);
  }
}

// econda tracking
if (TRACKING_ECONDA_ACTIVE == 'true') {
  require_once (DIR_WS_INCLUDES . 'econda/econda.php');
}

// end of page
echo '</body>';
echo '</html>';