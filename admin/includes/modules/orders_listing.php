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
        <div class="pageHeading flt-l"><?php echo HEADING_TITLE; ?>
          <div class="main pdg2"><?php echo TABLE_HEADING_CUSTOMERS ?></div>
        </div>
        <div class="flt-r">
          <div class="pageHeading">
            <?php echo xtc_draw_form('orders', FILENAME_ORDERS, '', 'get'); ?>
            <?php echo HEADING_TITLE_SEARCH . ' ' . xtc_draw_input_field('oID', '', 'size="12"') . xtc_draw_hidden_field('action', 'edit').xtc_draw_hidden_field(xtc_session_name(), xtc_session_id()); ?>
            </form>
          </div>
          <div class="main">
            <?php echo xtc_draw_form('status', FILENAME_ORDERS, '', 'get'); ?>
            <?php echo HEADING_TITLE_STATUS . ' ' . xtc_draw_pull_down_menu('status', array_merge(array(array('id' => '', 'text' => TEXT_ALL_ORDERS)),array(array('id' => '0', 'text' => TEXT_VALIDATING)), $orders_statuses),(isset($_GET['status']) && xtc_not_null($_GET['status']) ? (int)$_GET['status'] : ''),'onchange="this.form.submit();"').xtc_draw_hidden_field(xtc_session_name(), xtc_session_id()); ?>
            </form>        
          </div>
        </div>      
     
        <table class="tableCenter">      
          <tr>
            <td class="boxCenterLeft">
              <!-- BOC ORDERS LISTING -->
              <table class="tableBoxCenter collapse">
                <tr class="dataTableHeadingRow">
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERS; ?></td>
                  <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ORDERS_ID; ?></td>
                  <td class="dataTableHeadingContent" align="right" style="width:120px"><?php echo TEXT_SHIPPING_TO; ?></td>
                  <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ORDER_TOTAL; ?></td>
                  <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_DATE_PURCHASED; ?></td>
                  <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_STATUS; ?></td>
                  <?php if (AFTERBUY_ACTIVATED=='true') { ?>
                  <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_AFTERBUY; ?></td>
                  <?php } ?>
                  <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                </tr>
                <?php
                $sort = " ORDER BY o.orders_id DESC";
                if (isset($_GET['cID'])) {
                  $cID = (int) $_GET['cID'];
                  $orders_query_raw = "-- /admin/orders.php
                                       SELECT ".$order_select_fields.",
                                              s.orders_status_name
                                         FROM ".TABLE_ORDERS." o
                                    LEFT JOIN ".TABLE_ORDERS_STATUS." s
                                              ON (((o.orders_status = s.orders_status_id)
                                                   OR (o.orders_status = '0' 
                                                       AND s.orders_status_id = '1'))
                                                   AND s.language_id = '".(int)$_SESSION['languages_id']."')
                                        WHERE o.customers_id = '".xtc_db_input($cID)."'
                                               ".$sort;

                } elseif (isset($_GET['status']) && $_GET['status']=='0') {
                    $orders_query_raw = "-- /admin/orders.php
                                         SELECT ".$order_select_fields."
                                           FROM ".TABLE_ORDERS." o
                                           WHERE o.orders_status = '0'
                                                 ".$sort;

                } elseif (isset($_GET['status']) && xtc_not_null($_GET['status'])) { //web28 - 2012-04-14  - FIX xtc_not_null($_GET['status'])
                    $status = xtc_db_prepare_input($_GET['status']);
                    $orders_query_raw = "-- /admin/orders.php
                                         SELECT ".$order_select_fields.",
                                                s.orders_status_name
                                           FROM ".TABLE_ORDERS." o
                                      LEFT JOIN ".TABLE_ORDERS_STATUS." s
                                                ON (o.orders_status = s.orders_status_id
                                                    AND s.orders_status_id = '".xtc_db_input($status)."')
                                          WHERE s.language_id = '".(int)$_SESSION['languages_id']."'
                                                ".$sort;

                } elseif ($action == 'search' && $oID) {
                     // ADMIN SEARCH BAR $orders_query_raw moved it to the top
                } else {
                      $orders_query_raw = "-- /admin/orders.php
                                           SELECT ".$order_select_fields.",
                                                  s.orders_status_name
                                             FROM ".TABLE_ORDERS." o
                                        LEFT JOIN ".TABLE_ORDERS_STATUS." s
                                               ON (((o.orders_status = s.orders_status_id)
                                                    OR (o.orders_status = '0' 
                                                        AND s.orders_status_id = '1'))
                                                    AND s.language_id = '".(int)$_SESSION['languages_id']."'
                                                   )
                                                  ".$sort;
                }
                $orders_split = new splitPageResults($_GET['page'], MAX_DISPLAY_ORDER_RESULTS, $orders_query_raw, $orders_query_numrows);
                $orders_query = xtc_db_query($orders_query_raw);
                while ($orders = xtc_db_fetch_array($orders_query)) {
                  if ((!xtc_not_null($oID) || (isset($oID) && $oID == $orders['orders_id'])) && !isset($oInfo)) { //web28 - 2012-04-14 - FIX !xtc_not_null($oID)
                    $oInfo = new objectInfo($orders);
                  }
                  if (isset($oInfo) && is_object($oInfo) && ($orders['orders_id'] == $oInfo->orders_id)) {
                    $tr_attributes = 'class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\''.xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array ('oID', 'action')).'oID='.$oInfo->orders_id.'&action=edit').'\'"';
                  } else {
                    $tr_attributes = 'class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\''.xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array ('oID')).'oID='.$orders['orders_id']).'\'"';
                  }
                  $orders_link = xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('oID', 'action')) . 'oID=' . $orders['orders_id'] . '&action=edit');
                  $orders_image_preview = xtc_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW);
                  $orders['customers_name'] = (isset($orders['customers_company']) && $orders['customers_company'] != '') ? $orders['customers_company'] : $orders['customers_name'];
                  if (isset($oInfo) && is_object($oInfo) && ($orders['orders_id'] == $oInfo->orders_id) ) {
                    $orders_action_image = xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT);
                  } else {
                    $orders_action_image = '<a href="' . xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('oID')) . 'oID=' . $orders['orders_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
                  }
                  ?>
                <tr <?echo $tr_attributes;?>>
                  <td class="dataTableContent"><?php echo '<a href="' . $orders_link . '">' . $orders_image_preview . '</a>&nbsp;' . $orders['customers_name']; ?></td>
                  <td class="dataTableContent" align="right"><?php echo $orders['orders_id']; ?></td>
                  <td class="dataTableContent" align="right"><?php echo $orders['delivery_country']; ?>&nbsp;</td>
                  <td class="dataTableContent" align="right"><?php echo format_price(get_order_total($orders['orders_id']), 1, $orders['currency'], 0, 0); ?></td>
                  <td class="dataTableContent" align="center"><?php echo xtc_datetime_short($orders['date_purchased']); ?></td>
                  <td class="dataTableContent" align="right"><?php if($orders['orders_status']!='0') { echo $orders['orders_status_name']; }else{ echo '<font color="#FF0000">'.TEXT_VALIDATING.'</font>';}?></td>
                  <?php if (AFTERBUY_ACTIVATED=='true') { ?>
                  <td class="dataTableContent" align="right"><?php  echo ($orders['afterbuy_success'] == 1) ? $orders['afterbuy_id'] : 'TRANSMISSION_ERROR'; ?></td>
                  <?php } ?>
                  <td class="dataTableContent" align="right"><?php echo $orders_action_image; ?>&nbsp;</td>
                </tr>
                <?php
                }
                ?>                
              </table>
              
              <div class="smallText pdg2 flt-l"><?php echo $orders_split->display_count($orders_query_numrows, MAX_DISPLAY_ORDER_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_ORDERS); ?></div>
              <div class="smallText pdg2 flt-r"><?php echo $orders_split->display_links($orders_query_numrows, MAX_DISPLAY_ORDER_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], xtc_get_all_get_params(array('page', 'oID', 'action'))); ?></div>
           
              <!-- EOC ORDERS LISTING -->
            </td>
              <?php
                $heading = array ();
                $contents = array ();
                switch ($action) {
                  case 'delete' :
                    $heading[] = array ('text' => '<b>'.TEXT_INFO_HEADING_DELETE_ORDER.'</b>');
                    $contents = array ('form' => xtc_draw_form('orders', FILENAME_ORDERS, xtc_get_all_get_params(array ('oID', 'action')).'oID='.$oInfo->orders_id.'&action=deleteconfirm'));
                    $contents[] = array ('text' => TEXT_INFO_DELETE_INTRO.'<br /><br /><b>'.$oInfo->customers_name.'</b><br /><b>'.TABLE_HEADING_ORDERS_ID.'</b>: '.$oInfo->orders_id);
                    $contents[] = array ('text' => '<br />'.xtc_draw_checkbox_field('restock').' '.TEXT_INFO_RESTOCK_PRODUCT_QUANTITY);
                    //$contents[] = array ('text' => '<br />'.xtc_draw_checkbox_field('reverse_order', '', true).' '.TEXT_INFO_REVERSE_ORDER.xtc_cfg_pull_down_order_statuses('4')); // DokuMan - by default status '4' is reversal of an order
                    // Paypal Express Modul
                    if(defined('TABLE_PAYPAL')) {
                      $db_installed = false;
                      $tables = mysql_query('SHOW TABLES FROM `' . DB_DATABASE . '`');
                      while ($row = mysql_fetch_row($tables)) {
                        if ($row[0] == TABLE_PAYPAL) $db_installed = true;
                      }
                      if ($db_installed) {
                        $query = "-- /admin/orders.php
                                  SELECT *
                                    FROM " . TABLE_PAYPAL . "
                                   WHERE xtc_order_id = '" . $oInfo->orders_id . "'";
                        $query = xtc_db_query($query);
                        if(xtc_db_num_rows($query)>0) {
                          $contents[] = array ('text' => '<br />'.xtc_draw_checkbox_field('paypaldelete').' '.TEXT_INFO_PAYPAL_DELETE);
                        }
                      }
                    }
                    $contents[] = array ('align' => 'center', 'text' => '<br /><input type="submit" class="button" value="'. BUTTON_DELETE .'"><a class="button" href="'.xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array ('oID', 'action')).'oID='.$oInfo->orders_id).'">' . BUTTON_CANCEL . '</a>');
                    break;
                  default :
                    if (isset($oInfo) && is_object($oInfo)) {
                      $heading[] = array ('text' => '<b>['.$oInfo->orders_id.']&nbsp;&nbsp;'.xtc_datetime_short($oInfo->date_purchased).'</b>');
                      $contents[] = array ('align' => 'center', 'text' => '<a class="button" href="'.xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array ('oID', 'action')).'oID='.$oInfo->orders_id.'&action=edit').'">'.BUTTON_EDIT.'</a> <a class="button" href="'.xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array ('oID', 'action')).'oID='.$oInfo->orders_id.'&action=delete').'">'.BUTTON_DELETE.'</a>');
                      //BOF - Dokuman - 2012-06-19 - BILLSAFE payment module
                      if ($oInfo->payment_method === 'billsafe_2') {
                        $contents[] = array ('align' => 'center', 'text' => '<a class="button" href="billsafe_orders_2.php?oID='.$oInfo->orders_id.'">BillSAFE Details</a>');
                      } elseif ($oInfo->payment_method === 'billsafe_2hp') {
                        $contents[] = array ('align' => 'center', 'text' => '<a class="button" href="billsafe_orders_2hp.php?oID='.$oInfo->orders_id.'">BillSAFE Details</a>');
                      }
                      //EOF - Dokuman - 2012-06-19 - BILLSAFE payment module
                      if (AFTERBUY_ACTIVATED == 'true') {
                        $contents[] = array ('align' => 'center', 'text' => '<a class="button" href="'.xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array ('oID', 'action')).'oID='.$oInfo->orders_id.'&action=afterbuy_send').'">'.BUTTON_AFTERBUY_SEND.'</a>');
                      }
                      $contents[] = array ('text' => '<br />'.TEXT_DATE_ORDER_CREATED.' '.xtc_date_short($oInfo->date_purchased));
                        if (xtc_not_null($oInfo->last_modified)) {
                        $contents[] = array ('text' => TEXT_DATE_ORDER_LAST_MODIFIED.' '.xtc_date_short($oInfo->last_modified));
                      }
                      $contents[] = array ('text' => '<br />'.TEXT_INFO_PAYMENT_METHOD.' '.get_payment_name($oInfo->payment_method).' ('.$oInfo->payment_method.')');
                      $order = new order($oInfo->orders_id);
                      $contents[] = array ('text' => '<br /><br />'.sizeof($order->products).'&nbsp;'.TEXT_PRODUCTS);
                      for ($i = 0; $i < sizeof($order->products); $i ++) {
                        $contents[] = array ('text' => $order->products[$i]['qty'].'&nbsp;x&nbsp;'.$order->products[$i]['name']);
                        if (isset($order->products[$i]['attributes']) && sizeof($order->products[$i]['attributes']) > 0) {
                          for ($j = 0; $j < sizeof($order->products[$i]['attributes']); $j ++) {
                            $contents[] = array ('text' => '<small>&nbsp;<i> - '.$order->products[$i]['attributes'][$j]['option'].': '.$order->products[$i]['attributes'][$j]['value'].'</i></small></nobr>');
                          }
                        }
                      }
                      if ($order->info['comments']<>'') {
                        $contents[] = array ('text' => '<br><strong>'.TABLE_HEADING_COMMENTS.':</strong><br>'.$order->info['comments']);
                      }
                    }
                    break;
                }
                // display right box
                if ((xtc_not_null($heading)) && (xtc_not_null($contents))) {
                  echo '            <td class="boxRight">'."\n";
                  $box = new box;
                  echo $box->infoBox($heading, $contents);
                  echo '          </td>'."\n";
                }
              ?>              
          </tr>
        </table>