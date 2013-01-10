<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(reviews.php,v 1.40 2003/03/22); www.oscommerce.com
   (c) 2003 nextcommerce (reviews.php,v 1.9 2003/08/18); www.nextcommerce.org
   (c) 2006 XT-Commerce (reviews.php 1129 2005-08-05)

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  require('includes/application_top.php');
  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  if (xtc_not_null($action)) {
    switch ($action) {
      case 'update':
        $reviews_id = xtc_db_prepare_input($_GET['rID']);
        $reviews_rating = xtc_db_prepare_input($_POST['reviews_rating']);
        $last_modified = xtc_db_prepare_input($_POST['last_modified']);
        $reviews_text = xtc_db_prepare_input($_POST['reviews_text']);

        xtc_db_query("UPDATE " . TABLE_REVIEWS . " SET reviews_rating = '" . xtc_db_input($reviews_rating) . "', last_modified = now() WHERE reviews_id = '" . xtc_db_input($reviews_id) . "'");
        xtc_db_query("UPDATE " . TABLE_REVIEWS_DESCRIPTION . " SET reviews_text = '" . xtc_db_input($reviews_text) . "' WHERE reviews_id = '" . xtc_db_input($reviews_id) . "'");
        xtc_redirect(xtc_href_link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $reviews_id));
        break;
      case 'deleteconfirm':
        $reviews_id = xtc_db_prepare_input($_GET['rID']);
        xtc_db_query("DELETE FROM " . TABLE_REVIEWS . "
                            WHERE reviews_id = '" . (int)$reviews_id . "'");
        xtc_db_query("DELETE FROM " . TABLE_REVIEWS_DESCRIPTION . "
                            WHERE reviews_id = '" . (int)$reviews_id . "'");
        xtc_redirect(xtc_href_link(FILENAME_REVIEWS, 'page=' . $_GET['page']));
        break;
    }
  }

require (DIR_WS_INCLUDES.'head.php');
?>
  <script type="text/javascript" src="includes/general.js"></script>
</head>
<body onload="SetFocus();">
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
              <td width="100%">
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                  <tr>
                    <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
                    <td class="pageHeading" align="right"><?php echo xtc_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
                  </tr>
                </table>
              </td>
            </tr>
            <?php
            if ($action == 'edit') {
              $rID = xtc_db_prepare_input($_GET['rID']);
              $reviews_query = xtc_db_query("-- /admin/reviews.php#1
                                              SELECT r.reviews_id,
                                                     r.products_id,
                                                     r.customers_name,
                                                     r.reviews_rating,
                                                     r.date_added,
                                                     r.last_modified,
                                                     r.reviews_read,
                                                     rd.reviews_text
                                                FROM " . TABLE_REVIEWS . " r,
                                                     " . TABLE_REVIEWS_DESCRIPTION . " rd
                                               WHERE r.reviews_id = '" . (int)$rID . "'
                                                 AND r.reviews_id = rd.reviews_id");
              $reviews = xtc_db_fetch_array($reviews_query);
              $products_query = xtc_db_query("-- /admin/reviews.php#2
                                              SELECT products_image
                                                FROM " . TABLE_PRODUCTS . "
                                               WHERE products_id = '" . $reviews['products_id'] . "'");
              $products = xtc_db_fetch_array($products_query);
              $products_name_query = xtc_db_query("-- /admin/reviews.php#3
                                                   SELECT products_name
                                                     FROM " . TABLE_PRODUCTS_DESCRIPTION . "
                                                    WHERE products_id = '" . (int)$reviews['products_id'] . "'
                                                      AND language_id = '" . (int)$_SESSION['languages_id'] . "'");
              $products_name = xtc_db_fetch_array($products_name_query);
              $rInfo_array = xtc_array_merge($reviews, $products, $products_name);
              $rInfo = new objectInfo($rInfo_array);
              ?>
              <?php echo xtc_draw_form('review', FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $_GET['rID'] . '&action=preview'); ?>
                <tr>
                  <td>
                    <table border="0" width="100%" cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="main" valign="top"><b><?php echo ENTRY_PRODUCT; ?></b> <?php echo $rInfo->products_name; ?><br />
                                                      <b><?php echo ENTRY_FROM; ?></b> <?php echo $rInfo->customers_name; ?><br />
                                                      <b><?php echo ENTRY_DATE; ?></b> <?php echo xtc_date_short($rInfo->date_added); ?></td>
                        <td class="main" align="left" valign="top"><?php echo xtc_product_thumb_image($rInfo->products_image, $rInfo->products_name, defined('SMALL_IMAGE_WIDTH') ? SMALL_IMAGE_WIDTH : '', defined('SMALL_IMAGE_HEIGHT') ? SMALL_IMAGE_HEIGHT : ''); ?></td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <tr>
                  <td>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="main" valign="top"><b><?php echo ENTRY_REVIEW; ?></b><br /><br /><?php echo xtc_draw_textarea_field('reviews_text', 'soft', '60', '15', $rInfo->reviews_text); ?></td>
                      </tr>
                      <tr>
                        <td class="smallText" align="left"><?php echo ENTRY_REVIEW_TEXT; ?></td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <tr>
                  <td><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
                </tr>
                <tr>
                  <td class="main"><b><?php echo ENTRY_RATING; ?></b>&nbsp;<?php echo TEXT_BAD; ?>&nbsp;<?php for ($i=1; $i<=5; $i++) echo xtc_draw_radio_field('reviews_rating', $i, '', $rInfo->reviews_rating) . '&nbsp;'; echo TEXT_GOOD; ?></td>
                </tr>
                <tr>
                  <td><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
                </tr>
                <tr>
                  <td align="right" class="main"><?php echo xtc_draw_hidden_field('reviews_id', $rInfo->reviews_id) . xtc_draw_hidden_field('products_id', $rInfo->products_id) . xtc_draw_hidden_field('customers_name', $rInfo->customers_name) . xtc_draw_hidden_field('products_name', $rInfo->products_name) . xtc_draw_hidden_field('products_image', $rInfo->products_image) . xtc_draw_hidden_field('date_added', $rInfo->date_added) . '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_PREVIEW . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $_GET['rID']) . '">' . BUTTON_CANCEL . '</a>'; ?></td>
                </tr>
              </form>
              <?php
            } elseif ($action == 'preview') {
              if (xtc_not_null($_POST)) {
                $rInfo = new objectInfo($_POST);
              } else {
                $reviews_query = xtc_db_query("-- /admin/reviews.php#4
                                               SELECT r.reviews_id,
                                                      r.products_id,
                                                      r.customers_name,
                                                      r.reviews_rating,
                                                      r.date_added,
                                                      r.last_modified,
                                                      r.reviews_read,
                                                      rd.reviews_text
                                                 FROM " . TABLE_REVIEWS . " r,
                                                      " . TABLE_REVIEWS_DESCRIPTION . " rd
                                                WHERE r.reviews_id = '" . $_GET['rID'] . "'
                                                  AND r.reviews_id = rd.reviews_id");
                $reviews = xtc_db_fetch_array($reviews_query);
                $products_query = xtc_db_query("-- /admin/reviews.php#5
                                                SELECT products_image
                                                  FROM " . TABLE_PRODUCTS . "
                                                 WHERE products_id = '" . $reviews['products_id'] . "'");
                $products = xtc_db_fetch_array($products_query);
                $products_name_query = xtc_db_query("-- /admin/reviews.php#6
                                                     SELECT products_name
                                                       FROM " . TABLE_PRODUCTS_DESCRIPTION . "
                                                      WHERE products_id = '" . (int)$reviews['products_id'] . "'
                                                        AND language_id = '" . (int)$_SESSION['languages_id'] . "'");
                $products_name = xtc_db_fetch_array($products_name_query);
                $rInfo_array = xtc_array_merge($reviews, $products, $products_name);
                $rInfo = new objectInfo($rInfo_array);
              }
              ?>
              <?php echo xtc_draw_form('update', FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $_GET['rID'] . '&action=update', 'post', 'enctype="multipart/form-data"'); ?>
                <tr>
                  <td>
                    <table border="0" width="100%" cellspacing="0" cellpadding="0">
                      <tr>
                        <td class="main" valign="top"><b><?php echo ENTRY_PRODUCT; ?></b> <?php echo $rInfo->products_name; ?><br /><b><?php echo ENTRY_FROM; ?></b> <?php echo $rInfo->customers_name; ?><br /><br /><b><?php echo ENTRY_DATE; ?></b> <?php echo xtc_date_short($rInfo->date_added); ?></td>
                        <td class="main" align="right" valign="top"><?php echo xtc_image(HTTP_CATALOG_SERVER . DIR_WS_CATALOG_IMAGES . $rInfo->products_image, $rInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="5" vspace="5"'); ?></td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <tr>
                  <td>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td valign="top" class="main"><b><?php echo ENTRY_REVIEW; ?></b><br /><br /><?php echo nl2br(xtc_db_output(xtc_break_string($rInfo->reviews_text, 15))); ?></td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <tr>
                  <td><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
                </tr>
                <tr>
                  <td class="main"><b><?php echo ENTRY_RATING; ?></b>&nbsp;<?php echo xtc_image(HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'templates/'. CURRENT_TEMPLATE .'/img/stars_' . $rInfo->reviews_rating . '.gif', sprintf(TEXT_OF_5_STARS, $rInfo->reviews_rating)); ?>&nbsp;<small>[<?php echo sprintf(TEXT_OF_5_STARS, $rInfo->reviews_rating); ?>]</small></td>
                </tr>
                <tr>
                  <td><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
                </tr>
                <?php
                if (xtc_not_null($_POST)) {
                  // Re-Post all POST'ed variables
                  reset($_POST);
                  while(list($key, $value) = each($_POST))
                    echo '<input type="hidden" name="' . $key . '" value="' . htmlspecialchars(stripslashes($value)) . '">';
                  ?>
                  <tr>
                    <td align="right" class="smallText"><?php echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $rInfo->reviews_id . '&action=edit') . '">' . BUTTON_BACK . '</a> <input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UPDATE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $rInfo->reviews_id) . '">' . BUTTON_CANCEL . '</a>'; ?></td>
                  </tr>
                  <?php
                } else {
                  if (isset($_GET['origin'])) {
                    $back_url = $_GET['origin'];
                    $back_url_params = '';
                  } else {
                    $back_url = FILENAME_REVIEWS;
                    $back_url_params = 'page=' . $_GET['page'] . '&rID=' . $rInfo->reviews_id;
                  }
                  ?>
                  <tr>
                    <td align="right"><?php echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link($back_url, $back_url_params, 'NONSSL') . '">' . BUTTON_BACK . '</a>'; ?></td>
                  </tr>
                  <?php
                }
                ?>
              </form>
              <?php
            } else {
              ?>
              <tr>
                <td>
                  <table border="0" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                      <td valign="top">
                        <table border="0" width="100%" cellspacing="0" cellpadding="2">
                          <tr class="dataTableHeadingRow">
                            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
                            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_RATING; ?></td>
                            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_DATE_ADDED; ?></td>
                            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                          </tr>
                          <?php
                          $reviews_query_raw = "-- /admin/reviews.php#7
                                                SELECT reviews_id,
                                                       products_id,
                                                       reviews_rating,
                                                       date_added,
                                                       last_modified
                                                  FROM " . TABLE_REVIEWS . "
                                                  ORDER BY date_added DESC";
                          $reviews_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $reviews_query_raw, $reviews_query_numrows);
                          $reviews_query = xtc_db_query($reviews_query_raw);
                          while ($reviews = xtc_db_fetch_array($reviews_query)) {
                            if ((!isset($_GET['rID']) || (isset($_GET['rID']) && ($_GET['rID'] == $reviews['reviews_id']))) && !isset($rInfo) ) {
                              $reviews_text_query = xtc_db_query("-- /admin/reviews.php#8
                                                                  SELECT r.customers_name,
                                                                         r.reviews_read,
                                                                         length(rd.reviews_text) AS reviews_text_size
                                                                    FROM " . TABLE_REVIEWS . " r,
                                                                         " . TABLE_REVIEWS_DESCRIPTION . " rd
                                                                   WHERE r.reviews_id = '" . (int)$reviews['reviews_id'] . "'
                                                                     AND r.reviews_id = rd.reviews_id");
                              $reviews_text = xtc_db_fetch_array($reviews_text_query);
                              $products_image_query = xtc_db_query("-- /admin/reviews.php#9
                                                                    SELECT products_image
                                                                      FROM " . TABLE_PRODUCTS . "
                                                                     WHERE products_id = '" . (int)$reviews['products_id'] . "'");
                              $products_image = xtc_db_fetch_array($products_image_query);
                              $products_name_query = xtc_db_query("-- /admin/reviews.php#10
                                                                   SELECT products_name
                                                                     FROM " . TABLE_PRODUCTS_DESCRIPTION . "
                                                                    WHERE products_id = '" . (int)$reviews['products_id'] . "'
                                                                      AND language_id = '" . (int)$_SESSION['languages_id'] . "'");
                              $products_name = xtc_db_fetch_array($products_name_query);
                              $reviews_average_query = xtc_db_query("-- /admin/reviews.php#11
                                                                     SELECT (avg(reviews_rating) / 5 * 100) AS average_rating
                                                                       FROM " . TABLE_REVIEWS . "
                                                                      WHERE products_id = '" . (int)$reviews['products_id'] . "'");
                              $reviews_average = xtc_db_fetch_array($reviews_average_query);
                              $review_info = xtc_array_merge($reviews_text, $reviews_average, $products_name);
                              $rInfo_array = xtc_array_merge($reviews, $review_info, $products_image);
                              $rInfo = new objectInfo($rInfo_array);
                            }
                            if (isset($rInfo) && is_object($rInfo) && ($reviews['reviews_id'] == $rInfo->reviews_id) ) {
                              echo '              <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $rInfo->reviews_id . '&action=preview') . '\'">' . "\n";
                            } else {
                              echo '              <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $reviews['reviews_id']) . '\'">' . "\n";
                            }
                              ?>
                              <td class="dataTableContent"><?php echo '<a href="' . xtc_href_link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $reviews['reviews_id'] . '&action=preview') . '">' . xtc_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW) . '</a>&nbsp;' . xtc_get_products_name($reviews['products_id']); ?></td>
                              <td class="dataTableContent" align="right"><?php echo xtc_image(HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'templates/'. CURRENT_TEMPLATE .'/img/stars_' . $reviews['reviews_rating'] . '.gif'); ?></td>
                              <td class="dataTableContent" align="right"><?php echo xtc_date_short($reviews['date_added']); ?></td>
                              <td class="dataTableContent" align="right"><?php if (isset($rInfo) && is_object($rInfo) && ($reviews['reviews_id'] == $rInfo->reviews_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $reviews['reviews_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                            </tr>
                            <?php
                          }
                          ?>
                          <tr>
                            <td colspan="4">
                              <table border="0" width="100%" cellspacing="0" cellpadding="2">
                                <tr>
                                  <td class="smallText" valign="top"><?php echo $reviews_split->display_count($reviews_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_REVIEWS); ?></td>
                                  <td class="smallText" align="right"><?php echo $reviews_split->display_links($reviews_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                                </tr>
                              </table>
                            </td>
                          </tr>
                        </table>
                      </td>
                      <?php
                      $heading = array();
                      $contents = array();
                      switch ($action) {
                        case 'delete':
                          $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_REVIEW . '</b>');
                          $contents = array('form' => xtc_draw_form('reviews', FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $rInfo->reviews_id . '&action=deleteconfirm'));
                          $contents[] = array('text' => TEXT_INFO_DELETE_REVIEW_INTRO);
                          $contents[] = array('text' => '<br /><b>' . $rInfo->products_name . '</b>');
                          $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $rInfo->reviews_id) . '">' . BUTTON_CANCEL . '</a>');
                          break;
                        default:
                          if (isset($rInfo) && is_object($rInfo)) {
                            $heading[] = array('text' => '<b>' . $rInfo->products_name . '</b>');
                            $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $rInfo->reviews_id . '&action=edit') . '">' . BUTTON_EDIT . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $rInfo->reviews_id . '&action=delete') . '">' . BUTTON_DELETE . '</a>');
                            $contents[] = array('text' => '<br />' . TEXT_INFO_DATE_ADDED . ' ' . xtc_date_short($rInfo->date_added));
                            if (xtc_not_null($rInfo->last_modified)) {
                              $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . xtc_date_short($rInfo->last_modified));
                            }
                            $contents[] = array('text' => '<br />' . xtc_product_thumb_image($rInfo->products_image, $rInfo->products_name));
                            $contents[] = array('text' => '<br />' . TEXT_INFO_REVIEW_AUTHOR . ' ' . $rInfo->customers_name);
                            $contents[] = array('text' => TEXT_INFO_REVIEW_RATING . ' ' . xtc_image(HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'templates/'. CURRENT_TEMPLATE .'/img/stars_' . $rInfo->reviews_rating . '.gif'));
                            $contents[] = array('text' => TEXT_INFO_REVIEW_READ . ' ' . $rInfo->reviews_read);
                            $contents[] = array('text' => '<br />' . TEXT_INFO_REVIEW_SIZE . ' ' . $rInfo->reviews_text_size . ' bytes');
                            $contents[] = array('text' => '<br />' . TEXT_INFO_PRODUCTS_AVERAGE_RATING . ' ' . number_format($rInfo->average_rating, 2) . '%');
                          }
                          break;
                      }
                      if ( (xtc_not_null($heading)) && (xtc_not_null($contents)) ) {
                        echo '            <td width="25%" valign="top">' . "\n";
                        echo box::infoBoxSt($heading, $contents); // cYbercOsmOnauT - 2011-02-07 - Changed methods of the classes box and tableBox to static
                        echo '            </td>' . "\n";
                      }
                      ?>
                    </tr>
                  </table>
                </td>
              </tr>
              <?php
            }
            ?>
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