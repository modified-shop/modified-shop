<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(redirect.php,v 1.9 2003/02/13); www.oscommerce.com 
   (c) 2003	 nextcommerce (redirect.php,v 1.7 2003/08/17); www.nextcommerce.org
   (c) 2003 XT-Commerce
   
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

require_once (DIR_FS_INC.'xtc_update_banner_click_count.inc.php');

switch ($_GET['action']) {
	case 'banner' :
		$banner_query = xtc_db_query("SELECT banners_url 
		                                FROM ".TABLE_BANNERS." 
		                               WHERE banners_id = '".(int) $_GET['goto']."'");
		if (xtc_db_num_rows($banner_query)) {
			$banner = xtc_db_fetch_array($banner_query);
			xtc_update_banner_click_count($_GET['goto']);
      
      // remove session id
      if (strrpos($banner['banners_url'], session_name()) !== false) {
        $banner['banners_url'] = substr($banner['banners_url'], 0, strrpos($banner['banners_url'], session_name()));
      }
      $banner['banners_url'] = rtrim($banner['banners_url'], '&?');
            
      // Add the session ID when SID is defined
      $banner_url = xtc_get_top_level_domain($banner['banners_url']);
      $shop_url = xtc_get_top_level_domain(HTTP_SERVER);
      
      if ((!isset($truncate_session_id) || $truncate_session_id === false)
          && (SESSION_FORCE_COOKIE_USE == 'False' && !$cookie)
          && $shop_url['new'] == $banner_url['new']
         )
      {
        $separator = ((strpos($banner['banners_url'], '?') === false) ? '?' : '&');
        if (defined('SID')
            && constant('SID') != '')
        {
          $banner['banners_url'] .= $separator . session_name() . '=' . session_id();
        } elseif ($http_domain != $https_domain) {
          $banner['banners_url'] .= $separator . session_name() . '=' . session_id();
        }
      }

			xtc_redirect('http://'.str_replace(array('http://', 'https://'), '', $banner['banners_url']));
		} else {
			xtc_redirect(xtc_href_link(FILENAME_DEFAULT));
		}
		break;

	case 'product' :
		if (isset ($_GET['id'])) {
			$product_query = xtc_db_query("SELECT products_url 
                                       FROM ".TABLE_PRODUCTS_DESCRIPTION." 
                                      WHERE products_id='".(int) $_GET['id']."'
                                        AND trim(products_name) != ''           
                                        AND language_id='".(int) $_SESSION['languages_id']."'");
			if (xtc_db_num_rows($product_query)) {
				$product = xtc_db_fetch_array($product_query);

				xtc_redirect('http://'.str_replace(array('http://', 'https://'), '', $product['products_url']));
			} else {
				xtc_redirect(xtc_href_link(FILENAME_DEFAULT));
			}
		} else {
			xtc_redirect(xtc_href_link(FILENAME_DEFAULT));
		}
		break;

	case 'manufacturer' :
		if (isset ($_GET['manufacturers_id'])) {
			$manufacturer_query = xtc_db_query("SELECT manufacturers_url 
			                                      FROM ".TABLE_MANUFACTURERS_INFO." 
			                                     WHERE manufacturers_id = '".(int) $_GET['manufacturers_id']."' 
			                                       AND languages_id = '".(int) $_SESSION['languages_id']."'");
			if (!xtc_db_num_rows($manufacturer_query)) {
				// no url exists for the selected language, lets use the default language then
				$manufacturer_query = xtc_db_query("SELECT mi.languages_id, 
				                                           mi.manufacturers_url 
				                                      FROM ".TABLE_MANUFACTURERS_INFO." mi
				                                      JOIN ".TABLE_LANGUAGES." l 
				                                           ON mi.languages_id = l.languages_id
				                                              AND l.code = '".DEFAULT_LANGUAGE."'
				                                     WHERE mi.manufacturers_id = '".(int) $_GET['manufacturers_id']."'");
				if (!xtc_db_num_rows($manufacturer_query)) {
					// no url exists, return to the site
					xtc_redirect(xtc_href_link(FILENAME_DEFAULT));
				} else {
					$manufacturer = xtc_db_fetch_array($manufacturer_query);
					xtc_db_query("UPDATE ".TABLE_MANUFACTURERS_INFO." 
					                 SET url_clicked = url_clicked+1, 
					                     date_last_click = now() 
					               WHERE manufacturers_id = '".(int) $_GET['manufacturers_id']."' 
					                 AND languages_id = '".$manufacturer['languages_id']."'");
				}
			} else {
				// url exists in selected language
				$manufacturer = xtc_db_fetch_array($manufacturer_query);
				xtc_db_query("UPDATE ".TABLE_MANUFACTURERS_INFO." 
				                 SET url_clicked = url_clicked+1, 
				                     date_last_click = now() 
				               WHERE manufacturers_id = '".(int) $_GET['manufacturers_id']."' 
				                 AND languages_id = '".(int)$_SESSION['languages_id']."'");
			}

			xtc_redirect('http://'.str_replace(array('http://', 'https://'), '', $manufacturer['manufacturers_url']));
		} else {
			xtc_redirect(xtc_href_link(FILENAME_DEFAULT));
		}
		break;

	default :
		xtc_redirect(xtc_href_link(FILENAME_DEFAULT));
		break;
}
?>