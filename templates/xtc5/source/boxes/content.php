<?php
  /* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(information.php,v 1.6 2003/02/10); www.oscommerce.com
   (c) 2003   nextcommerce (content.php,v 1.2 2003/08/21); www.nextcommerce.org
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
$box_smarty = new smarty;
$box_content = '';

$box_smarty->assign('language', $_SESSION['language']);

// set cache ID
if (!CacheCheck()) {
	$cache=false;
	$box_smarty->caching = 0;
} else {
	$cache=true;
	$box_smarty->caching = 1;
	$box_smarty->cache_lifetime = CACHE_LIFETIME;
	$box_smarty->cache_modified_check = CACHE_CHECK;
	$cache_id = $_SESSION['language'].$_SESSION['customers_status']['customers_status_id'].$coPath;
}

if (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_content.html', $cache_id) || !$cache) {
  //BOF - GTB - 2010-08-03 - Security Fix - Base
  $box_smarty->assign('tpl_path',DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
  //$box_smarty->assign('tpl_path', 'templates/'.CURRENT_TEMPLATE.'/');
  //EOF - GTB - 2010-08-03 - Security Fix - Base

  // include needed functions
  require_once (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/inc/xtc_show_content.inc.php');

  $content_array=array();
  $content_string = '';
  $group_check = '';
	if (GROUP_CHECK == 'true') {
		$group_check = " AND group_ids LIKE '%c_".$_SESSION['customers_status']['customers_status_id']."_group%'";
	}
	$content_query = xtDBquery("SELECT content_id,
                                     categories_id,
                                     parent_id,
                                     content_title,
                                     content_group
                                FROM ".TABLE_CONTENT_MANAGER."
                               WHERE languages_id='".(int) $_SESSION['languages_id']."'
                                 AND file_flag='1'
                                     ".$group_check."
                                 AND content_status='1'
                                 AND parent_id='0'
                            ORDER BY sort_order");

  if (xtc_db_num_rows($content_query, true) > 0) {
    while ($content_data = xtc_db_fetch_array($content_query, true)) {
      $content_array[$content_data['content_id']] = array (
                                                  'name' => $content_data['content_title'],
                                                  'parent' => $content_data['parent_id'],
                                                  'level' => 0,
                                                  'coID' => $content_data['content_group'], 
                                                  'path' => $content_data['content_id'],
                                                  'next_id' => false
                                                  );

      if (isset ($prev_cid)) {
        $content_array[$prev_cid]['next_id'] = $content_data['content_id'];
      }

      $prev_cid = $content_data['content_id'];

      if (!isset ($first_content_element)) {
        $first_content_element = $content_data['content_id'];
      }
	  }
  }

  if ($coPath) {
    $new_path = '';
    $coid = explode('_', $coPath);
    reset($coid);
    while (list ($key, $value) = each($coid)) {
      unset ($prev_cid);
      unset ($first_cid);
      $content_query = xtDBquery("SELECT content_id,
                                            parent_id,
                                            content_title,
                                            content_group
                                       FROM ".TABLE_CONTENT_MANAGER."
                                      WHERE languages_id='".(int) $_SESSION['languages_id']."'
                                        AND file_flag='1'
                                            ".$group_check."
                                        AND content_status='1'
                                        AND parent_id='".$value."'
                                   ORDER BY sort_order");

      if (xtc_db_num_rows($content_query, true) > 0) {
        $new_path .= $value;
        while ($content = xtc_db_fetch_array($content_query, true)) {
          $content_array[$content['content_id']] = array ('name' => $content['content_title'], 
                                              'parent' => $content['parent_id'], 
                                              'level' => $key +1, 
                                              'coID' => $content['content_group'], 
                                              'path' => $new_path.'_'.$content['content_id'], 
                                              'next_id' => false);
          if (isset ($prev_cid)) {
            $content_array[$prev_cid]['next_id'] = $content['content_id'];
          }
          $prev_cid = $content['content_id'];
          if (!isset ($first_cid)) {
            $first_cid = $content['content_id'];
          }
          $last_cid = $content['content_id'];
        }

        $content_array[$last_cid]['next_id'] = isset($content_array[$value]['next_id']) ? $content_array[$value]['next_id'] : 0;
        $content_array[$value]['next_id'] = $first_cid;
        $new_path .= '_';
      } else {
        break;
      }
    }
  }
  if(!empty($first_content_element)) {
   xtc_show_content($first_content_element);
  }
  $box_smarty->assign('BOX_CONTENT', $content_string);
}

if (!$cache) {
  $box_content = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_content.html');
} else {
  $box_content = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_content.html', $cache_id);
}

$smarty->assign('box_CONTENT', $box_content);
?>