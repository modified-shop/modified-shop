<?php
  /* --------------------------------------------------------------
  $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce (coupon_admin.php); www.oscommerce.com
   (c) 2006 XT-Commerce (coupon_admin.php 1084 2005-07-23)

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c) Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org

   Add coupon_search 2018-11-14 by HE
   Add new coupon_type = 'T' : coupon_amount percent and shipping_free (c) 2017-05-31 by web28 - www.rpa-com.de
   Fix pagination and code cleanup (c) 2013-05-21 by web28 - www.rpa-com.de
   Fix html email and error handling  (c) 2011-07-07 by web28 - www.rpa-com.de

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  require_once('includes/application_top.php');

  require_once(DIR_FS_INC . 'xtc_wysiwyg.inc.php'); //web28- 2011-07-07 - Fix html email
  require_once(DIR_WS_CLASSES . 'currencies.php');
  require_once(DIR_FS_INC . 'xtc_php_mail.inc.php');

  //display per page
  $cfg_max_display_results_key = 'MAX_DISPLAY_COUPON_RESULTS';
  $page_max_display_results = xtc_cfg_save_max_display_results($cfg_max_display_results_key);

  $currencies = new currencies();

  $customers_statuses_array = xtc_get_customers_statuses(true);
  unset($customers_statuses_array[0]); //Admin
  //unset($customers_statuses_array[DEFAULT_CUSTOMERS_STATUS_ID_GUEST]); //Guest

  // initiate template engine for mail
  $smarty = new Smarty;

  $_GET['action'] = (isset($_GET['action']) ? $_GET['action'] : '');

  if (isset($_GET['selected_box'])) {
    $_GET['action'] = '';
    $_GET['old_action'] = '';
  }

  switch ($_GET['action']) {
  	case 'voucher_set_inactive':
      xtc_db_query("UPDATE " . TABLE_COUPONS . " SET coupon_active = 'N' WHERE coupon_id='".(int)$_GET['cid']."'");
      xtc_redirect(xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('action', 'uid', 'oldaction')) ));
      break;
		case 'voucher_set_active':
      xtc_db_query("UPDATE " . TABLE_COUPONS . " SET coupon_active = 'Y' WHERE coupon_id='".(int)$_GET['cid']."'");
      xtc_redirect(xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('action', 'uid', 'oldaction')) ));
      break;
    case 'confirmdelete':
      // delete coupon from DB
      xtc_db_query("DELETE FROM ".TABLE_COUPONS." WHERE coupon_id = '".(int)$_GET['cid']."'");
      xtc_db_query("DELETE FROM ".TABLE_COUPONS_DESCRIPTION." WHERE coupon_id = '".(int)$_GET['cid']."'");
      // delete coupon reports from DB
      xtc_db_query("DELETE FROM ".TABLE_COUPON_EMAIL_TRACK." WHERE coupon_id='".(int)$_GET['cid']."'");
			xtc_db_query("DELETE FROM ".TABLE_COUPON_REDEEM_TRACK." WHERE coupon_id='".(int)$_GET['cid']."'");
      xtc_redirect(xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('cid', 'action', 'uid', 'oldaction')) ));
      break;
    case 'update':
      $update_errors = 0;
      // get all _POST and validate
      $_POST['coupon_code'] = trim($_POST['coupon_code']);
      $languages = xtc_get_languages();
      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        $language_id = $languages[$i]['id'];
        $_POST['coupon_name'][$language_id] = trim($_POST['coupon_name'][$language_id]);
        if (!$_POST['coupon_name'][$language_id]) {
          $update_errors = 1;
          $messageStack->add(ERROR_NO_COUPON_NAME . $languages[$i]['name'], 'error');
        }
        $_POST['coupon_desc'][$language_id] = trim($_POST['coupon_desc'][$language_id]);
      }
      $_POST['coupon_amount'] = trim($_POST['coupon_amount']);
      $_POST['coupon_amount'] = preg_replace('/[^0-9.%]/', '', $_POST['coupon_amount']); //DokuMan - 2010-11-13 - allow numbers only
      if (!$_POST['coupon_name']) {
        $update_errors = 1;
        $messageStack->add(ERROR_NO_COUPON_NAME, 'error');
      }
      if ((!$_POST['coupon_amount']) && (!$_POST['coupon_free_ship'])) {
        $update_errors = 1;
        $messageStack->add(ERROR_NO_COUPON_AMOUNT, 'error');
      }
      if (strtotime($_POST['coupon_startdate']) > strtotime($_POST['coupon_finishdate'])) {
        $update_errors = 1;
        $messageStack->add(ERROR_COUPON_DATE, 'error');
      }
      if (!$_POST['coupon_code']) {
        $coupon_code = create_coupon_code();
      } else {
        $coupon_code = xtc_db_prepare_input($_POST['coupon_code']);
      }
      $query1 = xtc_db_query("SELECT coupon_code FROM " . TABLE_COUPONS . " WHERE coupon_code = '" . xtc_db_input($coupon_code) . "'");
      if (xtc_db_num_rows($query1) && $_POST['coupon_code'] && $_GET['oldaction'] != 'voucheredit')  {
        $update_errors = 1;
        $messageStack->add(ERROR_COUPON_EXISTS, 'error');
      }
      if ($update_errors != 0) {
        $_GET['action'] = $_GET['oldaction'];
      } else {
        $_GET['action'] = 'update_preview';
      }
      break;
    case 'update_confirm':
      if ( $_POST['back_x'] || $_POST['back_y'] || $_POST['back'] ) {
        $_GET['action'] = $_GET['oldaction'];
      } else {
        $coupon_type = "F";
        if (substr($_POST['coupon_amount'], -1) == '%') $coupon_type='P';
        if ($_POST['coupon_free_ship']) $coupon_type = 'S';

        if ($_POST['coupon_free_ship'] && substr($_POST['coupon_amount'], -1) == '%') {
          $coupon_type = 'T';
        }

        $_POST['coupon_amount'] = preg_replace('/[^0-9.]/', '', $_POST['coupon_amount']); //DokuMan - 2010-11-13 - allow numbers only

        $sql_data_array = array(
          'coupon_code' => xtc_db_prepare_input($_POST['coupon_code']),
          'coupon_amount' => xtc_db_prepare_input($_POST['coupon_amount']),
          'coupon_type' => xtc_db_prepare_input($coupon_type),
          'uses_per_coupon' => xtc_db_prepare_input((int)$_POST['coupon_uses_coupon']),
          'uses_per_user' => xtc_db_prepare_input((int)$_POST['coupon_uses_user']),
          'coupon_minimum_order' => xtc_db_prepare_input($_POST['coupon_min_order']),
          'restrict_to_products' => xtc_db_prepare_input($_POST['coupon_products']),
          'restrict_to_categories' => xtc_db_prepare_input($_POST['coupon_categories']),
          'restrict_to_customers' => xtc_db_prepare_input($_POST['coupon_groups']),
          'coupon_start_date' => xtc_db_prepare_input(date('Y-m-d', strtotime($_POST['coupon_startdate'])).' 00:00:00'),
          'coupon_expire_date' => xtc_db_prepare_input(date('Y-m-d', strtotime($_POST['coupon_finishdate'])).' 23:59:59'),
        );
        $languages = xtc_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $language_id = $languages[$i]['id'];
          $sql_data_marray[$i] = array('coupon_name' => xtc_db_prepare_input($_POST['coupon_name'][$language_id]),
                                       'coupon_description' => xtc_db_prepare_input($_POST['coupon_desc'][$language_id])
                                       );
        }

        if ($_GET['oldaction']=='voucheredit') {
          $sql_data_array['date_modified'] = 'now()';
          xtc_db_perform(TABLE_COUPONS, $sql_data_array, 'update', "coupon_id='" . (int)$_GET['cid']."'");
          for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $language_id = $languages[$i]['id'];
            //BOF - web28 - 2011-04-07 - BUGFIX no entry stored for previous deactivated languages
            $coupon_query = xtc_db_query("SELECT * FROM ".TABLE_COUPONS_DESCRIPTION." 
                                                  WHERE language_id = '".(int)$language_id."' 
                                                    AND coupon_id = '".(int)$_GET['cid']."'");
            if (xtc_db_num_rows($coupon_query) == 0) xtc_db_perform(TABLE_COUPONS_DESCRIPTION, array ('coupon_id' => (int)$_GET['cid'], 'language_id' => (int)$language_id));
            //EOF - web28 - 2011-04-07 - BUGFIX no entry stored for previous deactivated languages
            $sql_cdata_array = array(
                'coupon_name' => xtc_db_prepare_input($_POST['coupon_name'][$language_id]),
                'coupon_description' => xtc_db_prepare_input($_POST['coupon_desc'][$language_id])
              );
            xtc_db_perform(TABLE_COUPONS_DESCRIPTION, $sql_cdata_array, 'update', "coupon_id = '" . (int)$_GET['cid'] . "' AND language_id = '" . (int)$language_id . "'");
          }
        } else {
          $sql_data_array['date_created'] = 'now()';
          $query = xtc_db_perform(TABLE_COUPONS, $sql_data_array);
          $insert_id = xtc_db_insert_id();
          $_GET['cid'] = $insert_id;

          for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $language_id = $languages[$i]['id'];
            $sql_data_marray[$i]['coupon_id'] = $insert_id;
            $sql_data_marray[$i]['language_id'] = $language_id;
            xtc_db_perform(TABLE_COUPONS_DESCRIPTION, $sql_data_marray[$i]);
          }
      }
      xtc_redirect(xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('cid', 'action', 'uid', 'oldaction')) . 'cid=' . (int)$_GET['cid'] ));
    }
    break;
  }

require (DIR_WS_INCLUDES.'head.php');

if (USE_WYSIWYG=='true' && $_GET['action'] == 'email') {
 $query=xtc_db_query("SELECT code FROM ". TABLE_LANGUAGES ." WHERE languages_id='".(int)$_SESSION['languages_id']."'");
 $data=xtc_db_fetch_array($query);
 echo xtc_wysiwyg('gv_mail',$data['code']);
 }
 ?>
  <script type="text/javascript" src="includes/general.js"></script>
	<?php
	//jQueryDatepicker
	require (DIR_WS_INCLUDES.'javascript/jQueryDateTimePicker/datepicker.js.php');
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
      <?php
      switch ($_GET['action']) {
        case 'voucherreport':
        ?>
      <td class="boxCenter">
        <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_news.png'); ?></div>
        <div class="flt-l">
          <div class="pageHeading"><?php echo HEADING_TITLE; ?></div>
        </div>
        <div class="clear"></div>
        <table class="tableCenter">
          <tr>
            <td class="boxCenterLeft">
              <table class="tableBoxCenter collapse">
                <tr class="dataTableHeadingRow">
                  <td class="dataTableHeadingContent"><?php echo COUPON_ID; ?></td>
                  <td class="dataTableHeadingContent"><?php echo CUSTOMER_ID; ?></td>
                  <td class="dataTableHeadingContent"><?php echo CUSTOMER_NAME; ?></td>
                  <td class="dataTableHeadingContent"><?php echo IP_ADDRESS; ?></td>
                  <td class="dataTableHeadingContent"><?php echo REDEEM_DATE; ?></td>
                  <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                </tr>
                <?php
                $cc_query_raw = "SELECT * FROM " . TABLE_COUPON_REDEEM_TRACK . " WHERE coupon_id = '" . (int)$_GET['cid'] . "'";
                $cc_split = new splitPageResults($_GET['page'], $page_max_display_results, $cc_query_raw, $cc_query_numrows);
                $cc_query = xtc_db_query($cc_query_raw);
                while ($cc_list = xtc_db_fetch_array($cc_query)) {
                  if (((!$_GET['uid']) || (@$_GET['uid'] == $cc_list['unique_id'])) && (!$cInfo)) {
                    $cInfo = new objectInfo($cc_list);
                  }
                  if ( (is_object($cInfo)) && ($cc_list['unique_id'] == $cInfo->unique_id) ) {
                    $tr_attributes = 'class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('cid', 'action', 'uid')) . 'cid=' . $cInfo->coupon_id . '&action=voucherreport&uid=' . $cinfo->unique_id) . '\'"';
                  } else {
                    $tr_attributes = 'class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('cid', 'action', 'uid')) . 'cid=' . $cc_list['coupon_id'] . '&action=voucherreport&uid=' . $cc_list['unique_id']) . '\'"';
                  }
                  $customer_query = xtc_db_query("SELECT customers_firstname, customers_lastname FROM " . TABLE_CUSTOMERS . " WHERE customers_id = '" . $cc_list['customer_id'] . "'");
                  $customer = xtc_db_fetch_array($customer_query);
                ?>
                <tr <?php $tr_attributes;?>>
                  <td class="dataTableContent">&nbsp;<?php echo (int)$_GET['cid']; ?></td>
                  <td class="dataTableContent">&nbsp;<?php echo $cc_list['customer_id']; ?></td>
                  <td class="dataTableContent">&nbsp;<?php echo $customer['customers_firstname'] . ' ' . $customer['customers_lastname']; ?></td>
                  <td class="dataTableContent">&nbsp;<?php echo $cc_list['redeem_ip']; ?></td>
                  <td class="dataTableContent">&nbsp;<?php echo xtc_date_short($cc_list['redeem_date']); ?></td>
                  <td class="dataTableContent txta-r"><?php if (isset($cInfo) && is_object($cInfo) && ($cc_list['unique_id'] == $cInfo->unique_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('cid', 'action', 'uid')) . 'cid=' . $cc_list['coupon_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                </tr>
                <?php
                }
                ?>
              </table>
              <?php
              if (is_object($cc_split)) {
              ?>
                <div class="smallText pdg2 flt-l">&nbsp;<?php echo $cc_split->display_count($cc_query_numrows, MAX_DISPLAY_COUPON_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_COUPONS); ?>&nbsp;</div>
                <div class="smallText pdg2 flt-r">&nbsp;<?php echo $cc_split->display_links($cc_query_numrows, MAX_DISPLAY_COUPON_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page'],xtc_get_all_get_params(array('page','uid'))); ?>&nbsp;</div>
                <?php echo draw_input_per_page($PHP_SELF,$cfg_max_display_results_key,$page_max_display_results); ?>
              <?php
              }
              ?>
              <div class="clear"></div>
            </td>
          <?php
          $heading = array();
          $contents = array();
          $coupon_description_query = xtc_db_query("SELECT coupon_name FROM " . TABLE_COUPONS_DESCRIPTION . " WHERE coupon_id = '" . (int)$_GET['cid'] . "' AND language_id = '" . (int)$_SESSION['languages_id'] . "'");
          $coupon_desc = xtc_db_fetch_array($coupon_description_query);
          $count_customers = xtc_db_query("SELECT * FROM " . TABLE_COUPON_REDEEM_TRACK . " WHERE coupon_id = '" . (int)$_GET['cid'] . "' AND customer_id = '" . (int)$cInfo->customer_id . "'");

          $heading[] = array('text' => '<b>[' . (int)$_GET['cid'] . ']' . COUPON_NAME . ' ' . $coupon_desc['coupon_name'] . '</b>');
          $contents[] = array('text' => '<b>' . TEXT_REDEMPTIONS . '</b>');
          $contents[] = array('text' => TEXT_REDEMPTIONS_TOTAL . '=' . $cc_query_numrows);
          $contents[] = array('text' => TEXT_REDEMPTIONS_CUSTOMER . '=' . xtc_db_num_rows($count_customers));
          $contents[] = array('text' => '<a class="button" href="' . xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('action','uid','oldaction'))) . '">' . BUTTON_BACK . '</a>');
          ?>
          <td class="boxRight">
          <?php
          $box = new box;
          echo $box->infoBox($heading, $contents);
          echo '            </td>' . "\n";
          echo '         </tr>
                      </table>
                    </td>' . "\n";
    break;
  case 'preview_email':
    $coupon_query = xtc_db_query("SELECT coupon_code FROM " .TABLE_COUPONS . " WHERE coupon_id = '" . (int)$_GET['cid'] . "'");
    $coupon_result = xtc_db_fetch_array($coupon_query);
    $coupon_name_query = xtc_db_query("SELECT coupon_name FROM " . TABLE_COUPONS_DESCRIPTION . " WHERE coupon_id = '" . (int)$_GET['cid'] . "' AND language_id = '" . (int)$_SESSION['languages_id'] . "'");
    $coupon_name = xtc_db_fetch_array($coupon_name_query);
    switch ($_POST['customers_email_address']) {
    case '***':
      $mail_sent_to = TEXT_ALL_CUSTOMERS;
      break;
    case '**D':
      $mail_sent_to = TEXT_NEWSLETTER_CUSTOMERS;
      break;
    default:
      $mail_sent_to = $_POST['customers_email_address'];
      break;
    }
?>
    <td class="boxCenter">
      <div class="div_box">
       <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_news.png'); ?></div>
        <div class="flt-l">
          <div class="pageHeading"><?php echo HEADING_TITLE; ?></div>
        </div>
        <div class="clear"></div>
        <?php echo xtc_draw_form('mail', FILENAME_COUPON_ADMIN, 'action=send_email_to_user&cid=' . (int)$_GET['cid']); ?>
        <table class="tableConfig borderall">
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_CUSTOMER; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo $mail_sent_to; ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_FROM; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo encode_htmlspecialchars(stripslashes($_POST['from'])); ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_SUBJECT; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo encode_htmlspecialchars(stripslashes($_POST['subject'])); ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_MESSAGE; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo stripslashes($_POST['message']); ?></td>
          </tr>
        </table>
        <?php
        /* Re-Post all POST'ed variables */
        reset($_POST);
        foreach ($_POST as $key => $value) {
          if (!is_array($_POST[$key])) {
            echo xtc_draw_hidden_field($key, encode_htmlspecialchars(stripslashes($value)));
          }
        }
        ?>
        <div class="smallText pdg2 txta-r"><?php echo '<a class="button" href="' . xtc_href_link(FILENAME_COUPON_ADMIN) . '">' . BUTTON_CANCEL . '</a> <input type="submit" class="button" value="' . BUTTON_SEND_EMAIL . '"/>'; ?></div>
        </form>
      </div>
    </td>
<?php
    break;
  case 'email':
    $coupon_query = xtc_db_query("SELECT coupon_code FROM " . TABLE_COUPONS . " WHERE coupon_id = '" . (int)$_GET['cid'] . "'");
    $coupon_result = xtc_db_fetch_array($coupon_query);
    $coupon_name_query = xtc_db_query("SELECT coupon_name FROM " . TABLE_COUPONS_DESCRIPTION . " WHERE coupon_id = '" . (int)$_GET['cid'] . "' AND language_id = '" . (int)$_SESSION['languages_id'] . "'");
    $coupon_name = xtc_db_fetch_array($coupon_name_query);
?>
    <td class="boxCenter">
      <div class="div_box">
        <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_news.png'); ?></div>
        <div class="flt-l">
          <div class="pageHeading"><?php echo HEADING_TITLE; ?></div>
        </div>
        <div class="clear"></div>
        <?php
        $customers = array();
        $customers[] = array('id' => '', 'text' => TEXT_SELECT_CUSTOMER);
        $customers[] = array('id' => '***', 'text' => TEXT_ALL_CUSTOMERS);
        $customers[] = array('id' => '**D', 'text' => TEXT_NEWSLETTER_CUSTOMERS);
        $mail_query = xtc_db_query("SELECT customers_email_address, customers_firstname, customers_lastname FROM " . TABLE_CUSTOMERS . " order by customers_lastname");
        while($customers_values = xtc_db_fetch_array($mail_query)) {
          $customers[] = array('id' => $customers_values['customers_email_address'],
                               'text' => $customers_values['customers_lastname'] . ', ' . $customers_values['customers_firstname'] . ' (' . $customers_values['customers_email_address'] . ')');
        }
        ?>

        <?php echo xtc_draw_form('mail', FILENAME_COUPON_ADMIN, 'action=preview_email&cid='. (int)$_GET['cid']); ?>

        <table class="tableConfig borderall">
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_COUPON; ?>&nbsp;&nbsp;</td>
            <td class="dataTableConfig col-single-right"><?php echo $coupon_name['coupon_name']; ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_CUSTOMER; ?>&nbsp;&nbsp;</td>
            <td class="dataTableConfig col-single-right"><?php echo xtc_draw_pull_down_menu('customers_email_address', $customers, $_GET['customer']);?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_FROM; ?>&nbsp;&nbsp;</td>
            <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('from', EMAIL_FROM); ?></td>
          </tr>
          <?php
          /*
                        <tr>
                          <td class="main"><?php echo TEXT_RESTRICT; ?>&nbsp;&nbsp;</td>
                          <td><?php echo xtc_draw_checkbox_field('customers_restrict', $customers_restrict);?></td>
                        </tr>
                        <tr>
                          <td colspan="2"><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
                        </tr>
          */
          ?>
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_SUBJECT; ?>&nbsp;&nbsp;</td>
            <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('subject',$_POST['subject']); ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_MESSAGE; ?>&nbsp;&nbsp;</td>
            <td class="dataTableConfig col-single-right"><?php echo xtc_draw_textarea_field('message', 'soft', '60', '15', $_POST['message']); ?></td>
          </tr>
        </table>
        <br/>
        <div class="smallText mrg5">
          <?php echo '<a class="button" href="' . xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('action','oldaction'))) .'">'. BUTTON_CANCEL . '</a>'; ?>
          <?php echo '<input type="submit" class="button flt-r" value="' . BUTTON_SEND_EMAIL . '"/>'; ?>
        </div>
      </form>
      </div>
    </td>
    </div>
<?php
    break;
  case 'update_preview':
?>
    <td class="boxCenter">
      <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_news.png'); ?></div>
      <div class="flt-l">
        <div class="pageHeading"><?php echo HEADING_TITLE; ?></div>
      </div>
      <div class="clear"></div>
      <?php echo xtc_draw_form('coupon', FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('action','uid')) . 'action=update_confirm'); ?>
      <?php // BOF - web28 - 2011-03-11 - new table design ?>
      <table class="tableConfirm borderall collapse">
        <?php
        $languages = xtc_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $language_id = $languages[$i]['id'];
            $lang_img = '<span style="float:right; padding-top:2px;">'. xtc_image(DIR_WS_LANGUAGES . $languages[$i]['directory'].'/admin/images/'.$languages[$i]['image'], $languages[$i]['name']) . '</span>';
        ?>
        <tr>
          <td class="dataTableConfig col-left"><?php echo COUPON_NAME. $lang_img ; ?></td>
          <td class="dataTableConfig col-single-right"><?php echo $_POST['coupon_name'][$language_id]; ?>&nbsp;</td>
        </tr>
        <?php
        }
        $languages = xtc_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $language_id = $languages[$i]['id'];
            $lang_img = '<span style="float:right; padding-top:2px;">'. xtc_image(DIR_WS_LANGUAGES . $languages[$i]['directory'].'/admin/images/'.$languages[$i]['image'], $languages[$i]['name']) . '</span>';
        ?>
        <tr>
          <td class="dataTableConfig col-left"><?php echo COUPON_DESC. $lang_img ; ?></td>
          <td class="dataTableConfig col-single-right"><?php echo $_POST['coupon_desc'][$language_id]; ?>&nbsp;</td>
        </tr>
        <?php
        }
        ?>
        <tr>
          <td class="dataTableConfig col-left"><?php echo COUPON_AMOUNT; ?></td>
          <td class="dataTableConfig col-single-right"><?php echo $_POST['coupon_amount']; ?>&nbsp;</td>
        </tr>
        <tr>
          <td class="dataTableConfig col-left"><?php echo COUPON_MIN_ORDER; ?></td>
          <td class="dataTableConfig col-single-right"><?php echo $_POST['coupon_min_order']; ?>&nbsp;</td>
        </tr>
        <tr>
          <td class="dataTableConfig col-left"><?php echo COUPON_FREE_SHIP; ?></td>
          <td class="dataTableConfig col-single-right"><?php echo $_POST['coupon_free_ship'] ? TEXT_FREE_SHIPPING : TEXT_NO_FREE_SHIPPING; ?></td>
        </tr>
        <tr>
          <td class="dataTableConfig col-left"><?php echo COUPON_CODE; ?></td>
          <td class="dataTableConfig col-single-right"><?php echo $coupon_code; ?>&nbsp;</td>
        </tr>
        <tr>
          <td class="dataTableConfig col-left"><?php echo COUPON_USES_COUPON; ?></td>
          <td class="dataTableConfig col-single-right"><?php echo $_POST['coupon_uses_coupon']; ?>&nbsp;</td>
        </tr>
        <tr>
          <td class="dataTableConfig col-left"><?php echo COUPON_USES_USER; ?></td>
          <td class="dataTableConfig col-single-right"><?php echo $_POST['coupon_uses_user']; ?>&nbsp;</td>
        </tr>
         <tr>
          <td class="dataTableConfig col-left"><?php echo COUPON_PRODUCTS; ?></td>
          <td class="dataTableConfig col-single-right"><?php echo $_POST['coupon_products']; ?>&nbsp;</td>
        </tr>
        <tr>
          <td class="dataTableConfig col-left"><?php echo COUPON_CATEGORIES; ?></td>
          <td class="dataTableConfig col-single-right"><?php echo $_POST['coupon_categories']; ?>&nbsp;</td>
        </tr>
        <tr>
          <td class="dataTableConfig col-left"><?php echo COUPON_CUSTOMERS; ?></td>
          <td class="dataTableConfig col-single-right">
            <?php 
              if (!isset($_POST['coupon_groups']) 
                  || !is_array($_POST['coupon_groups']) 
                  || count($_POST['coupon_groups']) < 1 
                  || $_POST['coupon_groups'][0] == 'all'
                  )
              {
                echo TXT_ALL;
              } else {
                foreach ($_POST['coupon_groups'] as $customers_status_id) {                
                  echo $customers_statuses_array[$customers_status_id]['text'].'</br>';
                }
              }
            ?>
          </td>
        </tr>        
        <tr>
          <td class="dataTableConfig col-left"><?php echo COUPON_STARTDATE; ?></td>
          <?php
              $start_date = xtc_date_short($_POST['coupon_startdate']);
          ?>
         <td class="dataTableConfig col-single-right"><?php echo $start_date; ?>&nbsp;</td>
        </tr>
        <tr>
          <td class="dataTableConfig col-left"><?php echo COUPON_FINISHDATE; ?></td>
          <?php
              $finish_date = xtc_date_short($_POST['coupon_finishdate']);
          ?>
          <td class="dataTableConfig col-single-right"><?php echo $finish_date; ?>&nbsp;</td>
        </tr>
      </table>
      <?php
      $languages = xtc_get_languages();
      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        $language_id = $languages[$i]['id'];
        echo xtc_draw_hidden_field('coupon_name[' . $languages[$i]['id'] . ']', stripslashes($_POST['coupon_name'][$language_id])).PHP_EOL;
        echo xtc_draw_hidden_field('coupon_desc[' . $languages[$i]['id'] . ']', stripslashes($_POST['coupon_desc'][$language_id])).PHP_EOL;
      }
      echo xtc_draw_hidden_field('coupon_amount', $_POST['coupon_amount']).PHP_EOL;
      echo xtc_draw_hidden_field('coupon_min_order', $_POST['coupon_min_order']).PHP_EOL;
      echo xtc_draw_hidden_field('coupon_free_ship', $_POST['coupon_free_ship']).PHP_EOL;
      echo xtc_draw_hidden_field('coupon_code', $_POST['coupon_code']).PHP_EOL;
      echo xtc_draw_hidden_field('coupon_uses_coupon', $_POST['coupon_uses_coupon']).PHP_EOL;
      echo xtc_draw_hidden_field('coupon_uses_user', $_POST['coupon_uses_user']).PHP_EOL;
      echo xtc_draw_hidden_field('coupon_products', $_POST['coupon_products']).PHP_EOL;
      echo xtc_draw_hidden_field('coupon_categories', $_POST['coupon_categories']).PHP_EOL;
      echo xtc_draw_hidden_field('coupon_groups', ((isset($_POST['coupon_groups']) && $_POST['coupon_groups'][0] != 'all') ? implode(',', $_POST['coupon_groups']) : '')).PHP_EOL;
      echo xtc_draw_hidden_field('coupon_startdate', $_POST['coupon_startdate']).PHP_EOL;
      echo xtc_draw_hidden_field('coupon_finishdate', $_POST['coupon_finishdate']).PHP_EOL;
      ?>
      <div class="mrg5">
      <?php echo '<input type="submit" class="button" value="' . BUTTON_CONFIRM . '"/>'; ?>
      <?php echo '<input type="submit" name="back" class="button" value="' . BUTTON_BACK . '"/>'; ?>
      </div>
      </form>
    </td>
<?php
    break;
  case 'voucheredit':
    $languages = xtc_get_languages();
    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
      $language_id = $languages[$i]['id'];
      $coupon_query = xtc_db_query("SELECT coupon_name,coupon_description FROM " . TABLE_COUPONS_DESCRIPTION . " WHERE coupon_id = '" .  (int)$_GET['cid'] . "' AND language_id = '" . (int)$language_id . "'");
      $coupon = xtc_db_fetch_array($coupon_query);
      $coupon_name[$language_id] = $coupon['coupon_name'];
      $coupon_desc[$language_id] = $coupon['coupon_description'];
    }
    $coupon_query = xtc_db_query("SELECT * FROM " . TABLE_COUPONS . " WHERE coupon_id = '" . (int)$_GET['cid'] . "'");
    $coupon = xtc_db_fetch_array($coupon_query);
    $coupon_amount = $coupon['coupon_amount'];
    if ($coupon['coupon_type']=='P') {
      $coupon_amount .= '%';
    }
    if ($coupon['coupon_type']=='S') {
      $coupon_free_ship = true;
    }
    if ($coupon['coupon_type']=='T') {
      $coupon_amount .= '%';
      $coupon_free_ship = true;
    }
    $coupon_min_order = $coupon['coupon_minimum_order'];
    $coupon_code = $coupon['coupon_code'];
    $coupon_uses_coupon = $coupon['uses_per_coupon'];
    $coupon_uses_user = $coupon['uses_per_user'];
    $coupon_products = $coupon['restrict_to_products'];
    $coupon_categories = $coupon['restrict_to_categories'];
    $coupon_groups = explode(',', $coupon['restrict_to_customers']);
    $coupon_startdate = date('Y-m-d', strtotime($coupon['coupon_start_date']));
    $coupon_finishdate = date('Y-m-d', strtotime($coupon['coupon_expire_date']));

  case 'new':
    if (isset($_POST['coupon_amount'])) $coupon_amount = xtc_db_prepare_input($_POST['coupon_amount']);
    if (isset($_POST['coupon_min_order'])) $coupon_min_order = xtc_db_prepare_input($_POST['coupon_min_order']);
    if (isset($_POST['coupon_free_ship'])) $coupon_free_ship = xtc_db_prepare_input($_POST['coupon_free_ship']);
    if (isset($_POST['coupon_code'])) $coupon_code = xtc_db_prepare_input($_POST['coupon_code']);
    if (isset($_POST['coupon_uses_coupon'])) $coupon_uses_coupon = xtc_db_prepare_input($_POST['coupon_uses_coupon']);
    if (isset($_POST['coupon_uses_user'])) $coupon_uses_user = xtc_db_prepare_input($_POST['coupon_uses_user']);
    if (isset($_POST['coupon_products'])) $coupon_products = xtc_db_prepare_input($_POST['coupon_products']);
    if (isset($_POST['coupon_categories'])) $coupon_categories = xtc_db_prepare_input($_POST['coupon_categories']);
    if (isset($_POST['coupon_startdate'])) $coupon_startdate = xtc_db_prepare_input($_POST['coupon_startdate']);
    if (isset($_POST['coupon_finishdate'])) $coupon_finishdate = xtc_db_prepare_input($_POST['coupon_finishdate']);
    if (isset($_POST['coupon_groups'])) $coupon_groups = ((is_array($_POST['coupon_groups'])) ? $_POST['coupon_groups'] : explode(',', xtc_db_prepare_input($_POST['coupon_groups'])));
    
    if (isset($coupon_groups)) {
      $coupon_groups = array_filter($coupon_groups);
    }
    
    // set some defaults
    if (!isset($coupon_uses_user)) {
      $coupon_uses_user = 1;
		} elseif(empty($coupon_uses_user)) {
      $coupon_uses_user='';
		}
		if(empty($coupon_uses_coupon)) {
      $coupon_uses_coupon = '';
    }
    // coupon_startdate, coupon_finishdate
    if (!$coupon_startdate) {
      $coupon_startdate = date('Y-m-d');
    }
    if (!$coupon_finishdate) {
      $coupon_finishdate = date('Y-m-d', strtotime('+1 year'));
    }

    $input_name = '';
    $input_desc = '';
    $languages = xtc_get_languages();
    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
      $language_id = $languages[$i]['id'];
      if (isset($_POST['coupon_name'][$language_id])) {
        $coupon_name[$language_id] = xtc_db_prepare_input($_POST['coupon_name'][$language_id]);
      }
      if (isset($_POST['coupon_desc'][$language_id])) {
        $coupon_desc[$language_id] = xtc_db_prepare_input($_POST['coupon_desc'][$language_id]);
      }
      $lang_img = '<span style="float:left; padding-top:2px;">'. xtc_image(DIR_WS_LANGUAGES . $languages[$i]['directory'].'/admin/images/'.$languages[$i]['image'], $languages[$i]['name']) . '</span>';
      $input_name .= $lang_img . '&nbsp;'. xtc_draw_input_field('coupon_name[' . $languages[$i]['id'] . ']', $coupon_name[$language_id]) . '&nbsp;<br />';
      $input_desc .= $lang_img . '&nbsp;'. xtc_draw_textarea_field('coupon_desc[' . $languages[$i]['id'] . ']','physical','24','3', $coupon_desc[$language_id], 'class="textareaModule"') . '&nbsp;<br />';
    }
?>
    <td class="boxCenter">
      <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_news.png'); ?></div>
      <div class="flt-l">
        <div class="pageHeading"><?php echo HEADING_TITLE; ?></div>
      </div>
      <div class="clear"></div>
      <?php
      echo xtc_draw_form('coupon', FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('action', 'oldaction', 'cid')) . 'action=update&oldaction='.$_GET['action'] . ((isset($_GET['cid']) && $_GET['cid'] > 0) ? '&cid=' . (int)$_GET['cid'] : ''), 'post', 'enctype="multipart/form-data"');
      ?>
        <table class="tableConfig">
          <tr>
            <td class="dataTableConfig col-left"><?php echo COUPON_NAME; ?></td>
            <td class="dataTableConfig col-middle"><?php echo $input_name; ?></td>
            <td class="dataTableConfig col-right"><?php echo COUPON_NAME_HELP; ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo COUPON_DESC; ?></td>
            <td class="dataTableConfig col-middle"><?php echo $input_desc; ?></td>
            <td class="dataTableConfig col-right"><?php echo COUPON_DESC_HELP; ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo COUPON_AMOUNT; ?></td>
            <td class="dataTableConfig col-middle"><?php echo xtc_draw_input_field('coupon_amount', $coupon_amount); ?></td>
            <td class="dataTableConfig col-right"><?php echo COUPON_AMOUNT_HELP; ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo COUPON_MIN_ORDER; ?></td>
            <td class="dataTableConfig col-middle"><?php echo xtc_draw_input_field('coupon_min_order', $coupon_min_order); ?></td>
            <td class="dataTableConfig col-right"><?php echo COUPON_MIN_ORDER_HELP; ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo COUPON_FREE_SHIP; ?></td>
            <td class="dataTableConfig col-middle"><?php echo xtc_draw_checkbox_field('coupon_free_ship', $coupon_free_ship); ?></td>
            <td class="dataTableConfig col-right"><?php echo COUPON_FREE_SHIP_HELP; ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo COUPON_CODE; ?></td>
            <td class="dataTableConfig col-middle"><?php echo xtc_draw_input_field('coupon_code', $coupon_code); ?></td>
            <td class="dataTableConfig col-right"><?php echo COUPON_CODE_HELP; ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo COUPON_USES_COUPON; ?></td>
            <td class="dataTableConfig col-middle"><?php echo xtc_draw_input_field('coupon_uses_coupon', $coupon_uses_coupon); ?></td>
            <td class="dataTableConfig col-right"><?php echo COUPON_USES_COUPON_HELP; ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo COUPON_USES_USER; ?></td>
            <td class="dataTableConfig col-middle"><?php echo xtc_draw_input_field('coupon_uses_user', $coupon_uses_user); ?></td>
            <td class="dataTableConfig col-right"><?php echo COUPON_USES_USER_HELP; ?></td>
          </tr>
           <tr>
            <td class="dataTableConfig col-left"><?php echo COUPON_PRODUCTS; ?></td>
            <td class="dataTableConfig col-middle"><?php echo xtc_draw_input_field('coupon_products', $coupon_products); ?> <a href="<?php echo xtc_href_link('validproducts.php', '' , 'NONSSL');?>" target="_blank" onclick="window.open('validproducts.php', 'Valid_Products', 'scrollbars=yes,resizable=yes,menubar=yes,width=600,height=600'); return false"><?php echo TEXT_VIEW_SHORT;?></a></td>
            <td class="dataTableConfig col-right"><?php echo COUPON_PRODUCTS_HELP; ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo COUPON_CATEGORIES; ?></td>
            <td class="dataTableConfig col-middle"><?php echo xtc_draw_input_field('coupon_categories', $coupon_categories); ?> <a href="<?php echo xtc_href_link('validcategories.php', '' , 'NONSSL');?>" target="_blank" onclick="window.open('validcategories.php', 'Valid_Categories', 'scrollbars=yes,resizable=yes,menubar=yes,width=600,height=600'); return false"><?php echo TEXT_VIEW_SHORT;?></a></td>
            <td class="dataTableConfig col-right"><?php echo COUPON_CATEGORIES_HELP; ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo COUPON_CUSTOMERS; ?></td>
            <td class="dataTableConfig col-middle">
              <?php                      
                echo '<label>' . xtc_draw_checkbox_field('coupon_groups[]', 'all', ((!isset($coupon_groups) || !is_array($coupon_groups) || count($coupon_groups) < 1 || in_array('all', $coupon_groups)) ? true : false),'', 'id="cgAll"').TXT_ALL.'</label><br />';                
                foreach ($customers_statuses_array as $customers_statuses) {
                  echo '<label>'.  xtc_draw_checkbox_field('coupon_groups[]', $customers_statuses['id'], ((isset($coupon_groups) && in_array($customers_statuses['id'], $coupon_groups)) ? true : false), '', 'id="cg'.$customers_statuses['id'].'"') . $customers_statuses['text'].'</label><br />';
                }
              ?>
            </td>
            <td class="dataTableConfig col-right"><?php echo COUPON_CUSTOMERS_HELP; ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo COUPON_STARTDATE; ?></td>
            <td class="dataTableConfig col-middle nobr"><?php echo xtc_draw_input_field('coupon_startdate', $coupon_startdate ,'id="Datepicker1"'); ?></td>
            <td class="dataTableConfig col-right"><?php echo COUPON_STARTDATE_HELP.COUPON_DATE_START_TT; ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo COUPON_FINISHDATE; ?></td>
            <td class="dataTableConfig col-middle nobr"><?php echo xtc_draw_input_field('coupon_finishdate', $coupon_finishdate ,'id="Datepicker2"'); ?></td>
            <td class="dataTableConfig col-right"><?php echo COUPON_FINISHDATE_HELP.COUPON_DATE_END_TT; ?></td>
          </tr>
        </table>
        <div class="mrg5">
        <?php echo '<input type="submit" class="button" value="' . BUTTON_PREVIEW . '"/>'; ?>
        <?php echo '&nbsp;&nbsp;<a class="button" href="' . xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('action','oldaction'))) .'">'. BUTTON_CANCEL . '</a>'; ?>
        </div>
        </form>
    </td>
      </tr>
    </table>
  </td>
<?php
    break;
  default:
?>
    <td class="boxCenter">
        <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_news.png'); ?></div>
	    <!--BOF Adding coupon_search HE-->
	    <div>
		    <div class="pageHeading"><?php echo HEADING_TITLE; ?></div>
	    </div>
	    <div style="width: 100%; margin: 0 0 10px 0;">
		    <div class="main" style="display:inline-block; padding: 5px; vertical-align:top;">
			    <?php echo xtc_draw_form('status', FILENAME_COUPON_ADMIN, '', 'get');
			    $status_array[] = array('id' => 'Y', 'text' => TEXT_COUPON_ACTIVE);
			    $status_array[] = array('id' => 'N', 'text' => TEXT_COUPON_INACTIVE);
			    $status_array[] = array('id' => '*', 'text' => TEXT_COUPON_ALL);
			    $status = $_GET['status'] ? xtc_db_prepare_input($_GET['status']) : 'Y';
			    echo HEADING_TITLE_STATUS . ' &nbsp; ' . xtc_draw_pull_down_menu('status', $status_array, $status, '');
			    //echo HEADING_TITLE_STATUS . ' ' . xtc_draw_pull_down_menu('status', $status_array, $status, 'onChange="this.form.submit();"');
			    $input_id = !isset($_POST['input_id']) ? !isset($_GET['input_id']) ? '' : (int)$_GET['input_id'] : (int)$_POST['input_id'];
			    echo ' &nbsp; cID: <input type="text" name="input_id" value="'.$input_id.'"/> &nbsp; ';
			    $input_code = !isset($_POST['input_code']) ? !isset($_GET['input_code']) ? '' : xtc_db_input($_GET['input_code']) : xtc_db_input($_POST['input_code']);
			    echo ' &nbsp; Code: <input type="text" name="input_code" value="'.$input_code.'"/> &nbsp; ';
			    $input_name = !isset($_POST['input_name']) ? !isset($_GET['input_name']) ? '' : xtc_db_input($_GET['input_name']) : xtc_db_input($_POST['input_name']);
			    echo ' &nbsp; Name: <input type="text" name="input_name" value="'.$input_name.'"/> &nbsp; ';
			    echo '<input class="button no_top_margin" style="vertical-align:top;" type="submit" name="btnSearch" value="'.BUTTON_SEARCH.'"/>';
			    ?>
			    </form>
		    </div>
		    <div class="main" style="display:inline-block; padding:5px; vertical-align:top; margin-left:50px"><a class="button no_top_margin" href="<?php echo xtc_href_link(FILENAME_COUPON_ADMIN, 'action=new'); ?>"><?php echo BUTTON_INSERT; ?></a></div>
	    </div>
	    <!--EOF Adding coupon_search HE-->
        <table class="tableCenter">
          <tr>
            <td class="boxCenterLeft">
              <?php
              if ($_GET['action'] == '' && !defined('MODULE_ORDER_TOTAL_COUPON_STATUS')) {
                ?>
                <div class="main important_info">
                  <?php echo TEXT_OT_COUPON_STATUS_INFO;?>
                </div>
                <?php
              }
              ?>
              <table class="tableBoxCenter collapse">
                <tr class="dataTableHeadingRow">
                  <td class="dataTableHeadingContent" style="width:25px"><?php echo COUPON_ID; ?></td>
                  <td class="dataTableHeadingContent"><?php echo COUPON_NAME; ?></td>
                  <td class="dataTableHeadingContent" style="width:110px"><?php echo COUPON_AMOUNT; ?></td>
                  <td class="dataTableHeadingContent" style="width:110px"><?php echo TEXT_COUPON_MINORDER; ?></td>
                  <td class="dataTableHeadingContent" style="width:80px"><?php echo COUPON_CODE; ?></td>
                  <td class="dataTableHeadingContent txta-c" style="width:70px"><?php echo TEXT_COUPON_STATUS; ?></td>
                  <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                </tr>
                <?php
                $coupon_active = $status != '*' ? " AND coupon_active = '" . xtc_db_input($status)."'" : '';
                //BOF Adding coupon_search HE
                if($input_code != ''){
	                $coupon_active .= " AND c.coupon_code LIKE '%".$input_code."%'";
                }
                if(($input_id != '') && ($input_id > 0)){
	                $coupon_active .= " AND c.coupon_id = '".$input_id."'";
                }
                $sqlJoin = '';
                if($input_name != ''){
	                $coupon_active .= " AND cd.coupon_name LIKE '%".$input_name."%'";
	                $sqlJoin = " LEFT JOIN ".TABLE_COUPONS_DESCRIPTION." cd ON (c.coupon_id = cd.coupon_id AND cd.language_id = '" . (int)$_SESSION['languages_id'] . "')";
                }
                $cc_query_raw = "SELECT c.*
                                   FROM " . TABLE_COUPONS ." c
                                   ".$sqlJoin."
                                  WHERE c.coupon_type != 'G' 
                                        $coupon_active
                                  ORDER BY c.coupon_id DESC";
                //EOF Adding coupon_search HE
                $cc_split = new splitPageResults($_GET['page'], $page_max_display_results, $cc_query_raw, $cc_query_numrows);
                $cc_query = xtc_db_query($cc_query_raw);
                while ($cc_list = xtc_db_fetch_array($cc_query)) {
                  if ((!isset($_GET['cid']) || (isset($_GET['cid']) && ($_GET['cid'] == $cc_list['coupon_id']))) && !isset($cInfo)) {
                    $cInfo = new objectInfo($cc_list);
                  }
                  if (isset($cInfo) && is_object($cInfo) && ($cc_list['coupon_id'] == $cInfo->coupon_id) ) {
                    $tr_attributes = 'class="dataTableRowSelected" onmouseover="this.style.cursor=\'default\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('cid', 'action', 'oldaction')) . 'cid=' . $cInfo->coupon_id . '&action=edit') . '\'"';
                  } else {
                    $tr_attributes = 'class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'default\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('cid', 'action', 'oldaction')) . 'cid=' . $cc_list['coupon_id']) . '\'"';
                  }
                  $coupon_description_query = xtc_db_query("SELECT coupon_name FROM " . TABLE_COUPONS_DESCRIPTION . " WHERE coupon_id = '" . (int)$cc_list['coupon_id'] . "' AND language_id = '" . (int)$_SESSION['languages_id'] . "'");
                  $coupon_desc = xtc_db_fetch_array($coupon_description_query);
                  if ($cc_list['coupon_type'] == 'P') {
                    $coupon_amount = number_format($cc_list['coupon_amount'], 2) . '%';
                  } elseif ($cc_list['coupon_type'] == 'S') {
                    $coupon_amount = (($cc_list['coupon_amount'] > 0) ? $currencies->format($cc_list['coupon_amount']) . ' + ' : '') . TEXT_FREE_SHIPPING;
                  } elseif ($cc_list['coupon_type'] == 'T') {
                    $coupon_amount = number_format($cc_list['coupon_amount'], 2) . '%' . ' + '. TEXT_FREE_SHIPPING;
                  } else {
                    $coupon_amount = $currencies->format($cc_list['coupon_amount']);
                  }
                ?>
                <tr <?php echo $tr_attributes;?>>
                  <td class="dataTableContent">&nbsp;<?php echo $cc_list['coupon_id']; ?></td>
                  <td class="dataTableContent">&nbsp;<?php echo $coupon_desc['coupon_name']; ?></td>
                  <td class="dataTableContent" style="padding-left: 5px"><?php echo $coupon_amount;?>&nbsp;</td>
                  <td class="dataTableContent">&nbsp;<?php echo $currencies->format($cc_list['coupon_minimum_order']); ?></td>
                  <td class="dataTableContent nobr">&nbsp;<?php echo $cc_list['coupon_code']; ?></td>
                  <td class="dataTableContent txta-c"><?php if ($cc_list['coupon_active'] == 'N') { echo xtc_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10); } else { echo xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10); } ?></td>
                  <td class="dataTableContent txta-r"><?php if (isset($cInfo) && is_object($cInfo) && ($cc_list['coupon_id'] == $cInfo->coupon_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('page', 'cid', 'action', 'oldaction')) . 'page=' . $_GET['page'] . '&cid=' . $cc_list['coupon_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                </tr>
                <?php
                }
                ?>
              </table>

              <?php
              if (is_object($cc_split)) {
              ?>
              <div class="smallText pdg2 flt-l">&nbsp;<?php echo $cc_split->display_count($cc_query_numrows, MAX_DISPLAY_COUPON_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_COUPONS); ?>&nbsp;</div>
              <div class="smallText pdg2 flt-r">&nbsp;<?php echo $cc_split->display_links($cc_query_numrows, MAX_DISPLAY_COUPON_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page'],xtc_get_all_get_params(array('page','uid','cid'))); ?>&nbsp;</div>
              <?php echo draw_input_per_page($PHP_SELF,$cfg_max_display_results_key,$page_max_display_results); ?>
              <?php
              }
              ?>
              <div class="clear"></div>
              <div class="smallText pdg2 flt-r"><?php echo '<a class="button" href="' . xtc_href_link(FILENAME_COUPON_ADMIN, 'action=new') . '">' . BUTTON_INSERT . '</a>'; ?></div>

            </td>
            <?php
            $heading = array();
            $contents = array();
            switch ($_GET['action']) {
              case 'release':
                break;
              case 'voucherreport':
                $heading[] = array('text' => '<b>' . TEXT_HEADING_COUPON_REPORT . '</b>');
                $contents[] = array('text' => TEXT_NEW_INTRO);
                break;
              case 'new':
                $heading[] = array('text' => '<b>' . TEXT_HEADING_NEW_COUPON . '</b>');
                $contents[] = array('text' => TEXT_NEW_INTRO);
                $contents[] = array('text' => '<br />' . COUPON_NAME . '<br />' . xtc_draw_input_field('name'));
                $contents[] = array('text' => '<br />' . COUPON_AMOUNT . '<br />' . xtc_draw_input_field('voucher_amount'));
                $contents[] = array('text' => '<br />' . COUPON_CODE . '<br />' . xtc_draw_input_field('voucher_code'));
                $contents[] = array('text' => '<br />' . COUPON_USES_COUPON . '<br />' . xtc_draw_input_field('voucher_number_of'));
                break;
              default:
                if (isset($cInfo) && is_object($cInfo)) {
                  $heading[] = array('text'=>'<b>['.$cInfo->coupon_id.']  '.$cInfo->coupon_code.'</b>');
                  $amount = $cInfo->coupon_amount;
                  if ($cInfo->coupon_type == 'P') {
                    $amount = number_format($amount, 2).'%';
                  } elseif ($cInfo->coupon_type == 'T') {
                    $amount = number_format($amount, 2).'% + ' . TEXT_FREE_SHIPPING;
                    // BOF - web28 - 2010-07-22 - FIX coupon_amount
                  } elseif ($cInfo->coupon_type == 'S') {
                    $amount = ($amount > 0 ? $currencies->format($amount) . ' + ' : '') . TEXT_FREE_SHIPPING;
                    // EOF - web28 - 2010-07-22 - FIX coupon_amount
                  } else {
                    $amount = $currencies->format($amount);
                  }
                  if ($_GET['action'] == 'voucherdelete') {
                    $contents[] = array('text'=> TEXT_CONFIRM_DELETE . '<br /><br /><div style="text-align:center;">' .
                                        '<a class="button" href="'.xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('cid', 'action')). 'action=confirmdelete&cid='.(int)$_GET['cid'],'NONSSL').'">'.BUTTON_CONFIRM.'</a>' .
                                        '<a class="button" href="'.xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('cid', 'action')). 'cid='.$cInfo->coupon_id,'NONSSL').'">'.BUTTON_CANCEL.'</a></div>'
                                       );
                  } else {
                    $prod_details = TEXT_NONE;
                    if ($cInfo->restrict_to_products) {
                      $prod_details = '<a href="listproducts.php?cid=' . $cInfo->coupon_id . '" target="_blank" onclick="window.open(\'listproducts.php?cid=' . $cInfo->coupon_id . '\', \'Valid_Categories\', \'scrollbars=yes,resizable=yes,menubar=yes,width=600,height=600\'); return false"><strong>' . TEXT_VIEW_SHORT .'</strong></a>';
                    }
                    $cat_details = TEXT_NONE;
                    if ($cInfo->restrict_to_categories) {
                      $cat_details = '<a href="listcategories.php?cid=' . $cInfo->coupon_id . '" target="_blank" onclick="window.open(\'listcategories.php?cid=' . $cInfo->coupon_id . '\', \'Valid_Categories\', \'scrollbars=yes,resizable=yes,menubar=yes,width=600,height=600\'); return false"><strong>' . TEXT_VIEW_SHORT .'</strong></a>';
                    }
                    $coupon_name_query = xtc_db_query("SELECT coupon_name FROM " . TABLE_COUPONS_DESCRIPTION . " WHERE coupon_id = '" . (int)$cInfo->coupon_id . "' AND language_id = '" . (int)$_SESSION['languages_id'] . "'");
                    $coupon_name = xtc_db_fetch_array($coupon_name_query);

                    $coupon_status = '';
                    if($cInfo->coupon_active == 'N'){
                      $change_coupon_status = '<a class="button nobr" href="'.xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('cid', 'action')). 'action=voucher_set_active&cid='.$cInfo->coupon_id,'NONSSL').'">'.BUTTON_STATUS_ON.'</a>';
                      $coupon_status = '&status=N';
                    } else {
                      $change_coupon_status = '<a class="button nobr" href="'.xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('cid', 'action')). 'action=voucher_set_inactive&cid='.$cInfo->coupon_id,'NONSSL').'">'.BUTTON_STATUS_OFF.'</a>';
                    }
                    
                    $customers_list = '';
                    if ($cInfo->restrict_to_customers == '') {
                      $customers_list = TXT_ALL;
                    } else {
                      $coupon_groups = explode(',', $cInfo->restrict_to_customers);
                      
                      $customers_list = '<ul>';
                      foreach ($customers_statuses_array as $customers_statuses) {
                        if (in_array($customers_statuses['id'], $coupon_groups)) {
                          $customers_list .= '<li>'.$customers_statuses['text'].'</li>';
                        }
                      }
                      $customers_list .= '</ul>';
                    }
                    
                    $contents[] = array('text'=>COUPON_NAME . ':&nbsp;' . $coupon_name['coupon_name'] . '<br />' .
                      COUPON_AMOUNT . ':&nbsp;<strong><span class="col-red">' . $amount . '</span></strong><br /><br />' .
                      COUPON_STARTDATE . ':&nbsp;' . xtc_date_short($cInfo->coupon_start_date) . '<br />' .
                      COUPON_FINISHDATE . ':&nbsp;' . xtc_date_short($cInfo->coupon_expire_date) . '<br /><br />' .
                      COUPON_USES_COUPON . ':&nbsp;<strong>' . $cInfo->uses_per_coupon . '</strong><br />' .
                      COUPON_USES_USER . ':&nbsp;<strong>' . $cInfo->uses_per_user . '</strong><br /><br />' .
                      COUPON_PRODUCTS . ':&nbsp;' . $prod_details . '<br />' .
                      COUPON_CATEGORIES . ':&nbsp;' . $cat_details . '<br />' .
                      COUPON_CUSTOMERS . ':&nbsp;' . $customers_list . '<br /><br />' .
                      DATE_CREATED . ':&nbsp;' . xtc_date_short($cInfo->date_created) . '<br />' .
                      DATE_MODIFIED . ':&nbsp;' . xtc_date_short($cInfo->date_modified) . '<br /><br />');

                    $contents[] = array('text'=> '<div style="text-align:center;">'.'
                                 <a class="button" href="'.xtc_href_link(FILENAME_GV_MAIL, 'cid='.$cInfo->coupon_id, 'NONSSL').'">'.BUTTON_EMAIL.'</a>' .
                                 '<a class="button" href="'.xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('cid', 'action', 'oldaction')).'action=voucheredit&cid='.$cInfo->coupon_id,'NONSSL').'">'.BUTTON_EDIT.'</a>' .
                                 $change_coupon_status .
                                 '<a class="button" href="'.xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('cid', 'action', 'oldaction')). 'action=voucherdelete&cid='.$cInfo->coupon_id.$coupon_status,'NONSSL').'">'.BUTTON_DELETE.'</a>' .
                                 '<a class="button" href="'.xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('cid', 'action', 'oldaction')). 'action=voucherreport&cid='.$cInfo->coupon_id,'NONSSL').'">'.BUTTON_REPORT.'</a></div>'
                                 );
                  }
                }
                break;
              }

              //BOX RIGHT
              if ( (xtc_not_null($heading)) && (xtc_not_null($contents)) ) {
                echo '<td class="boxRight">'. PHP_EOL;
                $box = new box;
                echo $box->infoBox($heading, $contents);
                echo '</td>'. PHP_EOL;
              }
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