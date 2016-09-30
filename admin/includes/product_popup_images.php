<?php
/* --------------------------------------------------------------
   $Id: product_popup_images.php 899 2005-04-29 02:40:57Z hhgag $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------

   Released under the GNU General Public License
   --------------------------------------------------------------*/

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

if (!isset($products_image_name_process)) {
  $products_image_name_process = $products_image_name;
}

$a = new image_manipulation(DIR_FS_CATALOG_ORIGINAL_IMAGES . $products_image_name,PRODUCT_IMAGE_POPUP_WIDTH,PRODUCT_IMAGE_POPUP_HEIGHT,DIR_FS_CATALOG_POPUP_IMAGES . $products_image_name_process,IMAGE_QUALITY,'');

if (PRODUCT_IMAGE_POPUP_MERGE != '') {
  $string=str_replace("'",'',PRODUCT_IMAGE_POPUP_MERGE);
  $string=str_replace(')','',$string);
  $string=str_replace('(',DIR_FS_CATALOG_IMAGES,$string);
  $array=explode(',',$string);
  $a->merge($array[0],$array[1],$array[2],$array[3],$array[4]);
}

$a->create();

unset($products_image_name_process);
?>