<?php
 /*-------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/   
  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );
?>
 
      <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_orders.png'); ?></div>
      <div class="pageHeading pdg2"><?php echo TABLE_HEADING_ORDERS_ID.': ' . $oID . ' - ' . xtc_datetime_short($order->info['date_purchased']); ?></div>
      <div class="main pdg2"><?php echo HEADING_TITLE; ?></div>
      <div class="clear" style="margin-left: 5px;">     
        <a class="button" href="<?php echo xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action')));?>"><?php echo BUTTON_BACK; ?></a>
        <a class="button" href="<?php echo xtc_href_link(FILENAME_ORDERS_EDIT, 'oID='.$oID.'&cID=' . $order->customer['ID']);?>"><?php echo BUTTON_EDIT ?></a>        
      </div>

      <div class="div_box mrg5">
        <!-- BOC CUSTOMERS INFO BLOCK -->
        <table cellspacing="0" cellpadding="2" class="table">
          <tr>
            <td valign="top" style="border-right: 1px solid #a3a3a3;">
              <table width="100%" border="0" cellspacing="0" cellpadding="2">
                <?php if ($order->customer['csID']!='') { ?>
                <tr>
                  <td class="main bg_notice" valign="top"><b><?php echo ENTRY_CID; ?></b></td>
                  <td class="main bg_notice"><?php echo $order->customer['csID']; ?></td>
                </tr>
                <?php } ?>
                <tr>
                  <td class="main" valign="top"><b><?php echo ENTRY_CUSTOMER; ?></b></td>
                  <td class="main"><b><?php echo ENTRY_CUSTOMERS_ADDRESS; ?></b><br /><?php echo xtc_address_format($order->customer['format_id'], $order->customer, 1, '', '<br />'); ?></td>
                </tr>
                <tr>
                  <td colspan="2"><?php echo xtc_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
                </tr>
                <tr>
                  <td class="main" valign="top"><b><?php echo CUSTOMERS_MEMO; ?></b></td>
                <?php
                  // memo query
                  $memo_query = xtc_db_query("-- /admin/orders.php
                                             SELECT count(*) AS count
                                               FROM ".TABLE_CUSTOMERS_MEMO."
                                              WHERE customers_id=".$order->customer['ID']);
                  $memo_count = xtc_db_fetch_array($memo_query);
                ?>
                  <td class="main"><b><?php echo $memo_count['count'].'</b>'; ?>  <a style="cursor:pointer; font-size: 11px;" onclick="javascript:window.open('<?php echo xtc_href_link(FILENAME_POPUP_MEMO,'ID='.$order->customer['ID']); ?>', 'popup', 'scrollbars=yes, width=500, height=500')">(<?php echo DISPLAY_MEMOS; ?>)</a></td>
                </tr>
                <tr>
                  <td class="main"><b><?php echo ENTRY_TELEPHONE; ?></b></td>
                  <td class="main"><?php echo $order->customer['telephone']; ?></td>
                </tr>
                <tr>
                  <td class="main"><b><?php echo ENTRY_EMAIL_ADDRESS; ?></b></td>
                  <td class="main"><?php echo '<a href="mailto:' . $order->customer['email_address'] . '" style="font-size: 11px;">' . $order->customer['email_address'] . '</a>'; ?></td>
                </tr>
                <tr>
                  <td class="main"><b><?php echo ENTRY_CUSTOMERS_VAT_ID; ?></b></td>
                  <td class="main"><?php echo $order->customer['vat_id']; ?></td>
                </tr>
                <tr>
                  <td class="main bg_notice" valign="top"><b><?php echo IP; ?></b></td>
                  <td class="main bg_notice"><b><?php echo $order->customer['cIP']; ?></b></td>
                </tr>
              </table>
            </td>
              <?php
              $address_add_class = '';
              if ($order->delivery['name'] != $order->customer['name'] ||
                  $order->delivery['postcode'] != $order->customer['postcode'] ||
                  $order->delivery['city'] != $order->customer['city'] ||
                  $order->delivery['street_address'] != $order->customer['street_address']) {
                $address_add_class = ' bg_notice';
              }
              ?>
            <td class="main<?php echo $address_add_class; ?>" valign="top" style="border-right: 1px solid #a3a3a3;">
              <b><?php echo ENTRY_SHIPPING_ADDRESS; ?></b><br />
               <?php echo xtc_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br />'); ?>
            </td>
            <td valign="top" class="main">
              <b><?php echo ENTRY_BILLING_ADDRESS; ?></b><br />
              <?php echo xtc_address_format($order->billing['format_id'], $order->billing, 1, '', '<br />'); ?>
            </td>
          </tr>
        </table>
        <!-- EOC CUSTOMERS INFO BLOCK -->

        <!-- BOC PAYMENT BLOCK -->
        <table border="0" cellspacing="0" cellpadding="2" class="table">
          <tr>
            <td>
              <table border="0" cellspacing="0" cellpadding="2">
                <tr>
                  <td class="main" style="width:140px;"><b><?php echo ENTRY_LANGUAGE; ?></b></td>
                  <td class="main"><?php echo $lang_img = xtc_image(DIR_WS_LANGUAGES . $order->info['language'].'/admin/images/'.$lang_array['image'], $order->info['language']) .'&nbsp;&nbsp;'. $order->info['language']; ?></td>
                </tr>
                <tr>
                  <td class="main"><b><?php echo ENTRY_PAYMENT_METHOD; ?></b></td>
                  <td class="main"><?php echo get_payment_name($order->info['payment_method']) . ' ('.$order->info['payment_method'].')'; ?></td>
                </tr>
                <?php

                ## invoice number and date
                echo add_table_infos_ibillnr($order);
                
                ## easyBill
                include (DIR_FS_EXTERNAL.'easybill/admin/easybill.info.php');

                ## Paypal Express Modul
                if ($order->info['payment_method']=='paypal_directpayment' or $order->info['payment_method']=='paypal' or $order->info['payment_method']=='paypalexpress') {
                  require('../includes/classes/paypal_checkout.php');
                  require('includes/classes/class.paypal.php');
                  $paypal = new paypal_admin();
                  $paypal->admin_notification($oID);
                }

                ## Banktransfer
                $banktransfer_query = xtc_db_query("SELECT banktransfer_owner,
                                                           banktransfer_number,
                                                           banktransfer_bankname,
                                                           banktransfer_blz,
                                                           banktransfer_iban,
                                                           banktransfer_bic,
                                                           banktransfer_status,
                                                           banktransfer_prz,
                                                           banktransfer_fax,
                                                           banktransfer_owner_email
                                                      FROM ".TABLE_BANKTRANSFER."
                                                     WHERE orders_id = ".$oID);
                $banktransfer = xtc_db_fetch_array($banktransfer_query);
                if ($banktransfer['banktransfer_bankname'] || $banktransfer['banktransfer_blz'] || $banktransfer['banktransfer_number'] || $banktransfer['banktransfer_iban'] ) {
                  ?>
                  <tr>
                    <td colspan="2"><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
                  </tr>
                  <tr>
                    <td class="main"><?php echo TEXT_BANK_OWNER; ?></td>
                    <td class="main"><?php echo $banktransfer['banktransfer_owner']; ?></td>
                  </tr>
                  <tr>
                    <td class="main"><?php echo TEXT_BANK_NUMBER; ?></td>
                    <td class="main"><?php echo $banktransfer['banktransfer_number']; ?></td>
                  </tr>
                  <tr>
                    <td class="main"><?php echo TEXT_BANK_BLZ; ?></td>
                    <td class="main"><?php echo $banktransfer['banktransfer_blz']; ?></td>
                  </tr>
                  <tr>
                    <td class="main"><?php echo TEXT_BANK_IBAN; ?></td>
                    <td class="main"><?php echo $banktransfer['banktransfer_iban']; ?></td>
                  </tr>
                  <tr>
                    <td class="main"><?php echo TEXT_BANK_BIC; ?></td>
                    <td class="main"><?php echo $banktransfer['banktransfer_bic']; ?></td>
                  </tr>
                  <tr>
                    <td class="main"><?php echo TEXT_BANK_NAME; ?></td>
                    <td class="main"><?php echo $banktransfer['banktransfer_bankname']; ?></td>
                  </tr>
                  <tr>
                    <td class="main"><?php echo TEXT_BANK_OWNER_EMAIL; ?></td>
                    <td class="main"><?php echo $banktransfer['banktransfer_owner_email']; ?></td>
                  </tr>
                  <?php  if ($banktransfer['banktransfer_status'] == 0) { ?>
                  <tr>
                    <td class="main"><?php echo TEXT_BANK_STATUS; ?></td>
                    <td class="main"><?php echo "OK"; ?></td>
                  </tr>
                  <?php } else { ?>
                  <tr>
                    <td class="main"><?php echo TEXT_BANK_STATUS; ?></td>
                    <td class="main"><?php echo $banktransfer['banktransfer_status']; ?></td>
                  </tr>
                  <?php
                  $bt_status = (int) $banktransfer['banktransfer_status'];
                  $error_val = defined('TEXT_BANK_ERROR_'.$bt_status) ? constant('TEXT_BANK_ERROR_'.$bt_status) : '';
                  ?>
                  <tr>
                    <td class="main"><?php echo TEXT_BANK_ERRORCODE; ?></td>
                    <td class="main"><?php echo $error_val; ?></td>
                  </tr>
                  <tr>
                    <td class="main"><?php echo TEXT_BANK_PRZ; ?></td>
                    <td class="main"><?php echo $banktransfer['banktransfer_prz']; ?></td>
                  </tr>
                  <?php }
                }
                if ($banktransfer['banktransfer_fax']) {
                ?>
                  <tr>
                    <td class="main"><?php echo TEXT_BANK_FAX; ?></td>
                    <td class="main"><?php echo $banktransfer['banktransfer_fax']; ?></td>
                  </tr>
                <?php
                }

                ## Moneybookers
                if ($order->info['payment_method'] == 'amoneybookers') {
                  if (file_exists(DIR_FS_CATALOG.DIR_WS_MODULES.'payment/'.$order->info['payment_method'].'.php')) {
                    include(DIR_FS_CATALOG.DIR_WS_MODULES.'payment/'.$order->info['payment_method'].'.php');
                    include(DIR_FS_CATALOG.'lang/'.$order->info['language'].'/modules/payment/'.$order->info['payment_method'].'.php');
                    $class = $order->info['payment_method'];
                    $payment = new $class();
                    $payment->admin_order($oID);
                  }
                }

                ## Payone
                include (DIR_FS_EXTERNAL.'payone/modules/orders_payone.php'); 
                
                ?>
              </table>
            </td>
          </tr>
        </table>
        <!-- EOC PAYMENT BLOCK -->

        <!-- BOC ORDER BLOCK -->
        <div class="heading"><?php echo TEXT_ORDER; ?></div>
        <table cellspacing="0" cellpadding="2" class="table">
          <tr class="dataTableHeadingRow">
            <td class="dataTableHeadingContent" colspan="2"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></td>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRICE_EXCLUDING_TAX; ?></td>
            <?php if ($order->products[0]['allow_tax'] == 1) { ?>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TAX; ?></td>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRICE_INCLUDING_TAX; ?></td>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TOTAL_INCLUDING_TAX; ?></td>
            <?php  } else { ?>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TOTAL_EXCLUDING_TAX; ?></td>
            <?php } ?>
          </tr>
          <?php
          for ($i = 0, $n = sizeof($order->products); $i < $n; $i ++) {
            echo '          <tr class="dataTableRow">'.PHP_EOL;
            echo '            <td class="dataTableContent" valign="top" align="right">'.$order->products[$i]['qty'].'&nbsp;x&nbsp;</td>'.PHP_EOL;
            echo '            <td class="dataTableContent" valign="top">'.PHP_EOL;
            echo '              <a href="'.HTTP_CATALOG_SERVER.DIR_WS_CATALOG.'product_info.php?products_id='.$order->products[$i]['id'].'" target="_blank">'.$order->products[$i]['name'].'</a>';
            if (isset($order->products[$i]['attributes']) && sizeof($order->products[$i]['attributes']) > 0) {
              for ($j = 0, $k = sizeof($order->products[$i]['attributes']); $j < $k; $j ++) {
                echo '<br /><nobr><i>&nbsp; - '.$order->products[$i]['attributes'][$j]['option'].': '.$order->products[$i]['attributes'][$j]['value'].'</i></nobr> ';
              }
            }
            echo '            </td>'.PHP_EOL;
            echo '            <td class="dataTableContent" valign="top">';
            echo ($order->products[$i]['model'] != '') ? $order->products[$i]['model'] : '<br />';
            // attribute models
            if (isset($order->products[$i]['attributes']) && sizeof($order->products[$i]['attributes']) > 0) {
              for ($j = 0, $k = sizeof($order->products[$i]['attributes']); $j < $k; $j ++) {
                $model = xtc_get_attributes_model($order->products[$i]['id'], $order->products[$i]['attributes'][$j]['value'],$order->products[$i]['attributes'][$j]['option'],$lang); //web28 Fix attribute model  language problem
                echo !empty($model) ? $model.'<br />' : '<br />';
              }
            }
            echo '&nbsp;</td>'.PHP_EOL;
            echo '            <td class="dataTableContent" align="right" valign="top">'.format_price($order->products[$i]['price'], 1, $order->info['currency'], $order->products[$i]['allow_tax'], $order->products[$i]['tax']).'</td>'.PHP_EOL;
            if ($order->products[$i]['allow_tax'] == 1) {
              echo '            <td class="dataTableContent" align="right" valign="top">'.xtc_display_tax_value($order->products[$i]['tax']).'%</td>'.PHP_EOL;
              echo '            <td class="dataTableContent" align="right" valign="top"><b>'.format_price($order->products[$i]['price'], 1, $order->info['currency'], 0, 0).'</b></td>'.PHP_EOL;
            }
              echo '            <td class="dataTableContent" align="right" valign="top"><b>'.format_price(($order->products[$i]['final_price']), 1, $order->info['currency'], 0, 0).'</b></td>'.PHP_EOL;
              echo '          </tr>'.PHP_EOL;
          }
          ?>
          <tr>
            <td align="right" colspan="7">
               <table border="0" cellspacing="0" cellpadding="2">
                <?php
                  for ($i = 0, $n = sizeof($order->totals); $i < $n; $i ++) {
                    echo '                <tr>'.PHP_EOL.'                  <td align="right" class="smallText">'.$order->totals[$i]['title'].'</td>'.PHP_EOL;
                    echo '                  <td align="right" class="smallText">'.$order->totals[$i]['text'].'</td>'.PHP_EOL;
                    echo '                </tr>'.PHP_EOL;
                  }
                ?>
              </table>
            </td>
          </tr>
        </table>
        <!-- EOC ORDER BLOCK -->

        <!-- BOC DOWNLOAD BLOCK -->
        <?php
        $downloads_query = xtc_db_query("SELECT op.products_name, 
                                                opd.orders_products_download_id, 
                                                opd.orders_products_filename, 
                                                opd.download_count,
                                                opd.orders_products_id,
                                                if(opd.download_maxdays = 0, current_date, date(o.date_purchased)) + interval opd.download_maxdays + 1 day - interval 1 second download_expiry 
                                           FROM ".TABLE_ORDERS." o
                                           JOIN ".TABLE_ORDERS_PRODUCTS." op 
                                                on op.orders_id = o.orders_id
                                           JOIN ".TABLE_ORDERS_PRODUCTS_DOWNLOAD." opd 
                                                on opd.orders_products_id = op.orders_products_id
                                          WHERE o.orders_id = '".$order->info['orders_id']."'
                                            AND opd.orders_products_filename != ''
                                            AND o.customers_id = '".$order->customer['id']."'");

        if (xtc_db_num_rows($downloads_query) > 0) {
          ?>
          <div class="heading"><?php echo TEXT_DOWNLOADS; ?></div>
          <table cellspacing="0" cellpadding="2" class="table">
            <tr class="dataTableHeadingRow">
              <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
              <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_FILENAME; ?></td>
              <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_EXPIRES; ?></td>
              <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_DOWNLOADS; ?></td>
              <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_DAYS; ?></td>
              <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ACTION; ?></td>
            </tr>
            <?php
            while ($downloads = xtc_db_fetch_array($downloads_query)) {
              echo '<tr class="dataTableRow">' . xtc_draw_form('downloads', FILENAME_ORDERS, xtc_get_all_get_params(array('action')) . 'action=downloads').xtc_draw_hidden_field('orders_products_download_id', $downloads['orders_products_download_id']).xtc_draw_hidden_field('date_purchased', strtotime($order->info['date_purchased'])).PHP_EOL;
              echo '  <td class="dataTableContent">'.$downloads['products_name'].'</td>'.PHP_EOL;
              echo '  <td class="dataTableContent">'.$downloads['orders_products_filename'].'</td>'.PHP_EOL;
              echo '  <td class="dataTableContent"><span class="'.(($downloads['download_expiry'] < $order->info['date_purchased']) ? 'col-red' : 'col-green').'">'.xtc_datetime_short($downloads['download_expiry']).'</span></td>'.PHP_EOL;
              echo '  <td class="dataTableContent">'.xtc_draw_input_field('download_count', $downloads['download_count'], 'style="width:30px;"').'</td>'.PHP_EOL;
              echo '  <td class="dataTableContent">'.xtc_draw_input_field('download_maxdays', '', 'style="width:30px;"').'</td>'.PHP_EOL;
              echo '  <td class="dataTableContent"><input type="submit" class="button" onclick="this.blur();" value="'.BUTTON_UPDATE.'"/></td>'.PHP_EOL;
              echo '</form></tr>';
            }
            ?>
          </table>
          <?php
        }
        ?>
        <!-- EOC DOWNLOAD BLOCK -->

        <?php
          /* magnalister v1.0.0 */
          if (function_exists('magnaExecute')) echo magnaExecute('magnaRenderOrderDetails', array('oID' => $oID), array('order_details.php'));
          /* END magnalister */
        ?>

        <!-- BOC ORDER TRACK & TRACE BLOCK -->
        <div class="heading"><?php echo TABLE_HEADING_TRACK_TRACE; ?></div>
        <?php echo xtc_draw_form('carriers', FILENAME_ORDERS, xtc_get_all_get_params(array('action')) . 'action=inserttracking'); ?>
        <table cellspacing="0" cellpadding="5" class="table borderall">
          <tr>
            <td class="smallText" align="center" style="width:100px;"><strong><?php echo TABLE_HEADING_CARRIER; ?></strong></td>
            <td class="smallText" align="center"><strong><?php echo TABLE_HEADING_PARCEL_LINK; ?></strong></td>
            <td class="smallText" align="center" style="width:150px;"><strong><?php echo TABLE_HEADING_ACTION; ?></strong></td>
          </tr>
          <?php
            $service_array = array(
              array('id' => 'standard', 'text' => 'Standard'),
              array('id' => 'one_day', 'text' => 'Express'),
              array('id' => 'one_day_early', 'text' => 'Express 10:00'),
              array('id' => 'returns', 'text' => 'Retour'),
            );
            $parcel_array = array();
            $dim_array = explode(';', preg_replace("'[\r\n\s]+'", '', MODULE_SHIPCLOUD_PARCEL));
            for ($p=0, $pn=count($dim_array); $p<$pn; $p++) {
              if ($dim_array[$p] != '') {
                $parcel_array[] = array('id' => $dim_array[$p], 'text' => str_replace(',', 'cm x ', $dim_array[$p] .'cm'));
              }
            }
            $tracking_array = get_tracking_link($oID, $lang_code);
            if (count($tracking_array) > 0) {
              foreach($tracking_array as $tracking) {
                echo '          <tr>'.PHP_EOL;
                echo '            <td class="smallText" align="center">'.$tracking['carrier_name'].'</td>'.PHP_EOL;
                echo '            <td class="smallText" align="left"><a href="'.$tracking['tracking_link'].'" target="_blank">'.$tracking['parcel_id'].'</a></td>'.PHP_EOL;
                echo '            <td class="smallText" align="center">
                                    <a href="'.xtc_href_link(FILENAME_ORDERS, 'oID='.$oID.'&tID='.$tracking['tracking_id'].'&action=deletetracking').'">'.xtc_image(DIR_WS_ICONS.'cross.gif', ICON_DELETE).
                                    ((isset($tracking['sc_label_url']) && $tracking['sc_label_url'] != '') ? '<a style="margin-left:10px;" target="_blank" href="'.$tracking['sc_label_url'].'">'.xtc_image(DIR_WS_ICONS.'icon_pdf.gif', DOWNLOAD_LABEL).'</a>' : '').'
                                  </td>'.PHP_EOL;
                echo '          <tr>'.PHP_EOL;
              }
            }
          ?>
          <tr>
            <?php
              if (defined('MODULE_SHIPCLOUD_STATUS') && MODULE_SHIPCLOUD_STATUS == 'True') {
                echo '<td class="smallText" align="center" colspan="2">';
                echo xtc_draw_pull_down_menu('carrier_id', $carriers, $carriers[0]).'&nbsp;';
                echo xtc_draw_pull_down_menu('service', $service_array, $service_array[0]).'&nbsp;';
                echo xtc_draw_pull_down_menu('parcel', $parcel_array, $parcel_array[0]).'&nbsp;';
                echo xtc_draw_input_field('description', '' , 'style="width:350px;vertical-align:top;" placeholder="'.TEXT_CARRIER_PLACEHOLDER.'"');
                echo '</td>';
              } else {
                echo '<td class="smallText" align="center">'.xtc_draw_pull_down_menu('carrier_id', $carriers, $carriers[0]).'</td>';
                echo '<td class="smallText" align="center">'.xtc_draw_input_field('parcel_id', '' ,'style="width: 99%"').'</td>';
              }
            ?>
            <td class="smallText" align="center"><input class="button" type="submit" value="<?php echo ((defined('MODULE_SHIPCLOUD_STATUS') && MODULE_SHIPCLOUD_STATUS == 'True') ? CREATE_LABEL : BUTTON_INSERT); ?>"></td>
          </tr>
        </table>
        </form>
        <!-- EOC ORDER TRACK & TRACE BLOCK -->

        <!-- BOC ORDER HISTORY BLOCK -->
        <div class="heading"><?php echo TEXT_ORDER_HISTORY; ?></div>
        <table cellspacing="0" cellpadding="5" class="table borderall">
          <tr>
            <td class="smallText" align="center"><b><?php echo TABLE_HEADING_DATE_ADDED; ?></b></td>
            <td class="smallText" align="center"><b><?php echo TABLE_HEADING_CUSTOMER_NOTIFIED; ?></b></td>
            <td class="smallText" align="center"><b><?php echo TABLE_HEADING_STATUS; ?></b></td>
            <td class="smallText" align="center"><b><?php echo TABLE_HEADING_COMMENTS; ?></b></td>
            <td class="smallText" align="center"><b><?php echo TABLE_HEADING_COMMENTS_SENT; ?></b></td>
          </tr>
          <?php
            $orders_history_query = xtc_db_query("-- /admin/orders.php
                                                  SELECT orders_status_id,
                                                         date_added,
                                                         customer_notified,
                                                         comments,
                                                         comments_sent
                                                    FROM ".TABLE_ORDERS_STATUS_HISTORY."
                                                   WHERE orders_id = ".$oID."
                                                ORDER BY date_added");
            $count = xtc_db_num_rows($orders_history_query);
            if ($count) {
              while ($orders_history = xtc_db_fetch_array($orders_history_query)) {
                $count--;
                $class = ($count == 0) ? ' last_row' : '';
                echo '                <tr>'.PHP_EOL;
                echo '                  <td class="smallText'.$class.'" align="center">'.xtc_datetime_short($orders_history['date_added']).'</td>'.PHP_EOL;
                echo '                  <td class="smallText'.$class.'" align="center">';
                if ($orders_history['customer_notified'] == '1') {
                  echo xtc_image(DIR_WS_ICONS.'tick.gif').'</td>'.PHP_EOL;
                } else {
                  echo xtc_image(DIR_WS_ICONS.'cross.gif').'</td>'.PHP_EOL;
                }
                echo '            <td class="smallText'. $class.'">';
                if($orders_history['orders_status_id']!='0') {
                  echo $orders_status_array[$orders_history['orders_status_id']];
                }else{
                  echo '<span class="col-red">'.TEXT_VALIDATING.'</span>';
                }
                echo '</td>'.PHP_EOL;
                echo '                  <td class="smallText'.$class.'">'.nl2br(xtc_db_output($orders_history['comments'])).'&nbsp;</td>'. PHP_EOL;                 
                echo '                  <td class="smallText'.$class.'" align="center">';
                if ($orders_history['comments_sent'] == '1') {
                  echo xtc_image(DIR_WS_ICONS.'tick.gif').'</td>'.PHP_EOL;
                } else {
                  echo xtc_image(DIR_WS_ICONS.'cross.gif').'</td>'.PHP_EOL;
                }
                echo '</tr>'.PHP_EOL;
               }
            } else {
              echo '                <tr>'.PHP_EOL.'            <td class="smallText" colspan="5">'.TEXT_NO_ORDER_HISTORY.'</td>'.PHP_EOL.'                </tr>'.PHP_EOL;
            }
          ?>
          </tr>
        </table>
        <!-- EOC ORDER HISTORY BLOCK -->

        <!-- BOC ORDER STATUS BLOCK -->
        <div class="heading"><?php echo TEXT_ORDER_STATUS; ?></div>
        <?php echo xtc_draw_form('status', FILENAME_ORDERS, xtc_get_all_get_params(array('action')) . 'action=update_order'); ?>
        <table cellspacing="0" cellpadding="2" class="table">
          <tr>
            <td class="main"><b><?php echo TABLE_HEADING_COMMENTS; ?></b></td>
          </tr>
          <tr>
            <td class="main"><?php echo xtc_draw_textarea_field('comments', 'soft', '60', '8', ''); ?></td>
          </tr>
          <tr>
            <td><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><b><?php echo ENTRY_STATUS; ?></b> <?php echo xtc_draw_pull_down_menu('status', $orders_statuses, $order->info['orders_status']); ?></td>
          </tr>
          <?php
            /* magnalister v2.0.0 */
            if (function_exists('magnaExecute')) magnaExecute('magnaRenderOrderStatusSync', array(), array('order_details.php'));
            /* END magnalister */
          ?>
          <tr>
            <td>
              <table border="0" cellspacing="0" cellpadding="2">
                <tr>
                  <td class="main"><b><?php echo ENTRY_NOTIFY_CUSTOMER; ?></b></td>
                  <td class="main" style="width:40px;"><?php echo xtc_draw_checkbox_field('notify', '', true); ?></td>
                  <td class="main"><b><?php echo ENTRY_NOTIFY_COMMENTS; ?></b></td>
                  <td class="main" style="width:40px;"><?php echo xtc_draw_checkbox_field('notify_comments', '', true); ?></td>
                </tr>
              </table>
              <div style="float:right; margin: 10px 0 0;"><input type="submit" class="button" name="update" value="<?php echo BUTTON_UPDATE; ?>"></div>
              <?php
              //BOF EMAIL PREVIEW
              include('includes/modules/email_preview/email_preview_btn.php');
              //EOF EMAIL PREVIEW
              ?>
            </td>
          </tr>
        </table>
        </form>
        <!-- EOC ORDER STATUS BLOCK -->

        <!-- BOC BUTTONS BLOCK -->
        <table cellspacing="0" cellpadding="2" style="margin-bottom:10px;">
          <tr>
            <td align="right">
              <a class="button" href="<?php echo xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array ('oID', 'action')).'oID='.$oID.'&action=send&sta=0&stc=1&site=1'); ?>"><?php echo BUTTON_ORDER_CONFIRMATION; ?></a>
              <?php
                if (ACTIVATE_GIFT_SYSTEM == 'true') {
                echo '<a class="button" href="'.xtc_href_link(FILENAME_GV_MAIL, xtc_get_all_get_params(array ('cID', 'action')).'cID='.$order->customer['ID']).'">'.BUTTON_SEND_COUPON.'</a>';
              }
              // invoice number and date
              echo add_btn_ibillnr($order,$oID);
              ?>
              <a class="button" href="Javascript:void()" onclick="window.open('<?php echo xtc_href_link(FILENAME_PRINT_ORDER,'oID='.$oID); ?>', 'popup', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no, width=800, height=750')"><?php echo BUTTON_INVOICE; ?></a>
              <a class="button" href="Javascript:void()" onclick="window.open('<?php echo xtc_href_link(FILENAME_PRINT_PACKINGSLIP,'oID='.$oID); ?>', 'popup', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no, width=800, height=750')"><?php echo BUTTON_PACKINGSLIP; ?></a>
              <a class="button" href="<?php echo xtc_href_link(FILENAME_ORDERS, 'page='.$_GET['page'].'&oID='.$oID); ?>"><?php echo BUTTON_BACK;?></a>
            </td>
          </tr>
          <?php
            /* easyBill */
            include (DIR_FS_EXTERNAL.'easybill/admin/easybill.button.php');
          ?>
        </table>
        <!-- EOC BUTTONS BLOCK -->
      </div>
