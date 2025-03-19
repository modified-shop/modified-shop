<?php
/* --------------------------------------------------------------
   $Id$

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

  $confirm_save_entry = ' onclick="ButtonClicked(this);"';
  $confirm_submit = defined('CONFIRM_SAVE_ENTRY') && CONFIRM_SAVE_ENTRY == 'true' ? ' onsubmit="return confirmSubmit(\'\',\''. SAVE_ENTRY .'\',this)"' : '';

  if (isset($_GET['action'])) {
    switch ($_GET['action']) {
      case 'save':

        // reset values before writing
        $admin_access = get_admin_access($_GET['cID']);

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
        $new_field = xtc_db_prepare_input($_POST['admin_access_new']);
        $exists = false;
        $fields = xtc_db_query("SHOW COLUMNS FROM `".TABLE_ADMIN_ACCESS."` FROM `".DB_DATABASE."`");
        while ($field = xtc_db_fetch_array($fields)) {
          if ($field == $new_field) {
            $exists = true;
          }
        }
        if ($exists === false && $new_field != '') {
          xtc_db_query("ALTER TABLE ".TABLE_ADMIN_ACCESS." ADD ".$new_field." INT(1) NOT NULL DEFAULT 0;");
          xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS."
                           SET ".$new_field." = '1'
                         WHERE customers_id = '1'");
        }
        xtc_redirect(xtc_href_link(FILENAME_ACCOUNTING, xtc_get_all_get_params(array('cID','action')).'cID=' . (int)$_GET['cID'], 'NONSSL'));
        break;
    }
  }

  if ($_GET['cID'] != '') {
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
  
  $naming_array = array(
    '1' => array(
      'name' =>  TEXT_ADMIN_START,
      'color' => '#eeeeee',
    ),
    '2' => array(
      'name' =>  BOX_HEADING_CUSTOMERS,
      'color' => '#ebbb97',
    ),
    '3' => array(
      'name' =>  BOX_HEADING_PRODUCTS,
      'color' => '#aacfe2',
    ),   
    '4' => array(
      'name' =>  BOX_HEADING_STATISTICS,
      'color' => '#ebd397',
    ),
    '5' => array(
      'name' =>  BOX_HEADING_TOOLS,
      'color' => '#afd088',
    ),
    '6' => array(
      'name' =>  BOX_HEADING_GV_ADMIN,
      'color' => '#617d8d',
    ),
    '7' => array(
      'name' =>  BOX_HEADING_ZONE,
      'color' => '#666666',
    ),
    '8' => array(
      'name' =>  BOX_HEADING_CONFIGURATION,
      'color' => '#cb7272',
    ),
    '9' => array(
      'name' =>  BOX_HEADING_PARTNER_MODULES,
      'color' => '#8cd1ba',
    ),
    '0' => array(
      'name' =>  TXT_TOOLS,
      'color' => '#c689ab',
    ),
  );

require (DIR_WS_INCLUDES.'head.php');
?>
<script type="text/javascript">
  function set_checkbox(val, cid) {
    if (cid == 1) {
      var checked = 1;
    } else {
      var checked = $(".checkall"+val).is(':checked');
    }
    $(".access"+val).attr('checked', checked);
  }
</script>
<style>
.accounting_container {
  display: flex;
  margin: 0px -10px;
}
.accounting_col {
  width: 50%;
  padding: 10px;
  box-sizing: border-box;
}
 
.accounting_col .tableBoxCenter.collapse .dataTableHeadingRow {
  cursor:pointer;
}
.accounting_col .tableBoxCenter.collapse .dataTableHeadingRow:hover .dataTableHeadingContent {
  background-color:#ddd; 
}
.accounting_col .tableBoxCenter.collapse .dataTableHeadingRow em {
  position:relative;
  top:1px;
  left:5px;
  z-index:0;
}
.accounting_col .tableBoxCenter.collapse .dataTableHeadingRow input[type=checkbox].ChkBox:not(old) {
  position:relative;
  z-index:1;
}
</style>
</head>
<body>
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
    <td class="boxCenter" width="100%" valign="top">
      <div class="div_box mrg5">
        <div class="pageHeading pdg2"><?php echo TEXT_ACCOUNTING.' '.$allow_edit['customers_lastname'].' '.$allow_edit['customers_firstname'] . ' ['. (int)$_GET['cID'] .']'; ?>
          <div class="main flt-r"><?php echo xtc_draw_checkbox_field('complete', false) . ' ' . BUTTON_SET; ?></div>
        </div>
        <?php if ($_GET['cID'] == '1') { ?>
        <div class="main important_info" style="margin-top: 5px;">
          <?php  echo TEXT_ACCOUNTING_INFO ?> 
        </div>
        <?php } ?>
        <br/>
        <?php echo xtc_draw_form('accounting', FILENAME_ACCOUNTING, xtc_get_all_get_params(array('action'))  . 'action=new', 'post',  $confirm_submit);?>
          <table class="tableBoxCenter collapse">
            <tr class="dataTableHeadingRow">
              <td class="dataTableHeadingContent" style="vertical-align:middle;"><?php echo TEXT_ACCESS . ' ' . BUTTON_INSERT; ?></td>
              <td class="dataTableHeadingContent"><?php echo  xtc_draw_input_field('admin_access_new', '', 'style="width: 250px"'); ?></td>
              <td class="dataTableHeadingContent"><input type="submit" class="button" value="<?php echo BUTTON_INSERT; ?>" <?php echo $confirm_save_entry;?>></td>
            </tr>
          </table>
        </form>
        <br/>

        <?php echo xtc_draw_form('accounting', FILENAME_ACCOUNTING, xtc_get_all_get_params(array('action'))  . 'action=save', 'post', 'enctype="multipart/form-data"' . $confirm_submit); ?>
          <table class="tableBoxCenter collapse">
            <tr>
              <td>
                <?php
                $customers_id = xtc_db_prepare_input($_GET['cID']);

                $group_access = get_admin_access('groups');
                $admin_access = get_admin_access($_GET['cID']);
                if (count($admin_access) < 1) {
                  xtc_db_query("INSERT INTO " . TABLE_ADMIN_ACCESS . " (customers_id) VALUES ('" . (int)$_GET['cID'] . "')");
                  $admin_access = get_admin_access($_GET['cID'], false);
                }
                
                $fields = xtc_db_query("SHOW COLUMNS FROM `".TABLE_ADMIN_ACCESS."` FROM `".DB_DATABASE."`");
                while ($field = xtc_db_fetch_array($fields)) {
              
                  if ($field['Field'] != 'customers_id') {
                
                    $params = '';
                    $checked = false;
                    $params = '';
                    $checked = false;
                    $hidden_field = '';
                    if ($admin_access[$field['Field']] == '1') {
                      $checked = true;
                      if ($_GET['cID'] == '1') {
                        $params = ' disabled="disabled"';
                        $hidden_field =  xtc_draw_hidden_field('access[]', $field['Field']).PHP_EOL;
                      }
                    }

                    $accounting_array[$group_access[$field['Field']]][$field['Field']] = array(
                      'key' => $field['Field'],
                      'hidden' => $hidden_field,
                      'params' => $params,
                      'checked' => $checked,
                    );
                    ksort($accounting_array[$group_access[$field['Field']]]);
                  }
                }
                ksort($accounting_array);
                
                if (isset($accounting_array[0])) {
                  $accounting_tmp = $accounting_array[0];
                  unset($accounting_array[0]);
                  $accounting_array[0] = $accounting_tmp;
                }
                $accounting_array = array_values($accounting_array);
                $naming_array = array_values($naming_array);
                
                $total = count($accounting_array);
                $divide = ceil($total/2);
                
                echo '<div class="accounting_container">';
                echo '<div class="accounting_col">';
                for ($i=0; $i<$total; $i++) {
                  $totalaccess = count($accounting_array[$i]);
                  $totalchecked = array_sum(array_column($accounting_array[$i], 'checked'));
                  ?>
                  <table class="tableBoxCenter collapse">
                    <tr class="dataTableHeadingRow">
                      <td class="dataTableHeadingContent column<?php echo $i; ?>" colspan="2" style="vertical-align:middle;"><?php echo $naming_array[$i]['name']; ?></td>
                      <td class="dataTableHeadingContent txta-c column<?php echo $i; ?>" style="width:60px;vertical-align:middle;"><?php echo $totalchecked.'/'.$totalaccess; ?></td>
                      <td class="dataTableHeadingContent" style="width:90px;vertical-align:middle;"><?php echo TEXT_ALLOWED.' '.xtc_draw_checkbox_field('checkall'.$i, '', ($totalchecked === $totalaccess), '', 'class="checkall'.$i.'" onclick="set_checkbox('.$i.', '.$_GET['cID'].')"'); ?></td>
                    </tr>
                    <?php
                    foreach ($accounting_array[$i] as $details) {
                      ?>
                      <tr class="dataTableRow detail<?php echo $i; ?>" style="display:none;">
                        <td class="dataTableContent" style="width:18px; background:<?php echo $naming_array[$i]['color']; ?>;"></td>
                        <td class="dataTableContent" colspan="2"><?php echo $details['key']; ?></td>
                        <td class="dataTableContent txta-c" style="width:90px;"><?php echo xtc_draw_checkbox_field('access[]', $details['key'], $details['checked'], '', $details['params'].' class="access'.$i.'"').$details['hidden']; ?></td>
                      </tr>
                      <?php
                    }
                    ?>
                    <tr><td>&nbsp;</td></tr>
                  </table>
                  <?php
                  if (($i + 1) % $divide == 0 || ($i + 1) == $total) {
                    echo '</div>';
                    if (($i + 1) < $total) {
                      echo '<div class="accounting_col">';
                    }
                  }
                }
                echo '</div>';
                ?>
              </td>
            </tr>
          </table>
          <a class="button" href="<?php echo xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('action')));?>"><?php echo BUTTON_BACK; ?></a>
          <a class="button" id="collapseall" href="#"><?php echo BUTTON_DISPLAY_ALL; ?></a>
          <input type="submit" class="button flt-r" value="<?php echo BUTTON_SAVE; ?>" <?php echo $confirm_save_entry;?>>
        </form>
          
      </div>
    </td>
  </tr>
<!-- body_eof //-->
</table>

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<script>
  $('input[name="complete"]').on('click', function () {    
    $('input[name*="checkall"]').prop('checked', this.checked);
    $('input[name*="access"]').prop('checked', this.checked);
  });
  $('#collapseall').on('click', function () {
    $("[class*=detail]").show();
  });
  $("[class*=column]").on('click', function () {
    var num = $(this).attr('class').replace(/^\D+/g, ''); 
    $('.detail'+num).toggle();
  });
</script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>