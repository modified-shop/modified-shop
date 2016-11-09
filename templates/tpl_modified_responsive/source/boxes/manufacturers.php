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
   
// include smarty
include(DIR_FS_BOXES_INC . 'smarty_default.php');

// set cache id
$cache_id = md5($_SESSION['language'] . (isset($_GET['manufacturers_id']) ? (int)$_GET['manufacturers_id'] : '0'));

if (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_manufacturers.html', $cache_id) || !$cache) {
  $box_content = '';
  
  $manufacturers_query = "SELECT DISTINCT m.manufacturers_id,
                                          m.manufacturers_name 
                                     FROM ".TABLE_MANUFACTURERS." as m
                                     JOIN ".TABLE_PRODUCTS." as p 
                                          ON m.manufacturers_id = p.manufacturers_id
                                             AND p.products_status = '1'
                                 ORDER BY m.manufacturers_name";
  $manufacturers_query = xtDBquery($manufacturers_query);
  $manufacturers_count = xtc_db_num_rows($manufacturers_query, true);
  if ($manufacturers_count > 0) {
    if ($manufacturers_count <= MAX_DISPLAY_MANUFACTURERS_IN_A_LIST) {
      // Display a list
      while ($manufacturers = xtc_db_fetch_array($manufacturers_query, true)) {
        $manufacturers_name = ((strlen($manufacturers['manufacturers_name']) > MAX_DISPLAY_MANUFACTURER_NAME_LEN) ? substr($manufacturers['manufacturers_name'], 0, MAX_DISPLAY_MANUFACTURER_NAME_LEN).'..' : $manufacturers['manufacturers_name']);
        if (isset ($_GET['manufacturers_id']) && ($_GET['manufacturers_id'] == $manufacturers['manufacturers_id'])) {
          $manufacturers_name = '<strong>'.$manufacturers_name.'</strong>';
        }
        $box_content .= '<a href="'.xtc_href_link(FILENAME_DEFAULT, xtc_manufacturer_link($manufacturers['manufacturers_id'],$manufacturers['manufacturers_name'])).'">'.$manufacturers_name.'</a><br />';
      }
    } else {
      // Display a drop-down
      $js = 'location = form.manufacturers_id.options[form.manufacturers_id.selectedIndex].value;';
      $manufacturers_array = array ();
      if (MAX_MANUFACTURERS_LIST < 2) {
        $manufacturers_array[] = array ('id' => '', 'text' => PULL_DOWN_DEFAULT);
        $js = 'if (form.manufacturers_id.selectedIndex != 0) location = form.manufacturers_id.options[form.manufacturers_id.selectedIndex].value;';
      }
      while ($manufacturers = xtc_db_fetch_array($manufacturers_query, true)) {
        $manufacturers_name = ((strlen($manufacturers['manufacturers_name']) > MAX_DISPLAY_MANUFACTURER_NAME_LEN) ? substr($manufacturers['manufacturers_name'], 0, MAX_DISPLAY_MANUFACTURER_NAME_LEN).'..' : $manufacturers['manufacturers_name']);
        $manufacturers_array[] = array ('id' => xtc_href_link(FILENAME_DEFAULT,xtc_manufacturer_link($manufacturers['manufacturers_id'],$manufacturers['manufacturers_name'])), 'text' => $manufacturers_name);
      }
      if (count($manufacturers_array) > 1) {
        $box_content = xtc_draw_form('manufacturers', xtc_href_link(FILENAME_DEFAULT, '', $request_type, false), 'get').xtc_draw_pull_down_menu('manufacturers_id', $manufacturers_array, isset($_GET['manufacturers_id']) ? xtc_href_link(FILENAME_DEFAULT,xtc_manufacturer_link($_GET['manufacturers_id'], isset($_GET['manufacturers_name']) ? $_GET['manufacturers_name'] : '')) : '', 'onchange="'.$js.'" size="'.MAX_MANUFACTURERS_LIST.'" style="width: 100%;"').xtc_hide_session_id().'</form>';
      }
    }
    
    if ($box_content != '') { 
      $box_smarty->assign('BOX_CONTENT', $box_content);
    }    
  }
}

// set cache ID
if (!$cache) {
  $box_manufacturers = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_manufacturers.html');
} else {
  $box_manufacturers = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_manufacturers.html', $cache_id);
}

$smarty->assign('box_MANUFACTURERS', $box_manufacturers);
?>