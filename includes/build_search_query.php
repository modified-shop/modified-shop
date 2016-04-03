<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/
    
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
      $ent_keyword = $ent_keyword != $search_keywords[$i] ? xtc_db_input($ent_keyword) : false;
      $keyword = xtc_db_input($search_keywords[$i]);
      $where_str .= " ( ";
      $where_str .= "pd.products_keywords LIKE ('%".$keyword."%') ";
      $where_str .= $ent_keyword ? "OR pd.products_keywords LIKE ('%".$ent_keyword."%') " : '';
      if (SEARCH_IN_DESC == 'true') {
         $where_str .= "OR pd.products_description LIKE ('%".$keyword."%') ";
         $where_str .= $ent_keyword ? "OR pd.products_description LIKE ('%".$ent_keyword."%') " : '';
         $where_str .= "OR pd.products_short_description LIKE ('%".$keyword."%') ";
         $where_str .= $ent_keyword ? "OR pd.products_short_description LIKE ('%".$ent_keyword."%') " : '';
      }
      if (SEARCH_IN_MANU == 'true') {
         $where_str .= "OR m.manufacturers_name LIKE ('%".$keyword."%') ";
         $where_str .= $ent_keyword ? "OR m.manufacturers_name LIKE ('%".$ent_keyword."%') " : '';
      }
      $where_str .= "OR pd.products_name LIKE ('%".$keyword."%') ";
      $where_str .= $ent_keyword ? "OR pd.products_name LIKE ('%".$ent_keyword."%') " : '';
      $where_str .= "OR p.products_model LIKE ('%".$keyword."%') ";
      $where_str .= $ent_keyword ? "OR p.products_model LIKE ('%".$ent_keyword."%') " : '';
      $where_str .= "OR p.products_ean LIKE ('%".$keyword."%') ";
      $where_str .= $ent_keyword ? "OR p.products_ean LIKE ('%".$ent_keyword."%') " : '';
      $where_str .= "OR p.products_manufacturers_model LIKE ('%".$keyword."%') ";
      $where_str .= $ent_keyword ? "OR p.products_manufacturers_model LIKE ('%".$ent_keyword."%') " : '';
      if (ADD_WHERE_SEARCH != '') {
        $add_where = explode(',',ADD_WHERE_SEARCH);
        if (count($add_where)) {
          foreach($add_where as $entry) {
            $entry = trim($entry);
            if ($entry != '') {
              $where_str .= "OR ". $entry ." LIKE ('%".$keyword."%') ";
              $where_str .= $ent_keyword ? "OR ". $entry ." LIKE ('%".$ent_keyword."%') " : '';
            }
          }
        }
      }
      if (SEARCH_IN_FILTER == 'true') {
        $where_str .= "OR ptv.values_name LIKE ('%".$keyword."%') ";
        $where_str .= $ent_keyword ? "OR ptv.values_name LIKE ('%".$ent_keyword."%') " : '';
        $where_str .= "OR ptv.values_description LIKE ('%".$keyword."%') ";
        $where_str .= $ent_keyword ? "OR ptv.values_description LIKE ('%".$ent_keyword."%') " : '';
      }
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
