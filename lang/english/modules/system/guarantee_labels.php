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
  define('MODULE_GUARANTEE_LABELS_TEXT_GD_ERROR', 'GD with FreeType and the function imagettfbbox() are not available on this server. Without them the text width of the GARAN label cannot be measured. The module has not been installed.');
  define('MODULE_GUARANTEE_LABELS_TEXT_SCHEMA_ERROR', 'The database structure for the EU labels could not be created completely. The module has not been enabled.');

  define('MODULE_GUARANTEE_LABELS_TEXT_ERROR_TABLE', 'The table %s is missing.');
  define('MODULE_GUARANTEE_LABELS_TEXT_ERROR_INDEX', 'The index %s of table %s is missing.');
  define('MODULE_GUARANTEE_LABELS_TEXT_ERROR_COLUMN', 'The column %s of table %s is missing.');
  define('MODULE_GUARANTEE_LABELS_TEXT_ERROR_COLUMN_TYPE', 'The column %s of table %s has type %s, expected %s. The column has not been changed.');

  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS', 'Check of the requirements');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_OK', 'in place');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_FAILED', 'missing');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_EXTENSION', 'class extension %s installed');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_SELECT', 'guarantee duration part of %s');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_RENDERER', 'templates, fonts and GD for building the label');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_NOTICE', 'notice about the legal guarantee and texts for %s');
