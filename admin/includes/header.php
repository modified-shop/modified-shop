<?php
  /* --------------------------------------------------------------
   $Id: header.php 2638 2012-01-30 16:47:35Z hhacker $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce, www.oscommerce.com
   (c) 2003      nextcommerce; www.nextcommerce.org
   (c) 2006      xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/
  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

  if ($messageStack->size > 0) {
    echo $messageStack->output();
  }
  
  //define with and height for xtc_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT)
  define('HEADING_IMAGE_WIDTH',57);
  define('HEADING_IMAGE_HEIGHT',40);
  
  ((strip_tags($_GET['search']) != $_GET['search']) ? $_GET['search']=NULL : false);
  ((strip_tags($_GET['search_email']) != $_GET['search_email']) ? $_GET['search_email']=NULL : false);
  
  // Admin Language Switch
  if (!isset($lng) || (isset($lng) && !is_object($lng))) {
    include(DIR_WS_CLASSES . 'language.php');
    $lng = new language;
  }
  $languages_string = '';
  if (!isset($_GET['action']) || $_GET['action'] == 'edit') {
    reset($lng->catalog_languages);
    if (count($lng->catalog_languages) > 1) {
      while (list($key, $value) = each($lng->catalog_languages)) {
        if ( $value['status'] != 0 ){
          $languages_string .= '&nbsp;<a href="' . xtc_href_link($current_page, xtc_get_all_get_params(array('language', 'currency')).'language=' . $key, 'NONSSL') . '">' . xtc_image('../lang/' .  $value['directory'] .'/admin/images/' . $value['image'], $value['name']) . '</a>';
        }
      }
    }
  }
  
  if (!defined('NEW_ADMIN_STYLE')) { 
    // Admin Menu
    if (USE_ADMIN_TOP_MENU != 'false') {
    ?>
    <script src="includes/liststyle_menu/topmenu.js" type="text/javascript"></script>
    <script language="javascript">
    <!--
      document.write('<link href="includes/liststyle_menu/liststyle_top.css" rel="stylesheet" type="text/css" />');
    //-->
    </script>
    <?php
    } else {
      echo '<link href="includes/liststyle_menu/liststyle_left.css" rel="stylesheet" type="text/css" />';
    }
    ?>
    <noscript>
      <link href="includes/liststyle_menu/liststyle_left.css" rel="stylesheet" type="text/css" />
    </noscript>
  <?php
  }
  ?>
<div id="top1"><?php include(DIR_WS_INCLUDES . "admin_search_bar.php");?></div>
<table border="0" width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 5px;">
  <tr>
    <td><?php echo xtc_image(DIR_WS_IMAGES . 'logo.png', 'modified eCommerce Shopsoftware').'<br />&nbsp;&nbsp;&nbsp;'.$languages_string ; ?></td>
    <td valign="bottom" align="left" width="100%">
      <table border="0" cellspacing="0" cellpadding="2">
        <tr>          
          <td class="fastmenu" align="center">
            <a href="<?php echo xtc_href_link('orders.php', '', 'NONSSL') ; ?>">
              <img src="images/icons/fastnav/icon_orders.jpg" alt="<?php echo (BOX_ORDERS) ; ?>" width="40" height="40" border="0">
            </a>
            <br />
            <?php echo (BOX_ORDERS) ; ?>
          </td>          
          <td class="fastmenu" align="center">
            <a href="<?php echo xtc_href_link('content_manager.php', '', 'NONSSL') ; ?>">
              <img src="images/icons/fastnav/icon_content.jpg" alt="<?php echo (BOX_CONTENT) ; ?>" width="40" height="40" border="0">
            </a>
            <br />
            <?php echo (BOX_CONTENT) ; ?>
          </td>
          <td class="fastmenu" align="center">
            <a href="<?php echo xtc_href_link('backup.php', '', 'NONSSL') ; ?>">
              <img src="images/icons/fastnav/icon_backup.jpg" alt="<?php echo (BOX_BACKUP) ; ?>" width="40" height="40" border="0">
            </a>
            <br />
            <?php echo (BOX_BACKUP) ; ?>
          </td>
          <td class="fastmenu" align="center">
            <a href="<?php echo xtc_href_link('customers.php', '', 'NONSSL') ; ?>">
              <img src="images/icons/fastnav/icon_customers.jpg" alt="<?php echo (BOX_CUSTOMERS) ; ?>" width="40" height="40" border="0">
            </a>
            <br />
            <?php echo (BOX_CUSTOMERS) ; ?>
          </td>
          <td class="fastmenu" align="center">
            <a href="<?php echo xtc_href_link('categories.php', '', 'NONSSL') ; ?>">
              <img src="images/icons/fastnav/icon_categories.jpg" alt="<?php echo (BOX_CATEGORIES) ; ?>" width="40" height="40" border="0">
            </a>
            <br />
            <?php echo (BOX_CATEGORIES) ; ?>
          </td>
          <td class="fastmenu" align="center">
            <a href="<?php echo xtc_href_link('../index.php', '', 'NONSSL') ; ?>">
              <img src="images/icons/fastnav/icon_shop.jpg" width="40" height="40" border="0">
            </a>
            <br />
            Shop
          </td>
          <td class="fastmenu" align="center">
            <a href="<?php echo xtc_href_link('../logoff.php', '', 'NONSSL') ; ?>">
              <img src="images/icons/fastnav/icon_logout.jpg" width="40" height="40" border="0">
            </a>
            <br />
            Logout
          </td>
          <td class="fastmenu" align="center">
            <a href="<?php echo xtc_href_link('credits.php', '', 'NONSSL') ; ?>">
              <img src="images/icons/fastnav/icon_credits.jpg" width="40" height="40" border="0">
            </a>
            <br />
            Credits
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</div>
<div id="top2"></div>
<?php
if (USE_ADMIN_TOP_MENU != 'false') {
?>
<script language="javascript">
  <!--
    document.write('<?php ob_start(); require(DIR_WS_INCLUDES . "column_left.php"); $menucontent = ob_get_clean(); echo addslashes($menucontent);?>');
  //-->
</script>
<?php
}
?>