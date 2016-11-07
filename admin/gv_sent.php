<?php
   /* -----------------------------------------------------------------------------------------
   $Id: gv_sent.php 899 2005-04-29 02:40:57Z hhgag $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project (earlier name of osCommerce)
   (c) 2002-2003 osCommerce (gv_sent.php,v 1.2.2.1 2003/04/18); www.oscommerce.com

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c  Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org


   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


require('includes/application_top.php');

//display per page
$cfg_max_display_results_key = 'MAX_DISPLAY_GV_SENT_RESULTS';
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
        <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_news.png'); ?></div>
        <div class="flt-l">
          <div class="pageHeading"><?php echo HEADING_TITLE; ?></div>              
        </div>

        <table class="tableCenter">
          <tr>
            <td class="boxCenterLeft">
              <table class="tableBoxCenter collapse">
                <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SENDERS_NAME; ?></td>
                <td class="dataTableHeadingContent txta-c"><?php echo TABLE_HEADING_VOUCHER_VALUE; ?></td>
                <td class="dataTableHeadingContent txta-c"><?php echo TABLE_HEADING_VOUCHER_CODE; ?></td>
                <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_DATE_SENT; ?></td>		
                <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
              <?php
              $gv_query_raw = "select c.coupon_amount, c.coupon_code, c.coupon_id, et.sent_firstname, et.sent_lastname, et.customer_id_sent, et.emailed_to, et.date_sent, c.coupon_id from " . TABLE_COUPONS . " c, " . TABLE_COUPON_EMAIL_TRACK . " et where c.coupon_id = et.coupon_id";
              $gv_split = new splitPageResults($_GET['page'], $page_max_display_results, $gv_query_raw, $gv_query_numrows);
              $gv_query = xtc_db_query($gv_query_raw);
              while ($gv_list = xtc_db_fetch_array($gv_query)) {
                if (((!$_GET['gid']) || (@$_GET['gid'] == $gv_list['coupon_id'])) && (!$gInfo)) {
                $gInfo = new objectInfo($gv_list);
                }
                if ( (is_object($gInfo)) && ($gv_list['coupon_id'] == $gInfo->coupon_id) ) {
                  $tr_attributes = 'class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link('gv_sent.php', xtc_get_all_get_params(array('gid', 'action')) . 'gid=' . $gInfo->coupon_id . '&action=edit') .'\'"';
                } else {
                  $tr_attributes = 'class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link('gv_sent.php', xtc_get_all_get_params(array('gid', 'action')) . 'gid=' . $gv_list['coupon_id']) .'\'"';
                }
              ?>
              <tr <?php echo $tr_attributes;?>>
                <td class="dataTableContent"><?php echo $gv_list['sent_firstname'] . ' ' . $gv_list['sent_lastname']; ?></td>
                <td class="dataTableContent txta-c"><?php echo $currencies->format($gv_list['coupon_amount']); ?></td>
                <td class="dataTableContent txta-c"><?php echo $gv_list['coupon_code']; ?></td>
                <td class="dataTableContent txta-r"><?php echo xtc_date_short($gv_list['date_sent']); ?></td>
                <td class="dataTableContent txta-r"><?php if ( (is_object($gInfo)) && ($gv_list['coupon_id'] == $gInfo->coupon_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_GV_SENT, 'page=' . $_GET['page'] . '&gid=' . $gv_list['coupon_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
            <?php
              }
            ?>
            </table>
              
            <div class="smallText pdg2 flt-l"><?php echo $gv_split->display_count($gv_query_numrows, $page_max_display_results, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_GIFT_VOUCHERS); ?></div>
            <div class="smallText pdg2 flt-r"><?php echo $gv_split->display_links($gv_query_numrows, $page_max_display_results, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></div>
            <?php echo draw_input_per_page($PHP_SELF,$cfg_max_display_results_key,$page_max_display_results); ?>
          
          </td>
          <?php
            if (isset($gInfo) && is_object($gInfo)) {
              $heading = array();
              $contents = array();

              $heading[] = array('text' => '<b>[' . $gInfo->coupon_id . '] ' . ' ' . $currencies->format($gInfo->coupon_amount).'</b>');
              $redeem_query = xtc_db_query("select * from " . TABLE_COUPON_REDEEM_TRACK . " where coupon_id = '" . $gInfo->coupon_id . "'");
              $redeemed = 'No';
              if (xtc_db_num_rows($redeem_query) > 0) $redeemed = 'Yes';
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
              } else {
                $contents[] = array('text' => '<br />' . TEXT_INFO_NOT_REDEEMED);
              }
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