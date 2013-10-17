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
    xtc_db_query("UPDATE ". "shop_configuration" ." SET configuration_value= '" . $_POST['shop_offline']. "' WHERE configuration_key = 'SHOP_OFFLINE'");
    xtc_db_query("UPDATE ". "shop_configuration" ." SET configuration_value= '" . $_POST['offline_msg'] . "' WHERE configuration_key = 'SHOP_OFFLINE_MSG'");
    xtc_redirect(xtc_href_link('shop_offline.php'));  
  }
  
  require (DIR_WS_INCLUDES.'head.php');
?>
<script type="text/javascript" src="includes/general.js"></script>
<script type="text/javascript" src="includes/modules/fckeditor/fckeditor.js"></script>
<?php 
if (USE_WYSIWYG == 'true') {
  $query = xtc_db_query("SELECT code FROM ".TABLE_LANGUAGES." WHERE languages_id='".$_SESSION['languages_id']."'");
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
        <div class="clear dataTableHeadingContent"><?php echo BOX_SHOP_OFFLINE; ?></div>
        <div valign="top" class="main pdg2">
          <?php 
            echo xtc_draw_form('offline', 'shop_offline.php', '', 'post', 'enctype="multipart/form-data"');
            ?>
            <input type="checkbox" name="shop_offline" value="checked" <?php echo xtc_get_shop_conf('SHOP_OFFLINE'); ?>>
            <?php echo SETTINGS_OFFLINE ?><br /><br />
            <?php echo SETTINGS_OFFLINE_MSG ?>:<br />
            <?php
              echo xtc_draw_textarea_field('offline_msg', 'soft', '150', '20', stripslashes(xtc_get_shop_conf('SHOP_OFFLINE_MSG')));
            ?>
            <br />
            <br />
            <?php echo '<input type="submit" name="go" class="button" onclick="this.blur();" value="' . BUTTON_SAVE . '"/>'; ?>
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