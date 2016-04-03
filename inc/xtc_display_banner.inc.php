<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_display_banner.inc.php 899 2005-04-29 02:40:57Z hhgag $   

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
    
  // Display a banner from the specified group or banner id ($identifier)
  function xtc_display_banner($action, $identifier) {
    if ($action == 'dynamic') {
      $banners_query = xtc_db_query("SELECT count(*) as count 
                                       FROM " . TABLE_BANNERS . " 
                                      WHERE status = '1' 
                                        AND languages_id = '" . (int)$_SESSION['languages_id'] . "'
                                        AND banners_group = '" . xtc_db_input($identifier) . "'");
      $banners = xtc_db_fetch_array($banners_query);
      if ($banners['count'] > 0) {
        $banner = xtc_random_select("SELECT *
                                       FROM " . TABLE_BANNERS . " 
                                      WHERE status = '1' 
                                        AND languages_id = '" . (int)$_SESSION['languages_id'] . "'
                                        AND banners_group = '" . xtc_db_input($identifier) . "'");
      }
    } elseif ($action == 'static') {
      if (is_array($identifier)) {
        $banner = $identifier;
      } else {
        $banner_query = xtc_db_query("SELECT *
                                        FROM " . TABLE_BANNERS . " 
                                       WHERE status = '1' 
                                         AND languages_id = '" . (int)$_SESSION['languages_id'] . "'
                                         AND banners_id = '" . (int)$identifier . "'");
        if (xtc_db_num_rows($banner_query)) {
          $banner = xtc_db_fetch_array($banner_query);
        }
      }
    } elseif ($action == 'slider') {
      if (is_array($identifier)) {
        $banner_content = $identifier;
      } else {
        $banner_query = xtc_db_query("SELECT *
                                        FROM " . TABLE_BANNERS . " 
                                       WHERE status = '1' 
                                         AND banners_image != ''
                                         AND languages_id = '" . (int)$_SESSION['languages_id'] . "'
                                         AND banners_group = '" . xtc_db_input($identifier) . "'");
        if (xtc_db_num_rows($banner_query) > 0) {
          $banner_content = array();
          while ($banner = xtc_db_fetch_array($banner_query)) {
            $banner_content[] = $banner;
          }
        }
      }
      
      if (count($banner_content) > 0) {
        $shop_url = xtc_get_top_level_domain(HTTP_SERVER);
  
        $banner_array = array();
        foreach ($banner_content as $banner) {
          $banner_url = xtc_get_top_level_domain($banner['banners_url']);
          $banner_array[] = array('IMAGE' => ((xtc_not_null($banner['banners_url'])) ? '<a href="' . xtc_href_link(FILENAME_REDIRECT, 'action=banner&goto=' . $banner['banners_id']) . '"' . (($shop_url['new'] != $banner_url['new']) ? ' onclick="window.open(this.href); return false;"' : '') . '>' . xtc_image(DIR_WS_IMAGES.'banner/' . $banner['banners_image'], $banner['banners_title']) . '</a>' : xtc_image(DIR_WS_IMAGES.'banner/' . $banner['banners_image'], $banner['banners_title'])),
                                  'TEXT' => $banner['banners_html_text'],
                                  'TITLE' => $banner['banners_title']
                                  );
          xtc_update_banner_display_count($banner['banners_id']);
        }
        
        return $banner_array;
      }
    }

    
    if (xtc_not_null($banner['banners_html_text'])) {
      $banner_string = $banner['banners_html_text'];
    } elseif (xtc_not_null($banner['banners_url'])) {
      $banner_url = xtc_get_top_level_domain($banner['banners_url']);
      $shop_url = xtc_get_top_level_domain(HTTP_SERVER);
      $banner_string = '<a href="' . xtc_href_link(FILENAME_REDIRECT, 'action=banner&goto=' . $banner['banners_id']) . '"' . (($shop_url['new'] != $banner_url['new']) ? ' onclick="window.open(this.href); return false;"' : '') . '>' . xtc_image(DIR_WS_IMAGES.'banner/' . $banner['banners_image'], $banner['banners_title']) . '</a>';
    } else {
      $banner_string = xtc_image(DIR_WS_IMAGES.'banner/' . $banner['banners_image'], $banner['banners_title']);
    }

    xtc_update_banner_display_count($banner['banners_id']);

    return $banner_string;
  }
?>