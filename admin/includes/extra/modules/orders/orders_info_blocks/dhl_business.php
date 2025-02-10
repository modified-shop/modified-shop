<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  if (defined('MODULE_DHL_BUSINESS_STATUS') && MODULE_DHL_BUSINESS_STATUS == 'True') { 
    ?>
    <div class="heading"><?php echo 'DHL:'; ?></div>
    <?php
    echo xtc_draw_form('dhl', FILENAME_ORDERS, xtc_get_all_get_params(array('action')) . 'action=custom&subaction=createlabel').
         xtc_draw_hidden_field('oID', $oID);
      ?>
      <table cellspacing="0" cellpadding="5" class="table borderall">
        <tr>
          <td class="smallText" align="center"><strong><?php echo TABLE_HEADING_DHL_BUSINESS_SHIPPING_NUMBER; ?></strong></td>
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
                          <a href="'.xtc_href_link(FILENAME_ORDERS, 'oID='.$oID.'&tID='.$tracking['tracking_id'].'&action=custom&subaction=deletetracking').'">'.xtc_image(DIR_WS_ICONS.'cross.gif', ICON_DELETE).'</a>'.
                          (($tracking['dhl_label_url'] != '') ? " <a href=\"Javascript:void(0)\" onclick=\"window.open('".$tracking['dhl_label_url']."', 'DHL Label', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no, width=600, height=1000')\">".xtc_image(DIR_WS_ICONS.'icon_pdf.gif', 'DHL Label')."</a>" : '').
                          (($tracking['dhl_export_url'] != '') ? " <a href=\"Javascript:void(0)\" onclick=\"window.open('".$tracking['dhl_export_url']."', 'DHL Export', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no, width=1000, height=1000')\">".xtc_image(DIR_WS_ICONS.'icon_pdf.gif', 'DHL Export')."</a>" : '').'
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
                array('id' => '0', 'text' => TEXT_DHL_BUSINESS_NO),
                array('id' => '16', 'text' => sprintf(TEXT_DHL_BUSINESS_AVS_YEAR, 16)),
                array('id' => '18', 'text' => sprintf(TEXT_DHL_BUSINESS_AVS_YEAR, 18)),
              );

              $endorsement_array = array(
                array('id' => 'IMMEDIATE', 'text' => CFG_TXT_IMMEDIATE),
                array('id' => 'ABANDONMENT', 'text' => CFG_TXT_ABANDONMENT),
              );

              $orders_statuses_selected = MODULE_DHL_BUSINESS_STATUS_UPDATE;
              if (MODULE_DHL_BUSINESS_STATUS_UPDATE == '0') {
                $orders_statuses_selected = $order->info['orders_status'];
              }
              
              require_once(DIR_FS_EXTERNAL.'dhl/DHLBusinessShipment.php');
              $dhl = new DHLBusinessShipment(array());
              $weight = $dhl->calculate_weight($oID);
              
              if (!isset($order->customer['dob'])) {
                $order->customer['dob'] = '0000-00-00';
                $check_query = xtc_db_query("SELECT customers_dob
                                               FROM ".TABLE_CUSTOMERS."
                                              WHERE customers_id = '".(int)$order->customer['ID']."'");
                if (xtc_db_num_rows($check_query) > 0) {
                  $check = xtc_db_fetch_array($check_query);
                  $order->customer['dob'] = $check['customers_dob'];
                }
              }
            ?>
            <table cellspacing="0" cellpadding="5" class="tableInput border0" style="padding:0;">
              <tr>
                <td style="width:16%;padding:5px;border-width: 0 0 1px 0;"><?php echo TEXT_DHL_BUSINESS_WEIGHT; ?></td>
                <td style="width:16%;padding:5px;border-width: 0 1px 1px 0;"><?php echo xtc_draw_input_field('weight', $weight, 'style="width: 120px; padding:5px;" placeholder="optional..."'); ?></td>
                <td style="width:16%;padding:5px;border-width: 0 0 1px 0;"><?php echo TEXT_DHL_BUSINESS_INSURANCE; ?></td>
                <td style="width:16%;padding:5px;border-width: 0 1px 1px 0;"><?php echo xtc_draw_pull_down_menu('insurance', $insurance_array, '', 'style="width:120px;"'); ?></td>
                <td style="width:16%;padding:5px;border-width: 0 0 1px 0;"><?php echo TEXT_DHL_BUSINESS_CODEABLE; ?></td>
                <td style="width:16%;padding:5px;border-width: 0 0 1px 0;"><?php echo xtc_draw_pull_down_menu('codeable', 'checkbox', ((MODULE_DHL_BUSINESS_CODING == 'True') ? true : false), 'style="width:120px;"'); ?></td>
              </tr>
              <tr class="dhl_expand dhl_toggle">
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo TEXT_DHL_BUSINESS_SIGNED; ?></td>
                <td style="padding:5px;border-width: 0 1px 1px 0;"><?php echo xtc_draw_pull_down_menu('signed', 'checkbox', ((MODULE_DHL_BUSINESS_SIGNED == 'True') ? true : false), 'style="width:120px;"'); ?></td>
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo TEXT_DHL_BUSINESS_RETOURE; ?></td>
                <td style="padding:5px;border-width: 0 1px 1px 0;"><?php echo xtc_draw_pull_down_menu('retoure', 'checkbox', ((MODULE_DHL_BUSINESS_RETOURE == 'True') ? true : false), 'style="width:120px;"'); ?></td>
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo TEXT_DHL_BUSINESS_STATUS_UPDATE; ?></td>
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo xtc_draw_pull_down_menu('status_update', array_merge(array(array('id' => '0', 'text' => TEXT_DHL_BUSINESS_NO)), $orders_statuses), ((MODULE_DHL_BUSINESS_STATUS_UPDATE == '0') ? $order->info['orders_status'] : MODULE_DHL_BUSINESS_STATUS_UPDATE), 'style="width:120px;"'); ?></td>
              </tr>
              <tr class="dhl_expand dhl_toggle">
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo TEXT_DHL_BUSINESS_AVS; ?></td>
                <td style="padding:5px;border-width: 0 1px 1px 0;"><?php echo xtc_draw_pull_down_menu('avs', $avs_array, MODULE_DHL_BUSINESS_AVS, 'style="width:120px;"'); ?></td>
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo TEXT_DHL_BUSINESS_PERSONAL; ?></td>
                <td style="padding:5px;border-width: 0 1px 1px 0;"><?php echo xtc_draw_pull_down_menu('personal', 'checkbox', ((MODULE_DHL_BUSINESS_PERSONAL == 'True') ? true : false), 'style="width:120px;"'); ?></td>
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo TEXT_DHL_BUSINESS_NO_NEIGHBOUR; ?></td>
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo xtc_draw_pull_down_menu('no_neighbour', 'checkbox', ((MODULE_DHL_BUSINESS_NO_NEIGHBOUR == 'True') ? true : false), 'style="width:120px;"'); ?></td>
              </tr>
              <tr class="dhl_expand dhl_toggle">
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo TEXT_DHL_BUSINESS_IDENT; ?></td>
                <td style="padding:5px;border-width: 0 1px 1px 0;"><?php echo xtc_draw_pull_down_menu('ident', $avs_array, MODULE_DHL_BUSINESS_IDENT, 'style="width:120px;"'); ?></td>
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo TEXT_DHL_BUSINESS_DOB; ?></td>
                <td style="padding:5px;border-width: 0 1px 1px 0;"><?php echo xtc_draw_input_field('dob', ((strtotime($order->customer['dob']) > 0 && strtotime($order->customer['dob']) != false) ? date('d.m.Y', strtotime($order->customer['dob'])) : ''), 'style="width: 120px; padding:5px;" placeholder="dd.mm.YYYY"'); ?></td>
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo TEXT_DHL_BUSINESS_BULKY; ?></td>
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo xtc_draw_pull_down_menu('bulky', 'checkbox', ((MODULE_DHL_BUSINESS_BULKY == 'True') ? true : false), 'style="width:120px;"'); ?></td>
              </tr>
              <tr class="dhl_expand dhl_toggle">
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo TEXT_DHL_BUSINESS_PARCEL_OUTLET; ?></td>
                <td style="padding:5px;border-width: 0 1px 1px 0;"><?php echo xtc_draw_pull_down_menu('parcel_outlet', 'checkbox', ((MODULE_DHL_BUSINESS_PARCEL_OUTLET == 'True') ? true : false), 'style="width:120px;"'); ?></td>
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo TEXT_DHL_BUSINESS_PREMIUM; ?></td>
                <td style="padding:5px;border-width: 0 1px 1px 0;"><?php echo xtc_draw_pull_down_menu('premium', 'checkbox', ((MODULE_DHL_BUSINESS_PREMIUM == 'True') ? true : false), 'style="width:120px;"'); ?></td>
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo TEXT_DHL_BUSINESS_NOTIFICATION; ?></td>
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo xtc_draw_pull_down_menu('notification', 'checkbox', ((MODULE_DHL_BUSINESS_NOTIFICATION == 'True') ? true : false), 'style="width:120px;"'); ?></td>
              </tr>
              <tr class="dhl_expand dhl_toggle">
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo TEXT_DHL_BUSINESS_ENDORSEMENT; ?></td>
                <td style="padding:5px;border-width: 0 1px 1px 0;"><?php echo xtc_draw_pull_down_menu('endorsement', $endorsement_array, MODULE_DHL_BUSINESS_ENDORSEMENT, 'style="width:120px;"'); ?></td>
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo TEXT_DHL_BUSINESS_DUTYPAID; ?></td>
                <td style="padding:5px;border-width: 0 1px 1px 0;"><?php echo xtc_draw_pull_down_menu('dutypaid', 'checkbox', ((MODULE_DHL_BUSINESS_DUTYPAID == 'True') ? true : false), 'style="width:120px;"'); ?></td>
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo TEXT_DHL_BUSINESS_DROPPOINT; ?></td>
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo xtc_draw_pull_down_menu('droppoint', 'checkbox', ((MODULE_DHL_BUSINESS_DROPPOINT == 'True') ? true : false), 'style="width:120px;"'); ?></td>
              </tr>
              <tr class="dhl_expand dhl_toggle">
                <td style="padding:5px;border-width: 0 0 1px 0;"><?php echo TEXT_DHL_BUSINESS_MRN; ?></td>
                <td style="padding:5px;border-width: 0 1px 1px 0;"><?php echo xtc_draw_input_field('mrn', '', 'style="width: 120px; padding:5px;"'); ?></td>
                <td style="padding:5px;border-width: 0 0 1px 0;"></td>
                <td style="padding:5px;border-width: 0 0 1px 0;"></td>
                <td style="padding:5px;border-width: 0 0 1px 0;"></td>
                <td style="padding:5px;border-width: 0 0 1px 0;"></td>
              </tr>
              <tr id="dhl_expand">
                <td colspan="6" style="padding:5px;border-width: 0 0 0 0;">
                  <div style="text-align:center;font-weight:bold;">
                    <div class="dhl_expand" style="cursor:pointer;"><?php echo TEXT_DHL_BUSINESS_SHOW_MORE; ?></div>
                    <div class="dhl_expand dhl_toggle" style="cursor:pointer;"><?php echo TEXT_DHL_BUSINESS_SHOW_LESS; ?></div>
                  </div>
                </td>
              </tr>
            </table>
          </td>
          <td class="smallText" align="center">
            <button class="button btnbox" name="type" type="submit" value="0"><?php echo TEXT_DHL_BUSINESS_BUTTON_CREATE_PK; ?></button>
            <button class="button btnbox no_bottom_margin" name="type" type="submit" value="1"><?php echo (($order->delivery['country_iso_2'] == 'DE') ? TEXT_DHL_BUSINESS_BUTTON_CREATE_KP : TEXT_DHL_BUSINESS_BUTTON_CREATE_WP); ?></button>            
          </td>
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
    if (MODULE_DHL_BUSINESS_DISPLAY_LABEL == 'True'
        && isset($_SESSION['DHLparcel_id']) 
        && $_SESSION['DHLparcel_id'] != ''
        )
    {                                    
      $check_query = xtc_db_query("SELECT *
                                     FROM ".TABLE_ORDERS_TRACKING."
                                    WHERE parcel_id = '".xtc_db_input($_SESSION['DHLparcel_id'])."'
                                      AND orders_id = '".(int)$oID."'
                                      AND dhl_label_url != ''
                                 ORDER BY tracking_id DESC 
                                    LIMIT 1");
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
