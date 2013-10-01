<?php
  /* --------------------------------------------------------------
   $Id$

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
  // default view (daily)
  $srDefaultView = 2;
  // default export
  $srDefaultExp = 0;
  // default sort
  $srDefaultSort = 4;

  $srView = 0;
  $srDetail = 0;
  $srExp = 0;
  $srMax = 0;
  $srStatus = 0;
  $srPayment = 0;
  $srSort = 0;
  $srFilter = 0;

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
  if (isset($_GET['status']) && (xtc_not_null($_GET['status'])) ) {
    $srStatus = $_GET['status'];
  }
  if (!is_numeric($srStatus)) {
    $srStatus = 0;
  }

   // paymenttype
  if (isset($_GET['payment']) && (xtc_not_null($_GET['payment'])) ) {
    $srPayment = $_GET['payment'];
  } else {
    $srPayment = 0;
  }

  // sort
  if (isset($_GET['sort']) && (xtc_not_null($_GET['sort'])) ) {
    $srSort = $_GET['sort'];
  }
  if ($srSort < 1 || $srSort > 6) {
    $srSort = $srDefaultSort;
  }

  // check start and end Date
  $startDate = "";
  $startDateG = 0;
  if (isset($_GET['startD']) && (xtc_not_null($_GET['startD'])) ) {
    $sDay = $_GET['startD'];
    $startDateG = 1;
  } else {
    $sDay = 1;
  }
  if (isset($_GET['startM']) && (xtc_not_null($_GET['startM'])) ) {
    $sMon = $_GET['startM'];
    $startDateG = 1;
  } else {
    $sMon = 1;
  }
  if (isset($_GET['startY']) && (xtc_not_null($_GET['startY'])) ) {
    $sYear = $_GET['startY'];
    $startDateG = 1;
  } else {
    $sYear = date("Y");
  }
  if ($startDateG) {
    $startDate = mktime(0, 0, 0, $sMon, $sDay, $sYear);
  } else {
    $startDate = mktime(0, 0, 0, date("m"), 1, date("Y"));
  }

  $endDate = "";
  $endDateG = 0;
  if (isset($_GET['endD']) && (xtc_not_null($_GET['endD'])) ) {
    $eDay = $_GET['endD'];
    $endDateG = 1;
  } else {
    $eDay = 1;
  }
  if (isset($_GET['endM']) && (xtc_not_null($_GET['endM'])) ) {
    $eMon = $_GET['endM'];
    $endDateG = 1;
  } else {
    $eMon = 1;
  }
  if (isset($_GET['endY']) && (xtc_not_null($_GET['endY'])) ) {
    $eYear = $_GET['endY'];
    $endDateG = 1;
  } else {
    $eYear = date("Y");
  }
  if ($endDateG) {
    $endDate = mktime(0, 0, 0, $eMon, $eDay + 1, $eYear);
  } else {
    $endDate = mktime(0, 0, 0, date("m"), date("d") + 1, date("Y"));
  }
  
  require(DIR_WS_CLASSES . 'sales_report.php');
  $sr = new sales_report($srView, $startDate, $endDate, $srSort, $srStatus, $srFilter,$srPayment);
  $startDate = $sr->startDate;
  $endDate = $sr->endDate;
  //echo 'SD'.$startDate;
  

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
                <div class="pageHeading pdg2"><?php echo HEADING_TITLE; ?></div>              
                <div class="main pdg2">Statistics</div>
                <div class="clear"></div>
                    
                  <?php
                  if ($srExp < 1) {
                    echo xtc_draw_form('sales_report', FILENAME_SALES_REPORT, '', 'get').xtc_draw_hidden_field(xtc_session_name(), xtc_session_id());
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
                                <?php echo REPORT_START_DATE;?><br />
                                <select name="startD" size="1">
                                  <?php                                  
                                  if ($startDate) {
                                    $j = date("j", $startDate);                                    
                                  } else {
                                    $j = 1;
                                  }
                                  for ($i = 1; $i < 32; $i++) {
                                    ?>
                                    <option value="<?php echo $i; ?>"<?php if ($j == $i) echo " selected"; ?>><?php echo $i; ?></option>
                                    <?php
                                  }
                                  ?>
                                </select>
                                <select name="startM" size="1">
                                  <?php
                                  if ($startDate) {
                                    $m = date("n", $startDate);
                                  } else {
                                    $m = 1;
                                  }
                                  for ($i = 1; $i < 13; $i++) {
                                    ?>
                                    <option value="<?php echo $i; ?>"<?php if ($m == $i) echo " selected"; ?>><?php echo strftime("%B", mktime(0, 0, 0, $i, 1)); ?></option>
                                    <?php
                                  }
                                  ?>
                                </select>
                                <select name="startY" size="1">
                                  <?php
                                  if ($startDate) {
                                    $y = date("Y") - date("Y", $startDate);
                                  } else {
                                    $y = 0;
                                  }
                                  for ($i = 10; $i >= 0; $i--) {
                                    ?>
                                    <option value="<?php echo date("Y") - $i; ?>"<?php if ($y == $i) echo " selected"; ?>><?php echo date("Y") - $i; ?></option>
                                    <?php
                                  }
                                  ?>
                                </select>
                              </td>
                              <td rowspan="2" class="menuBoxHeading txta-l">
                                <?php echo REPORT_DETAIL; ?><br />
                                <select name="detail" size="1">
                                  <option value="0"<?php if ($srDetail == 0) echo "selected"; ?>><?php echo DET_HEAD_ONLY; ?></option>
                                  <option value="1"<?php if ($srDetail == 1) echo " selected"; ?>><?php echo DET_DETAIL; ?></option>
                                  <option value="2"<?php if ($srDetail == 2) echo " selected"; ?>><?php echo DET_DETAIL_ONLY; ?></option>
                                </select>
                                <br />
                                <?php echo REPORT_MAX; ?><br />
                                <select name="max" size="1">
                                  <option value="0"><?php echo REPORT_ALL; ?></option>
                                  <option<?php if ($srMax == 1) echo " selected"; ?>>1</option>
                                  <option<?php if ($srMax == 3) echo " selected"; ?>>3</option>
                                  <option<?php if ($srMax == 5) echo " selected"; ?>>5</option>
                                  <option<?php if ($srMax == 10) echo " selected"; ?>>10</option>
                                  <option<?php if ($srMax == 25) echo " selected"; ?>>25</option>
                                  <option<?php if ($srMax == 50) echo " selected"; ?>>50</option>
                                </select>
                              </td>
                              <td rowspan="2" class="menuBoxHeading txta-l">
                                <?php echo REPORT_STATUS_FILTER; ?><br />
                                <select name="status" size="1">
                                  <option value="0"><?php echo REPORT_ALL; ?></option>
                                  <?php
                                  foreach ($sr->status as $value) {
                                    ?>
                                    <option value="<?php echo $value["orders_status_id"]?>"<?php if ($srStatus == $value["orders_status_id"]) echo " selected"; ?>><?php echo $value["orders_status_name"] ; ?></option>
                                    <?php
                                  }
                                  ?>
                                </select>
                                <br />
                                <?php echo REPORT_PAYMENT_FILTER; ?><br />
                                <select name="payment" size="1">
                                  <option value="0" <?php if ($srPayment === 0) echo " selected"; ?>><?php echo REPORT_ALL; ?></option>
                                  <?php
                                  $payments = explode(';', MODULE_PAYMENT_INSTALLED);
                                  for ($i=0; $i<count($payments); $i++){
                                    require(DIR_FS_LANGUAGES . $_SESSION['language'] . '/modules/payment/' . $payments[$i]);
                                    $payment = substr($payments[$i], 0, strrpos($payments[$i], '.'));
                                    $payment_text = constant('MODULE_PAYMENT_'.strtoupper($payment).'_TEXT_TITLE');
                                    ?>
                                    <option value="<?php echo $payment; ?>"<?php if ($srPayment === $payment) echo " selected"; ?>><?php echo $payment_text ; ?></option>
                                    <?php
                                  }
                                  ?>
                                </select>
                                <br />
                              </td>
                              <td rowspan="2" class="menuBoxHeading txta-l">
                                <?php echo REPORT_EXP; ?><br />
                                <select name="export" size="1">
                                  <option value="0" selected><?php echo EXP_NORMAL; ?></option>
                                  <option value="1"><?php echo EXP_HTML; ?></option>
                                  <option value="2"><?php echo EXP_CSV; ?></option>
                                </select>
                                <br />
                                <?php echo REPORT_SORT; ?><br />
                                <select name="sort" size="1">
                                  <option value="0"<?php if ($srSort == 0) echo " selected"; ?>><?php echo SORT_VAL0; ?></option>
                                  <option value="1"<?php if ($srSort == 1) echo " selected"; ?>><?php echo SORT_VAL1; ?></option>
                                  <option value="2"<?php if ($srSort == 2) echo " selected"; ?>><?php echo SORT_VAL2; ?></option>
                                  <option value="3"<?php if ($srSort == 3) echo " selected"; ?>><?php echo SORT_VAL3; ?></option>
                                  <option value="4"<?php if ($srSort == 4) echo " selected"; ?>><?php echo SORT_VAL4; ?></option>
                                  <option value="5"<?php if ($srSort == 5) echo " selected"; ?>><?php echo SORT_VAL5; ?></option>
                                  <option value="6"<?php if ($srSort == 6) echo " selected"; ?>><?php echo SORT_VAL6; ?></option>
                                </select>
                                <br />
                              </td>
                            </tr>
                            <tr>
                              <td class="menuBoxHeading">
                                <?php echo REPORT_END_DATE; ?><br />
                                <select name="endD" size="1">
                                  <?php
                                  echo $endDate;
                                  
                                  if ($endDate) {
                                    $j = date("j", $endDate - (60 * 60 * 24));
                                  } else {
                                    $j = date("j");
                                  }
                                  for ($i = 1; $i < 32; $i++) {
                                    ?>
                                    <option value="<?php echo $i; ?>"<?php if ($j == $i) echo " selected"; ?>><?php echo $i; ?></option>
                                    <?php
                                  }
                                  ?>
                                </select>
                                <select name="endM" size="1">
                                  <?php
                                  if ($endDate) {
                                    $m = date("n", $endDate - 60* 60 * 24);
                                  } else {
                                    $m = date("n");
                                  }
                                  for ($i = 1; $i < 13; $i++) {
                                    ?>
                                    <option value="<?php echo $i; ?>"<?php if ($m == $i) echo " selected"; ?>><?php echo strftime("%B", mktime(0, 0, 0, $i, 1)); ?></option>
                                    <?php
                                  }
                                  ?>
                                </select>
                                <select name="endY" size="1">
                                  <?php
                                  if ($endDate) {
                                    $y = date("Y") - date("Y", $endDate - 60* 60 * 24);
                                  } else {
                                    $y = 0;
                                  }
                                  for ($i = 10; $i >= 0; $i--) {
                                    ?>
                                    <option value="<?php echo date("Y") - $i; ?>"<?php if ($y == $i) echo " selected"; ?>><?php echo date("Y") - $i; ?></option>
                                    <?php
                                  }
                                  ?>
                                </select>
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
                                <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_DATE; ?></td>
                                <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ORDERS;?></td>
                                <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ITEMS; ?></td>
                                <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_REVENUE;?></td>
                                <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_SHIPPING;?></td>
                              </tr>
                              <?php
                            } // end of if $srExp < 2 csv export

                            $total_order = 0;
                            $total_item = 0;
                            $total_total = 0;
                            $total_shipping = 0;

                            while ($sr->actDate < $sr->endDate) {
                              $info = $sr->getNext($srDetail);
                              $last = sizeof($info) - 1;
                              if ($srExp < 2) {
                                ?>
                                <tr class="dataTableRow"onMouseOver="this.className='dataTableRowOver';this.style.cursor='pointer'" onMouseOut="this.className='dataTableRow'">
                                  <?php
                                  switch ($srView) {
                                    case '3':
                                      ?>
                                      <td class="dataTableContent txta-r"><?php echo xtc_date_long(date("Y-m-d H:i:s", $sr->showDate)) . " - " . xtc_date_short(date("Y-m-d H:i:s", $sr->showDateEnd)); ?></td>
                                      <?php
                                      break;
                                    case '4':
                                      ?>
                                      <td class="dataTableContent txta-r"><?php echo xtc_date_long(date("Y-m-d H:i:s", $sr->showDate)); ?></td>
                                      <?php
                                      break;
                                    default;
                                      ?>
                                      <td class="dataTableContent txta-r"><?php echo xtc_date_short(date("Y-m-d H:i:s", $sr->showDate)) . " - " . xtc_date_short(date("Y-m-d H:i:s", $sr->showDateEnd)); ?></td>
                                     <?php
                                  }
                                  ?>
                                  <td class="dataTableContent txta-r"><?php echo (isset($info[0]['order']) ? $info[0]['order'] : '&nbsp;'); ?></td>
                                  <td class="dataTableContent txta-r"><?php echo (isset($info[$last]['totitem']) ? $info[$last]['totitem'] : '&nbsp;'); ?></td>
                                  <td class="dataTableContent txta-r"><?php echo (isset($info[$last]['totsum']) ? $currencies->format($info[$last]['totsum']) : '&nbsp;' ); ?></td>
                                  <td class="dataTableContent txta-r"><?php echo $currencies->format($info[0]['shipping']); ?></td>
                                </tr>
                                <?php
                                  $total_order += (isset($info[0]['order']) ? $info[0]['order'] : 0);
                                  $total_item += (isset($info[$last]['totitem']) ? $info[$last]['totitem'] : 0);
                                  $total_total += (isset($info[$last]['totsum']) ? $info[$last]['totsum'] : 0);
                                  $total_shipping += (isset($info[0]['shipping']) ? $info[0]['shipping'] : 0);
                             } else {
                                // csv export
                                ('Content-type: application/x-octet-stream');
                                header('Content-disposition: attachment; filename=stats_sales_report.csv');
                                echo date(DATE_FORMAT, $sr->showDate) . SR_SEPARATOR1 . date(DATE_FORMAT, $sr->showDateEnd) . SR_SEPARATOR1;
                                echo $info[0]['order'] . SR_SEPARATOR1;
                                echo $info[$last]['totitem'] . SR_SEPARATOR1;
                                echo number_format($info[$last]['totsum'], 2, '.', '') . SR_SEPARATOR1;
                                echo number_format($info[0]['shipping'], 2, '.', '') . "\n";
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