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

if (!isset($banners_image_name_process)) {
  $manufacturers_image_name_process = $manufacturers_image_name;
}

if (is_file(DIR_FS_CATALOG_IMAGES.'banner/'.$manufacturers_image_name_process)) {
  unlink(DIR_FS_CATALOG_IMAGES.'banner/'.$manufacturers_image_name_process);
}

$a = new image_manipulation(DIR_FS_CATALOG_IMAGES.'manufacturers/original_images/'.$manufacturers_image_name, MANUFACTURER_IMAGE_WIDTH, MANUFACTURER_IMAGE_HEIGHT, DIR_FS_CATALOG_IMAGES.'manufacturers/'.$manufacturers_image_name_process, IMAGE_QUALITY, '');
$a->create();

unset($manufacturers_image_name_process);
?>