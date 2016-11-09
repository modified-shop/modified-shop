<?php
  /* --------------------------------------------------------------
   $Id: manufacturers.php 5850 2013-09-30 09:37:43Z Tomcraft $

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
  
  // languages
  $languages = xtc_get_languages(); 

  //display per page
  $cfg_max_display_results_key = 'MAX_DISPLAY_MANUFACTURERS_RESULTS';
  $page_max_display_results = xtc_cfg_save_max_display_results($cfg_max_display_results_key);

  switch ($_GET['action']) {
    case 'insert':
    case 'save':
      $manufacturers_id = (int)$_GET['mID'];
      $manufacturers_name = xtc_db_prepare_input($_POST['manufacturers_name']);

      $sql_data_array = array('manufacturers_name' => $manufacturers_name);

      if ($_GET['action'] == 'insert') {
        $insert_sql_data = array('date_added' => 'now()');
        $sql_data_array = xtc_array_merge($sql_data_array, $insert_sql_data);
        xtc_db_perform(TABLE_MANUFACTURERS, $sql_data_array);
        $manufacturers_id = xtc_db_insert_id();
      } elseif ($_GET['action'] == 'save') {
        $update_sql_data = array('last_modified' => 'now()');
        $sql_data_array = xtc_array_merge($sql_data_array, $update_sql_data);
        xtc_db_perform(TABLE_MANUFACTURERS, $sql_data_array, 'update', "manufacturers_id = '" . (int)$manufacturers_id . "'");
      }

      //delete manufacturers_image
      if ($_POST['delete_image'] == 'on') {
        $manufacturer_query = xtc_db_query("SELECT manufacturers_image 
                                              FROM " . TABLE_MANUFACTURERS . " 
                                             WHERE manufacturers_id = '" . (int)$manufacturers_id . "'");
        $manufacturer = xtc_db_fetch_array($manufacturer_query);
        $image_location = DIR_FS_CATALOG_IMAGES . $manufacturer['manufacturers_image'];
        if (file_exists($image_location)) {
          @unlink($image_location);
          $sql_data_array['manufacturers_image'] = '';
          xtc_db_perform(TABLE_MANUFACTURERS, $sql_data_array, 'update', "manufacturers_id = '" . (int)$manufacturers_id . "'"); 
        }
      }
      
      //store manufacturers_image
      $accepted_manufacturers_image_files_extensions = array("jpg","jpeg","jpe","gif","png","bmp","tiff","tif","bmp");
      $accepted_manufacturers_image_files_mime_types = array("image/jpeg","image/gif","image/png","image/bmp");
      $dir_manufacturers = DIR_FS_CATALOG_IMAGES . 'manufacturers/';
      if ($manufacturers_image = xtc_try_upload('manufacturers_image', $dir_manufacturers, '644', $accepted_manufacturers_image_files_extensions, $accepted_manufacturers_image_files_mime_types)) {
        $sql_data_array['manufacturers_image'] = 'manufacturers/'.$manufacturers_image->filename;
        xtc_db_perform(TABLE_MANUFACTURERS, $sql_data_array, 'update', "manufacturers_id = '" . (int)$manufacturers_id . "'");
      }

      $languages = xtc_get_languages();
      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $manufacturers_url_array = $_POST['manufacturers_url'];
          $manufacturers_description_array = $_POST['manufacturers_description'];
          $manufacturers_meta_title_array = $_POST['manufacturers_meta_title'];
          $manufacturers_meta_description_array = $_POST['manufacturers_meta_description'];
          $manufacturers_meta_keywords_array = $_POST['manufacturers_meta_keywords'];
          $language_id = $languages[$i]['id'];

          $sql_data_array = array('manufacturers_url' => xtc_db_prepare_input($manufacturers_url_array[$language_id]),
                                  'manufacturers_description' => xtc_db_prepare_input($manufacturers_description_array[$language_id]),
                                  'manufacturers_meta_title' => xtc_db_prepare_input($manufacturers_meta_title_array[$language_id]),
                                  'manufacturers_meta_description' => xtc_db_prepare_input($manufacturers_meta_description_array[$language_id]),
                                  'manufacturers_meta_keywords' => xtc_db_prepare_input($manufacturers_meta_keywords_array[$language_id])                    
                                  );

        if ($_GET['action'] == 'insert') {
          $insert_sql_data = array('manufacturers_id' => $manufacturers_id,
                                   'languages_id' => $language_id);
          $sql_data_array = xtc_array_merge($sql_data_array, $insert_sql_data);
          xtc_db_perform(TABLE_MANUFACTURERS_INFO, $sql_data_array);
        } elseif ($_GET['action'] == 'save') {
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

      xtc_redirect(xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . (int)$_GET['page'] . '&mID=' . $manufacturers_id));
      break;

    case 'deleteconfirm':
      $manufacturers_id = xtc_db_prepare_input($_GET['mID']);

      if ($_POST['delete_image'] == 'on') {
        $manufacturer_query = xtc_db_query("SELECT manufacturers_image 
                                              FROM " . TABLE_MANUFACTURERS . " 
                                             WHERE manufacturers_id = '" . (int)$manufacturers_id . "'");
        $manufacturer = xtc_db_fetch_array($manufacturer_query);
        $image_location = DIR_FS_DOCUMENT_ROOT . DIR_WS_IMAGES . $manufacturer['manufacturers_image'];
        if (file_exists($image_location)) @unlink($image_location);
      }

      xtc_db_query("DELETE FROM " . TABLE_MANUFACTURERS . " WHERE manufacturers_id = '" . (int)$manufacturers_id . "'");
      xtc_db_query("DELETE FROM " . TABLE_MANUFACTURERS_INFO . " WHERE manufacturers_id = '" . (int)$manufacturers_id . "'");

      if ($_POST['delete_products'] == 'on') {
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

      xtc_redirect(xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . (int)$_GET['page']));
      break;
  }
  
require (DIR_WS_INCLUDES.'head.php');
?>
<script type="text/javascript" src="includes/general.js"></script>
<?php
// Include WYSIWYG if is activated
if (USE_WYSIWYG == 'true') {
	$query = xtc_db_query("SELECT code FROM ".TABLE_LANGUAGES." WHERE languages_id='".(int)$_SESSION['languages_id']."'");
	$data = xtc_db_fetch_array($query);
	// generate editor 
	echo PHP_EOL . (!function_exists('editorJSLink') ? '<script type="text/javascript" src="includes/modules/fckeditor/fckeditor.js"></script>' : '') . PHP_EOL;
	if ($_GET['action'] == 'edit' || $_GET['action'] == 'new') {
	  for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
      echo xtc_wysiwyg('manufacturers_description', $data['code'], $languages[$i]['id']);
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
        <div class="pageHeading pdg2 mrg5"><?php echo HEADING_TITLE; ?></div>
        <?php
        if (isset($_GET['action']) && ($_GET['action']=='edit' || $_GET['action']=='new')) {
          if ($_GET['action'] == 'new') {
            unset($_GET['mID']);
          } else {
            $manufact_query = xtc_db_query("SELECT manufacturers_name,
                                                   manufacturers_image
                                              FROM " . TABLE_MANUFACTURERS . "
                                             WHERE manufacturers_id='".(int)$_GET['mID']."'
                                           ");
            $manufact = xtc_db_fetch_array($manufact_query);          
          }
          echo xtc_draw_form('manufacturers', FILENAME_MANUFACTURERS, 'page=' . (int)$_GET['page'] . ((isset($_GET['mID'])) ? '&mID=' . (int)$_GET['mID'] : ''). '&action='.(($_GET['action']=='new') ? 'insert' : 'save'), 'post', 'enctype="multipart/form-data"');
          ?>
          <div class="div_box mrg5">       
            <div class="pdg2">
              <table class="tableInput bg_notice" style="padding: 5px 12px;">
                <tr>
                  <td class="main" style="width:185px;"><b><?php echo TEXT_MANUFACTURERS_NAME; ?></b></td>
                  <td class="main"><?php echo xtc_draw_input_field('manufacturers_name', ((isset($manufact['manufacturers_name'])) ? $manufact['manufacturers_name'] : ''), 'style="width:100%" maxlength="255"'); ?></td>
                </tr>
              </table>
              <div style="width:100%; height: 20px;"></div>

              <!-- BOF manufacturer description block //-->
              <?php
              include('includes/lang_tabs.php');
              for ($i=0; $i<sizeof($languages); $i++) {
                echo ('<div id="tab_lang_' . $i . '">');
                $lng_image = '<div style="float:left;margin-right:5px;">'.xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']).'</div>';
                $manufacturers_query = xtc_db_query("SELECT *
                                                       FROM " . TABLE_MANUFACTURERS_INFO . "
                                                      WHERE manufacturers_id='".(int)$_GET['mID']."'
                                                        AND languages_id='".$languages[$i]['id']."'");
                $manufacturer = xtc_db_fetch_array($manufacturers_query);
                ?>
                <table class="tableInput border0">
                  <tr>
                    <td class="main" style="width:190px;"><b><?php echo $lng_image.TEXT_MANUFACTURERS_URL; ?></b></td>
                    <td class="main"><?php echo xtc_draw_input_field('manufacturers_url[' . $languages[$i]['id'] . ']', xtc_get_manufacturer_url($manufacturer['manufacturers_id'], $languages[$i]['id']), 'style="width:99%" maxlength="255"'); ?></td>
                  </tr>
                  <tr>
                    <td class="main"><b><?php  echo $lng_image.TEXT_MANUFACTURERS_DESCRIPTION; ?></b></td>
                    <td class="main">&nbsp;</td>
                  </tr>
                  <tr>
                    <td class="main" colspan="2"><?php echo xtc_draw_textarea_field('manufacturers_description[' . $languages[$i]['id'] . ']', 'soft', '100', '25', ((isset($manufacturer['manufacturers_description'])) ? stripslashes($manufacturer['manufacturers_description']) : ''), 'style="width:99%"'); ?></td>
                  </tr>
                  <tr>
                    <td class="main"><b><?php  echo $lng_image.TEXT_META_TITLE .'<br /> (max. ' . META_TITLE_LENGTH . ' ' . TEXT_CHARACTERS .')'; ?></b></td>
                    <td class="main"><?php echo xtc_draw_input_field('manufacturers_meta_title[' . $languages[$i]['id'] . ']', ((isset($manufacturer['manufacturers_meta_title'])) ? stripslashes($manufacturer['manufacturers_meta_title']) : ''), 'style="width:99%" maxlength="' . META_TITLE_LENGTH . '"'); ?></td>
                  </tr>
                  <tr>
                    <td class="main"><b><?php  echo $lng_image.TEXT_META_DESCRIPTION .'<br /> (max. ' . META_DESCRIPTION_LENGTH . ' ' . TEXT_CHARACTERS .')'; ?></b></td>
                    <td class="main"><?php echo xtc_draw_input_field('manufacturers_meta_description[' . $languages[$i]['id'] . ']', ((isset($manufacturer['manufacturers_meta_description'])) ? stripslashes($manufacturer['manufacturers_meta_description']) : ''),'style="width:99%" maxlength="' . META_DESCRIPTION_LENGTH . '"'); ?></td>
                  </tr>
                  <tr>
                    <td class="main"><b><?php  echo $lng_image.TEXT_META_KEYWORDS .'<br /> (max. ' . META_KEYWORDS_LENGTH . ' ' . TEXT_CHARACTERS .')'; ?></b></td>
                    <td class="main"><?php echo xtc_draw_input_field('manufacturers_meta_keywords[' . $languages[$i]['id'] . ']', ((isset($manufacturer['manufacturers_meta_keywords'])) ? stripslashes($manufacturer['manufacturers_meta_keywords']) : ''),'style="width:99%" maxlength="' . META_KEYWORDS_LENGTH . '"'); ?></td>
                  </tr>
                </table>
                <?php
                echo ('</div>');
              } ?>
              <!-- EOF manufacturer description block //-->
            </div>

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
                    <td class="dataTableConfig col-right"<?php echo $rowspan;?>><?php if ($manufact['manufacturers_image']) { ?><img src="<?php echo DIR_WS_CATALOG_IMAGES . $manufact['manufacturers_image']; ?>" /><?php } ?></td>
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
              <?php echo xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . (int)$_GET['page'] . '&mID=' . (int)$_GET['mID'])) . '&nbsp;' . xtc_button(BUTTON_SAVE); ?>
            </div>
            <!-- EOF Save block //-->
          </div>
        <?php } else { ?>
          <table class="tableCenter">
            <tr>
              <td class="boxCenterLeft">
                <table class="tableBoxCenter collapse">
                  <tr class="dataTableHeadingRow">
                    <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MANUFACTURERS; ?></td>
                    <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                  </tr>
                  <?php
                  $manufacturers_query_raw = "SELECT manufacturers_id, 
                                                     manufacturers_name, 
                                                     manufacturers_image, 
                                                     date_added, 
                                                     last_modified 
                                                FROM " . TABLE_MANUFACTURERS . " 
                                            ORDER BY manufacturers_name";
                  $manufacturers_split = new splitPageResults($_GET['page'], $page_max_display_results, $manufacturers_query_raw, $manufacturers_query_numrows);
                  $manufacturers_query = xtc_db_query($manufacturers_query_raw);
                  while ($manufacturers = xtc_db_fetch_array($manufacturers_query)) {
                    if (((!$_GET['mID']) || (@$_GET['mID'] == $manufacturers['manufacturers_id'])) && (!$mInfo) && (substr($_GET['action'], 0, 3) != 'new')) {
                      $manufacturer_products_query = xtc_db_query("SELECT count(*) as products_count 
                                                                     FROM " . TABLE_PRODUCTS . " 
                                                                    WHERE manufacturers_id = '" . $manufacturers['manufacturers_id'] . "'");
                      $manufacturer_products = xtc_db_fetch_array($manufacturer_products_query);
                      $mInfo_array = xtc_array_merge($manufacturers, $manufacturer_products);
                      $mInfo = new objectInfo($mInfo_array);
                    }

                    if ( (is_object($mInfo)) && ($manufacturers['manufacturers_id'] == $mInfo->manufacturers_id) ) {
                      echo '              <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . (int)$_GET['page'] . '&mID=' . $manufacturers['manufacturers_id'] . '&action=edit') . '\'">' . "\n";
                    } else {
                      echo '              <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . (int)$_GET['page'] . '&mID=' . $manufacturers['manufacturers_id']) . '\'">' . "\n";
                    }
                  ?>
                  <td class="dataTableContent"><?php echo $manufacturers['manufacturers_name']; ?></td>
                  <td class="dataTableContent txta-r"><?php if ( (is_object($mInfo)) && ($manufacturers['manufacturers_id'] == $mInfo->manufacturers_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . (int)$_GET['page'] . '&mID=' . $manufacturers['manufacturers_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                </tr>
                <?php
                  }
                ?>              
                </table>
                <div class="smallText pdg2 flt-l"><?php echo $manufacturers_split->display_count($manufacturers_query_numrows, $page_max_display_results, (int)$_GET['page'], TEXT_DISPLAY_NUMBER_OF_MANUFACTURERS); ?></div>
                <div class="smallText pdg2 flt-r"><?php echo $manufacturers_split->display_links($manufacturers_query_numrows, $page_max_display_results, MAX_DISPLAY_PAGE_LINKS, (int)$_GET['page']); ?></div>
                <?php echo draw_input_per_page($PHP_SELF,$cfg_max_display_results_key,$page_max_display_results); ?>
                <?php
                if ($_GET['action'] != 'new') {
                ?>
                  <div class="smallText pdg2 flt-r"><?php echo xtc_button_link(BUTTON_INSERT, xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . (int)$_GET['page'] . '&action=new')); ?></div>
                <?php
                }
                ?>
              </td>
              <?php
                $heading = array();
                $contents = array();
                switch ($_GET['action']) {
                              
                  case 'delete':
                    $heading[] = array('text' => '<b>' . TEXT_HEADING_DELETE_MANUFACTURER . '</b>');
                    $contents = array('form' => xtc_draw_form('manufacturers', FILENAME_MANUFACTURERS, 'page=' . (int)$_GET['page'] . '&mID=' . $mInfo->manufacturers_id . '&action=deleteconfirm'));
                    $contents[] = array('text' => TEXT_DELETE_INTRO);
                    $contents[] = array('text' => '<br /><b>' . $mInfo->manufacturers_name . '</b>');
                    $contents[] = array('text' => '<br />' . xtc_draw_checkbox_field('delete_image', '', true) . ' ' . TEXT_DELETE_IMAGE);
                    if ($mInfo->products_count > 0) {
                      $contents[] = array('text' => '<br />' . xtc_draw_checkbox_field('delete_products') . ' ' . TEXT_DELETE_PRODUCTS);
                      $contents[] = array('text' => '<br />' . sprintf(TEXT_DELETE_WARNING_PRODUCTS, $mInfo->products_count));
                    }
                    $contents[] = array('align' => 'center', 'text' => '<br />' . xtc_button(BUTTON_DELETE) . '&nbsp;' . xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . (int)$_GET['page'] . '&mID=' . $mInfo->manufacturers_id)));
                    break;

                  default:
                    if (is_object($mInfo)) {
                      $heading[] = array('text' => '<b>' . $mInfo->manufacturers_name . '</b>');
                      $contents[] = array('align' => 'center', 'text' => xtc_button_link(BUTTON_EDIT, xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . (int)$_GET['page'] . '&mID=' . $mInfo->manufacturers_id . '&action=edit')) . '&nbsp;' . xtc_button_link(BUTTON_DELETE, xtc_href_link(FILENAME_MANUFACTURERS, 'page=' . (int)$_GET['page'] . '&mID=' . $mInfo->manufacturers_id . '&action=delete')));
                      $contents[] = array('text' => '<br />' . TEXT_DATE_ADDED . ' ' . xtc_date_short($mInfo->date_added));
                      if (xtc_not_null($mInfo->last_modified)) {
                        $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . xtc_date_short($mInfo->last_modified));
                      }
                      $contents[] = array('text' => '<br />' . xtc_info_image($mInfo->manufacturers_image, $mInfo->manufacturers_name));
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