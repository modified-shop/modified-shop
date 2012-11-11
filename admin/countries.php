<?php
/* --------------------------------------------------------------
   $Id: countries.php 1123 2005-07-27 09:00:31Z novalis $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(countries.php,v 1.26 2003/05/17); www.oscommerce.com 
   (c) 2003	 nextcommerce (countries.php,v 1.9 2003/08/18); www.nextcommerce.org

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

  require('includes/application_top.php');

  if ($_GET['action']) {
    switch ($_GET['action']) {
      case 'insert':
        $countries_name = xtc_db_prepare_input($_POST['countries_name']);
        $countries_iso_code_2 = xtc_db_prepare_input($_POST['countries_iso_code_2']);
        $countries_iso_code_3 = xtc_db_prepare_input($_POST['countries_iso_code_3']);
        $address_format_id = xtc_db_prepare_input($_POST['address_format_id']);

        xtc_db_query("insert into " . TABLE_COUNTRIES . " (countries_name, countries_iso_code_2, countries_iso_code_3, address_format_id) values ('" . xtc_db_input($countries_name) . "', '" . xtc_db_input($countries_iso_code_2) . "', '" . xtc_db_input($countries_iso_code_3) . "', '" . xtc_db_input($address_format_id) . "')");
        xtc_redirect(xtc_href_link(FILENAME_COUNTRIES));
        break;
      case 'save':
        $countries_id = xtc_db_prepare_input($_GET['cID']);
        $countries_name = xtc_db_prepare_input($_POST['countries_name']);
        $countries_iso_code_2 = xtc_db_prepare_input($_POST['countries_iso_code_2']);
        $countries_iso_code_3 = xtc_db_prepare_input($_POST['countries_iso_code_3']);
        $address_format_id = xtc_db_prepare_input($_POST['address_format_id']);

        xtc_db_query("update " . TABLE_COUNTRIES . " set countries_name = '" . xtc_db_input($countries_name) . "', countries_iso_code_2 = '" . xtc_db_input($countries_iso_code_2) . "', countries_iso_code_3 = '" . xtc_db_input($countries_iso_code_3) . "', address_format_id = '" . xtc_db_input($address_format_id) . "' where countries_id = '" . xtc_db_input($countries_id) . "'");
        xtc_redirect(xtc_href_link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $countries_id));
        break;
      case 'deleteconfirm':
        $countries_id = xtc_db_prepare_input($_GET['cID']);

        xtc_db_query("delete from " . TABLE_COUNTRIES . " where countries_id = '" . xtc_db_input($countries_id) . "'");
        xtc_redirect(xtc_href_link(FILENAME_COUNTRIES, 'page=' . $_GET['page']));
        break;
      case 'setlflag':
        $countries_id = xtc_db_prepare_input($_GET['cID']);
        $status = xtc_db_prepare_input($_GET['flag']);      
        xtc_db_query("update " . TABLE_COUNTRIES . " set status = '" . xtc_db_input($status) . "' where countries_id = '" . xtc_db_input($countries_id) . "'");
        xtc_redirect(xtc_href_link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $countries_id));      
      break;
	  //BOF - web28 - 2010.05.30 - set/unset all flags
	  case 'setallflags':
        $countries_id = xtc_db_prepare_input($_GET['cID']);
        $status = xtc_db_prepare_input($_GET['flag']);      
        xtc_db_query("update " . TABLE_COUNTRIES . " set status = '" . xtc_db_input($status) . "'");
        xtc_redirect(xtc_href_link(FILENAME_COUNTRIES, 'page=' . $_GET['page']));      
      break;
	  //EOF - web28 - 2010.05.30 - set/unset all flags	  
    }
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
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td width="80" rowspan="2"><?php echo xtc_image(DIR_WS_ICONS.'heading_configuration.gif'); ?></td>
    <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
  </tr>
  <tr>
    <td class="main" valign="top">Configuration
	<?php //BOF - web28 - 2010.05.30 - set/unset all flags ?>
	<?php 
		echo '<a class="button" style="margin-left:150px;" href="' . xtc_href_link(FILENAME_COUNTRIES, xtc_get_all_get_params(array('page', 'action', 'cID')) . 'action=setallflags&flag=1&page='.$_GET['page']) . '">'.BUTTON_SET.'</a>';
		echo '&nbsp;&nbsp;&nbsp;';
		echo '<a class="button" href="' . xtc_href_link(FILENAME_COUNTRIES, xtc_get_all_get_params(array('page', 'action', 'cID')) . 'action=setallflags&flag=0&page='.$_GET['page']) . '">'.BUTTON_UNSET.'</a>';
	 ?>
	 <?php //EOF - web28 - 2010.05.30 - set/unset all flags ?>
	</td>
  </tr>
</table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_COUNTRY_NAME; ?></td>
                <td class="dataTableHeadingContent" align="center" colspan="2"><?php echo TABLE_HEADING_COUNTRY_CODES; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>                
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $countries_query_raw = "select countries_id, countries_name, countries_iso_code_2, countries_iso_code_3, status, address_format_id from " . TABLE_COUNTRIES . " order by countries_name";
  $countries_split = new splitPageResults($_GET['page'], '20', $countries_query_raw, $countries_query_numrows);
  $countries_query = xtc_db_query($countries_query_raw);
  while ($countries = xtc_db_fetch_array($countries_query)) {
    if (((!$_GET['cID']) || (@$_GET['cID'] == $countries['countries_id'])) && (!$cInfo) && (substr($_GET['action'], 0, 3) != 'new')) {
      $cInfo = new objectInfo($countries);
    }

    if ( (is_object($cInfo)) && ($countries['countries_id'] == $cInfo->countries_id) ) {
      echo '                  <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->countries_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $countries['countries_id']) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $countries['countries_name']; ?></td>
                <td class="dataTableContent" align="center" width="40"><?php echo $countries['countries_iso_code_2']; ?></td>
                <td class="dataTableContent" align="center" width="40"><?php echo $countries['countries_iso_code_3']; ?></td>
                <td class="dataTableContent" align="center">
<?php               
      if ($countries['status'] == '1') {
        echo xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . xtc_href_link(FILENAME_COUNTRIES, xtc_get_all_get_params(array('page', 'action', 'cID')) . 'action=setlflag&flag=0&cID=' . $countries['countries_id'] . '&page='.$_GET['page']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>';
      } else {
        echo '<a href="' . xtc_href_link(FILENAME_COUNTRIES, xtc_get_all_get_params(array('page', 'action', 'cID')) . 'action=setlflag&flag=1&cID=' . $countries['countries_id'].'&page='.$_GET['page']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . xtc_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
      }                
?>
                </td>    
<!-- BOF - Tomcraft - 2009-06-10 - added some missing alternative text on admin icons -->    
<!--
                <td class="dataTableContent" align="right"><?php if ( (is_object($cInfo)) && ($countries['countries_id'] == $cInfo->countries_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . xtc_href_link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $countries['countries_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
-->
                <td class="dataTableContent" align="right"><?php if ( (is_object($cInfo)) && ($countries['countries_id'] == $cInfo->countries_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $countries['countries_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
<!-- EOF - Tomcraft - 2009-06-10 - added some missing alternative text on admin icons -->
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="4"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $countries_split->display_count($countries_query_numrows, '20', $_GET['page'], TEXT_DISPLAY_NUMBER_OF_COUNTRIES); ?></td>
                    <td class="smallText" align="right"><?php echo $countries_split->display_links($countries_query_numrows, '20', MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
<?php
  if (!$_GET['action']) {
?>
                  <tr>
                    <td colspan="2" align="right"><?php echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&action=new') . '">' . BUTTON_NEW_COUNTRY . '</a>'; ?></td>
                  </tr>
<?php
  }
?>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();
  switch ($_GET['action']) {
    case 'new':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_COUNTRY . '</b>');

      $contents = array('form' => xtc_draw_form('countries', FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&action=insert'));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_NAME . '<br />' . xtc_draw_input_field('countries_name'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_CODE_2 . '<br />' . xtc_draw_input_field('countries_iso_code_2'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_CODE_3 . '<br />' . xtc_draw_input_field('countries_iso_code_3'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_ADDRESS_FORMAT . '<br />' . xtc_draw_pull_down_menu('address_format_id', xtc_get_address_formats()));
      $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_INSERT . '"/>&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_COUNTRIES, 'page=' . $_GET['page']) . '">' . BUTTON_CANCEL . '</a>');
      break;
    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_COUNTRY . '</b>');

      $contents = array('form' => xtc_draw_form('countries', FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->countries_id . '&action=save'));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_NAME . '<br />' . xtc_draw_input_field('countries_name', $cInfo->countries_name));
      $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_CODE_2 . '<br />' . xtc_draw_input_field('countries_iso_code_2', $cInfo->countries_iso_code_2));
      $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_CODE_3 . '<br />' . xtc_draw_input_field('countries_iso_code_3', $cInfo->countries_iso_code_3));
      $contents[] = array('text' => '<br />' . TEXT_INFO_ADDRESS_FORMAT . '<br />' . xtc_draw_pull_down_menu('address_format_id', xtc_get_address_formats(), $cInfo->address_format_id));
      $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UPDATE . '"/>&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->countries_id) . '">' . BUTTON_CANCEL . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_COUNTRY . '</b>');

      $contents = array('form' => xtc_draw_form('countries', FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->countries_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br /><b>' . $cInfo->countries_name . '</b>');
      $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/>&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->countries_id) . '">' . BUTTON_CANCEL . '</a>');
      break;
    default:
      if (is_object($cInfo)) {
        $heading[] = array('text' => '<b>' . $cInfo->countries_name . '</b>');

        $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->countries_id . '&action=edit') . '">' . BUTTON_EDIT . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_COUNTRIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->countries_id . '&action=delete') . '">' . BUTTON_DELETE . '</a>');
        $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_NAME . '<br />' . $cInfo->countries_name);
        $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_CODE_2 . ' ' . $cInfo->countries_iso_code_2);
        $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_CODE_3 . ' ' . $cInfo->countries_iso_code_3);
        $contents[] = array('text' => '<br />' . TEXT_INFO_ADDRESS_FORMAT . ' ' . $cInfo->address_format_id);
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