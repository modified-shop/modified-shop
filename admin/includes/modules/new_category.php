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

  $confirm_save_entry = 'onclick="return confirm(\''. SAVE_ENTRY .'\')"';
  if (defined('CONFIRM_SAVE_ENTRY')) {
    $confirm_save_entry = CONFIRM_SAVE_ENTRY == 'true' ? $confirm_save_entry : '';
  }

  if (isset($_GET['cID']) && (!$_POST) ) {
    $category_query = xtc_db_query("select * from " .
                                    TABLE_CATEGORIES . " c, " .
                                    TABLE_CATEGORIES_DESCRIPTION . " cd
                                    where c.categories_id = cd.categories_id
                                    and c.categories_id = '" . $_GET['cID'] . "'");

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

  $text_new_or_edit = ($_GET['action']=='new_category_ACD') ? TEXT_INFO_HEADING_NEW_CATEGORY : TEXT_INFO_HEADING_EDIT_CATEGORY;

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
  $order_array_desc =array(array('id' => 'ASC','text'=>TEXT_SORT_ASC),
                          array('id' => 'DESC','text'=>TEXT_SORT_DESC));
?>
  <tr>
    <td class="pageHeading"><?php echo sprintf($text_new_or_edit, xtc_output_generated_category_path($current_category_id)); ?></td>
    <td class="pageHeading" align="right"><?php echo xtc_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
  </tr>
  <tr>
    <?php
    $form_action = isset($_GET['cID']) ? 'update_category' : 'insert_category';
    echo xtc_draw_form('new_category', FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $_GET['cID'] . '&action='.$form_action, 'post', 'enctype="multipart/form-data"'); ?>
    <td>
      <!-- BOF - Tomcraft - 2009-11-02 - Block1 //-->
      <div style="width: 860px; padding:5px;">
      <table width="100%" border="0" cellpadding="5" cellspacing="0" bgcolor="f3f3f3" style="width: 100%; border: 1px solid; border-color: #aaaaaa; padding: 5px;">
        <tr>
          <td class="main" width="204" valign="top"><?php echo TEXT_EDIT_CATEGORIES_IMAGE; ?></td>
          <td class="main"><?php echo xtc_draw_file_field('categories_image') . '<br />' . xtc_draw_separator('pixel_trans.gif', '24', '15') . xtc_draw_hidden_field('categories_previous_image', $cInfo->categories_image); ?>
            <?php
            if ($cInfo->categories_image) {
              ?>
              <br />
              <img src="<?php echo DIR_WS_CATALOG.'images/categories/'.$cInfo->categories_image; ?>" style="max-width:200px; max-height:200px">
              <br /><?php echo '&nbsp;' .$cInfo->categories_image;
              echo xtc_draw_selection_field('del_cat_pic', 'checkbox', 'yes').TEXT_DELETE;
            } ?>
          </td>
        </tr>
        <tr>
          <td class="main"><?php echo TEXT_EDIT_STATUS; ?>:</td>
          <td class="main"><?php echo xtc_draw_selection_field('status', 'checkbox', '1',$cInfo->categories_status==1 ? true : false); ?></td>
        </tr>
        <tr>
          <td class="main"><?php echo TEXT_EDIT_PRODUCT_SORT_ORDER; ?>:</td>
          <td class="main"><?php echo xtc_draw_pull_down_menu('products_sorting',$order_array,$cInfo->products_sorting, 'style="width: 130px"'); ?>&nbsp;<?php echo xtc_draw_pull_down_menu('products_sorting2',$order_array_desc,$cInfo->products_sorting2); ?></td>
        </tr>
        <tr>
          <td class="main"><?php echo TEXT_EDIT_SORT_ORDER; ?></td>
          <td class="main"><?php echo xtc_draw_input_field('sort_order', $cInfo->sort_order, 'style="width: 130px"'); ?></td>
        </tr>
        <tr>
          <td><span class="main"><?php echo TEXT_CHOOSE_INFO_TEMPLATE_LISTING; ?>:</span></td>
          <td><span class="main"><?php echo $catfunc->create_templates_dropdown_menu('listing_template','/module/product_listing/',$cInfo->listing_template, 'style="width: 200px"');?></span></td>
        </tr>
        <tr>
          <td><span class="main"><?php echo TEXT_CHOOSE_INFO_TEMPLATE_CATEGORIE; ?>:</span></td>
          <td><span class="main"><?php echo $catfunc->create_templates_dropdown_menu('categories_template','/module/categorie_listing/',$cInfo->categories_template, 'style="width: 200px"');?></span></td>
        </tr>
      </table>
      </div>
      <!-- EOF - Tomcraft - 2009-11-02 - Block1 //-->

      <!-- BOF - Tomcraft - 2009-11-02 - Customers group block //-->
      <?php
      if (GROUP_CHECK=='true') {
      ?>
      <div style="width: 860px; padding:5px;">
      <table width="100%" border="0" cellpadding="5" cellspacing="0" bgcolor="f3f3f3" style="width: 100%; border: 1px solid; border-color: #aaaaaa; padding: 5px;">
        <tr>
          <td style="border-top: 0px solid;  border-color: #ff0000;" width="204" valign="top" class="main" ><?php echo ENTRY_CUSTOMERS_STATUS; ?></td>
          <td style="border: 1px solid; border-color: #ff0000;"  bgcolor="#FFCC33" class="main">
            <?php
            echo $catfunc->create_permission_checkboxes($category);
            ?>
          </td>
        </tr>
      </table>
      </div>
      <?php
      }
      ?>
      <!-- EOF - Tomcraft - 2009-11-02 - Customers group block //-->

      <link rel="stylesheet" type="text/css" href="includes/lang_tabs_menu/lang_tabs_menu.css">
      <script type="text/javascript" src="includes/lang_tabs_menu/lang_tabs_menu.js"></script>
      <?php
      $langtabs = '<div class="tablangmenu"><ul>';
      $csstabstyle = 'border: 1px solid #aaaaaa; padding: 5px; width: 850px; margin-top: -1px; margin-bottom: 10px; float: left; background: #f3f3f3;';
      $csstab = '<style type="text/css">' .  '#tab_lang_0' . '{display: block;' . $csstabstyle . '}';
      $csstab_nojs = '<style type="text/css">';
      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        $tabtmp = "\'tab_lang_$i\'," ;
        $langtabs.= '<li onclick="showTab('. $tabtmp. $n.')" style="cursor: pointer;" id="tabselect_' . $i .'">' .xtc_image(DIR_WS_LANGUAGES . $languages[$i]['directory'].'/admin/images/'.$languages[$i]['image'], $languages[$i]['name']) . ' ' . $languages[$i]['name'].  '</li>';
        if($i > 0) $csstab .= '#tab_lang_' . $i .'{display: none;' . $csstabstyle . '}';
        $csstab_nojs .= '#tab_lang_' . $i .'{display: block;' . $csstabstyle . '}';
      }
      $csstab .= '</style>';
      $csstab_nojs .= '</style>';
      $langtabs.= '</ul></div>';
      //echo $csstab;
      //echo $langtabs;
      ?>
      <div style="width: 860px; padding:5px;">
        <?php if (USE_ADMIN_LANG_TABS != 'false') { ?>
        <script type="text/javascript">
          document.write('<?php echo ($csstab);?>');
          document.write('<?php echo ($langtabs);?>');
          //alert ("TEST");
        </script>
        <?php } else echo ($csstab_nojs);?>
        <noscript>
          <?php echo ($csstab_nojs);?>
        </noscript>
        <?php
        for ($i=0; $i<sizeof($languages); $i++) {
          echo ('<div id="tab_lang_' . $i . '">');
          $lng_image = '<div style="float:left;margin-right:5px;">'.xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']).'</div>';
          $categories_desc_fields = $catfunc->get_categories_desc_fields($cInfo->categories_id, $languages[$i]['id']);
        ?>
        <table width="100%" border="0" cellspacing="0" cellpadding="5">
          <tr>
            <td class="main" width="190"><?php echo $lng_image.TEXT_EDIT_CATEGORIES_NAME; ?></td>
            <td class="main"><?php echo xtc_draw_input_field('categories_name[' . $languages[$i]['id'] . ']', (isset($categories_name[$languages[$i]['id']]) ? stripslashes($categories_name[$languages[$i]['id']]) : $categories_desc_fields['categories_name']), 'style="width:100%" maxlength="255"'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo $lng_image.TEXT_EDIT_CATEGORIES_HEADING_TITLE; ?></td>
            <td class="main"><?php echo xtc_draw_input_field('categories_heading_title[' . $languages[$i]['id'] . ']', (isset($categories_name[$languages[$i]['id']]) ? stripslashes($categories_name[$languages[$i]['id']]) : $categories_desc_fields['categories_heading_title']), 'style="width:100%" maxlength="255"'); ?></td>
          </tr>
          <tr>
            <td class="main" valign="top"><?php  echo $lng_image.TEXT_EDIT_CATEGORIES_DESCRIPTION; ?></td>
            <td class="main" valign="top">&nbsp;</td>
          </tr>
          <tr>
            <td class="main" colspan="2"><?php echo xtc_draw_textarea_field('categories_description[' . $languages[$i]['id'] . ']', 'soft', '100', '25', (isset($categories_description[$languages[$i]['id']]) ? stripslashes($categories_description[$languages[$i]['id']]) : $categories_desc_fields['categories_description']), 'style="width:100%"'); ?></td>
          </tr>
            <td class="main" valign="top"><?php  echo $lng_image.TEXT_META_TITLE .'<br /> (max. 50 '. TEXT_CHARACTERS .')'; ?></td>
            <td class="main"><?php echo xtc_draw_input_field('categories_meta_title[' . $languages[$i]['id'] . ']',(isset($categories_meta_title[$languages[$i]['id']]) ? stripslashes($categories_meta_title[$languages[$i]['id']]) : $categories_desc_fields['categories_meta_title']), 'style="width:100%" maxlength="50"'); ?></td>
          <tr>
          </tr>
           <tr>
            <td class="main" valign="top"><?php  echo $lng_image.TEXT_META_DESCRIPTION .'<br /> (max. 140 '. TEXT_CHARACTERS .')'; ?></td>
            <td class="main"><?php echo xtc_draw_input_field('categories_meta_description[' . $languages[$i]['id'] . ']', (isset($categories_meta_description[$languages[$i]['id']]) ? stripslashes($categories_meta_description[$languages[$i]['id']]) : $categories_desc_fields['categories_meta_description']),'style="width:100%" maxlength="140"'); ?></td>
          </tr>
           <tr>
            <td class="main" valign="top"><?php  echo $lng_image.TEXT_META_KEYWORDS .'<br /> (max. 180 '. TEXT_CHARACTERS .')'; ?></td>
            <td class="main"><?php echo xtc_draw_input_field('categories_meta_keywords[' . $languages[$i]['id'] . ']',(isset($categories_meta_keywords[$languages[$i]['id']]) ? stripslashes($categories_meta_keywords[$languages[$i]['id']]) : $categories_desc_fields['categories_meta_keywords']),'style="width:100%" maxlength="180"'); ?></td>
          </tr>
        </table>
        <?php echo ('</div>');?>
        <?php } ?>
      </div>
      <div style="clear:both;"></div>
      <div style="text-align:right;width:850px;">
        <?php echo xtc_draw_hidden_field('categories_date_added', (($cInfo->date_added) ? $cInfo->date_added : date('Y-m-d'))) . xtc_draw_hidden_field('parent_id', $cInfo->parent_id); ?>
        <?php echo xtc_draw_hidden_field('categories_id', $cInfo->categories_id); ?>
        <input type="submit" class="button" name="update_category" value="<?php echo BUTTON_SAVE; ?>" style="cursor:pointer" <?php echo $confirm_save_entry;?>>&nbsp;&nbsp;
        <a class="button" onclick="this.blur()" href="<?php echo xtc_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $_GET['cID']); ?>"><?php echo BUTTON_CANCEL ; ?></a>
      </div>
    </td>
  </form>
  </tr>