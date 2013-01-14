<?php
  /* -----------------------------------------------------------------------------------------
   $Id: manufacturers.php 2081 2011-08-03 09:06:48Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(manufacturers.php,v 1.18 2003/02/10); www.oscommerce.com
   (c) 2003 nextcommerce (manufacturers.php,v 1.9 2003/08/17); www.nextcommerce.org
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  $box_smarty = new smarty;
  $box_content = '';

  $box_smarty->assign('language', $_SESSION['language']);
  // set cache ID
  if (!CacheCheck()) {
    $cache = false;
    $box_smarty->caching = 0;
  } else {
    $cache = true;
    $box_smarty->caching = 1;
    $box_smarty->cache_lifetime = CACHE_LIFETIME;
    $box_smarty->cache_modified_check = CACHE_CHECK;
    $cache_id = $_SESSION['language'] . (isset($_GET['manufacturers_id']) ? (int)$_GET['manufacturers_id'] : 0);
  }

  if (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_manufacturers.html', $cache_id) || !$cache) {

    $box_smarty->assign('tpl_path', 'templates/'.CURRENT_TEMPLATE.'/');
    
    // include needed funtions
    require_once (DIR_FS_INC.'xtc_hide_session_id.inc.php');
    require_once (DIR_FS_INC.'xtc_draw_form.inc.php');
    require_once (DIR_FS_INC.'xtc_draw_pull_down_menu.inc.php');

    $manufacturers_query = "select distinct m.manufacturers_id, m.manufacturers_name from ".TABLE_MANUFACTURERS." as m, ".TABLE_PRODUCTS." as p where m.manufacturers_id=p.manufacturers_id order by m.manufacturers_name";
    $manufacturers_query = xtDBquery($manufacturers_query);
    if (xtc_db_num_rows($manufacturers_query, true) <= MAX_DISPLAY_MANUFACTURERS_IN_A_LIST) {
      // Display a list
      $manufacturers_list = '';
      while ($manufacturers = xtc_db_fetch_array($manufacturers_query, true)) {
        $manufacturers_name = ((strlen($manufacturers['manufacturers_name']) > MAX_DISPLAY_MANUFACTURER_NAME_LEN) ? substr($manufacturers['manufacturers_name'], 0, MAX_DISPLAY_MANUFACTURER_NAME_LEN).'..' : $manufacturers['manufacturers_name']);
        if (isset ($_GET['manufacturers_id']) && ($_GET['manufacturers_id'] == $manufacturers['manufacturers_id'])) {
          $manufacturers_name = '<strong>'.$manufacturers_name.'</strong>';
        }
        $manufacturers_list .= '<a href="'.xtc_href_link(DIR_WS_CATALOG . FILENAME_DEFAULT, 'manufacturers_id='.$manufacturers['manufacturers_id']).'">'.$manufacturers_name.'</a><br />'; //DokuMan - 2010-09-30 - added DIR_WS_CATALOG for manufacturer_dropdown to work properly
      }
      $box_content = $manufacturers_list;
    } else {
      // Display a drop-down
      $manufacturers_array = array ();
      if (MAX_MANUFACTURERS_LIST < 2) {
        $manufacturers_array[] = array ('id' => '', 'text' => PULL_DOWN_DEFAULT);
      }
      while ($manufacturers = xtc_db_fetch_array($manufacturers_query, true)) {
        $manufacturers_name = ((strlen($manufacturers['manufacturers_name']) > MAX_DISPLAY_MANUFACTURER_NAME_LEN) ? substr($manufacturers['manufacturers_name'], 0, MAX_DISPLAY_MANUFACTURER_NAME_LEN).'..' : $manufacturers['manufacturers_name']);
        $manufacturers_array[] = array ('id' => xtc_href_link(FILENAME_DEFAULT,xtc_manufacturer_link($manufacturers['manufacturers_id'],$manufacturers['manufacturers_name'])), 'text' => $manufacturers_name);
      }
      //BOF - h-h-h - 2011-05-16 - fix view selected manufacturers
      //$box_content = xtc_draw_form('manufacturers', xtc_href_link(FILENAME_DEFAULT, '', 'NONSSL', false), 'get').xtc_draw_pull_down_menu('manufacturers_id', $manufacturers_array, isset($_GET['manufacturers_id']) ? $_GET['manufacturers_id'] : '', 'onchange="if (form.manufacturers_id.selectedIndex != 0) location = form.manufacturers_id.options[form.manufacturers_id.selectedIndex].value;" size="'.MAX_MANUFACTURERS_LIST.'" style="width: 100%;"').xtc_hide_session_id().'</form>';
      $box_content = xtc_draw_form('manufacturers', xtc_href_link(FILENAME_DEFAULT, '', 'NONSSL', false), 'get').xtc_draw_pull_down_menu('manufacturers_id', $manufacturers_array, isset($_GET['manufacturers_id']) ? xtc_href_link(FILENAME_DEFAULT,xtc_manufacturer_link($_GET['manufacturers_id'], isset($_GET['manufacturers_name']) ? $_GET['manufacturers_name'] : '')) : '', 'onchange="if (form.manufacturers_id.selectedIndex != 0) location = form.manufacturers_id.options[form.manufacturers_id.selectedIndex].value;" size="'.MAX_MANUFACTURERS_LIST.'" style="width: 100%;"').xtc_hide_session_id().'</form>';
      //EOF - h-h-h - 2011-05-16 - fix view selected manufacturers
    }
    if ($box_content != '')
      $box_smarty->assign('BOX_CONTENT', $box_content);
  }
  // set cache ID
  if (!$cache) {
    $box_manufacturers = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_manufacturers.html');
  } else {
    $box_manufacturers = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_manufacturers.html', $cache_id);
  }
  $smarty->assign('box_MANUFACTURERS', $box_manufacturers);
?>