<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(stats_products_purchased.php,v 1.27 2002/11/18); www.oscommerce.com
   (c) 2003 nextcommerce (stats_products_purchased.php,v 1.9 2003/08/18); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  require('includes/application_top.php');

  //BOF - DokuMan - 2010-08-12 - added possibility to reset admin statistics
  require(DIR_FS_INC. 'xtc_remove_non_numeric.inc.php');
  $maxrows = '';
  if (isset($_POST['maxrows'])){
    $maxrows = xtc_remove_non_numeric(xtc_db_prepare_input($_POST['maxrows']));
  } elseif(isset($_GET['maxrows']))  {
    $maxrows = $_GET['maxrows'];
  }
  if ($maxrows <= '20') $maxrows=20;

  if (isset($_GET['clear_id'])){
      xtc_db_query("update " . TABLE_PRODUCTS . " set products_ordered = '0' where products_id ='".$_GET['clear_id']."'");
  }
  if (isset($_GET['clear_id']) && $_GET['clear_all']=='true'){
    xtc_db_query("update " . TABLE_PRODUCTS . " set products_ordered = '0' ");
  }
  //EOF - DokuMan - 2010-08-12 - added possibility to reset admin statistics

require (DIR_WS_INCLUDES.'head.php');
?>
  </head>
  <body>
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <!-- body //-->
    <table border="0" width="100%" cellspacing="2" cellpadding="2">
      <tr>
        <td class="columnLeft2" width="<?php echo BOX_WIDTH; ?>" valign="top">
          <table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
            <!-- left_navigation //-->
            <?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
            <!-- left_navigation_eof //-->
          </table>
        </td>
        <!-- body_text //-->
        <td class="boxCenter" width="100%" valign="top">
          <table border="0" width="100%" cellspacing="0" cellpadding="2">
            <tr>
              <td>
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                  <tr>
                    <td width="80" rowspan="2"><?php echo xtc_image(DIR_WS_ICONS.'heading_statistic.gif'); ?></td>
                    <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
                  </tr>
                  <tr>
                    <td class="main" valign="top">Statistics</td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr>
              <td>
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                  <tr>
                    <td valign="top">
                      <table border="0" width="100%" cellspacing="0" cellpadding="2">
                        <tr class="dataTableHeadingRow">
                          <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_NUMBER; ?></td>
                          <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MODEL; ?></td>
                          <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
                          <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_PURCHASED; ?>&nbsp;</td>
                          <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_RESET; ?>&nbsp;</td>
                        </tr>
                        <?php
                        $rows = 0;
                        if (isset($_GET['page']) && $_GET['page'] > 1) {
                          $rows = $_GET['page'] * $maxrows - $maxrows;
                        }
                        $products_query_raw = "select p.products_id,
                                                      p.products_model,
                                                      p.products_ordered,
                                                      pd.products_name
                                                 from " . TABLE_PRODUCTS . " p,
                                                      " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                                where pd.products_id = p.products_id
                                                  and pd.language_id = '" . $_SESSION['languages_id'] . "'
                                                  and p.products_ordered > 0
                                             group by pd.products_id
                                             order by p.products_ordered DESC";
                        $products_split = new splitPageResults($_GET['page'], $maxrows, $products_query_raw, $products_query_numrows);
                        $products_query = xtc_db_query($products_query_raw);
                        while ($products = xtc_db_fetch_array($products_query)) {
                          $rows++;
                          if (strlen($rows) < 2) {
                            $rows = '0' . $rows;
                          }
                          ?>
                          <tr class="dataTableRow" onmouseover="this.className='dataTableRowOver';this.style.cursor='pointer'" onmouseout="this.className='dataTableRow'" onclick="document.location.href='<?php echo xtc_href_link(FILENAME_CATEGORIES, 'action=new_product&read=only&pID=' . $products['products_id'] . '&origin=' . FILENAME_STATS_PRODUCTS_PURCHASED . '?page=' . $_GET['page'], 'NONSSL'); ?>'">
                            <td class="dataTableContent"><?php echo $rows; ?>.</td>
                            <td class="dataTableContent"><?php echo $products['products_model']; ?></td>
                            <td class="dataTableContent"><?php echo $products['products_name'] .'</a>'; ?></td>
                            <td class="dataTableContent" align="center"><?php echo $products['products_ordered']; ?>&nbsp;</td>
                            <td class="dataTableContent" align="center"><?php echo '<a href="'.$_SERVER['PHP_SELF'].'?clear_id='.$products['products_id'].'&page='.$_GET['page'].'&maxrows='.$maxrows.'"><img src="images/icon_delete.gif" alt="reset" style="border:0px;" /> </a>'; ?></td>
                          </tr>
                          <?php
                        }
                        ?>
                        <tr>
                          <td class="dataTableContent" colspan="4" align="right" style="padding-right:20px">
                            <?php echo xtc_draw_form('resetall', FILENAME_STATS_PRODUCTS_PURCHASED, 'clear_all=true&page='.$_GET['page'].'&maxrows='.$maxrows);?>
                              <img src="images/icons/warning.gif" alt="" style="border:0px;" />
                              <input type="submit" value="<?php echo BUTTON_RESET_PRODUCTS_PURCHASED; ?>" onclick="this.blur();" class="button" />
                              <img src="images/icons/warning.gif" alt="" style="border:0px;" />
                            </form>
                          </td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="3">
                      <table border="0" width="100%" cellspacing="0" cellpadding="2">
                        <tr>
                          <td class="smallText" valign="top"><?php echo $products_split->display_count($products_query_numrows, $maxrows, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></td>
                          <td class="smallText" align="right"><?php echo $products_split->display_links($products_query_numrows, $maxrows, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?>&nbsp;</td>
                        </tr>
                        <tr>
                          <td class="smallText">
                            <?php echo TEXT_ROWS.'&nbsp;'. xtc_draw_form('getmaxrows', FILENAME_STATS_PRODUCTS_PURCHASED, 'page='.$_GET['page']) . xtc_draw_input_field('maxrows', $maxrows, 'style="width:50px"'); ?>
                              <input type="image" src="images/icon_arrow_right.gif" style="vertical-align:bottom" alt="los" title="los" />
                            </form>
                          </td>
                        </tr>
                      </table>
                    </td>
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
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>