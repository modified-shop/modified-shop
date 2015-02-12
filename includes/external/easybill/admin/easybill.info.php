<?php
/* -----------------------------------------------------------------------------------------
   $Id: easybill.info.php 4241 2013-01-11 13:47:24Z gtb-modified $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

if (MODULE_EASYBILL_STATUS=='True') {
  include_once (DIR_FS_EXTERNAL.'easybill/lang/'.$order->info['language'].'/easybill.php');
  $easybill_query = xtc_db_query("SELECT * 
                                    FROM ".TABLE_EASYBILL." 
                                   WHERE orders_id='".(int)$oID."'");
  if (xtc_db_num_rows($easybill_query)>0) {
    $easybill = xtc_db_fetch_array($easybill_query);
    ?>
      <tr>
        <td class="main" valign="top"><b>easyBill:</b></td>
        <td>
          <fieldset>
            <legend><span class="main">easyBill</span></legend>
              <table>
                <tr>
                  <td class="main"><b><?php echo EASYBILL_INVOICE_ID; ?></b></td>
                  <td class="main"><?php echo $easybill['billing_id']; ?></td>
                </tr>
                <tr>
                  <td class="main"><b><?php echo EASYBILL_INVOICE_DATE; ?></b></td>
                  <td class="main"><?php echo xtc_datetime_short($easybill['billing_date']); ?></td>
                </tr>
              </table>
          </fieldset>
        </td>
      </tr>
    <?php
  } 
}
?>