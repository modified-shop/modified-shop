<?php
/* --------------------------------------------------------------
   $Id$

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------

   Released under the GNU General Public License
   --------------------------------------------------------------*/

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

if (!isset($categories_image_name_process)) {
  $categories_image_name_process = $categories_image_name;
}

if (is_file(DIR_FS_CATALOG_IMAGES.'categories/'.$categories_image_name_process)) {
  unlink(DIR_FS_CATALOG_IMAGES.'categories/'.$categories_image_name_process);
}

$a = new image_manipulation(DIR_FS_CATALOG_IMAGES.'categories/original_images/'.$categories_image_name, CATEGORIES_IMAGE_MOBILE_WIDTH, CATEGORIES_IMAGE_MOBILE_HEIGHT, DIR_FS_CATALOG_IMAGES.'categories/'.$categories_image_name_process, IMAGE_QUALITY, '');
$a->create();

unset($categories_image_name_process);
?>