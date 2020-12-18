<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(banner.php,v 1.10 2003/02/11); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_display_banner.inc.php,v 1.3 2003/08/1); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
    
  require_once(DIR_FS_INC . 'xtc_banner_exists.inc.php');
  
  // Display a banner from the specified group or banner id ($identifier)
  function xtc_display_banner($action, $identifier) {
    if ($action == 'dynamic') {
      if (is_array($identifier)) {
        $banner = $identifier;
      } else {
        $banner = xtc_banner_exists($action, $identifier);
      }
    } elseif ($action == 'static') {
      if (is_array($identifier)) {
        $banner = $identifier;
      } else {
        $banner = xtc_banner_exists($action, $identifier);
      }
    } elseif ($action == 'slider') {
      if (is_array($identifier)) {
        $banner_content = $identifier;
      } else {
        $banner_content = xtc_banner_exists($action, $identifier);
      }
      
      if (count($banner_content) > 0) {
        $shop_url = xtc_get_top_level_domain(HTTP_SERVER);
  
        $banner_array = array();
        foreach ($banner_content as $banner) {
          $banner_url = xtc_get_top_level_domain($banner['banners_url']);
          $banner_title = xtc_parse_input_field_data($banner['banners_title'], array('"' => '&quot;'));
          $banner_link = xtc_href_link(FILENAME_REDIRECT, 'action=banner&goto=' . $banner['banners_id']);
          $banner_target = (($shop_url['domain'] != $banner_url['domain']) ? ' target="_blank" rel="noopener"' : '');
          $banner_image = (($banner['banners_image'] != '') ? xtc_image(DIR_WS_IMAGES.'banner/'.$banner['banners_image'], $banner['banners_title'], '', '', 'title="'.$banner['banners_title'].'"') : '');
          
          $banner_array[] = array(
            'IMAGE' => ((xtc_not_null($banner['banners_url'])) ? '<a title="'.$banner_title.'" href="'.$banner_link.'"'.$banner_target.'>'.$banner_image.'</a>' : $banner_image),
            'IMAGE_SRC' => (($banner['banners_image'] != '') ? DIR_WS_BASE.DIR_WS_IMAGES.'banner/'.$banner['banners_image'] : ''),
            'IMAGE_IMG' => $banner_image,
            'LINK' => ((xtc_not_null($banner['banners_url'])) ? $banner_link : ''),
            'TARGET' => $banner_target,
            'TEXT' => $banner['banners_html_text'],
            'TITLE' => $banner_title,
            'GROUP' => $banner['banners_group'],
          );
          xtc_update_banner_display_count($banner['banners_id']);
        }
        
        return $banner_array;
      }
      
      return false;
    }
    
    $shop_url = xtc_get_top_level_domain(HTTP_SERVER);
    $banner_url = xtc_get_top_level_domain($banner['banners_url']);
    $banner_title = xtc_parse_input_field_data($banner['banners_title'], array('"' => '&quot;'));
    $banner_link = xtc_href_link(FILENAME_REDIRECT, 'action=banner&goto=' . $banner['banners_id']);
    $banner_target = (($shop_url['domain'] != $banner_url['domain']) ? ' target="_blank" rel="noopener"' : '');
    $banner_image = (($banner['banners_image'] != '') ? xtc_image(DIR_WS_IMAGES.'banner/'.$banner['banners_image'], $banner['banners_title'], '', '', 'title="'.$banner['banners_title'].'"') : '');
    
    $banner_array = array(
      'IMAGE' => ((xtc_not_null($banner['banners_url'])) ? '<a title="'.$banner_title.'" href="'.$banner_link.'"'.$banner_target.'>'.$banner_image.'</a>' : $banner_image),
      'IMAGE_SRC' => (($banner['banners_image'] != '') ? DIR_WS_BASE.DIR_WS_IMAGES.'banner/'.$banner['banners_image'] : ''),
      'IMAGE_IMG' => $banner_image,
      'LINK' => ((xtc_not_null($banner['banners_url'])) ? $banner_link : ''),
      'TARGET' => $banner_target,
      'TEXT' => $banner['banners_html_text'],
      'TITLE' => $banner_title,
      'GROUP' => $banner['banners_group'],
    );
    
    xtc_update_banner_display_count($banner['banners_id']);
        
    return $banner_array;
  }
?>