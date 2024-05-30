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
  
  // include needed classes
  require_once(DIR_WS_CLASSES . 'currencies.php');

  //display per page
  $cfg_max_display_results_key = 'MAX_DISPLAY_COUPON_RESULTS';
  $page_max_display_results = xtc_cfg_save_max_display_results($cfg_max_display_results_key);

  $customers_statuses_array = xtc_get_customers_statuses(true);
  unset($customers_statuses_array[0]); //Admin

  $page = (isset($_GET['page']) ? (int)$_GET['page'] : 1);
  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  $currencies = new currencies();

  switch ($action) {
  	case 'voucher_set_inactive':
      xtc_db_query("UPDATE " . TABLE_COUPONS . " SET coupon_active = 'N' WHERE coupon_id='".(int)$_GET['cID']."'");
      xtc_redirect(xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('action', 'uid')) ));
      break;
      
		case 'voucher_set_active':
      xtc_db_query("UPDATE " . TABLE_COUPONS . " SET coupon_active = 'Y' WHERE coupon_id='".(int)$_GET['cID']."'");
      xtc_redirect(xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('action', 'uid')) ));
      break;
      
    case 'confirmdelete':
      xtc_db_query("DELETE FROM ".TABLE_COUPONS." WHERE coupon_id = '".(int)$_GET['cID']."'");
      xtc_db_query("DELETE FROM ".TABLE_COUPONS_DESCRIPTION." WHERE coupon_id = '".(int)$_GET['cID']."'");
      xtc_db_query("DELETE FROM ".TABLE_COUPON_EMAIL_TRACK." WHERE coupon_id='".(int)$_GET['cID']."'");
			xtc_db_query("DELETE FROM ".TABLE_COUPON_REDEEM_TRACK." WHERE coupon_id='".(int)$_GET['cID']."'");
      xtc_redirect(xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('cID', 'action', 'uid')) ));
      break;
      
    case 'insert':
    case 'update':
      $error = false;
      $languages = xtc_get_languages();
      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        $_POST['coupon_name'][$languages[$i]['id']] = trim($_POST['coupon_name'][$languages[$i]['id']]);
        if (!$_POST['coupon_name'][$languages[$i]['id']]) {
          $error = true;
          $messageStack->add(ERROR_NO_COUPON_NAME . $languages[$i]['name'], 'error');
        }
        $_POST['coupon_desc'][$languages[$i]['id']] = trim($_POST['coupon_desc'][$languages[$i]['id']]);
      }
      $_POST['coupon_amount'] = trim($_POST['coupon_amount']);
      $_POST['coupon_amount'] = preg_replace('/[^0-9.%]/', '', $_POST['coupon_amount']);
      
      if (!$_POST['coupon_name']) {
        $error = true;
        $messageStack->add(ERROR_NO_COUPON_NAME, 'error');
      }
      
      if (empty($_POST['coupon_amount']) && !isset($_POST['coupon_free_ship'])) {
        $error = true;
        $messageStack->add(ERROR_NO_COUPON_AMOUNT, 'error');
      }
      
      if (strtotime($_POST['coupon_startdate']) > strtotime($_POST['coupon_finishdate'])) {
        $error = true;
        $messageStack->add(ERROR_COUPON_DATE, 'error');
      }
      
      if (empty($_POST['coupon_code'])) {
        $_POST['coupon_code'] = create_coupon_code();
      } else {
        $_POST['coupon_code'] = xtc_db_prepare_input($_POST['coupon_code']);
      }
      
      $check_query = xtc_db_query("SELECT coupon_code 
                                     FROM " . TABLE_COUPONS . " 
                                    WHERE coupon_code = '" . xtc_db_input($_POST['coupon_code']) . "'");
      if (xtc_db_num_rows($check_query) > 0 && $_POST['coupon_code'] != $_POST['coupon_code_old'])  {
        $error = true;
        $messageStack->add(ERROR_COUPON_EXISTS, 'error');
      }
      
      if ($error !== true) {
        $coupon_type = "F";
        if (substr($_POST['coupon_amount'], -1) == '%') $coupon_type='P';
        if (isset($_POST['coupon_free_ship'])) $coupon_type = 'S';

        if (isset($_POST['coupon_free_ship']) && substr($_POST['coupon_amount'], -1) == '%') {
          $coupon_type = 'T';
        }

        $_POST['coupon_amount'] = preg_replace('/[^0-9.]/', '', $_POST['coupon_amount']);

        $sql_data_array = array(
          'coupon_code' => xtc_db_prepare_input($_POST['coupon_code']),
          'coupon_amount' => xtc_db_prepare_input($_POST['coupon_amount']),
          'coupon_type' => xtc_db_prepare_input($coupon_type),
          'uses_per_coupon' => xtc_db_prepare_input((int)$_POST['coupon_uses_coupon']),
          'uses_per_user' => xtc_db_prepare_input((int)$_POST['coupon_uses_user']),
          'coupon_minimum_order' => xtc_db_prepare_input($_POST['coupon_min_order']),
          'restrict_to_products' => xtc_db_prepare_input($_POST['coupon_products']),
          'restrict_to_categories' => xtc_db_prepare_input($_POST['coupon_categories']),
          'restrict_to_customers' => xtc_db_prepare_input((isset($_POST['coupon_groups']) && $_POST['coupon_groups'][0] != 'all') ? implode(',', $_POST['coupon_groups']) : ''),
          'coupon_start_date' => xtc_db_prepare_input(date('Y-m-d H:i:00', strtotime($_POST['coupon_startdate']))),
          'coupon_expire_date' => xtc_db_prepare_input(date('Y-m-d H:i:59', strtotime($_POST['coupon_finishdate']))),
        );
        
        if ($action == 'update') {
          $sql_data_array['date_modified'] = 'now()';
          xtc_db_perform(TABLE_COUPONS, $sql_data_array, 'update', "coupon_id='" . (int)$_GET['cID']."'");
          for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $coupon_query = xtc_db_query("SELECT * FROM ".TABLE_COUPONS_DESCRIPTION." 
                                                  WHERE language_id = '".(int)$languages[$i]['id']."' 
                                                    AND coupon_id = '".(int)$_GET['cID']."'");
            if (xtc_db_num_rows($coupon_query) == 0) {
              xtc_db_perform(TABLE_COUPONS_DESCRIPTION, array('coupon_id' => (int)$_GET['cID'], 'language_id' => (int)$languages[$i]['id']));
            }
            
            $sql_cdata_array = array(
              'coupon_name' => xtc_db_prepare_input($_POST['coupon_name'][$languages[$i]['id']]),
              'coupon_description' => xtc_db_prepare_input($_POST['coupon_desc'][$languages[$i]['id']])
            );
            xtc_db_perform(TABLE_COUPONS_DESCRIPTION, $sql_cdata_array, 'update', "coupon_id = '" . (int)$_GET['cID'] . "' AND language_id = '" . (int)$languages[$i]['id'] . "'");
          }
        } else {
          $sql_data_array['date_created'] = 'now()';
          xtc_db_perform(TABLE_COUPONS, $sql_data_array);
          $insert_id = xtc_db_insert_id();
          $_GET['cID'] = $insert_id;

          for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $sql_data_array = array(
              'coupon_id' => $insert_id,
              'language_id' => $languages[$i]['id'],
              'coupon_name' => xtc_db_prepare_input($_POST['coupon_name'][$languages[$i]['id']]),
              'coupon_description' => xtc_db_prepare_input($_POST['coupon_desc'][$languages[$i]['id']])
            );
            xtc_db_perform(TABLE_COUPONS_DESCRIPTION, $sql_data_array);
          }
        }
        xtc_redirect(xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('cID', 'action', 'uid')) . 'cID=' . (int)$_GET['cID']));
      }
      break;
  }

require (DIR_WS_INCLUDES.'head.php');
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
      switch ($action) {
        case 'voucherreport':
          ?>
          <td class="boxCenter">
            <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_news.png'); ?></div>
            <div class="flt-l">
              <div class="pageHeading"><?php echo HEADING_TITLE; ?></div>
            </div>           
            <div class="main pdg2 flt-l" style="margin-left:100px;">
              <a class="button" href="<?php echo xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('action', 'uid'))); ?>"><?php echo BUTTON_BACK; ?></a>
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
                    $cc_query_raw = "SELECT * 
                                       FROM " . TABLE_COUPON_REDEEM_TRACK . " crt
                                       JOIN " . TABLE_COUPONS . " c
                                            ON c.coupon_id = crt.coupon_id
                                      WHERE crt.coupon_id = '" . (int)$_GET['cID'] . "'";
                    $cc_split = new splitPageResults($page, $page_max_display_results, $cc_query_raw, $cc_query_numrows);
                    $cc_query = xtc_db_query($cc_query_raw);
                    while ($cc_list = xtc_db_fetch_array($cc_query)) {
                      if ((!isset($_GET['uid']) || ($_GET['uid'] == $cc_list['unique_id'])) && !isset($cInfo)) {
                        $cInfo = new objectInfo($cc_list);
                      }
                      if (isset($cInfo) && is_object($cInfo) && $cc_list['unique_id'] == $cInfo->unique_id) {
                        $tr_attributes = 'class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('cID', 'action', 'uid')) . 'cID=' . $cInfo->coupon_id . '&action=voucherreport&uid=' . $cinfo->unique_id) . '\'"';
                      } else {
                        $tr_attributes = 'class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('cID', 'action', 'uid')) . 'cID=' . $cc_list['coupon_id'] . '&action=voucherreport&uid=' . $cc_list['unique_id']) . '\'"';
                      }
                      $customer_query = xtc_db_query("SELECT customers_firstname, customers_lastname FROM " . TABLE_CUSTOMERS . " WHERE customers_id = '" . $cc_list['customer_id'] . "'");
                      $customer = xtc_db_fetch_array($customer_query);
                    ?>
                    <tr <?php echo $tr_attributes;?>>
                      <td class="dataTableContent">&nbsp;<?php echo (int)$_GET['cID']; ?></td>
                      <td class="dataTableContent">&nbsp;<?php echo $cc_list['customer_id']; ?></td>
                      <td class="dataTableContent">&nbsp;<?php echo $customer['customers_firstname'] . ' ' . $customer['customers_lastname']; ?></td>
                      <td class="dataTableContent">&nbsp;<?php echo $cc_list['redeem_ip']; ?></td>
                      <td class="dataTableContent">&nbsp;<?php echo xtc_date_short($cc_list['redeem_date']); ?></td>
                      <td class="dataTableContent txta-r"><?php if (isset($cInfo) && is_object($cInfo) && $cc_list['unique_id'] == $cInfo->unique_id) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('cID', 'action', 'uid')) . 'cID=' . $cc_list['coupon_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                    </tr>
                    <?php
                    }
                    ?>
                  </table>

                  <div class="smallText pdg2 flt-l"><?php echo $cc_split->display_count($cc_query_numrows, $page_max_display_results, $page, TEXT_DISPLAY_NUMBER_OF_COUPONS); ?></div>
                  <div class="smallText pdg2 flt-r"><?php echo $cc_split->display_links($cc_query_numrows, $page_max_display_results, MAX_DISPLAY_PAGE_LINKS, $page,xtc_get_all_get_params(array('page','uid'))); ?></div>
                  <?php echo draw_input_per_page($PHP_SELF,$cfg_max_display_results_key,$page_max_display_results); ?>
                </td>
                <?php
                $heading = array();
                $contents = array();
                if (isset($cInfo)) {
                  $count_customers = xtc_db_query("SELECT * 
                                                     FROM " . TABLE_COUPON_REDEEM_TRACK . " 
                                                    WHERE coupon_id = '" . (int)$cInfo->coupon_id . "' 
                                                      AND customer_id = '" . (int)$cInfo->customer_id . "'");
                  $total = xtc_db_num_rows($count_customers);

                  $heading[] = array('text' => '<b>[' . $cInfo->coupon_id . '] ' . $cInfo->coupon_code . '</b>');
                  $contents[] = array('text' => '<b>' . TEXT_REDEMPTIONS . '</b>');
                  $contents[] = array('text' => TEXT_REDEMPTIONS_TOTAL . ' ' . $cc_query_numrows);
                  $contents[] = array('text' => TEXT_REDEMPTIONS_CUSTOMER . ' ' . $total);
                }
          
                if ( (xtc_not_null($heading)) && (xtc_not_null($contents)) ) {
                  echo '<td class="boxRight">'. PHP_EOL;
                  $box = new box;
                  echo $box->infoBox($heading, $contents);
                  echo '</td>'. PHP_EOL;
                }
                ?>
              </tr>
            </table>
          </td>
          <?php
          break;

      case 'voucheredit':
        $coupon_desc = array();
        $coupon_name = array();
    
        $languages = xtc_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $coupon_query = xtc_db_query("SELECT coupon_name,
                                               coupon_description 
                                          FROM " . TABLE_COUPONS_DESCRIPTION . " 
                                         WHERE coupon_id = '" .  (int)$_GET['cID'] . "' 
                                           AND language_id = '" . (int)$languages[$i]['id'] . "'");
          $coupon = xtc_db_fetch_array($coupon_query);
          $coupon_name[$languages[$i]['id']] = $coupon['coupon_name'];
          $coupon_desc[$languages[$i]['id']] = $coupon['coupon_description'];
        }
    
        $coupon_query = xtc_db_query("SELECT * 
                                        FROM " . TABLE_COUPONS . " 
                                       WHERE coupon_id = '" . (int)$_GET['cID'] . "'");
        $coupon = xtc_db_fetch_array($coupon_query);
        $coupon_amount = $coupon['coupon_amount'];
        if ($coupon['coupon_type'] == 'P') {
          $coupon_amount .= '%';
        }
        if ($coupon['coupon_type'] == 'S') {
          $coupon_free_ship = true;
        }
        if ($coupon['coupon_type'] == 'T') {
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
        $coupon_startdate = date('Y-m-d H:i', strtotime($coupon['coupon_start_date']));
        $coupon_finishdate = date('Y-m-d H:i', strtotime($coupon['coupon_expire_date']));

      case 'new':
      case 'insert':
      case 'update':
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
    
        if (!isset($coupon_amount)) {
          $coupon_amount = '';
        }
        if (!isset($coupon_min_order)) {
          $coupon_min_order = '';
        }
        if (!isset($coupon_code)) {
          $coupon_code = '';
        }
        if (!isset($coupon_products)) {
          $coupon_products = '';
        }
        if (!isset($coupon_categories)) {
          $coupon_categories = '';
        }
        if (!isset($coupon_free_ship)) {
          $coupon_free_ship = false;
        }
        if (isset($coupon_groups)) {
          $coupon_groups = array_filter($coupon_groups);
        }
        if (!isset($coupon_uses_user)) {
          $coupon_uses_user = 1;
        }
        if (!isset($coupon_uses_coupon)) {
          $coupon_uses_coupon = '';
        }
        if (!isset($coupon_startdate)) {
          $coupon_startdate = date('Y-m-d');
        }
        if (!isset($coupon_finishdate)) {
          $coupon_finishdate = date('Y-m-d', strtotime('+1 year'));
        }

        $input_name = '';
        $input_desc = '';
        $languages = xtc_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          if (isset($_POST['coupon_name'][$languages[$i]['id']])) {
            $coupon_name[$languages[$i]['id']] = xtc_db_prepare_input($_POST['coupon_name'][$languages[$i]['id']]);
          }
          if (isset($_POST['coupon_desc'][$languages[$i]['id']])) {
            $coupon_desc[$languages[$i]['id']] = xtc_db_prepare_input($_POST['coupon_desc'][$languages[$i]['id']]);
          }
          $lang_img = '<span style="float:left; padding-top:2px;">'. xtc_image(DIR_WS_LANGUAGES . $languages[$i]['directory'].'/admin/images/'.$languages[$i]['image'], $languages[$i]['name']) . '</span>';
          $input_name .= $lang_img . '&nbsp;'. xtc_draw_input_field('coupon_name[' . $languages[$i]['id'] . ']', ((isset($coupon_name[$languages[$i]['id']])) ? $coupon_name[$languages[$i]['id']] : '')) . '&nbsp;<br />';
          $input_desc .= $lang_img . '&nbsp;'. xtc_draw_textarea_field('coupon_desc[' . $languages[$i]['id'] . ']','physical','24','3', ((isset($coupon_desc[$languages[$i]['id']])) ? $coupon_desc[$languages[$i]['id']] : ''), 'class="textareaModule"') . '&nbsp;<br />';
        }
        ?>
        <td class="boxCenter">
          <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_news.png'); ?></div>
          <div class="flt-l">
            <div class="pageHeading"><?php echo HEADING_TITLE; ?></div>
          </div>
          <div class="clear"></div>
          <?php
          echo xtc_draw_form('coupon', FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('action', 'cID')) . 'action='.(($action == 'new' || $action == 'insert') ? 'insert' : 'update') . ((isset($_GET['cID']) && $_GET['cID'] > 0) ? '&cID=' . (int)$_GET['cID'] : ''), 'post', 'enctype="multipart/form-data"');
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
                <td class="dataTableConfig col-middle"><?php echo xtc_draw_input_field('coupon_code', $coupon_code).xtc_draw_hidden_field('coupon_code_old', $coupon_code); ?></td>
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
                <td class="dataTableConfig col-middle nobr"><?php echo xtc_draw_input_field('coupon_startdate', $coupon_startdate ,'id="Datetimepicker1"'); ?></td>
                <td class="dataTableConfig col-right"><?php echo COUPON_STARTDATE_HELP.COUPON_DATE_START_TT; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo COUPON_FINISHDATE; ?></td>
                <td class="dataTableConfig col-middle nobr"><?php echo xtc_draw_input_field('coupon_finishdate', $coupon_finishdate ,'id="Datetimepicker2"'); ?></td>
                <td class="dataTableConfig col-right"><?php echo COUPON_FINISHDATE_HELP.COUPON_DATE_END_TT; ?></td>
              </tr>
            </table>
            <div class="main" style="margin:20px 5px;float:right;">
              <?php echo '<input type="submit" class="button" value="' . BUTTON_SAVE . '"/>'; ?>
              <?php echo '&nbsp;&nbsp;<a class="button" href="' . xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('action'))) .'">'. BUTTON_CANCEL . '</a>'; ?>
            </div>
          </form>
        </td>
        <?php
        break;
        
      default:
        ?>
        <td class="boxCenter">
          <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_news.png'); ?></div>
          <div class="pageHeading flt-l"><?php echo HEADING_TITLE; ?>
            <div class="main pdg2"><?php echo 'Configuration'; ?></div>
          </div>
          <div class="main flt-l pdg2 mrg5" style="margin-left:20px;">
            <?php echo xtc_draw_form('status', FILENAME_COUPON_ADMIN, '', 'get');
              $status_array[] = array('id' => 'Y', 'text' => TEXT_COUPON_ACTIVE);
              $status_array[] = array('id' => 'N', 'text' => TEXT_COUPON_INACTIVE);
              $status_array[] = array('id' => '*', 'text' => TEXT_COUPON_ALL);
              $status = isset($_GET['status']) ? xtc_db_prepare_input($_GET['status']) : 'Y';              $orders_statuses_array = array();
              echo HEADING_TITLE_STATUS . ' ' . xtc_draw_pull_down_menu('status', $status_array, $status, 'onchange="this.form.submit();" style="margin-top: 2px; min-width:150px;"'); 
              echo xtc_draw_hidden_filter_field('input_id', ((isset($_GET['input_id'])) ? $_GET['input_id'] : ''));
              echo xtc_draw_hidden_filter_field('input_code', ((isset($_GET['input_code'])) ? $_GET['input_code'] : ''));
              echo xtc_draw_hidden_filter_field('input_name', ((isset($_GET['input_name'])) ? $_GET['input_name'] : ''));
            ?>
            </form>        
          </div>
          <div class="main flt-l pdg2 mrg5" style="margin-left:20px;">
            <?php echo xtc_draw_form('search', FILENAME_COUPON_ADMIN, '', 'get');
              $input_id = !isset($_POST['input_id']) ? !isset($_GET['input_id']) ? '' : (int)$_GET['input_id'] : (int)$_POST['input_id'];
              echo ' &nbsp; cID: <input type="text" name="input_id" value="'.$input_id.'"/> &nbsp; ';
              $input_code = !isset($_POST['input_code']) ? !isset($_GET['input_code']) ? '' : xtc_db_input($_GET['input_code']) : xtc_db_input($_POST['input_code']);
              echo ' &nbsp; Code: <input type="text" name="input_code" value="'.$input_code.'"/> &nbsp; ';
              $input_name = !isset($_POST['input_name']) ? !isset($_GET['input_name']) ? '' : xtc_db_input($_GET['input_name']) : xtc_db_input($_POST['input_name']);
              echo ' &nbsp; Name: <input type="text" name="input_name" value="'.$input_name.'"/> &nbsp; ';
              echo '<input class="button no_top_margin" style="vertical-align:top;" type="submit" name="btnSearch" value="'.BUTTON_SEARCH.'"/>';
              echo xtc_draw_hidden_filter_field('status', ((isset($_GET['status'])) ? $_GET['status'] : ''));
              ?>
            </form>
          </div>
          <div class="main" style="display:inline-block; padding:5px; vertical-align:top; margin-left:50px"><a class="button no_top_margin" href="<?php echo xtc_href_link(FILENAME_COUPON_ADMIN, 'action=new'); ?>"><?php echo BUTTON_INSERT; ?></a></div>    
          <table class="tableCenter">
            <tr>
              <td class="boxCenterLeft">
                <?php
                if ($action == '' && !defined('MODULE_ORDER_TOTAL_COUPON_STATUS')) {
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

                  if ($input_code != '') {
                    $coupon_active .= " AND c.coupon_code LIKE '%".$input_code."%'";
                  }
                  if (($input_id != '') && ($input_id > 0)) {
                    $coupon_active .= " AND c.coupon_id = '".$input_id."'";
                  }
                  $sqlJoin = '';
                  if ($input_name != '') {
                    $coupon_active .= " AND cd.coupon_name LIKE '%".$input_name."%'";
                    $sqlJoin = " LEFT JOIN ".TABLE_COUPONS_DESCRIPTION." cd ON (c.coupon_id = cd.coupon_id AND cd.language_id = '" . (int)$_SESSION['languages_id'] . "')";
                  }
                  $cc_query_raw = "SELECT c.*
                                     FROM " . TABLE_COUPONS ." c
                                          ".$sqlJoin."
                                    WHERE c.coupon_type != 'G' 
                                          $coupon_active
                                    ORDER BY c.coupon_id DESC";

                  $cc_split = new splitPageResults($page, $page_max_display_results, $cc_query_raw, $cc_query_numrows);
                  $cc_query = xtc_db_query($cc_query_raw);
                  while ($cc_list = xtc_db_fetch_array($cc_query)) {
                    if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ($_GET['cID'] == $cc_list['coupon_id']))) && !isset($cInfo)) {
                      $cInfo = new objectInfo($cc_list);
                    }
                    if (isset($cInfo) && is_object($cInfo) && ($cc_list['coupon_id'] == $cInfo->coupon_id) ) {
                      $tr_attributes = 'class="dataTableRowSelected" onmouseover="this.style.cursor=\'default\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->coupon_id . '&action=voucheredit') . '\'"';
                    } else {
                      $tr_attributes = 'class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'default\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('cID', 'action')) . 'cID=' . $cc_list['coupon_id']) . '\'"';
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
                    <td  class="dataTableContent txta-c">
                      <?php
                      if ($cc_list['coupon_active'] == 'Y') {
                        echo xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 12, 12, 'style="margin-right:5px;"') . '<a href="' . xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('cID', 'action')). 'action=voucher_set_inactive&cID='.$cc_list['coupon_id'],'NONSSL') . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 12, 12) . '</a>';
                      } else {
                        echo '<a href="' . xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('cID', 'action')). 'action=voucher_set_active&cID='.$cc_list['coupon_id'],'NONSSL') . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 12, 12, 'style="margin-right:5px;"') . '</a>' . xtc_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 12, 12);
                      }
                      ?>
                    </td>
                    <td class="dataTableContent txta-r"><?php if (isset($cInfo) && is_object($cInfo) && ($cc_list['coupon_id'] == $cInfo->coupon_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('page', 'cID', 'action')) . 'page=' . $page . '&cID=' . $cc_list['coupon_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                  </tr>
                  <?php
                  }
                  ?>
                </table>

                <div class="smallText pdg2 flt-l"><?php echo $cc_split->display_count($cc_query_numrows, $page_max_display_results, $page, TEXT_DISPLAY_NUMBER_OF_COUPONS); ?></div>
                <div class="smallText pdg2 flt-r"><?php echo $cc_split->display_links($cc_query_numrows, $page_max_display_results, MAX_DISPLAY_PAGE_LINKS, $page,xtc_get_all_get_params(array('page','uid','cID'))); ?></div>
                <?php echo draw_input_per_page($PHP_SELF,$cfg_max_display_results_key,$page_max_display_results); ?>
                <div class="pdg2 flt-r smallText"><?php echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_COUPON_ADMIN, 'action=new') . '">' . BUTTON_INSERT . '</a>'; ?></div>
              </td>
              <?php
              $heading = array();
              $contents = array();
              switch ($action) {
                case 'voucherdelete':
                  $heading[] = array('text'=>'<b>['.$cInfo->coupon_id.'] '.$cInfo->coupon_code.'</b>');
                  $contents[] = array('text' => TEXT_CONFIRM_DELETE);
                  $contents[] = array('align' => 'center', 'text' => 
                    '<a class="button" href="'.xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('cID', 'action')). 'action=confirmdelete&cID='.(int)$_GET['cID'],'NONSSL').'">'.BUTTON_CONFIRM.'</a>' .
                    '<a class="button" href="'.xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('cID', 'action')). 'cID='.$cInfo->coupon_id,'NONSSL').'">'.BUTTON_CANCEL.'</a>'
                  );
                  break;
                  
                default:
                  if (isset($cInfo) && is_object($cInfo)) {
                    $heading[] = array('text'=>'<b>['.$cInfo->coupon_id.'] '.$cInfo->coupon_code.'</b>');
                    $amount = $cInfo->coupon_amount;
                    if ($cInfo->coupon_type == 'P') {
                      $amount = number_format($amount, 2).'%';
                    } elseif ($cInfo->coupon_type == 'T') {
                      $amount = number_format($amount, 2).'% + ' . TEXT_FREE_SHIPPING;
                    } elseif ($cInfo->coupon_type == 'S') {
                      $amount = ($amount > 0 ? $currencies->format($amount) . ' + ' : '') . TEXT_FREE_SHIPPING;
                    } else {
                      $amount = $currencies->format($amount);
                    }

                    $prod_details = TEXT_NO_RESTRICTION;
                    if ($cInfo->restrict_to_products) {
                      $prod_details = '<a href="listproducts.php?cID=' . $cInfo->coupon_id . '" target="_blank" onclick="window.open(\'listproducts.php?cID=' . $cInfo->coupon_id . '\', \'Valid_Categories\', \'scrollbars=yes,resizable=yes,menubar=yes,width=600,height=600\'); return false"><strong>' . TEXT_VIEW_SHORT .'</strong></a>';
                    }
                    $cat_details = TEXT_NO_RESTRICTION;
                    if ($cInfo->restrict_to_categories) {
                      $cat_details = '<a href="listcategories.php?cID=' . $cInfo->coupon_id . '" target="_blank" onclick="window.open(\'listcategories.php?cID=' . $cInfo->coupon_id . '\', \'Valid_Categories\', \'scrollbars=yes,resizable=yes,menubar=yes,width=600,height=600\'); return false"><strong>' . TEXT_VIEW_SHORT .'</strong></a>';
                    }
                    $coupon_name_query = xtc_db_query("SELECT coupon_name FROM " . TABLE_COUPONS_DESCRIPTION . " WHERE coupon_id = '" . (int)$cInfo->coupon_id . "' AND language_id = '" . (int)$_SESSION['languages_id'] . "'");
                    $coupon_name = xtc_db_fetch_array($coupon_name_query);

                    $coupon_status = '';
                    if ($cInfo->coupon_active == 'N') {
                      $change_coupon_status = '<a class="button nobr" href="'.xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('cID', 'action')). 'action=voucher_set_active&cID='.$cInfo->coupon_id,'NONSSL').'">'.BUTTON_STATUS_ON.'</a>';
                      $coupon_status = '&status=N';
                    } else {
                      $change_coupon_status = '<a class="button nobr" href="'.xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('cID', 'action')). 'action=voucher_set_inactive&cID='.$cInfo->coupon_id,'NONSSL').'">'.BUTTON_STATUS_OFF.'</a>';
                    }
              
                    $customers_list = '';
                    if ($cInfo->restrict_to_customers == '') {
                      $customers_list = TXT_ALL.'<br/>';
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
              
                    $contents[] = array('align' => 'center', 'text' => 
                      '<a class="button" href="'.xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('cID', 'action')).'action=voucheredit&cID='.$cInfo->coupon_id,'NONSSL').'">'.BUTTON_EDIT.'</a>' .
                      $change_coupon_status .
                      '<a class="button" href="'.xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('cID', 'action')). 'action=voucherdelete&cID='.$cInfo->coupon_id.$coupon_status,'NONSSL').'">'.BUTTON_DELETE.'</a>'
                    );
                    $contents[] = array('align' => 'center', 'text' => 
                      '<a class="button" href="'.xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('cID', 'action')). 'action=voucherreport&cID='.$cInfo->coupon_id,'NONSSL').'">'.BUTTON_REPORT.'</a>' . 
                      '<a class="button" href="'.xtc_href_link(FILENAME_GV_MAIL, 'cID='.$cInfo->coupon_id, 'NONSSL').'">'.BUTTON_EMAIL.'</a>&nbsp;'
                    );

                    $contents[] = array('text' => '<br />' . COUPON_NAME . ':&nbsp;' . $coupon_name['coupon_name'] . '<br />' .
                      COUPON_AMOUNT . ':&nbsp;<strong><span class="col-red">' . $amount . '</span></strong><br /><br />' .
                      COUPON_STARTDATE . ':&nbsp;' . xtc_datetime_short($cInfo->coupon_start_date) . '<br />' .
                      COUPON_FINISHDATE . ':&nbsp;' . xtc_datetime_short($cInfo->coupon_expire_date) . '<br /><br />' .
                      COUPON_USES_COUPON . ':&nbsp;<strong>' . $cInfo->uses_per_coupon . '</strong><br />' .
                      COUPON_USES_USER . ':&nbsp;<strong>' . $cInfo->uses_per_user . '</strong><br /><br />' .
                      COUPON_PRODUCTS . ':&nbsp;' . $prod_details . '<br />' .
                      COUPON_CATEGORIES . ':&nbsp;' . $cat_details . '<br />' .
                      COUPON_CUSTOMERS . ':&nbsp;' . $customers_list . '<br />' .
                      DATE_CREATED . ':&nbsp;' . xtc_date_short($cInfo->date_created) . '<br />' .
                      DATE_MODIFIED . ':&nbsp;' . xtc_date_short($cInfo->date_modified) . '<br />'
                    );
                  }
                  break;
                }

                if ( (xtc_not_null($heading)) && (xtc_not_null($contents)) ) {
                  echo '<td class="boxRight">'. PHP_EOL;
                  $box = new box;
                  echo $box->infoBox($heading, $contents);
                  echo '</td>'. PHP_EOL;
                }
              ?>
            </tr>
          </table>
        </td>
        <?php
      }
      ?>
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