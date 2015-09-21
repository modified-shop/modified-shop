<?php
/* --------------------------------------------------------------
   $Id: csv_backend.php 5750 2013-09-13 13:26:51Z Tomcraft $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommercecoding standards (a typical file) www.oscommerce.com
   (c) 2006 xt:Commerce (csv_backend.php)

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  require('includes/application_top.php');
  require(DIR_WS_CLASSES . 'import.php');
  require_once(DIR_FS_INC . 'xtc_format_filesize.inc.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  switch ($action) {
    case 'upload':
      $upload_file=xtc_db_prepare_input($_POST['file_upload']);
      $accepted_file_upload_files_extensions = array("txt","csv","tsv");
      $accepted_file_upload_files_mime_types = array("text/plain","text/comma-separated-values","text/tab-separated-values");
      if ($upload_file = &xtc_try_upload('file_upload',DIR_FS_CATALOG.'import/','644',$accepted_file_upload_files_extensions,$accepted_file_upload_files_mime_types)) {
        ${$upload_file_name} = $upload_file->filename;
      }
    break;

    case 'import':
      $handler = new xtcImport($_POST['select_file']);
      $mapping=$handler->map_file($handler->generate_map());
      $import=$handler->import($mapping);
    break;

    case 'export':
      $handler = new xtcExport('export.csv');
      $import=$handler->exportProdFile();
    break;

    case 'save':
      $configuration_query = xtc_db_query("select configuration_key,configuration_id, configuration_value, use_function,set_function from " . TABLE_CONFIGURATION . " where configuration_group_id = '20' order by sort_order");

      while ($configuration = xtc_db_fetch_array($configuration_query)) {
        xtc_db_query("UPDATE ".TABLE_CONFIGURATION." SET configuration_value='".$_POST[$configuration['configuration_key']]."' where configuration_key='".$configuration['configuration_key']."'");
      }
      xtc_redirect(xtc_href_link(FILENAME_CSV_BACKEND));
      break;
  }

  $cfg_group_query = xtc_db_query("select configuration_group_title from " . TABLE_CONFIGURATION_GROUP . " where configuration_group_id = '20'");
  $cfg_group = xtc_db_fetch_array($cfg_group_query);

require (DIR_WS_INCLUDES.'head.php');
?>
<script type="text/javascript" src="includes/general.js"></script>
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
        <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_content.png'); ?></div>
        <div class="flt-l">
        	<div class="pageHeading">CSV Import/Export<br></div>
          <div class="main pdg2 flt-l">Tools</div>
        </div>
        <div class="main pdg2 flt-l" style="margin-left:20px;">
          <a class="button" href="#" onclick="toggleBox('config');"><?php echo CSV_SETUP; ?></a>
        </div>
        <div class="clear div_box brd-none">
          <div id="config" class="longDescription">
          <?php echo xtc_draw_form('configuration', FILENAME_CSV_BACKEND, 'gID=20&action=save'); ?>
          <table class="tableConfig">
          <?php
            $configuration_query = xtc_db_query("select configuration_key,configuration_id, configuration_value, use_function,set_function from " . TABLE_CONFIGURATION . " where configuration_group_id = '20' order by sort_order");

            while ($configuration = xtc_db_fetch_array($configuration_query)) {
              if ($_GET['gID'] == 6) {
                switch ($configuration['configuration_key']) {
                  case 'MODULE_PAYMENT_INSTALLED':
                    if ($configuration['configuration_value'] != '') {
                      $payment_installed = explode(';', $configuration['configuration_value']);
                      for ($i = 0, $n = sizeof($payment_installed); $i < $n; $i++) {
                        include(DIR_FS_CATALOG_LANGUAGES . $language . '/modules/payment/' . $payment_installed[$i]);
                      }
                    }
                    break;

                  case 'MODULE_SHIPPING_INSTALLED':
                    if ($configuration['configuration_value'] != '') {
                      $shipping_installed = explode(';', $configuration['configuration_value']);
                      for ($i = 0, $n = sizeof($shipping_installed); $i < $n; $i++) {
                        include(DIR_FS_CATALOG_LANGUAGES . $language . '/modules/shipping/' . $shipping_installed[$i]);
                      }
                    }
                    break;

                  case 'MODULE_ORDER_TOTAL_INSTALLED':
                    if ($configuration['configuration_value'] != '') {
                      $ot_installed = explode(';', $configuration['configuration_value']);
                      for ($i = 0, $n = sizeof($ot_installed); $i < $n; $i++) {
                        include(DIR_FS_CATALOG_LANGUAGES . $language . '/modules/order_total/' . $ot_installed[$i]);
                      }
                    }
                    break;
                }
              }
              if (xtc_not_null($configuration['use_function'])) {
                $use_function = $configuration['use_function'];
                if (preg_match('/->/', $use_function)) { // Hetfield - 2009-08-19 - replaced deprecated function ereg with preg_match to be ready for PHP >= 5.3
                  $class_method = explode('->', $use_function);
                  if (!is_object(${$class_method[0]})) {
                    include(DIR_WS_CLASSES . $class_method[0] . '.php');
                    ${$class_method[0]} = new $class_method[0]();
                  }
                  $cfgValue = xtc_call_function($class_method[1], $configuration['configuration_value'], ${$class_method[0]});
                } else {
                  $cfgValue = xtc_call_function($use_function, $configuration['configuration_value']);
                }
              } else {
                $cfgValue = $configuration['configuration_value'];
              }

              if (((!$_GET['cID']) || (@$_GET['cID'] == $configuration['configuration_id'])) && (!$cInfo) && (substr($action, 0, 3) != 'new')) {
                $cfg_extra_query = xtc_db_query("select configuration_key,configuration_value, date_added, last_modified, use_function, set_function from " . TABLE_CONFIGURATION . " where configuration_id = '" . $configuration['configuration_id'] . "'");
                $cfg_extra = xtc_db_fetch_array($cfg_extra_query);

                $cInfo_array = xtc_array_merge($configuration, $cfg_extra);
                $cInfo = new objectInfo($cInfo_array);
              }
              if ($configuration['set_function']) {
                eval('$value_field = ' . $configuration['set_function'] . '"' . encode_htmlspecialchars($configuration['configuration_value']) . '");');
              } else {
                $value_field = xtc_draw_input_field($configuration['configuration_key'], $configuration['configuration_value'],'size=40');
              }

              if (strstr($value_field,'configuration_value')) $value_field=str_replace('configuration_value',$configuration['configuration_key'],$value_field);

              echo '<tr>
                      <td class="dataTableConfig col-left">'.constant(strtoupper($configuration['configuration_key'].'_TITLE')).'</td>
                      <td class="dataTableConfig col-middle">'.$value_field.'</td>
                      <td class="dataTableConfig col-right">'.constant(strtoupper( $configuration['configuration_key'].'_DESC')).'</td>
                    </tr>';
            }
          ?>
          </table>
          <div class="clear mrg5 txta-r"><?php echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SAVE . '"/>'; ?></div>
          </form>
          </div>
          <?php
          if (isset($import)) {
            if ($import[0]) {
              echo '<div class="success_message">';
              if (isset($import[0]['prod_new'])) echo 'new products:'.$import[0]['prod_new'].'<br />';
              if (isset($import[0]['cat_new'])) echo 'new categories:'.$import[0]['cat_new'].'<br />';
              if (isset($import[0]['prod_upd'])) echo 'updated products:'.$import[0]['prod_upd'].'<br />';
              if (isset($import[0]['cat_upd'])) echo 'updated categories:'.$import[0]['cat_upd'].'<br />';
              if (isset($import[0]['cat_touched'])) echo 'touched categories:'.$import[0]['cat_touched'].'<br />';
              if (isset($import[0]['prod_exp'])) echo 'products exported:'.$import[0]['prod_exp'].'<br />';
              if (isset($import[2])) echo $import[2];
              echo '</div>';
            }

            if (isset($import[1]) && $import[1][0] != ''){
              echo '<div class="error_message">';
              for ($i=0;$i<count($import[1]);$i++) {
                echo $import[1][$i].'<br />';
              }
              echo '</div>';
            }
          }
          ?>
          <div class="pageHeading mrg5">Import</div>
          <div class="div_box mrg5"><?php echo TEXT_IMPORT; ?>
            <div class="mrg5"><?php echo UPLOAD; ?></div>
    
            <?php echo xtc_draw_form('upload',FILENAME_CSV_BACKEND,'action=upload','post','enctype="multipart/form-data"');
            echo '<div class="mrg5">'.xtc_draw_file_field('file_upload').'</div>';
            echo '<div class="mrg5"><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UPLOAD . '"/></div>';
            ?>
            </form>
     
            <div class="mrg5"><?php echo SELECT; ?></div>
    
            <?php
            $files = array();
            echo xtc_draw_form('import',FILENAME_CSV_BACKEND,'action=import','post','enctype="multipart/form-data"');
            if ($dir= opendir(DIR_FS_CATALOG.'import/')) {
              while (($file = readdir($dir)) !== false) {
                if (is_file(DIR_FS_CATALOG.'import/'.$file) and ($file !=".htaccess")) {
                  $size=filesize(DIR_FS_CATALOG.'import/'.$file);
                  $files[] = array( 'id' => $file, 'text' => $file.' | '.xtc_format_filesize($size));
                }
              }
              closedir($dir);
            }
            echo '<div class="mrg5">'.xtc_draw_pull_down_menu('select_file', array_merge(array(array('id'=>'', 'text' => TEXT_SELECT)), $files),'').'</div>';
            echo '<div class="mrg5"><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_IMPORT . '"/></div>';
            ?>
            </form>
          </div>
          
          <div class="pageHeading mrg5">Export</div>
          <div class="div_box mrg5"><?php echo TEXT_EXPORT; ?>
            <?php echo xtc_draw_form('export',FILENAME_CSV_BACKEND,'action=export','post','enctype="multipart/form-data"');
            $content=array();
            $content[]=array('id'=>'products','text'=>TEXT_PRODUCTS);
            echo '<div class="mrg5">'.xtc_draw_pull_down_menu('select_content',$content,'products').'</div>';
            echo '<div class="mrg5"><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_EXPORT . '"/></div>';
            ?>
            </form>
          </div>
          
        </div>
      </td>
    <!-- body_text_eof //-->
    </tr>
  </table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>