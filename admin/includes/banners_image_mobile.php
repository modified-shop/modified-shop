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
  $banners_image_name_process = $banners_image_name;
}

if (is_file(DIR_FS_CATALOG_IMAGES.'banner/'.$banners_image_name_process)) {
  unlink(DIR_FS_CATALOG_IMAGES.'banner/'.$banners_image_name_process);
}

$a = new image_manipulation(DIR_FS_CATALOG_IMAGES.'banner/original_images/'.$banners_image_name, BANNERS_IMAGE_MOBILE_WIDTH, BANNERS_IMAGE_MOBILE_HEIGHT, DIR_FS_CATALOG_IMAGES.'banner/'.$banners_image_name_process, IMAGE_QUALITY, '');
$a->create();

unset($banners_image_name_process);
?>