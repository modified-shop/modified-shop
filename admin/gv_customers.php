<?php
/* -----------------------------------------------------------------------------------------
   $Id:$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  require('includes/application_top.php');
  
  //display per page
  $cfg_max_display_results_key = 'MAX_DISPLAY_GV_CUSTOMERS_RESULTS';
  $page_max_display_results = xtc_cfg_save_max_display_results($cfg_max_display_results_key);

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  $gv_query_raw = "SELECT SUM(amount) as total 
                     FROM " . TABLE_COUPON_GV_CUSTOMER . " cgc
                     JOIN " . TABLE_CUSTOMERS . " c
                          ON c.customers_id = cgc.customer_id";
  $gv_query = xtc_db_query($gv_query_raw);
  $gv = xtc_db_fetch_array($gv_query);
  $gv_total = $gv['total'];
  
require (DIR_WS_INCLUDES.'head.php');
?>

</head>
<body>
  <!-- header //-->
  <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
  <!-- header_eof //-->
  <!-- body //-->
  <table class="tableBody">
    <tr>
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

      <td class="boxCenter">
        <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_news.png'); ?></div>
        <div class="pageHeading"><?php echo HEADING_TITLE; ?><br /></div>              
        <div class="main pdg2 flt-l"><?php echo HEADING_TITLE_TOTAL . $currencies->format($gv_total); ?></div>
        <table class="tableCenter">
          <tr>
            <td class="boxCenterLeft">
              <table class="tableBoxCenter collapse">
                <tr class="dataTableHeadingRow">
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_GV_ID; ?></td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_GV_NAME; ?></td>
                  <td class="dataTableHeadingContent txta-c"><?php echo TABLE_HEADING_GV_AMOUNT; ?></td>
                </tr>
                <?php
                  $gv_query_raw = "SELECT c.customers_id,
                                          c.customers_firstname,
                                          c.customers_lastname,
                                          cgc.amount
                                     FROM " . TABLE_COUPON_GV_CUSTOMER . " cgc
                                     JOIN " . TABLE_CUSTOMERS . " c
                                          ON c.customers_id = cgc.customer_id";
                  $gv_split = new splitPageResults($_GET['page'], $page_max_display_results, $gv_query_raw, $gv_query_numrows);
                  $gv_query = xtc_db_query($gv_query_raw);
                  while ($gv_list = xtc_db_fetch_array($gv_query)) {
                    $tr_attributes ='class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_CUSTOMERS, 'cID=' . $gv_list['customers_id'] . '&action=edit') .'\'"';
                    ?>
                    <tr <?php echo $tr_attributes;?>>
                      <td class="dataTableContent"><?php echo $gv_list['customers_id']; ?></td>
                      <td class="dataTableContent"><?php echo $gv_list['customers_firstname'] . ' ' . $gv_list['customers_lastname']; ?></td>
                      <td class="dataTableContent txta-c"><?php echo $currencies->format($gv_list['amount']); ?></td>
                    </tr>
                  <?php
                  }
                ?>
              </table>
              <div class="smallText pdg2 flt-l"><?php echo $gv_split->display_count($gv_query_numrows, $page_max_display_results, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_CUSTOMERS); ?></div>
              <div class="smallText pdg2 flt-r"><?php echo $gv_split->display_links($gv_query_numrows, $page_max_display_results, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></div>
              <?php echo draw_input_per_page($PHP_SELF,$cfg_max_display_results_key,$page_max_display_results); ?>
            </td>            
          </tr>
        </table>
      </td>            
      <!-- body_text_eof //-->
    </tr>
  </table>
  <!-- body_eof //-->
  <!-- footer //-->
  <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
  <!-- footer_eof //-->
  <br />
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>