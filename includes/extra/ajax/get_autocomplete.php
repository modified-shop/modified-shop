<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2019 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

  if (isset($_REQUEST['speed'])) {
    require_once (DIR_FS_INC.'auto_include.inc.php');

    require_once (DIR_FS_INC.'db_functions_'.DB_MYSQL_TYPE.'.inc.php');
    require_once (DIR_FS_INC.'db_functions.inc.php');
    require_once (DIR_FS_INC.'xtc_input_validation.inc.php');

    require_once (DIR_WS_INCLUDES.'database_tables.php');
  }


  function get_autocomplete() {
    global $product, $xtPrice;
    
    $data = array('result' => '');
    
    // include needed functions
    require_once (DIR_FS_INC.'xtc_parse_search_string.inc.php');
  
    $module_smarty = new Smarty;
    $module_smarty->assign('language', $_SESSION['language']);
  
    if (isset($_POST['keywords'])) {    
      $from_str = $where_str = $p2c_condition = $params = '';
    
      $keywords = stripslashes(trim(decode_utf8(urldecode($_POST['keywords']))));    
      $categories_id = !empty($_POST['categories_id']) ? (int)$_POST['categories_id'] : false;
      $inc_subcat = !empty($_POST['inc_subcat']) ? (int)$_POST['inc_subcat'] : null;

      // create $search_keywords array
      $keywordcheck = xtc_parse_search_string($keywords, $search_keywords);
        
      if ($keywordcheck === true && mb_strlen($keywords, $_SESSION['language_charset']) >= SEARCH_AC_MIN_LENGTH) {
      
        $from_str .= SEARCH_IN_ATTR == 'true' ? " LEFT OUTER JOIN ".TABLE_PRODUCTS_ATTRIBUTES." AS pa ON (p.products_id = pa.products_id) 
                                                  LEFT OUTER JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES." AS pov ON (pa.options_values_id = pov.products_options_values_id) " : "";
        $from_str .= SEARCH_IN_MANU == 'true' ? " LEFT OUTER JOIN ".TABLE_MANUFACTURERS." AS m ON (p.manufacturers_id = m.manufacturers_id) " : "";

        if (SEARCH_IN_FILTER == 'true') {
          $from_str .= "LEFT JOIN ".TABLE_PRODUCTS_TAGS." pt ON (pt.products_id = p.products_id)
                        LEFT JOIN ".TABLE_PRODUCTS_TAGS_VALUES." ptv ON (ptv.options_id = pt.options_id AND ptv.values_id = pt.values_id AND ptv.status = '1' AND ptv.languages_id = '".(int)$_SESSION['languages_id']."') ";
        }

        //include subcategories if needed
        if ($categories_id !== false) {
          $params .= '&categories_id='.$categories_id;
          if ($inc_subcat == '1') {
            $params .= '&inc_subcat=1';
            $subcategories_array = array ();
            xtc_get_subcategories($subcategories_array, $categories_id);
            $subcategories_array[] = $categories_id;
            $p2c_condition = " AND p2c.categories_id IN ('".implode("', '", $subcategories_array)."') ";
          } else {
            $p2c_condition = " AND p2c.categories_id = '".$categories_id."' ";
          }
        }
      
        include(DIR_WS_INCLUDES.'build_search_query.php');
        
        $sorting = ' ORDER BY '.SEARCH_AC_FIELD.' '.SEARCH_AC_SORT.' ';
                                                 
        $autocomplete_search_query = "SELECT ".$product->default_select.",
                                             IFNULL(s.specials_new_products_price, p.products_price) AS price
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
                                   LEFT JOIN ".TABLE_SPECIALS." s 
                                             ON p.products_id = s.products_id 
                                                ".SPECIALS_CONDITIONS_S."
                                       WHERE p.products_status = '1'
                                             ".$where_str."
                                             ".PRODUCTS_CONDITIONS_P."
                                    GROUP BY p.products_id 
                                             ".((isset($_SESSION['filter_sorting'])) ? $_SESSION['filter_sorting'] : $sorting);
      
        $autocomplete_search_query = xtc_db_query($autocomplete_search_query);
        $autocomplete_search_total = xtc_db_num_rows($autocomplete_search_query);
        
        if ($autocomplete_search_total > 0) {
          $module_content = array();
          while ($autocomplete_search = xtc_db_fetch_array($autocomplete_search_query)) {
            $module_content[] = $product->buildDataArray($autocomplete_search);
            if (count($module_content) == MAX_DISPLAY_SEARCH_AC_RESULTS) break;
          }
          $module_smarty->assign('module_content', $module_content);
          $module_smarty->assign('BUTTON_VIEW_RESULTS', '<a href="'.xtc_href_link(FILENAME_ADVANCED_SEARCH_RESULT, 'keywords='.$keywords.$params).'">'.xtc_image_button('button_results.gif', sprintf(TEXT_BUTTON_RESULTS, $autocomplete_search_total)).'</a>');
          $module_smarty->assign('module_content', $module_content);
        } else {
          $module_smarty->assign('error', 'true');
        }
        $module_smarty->caching = 0;
        $data['result'] = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/autocomplete.html');
      }
    }
    
    return $data;
  }
