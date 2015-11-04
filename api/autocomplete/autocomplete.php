<?php
/* -----------------------------------------------------------------------------------------
   $Id: stylesheet.css 4246 2013-01-11 14:36:07Z Tomcraft1980 $

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
    
    $from_str = '';
    
    $queryString = xtc_db_input(trim(decode_utf8($_POST['queryString'])));

    // create $search_keywords array
    $keywordcheck = xtc_parse_search_string($queryString, $search_keywords);
        
    if ($keywordcheck === true && strlen($queryString) > SEARCH_AC_MIN_LENGTH) {
      
      $from_str .= SEARCH_IN_ATTR == 'true' ? " LEFT OUTER JOIN ".TABLE_PRODUCTS_ATTRIBUTES." AS pa ON (p.products_id = pa.products_id) 
                                                LEFT OUTER JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES." AS pov ON (pa.options_values_id = pov.products_options_values_id) " : "";
      $from_str .= SEARCH_IN_MANU == 'true' ? " LEFT OUTER JOIN ".TABLE_MANUFACTURERS." AS m ON (p.manufacturers_id = m.manufacturers_id) " : "";

      if (SEARCH_IN_FILTER == 'true') {
        $from_str .= "LEFT JOIN ".TABLE_PRODUCTS_TAGS." pt ON (pt.products_id = p.products_id)
                      LEFT JOIN ".TABLE_PRODUCTS_TAGS_VALUES." ptv ON (ptv.options_id = pt.options_id AND ptv.values_id = pt.values_id AND ptv.status = '1' AND ptv.languages_id = '".(int)$_SESSION['languages_id']."') ";
      }

      include(DIR_WS_INCLUDES.'build_search_query.php');
      
      $where_str .= " ) ";
                                                   
      $autocomplete_search_query = "SELECT p.*, 
                                           pd.products_name
                                      FROM ".TABLE_PRODUCTS." p 
                                 LEFT JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd 
                                           ON p.products_id = pd.products_id
                                              AND pd.language_id = '".(int)$_SESSION['languages_id']."'
                                              AND trim(pd.products_name) != ''
                                           ".$from_str."
                                     WHERE p.products_status = '1' 
                                            ".$where_str."
                                            ".PRODUCTS_CONDITIONS_P."
                                   GROUP BY p.products_id 
                                   ORDER BY p.products_id ASC";
      
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