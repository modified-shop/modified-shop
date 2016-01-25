<?php
/* --------------------------------------------------------------
   $Id: stats_stock_warning.php 899 2005-04-29 02:40:57Z hhgag $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(stats_products_viewed.php,v 1.27 2003/01/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (stats_stock_warning.php,v 1.9 2003/08/18); www.nextcommerce.org
   (c) 2005 xtCommerce (stats_stock_warning.php); www.xt-commerce.com

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

require('includes/application_top.php');

// include needed functions
require_once (DIR_FS_INC.'xtc_get_category_path.inc.php');
require_once (DIR_FS_INC.'xtc_get_parent_categories.inc.php');

//display per page
$cfg_max_display_results_key = 'MAX_DISPLAY_STATS_STOCK_WARNING_RESULTS';
$page_max_display_results = xtc_cfg_save_max_display_results($cfg_max_display_results_key);

$stock_array = array(
  array('id' => '', 'text' => TXT_ALL),
  array('id' => '0', 'text' => '0'),
  array('id' => '1', 'text' => '1'),
  array('id' => '3', 'text' => '3'),
  array('id' => '5', 'text' => '5'),
  array('id' => '10', 'text' => '10'),
  array('id' => '20', 'text' => '20'),
  array('id' => '50', 'text' => '50'),
);

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
            <?php  echo xtc_draw_form('stats_stock_warning', FILENAME_STATS_STOCK_WARNING, xtc_get_all_get_params(), 'get'); ?>
            <table class="tableCenter collapse">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_NUMBER; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MODEL; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
                <td class="dataTableHeadingContent txta-c"><?php echo TABLE_HEADING_QUANTITY.' '.xtc_draw_pull_down_menu('qty', $stock_array, ((isset($_GET['qty']) && $_GET['qty'] != '') ? (int)$_GET['qty'] : ''), 'style="width: 80px" onChange="this.form.submit();"'); ?></td>
              </tr>
              <?php
              $rows = (isset($_GET['page']) && $_GET['page'] > 1) ? $_GET['page']*$page_max_display_results-$page_max_display_results : 0;   
              $where = '';
              $where_attr = '';
              if (isset($_GET['qty']) && $_GET['qty'] != '') {
                $where = " WHERE (p.products_quantity <= '".(int)$_GET['qty']."' OR pa.attributes_stock <= '".(int)$_GET['qty']."') ";
                $where_attr = " AND pa.attributes_stock <= '".(int)$_GET['qty']."' ";
              }
              $products_query_raw = "SELECT p.products_id,
                                            p.products_model,
                                            p.products_quantity,
                                            pd.products_name,
                                            p2c.categories_id,
                                            pa.attributes_stock
                                       FROM " . TABLE_PRODUCTS . " p
                                       JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                            ON pd.products_id = p.products_id  
                                               AND pd.language_id = '" . (int)$_SESSION['languages_id'] . "' 
                                       JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                                            ON p2c.products_id = p.products_id
                                               AND p2c.categories_id != '0' 
                                  LEFT JOIN ".TABLE_PRODUCTS_ATTRIBUTES." pa
                                            ON pa.products_id = p.products_id
                                            ".$where."
                                   GROUP BY p.products_id  
                                   ORDER BY p.products_quantity ASC";
              $products_split = new splitPageResults($_GET['page'], $page_max_display_results, $products_query_raw, $products_query_numrows, 'p.products_id');
              $products_query = xtc_db_query($products_query_raw);
              while ($products = xtc_db_fetch_array($products_query)) {
                $rows++;
                $rows = str_pad($rows, strlen($page_max_display_results), '0', STR_PAD_LEFT);
                echo '<tr class="dataTableRow brd-t" onmouseover="this.className=\'dataTableRowOver brd-t\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow brd-t\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_CATEGORIES, 'action=new_product_preview&read=only&pID=' . $products['products_id'] . '&origin=' . FILENAME_STATS_STOCK_WARNING . '&page=' . $_GET['page'] . '&cPath='.xtc_get_category_path($products['categories_id']), 'NONSSL') . '\'">
                        <td class="dataTableContent">' . $rows . '.</td>
                        <td class="dataTableContent">' .  $products['products_model'] . '</td>
                        <td class="dataTableContent"><b>' . $products['products_name'] . '</b></td>
                        <td class="dataTableContent txta-c">';
                if ($products['products_quantity'] <= STOCK_REORDER_LEVEL) {
                  echo '<span class="col-red"><b>'.$products['products_quantity'].'</b></span>';
                } else {
                  echo $products['products_quantity'];
                }
                echo '  </td>
                      </tr>';

                $products_attributes_query = xtc_db_query("SELECT pov.products_options_values_name,
                                                                  pa.attributes_model,
                                                                  pa.attributes_stock
                                                             FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                                             JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov
                                                                  ON pov.products_options_values_id = pa.options_values_id
                                                                     AND pov.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                                            WHERE pa.products_id = '".$products['products_id'] . "' 
                                                                  ".$where_attr."
                                                         ORDER BY pa.attributes_stock");
              
                while ($products_attributes_values = xtc_db_fetch_array($products_attributes_query)) {
                  echo '<tr class="dataTableRowSub" onmouseover="this.className=\'dataTableRowSubOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRowSub\'">
                          <td class="dataTableContentSub">&nbsp;</td>
                          <td class="dataTableContentSub">' .  $products_attributes_values['attributes_model'] . '</td>
                          <td class="dataTableContentSub">&nbsp;&nbsp;&nbsp;&nbsp;-' . $products_attributes_values['products_options_values_name'] . '</td>
                          <td class="dataTableContentSub txta-c">';
                  if ($products_attributes_values['attributes_stock'] <= STOCK_REORDER_LEVEL) {
                    echo '<span class="col-red"><b>' . $products_attributes_values['attributes_stock'] . '</b></span>';
                  } else {
                    echo $products_attributes_values['attributes_stock'];
                  }
                  echo '  </td>
                        </tr>';
                }
              }
            ?>
            </table>
            </form>
          </td>
        </tr>
      </table>
      <div class="smallText pdg2 flt-l"><?php echo $products_split->display_count($products_query_numrows, $page_max_display_results, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></div>
      <div class="smallText pdg2 flt-r"><?php echo $products_split->display_links($products_query_numrows, $page_max_display_results, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], xtc_get_all_get_params(array('page'))); ?></div>
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