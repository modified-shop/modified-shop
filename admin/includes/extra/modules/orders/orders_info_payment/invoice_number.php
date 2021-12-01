<?php
 /*-------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  if (defined('MODULE_INVOICE_NUMBER_STATUS') 
      && MODULE_INVOICE_NUMBER_STATUS == 'True'
      )
  {
    ?>
    <tr>
      <td class="main"><b><?php echo ENTRY_INVOICE_NUMBER; ?></b></td>
      <td class="main"><?php echo (($order->info['ibn_billnr'] == '') ? '<span class="not_assigned">'.NOT_ASSIGNED.'<span>' : $order->info['ibn_billnr']); ?></td>
    </tr>
    <tr>
      <td class="main"><b><?php echo ENTRY_INVOICE_DATE; ?></b></td>
      <td class="main"><?php echo (($order->info['ibn_billdate'] == '0000-00-00') ? '<span class="not_assigned">'.NOT_ASSIGNED.'<span>' : xtc_date_short($order->info['ibn_billdate'])); ?></td>
    </tr>
    <?php
  }
