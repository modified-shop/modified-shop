<?php
  /* --------------------------------------------------------------
   $Id: application_bottom.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(application_bottom.php,v 1.8 2002/03/15); www.oscommerce.com 
   (c) 2003	 nextcommerce (application_bottom.php,v 1.6 2003/08/1); www.nextcommerce.org
   (c) 2006 xt:Commerce (application_bottom.php 1314 2005-10-20); www.xt-commerce.de

   Released under the GNU General Public License 
   --------------------------------------------------------------*/
  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

  // page parse time
  if (STORE_PAGE_PARSE_TIME == 'true') {
    $parse_time = number_format((microtime(true)-PAGE_PARSE_START_TIME), 3);
    if ($parse_time >= STORE_PAGE_PARSE_TIME_THRESHOLD) {
      error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' [' . $parse_time . 's] ' . getenv('REQUEST_URI') . "\n", 3, DIR_FS_LOG.'mod_parsetime_admin_'. date('Y-m-d') .'.log');
    }
  }
  
  foreach(auto_include(DIR_FS_ADMIN.'includes/extra/application_bottom/','php') as $file) require ($file);

  // new error handling
  if (isset($error_exceptions) && is_array($error_exceptions) && count($error_exceptions) > 0) {
    if ((DISPLAY_ERROR_REPORTING == 'all') || DISPLAY_ERROR_REPORTING == 'admin') {
      echo '<div style="width:1000px; margin:20px auto; font-family: Verdana,Arial,sans-serif; font-size: 10px;">' . PHP_EOL .
             '<h2 style="color: rgb(190, 50, 50);">Exception Occured:</h2>' . PHP_EOL;
             echo implode(PHP_EOL, $error_exceptions);
      echo '</div>';
    }
  }

  // close MySQL connection
  session_write_close();
  xtc_db_close();
 
?>