<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(popup_image.php,v 1.12 2001/12/12); www.oscommerce.com 
   (c) 2003 XT-Commerce(show_product_thumbs.php 831 2005-03-13 10:16:09Z mz); www.xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

require ('includes/application_top.php');
require_once (DIR_FS_INC.'xtc_get_products_mo_images.inc.php');
?>
<html>
<head><meta name="robots" content="noindex, nofollow"></head>
<body>
<div style="text-align:center;">
<?php
$products_query = xtc_db_query("SELECT pd.products_name, p.products_image FROM ".TABLE_PRODUCTS." p LEFT JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd ON p.products_id = pd.products_id WHERE p.products_status = '1' AND p.products_id = '".(int) $_GET['pID']."' AND pd.language_id = '".(int) $_SESSION['languages_id']."'");
$products_values = xtc_db_fetch_array($products_query);
echo '  <a href="popup_image.php?pID='.(int) $_GET['pID'].'&imgID=0" target="_parent">' . 
       xtc_image(DIR_WS_THUMBNAIL_IMAGES.$products_values['products_image'], $products_values['products_name']) .
     '</a>&nbsp;&nbsp;';
if ($mo_images = xtc_get_products_mo_images((int) $_GET['pID'])) {
	foreach ($mo_images as $mo_img) {
		echo '  <a href="popup_image.php?pID='.(int) $_GET['pID'].'&imgID='.$mo_img['image_nr'].'" target="_parent">' .
           xtc_image(DIR_WS_THUMBNAIL_IMAGES.$mo_img['image_name'], $products_values['products_name']) .
         '</a>&nbsp;&nbsp;';
	}
}
?>
</div>
</body>
</html>