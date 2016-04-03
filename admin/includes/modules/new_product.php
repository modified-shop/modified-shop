<?php
/* --------------------------------------------------------------
   $Id: new_product.php 5007 2013-07-04 09:31:37Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(categories.php,v 1.140 2003/03/24); www.oscommerce.com
   (c) 2003 nextcommerce (categories.php,v 1.37 2003/08/18); www.nextcommerce.org
   (c) 2006 XT-Commerce (new_product.php 1193 2010-08-21)

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
  
  $text_new_or_edit = TEXT_EDIT_PRODUCT;
  
  if (isset($_GET['pID']) && (!$_POST)) {
    $product_query = xtc_db_query("SELECT *,
                                          date_format(p.products_date_available, '%Y-%m-%d') as products_date_available
                                     FROM ".TABLE_PRODUCTS." p,
                                          ".TABLE_PRODUCTS_DESCRIPTION." pd
                                    WHERE p.products_id = '".(int) $_GET['pID']."'
                                      AND p.products_id = pd.products_id
                                      AND pd.language_id = '".(int)$_SESSION['languages_id']."'");
    $product = xtc_db_fetch_array($product_query);
    $pInfo = new objectInfo($product);
  } elseif ($_POST) {
    $pInfo = new objectInfo($_POST);
    $products_name = $_POST['products_name'];
    $products_description = $_POST['products_description'];
    $products_short_description = $_POST['products_short_description'];
    $products_order_description = $_POST['products_order_description'];
    $products_keywords = $_POST['products_keywords'];
    $products_meta_title = $_POST['products_meta_title'];
    $products_meta_description = $_POST['products_meta_description'];
    $products_meta_keywords = $_POST['products_meta_keywords'];
    $products_url = $_POST['products_url'];
    $products_startpage_sort = $_POST['products_startpage_sort'];
    $pInfo->products_startpage = $_POST['products_startpage'];
  } else {
    $pInfo = new objectInfo(array ());
    $text_new_or_edit = TEXT_NEW_PRODUCT;
  }

  $prod_quantity_query = xtc_db_query("SELECT products_quantity FROM ".TABLE_PRODUCTS." WHERE products_id = '".$pInfo->products_id."'");
  $prod_quantity = xtc_db_fetch_array($prod_quantity_query);

  $manufacturers_array = array (array ('id' => '', 'text' => TEXT_NONE));
  $manufacturers_query = xtc_db_query("SELECT manufacturers_id, manufacturers_name FROM ".TABLE_MANUFACTURERS." ORDER BY manufacturers_name");
  while ($manufacturers = xtc_db_fetch_array($manufacturers_query)) {
    $manufacturers_array[] = array ('id' => $manufacturers['manufacturers_id'], 'text' => $manufacturers['manufacturers_name']);
  }

  $vpe_array = array (array ('id' => '', 'text' => TEXT_NONE));
  $vpe_query = xtc_db_query("SELECT products_vpe_id, products_vpe_name FROM ".TABLE_PRODUCTS_VPE." WHERE language_id='".(int)$_SESSION['languages_id']."' ORDER BY products_vpe_name");
  while ($vpe = xtc_db_fetch_array($vpe_query)) {
    $vpe_array[] = array ('id' => $vpe['products_vpe_id'], 'text' => $vpe['products_vpe_name']);
  }

  $tax_class_array = array (array ('id' => '0', 'text' => TEXT_NONE));
  $tax_class_query = xtc_db_query("SELECT tax_class_id, tax_class_title FROM ".TABLE_TAX_CLASS." ORDER BY tax_class_title");
  while ($tax_class = xtc_db_fetch_array($tax_class_query)) {
    $tax_class_array[] = array ('id' => $tax_class['tax_class_id'], 'text' => $tax_class['tax_class_title']);
  }

  $shipping_statuses = array ();
  $shipping_statuses = xtc_get_shipping_status();
  $languages = xtc_get_languages();

  $product_status_array = array(array('id'=>1,'text'=>TEXT_PRODUCT_AVAILABLE),
                                array('id'=>0,'text'=>TEXT_PRODUCT_NOT_AVAILABLE),
                               );

  //if ($pInfo->products_startpage == '1') { $startpage_checked = true; } else { $startpage_checked = false; }

  $form_action = isset($_GET['pID']) ? 'update_product' : 'insert_product';
  echo xtc_draw_form('new_product', FILENAME_CATEGORIES, 'cPath=' . $_GET['cPath'] . $catfunc->page_parameter . '&pID=' . $_GET['pID'] . '&action='.$form_action, 'post', 'id="new_product" enctype="multipart/form-data"' . $confirm_submit); 
  echo xtc_draw_hidden_field('products_quantity_before_edit', $prod_quantity['products_quantity']);
  ?>
  <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_news.png'); ?></div>
  <div class="pageHeading"><?php echo $pInfo->products_name; ?><br /></div>
  <div class="main pdg2 flt-l"><?php echo sprintf($text_new_or_edit,$breadcrumb_html); ?></div>
    <div class="clear div_box mrg5">
      <div style="float:left; width:57%; vertical-align:top">
        <table class="tableInput border0">
          <tr>
            <td style="width:260px"><span class="main"><?php echo TEXT_PRODUCTS_STATUS; ?></span></td>
            <td><span class="main"><?php echo draw_on_off_selection('products_status', $product_status_array, ($pInfo->products_status == '0' ? false : true), 'style="width: 155px"'); ?></span></td>
          </tr>
          <tr>
            <td><span class="main"><?php echo TEXT_PRODUCTS_DATE_AVAILABLE; ?> <small><?php echo TEXT_PRODUCTS_DATE_FORMAT; ?></small></span></td>
            <td><span class="main"><?php echo xtc_draw_input_field('products_date_available', isset($pInfo->products_date_available) ? $pInfo->products_date_available : '' ,'id="DatepickerProduct" style="width: 155px"'); ?></span></td>
          </tr>
          <tr>
            <td><span class="main"><?php echo TEXT_PRODUCTS_STARTPAGE; ?></span></td>
            <td><span class="main"><?php echo draw_on_off_selection('products_startpage', 'checkbox', (isset($pInfo->products_startpage) && $pInfo->products_startpage==1 ? true : false)) ?></span></td>
          </tr>
          <tr>
            <td><span class="main"><?php echo TEXT_PRODUCTS_STARTPAGE_SORT; ?></span></td>
            <td><span class="main"><?php echo  xtc_draw_input_field('products_startpage_sort', $pInfo->products_startpage_sort ,'style="width: 155px"'); ?></span></td>
          </tr>
          <tr>
            <td><span class="main"><?php echo TEXT_PRODUCTS_SORT; ?></span></td>
            <td><span class="main"><?php echo  xtc_draw_input_field('products_sort', $pInfo->products_sort,'style="width: 155px"'); ?></span></td>
          </tr>
          <tr>
            <td>
              <span class="main"><div class="flt-l" style="margin-top:8px"><?php echo TEXT_PRODUCTS_VPE_VISIBLE. '</div>&nbsp;' . xtc_draw_pull_down_menu('products_vpe_status', 'checkbox', (isset($pInfo->products_vpe_status) && $pInfo->products_vpe_status==1 ? true : false));?></span>
              <div class="flt-r" style="margin-top:8px">
              <span class="main"><?php echo TEXT_PRODUCTS_VPE_VALUE; ?></span>
              </div>                
            </td>
            <td><span class="main"><?php echo xtc_draw_input_field('products_vpe_value', $pInfo->products_vpe_value,'style="width: 155px"'); ?></span></td>
          </tr>
          <tr>
            <td><span class="main"><?php echo TEXT_PRODUCTS_VPE ?></span></td>
            <td><span class="main"><?php echo xtc_draw_pull_down_menu('products_vpe', $vpe_array, $pInfo->products_vpe=='' ?  DEFAULT_PRODUCTS_VPE_ID : $pInfo->products_vpe, 'style="width: 155px"'); ?></span></td>
          </tr>
          <tr>
            <td><span class="main"><?php echo TEXT_FSK18; ?></span></td>
            <td><span class="main"><?php echo xtc_draw_pull_down_menu('fsk18', 'checkbox', (isset($pInfo->products_fsk18) && $pInfo->products_fsk18==1 ? true : false)); ?></span></td>
          </tr>
        </table>
      </div>
      
      <div style="float:left;width:43%; vertical-align:top">
        <table class="tableInput border0">
          <tr>
            <td style="width:180px"><span class="main"><?php echo TEXT_PRODUCTS_QUANTITY; ?></span></td>
            <td><span class="main"><?php echo xtc_draw_input_field('products_quantity', $pInfo->products_quantity, 'style="width: 155px"'); ?></span></td>
          </tr>
          <tr>
            <td><span class="main"><?php echo TEXT_PRODUCTS_MODEL; ?></span></td>
            <td><span class="main"><?php echo  xtc_draw_input_field('products_model', $pInfo->products_model, 'style="width: 155px"'); ?></span></td>
          </tr>
          <tr>
            <td><span class="main"><?php echo TEXT_PRODUCTS_EAN; ?></span></td>
            <td><span class="main"><?php echo  xtc_draw_input_field('products_ean', $pInfo->products_ean, 'style="width: 155px"'); ?></span></td>
          </tr>
          <tr>
            <td><span class="main"><?php echo TEXT_PRODUCTS_MANUFACTURER; ?></span></td>
            <td><span class="main"><?php echo xtc_draw_pull_down_menu('manufacturers_id', $manufacturers_array, $pInfo->manufacturers_id, 'style="width: 155px"'); ?></span></td>
          </tr>
          <tr>
            <td><span class="main"><?php echo TEXT_PRODUCTS_MANUFACTURER_MODEL; ?></span></td>
            <td><span class="main"><?php echo  xtc_draw_input_field('products_manufacturers_model', $pInfo->products_manufacturers_model, 'style="width: 155px"'); ?></span></td>
          </tr>
          <tr>
            <td><span class="main"><?php echo TEXT_PRODUCTS_WEIGHT; ?><?php echo TEXT_PRODUCTS_WEIGHT_INFO; ?></span></td>
            <td><span class="main"><?php echo xtc_draw_input_field('products_weight', $pInfo->products_weight, 'style="width: 155px"'); ?></span></td>
          </tr>
          <?php if (ACTIVATE_SHIPPING_STATUS=='true') { ?>
          <tr>
            <td><span class="main"><?php echo BOX_SHIPPING_STATUS.':'; ?></span></td>
            <td><span class="main"><?php echo xtc_draw_pull_down_menu('shipping_status', $shipping_statuses, $pInfo->products_shippingtime=='' ? (int)(DEFAULT_SHIPPING_STATUS_ID) : $pInfo->products_shippingtime, 'style="width: 155px"'); ?></span></td>
          </tr>
          <?php } ?>
          <tr>
            <td><span class="main">&nbsp;</span></td>
            <td><span class="main">&nbsp;</span></td>
          </tr>            
        </table>
      </div>
      
    <div style="clear:both;"></div>
    <table class="tableInput border0">
      <tr>
        <td style="width:260px"><span class="main"><?php echo TEXT_CHOOSE_INFO_TEMPLATE; ?>:</span></td>
        <td><span class="main"><?php echo $catfunc->create_templates_dropdown_menu('info_template', '/module/product_info/', $pInfo->product_template ,'style="width: 250px"'); ?></span></td>
      </tr>
      <tr>
        <td><span class="main"><?php echo TEXT_CHOOSE_OPTIONS_TEMPLATE; ?>:</span></td>
        <td><span class="main"><?php echo $catfunc->create_templates_dropdown_menu('options_template', '/module/product_options/', $pInfo->options_template, 'style="width: 250px"'); ?></span></td>
      </tr>
    </table>
    
    <?php 
    //autoload new product addons 
    require_once(DIR_FS_INC.'auto_include.inc.php');
    foreach(auto_include(DIR_FS_ADMIN.'includes/extra/modules/new_product/','php') as $file) require ($file);
    
    //products tags
    if (!is_file('includes/modules/products_tags_iframe.php')) {
      include(DIR_WS_MODULES.'products_tags.php');
    }

    //Price options
    include(DIR_WS_MODULES.'group_prices.php');
    
    // customers group
    if (GROUP_CHECK=='true') {?>
    <div style="padding:4px;">
      <div class="main div_header"><?php echo BOX_CUSTOMERS_STATUS; ?></div>
      <div class="div_box" style="margin-bottom:0;">
        <div class="main flt-l" style="width:175px;"><?php echo ENTRY_CUSTOMERS_STATUS; ?></div>
        <div class="main customers-groups">
          <?php
          echo $catfunc->create_permission_checkboxes($product);
          ?>
        </div>
        <div style="clear:both;"></div>
      </div>      
    </div>
    <?php } ?>
          
    <div class="main" style="margin:20px 5px;float:right;">
      <input type="submit" class="button" value="<?php echo BUTTON_SAVE; ?>" <?php echo $confirm_save_entry;?>>
      <?php
      if (isset($_GET['pID']) && $_GET['pID'] > 0) {
        echo '&nbsp;&nbsp;<input type="submit" class="button" name="prod_update" value="'.BUTTON_UPDATE.'" '.$confirm_save_entry.'/>';
        if (is_file('includes/modules/products_tags_iframe.php')) {
          include_once("includes/modules/products_tags_iframe.php");
          if (function_exists('tags_iframe_link')) {
            echo '&nbsp;&nbsp;'.tags_iframe_link($_GET['pID']);
          } 
        }
        if (is_file('includes/modules/products_attributes_iframe.php')) {
          include_once("includes/modules/products_attributes_iframe.php");
        }
        if (function_exists('attributes_iframe_link')) {
          echo '&nbsp;&nbsp;'.attributes_iframe_link($_GET['pID']);
        } else {
          echo '&nbsp;&nbsp;<a class="button" href="' . xtc_href_link('new_attributes.php','cpath='. $cPath . $catfunc->page_parameter.'&current_product_id='.$_GET['pID'].'&action=edit&oldaction=new_product').'" onclick="this.blur()">'.BUTTON_EDIT_ATTRIBUTES.'</a>';
        }
        echo '&nbsp;&nbsp;<a class="button" href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action')) . 'last_action='.$_GET['action'].'&action=new_products_content'.'&set=product') . '">' . BUTTON_NEW_CONTENT . '</a>';
        echo '&nbsp;&nbsp;<a class="button" href="' . xtc_catalog_href_link('product_info.php', 'products_id=' . $_GET['pID']) . '" target="_blank">' . BUTTON_VIEW_PRODUCT . '</a>';
      }
      echo '&nbsp;&nbsp;<a class="button" href="' . xtc_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . $catfunc->page_parameter . ((isset($_GET['pID']) && $_GET['pID']!='') ? '&pID=' . (int)$_GET['pID'] : '')) . '">' . BUTTON_CANCEL . '</a>';
      ?>
    </div>

    <!-- BOF Block2 //-->
    <div style="padding:5px;clear:both;">
      <?php
      include('includes/lang_tabs.php');
      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        echo ('<div id="tab_lang_' . $i . '">');
        $lng_image = xtc_image(DIR_WS_LANGUAGES . $languages[$i]['directory'] .'/admin/images/'. $languages[$i]['image'], $languages[$i]['name']);
        $products_desc_fields = $catfunc->get_products_desc_fields($pInfo->products_id, $languages[$i]['id']);
        ?>
        <div class="bg_notice" style="height:5px;"></div>
        <div class="main bg_notice" style="padding:3px; line-height:20px;">
          <?php echo $lng_image ?>&nbsp;<b><?php echo TEXT_PRODUCTS_NAME; ?>&nbsp;</b><?php echo xtc_draw_input_field('products_name[' . $languages[$i]['id'] . ']', (isset($products_name[$languages[$i]['id']]) ? stripslashes($products_name[$languages[$i]['id']]) : $products_desc_fields['products_name']),'style="width:80%" maxlength="255"'); ?>
        </div>
        <div class="main" style="padding: 3px; line-height:20px;">
           <?php echo $lng_image. '&nbsp;'.TEXT_PRODUCTS_URL . '&nbsp;<small>' . TEXT_PRODUCTS_URL_WITHOUT_HTTP . '</small>&nbsp;'; ?><?php echo xtc_draw_input_field('products_url[' . $languages[$i]['id'] . ']', (isset($products_url[$languages[$i]['id']]) ? stripslashes($products_url[$languages[$i]['id']]) : $products_desc_fields['products_url']),'style="width:70%" maxlength="255"'); ?>
        </div>
        <!-- input boxes desc, meta etc -->
        <div class="main" style="padding: 3px; line-height:20px;">
           <b><?php echo $lng_image . '&nbsp;' . TEXT_PRODUCTS_DESCRIPTION; ?></b><br />
           <?php echo xtc_draw_textarea_field('products_description[' . $languages[$i]['id'] . ']', 'soft', '103', '30', (isset($products_description[$languages[$i]['id']]) ? stripslashes($products_description[$languages[$i]['id']]) : $products_desc_fields['products_description'])); ?>
        </div>
        <div style="height: 8px;"></div>
        <div class="main" style="vertical-align:top; padding: 3px; line-height:20px;">
          <b><?php echo $lng_image . '&nbsp;' . TEXT_PRODUCTS_SHORT_DESCRIPTION; ?></b><br />
          <?php echo xtc_draw_textarea_field('products_short_description[' . $languages[$i]['id'] . ']', 'soft', '103', '20', (isset($products_short_description[$languages[$i]['id']]) ? stripslashes($products_short_description[$languages[$i]['id']]) : $products_desc_fields['products_short_description'])); ?>
        </div>
        <div class="main" style="vertical-align:top; padding: 3px; line-height:20px;">
          <b><?php echo $lng_image . '&nbsp;' . TEXT_PRODUCTS_ORDER_DESCRIPTION; ?></b><br />
          <?php echo xtc_draw_textarea_field('products_order_description[' . $languages[$i]['id'] . ']', 'soft', '103', '10', (isset($products_order_description[$languages[$i]['id']]) ? stripslashes($products_order_description[$languages[$i]['id']]) : $products_desc_fields['products_order_description']), 'style="width:100%; height:50px;"'); ?>
        </div>
        <div class="main" style="vertical-align:top; padding: 3px; line-height:20px;">
            <?php echo $lng_image. '&nbsp;'. TEXT_PRODUCTS_KEYWORDS . ' (max. ' . META_PRODUCTS_KEYWORDS_LENGTH . ' ' . TEXT_CHARACTERS .')'; ?> <br/>
            <?php echo xtc_draw_input_field('products_keywords[' . $languages[$i]['id'] . ']',(isset($products_keywords[$languages[$i]['id']]) ? stripslashes($products_keywords[$languages[$i]['id']]) : $products_desc_fields['products_keywords']), 'style="width:100%" maxlength="' . META_PRODUCTS_KEYWORDS_LENGTH . '"'); ?><br/>
            <?php echo $lng_image. '&nbsp;'. TEXT_META_TITLE. ' (max. ' . META_TITLE_LENGTH . ' ' . TEXT_CHARACTERS .')'; ?> <br/>
            <?php echo xtc_draw_input_field('products_meta_title[' . $languages[$i]['id'] . ']',(isset($products_meta_title[$languages[$i]['id']]) ? stripslashes($products_meta_title[$languages[$i]['id']]) : $products_desc_fields['products_meta_title']), 'style="width:100%" maxlength="' . META_TITLE_LENGTH . '"'); ?><br/>
            <?php echo $lng_image. '&nbsp;'. TEXT_META_DESCRIPTION. ' (max. ' . META_DESCRIPTION_LENGTH . ' ' . TEXT_CHARACTERS .')'; ?> <br/>
            <?php echo xtc_draw_input_field('products_meta_description[' . $languages[$i]['id'] . ']',(isset($products_meta_description[$languages[$i]['id']]) ? stripslashes($products_meta_description[$languages[$i]['id']]) : $products_desc_fields['products_meta_description']), 'style="width:100%" maxlength="' . META_DESCRIPTION_LENGTH . '"'); ?><br/>
            <?php echo $lng_image. '&nbsp;'. TEXT_META_KEYWORDS. ' (max. ' . META_KEYWORDS_LENGTH . ' ' . TEXT_CHARACTERS .')'; ?> <br/>
            <?php echo xtc_draw_input_field('products_meta_keywords[' . $languages[$i]['id'] . ']', (isset($products_meta_keywords[$languages[$i]['id']]) ? stripslashes($products_meta_keywords[$languages[$i]['id']]) : $products_desc_fields['products_meta_keywords']), 'style="width:100%" maxlength="' . META_KEYWORDS_LENGTH . '"'); ?>
        </div>
        <?php
        
        if (file_exists("includes/modules/new_products_content.php")) {
          include("includes/modules/new_products_content.php");
        }

        echo ('</div>');
      } ?>
    </div>
    <!-- EOF Block2 //-->

    <div style="clear:both;"></div>

    <?php
    if (file_exists("includes/modules/new_products_content.php")) {
      include_once("includes/modules/new_products_content.php");
    }
    ?>

    <div style="padding:5px;">
       <!-- BOF Product images //-->
        <?php
          include (DIR_WS_MODULES.'products_images.php');
        ?>
      <div style="clear:both;"></div>          
      <!-- EOF Product images //-->
      
      <!-- BOF Save //-->
      <div style="text-align:right; margin-top:10px;">
        <?php
        if ($form_action == 'insert_product') {
          echo xtc_draw_hidden_field('products_date_added', (($pInfo->products_date_added) ? $pInfo->products_date_added : date('Y-m-d')));
        } else {
          echo xtc_draw_hidden_field('products_last_modified', (($pInfo->products_last_modified) ? $pInfo->products_last_modified : date('Y-m-d')));
        }
        echo xtc_draw_hidden_field('products_id', $pInfo->products_id);
        echo '<input type="submit" class="button" value="'.BUTTON_SAVE.'" '.$confirm_save_entry.'/>';
        if (isset($_GET['pID']) && $_GET['pID'] > 0) {
          echo '&nbsp;&nbsp;<input type="submit" class="button" name="prod_update" value="'.BUTTON_UPDATE.'" '.$confirm_save_entry.'/>';
          echo '&nbsp;&nbsp;<a class="button" href="' . xtc_catalog_href_link('product_info.php', 'products_id=' . $_GET['pID']) . '" target="_blank">' . BUTTON_VIEW_PRODUCT . '</a>';
        }
        echo '&nbsp;&nbsp;<a class="button" href="' . xtc_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . $catfunc->page_parameter . ((isset($_GET['pID']) && $_GET['pID']!='') ? '&pID=' . (int)$_GET['pID'] : '')) . '">' . BUTTON_CANCEL . '</a>';
        ?>
      </div>
      <!-- EOF Save //-->
    </div>
  </div>
</form>