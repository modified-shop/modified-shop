<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


  require_once(DIR_FS_INC . 'xtc_banner_exists.inc.php');
  require_once(DIR_FS_INC . 'xtc_display_banner.inc.php');
  require_once(DIR_FS_INC . 'xtc_update_banner_display_count.inc.php');
  
  if (MODULE_BANNER_MANAGER_STATUS == 'true'
      && basename($PHP_SELF) == FILENAME_DEFAULT 
      && !isset($_GET['cPath']) 
      && !isset($_GET['manufacturers_id'])
      )
  {
    $banner_smarty = new Smarty;
    $banner_smarty->caching = false;
    $banner_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
    $banner_smarty->assign('language', $_SESSION['language']);

    // auto activate and expire banners
    xtc_activate_banners();
    xtc_expire_banners();

    $banners_group_condition = ((isset($banners_group_condition)) ? $banners_group_condition : '');
    
    $groups_query = xtc_db_query("SELECT DISTINCT banners_group 
                                             FROM " . TABLE_BANNERS . " 
                                            WHERE banners_group != 'slider'
                                                  ".$banners_group_condition."
                                         ORDER BY banners_group");
    while ($groups = xtc_db_fetch_array($groups_query)) {
      if ($banner = xtc_banner_exists('dynamic', $groups['banners_group'])) {
        $banner_array = xtc_display_banner('static', $banner);

        if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/banners.html')) {
          $banner_smarty->assign('banner_data', $banner_array);
          $banner_smarty->caching = 0;
          $banners = $banner_smarty->fetch(CURRENT_TEMPLATE.'/module/banners.html');
        } else {
          if (xtc_not_null($banner_array['TEXT'])) {
            $banners = $banner_array['TEXT'];
          } elseif (xtc_not_null($banner_array['LINK'])) {      
            $banners = '<a title="'.$banner_array['TITLE'].'" href="'.$banner_array['LINK'].'"'.$banner_array['TARGET'].'>'.$banner_array['IMAGE_IMG'].'</a>';
          } else {
            $banners = $banner_array['IMAGE_IMG'];
          }
        }
        $smarty->assign(strtoupper($groups['banners_group']), $banners);
      }
    }

    if ($banner = xtc_banner_exists('slider', 'slider')) {
      $smarty->assign('SLIDER', xtc_display_banner('slider', $banner));
    }
  }
?>