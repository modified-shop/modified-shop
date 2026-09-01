<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  // The files may already be in place while the database update and the module installation
  // are not. Asking for the new article columns before that would break the order editing of
  // such a shop, so the status decides first.
  if (defined('MODULE_GUARANTEE_LABELS_STATUS') && MODULE_GUARANTEE_LABELS_STATUS == 'true') {
    require_once(DIR_FS_INC.'guarantee_labels_snapshot.inc.php');

    $guarantee_labels_product = guarantee_labels_snapshot_product($data_array['products_id']);

    if ($guarantee_labels_product !== false) {
      // the group of the order decides, the session belongs to the admin. The language of the
      // order selects the guarantee conditions, it may differ from the one of the backend.
      guarantee_labels_product_snapshot(
        $oID,
        $orders_products_id,
        $guarantee_labels_product,
        $lang['languages_id'],
        $order->info['status']
      );
    }

    // a broken language, renderer or archive must not end in the log alone
    foreach (guarantee_labels_snapshot_failures() as $guarantee_labels_error) {
      $messageStack->add_session(sprintf(ERROR_GUARANTEE_LABELS_SNAPSHOT_FAILED, encode_htmlspecialchars($guarantee_labels_error)), 'error');
    }

    unset($guarantee_labels_product);
  }
