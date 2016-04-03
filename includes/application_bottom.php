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
  if ($parse_time >= STORE_PAGE_PARSE_TIME_THRESHOLD) {
    error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' [' . $parse_time . 's] ' . getenv('REQUEST_URI') . "\n", 3, DIR_FS_LOG.'mod_parsetime_'. date('Y-m-d') .'.log');
  }
}
if (DISPLAY_PAGE_PARSE_TIME == 'all') {
  $parse_time = number_format((microtime(true)-PAGE_PARSE_START_TIME), 3);
  echo '<div class="parseTime">Parse Time: ' . $parse_time . 's</div>';
} else if (DISPLAY_PAGE_PARSE_TIME == 'admin' && $_SESSION['customers_status']['customers_status'] == '0') {
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
  require_once (DIR_FS_EXTERNAL . 'econda/econda.php');
}

// require theme based css
if (is_file('templates/'.CURRENT_TEMPLATE.'/css/general_bottom.css.php')) {
  require('templates/'.CURRENT_TEMPLATE.'/css/general_bottom.css.php');
}

// require theme based javascript
if (is_file('templates/'.CURRENT_TEMPLATE.'/javascript/general_bottom.js.php')) {
  require('templates/'.CURRENT_TEMPLATE.'/javascript/general_bottom.js.php');
}

foreach(auto_include(DIR_FS_CATALOG.'includes/extra/application_bottom/','php') as $file) require ($file);

// new error handling
if (isset($error_exceptions) && is_array($error_exceptions) && count($error_exceptions) > 0) {
  if ((DISPLAY_ERROR_REPORTING == 'all') || (DISPLAY_ERROR_REPORTING == 'admin' && $_SESSION['customers_status']['customers_status'] == '0')) {
    echo '<div style="max-width:1000px; margin:20px auto; font-family: Verdana,Arial,sans-serif; font-size: 10px;">' . PHP_EOL .
           '<h2 style="color: #BE3232;">Exception Occured:</h2>' . PHP_EOL;
           echo implode(PHP_EOL, $error_exceptions);
    echo '</div>';
  }
}

// close MySQL connection
session_write_close();
xtc_db_close();

// end of page
echo '</body>';
echo '</html>';
?>