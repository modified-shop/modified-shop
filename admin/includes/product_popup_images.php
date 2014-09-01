<?php
/* --------------------------------------------------------------
   $Id: product_popup_images.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------

   Released under the GNU General Public License 
   --------------------------------------------------------------*/
   
$a = new image_manipulation(DIR_FS_CATALOG_ORIGINAL_IMAGES . $products_image_name,PRODUCT_IMAGE_POPUP_WIDTH,PRODUCT_IMAGE_POPUP_HEIGHT,DIR_FS_CATALOG_POPUP_IMAGES . $products_image_name,IMAGE_QUALITY,'');
$array=clear_string(PRODUCT_IMAGE_POPUP_BEVEL);
if (PRODUCT_IMAGE_POPUP_BEVEL != ''){
$a->bevel($array[0],$array[1],$array[2]);}

$array=clear_string(PRODUCT_IMAGE_POPUP_GREYSCALE);
if (PRODUCT_IMAGE_POPUP_GREYSCALE != ''){
$a->greyscale($array[0],$array[1],$array[2]);}

$array=clear_string(PRODUCT_IMAGE_POPUP_ELLIPSE);
if (PRODUCT_IMAGE_POPUP_ELLIPSE != ''){
$a->ellipse($array[0]);}

$array=clear_string(PRODUCT_IMAGE_POPUP_ROUND_EDGES);
if (PRODUCT_IMAGE_POPUP_ROUND_EDGES != ''){
$a->round_edges($array[0],$array[1],$array[2]);}

$string=str_replace("'",'',PRODUCT_IMAGE_POPUP_MERGE);
$string=str_replace(')','',$string);
$string=str_replace('(',DIR_FS_CATALOG_IMAGES,$string);
$array=explode(',',$string);
//$array=clear_string();
if (PRODUCT_IMAGE_POPUP_MERGE != ''){
$a->merge($array[0],$array[1],$array[2],$array[3],$array[4]);}

$array=clear_string(PRODUCT_IMAGE_POPUP_FRAME);
if (PRODUCT_IMAGE_POPUP_FRAME != ''){
$a->frame($array[0],$array[1],$array[2],$array[3]);}

$array=clear_string(PRODUCT_IMAGE_POPUP_DROP_SHADOW);
if (PRODUCT_IMAGE_POPUP_DROP_SHADOW != ''){
$a->drop_shadow($array[0],$array[1],$array[2]);}

$array=clear_string(PRODUCT_IMAGE_POPUP_MOTION_BLUR);
if (PRODUCT_IMAGE_POPUP_MOTION_BLUR != ''){
$a->motion_blur($array[0],$array[1]);}
	  $a->create();
?>