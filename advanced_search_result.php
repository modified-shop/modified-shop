<?php
/* -----------------------------------------------------------------------------------------
   $Id: advanced_search_result.php 3413 2012-08-10 15:53:56Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(advanced_search_result.php,v 1.68 2003/05/14); www.oscommerce.com
   (c) 2003 nextcommerce (advanced_search_result.php,v 1.17 2003/08/21); www.nextcommerce.org
   (c) 2006 XT-Commerce (advanced_search_result.php 1141 2005-08-10)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

// create smarty elements
$smarty = new Smarty;

// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

// include needed functions
require_once (DIR_FS_INC.'xtc_parse_search_string.inc.php');
require_once (DIR_FS_INC.'xtc_get_subcategories.inc.php');
require_once (DIR_FS_INC.'xtc_get_currencies_values.inc.php');

// security fix
//set $_GET variables for function xtc_get_all_get_params()
$keywords = $_GET['keywords'] = isset($_GET['keywords']) && !empty($_GET['keywords']) ? stripslashes(trim(urldecode($_GET['keywords']))) : false;
$pfrom = $_GET['pfrom'] = isset($_GET['pfrom']) && !empty($_GET['pfrom']) ? stripslashes($_GET['pfrom']) : false;
$pto = $_GET['pto'] = isset($_GET['pto']) && !empty($_GET['pto']) ? stripslashes($_GET['pto']) : false;
$manufacturers_id  = $_GET['manufacturers_id'] = isset($_GET['manufacturers_id']) && xtc_not_null($_GET['manufacturers_id']) ? (int)$_GET['manufacturers_id'] : false;
$categories_id = $_GET['categories_id'] = isset($_GET['categories_id']) && xtc_not_null($_GET['categories_id']) ? (int)$_GET['categories_id'] : false;
$_GET['inc_subcat'] = isset($_GET['inc_subcat']) && xtc_not_null($_GET['inc_subcat']) ? (int)$_GET['inc_subcat'] : null;
// reset error
$errorno = 0;

// error check
if ($keywords && strlen($keywords) < 3 && strlen($keywords) > 0) {
  $errorno += 1;
}
if (!$keywords && !$pfrom && !$pto && isset($_GET['x'])) {
  $errorno += 1;
}
if ($pfrom && !settype($pfrom, "float")) {
  $errorno += 10000;
}
if ($pto && !settype($pto, "float")) {
  $errorno += 100000;
}
if ($pfrom && !(($errorno & 10000) == 10000) && $pto && !(($errorno & 100000) == 100000) && $pfrom > $pto) {
  $errorno += 1000000;
}
if ($keywords && !xtc_parse_search_string($keywords, $search_keywords)) {
  $errorno += 10000000;
}

if ($errorno) {
  xtc_redirect(xtc_href_link(FILENAME_ADVANCED_SEARCH, 'errorno='.$errorno.'&'.xtc_get_all_get_params()));

} else {

  // build breadcrumb
  $breadcrumb->add(NAVBAR_TITLE1_ADVANCED_SEARCH, xtc_href_link(FILENAME_ADVANCED_SEARCH));
  $breadcrumb->add(NAVBAR_TITLE2_ADVANCED_SEARCH, xtc_href_link(FILENAME_ADVANCED_SEARCH_RESULT, xtc_get_all_get_params()));

  // default values
  $subcat_join  = '';
  $subcat_where = '';
  $tax_where    = '';
  $cats_list    = '';
  $left_join    = '';

  // fsk18 lock
  $fsk_lock = $_SESSION['customers_status']['customers_fsk18_display'] == '0' ? " AND p.products_fsk18 != '1' " : "";

  // group check
  $group_check = GROUP_CHECK == 'true' ? " AND p.group_permission_".$_SESSION['customers_status']['customers_status_id']."=1 " : "";

  // manufacturers check
  $manu_check = $manufacturers_id !== false ? " AND p.manufacturers_id = '".$manufacturers_id."' " : "";

  //include subcategories if needed
  if ($categories_id !== false) {
    if (isset($_GET['inc_subcat']) && $_GET['inc_subcat'] == '1') {
      $subcategories_array = array();
      xtc_get_subcategories($subcategories_array, $categories_id);
      $subcat_join = " LEFT OUTER JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." AS p2c ON (p.products_id = p2c.products_id) ";
      $subcat_where = " AND p2c.categories_id IN ('".$categories_id."' ";
      foreach ($subcategories_array AS $scat) {
        $subcat_where .= ", '".$scat."'";
      }
      $subcat_where .= ") ";
    } else {
      $subcat_join = " LEFT OUTER JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." AS p2c ON (p.products_id = p2c.products_id) ";
      $subcat_where = " AND p2c.categories_id = '".$categories_id."' ";
    }
  }

  // price by currency
  $NeedTax = false;
  if ($pfrom || $pto) {
    $rate = xtc_get_currencies_values($_SESSION['currency']);
    $rate = $rate['value'];
    if ($rate && $pfrom) {
      $pfrom = $pfrom / $rate;
    }
    if ($rate && $pto) {
      $pto = $pto / $rate;
    }
    if($_SESSION['customers_status']['customers_status_show_price_tax']) {
      $NeedTax = true;
    }
  }
  
  //price filters
  if (($pfrom != '') && (is_numeric($pfrom))) {
    if($NeedTax)
      $pfrom_check = " AND (IF(s.status = '1' AND p.products_id = s.products_id, s.specials_new_products_price, p.products_price) >= round((".$pfrom."/(1+tax_rate/100)),".PRICE_PRECISION.") ) ";
    else
      $pfrom_check = " AND (IF(s.status = '1' AND p.products_id = s.products_id, s.specials_new_products_price, p.products_price) >= round(".$pfrom.",".PRICE_PRECISION.") ) ";
  } else {
    $pfrom_check = '';
  }

  if (($pto != '') && (is_numeric($pto))) {
    if($NeedTax)
      $pto_check = " AND (IF(s.status = '1' AND p.products_id = s.products_id, s.specials_new_products_price, p.products_price) <= round((".$pto."/(1+tax_rate/100)),".PRICE_PRECISION.") ) ";
    else
      $pto_check = " AND (IF(s.status = '1' AND p.products_id = s.products_id, s.specials_new_products_price, p.products_price) <= round(".$pto.",".PRICE_PRECISION.") ) ";
  } else {
    $pto_check = '';
  }

  //build query
  $add_select = 'p.products_manufacturers_model,';
  $select_str = "SELECT distinct
                    $add_select
                    p.products_id,
                    p.products_ean,
                    p.products_quantity,
                    p.products_shippingtime,
                    p.products_model,
                    p.products_image,
                    p.products_price,
                    p.products_weight,
                    p.products_tax_class_id,
                    p.products_fsk18,
                    p.products_vpe,
                    p.products_vpe_status,
                    p.products_vpe_value,
                    pd.products_name,
                    pd.products_short_description,
                    pd.products_description ";

  $from_str  = "FROM ".TABLE_PRODUCTS." AS p LEFT JOIN ".TABLE_PRODUCTS_DESCRIPTION." AS pd ON (p.products_id = pd.products_id) ";
  $from_str .= $subcat_join;
  $from_str .= SEARCH_IN_ATTR == 'true' ? " LEFT OUTER JOIN ".TABLE_PRODUCTS_ATTRIBUTES." AS pa ON (p.products_id = pa.products_id) LEFT OUTER JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES." AS pov ON (pa.options_values_id = pov.products_options_values_id) " : "";
  $from_str .= "LEFT OUTER JOIN ".TABLE_SPECIALS." AS s ON (p.products_id = s.products_id) AND s.status = '1'";

  if($NeedTax) {
    if (!isset ($_SESSION['customer_country_id'])) {
      $_SESSION['customer_country_id'] = STORE_COUNTRY;
      $_SESSION['customer_zone_id'] = STORE_ZONE;
    }
    $from_str .= " LEFT OUTER JOIN ".TABLE_TAX_RATES." tr ON (p.products_tax_class_id = tr.tax_class_id) LEFT OUTER JOIN ".TABLE_ZONES_TO_GEO_ZONES." gz ON (tr.tax_zone_id = gz.geo_zone_id) ";
    $tax_where = " AND (gz.zone_country_id IS NULL OR gz.zone_country_id = '0' OR gz.zone_country_id = '".(int) $_SESSION['customer_country_id']."') AND (gz.zone_id is null OR gz.zone_id = '0' OR gz.zone_id = '".(int) $_SESSION['customer_zone_id']."')";
  }

  //where-string
  $where_str = "
  WHERE p.products_status = 1
  AND pd.language_id = '".$_SESSION['languages_id']."'"  
  .$subcat_where
  .$fsk_lock
  .$manu_check
  .$group_check
  .$tax_where
  .$pfrom_check
  .$pto_check;

  //go for keywords... this is the main search process
  if ($keywords) {
    if (xtc_parse_search_string($keywords, $search_keywords)) {
      $where_str .= " AND ( ";
      for ($i = 0, $n = sizeof($search_keywords); $i < $n; $i ++) {
        switch ($search_keywords[$i]) {
          case '(' :
          case ')' :
          case 'and' :
          case 'or' :
            $where_str .= " ".$search_keywords[$i]." ";
            break;
          default :
          $ent_keyword = encode_htmlentities($search_keywords[$i]); // umlauts
          $ent_keyword = $ent_keyword != $search_keywords[$i] ? addslashes($ent_keyword) : false;
          $keyword = addslashes($search_keywords[$i]);
          $where_str .= " ( ";
          $where_str .= "pd.products_keywords LIKE ('%".$keyword."%') ";
          $where_str .= $ent_keyword ? "OR pd.products_keywords LIKE ('%".$ent_keyword."%') " : '';
          if (SEARCH_IN_DESC == 'true') {
             $where_str .= "OR pd.products_description LIKE ('%".$keyword."%') ";
             $where_str .= $ent_keyword ? "OR pd.products_description LIKE ('%".$ent_keyword."%') " : '';
             $where_str .= "OR pd.products_short_description LIKE ('%".$keyword."%') ";
             $where_str .= $ent_keyword ? "OR pd.products_short_description LIKE ('%".$ent_keyword."%') " : '';
          }
          $where_str .= "OR pd.products_name LIKE ('%".$keyword."%') ";
          $where_str .= $ent_keyword ? "OR pd.products_name LIKE ('%".$ent_keyword."%') " : '';
          $where_str .= "OR p.products_model LIKE ('%".$keyword."%') ";
          $where_str .= $ent_keyword ? "OR p.products_model LIKE ('%".$ent_keyword."%') " : '';
          $where_str .= "OR p.products_ean LIKE ('%".$keyword."%') ";
          $where_str .= $ent_keyword ? "OR p.products_ean LIKE ('%".$ent_keyword."%') " : '';
          $where_str .= "OR p.products_manufacturers_model LIKE ('%".$keyword."%') ";
          $where_str .= $ent_keyword ? "OR p.products_manufacturers_model LIKE ('%".$ent_keyword."%') " : '';
          if (SEARCH_IN_ATTR == 'true') {
            $where_str .= "OR pa.attributes_model LIKE ('%".$keyword."%') ";
            $where_str .= ($ent_keyword) ? "OR pa.attributes_model LIKE ('%".$ent_keyword."%') " : '';
            $where_str .= "OR pa.attributes_ean LIKE ('%".$keyword."%') ";
            $where_str .= ($ent_keyword) ? "OR pa.attributes_ean LIKE ('%".$ent_keyword."%') " : '';
            $where_str .= "OR (pov.products_options_values_name LIKE ('%".$keyword."%') ";
            $where_str .= ($ent_keyword) ? "OR pov.products_options_values_name LIKE ('%".$ent_keyword."%') " : '';
            $where_str .= "AND pov.language_id = '".(int) $_SESSION['languages_id']."')";
          }
          $where_str .= " ) ";
          break;
        }
      }
      $where_str .= " ) GROUP BY p.products_id ORDER BY p.products_id ";
    }
  }

  // glue together
  $listing_sql = $select_str.$from_str.$where_str;

  $_GET['keywords'] = urlencode($keywords);
  require (DIR_WS_MODULES.FILENAME_PRODUCT_LISTING);
  require (DIR_WS_INCLUDES.'header.php');
}

$smarty->assign('language', $_SESSION['language']);
if (!defined('RM')) {
  $smarty->load_filter('output', 'note');
}
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>