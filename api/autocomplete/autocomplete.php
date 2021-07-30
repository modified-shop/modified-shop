<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  chdir('../../');
  include('includes/application_top.php');


  // include needed functions
  require_once (DIR_FS_INC.'xtc_parse_search_string.inc.php');
  
  $module_smarty = new Smarty;
  $module_smarty->assign('language', $_SESSION['language']);
  
  if (isset($_POST['queryString'])) {
    
    $from_str = $where_str = $p2c_condition = '';
    
    $queryString = stripslashes(trim(decode_utf8(urldecode($_POST['queryString']))));    
    $categories_id = !empty($_POST['categories_id']) ? (int)$_POST['categories_id'] : false;
    $inc_subcat = !empty($_POST['inc_subcat']) ? (int)$_POST['inc_subcat'] : null;

    // create $search_keywords array
    $keywordcheck = xtc_parse_search_string($queryString, $search_keywords);
        
    if ($keywordcheck === true && mb_strlen($queryString, $_SESSION['language_charset']) >= SEARCH_AC_MIN_LENGTH) {
      
      $from_str .= SEARCH_IN_ATTR == 'true' ? " LEFT OUTER JOIN ".TABLE_PRODUCTS_ATTRIBUTES." AS pa ON (p.products_id = pa.products_id) 
                                                LEFT OUTER JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES." AS pov ON (pa.options_values_id = pov.products_options_values_id) " : "";
      $from_str .= SEARCH_IN_MANU == 'true' ? " LEFT OUTER JOIN ".TABLE_MANUFACTURERS." AS m ON (p.manufacturers_id = m.manufacturers_id) " : "";

      if (SEARCH_IN_FILTER == 'true') {
        $from_str .= "LEFT JOIN ".TABLE_PRODUCTS_TAGS." pt ON (pt.products_id = p.products_id)
                      LEFT JOIN ".TABLE_PRODUCTS_TAGS_VALUES." ptv ON (ptv.options_id = pt.options_id AND ptv.values_id = pt.values_id AND ptv.status = '1' AND ptv.languages_id = '".(int)$_SESSION['languages_id']."') ";
      }

      //include subcategories if needed
      if ($categories_id !== false) {
        if ($inc_subcat == '1') {
          $subcategories_array = array ();
          xtc_get_subcategories($subcategories_array, $categories_id);
          $subcategories_array[] = $categories_id;
          $p2c_condition = " AND p2c.categories_id IN ('".implode("', '", $subcategories_array)."') ";
        } else {
          $p2c_condition = " AND p2c.categories_id = '".$categories_id."' ";
        }
      }
      
      include(DIR_WS_INCLUDES.'build_search_query.php');
      
      $where_str .= " ) ";
                                                   
      $autocomplete_search_query = "SELECT ".$product->default_select."
                                      FROM ".TABLE_PRODUCTS." p 
                                      JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd 
                                           ON p.products_id = pd.products_id
                                              AND pd.language_id = '".(int)$_SESSION['languages_id']."'
                                              AND trim(pd.products_name) != ''
                                      JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c 
                                           ON p2c.products_id = pd.products_id
                                              ".$p2c_condition."
                                      JOIN ".TABLE_CATEGORIES." c
                                           ON c.categories_id = p2c.categories_id
                                              AND c.categories_status = 1
                                                  ".CATEGORIES_CONDITIONS_C."
                                           ".$from_str."
                                     WHERE p.products_status = '1'
                                       AND p.products_price > 0 
                                            ".$where_str."
                                            ".PRODUCTS_CONDITIONS_P."
                                   GROUP BY p.products_id 
                                   ORDER BY p.products_id ASC
                                      LIMIT ".MAX_DISPLAY_ADVANCED_SEARCH_RESULTS;
      
      $autocomplete_search_query = xtc_db_query($autocomplete_search_query);                      
      if (xtc_db_num_rows($autocomplete_search_query) > 0) {
        $module_content = array();
        while ($autocomplete_search = xtc_db_fetch_array($autocomplete_search_query)) {
          $module_content[] = $product->buildDataArray($autocomplete_search);
        }
        $module_smarty->assign('module_content', $module_content);
      } else {
        $module_smarty->assign('error', 'true');
      }
      $module_smarty->caching = 0;
      $module_smarty->display(CURRENT_TEMPLATE.'/module/autocomplete.html');
    }
  }
?>