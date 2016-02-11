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

// findologic
if (defined('MODULE_FINDOLOGIC_STATUS') && MODULE_FINDOLOGIC_STATUS == 'True' && !isset($_GET['f'])) {
  xtc_redirect(xtc_href_link(FILENAME_FINDOLOGIC, xtc_get_all_get_params()));
}
unset($_GET['f']);

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
$pfrom = $_GET['pfrom'] = isset($_GET['pfrom']) && !empty($_GET['pfrom']) ? str_replace(',', '.', stripslashes(trim(urldecode($_GET['pfrom'])))) : false;
$pto = $_GET['pto'] = isset($_GET['pto']) && !empty($_GET['pto']) ? str_replace(',', '.', stripslashes(trim(urldecode($_GET['pto'])))) : false;
$manufacturers_id  = $_GET['manufacturers_id'] = isset($_GET['manufacturers_id']) && xtc_not_null($_GET['manufacturers_id']) ? (int)$_GET['manufacturers_id'] : false;
$categories_id = $_GET['categories_id'] = isset($_GET['categories_id']) && xtc_not_null($_GET['categories_id']) ? (int)$_GET['categories_id'] : false;
$_GET['inc_subcat'] = isset($_GET['inc_subcat']) && xtc_not_null($_GET['inc_subcat']) ? (int)$_GET['inc_subcat'] : null;

// reset error
$errorno = 0;

// create $search_keywords array
$keywordcheck = xtc_parse_search_string($keywords, $search_keywords);

// error check
if ($keywords && strlen($keywords) < 3 && strlen($keywords) > 0) {
  $errorno += 1;
}
if (!$keywords && !$pfrom && !$pto) {
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
if ($keywords && !$keywordcheck) {
  $errorno += 10000000;
}

if ($errorno) {
  xtc_redirect(xtc_href_link(FILENAME_ADVANCED_SEARCH, xtc_get_all_get_params().'errorno='.$errorno));

} else {

  // build breadcrumb
  $breadcrumb->add(NAVBAR_TITLE1_ADVANCED_SEARCH, xtc_href_link(FILENAME_ADVANCED_SEARCH));
  $breadcrumb->add(NAVBAR_TITLE2_ADVANCED_SEARCH, xtc_href_link(FILENAME_ADVANCED_SEARCH_RESULT, xtc_get_all_get_params(array('filter', 'show', 'filter_id', 'cat'))));

  // default values
  $subcat_join  = '';
  $subcat_where = '';
  $tax_where    = '';
  $cats_list    = '';
  $left_join    = '';

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
  $select_str = "SELECT distinct ".ADD_SELECT_SEARCH."
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
                                 pd.products_description,
                                 IFNULL(s.specials_new_products_price, p.products_price) AS price ";
  
  if (PRODUCT_LIST_FILTER == 'true') { 
    $select_str = "SELECT DISTINCT p.products_id ";
  }
  
  $from_str  = "FROM ".TABLE_PRODUCTS." AS p 
           LEFT JOIN ".TABLE_PRODUCTS_DESCRIPTION." AS pd ON (p.products_id = pd.products_id AND trim(pd.products_name) != '' AND pd.language_id = '".(int)$_SESSION['languages_id']."') ";
  $from_str .= $subcat_join;
  $from_str .= SEARCH_IN_ATTR == 'true' ? " LEFT OUTER JOIN ".TABLE_PRODUCTS_ATTRIBUTES." AS pa ON (p.products_id = pa.products_id) 
                                            LEFT OUTER JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES." AS pov ON (pa.options_values_id = pov.products_options_values_id) " : "";
  $from_str .= "LEFT OUTER JOIN ".TABLE_SPECIALS." AS s ON (p.products_id = s.products_id) AND s.status = '1'";
  $from_str .= SEARCH_IN_MANU == 'true' ? " LEFT OUTER JOIN ".TABLE_MANUFACTURERS." AS m ON (p.manufacturers_id = m.manufacturers_id) " : "";
  
  if (SEARCH_IN_FILTER == 'true') {
    $from_str .= "LEFT JOIN ".TABLE_PRODUCTS_TAGS." pt ON (pt.products_id = p.products_id)
                  LEFT JOIN ".TABLE_PRODUCTS_TAGS_VALUES." ptv ON (ptv.options_id = pt.options_id AND ptv.values_id = pt.values_id AND ptv.status = '1' AND ptv.languages_id = '".(int)$_SESSION['languages_id']."') ";
  }
  
  if($NeedTax) {
    if (!isset ($_SESSION['customer_country_id'])) {
      $_SESSION['customer_country_id'] = STORE_COUNTRY;
      $_SESSION['customer_zone_id'] = STORE_ZONE;
    }
    $from_str .= " LEFT OUTER JOIN ".TABLE_TAX_RATES." tr ON (p.products_tax_class_id = tr.tax_class_id) 
                   LEFT OUTER JOIN ".TABLE_ZONES_TO_GEO_ZONES." gz ON (tr.tax_zone_id = gz.geo_zone_id) ";
    $tax_where = " AND (gz.zone_country_id IS NULL OR gz.zone_country_id = '0' OR gz.zone_country_id = '".(int) $_SESSION['customer_country_id']."') 
                   AND (gz.zone_id is null OR gz.zone_id = '0' OR gz.zone_id = '".(int) $_SESSION['customer_zone_id']."')";
  }

  //where-string
  $where_str = " WHERE p.products_status = '1' AND trim(pd.products_name) != '' "  
  .$subcat_where
  .$manu_check
  .PRODUCTS_CONDITIONS_P
  .$tax_where
  .$pfrom_check
  .$pto_check;

  //go for keywords... this is the main search process
  if ($keywords && $keywordcheck) {
    
    include(DIR_WS_INCLUDES.'build_search_query.php');
    
    if (PRODUCT_LIST_FILTER == 'true') { 
      $where_str .= " ) GROUP BY p.products_id";
    } else {
      $where_str .= " ) GROUP BY p.products_id ORDER BY p.products_id ASC";
    }
  }

  // glue together
  $listing_sql = $select_str.$from_str.$where_str;
  
  if (PRODUCT_LIST_FILTER == 'true') { 
    $products_search_array = array();
    $result_query = xtDBquery($listing_sql);
    while ($result = xtc_db_fetch_array($result_query, true)) {
      $products_search_array[] = $result['products_id'];
    }
    
    $join = '';                 
    if (isset($_GET['filter_id']) && xtc_not_null($_GET['filter_id'])) {
      $join = " JOIN ".TABLE_MANUFACTURERS." m 
                     ON p.manufacturers_id = m.manufacturers_id
                        AND m.manufacturers_id = '".(int)$_GET['filter_id']."' ";
    }

    $filter_join = '';
    if (isset($_GET['filter']) && is_array($_GET['filter'])) {
      $fi = 1;
      foreach ($_GET['filter'] as $options_id => $values_id) {
        if ($values_id != '') {
          $filter_join .= " JOIN ".TABLE_PRODUCTS_TAGS." pt".$fi." 
                                 ON pt".$fi.".products_id = p.products_id
                                    AND pt".$fi.".options_id = '".(int)$options_id."'
                                    AND pt".$fi.".values_id = '".(int)$values_id."' ";
          $fi ++;
        }
      }
    }
  
    $listing_sql = "SELECT ".ADD_SELECT_SEARCH."
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
                           pd.products_description,
                           IFNULL(s.specials_new_products_price, p.products_price) AS price
                      FROM ".TABLE_PRODUCTS." p
                      JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                           ON p.products_id = pd.products_id
                              AND pd.language_id = '".(int)$_SESSION['languages_id']."'
                              AND trim(pd.products_name) != ''
                LEFT JOIN ".TABLE_SPECIALS." s 
                          ON p.products_id = s.products_id
                             AND s.status = '1'
                          ".$join."
                          ".$filter_join."
                    WHERE p.products_id IN ('".implode("', '", $products_search_array)."')
                          ".((isset($_SESSION['filter_sorting'])) ? $_SESSION['filter_sorting'] : 'ORDER BY p.products_id ASC');
  }
  
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