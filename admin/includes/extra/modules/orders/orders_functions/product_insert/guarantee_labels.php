<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

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

  unset($guarantee_labels_product);
