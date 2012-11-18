<?php
  /* -----------------------------------------------------------------------------------------
   $Id$

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2009 - 2012 xtcModified
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(information.php,v 1.6 2003/02/10); www.oscommerce.com
   (c) 2003   nextcommerce (information.php,v 1.8 2003/08/21); www.nextcommerce.org
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  $box_smarty = new smarty;
  $content_string = '';
  //$rebuild = false; //DokuMan - 2010-02-28 - fix Smarty cache error on unlink

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
    $cache_id = $_SESSION['language'].$_SESSION['customers_status']['customers_status_id'];
  }

  if (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_information.html', $cache_id) || !$cache) {
    //BOF - GTB - 2010-08-03 - Security Fix - Base
    $box_smarty->assign('tpl_path',DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
    //$box_smarty->assign('tpl_path', 'templates/'.CURRENT_TEMPLATE.'/');
    //EOF - GTB - 2010-08-03 - Security Fix - Base
    //$rebuild = true; //DokuMan - 2010-02-28 - fix Smarty cache error on unlink

    $group_check = '';
    if (GROUP_CHECK == 'true') {
      $group_check = "and group_ids LIKE '%c_".$_SESSION['customers_status']['customers_status_id']."_group%'";
    }

    $content_query = "SELECT
                             content_id,
                             categories_id,
                             parent_id,
                             content_title,
                             content_group
                        FROM ".TABLE_CONTENT_MANAGER."
                       WHERE languages_id='".(int) $_SESSION['languages_id']."'
                         AND file_flag=0 
                             ".$group_check." 
                         AND content_status=1 
                    ORDER BY sort_order";

    $content_query = xtDBquery($content_query);

    $content_string='<ul class="contentlist">';
    while ($content_data = xtc_db_fetch_array($content_query, true)) {
      $SEF_parameter = '';
      if (SEARCH_ENGINE_FRIENDLY_URLS == 'true')
        $SEF_parameter = '&product='.xtc_cleanName($content_data['content_title']);
      $content_string .= '<li><a href="'.xtc_href_link(FILENAME_CONTENT, 'coID='.$content_data['content_group'].$SEF_parameter).'"><strong>'.$content_data['content_title'].'</strong></a></li>';
    }
    if ($content_string != '') {
      $content_string.='</ul>';
      $box_smarty->assign('BOX_CONTENT', $content_string);
    }
  }

  //BOF - DokuMan - 2010-02-28 - fix Smarty cache error on unlink
  //if ($rebuild) $box_smarty->clear_cache(CURRENT_TEMPLATE.'/boxes/box_information.html', $cache_id);
  //$box_information = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_information.html',$cache_id);
  if (!$cache) {
    $box_information = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_information.html');
  } else {
    $box_information = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_information.html', $cache_id);
  }
  //EOF - DokuMan - 2010-02-28 - fix Smarty cache error on unlink
  $smarty->assign('box_INFORMATION', $box_information);
?>