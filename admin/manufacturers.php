<?php
/* --------------------------------------------------------------
   $Id: manufacturers.php 901 2005-04-29 10:32:14Z novalis $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(manufacturers.php,v 1.52 2003/03/22); www.oscommerce.com 
   (c) 2003	 nextcommerce (manufacturers.php,v 1.9 2003/08/18); www.nextcommerce.org

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

  require('includes/application_top.php');

  switch ($_GET['action']) {
    case 'insert':
    case 'save':
      $manufacturers_id = xtc_db_prepare_input($_GET['mID']);
      $manufacturers_name = xtc_db_prepare_input($_POST['manufacturers_name']);

      $sql_data_array = array('manufacturers_name' => $manufacturers_name);

      if ($_GET['action'] == 'insert') {
        $insert_sql_data = array('date_added' => 'now()');
        $sql_data_array = xtc_array_merge($sql_data_array, $insert_sql_data);
        xtc_db_perform(TABLE_MANUFACTURERS, $sql_data_array);
        $manufacturers_id = xtc_db_insert_id();
      } elseif ($_GET['action'] == 'save') {
        $update_sql_data = array('last_modified' => 'now()');
        $sql_data_array = xtc_array_merge($sql_data_array, $update_sql_data);
        xtc_db_perform(TABLE_MANUFACTURERS, $sql_data_array, 'update', "manufacturers_id = '" . xtc_db_input($manufacturers_id) . "'");
      }

	$dir_manufacturers=DIR_FS_CATALOG_IMAGES."/manufacturers";
    if ($manufacturers_image = xtc_try_upload('manufacturers_image', $dir_manufacturers)) {
        xtc_db_query("update " . TABLE_MANUFACTURERS . " set
                                 manufacturers_image ='manufacturers/".$manufacturers_image->filename . "'
                                 where manufacturers_id = '" . xtc_db_input($manufacturers_id) . "'");
    }

      $languages = xtc_get_languages();
      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        $manufacturers_url_array = $_POST['manufacturers_url'];
        $language_id = $languages[$i]['id'];

        $sql_data_array = array('manufacturers_url' => xtc_db_prepare_input($manufacturers_url_array[$language_id]));

        if ($_GET['action'] == 'insert') {
          $insert_sql_data = array('manufacturers_id' => $manufacturers_id,
                                   'languages_id' => $language_id);
          $sql_data_array = xtc_array_merge($sql_data_array, $insert_sql_data);
          xtc_db_perform(TABLE_MANUFACTURERS_INFO, $sql_data_array);
        } elseif ($_GET['action'] == 'save') {
          //BOF - web28 - 2010-07-11 - BUGFIX no entry stored for previous deactivated languages
          $manufacturers_query = xtc_db_query("select * from ".TABLE_MANUFACTURERS_INFO." where languages_id = '".$language_id."' and manufacturers_id = '".xtc_db_input($manufacturers_id)."'");
          if (xtc_db_num_rows($manufacturers_query) == 0) xtc_db_perform(TABLE_MANUFACTURERS_INFO, array ('manufacturers_id' => xtc_db_input($manufacturers_id), 'languages_id' => $language_id));
          //EOF - web28 - 2010-07-11 - BUGFIX no entry stored for previous deactivated languages
          xtc_db_perform(TABLE_MANUFACTURERS_INFO, $sql_data_array, 'update', "manufacturers_id = '" . xtc_db_input($manufacturers_id) . "' and languages_id = '" . $language_id . "'");
        }
      }

      if (USE_CACHE == 'true') {
        xtc_reset_cache_block('manufacturers');
      }

      xtc_redirect(xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . $_GET['page'] . '&mID=' . $manufacturers_id));
      break;

    case 'deleteconfirm':
      $manufacturers_id = xtc_db_prepare_input($_GET['mID']);

      if ($_POST['delete_image'] == 'on') {
        $manufacturer_query = xtc_db_query("select manufacturers_image from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . xtc_db_input($manufacturers_id) . "'");
        $manufacturer = xtc_db_fetch_array($manufacturer_query);
        $image_location = DIR_FS_DOCUMENT_ROOT . DIR_WS_IMAGES . $manufacturer['manufacturers_image'];
        if (file_exists($image_location)) @unlink($image_location);
      }

      xtc_db_query("delete from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . xtc_db_input($manufacturers_id) . "'");
      xtc_db_query("delete from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . xtc_db_input($manufacturers_id) . "'");

      if ($_POST['delete_products'] == 'on') {
        $products_query = xtc_db_query("select products_id from " . TABLE_PRODUCTS . " where manufacturers_id = '" . xtc_db_input($manufacturers_id) . "'");
        
        //BOC web28 - 2012-04-02 - BUGFIX remove products
        require_once('includes/classes/categories.php');
        $tmp_categories = new categories();
        
        while ($products = xtc_db_fetch_array($products_query)) {
          //xtc_remove_product($products['products_id']);
          $tmp_categories->remove_product($products['products_id']);
        }
        unset($tmp_categories);
        //BOC web28 - 2012-04-02 - BUGFIX remove products
      } else {
        xtc_db_query("update " . TABLE_PRODUCTS . " set manufacturers_id = '' where manufacturers_id = '" . xtc_db_input($manufacturers_id) . "'");
      }

      if (USE_CACHE == 'true') {
        xtc_reset_cache_block('manufacturers');
      }

      xtc_redirect(xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . $_GET['page']));
      break;
  }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_SESSION['language_charset']; ?>"> 
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
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
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo xtc_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MANUFACTURERS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $manufacturers_query_raw = "select manufacturers_id, manufacturers_name, manufacturers_image, date_added, last_modified from " . TABLE_MANUFACTURERS . " order by manufacturers_name";
  $manufacturers_split = new splitPageResults($_GET['page'], '20', $manufacturers_query_raw, $manufacturers_query_numrows);
  $manufacturers_query = xtc_db_query($manufacturers_query_raw);
  while ($manufacturers = xtc_db_fetch_array($manufacturers_query)) {
    if (((!$_GET['mID']) || (@$_GET['mID'] == $manufacturers['manufacturers_id'])) && (!$mInfo) && (substr($_GET['action'], 0, 3) != 'new')) {
      $manufacturer_products_query = xtc_db_query("select count(*) as products_count from " . TABLE_PRODUCTS . " where manufacturers_id = '" . $manufacturers['manufacturers_id'] . "'");
      $manufacturer_products = xtc_db_fetch_array($manufacturer_products_query);

      $mInfo_array = xtc_array_merge($manufacturers, $manufacturer_products);
      $mInfo = new objectInfo($mInfo_array);
    }

    if ( (is_object($mInfo)) && ($manufacturers['manufacturers_id'] == $mInfo->manufacturers_id) ) {
      echo '              <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . $_GET['page'] . '&mID=' . $manufacturers['manufacturers_id'] . '&action=edit') . '\'">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . $_GET['page'] . '&mID=' . $manufacturers['manufacturers_id']) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $manufacturers['manufacturers_name']; ?></td>
<!-- BOF - Tomcraft - 2009-06-10 - added some missing alternative text on admin icons -->
<!--
                <td class="dataTableContent" align="right"><?php if ( (is_object($mInfo)) && ($manufacturers['manufacturers_id'] == $mInfo->manufacturers_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . $_GET['page'] . '&mID=' . $manufacturers['manufacturers_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
-->
                <td class="dataTableContent" align="right"><?php if ( (is_object($mInfo)) && ($manufacturers['manufacturers_id'] == $mInfo->manufacturers_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . $_GET['page'] . '&mID=' . $manufacturers['manufacturers_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
<!-- EOF - Tomcraft - 2009-06-10 - added some missing alternative text on admin icons -->
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $manufacturers_split->display_count($manufacturers_query_numrows, '20', $_GET['page'], TEXT_DISPLAY_NUMBER_OF_MANUFACTURERS); ?></td>
                    <td class="smallText" align="right"><?php echo $manufacturers_split->display_links($manufacturers_query_numrows, '20', MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
                </table></td>
              </tr>
<?php
  if ($_GET['action'] != 'new') {
?>
              <tr>
                <td align="right" colspan="2" class="smallText"><?php echo xtc_button_link(BUTTON_INSERT, xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . $_GET['page'] . '&mID=' . $mInfo->manufacturers_id . '&action=new')); ?></td>
              </tr>
<?php
  }
?>
            </table></td>
<?php
  $heading = array();
  $contents = array();
  switch ($_GET['action']) {
    case 'new':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_NEW_MANUFACTURER . '</b>');

      $contents = array('form' => xtc_draw_form('manufacturers', FILENAME_MANUFACTURERS, 'action=insert', 'post', 'enctype="multipart/form-data"'));
      $contents[] = array('text' => TEXT_NEW_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_MANUFACTURERS_NAME . '<br />' . xtc_draw_input_field('manufacturers_name'));
      $contents[] = array('text' => '<br />' . TEXT_MANUFACTURERS_IMAGE . '<br />' . xtc_draw_file_field('manufacturers_image'));

      $manufacturer_inputs_string = '';
      $languages = xtc_get_languages();
      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        $manufacturer_inputs_string .= '<br />' . xtc_image(DIR_WS_LANGUAGES . $languages[$i]['directory'] . '/admin/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . xtc_draw_input_field('manufacturers_url[' . $languages[$i]['id'] . ']');
      }

      $contents[] = array('text' => '<br />' . TEXT_MANUFACTURERS_URL . $manufacturer_inputs_string);
      $contents[] = array('align' => 'center', 'text' => '<br />' . xtc_button(BUTTON_SAVE) . '&nbsp;' . xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . $_GET['page'] . '&mID=' . $_GET['mID'])));
      break;

    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_EDIT_MANUFACTURER . '</b>');

      $contents = array('form' => xtc_draw_form('manufacturers', FILENAME_MANUFACTURERS, 'page=' . $_GET['page'] . '&mID=' . $mInfo->manufacturers_id . '&action=save', 'post', 'enctype="multipart/form-data"'));
      $contents[] = array('text' => TEXT_EDIT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_MANUFACTURERS_NAME . '<br />' . xtc_draw_input_field('manufacturers_name', $mInfo->manufacturers_name));
      $contents[] = array('text' => '<br />' . TEXT_MANUFACTURERS_IMAGE . '<br />' . xtc_draw_file_field('manufacturers_image') . '<br />' . $mInfo->manufacturers_image);

      $manufacturer_inputs_string = '';
      $languages = xtc_get_languages();
      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        $manufacturer_inputs_string .= '<br />' . xtc_image(DIR_WS_LANGUAGES . $languages[$i]['directory'] . '/admin/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . xtc_draw_input_field('manufacturers_url[' . $languages[$i]['id'] . ']', xtc_get_manufacturer_url($mInfo->manufacturers_id, $languages[$i]['id']));
      }

      $contents[] = array('text' => '<br />' . TEXT_MANUFACTURERS_URL . $manufacturer_inputs_string);
      $contents[] = array('align' => 'center', 'text' => '<br />' . xtc_button(BUTTON_SAVE) . '&nbsp;' . xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . $_GET['page'] . '&mID=' . $mInfo->manufacturers_id)));
      break;

    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_DELETE_MANUFACTURER . '</b>');

      $contents = array('form' => xtc_draw_form('manufacturers', FILENAME_MANUFACTURERS, 'page=' . $_GET['page'] . '&mID=' . $mInfo->manufacturers_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_DELETE_INTRO);
      $contents[] = array('text' => '<br /><b>' . $mInfo->manufacturers_name . '</b>');
      $contents[] = array('text' => '<br />' . xtc_draw_checkbox_field('delete_image', '', true) . ' ' . TEXT_DELETE_IMAGE);

      if ($mInfo->products_count > 0) {
        $contents[] = array('text' => '<br />' . xtc_draw_checkbox_field('delete_products') . ' ' . TEXT_DELETE_PRODUCTS);
        $contents[] = array('text' => '<br />' . sprintf(TEXT_DELETE_WARNING_PRODUCTS, $mInfo->products_count));
      }

      $contents[] = array('align' => 'center', 'text' => '<br />' . xtc_button(BUTTON_DELETE) . '&nbsp;' . xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . $_GET['page'] . '&mID=' . $mInfo->manufacturers_id)));
      break;

    default:
      if (is_object($mInfo)) {
        $heading[] = array('text' => '<b>' . $mInfo->manufacturers_name . '</b>');

        $contents[] = array('align' => 'center', 'text' => xtc_button_link(BUTTON_EDIT, xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . $_GET['page'] . '&mID=' . $mInfo->manufacturers_id . '&action=edit')) . '&nbsp;' . xtc_button_link(BUTTON_DELETE, xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . $_GET['page'] . '&mID=' . $mInfo->manufacturers_id . '&action=delete')));
        $contents[] = array('text' => '<br />' . TEXT_DATE_ADDED . ' ' . xtc_date_short($mInfo->date_added));
        if (xtc_not_null($mInfo->last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . xtc_date_short($mInfo->last_modified));
        $contents[] = array('text' => '<br />' . xtc_info_image($mInfo->manufacturers_image, $mInfo->manufacturers_name));
        $contents[] = array('text' => '<br />' . TEXT_PRODUCTS . ' ' . $mInfo->products_count);
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