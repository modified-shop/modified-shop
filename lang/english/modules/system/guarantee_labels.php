<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  define('MODULE_GUARANTEE_LABELS_TEXT_TITLE', 'EU legal guarantee and GARAN label');
  define('MODULE_GUARANTEE_LABELS_TEXT_DESCRIPTION', 'Provides the harmonised notice on the legal guarantee required from 27 September 2026 and the GARAN label for qualified commercial durability guarantees.');

  define('MODULE_GUARANTEE_LABELS_STATUS_TITLE', 'Enable module?');
  define('MODULE_GUARANTEE_LABELS_STATUS_DESC', 'Output the EU labels in the shop. While the module is inactive no new order snapshots are created; existing ones are kept.');

  define('MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS_TITLE', 'B2B customer groups');
  define('MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS_DESC', 'Customer groups excluded from the output. Without a selection all customer groups including guests count as B2C.');

  define('MODULE_GUARANTEE_LABELS_TEXT_INSTALL_SUCCESS', 'The database structure for the EU labels has been created and verified.');
  define('MODULE_GUARANTEE_LABELS_TEXT_UPDATE_SUCCESS', 'The database structure for the EU labels is complete.');
  define('MODULE_GUARANTEE_LABELS_TEXT_SCHEMA_ERROR', 'The database structure for the EU labels could not be created completely. The module has not been enabled.');

  define('MODULE_GUARANTEE_LABELS_TEXT_ERROR_TABLE', 'The table %s is missing.');
  define('MODULE_GUARANTEE_LABELS_TEXT_ERROR_INDEX', 'The index %s of table %s is missing.');
  define('MODULE_GUARANTEE_LABELS_TEXT_ERROR_COLUMN', 'The column %s of table %s is missing.');
  define('MODULE_GUARANTEE_LABELS_TEXT_ERROR_COLUMN_TYPE', 'The column %s of table %s has type %s, expected %s. The column has not been changed.');
