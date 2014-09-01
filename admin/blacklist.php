<?php
/*------------------------------------------------------------------------------
  $Id: blacklist.php 1023 2005-07-14 11:41:37Z novalis $

  XTC-CC - Contribution for XT-Commerce http://www.xt-commerce.com
  modified by http://www.netz-designer.de

  Copyright (c) 2003 netz-designer
  -----------------------------------------------------------------------------
  based on:
  $Id: blacklist.php,v 1.00 2003/04/10 BMC

  Copyright (c) 2003 BMC
  http://www.mainframes.co.uk

  Released under the GNU General Public License
------------------------------------------------------------------------------*/

  require('includes/application_top.php');


  switch ($_GET['action']) {
    case 'insert':
    case 'save':
      $blacklist_id = xtc_db_prepare_input($_GET['bID']);
      $blacklist_card_number = xtc_db_prepare_input($_POST['blacklist_card_number']);

      $sql_data_array = array('blacklist_card_number' => $blacklist_card_number);

      if ($_GET['action'] == 'insert') {
        $insert_sql_data = array('date_added' => 'now()');
        $sql_data_array = xtc_array_merge($sql_data_array, $insert_sql_data);
        xtc_db_perform(TABLE_BLACKLIST, $sql_data_array);
        $blacklist_id = xtc_db_insert_id();
      } elseif ($_GET['action'] == 'save') {
        $update_sql_data = array('last_modified' => 'now()');
        $sql_data_array = xtc_array_merge($sql_data_array, $update_sql_data);
        xtc_db_perform(TABLE_BLACKLIST, $sql_data_array, 'update', "blacklist_id = '" . xtc_db_input($blacklist_id) . "'");
      }


      if (USE_CACHE == 'true') {
        xtc_reset_cache_block('blacklist');
      }

      xtc_redirect(xtc_href_link(FILENAME_BLACKLIST, 'page=' . $_GET['page'] . '&bID=' . $blacklist_id));
      break;
    case 'deleteconfirm':
      $blacklist_id = xtc_db_prepare_input($_GET['bID']);


      xtc_db_query("delete from " . TABLE_BLACKLIST . " where blacklist_id = '" . xtc_db_input($blacklist_id) . "'");

      if (USE_CACHE == 'true') {
        xtc_reset_cache_block('manufacturers');
      }

      xtc_redirect(xtc_href_link(FILENAME_BLACKLIST, 'page=' . $_GET['page']));
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
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onLoad="SetFocus();">
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
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_BLACKLIST; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $blacklist_query_raw = "select blacklist_id, blacklist_card_number, date_added, last_modified from " . TABLE_BLACKLIST . " order by blacklist_id";
  $blacklist_split = new splitPageResults($_GET['page'], '20', $blacklist_query_raw, $blacklist_query_numrows);
  $blacklist_query = xtc_db_query($blacklist_query_raw);
  while ($blacklist = xtc_db_fetch_array($blacklist_query)) {
    if (((!$_GET['bID']) || (@$_GET['bID'] == $blacklist['blacklist_id'])) && (!$bInfo) && (substr($_GET['action'], 0, 3) != 'new')) {
      $blacklist_numbers_query = xtc_db_query("select count(*) as blacklist_count from " . TABLE_BLACKLIST . " where blacklist_id = '" . $blacklist['blacklist_id'] . "'");
      $blacklist_numbers = xtc_db_fetch_array($blacklist_numbers_query);

      $bInfo_array = xtc_array_merge($blacklist, $blacklist_numbers);
      $bInfo = new objectInfo($bInfo_array);
    }

    if ( (is_object($bInfo)) && ($blacklist['blacklist_id'] == $bInfo->blacklist_id) ) {
      echo '              <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_BLACKLIST, 'page=' . $_GET['page'] . '&bID=' . $blacklist['blacklist_id'] . '&action=edit') . '\'">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_BLACKLIST, 'page=' . $_GET['page'] . '&bID=' . $blacklist['blacklist_id']) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $blacklist['blacklist_card_number']; ?></td>
<!-- BOF - Tomcraft - 2009-06-10 - added some missing alternative text on admin icons -->
<!--
                <td class="dataTableContent" align="right"><?php if ( (is_object($bInfo)) && ($blacklist['blacklist_id'] == $bInfo->blacklist_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . xtc_href_link(FILENAME_BLACKLIST, 'page=' . $_GET['page'] . '&bID=' . $blacklist['blacklist_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
-->
                <td class="dataTableContent" align="right"><?php if ( (is_object($bInfo)) && ($blacklist['blacklist_id'] == $bInfo->blacklist_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_BLACKLIST, 'page=' . $_GET['page'] . '&bID=' . $blacklist['blacklist_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
<!-- EOF - Tomcraft - 2009-06-10 - added some missing alternative text on admin icons -->
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $blacklist_split->display_count($blacklist_query_numrows, '20', $_GET['page'], TEXT_DISPLAY_NUMBER_OF_BLACKLIST_CARDS); ?></td>
                    <td class="smallText" align="right"><?php echo $blacklist_split->display_links($blacklist_query_numrows, '20', MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
                </table></td>
              </tr>
<?php
  if ($_GET['action'] != 'new') {
?>
              <tr>
                <td align="right" colspan="2" class="smallText"><?php echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_BLACKLIST, 'page=' . $_GET['page'] . '&bID=' . $bInfo->blacklist_id . '&action=new') . '">' . BUTTON_INSERT . '</a>'; ?></td>
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
      $heading[] = array('text' => '<b>' . TEXT_HEADING_NEW_BLACKLIST_CARD . '</b>');

      $contents = array('form' => xtc_draw_form('blacklisted', FILENAME_BLACKLIST, 'action=insert', 'post', 'enctype="multipart/form-data"'));
      $contents[] = array('text' => TEXT_NEW_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_BLACKLIST_CARD_NUMBER . '<br />' . xtc_draw_input_field('blacklist_card_number'));

      $blacklist_inputs_string = '';

      $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SAVE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_BLACKLIST, 'page=' . $_GET['page'] . '&bID=' . $_GET['bID']) . '">' . BUTTON_CANCEL . '</a>');
      break;
    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_EDIT_BLACKLIST_CARD . '</b>');

      $contents = array('form' => xtc_draw_form('blacklisted', FILENAME_BLACKLIST, 'page=' . $_GET['page'] . '&bID=' . $bInfo->blacklist_id . '&action=save', 'post', 'enctype="multipart/form-data"'));
      $contents[] = array('text' => TEXT_EDIT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_BLACKLIST_CARD_NUMBER . '<br />' . xtc_draw_input_field('blacklist_card_number', $bInfo->blacklist_card_number));

      $blacklist_inputs_string = '';

      $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SAVE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_BLACKLIST, 'page=' . $_GET['page'] . '&bID=' . $mInfo->blacklist_id) . '">' . BUTTON_CANCEL . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_HEADING_DELETE_BLACKLIST_CARD . '</b>');

      $contents = array('form' => xtc_draw_form('blacklisted', FILENAME_BLACKLIST, 'page=' . $_GET['page'] . '&bID=' . $bInfo->blacklist_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_DELETE_INTRO);
      $contents[] = array('text' => '<br /><b>' . $bInfo->blacklist_card_number . '</b>');


      $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_BLACKLIST, 'page=' . $_GET['page'] . '&bID=' . $bInfo->blacklist_id) . '">' . BUTTON_CANCEL . '</a>');
      break;
    default:
      if (is_object($bInfo)) {
        $heading[] = array('text' => '<b>' . $bInfo->blacklist_card_number . '</b>');

        $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_BLACKLIST, 'page=' . $_GET['page'] . '&bID=' . $bInfo->blacklist_id . '&action=edit') . '">' . BUTTON_EDIT . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_BLACKLIST, 'page=' . $_GET['page'] . '&bID=' . $bInfo->blacklist_id . '&action=delete') . '">' . BUTTON_DELETE . '</a>');
        $contents[] = array('text' => '<br />' . TEXT_DATE_ADDED . ' ' . xtc_date_short($bInfo->date_added));
        if (xtc_not_null($bInfo->last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . xtc_date_short($bInfo->last_modified));
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