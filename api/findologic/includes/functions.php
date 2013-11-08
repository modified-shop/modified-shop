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
    $image = HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_ORIGINAL_IMAGES . $image;
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
                                               patrib.attributes_model,
                                               patrib.attributes_ean,
                                               popt.products_options_name,
                                               poptv.products_options_values_name,
                                               MAX(patrib.options_values_price) AS max_options_values_price
                                          FROM ".TABLE_PRODUCTS_ATTRIBUTES." patrib
                                          JOIN ".TABLE_PRODUCTS_OPTIONS." popt
                                               ON patrib.options_id = popt.products_options_id
                                                  AND popt.language_id = '".(int) FL_LANG_ID."'
                                          JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES." poptv
                                               ON patrib.options_values_id = poptv.products_options_values_id 
                                                  AND poptv.language_id = '".(int) FL_LANG_ID."'
                                         WHERE patrib.products_id='".$products_id."'";

    $result_fla = xtc_db_query($products_options_name_query_raw);

    if (xtc_db_num_rows($result_fla) > 0) {
      while ($row_fla = xtc_db_fetch_array($result_fla)) {

        if ($debug) {
          output_row($row_fla);
        }

        if(!isset($attributes[$row_fla['products_options_name']]))	{
          $attributes[$row_fla['products_options_name']] = array($row_fla['products_options_values_name']);
        } else {
          array_push($attributes[$row_fla['products_options_name']], $row_fla['products_options_values_name']);
        }
        
        $max_options_values_price = $row_fla['max_options_values_price'];
        $attributes_model[] = $row_fla['attributes_model'];
        $attributes_model[] = $row_fla['attributes_ean'];
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
    $attributes_model[] = $row['products_ean'];
    $attributes_model = array_filter($attributes_model, 'xtc_not_null');

    $product = array(
      "id" => $row['products_id'],
      "ordernumber" => implode(',', $attributes_model),
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
  $sql = "SELECT pc.categories_id AS cat FROM ".TABLE_PRODUCTS_TO_CATEGORIES." pc WHERE pc.products_id = ".$productId;
  $result = xtc_db_query($sql);
  if (xtc_db_num_rows($result)) {
    while ($row = xtc_db_fetch_array($result)) {

      if ($debug) {
        output_row($row);
      }

      array_push($categories, get_category_and_parent_category_names($row['cat'], $debug));
    }
  }
  return $categories;
}


function get_category_and_parent_category_names($cat, $debug = false) {
  $catid = $cat;
  $depthLimit = 100;

  $categories = array();
  $depthLevel = 0;
  while ($catid != 0 && $depthLevel < $depthLimit)
  {
    $sql =
      "SELECT
        c.parent_id AS parent,
        cd.categories_name AS name
      FROM
        ".TABLE_CATEGORIES." c
        LEFT OUTER JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd
          ON (c.categories_id = cd.categories_id AND cd.language_id = " . FL_LANG_ID . ")
      WHERE
        c.categories_id = '".$catid."'
      AND 
        c.categories_status = '1'";

    $result = xtc_db_query($sql);

    if (xtc_db_num_rows($result) && ($row = xtc_db_fetch_array($result))) {

      if ($debug) {
        output_row($row);
      }

      $newcatid = $row['parent'];
      $name = strip_tags($row['name']);
      $name = str_replace("/", "/&shy;", $name);
      /* push the parent category on the category stack */
      array_push($categories, $name);
      if ($newcatid == $catid) break;
      $catid = $newcatid;
      $depthLevel++;
    } else {
      break;
    }
  }

  /* higher categories are further back in the category stack, reverse it */
  $categories = array_reverse($categories);

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
  fclose($fp);
}

?>