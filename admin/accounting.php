<?php
/* --------------------------------------------------------------
   $Id: accounting.php 1167 2005-08-22 00:43:01Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommercecoding standards www.oscommerce.com 
   (c) 2003	 nextcommerce (accounting.php,v 1.27 2003/08/24); www.nextcommerce.org

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

  require('includes/application_top.php');

  if ($_GET['action']) {
    switch ($_GET['action']) {
      case 'save':


      // reset values before writing
       $admin_access_query = xtc_db_query("select * from " . TABLE_ADMIN_ACCESS . " where customers_id = '" . (int)$_GET['cID'] . "'");
       $admin_access = xtc_db_fetch_array($admin_access_query);

       $fields = mysql_list_fields(DB_DATABASE, TABLE_ADMIN_ACCESS);
       $columns = mysql_num_fields($fields);

		for ($i = 0; $i < $columns; $i++) {
             $field=mysql_field_name($fields, $i);
                    if ($field!='customers_id') {

                    xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." SET
                                  ".$field."=0 where customers_id='".(int)$_GET['cID']."'");
    		}
        }



      $access_ids='';
        if(isset($_POST['access'])) foreach($_POST['access'] as $key){

        xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." SET ".$key."=1 where customers_id='".(int)$_GET['cID']."'");

        }

        xtc_redirect(xtc_href_link(FILENAME_CUSTOMERS, 'cID=' . (int)$_GET['cID'], 'NONSSL'));
        break;
      }
    }
    if ($_GET['cID'] != '') {
      if ($_GET['cID'] == 1) {
        xtc_redirect(xtc_href_link(FILENAME_CUSTOMERS, 'cID=' . (int)$_GET['cID'], 'NONSSL'));
      } else {
        $allow_edit_query = xtc_db_query("select customers_status, customers_firstname, customers_lastname from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$_GET['cID'] . "'");
        $allow_edit = xtc_db_fetch_array($allow_edit_query);
        if ($allow_edit['customers_status'] != 0 || $allow_edit == '') {
          xtc_redirect(xtc_href_link(FILENAME_CUSTOMERS, 'cID=' . (int)$_GET['cID'], 'NONSSL'));
        }
      }
    }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_SESSION['language_charset']; ?>"> 
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">

<!-- BOF - web28 - 2010.05.30 - set all checkboxes -->
<script type="text/javascript">
function set_checkbox (set) {
  if (set == 1) {
    for (var i = 0; i < document.getElementsByName("access[]").length; ++i)
		document.getElementsByName("access[]")[i].checked = true;    
  }
  if (set == 0) {
    for (var i = 0; i < document.getElementsByName("access[]").length; ++i)
		document.getElementsByName("access[]")[i].checked = false; 
  }
  
}
</script>
<!-- EOF - web28 - 2010.05.30 - set all checkboxes -->

</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
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
    <td class="boxCenter" width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo TEXT_ACCOUNTING.' '.$allow_edit['customers_lastname'].' '.$allow_edit['customers_firstname']; ?></td>
            <td class="pageHeading" align="right"><?php echo xtc_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?><br /><br /></td>
          </tr>
        </table></td>
      </tr>
      <tr>
      <td colspan="2" class="main"> <br /><?php echo TXT_GROUPS; ?><br />

      <table width="100%" cellpadding="0" cellspacing="2">
      <tr>
       <td style="border: 1px solid; border-color: #000000;" width="10" bgcolor="FF6969" ><?php echo xtc_draw_separator('pixel_trans.gif',15, 15); ?></td>
       <td width="100%" class="main"><?php echo TXT_SYSTEM; ?></td>
      </tr>
      <tr>
       <td style="border: 1px solid; border-color: #000000;" width="10" bgcolor="69CDFF" ><?php echo xtc_draw_separator('pixel_trans.gif',10, 15); ?></td>
       <td width="100%" class="main"><?php echo TXT_CUSTOMERS; ?></td>
      </tr>
      <tr>
       <td style="border: 1px solid; border-color: #000000;" width="10" bgcolor="6BFF7F" ><?php echo xtc_draw_separator('pixel_trans.gif',15, 15); ?></td>
       <td width="100%" class="main"><?php echo TXT_PRODUCTS; ?></td>
      </tr>
      <tr>
       <td style="border: 1px solid; border-color: #000000;" width="10" bgcolor="BFA8FF" ><?php echo xtc_draw_separator('pixel_trans.gif',15, 15); ?></td>
       <td width="100%" class="main"><?php echo TXT_STATISTICS; ?></td>
      </tr>
      <tr>
       <td style="border: 1px solid; border-color: #000000;" width="10" bgcolor="FFE6A8" ><?php echo xtc_draw_separator('pixel_trans.gif',15, 15); ?></td>
       <td width="100%" class="main"><?php echo TXT_TOOLS; ?></td>
      </tr>
      </table>
      <br />
	   <!-- BOF - web28 - 2010.05.30 - set all checkboxes -->
	  <a class="button" href="#" onclick="set_checkbox(1);"><?php echo BUTTON_SET; ?></a>&nbsp;&nbsp;&nbsp;<a class="button" href="#" onclick="set_checkbox(0);"><?php echo BUTTON_UNSET; ?></a>
	  <br /><br />
	  <!-- EOF - web28 - 2010.05.30 - set all checkboxes -->
      </td>
      </tr>
      <tr>
        <td><table valign="top" width="100%" border="0" cellpadding="0" cellspacing="0">
          <tr class="dataTableHeadingRow">
            <td class="dataTableHeadingContent"><?php echo TEXT_ACCESS; ?></td>
            <td class="dataTableHeadingContent"><?php echo TEXT_ALLOWED; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr><table border="0" cellpadding="0" cellspacing="2">
<?php
 echo xtc_draw_form('accounting', FILENAME_ACCOUNTING, 'cID=' . $_GET['cID']  . '&action=save', 'post', 'enctype="multipart/form-data"');

   $admin_access='';
    $customers_id = xtc_db_prepare_input($_GET['cID']);
    $admin_access_query = xtc_db_query("select * from " . TABLE_ADMIN_ACCESS . " where customers_id = '" . (int)$_GET['cID'] . "'");
    $admin_access = xtc_db_fetch_array($admin_access_query);

    $group_query=xtc_db_query("select * from " . TABLE_ADMIN_ACCESS . " where customers_id = 'groups'");
    $group_access = xtc_db_fetch_array($group_query);
    if ($admin_access == '') {
      xtc_db_query("INSERT INTO " . TABLE_ADMIN_ACCESS . " (customers_id) VALUES ('" . (int)$_GET['cID'] . "')");
      $admin_access_query = xtc_db_query("select * from " . TABLE_ADMIN_ACCESS . " where customers_id = '" . (int)$_GET['cID'] . "'");
      $group_query=xtc_db_query("select * from " . TABLE_ADMIN_ACCESS . " where customers_id = 'groups'");
      $group_access = xtc_db_fetch_array($admin_access_query);
      $admin_access = xtc_db_fetch_array($admin_access_query);
    }

$fields = mysql_list_fields(DB_DATABASE, TABLE_ADMIN_ACCESS);
$columns = mysql_num_fields($fields);

for ($i = 0; $i < $columns; $i++) {
    $field=mysql_field_name($fields, $i);
    if ($field!='customers_id') {
    $checked='';
    if ($admin_access[$field] == '1') $checked='checked';

    // colors
    switch ($group_access[$field]) {
            case '1':
            $color='#FF6969';
            break;
            case '2':
            $color='#69CDFF';
            break;
            case '3':
            $color='#6BFF7F';
            break;
            case '4':
            $color='#BFA8FF';
            break;
            case '5':
            $color='#FFE6A8';

    }
    echo '<tr class="dataTable">
    <td style="border: 1px solid; border-color: #000000;" width="10" bgcolor="'.$color.'" >'.xtc_draw_separator('pixel_trans.gif',15, 15).'</td>
        <td width="100%" class="dataTableContentRow">
        <input type="checkbox" name="access[]" value="'.$field.'"'.$checked.'>
        '.$field.'</td>
        <td></td></tr>';
    }
}
?>
    </table>
<input type="submit" class="button" onclick="return confirm('<?php echo SAVE_ENTRY; ?>')" value="<?php echo BUTTON_SAVE; ?>">
</td>

<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>