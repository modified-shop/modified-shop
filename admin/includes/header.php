<?php
  /* --------------------------------------------------------------
   $Id$

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

  require_once(DIR_FS_INC . 'xtc_get_shop_conf.inc.php'); 
  
  ((isset($_GET['search']) && strip_tags($_GET['search']) != $_GET['search']) ? $_GET['search'] = NULL : false);
  ((isset($_GET['search_email']) && strip_tags($_GET['search_email']) != $_GET['search_email']) ? $_GET['search_email'] = NULL : false);
  
  // Admin Language Switch
  $ls_languages = xtc_get_languages();  
  $languages_array = array();
  if (count($ls_languages) > 1) {
    foreach ($ls_languages as $key => $value) {
      if (!isset($_GET['action']) || $_GET['action'] == 'edit') {
        $languages_array[] = '<a href="' . xtc_href_link($current_page, xtc_get_all_get_params(array('language', 'currency')).'language=' . $value['code'], 'NONSSL') . '">' . xtc_image('../lang/' .  $value['directory'] .'/admin/images/' . $value['image'], $value['name']) . '</a>';
      } else {
        $languages_array[] = '<span class="nolink">' . xtc_image('../lang/' .  $value['directory'] .'/admin/images/' . $value['image'], $value['name']).'</span>';
      }
    }
  }
  $languages_string = implode('&nbsp;', $languages_array);
  
  // newsfeed
  require_once(DIR_FS_INC.'get_newsfeed.inc.php');
  get_newsfeed();
  
  // news count
  $num_news_query = xtc_db_query("SELECT count(*) as total FROM newsfeed WHERE news_date > '".NEWSFEED_LAST_READ."'");
  $num_news = xtc_db_fetch_array($num_news_query);

  // check update
  require_once(DIR_FS_INC.'check_version_update.inc.php');
  $update_array = check_version_update();
  
  // caching
  $configuration_query = xtc_db_query("SELECT count(*) as total 
                                         FROM ".TABLE_CONFIGURATION."
                                        WHERE configuration_value = 'true' 
                                          AND (configuration_key = 'DB_CACHE' OR configuration_key = 'USE_CACHE')");
  $configuration = xtc_db_fetch_array($configuration_query);
?> 
<div id="fixed-header"<?php echo ((USE_ADMIN_FIXED_SEARCH == 'true') ? ' class="active"' : ''); ?>>
  <div class="admin_spacer"></div>
  <div class="adminbar">
    <div class="row_adminbar cf">
      <ul class="cf">
        <li class="logo"><a href="<?php echo xtc_catalog_href_link('index.php'); ?>"><?php echo xtc_image(DIR_WS_IMAGES . 'logo.png', 'modified eCommerce Shopsoftware');?></a></li>
        <li class="language"><?php echo $languages_string ;?></li>
        <?php
          $favorites = array();

          $favorites[] = array(
              'file'  => 'index.php',
              'par'  => '', 
              'mode'  => 1,
              'icon'  => (xtc_get_shop_conf('SHOP_OFFLINE') == 'checked' ? 'icon_shop_closed.png' : 'icon_shop_open.png'),
              'name'  => BOX_SHOP,
              'class' => ''
            );
          $favorites[] = array(
              'file'  => 'orders.php',
              'par'  => '', 
              'mode'  => 0,
              'icon'  => 'icon_orders.png',
              'name'  => BOX_ORDERS,
              'class' => ''
            );
          $favorites[] = array(
              'file'  => 'customers.php',
              'par'  => '', 
              'mode'  => 0,
              'icon'  => 'icon_customers.png',
              'name'  => BOX_CUSTOMERS,
              'class' => ''
            );
          $favorites[] = array(
              'file'  => 'categories.php',
              'par'  => '', 
              'mode'  => 0,
              'icon'  => 'icon_categories.png',
              'name'  => BOX_CATEGORIES,
              'class' => ''
            );
          $favorites[] = array(
              'file'  => 'content_manager.php',
              'par'  => '', 
              'mode'  => 0,
              'icon'  => 'icon_content.png',
              'name'  => BOX_CONTENT,
              'class' => ''
            );
          $favorites[] = array(
              'file'  => 'backup.php',
              'par'  => '', 
              'mode'  => 0,
              'icon'  => 'icon_backup.png',
              'name'  => BOX_BACKUP,
              'class' => ''
            );

          if ($configuration['total'] > 0) {
            $favorites[] = array(
                'file' => 'configuration.php',
                'par' => 'gID=11',
                'mode' => 0,
                'icon' => 'icon_attention.png',
                'name' => BOX_CACHING,
                'class' => ''
              );
          }

          if (xtc_get_shop_conf('SHOP_OFFLINE') == 'checked') {
            $favorites[] = array(
                'file' => 'shop_offline.php',
                'par' => '',
                'mode' => 0,
                'icon' => 'icon_offline.png',
                'name' => BOX_OFFLINE,
                'class' => ''
              );
          }
          
          if (USE_ADMIN_FIXED_SEARCH == 'false') {
            $favorites[] = array(
                'file' => "javascript:void(0)\" onclick=\"$('#searchbar_new').toggle('fast').parent('#fixed-header').toggleClass('active').siblings('.fixed-header-height').toggleClass('active');",
                'par' => '',
                'mode' => 2,
                'icon' => 'icon_search.png',
                'name' => BUTTON_SEARCH,
                'class' => ''
              );
          }
          
          $favorites[] = array(
              'file' => 'logoff.php',
              'par' => '', 
              'mode' => 1,
              'icon' => 'icon_logout.png',
              'name' => BOX_LOGOUT,
              'class' => 'right'
            );    
          $favorites[] = array(
              'file'  => 'newsfeed.php',
              'par'   => '', 
              'mode'  => 0,
              'icon'  => 'icon_feed.png',
              'name'  => 'News',
              'class' => 'right',
              'count' => $num_news['total']
            );
          $favorites[] = array(
              'file'  => 'credits.php',
              'par'   => '', 
              'mode'  => 0,
              'icon'  => 'icon_credits.png',
              'name'  => BOX_CREDITS,
              'class' => 'right'
            );
          $favorites[] = array(
              'file'  => 'check_update.php',
              'par'   => '', 
              'mode'  => 0,
              'icon'  => 'icon_update.png',
              'name'  => BOX_UPDATE,
              'class' => 'right',
              'count' => $update_array['update']
            );
          $favorites[] = array(
              'file'  => xtc_href_link('support.php'),
              'par'   => '', 
              'mode'  => 2,
              'icon'  => 'icon_support.png',
              'name'  => BOX_SUPPORT,
              'class' => 'right',
            );
          $favorites[] = array(
              'file'  => 'https://www.modified-shop.org/shop/',
              'target' => '_blank',
              'par'   => '', 
              'mode'  => 2,
              'icon'  => 'icon_modified_shop.png',
              'name'  => BOX_SHOP,
              'class' => 'right',
            );

          // overwrite with hooks
          if(isset($own_favorites) && is_array($own_favorites)) {
            foreach ($own_favorites as $key => $value) {
              $favorites[$key] = $value;
            }
          }

          $page_permission_query = xtc_db_query("SELECT * FROM ".TABLE_ADMIN_ACCESS." WHERE customers_id = '".$_SESSION['customer_id']."'");
          $page_permission = xtc_db_fetch_array($page_permission_query);
  
          foreach ($favorites as $f) {
            if (is_array($f)) {
              if ($f['mode'] == 2) {
                $link = $f['file'].$f['par'];
              } else if ($f['mode'] == 1) {
                $link = xtc_catalog_href_link($f['file'], $f['par'], 'NONSSL', true);
              } else {
                if ($page_permission[strtok($f['file'], '.')] != '1') continue;
                $link = xtc_href_link($f['file'], $f['par'], 'NONSSL', true);
              }
              echo '<li'.($f['class'] ? ' class="'.$f['class'].'"' : '').'><a'.((isset($f['target'])) ? ' target="'.$f['target'].'"' : '').' href="' . $link . '">'.
                   xtc_image(DIR_WS_ICONS.'fastnav/'.$f['icon'], $f['name'], 32, 32).
                   (isset($f['count']) && $f['count'] ? '<div class="icon_count">'.$f['count'].'</div>' : '').
                   '</a></li>' . PHP_EOL;
            }
          }
        ?>
      </ul>
    </div>
  </div>
  <?php 
  include(DIR_WS_INCLUDES . "admin_search_bar.php");

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
<div class="fixed-header-height<?php echo ((USE_ADMIN_FIXED_SEARCH == 'true') ? ' active' : ''); ?>">&nbsp;</div>

<noscript>
    <div class="fixed_messageStack">
        <div class="error_message">
            <?php echo JAVASCRIPT_DISABLED_INFO;?>
        </div>
    </div>
</noscript>

<?php
  if ($messageStack->size > 0) {
    echo '<div class="fixed_messageStack">'.$messageStack->output().'</div>';
  }
?>