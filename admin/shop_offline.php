<?php
  /* --------------------------------------------------------------
   $Id: shop_offline.php 3512 2012-08-23 17:46:58Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]

   --------------------------------------------------------------

   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(configuration.php,v 1.40 2002/12/29); www.oscommerce.com 
   (c) 2003 nextcommerce (configuration.php,v 1.16 2003/08/19); www.nextcommerce.org
   (c) 2003 XT-Commerce - www.xt-commerce.de
   (c) 2008 Gambio OHG - www.gambio.de

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

  require('includes/application_top.php');
  require_once(DIR_FS_INC . 'xtc_get_shop_conf.inc.php');  
  require_once(DIR_FS_INC . 'xtc_wysiwyg.inc.php');

  if(isset($_POST['go'])) {
    xtc_db_query("UPDATE ". "shop_configuration" ." SET configuration_value= '" . xtc_db_input(xtc_db_prepare_input($_POST['shop_offline'])). "' WHERE configuration_key = 'SHOP_OFFLINE'");
    xtc_db_query("UPDATE ". "shop_configuration" ." SET configuration_value= '" . xtc_db_input(xtc_db_prepare_input($_POST['offline_msg'])) . "' WHERE configuration_key = 'SHOP_OFFLINE_MSG'");
    
    // set allowed c.groups
    $group_ids='';
    if(isset($_POST['customers_groups'])) { 
      foreach($_POST['customers_groups'] as $b){
        $group_ids .= 'c_'.$b."_group ,";
      }
    }
    $customers_statuses_array = xtc_get_customers_statuses();
    if (xtc_get_shop_conf('SHOP_OFFLINE_ALLOWED_CUSTOMERS_GROUPS') !== false) {
      xtc_db_query("UPDATE ". "shop_configuration" ." SET configuration_value= '" . $group_ids . "' WHERE configuration_key = 'SHOP_OFFLINE_ALLOWED_CUSTOMERS_GROUPS'");
    } else {
      $sql_data_array = array(
          'configuration_key' => 'SHOP_OFFLINE_ALLOWED_CUSTOMERS_GROUPS', 
          'configuration_value' => $group_ids
        );
      xtc_db_perform("shop_configuration", $sql_data_array);
    }
    if (xtc_get_shop_conf('SHOP_OFFLINE_ALLOWED_CUSTOMERS_EMAILS') !== false) {
      xtc_db_query("UPDATE ". "shop_configuration" ." SET configuration_value= '" . xtc_db_input(xtc_db_prepare_input($_POST['customers_emails'])) . "' WHERE configuration_key = 'SHOP_OFFLINE_ALLOWED_CUSTOMERS_EMAILS'");
    } else {
      $sql_data_array = array(
          'configuration_key' => 'SHOP_OFFLINE_ALLOWED_CUSTOMERS_EMAILS', 
          'configuration_value' => xtc_db_prepare_input($_POST['customers_emails'])
        );
      xtc_db_perform("shop_configuration", $sql_data_array);
    }
    xtc_redirect(xtc_href_link('shop_offline.php'));  
  }
  
  $customers_statuses_array = xtc_get_customers_statuses();
  unset($customers_statuses_array[0]); //Admin
  unset($customers_statuses_array[1]); //Guest
  //echo '<pre>'.print_r($customers_statuses_array,1).'</pre>';
  $customers_statuses_array = array_merge($customers_statuses_array);
 
  $offline_status_array = array(
      array('id' => 'checked','text'=> CFG_TXT_YES),
      array('id' => '','text'=> CFG_TXT_NO)
  );
 
  require (DIR_WS_INCLUDES.'head.php');
?>
<script type="text/javascript" src="includes/general.js"></script>
<?php 
if (USE_WYSIWYG == 'true') {
  $query = xtc_db_query("SELECT code FROM ".TABLE_LANGUAGES." WHERE languages_id='".(int)$_SESSION['languages_id']."'");
  $data = xtc_db_fetch_array($query);
  $languages = xtc_get_languages();
  echo xtc_wysiwyg('shop_offline',$data['code']);
}
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
      <td class="boxCenter">
        <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_configuration.png'); ?></div>
        <div class="pageHeading"><?php echo HEADING_TITLE; ?></div>       
        <div class="main pdg2 flt-l">Configuration</div>
        <div valign="top" class="clear div_box mrg5">
          <div class="important_info"><?php echo BOX_SHOP_OFFLINE; ?></div>
          <?php 
            echo xtc_draw_form('offline', 'shop_offline.php', '', 'post', 'enctype="multipart/form-data"').PHP_EOL;
            echo '<div style="margin: 10px 0 0">'.PHP_EOL;
            echo SETTINGS_OFFLINE.PHP_EOL;
            echo '<div style="margin: 10px 0 20px">'.PHP_EOL;
            echo draw_on_off_selection('shop_offline', $offline_status_array, ((xtc_get_shop_conf('SHOP_OFFLINE') == 'checked') ? true : false)).PHP_EOL;
            echo '</div>'.PHP_EOL;
            echo '</div>'.PHP_EOL;
            ?>
            <?php echo SETTINGS_OFFLINE_MSG ?>:<br />
            <?php
              echo xtc_draw_textarea_field('offline_msg', 'soft', '150', '20', stripslashes(xtc_get_shop_conf('SHOP_OFFLINE_MSG')));
            ?>
            <div class="mrg5 pdg2">
              <div class="pdg2 flt-l" style="width:300px;margin-right:10px;">
                <?php echo SHOP_OFFLINE_ALLOWED_CUSTOMERS_GROUPS_TXT; ?>
              </div>
              <div class="customers-groups">
                <?php
                $customers_groups = xtc_get_shop_conf('SHOP_OFFLINE_ALLOWED_CUSTOMERS_GROUPS');
                for ($i=0;$n=sizeof($customers_statuses_array),$i<$n;$i++) {
                  $checked = false;
                  if (strstr($customers_groups,'c_'.$customers_statuses_array[$i]['id'].'_group')) {
                    $checked = true;
                  }
                  echo xtc_draw_checkbox_field('customers_groups[]', $customers_statuses_array[$i]['id'], $checked).' '.$customers_statuses_array[$i]['text'].'<br />';
                  }
                ?>
              </div>
            </div>
            <div class="clear"></div>
            
            <div class="mrg5 pdg2">
              <div class="pdg2 flt-l" style="width:300px;margin-right:10px;">
                <?php echo SHOP_OFFLINE_ALLOWED_CUSTOMERS_EMAILS_TXT; ?>
              </div>
              <div class="">
               <?php
               $customers_emails = xtc_get_shop_conf('SHOP_OFFLINE_ALLOWED_CUSTOMERS_EMAILS');            
               echo xtc_draw_textarea_field('customers_emails', 'soft', '103', '10', (isset($customers_emails) ? stripslashes($customers_emails) : ''), 'style="width:380px; height:50px;"'); 
               ?>
              </div>
            </div>
            <div class="clear"></div>
            <br />
            <div class="txta-r">
              <?php echo '<input type="submit" name="go" class="button" onclick="this.blur();" value="' . BUTTON_SAVE . '"/>'; ?>
            </div>
          </form>
        </div>                 
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