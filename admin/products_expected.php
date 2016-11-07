<?php
  /* --------------------------------------------------------------
   $Id: products_expected.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(products_expected.php,v 1.29 2002/03/17); www.oscommerce.com
   (c) 2003 nextcommerce (products_expected.php,v 1.9 2003/08/18); www.nextcommerce.org
   (c) 2006 XT-Commerce (products_expected.php 1125 2005-07-28)

   Released under the GNU General Public License
   --------------------------------------------------------------*/

require('includes/application_top.php');

//display per page
$cfg_max_display_results_key = 'MAX_DISPLAY_PRODUCTS_EXPECTED_RESULTS';
$page_max_display_results = xtc_cfg_save_max_display_results($cfg_max_display_results_key);

xtc_db_query("update " . TABLE_PRODUCTS . " set products_date_available = '' where to_days(now()) > to_days(products_date_available)");

require (DIR_WS_INCLUDES.'head.php');
?>
<script type="text/javascript" src="includes/general.js"></script>
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
        <div class="pageHeading pdg2 mrg5"><?php echo HEADING_TITLE; ?></div>
        <table class="tableCenter">
          <tr>
            <td class="boxCenterLeft">
              <table class="tableBoxCenter collapse">
                <tr class="dataTableHeadingRow">
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
                  <td class="dataTableHeadingContent txta-c"><?php echo TABLE_HEADING_DATE_EXPECTED; ?></td>
                  <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                </tr>
                <?php
                $products_query_raw = "SELECT
                                              pd.products_id,
                                              pd.products_name,
                                              p.products_date_available
                                         FROM " . TABLE_PRODUCTS_DESCRIPTION . " pd,
                                              " . TABLE_PRODUCTS . " p
                                        WHERE p.products_id = pd.products_id
                                          AND p.products_date_available != ''
                                          AND pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                     ORDER BY p.products_date_available DESC";
                $products_split = new splitPageResults($_GET['page'], $page_max_display_results, $products_query_raw, $products_query_numrows);
                $products_query = xtc_db_query($products_query_raw);
                while ($products = xtc_db_fetch_array($products_query)) {
                  if ((!isset($_GET['pID']) || (isset($_GET['pID']) && ($_GET['pID'] == $products['products_id']))) && !isset($pInfo) ) {
                    $pInfo = new objectInfo($products);
                  }
                  if (isset($pInfo) && is_object($pInfo) && ($products['products_id'] == $pInfo->products_id) ) {
                    echo '                  <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_CATEGORIES, 'pID=' . $products['products_id'] . '&action=new_product') . '\'">' . "\n";
                  } else {
                    echo '                  <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_PRODUCTS_EXPECTED, 'page=' . $_GET['page'] . '&pID=' . $products['products_id']) . '\'">' . "\n";
                  }
                    ?>
                    <td class="dataTableContent"><?php echo $products['products_name']; ?></td>
                    <td class="dataTableContent txta-c"><?php echo xtc_date_short($products['products_date_available']); ?></td>
                    <td class="dataTableContent txta-r"><?php if (isset($pInfo) && is_object($pInfo) && ($products['products_id'] == $pInfo->products_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_PRODUCTS_EXPECTED, 'page=' . $_GET['page'] . '&pID=' . $products['products_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                  </tr>
                  <?php
                }
                ?>
              </table>
                          
              <div class="smallText pdg2 flt-l"><?php echo $products_split->display_count($products_query_numrows, $page_max_display_results, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_PRODUCTS_EXPECTED); ?></div>
              <div class="smallText pdg2 flt-r"><?php echo $products_split->display_links($products_query_numrows, $page_max_display_results, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></div>
              <?php echo draw_input_per_page($PHP_SELF,$cfg_max_display_results_key,$page_max_display_results); ?>
            </td>
            <?php
            $heading = array();
            $contents = array();
            if (isset($pInfo) && is_object($pInfo)) {
              $heading[] = array('text' => '<b>' . $pInfo->products_name . '</b>');
              $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CATEGORIES, 'pID=' . $pInfo->products_id . '&action=new_product') . '">' . BUTTON_EDIT . '</a>');
              $contents[] = array('text' => '<br />' . TEXT_INFO_DATE_EXPECTED . ' ' . xtc_date_short($pInfo->products_date_available));
            }
            if ( (xtc_not_null($heading)) && (xtc_not_null($contents)) ) {
              echo '            <td class="boxRight">' . "\n";
              $box = new box;
              echo $box->infoBox($heading, $contents);
              echo '            </td>' . "\n";
            }
          ?>
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