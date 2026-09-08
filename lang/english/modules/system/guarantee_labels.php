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
  define('MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS_DESC', 'Customer groups excluded from the output. Without a selection all customer groups including guests count as B2C. The group &quot;Admin&quot; is not a customer group: it never orders and is ignored here, even though the list offers it.');

  define('MODULE_GUARANTEE_LABELS_TEXT_INSTALL_SUCCESS', 'The database structure for the EU labels has been created and verified.');
  define('MODULE_GUARANTEE_LABELS_TEXT_UPDATE_SUCCESS', 'The database structure for the EU labels is complete.');
  define('MODULE_GUARANTEE_LABELS_TEXT_REMOVE_KEEPS_GUARD', 'The catalogue still carries GARAN data. The extension guarantee_labels_product.php therefore stays installed: it keeps a duplicate from inheriting the guarantee of the article it was copied from and does nothing else without this module. The article data itself is kept so a later reinstall can pick it up again. To remove the extension anyway, uninstall it under Modules &gt; Article administration; duplicating will then carry model identifier, duration and guarantee conditions over again.');
  define('MODULE_GUARANTEE_LABELS_TEXT_GD_ERROR', 'GD with FreeType and the function imagettfbbox() are not available on this server. Without them the text width of the GARAN label cannot be measured. The module has not been installed.');
  define('MODULE_GUARANTEE_LABELS_TEXT_INCOMPLETE', 'The module cannot be switched on because parts of it are missing: %s');
  define('MODULE_GUARANTEE_LABELS_TEXT_SCHEMA_ERROR', 'The database structure for the EU labels could not be created completely. The module has not been enabled.');

  define('MODULE_GUARANTEE_LABELS_TEXT_ERROR_TABLE', 'The table %s is missing.');
  define('MODULE_GUARANTEE_LABELS_TEXT_ERROR_INDEX', 'The index %s of table %s is missing.');
  define('MODULE_GUARANTEE_LABELS_TEXT_ERROR_COLUMN', 'The column %s of table %s is missing.');
  define('MODULE_GUARANTEE_LABELS_TEXT_ERROR_COLUMN_TYPE', 'The column %s of table %s has type %s, expected %s. The column has not been changed.');
  define('MODULE_GUARANTEE_LABELS_TEXT_ERROR_COLUMN_PROPERTY', 'The column %s of the table %s does not match the intended schema in its %s.');

  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS', 'Check of the requirements');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_CACHE', 'The module empties no cache by itself. Rendered labels are stored under the hash of their content and are never wrong, only orphaned after a change. With the shop cache switched on, cross selling, new articles and similar blocks can also show an old label until the cache lifetime runs out. Empty the shop cache through configuration &raquo; cache after changes to manufacturers or articles.');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_OK', 'in place');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_COMPLETE', 'Every requirement is met.');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_FAILED', 'missing');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_EXTENSION', 'class extension %s installed and active');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_SELECT', 'guarantee duration part of %s');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_RENDERER', 'templates, fonts and GD for building the label');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_NOTICE', 'notice about the legal guarantee and texts for %s');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_SCHEMA', 'tables, indexes and columns of the module');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_WRITABLE', 'write permission on %s');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_PRODUCTS', 'articles with duration, active manufacturer and model identifier');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_DURATION', 'durations within the allowed range and in half year steps');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_AMBIGUOUS', 'guarantee conditions unique per article and language');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_GROUPS', 'guarantee conditions reachable for every customer group that sees the label');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_SHARED', 'the same file marked as guarantee conditions in several languages');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_ARCHIVE', 'archived files of existing orders in place');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_INCOMPLETE', 'incomplete:');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_LOCKED', 'not writable:');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_AFFECTED', 'affected:');
