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

clearstatcache();

// show images
if ($_GET['action'] == 'new_product') {

  echo '<div class="main div_header">'.HEADING_PRODUCT_IMAGES.'</div>';
  echo '<div class="div_box">';
  // display images fields:  
  $rowspan = ' rowspan="'. 3 .'"';
  ?>
  <table class="tableConfig borderall">
    <tr>
      <td class="dataTableConfig col-left"><?php echo TEXT_PRODUCTS_IMAGE; ?></td>
      <td class="dataTableConfig col-middle"><?php echo $pInfo->products_image; ?></td>
      <td class="dataTableConfig col-right"<?php echo $rowspan;?>><?php echo $pInfo->products_image ? xtc_image(DIR_WS_CATALOG_THUMBNAIL_IMAGES.$pInfo->products_image, 'Standard Image','','','class="thumbnail-productsimage"') : xtc_draw_separator('pixel_trans.gif', PRODUCT_IMAGE_THUMBNAIL_WIDTH, 10); ?></td>
    </tr>
    <tr>
      <td class="dataTableConfig col-left"><?php echo TEXT_PRODUCTS_IMAGE; ?></td>
      <td class="dataTableConfig col-middle"><?php echo xtc_draw_file_field('products_image', false, 'class="imgupload"'); ?></td>      
    </tr>    
    <tr>
      <td class="dataTableConfig col-left"><?php echo TEXT_DELETE; ?></td>
      <td class="dataTableConfig col-middle"><?php echo xtc_draw_checkbox_field('del_pic', $pInfo->products_image); ?></td>      
    </tr>
  </table>
  
  <?php
  echo xtc_draw_hidden_field('products_previous_image_0', $pInfo->products_image);
  
  // display MO PICS
  if (MO_PICS > 0) {
    $mo_images = xtc_get_products_mo_images($pInfo->products_id);
    for ($i = 0; $i < MO_PICS; $i ++) {
      ?>
      <div class="clear">&nbsp;</div>
      <table class="tableConfig borderall">
        <tr>
          <td class="dataTableConfig col-left"><?php echo TEXT_PRODUCTS_IMAGE.' '. ($i +1); ?></td>
          <td class="dataTableConfig col-middle"><?php echo (isset($mo_images[$i]['image_name']) ? $mo_images[$i]['image_name'] : ''); ?></td>
          <td class="dataTableConfig col-right"<?php echo $rowspan;?>><?php echo (isset($mo_images[$i]['image_name']) ? xtc_image(DIR_WS_CATALOG_THUMBNAIL_IMAGES.$mo_images[$i]['image_name'], 'Image '. ($i +1),'','','class="thumbnail-productsimage"') : xtc_draw_separator('pixel_trans.gif', PRODUCT_IMAGE_THUMBNAIL_WIDTH, 10)); ?></td>
        </tr>
        <tr>
          <td class="dataTableConfig col-left"><?php echo TEXT_PRODUCTS_IMAGE.' '. ($i +1); ?></td>
          <td class="dataTableConfig col-middle"><?php echo xtc_draw_file_field('mo_pics_'.$i, false, 'class="imgupload"'); ?></td>      
        </tr>        
        <tr>
          <td class="dataTableConfig col-left"><?php echo TEXT_DELETE; ?></td>
          <td class="dataTableConfig col-middle"><?php echo xtc_draw_checkbox_field('del_mo_pic[]', (isset($mo_images[$i]['image_name']) ? $mo_images[$i]['image_name'] : '')); ?></td>      
        </tr>
      </table>
      <?php
      echo xtc_draw_hidden_field('products_previous_image_'. ($i +1), (isset($mo_images[$i]['image_name']) ? $mo_images[$i]['image_name'] : ''));
    }
  }
  echo '<div style="clear:both;"></div>';
  echo '</div>';
}
?>

<script type="text/javascript">
//disable empty upload fields - fix ticket #459
$(function() {
    $('#new_product').submit(function( event ) {
        var images = $("[name='products_image'],[name^='mo_pics_']");
        images.each(function() {
            $(this).prop( "disabled", false );
            if ($(this).val() == '') {
                $(this).prop( "disabled", true );
            }
        });
    });
});
</script>