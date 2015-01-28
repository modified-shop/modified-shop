<?php
/* --------------------------------------------------------------
   $Id: new_category.php 3072 2012-06-18 15:01:13Z hhacker $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(categories.php,v 1.140 2003/03/24); www.oscommerce.com
   (c) 2003  nextcommerce (categories.php,v 1.37 2003/08/18); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------
   Third Party contribution:
   Enable_Disable_Categories 1.3               Autor: Mikel Williams | mikel@ladykatcostumes.com
   New Attribute Manager v4b                   Autor: Mike G | mp3man@internetwork.net | http://downloads.ephing.com
   Category Descriptions (Version: 1.5 MS2)    Original Author:   Brian Lowe <blowe@wpcusrgrp.org> | Editor: Lord Illicious <shaolin-venoms@illicious.net>
   Customers Status v3.x  (c) 2002-2003 Copyright Elari elari@free.fr | www.unlockgsm.com/dload-osc/ | CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/elari/?sortby=date#dirlist

   Released under the GNU General Public License
   --------------------------------------------------------------*/
  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

  //$confirm_save_entry = defined('CONFIRM_SAVE_ENTRY') && CONFIRM_SAVE_ENTRY == 'true' ? ' onclick="return confirm(\''. SAVE_ENTRY .'\')"' : '';
  $confirm_save_entry = ' onclick="ButtonClicked(this);"';
  $confirm_submit = defined('CONFIRM_SAVE_ENTRY') && CONFIRM_SAVE_ENTRY == 'true' ? ' onsubmit="return confirmSubmit(\'\',\''. SAVE_ENTRY .'\',this)"' : '';

  if (isset($_GET['cID']) && (!$_POST) ) {
    $category_query = xtc_db_query("select * from " .
                                    TABLE_CATEGORIES . " c, " .
                                    TABLE_CATEGORIES_DESCRIPTION . " cd
                                    where c.categories_id = cd.categories_id
                                    and c.categories_id = '" . (int)$_GET['cID'] . "'");

    $category = xtc_db_fetch_array($category_query);

    $cInfo = new objectInfo($category);
  } elseif (xtc_not_null($_POST)) {
    $cInfo = new objectInfo($_POST);
    $categories_name = $_POST['categories_name'];
    $categories_heading_title = $_POST['categories_heading_title'];
    $categories_description = $_POST['categories_description'];
    $categories_meta_title = $_POST['categories_meta_title'];
    $categories_meta_description = $_POST['categories_meta_description'];
    $categories_meta_keywords = $_POST['categories_meta_keywords'];
  } else {
    $cInfo = new objectInfo(array());
  }

  $languages = xtc_get_languages();

  $cat_id = '';
  if (!isset($_GET['cID'])) {
    $cat_id_array = xtc_parse_category_path($cPath);
    $cat_id = $cPath_array[(sizeof($cat_id_array) - 1)];
  } else {
    $cat_id = $_GET['cID'];
  }
    
  $text_new_or_edit = ($_GET['action']=='new_category') ? TEXT_INFO_HEADING_NEW_CATEGORY : TEXT_INFO_HEADING_EDIT_CATEGORY;

  $order_array='';
  $order_array=array(array('id' => 'p.products_price','text'=>TXT_PRICES),
                     array('id' => 'pd.products_name','text'=>TXT_NAME),
                     array('id' => 'p.products_date_added','text'=>TXT_DATE),
                     array('id' => 'p.products_model','text'=>TXT_MODEL),
                     array('id' => 'p.products_ordered','text'=>TXT_ORDERED),
                     array('id' => 'p.products_sort','text'=>TXT_SORT),
                     array('id' => 'p.products_weight','text'=>TXT_WEIGHT),
                     array('id' => 'p.products_quantity','text'=>TXT_QTY));
  $default_value='pd.products_name';
  $order_array_desc='';
  $order_array_desc = array(array('id' => 'ASC','text'=>TEXT_SORT_ASC),
                            array('id' => 'DESC','text'=>TEXT_SORT_DESC));

  $category_status_array = array(array('id' => '0','text'=>TEXT_PRODUCT_NOT_AVAILABLE),
                                 array('id' => '1','text'=>TEXT_PRODUCT_AVAILABLE)
                                 );

  $form_action = isset($_GET['cID']) ? 'update_category' : 'insert_category';    
  echo xtc_draw_form('new_category', FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . (int)$_GET['cID'] . '&action='.$form_action, 'post', 'enctype="multipart/form-data"' . $confirm_submit); ?>
?>

  <div class="pageHeading pdg2"><?php echo sprintf($text_new_or_edit, xtc_output_generated_category_path($cat_id)); ?></div>
  <div class="div_box mrg5" style="width:900px;">
    <!-- BOF Category group block //-->
    <div class="main div_header"><?php echo TEXT_CATEGORY_SETTINGS; ?></div>
    <div class="div_box">
      <table class="tableInput border0">
        <tr>
          <td class="main" style="width:260px"><?php echo TEXT_EDIT_STATUS; ?>:</td>
          <td class="main"><?php echo xtc_draw_pull_down_menu('status', $category_status_array, (($cInfo->categories_status == '0') ? false : true), 'style="width: 135px"'); ?></td>
        </tr>
        <tr>
          <td class="main"><?php echo TEXT_EDIT_PRODUCT_SORT_ORDER; ?>:</td>
          <td class="main"><?php echo xtc_draw_pull_down_menu('products_sorting',$order_array,((xtc_not_null($cInfo->products_sorting))?$cInfo->products_sorting:$default_value), 'style="width: 135px"'); ?>&nbsp;<?php echo xtc_draw_pull_down_menu('products_sorting2',$order_array_desc,$cInfo->products_sorting2); ?></td>
        </tr>
        <tr>
          <td class="main"><?php echo TEXT_EDIT_SORT_ORDER; ?></td>
          <td class="main"><?php echo xtc_draw_input_field('sort_order', $cInfo->sort_order, 'style="width: 135px"'); ?></td>
        </tr>
      </table>
      <!-- EOF Category group block //-->

      <!-- BOF Category template group block //-->
      <div style="clear:both;"></div>
      <table class="tableInput border0">
        <tr>
          <td class="main" style="width:260px">&nbsp;</td>
          <td class="main">&nbsp;</td>
        </tr>
        <tr>
          <td><span class="main"><?php echo TEXT_CHOOSE_INFO_TEMPLATE_LISTING; ?>:</span></td>
          <td><span class="main"><?php echo $catfunc->create_templates_dropdown_menu('listing_template','/module/product_listing/',$cInfo->listing_template, 'style="width: 200px"');?></span></td>
        </tr>
        <tr>
          <td><span class="main"><?php echo TEXT_CHOOSE_INFO_TEMPLATE_CATEGORIE; ?>:</span></td>
          <td><span class="main"><?php echo $catfunc->create_templates_dropdown_menu('categories_template','/module/categorie_listing/',$cInfo->categories_template, 'style="width: 200px"');?></span></td>
        </tr>
       <tr>
          <td class="main">&nbsp;</td>
          <td class="main">&nbsp;</td>
        </tr>
      </table>
      <!-- EOF Category template group block //-->
    </div>

    <!-- BOF Autoload new_category addons block //-->
    <div style="clear:both;"></div>
    <?php //autoload new_category addons 
    require_once(DIR_FS_INC.'auto_include.inc.php');
    foreach(auto_include(DIR_FS_ADMIN.'includes/extra/modules/new_category/','php') as $file) require ($file);
    ?>
    <!-- EOF Autoload new_category addons block //-->

    <!-- BOF Customers group block //-->
    <div style="clear:both;"></div>
    <?php if (GROUP_CHECK=='true') {?>
    <div style="padding:4px;">
      <div class="main div_header"><?php echo BOX_CUSTOMERS_STATUS; ?></div>
      <div class="div_box">
        <div class="main flt-l" style="width:214px"><?php echo ENTRY_CUSTOMERS_STATUS; ?></div>
        <div class="main customers-groups">
          <?php
          echo $catfunc->create_permission_checkboxes($category);
          ?>
        </div>
        <div style="clear:both;padding:5px;"></div>
        <div class="main flt-l" style="width:214px">&nbsp;</div>
        <div class="main">
          <?php          
          echo xtc_draw_selection_field('set_groups_permissions', 'checkbox', '1', false). ' ' . TEXT_SET_GROUP_PERMISSIONS;
          ?>           
        </div>
        <div style="clear:both"></div>            
      </div>      
    </div>
    <?php } ?>
    <!-- EOF Customers group block //-->

    <!-- BOF Save block #1 //-->
    <div style="clear:both;"></div>
    <div class="txta-r mrg5">
      <?php echo xtc_draw_hidden_field('categories_date_added', (($cInfo->date_added) ? $cInfo->date_added : date('Y-m-d'))) . xtc_draw_hidden_field('parent_id', $cInfo->parent_id); ?>
      <?php echo xtc_draw_hidden_field('categories_id', $cInfo->categories_id); ?>
      <input type="submit" class="button" name="update_category" value="<?php echo BUTTON_SAVE; ?>" style="cursor:pointer" <?php echo $confirm_save_entry;?>>&nbsp;&nbsp;
      <a class="button" onclick="this.blur()" href="<?php echo xtc_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . ((isset($_GET['action']) && $_GET['action']=='edit_category') ? '&cID=' . (int)$_GET['cID'] : '') . ((isset($_GET['page']) && $_GET['page']>'1') ? '&page=' . (int)$_GET['page'] : '')); ?>"><?php echo BUTTON_CANCEL ; ?></a>
    </div>
    <!-- EOF Save block #1 //-->

    <!-- BOF Categories description block //-->
    <div style="clear:both;"></div>
    <div class="pdg2">
      <?php
      include('includes/lang_tabs.php');
      for ($i=0; $i<sizeof($languages); $i++) {
        echo ('<div id="tab_lang_' . $i . '">');
        $lng_image = '<div style="float:left;margin-right:5px;">'.xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']).'</div>';
        $categories_desc_fields = $catfunc->get_categories_desc_fields($cInfo->categories_id, $languages[$i]['id']);
        ?>
        <table class="tableInput border0">
          <tr>
            <td class="main" style="width:190px;"><?php echo $lng_image.TEXT_EDIT_CATEGORIES_NAME; ?></td>
            <td class="main"><?php echo xtc_draw_input_field('categories_name[' . $languages[$i]['id'] . ']', (isset($categories_name[$languages[$i]['id']]) ? stripslashes($categories_name[$languages[$i]['id']]) : $categories_desc_fields['categories_name']), 'style="width:99%" maxlength="255"'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo $lng_image.TEXT_EDIT_CATEGORIES_HEADING_TITLE; ?></td>
            <td class="main"><?php echo xtc_draw_input_field('categories_heading_title[' . $languages[$i]['id'] . ']', (isset($categories_name[$languages[$i]['id']]) ? stripslashes($categories_name[$languages[$i]['id']]) : $categories_desc_fields['categories_heading_title']), 'style="width:99%" maxlength="255"'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php  echo $lng_image.TEXT_EDIT_CATEGORIES_DESCRIPTION; ?></td>
            <td class="main">&nbsp;</td>
          </tr>
          <tr>
            <td class="main" colspan="2"><?php echo xtc_draw_textarea_field('categories_description[' . $languages[$i]['id'] . ']', 'soft', '100', '25', (isset($categories_description[$languages[$i]['id']]) ? stripslashes($categories_description[$languages[$i]['id']]) : $categories_desc_fields['categories_description']), 'style="width:99%"'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php  echo $lng_image.TEXT_META_TITLE .'<br /> (max. 50 '. TEXT_CHARACTERS .')'; ?></td>
            <td class="main"><?php echo xtc_draw_input_field('categories_meta_title[' . $languages[$i]['id'] . ']',(isset($categories_meta_title[$languages[$i]['id']]) ? stripslashes($categories_meta_title[$languages[$i]['id']]) : $categories_desc_fields['categories_meta_title']), 'style="width:99%" maxlength="50"'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php  echo $lng_image.TEXT_META_DESCRIPTION .'<br /> (max. 140 '. TEXT_CHARACTERS .')'; ?></td>
            <td class="main"><?php echo xtc_draw_input_field('categories_meta_description[' . $languages[$i]['id'] . ']', (isset($categories_meta_description[$languages[$i]['id']]) ? stripslashes($categories_meta_description[$languages[$i]['id']]) : $categories_desc_fields['categories_meta_description']),'style="width:99%" maxlength="140"'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php  echo $lng_image.TEXT_META_KEYWORDS .'<br /> (max. 180 '. TEXT_CHARACTERS .')'; ?></td>
            <td class="main"><?php echo xtc_draw_input_field('categories_meta_keywords[' . $languages[$i]['id'] . ']',(isset($categories_meta_keywords[$languages[$i]['id']]) ? stripslashes($categories_meta_keywords[$languages[$i]['id']]) : $categories_desc_fields['categories_meta_keywords']),'style="width:99%" maxlength="180"'); ?></td>
          </tr>
        </table>
      </div>
      <?php } ?>
    </div>
    <!-- EOF Categories description block //-->

    <!-- BOF Categorie images block //-->
    <div style="clear:both;"></div>
    <div class="main div_header"><?php echo TEXT_EDIT_CATEGORIES_IMAGE; ?></div>
      <?php
        echo '<div class="div_box">';
        // display images fields:  
        $rowspan = ' rowspan="'. 3 .'"';
        ?>
        <table class="tableConfig borderall">
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_EDIT_CATEGORIES_IMAGE; ?></td>
            <td class="dataTableConfig col-middle"><?php echo '&nbsp;' .$cInfo->categories_image; ?></td>
            <td class="dataTableConfig col-right"<?php echo $rowspan;?>><?php if ($cInfo->categories_image) { ?><img src="<?php echo DIR_WS_CATALOG.'images/categories/'.$cInfo->categories_image; ?>" style="max-width:200px; max-height:200px"><?php } ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_EDIT_CATEGORIES_IMAGE; ?></td>
            <td class="dataTableConfig col-middle"><?php echo xtc_draw_file_field('categories_image') . xtc_draw_hidden_field('categories_previous_image', $cInfo->categories_image); ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_DELETE; ?></td>
            <td class="dataTableConfig col-middle"><?php echo xtc_draw_selection_field('del_cat_pic', 'checkbox', 'yes'); ?></td>
          </tr>
        </table>
        <?php
        echo '</div>';
      ?>
    <!-- EOF Categorie images block //-->

    <!-- BOF Save block #2 //-->
    <div style="clear:both;"></div>
    <div class="txta-r mrg5">
      <?php echo xtc_draw_hidden_field('categories_date_added', (($cInfo->date_added) ? $cInfo->date_added : date('Y-m-d'))) . xtc_draw_hidden_field('parent_id', $cInfo->parent_id); ?>
      <?php echo xtc_draw_hidden_field('categories_id', $cInfo->categories_id); ?>
      <input type="submit" class="button" name="update_category" value="<?php echo BUTTON_SAVE; ?>" style="cursor:pointer" <?php echo $confirm_save_entry;?>>&nbsp;&nbsp;
      <a class="button" onclick="this.blur()" href="<?php echo xtc_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . ((isset($_GET['action']) && $_GET['action']=='edit_category') ? '&cID=' . (int)$_GET['cID'] : '') . ((isset($_GET['page']) && $_GET['page']>'1') ? '&page=' . (int)$_GET['page'] : '')); ?>"><?php echo BUTTON_CANCEL ; ?></a>
    </div>
    <!-- EOF Save block #2 //-->
  </div>
</form>