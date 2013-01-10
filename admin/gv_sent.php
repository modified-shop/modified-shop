<?php
  /* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project (earlier name of osCommerce)
   (c) 2002-2003 osCommerce (gv_sent.php,v 1.2.2.1 2003/04/18); www.oscommerce.com
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c) Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  require('includes/application_top.php');
  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  //BOF - DokuMan - 2010-11-16 - delete sent vouchers
  $gv_id = isset($_GET['gid'])?xtc_db_prepare_input($_GET['gid']):'';
  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  if ($action && $gv_id != '') {
    switch ($action) {
      case 'deleteconfirm' :
        xtc_db_query("delete from ".TABLE_COUPONS." where coupon_id = '".xtc_db_input($gv_id)."'");
        xtc_db_query("delete from ".TABLE_COUPON_EMAIL_TRACK." where coupon_id = '".xtc_db_input($gv_id)."'");
        xtc_db_query("delete from ".TABLE_COUPON_REDEEM_TRACK." where coupon_id = '".xtc_db_input($gv_id)."'");
        xtc_redirect(xtc_href_link(FILENAME_GV_SENT, xtc_get_all_get_params(array ('gid', 'action'))));
        break;
    }
  }
  //EOF - DokuMan - 2010-11-16 - delete sent vouchers

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
              <td width="100%">
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                  <tr>
                    <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
                    <td class="pageHeading" align="right"><?php echo xtc_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
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
                          <td class="dataTableHeadingContent" align="left"><?php echo TABLE_HEADING_ID; ?></td>
                          <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SENDERS_NAME; ?></td>
                          <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ADDRESSEE; ?></td>
                          <td class="dataTableHeadingContent" align="left"><?php echo TABLE_HEADING_VOUCHER_VALUE; ?></td>
                          <td class="dataTableHeadingContent" align="left"><?php echo TABLE_HEADING_VOUCHER_CODE; ?></td>
                          <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_DATE_SENT; ?></td>
                          <td class="dataTableHeadingContent" align="center"><?php echo TEXT_VOUCHER_STATUS; ?></td>
                          <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                        </tr>
                        <?php
                        $gv_query_raw = "select c.coupon_amount, c.coupon_code, c.coupon_id, c.coupon_active, et.sent_firstname, et.sent_lastname, et.customer_id_sent, et.emailed_to, et.date_sent, c.coupon_id from " . TABLE_COUPONS . " c, " . TABLE_COUPON_EMAIL_TRACK . " et where c.coupon_id = et.coupon_id";
                        $gv_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $gv_query_raw, $gv_query_numrows);
                        $gv_query = xtc_db_query($gv_query_raw);
                        while ($gv_list = xtc_db_fetch_array($gv_query)) {
                          if ((!isset($_GET['gid']) || (isset($_GET['gid']) && ($_GET['gid'] == $gv_list['coupon_id']))) && !isset($gInfo)) {
                            $gInfo = new objectInfo($gv_list);
                          }
                          if (isset($gInfo) && is_object($gInfo) && ($gv_list['coupon_id'] == $gInfo->coupon_id) ) {
                            echo '              <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link('gv_sent.php', xtc_get_all_get_params(array('gid', 'action')) . 'gid=' . $gInfo->coupon_id . '&action=edit') . '\'">' . "\n";
                          } else {
                            echo '              <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link('gv_sent.php', xtc_get_all_get_params(array('gid', 'action')) . 'gid=' . $gv_list['coupon_id']) . '\'">' . "\n";
                          }
                            ?>
                            <td class="dataTableContent" align="left">&nbsp;<?php echo $gv_list['coupon_id']; ?></td>
                            <td class="dataTableContent" align="left">&nbsp;<?php echo $gv_list['sent_firstname'] . ' ' . $gv_list['sent_lastname']; ?></td>
                            <td class="dataTableContent" align="left">&nbsp;<?php echo $gv_list['emailed_to']; ?></td>
                            <td class="dataTableContent" align="left">&nbsp;<?php echo $currencies->format($gv_list['coupon_amount']); ?></td>
                            <td class="dataTableContent" align="left">&nbsp;<?php echo $gv_list['coupon_code']; ?></td>
                            <td class="dataTableContent" align="right"><?php echo xtc_date_short($gv_list['date_sent']); ?>&nbsp;</td>
                            <td class="dataTableContent" align="center">
                              <?php
                              if ($gv_list['coupon_active'] == 'N') {
                                echo xtc_image(DIR_WS_ICONS . 'icon_status_green.gif', STATUS_ICON_STATUS_GREEN, 10, 10);
                              } else {
                                echo xtc_image(DIR_WS_ICONS . 'icon_status_red.gif', STATUS_ICON_STATUS_RED, 10, 10);
                              }
                              ?>
                            </td>
                            <td class="dataTableContent" align="right"><?php if (isset($gInfo) && is_object($gInfo) && ($gv_list['coupon_id'] == $gInfo->coupon_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_GV_SENT, 'page=' . $_GET['page'] . '&gid=' . $gv_list['coupon_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                          </tr>
                          <?php
                        }
                        ?>
                        <tr>
                          <td colspan="5">
                            <table border="0" width="100%" cellspacing="0" cellpadding="2">
                              <tr>
                                <td class="smallText" valign="top"><?php echo $gv_split->display_count($gv_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_GIFT_VOUCHERS); ?></td>
                                <td class="smallText" align="right"><?php echo $gv_split->display_links($gv_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                              </tr>
                            </table>
                          </td>
                        </tr>
                      </table>
                    </td>
                    <?php
                    $heading = array();
                    $contents = array();
                    if (isset($gInfo) && is_object($gInfo)) {
                      $heading[] = array('text' => '[' . $gInfo->coupon_id . '] ' . ' ' . $currencies->format($gInfo->coupon_amount));
                      $redeem_query = xtc_db_query("select * from " . TABLE_COUPON_REDEEM_TRACK . " where coupon_id = '" . $gInfo->coupon_id . "'");
                      $redeemed = 'No';
                      if (xtc_db_num_rows($redeem_query) > 0)
                        $redeemed = 'Yes';
                      //BOF - DokuMan - 2010-11-16 - delete sent vouchers
                      $contents = array('form' => xtc_draw_form('gv', FILENAME_GV_SENT, xtc_get_all_get_params(array ('gid', 'action')).'gid='.$_GET['gid'].'&action=confirm'));
                      //EOF - DokuMan - 2010-11-16 - delete sent vouchers
                      $contents[] = array('text' => TEXT_INFO_SENDERS_ID . ' ' . $gInfo->customer_id_sent);
                      $contents[] = array('text' => TEXT_INFO_AMOUNT_SENT . ' ' . $currencies->format($gInfo->coupon_amount));
                      $contents[] = array('text' => TEXT_INFO_DATE_SENT . ' ' . xtc_date_short($gInfo->date_sent));
                      $contents[] = array('text' => TEXT_INFO_VOUCHER_CODE . ' ' . $gInfo->coupon_code);
                      $contents[] = array('text' => TEXT_INFO_EMAIL_ADDRESS . ' ' . $gInfo->emailed_to);
                      if ($redeemed=='Yes') {
                        $redeem = xtc_db_fetch_array($redeem_query);
                        $contents[] = array('text' => '<br />' . TEXT_INFO_DATE_REDEEMED . ' ' . xtc_date_short($redeem['redeem_date']));
                        $contents[] = array('text' => TEXT_INFO_IP_ADDRESS . ' ' . $redeem['redeem_ip']);
                        $contents[] = array('text' => TEXT_INFO_CUSTOMERS_ID . ' ' . $redeem['customer_id']);
                        //BOF - DokuMan - 2010-08-10 - show customer's remaining credit
                        $query_remainingcredit = xtc_db_query("SELECT * FROM " . TABLE_COUPON_GV_CUSTOMER  ." WHERE customer_id='". $redeem['customer_id']."'");
                        $array_remainingcredit = xtc_db_fetch_array($query_remainingcredit);
                        $remainingcredit = $array_remainingcredit['amount'];
                        $contents[] = array('text' => TEXT_INFO_REMAINING_CREDIT . ' ' . $currencies->format($remainingcredit));
                        //BOF - DokuMan - 2010-08-10 - show customer's remaining credit
                      } else {
                        $contents[] = array('text' => '<br />' . TEXT_INFO_NOT_REDEEMED);
                        //BOF - DokuMan - 2010-11-16 - delete sent vouchers
                        $contents[] = array ('align' => 'center', 'text' => '<br /><input type="submit" class="button" value="'.BUTTON_DELETE.'">');
                      }
                      if ( $action && $gv_id != '' ) {
                        switch ($action) {
                          case 'confirm' :
                            $heading[]  = array ('text'  => '<strong>'.TEXT_INFO_HEADING_DELETE_GV.'<strong>');
                            $contents   = array('form'   => xtc_draw_form('gv', FILENAME_GV_SENT, xtc_get_all_get_params(array ('gid', 'action')).'gid='.$_GET['gid'].'&action=deleteconfirm'));
                            $contents[] = array ('text'  => TEXT_DELETE_INTRO.'<br /><br />');
                            $contents[] = array ('align' => 'center', 'text' => '<br /><input type="submit" class="button" value="'.BUTTON_DELETE.'"><a class="button" href="'.xtc_href_link(FILENAME_GV_SENT, xtc_get_all_get_params(array ('gid', 'action')).'gid='.$gv_id).'">'.BUTTON_CANCEL.'</a>');
                            break;
                        }
                      }
                      //BOF - DokuMan - 2010-11-16 - delete sent vouchers
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