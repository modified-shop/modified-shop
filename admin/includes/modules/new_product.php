<?php
/* --------------------------------------------------------------
   $Id: new_product.php 3072 2012-06-18 15:01:13Z hhacker $

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

  $confirm_save_entry = 'onclick="return confirm(\''. SAVE_ENTRY .'\')"';
  if (defined('CONFIRM_SAVE_ENTRY')) {
    $confirm_save_entry = CONFIRM_SAVE_ENTRY == 'true' ? $confirm_save_entry : '';
  }

  if (isset($_GET['pID']) && (!$_POST)) {
    $product_query = xtc_db_query("SELECT *,
                                          date_format(p.products_date_available, '%Y-%m-%d') as products_date_available
                                     FROM ".TABLE_PRODUCTS." p,
                                          ".TABLE_PRODUCTS_DESCRIPTION." pd
                                    WHERE p.products_id = '".(int) $_GET['pID']."'
                                      AND p.products_id = pd.products_id
                                      AND pd.language_id = '".$_SESSION['languages_id']."'");
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
  }

  $manufacturers_array = array (array ('id' => '', 'text' => TEXT_NONE));
  $manufacturers_query = xtc_db_query("SELECT manufacturers_id, manufacturers_name FROM ".TABLE_MANUFACTURERS." ORDER BY manufacturers_name");
  while ($manufacturers = xtc_db_fetch_array($manufacturers_query)) {
    $manufacturers_array[] = array ('id' => $manufacturers['manufacturers_id'], 'text' => $manufacturers['manufacturers_name']);
  }

  $vpe_array = array (array ('id' => '', 'text' => TEXT_NONE));
  $vpe_query = xtc_db_query("SELECT products_vpe_id, products_vpe_name FROM ".TABLE_PRODUCTS_VPE." WHERE language_id='".$_SESSION['languages_id']."' ORDER BY products_vpe_name");
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

  $status = $pInfo->products_status == '0' ? false : true;

  $product_status_array = array(array('id'=>0,'text'=>TEXT_PRODUCT_NOT_AVAILABLE),
                                array('id'=>1,'text'=>TEXT_PRODUCT_AVAILABLE)
                               );

  //if ($pInfo->products_startpage == '1') { $startpage_checked = true; } else { $startpage_checked = false; }

?>
<link rel="stylesheet" type="text/css" href="includes/javascript/spiffyCal/spiffyCal_v2_1.css">
<script type="text/javascript" src="includes/javascript/spiffyCal/spiffyCal_v2_1.js"></script>
<script type="text/javascript">
  var dateAvailable = new ctlSpiffyCalendarBox("dateAvailable", "new_product", "products_date_available","btnDate1","<?php echo $pInfo->products_date_available; ?>",2);
</script>

<tr>
  <td>
  <?php
  $form_action = isset($_GET['pID']) ? 'update_product' : 'insert_product';
  echo xtc_draw_form('new_product', FILENAME_CATEGORIES, 'cPath=' . $_GET['cPath'] . $catfunc->page_parameter . '&pID=' . $_GET['pID'] . '&action='.$form_action, 'post', 'enctype="multipart/form-data"');
  ?>
  <span class="pageHeading"><?php echo sprintf(TEXT_NEW_PRODUCT, xtc_output_generated_category_path($current_category_id)); ?></span><br />

<div style="width: 860px; padding:5px;">
<table bgcolor="f3f3f3" style="width: 100%; border: 1px solid; border-color: #aaaaaa; padding:5px;">
  <tr>
    <td>
      <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr>
          <td width="58%" valign="top">
            <table width="100%" border="0" cellspacing="0" cellpadding="3">
            <tr>
              <td width="260"><span class="main"><?php echo TEXT_PRODUCTS_STATUS; ?></span></td>
              <td><span class="main"><?php echo xtc_draw_pull_down_menu('products_status', $product_status_array, $status, 'style="width: 135px"'); ?></span></td>
            </tr>
            <tr>
              <td><span class="main"><?php echo TEXT_PRODUCTS_DATE_AVAILABLE; ?> <small><?php echo TEXT_PRODUCTS_DATE_FORMAT; ?></small></span></td>
              <td>
                <span class="main">
                <script type="text/javascript">dateAvailable.writeControl(); dateAvailable.dateFormat="yyyy-MM-dd";</script>
                <noscript>
                <?php echo  xtc_draw_input_field('products_date_available', $pInfo->products_date_available ,'style="width: 135px"'); ?>
                </noscript>
                </span>
              </td>
            </tr>
            <tr>
              <td><span class="main"><?php echo TEXT_PRODUCTS_STARTPAGE; ?></span></td>
              <td><span class="main"><?php echo xtc_draw_selection_field('products_startpage', 'checkbox', '1',isset($pInfo->products_startpage) && $pInfo->products_startpage==1 ? true : false); ?></span></td>
            </tr>
            <tr>
              <td><span class="main"><?php echo TEXT_PRODUCTS_STARTPAGE_SORT; ?></span></td>
              <td><span class="main"><?php echo  xtc_draw_input_field('products_startpage_sort', $pInfo->products_startpage_sort ,'style="width: 135px"'); ?></span></td>
            </tr>
            <tr>
              <td><span class="main"><?php echo TEXT_PRODUCTS_SORT; ?></span></td>
              <td><span class="main"><?php echo  xtc_draw_input_field('products_sort', $pInfo->products_sort,'style="width: 135px"'); ?></span></td>
            </tr>
            <tr>
              <td>
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                  <tr>
                    <td><span class="main"><?php echo TEXT_PRODUCTS_VPE_VISIBLE.xtc_draw_selection_field('products_vpe_status', 'checkbox', '1',$pInfo->products_vpe_status==1 ? true : false);?></span></td>
                    <td align="right"><span class="main"><?php echo TEXT_PRODUCTS_VPE_VALUE; ?></span></td>
                  </tr>
                </table>
              </td>
              <td><span class="main"><?php echo xtc_draw_input_field('products_vpe_value', $pInfo->products_vpe_value,'style="width: 135px"'); ?></span></td>
            </tr>
            <tr>
              <td><span class="main"><?php echo TEXT_PRODUCTS_VPE ?></span></td>
              <td><span class="main"><?php echo xtc_draw_pull_down_menu('products_vpe', $vpe_array, $pInfo->products_vpe=='' ?  DEFAULT_PRODUCTS_VPE_ID : $pInfo->products_vpe, 'style="width: 135px"'); ?></span></td>
            </tr>
            <tr>
              <td><span class="main"><?php echo TEXT_FSK18; ?></span></td>
              <td><span class="main"><?php echo xtc_draw_checkbox_field('fsk18', '1', $pInfo->products_fsk18=='1'); ?></span></td>
            </tr>
          </table>
        </td>
        <td width="4%"><?php echo xtc_draw_separator('pixel_trans.gif', '24', '15'); ?></td>
        <td width="38%" valign="top">
          <table width="100%" border="0" cellspacing="0" cellpadding="3">
            <tr>
              <td><span class="main"><?php echo TEXT_PRODUCTS_QUANTITY; ?></span></td>
              <td><span class="main"><?php echo xtc_draw_input_field('products_quantity', $pInfo->products_quantity, 'style="width: 135px"'); ?></span></td>
            </tr>
            <tr>
              <td><span class="main"><?php echo TEXT_PRODUCTS_MODEL; ?></span></td>
              <td><span class="main"><?php echo  xtc_draw_input_field('products_model', $pInfo->products_model, 'style="width: 135px"'); ?></span></td>
            </tr>
            <tr>
              <td><span class="main"><?php echo TEXT_PRODUCTS_EAN; ?></span></td>
              <td><span class="main"><?php echo  xtc_draw_input_field('products_ean', $pInfo->products_ean, 'style="width: 135px"'); ?></span></td>
            </tr>
            <tr>
              <td><span class="main"><?php echo TEXT_PRODUCTS_MANUFACTURER; ?></span></td>
              <td><span class="main"><?php echo xtc_draw_pull_down_menu('manufacturers_id', $manufacturers_array, $pInfo->manufacturers_id, 'style="width: 135px"'); ?></span></td>
            </tr>
            <tr>
              <td><span class="main"><?php echo TEXT_PRODUCTS_MANUFACTURER_MODEL; ?></span></td>
              <td><span class="main"><?php echo  xtc_draw_input_field('products_manufacturers_model', $pInfo->products_manufacturers_model, 'style="width: 135px"'); ?></span></td>
            </tr>
            <tr>
              <td><span class="main"><?php echo TEXT_PRODUCTS_WEIGHT; ?></span></td>
              <td><span class="main"><?php echo xtc_draw_input_field('products_weight', $pInfo->products_weight, 'style="width: 135px"'); ?>&nbsp;<?php echo TEXT_PRODUCTS_WEIGHT_INFO; ?></span></td>
            </tr>
            <?php if (ACTIVATE_SHIPPING_STATUS=='true') { ?>
            <tr>
              <td><span class="main"><?php echo BOX_SHIPPING_STATUS.':'; ?></span></td>
              <td><span class="main"><?php echo xtc_draw_pull_down_menu('shipping_status', $shipping_statuses, $pInfo->products_shippingtime=='' ? (int)(DEFAULT_SHIPPING_STATUS_ID) : $pInfo->products_shippingtime, 'style="width: 135px"'); ?></span></td>
            </tr>
            <?php } ?>
            <tr>
              <td><span class="main">&nbsp;</span></td>
              <td><span class="main">&nbsp;</span></td>
            </tr>
            <tr>
              <td><span class="main">&nbsp;</span></td>
              <td><span class="main">&nbsp;</span></td>
            </tr>
            </table>
          </td>
        </tr>
      </table>

      <table width="500" border="0" cellpadding="3" cellspacing="0">
        <tr>
          <td width="260"><span class="main">&nbsp;</span></td>
          <td><span class="main">&nbsp;</span></td>
        </tr>
        <tr>
          <td><span class="main"><?php echo TEXT_CHOOSE_INFO_TEMPLATE; ?>:</span></td>
          <td><span class="main"><?php echo $catfunc->create_templates_dropdown_menu('info_template', '/module/product_info/', $pInfo->product_template ,'style="width: 220px"'); ?></span></td>
        </tr>
        <tr>
          <td><span class="main"><?php echo TEXT_CHOOSE_OPTIONS_TEMPLATE; ?>:</span></td>
          <td><span class="main"><?php echo $catfunc->create_templates_dropdown_menu('options_template', '/module/product_options/', $pInfo->options_template, 'style="width: 220px"'); ?></span></td>
        </tr>
        <tr>
          <td><span class="main">&nbsp;</span></td>
          <td><span class="main">&nbsp;</span></td>
        </tr>
      </table>

      <!-- BOF - Tomcraft - 2009-11-06 - Included specials //-->
      <?php
      if (file_exists("includes/modules/categories_specials.php")) {
        require_once("includes/modules/categories_specials.php");
        showSpecialsBox();
      }
      ?>
      <!-- EOF - Tomcraft - 2009-11-06 - Included specials //-->

      <!-- BOF - Tomcraft - 2009-11-02 - TOP SAVE AND CANCEL BUTTON //-->
      <?php
      if (file_exists("includes/modules/categories_specials.php")) { ?>
      <div class="main" style="margin-top: 7px;float:left">
        <div id="butSpecial">&nbsp;</div>
      </div>
      <script language="JavaScript" type="text/JavaScript">
        document.getElementById('butSpecial').innerHTML= '<a href="JavaScript:showSpecial()" class="button">Sonderangebot &raquo;</a>';
      </script>
      <?php } ?>
      <div class="main" style="margin-bottom:10px;float:right;">
        <input type="submit" class="button" value="<?php echo BUTTON_SAVE; ?>" <?php echo $confirm_save_entry;?>>
        &nbsp;&nbsp;
        <input type="submit" class="button" name="prod_update" value="<?php echo BUTTON_UPDATE; ?>" <?php echo $confirm_save_entry;?>>
        <?php
        if (isset($_GET['pID']) && $_GET['pID'] > 0) {
          echo '&nbsp;&nbsp;<a class="button" href="' . xtc_href_link('../product_info.php', 'products_id=' . $_GET['pID']) . '" target="_blank">' . BUTTON_VIEW_PRODUCT . '</a>';
        }
        echo '&nbsp;&nbsp;<a class="button" href="' . xtc_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . $catfunc->page_parameter . '&pID=' . $_GET['pID']) . '">' . BUTTON_CANCEL . '</a>';
        ?>
      </div>
      <!-- EOF - Tomcraft - 2009-11-02 - TOP SAVE AND CANCEL BUTTON //-->


      <!-- BOF - Tomcraft - 2009-11-02 - Block2 //-->
      <div style="width: 860px; padding:5px;clear:both;">
        <link rel="stylesheet" type="text/css" href="includes/lang_tabs_menu/lang_tabs_menu.css">
        <script type="text/javascript" src="includes/lang_tabs_menu/lang_tabs_menu.js"></script>
        <?php
        $langtabs = '<div class="tablangmenu"><ul>';
        $csstabstyle = 'border: 1px solid #aaaaaa; padding: 5px; width: 850px; margin-top: -1px; margin-bottom: 10px; float: left;background: #F3F3F3;';
        $csstab = '<style type="text/css">' .  '#tab_lang_0' . '{display: block;' . $csstabstyle . '}';
        $csstab_nojs = '<style type="text/css">';
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $tabtmp = "\'tab_lang_$i\'," ;
          $langtabs.= '<li onclick="showTab('. $tabtmp. $n.')" style="cursor: pointer;" id="tabselect_' . $i .'">' .xtc_image(DIR_WS_LANGUAGES . $languages[$i]['directory'] .'/admin/images/'. $languages[$i]['image'], $languages[$i]['name']) . ' ' . $languages[$i]['name'].  '</li>';
          if($i > 0) $csstab .= '#tab_lang_' . $i .'{display: none;' . $csstabstyle . '}';
          $csstab_nojs .= '#tab_lang_' . $i .'{display: block;' . $csstabstyle . '}';
        }
        $csstab .= '</style>';
        $csstab_nojs .= '</style>';
        $langtabs.= '</ul></div>';
        //echo $csstab;
        //echo $langtabs;
        ?>
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
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          echo ('<div id="tab_lang_' . $i . '">');
          $lng_image = xtc_image(DIR_WS_LANGUAGES . $languages[$i]['directory'] .'/admin/images/'. $languages[$i]['image'], $languages[$i]['name']);
          $products_desc_fields = $catfunc->get_products_desc_fields($pInfo->products_id, $languages[$i]['id']);
          ?>
          <div style="background:#000000;height:10px;"></div>
          <div class="main" style="background:#FFCC33;padding: 3px; line-height:20px;">
            <?php echo $lng_image ?>&nbsp;<b><?php echo TEXT_PRODUCTS_NAME; ?>&nbsp;</b><?php echo xtc_draw_input_field('products_name[' . $languages[$i]['id'] . ']', (($products_name[$languages[$i]['id']]) ? stripslashes($products_name[$languages[$i]['id']]) : $products_desc_fields['products_name']),'style="width:80%" maxlength="255"'); ?>
          </div>
          <div class="main" style="padding: 3px; line-height:20px;">
             <?php echo $lng_image. '&nbsp;'.TEXT_PRODUCTS_URL . '&nbsp;<small>' . TEXT_PRODUCTS_URL_WITHOUT_HTTP . '</small>'; ?><?php echo xtc_draw_input_field('products_url[' . $languages[$i]['id'] . ']', (($products_url[$languages[$i]['id']]) ? stripslashes($products_url[$languages[$i]['id']]) : $products_desc_fields['products_url']),'style="width:70%" maxlength="255"'); ?>
          </div>
          <!-- input boxes desc, meta etc -->
          <div class="main" style="padding: 3px; line-height:20px;">
             <b><?php echo $lng_image . '&nbsp;' . TEXT_PRODUCTS_DESCRIPTION; ?></b><br />
             <?php echo xtc_draw_textarea_field('products_description_' . $languages[$i]['id'], 'soft', '103', '30', (isset($products_description[$languages[$i]['id']]) ? stripslashes($products_description[$languages[$i]['id']]) : $products_desc_fields['products_description'])); ?>
          </div>
          <div style="height: 8px;"></div>
          <div width="100%" valign="top" class="main" style="padding: 3px; line-height:20px;">
            <b><?php echo $lng_image . '&nbsp;' . TEXT_PRODUCTS_SHORT_DESCRIPTION; ?></b><br />
            <?php echo xtc_draw_textarea_field('products_short_description_' . $languages[$i]['id'], 'soft', '103', '20', (isset($products_short_description[$languages[$i]['id']]) ? stripslashes($products_short_description[$languages[$i]['id']]) : $products_desc_fields['products_short_description'])); ?>
          </div>
          <div valign="top" class="main" style="padding: 3px; line-height:20px;">
            <b><?php echo $lng_image . '&nbsp;' . TEXT_PRODUCTS_ORDER_DESCRIPTION; ?></b><br />
            <?php echo xtc_draw_textarea_field('products_order_description[' . $languages[$i]['id'] . ']', 'soft', '103', '10', (isset($products_order_description[$languages[$i]['id']]) ? stripslashes($products_order_description[$languages[$i]['id']]) : $products_desc_fields['products_order_description']), 'style="width:100%; height:50px;"'); ?>
          </div>
          <div class="main" valign="top" style="padding: 3px; line-height:20px;">
              <?php echo $lng_image. '&nbsp;'. TEXT_PRODUCTS_KEYWORDS . ' (max. 255 '. TEXT_CHARACTERS .')'; ?> <br/>
              <?php echo xtc_draw_input_field('products_keywords[' . $languages[$i]['id'] . ']',(isset($products_keywords[$languages[$i]['id']]) ? stripslashes($products_keywords[$languages[$i]['id']]) : $products_desc_fields['products_keywords']), 'style="width:100%" maxlength="255"'); ?><br/>
              <?php echo $lng_image. '&nbsp;'. TEXT_META_TITLE. ' (max. 50 '. TEXT_CHARACTERS .')'; ?> <br/>
              <?php echo xtc_draw_input_field('products_meta_title[' . $languages[$i]['id'] . ']',(isset($products_meta_title[$languages[$i]['id']]) ? stripslashes($products_meta_title[$languages[$i]['id']]) : $products_desc_fields['products_meta_title']), 'style="width:100%" maxlength="50"'); ?><br/>
              <?php echo $lng_image. '&nbsp;'. TEXT_META_DESCRIPTION. ' (max. 140 '. TEXT_CHARACTERS .')'; ?> <br/>
              <?php echo xtc_draw_input_field('products_meta_description[' . $languages[$i]['id'] . ']',(isset($products_meta_description[$languages[$i]['id']]) ? stripslashes($products_meta_description[$languages[$i]['id']]) : $products_desc_fields['products_meta_description']), 'style="width:100%" maxlength="140"'); ?><br/>
              <?php echo $lng_image. '&nbsp;'. TEXT_META_KEYWORDS. ' (max. 180 '. TEXT_CHARACTERS .')'; ?> <br/>
              <?php echo xtc_draw_input_field('products_meta_keywords[' . $languages[$i]['id'] . ']', (isset($products_meta_keywords[$languages[$i]['id']]) ? stripslashes($products_meta_keywords[$languages[$i]['id']]) : $products_desc_fields['products_meta_keywords']), 'style="width:100%" maxlength="180"'); ?>
          </div>
          <?php
          echo ('</div>');
        } ?>
      </div>

      <div style="clear:both;"></div>

      <div style="width: 860px; padding:5px;">
        <!-- BOF - Tomcraft - 2009-11-02 - Product images //-->
        <div class="main" style="margin:10px 5px 5px 5px"><?php echo HEADING_PRODUCT_IMAGES; ?></div>
          <table width="100%" border="0" bgcolor="f3f3f3" style="border: 1px solid #aaaaaa; padding:5px;">
            <?php
            include (DIR_WS_MODULES.'products_images.php');
            ?>
          </table>
        <!-- EOF - Tomcraft - 2009-11-02 - Product images //-->

        <?php
        //Customers group block
        if (GROUP_CHECK == 'true') {
          ?>
          <div class="main" style="margin:10px 5px 5px 5px;font-weight:bold;"><?php echo BOX_CUSTOMERS_STATUS; ?></div>
          <table width="100%" border="0" bgcolor="f3f3f3" style="border: 1px solid #aaaaaa; padding:5px;">
            <tr>
              <td style="border-top: 0px solid; border-color: #ff0000;" valign="top" class="main" ><?php echo ENTRY_CUSTOMERS_STATUS; ?></td>
              <td style="border: 1px solid #ff0000;"  bgcolor="#FFCC33" class="main">
                <?php
                echo $catfunc->create_permission_checkboxes($product);
                ?>
              </td>
            </tr>
          </table>
          <?php
        }
        ?>

        <?php
        //Price options
        include(DIR_WS_MODULES.'group_prices.php');
        ?>

        <!-- BOF - Tomcraft - 2009-11-02 - Save //-->
        <div style="text-align:right;">
          <?php
          if($form_action == 'insert_product'){
            echo xtc_draw_hidden_field('products_date_added', (($pInfo->products_date_added) ? $pInfo->products_date_added : date('Y-m-d')));
          } else {
            echo xtc_draw_hidden_field('products_last_modified', (($pInfo->products_last_modified) ? $pInfo->products_last_modified : date('Y-m-d')));
          }
          echo xtc_draw_hidden_field('products_id', $pInfo->products_id);
          ?>
          <input type="submit" class="button" value="<?php echo BUTTON_SAVE; ?>" <?php echo $confirm_save_entry;?>>
          &nbsp;&nbsp;
          <input type="submit" class="button" name="prod_update" value="<?php echo BUTTON_UPDATE; ?>" <?php echo $confirm_save_entry;?>>
          <?php
          if (isset($_GET['pID']) && $_GET['pID'] > 0) {
            echo '&nbsp;&nbsp;<a class="button" href="' . xtc_href_link('../product_info.php', 'products_id=' . $_GET['pID']) . '" target="_blank">' . BUTTON_VIEW_PRODUCT . '</a>';
          }
          echo '&nbsp;&nbsp;<a class="button" href="' . xtc_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . $catfunc->page_parameter . '&pID=' . $_GET['pID']) . '">' . BUTTON_CANCEL . '</a>';
          ?>
        </div>
        <!-- EOF - Tomcraft - 2009-11-02 - Save //-->
      </div>
    </td>
  </tr>
</table>
</div>
</form>
</td>
</tr>
