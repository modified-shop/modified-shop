<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2009 - 2012 xtcModified
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(popup_image.php,v 1.12 2001/12/12); www.oscommerce.com 
   (c) 2005 xtCommerce (show_product_thumbs.php 831 2005-03-13 10:16:09Z mz); www.xt-commerce.com

   Released under the GNU General Public License 
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   Modified by BIA Solutions (www.biasolutions.com) to create a bordered look to the image

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  require ('includes/application_top.php');
  require_once (DIR_FS_INC.'xtc_get_products_mo_images.inc.php');
?>
<body>
<table align="center">
    <tr>
<?php
  $mo_images = xtc_get_products_mo_images((int) $_GET['pID']);
  if ((int) $_GET['imgID'] == 0){
    $actual = ' style="background: #ff0000"';
  } else {
    $actual = '';
  }
  echo '<td align="left"'.$actual.'>';
  
  $products_query = xtc_db_query("select 
                    pd.products_name,
                    p.products_image
                    from ".TABLE_PRODUCTS." p
                    left join ".TABLE_PRODUCTS_DESCRIPTION." pd on p.products_id = pd.products_id
                    where p.products_status = '1'
                    and p.products_id = '".(int) $_GET['pID']."'
                    and pd.language_id = '".(int) $_SESSION['languages_id']."'");
  
  $products_values = xtc_db_fetch_array($products_query);
  echo '<a href="popup_image.php?pID='.(int) $_GET['pID'].'&imgID=0" target="_parent">'.xtc_image(DIR_WS_THUMBNAIL_IMAGES.$products_values['products_image'], $products_values['products_name']).'</a>';
  echo '</td>';
  if ($mo_images != false) {
    foreach ($mo_images as $mo_img) {
      if ($mo_img['image_nr'] == (int) $_GET['imgID']) {
          $actual = ' style="background: #ff0000;"';
      } else {
          $actual = '';
      }
      echo '<td align=left'.$actual.'><a href="popup_image.php?pID='.(int) $_GET['pID'].'&imgID='.$mo_img['image_nr'].'" target="_parent">'.xtc_image(DIR_WS_THUMBNAIL_IMAGES.$mo_img['image_name'], $products_values['products_name']).'</a></td>';
    }
  }
?>
</tr>
</table>
</body>