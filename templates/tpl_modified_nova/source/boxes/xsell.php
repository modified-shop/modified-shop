<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  // include smarty
  include(DIR_FS_BOXES_INC . 'smarty_default.php');

  if ($_SESSION['cart']->count_contents() > 0) {
    $products_id_array = $_SESSION['cart']->get_product_id_array();
    $products_id_array = array_unique($products_id_array);
    
    $box_content = array();
    foreach ($products_id_array as $products_id) {
      $data = $product->getCrossSells($products_id);
      
      if (count($data) > 0) {
        foreach ($data as $xsell_grp_id => $xsell) {
          if (!isset($box_content[$xsell_grp_id])) {
            $box_content[$xsell_grp_id] = $xsell;
          }
          foreach ($xsell['PRODUCTS'] as $products_id => $products_data) {
            $box_content[$xsell_grp_id]['PRODUCTS'][$products_id] = $products_data;
          }
        }
      }
    }
    
    if (count($box_content) > 0) {    
      ksort($box_content);
      $box_smarty->assign('BOX_CONTENT', $box_content);

      if (defined('PICTURESET_BOX')) {
        $box_smarty->assign('pictureset_box', get_pictureset_data(PICTURESET_BOX));
      }
      if (defined('PICTURESET_ROW')) {
        $box_smarty->assign('pictureset_row', get_pictureset_data(PICTURESET_ROW));
      }
    }
    
    $box_smarty->caching = 0;
    $box_xsell = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_xsell.html');
  
    $smarty->assign('box_XSELL', $box_xsell);
  }
