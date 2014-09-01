<?php
/* --------------------------------------------------------------
   $Id: languages.php 1180 2005-08-26 08:44:53Z novalis $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(languages.php,v 1.33 2003/05/07); www.oscommerce.com 
   (c) 2003	 nextcommerce (languages.php,v 1.10 2003/08/18); www.nextcommerce.org

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

  require('includes/application_top.php');

  switch ($_GET['action']) {
// BOF - Tomcraft - 2009-11-22 - Added option to deactivate languages (clickable status icons)
    case 'setlflag':
        $language_id = xtc_db_prepare_input($_GET['lID']);
        $status = xtc_db_prepare_input($_GET['flag']);      
        xtc_db_query("update " . TABLE_LANGUAGES . " set status = '" . xtc_db_input($status) . "' where languages_id = '" . xtc_db_input($language_id) . "'");
        xtc_redirect(xtc_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $language_id));      
      break;
// BOF - Tomcraft - 2009-11-22 - Added option to deactivate languages (clickable status icons)
    case 'insert':
      $name = xtc_db_prepare_input($_POST['name']);
      $code = xtc_db_prepare_input($_POST['code']);
      $image = xtc_db_prepare_input($_POST['image']);
      $directory = xtc_db_prepare_input($_POST['directory']);
// BOF - Tomcraft - 2009-11-08 - Added option to deactivate languages
      $status = xtc_db_prepare_input($_POST['status']);
// EOF - Tomcraft - 2009-11-08 - Added option to deactivate languages
      $sort_order = xtc_db_prepare_input($_POST['sort_order']);
      $charset = xtc_db_prepare_input($_POST['charset']);

// BOF - Tomcraft - 2009-11-08 - Added option to deactivate languages
      //xtc_db_query("insert into " . TABLE_LANGUAGES . " (name, code, image, directory, sort_order,language_charset) values ('" . xtc_db_input($name) . "', '" . xtc_db_input($code) . "', '" . xtc_db_input($image) . "', '" . xtc_db_input($directory) . "', '" . xtc_db_input($sort_order) . "', '" . xtc_db_input($charset) . "')");
      xtc_db_query("insert into " . TABLE_LANGUAGES . " (name, code, image, directory, status, sort_order,language_charset) values ('" . xtc_db_input($name) . "', '" . xtc_db_input($code) . "', '" . xtc_db_input($image) . "', '" . xtc_db_input($directory) . "', '" . xtc_db_input($status) . "', '" . xtc_db_input($sort_order) . "', '" . xtc_db_input($charset) . "')");
// EOF - Tomcraft - 2009-11-08 - Added option to deactivate languages
	  $insert_id = xtc_db_insert_id();

      // create additional categories_description records
      $categories_query = xtc_db_query("select c.categories_id, cd.categories_name from " . TABLE_CATEGORIES . " c left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id = cd.categories_id where cd.language_id = '" . $_SESSION['languages_id'] . "'");
      while ($categories = xtc_db_fetch_array($categories_query)) {
        xtc_db_query("insert into " . TABLE_CATEGORIES_DESCRIPTION . " (categories_id, language_id, categories_name) values ('" . $categories['categories_id'] . "', '" . $insert_id . "', '" . xtc_db_input($categories['categories_name']) . "')");
      }

      // create additional products_description records
      $products_query = xtc_db_query("select p.products_id, pd.products_name, pd.products_description, pd.products_url from " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id where pd.language_id = '" . $_SESSION['languages_id'] . "'");
      while ($products = xtc_db_fetch_array($products_query)) {
        xtc_db_query("insert into " . TABLE_PRODUCTS_DESCRIPTION . " (products_id, language_id, products_name, products_description, products_url) values ('" . $products['products_id'] . "', '" . $insert_id . "', '" . xtc_db_input($products['products_name']) . "', '" . xtc_db_input($products['products_description']) . "', '" . xtc_db_input($products['products_url']) . "')");
      }

      // create additional products_options records
      $products_options_query = xtc_db_query("select products_options_id, products_options_name from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . $_SESSION['languages_id'] . "'");
      while ($products_options = xtc_db_fetch_array($products_options_query)) {
        xtc_db_query("insert into " . TABLE_PRODUCTS_OPTIONS . " (products_options_id, language_id, products_options_name) values ('" . $products_options['products_options_id'] . "', '" . $insert_id . "', '" . xtc_db_input($products_options['products_options_name']) . "')");
      }

      // create additional products_options_values records
      $products_options_values_query = xtc_db_query("select products_options_values_id, products_options_values_name from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where language_id = '" . $_SESSION['languages_id'] . "'");
      while ($products_options_values = xtc_db_fetch_array($products_options_values_query)) {
        xtc_db_query("insert into " . TABLE_PRODUCTS_OPTIONS_VALUES . " (products_options_values_id, language_id, products_options_values_name) values ('" . $products_options_values['products_options_values_id'] . "', '" . $insert_id . "', '" . xtc_db_input($products_options_values['products_options_values_name']) . "')");
      }

      // create additional manufacturers_info records
      $manufacturers_query = xtc_db_query("select m.manufacturers_id, mi.manufacturers_url from " . TABLE_MANUFACTURERS . " m left join " . TABLE_MANUFACTURERS_INFO . " mi on m.manufacturers_id = mi.manufacturers_id where mi.languages_id = '" . $_SESSION['languages_id'] . "'");
      while ($manufacturers = xtc_db_fetch_array($manufacturers_query)) {
        xtc_db_query("insert into " . TABLE_MANUFACTURERS_INFO . " (manufacturers_id, languages_id, manufacturers_url) values ('" . $manufacturers['manufacturers_id'] . "', '" . $insert_id . "', '" . xtc_db_input($manufacturers['manufacturers_url']) . "')");
      }

      // create additional orders_status records
      $orders_status_query = xtc_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . $_SESSION['languages_id'] . "'");
      while ($orders_status = xtc_db_fetch_array($orders_status_query)) {
        xtc_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $orders_status['orders_status_id'] . "', '" . $insert_id . "', '" . xtc_db_input($orders_status['orders_status_name']) . "')");
      }
      
      // create additional shipping_status records
      $shipping_status_query = xtc_db_query("select shipping_status_id, shipping_status_name from " . TABLE_SHIPPING_STATUS . " where language_id = '" . $_SESSION['languages_id'] . "'");
      while ($shipping_status = xtc_db_fetch_array($shipping_status_query)) {
        xtc_db_query("insert into " . TABLE_SHIPPING_STATUS . " (shipping_status_id, language_id, shipping_status_name) values ('" . $shipping_status['shipping_status_id'] . "', '" . $insert_id . "', '" . xtc_db_input($shipping_status['shipping_status_name']) . "')");
      }
      
      // create additional orders_status records
      $xsell_grp_query = xtc_db_query("select products_xsell_grp_name_id,xsell_sort_order, groupname from " . TABLE_PRODUCTS_XSELL_GROUPS . " where language_id = '" . $_SESSION['languages_id'] . "'");
      while ($xsell_grp = xtc_db_fetch_array($xsell_grp_query)) {
        xtc_db_query("insert into " . TABLE_PRODUCTS_XSELL_GROUPS . " (products_xsell_grp_name_id,xsell_sort_order, language_id, groupname) values ('" . $xsell_grp['products_xsell_grp_name_id'] . "','" . $xsell_grp['xsell_sort_order'] . "', '" . $insert_id . "', '" . xtc_db_input($xsell_grp['groupname']) . "')");
      }
      
      // create additional customers status
            $customers_status_query=xtc_db_query("SELECT DISTINCT customers_status_id 
      						FROM ".TABLE_CUSTOMERS_STATUS);
      while ($data=xtc_db_fetch_array($customers_status_query)) {
 
      $customers_status_data_query=xtc_db_query("SELECT * 
      						FROM ".TABLE_CUSTOMERS_STATUS."
      						WHERE customers_status_id='".$data['customers_status_id']."'"); 
      
      $group_data=xtc_db_fetch_array($customers_status_data_query);
 	$c_data=array(
 		'customers_status_id'=>$data['customers_status_id'],
 		'language_id'=>$insert_id,
 		'customers_status_name'=>$group_data['customers_status_name'],
 		'customers_status_public'=>$group_data['customers_status_public'],
 		'customers_status_image'=>$group_data['customers_status_image'],
 		'customers_status_discount'=>$group_data['customers_status_discount'],
 		'customers_status_ot_discount_flag'=>$group_data['customers_status_ot_discount_flag'],
 		'customers_status_ot_discount'=>$group_data['customers_status_ot_discount'],
 		'customers_status_graduated_prices'=>$group_data['customers_status_graduated_prices'],
 		'customers_status_show_price'=>$group_data['customers_status_show_price'],
 		'customers_status_show_price_tax'=>$group_data['customers_status_show_price_tax'],
 		'customers_status_add_tax_ot'=>$group_data['customers_status_add_tax_ot'],
 		'customers_status_payment_unallowed'=>$group_data['customers_status_payment_unallowed'],
 		'customers_status_shipping_unallowed'=>$group_data['customers_status_shipping_unallowed'],
 		'customers_status_discount_attributes'=>$group_data['customers_status_discount_attributes']);  
 		
 	xtc_db_perform(TABLE_CUSTOMERS_STATUS, $c_data);	    	
      	
	}

      if ($_POST['default'] == 'on') {
        xtc_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . xtc_db_input($code) . "' where configuration_key = 'DEFAULT_LANGUAGE'");
      }

      xtc_redirect(xtc_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $insert_id));
      break;

    case 'save':
      $lID = xtc_db_prepare_input($_GET['lID']);
      $name = xtc_db_prepare_input($_POST['name']);
      $code = xtc_db_prepare_input($_POST['code']);
      $image = xtc_db_prepare_input($_POST['image']);
      $directory = xtc_db_prepare_input($_POST['directory']);
// BOF - Tomcraft - 2009-11-08 - Added option to deactivate languages
      $status = xtc_db_prepare_input($_POST['status']);
// EOF - Tomcraft - 2009-11-08 - Added option to deactivate languages
      $sort_order = xtc_db_prepare_input($_POST['sort_order']);
      $charset = xtc_db_prepare_input($_POST['charset']);
	  
// BOF - Tomcraft - 2009-11-08 - Added option to deactivate languages
      //xtc_db_query("update " . TABLE_LANGUAGES . " set name = '" . xtc_db_input($name) . "', code = '" . xtc_db_input($code) . "', image = '" . xtc_db_input($image) . "', directory = '" . xtc_db_input($directory) . "', sort_order = '" . xtc_db_input($sort_order) . "', language_charset = '" . xtc_db_input($charset) . "' where languages_id = '" . xtc_db_input($lID) . "'");
      xtc_db_query("update " . TABLE_LANGUAGES . " set name = '" . xtc_db_input($name) . "', code = '" . xtc_db_input($code) . "', image = '" . xtc_db_input($image) . "', directory = '" . xtc_db_input($directory) . "', status = '" . xtc_db_input($status) . "', sort_order = '" . xtc_db_input($sort_order) . "', language_charset = '" . xtc_db_input($charset) . "' where languages_id = '" . xtc_db_input($lID) . "'");
// EOF - Tomcraft - 2009-11-08 - Added option to deactivate languages
	  
      if ($_POST['default'] == 'on') {
        xtc_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . xtc_db_input($code) . "' where configuration_key = 'DEFAULT_LANGUAGE'");
      }

      xtc_redirect(xtc_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $_GET['lID']));
      break;

    case 'deleteconfirm':
      $lID = xtc_db_prepare_input($_GET['lID']);

      $lng_query = xtc_db_query("select languages_id from " . TABLE_LANGUAGES . " where code = '" . DEFAULT_CURRENCY . "'");
      $lng = xtc_db_fetch_array($lng_query);
      if ($lng['languages_id'] == $lID) {
        xtc_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '' where configuration_key = 'DEFAULT_CURRENCY'");
      }

      xtc_db_query("delete from " . TABLE_CATEGORIES_DESCRIPTION . " where language_id = '" . xtc_db_input($lID) . "'");
      xtc_db_query("delete from " . TABLE_PRODUCTS_DESCRIPTION . " where language_id = '" . xtc_db_input($lID) . "'");
      xtc_db_query("delete from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . xtc_db_input($lID) . "'");
      xtc_db_query("delete from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where language_id = '" . xtc_db_input($lID) . "'");
      xtc_db_query("delete from " . TABLE_MANUFACTURERS_INFO . " where languages_id = '" . xtc_db_input($lID) . "'");
      xtc_db_query("delete from " . TABLE_ORDERS_STATUS . " where language_id = '" . xtc_db_input($lID) . "'");
      xtc_db_query("delete from " . TABLE_LANGUAGES . " where languages_id = '" . xtc_db_input($lID) . "'");
      xtc_db_query("delete from " . TABLE_CONTENT_MANAGER . " where languages_id = '" . xtc_db_input($lID) . "'");
      xtc_db_query("delete from " . TABLE_PRODUCTS_CONTENT . " where languages_id = '" . xtc_db_input($lID) . "'");
      xtc_db_query("delete from " . TABLE_CUSTOMERS_STATUS . " where language_id = '" . xtc_db_input($lID) . "'");

      xtc_redirect(xtc_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page']));
      break;

    case 'delete':
      $lID = xtc_db_prepare_input($_GET['lID']);

      $lng_query = xtc_db_query("select code from " . TABLE_LANGUAGES . " where languages_id = '" . xtc_db_input($lID) . "'");
      $lng = xtc_db_fetch_array($lng_query);

      $remove_language = true;
      if ($lng['code'] == DEFAULT_LANGUAGE) {
        $remove_language = false;
        $messageStack->add(ERROR_REMOVE_DEFAULT_LANGUAGE, 'error');
      }
	  
	  // BOF - vr - 2009-12-11 - $lng must not be an array when entering header 
	  unset($lng);
	  // EOF - vr - 2009-12-11 - $lng must not be an array when entering header 
	  
      break;
  }
  require (DIR_WS_INCLUDES.'head.php');
?>
<script type="text/javascript" src="includes/general.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onload="SetFocus();">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td class="columnLeft2" width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
    <td class="boxCenter" width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td width="80" rowspan="2"><?php echo xtc_image(DIR_WS_ICONS.'heading_configuration.gif'); ?></td>
    <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
  </tr>
  <tr>
    <td class="main" valign="top">Configuration</td>
  </tr>
</table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_LANGUAGE_NAME; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_LANGUAGE_CODE; ?></td>
<!-- BOF - Tomcraft - 2009-11-08 - Added option to deactivate languages //-->
				<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_LANGUAGE_STATUS; ?></td>				
<!-- EOF - Tomcraft - 2009-11-08 - Added option to deactivate languages //-->
				<td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
// BOF - Tomcraft - 2009-11-08 - Added option to deactivate languages
  //$languages_query_raw = "select languages_id, name, code, image, directory, sort_order,language_charset from " . TABLE_LANGUAGES . " order by sort_order";
  $languages_query_raw = "select languages_id, name, code, image, directory, status, sort_order,language_charset from " . TABLE_LANGUAGES . " order by sort_order";
// EOF - Tomcraft - 2009-11-08 - Added option to deactivate languages
  $languages_split = new splitPageResults($_GET['page'], '20', $languages_query_raw, $languages_query_numrows);
  $languages_query = xtc_db_query($languages_query_raw);

  while ($languages = xtc_db_fetch_array($languages_query)) {
    if (((!$_GET['lID']) || (@$_GET['lID'] == $languages['languages_id'])) && (!$lInfo) && (substr($_GET['action'], 0, 3) != 'new')) {
      $lInfo = new objectInfo($languages);
    }

    if ( (is_object($lInfo)) && ($languages['languages_id'] == $lInfo->languages_id) ) {
      echo '                  <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $languages['languages_id']) . '\'">' . "\n";
    }

    if (DEFAULT_LANGUAGE == $languages['code']) {
      echo '                <td class="dataTableContent"><b>' . $languages['name'] . ' (' . TEXT_DEFAULT . ')</b></td>' . "\n";
    } else {
      echo '                <td class="dataTableContent">' . $languages['name'] . '</td>' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $languages['code']; ?></td>
<!-- BOF - Tomcraft - 2009-11-22 - Added option to deactivate languages (clickable status icons) //-->
				<td class="dataTableContent">				
				<?php               
			      if ($languages['status'] == 1) {
			        echo xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10, 'style="margin-left: 5px;"') . '<a href="' . xtc_href_link(FILENAME_LANGUAGES, xtc_get_all_get_params(array('page', 'action', 'lID')) . 'action=setlflag&flag=0&lID=' . $languages['languages_id'] . '&page='.$_GET['page']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10, 'style="margin-left: 5px;"') . '</a>';
			      } else {
			        echo '<a href="' . xtc_href_link(FILENAME_LANGUAGES, xtc_get_all_get_params(array('page', 'action', 'lID')) . 'action=setlflag&flag=1&lID=' . $languages['languages_id'].'&page='.$_GET['page']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10, 'style="margin-left: 5px;"') . '</a>' . xtc_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10, 'style="margin-left: 5px;"');
			      }                
				?>
				</td>
<!-- EOF - Tomcraft - 2009-11-22 - Added option to deactivate languages (clickable status icons) //-->
<!-- BOF - Tomcraft - 2009-06-10 - added some missing alternative text on admin icons //-->
<!--
                <td class="dataTableContent" align="right"><?php if ( (is_object($lInfo)) && ($languages['languages_id'] == $lInfo->languages_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . xtc_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $languages['languages_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
//-->
                <td class="dataTableContent" align="right"><?php if ( (is_object($lInfo)) && ($languages['languages_id'] == $lInfo->languages_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $languages['languages_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
<!-- EOF - Tomcraft - 2009-06-10 - added some missing alternative text on admin icons //-->
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $languages_split->display_count($languages_query_numrows, '20', $_GET['page'], TEXT_DISPLAY_NUMBER_OF_LANGUAGES); ?></td>
                    <td class="smallText" align="right"><?php echo $languages_split->display_links($languages_query_numrows, '20', MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
<?php
  if (!$_GET['action']) {
?>
                  <tr>
                    <td align="right" colspan="2"><?php echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=new') . '">' . BUTTON_NEW_LANGUAGE . '</a>'; ?></td>
                  </tr>
<?php
  }
?>
                </table></td>
              </tr>
            </table></td>
<?php
  $direction_options = array( array('id' => '', 'text' => TEXT_INFO_LANGUAGE_DIRECTION_DEFAULT),
                              array('id' => 'ltr', 'text' => TEXT_INFO_LANGUAGE_DIRECTION_LEFT_TO_RIGHT),
                              array('id' => 'rtl', 'text' => TEXT_INFO_LANGUAGE_DIRECTION_RIGHT_TO_LEFT));

  $heading = array();
  $contents = array();
  switch ($_GET['action']) {
    case 'new':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_LANGUAGE . '</b>');

      $contents = array('form' => xtc_draw_form('languages', FILENAME_LANGUAGES, 'action=insert'));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_NAME . '<br />' . xtc_draw_input_field('name'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_CODE . '<br />' . xtc_draw_input_field('code'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_CHARSET . '<br />' . xtc_draw_input_field('charset'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_IMAGE . '<br />' . xtc_draw_input_field('image', 'icon.gif'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_DIRECTORY . '<br />' . xtc_draw_input_field('directory'));
// BOF - Tomcraft - 2009-11-08 - Added option to deactivate languages
      //$contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_SORT_ORDER . '<br />' . xtc_draw_input_field('sort_order'));
	  $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_STATUS . '<br />' . xtc_draw_input_field('status'));	  
// EOF - Tomcraft - 2009-11-08 - Added option to deactivate languages
	  $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_SORT_ORDER . '<br />' . xtc_draw_input_field('sort_order'));
      $contents[] = array('text' => '<br />' . xtc_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => '<br /><button class="button" type="submit" />' . BUTTON_INSERT . '</button> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $_GET['lID']) . '">' . BUTTON_CANCEL . '</a>');
      break;

    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_LANGUAGE . '</b>');

      $contents = array('form' => xtc_draw_form('languages', FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=save'));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_NAME . '<br />' . xtc_draw_input_field('name', $lInfo->name));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_CODE . '<br />' . xtc_draw_input_field('code', $lInfo->code));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_CHARSET . '<br />' . xtc_draw_input_field('charset', $lInfo->language_charset));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_IMAGE . '<br />' . xtc_draw_input_field('image', $lInfo->image));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_DIRECTORY . '<br />' . xtc_draw_input_field('directory', $lInfo->directory));
// BOF - Tomcraft - 2009-11-08 - Added option to deactivate languages
      //$contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_SORT_ORDER . '<br />' . xtc_draw_input_field('sort_order', $lInfo->sort_order));
	  $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_STATUS . '<br />' . xtc_draw_input_field('status', $lInfo->status));	
// EOF - Tomcraft - 2009-11-08 - Added option to deactivate languages
	  $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_SORT_ORDER . '<br />' . xtc_draw_input_field('sort_order', $lInfo->sort_order)); 
      if (DEFAULT_LANGUAGE != $lInfo->code) $contents[] = array('text' => '<br />' . xtc_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UPDATE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id) . '">' . BUTTON_CANCEL . '</a>');
      break;

    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_LANGUAGE . '</b>');

      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br /><b>' . $lInfo->name . '</b>');
      $contents[] = array('align' => 'center', 'text' => '<br />' . (($remove_language) ? '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=deleteconfirm') . '">' . BUTTON_DELETE . '</a>' : '') . ' <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id) . '">' . BUTTON_CANCEL . '</a>');
      break;

    default:
      if (is_object($lInfo)) {
        $heading[] = array('text' => '<b>' . $lInfo->name . '</b>');

        $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=edit') . '">' . BUTTON_EDIT . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=delete') . '">' . BUTTON_DELETE . '</a>');
        $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_NAME . ' ' . $lInfo->name);
        $contents[] = array('text' => TEXT_INFO_LANGUAGE_CODE . ' ' . $lInfo->code);
        $contents[] = array('text' => TEXT_INFO_LANGUAGE_CHARSET_INFO . ' ' . $lInfo->language_charset);
// BOF - Tomcraft - 2009-12-08 - show language_id
        $contents[] = array('text' => 'Language-ID:' . ' ' . $lInfo->languages_id);
// EOF - Tomcraft - 2009-12-08 - show language_id
        $contents[] = array('text' => '<br />' . xtc_image(DIR_WS_LANGUAGES . $lInfo->directory . '/' . $lInfo->image, $lInfo->name));
        $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_DIRECTORY . '<br />' . DIR_WS_LANGUAGES . '<b>' . $lInfo->directory . '</b>');
// BOF - Tomcraft - 2009-11-08 - Added option to deactivate languages
		//$contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_SORT_ORDER . ' ' . $lInfo->sort_order);
		$contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_STATUS . ' ' . $lInfo->status);	
// EOF - Tomcraft - 2009-11-08 - Added option to deactivate languages
		$contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_SORT_ORDER . ' ' . $lInfo->sort_order);
      }
      break;
  }

  if ( (xtc_not_null($heading)) && (xtc_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
    </table></td>
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