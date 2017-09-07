<?php
  /* --------------------------------------------------------------
   $Id: stats_sales_report.php 1687 2011-01-23 12:12:04Z franky-n-xtcm $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce coding standards; www.oscommerce.com
   (c) 2006 xt:Commerce (stats_sales_report.php 1311 2005-10-18)

   Released under the GNU General Public License
   --------------------------------------------------------------
   Third Party contribution:

   stats_sales_report (c) Charly Wilhelm  charly@yoshi.ch

   possible views (srView):
   1 yearly
   2 monthly
   3 weekly
   4 daily

   possible options (srDetail):
   0 no detail
   1 show details (products)
   2 show details only (products)

   export
   0 normal view
   1 html view without left and right
   2 csv

   sort
   0 no sorting
   1 product description asc
   2 product description desc
   3 #product asc, product descr asc
   4 #product desc, product descr desc
   5 revenue asc, product descr asc
   6 revenue desc, product descr des

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  require('includes/application_top.php');
  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();
  
  // default detail no detail
  $srDefaultDetail = 0;
  // default view (monthly)
  $srDefaultView = 2;
  // default export
  $srDefaultExp = 0;
  // default sort
  $srDefaultSort = 4;

  $srView = 0;
  $srDetail = 0;
  $srExp = 0;
  $srMax = 0;
  $srPayment = 0;
  $srSort = 0;
  $srFilter = 0;
  $srCgroup = '';

  // report views (1: yearly 2: monthly 3: weekly 4: daily)
  if (isset($_GET['report']) && (xtc_not_null($_GET['report'])) ) {
    $srView = $_GET['report'];
  }
  if ($srView < 1 || $srView > 4) {
    $srView = $srDefaultView;
  }

  // detail
  if (isset($_GET['detail']) && (xtc_not_null($_GET['detail'])) ) {
    $srDetail = $_GET['detail'];
  }
  if ($srDetail < 0 || $srDetail > 2) {
    $srDetail = $srDefaultDetail;
  }

  // report views (1: yearly 2: monthly 3: weekly 4: daily)
  if (isset($_GET['export']) && (xtc_not_null($_GET['export'])) ) {
    $srExp = $_GET['export'];
  }
  if ($srExp < 0 || $srExp > 2) {
    $srExp = $srDefaultExp;
  }

  // item_level
  if (isset($_GET['max']) && (xtc_not_null($_GET['max'])) ) {
    $srMax = $_GET['max'];
  }
  if (!is_numeric($srMax)) {
    $srMax = 0;
  }

  // order status
  $srStatus = (isset($_GET['status']) && preg_match("/^[0-9\,]+$/", $_GET['status'])) ? $_GET['status'] : 0;
  
  // customers status/group
  $srCgroup = (isset($_GET['cgroup']) && preg_match("/^[0-9\,]+$/", $_GET['cgroup'])) ? $_GET['cgroup'] : '';
  
   // paymenttype
  if (isset($_GET['payment']) && (xtc_not_null($_GET['payment'])) ) {
    $srPayment = $_GET['payment'];
  } else {
    $srPayment = '0';
  }

  // sort
  if (isset($_GET['sort']) && (xtc_not_null($_GET['sort'])) ) {
    $srSort = $_GET['sort'];
  }
  if ($srSort < 0 || $srSort > 6) {
    $srSort = $srDefaultSort;
  }

  // check start and end Date
  if (isset($_GET['startD']) && (xtc_not_null($_GET['startD'])) ) {
    $sDay = $_GET['startD'];
  } else {
    $sDay = 1;
  }
  if (isset($_GET['startM']) && (xtc_not_null($_GET['startM'])) ) {
    $sMon = $_GET['startM'];
  } else {
    switch ($srDefaultView) {
      case 1:
        $sMon = 1;
        break;
      default:
        $sMon = date("n");
        break;
    }
  }
  if (isset($_GET['startY']) && (xtc_not_null($_GET['startY'])) ) {
    $sYear = $_GET['startY'];
  } else {
    $sYear = date("Y");
  }
  $startDate = mktime(0, 0, 0, $sMon, $sDay, $sYear);

  if (isset($_GET['endD']) && (xtc_not_null($_GET['endD'])) ) {
    $eDay = $_GET['endD'];
  } else {
    $eDay = date("j");
  }
  if (isset($_GET['endM']) && (xtc_not_null($_GET['endM'])) ) {
    $eMon = $_GET['endM'];
  } else {
    $eMon = date("n");
  }
  if (isset($_GET['endY']) && (xtc_not_null($_GET['endY'])) ) {
    $eYear = $_GET['endY'];
  } else {
    $eYear = date("Y");
  }
  $endDate = mktime(0, 0, 0, $eMon, $eDay + 1, $eYear);
  
  require(DIR_WS_CLASSES . 'sales_report.php');
  $sr = new sales_report($srView, $startDate, $endDate, $srSort, $srStatus, $srFilter, $srPayment, $srCgroup);
  $startDate = $sr->startDate;
  $endDate = $sr->endDate;
  
  $day_array = array();
  for ($i = 1; $i < 32; $i++) {
    $day_array[] = array('id' => $i, 'text' => $i);
  }

  $month_array = array();
  for ($i = 1; $i < 13; $i++) {
    $month_array[] = array('id' => $i, 'text' => decode_utf8(strftime("%B", mktime(0, 0, 0, $i, 1))));
  }

  $year_array = array();
  for ($i = 10; $i >= 0; $i--) {
    $year_array[] = array('id' => date("Y") - $i, 'text' => date("Y") - $i);
  }

  $sort_array = array();
  for ($i = 0; $i < 7; $i++) {
    $sort_array[] = array('id' => $i, 'text' => constant('SORT_VAL'.$i));
  }

  $exp_array = array(array('id' => 0, 'text' => EXP_NORMAL),
                     array('id' => 1, 'text' => EXP_HTML),
                     array('id' => 2, 'text' => EXP_CSV)
                     );
  
  $payment_array = array(array('id' => 0, 'text' => REPORT_ALL));
  $payments = explode(';', MODULE_PAYMENT_INSTALLED);
  $payments = array_filter($payments); //fix for empty array
  for ($i=0, $n=count($payments); $i<$n; $i++){
    if (is_file(DIR_FS_LANGUAGES . $_SESSION['language'] . '/modules/payment/' . $payments[$i])) {
      include(DIR_FS_LANGUAGES . $_SESSION['language'] . '/modules/payment/' . $payments[$i]);
      $payment_id = substr($payments[$i], 0, strrpos($payments[$i], '.'));
      $payment_text = constant('MODULE_PAYMENT_'.strtoupper($payment_id).'_TEXT_TITLE');
      $payment_array[] = array('id' => $payment_id, 'text' => $payment_text);
    } else {
      $payment_id = substr($payments[$i], 0, strrpos($payments[$i], '.'));
      $payment_array[] = array('id' => $payment_id, 'text' => $payment_id);
    }
  }

  $status_array = array(array('id' => 0, 'text' => REPORT_ALL));
  if (strpos(ORDER_STATUSES_FOR_SALES_STATISTICS, ',') !== false) {
    $status_array[] = array('id' => ORDER_STATUSES_FOR_SALES_STATISTICS, 'text' => REPORT_SALES_STATISTICS);
  }
  foreach ($sr->status as $value) {
    $status_array[] = array('id' => $value["orders_status_id"], 'text' => $value["orders_status_name"]);
  }

  $max_array = array(array('id' => 0, 'text' => REPORT_ALL),
                     array('id' => 1, 'text' => '1'),
                     array('id' => 3, 'text' => '3'),
                     array('id' => 5, 'text' => '5'),
                     array('id' => 10, 'text' => '10'),
                     array('id' => 25, 'text' => '25'),
                     array('id' => 50, 'text' => '50'),
                     );

  $detail_array = array(array('id' => 0, 'text' => DET_HEAD_ONLY),
                        array('id' => 1, 'text' => DET_DETAIL),
                        array('id' => 2, 'text' => DET_DETAIL_ONLY)
                       );

  $customers_statuses_array = array_merge(array (array ('id' => '', 'text' => TXT_ALL)), xtc_get_customers_statuses());

  if ($srExp < 2) {
    // not for csv export
    require (DIR_WS_INCLUDES.'head.php');
    ?>    
      </head>
        <body>
          <?php
          if ($srExp < 1) {
            require(DIR_WS_INCLUDES . 'header.php');
          }
          ?>
          <!-- header_eof //-->
          <!-- body //-->
          <table class="tableBody">
            <tr>
              <?php
              if ($srExp < 1) {
                ?>
                <?php //left_navigation
                if (USE_ADMIN_TOP_MENU == 'false') {
                  echo '<td class="columnLeft2">'.PHP_EOL;
                  echo '<!-- left_navigation //-->'.PHP_EOL;       
                  require_once(DIR_WS_INCLUDES . 'column_left.php');
                  echo '<!-- left_navigation eof //-->'.PHP_EOL; 
                  echo '</td>'.PHP_EOL;      
                }
                ?>
                <!-- body_text //-->
                <?php
              } // end sr_exp
              ?>
              <td class="boxCenter">
                <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_statistic.png'); ?></div>
                <div class="pageHeading"><?php echo HEADING_TITLE; ?></div>              
                <div class="main pdg2">Statistics</div>
                <div class="clear"></div>
                <table class="tableCenter">      
                  <tr>
                    <td class="boxCenterFull">
                      <?php
                      if ($srExp < 1) {
                        echo xtc_draw_form('sales_report', FILENAME_SALES_REPORT, '', 'get');
                        ?>                    
                          <table style="border: 1px solid #cccccc; width:100%; padding:5px; background:#f1f1f1;">
                            <tr>
                              <td rowspan="2" class="menuBoxHeading txta-l">
                                <input type="radio" name="report" value="1" <?php if ($srView == 1) echo "checked"; ?>><?php echo REPORT_TYPE_YEARLY; ?><br />
                                <input type="radio" name="report" value="2" <?php if ($srView == 2) echo "checked"; ?>><?php echo REPORT_TYPE_MONTHLY; ?><br />
                                <input type="radio" name="report" value="3" <?php if ($srView == 3) echo "checked"; ?>><?php echo REPORT_TYPE_WEEKLY; ?><br />
                                <input type="radio" name="report" value="4" <?php if ($srView == 4) echo "checked"; ?>><?php echo REPORT_TYPE_DAILY; ?><br />
                              </td>
                              <td class="menuBoxHeading">
                                <?php 
                                  echo '<p class="pdg2 mrg0"><b>'.REPORT_START_DATE.'</b></p>';
                                  echo xtc_draw_pull_down_menu('startD', $day_array, $sDay);
                                  echo xtc_draw_pull_down_menu('startM', $month_array, $sMon);
                                  echo xtc_draw_pull_down_menu('startY', $year_array, $sYear);
                                ?>
                              </td>
                              <td class="menuBoxHeading txta-l">
                                <?php 
                                  echo '<p class="pdg2 mrg0"><b>'.REPORT_DETAIL.'</b></p>'; 
                                  echo xtc_draw_pull_down_menu('detail', $detail_array, $srDetail);
                                ?>
                              </td>
                              <td class="menuBoxHeading txta-l">
                                <?php
                                  echo '<div class="flt-l">';                                    
                                  echo '<p class="pdg2 mrg0"><b>'.REPORT_STATUS_FILTER.'</b></p>'; 
                                  echo xtc_draw_pull_down_menu('status', $status_array, $srStatus);
                                  echo '</div>';
                                  echo '<div class="flt-l">'; 
                                  echo '<p class="pdg2 mrg0"><b>'.ENTRY_CUSTOMERS_STATUS.'</b></p>'; 
                                  echo xtc_draw_pull_down_menu('cgroup', $customers_statuses_array, $srCgroup);
                                  echo '</div>';
                                ?>
                              </td>
                              <td class="menuBoxHeading txta-l">
                                <?php 
                                  echo '<p class="pdg2 mrg0"><b>'.REPORT_EXP.'</b></p>'; 
                                  echo xtc_draw_pull_down_menu('export', $exp_array, $srExp);
                                ?>
                              </td>
                            </tr>
                            <tr>
                              <td class="menuBoxHeading">
                                <?php 
                                  echo '<p class="pdg2 mrg0"><b>'.REPORT_END_DATE.'</b></p>';
                                  echo xtc_draw_pull_down_menu('endD', $day_array, $eDay);
                                  echo xtc_draw_pull_down_menu('endM', $month_array, $eMon);
                                  echo xtc_draw_pull_down_menu('endY', $year_array, $eYear);
                                ?>
                              </td>
                              <td class="menuBoxHeading txta-l">
                                <?php 
                                  echo'<p class="pdg2 mrg0"><b>'. REPORT_MAX.'</b></p>'; 
                                  echo xtc_draw_pull_down_menu('max', $max_array, $srMax);
                                ?>
                              </td>
                              <td class="menuBoxHeading txta-l">
                                <?php
                                  echo '<p class="pdg2 mrg0"><b>'.REPORT_PAYMENT_FILTER.'</b></p>'; 
                                  echo xtc_draw_pull_down_menu('payment', $payment_array, $srPayment);
                                ?>
                              </td>
                              <td class="menuBoxHeading txta-l">
                                <?php 
                                  echo '<p class="pdg2 mrg0"><b>'.REPORT_SORT.'</b></p>'; 
                                  echo xtc_draw_pull_down_menu('sort', $sort_array, $srSort);
                                ?>
                              </td>
                            </tr>
                          </table>  
                          <div class="main mrg5 txta-r">
                            <?php echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UPDATE . '"/>'; ?>
                          </div>                         
                        </form>
                        <?php
                      } // end of ($srExp < 1)
                      ?>
                      <table class="tableCenter collapse">
                        <tr class="dataTableHeadingRow">
                          <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_DATE; ?></td>
                          <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ORDERS;?></td>
                          <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ITEMS; ?></td>
                          <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_REVENUE;?></td>
                          <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_SHIPPING;?></td>
                          <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ADDITIONAL;?></td>
                          <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_TOTAL;?></td>
                        </tr>
                        <?php
                      } // end of if $srExp < 2 csv export

                      $total_order = 0;
                      $total_item = 0;
                      $total_total = 0;
                      $total_shipping = 0;
                      $total_additional = 0;
        
                      while ($sr->actDate < $sr->endDate) {
                        $info = $sr->getNext($srDetail);
                        $last = sizeof($info) - 1;
                        if ($srExp < 2) {
                          ?>
                          <tr class="dataTableRow" onMouseOver="this.className='dataTableRowOver';this.style.cursor='pointer'" onMouseOut="this.className='dataTableRow'">
                            <?php
                            switch ($srView) {
                              case '3':
                                ?>
                                <td class="dataTableContent"><?php echo xtc_date_long(date("Y-m-d H:i:s", $sr->showDate)) . " - " . xtc_date_short(date("Y-m-d H:i:s", $sr->showDateEnd)); ?></td>
                                <?php
                                break;
                              case '4':
                                ?>
                                <td class="dataTableContent"><?php echo xtc_date_long(date("Y-m-d H:i:s", $sr->showDate)); ?></td>
                                <?php
                                break;
                              default;
                                ?>
                                <td class="dataTableContent"><?php echo xtc_date_short(date("Y-m-d H:i:s", $sr->showDate)) . " - " . xtc_date_short(date("Y-m-d H:i:s", $sr->showDateEnd)); ?></td>
                               <?php
                            }
                            $total_order += (isset($info[0]['order']) ? $info[0]['order'] : 0);
                            $total_item += (isset($info[$last]['totitem']) ? $info[$last]['totitem'] : 0);
                            $total_total += (isset($info[$last]['totsum']) ? $info[$last]['totsum'] : 0);
                            $total_shipping += (isset($info[0]['shipping']) ? $info[0]['shipping'] : 0);
                            $total_additional += (isset($info[0]['additional']) ? $info[0]['additional'] : 0);
                            ?>
                            <td class="dataTableContent txta-r"><?php echo (isset($info[0]['order']) ? $info[0]['order'] : '&nbsp;'); ?></td>
                            <td class="dataTableContent txta-r"><?php echo (isset($info[$last]['totitem']) ? $info[$last]['totitem'] : '&nbsp;'); ?></td>
                            <td class="dataTableContent txta-r"><?php echo (isset($info[$last]['totsum']) ? $currencies->format($info[$last]['totsum']) : '&nbsp;' ); ?></td>
                            <td class="dataTableContent txta-r"><?php echo (isset($info[0]['shipping']) ? $currencies->format($info[0]['shipping']) : '&nbsp;' ); ?></td>
                            <td class="dataTableContent txta-r"><?php echo (isset($info[0]['additional']) ? $currencies->format($info[0]['additional']) : '&nbsp;' ); ?></td>
                            <td class="dataTableContent txta-r"><?php echo $currencies->format((isset($info[$last]['totsum']) ? $info[$last]['totsum'] : 0) + (isset($info[0]['shipping']) ? $info[0]['shipping'] : 0) + (isset($info[0]['additional']) ? $info[0]['additional'] : 0)); ?></td>
                          </tr>
                          <?php
                        } else {
                          // csv export
                          ('Content-type: application/x-octet-stream');
                          header('Content-disposition: attachment; filename=stats_sales_report.csv');
                          echo date(DATE_FORMAT, $sr->showDate) . SR_SEPARATOR1 . date(DATE_FORMAT, $sr->showDateEnd) . SR_SEPARATOR1;
                          echo $info[0]['order'] . SR_SEPARATOR1;
                          echo $info[$last]['totitem'] . SR_SEPARATOR1;
                          echo number_format($info[$last]['totsum'], 2, '.', '') . SR_SEPARATOR1;
                          echo number_format($info[0]['shipping'], 2, '.', '') . SR_SEPARATOR1;
                          echo number_format($info[0]['additional'], 2, '.', '') . "\n";
                        }
                        if ($srDetail) {
                          for ($i = 0; $i <= $last; $i++) {
                            if ($srMax == 0 or $i < $srMax) {
                              if ($srExp < 2) {
                                ?>
                                <tr class="dataTableRow" onMouseOver="this.className='dataTableRowOver';this.style.cursor='pointer'" onMouseOut="this.className='dataTableRow'">
                                  <td class="dataTableContent">&nbsp;</td>
                                  <td class="dataTableContent txta-l">
                                    <a href="<?php echo xtc_catalog_href_link("product_info.php?products_id=" . $info[$i]['pid']) ?>" target="_blank"><?php echo ((xtc_not_null($info[$i]['pmodel'])) ? $info[$i]['pmodel'].' : ' : '').$info[$i]['pname']; ?></a>
                                    <?php
                                    if (is_array($info[$i]['attr'])) {
                                      foreach ($info[$i]['attr'] as $attr) {
                                        $price = 0;
                                        echo '<div style="font-style:italic; text-indent:10px;">';
                                        for ($x=0, $n=sizeof($attr['options_values']); $x<$n; $x++) {
                                          if ($x > 0) echo '<br/>';
                                          echo $attr['options'][$x] . ': ' . $attr['options_values'][$x];
                                          echo (($attr['price'][$x]>0) ? ' (' . $attr['price_prefix'][$x] . $currencies->format($attr['price'][$x]) . ')' : '');
                                        }
                                        echo '</div>';
                                      }
                                    }
                                    ?>
                                  </td>
                                  <td class="dataTableContent txta-r"><?php echo $info[$i]['pquant']; ?></td>
                                  <?php
                                  if ($srDetail == 2) {?>
                                    <td class="dataTableContent txta-r"><?php echo $currencies->format($info[$i]['psum']); ?></td>
                                    <?php
                                  } else {
                                    ?>
                                    <td class="dataTableContent">&nbsp;</td>
                                    <?php
                                  }
                                  ?>
                                  <td class="dataTableContent">&nbsp;</td>
                                  <td class="dataTableContent">&nbsp;</td>
                                  <td class="dataTableContent">&nbsp;</td>
                                </tr>
                                <?php
                              } else {
                                // csv export details
                                echo $info[$i]['pmodel'] . SR_SEPARATOR2 . $info[$i]['pname'] . SR_SEPARATOR2;
                                if (is_array($info[$i]['attr'])) {
                                  foreach ($info[$i]['attr'] as $attr) {
                                    for ($x=0, $n=sizeof($attr['options_values']); $x<$n; $x++) {
                                      if ($x > 0) echo ', ';
                                      echo $attr['options'][$x] . ': ' . $attr['options_values'][$x];
                                      echo (($attr['price'][$x]>0) ? ' (' . $attr['price_prefix'][$x] . number_format($attr['price'][$x], 2, '.', '') . ')' : '');
                                    }
                                  }
                                }
                                echo SR_SEPARATOR2;
                                if ($srDetail == 2) {
                                  echo $info[$i]['pquant'] . SR_SEPARATOR2;
                                  echo number_format($info[$i]['psum'], 2, '.', '') . SR_SEPARATOR2;
                                } else {
                                  echo $info[$i]['pquant'] . SR_SEPARATOR2;
                                  echo SR_SEPARATOR2;
                                }
                                echo "\n";
                              }
                            }
                          }
                        }
                      }
                      if ($srExp < 2) {
                      ?>
                        <tr class="dataTableHeadingRow">
                          <td class="dataTableHeadingContent txta-r"><?php echo BOX_ORDER_TOTAL; ?></td>
                          <td class="dataTableHeadingContent txta-r"><?php echo $total_order;?></td>
                          <td class="dataTableHeadingContent txta-r"><?php echo $total_item; ?></td>
                          <td class="dataTableHeadingContent txta-r"><?php echo $currencies->format($total_total);?></td>
                          <td class="dataTableHeadingContent txta-r"><?php echo $currencies->format($total_shipping);?></td>
                          <td class="dataTableHeadingContent txta-r"><?php echo $currencies->format($total_additional);?></td>
                          <td class="dataTableHeadingContent txta-r"><?php echo $currencies->format($total_total + $total_shipping + $total_additional);?></td>
                        </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
            <!-- body_text_eof //-->
          </tr>
        </table>
        <!-- body_eof //-->
        <!-- footer //-->
        <?php
        if ($srExp < 1) {
          require(DIR_WS_INCLUDES . 'footer.php');
        }
        ?>
        <!-- footer_eof //-->
      </body>
    </html>
    <?php
    require(DIR_WS_INCLUDES . 'application_bottom.php');
  } // end if $srExp < 2
?>