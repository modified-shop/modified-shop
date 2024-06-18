<?php
/* --------------------------------------------------------------
   $Id$

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
    $languages = xtc_get_languages();
    
    $mo_images = array();
    for ($l = 0, $n = sizeof($languages); $l < $n; $l++) {
      $mo_images[$languages[$l]['id']] = xtc_get_products_mo_images($pInfo->products_id, $languages[$l]['id']);
    }
       
    for ($i = 0; $i < MO_PICS; $i ++) {
      ?>
      <div class="clear">&nbsp;</div>
      <table class="tableConfig borderall">
        <tr>
          <td class="dataTableConfig col-left"><?php echo TEXT_PRODUCTS_IMAGE.' '. ($i +1); ?></td>
          <td class="dataTableConfig col-middle"><?php echo (isset($mo_images[$_SESSION['languages_id']][$i]['image_name']) ? $mo_images[$_SESSION['languages_id']][$i]['image_name'] : ''); ?></td>
          <td class="dataTableConfig col-right"<?php echo $rowspan;?>><?php echo (isset($mo_images[$_SESSION['languages_id']][$i]['image_name']) ? xtc_image(DIR_WS_CATALOG_THUMBNAIL_IMAGES.$mo_images[$_SESSION['languages_id']][$i]['image_name'], 'Image '. ($i +1),'','','class="thumbnail-productsimage"') : xtc_draw_separator('pixel_trans.gif', PRODUCT_IMAGE_THUMBNAIL_WIDTH, 10)); ?></td>
        </tr>
        <tr>
          <td class="dataTableConfig col-left"><?php echo TEXT_PRODUCTS_IMAGE.' '. ($i +1); ?></td>
          <td class="dataTableConfig col-middle"><?php echo xtc_draw_file_field('mo_pics_'.$i, false, 'class="imgupload"'); ?></td>      
        </tr>        
        <tr>
          <td class="dataTableConfig col-left"><?php echo TEXT_DELETE; ?></td>
          <td class="dataTableConfig col-middle"><?php echo xtc_draw_checkbox_field('del_mo_pic[]', (isset($mo_images[$_SESSION['languages_id']][$i]['image_name']) ? $mo_images[$_SESSION['languages_id']][$i]['image_name'] : '')); ?></td>      
        </tr>
        <?php                
          for ($l = 0, $n = sizeof($languages); $l < $n; $l++) {
            ?>
            <tr>
              <td class="dataTableConfig col-left"><?php echo xtc_image(DIR_WS_LANGUAGES.$languages[$l]['directory'].'/admin/images/'.$languages[$l]['image']) . '&nbsp;' . TEXT_PRODUCTS_IMAGE_TITLE.' '. ($i +1); ?></td>
              <td class="dataTableConfig col-right" colspan="2"><?php echo xtc_draw_input_field('image_title[' . ($i +1) . '][' . $languages[$l]['id'] . ']', isset($mo_images[$languages[$l]['id']][$i]) ? $mo_images[$languages[$l]['id']][$i]['image_title'] : ''); ?></td>
            </tr>
            <tr>
              <td class="dataTableConfig col-left"><?php echo xtc_image(DIR_WS_LANGUAGES.$languages[$l]['directory'].'/admin/images/'.$languages[$l]['image']) . '&nbsp;' . TEXT_PRODUCTS_IMAGE_ALT.' '. ($i +1); ?></td>
              <td class="dataTableConfig col-right" colspan="2"><?php echo xtc_draw_input_field('image_alt[' . ($i +1) . '][' . $languages[$l]['id'] . ']', isset($mo_images[$languages[$l]['id']][$i]) ? $mo_images[$languages[$l]['id']][$i]['image_alt'] : ''); ?></td>
            </tr>
            <?php
          }          
        ?>
      </table>
      <?php
      echo xtc_draw_hidden_field('products_previous_image_'. ($i +1), (isset($mo_images[$_SESSION['languages_id']][$i]['image_name']) ? $mo_images[$_SESSION['languages_id']][$i]['image_name'] : ''));
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