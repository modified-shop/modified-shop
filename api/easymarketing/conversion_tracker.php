<?php
/* -----------------------------------------------------------------------------------------
   $Id:$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require_once (DIR_FS_INC.'get_external_content.inc.php');

if (basename($PHP_SELF) == FILENAME_CHECKOUT_SUCCESS && defined('MODULE_EASYMARKETING_STATUS') && MODULE_EASYMARKETING_STATUS == 'True') {

  // include easymarketing configuration
  require_once(DIR_FS_CATALOG.'api/easymarketing/includes/config.php');

  // get conversion tracker
  $conversion_tracker = get_external_content(EASYMARKETING_API_URL.'/conversion_tracker/'.str_replace(array('http://', 'https://'), '', HTTP_SERVER).'?access_token='.MODULE_EASYMARKETING_ACCESS_TOKEN, 3, false);  
  $response = json_decode($conversion_tracker);

  // print out code
  if (is_object($response)) {
    echo $response->code;
    echo $response->img;
  }

}