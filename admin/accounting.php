<?php
/* --------------------------------------------------------------
   $Id: accounting.php 1167 2005-08-22 00:43:01Z mz $ 

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommercecoding standards www.oscommerce.com 
   (c) 2003	nextcommerce (accounting.php,v 1.27 2003/08/24); www.nextcommerce.org
   (c) 2003 XT-Commerce

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

  require('includes/application_top.php');

  if ($_GET['action']) {
    switch ($_GET['action']) {
      case 'save':

        // reset values before writing
        $admin_access_query = xtc_db_query("SELECT * 
                                              FROM " . TABLE_ADMIN_ACCESS . " 
                                             WHERE customers_id = '" . (int)$_GET['cID'] . "'");
        $admin_access = xtc_db_fetch_array($admin_access_query);

        $fields = xtc_db_query("SHOW COLUMNS FROM `".TABLE_ADMIN_ACCESS."` FROM `".DB_DATABASE."`");
        $columns = xtc_db_num_rows($fields);

        while ($field = xtc_db_fetch_array($fields)) {
          if ($field['Field'] != 'customers_id') {
            xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." 
                             SET ".$field['Field']." = '0'
                           WHERE customers_id = '".(int)$_GET['cID']."'");
          }
        }

        if (isset($_POST['access'])) foreach($_POST['access'] as $key){
          xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." 
                           SET ".$key." = '1' 
                         WHERE customers_id = '".(int)$_GET['cID']."'");
        }
        xtc_redirect(xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('cID','action')).'cID=' . (int)$_GET['cID'], 'NONSSL'));
        break;
      
      case 'new':
        $new_field = xtc_db_prepare_input($_POST['admin_access']);
        $exists = false;
        $fields = xtc_db_query("SHOW COLUMNS FROM `".TABLE_ADMIN_ACCESS."` FROM `".DB_DATABASE."`");
        while ($field = xtc_db_fetch_array($fields)) {
          if ($field == $new_field) {
            $exists = true;
          }
        }
        if ($exists === false) {
          xtc_db_query("ALTER TABLE ".TABLE_ADMIN_ACCESS." ADD ".$new_field." INT(1) NOT NULL DEFAULT 0;");
        }
        xtc_redirect(xtc_href_link(FILENAME_ACCOUNTING, xtc_get_all_get_params(array('cID','action')).'cID=' . (int)$_GET['cID'], 'NONSSL'));  
        break;
    }
  }
    
  if ($_GET['cID'] != '') {
    if ($_GET['cID'] == 1) {
      xtc_redirect(xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('cID','action')).'cID=' . (int)$_GET['cID'], 'NONSSL'));
    } else {
      $allow_edit_query = xtc_db_query("SELECT customers_status, 
                                               customers_firstname, 
                                               customers_lastname 
                                          FROM " . TABLE_CUSTOMERS . " 
                                         WHERE customers_id = '" . (int)$_GET['cID'] . "'");
      $allow_edit = xtc_db_fetch_array($allow_edit_query);
      if ($allow_edit['customers_status'] != 0 || $allow_edit == '') {
        xtc_redirect(xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('cID','action')).'cID=' . (int)$_GET['cID'], 'NONSSL'));
      }
    }
  }

require (DIR_WS_INCLUDES.'head.php');
?>
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
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
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
	        <a class="button" href="#" onclick="set_checkbox(1);"><?php echo BUTTON_SET; ?></a>&nbsp;&nbsp;&nbsp;<a class="button" href="#" onclick="set_checkbox(0);"><?php echo BUTTON_UNSET; ?></a>
	        <br /><br />
        </td>
      </tr>
      <tr>
        <td>
          <?php echo xtc_draw_form('accounting', FILENAME_ACCOUNTING, xtc_get_all_get_params(array('action'))  . 'action=new');?>
            <table valign="top" width="100%" border="0" cellpadding="0" cellspacing="0">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TEXT_ACCESS . ' ' . BUTTON_INSERT; ?></td>
                <td class="dataTableHeadingContent"><?php echo  xtc_draw_input_field('admin_access', '', 'style="width: 250px"'); ?></td>
                <td class="dataTableHeadingContent"><input type="submit" class="button" onclick="return confirm('<?php echo SAVE_ENTRY; ?>')" value="<?php echo BUTTON_INSERT; ?>"></td>
              </tr>
            </table>
          </form>
        </td>
      </tr>
      <tr><td style="height:20px;">&nbsp;</td></tr>
      <tr>
        <td><table valign="top" width="100%" border="0" cellpadding="0" cellspacing="0">
          <tr class="dataTableHeadingRow">
            <td class="dataTableHeadingContent"><?php echo TEXT_ACCESS; ?></td>
            <td class="dataTableHeadingContent"><?php echo TEXT_ALLOWED; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td>
          <table border="0" cellpadding="0" cellspacing="2">
            <?php 
            echo xtc_draw_form('accounting', FILENAME_ACCOUNTING, xtc_get_all_get_params(array('action'))  . 'action=save', 'post', 'enctype="multipart/form-data"');

            $customers_id = xtc_db_prepare_input($_GET['cID']);
            $admin_access_query = xtc_db_query("SELECT * 
                                                  FROM " . TABLE_ADMIN_ACCESS . " 
                                                 WHERE customers_id = '" . (int)$_GET['cID'] . "'");
            if (xtc_db_num_rows($admin_access_query) < 1) {
              xtc_db_query("INSERT INTO " . TABLE_ADMIN_ACCESS . " (customers_id) VALUES ('" . (int)$_GET['cID'] . "')");
              $admin_access_query = xtc_db_query("SELECT * 
                                                    FROM " . TABLE_ADMIN_ACCESS . " 
                                                   WHERE customers_id = '" . (int)$_GET['cID'] . "'");
            }
            $admin_access = xtc_db_fetch_array($admin_access_query);

            $group_query = xtc_db_query("SELECT * 
                                           FROM " . TABLE_ADMIN_ACCESS . " 
                                          WHERE customers_id = 'groups'");
            $group_access = xtc_db_fetch_array($group_query);
            
            $fields = xtc_db_query("SHOW COLUMNS FROM `".TABLE_ADMIN_ACCESS."` FROM `".DB_DATABASE."`");
            while ($field = xtc_db_fetch_array($fields)) {
              if ($field['Field'] != 'customers_id') {
                $checked='';
                if ($admin_access[$field['Field']] == '1') {
                  $checked='checked';
                }
                
                // colors
                switch ($group_access[$field['Field']]) {
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
                    break;
                }
    
                echo '<tr class="dataTable">
                        <td style="border: 1px solid; border-color: #000000;" width="10" bgcolor="'.$color.'" >'.xtc_draw_separator('pixel_trans.gif',15, 15).'</td>
                        <td width="100%" class="dataTableContentRow"><input type="checkbox" name="access[]" value="'.$field['Field'].'"'.$checked.'>'.$field['Field'].'</td>
                        <td></td>
                      </tr>';
                }
            }
            ?>
          </table>
          <input type="submit" class="button" onclick="return confirm('<?php echo SAVE_ENTRY; ?>')" value="<?php echo BUTTON_SAVE; ?>">
        </td>
      <!-- body_text_eof //-->
      </tr>
    </table></td>
  </tr>
<!-- body_eof //-->
</table>

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>