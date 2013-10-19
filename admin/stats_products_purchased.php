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
      <div class="pageHeading pdg2"><?php echo HEADING_TITLE; ?></div>              
      <div class="main pdg2">Statistics</div>
      
      <table class="tableCenter collapse">
        <tr class="dataTableHeadingRow">
          <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_NUMBER; ?></td>
          <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MODEL; ?></td>
          <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
          <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_PURCHASED; ?>&nbsp;</td>
          <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_QUANTITY; ?>&nbsp;</td>
        </tr>
        <?php
        $rows = (isset($_GET['page']) && $_GET['page'] > 1) ? $_GET['page']*$page_max_display_results-$page_max_display_results : 0;             
        $products_query_raw = "select p.products_id,
                                      p.products_model,  
                                      p.products_ordered,
                                      p.products_quantity,
                                      pd.products_name 
                                 from " . TABLE_PRODUCTS . " p, 
                                      " . TABLE_PRODUCTS_DESCRIPTION . " pd 
                                where pd.products_id = p.products_id 
                                  and pd.language_id = '" . $_SESSION['languages_id'] . "' 
                                  and p.products_ordered > 0 
                             group by pd.products_id 
                             order by p.products_ordered DESC, pd.products_name";
        $products_split = new splitPageResults($_GET['page'], $page_max_display_results, $products_query_raw, $products_query_numrows, 'p.products_id');

        $products_query = xtc_db_query($products_query_raw);
        while ($products = xtc_db_fetch_array($products_query)) {
          $rows++;
          $rows = str_pad($rows, strlen($page_max_display_results), '0', STR_PAD_LEFT);
        ?>
        <tr class="dataTableRow" onmouseover="this.className='dataTableRowOver';this.style.cursor='pointer'" onmouseout="this.className='dataTableRow'" onclick="document.location.href='<?php echo xtc_href_link(FILENAME_CATEGORIES, 'action=new_product_preview&read=only&pID=' . $products['products_id'] . '&origin=' . FILENAME_STATS_PRODUCTS_PURCHASED . '?page=' . $_GET['page'], 'NONSSL'); ?>'">
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
      <div class="smallText pdg2 flt-r"><?php echo $products_split->display_links($products_query_numrows, $page_max_display_results, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></div>
      <div class="clear"></div>
      <div class="smallText pdg2 flt-l">
        <?php 
        echo xtc_draw_form('cfg_max', FILENAME_STATS_PRODUCTS_PURCHASED);         
        echo DISPLAY_PER_PAGE.xtc_draw_input_field($cfg_max_display_results_key, $page_max_display_results, 'style="width: 40px"');
        echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SAVE . '"/>';
        echo '</form>'; 
        ?> 
      </div>
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