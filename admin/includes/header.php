<?php
  /* --------------------------------------------------------------
   $Id: header.php 5065 2013-07-15 12:22:56Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce, www.oscommerce.com
   (c) 2003  nextcommerce; www.nextcommerce.org
   (c) 2006      xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/
  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

  if ($messageStack->size > 0) {
    echo '<div class="fixed_messageStack">'.$messageStack->output() . '</div>';
  }
  
  //define with and height for xtc_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT)
  define('HEADING_IMAGE_WIDTH',57);
  define('HEADING_IMAGE_HEIGHT',40);
  
  ((isset($_GET['search']) && strip_tags($_GET['search']) != $_GET['search']) ? $_GET['search'] = NULL : false);
  ((isset($_GET['search_email']) && strip_tags($_GET['search_email']) != $_GET['search_email']) ? $_GET['search_email'] = NULL : false);
  
  // Admin Language Switch
  $languages_string = '';
  if (!isset($_GET['action']) || $_GET['action'] == 'edit') {
    $ls_languages = xtc_get_languages();
    if (count($ls_languages) > 1) {
      while (list($key, $value) = each($ls_languages)) {
        $languages_string .= '&nbsp;<a href="' . xtc_href_link($current_page, xtc_get_all_get_params(array('language', 'currency')).'language=' . $value['code'], 'NONSSL') . '">' . xtc_image('../lang/' .  $value['directory'] .'/admin/images/' . $value['image'], $value['name']) . '</a>';
      }
    }
  }
  ?>

<div id="fixed-header">
<div id="top1"><?php include(DIR_WS_INCLUDES . "admin_search_bar.php");?></div>

<div id="favorites">
  <div id="logo">
    <div><?php echo xtc_image(DIR_WS_IMAGES . 'logo.png', 'modified eCommerce Shopsoftware');?></div>
    <div><?php echo '&nbsp;&nbsp;&nbsp;'.$languages_string ;?></div>
  </div>
  <table class="favorites">
    <tr>          
      <td class="fastmenu">
        <a href="<?php echo xtc_href_link('orders.php', '', 'NONSSL') ; ?>">
          <?php echo xtc_image(DIR_WS_ICONS .'fastnav/icon_orders.png', BOX_ORDERS, 32, 32);?>
        </a>
        <br />
        <?php echo (BOX_ORDERS) ; ?>
      </td>          
      <td class="fastmenu">
        <a href="<?php echo xtc_href_link('content_manager.php', '', 'NONSSL') ; ?>">
          <?php echo xtc_image(DIR_WS_ICONS .'fastnav/icon_content.png', BOX_CONTENT, 32, 32);?>          
        </a>
        <br />
        <?php echo (BOX_CONTENT) ; ?>
      </td>
      <td class="fastmenu">
        <a href="<?php echo xtc_href_link('backup.php', '', 'NONSSL') ; ?>">
          <?php echo xtc_image(DIR_WS_ICONS .'fastnav/icon_backup.png', BOX_BACKUP, 32, 32);?>
        </a>
        <br />
        <?php echo (BOX_BACKUP) ; ?>
      </td>
      <td class="fastmenu">
        <a href="<?php echo xtc_href_link('customers.php', '', 'NONSSL') ; ?>">
          <?php echo xtc_image(DIR_WS_ICONS .'fastnav/icon_customers.png', BOX_CUSTOMERS, 32, 32);?> 
        </a>
        <br />
        <?php echo (BOX_CUSTOMERS) ; ?>
      </td>
      <td class="fastmenu">
        <a href="<?php echo xtc_href_link('categories.php', '', 'NONSSL') ; ?>">
          <?php echo xtc_image(DIR_WS_ICONS .'fastnav/icon_categories.png', BOX_CATEGORIES, 32, 32);?>
        </a>
        <br />
        <?php echo (BOX_CATEGORIES) ; ?>
      </td>
      <td class="fastmenu">
        <a href="<?php echo xtc_catalog_href_link('index.php', '', 'NONSSL') ; ?>">
          <?php echo xtc_image(DIR_WS_ICONS .'fastnav/icon_shop.png', BOX_SHOP, 32, 32);?>
        </a>
        <br />
        <?php echo (BOX_SHOP) ; ?>
      </td>
      <td class="fastmenu">
        <a href="<?php echo xtc_catalog_href_link('logoff.php', '', 'NONSSL') ; ?>">
          <?php echo xtc_image(DIR_WS_ICONS .'fastnav/icon_logout.png', BOX_LOGOUT, 32, 32);?>
        </a>
        <br />
        <?php echo (BOX_LOGOUT) ; ?>
      </td>
      <td class="fastmenu">
        <a href="<?php echo xtc_href_link('credits.php', '', 'NONSSL') ; ?>">
          <?php echo xtc_image(DIR_WS_ICONS .'fastnav/icon_credits.png', BOX_CREDITS, 32, 32);?>
        </a>
        <br />
        <?php echo (BOX_CREDITS) ; ?>
      </td>
      <td class="fastmenu">
        <a href="<?php echo xtc_href_link('check_update.php', '', 'NONSSL') ; ?>">
          <?php echo xtc_image(DIR_WS_ICONS .'fastnav/icon_update.png', BOX_UPDATE, 32, 32);?>
        </a>
        <br />
        <?php echo (BOX_UPDATE) ; ?>
      </td>
    </tr>
  </table>
</div>

<div id="top2" class="clear"></div>
<?php
if (USE_ADMIN_TOP_MENU != 'false') {
  if (defined('NEW_ADMIN_STYLE')) { 
    require_once(DIR_WS_INCLUDES . "column_left.php");
  } else {
    ?>
    <script type="text/javascript">
      <!--
        document.write('<?php ob_start(); require(DIR_WS_INCLUDES . "column_left.php"); $menucontent = ob_get_clean(); echo addslashes($menucontent);?>');
      //-->
    </script>
    <?php
  }
}
?>
</div>
<div class="fixed-header-height">&nbsp;</div>