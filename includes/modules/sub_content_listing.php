<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

define('CONTENT_CONDITIONS_C2', str_replace('c2.', '', CONTENT_CONDITIONS_C1));
define('CONTENT_CONDITIONS_C3', str_replace('c3.', '', CONTENT_CONDITIONS_C1));

$module_smarty = new Smarty;
$module_smarty->assign('language', $_SESSION['language']);
$module_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');

// set cache ID
if (!CacheCheck()) {
  $cache = false;
  $module_smarty->caching = 0;
  $cache_id = null;
} else {
  $cache = true;
  $module_smarty->caching = 1;
  $module_smarty->cache_lifetime = CACHE_LIFETIME;
  $module_smarty->cache_modified_check = CACHE_CHECK == 'true';
  $cache_id = md5('lID:'.$_SESSION['language'].'|csID:'.$_SESSION['customers_status']['customers_status_id'].'|coID:'.$shop_content_data['content_id']);
}

if (!$module_smarty->is_cached(CURRENT_TEMPLATE.'/module/sub_content_listing.html', $cache_id) || !$cache) {
  $result = false;
  if ($shop_content_data['parent_id'] == '0') { 
    $shop_content_sub_query_1 = xtDBquery("SELECT c2.content_title,
                                                  c2.content_group,
                                                  c2.content_text,
                                                  c2.content_status as parent_status,
                                                  c1.content_title as title, 
                                                  c1.content_group as group_id,
                                                  c1.content_status,
                                                  c1.parent_id
                                             FROM ".TABLE_CONTENT_MANAGER." c1
                                             JOIN ".TABLE_CONTENT_MANAGER." c2
                                                  ON c2.content_id = c1.parent_id
                                                     AND c2.file_flag = c1.file_flag
                                                     AND c2.content_active = '1'
                                                     AND c2.languages_id = '".(int)$_SESSION['languages_id']."'
                                                     ".CONTENT_CONDITIONS_C2."
                                            WHERE c1.parent_id = '".$shop_content_data['content_id']."'
                                              AND c1.content_status = '1'
                                              AND c1.languages_id = '".(int)$_SESSION['languages_id']."'
                                                  ".CONTENT_CONDITIONS_C1."
                                         ORDER BY c1.sort_order");

    if (xtc_db_num_rows($shop_content_sub_query_1, true) > 0) {
      $shop_content_sub_query = $shop_content_sub_query_1;
      $result = true;
      $add_breadcrumb = false;
    }
  } else {
    $shop_content_sub_query_2 = xtDBquery("SELECT c3.content_title,
                                                  c3.content_group,
                                                  c3.content_text,
                                                  c2.content_title as title, 
                                                  c2.content_group as group_id,
                                                  c2.content_status,
                                                  c2.parent_id,
                                                  c3.content_status as parent_status
                                             FROM ".TABLE_CONTENT_MANAGER." c1
                                             JOIN ".TABLE_CONTENT_MANAGER." c2
                                                  ON c1.parent_id = c2.parent_id
                                                     AND c2.file_flag = c1.file_flag
                                                     AND c2.content_active = '1'
                                                     AND c2.languages_id = '".(int)$_SESSION['languages_id']."'
                                                     ".CONTENT_CONDITIONS_C2."
                                             JOIN ".TABLE_CONTENT_MANAGER." c3
                                                  ON c3.content_id = c2.parent_id
                                                     AND c3.file_flag = c2.file_flag
                                                     AND c3.content_active = '1'
                                                     AND c3.languages_id = '".(int)$_SESSION['languages_id']."'
                                                     ".CONTENT_CONDITIONS_C3."
                                            WHERE c1.content_id = '".$shop_content_data['content_id']."'
                                                  ".CONTENT_CONDITIONS_C1."
                                         ORDER BY c2.sort_order");

    if (xtc_db_num_rows($shop_content_sub_query_2, true) > 0) {
      $shop_content_sub_query = $shop_content_sub_query_2;
      $result = true;
      $add_breadcrumb = true;
    }
  }

  if ($result === true) {
    $sub_content = array();
    $parent_content = array();
    while ($shop_sub_content = xtc_db_fetch_array($shop_content_sub_query, true)) {
      if ($shop_sub_content['content_status'] == 1) {
        $sub_content[] = array(
          'CONTENT_TITLE' => $shop_sub_content['title'],
          'CONTENT_GROUP' => $shop_sub_content['group_id'],
          'CONTENT_LINK' => xtc_href_link(FILENAME_CONTENT, xtc_content_link($shop_sub_content['group_id'], $shop_sub_content['title']), 'NONSSL')
        );
      }
      if (count($parent_content) == 0) {
        if ($add_breadcrumb === true) {
          $add_breadcrumb = false;
          $breadcrumb->add($shop_sub_content['content_title'], xtc_href_link(FILENAME_CONTENT, xtc_content_link($shop_sub_content['content_group'], $shop_sub_content['content_title'])));
        }
        if ($shop_sub_content['parent_status'] == 1) {
          $parent_content = array(
            'CONTENT_TITLE' => $shop_sub_content['content_title'],
            'CONTENT_TEXT' => $shop_sub_content['content_text'],
            'CONTENT_GROUP' => $shop_sub_content['content_group'],
            'CONTENT_LINK' => xtc_href_link(FILENAME_CONTENT, xtc_content_link($shop_sub_content['content_group'], $shop_sub_content['content_title']), 'NONSSL')
          );
        }
      }
    }

    $module_smarty->assign('parent_content', $parent_content);
    $module_smarty->assign('sub_content', $sub_content);
  }
}

$module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/sub_content_listing.html', $cache_id);
$smarty->assign('SUB_CONTENT_LISTING', !empty($module) ? trim($module) : $module);
