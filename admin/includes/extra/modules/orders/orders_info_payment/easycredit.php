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

  ## easyCredit
  if ($order->info['payment_method'] == 'easycredit') {
    $easycredit_query = xtc_db_query("SELECT *
                                        FROM `easycredit`
                                       WHERE orders_id = '".(int)$oID."'");
    if (xtc_db_num_rows($easycredit_query) == 1) {
      $easycredit = xtc_db_fetch_array($easycredit_query);
      ?>
        <tr>
          <td class="main"><b><?php echo TEXT_EASYCREDIT_TBAID; ?>:</b></td>
          <td class="main"><?php echo $easycredit['technicalTbaId']; ?></td>
        </tr>
      <?php
    }
  }
?>