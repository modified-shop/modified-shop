<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  if (defined('MODULE_DHL_STATUS') && MODULE_DHL_STATUS == 'True') { 
    ?>
    <div class="heading"><?php echo 'DHL:'; ?></div>
    <?php
    echo xtc_draw_form('dhl', FILENAME_ORDERS, xtc_get_all_get_params(array('action')) . 'action=custom&saction=createlabel').
         xtc_draw_hidden_field('oID', $oID);
      ?>
      <table cellspacing="0" cellpadding="5" class="table borderall">
        <tr>
          <td class="smallText" align="center"><strong><?php echo TABLE_HEADING_DHL_SHIPPING_NUMBER; ?></strong></td>
          <td class="smallText" align="center" style="width:100px;"><strong><?php echo TABLE_HEADING_DATE; ?></strong></td>
          <td class="smallText" align="center" style="width:150px;"><strong><?php echo TABLE_HEADING_ACTION; ?></strong></td>
        </tr>
        <?php
          $tracking_array = get_tracking_link($oID, $lang_code);
          if (count($tracking_array) > 0) {
            foreach($tracking_array as $tracking) {
              if ($tracking['external'] == '2') {
                echo '<tr>'.PHP_EOL;
                echo '  <td class="smallText" align="left">'.$tracking['parcel_id'].'</td>'.PHP_EOL;
                echo '  <td class="smallText" align="center">'.xtc_date_short($tracking['date_added']).'</td>'.PHP_EOL;
                echo '  <td class="smallText" align="center">
                          <a href="'.xtc_href_link(FILENAME_ORDERS, 'oID='.$oID.'&tID='.$tracking['tracking_id'].'&action=custom&saction=deletetracking').'">'.xtc_image(DIR_WS_ICONS.'cross.gif', ICON_DELETE).'</a>'.
                          (($tracking['dhl_label_url'] != '') ? " <a href=\"Javascript:void()\" onclick=\"window.open('".$tracking['dhl_label_url']."', 'DHL Label', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no, width=600, height=1000')\">".xtc_image(DIR_WS_ICONS.'icon_pdf.gif', 'DHL Label')."</a>" : '').
                          (($tracking['dhl_export_url'] != '') ? " <a href=\"Javascript:void()\" onclick=\"window.open('".$tracking['dhl_export_url']."', 'DHL Export', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no, width=1000, height=1000')\">".xtc_image(DIR_WS_ICONS.'icon_pdf.gif', 'DHL Export')."</a>" : '').'
                        </td>'.PHP_EOL;
                echo '<tr>'.PHP_EOL;
              }
            }
          }
        ?>
        <tr>
          <td class="smallText" align="center" colspan="2" style="padding:0;">
            <?php 
              $insurance_array = array(
                array('id' => '0', 'text' => 'Standard'),
                array('id' => '1', 'text' => '2.500,-'),
                array('id' => '2', 'text' => '25.000,-'),
              );

              $avs_array = array(
                array('id' => '0', 'text' => TEXT_DHL_NO),
                array('id' => '16', 'text' => sprintf(TEXT_DHL_AVS_YEAR, 16)),
                array('id' => '18', 'text' => sprintf(TEXT_DHL_AVS_YEAR, 18)),
              );

              $type_array = array(
                array('id' => '0', 'text' => 'Paket'),
                array('id' => '1', 'text' => 'Warenpost'),
              );
              
              $orders_statuses_selected = MODULE_DHL_STATUS_UPDATE;
              if (MODULE_DHL_STATUS_UPDATE == '0') {
                $orders_statuses_selected = $order->info['orders_status'];
              }
              
              require_once(DIR_FS_EXTERNAL.'dhl/DHLBusinessShipment.php');
              $dhl = new DHLBusinessShipment(array());
              $weight = $dhl->calculate_weight($oID);
              
              if (!isset($order->customer['dob'])) {
                $check_query = xtc_db_query("SELECT customers_dob
                                               FROM ".TABLE_CUSTOMERS."
                                              WHERE customers_id = '".(int)$order->customer['ID']."'");
                $check = xtc_db_fetch_array($check_query);
                $order->customer['dob'] = $check['customers_dob'];
              }
            ?>
            <table cellspacing="0" cellpadding="5" class="tableInput border0" style="padding:0;">
              <tr>
                <td style="width:16%;padding:5px;border-width: 0 0 1px 0;"><?php echo TEXT_DHL_WEIGHT; ?></td>
                <td style="width:16%;padding:5px;border-width: 0 1px 1px 0;"><?php echo xtc_draw_input_field('weight', $weight, 'style="width: 120px; padding:5px;" placeholder="optional..."'); ?></td>
                <td style="width:16%;padding:5px;border-width: 0 0 1px 0;"><?php echo TEXT_DHL_TYPE; ?></td>
                <td style="width:16%;padding:5px;border-width: 0 1px 1px 0;"><?php echo xtc_draw_pull_down_menu('type', $type_array, ((MODULE_DHL_PRODUCT == 'Paket') ? 0 : 1), 'style="width:120px;"'); ?></td>
                <td style="width:16%;padding:5px;border-width: 0 0 1px 0;"><?php echo TEXT_DHL_CODEABLE; ?></td>
                <td style="width:16%;padding:5px;border-width: 0 0 1px 0;"><?php echo xtc_draw_pull_down_menu('codeable', 'checkbox', ((MODULE_DHL_CODING == 'True') ? true : false), 'style="width:120px;"'); ?></td>
              </tr>
              <tr class="dhl_expand dhl_toggle">
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo TEXT_DHL_INSURANCE; ?></td>
                <td style="padding:5px;border-width: 0 1px 1px 0;"><?php echo xtc_draw_pull_down_menu('insurance', $insurance_array, '', 'style="width:120px;"'); ?></td>
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo TEXT_DHL_RETOURE; ?></td>
                <td style="padding:5px;border-width: 0 1px 1px 0;"><?php echo xtc_draw_pull_down_menu('retoure', 'checkbox', ((MODULE_DHL_RETOURE == 'True') ? true : false), 'style="width:120px;"'); ?></td>
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo TEXT_DHL_STATUS_UPDATE; ?></td>
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo xtc_draw_pull_down_menu('status_update', array_merge(array(array('id' => '0', 'text' => TEXT_DHL_NO)), $orders_statuses), ((MODULE_DHL_STATUS_UPDATE == '0') ? $order->info['orders_status'] : MODULE_DHL_STATUS_UPDATE), 'style="width:120px;"'); ?></td>
              </tr>
              <tr class="dhl_expand dhl_toggle">
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo TEXT_DHL_AVS; ?></td>
                <td style="padding:5px;border-width: 0 1px 1px 0;"><?php echo xtc_draw_pull_down_menu('avs', $avs_array, '', 'style="width:120px;"'); ?></td>
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo TEXT_DHL_PERSONAL; ?></td>
                <td style="padding:5px;border-width: 0 1px 1px 0;"><?php echo xtc_draw_pull_down_menu('personal', 'checkbox', ((MODULE_DHL_PERSONAL == 'True') ? true : false), 'style="width:120px;"'); ?></td>
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo TEXT_DHL_BULKY; ?></td>
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo xtc_draw_pull_down_menu('bulky', 'checkbox', ((MODULE_DHL_BULKY == 'True') ? true : false), 'style="width:120px;"'); ?></td>
              </tr>
              <tr class="dhl_expand dhl_toggle">
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo TEXT_DHL_IDENT; ?></td>
                <td style="padding:5px;border-width: 0 1px 1px 0;"><?php echo xtc_draw_pull_down_menu('ident', $avs_array, '', 'style="width:120px;"'); ?></td>
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo TEXT_DHL_DOB; ?></td>
                <td style="padding:5px;border-width: 0 1px 1px 0;"><?php echo xtc_draw_input_field('dob', ((strtotime($order->customer['dob']) > 0 && strtotime($order->customer['dob']) != false) ? date('d.m.Y', strtotime($order->customer['dob'])) : ''), 'style="width: 120px; padding:5px;" placeholder="dd.mm.YYYY"'); ?></td>
                <td style="padding:5px;border-width: 0 0 1px 0;"></td>
                <td style="padding:5px;border-width: 0 0 1px 0;"></td>
              </tr>
              <tr id="dhl_expand">
                <td colspan="6" style="padding:5px;border-width: 0 0 0 0;">
                  <div style="text-align:center;font-weight:bold;">
                    <div class="dhl_expand" style="cursor:pointer;"><?php echo TEXT_DHL_SHOW_MORE; ?></div>
                    <div class="dhl_expand dhl_toggle" style="cursor:pointer;"><?php echo TEXT_DHL_SHOW_LESS; ?></div>
                  </div>
                </td>
              </tr>
            </table>
          </td>
          <td class="smallText" align="center"><input class="button" type="submit" value="<?php echo TEXT_DHL_BUTTON_CREATE; ?>"></td>
        </tr>
      </table>
    </form>
    <script>      
      if (localStorage.toggled !== undefined) $('.dhl_expand').toggleClass(localStorage.toggled);

      $('#dhl_expand').on('click',function(){
        $('.dhl_expand').toggleClass("dhl_toggle");
        if (localStorage.toggled != "dhl_toggle" ) {
          localStorage.toggled = "dhl_toggle";
        } else {
          localStorage.toggled = "";
        }
      });
    </script>
    <style>.dhl_toggle{display:none;visibillity:hidden;}</style>
    <?php
    if (MODULE_DHL_DISPLAY_LABEL == 'True'
        && isset($_SESSION['DHLparcel_id']) 
        && $_SESSION['DHLparcel_id'] != ''
        )
    {                                    
      $check_query = xtc_db_query("SELECT *
                                     FROM ".TABLE_ORDERS_TRACKING."
                                    WHERE parcel_id = '".xtc_db_input($_SESSION['DHLparcel_id'])."'
                                      AND dhl_label_url != ''");
      if (xtc_db_num_rows($check_query) > 0) {
        $check = xtc_db_fetch_array($check_query);
      
        if ($check['dhl_label_url'] != '') {
          echo "<script>
                  $(document).ready(function() {      
                    window.open('".$check['dhl_label_url']."', 'DHL Label', 'toolbar=0, width=600, height=1000');
                  });
                </script>";
        }     

        if ($check['dhl_export_url'] != '') {
          echo "<script>
                  $(document).ready(function() {      
                    window.open('".$check['dhl_export_url']."', 'DHL Export', 'toolbar=0, width=1000, height=1000');
                  });
                </script>";
        }     
      }
      unset($_SESSION['DHLparcel_id']);
    }
  }
