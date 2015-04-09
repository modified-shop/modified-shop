<?php
/* --------------------------------------------------------------
   $Id: stats_customers.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(stats_customers.php,v 1.29 2002/05/16); www.oscommerce.com 
   (c) 2003	 nextcommerce (stats_customers.php,v 1.9 2003/08/18); www.nextcommerce.org

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

require('includes/application_top.php');

//display per page
$cfg_max_display_results_key = 'MAX_DISPLAY_STATS_CUSTOMERS_RESULTS';
$page_max_display_results = xtc_cfg_save_max_display_results($cfg_max_display_results_key);

require(DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();

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
      <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_statistic.png'); ?></div>
      <div class="pageHeading"><?php echo HEADING_TITLE; ?></div>              
      <div class="main pdg2">Statistics</div>




      <table class="tableCenter">      
        <tr>
          <td class="boxCenterFull">
      




            <table class="tableCenter collapse">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_NUMBER; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERS; ?></td>
                <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_TOTAL_PURCHASED; ?>&nbsp;</td>
              </tr>
              <?php
                $rows = (isset($_GET['page']) && $_GET['page'] > 1) ? $_GET['page']*$page_max_display_results-$page_max_display_results : 0;   
                $customers_query_raw = "SELECT c.customers_firstname, 
                                               c.customers_lastname,
                                               c.customers_id, 
                                               SUM(op.final_price) AS ordersum 
                                          FROM " . TABLE_CUSTOMERS . " c
                                          JOIN " . TABLE_ORDERS . " o 
                                               ON c.customers_id = o.customers_id
                                          JOIN " . TABLE_ORDERS_PRODUCTS . " op
                                               ON o.orders_id = op.orders_id
                                      GROUP BY c.customers_id
                                      ORDER BY ordersum DESC";
                $customers_split = new splitPageResults($_GET['page'], $page_max_display_results, $customers_query_raw, $customers_query_numrows, 'c.customers_id');
                $customers_query = xtc_db_query($customers_query_raw);
                while ($customers = xtc_db_fetch_array($customers_query)) {
                  $rows++;
                  $rows = str_pad($rows, strlen($page_max_display_results), '0', STR_PAD_LEFT);
                ?>
                <tr class="dataTableRow" onmouseover="this.className='dataTableRowOver';this.style.cursor='pointer'" onmouseout="this.className='dataTableRow'" onclick="document.location.href='<?php echo xtc_href_link(FILENAME_ORDERS, 'cID=' . $customers['customers_id'], 'NONSSL'); ?>'">
                  <td class="dataTableContent"><?php echo $rows; ?>.</td>
                  <td class="dataTableContent"><?php echo '<a href="' . xtc_href_link(FILENAME_CUSTOMERS, 'cID=' . $customers['customers_id'], 'NONSSL') . '">' . $customers['customers_firstname'] . ' ' . $customers['customers_lastname'] . '</a>'; ?></td>
                  <td class="dataTableContent txta-r"><?php echo $currencies->format($customers['ordersum']); ?>&nbsp;</td>
                </tr>
                <?php
                }
              ?>
            </table>




          </td>
        </tr>
      </table>
      
      
      
      
      
      <div class="smallText pdg2 flt-l"><?php echo $customers_split->display_count($customers_query_numrows, $page_max_display_results, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_CUSTOMERS); ?></div>
      <div class="smallText pdg2 flt-r"><?php echo $customers_split->display_links($customers_query_numrows, $page_max_display_results, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?>&nbsp;</div>
      <?php echo draw_input_per_page($PHP_SELF,$cfg_max_display_results_key,$page_max_display_results); ?>
     </td>
    <!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>