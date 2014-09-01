<?php
/* --------------------------------------------------------------
   $Id: products_images.php 3568 2012-08-30 08:45:43Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]


   Released under the GNU General Public License
   --------------------------------------------------------------*/
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

//include needed functions
require_once (DIR_FS_INC.'xtc_get_products_mo_images.inc.php');

// show images
if ($_GET['action'] == 'new_product') {

  // display images fields:
  echo '<tr><td colspan="4">'.xtc_draw_separator('pixel_trans.gif', '1', '10').'</td></tr><tr>';
  if ($pInfo->products_image) {
    echo '<td colspan="4"><table><tr><td align="center" class="main" width="'. (PRODUCT_IMAGE_THUMBNAIL_WIDTH + 15).'">'.xtc_image(DIR_WS_CATALOG_THUMBNAIL_IMAGES.$pInfo->products_image, 'Standard Image').'</td>';
  }
  echo '<td class="main">'.TEXT_PRODUCTS_IMAGE.'<br />'.xtc_draw_file_field('products_image').'<br />'.xtc_draw_separator('pixel_trans.gif', '24', '15').'&nbsp;'.$pInfo->products_image.xtc_draw_hidden_field('products_previous_image_0', $pInfo->products_image);

  if ($pInfo->products_image != '') {
    echo '</tr><tr><td align="center" class="main" valign="middle">'.xtc_draw_selection_field('del_pic', 'checkbox', $pInfo->products_image).' '.TEXT_DELETE.'</td></tr></table>';
  } else {
    echo '</td></tr>';
  }

  // display MO PICS
  if (MO_PICS > 0) {
    $mo_images = xtc_get_products_mo_images($pInfo->products_id);
    for ($i = 0; $i < MO_PICS; $i ++) {
      echo '<tr><td colspan="4">'.xtc_draw_separator('pixel_black.gif', '100%', '1').'</td></tr>';
      echo '<tr><td colspan="4">'.xtc_draw_separator('pixel_trans.gif', '1', '10').'</td></tr>';
      if ($mo_images[$i]["image_name"]) {
        echo '<tr><td colspan="4"><table><tr><td align="center" class="main" width="'. (PRODUCT_IMAGE_THUMBNAIL_WIDTH + 15).'">'.xtc_image(DIR_WS_CATALOG_THUMBNAIL_IMAGES.$mo_images[$i]["image_name"], 'Image '. ($i +1)).'</td>';
      } else {
        echo '<tr>';
      }
      echo '<td class="main">'.TEXT_PRODUCTS_IMAGE.' '. ($i +1).'<br />'.xtc_draw_file_field('mo_pics_'.$i).'<br />'.xtc_draw_separator('pixel_trans.gif', '24', '15').'&nbsp;'.$mo_images[$i]["image_name"].xtc_draw_hidden_field('products_previous_image_'. ($i +1), $mo_images[$i]["image_name"]);
      if (isset ($mo_images[$i]["image_name"])) {
        echo '</tr><tr><td align="center" class="main" valign="middle">'.xtc_draw_selection_field('del_mo_pic[]', 'checkbox', $mo_images[$i]["image_name"]).' '.TEXT_DELETE.'</td></tr></table>';
      } else {
        echo '</td></tr>';
      }
    }
  }

}
?>