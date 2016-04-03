<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2009 FINDOLOGIC GmbH - Version: 4.1 (120)

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  $shop_id = trim(constant('MODULE_FINDOLOGIC_SHOP_ID_'.strtoupper($_SESSION['language_code'])));
  if ($shop_id == '' && $_SESSION['language_code'] != DEFAULT_LANGUAGE) {
    $shop_id = trim(constant('MODULE_FINDOLOGIC_SHOP_ID_'.strtoupper(DEFAULT_LANGUAGE)));
  }

  $service_url = trim(constant('MODULE_FINDOLOGIC_SERVICE_URL_'.strtoupper($_SESSION['language_code'])));
  if ($service_url == '' && $_SESSION['language_code'] != DEFAULT_LANGUAGE) {
    $service_url = trim(constant('MODULE_FINDOLOGIC_SERVICE_URL_'.strtoupper(DEFAULT_LANGUAGE)));
  }

  define('FL_FS_API', DIR_FS_CATALOG . 'api/findologic/');
  define('FL_SHOP_ID', $shop_id);
  define('FL_SHOP_URL', str_replace(array('http://', 'https://'), '', HTTP_SERVER).DIR_WS_CATALOG); // Changed to static value
  define('FL_SERVICE_URL', $service_url);
  define('FL_NET_PRICE', false); // Changed to static value
  define('FL_ALIVE_TEST_TIMEOUT', 1); // Changed to static value
  define('FL_REQUEST_TIMEOUT', 3); // Changed to static value
  define('FL_EXPORT_FILENAME', MODULE_FINDOLOGIC_EXPORT_FILENAME);
  define('FL_REVISION', preg_replace('/.*(\d+).*/', '$1', '$Revision: 204 $')); // Changed to static value
  define('FL_LANG', MODULE_FINDOLOGIC_LANG);
  define('CUSTOMER_GROUP', MODULE_FINDOLOGIC_CUSTOMER_GROUP);
  define('CURRENCY', MODULE_FINDOLOGIC_CURRENCY);
?>