<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce( based on original files from OSCommerce CVS 2.2 2002/08/28 02:14:35); www.oscommerce.com
   (c) 2003	 nextcommerce (customers_status.php,v 1.28 2003/08/18); www.nextcommerce.org
   (c) 2006 XT-Commerce (customers_status.php 1064 2005-07-21)

   Released under the GNU General Public License
   --------------------------------------------------------------
   based on Third Party contribution:
   Customers Status v3.x  (c) 2002-2003 Copyright Elari elari@free.fr | www.unlockgsm.com/dload-osc/ | CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/elari/?sortby=date#dirlist

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  require('includes/application_top.php');
  
  if (!function_exists('get_table_columns')) {
    function get_table_columns($table, $col = '', $like = false) {
      $columns = array();
      $test = false;

      $result_query = xtc_db_query("SHOW COLUMNS FROM ".$table.(($col != '' && $like === true) ? " LIKE '".$col."'" : ''));
      if (xtc_db_num_rows($result_query) > 0) {
        while($row = xtc_db_fetch_array($result_query)){
          $columns[$row['Field']] = '';        
          if ($col != '' && $col == $row['Field'] && $like === false) {
            $test = true;
            break;
          }
        }
      
      }
      if ($col != '' && $like === false) {
        return $test;
      }
      return $columns;
    }
  }

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (xtc_not_null($action)) {
    switch ($action) {
      case 'insert':
      case 'save':
        if (isset($_POST) && count($_POST) > 0) {
        $customers_status_id = xtc_db_prepare_input($_GET['cID']);
        $languages = xtc_get_languages();
        for ($i=0; $i < sizeof($languages); $i++) {
          $language_id = $languages[$i]['id'];
          $customers_status_payment_unallowed = implode(',', (is_array($_POST['customers_status_payment_unallowed']) ? $_POST['customers_status_payment_unallowed'] : array()));
          $customers_status_shipping_unallowed = implode(',', (is_array($_POST['customers_status_shipping_unallowed']) ? $_POST['customers_status_shipping_unallowed'] : array()));
          $sql_data_array = array(
              'customers_status_name' => xtc_db_prepare_input($_POST['customers_status_name'][$language_id]),
              'customers_status_public' => xtc_db_prepare_input($_POST['customers_status_public']),
              'customers_status_show_price' => xtc_db_prepare_input($_POST['customers_status_show_price']),
              'customers_status_show_price_tax' => xtc_db_prepare_input($_POST['customers_status_show_price_tax']),
              'customers_status_min_order' => xtc_db_prepare_input($_POST['customers_status_min_order']),
              'customers_status_max_order' => xtc_db_prepare_input($_POST['customers_status_max_order']),
              'customers_status_discount' => xtc_db_prepare_input($_POST['customers_status_discount']),
              'customers_status_ot_discount_flag' => xtc_db_prepare_input($_POST['customers_status_ot_discount_flag']),
              'customers_status_ot_discount' => xtc_db_prepare_input($_POST['customers_status_ot_discount']),
              'customers_status_graduated_prices' => xtc_db_prepare_input($_POST['customers_status_graduated_prices']),
              'customers_status_add_tax_ot' => xtc_db_prepare_input($_POST['customers_status_add_tax_ot']),
              'customers_status_payment_unallowed' => xtc_db_prepare_input($customers_status_payment_unallowed),
              'customers_status_shipping_unallowed' => xtc_db_prepare_input($customers_status_shipping_unallowed),
              'customers_fsk18' => xtc_db_prepare_input($_POST['customers_fsk18']),
              'customers_fsk18_display' => xtc_db_prepare_input($_POST['customers_fsk18_display']),
              'customers_status_write_reviews' => xtc_db_prepare_input($_POST['customers_status_write_reviews']),
              'customers_status_read_reviews' => xtc_db_prepare_input($_POST['customers_status_read_reviews']),
              'customers_status_discount_attributes' => xtc_db_prepare_input($_POST['customers_status_discount_attributes']),
              'customers_status_show_tax_total' => xtc_db_prepare_input($_POST['customers_status_show_tax_total'])
            );
          if ($action == 'insert') {
            if (!xtc_not_null($customers_status_id)) {
              $next_id_query = xtc_db_query("SELECT MAX(customers_status_id) AS customers_status_id FROM " . TABLE_CUSTOMERS_STATUS . "");
              $next_id = xtc_db_fetch_array($next_id_query);
              $customers_status_id = $next_id['customers_status_id'] + 1;
            }
            $insert_sql_data = array('customers_status_id' => xtc_db_prepare_input($customers_status_id), 'language_id' => xtc_db_prepare_input($language_id));
            $sql_data_array = xtc_array_merge($sql_data_array, $insert_sql_data);
            xtc_db_perform(TABLE_CUSTOMERS_STATUS, $sql_data_array);
          } elseif ($action == 'save') {
            $customers_status_query = xtc_db_query("SELECT * FROM ".TABLE_CUSTOMERS_STATUS." WHERE language_id = '".$language_id."' AND customers_status_id = '".xtc_db_input($customers_status_id)."'");
            if (xtc_db_num_rows($customers_status_query) == 0)
              xtc_db_perform(TABLE_CUSTOMERS_STATUS, array ('customers_status_id' => xtc_db_input($customers_status_id), 'language_id' => $language_id));
              xtc_db_perform(TABLE_CUSTOMERS_STATUS, $sql_data_array, 'update', "customers_status_id = '" . xtc_db_input($customers_status_id) . "' AND language_id = '" . $language_id . "'");
          }
        } # end of languages for-loop

        if ($action == 'insert') {
          // Check if table exists and delete it first
          xtc_db_query("DROP TABLE IF EXISTS personal_offers_by_customers_status_" . $customers_status_id);

          // We want to create a personal offer table corresponding to each customers_status
          xtc_db_query("CREATE TABLE personal_offers_by_customers_status_" . $customers_status_id . " (price_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, products_id int NOT NULL, quantity int, personal_offer decimal(15,4))");

          // get lat group
          $last_group_table = get_table_columns(TABLE_CATEGORIES, 'group_permission_%', true);
          $last_group = key(array_slice($last_group_table, -1, 1, true));

          // Check if table column exists 
          if (!get_table_columns(TABLE_PRODUCTS,'group_permission_' . $customers_status_id)) {
            xtc_db_query("ALTER TABLE ".TABLE_PRODUCTS." ADD group_permission_" . $customers_status_id . " TINYINT( 1 ) NOT NULL AFTER `".$last_group."`");
          }

          // Check if table column exists
          if (!get_table_columns(TABLE_CATEGORIES,'group_permission_' . $customers_status_id)) {
            xtc_db_query("ALTER TABLE ".TABLE_CATEGORIES." ADD group_permission_" . $customers_status_id . " TINYINT( 1 ) NOT NULL AFTER `".$last_group."`");
          }
        }

        // adopt customer group permission
        if (isset($_POST['customers_group_adopt_permission']) && $_POST['customers_group_adopt_permission'] !== '') {
          $adopt_permission = (int)$_POST['customers_group_adopt_permission'];
          // categories
          $adopt_categories_permission = xtc_db_query("SELECT categories_id, group_permission_".$adopt_permission." FROM " . TABLE_CATEGORIES);
          while($adopt_catp = xtc_db_fetch_array($adopt_categories_permission)) {
            xtc_db_query("UPDATE ".TABLE_CATEGORIES."
                             SET group_permission_" . $customers_status_id . "=".$adopt_catp['group_permission_'.$adopt_permission]."
                           WHERE categories_id=".$adopt_catp['categories_id']);
          }
          // products
          $adopt_products_permission = xtc_db_query("SELECT products_id, group_permission_".$adopt_permission." FROM " . TABLE_PRODUCTS);
          while($adopt_pp = xtc_db_fetch_array($adopt_products_permission)) {
            xtc_db_query("UPDATE ".TABLE_PRODUCTS."
                             SET group_permission_" . $customers_status_id . "=".$adopt_pp['group_permission_'.$adopt_permission]." 
                           WHERE products_id=".$adopt_pp['products_id']);
          }
          // content
          $adopt_content_permission = xtc_db_query("SELECT content_id, group_ids FROM " . TABLE_CONTENT_MANAGER . " WHERE group_ids LIKE '%c_".$adopt_permission."_group%'");
          while ($adopt_cp = xtc_db_fetch_array($adopt_content_permission)) {
            xtc_db_query("UPDATE " . TABLE_CONTENT_MANAGER . "
                             SET group_ids=CONCAT(group_ids, ',c_" . $customers_status_id . "_group')
                           WHERE content_id=" . $adopt_cp['content_id'] . "
                             AND group_ids NOT LIKE 'c_" . $customers_status_id . "_group'");
          }
        }

        // adopt customer prices
        if (isset($_POST['customers_base_status']) && !empty($_POST['customers_base_status'])) {
          if ($action == 'save') {
            xtc_db_query('TRUNCATE TABLE personal_offers_by_customers_status_' . $customers_status_id);
          }
          $products_query = xtc_db_query("SELECT price_id, products_id, quantity, personal_offer FROM personal_offers_by_customers_status_".(int)$_POST['customers_base_status']."");
          while($products = xtc_db_fetch_array($products_query)){
            $product_data_array = array(
                'price_id' => xtc_db_prepare_input($products['price_id']),
                'products_id' => xtc_db_prepare_input($products['products_id']),
                'quantity' => xtc_db_prepare_input($products['quantity']),
                'personal_offer' => xtc_db_prepare_input($products['personal_offer'])
              );
            xtc_db_perform('personal_offers_by_customers_status_' . $customers_status_id, $product_data_array);
          }
        }

        $accepted_customers_status_image_files_extensions = array("jpg","jpeg","jpe","gif","png","bmp","tiff","tif","bmp");
        $accepted_customers_status_image_files_mime_types = array("image/jpeg","image/gif","image/png","image/bmp");
        if ($customers_status_image = xtc_try_upload('customers_status_image', DIR_FS_CATALOG.DIR_WS_ICONS, '644', $accepted_customers_status_image_files_extensions, $accepted_customers_status_image_files_mime_types)) {
          xtc_db_query("UPDATE " . TABLE_CUSTOMERS_STATUS . " SET customers_status_image = '" . $customers_status_image->filename . "' WHERE customers_status_id = '" . xtc_db_input($customers_status_id) . "'");
        }

        if ($_POST['default'] == 'on') {
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '" . xtc_db_input($customers_status_id) . "' WHERE configuration_key = 'DEFAULT_CUSTOMERS_STATUS_ID'");
        }
        }
        xtc_redirect(xtc_href_link(FILENAME_CUSTOMERS_STATUS, 'page=' . $_GET['page'] . '&cID=' . $customers_status_id));
        break;

      case 'deleteconfirm':
        $cID = xtc_db_prepare_input($_GET['cID']);

        $customers_status_query = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'DEFAULT_CUSTOMERS_STATUS_ID'");
        $customers_status = xtc_db_fetch_array($customers_status_query);
        if ($customers_status['configuration_value'] == $cID) {
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '' WHERE configuration_key = 'DEFAULT_CUSTOMERS_STATUS_ID'");
        }

        xtc_db_query("DELETE FROM " . TABLE_CUSTOMERS_STATUS . " WHERE customers_status_id = '" . (int)$cID . "'");

        // We want to drop the existing corresponding personal_offers table
        xtc_db_query("DROP TABLE IF EXISTS personal_offers_by_customers_status_" . (int)$cID);
        xtc_db_query("ALTER TABLE `products` DROP `group_permission_" . (int)$cID . "`");
        xtc_db_query("ALTER TABLE `categories` DROP `group_permission_" . (int)$cID . "`");
        xtc_redirect(xtc_href_link(FILENAME_CUSTOMERS_STATUS, 'page=' . (int)$_GET['page']));
        break;

      case 'delete':
        $cID = xtc_db_prepare_input($_GET['cID']);

        $status_query = xtc_db_query("SELECT COUNT(*) AS count FROM " . TABLE_CUSTOMERS . " WHERE customers_status = '" . xtc_db_input($cID) . "'");
        $status = xtc_db_fetch_array($status_query);

        $remove_status = true;
        if (($cID == DEFAULT_CUSTOMERS_STATUS_ID) || ($cID == DEFAULT_CUSTOMERS_STATUS_ID_GUEST) || ($cID == DEFAULT_CUSTOMERS_STATUS_ID_NEWSLETTER)) {
          $remove_status = false;
          $messageStack->add(ERROR_REMOVE_DEFAULT_CUSTOMERS_STATUS, 'error');
        } elseif ($status['count'] > 0) {
          $remove_status = false;
          $messageStack->add(ERROR_STATUS_USED_IN_CUSTOMERS, 'error');
        } else {
          $history_query = xtc_db_query("SELECT COUNT(*) AS count FROM " . TABLE_CUSTOMERS_STATUS_HISTORY . " WHERE '" . xtc_db_input($cID) . "' in (new_value, old_value)");
          $history = xtc_db_fetch_array($history_query);
          if ($history['count'] > 0) {
            // delete from history
            xtc_db_query("DELETE FROM " . TABLE_CUSTOMERS_STATUS_HISTORY . "
                          WHERE '" . xtc_db_input($cID) . "' in (new_value, old_value)");
            $remove_status = true;
            // $messageStack->add(ERROR_STATUS_USED_IN_HISTORY, 'error');
          }
        }
        break;
    }
  }

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
        <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_customers.png'); ?></div>
        <div class="pageHeading"><?php echo HEADING_TITLE; ?></div> 
        <div class="main pdg2 flt-l"><?php echo BOX_HEADING_CUSTOMERS; ?></div>        
          <table class="tableCenter">      
          <tr>
            <td class="boxCenterLeft">
              <table class="tableBoxCenter collapse">
                <tr class="dataTableHeadingRow" style="line-height:18px;">
                  <td class="dataTableHeadingContent"><?php echo 'cID'; ?></td>
                  <td class="dataTableHeadingContent"><?php echo 'icon'; ?></td>
                  <td class="dataTableHeadingContent"><?php echo 'user'; ?></td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERS_STATUS; ?></td>
                  <td class="dataTableHeadingContent txta-c"><?php echo TABLE_HEADING_TAX_PRICE; ?></td>
                  <td class="dataTableHeadingContent txta-c" colspan="2"><?php echo TABLE_HEADING_DISCOUNT; ?></td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERS_GRADUATED; ?></td>
                  <td class="dataTableHeadingContent txta-c"><?php echo TABLE_HEADING_CUSTOMERS_UNALLOW; ?></td>
                  <td class="dataTableHeadingContent txta-c"><?php echo TABLE_HEADING_CUSTOMERS_UNALLOW_SHIPPING; ?></td>
                  <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                </tr>
                <?php
                $customers_status_ot_discount_flag_array = array(array('id' => '0', 'text' => ENTRY_NO), array('id' => '1', 'text' => ENTRY_YES));
                $customers_status_graduated_prices_array = array(array('id' => '0', 'text' => ENTRY_NO), array('id' => '1', 'text' => ENTRY_YES));
                $customers_status_public_array = array(array('id' => '0', 'text' => ENTRY_NO), array('id' => '1', 'text' => ENTRY_YES));
                $customers_status_show_price_array = array(array('id' => '0', 'text' => ENTRY_NO), array('id' => '1', 'text' => ENTRY_YES));
                $customers_status_show_price_tax_array = array(array('id' => '0', 'text' => ENTRY_NO), array('id' => '1', 'text' => ENTRY_YES));
                $customers_status_discount_attributes_array = array(array('id' => '0', 'text' => ENTRY_NO), array('id' => '1', 'text' => ENTRY_YES));
                $customers_status_add_tax_ot_array = array(array('id' => '0', 'text' => ENTRY_NO), array('id' => '1', 'text' => ENTRY_YES));
                $customers_fsk18_array = array(array('id' => '0', 'text' => ENTRY_NO), array('id' => '1', 'text' => ENTRY_YES));
                $customers_fsk18_display_array = array(array('id' => '0', 'text' => ENTRY_NO), array('id' => '1', 'text' => ENTRY_YES));
                $customers_status_write_reviews_array = array(array('id' => '0', 'text' => ENTRY_NO), array('id' => '1', 'text' => ENTRY_YES));
                $customers_status_read_reviews_array = array(array('id' => '0', 'text' => ENTRY_NO), array('id' => '1', 'text' => ENTRY_YES));
                $customers_status_query_raw = "select * from " . TABLE_CUSTOMERS_STATUS . " where language_id = '" . (int)$_SESSION['languages_id'] . "' order by customers_status_id";

                $customers_status_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $customers_status_query_raw, $customers_status_query_numrows);
                $customers_status_query = xtc_db_query($customers_status_query_raw);
                while ($customers_status = xtc_db_fetch_array($customers_status_query)) {
                  if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ($_GET['cID'] == $customers_status['customers_status_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
                    $cInfo = new objectInfo($customers_status);
                  }

                  if (isset($cInfo) && is_object($cInfo) && ($customers_status['customers_status_id'] == $cInfo->customers_status_id) ) {
                    $tr_attributes = 'class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_CUSTOMERS_STATUS, 'page=' . $_GET['page'] . '&cID=' . $cInfo->customers_status_id . '&action=edit') .'\'"';
                  } else {
                    $tr_attributes = 'class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_CUSTOMERS_STATUS, 'page=' . $_GET['page'] . '&cID=' . $customers_status['customers_status_id']) .'\'"';
                  }
                  ?>
                  <tr <?php echo $tr_attributes;?>>
                    <td class="dataTableContent txta-c"><?php echo $customers_status['customers_status_id'];?></td>
                    <td class="dataTableContent txta-c"><?php echo $customers_status['customers_status_image'] != '' ? xtc_image(DIR_WS_CATALOG.DIR_WS_ICONS . $customers_status['customers_status_image'] , IMAGE_ICON_INFO) : '&nbsp;'?></td>
                    <td class="dataTableContent txta-c"><?php echo xtc_get_status_users($customers_status['customers_status_id']);?></td>
                    <td class="dataTableContent"><?php echo $customers_status['customers_status_name'] . ($customers_status['customers_status_id'] == DEFAULT_CUSTOMERS_STATUS_ID ? ' (' . TEXT_DEFAULT . ')' : '') . ($customers_status['customers_status_public'] == '1' ? ' ,public ' : '');?></td>
                    <td class="dataTableContent txta-c"><?php echo $customers_status['customers_status_show_price'] == '1' ? ($customers_status['customers_status_show_price_tax'] == '1' ? TAX_YES : TAX_NO) : '---' ;?></td>
                    <td class="dataTableContent txta-c"><?php echo $customers_status['customers_status_discount'];?> %</td>
                    <td class="dataTableContent txta-c"><?php echo ($customers_status['customers_status_ot_discount_flag'] == 0 ? '<span class="colorRed">' : '<span>' ).$customers_status['customers_status_ot_discount'];?> %</span></td>
                    <td class="dataTableContent txta-c"><?php echo $customers_status['customers_status_graduated_prices'] == 0 ? NO : YES;?></td>
                    <td class="dataTableContent txta-c"><?php echo $customers_status['customers_status_payment_unallowed'];?></td>
                    <td class="dataTableContent txta-c"><?php echo $customers_status['customers_status_shipping_unallowed'];?></td>
                    <td class="dataTableContent txta-r"><?php if (isset($cInfo) && is_object($cInfo) && ($customers_status['customers_status_id'] == $cInfo->customers_status_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_CUSTOMERS_STATUS, 'page=' . $_GET['page'] . '&cID=' . $customers_status['customers_status_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                  </tr>
                  <?php
                }
                ?>
                <tr>                          
              </table>
              <div class="smallText pdg2 flt-l"><?php echo $customers_status_split->display_count($customers_status_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_CUSTOMERS_STATUS); ?></div>
              <div class="smallText pdg2 flt-r"><?php echo $customers_status_split->display_links($customers_status_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></div>
              <?php
              if (empty($action)) {
                ?>
                <div class="clear"></div>
                <div class="pdg2 flt-r"><?php echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CUSTOMERS_STATUS, 'page=' . $_GET['page'] . '&action=new') . '">' . BUTTON_INSERT . '</a>'; ?></div>
                <?php
              }
              ?>        
              
            </td>
            <?php
              $heading = array();
              $contents = array();
              switch ($action) {
                case 'new':
                  $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_CUSTOMERS_STATUS . '</b>');
                  $contents = array('form' => xtc_draw_form('status', FILENAME_CUSTOMERS_STATUS, 'page=' . $_GET['page'] . '&action=insert', 'post', 'enctype="multipart/form-data"'));
                  $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
                  $customers_status_inputs_string = '';
                  $languages = xtc_get_languages();
                  for ($i=0; $i<sizeof($languages); $i++) {
                    $customers_status_inputs_string .= '<br />' . xtc_image(DIR_WS_CATALOG.'lang/'.$languages[$i]['directory'].'/admin/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . xtc_draw_input_field('customers_status_name[' . $languages[$i]['id'] . ']');
                  }
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_NAME . $customers_status_inputs_string);
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_IMAGE . '<br />' . xtc_draw_file_field('customers_status_image') . ' (jpg,jpeg,jpe,gif,png,bmp,tiff,tif,bmp)');
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_PUBLIC_INTRO . '<br />' . ENTRY_CUSTOMERS_STATUS_PUBLIC . ' ' . xtc_draw_pull_down_menu('customers_status_public', $customers_status_public_array, $cInfo->customers_status_public ));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_MIN_ORDER_INTRO . '<br />' . ENTRY_CUSTOMERS_STATUS_MIN_ORDER . ' ' . xtc_draw_input_field('customers_status_min_order', $cInfo->customers_status_min_order ));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_MAX_ORDER_INTRO . '<br />' . ENTRY_CUSTOMERS_STATUS_MAX_ORDER . ' ' . xtc_draw_input_field('customers_status_max_order', $cInfo->customers_status_max_order ));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_SHOW_PRICE_INTRO     . '<br />' . ENTRY_CUSTOMERS_STATUS_SHOW_PRICE . ' ' . xtc_draw_pull_down_menu('customers_status_show_price', $customers_status_show_price_array, $cInfo->customers_status_show_price ));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_SHOW_PRICE_TAX_INTRO . '<br />' . ENTRY_CUSTOMERS_STATUS_SHOW_PRICE_TAX . ' ' . xtc_draw_pull_down_menu('customers_status_show_price_tax', $customers_status_show_price_tax_array, $cInfo->customers_status_show_price_tax ));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_ADD_TAX_INTRO . '<br />' . ENTRY_CUSTOMERS_STATUS_ADD_TAX . ' ' . xtc_draw_pull_down_menu('customers_status_add_tax_ot', $customers_status_add_tax_ot_array, $cInfo->customers_status_add_tax_ot));             
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_SHOW_PRICE_TAX_TOTAL . '<br />' . ENTRY_CUSTOMERS_STATUS_SHOW_PRICE_TAX_TOTAL . ' ' . xtc_draw_input_field('customers_status_show_tax_total', $cInfo->customers_status_show_tax_total ));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_PRICE_INTRO . '<br />' . TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_PRICE . '<br />' . xtc_draw_input_field('customers_status_discount', $cInfo->customers_status_discount));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_OT_XMEMBER_INTRO . '<br /> ' . ENTRY_OT_XMEMBER . ' ' . xtc_draw_pull_down_menu('customers_status_ot_discount_flag', $customers_status_ot_discount_flag_array, $cInfo->customers_status_ot_discount_flag ). '<br />' . TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_PRICE . '<br />' . xtc_draw_input_field('customers_status_ot_discount', $cInfo->customers_status_ot_discount));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_GRADUATED_PRICES_INTRO . '<br />' . ENTRY_GRADUATED_PRICES . ' ' . xtc_draw_pull_down_menu('customers_status_graduated_prices', $customers_status_graduated_prices_array, $cInfo->customers_status_graduated_prices ));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_ATTRIBUTES_INTRO . '<br />' . ENTRY_CUSTOMERS_STATUS_DISCOUNT_ATTRIBUTES . ' ' . xtc_draw_pull_down_menu('customers_status_discount_attributes', $customers_status_discount_attributes_array, $cInfo->customers_status_discount_attributes ));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_PAYMENT_UNALLOWED_INTRO . '<br />' . ENTRY_CUSTOMERS_STATUS_PAYMENT_UNALLOWED . '<br/>' . xtc_cfg_checkbox_unallowed_module('payment','customers_status_payment_unallowed',$cInfo->customers_status_payment_unallowed) );
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_SHIPPING_UNALLOWED_INTRO . '<br />' . ENTRY_CUSTOMERS_STATUS_SHIPPING_UNALLOWED . '<br/>' . xtc_cfg_checkbox_unallowed_module('shipping','customers_status_shipping_unallowed',$cInfo->customers_status_shipping_unallowed) );                          
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_FSK18_INTRO . '<br />' . ENTRY_CUSTOMERS_FSK18 . ' ' . xtc_draw_pull_down_menu('customers_fsk18', $customers_fsk18_array, $cInfo->customers_fsk18));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_FSK18_DISPLAY_INTRO . '<br />' . ENTRY_CUSTOMERS_FSK18_DISPLAY . ' ' . xtc_draw_pull_down_menu('customers_fsk18_display', $customers_fsk18_display_array, $cInfo->customers_fsk18_display));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_WRITE_REVIEWS_INTRO . '<br />' . ENTRY_CUSTOMERS_STATUS_WRITE_REVIEWS . ' ' . xtc_draw_pull_down_menu('customers_status_write_reviews', $customers_status_write_reviews_array, $cInfo->customers_status_write_reviews));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_READ_REVIEWS_INTRO . '<br />' . ENTRY_CUSTOMERS_STATUS_READ_REVIEWS . ' ' . xtc_draw_pull_down_menu('customers_status_read_reviews', $customers_status_read_reviews_array, $cInfo->customers_status_read_reviews));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_BASE . '<br />' . ENTRY_CUSTOMERS_STATUS_BASE . '<br />' . xtc_draw_pull_down_menu('customers_base_status', xtc_get_customers_statuses()));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_GROUP_ADOPT_PERMISSION . '<br />' . ENTRY_CUSTOMERS_GROUP_ADOPT_PERMISSION . '<br />' . xtc_draw_pull_down_menu('customers_group_adopt_permission', array_merge(array(array('id' => '', 'text' => CUSTOMERS_GROUP_ADOPT_PERMISSIONS)), xtc_get_customers_statuses())));
                  $contents[] = array('text' => '<br />' . xtc_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
                  $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_INSERT . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CUSTOMERS_STATUS, 'page=' . $_GET['page']) . '">' . BUTTON_CANCEL . '</a>');
                  break;

                case 'edit':
                  $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_CUSTOMERS_STATUS . '</b>');
                  $contents = array('form' => xtc_draw_form('status', FILENAME_CUSTOMERS_STATUS, 'page=' . $_GET['page'] . '&cID=' . $cInfo->customers_status_id  .'&action=save', 'post', 'enctype="multipart/form-data"'));
                  $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
                  $customers_status_inputs_string = '';
                  $languages = xtc_get_languages();
                  for ($i=0; $i<sizeof($languages); $i++) {
                    $customers_status_inputs_string .= '<br />' . xtc_image(DIR_WS_CATALOG.'lang/'.$languages[$i]['directory'].'/admin/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . xtc_draw_input_field('customers_status_name[' . $languages[$i]['id'] . ']', xtc_get_customers_status_name($cInfo->customers_status_id, $languages[$i]['id']));
                  }

                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_NAME . $customers_status_inputs_string);
                  $contents[] = array('text' => '<br />' . xtc_image(DIR_WS_CATALOG.DIR_WS_ICONS . $cInfo->customers_status_image, $cInfo->customers_status_name) . '<br />' . DIR_WS_CATALOG.DIR_WS_ICONS . '<b>' . $cInfo->customers_status_image . '</b>'); 
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_IMAGE . '<br />' . xtc_draw_file_field('customers_status_image', $cInfo->customers_status_image) . ' (jpg,jpeg,jpe,gif,png,bmp,tiff,tif,bmp)');
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_PUBLIC_INTRO . '<br />' . ENTRY_CUSTOMERS_STATUS_PUBLIC . ' ' . xtc_draw_pull_down_menu('customers_status_public', $customers_status_public_array, $cInfo->customers_status_public ));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_MIN_ORDER_INTRO . '<br />' . ENTRY_CUSTOMERS_STATUS_MIN_ORDER . ' ' . xtc_draw_input_field('customers_status_min_order', $cInfo->customers_status_min_order ));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_MAX_ORDER_INTRO . '<br />' . ENTRY_CUSTOMERS_STATUS_MAX_ORDER . ' ' . xtc_draw_input_field('customers_status_max_order', $cInfo->customers_status_max_order ));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_SHOW_PRICE_INTRO     . '<br />' . ENTRY_CUSTOMERS_STATUS_SHOW_PRICE . ' ' . xtc_draw_pull_down_menu('customers_status_show_price', $customers_status_show_price_array, $cInfo->customers_status_show_price ));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_SHOW_PRICE_TAX_INTRO . '<br />' . ENTRY_CUSTOMERS_STATUS_SHOW_PRICE_TAX . ' ' . xtc_draw_pull_down_menu('customers_status_show_price_tax', $customers_status_show_price_tax_array, $cInfo->customers_status_show_price_tax ));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_ADD_TAX_INTRO . '<br />' . ENTRY_CUSTOMERS_STATUS_ADD_TAX . ' ' . xtc_draw_pull_down_menu('customers_status_add_tax_ot', $customers_status_add_tax_ot_array, $cInfo->customers_status_add_tax_ot));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_SHOW_PRICE_TAX_TOTAL . '<br />' . ENTRY_CUSTOMERS_STATUS_SHOW_PRICE_TAX_TOTAL . ' ' . xtc_draw_input_field('customers_status_show_tax_total', $cInfo->customers_status_show_tax_total ));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_PRICE_INTRO . '<br />' . TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_PRICE . ' ' . xtc_draw_input_field('customers_status_discount', $cInfo->customers_status_discount));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_ATTRIBUTES_INTRO . '<br />' . ENTRY_CUSTOMERS_STATUS_DISCOUNT_ATTRIBUTES . ' ' . xtc_draw_pull_down_menu('customers_status_discount_attributes', $customers_status_discount_attributes_array, $cInfo->customers_status_discount_attributes ));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_OT_XMEMBER_INTRO . '<br /> ' . ENTRY_OT_XMEMBER . ' ' . xtc_draw_pull_down_menu('customers_status_ot_discount_flag', $customers_status_ot_discount_flag_array, $cInfo->customers_status_ot_discount_flag). '<br />' . TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_PRICE . ' ' . xtc_draw_input_field('customers_status_ot_discount', $cInfo->customers_status_ot_discount));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_GRADUATED_PRICES_INTRO . '<br />' . ENTRY_GRADUATED_PRICES . ' ' . xtc_draw_pull_down_menu('customers_status_graduated_prices', $customers_status_graduated_prices_array, $cInfo->customers_status_graduated_prices));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_PAYMENT_UNALLOWED_INTRO . '<br />' . ENTRY_CUSTOMERS_STATUS_PAYMENT_UNALLOWED . '<br/>' . xtc_cfg_checkbox_unallowed_module('payment','customers_status_payment_unallowed',$cInfo->customers_status_payment_unallowed) );
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_SHIPPING_UNALLOWED_INTRO . '<br />' . ENTRY_CUSTOMERS_STATUS_SHIPPING_UNALLOWED . '<br/>' . xtc_cfg_checkbox_unallowed_module('shipping','customers_status_shipping_unallowed',$cInfo->customers_status_shipping_unallowed) );                          
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_FSK18_INTRO . '<br />' . ENTRY_CUSTOMERS_FSK18 . ' ' . xtc_draw_pull_down_menu('customers_fsk18', $customers_fsk18_array, $cInfo->customers_fsk18 ));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_FSK18_DISPLAY_INTRO . '<br />' . ENTRY_CUSTOMERS_FSK18_DISPLAY . ' ' . xtc_draw_pull_down_menu('customers_fsk18_display', $customers_fsk18_display_array, $cInfo->customers_fsk18_display));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_WRITE_REVIEWS_INTRO . '<br />' . ENTRY_CUSTOMERS_STATUS_WRITE_REVIEWS . ' ' . xtc_draw_pull_down_menu('customers_status_write_reviews', $customers_status_write_reviews_array, $cInfo->customers_status_write_reviews));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_READ_REVIEWS_INTRO . '<br />' . ENTRY_CUSTOMERS_STATUS_READ_REVIEWS . ' ' . xtc_draw_pull_down_menu('customers_status_read_reviews', $customers_status_read_reviews_array, $cInfo->customers_status_read_reviews));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_BASE . '<br />' . ENTRY_CUSTOMERS_STATUS_BASE_EDIT . '<br />' . xtc_draw_pull_down_menu('customers_base_status', xtc_get_customers_statuses()));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_GROUP_ADOPT_PERMISSION . '<br />' . ENTRY_CUSTOMERS_GROUP_ADOPT_PERMISSION . '<br />' . xtc_draw_pull_down_menu('customers_group_adopt_permission', array_merge(array(array('id' => '', 'text' => CUSTOMERS_GROUP_ADOPT_PERMISSIONS)), xtc_get_customers_statuses())));
                  if (DEFAULT_CUSTOMERS_STATUS_ID != $cInfo->customers_status_id)
                    $contents[] = array('text' => '<br />' . xtc_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
                  $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UPDATE . '"> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CUSTOMERS_STATUS, 'page=' . $_GET['page'] . '&cID=' . $cInfo->customers_status_id) . '">' . BUTTON_CANCEL . '</a>');
                  break;

                case 'delete':
                  $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_CUSTOMERS_STATUS . '</b>');

                  $contents = array('form' => xtc_draw_form('status', FILENAME_CUSTOMERS_STATUS, 'page=' . $_GET['page'] . '&cID=' . $cInfo->customers_status_id  . '&action=deleteconfirm'));
                  $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
                  $contents[] = array('text' => '<br /><b>' . $cInfo->customers_status_name . '</b>');

                  if ($remove_status)
                    $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CUSTOMERS_STATUS, 'page=' . $_GET['page'] . '&cID=' . $cInfo->customers_status_id) . '">' . BUTTON_CANCEL . '</a>');
                  break;

                default:
                  if (isset($cInfo) && is_object($cInfo)) {
                    $heading[] = array('text' => '<b>' . $cInfo->customers_status_name . '</b>');

                    $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CUSTOMERS_STATUS, 'page=' . $_GET['page'] . '&cID=' . $cInfo->customers_status_id . '&action=edit') . '">' . BUTTON_EDIT . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CUSTOMERS_STATUS, 'page=' . $_GET['page'] . '&cID=' . $cInfo->customers_status_id . '&action=delete') . '">' . BUTTON_DELETE . '</a>');
                    $customers_status_inputs_string = '';
                    $languages = xtc_get_languages();
                    for ($i=0; $i<sizeof($languages); $i++) {
                      $customers_status_inputs_string .= '<br />' . xtc_image(DIR_WS_CATALOG.'lang/'. $languages[$i]['directory'] . '/admin/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . xtc_get_customers_status_name($cInfo->customers_status_id, $languages[$i]['id']);
                    }
                    $contents[] = array('text' => $customers_status_inputs_string);
                    $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_SHOW_PRICE_INTRO. '<br />' . ENTRY_CUSTOMERS_STATUS_SHOW_PRICE . ': ' . $customers_status_show_price_array[$cInfo->customers_status_show_price]['text'] . ' (' . $cInfo->customers_status_show_price . ')');
                    $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_SHOW_PRICE_TAX_INTRO. '<br />' . ENTRY_CUSTOMERS_STATUS_SHOW_PRICE_TAX . ': ' . $customers_status_show_price_tax_array[$cInfo->customers_status_show_price_tax]['text'] . ' (' . $cInfo->customers_status_show_price_tax . ')');
                    $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_ADD_TAX_INTRO. '<br />' . ENTRY_CUSTOMERS_STATUS_ADD_TAX . ': ' . $customers_status_add_tax_ot_array[$cInfo->customers_status_add_tax_ot]['text'] . ' (' . $cInfo->customers_status_add_tax_ot . ')');
                    $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_SHOW_PRICE_TAX_TOTAL . '<br />' . ENTRY_CUSTOMERS_STATUS_SHOW_PRICE_TAX_TOTAL . ': ' . $cInfo->customers_status_show_tax_total);
                    $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_PRICE_INTRO . '<br />' . TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_PRICE . ' ' . $cInfo->customers_status_discount . '%');
                    $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_OT_XMEMBER_INTRO . '<br />' . ENTRY_OT_XMEMBER . ' ' . $customers_status_ot_discount_flag_array[$cInfo->customers_status_ot_discount_flag]['text'] . ' (' . $cInfo->customers_status_ot_discount_flag . ')' . ' - ' . $cInfo->customers_status_ot_discount . '%');
                    $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_GRADUATED_PRICES_INTRO . '<br />' . ENTRY_GRADUATED_PRICES . ' ' . $customers_status_graduated_prices_array[$cInfo->customers_status_graduated_prices]['text'] . ' (' . $cInfo->customers_status_graduated_prices . ')' );
                    $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_DISCOUNT_ATTRIBUTES_INTRO . '<br />' . ENTRY_CUSTOMERS_STATUS_DISCOUNT_ATTRIBUTES . ' ' . $customers_status_discount_attributes_array[$cInfo->customers_status_discount_attributes]['text'] . ' (' . $cInfo->customers_status_discount_attributes . ')' );
                    $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_PAYMENT_UNALLOWED_INTRO . '<br />' . ENTRY_CUSTOMERS_STATUS_PAYMENT_UNALLOWED . ':<b> ' . $cInfo->customers_status_payment_unallowed.'</b>');
                    $contents[] = array('text' => '<br />' . TEXT_INFO_CUSTOMERS_STATUS_SHIPPING_UNALLOWED_INTRO . '<br />' . ENTRY_CUSTOMERS_STATUS_SHIPPING_UNALLOWED . ':<b> ' . $cInfo->customers_status_shipping_unallowed.'</b>');
                  }
                  break;
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