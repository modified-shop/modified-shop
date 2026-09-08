<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // The module status first: these files ship with the module, the configuration does not.
  // A shop that never installed it must not load module code on every request.
  if (defined('MODULE_GUARANTEE_LABELS_STATUS') && MODULE_GUARANTEE_LABELS_STATUS == 'true') {
    require_once(DIR_FS_INC.'guarantee_labels_snapshot.inc.php');

    // The cart strips the products_ prefix from every column, the snapshot works on the names of
    // the catalogue. This point runs after the insert into orders_products, so the row id exists.
    guarantee_labels_product_snapshot(
      $insert_id,
      $order_products_id,
      array(
        'products_id' => xtc_get_prid($order->products[$i]['id']),
        'products_garan_duration' => isset($order->products[$i]['garan_duration']) ? $order->products[$i]['garan_duration'] : null,
        'products_manufacturers_model' => isset($order->products[$i]['manufacturers_model']) ? $order->products[$i]['manufacturers_model'] : '',
        'manufacturers_id' => isset($order->products[$i]['manufacturers_id']) ? $order->products[$i]['manufacturers_id'] : 0,
      ),
      $_SESSION['languages_id'],
      null,
      // the same decision the cart made: the chosen combination, not the whole article
      $order->products[$i]['id']
    );
  }
