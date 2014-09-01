<?php

/* -----------------------------------------------------------------------------------------
   $Id: show_product_thumbs.php 831 2005-03-13 10:16:09Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(popup_image.php,v 1.12 2001/12/12); www.oscommerce.com 

   Released under the GNU General Public License 
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   Modified by BIA Solutions (www.biasolutions.com) to create a bordered look to the image

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

require ('includes/application_top.php');
require_once (DIR_FS_INC.'xtc_get_products_mo_images.inc.php');
?>
<?php
//BOF - Dokuman - 2009-06-05 - replace table with div
//<body bgcolor="#FFFFFF">
//<table align="center">
//<tr>
echo '<body>';
echo '<div align="center">';
//EOF - Dokuman - 2009-06-05 - replace table with div
?>
<?php
$mo_images = xtc_get_products_mo_images((int) $_GET['pID']);
if ((int) $_GET['imgID'] == 0)
	$actual = ' bgcolor="#FF0000"';
else
	unset ($actual);
//BOF - Dokuman - 2009-06-05 - replace table with div
//echo '<td align="left"'.$actual.'>';
echo '<div style="display:inline">';
//EOF - Dokuman - 2009-06-05 - replace table with div

$products_query = xtc_db_query("select pd.products_name, p.products_image from ".TABLE_PRODUCTS." p left join ".TABLE_PRODUCTS_DESCRIPTION." pd on p.products_id = pd.products_id where p.products_status = '1' and p.products_id = '".(int) $_GET['pID']."' and pd.language_id = '".(int) $_SESSION['languages_id']."'");
$products_values = xtc_db_fetch_array($products_query);
//BOF - Dokuman - 2009-06-05 - replace table with div
//echo '<a href="popup_image.php?pID='.(int) $_GET['pID'].'&imgID=0" target="_parent">'.xtc_image(DIR_WS_THUMBNAIL_IMAGES.$products_values['products_image'], $products_values['products_name']).'</a>';
//echo '</td>';
echo '<a href="popup_image.php?pID='.(int) $_GET['pID'].'&imgID=0" target="_parent">'.xtc_image(DIR_WS_THUMBNAIL_IMAGES.$products_values['products_image'], $products_values['products_name']).'</a>&nbsp;&nbsp;';
//EOF - Dokuman - 2009-06-05 - replace table with div

if ($mo_images != false) {
	foreach ($mo_images as $mo_img) {
		if ($mo_img['image_nr'] == (int) $_GET['imgID'])
			$actual = ' bgcolor="#FF0000"';
		else
			unset ($actual);
//BOF - Dokuman - 2009-06-05 - replace table with div
		//echo '<td align=left'.$actual.'><a href="popup_image.php?pID='.(int) $_GET['pID'].'&imgID='.$mo_img['image_nr'].'" target="_parent">'.xtc_image(DIR_WS_THUMBNAIL_IMAGES.$mo_img['image_name'], $products_values['products_name']).'</a></td>';
		echo '<a href="popup_image.php?pID='.(int) $_GET['pID'].'&imgID='.$mo_img['image_nr'].'" target="_parent">'.xtc_image(DIR_WS_THUMBNAIL_IMAGES.$mo_img['image_name'], $products_values['products_name']).'</a>&nbsp;&nbsp;';
//EOF - Dokuman - 2009-06-05 - replace table with div

	}
}
//BOF - Dokuman - 2009-06-05 - replace table with div
/*
?>
</tr>
</table>
*/
echo '  </div>';
echo '</div>';
//EOF - Dokuman - 2009-06-05 - replace table with div
?>
</body>