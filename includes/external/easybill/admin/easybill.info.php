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
  include_once (DIR_FS_EXTERNAL.'easybill/lang/'.$_SESSION['language'].'/easybill.php');
  $easybill_query = xtc_db_query("SELECT * 
                                    FROM ".TABLE_EASYBILL." 
                                   WHERE orders_id='".(int)$oID."'");
  $easybill_num = xtc_db_num_rows($easybill_query);
  if ($easybill_num >= 0) {
    $easybill = xtc_db_fetch_array($easybill_query);
    ?>
    <div class="heading">easyBill:</div>
    <table cellspacing="0" cellpadding="2" class="table">
      <?php
      if ($easybill_num > 0) {
        ?>
        <tr>
          <td class="main" style="width:180px;"><b><?php echo EASYBILL_INVOICE_ID; ?></b></td>
          <td class="main"><?php echo $easybill['billing_id']; ?></td>
        </tr>
        <tr>
          <td class="main"><b><?php echo EASYBILL_INVOICE_DATE; ?></b></td>
          <td class="main"><?php echo xtc_datetime_short($easybill['billing_date']); ?></td>
        </tr>
        <?php
      }
      ?>
      <tr>
        <td colspan="2">
          <?php
          if ($easybill_num == 0) {
            ?>
            <a class="button" href="<?php echo xtc_href_link(FILENAME_ORDERS, 'oID='.$oID.'&action=custom&subaction=easybill'); ?>"><?php echo EASYBILL_BUTTON_CREATE; ?></a>
            <?php
          } else {
            if ($easybill['payment'] != '1') {
              ?>
              <a class="button" href="<?php echo xtc_href_link(FILENAME_ORDERS, 'oID='.$oID.'&action=custom&subaction=easybill&payment=true'); ?>"><?php echo EASYBILL_BUTTON_PAYMENT; ?></a>
              <?php
            }
            ?>
            <a class="button" href="<?php echo xtc_href_link(FILENAME_ORDERS, 'oID='.$oID.'&action=custom&subaction=easybill&download=true'); ?>"><?php echo EASYBILL_BUTTON_OPEN; ?></a>
            <a class="button" href="<?php echo xtc_href_link(FILENAME_ORDERS, 'oID='.$oID.'&action=custom&subaction=easybill&save=true'); ?>"><?php echo EASYBILL_BUTTON_SAVE; ?></a>
            <?php
          }
          ?>
        </td>
      </tr>
    </table>
    <?php
  } 
}
?>