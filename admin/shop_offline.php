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
    <table border="0" width="100%" cellspacing="2" cellpadding="2">
      <tr>
        <td class="columnLeft2" width="<?php echo BOX_WIDTH; ?>" valign="top">
          <table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
            <!-- left_navigation //-->
            <?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
            <!-- left_navigation_eof //-->
          </table>
        </td>
        <!-- body_text //-->
        <td class="boxCenter" width="100%" valign="top">
          <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
              <td>
                <div class="pageHeading"><?php echo HEADING_TITLE; ?></div>
                <br />
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                  <tr>
                    <td class="dataTableHeadingContent">
                      <?php echo BOX_SHOP_OFFLINE; ?>                          
                    </td>
                  </tr>
                </table>
                <table border="0" width="100%" cellspacing="0" cellpadding="0" style="width: 100%; border: 1px solid; border-color: #aaaaaa; padding: 5px;">
                  <tr>
                    <td valign="top" class="main">    
                      <form name="img_upload" action="shop_offline.php" method="post" enctype="multipart/form-data">
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
                    </td>
                  </tr>
                </table>
                <br />
              </td>
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
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>