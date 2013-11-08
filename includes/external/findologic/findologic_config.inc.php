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

  define('FL_FS_API', DIR_FS_CATALOG . 'api/findologic/');
  define('FL_SHOP_ID', trim(MODULE_FINDOLOGIC_SHOP_ID));
  define('FL_SHOP_URL', str_replace(array('http://', 'https://'), '', HTTP_SERVER).DIR_WS_CATALOG); // Changed to static value
  define('FL_SERVICE_URL', MODULE_FINDOLOGIC_SERVICE_URL);
  define('FL_NET_PRICE', false); // Changed to static value
  define('FL_ALIVE_TEST_TIMEOUT', 1); // Changed to static value
  define('FL_REQUEST_TIMEOUT', 3); // Changed to static value
  define('FL_EXPORT_FILENAME', MODULE_FINDOLOGIC_EXPORT_FILENAME);
  define('FL_REVISION', preg_replace('/.*(\d+).*/', '$1', '$Revision: 204 $')); // Changed to static value
  define('FL_LANG', MODULE_FINDOLOGIC_LANG);
  define('CUSTOMER_GROUP', MODULE_FINDOLOGIC_CUSTOMER_GROUP);
  define('CURRENCY', MODULE_FINDOLOGIC_CURRENCY);

?>