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

   Released under the GNU General Public License
   --------------------------------------------------------------*/

require ('includes/application_top.php');

require (DIR_WS_CLASSES.'currencies.php');
$currencies = new currencies();

require (DIR_WS_CLASSES.'campaigns.php');
$campaign = new campaigns($_GET);

// default view (monthly)
$srDefaultView = 2;

$orders_statuses = array();
$orders_status_array = array();
$orders_status_query = xtc_db_query("SELECT orders_status_id,
                                            orders_status_name,
                                            sort_order
                                       FROM ".TABLE_ORDERS_STATUS."
                                      WHERE language_id = '".(int)$_SESSION['languages_id']."'
                                   ORDER BY sort_order");
while ($orders_status = xtc_db_fetch_array($orders_status_query)) {
  $orders_statuses[] = array ('id' => $orders_status['orders_status_id'], 'text' => $orders_status['orders_status_name']);
  $orders_status_array[$orders_status['orders_status_id']] = $orders_status['orders_status_name'];
}

$campaigns = array ();
$campaign_query = "SELECT * FROM ".TABLE_CAMPAIGNS." ORDER BY campaigns_name";
$campaign_query = xtc_db_query($campaign_query);
while ($campaign_data = xtc_db_fetch_array($campaign_query)) {
	$campaigns[] = array ('id' => $campaign_data['campaigns_refID'], 'text' => $campaign_data['campaigns_name']);
}

// report views (1: yearly 2: monthly 3: weekly 4: daily)
if (isset($_GET['report']) && (xtc_not_null($_GET['report'])) ) {
  $srView = $_GET['report'];
}
if ($srView < 1 || $srView > 4) {
  $srView = $srDefaultView;
}

// check start and end Date
$startDate = "";
$startDateG = 0;
if (($_GET['startD']) && (xtc_not_null($_GET['startD']))) {
	$sDay = $_GET['startD'];
	$startDateG = 1;
} else {
	$sDay = 1;
}
if (($_GET['startM']) && (xtc_not_null($_GET['startM']))) {
	$sMon = $_GET['startM'];
	$startDateG = 1;
} else {
	$sMon = 1;
}
if (($_GET['startY']) && (xtc_not_null($_GET['startY']))) {
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
if (($_GET['endD']) && (xtc_not_null($_GET['endD']))) {
	$eDay = $_GET['endD'];
	$endDateG = 1;
} else {
	$eDay = 1;
}
if (($_GET['endM']) && (xtc_not_null($_GET['endM']))) {
	$eMon = $_GET['endM'];
	$endDateG = 1;
} else {
	$eMon = 1;
}
if (($_GET['endY']) && (xtc_not_null($_GET['endY']))) {
	$eYear = $_GET['endY'];
	$endDateG = 1;
} else {
	$eYear = date("Y");
}
if ($endDateG) {
	$endDate = mktime(0, 0, 0, $eMon, $eDay +1, $eYear);
} else {
	$endDate = mktime(0, 0, 0, date("m"), date("d") + 1, date("Y"));
}

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

require (DIR_WS_INCLUDES.'head.php');
?>
</head>
  <body>
    <?php
    require(DIR_WS_INCLUDES . 'header.php');
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
                    echo xtc_draw_form('campaigns_report', FILENAME_CAMPAIGNS_REPORT, '', 'get').xtc_draw_hidden_field(xtc_session_name(), xtc_session_id());
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
                            $day = $month = $year = 1;
                            if ($startDate) {
                              $day = date("j", $startDate);
                              $month = date("n", $startDate);
                              $year = date("Y", $startDate);
                            }
                            echo REPORT_START_DATE.'<br/>';
                            echo xtc_draw_pull_down_menu('startD', $day_array, $day);
                            echo xtc_draw_pull_down_menu('startM', $month_array, $month);
                            echo xtc_draw_pull_down_menu('startY', $year_array, $year);
                          ?>
                        </td>
                        <td rowspan="2" class="menuBoxHeading txta-l">
                          <?php echo REPORT_STATUS_FILTER; ?><br />
                          <?php echo xtc_draw_pull_down_menu('status', array_merge(array(array('id' => '0', 'text' => REPORT_ALL)), $orders_statuses), $_GET['status']); ?> 
                          <br /><?php echo REPORT_CAMPAIGN_FILTER; ?><br /> 
                          <?php echo xtc_draw_pull_down_menu('campaign', array_merge(array(array('id' => '0', 'text' => REPORT_ALL)), $campaigns), $_GET['campaign']); ?> 
                        </td>
                        <td rowspan="2" align="left" class="menuBoxHeading"><br /></td>
                        <td rowspan="2" align="left" class="menuBoxHeading"><br /></td>
                      </tr>
                      <tr>
                        <td class="menuBoxHeading">
                          <?php 
                            $day = date("j");
                            $month = date("n");
                            $year = 0;
                            if ($endDate) {
                              $day = date("j", $endDate - (60 * 60 * 24));
                              $month = date("n", $endDate - (60 * 60 * 24));
                              $year = date("Y", $endDate - (60 * 60 * 24));
                            }
                            echo REPORT_END_DATE.'<br/>';
                            echo xtc_draw_pull_down_menu('endD', $day_array, $day);
                            echo xtc_draw_pull_down_menu('endM', $month_array, $month);
                            echo xtc_draw_pull_down_menu('endY', $year_array, $year);
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

                  if (count($campaign->result)) {
                  ?>
                <table class="tableCenter collapse"> 
                  <tr class="dataTableHeadingRow"> 
                    <td class="dataTableHeadingContent" colspan="2" width="25%"><?php echo HEADING_TOTAL; ?></td>
                    <td class="dataTableHeadingContent txta-r" width="10%">&nbsp;</td>
                    <td class="dataTableHeadingContent txta-r" width="15%"><?php echo $campaign->total['leads']; ?></td>
                    <td class="dataTableHeadingContent txta-r" colspan="2" width="30%"><?php echo $campaign->total['sells']; ?></td>
                    <td class="dataTableHeadingContent txta-r" width="20%"><?php echo $campaign->total['sum']; ?></td>
                  </tr>
                  <tr class="dataTableHeadingRow"> 
                    <td class="dataTableHeadingContent" colspan="2" width="25%">&nbsp;</td>
                    <td class="dataTableHeadingContent txta-r" width="10%"><?php echo HEADING_HITS; ?></td>
                    <td class="dataTableHeadingContent txta-r" width="15%"><?php echo HEADING_LEADS; ?></td>
                    <td class="dataTableHeadingContent txta-r" width="15%"><?php echo HEADING_SELLS; ?></td>
                    <td class="dataTableHeadingContent txta-r" width="15%"><?php echo HEADING_LATESELLS; ?></td>
                    <td class="dataTableHeadingContent txta-r" width="20%"><?php echo HEADING_SUM; ?></td>
                  </tr>
                  <?php
                  // show campaigns
                  for ($n = 0; $n < count($campaign->result); $n ++) {
                  ?>
                    <tr class="dataTableRow"> 
                      <td class="main" colspan="7" style="border-bottom: 2px solid;"><br /><?php echo $campaign->result[$n]['text'].' '.TEXT_REFERER .' ('.$campaign->result[$n]['id'].')'; ?></td>
                    </tr>
                    <?php
                    // show values
                    for ($nn = 0; $nn < count($campaign->result[$n]['result']); $nn ++) {
                      ?>
                      <tr class="dataTableRow"> 
                        <td class="dataTableContent">&nbsp;</td>
                        <td class="dataTableContent"><?php echo $campaign->result[$n]['result'][$nn]['range']; ?></td>
                        <td class="dataTableContent txta-r"><?php echo $campaign->result[$n]['result'][$nn]['hits']; ?></td>
                        <td class="dataTableContent txta-r"><?php echo $campaign->result[$n]['result'][$nn]['leads'].' ('.$campaign->result[$n]['result'][$nn]['leads_p'].'%)'; ?></td>
                        <td class="dataTableContent txta-r"><?php echo $campaign->result[$n]['result'][$nn]['sells'].' ('.$campaign->result[$n]['result'][$nn]['sells_p'].'%)'; ?></td>
                        <td class="dataTableContent txta-r"><?php echo $campaign->result[$n]['result'][$nn]['late_sells'].' ('.$campaign->result[$n]['result'][$nn]['late_sells_p'].'%)'; ?></td>
                        <td class="dataTableContent txta-r"><?php echo $campaign->result[$n]['result'][$nn]['sum'].' ('.$campaign->result[$n]['result'][$nn]['sum_p'].'%)'; ?></td>
                      </tr>
                      <?php
                    }
                    ?>
                    <tr class="dataTableHeadingRow"> 
                      <td class="dataTableHeadingContent" colspan="2"><strong><?php echo HEADING_SUM; ?></strong></td>
                      <td class="dataTableHeadingContent txta-r"><strong><?php echo $campaign->result[$n]['hits_s']; ?></strong></td>
                      <td class="dataTableHeadingContent txta-r"><strong><?php echo $campaign->result[$n]['leads_s'].' ('.($campaign->total['leads']> 0 ? ($campaign->result[$n]['leads_s']/$campaign->total['leads']*100):'0').'%)'; ?></strong></td>
                      <td class="dataTableHeadingContent txta-r"><strong><?php echo $campaign->result[$n]['sells_s'].' ('.($campaign->total['sells']> 0 ? ($campaign->result[$n]['sells_s']/$campaign->total['sells']*100):'0').'%)'; ?></strong></td>
                      <td class="dataTableHeadingContent txta-r"><strong><?php echo $campaign->result[$n]['late_sells_s'].' ('.($campaign->total['sells']> 0 ? ($campaign->result[$n]['late_sells_s']/$campaign->total['sells']*100):'0').'%)'; ?></strong></td>
                      <td class="dataTableHeadingContent txta-r"><strong><?php echo $campaign->result[$n]['sum_s'].' ('.($campaign->total['sum_plain']> 0 ? round(($campaign->result[$n]['sum_s']/$campaign->total['sum_plain']*100),0):'0').'%)'; ?></strong></td>
                    </tr>
                    <?php
                  }
                  ?>
                </table>
              </td>
            </tr>
          </table>
            <?php 
          } 
          ?>
        </td>
        <!-- body_text_eof //-->
      </tr>
    </table>
    <!-- body_eof //-->
    <!-- footer //-->
    <?php
    require(DIR_WS_INCLUDES . 'footer.php');
    ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php
require(DIR_WS_INCLUDES . 'application_bottom.php');