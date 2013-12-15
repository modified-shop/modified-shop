<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(best_sellers.php,v 1.20 2003/02/10); www.oscommerce.com
   (c) 2003 nextcommerce (best_sellers.php,v 1.10 2003/08/17); www.nextcommerce.org
   (c) 2006 XT-Commerce

   Third Party contributions:
   Enable_Disable_Categories 1.3 Autor: Mikel Williams | mikel@ladykatcostumes.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

$box_smarty = new smarty;
$box_smarty->assign('language', $_SESSION['language']);
$box_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');

$box_content = '';

	// set cache ID
	if (!CacheCheck()) {
	 	$cache=false;
		$box_smarty->caching = 0;
	} else {
		$cache=true;
		$box_smarty->caching = 1;
		$box_smarty->cache_lifetime = CACHE_LIFETIME;
		$box_smarty->cache_modified_check = CACHE_CHECK;
		$cache_id = $_SESSION['language'].$current_category_id;
	}

if (MIN_DISPLAY_BESTSELLERS > 0 && (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_best_sellers.html', $cache_id) || !$cache)) {
	
	// include needed functions
	require_once (DIR_FS_INC.'xtc_row_number_format.inc.php');
	
  $file = DIR_FS_CATALOG . 'cache/bestseller/' . (int)$current_category_id.'.cache';
  if (is_file($file)) {
      $box_content = unserialize(implode('', file($file)));
  } else {
		if (isset($current_category_id) && $current_category_id > 0) {
			$best_sellers_query = "select distinct
			                       p.products_id,
			                       p.products_price,
			                       p.products_tax_class_id,
			                       p.products_image,
			                       p.products_vpe,
			                       p.products_vpe_status,
			                       p.products_vpe_value,
			                       pd.products_name
			                       from ".TABLE_PRODUCTS." p,
			                       ".TABLE_PRODUCTS_DESCRIPTION." pd,
			                       ".TABLE_PRODUCTS_TO_CATEGORIES." p2c,
			                       ".TABLE_CATEGORIES." c
			                       where p.products_status = 1
			                       and c.categories_status = 1
			                       and p.products_ordered > 0
			                       and p.products_id = pd.products_id
			                       and pd.language_id = '".$_SESSION['languages_id']."'
			                       and p.products_id = p2c.products_id
			                       ".PRODUCTS_CONDITIONS_P."
			                       and p2c.categories_id = c.categories_id 
			                       and (c.categories_id = '" . (int)$current_category_id . "' or c.parent_id = '" . (int)$current_category_id . "')
			                       order by p.products_ordered desc
			                       limit ".MAX_DISPLAY_BESTSELLERS;

		} else {
			$best_sellers_query = "select distinct
			                       p.products_id,
			                       p.products_image,
			                       p.products_price,
			                       p.products_vpe,
			                       p.products_vpe_status,
			                       p.products_vpe_value,
			                       p.products_tax_class_id,
			                       pd.products_name from ".TABLE_PRODUCTS." p,
			                       ".TABLE_PRODUCTS_DESCRIPTION." pd	
			                       where p.products_status = 1
			                       ".PRODUCTS_CONDITIONS_P."
			                       and p.products_ordered > 0
			                       and p.products_id = pd.products_id
			                       and pd.language_id = '".$_SESSION['languages_id']."'
			                       order by p.products_ordered desc
			                       limit ".MAX_DISPLAY_BESTSELLERS;
		}

		$best_sellers_query = xtDBquery($best_sellers_query);
		if (xtc_db_num_rows($best_sellers_query, true) >= MIN_DISPLAY_BESTSELLERS) {
		
			$rows = 0;
			$box_content = array ();
			while ($best_sellers = xtc_db_fetch_array($best_sellers_query, true)) {
				$rows ++;
				$image = '';
				
				$best_sellers = array_merge($best_sellers, array ('ID' => xtc_row_number_format($rows)));
				$box_content[] = $product->buildDataArray($best_sellers);
				
			}
		}
	}
  $box_smarty->assign('box_content', $box_content);

  if (xtc_not_null($box_content)) {
    $box_best_sellers = '';
    if (!$cache) {
      $box_best_sellers = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_best_sellers.html');
    } else {
      $box_best_sellers = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_best_sellers.html', $cache_id);
    }

    $smarty->assign('box_BESTSELLERS', $box_best_sellers);
  }
}

?>