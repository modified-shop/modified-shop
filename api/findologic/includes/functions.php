<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2009 FINDOLOGIC GmbH - Version: 4.1 (120)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
	
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
  die('Direct Access to this location is not allowed.');
}


function get_columns() {
  return array('id',
               'ordernumber',
               'name',
               'summary',
               'description',
               'price',
               'instead',
               'maxprice',
               'taxrate',
               'url',
               'image',
               'attributes',
               'keywords',
               'groups',
               'bonus',
               'shipping',
               );
}


function get_column_delimiter() {
  return "\t";
}


function get_category_delimiter() {
  return "_";
}


function get_image($image) {
  if (!empty($image)) {
    $image = HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_POPUP_IMAGES . $image;
  } 
  return $image;
}


function extract_text($string) {
  $string = strip_tags($string);
  $string = str_replace(array("\r", "\n", "\t"), ' ', $string);
  $string = trim(preg_replace("/\s+/"," ",$string)); 

  return $string;
}


function get_encoded_text($text) {
  $text = str_replace("&nbsp;","",$text);
  return $text;
}


function get_description($model, $description) {
  return (extract_text("Artikelnummer: " . str_pad($model, 7 ,'0', STR_PAD_LEFT) . " " .$description));
}


function ensure_encoding($string) {

  if (!is_string($string)) {
    return $string;
  }

  // convert entities
  $string = decode_htmlentities($string);
  
  /* ensure that strings are not utf8-encoded twice */
  $is_unicode = (mb_detect_encoding($string, array('UTF-8'), true) == 'UTF-8');

  if ($is_unicode) {
    return $string;
  } else {
    return utf8_encode($string);
  }
}


function select_product($products_id, $debug=false) {
  global $fp, $xtcPrice, $main;

  $products_query_raw = "SELECT p.products_id,
                                p.products_model,
                                p.products_ean,
                                p.products_price,
                                p.products_discount_allowed,
                                p.products_image,
                                p.products_ordered,				
                                p.products_tax_class_id,
                                p.products_shippingtime,
                                p.products_manufacturers_model,
                                pd.products_name,
                                pd.products_short_description,
                                pd.products_description,
                                pd.products_keywords,
                                s.specials_new_products_price,
                                m.manufacturers_name
                           FROM ".TABLE_PRODUCTS." p
                           JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                ON p.products_id = pd.products_id
                                   AND pd.language_id = ".(int) FL_LANG_ID." 
                                   AND pd.products_name != ''
                      LEFT JOIN ".TABLE_MANUFACTURERS." m
                                ON p.manufacturers_id = m.manufacturers_id
                      LEFT JOIN ".TABLE_SPECIALS." s
                                ON s.products_id = p.products_id
                          WHERE p.products_id = '".$products_id."'";


  $result = xtc_db_query($products_query_raw);

  if (xtc_db_num_rows($result) > 0) {
    
    $attributes = array();
    $attributes_model = array();
    
    $row = xtc_db_fetch_array($result);
     
    if ($debug) {
      output_row($row);
    }
  
    if (xtc_not_null($row['manufacturers_name'])) {			
      $attributes['vendor'] = $row['manufacturers_name'];
    }
    
    $all_cat = get_all_product_category_names($row['products_id'], $debug);
    if(isset($all_cat) && !empty($all_cat)) {
      $attributes['cat'] = $all_cat;
    }
    
    $max_options_values_price = 0;
    $products_options_name_query_raw = "SELECT DISTINCT
                                               popt.products_options_id,
                                               popt.products_options_name,
                                               popt.products_options_sortorder
                                          FROM ".TABLE_PRODUCTS_OPTIONS." popt
                                          JOIN ".TABLE_PRODUCTS_ATTRIBUTES." patrib
                                               ON patrib.options_id = popt.products_options_id
                                         WHERE patrib.products_id='".$products_id."'
                                           AND popt.language_id = '".(int) FL_LANG_ID."'
                                      ORDER BY popt.products_options_sortorder, popt.products_options_id";

    $result_fla = xtc_db_query($products_options_name_query_raw);

    if (xtc_db_num_rows($result_fla) > 0) {
      while ($row_fla = xtc_db_fetch_array($result_fla)) {

        if ($debug) {
          output_row($row_fla);
        }

        $products_options_query_raw = "SELECT pov.products_options_values_id,
                                              pov.products_options_values_name,
                                              pa.*
                                         FROM ".TABLE_PRODUCTS_ATTRIBUTES." pa
                                         JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES." pov
                                              ON pa.options_values_id = pov.products_options_values_id
                                                 AND pov.language_id = '".(int) FL_LANG_ID."'
                                        WHERE pa.products_id = '".$products_id."'
                                          AND pa.options_id = '".$row_fla['products_options_id']."'
                                     ORDER BY pa.sortorder, pa.options_values_id";

        $result_flo = xtc_db_query($products_options_query_raw);
        
        while ($row_flo = xtc_db_fetch_array($result_flo)) {

          if ($debug) {
            output_row($row_flo);
          }

          if(!isset($attributes[$row_fla['products_options_name']]))	{
            $attributes[$row_fla['products_options_name']] = array($row_flo['products_options_values_name']);
          } else {
            array_push($attributes[$row_fla['products_options_name']], $row_flo['products_options_values_name']);
          }
          
          $price = 0;
          if ($row_flo['options_values_price'] != '0.00') {
            $CalculateCurr = ($row['products_tax_class_id'] == 0) ? true : false;
            $price = $xtcPrice->xtcFormat($row_flo['options_values_price'], false, $row['products_tax_class_id'], $CalculateCurr);
          }
          $attr_price = $price;
          if ($row_flo['price_prefix'] == "-") {
            $attr_price=$price*(-1);
          }
          
          if ($max_options_values_price < $attr_price) {
            $max_options_values_price = $attr_price;
          }
       
          $attributes_model[] = $row_flo['attributes_model'];
          $attributes_model[] = $row_flo['attributes_ean'];
        }
      }			
    }
    
    $attributes_enc = null;
    foreach($attributes as $key => $value) {
      if(!is_array($value)) {
        if(!empty($value)) {
          $attributes_enc = $attributes_enc . "&" . urlencode(ensure_encoding($key)) . "[]=" . urlencode(ensure_encoding($value));
        }
      } else {
        foreach($value as $skey => $svalue) {
          if(!empty($svalue)) {
            $attributes_enc = $attributes_enc . "&" . urlencode(ensure_encoding($key)) . "[]=" . urlencode(ensure_encoding($svalue));
          }
        }
      }
    }

    if($attributes_enc[0] == '&') {
      $attributes_enc = substr($attributes_enc, 1);
    }
    
    $attributes_model[] = $row['products_model'];
    $attributes_model[] = $row['products_manufacturers_model'];
    $attributes_model[] = $row['products_ean'];
    $attributes_model = array_filter($attributes_model, 'xtc_not_null');

    $product = array("id" => $row['products_id'],
                     "ordernumber" => implode('|', $attributes_model),
                     "name" => $row['products_name'],
                     "summary" => extract_text($row['products_short_description']),
                     "description" => get_description($row['products_model'], $row['products_short_description']),
                     "price" => $xtcPrice->xtcGetPrice($row['products_id'], false, 1, $row['products_tax_class_id']),
                     "instead" => $xtcPrice->xtcFormat($row['products_price'], false, $row['products_tax_class_id']),
                     "maxprice" => $xtcPrice->xtcFormat(($row['products_price'] + $max_options_values_price), false, $row['products_tax_class_id']),
                     "taxrate" => xtc_get_tax_rate($row['products_tax_class_id']),
                     "url" => xtc_href_link(FILENAME_PRODUCT_INFO, xtc_product_link($row['products_id'], $row['products_name']), 'NONSSL', false),
                     "image" => get_image($row['products_image']),
                     "attributes" => $attributes_enc,
                     "keywords" => $row['products_keywords'],
                     "groups" => '',
                     "bonus" => '',
                     "shipping" => $main->getShippingStatusName($row['products_shippingtime']),
                     );

    $values = array();
    foreach (get_columns() as $property) {
      array_push(
        $values,
        $product[$property]
      );
    }
    $text = get_encoded_text(implode(get_column_delimiter(), $values));

    fwrite($fp , $text."\n");
    return true;
  }

  return false;
}


function get_all_product_category_names($productId, $debug=false) {
  $categories = array();
  $sql = "SELECT pc.categories_id
            FROM ".TABLE_PRODUCTS_TO_CATEGORIES." pc 
            JOIN ".TABLE_CATEGORIES." c
                 ON c.categories_id = pc.categories_id
                    AND  c.categories_status = '1'
           WHERE pc.products_id = ".$productId;
  $result = xtc_db_query($sql);
  if (xtc_db_num_rows($result)) {
    while ($row = xtc_db_fetch_array($result)) {

      if ($debug) {
        output_row($row);
      }

      array_push($categories, get_category_and_parent_category_names($row['categories_id'], $debug));
    }
  }
  return implode(get_category_delimiter(), $categories);
}


function get_category_and_parent_category_names($categories_id, $debug=false) {
  $depthLimit = 100;

  $categories = array();
  $depthLevel = 0;
  while ($categories_id != 0 && $depthLevel < $depthLimit) {
    $sql = "SELECT c.parent_id,
                   cd.categories_name
              FROM ".TABLE_CATEGORIES." c
              JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd
                   ON (c.categories_id = cd.categories_id 
                       AND cd.language_id = " . FL_LANG_ID . ")
             WHERE c.categories_id = '".$categories_id."'
               AND c.categories_status = '1'";

    $result = xtc_db_query($sql);

    if (xtc_db_num_rows($result) > 0) {
      while ($row = xtc_db_fetch_array($result)) {

        if ($debug) {
          output_row($row);
        }

        $parent = $row['parent_id'];
        $name = strip_tags($row['categories_name']);
        $name = str_replace("/", "/&shy;", $name);
        /* push the parent category on the category stack */
        array_unshift($categories, $name);
        if ($parent == $categories_id) {
          break;
        }
        $categories_id = $parent;
        $depthLevel++;
      }
    } else {
      break;
    }
  }

  if ($depthLevel < $depthLimit) {
    return implode(get_category_delimiter(), $categories);
  } else {
    return $name;
  }
}


function output_row($row) {
  $fp = fopen('php://output', 'w');
  fputcsv($fp, array_map('extract_text', array_keys($row)), get_column_delimiter());
  fputcsv($fp, array_map('extract_text', array_values($row)), get_column_delimiter());
  echo '<br/>';
  fclose($fp);
}

?>