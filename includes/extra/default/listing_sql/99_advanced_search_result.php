<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


  if (basename($PHP_SELF) == FILENAME_ADVANCED_SEARCH_RESULT) {    
    //set $_GET variables for function xtc_get_all_get_params()
    $keywords = $_GET['keywords'] = !empty($_GET['keywords']) ? stripslashes(trim($_GET['keywords'])) : false;
    $pfrom = $_GET['pfrom'] = !empty($_GET['pfrom']) ? str_replace(',', '.', stripslashes(trim($_GET['pfrom']))) : false;
    $pto = $_GET['pto'] = !empty($_GET['pto']) ? str_replace(',', '.', stripslashes(trim($_GET['pto']))) : false;
    $manufacturers_id  = $_GET['filter_id'] = !empty($_GET['filter_id']) ? (int)$_GET['filter_id'] : false;
    $categories_id = $_GET['categories_id'] = !empty($_GET['categories_id']) ? (int)$_GET['categories_id'] : false;
    $_GET['inc_subcat'] = !empty($_GET['inc_subcat']) ? (int)$_GET['inc_subcat'] : null;

    // manufacturers
    $manu_check = $manufacturers_id !== false ? " AND p.manufacturers_id = '".$manufacturers_id."' " : "";
    
    // filter
    $filter_where = '';
    $tags_array = get_filter_tags();
    if (count($tags_array) > 0) {
      $filter_where .= "AND p.products_id IN (".implode(', ', $tags_array).")";
    }

    //include subcategories if needed
    $p2c_condition = '';
    if ($categories_id !== false) {
      if (isset($_GET['inc_subcat']) && $_GET['inc_subcat'] == '1') {
        $subcategories_array = array ();
        xtc_get_subcategories($subcategories_array, $categories_id);
        $subcategories_array[] = $categories_id;
        $p2c_condition = " AND p2c.categories_id IN ('".implode("', '", $subcategories_array)."') ";
      } else {
        $p2c_condition = " AND p2c.categories_id = '".$categories_id."' ";
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
      if ($_SESSION['customers_status']['customers_status_show_price_tax']) {
        $NeedTax = true;
      }
    }
  
    //price filters
    $pfrom_check = '';
    if (($pfrom != '') && (is_numeric($pfrom))) {
      if ($NeedTax) {
        $pfrom_check = " AND (IF(s.status = '1' AND p.products_id = s.products_id, s.specials_new_products_price, p.products_price) >= round((".$pfrom."/(1+tax_rate/100)),".PRICE_PRECISION.") ) ";
      } else {
        $pfrom_check = " AND (IF(s.status = '1' AND p.products_id = s.products_id, s.specials_new_products_price, p.products_price) >= round(".$pfrom.",".PRICE_PRECISION.") ) ";
      }
    }

    $pto_check = '';
    if (($pto != '') && (is_numeric($pto))) {
      if ($NeedTax) {
        $pto_check = " AND (IF(s.status = '1' AND p.products_id = s.products_id, s.specials_new_products_price, p.products_price) <= round((".$pto."/(1+tax_rate/100)),".PRICE_PRECISION.") ) ";
      } else {
        $pto_check = " AND (IF(s.status = '1' AND p.products_id = s.products_id, s.specials_new_products_price, p.products_price) <= round(".$pto.",".PRICE_PRECISION.") ) ";
      }
    }
  
    //build query
    $select_str = "SELECT ".ADD_SELECT_SEARCH."
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
                          pd.products_heading_title,
                          pd.products_short_description,
                          pd.products_description,
                          IFNULL(s.specials_new_products_price, p.products_price) AS price ";
  
    if (PRODUCT_LIST_FILTER == 'true') { 
      $select_str = "SELECT p.products_id ";
    }
  
    $from_str  = "FROM ".TABLE_PRODUCTS." AS p 
                  JOIN ".TABLE_PRODUCTS_DESCRIPTION." AS pd 
                       ON p.products_id = pd.products_id 
                          AND trim(pd.products_name) != '' 
                          AND pd.language_id = '".(int)$_SESSION['languages_id']."'
                  JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c 
                       ON p2c.products_id = pd.products_id
                          ".$p2c_condition."
                  JOIN ".TABLE_CATEGORIES." c
                       ON c.categories_id = p2c.categories_id
                          AND c.categories_status = 1
                              ".CATEGORIES_CONDITIONS_C." ";
                           
    $from_str .= SEARCH_IN_ATTR == 'true' ? " LEFT JOIN ".TABLE_PRODUCTS_ATTRIBUTES." AS pa ON p.products_id = pa.products_id
                                              LEFT JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES." AS pov ON pa.options_values_id = pov.products_options_values_id AND pov.language_id = '".(int) $_SESSION['languages_id']."' " : "";
    $from_str .= "LEFT JOIN ".TABLE_SPECIALS." AS s ON p.products_id = s.products_id ".SPECIALS_CONDITIONS_S." ";
    $from_str .= SEARCH_IN_MANU == 'true' ? " LEFT JOIN ".TABLE_MANUFACTURERS." AS m ON p.manufacturers_id = m.manufacturers_id " : "";
  
    if (SEARCH_IN_FILTER == 'true') {
      $from_str .= "LEFT JOIN ".TABLE_PRODUCTS_TAGS." pt ON pt.products_id = p.products_id
                    LEFT JOIN ".TABLE_PRODUCTS_TAGS_VALUES." ptv ON ptv.options_id = pt.options_id AND ptv.values_id = pt.values_id AND ptv.status = '1' AND ptv.languages_id = '".(int)$_SESSION['languages_id']."' ";
    }
    
    $tax_where = '';
    if ($NeedTax) {
      if (!isset ($_SESSION['customer_country_id'])) {
        $_SESSION['customer_country_id'] = STORE_COUNTRY;
        $_SESSION['customer_zone_id'] = STORE_ZONE;
      }
      $from_str .= " LEFT JOIN ".TABLE_TAX_RATES." tr ON p.products_tax_class_id = tr.tax_class_id 
                     LEFT JOIN ".TABLE_ZONES_TO_GEO_ZONES." gz ON tr.tax_zone_id = gz.geo_zone_id ";
      $tax_where = " AND (gz.zone_country_id IS NULL OR gz.zone_country_id = '0' OR gz.zone_country_id = '".(int) $_SESSION['customer_country_id']."') 
                     AND (gz.zone_id is null OR gz.zone_id = '0' OR gz.zone_id = '".(int) $_SESSION['customer_zone_id']."')";
    }

    //where-string
    $where_str = " WHERE p.products_status = '1' "  
    .PRODUCTS_CONDITIONS_P
    .$filter_where
    .$manu_check
    .$tax_where
    .$pfrom_check
    .$pto_check;

    // create $search_keywords array
    $keywordcheck = xtc_parse_search_string($keywords, $search_keywords);

    $sorting = ' ORDER BY '.SEARCH_RESULTS_FIELD.' '.SEARCH_RESULTS_SORT.', p.products_id ASC ';

    //go for keywords... this is the main search process
    if ($keywords && $keywordcheck) {
    
      include(DIR_WS_INCLUDES.'build_search_query.php');
    
      if (PRODUCT_LIST_FILTER != 'true') { 
        $where_str .= " GROUP BY p.products_id ";
        $where_str .= ((isset($_SESSION['filter_sorting'])) ? $_SESSION['filter_sorting'] : $sorting);
      }
    }

    // glue together
    $listing_sql = $select_str.$from_str.$where_str;
  
    if (PRODUCT_LIST_FILTER == 'true') { 
      $products_search_array = array();
      $result_query = xtDBquery($listing_sql);
      while ($result = xtc_db_fetch_array($result_query, true)) {
        $products_search_array[$result['products_id']] = $result['products_id'];
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
                             pd.products_heading_title,
                             pd.products_short_description,
                             pd.products_description,
                             IFNULL(s.specials_new_products_price, p.products_price) AS price
                        FROM ".TABLE_PRODUCTS." p
                        JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                             ON p.products_id = pd.products_id
                                AND pd.language_id = '".(int)$_SESSION['languages_id']."'
                   LEFT JOIN ".TABLE_SPECIALS." s 
                             ON p.products_id = s.products_id
                                ".SPECIALS_CONDITIONS_S."
                       WHERE p.products_id IN ('".implode("', '", $products_search_array)."')
                             ".((isset($_SESSION['filter_sorting'])) ? $_SESSION['filter_sorting'] : $sorting);
    }
  
    $_GET['keywords'] = urlencode($keywords);
  }
?>