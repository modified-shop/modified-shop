<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/


  define('MODULE_CATEGORIES_ZETTLE_CATEGORIES_TITLE', 'Zettle by PayPal');
  define('MODULE_CATEGORIES_ZETTLE_CATEGORIES_DESCRIPTION', 'Modern POS systems as an app for accepting payments and tracking sales: Make your day-to-day business easier with Zettle.<br>
                                                             <br>The following range of functions is currently supported:
                                                             <ul style="padding-left: 20px;">
                                                               <li>Products are transferred from the shop to Zettle</li>
                                                               <li>Separated customers status for prices</li>
                                                               <li>Stock is synchronised between shop and Zettle</li>
                                                             </ul>');

  define('MODULE_CATEGORIES_ZETTLE_CATEGORIES_BULK_TITLE', 'Bulk Import');
  define('MODULE_CATEGORIES_ZETTLE_CATEGORIES_BULK_DESC', 'Should the bulk import be activated?<br><b>Note:</b> For this purpose, it is necessary that a cronjob is set to the URL '.HTTP_SERVER.DIR_WS_CATALOG.'api/zettle/cronjob.php is created.');

  define('MODULE_CATEGORIES_ZETTLE_CATEGORIES_API_KEY_TITLE', 'API Key');
  define('MODULE_CATEGORIES_ZETTLE_CATEGORIES_API_KEY_DESC', 'Enter the Zettle API Key.');

  define('MODULE_CATEGORIES_ZETTLE_CATEGORIES_LANGUAGE_TITLE', 'Language');
  define('MODULE_CATEGORIES_ZETTLE_CATEGORIES_LANGUAGE_DESC', 'Select the language for the transmission of the articles.');

  define('MODULE_CATEGORIES_ZETTLE_CATEGORIES_CUSTOMERS_STATUS_TITLE', 'Customers status');
  define('MODULE_CATEGORIES_ZETTLE_CATEGORIES_CUSTOMERS_STATUS_DESC', 'Select the customer group for the transfer of prices.');

  define('MODULE_CATEGORIES_ZETTLE_CATEGORIES_BUTTON_API', 'Create Zettle API Key');
