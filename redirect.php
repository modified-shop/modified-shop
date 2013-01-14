<?php


/* -----------------------------------------------------------------------------------------
   $Id: redirect.php 1060 2005-07-21 18:32:58Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(redirect.php,v 1.9 2003/02/13); www.oscommerce.com 
   (c) 2003	 nextcommerce (redirect.php,v 1.7 2003/08/17); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

require_once (DIR_FS_INC.'xtc_update_banner_click_count.inc.php');

switch ($_GET['action']) {
	case 'banner' :
		$banner_query = xtc_db_query("select banners_url from ".TABLE_BANNERS." where banners_id = '".(int) $_GET['goto']."'");
		if (xtc_db_num_rows($banner_query)) {
			$banner = xtc_db_fetch_array($banner_query);
			xtc_update_banner_click_count($_GET['goto']);

			xtc_redirect($banner['banners_url']);
		} else {
			xtc_redirect(xtc_href_link(FILENAME_DEFAULT));
		}
		break;

	case 'product' :
		if (isset ($_GET['id'])) {
			$product_query = xtc_db_query("SELECT products_url FROM ".TABLE_PRODUCTS_DESCRIPTION." WHERE products_id='".(int) $_GET['id']."' and language_id='".(int) $_SESSION['languages_id']."'");

			if (xtc_db_num_rows($product_query)) {
				$product = xtc_db_fetch_array($product_query);

				xtc_redirect('http://'.$product['products_url']);
			} else {
				xtc_redirect(xtc_href_link(FILENAME_DEFAULT));
			}
		} else {
			xtc_redirect(xtc_href_link(FILENAME_DEFAULT));
		}
		break;

	case 'manufacturer' :
		if (isset ($_GET['manufacturers_id'])) {
			$manufacturer_query = xtc_db_query("select manufacturers_url from ".TABLE_MANUFACTURERS_INFO." where manufacturers_id = '".(int) $_GET['manufacturers_id']."' and languages_id = '".(int) $_SESSION['languages_id']."'");
			if (!xtc_db_num_rows($manufacturer_query)) {
				// no url exists for the selected language, lets use the default language then
				$manufacturer_query = xtc_db_query("select mi.languages_id, mi.manufacturers_url from ".TABLE_MANUFACTURERS_INFO." mi, ".TABLE_LANGUAGES." l where mi.manufacturers_id = '".(int) $_GET['manufacturers_id']."' and mi.languages_id = l.languages_id and l.code = '".DEFAULT_LANGUAGE."'");
				if (!xtc_db_num_rows($manufacturer_query)) {
					// no url exists, return to the site
					xtc_redirect(xtc_href_link(FILENAME_DEFAULT));
				} else {
					$manufacturer = xtc_db_fetch_array($manufacturer_query);
					xtc_db_query("update ".TABLE_MANUFACTURERS_INFO." set url_clicked = url_clicked+1, date_last_click = now() where manufacturers_id = '".(int) $_GET['manufacturers_id']."' and languages_id = '".$manufacturer['languages_id']."'");
				}
			} else {
				// url exists in selected language
				$manufacturer = xtc_db_fetch_array($manufacturer_query);
				xtc_db_query("update ".TABLE_MANUFACTURERS_INFO." set url_clicked = url_clicked+1, date_last_click = now() where manufacturers_id = '".(int) $_GET['manufacturers_id']."' and languages_id = '".$_SESSION['languages_id']."'");
			}

			xtc_redirect($manufacturer['manufacturers_url']);
		} else {
			xtc_redirect(xtc_href_link(FILENAME_DEFAULT));
		}
		break;

	default :
		xtc_redirect(xtc_href_link(FILENAME_DEFAULT));
		break;
}
?>