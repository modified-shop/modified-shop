<?php
/* --------------------------------------------------------------
   $Id: product_info_images.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

$a = new image_manipulation(DIR_FS_CATALOG_ORIGINAL_IMAGES . $products_image_name,PRODUCT_IMAGE_INFO_WIDTH,PRODUCT_IMAGE_INFO_HEIGHT,DIR_FS_CATALOG_INFO_IMAGES . $products_image_name,IMAGE_QUALITY,'');
$array=clear_string(PRODUCT_IMAGE_INFO_BEVEL);
if (PRODUCT_IMAGE_INFO_BEVEL != ''){
$a->bevel($array[0],$array[1],$array[2]);}

$array=clear_string(PRODUCT_IMAGE_INFO_GREYSCALE);
if (PRODUCT_IMAGE_INFO_GREYSCALE != ''){
$a->greyscale($array[0],$array[1],$array[2]);}

$array=clear_string(PRODUCT_IMAGE_INFO_ELLIPSE);
if (PRODUCT_IMAGE_INFO_ELLIPSE != ''){
$a->ellipse($array[0]);}

$array=clear_string(PRODUCT_IMAGE_INFO_ROUND_EDGES);
if (PRODUCT_IMAGE_INFO_ROUND_EDGES != ''){
$a->round_edges($array[0],$array[1],$array[2]);}

$string=str_replace("'",'',PRODUCT_IMAGE_INFO_MERGE);
$string=str_replace(')','',$string);
$string=str_replace('(',DIR_FS_CATALOG_IMAGES,$string);
$array=explode(',',$string);
//$array=clear_string();
if (PRODUCT_IMAGE_INFO_MERGE != ''){
$a->merge($array[0],$array[1],$array[2],$array[3],$array[4]);}

$array=clear_string(PRODUCT_IMAGE_INFO_FRAME);
if (PRODUCT_IMAGE_INFO_FRAME != ''){
$a->frame($array[0],$array[1],$array[2],$array[3]);}

$array=clear_string(PRODUCT_IMAGE_INFO_DROP_SHADOW);
if (PRODUCT_IMAGE_INFO_DROP_SHADOW != ''){
$a->drop_shadow($array[0],$array[1],$array[2]);}

$array=clear_string(PRODUCT_IMAGE_INFO_MOTION_BLUR);
if (PRODUCT_IMAGE_INFO_MOTION_BLUR != ''){
$a->motion_blur($array[0],$array[1]);}
	  $a->create();
?>