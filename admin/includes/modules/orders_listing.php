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
  
  //display per page
  $cfg_max_display_results_key = 'MAX_DISPLAY_ORDER_RESULTS';
  $page_max_display_results = xtc_cfg_save_max_display_results($cfg_max_display_results_key);
  
  $customers_statuses_array = xtc_get_customers_statuses();
  
  $payment_array = array();
  $dbQuery = xtc_db_query("SELECT payment_class FROM ".TABLE_ORDERS." GROUP BY payment_class ORDER BY payment_class");
  while ($payments = xtc_db_fetch_array($dbQuery)) {
    $payment_text = $payments['payment_class'];
    if (is_file(DIR_FS_LANGUAGES . $_SESSION['language'] . '/modules/payment/' . $payments['payment_class'].'.php')) {
      include(DIR_FS_LANGUAGES . $_SESSION['language'] . '/modules/payment/' . $payments['payment_class'].'.php');
      $payment_text = constant('MODULE_PAYMENT_'.strtoupper($payments['payment_class']).'_TEXT_TITLE');
    } 
    $payment_array[] = array('id' => $payments['payment_class'], 'text' => $payment_text);
  }
  
  if (!function_exists('xtc_draw_hidden_filter_field')) {
    function xtc_draw_hidden_filter_field($name, $value = '') {
      if ($value != '') {
        return xtc_draw_hidden_field($name, $value);
      }
    }
  }
?>
 
        <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_orders.png'); ?></div>
        <div class="pageHeading flt-l"><?php echo HEADING_TITLE; ?>
          <div class="main pdg2"><?php echo TABLE_HEADING_CUSTOMERS ?></div>
        </div>

        <div class="main flt-l pdg2 mrg5" style="margin-left:20px;">
          <?php echo xtc_draw_form('orders', FILENAME_ORDERS, '', 'get'); ?>
          <?php echo ASB_QUICK_SEARCH_CUSTOMER . ' ' . xtc_draw_input_field('customer', '', 'size="12"') . xtc_draw_hidden_field('action', 'search'); ?>
          </form>
        </div>
        <div class="main flt-l pdg2 mrg5" style="margin-left:20px;">
          <?php echo xtc_draw_form('status', FILENAME_ORDERS, '', 'get'); ?>
          <?php
            $orders_statuses_array = array();
            if (defined('ORDER_STATUSES_DISPLAY_DEFAULT') && ORDER_STATUSES_DISPLAY_DEFAULT != '') {
              $orders_statuses_array[] = array('id' => '-1', 'text' => TEXT_ALL_ORDERS);
              $orders_statuses_array[] = array('id' => '', 'text' => TEXT_ORDERS_STATUS_FILTER);
            } else {
              $orders_statuses_array[] = array('id' => '', 'text' => TEXT_ALL_ORDERS);
            }
            $orders_statuses_array[] = array('id' => '0', 'text' => TEXT_VALIDATING);
            echo HEADING_TITLE_STATUS . ' ' . xtc_draw_pull_down_menu('status', array_merge($orders_statuses_array, $orders_statuses),(isset($_GET['status']) && xtc_not_null($_GET['status']) ? (int)$_GET['status'] : ''),'onchange="this.form.submit();"'); 
          ?>
          <?php echo xtc_draw_hidden_filter_field('cgroup', $_GET['cgroup'])?>
          <?php echo xtc_draw_hidden_filter_field('payment', $_GET['payment'])?>
          </form>        
        </div>
        <div class="main flt-l pdg2 mrg5" style="margin-left:20px;">
          <?php echo xtc_draw_form('payment', FILENAME_ORDERS, '', 'get'); ?>
          <?php echo TEXT_INFO_PAYMENT_METHOD . ' ' . xtc_draw_pull_down_menu('payment',xtc_array_merge(array (array ('id' => '', 'text' => TXT_ALL)), $payment_array), isset($_GET['payment']) ? $_GET['payment'] : '', 'onChange="this.form.submit();"'); ?>
          <?php echo xtc_draw_hidden_filter_field('status', $_GET['status'])?>
          <?php echo xtc_draw_hidden_filter_field('cgroup', $_GET['cgroup'])?>
          </form>
        </div>
        <div class="main flt-l pdg2 mrg5" style="margin-left:20px;">
          <?php echo xtc_draw_form('cgroup', FILENAME_ORDERS, '', 'get'); ?>
          <?php echo ENTRY_CUSTOMERS_STATUS . ' ' . xtc_draw_pull_down_menu('cgroup',xtc_array_merge(array (array ('id' => '', 'text' => TXT_ALL)), $customers_statuses_array), isset($_GET['cgroup']) ? $_GET['cgroup'] : '', 'onChange="this.form.submit();"'); ?>
          <?php echo xtc_draw_hidden_filter_field('status', $_GET['status'])?>
          <?php echo xtc_draw_hidden_filter_field('payment', $_GET['payment'])?>
          </form>
        </div>
        <div class="clear"></div>      
     
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
                  <td class="dataTableHeadingContent" align="center"><?php echo str_replace(':','',TEXT_INFO_PAYMENT_METHOD); ?></td>
                  <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_STATUS; ?></td>
                  <?php if (AFTERBUY_ACTIVATED=='true') { ?>
                  <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_AFTERBUY; ?></td>
                  <?php } ?>
                  <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                </tr>
                <?php
                $sort = " ORDER BY o.date_purchased DESC";
                $filter = isset($_GET['cgroup']) && $_GET['cgroup'] != '' ? " AND o.customers_status = '" . (int)$_GET['cgroup'] ."'": '';
                $filter .=  isset($_GET['payment']) && $_GET['payment'] != '' ? " AND o.payment_class = '" . $_GET['payment'] ."'": '';               
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
                                               ".$filter.$sort;

                } elseif (isset($_GET['status']) && $_GET['status']=='0') {
                    $orders_query_raw = "-- /admin/orders.php
                                         SELECT ".$order_select_fields."
                                           FROM ".TABLE_ORDERS." o
                                           WHERE o.orders_status = '0'
                                                 ".$filter.$sort;

                } elseif (isset($_GET['status']) && xtc_not_null($_GET['status']) && $_GET['status'] != '-1') {
                    $status = xtc_db_prepare_input($_GET['status']);
                    $orders_query_raw = "-- /admin/orders.php
                                         SELECT ".$order_select_fields.",
                                                s.orders_status_name
                                           FROM ".TABLE_ORDERS." o
                                      LEFT JOIN ".TABLE_ORDERS_STATUS." s
                                                ON (o.orders_status = s.orders_status_id
                                                    AND s.orders_status_id = '".xtc_db_input($status)."')
                                          WHERE s.language_id = '".(int)$_SESSION['languages_id']."'
                                                ".$filter.$sort;

                } elseif ($action == 'search' && $oID) {
                     // ADMIN SEARCH BAR $orders_query_raw moved it to the top
                } elseif ($action == 'search' && $customer) {
                      $orders_query_raw = "-- /admin/orders.php
                                           SELECT ".$order_select_fields.",
                                                  s.orders_status_name
                                             FROM ".TABLE_ORDERS." o
                                        LEFT JOIN ".TABLE_ORDERS_STATUS." s
                                               ON (o.orders_status = s.orders_status_id
                                                    AND s.orders_status_id = '".xtc_db_input($status)."')
                                            WHERE (o.customers_name LIKE '%".xtc_db_input($customer)."%'
                                               OR o.customers_firstname LIKE '%".xtc_db_input($customer)."%'
                                               OR o.customers_lastname LIKE '%".xtc_db_input($customer)."%'
                                               OR o.customers_company LIKE '%".xtc_db_input($customer)."%')                       
                                         ".$filter.$sort;
                } else {
                      $filter = strpos($filter,' AND') !== false ? substr_replace($filter,' WHERE',0,strlen(' AND')) : ''; //replace ONLY FIRST occurrence of a string within a string
                      $default_status = '';
                      if (defined('ORDER_STATUSES_DISPLAY_DEFAULT') && ORDER_STATUSES_DISPLAY_DEFAULT != '' && (!isset($_GET['status']) || $_GET['status'] == '')) {
                        $default_status_array = explode(',', ORDER_STATUSES_DISPLAY_DEFAULT);
                        $default_status = ((strpos($filter, 'WHERE') !== false) ? " AND " : " WHERE ")."o.orders_status IN ('".implode("', '", $default_status_array)."') ";
                      }
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
                                                  ".$filter.$default_status.$sort;                  
                }
                $orders_split = new splitPageResults($_GET['page'], $page_max_display_results, $orders_query_raw, $orders_query_numrows);
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
                  $orders_image_preview = xtc_image(DIR_WS_ICONS . 'icon_edit.gif', ICON_EDIT);
                  $orders['customers_name'] = (isset($orders['customers_company']) && $orders['customers_company'] != '') ? $orders['customers_company'] : $orders['customers_name'];
                  if (isset($oInfo) && is_object($oInfo) && ($orders['orders_id'] == $oInfo->orders_id) ) {
                    $orders_action_image = xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_EDIT);
                  } else {
                    $orders_action_image = '<a href="' . xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('oID')) . 'oID=' . $orders['orders_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>';
                  }
                  ?>
                <tr <?php echo $tr_attributes;?>>
                  <td class="dataTableContent" <?php 
                    /* magnalister v1.0.0 */
                    if (function_exists('magnaExecute')) echo magnaExecute('magnaRenderOrderPlatformIcon', array('oID' => $orders['orders_id']), array('order_details.php'));
                    /* END magnalister */
                  ?>><?php echo $orders['customers_name']; ?></td>
                  <td class="dataTableContent" align="right"><?php echo $orders['orders_id']; ?></td>
                  <td class="dataTableContent" align="right"><?php echo $orders['delivery_country']; ?>&nbsp;</td>
                  <td class="dataTableContent" align="right"><?php echo format_price(get_order_total($orders['orders_id']), 1, $orders['currency'], 0, 0); ?></td>
                  <td class="dataTableContent" align="center"><?php echo xtc_datetime_short($orders['date_purchased']); ?></td>
                  <td class="dataTableContent" align="center"><?php echo get_payment_name($orders['payment_method']); ?></td>
                  <td class="dataTableContent" align="right"><?php if($orders['orders_status']!='0') { echo $orders['orders_status_name']; }else{ echo '<span class="col-red">'.TEXT_VALIDATING.'</span>';}?></td>
                  <?php if (AFTERBUY_ACTIVATED=='true') { ?>
                  <td class="dataTableContent" align="right"><?php  echo ($orders['afterbuy_success'] == 1) ? $orders['afterbuy_id'] : 'TRANSMISSION_ERROR'; ?></td>
                  <?php } ?>
                  <td class="dataTableContent" align="right"><?php echo '<a href="' . $orders_link . '">' . $orders_image_preview . '</a>&nbsp;&nbsp;'.$orders_action_image; ?>&nbsp;</td>
                </tr>
                <?php
                }
                ?>                
              </table>
              
              <div class="smallText pdg2 flt-l"><?php echo $orders_split->display_count($orders_query_numrows, $page_max_display_results, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_ORDERS); ?></div>
              <div class="smallText pdg2 flt-r"><?php echo $orders_split->display_links($orders_query_numrows, $page_max_display_results, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], xtc_get_all_get_params(array('page', 'oID', 'action'))); ?></div>
              <?php echo draw_input_per_page($PHP_SELF,$cfg_max_display_results_key,$page_max_display_results); ?>
              <!-- EOC ORDERS LISTING -->
            </td>
              <?php
                $heading = array ();
                $contents = array ();
                switch ($action) {
                  case 'storno' :
                    $heading[] = array ('text' => '<b>'.TEXT_INFO_HEADING_REVERSE_ORDER.'</b>');
                    $contents = array ('form' => xtc_draw_form('orders', FILENAME_ORDERS, xtc_get_all_get_params(array ('oID', 'action')).'oID='.$oInfo->orders_id.'&action=stornoconfirm'));
                    $contents[] = array ('text' => TEXT_INFO_REVERSE_INTRO.'<br /><br /><b>'.$oInfo->customers_name.'</b><br /><b>'.TABLE_HEADING_ORDERS_ID.'</b>: '.$oInfo->orders_id);
                    $contents[] = array ('text' => HEADING_TITLE_STATUS . ' ' . xtc_draw_pull_down_menu('status_storno', array_merge(array(array('id' => '0', 'text' => TEXT_VALIDATING)), $orders_statuses), $oInfo->orders_status));
                    $contents[] = array ('text' => xtc_draw_checkbox_field('restock').' '.TEXT_INFO_RESTOCK_PRODUCT_QUANTITY);
                    $contents[] = array ('align' => 'center', 'text' => '<br /><input type="submit" class="button" value="'. BUTTON_REVERSE .'"><a class="button" href="'.xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array ('oID', 'action')).'oID='.$oInfo->orders_id).'">' . BUTTON_CANCEL . '</a>');
                    break;
                  case 'delete' :
                    $heading[] = array ('text' => '<b>'.TEXT_INFO_HEADING_DELETE_ORDER.'</b>');
                    $contents = array ('form' => xtc_draw_form('orders', FILENAME_ORDERS, xtc_get_all_get_params(array ('oID', 'action')).'oID='.$oInfo->orders_id.'&action=deleteconfirm'));
                    $contents[] = array ('text' => TEXT_INFO_DELETE_INTRO.'<br /><br /><b>'.$oInfo->customers_name.'</b><br /><b>'.TABLE_HEADING_ORDERS_ID.'</b>: '.$oInfo->orders_id);
                    $contents[] = array ('text' => '<br />'.xtc_draw_checkbox_field('restock').' '.TEXT_INFO_RESTOCK_PRODUCT_QUANTITY);
                    $contents[] = array ('align' => 'center', 'text' => '<br /><input type="submit" class="button" value="'. BUTTON_DELETE .'"><a class="button" href="'.xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array ('oID', 'action')).'oID='.$oInfo->orders_id).'">' . BUTTON_CANCEL . '</a>');
                    break;
                  default :
                    if (isset($oInfo) && is_object($oInfo)) {
                      $heading[] = array ('text' => '<b>['.$oInfo->orders_id.']&nbsp;&nbsp;'.xtc_datetime_short($oInfo->date_purchased).'</b>');
                      $contents[] = array ('align' => 'center', 'text' => '<a class="button" href="'.xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array ('oID', 'action')).'oID='.$oInfo->orders_id.'&action=edit').'">'.BUTTON_EDIT.'</a>
                                                                           <a class="button" href="'.xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array ('oID', 'action')).'oID='.$oInfo->orders_id.'&action=delete').'">'.BUTTON_DELETE.'</a>
                                                                           <a class="button" href="'.xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array ('oID', 'action')).'oID='.$oInfo->orders_id.'&action=storno').'">'.BUTTON_REVERSE.'</a>');
                      ## BILLSAFE payment module
                      if ($oInfo->payment_method === 'billsafe_2') {
                        $contents[] = array ('align' => 'center', 'text' => '<a class="button" href="billsafe_orders_2.php?oID='.$oInfo->orders_id.'">BillSAFE Details</a>');
                      }
                      ## BILLSAFE payment module
                      if (AFTERBUY_ACTIVATED == 'true') {
                        $contents[] = array ('align' => 'center', 'text' => '<a class="button" href="'.xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array ('oID', 'action')).'oID='.$oInfo->orders_id.'&action=custom&subaction=afterbuy_send').'">'.BUTTON_AFTERBUY_SEND.'</a>');
                      }
                      $contents[] = array ('text' => '<br />'.TEXT_DATE_ORDER_CREATED.' '.xtc_date_short($oInfo->date_purchased));
                        if (xtc_not_null($oInfo->last_modified)) {
                        $contents[] = array ('text' => TEXT_DATE_ORDER_LAST_MODIFIED.' '.xtc_date_short($oInfo->last_modified));
                      }
                      if ($oInfo->payment_method != '') {
                        $contents[] = array ('text' => '<br />'.TEXT_INFO_PAYMENT_METHOD.' '.get_payment_name($oInfo->payment_method, $oInfo->orders_id).' ('.$oInfo->payment_method.')');
                      }
                      if ($oInfo->shipping_class != '') {
                        $contents[] = array ('text' => (($oInfo->payment_method == '') ? '<br/>' : '').TEXT_INFO_SHIPPING_METHOD.' '.get_shipping_name($oInfo->shipping_class));
                      }
                      $order = new order($oInfo->orders_id);
                      $contents[] = array ('text' => '<br />'.sizeof($order->products).'&nbsp;'.TEXT_PRODUCTS);
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