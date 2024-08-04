<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(manufacturers.php,v 1.52 2003/03/22); www.oscommerce.com
   (c) 2003	nextcommerce (manufacturers.php,v 1.9 2003/08/18); www.nextcommerce.org
   (c) 2006 XT-Commerce (manufacturers.php 901 2005-04-29)

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  require('includes/application_top.php');
  
  // include needed function
  require_once (DIR_FS_INC.'xtc_wysiwyg.inc.php');
  
  // include needed classes
  require_once (DIR_WS_CLASSES.FILENAME_IMAGEMANIPULATOR);
  require_once (DIR_WS_CLASSES.'categories.php');

  $catfunc = new categories();
  
  // languages
  $languages = xtc_get_languages(); 

  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  $page = (isset($_GET['page']) ? (int)$_GET['page'] : 1);
  $sorting = (isset($_GET['sorting']) ? $_GET['sorting'] : '');

  //display per page
  $cfg_max_display_results_key = 'MAX_DISPLAY_MANUFACTURERS_RESULTS';
  $page_max_display_results = xtc_cfg_save_max_display_results($cfg_max_display_results_key);

  $manufacturers_status_array = array(
    array('id' => 1, 'text' => TEXT_MANUFACTURER_AVAILABLE),
    array('id' => 0, 'text' => TEXT_MANUFACTURER_NOT_AVAILABLE),
  );

  $order_array = array(
    array('id' => 'p.products_price', 'text' => TXT_PRICES),
    array('id' => 'pd.products_name', 'text' => TXT_NAME),
    array('id' => 'p.products_date_added', 'text' => TXT_DATE),
    array('id' => 'p.products_model', 'text' => TXT_MODEL),
    array('id' => 'p.products_ordered', 'text' => TXT_ORDERED),
    array('id' => 'p.products_sort', 'text' => TXT_SORT),
    array('id' => 'p.products_weight', 'text' => TXT_WEIGHT),
    array('id' => 'p.products_quantity', 'text' => TXT_QTY)
  );
  $default_value = 'pd.products_name';

  $order_array_desc = array(
    array('id' => 'ASC', 'text' => TEXT_SORT_ASC),
    array('id' => 'DESC', 'text' => TEXT_SORT_DESC)
  );

  switch ($action) {
    case 'setflag':
      $manufacturers_id = (int)$_GET['mID'];
      $manufacturers_status = (int)$_GET['flag'];
      xtc_db_query("UPDATE " . TABLE_MANUFACTURERS . " 
                       SET manufacturers_status = '" . xtc_db_input($manufacturers_status) . "' 
                     WHERE manufacturers_id = '" . $manufacturers_id . "'"); 
      xtc_redirect(xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . $page . '&mID=' . $manufacturers_id));
      break;

    case 'insert':
    case 'save':
      $manufacturers_id = ((isset($_GET['mID'])) ? (int)$_GET['mID'] : null);
      $manufacturers_name = xtc_db_prepare_input($_POST['manufacturers_name']);
      $manufacturers_status = (int)$_POST['manufacturers_status'];
      $sort_order = xtc_db_prepare_input($_POST['sort_order']);
      $products_sorting = xtc_db_prepare_input($_POST['products_sorting']);
      $products_sorting2 = xtc_db_prepare_input($_POST['products_sorting2']);
      $listing_template = xtc_db_prepare_input($_POST['listing_template']);
      $categories_template = xtc_db_prepare_input($_POST['categories_template']);

      $sql_data_array = array(
        'manufacturers_name' => $manufacturers_name,
        'manufacturers_status' => $manufacturers_status,
        'sort_order' => $sort_order,
        'products_sorting' => $products_sorting,
        'products_sorting2' => $products_sorting2,
        'listing_template' => $listing_template,
        'categories_template' => $categories_template,
      );

      if ($action == 'insert') {
        $insert_sql_data = array('date_added' => 'now()');
        $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
        xtc_db_perform(TABLE_MANUFACTURERS, $sql_data_array);
        $manufacturers_id = xtc_db_insert_id();
      } elseif ($action == 'save') {
        $update_sql_data = array('last_modified' => 'now()');
        $sql_data_array = array_merge($sql_data_array, $update_sql_data);
        xtc_db_perform(TABLE_MANUFACTURERS, $sql_data_array, 'update', "manufacturers_id = '" . (int)$manufacturers_id . "'");
      }

      //delete manufacturers_image
      if (isset($_POST['delete_image']) && $_POST['delete_image'] == 'on') {
        $manufacturer_query = xtc_db_query("SELECT manufacturers_image 
                                              FROM " . TABLE_MANUFACTURERS . " 
                                             WHERE manufacturers_id = '" . (int)$manufacturers_id . "'");
        $manufacturer = xtc_db_fetch_array($manufacturer_query);

        if (is_file(DIR_FS_CATALOG_IMAGES . 'manufacturers/original_images/' . $manufacturer['manufacturers_image'])) {
          unlink(DIR_FS_CATALOG_IMAGES . 'manufacturers/original_images/' . $manufacturer['manufacturers_image']);
        }

        if (is_file(DIR_FS_CATALOG_IMAGES . 'manufacturers/' . $manufacturer['manufacturers_image'])) {
          unlink(DIR_FS_CATALOG_IMAGES . 'manufacturers/' . $manufacturer['manufacturers_image']);
        }

        xtc_db_query("UPDATE ".TABLE_MANUFACTURERS."
                         SET manufacturers_image = ''
                       WHERE manufacturers_id = '".(int)$manufacturers_id."'");
      }
      
      //store manufacturers_image
      require(DIR_WS_INCLUDES.'upload_types.php');
      
      if ($manufacturers_image = xtc_try_upload('manufacturers_image', DIR_FS_CATALOG_IMAGES.'manufacturers/original_images/', '644', $accepted_image_extensions, $accepted_image_mime_types)) {        
        $manufacturers_image_name_process = $manufacturers_image_name = $manufacturers_image->filename;
        require(DIR_WS_INCLUDES.'manufacturers_image.php');
        
        xtc_db_query("UPDATE ".TABLE_MANUFACTURERS."
                         SET manufacturers_image = '".xtc_db_input($manufacturers_image_name)."'
                       WHERE manufacturers_id = '".(int)$manufacturers_id."'");
      }

      $languages = xtc_get_languages();
      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        $manufacturers_url_array = $_POST['manufacturers_url'];
        $manufacturers_title_array = $_POST['manufacturers_title'];
        $manufacturers_description_array = $_POST['manufacturers_description'];
        $manufacturers_short_description_array = $_POST['manufacturers_short_description'];
        $manufacturers_add_description_array = $_POST['manufacturers_add_description'];
        $manufacturers_meta_title_array = $_POST['manufacturers_meta_title'];
        $manufacturers_meta_description_array = $_POST['manufacturers_meta_description'];
        $manufacturers_meta_keywords_array = $_POST['manufacturers_meta_keywords'];
        $language_id = $languages[$i]['id'];

        $sql_data_array = array(
          'manufacturers_url' => xtc_db_prepare_input($manufacturers_url_array[$language_id]),
          'manufacturers_title' => xtc_db_prepare_input($manufacturers_title_array[$language_id]),
          'manufacturers_description' => xtc_db_prepare_input($manufacturers_description_array[$language_id]),
          'manufacturers_short_description' => xtc_db_prepare_input($manufacturers_short_description_array[$language_id]),
          'manufacturers_add_description' => xtc_db_prepare_input($manufacturers_add_description_array[$language_id]),
          'manufacturers_meta_title' => xtc_db_prepare_input($manufacturers_meta_title_array[$language_id]),
          'manufacturers_meta_description' => xtc_db_prepare_input($manufacturers_meta_description_array[$language_id]),
          'manufacturers_meta_keywords' => xtc_db_prepare_input($manufacturers_meta_keywords_array[$language_id])                    
        );

        if ($action == 'insert') {
          $insert_sql_data = array('manufacturers_id' => $manufacturers_id,
                                   'languages_id' => $language_id);
          $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
          xtc_db_perform(TABLE_MANUFACTURERS_INFO, $sql_data_array);
        } elseif ($action == 'save') {
          $manufacturers_query = xtc_db_query("SELECT * 
                                                 FROM ".TABLE_MANUFACTURERS_INFO." 
                                                WHERE languages_id = '".$language_id."' 
                                                  AND manufacturers_id = '".$manufacturers_id."'");
          if (xtc_db_num_rows($manufacturers_query) == 0) {
            xtc_db_perform(TABLE_MANUFACTURERS_INFO, array('manufacturers_id' => $manufacturers_id , 'languages_id' => $language_id));
          }
          xtc_db_perform(TABLE_MANUFACTURERS_INFO, $sql_data_array, 'update', "manufacturers_id = '" . $manufacturers_id . "' and languages_id = '" . $language_id . "'");
        }
      }

      foreach(auto_include(DIR_FS_ADMIN.'includes/extra/modules/manufacturers/action/','php') as $file) require ($file);
      
      if (isset($_POST['man_update'])) {
        xtc_redirect(xtc_href_link(FILENAME_MANUFACTURERS, 'action=edit&page=' . $page . '&mID=' . $manufacturers_id));
      }
      xtc_redirect(xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . $page . '&mID=' . $manufacturers_id));
      break;

    case 'deleteconfirm':
      $manufacturers_id = xtc_db_prepare_input($_GET['mID']);

      if (isset($_POST['delete_image']) && $_POST['delete_image'] == 'on') {
        $manufacturer_query = xtc_db_query("SELECT * 
                                              FROM " . TABLE_MANUFACTURERS . " 
                                             WHERE manufacturers_id = '" . (int)$manufacturers_id . "'");
        $manufacturer = xtc_db_fetch_array($manufacturer_query);
        $image_location = DIR_FS_CATALOG_IMAGES.'manufacturers/original_images/'.$manufacturer['manufacturers_image'];
        if (is_file($image_location)) {
          unlink($image_location);
        }
        $image_location = DIR_FS_CATALOG_IMAGES.'manufacturers/'.$manufacturer['manufacturers_image'];
        if (is_file($image_location)) {
          unlink($image_location);
        }
      }

      xtc_db_query("DELETE FROM " . TABLE_MANUFACTURERS . " WHERE manufacturers_id = '" . (int)$manufacturers_id . "'");
      xtc_db_query("DELETE FROM " . TABLE_MANUFACTURERS_INFO . " WHERE manufacturers_id = '" . (int)$manufacturers_id . "'");

      if (isset($_POST['delete_products']) && $_POST['delete_products'] == 'on') {
        $products_query = xtc_db_query("SELECT products_id 
                                          FROM " . TABLE_PRODUCTS . " 
                                         WHERE manufacturers_id = '" . (int)$manufacturers_id . "'");
        
        require_once(DIR_WS_CLASSES.'categories.php');
        $tmp_categories = new categories();
        while ($products = xtc_db_fetch_array($products_query)) {
          $tmp_categories->remove_product($products['products_id']);
        }
        unset($tmp_categories);
      } else {
        xtc_db_query("UPDATE " . TABLE_PRODUCTS . " 
                         SET manufacturers_id = '' 
                       WHERE manufacturers_id = '" . (int)$manufacturers_id . "'");
      }

      foreach(auto_include(DIR_FS_ADMIN.'includes/extra/modules/manufacturers/action/','php') as $file) require ($file);

      xtc_redirect(xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . $page));
      break;
  }
  
require (DIR_WS_INCLUDES.'head.php');
?>
<script type="text/javascript" src="includes/general.js"></script>
<script type="text/javascript"> 
  var lang_chars_left = '<?php echo CHARS_LEFT; ?>'; 
  var lang_chars_max = '<?php echo CHARS_MAX; ?>'; 
</script>  
<script type="text/javascript" src="includes/javascript/countdown.js"></script> 
<?php
// Include WYSIWYG if is activated
if (USE_WYSIWYG == 'true') {
	// generate editor 
	echo PHP_EOL . (!function_exists('editorJSLink') ? '<script type="text/javascript" src="includes/modules/fckeditor/fckeditor.js"></script>' : '') . PHP_EOL;
	if ($action == 'edit' || $action == 'new') {
	  for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
      echo xtc_wysiwyg('manufacturers_description', $languages[$i]['code'], $languages[$i]['id']);
      echo xtc_wysiwyg('manufacturers_short_description', $languages[$i]['code'], $languages[$i]['id']);
	  }
	}
}
?>
</head>
<body>
  <!-- header //-->
  <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
  <!-- header_eof //-->
  <!-- body //-->
  <table class="tableBody">
    <tr>
      <?php //left_navigation
      if (USE_ADMIN_TOP_MENU == 'false') {
        echo '<td class="columnLeft2">'.PHP_EOL;
        echo '<!-- left_navigation //-->'.PHP_EOL;       
        require_once(DIR_WS_INCLUDES . 'column_left.php');
        echo '<!-- left_navigation eof //-->'.PHP_EOL; 
        echo '</td>'.PHP_EOL;      
      }
      ?>
      <!-- body_text //-->
      <td class="boxCenter">   
        <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'factory.png'); ?></div>
        <div class="flt-l">
          <div class="pageHeading pdg2"><?php echo HEADING_TITLE; ?></div>
          <div class="main pdg2">Products</div>
        </div>
        <?php
        if ($action == 'edit' || $action == 'new') {
          if ($action == 'new') {
            unset($_GET['mID']);
            $manufact = xtc_get_default_table_data(TABLE_MANUFACTURERS);
          } else {
            $manufact_query = xtc_db_query("SELECT *
                                              FROM " . TABLE_MANUFACTURERS . "
                                             WHERE manufacturers_id='".(int)$_GET['mID']."'
                                           ");
            $manufact = xtc_db_fetch_array($manufact_query);          
          }
          $manufact['manufacturers_image'] = str_replace('manufacturers/', '', $manufact['manufacturers_image']);
          
          echo xtc_draw_form('manufacturers', FILENAME_MANUFACTURERS, 'page=' . $page . ((isset($_GET['mID'])) ? '&mID=' . (int)$_GET['mID'] : ''). '&action='.(($action=='new') ? 'insert' : 'save'), 'post', 'enctype="multipart/form-data"');
          ?>
          <div class="clear div_box mrg5">       
            <table class="tableInput border0">
              <tr>
                <td class="main" style="width:260px"><?php echo TEXT_MANUFACTURER_STATUS; ?></td>
                <td class="main"><?php echo draw_on_off_selection('manufacturers_status', $manufacturers_status_array, ($manufact['manufacturers_status'] == '0' ? false : true), 'style="width: 155px"'); ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo TEXT_MANUFACTURERS_PRODUCT_SORT_ORDER; ?></td>
                <td class="main"><?php echo xtc_draw_pull_down_menu('products_sorting', $order_array, ((isset($manufact['products_sorting'])) ? $manufact['products_sorting'] : $default_value), 'style="width: 155px"'); ?>
                                 <?php echo xtc_draw_pull_down_menu('products_sorting2', $order_array_desc, ((isset($manufact['products_sorting2'])) ? $manufact['products_sorting2'] : ''), 'style="width: 155px; margin-left: 5px;"'); ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo TEXT_MANUFACTURERS_SORT_ORDER; ?></td>
                <td class="main"><?php echo xtc_draw_input_field('sort_order', ((isset($manufact['sort_order'])) ? $manufact['sort_order'] : ''), 'style="width: 155px"'); ?></td>
              </tr>
            </table>

            <table class="tableInput border0">
              <tr>
                <td class="main" style="width:260px">&nbsp;</td>
                <td class="main">&nbsp;</td>
              </tr>
              <tr>
                <td><span class="main"><?php echo TEXT_CHOOSE_INFO_TEMPLATE_LISTING; ?>:</span></td>
                <td><span class="main"><?php echo $catfunc->create_templates_dropdown_menu('listing_template', '/module/product_listing/', $manufact['listing_template'], 'style="width: 250px"');?></span></td>
              </tr>
              <tr>
                <td><span class="main"><?php echo TEXT_CHOOSE_INFO_TEMPLATE_CATEGORIE; ?>:</span></td>
                <td><span class="main"><?php echo $catfunc->create_templates_dropdown_menu('categories_template', '/module/categorie_listing/', $manufact['categories_template'], 'style="width: 250px"');?></span></td>
              </tr>
              <tr>
                <td class="main" style="width:260px">&nbsp;</td>
                <td class="main">&nbsp;</td>
              </tr>
            </table>

            <table class="tableInput border0 bg_notice">
              <tr>
                <td class="main" style="width:260px;"><b><?php echo TEXT_MANUFACTURERS_NAME; ?></b></td>
                <td class="main"><?php echo xtc_draw_input_field('manufacturers_name', ((isset($manufact['manufacturers_name'])) ? $manufact['manufacturers_name'] : ''), 'style="width:100%" maxlength="255"'); ?></td>
              </tr>
            </table>

            <?php 
              foreach(auto_include(DIR_FS_ADMIN.'includes/extra/modules/manufacturers/details/','php') as $file) require ($file);
            ?>    

            <div class="main" style="margin:20px 5px;float:right;">
              <?php 
              echo xtc_button(BUTTON_SAVE) . '&nbsp;&nbsp;';
              if (isset($_GET['mID']) && $_GET['mID'] > 0) {
                echo '<input type="submit" class="button" name="man_update" value="'.BUTTON_UPDATE.'" style="cursor:pointer" />&nbsp;&nbsp;';
                echo '<a class="button" href="' . xtc_catalog_href_link('index.php', 'manufacturers_id=' . (int)$_GET['mID']) . '" target="_blank">' . BUTTON_VIEW_MANUFACTURER . '</a>&nbsp;&nbsp;';
              }
              echo xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . $page . ((isset($_GET['mID'])) ? '&mID=' . (int)$_GET['mID'] : '')));
              ?>
            </div>
            
            <!-- BOF manufacturer description block //-->
            <div style="clear:both;"></div>
            <div style="padding:5px;clear:both;">
              <?php
              include('includes/lang_tabs.php');
              for ($i=0; $i<sizeof($languages); $i++) {
                echo ('<div id="tab_lang_' . $i . '">');
                $lng_image = '<div style="float:left;margin-right:5px;">'.xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']).'</div>';
                if ($action == 'new') {
                  $manufacturer = xtc_get_default_table_data(TABLE_MANUFACTURERS_INFO);
                } else {
                  $manufacturers_query = xtc_db_query("SELECT *
                                                         FROM " . TABLE_MANUFACTURERS_INFO . "
                                                        WHERE manufacturers_id='".(int)$_GET['mID']."'
                                                          AND languages_id='".$languages[$i]['id']."'");
                  $manufacturer = xtc_db_fetch_array($manufacturers_query);
                }
                ?>
                <table class="tableInput border0">
                  <tr>
                    <td class="main" style="width:190px;"><b><?php echo $lng_image.TEXT_MANUFACTURERS_TITLE; ?></b></td>
                    <td class="main"><?php echo xtc_draw_input_field('manufacturers_title[' . $languages[$i]['id'] . ']', ((isset($manufacturer['manufacturers_title'])) ? stripslashes($manufacturer['manufacturers_title']) : ''), 'style="width:99%" maxlength="255"'); ?></td>
                  </tr>
                  <tr>
                    <td class="main"><b><?php echo $lng_image.TEXT_MANUFACTURERS_URL; ?></b></td>
                    <td class="main"><?php echo xtc_draw_input_field('manufacturers_url[' . $languages[$i]['id'] . ']', xtc_get_manufacturer_url($manufacturer['manufacturers_id'], $languages[$i]['id']), 'style="width:99%" maxlength="255"'); ?></td>
                  </tr>
                  <tr>
                    <td class="main" colspan="2"><b><?php  echo $lng_image.TEXT_MANUFACTURERS_DESCRIPTION; ?></b></td>
                  </tr>
                  <tr>
                    <td class="main" colspan="2"><?php echo xtc_draw_textarea_field('manufacturers_description[' . $languages[$i]['id'] . ']', 'soft', '100', '30', ((isset($manufacturer['manufacturers_description'])) ? stripslashes($manufacturer['manufacturers_description']) : ''), 'style="width:99%"'); ?></td>
                  </tr>
                  <tr>
                    <td class="main" colspan="2"><b><?php  echo $lng_image.TEXT_MANUFACTURERS_SHORT_DESCRIPTION; ?></b></td>
                  </tr>
                  <tr>
                    <td class="main" colspan="2"><?php echo xtc_draw_textarea_field('manufacturers_short_description[' . $languages[$i]['id'] . ']', 'soft', '100', '20', ((isset($manufacturer['manufacturers_short_description'])) ? stripslashes($manufacturer['manufacturers_short_description']) : ''), 'style="width:99%"'); ?></td>
                  </tr>
                  <tr>
                    <td class="main" colspan="2"><b><?php  echo $lng_image.TEXT_MANUFACTURERS_ADD_DESCRIPTION; ?></b></td>
                  </tr>
                  <tr>
                    <td class="main" colspan="2"><?php echo xtc_draw_textarea_field('manufacturers_add_description[' . $languages[$i]['id'] . ']', 'soft', '100', '10', ((isset($manufacturer['manufacturers_add_description'])) ? stripslashes($manufacturer['manufacturers_add_description']) : ''), 'style="width:99%"'); ?></td>
                  </tr>
                  <tr>
                    <td class="main" colspan="2"><div style="padding: 3px; line-height:20px">
                      <?php  echo $lng_image.TEXT_META_TITLE .' (max. ' . META_TITLE_LENGTH . ' ' . TEXT_CHARACTERS .')'; ?><br/>
                      <?php echo xtc_draw_input_field('manufacturers_meta_title[' . $languages[$i]['id'] . ']', ((isset($manufacturer['manufacturers_meta_title'])) ? stripslashes($manufacturer['manufacturers_meta_title']) : ''), 'style="width:99%" maxlength="' . META_TITLE_LENGTH . '"'); ?>
                    </div></td>
                  </tr>
                  <tr>
                    <td class="main" colspan="2"><div style="padding: 3px; line-height:20px">
                      <?php  echo $lng_image.TEXT_META_DESCRIPTION .' (max. ' . META_DESCRIPTION_LENGTH . ' ' . TEXT_CHARACTERS .')'; ?><br/>
                      <?php echo xtc_draw_input_field('manufacturers_meta_description[' . $languages[$i]['id'] . ']', ((isset($manufacturer['manufacturers_meta_description'])) ? stripslashes($manufacturer['manufacturers_meta_description']) : ''),'style="width:99%" maxlength="' . META_DESCRIPTION_LENGTH . '"'); ?>
                    </div></td>
                  </tr>
                  <tr>
                    <td class="main" colspan="2"><div style="padding: 3px; line-height:20px">
                      <?php  echo $lng_image.TEXT_META_KEYWORDS .' (max. ' . META_KEYWORDS_LENGTH . ' ' . TEXT_CHARACTERS .')'; ?><br/>
                      <?php echo xtc_draw_input_field('manufacturers_meta_keywords[' . $languages[$i]['id'] . ']', ((isset($manufacturer['manufacturers_meta_keywords'])) ? stripslashes($manufacturer['manufacturers_meta_keywords']) : ''),'style="width:99%" maxlength="' . META_KEYWORDS_LENGTH . '"'); ?>
                    </div></td>
                  </tr>
                </table>
                <?php
                echo ('</div>');
              } ?>
            </div>
            <!-- EOF manufacturer description block //-->

            <!-- BOF manufacturer images block //-->
            <div style="clear:both;"></div>
            <div class="main div_header"><?php echo TEXT_MANUFACTURERS_IMAGE; ?></div>
              <?php
                echo '<div class="div_box">';
                // display images fields:  
                $rowspan = ' rowspan="'. 3 .'"';
                ?>
                <table class="tableConfig borderall">
                  <tr>
                    <td class="dataTableConfig col-left"><?php echo TEXT_MANUFACTURERS_IMAGE; ?></td>
                    <td class="dataTableConfig col-middle"><?php echo $manufact['manufacturers_image']; ?></td>
                    <td class="dataTableConfig col-right"<?php echo $rowspan;?>><?php if (xtc_not_null($manufact['manufacturers_image'])) { echo xtc_info_image('manufacturers/' . $manufact['manufacturers_image'], $manufact['manufacturers_name'], '', '', 'class="thumbnail-manufacturer"'); } ?></td>
                  </tr>
                  <tr>
                    <td class="dataTableConfig col-left"><?php echo TEXT_MANUFACTURERS_IMAGE; ?></td>
                    <td class="dataTableConfig col-middle"><?php echo xtc_draw_file_field('manufacturers_image', false, 'class="imgupload"'); ?></td>
                  </tr>
                  <tr>
                    <td class="dataTableConfig col-left"><?php echo TEXT_DELETE; ?></td>
                    <td class="dataTableConfig col-middle"><?php echo xtc_draw_checkbox_field('delete_image', 'on'); ?></td>
                  </tr>
                </table>
                <?php
                echo '</div>';
              ?>
            <!-- EOF manufacturer images block //-->

            <!-- BOF Save block //-->
            <div style="clear:both;"></div>
            <div class="txta-r">
              <?php 
              echo xtc_button(BUTTON_SAVE) . '&nbsp;&nbsp;';
              if (isset($_GET['mID']) && $_GET['mID'] > 0) {
                echo '<input type="submit" class="button" name="man_update" value="'.BUTTON_UPDATE.'" style="cursor:pointer" />&nbsp;&nbsp;';
                echo '<a class="button" href="' . xtc_catalog_href_link('index.php', 'manufacturers_id=' . (int)$_GET['mID']) . '" target="_blank">' . BUTTON_VIEW_MANUFACTURER . '</a>&nbsp;&nbsp;';
              }
              echo xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . $page . ((isset($_GET['mID'])) ? '&mID=' . (int)$_GET['mID'] : '')));
              ?>
            </div>
            <!-- EOF Save block //-->
          </div>
          </form>
        <?php } else { ?>
          <table class="tableCenter">
            <tr>
              <td class="boxCenterLeft">
                <table class="tableBoxCenter collapse">
                  <tr class="dataTableHeadingRow">
                    <td class="dataTableHeadingContent" width="10%"><?php echo TABLE_HEADING_SORTING.xtc_sorting(FILENAME_MANUFACTURERS, 'sort'); ?></td>
                    <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MANUFACTURERS.xtc_sorting(FILENAME_MANUFACTURERS, 'name'); ?></td>
                    <td class="dataTableHeadingContent txta-c" width="10%"><?php echo TABLE_HEADING_STATUS.xtc_sorting(FILENAME_MANUFACTURERS, 'status'); ?></td>
                    <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                  </tr>
                  <?php
                  if (xtc_not_null($sorting)) {
                    switch ($sorting) {
                      case 'sort':
                        $csort = 'sort_order ASC';
                        break;
                      case 'sort-desc':
                        $csort = 'sort_order DESC';
                        break;
                      case 'name':
                        $csort = 'manufacturers_name ASC';
                        break;
                      case 'name-desc':
                        $csort = 'manufacturers_name DESC';
                        break;
                      case 'status':
                        $csort = 'sort_order ASC';
                        break;
                      case 'status-desc':
                        $csort = 'sort_order DESC';
                        break;
                      default:
                        $csort = 'manufacturers_name ASC';
                        break;
                    }
                    $sort = " ORDER BY ".$csort.", manufacturers_id ASC ";
                  } else {
                    $sort = " ORDER BY manufacturers_name ASC, manufacturers_id ";
                  }
                  $manufacturers_query_raw = "SELECT *
                                                FROM " . TABLE_MANUFACTURERS . " 
                                                     ".$sort;
                  $manufacturers_split = new splitPageResults($page, $page_max_display_results, $manufacturers_query_raw, $manufacturers_query_numrows, 'manufacturers_id', 'mID');
                  $manufacturers_query = xtc_db_query($manufacturers_query_raw);
                  while ($manufacturers = xtc_db_fetch_array($manufacturers_query)) {
                    $manufacturers['manufacturers_image'] = str_replace('manufacturers/', '', $manufacturers['manufacturers_image']);
                    if ((!isset($_GET['mID']) || $_GET['mID'] == $manufacturers['manufacturers_id']) && !isset($mInfo) && substr($action, 0, 3) != 'new') {
                      $manufacturer_products_query = xtc_db_query("SELECT count(*) as products_count 
                                                                     FROM " . TABLE_PRODUCTS . " 
                                                                    WHERE manufacturers_id = '" . $manufacturers['manufacturers_id'] . "'");
                      $manufacturer_products = xtc_db_fetch_array($manufacturer_products_query);
                      $mInfo_array = array_merge($manufacturers, $manufacturer_products);
                      $mInfo = new objectInfo($mInfo_array);                      
                    }

                    if (isset($mInfo) && is_object($mInfo) && $manufacturers['manufacturers_id'] == $mInfo->manufacturers_id) {
                      echo '<tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . $page . '&mID=' . $manufacturers['manufacturers_id'] . '&action=edit') . '\'">' . "\n";
                    } else {
                      echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . $page . '&mID=' . $manufacturers['manufacturers_id']) . '\'">' . "\n";
                    }
                    ?>
                    <td class="dataTableContent"><?php echo $manufacturers['sort_order']; ?></td>
                    <td class="dataTableContent"><?php echo $manufacturers['manufacturers_name']; ?></td>
                    <td class="dataTableContent txta-c">
                      <?php
                      if ($manufacturers['manufacturers_status'] == 1) {
                        echo xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 12, 12, 'style="margin-left: 5px;"') . '<a href="' . xtc_href_link(FILENAME_MANUFACTURERS, xtc_get_all_get_params(array('action', 'mID', 'flag')) . 'action=setflag&flag=0&mID='.$manufacturers['manufacturers_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 12, 12, 'style="margin-left: 5px;"') . '</a>';
                      } else {
                        echo '<a href="' . xtc_href_link(FILENAME_MANUFACTURERS, xtc_get_all_get_params(array('action', 'mID', 'flag')) . 'action=setflag&flag=1&mID='.$manufacturers['manufacturers_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 12, 12, 'style="margin-left: 5px;"') . '</a>' . xtc_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 12, 12, 'style="margin-left: 5px;"');
                      }
                      ?>
                    </td>
                    <td class="dataTableContent txta-r"><?php if (isset($mInfo) && is_object($mInfo) && $manufacturers['manufacturers_id'] == $mInfo->manufacturers_id) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . $page . '&mID=' . $manufacturers['manufacturers_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                  </tr>
                  <?php
                  }
                ?>              
                </table>
                <div class="smallText pdg2 flt-l"><?php echo $manufacturers_split->display_count($manufacturers_query_numrows, $page_max_display_results, $page, TEXT_DISPLAY_NUMBER_OF_MANUFACTURERS); ?></div>
                <div class="smallText pdg2 flt-r"><?php echo $manufacturers_split->display_links($manufacturers_query_numrows, $page_max_display_results, MAX_DISPLAY_PAGE_LINKS, $page); ?></div>
                <?php echo draw_input_per_page($PHP_SELF,$cfg_max_display_results_key,$page_max_display_results); ?>
                <?php
                if ($action != 'new') {
                ?>
                  <div class="smallText pdg2 flt-r"><?php echo xtc_button_link(BUTTON_INSERT, xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . $page . '&action=new')); ?></div>
                <?php
                }
                ?>
              </td>
              <?php
                $heading = array();
                $contents = array();
                switch ($action) {
                              
                  case 'delete':
                    $heading[] = array('text' => '<b>' . TEXT_HEADING_DELETE_MANUFACTURER . '</b>');
                    $contents = array('form' => xtc_draw_form('manufacturers', FILENAME_MANUFACTURERS, 'page=' . $page . '&mID=' . $mInfo->manufacturers_id . '&action=deleteconfirm'));
                    $contents[] = array('text' => TEXT_DELETE_INTRO);
                    $contents[] = array('text' => '<br /><b>' . $mInfo->manufacturers_name . '</b>');
                    $contents[] = array('text' => '<br />' . xtc_draw_checkbox_field('delete_image', '', true) . ' ' . TEXT_DELETE_IMAGE);
                    if ($mInfo->products_count > 0) {
                      $contents[] = array('text' => '<br />' . xtc_draw_checkbox_field('delete_products') . ' ' . TEXT_DELETE_PRODUCTS);
                      $contents[] = array('text' => '<br />' . sprintf(TEXT_DELETE_WARNING_PRODUCTS, $mInfo->products_count));
                    }
                    $contents[] = array('align' => 'center', 'text' => '<br />' . xtc_button(BUTTON_DELETE) . '&nbsp;' . xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . $page . '&mID=' . $mInfo->manufacturers_id)));
                    break;

                  default:
                    if (isset($mInfo) && is_object($mInfo)) {
                      $heading[] = array('text' => '<b>' . $mInfo->manufacturers_name . '</b>');
                      $contents[] = array('align' => 'center', 'text' => xtc_button_link(BUTTON_EDIT, xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . $page . '&mID=' . $mInfo->manufacturers_id . '&action=edit')) . '&nbsp;' . xtc_button_link(BUTTON_DELETE, xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . $page . '&mID=' . $mInfo->manufacturers_id . '&action=delete')));
                      $contents[] = array('text' => '<br />' . TEXT_DATE_ADDED . ' ' . xtc_date_short($mInfo->date_added));
                      if (xtc_not_null($mInfo->last_modified)) {
                        $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . xtc_date_short($mInfo->last_modified));
                      }
                      if (xtc_not_null($mInfo->manufacturers_image)) {
                        $contents[] = array('text' => '<br />' . xtc_info_image('manufacturers/' . $mInfo->manufacturers_image, $mInfo->manufacturers_name, '', '', 'class="thumbnail-manufacturer"'));
                      }
                      $contents[] = array('text' => '<br />' . TEXT_PRODUCTS . ' ' . $mInfo->products_count);
                    }
                    break;
                }

                if ( (xtc_not_null($heading)) && (xtc_not_null($contents)) ) {
                  echo '            <td class="boxRight">' . "\n";
                  $box = new box;
                  echo $box->infoBox($heading, $contents);
                  echo '            </td>' . "\n";
                }
              ?>
            </tr>
          </table>
        <?php } ?>
      </td>
      <!-- body_text_eof //-->
    </tr>
  </table>
  <!-- body_eof //-->
  <!-- footer //-->
  <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
  <!-- footer_eof //-->
  <br />
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>