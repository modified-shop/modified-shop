<?php
/* -----------------------------------------------------------------------------------------
   $Id: easybill.button.php 4241 2013-01-11 13:47:24Z gtb-modified $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  if (MODULE_EASYBILL_STATUS=='True') {
    include_once (DIR_FS_EXTERNAL.'easybill/lang/'.$order->info['language'].'/easybill.php');
    ?>
    <tr>
      <td>
        <fieldset>
          <legend><span class="main">easyBill</span></legend>
            <table>
              <tr>
                <td>
                  <?php
                    $easybill_query = xtc_db_query("SELECT * 
                                                      FROM ".TABLE_EASYBILL." 
                                                     WHERE orders_id='".(int)$oID."'");
                    if (xtc_db_num_rows($easybill_query)==0) {
                    ?>
                      <a class="button" href="<?php echo xtc_href_link(FILENAME_ORDERS, 'oID='.$oID.'&action=custom&subaction=easybill'); ?>"><?php echo EASYBILL_BUTTON_CREATE; ?></a>
                    <?php
                    } else {
                      $easybill = xtc_db_fetch_array($easybill_query);
                      if ($easybill['payment'] != '1') {
                      ?>
                        <a class="button" href="<?php echo xtc_href_link(FILENAME_ORDERS, 'oID='.$oID.'&action=custom&subaction=easybill&payment=true'); ?>"><?php echo EASYBILL_BUTTON_PAYMENT; ?></a>
                      <?php
                      }
                    }
                  ?>
                  <a class="button" href="<?php echo xtc_href_link(FILENAME_ORDERS, 'oID='.$oID.'&action=custom&subaction=easybill&download=true'); ?>"><?php echo EASYBILL_BUTTON_OPEN; ?></a>
                  <a class="button" href="<?php echo xtc_href_link(FILENAME_ORDERS, 'oID='.$oID.'&action=custom&subaction=easybill&save=true'); ?>"><?php echo EASYBILL_BUTTON_SAVE; ?></a>
                </td>
              </tr>
            </table>
        </fieldset>
      </td>
    </tr>
    <?php
  }
  ?>