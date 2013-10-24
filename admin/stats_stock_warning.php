<?php
/* --------------------------------------------------------------
   $Id: stats_stock_warning.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(stats_products_viewed.php,v 1.27 2003/01/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (stats_stock_warning.php,v 1.9 2003/08/18); www.nextcommerce.org

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

require('includes/application_top.php');

//display per page
$cfg_max_display_results_key = 'MAX_DISPLAY_STATS_STOCK_WARNING_RESULTS';
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
      <div class="pageHeading pdg2"><?php echo HEADING_TITLE; ?></div>              
      <div class="main pdg2">Statistics</div>
      
      <table class="tableCenter collapse">
        <tr class="dataTableHeadingRow">
          <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_NUMBER; ?></td>
          <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MODEL; ?></td>
          <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
          <td class="dataTableHeadingContent txta-c"><?php echo TABLE_HEADING_QUANTITY; ?>&nbsp;</td>
        </tr>
        <?php
        $rows = (isset($_GET['page']) && $_GET['page'] > 1) ? $_GET['page']*$page_max_display_results-$page_max_display_results : 0;   
        $products_query_raw = "select p.products_id,
                                      p.products_model,
                                      p.products_quantity,
                                      pd.products_name
                                 FROM " . TABLE_PRODUCTS . " p,
                                      " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                WHERE pd.language_id = '" . $_SESSION['languages_id'] . "'
                                  AND pd.products_id = p.products_id
                             ORDER BY products_quantity";
        $products_split = new splitPageResults($_GET['page'], $page_max_display_results, $products_query_raw, $products_query_numrows);
        $products_query = xtc_db_query($products_query_raw);
        while ($products = xtc_db_fetch_array($products_query)) {
          while ($products_values = xtc_db_fetch_array($products_query)) {
            $rows++;
            $rows = str_pad($rows, strlen($page_max_display_results), '0', STR_PAD_LEFT);
            echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_CATEGORIES, 'action=new_product_preview&read=only&pID=' . $products['products_id'] . '&origin=' . FILENAME_STATS_STOCK_WARNING . '?page=' . $_GET['page'], 'NONSSL') . '\'">
                    <td class="dataTableContent">' . $rows . '.</td>
                    <td class="dataTableContent">' .  $products_values['products_model'] . '</td>
                    <td class="dataTableContent"><b>' . $products_values['products_name'] . '</b></td>
                    <td class="dataTableContent txta-c">';
            if ($products_values['products_quantity'] <='0') {
              echo '<font color="#ff0000"><b>'.$products_values['products_quantity'].'</b></font>';
            } else {
              echo $products_values['products_quantity'];
            }
            echo '  </td>
                  </tr>';

            $products_attributes_query = xtc_db_query("SELECT
                                                           pov.products_options_values_name,
                                                           pa.attributes_model,
                                                           pa.attributes_stock
                                                       FROM
                                                           " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov
                                                       WHERE
                                                           pa.products_id = '".$products_values['products_id'] . "' AND pov.products_options_values_id = pa.options_values_id AND pov.language_id = '" . $_SESSION['languages_id'] . "' ORDER BY pa.attributes_stock");
                
            while ($products_attributes_values = xtc_db_fetch_array($products_attributes_query)) {
              echo '<tr>
                      <td class="dataTableContent">&nbsp;</td>
                      <td class="dataTableContent">' .  $products_attributes_values['attributes_model'] . '</td>
                      <td class="dataTableContent">&nbsp;&nbsp;&nbsp;&nbsp;-' . $products_attributes_values['products_options_values_name'] . '</td>
                      <td class="dataTableContent txta-c">';
              if ($products_attributes_values['attributes_stock'] <= '0') {
                echo '<font color="#ff0000"><b>' . $products_attributes_values['attributes_stock'] . '</b></font>';
              } else {
                echo $products_attributes_values['attributes_stock'];
              }
              echo '  </td>
                    </tr>';
            }
          }
        }
      ?>
      </table>             
      <div class="smallText pdg2 flt-l"><?php echo $products_split->display_count($products_query_numrows, $page_max_display_results, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></div>
      <div class="smallText pdg2 flt-r"><?php echo $products_split->display_links($products_query_numrows, $page_max_display_results, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></div>
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