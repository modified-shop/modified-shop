<?php
/* --------------------------------------------------------------
   $Id: stats_products_purchased.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(stats_products_purchased.php,v 1.27 2002/11/18); www.oscommerce.com 
   (c) 2003	 nextcommerce (stats_products_purchased.php,v 1.9 2003/08/18); www.nextcommerce.org

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

require('includes/application_top.php');
  
// include needed functions
require_once (DIR_FS_INC.'xtc_get_category_path.inc.php');
require_once (DIR_FS_INC.'xtc_get_parent_categories.inc.php');

//display per page
$cfg_max_display_results_key = 'MAX_DISPLAY_STATS_STATS_PRODUCTS_PURCHASED_RESULTS';
$page_max_display_results = xtc_cfg_save_max_display_results($cfg_max_display_results_key);
 
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
      <div class="pageHeading flt-l">
        <?php echo HEADING_TITLE; ?>
        <div class="main pdg2">Statistics</div>
      </div>              
      <div class="main flt-r pdg2 mrg5" style="margin-left:20px;">
        <?php echo xtc_draw_form('search', FILENAME_STATS_PRODUCTS_PURCHASED, '', 'get'); ?>
        <?php echo TEXT_SEARCH_PRODUCTS . ' ' . xtc_draw_input_field('search', ((isset($_GET['search'])) ? $_GET['search'] : ''), 'size="24"'); ?>
        </form>
      </div>
      <table class="tableCenter">      
        <tr>
          <?php
          if (isset($_GET['action']) 
              && $_GET['action'] == 'orders' 
              && isset($_GET['pID']) 
              && $_GET['pID'] != ''
              )
          {
            //display per page
            $cfg_max_display_results_key = 'MAX_DISPLAY_ORDER_RESULTS';
            $page_max_display_results = xtc_cfg_save_max_display_results($cfg_max_display_results_key);
            ?>
            <td class="boxCenterFull">
              <table class="tableCenter collapse">
                <tr class="dataTableHeadingRow">
                  <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_DATE_PURCHASED; ?></td>
                  <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ORDERS_ID; ?></td>
                  <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_QUANTITY; ?></td>
                </tr>
                <?php
                $products_query_raw = "SELECT op.orders_id, 
                                              op.products_quantity, 
                                              o.date_purchased 
                                         FROM " . TABLE_ORDERS_PRODUCTS . " op 
                                         JOIN " . TABLE_ORDERS . " o 
                                              ON op.orders_id = o.orders_id 
                                        WHERE op.products_id = '" . (int) $_GET['pID'] . "'
                                     ORDER BY op.orders_id DESC";
                $products_split = new splitPageResults($_GET['spage'], $page_max_display_results, $products_query_raw, $products_query_numrows, 'op.orders_id');
                $products_query = xtc_db_query($products_query_raw);
                while ($products = xtc_db_fetch_array($products_query)) {
                  ?>
                  <tr class="dataTableRow" onmouseover="this.className='dataTableRowOver';this.style.cursor='pointer'" onmouseout="this.className='dataTableRow'" onclick="document.location.href='<?php echo xtc_href_link(FILENAME_ORDERS, 'action=edit&oID=' . (int) $products['orders_id'], 'NONSSL'); ?>'">
                    <td class="dataTableContent" align="center"><?php echo xtc_date_long($products['date_purchased']); ?></td>
                    <td class="dataTableContent" align="right"><?php echo $products['orders_id']; ?></td>
                    <td class="dataTableContent" align="right"><?php echo $products['products_quantity']; ?></td>
                  </tr>
				          <?php
				        }
                ?>
              </table>
            </td>
          <?php
          } else {
          ?>
            <td class="boxCenterLeft">
              <table class="tableCenter collapse">
                <tr class="dataTableHeadingRow">
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_NUMBER; ?></td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MODEL; ?></td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
                  <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_PURCHASED; ?></td>
                  <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_QUANTITY; ?></td>
                </tr>
                <?php
                $rows = (isset($_GET['page']) && $_GET['page'] > 1) ? $_GET['page']*$page_max_display_results-$page_max_display_results : 0;
                $where = '';
                if (isset($_GET['search']) && $_GET['search'] != '') {
                  $where = " AND (pd.products_name LIKE ('%".xtc_db_input($_GET['search'])."%')
                                 OR p.products_model LIKE ('%".xtc_db_input($_GET['search'])."%')) ";
                }
                $products_query_raw = "SELECT p.products_id,
                                              p.products_model,  
                                              p.products_ordered,
                                              p.products_quantity,
                                              pd.products_name,
                                              p2c.categories_id
                                         FROM " . TABLE_PRODUCTS . " p
                                         JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                              ON pd.products_id = p.products_id  
                                                 AND pd.language_id = '" . (int)$_SESSION['languages_id'] . "' 
                                         JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                                              ON p2c.products_id = p.products_id
                                                 AND p2c.categories_id != '0'  
                                        WHERE p.products_ordered > 0
                                              ".$where."
                                     GROUP BY pd.products_id 
                                     ORDER BY p.products_ordered DESC, pd.products_name ASC";
                $products_split = new splitPageResults($_GET['page'], $page_max_display_results, $products_query_raw, $products_query_numrows, 'p.products_id');
                $products_query = xtc_db_query($products_query_raw);
                while ($products = xtc_db_fetch_array($products_query)) {
                  $rows++;
                  $rows = str_pad($rows, strlen($page_max_display_results), '0', STR_PAD_LEFT);
                  if ((!xtc_not_null($_GET['pID']) || (isset($_GET['pID']) && $_GET['pID'] == $products['products_id'])) && !isset($pInfo)) {
                    $pInfo = new objectInfo($products);
                  }

                  if ((is_object($pInfo)) && ($products['products_id'] == $pInfo->products_id) ) {
                    echo '<tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_STATS_PRODUCTS_PURCHASED, xtc_get_all_get_params(array('action', 'pID')) . 'pID=' . $pInfo->products_id . '&action=orders') . '\'">' . "\n";
                  } else {
                    echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_STATS_PRODUCTS_PURCHASED, xtc_get_all_get_params(array('action', 'pID')) . 'pID=' . $products['products_id']) . '\'">' . "\n";
                  }
                  ?>
                    <td class="dataTableContent"><?php echo $rows; ?>.</td>
                    <td class="dataTableContent"><?php echo $products['products_model']; ?>&nbsp;</td>
                    <td class="dataTableContent"><?php echo $products['products_name']; ?></td>                      
                    <td class="dataTableContent" align="center"><?php echo $products['products_ordered']; ?>&nbsp;</td>
                    <td class="dataTableContent" align="center"><?php echo $products['products_quantity']; ?>&nbsp;</td>
                  </tr>
                  <?php
                }
                ?>
              </table>
              <div class="smallText pdg2 flt-l"><?php echo $products_split->display_count($products_query_numrows, $page_max_display_results, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></div>
              <div class="smallText pdg2 flt-r"><?php echo $products_split->display_links($products_query_numrows, $page_max_display_results, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], xtc_get_all_get_params(array('page')), 'page'); ?></div>
              <?php echo draw_input_per_page($PHP_SELF.'?'.xtc_get_all_get_params(),$cfg_max_display_results_key,$page_max_display_results); ?>
            </td>
            <?php
            $heading = array();
            $contents = array();
            $heading[] = array('text' => '<b>' . $pInfo->products_name . '</b>');
            $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_STATS_PRODUCTS_PURCHASED, xtc_get_all_get_params(array('action', 'pID')).'action=orders&pID=' . $pInfo->products_id) . '">' . BUTTON_ORDERS . '</a> 
                                                                <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CATEGORIES, 'action=new_product_preview&read=only&pID=' . $pInfo->products_id . '&origin=' . FILENAME_STATS_PRODUCTS_PURCHASED . '&page=' . $_GET['page'] . '&cPath='.xtc_get_category_path($pInfo->categories_id)) . '">' . BUTTON_EDIT . '</a>');
            
            if ( (xtc_not_null($heading)) && (xtc_not_null($contents)) ) {
              echo '<td class="boxRight">' . "\n";
              $box = new box;
              echo $box->infoBox($heading, $contents);
              echo '</td>' . "\n";
            }
          }
          ?>
          </td>
        </tr>
      </table>
      <?php
        if (isset($_GET['action']) 
            && $_GET['action'] == 'orders' 
            && isset($_GET['pID']) 
            && $_GET['pID'] != ''
            )
        {
        ?>
          <div class="smallText pdg2 flt-l"><?php echo $products_split->display_count($products_query_numrows, $page_max_display_results, $_GET['spage'], TEXT_DISPLAY_NUMBER_OF_ORDERS); ?></div>
          <div class="smallText pdg2 flt-r"><?php echo $products_split->display_links($products_query_numrows, $page_max_display_results, MAX_DISPLAY_PAGE_LINKS, $_GET['spage'], xtc_get_all_get_params(array('spage')), 'spage'); ?></div>
          <?php echo draw_input_per_page($PHP_SELF.'?'.xtc_get_all_get_params(),$cfg_max_display_results_key,$page_max_display_results); ?>
          <div class="smallText pdg2 flt-r"><?php echo '<a class="button" href="' . xtc_href_link(FILENAME_STATS_PRODUCTS_PURCHASED, xtc_get_all_get_params(array('action', 'spage'))) . '">' . BUTTON_BACK . '</a>'; ?></div>
        <?php
        }
      ?>
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