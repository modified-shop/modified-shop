<?php
/* -----------------------------------------------------------------------------------------
   $Id: easybill.button.php 4241 2013-01-11 13:47:24Z gtb-modified $

   Modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 Modified
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  if (MODULE_EASYBILL_STATUS=='True') {
  ?>
    <tr>
      <td>
        <fieldset>
          <legend><span class="main">easyBill</span></legend>
            <table>
              <tr>
                <td>
                  <?php
                    $easybill_query = xtc_db_query("SELECT * FROM easybill WHERE orders_id='".$oID."'");
                    if (xtc_db_num_rows($easybill_query)==0) {
                    ?>
                      <a class="button" href="<?php echo xtc_href_link(FILENAME_ORDERS, 'oID='.$oID.'&action=easybill'); ?>"><?php echo 'Rechnung erstellen';?></a>
                    <?php
                    } else {
                      $easybill = xtc_db_fetch_array($easybill_query);
                      if ($easybill['payment'] != '1') {
                      ?>
                        <a class="button" href="<?php echo xtc_href_link(FILENAME_ORDERS, 'oID='.$oID.'&action=easybill&payment=true'); ?>"><?php echo 'Rechnung bezahlt';?></a>
                      <?php
                      }
                    }
                  ?>
                  <a class="button" href="<?php echo xtc_href_link(FILENAME_ORDERS, 'oID='.$oID.'&action=easybill&download=true'); ?>"><?php echo 'Rechnung &ouml;ffnen';?></a>
                  <a class="button" href="<?php echo xtc_href_link(FILENAME_ORDERS, 'oID='.$oID.'&action=easybill&save=true'); ?>"><?php echo 'Rechnung speichern';?></a>
                </td>
              </tr>
            </table>
        </fieldset>
      </td>
    </tr>
  <?php
  }
  ?>