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
                                    and c.categories_id = '" . (int)$_GET['cID'] . "'
                                    AND cd.language_id = '".(int)$_SESSION['languages_id']."'");

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

  $category_status_array = array(array('id' => '1','text'=>TEXT_PRODUCT_AVAILABLE),
                                 array('id' => '0','text'=>TEXT_PRODUCT_NOT_AVAILABLE),
                                 );

  $form_action = isset($_GET['cID']) ? 'update_category' : 'insert_category';    
  echo xtc_draw_form('new_category', FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . (int)$_GET['cID'] . '&action='.$form_action, 'post', 'enctype="multipart/form-data"' . $confirm_submit);
?>
<div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_news.png'); ?></div>
<div class="pageHeading"><?php echo $cInfo->categories_name; ?><br /></div>
<div class="main pdg2 flt-l"><?php echo sprintf($text_new_or_edit, $breadcrumb_html); ?></div>
<div class="clear div_box mrg5">
    <div>
      <table class="tableInput border0">
        <tr>
          <td class="main" style="width:260px"><?php echo TEXT_EDIT_STATUS; ?>:</td>
          <td class="main"><?php echo draw_on_off_selection('status', $category_status_array, ($cInfo->categories_status == '1' ? true : false), 'style="width: 155px"'); ?></td>
        </tr>
        <tr>
          <td class="main"><?php echo TEXT_EDIT_PRODUCT_SORT_ORDER; ?>:</td>
          <td class="main"><?php echo xtc_draw_pull_down_menu('products_sorting',$order_array,((xtc_not_null($cInfo->products_sorting))?$cInfo->products_sorting:$default_value), 'style="width: 155px"'); ?>
                           <?php echo xtc_draw_pull_down_menu('products_sorting2',$order_array_desc,$cInfo->products_sorting2, 'style="width: 155px; margin-left: 5px;"'); ?></td>
        </tr>
        <tr>
          <td class="main"><?php echo TEXT_EDIT_SORT_ORDER; ?></td>
          <td class="main"><?php echo xtc_draw_input_field('sort_order', $cInfo->sort_order, 'style="width: 155px"'); ?></td>
        </tr>
      </table>

      <table class="tableInput border0">
        <tr>
          <td class="main" style="width:260px">&nbsp;</td>
          <td class="main">&nbsp;</td>
        </tr>
        <tr>
          <td><span class="main"><?php echo TEXT_CHOOSE_INFO_TEMPLATE_LISTING; ?>:</span></td>
          <td><span class="main"><?php echo $catfunc->create_templates_dropdown_menu('listing_template','/module/product_listing/',$cInfo->listing_template, 'style="width: 250px"');?></span></td>
        </tr>
        <tr>
          <td><span class="main"><?php echo TEXT_CHOOSE_INFO_TEMPLATE_CATEGORIE; ?>:</span></td>
          <td><span class="main"><?php echo $catfunc->create_templates_dropdown_menu('categories_template','/module/categorie_listing/',$cInfo->categories_template, 'style="width: 250px"');?></span></td>
        </tr>
      </table>
    </div>

    <div style="clear:both;"></div>
    <?php //autoload new_category addons 
    require_once(DIR_FS_INC.'auto_include.inc.php');
    foreach(auto_include(DIR_FS_ADMIN.'includes/extra/modules/new_category/','php') as $file) require ($file);
    ?>

    <?php if (GROUP_CHECK=='true') { ?>
    <div style="padding:4px;">
      <div class="main div_header"><?php echo BOX_CUSTOMERS_STATUS; ?></div>
      <div class="div_box" style="margin-bottom:0;">
        <div class="main flt-l" style="width:265px"><?php echo ENTRY_CUSTOMERS_STATUS; ?></div>
        <div class="main customers-groups">
          <?php
          echo $catfunc->create_permission_checkboxes($category);
          ?>
        </div>
        <div style="clear:both;padding:5px;"></div>
        <div class="main flt-l" style="width:266px">&nbsp;</div>
        <div class="main">
          <?php
          echo xtc_draw_checkbox_field('set_groups_permissions', 1) . ' ' . TEXT_SET_GROUP_PERMISSIONS;
          ?>           
        </div>
        <div style="clear:both"></div>            
      </div>      
    </div>
    <?php } ?>

    <div class="main" style="margin:20px 5px;float:right;">
      <?php echo xtc_draw_hidden_field('categories_date_added', (($cInfo->date_added) ? $cInfo->date_added : date('Y-m-d'))) . xtc_draw_hidden_field('parent_id', $cInfo->parent_id); ?>
      <?php echo xtc_draw_hidden_field('categories_id', $cInfo->categories_id); ?>
      <input type="submit" class="button" name="cat_save" value="<?php echo BUTTON_SAVE; ?>" style="cursor:pointer" <?php echo $confirm_save_entry;?>>&nbsp;&nbsp;
      <?php
      if (isset($_GET['cID']) && $_GET['cID'] > 0) {
        echo '<input type="submit" class="button" name="cat_update" value="'.BUTTON_UPDATE.'" style="cursor:pointer" '.$confirm_save_entry.'/>&nbsp;&nbsp';
      }
      ?>
      <a class="button" onclick="this.blur()" href="<?php echo xtc_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . ((isset($_GET['action']) && $_GET['action']=='edit_category') ? '&cID=' . (int)$_GET['cID'] : '') . ((isset($_GET['page']) && $_GET['page']>'1') ? '&page=' . (int)$_GET['page'] : '')); ?>"><?php echo BUTTON_CANCEL ; ?></a>
    </div>

    <div style="clear:both;"></div>
    <div style="padding:5px;clear:both;">
      <?php
      include('includes/lang_tabs.php');
      for ($i = 0; $i < sizeof($languages); $i++) {
        echo ('<div id="tab_lang_' . $i . '">');
        $lng_image = xtc_image(DIR_WS_LANGUAGES . $languages[$i]['directory'] .'/admin/images/'. $languages[$i]['image'], $languages[$i]['name']);
        $categories_desc_fields = $catfunc->get_categories_desc_fields($cInfo->categories_id, $languages[$i]['id']);
        ?>
        <div class="bg_notice" style="height:5px;"></div>
        <div class="main bg_notice" style="padding:3px; line-height:20px;">
          <?php echo $lng_image ?>&nbsp;<b><?php echo TEXT_EDIT_CATEGORIES_NAME; ?>&nbsp;</b><?php echo xtc_draw_input_field('categories_name[' . $languages[$i]['id'] . ']', (isset($categories_name[$languages[$i]['id']]) ? stripslashes($categories_name[$languages[$i]['id']]) : $categories_desc_fields['categories_name']), 'style="width:80%" maxlength="255"'); ?>
        </div>
        <div class="main" style="padding: 3px; line-height:20px;">
          <?php echo $lng_image ?>&nbsp;<b><?php echo TEXT_EDIT_CATEGORIES_HEADING_TITLE; ?>&nbsp;</b><?php echo xtc_draw_input_field('categories_heading_title[' . $languages[$i]['id'] . ']', (isset($categories_name[$languages[$i]['id']]) ? stripslashes($categories_name[$languages[$i]['id']]) : $categories_desc_fields['categories_heading_title']), 'style="width:80%" maxlength="255"'); ?>
        </div>
        <div class="main" style="padding: 3px; line-height:20px;">
          <b><?php echo $lng_image . '&nbsp;' . TEXT_EDIT_CATEGORIES_DESCRIPTION; ?></b><br />
          <?php echo xtc_draw_textarea_field('categories_description[' . $languages[$i]['id'] . ']', 'soft', '100', '25', (isset($categories_description[$languages[$i]['id']]) ? stripslashes($categories_description[$languages[$i]['id']]) : $categories_desc_fields['categories_description'])); ?>
        </div>
        <div class="main" style="vertical-align:top; padding: 3px; line-height:20px;">
          <?php echo $lng_image . '&nbsp;' . TEXT_META_TITLE .' (max. ' . META_TITLE_LENGTH . ' ' . TEXT_CHARACTERS .')'; ?> <br/>
          <?php echo xtc_draw_input_field('categories_meta_title[' . $languages[$i]['id'] . ']',(isset($categories_meta_title[$languages[$i]['id']]) ? stripslashes($categories_meta_title[$languages[$i]['id']]) : $categories_desc_fields['categories_meta_title']), 'style="width:100%" maxlength="' . META_TITLE_LENGTH . '"'); ?><br/>
          <?php echo $lng_image . '&nbsp;' . TEXT_META_DESCRIPTION .' (max. ' . META_DESCRIPTION_LENGTH . ' ' . TEXT_CHARACTERS .')'; ?> <br/>
          <?php echo xtc_draw_input_field('categories_meta_description[' . $languages[$i]['id'] . ']', (isset($categories_meta_description[$languages[$i]['id']]) ? stripslashes($categories_meta_description[$languages[$i]['id']]) : $categories_desc_fields['categories_meta_description']),'style="width:100%" maxlength="' . META_DESCRIPTION_LENGTH . '"'); ?><br/>
          <?php echo $lng_image . '&nbsp;' . TEXT_META_KEYWORDS .' (max. ' . META_KEYWORDS_LENGTH . ' ' . TEXT_CHARACTERS .')'; ?> <br/>
          <?php echo xtc_draw_input_field('categories_meta_keywords[' . $languages[$i]['id'] . ']',(isset($categories_meta_keywords[$languages[$i]['id']]) ? stripslashes($categories_meta_keywords[$languages[$i]['id']]) : $categories_desc_fields['categories_meta_keywords']),'style="width:100%" maxlength="' . META_KEYWORDS_LENGTH . '"'); ?>
        </div>
      </div>
      <?php } ?>
    </div>

    <div style="clear:both;"></div>
    <div style="padding:5px;">
      <div class="main div_header"><?php echo TEXT_EDIT_CATEGORIES_IMAGE; ?></div>
        <?php
          echo '<div class="div_box">';
          // display images fields:  
          $rowspan = ' rowspan="'. 3 .'"';
          ?>
          <table class="tableConfig borderall">
            <tr>
              <td class="dataTableConfig col-left"><?php echo TEXT_EDIT_CATEGORIES_IMAGE; ?></td>
              <td class="dataTableConfig col-middle"><?php echo $cInfo->categories_image; ?></td>
              <td class="dataTableConfig col-right"<?php echo $rowspan;?>><?php if ($cInfo->categories_image) { ?><img src="<?php echo DIR_WS_CATALOG.'images/categories/'.$cInfo->categories_image; ?>" /><?php } ?></td>
            </tr>
            <tr>
              <td class="dataTableConfig col-left"><?php echo TEXT_EDIT_CATEGORIES_IMAGE; ?></td>
              <td class="dataTableConfig col-middle"><?php echo xtc_draw_file_field('categories_image', false, 'class="imgupload"') . xtc_draw_hidden_field('categories_previous_image', $cInfo->categories_image); ?></td>
            </tr>
            <tr>
              <td class="dataTableConfig col-left"><?php echo TEXT_DELETE; ?></td>
              <td class="dataTableConfig col-middle"><?php echo xtc_draw_checkbox_field('del_cat_pic', 'yes'); ?></td>
            </tr>
          </table>
          <?php
          echo '</div>';
        ?>

      <div class="main" style="margin-top:10px;text-align:right;">
        <?php echo xtc_draw_hidden_field('categories_date_added', (($cInfo->date_added) ? $cInfo->date_added : date('Y-m-d'))) . xtc_draw_hidden_field('parent_id', $cInfo->parent_id); ?>
        <?php echo xtc_draw_hidden_field('categories_id', $cInfo->categories_id); ?>
        <input type="submit" class="button" name="cat_save" value="<?php echo BUTTON_SAVE; ?>" style="cursor:pointer" <?php echo $confirm_save_entry;?>>&nbsp;&nbsp;
        <?php
        if (isset($_GET['cID']) && $_GET['cID'] > 0) {
          echo '<input type="submit" class="button" name="cat_update" value="'.BUTTON_UPDATE.'" style="cursor:pointer" '.$confirm_save_entry.'/>&nbsp;&nbsp';
        }
        ?>
        <a class="button" onclick="this.blur()" href="<?php echo xtc_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . ((isset($_GET['action']) && $_GET['action']=='edit_category') ? '&cID=' . (int)$_GET['cID'] : '') . ((isset($_GET['page']) && $_GET['page']>'1') ? '&page=' . (int)$_GET['page'] : '')); ?>"><?php echo BUTTON_CANCEL ; ?></a>
      </div>
    </div>
  </div>
</form>