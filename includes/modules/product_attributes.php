<?php
/* -----------------------------------------------------------------------------------------
   $Id: product_attributes.php 3045 2012-06-16 20:06:59Z hhacker $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(product_info.php,v 1.94 2003/05/04); www.oscommerce.com
   (c) 2003      nextcommerce (product_info.php,v 1.46 2003/08/25); www.nextcommerce.org
   (c) 2006 xt:Commerce (product_attributes.php 1255 2005-09-28); www.xt-commerce.de

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:
   Customers Status v3.x  (c) 2002-2003 Copyright Elari elari@free.fr | www.unlockgsm.com/dload-osc/ | CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/elari/?sortby=date#dirlist
   New Attribute Manager v4b                            Autor: Mike G | mp3man@internetwork.net | http://downloads.ephing.com
   Cross-Sell (X-Sell) Admin 1                          Autor: Joshua Dechant (dreamscape)
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

if ($product->getAttributesCount() > 0) {

  $module_smarty = new Smarty;

  $module_smarty->assign('tpl_path',DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');

  $products_options_name_query = xtDBquery("SELECT distinct
                                                   popt.products_options_id,
                                                   popt.products_options_name,
                                                   popt.products_options_sortorder
                                              FROM ".TABLE_PRODUCTS_OPTIONS." popt,
                                                   ".TABLE_PRODUCTS_ATTRIBUTES." patrib
                                             WHERE patrib.products_id='".$product->data['products_id']."'
                                               AND patrib.options_id = popt.products_options_id
                                               AND popt.language_id = '".(int) $_SESSION['languages_id']."'
                                          ORDER BY popt.products_options_sortorder, popt.products_options_id"
                                          );

  $row = 0;

  $products_options_data = array ();
  while ($products_options_name = xtc_db_fetch_array($products_options_name_query,true)) {
    $selected = 0;
    $products_options_array = array ();

    $products_options_data[$row] = array ('NAME' => $products_options_name['products_options_name'],
                                          'ID' => $products_options_name['products_options_id'],
                                          'SORTORDER' => $products_options_name['products_options_sortorder'],  //web28 - 2010-12-14  - add OPTIONS SORTORDER for using in template
                                          'DATA' => ''
                                          );

    $products_options_query = xtDBquery("SELECT pov.products_options_values_id,
                                                pov.products_options_values_name,
                                                pa.*
                                           FROM ".TABLE_PRODUCTS_ATTRIBUTES." pa,
                                                ".TABLE_PRODUCTS_OPTIONS_VALUES." pov
                                          WHERE pa.products_id = '".$product->data['products_id']."'
                                            AND pa.options_id = '".$products_options_name['products_options_id']."'
                                            AND pa.options_values_id = pov.products_options_values_id
                                            AND pov.language_id = '".(int) $_SESSION['languages_id']."'
                                       ORDER BY pa.sortorder, pa.options_values_id
                                        ");
    $col = 0;
    while ($products_options = xtc_db_fetch_array($products_options_query,true)) {
      $price = '';
      if ($_SESSION['customers_status']['customers_status_show_price'] == '0') {
        $products_options_data[$row]['DATA'][$col] = array ('ID' => $products_options['products_options_values_id'],
                                                            'TEXT' => $products_options['products_options_values_name'],
                                                            'MODEL' => $products_options['attributes_model'],
                                                            'PRICE' => '',
                                                            'FULL_PRICE' => '',
                                                            'PLAIN_PRICE' => '',
                                                            'STOCK' => $products_options['attributes_stock'],
                                                            'SORTORDER' => $products_options['sortorder'],
                                                            'PREFIX' => $products_options['price_prefix']
                                                            );
      } else {
        if ($products_options['options_values_price'] != '0.00') {
          $CalculateCurr = ($product->data['products_tax_class_id'] == 0) ? true : false; //FIX several currencies on product attributes
          $price = $xtPrice->xtcFormat($products_options['options_values_price'], false, $product->data['products_tax_class_id'],$CalculateCurr);
        }

        $products_price = $xtPrice->xtcGetPrice($product->data['products_id'], $format = false, 1, $product->data['products_tax_class_id'], $product->data['products_price']);

        if ($_SESSION['customers_status']['customers_status_discount_attributes'] == 1 && $products_options['price_prefix'] == '+') {
          $price -= $price / 100 * $discount;
        }

        $attr_price=$price;

        if ($products_options['price_prefix'] == "-") {
          $attr_price=$price*(-1);
        }

        $full = $products_price + $attr_price;

        $products_options_data[$row]['DATA'][$col] = array ('ID' => $products_options['products_options_values_id'],
                                                            'TEXT' => $products_options['products_options_values_name'],
                                                            'MODEL' => $products_options['attributes_model'],
                                                            'PRICE' => $xtPrice->xtcFormat($price, true),
                                                            'FULL_PRICE' => $xtPrice->xtcFormat($full, true),
                                                            'PLAIN_PRICE' => $xtPrice->xtcFormat($price,false),
                                                            'STOCK' => $products_options['attributes_stock'],
                                                            'SORTORDER' => $products_options['sortorder'],
                                                            'PREFIX' => $products_options['price_prefix']
                                                            );

        //if PRICE for option is 0 we don't need to display it
        if ($price == 0) {
          unset ($products_options_data[$row]['DATA'][$col]['PRICE']);
          //BOF - Tomcraft - 2012-09-14 - Partly revoked r2356 to have FULL_PRICE and PLAIN_PRICE available in options template file for the first option, if the options price is 0
          /*
          unset ($products_options_data[$row]['DATA'][$col]['FULL_PRICE']);
          unset ($products_options_data[$row]['DATA'][$col]['PLAIN_PRICE']);
          */
          //EOF - Tomcraft - 2012-09-14 - Partly revoked r2356 to have FULL_PRICE and PLAIN_PRICE available in options template file for the first option, if the options price is 0
          unset ($products_options_data[$row]['DATA'][$col]['PREFIX']);
        }

      }
      $col ++;
    }
    $row ++;
  }


  if ($product->data['options_template'] == '' or $product->data['options_template'] == 'default') {
    $files = array ();
    if ($dir = opendir(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/product_options/')) {
      while (($file = readdir($dir)) !== false) {
        if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/product_options/'.$file) and (substr($file, -5) == ".html") and ($file != "index.html") and (substr($file, 0, 1) !=".")) {
          $files[] = $file;
        }
      }
      closedir($dir);
    }
    sort($files);
    $product->data['options_template'] = $files[0];
  }

  $module_smarty->assign('language', $_SESSION['language']);
  $module_smarty->assign('options', $products_options_data);

  $module_smarty->caching = 0;
  $module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/product_options/'.$product->data['options_template']);

  $info_smarty->assign('MODULE_product_options', $module);

}

?>