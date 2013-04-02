<?php
  /* -----------------------------------------------------------------------------------------
   $Id: categories.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(categories.php,v 1.23 2002/11/12); www.oscommerce.com
   (c) 2003 nextcommerce (categories.php,v 1.10 2003/08/17); www.nextcommerce.org
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   Enable_Disable_Categories 1.3          Autor: Mikel Williams | mikel@ladykatcostumes.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  // reset var
  $box_smarty = new smarty;
  $box_content = '';
  //$rebuild = false; //DokuMan - 2010-02-28 - fix Smarty cache error on unlink

  $box_smarty->assign('language', $_SESSION['language']);
  // set cache ID
  if (!CacheCheck()) {
    $cache=false;
    $box_smarty->caching = 0;
    $cache_id = null; //DokuMan - 2010-02-26 - Undefined variable: cache_id
  } else {
    $cache=true;
    $box_smarty->caching = 1;
    $box_smarty->cache_lifetime = CACHE_LIFETIME;
    $box_smarty->cache_modified_check = CACHE_CHECK;
    $cache_id = $_SESSION['language'].$_SESSION['customers_status']['customers_status_id'].'-'.$cPath;
  }

  if(!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_categories.html', $cache_id) || !$cache){
    //BOF - GTB - 2010-08-03 - Security Fix - Base
    $box_smarty->assign('tpl_path',DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
    //$box_smarty->assign('tpl_path', 'templates/'.CURRENT_TEMPLATE.'/');
    //EOF - GTB - 2010-08-03 - Security Fix - Base
    //$rebuild=true; //DokuMan - 2010-02-28 - fix Smarty cache error on unlink

    // include needed functions
    require_once (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/inc/xtc_show_category.inc.php');
    require_once (DIR_FS_INC.'xtc_has_category_subcategories.inc.php');
    require_once (DIR_FS_INC.'xtc_count_products_in_category.inc.php');

    $categories_string = '';
    $group_check = ''; //DokuMan - 2010-02-28 - set undefined variable group_check
    if (GROUP_CHECK == 'true') {
      $group_check = "and c.group_permission_".$_SESSION['customers_status']['customers_status_id']."=1 ";
    }
    //BOF - web - 2010-11-11 - Do not display empty catagorie names - add and trim(cd.categories_name) != ''
    $categories_query = "select c.categories_id,
                                cd.categories_name,
                                c.parent_id
                           from ".TABLE_CATEGORIES." c,
                                ".TABLE_CATEGORIES_DESCRIPTION." cd
                          where c.categories_status = '1'
                            and c.parent_id = '0'
                                ".$group_check."
                            and c.categories_id = cd.categories_id
                            and cd.language_id='".(int) $_SESSION['languages_id']."'
                            and trim(cd.categories_name) != ''
                          order by sort_order, cd.categories_name";
    //EOF - web - 2010-11-11 - Do not display empty catagorie names - add and trim(cd.categories_name) != ''

    $categories_query = xtDBquery($categories_query);
    while ($categories = xtc_db_fetch_array($categories_query, true)) {
      $foo[$categories['categories_id']] = array (
                                                  'name' => $categories['categories_name'],
                                                  'parent' => $categories['parent_id'],
                                                  'level' => 0,
                                                  'path' => $categories['categories_id'],
                                                  'next_id' => false
                                                  );

      if (isset ($prev_id)) {
        $foo[$prev_id]['next_id'] = $categories['categories_id'];
      }

      $prev_id = $categories['categories_id'];

      if (!isset ($first_element)) {
        $first_element = $categories['categories_id'];
      }
    }
    //------------------------
    if ($cPath) {
      $new_path = '';
      $id = explode('_', $cPath); // Hetfield - 2009-08-18 - replaced deprecated function split with explode to be ready for PHP >= 5.3
      reset($id);
      while (list ($key, $value) = each($id)) {
        unset ($prev_id);
        unset ($first_id);
        //BOF - web - 2010-11-11 - Do not display empty catagorie names - add and trim(cd.categories_name) != ''
        $categories_query = "select c.categories_id,
                                    cd.categories_name,
                                    c.parent_id
                               from ".TABLE_CATEGORIES." c,
                                    ".TABLE_CATEGORIES_DESCRIPTION." cd
                              where c.categories_status = '1'
                                and c.parent_id = '".$value."'
                                    ".$group_check."
                                and c.categories_id = cd.categories_id
                                and cd.language_id='".$_SESSION['languages_id']."'
                                and trim(cd.categories_name) != ''
                              order by sort_order, cd.categories_name";
        //EOF - web - 2010-11-11 - Do not display empty catagorie names - add and trim(cd.categories_name) != ''
        $categories_query = xtDBquery($categories_query);
        $category_check = xtc_db_num_rows($categories_query, true);
        if ($category_check > 0) {
          $new_path .= $value;
          while ($row = xtc_db_fetch_array($categories_query, true)) {
            $foo[$row['categories_id']] = array ('name' => $row['categories_name'], 'parent' => $row['parent_id'], 'level' => $key +1, 'path' => $new_path.'_'.$row['categories_id'], 'next_id' => false);
            if (isset ($prev_id)) {
              $foo[$prev_id]['next_id'] = $row['categories_id'];
            }
            $prev_id = $row['categories_id'];
            if (!isset ($first_id)) {
              $first_id = $row['categories_id'];
            }
            $last_id = $row['categories_id'];
          }
          //BOF - DokuMan - 2010-09-21 - fixed undefined index 2
          //$foo[$last_id]['next_id'] = $foo[$value]['next_id'];
          $foo[$last_id]['next_id'] = isset($foo[$value]['next_id']) ? $foo[$value]['next_id'] : 0;
          //EOF - DokuMan - 2010-09-21 - fixed undefined index 2
          $foo[$value]['next_id'] = $first_id;
          $new_path .= '_';
        } else {
          break;
        }
      }
    }
    //BOF - DokuMan - 2011-01-21 - Fix a notice when there is no category
    //xtc_show_category($first_element);
    if(!empty($first_element)) {
      xtc_show_category($first_element);
    }
    //BOF - DokuMan - 2011-01-21 - Fix a notice when there is no category
    $box_smarty->assign('BOX_CONTENT', $categories_string); //DokuMan - 2010-03-02 - BOX_CONTENT on wrong position
  }

  // set cache ID
  //BOF - DokuMan - 2010-02-28 - fix Smarty cache error on unlink
  /*
  if (!$cache || $rebuild) {
    $box_smarty->assign('BOX_CONTENT', $categories_string);
    if ($rebuild) $box_smarty->clear_cache(CURRENT_TEMPLATE.'/boxes/box_categories.html', $cache_id);
    $box_categories = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_categories.html',$cache_id);
  } else {
    $box_categories = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_categories.html', $cache_id);
  }
  */
  if (!$cache) {
    $box_categories = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_categories.html');
  } else {
    $box_categories = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_categories.html', $cache_id);
  }
  //EOF - DokuMan - 2010-02-28 - fix Smarty cache error on unlink
  $smarty->assign('box_CATEGORIES', $box_categories);
?>