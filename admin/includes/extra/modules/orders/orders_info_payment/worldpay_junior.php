<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  if ((int)$oID > 0
      && isset($order)
      && is_object($order)
      && isset($order->info['payment_method'])
      && $order->info['payment_method'] === 'worldpay_junior'
      && defined('TABLE_WORLDPAY_JUNIOR_TRANSACTIONS')
      )
  {
    $worldpay_table_pattern = str_replace('_', '\\_', TABLE_WORLDPAY_JUNIOR_TRANSACTIONS);
    $worldpay_table_query = xtc_db_query("SHOW TABLES LIKE '".xtc_db_input($worldpay_table_pattern)."'");
    if ($worldpay_table_query !== false && xtc_db_num_rows($worldpay_table_query) === 1) {
      $worldpay_transaction_query = xtc_db_query("SELECT transaction_id,
                                                          transaction_status
                                                    FROM ".TABLE_WORLDPAY_JUNIOR_TRANSACTIONS."
                                                   WHERE orders_id = '".(int)$oID."'
                                                   LIMIT 1");
      if ($worldpay_transaction_query !== false
          && xtc_db_num_rows($worldpay_transaction_query) === 1
          )
      {
        $worldpay_transaction = xtc_db_fetch_array($worldpay_transaction_query);
        ?>
        <tr>
          <td class="main"><b><?php echo TEXT_WORLDPAY_JUNIOR_TRANSACTION_ID; ?></b></td>
          <td class="main"><?php echo encode_htmlspecialchars($worldpay_transaction['transaction_id']); ?></td>
        </tr>
        <tr>
          <td class="main"><b><?php echo TEXT_WORLDPAY_JUNIOR_TRANSACTION_STATUS; ?></b></td>
          <td class="main">
            <?php
              echo $worldpay_transaction['transaction_status'] === 'verified'
                ? TEXT_WORLDPAY_JUNIOR_TRANSACTION_STATUS_VERIFIED
                : TEXT_WORLDPAY_JUNIOR_TRANSACTION_STATUS_PENDING;
            ?>
          </td>
        </tr>
        <?php
      }
    }
  }
