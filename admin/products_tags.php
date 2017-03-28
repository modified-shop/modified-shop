<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  define('IMPORT_LIMIT', 5000);
  
  require('includes/application_top.php');

	if (isset($_GET['iframe'])) {
	  require(DIR_WS_MODULES.'products_tags.php');
    exit;    
	}

  //display per page
  $cfg_max_display_options_key = 'MAX_DISPLAY_NUMBER_OF_OPTIONS';
  $page_max_display_options_results = xtc_cfg_save_max_display_results($cfg_max_display_options_key);

  $cfg_max_display_values_key = 'MAX_DISPLAY_NUMBER_OF_VALUES';
  $page_max_display_values_results = xtc_cfg_save_max_display_results($cfg_max_display_values_key);

  $dir_values = DIR_FS_CATALOG_IMAGES . 'tags/';
  $accepted_values_image_files_extensions = array("jpg","jpeg","jpe","gif","png","bmp","tiff","tif","bmp");
  $accepted_values_image_files_mime_types = array("image/jpeg","image/gif","image/png","image/bmp");

  $languages = xtc_get_languages();

  function xtc_get_values_detail($values_id, $languages_id, $db_field) {
    $values_query = xtc_db_query("SELECT ".$db_field." 
                                    FROM ".TABLE_PRODUCTS_TAGS_VALUES."
                                   WHERE values_id = '".$values_id."'
                                     AND languages_id = '".$languages_id."'");
    $values = xtc_db_fetch_array($values_query);
    
    return $values[$db_field];
  }

  function xtc_get_options_detail($options_id, $languages_id, $db_field) {
    $options_query = xtc_db_query("SELECT ".$db_field." 
                                     FROM ".TABLE_PRODUCTS_TAGS_OPTIONS."
                                    WHERE options_id = '".$options_id."'
                                      AND languages_id = '".$languages_id."'");
    $options = xtc_db_fetch_array($options_query);
    
    return $options[$db_field];
  }

  if (isset($_GET['saction'])) {
    switch ($_GET['saction']) {
    
      case 'setvaluesflag':
        $vID = (int)$_GET['vID'];
        $oID = (int)$_GET['oID'];
        $status = (int)$_GET['flag'];
        xtc_db_query("UPDATE " . TABLE_PRODUCTS_TAGS_VALUES . " 
                         SET status = '" . xtc_db_input($status) . "' 
                       WHERE values_id = '" . $vID . "'"); 
        xtc_redirect(xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . (int)$_GET['page'] . '&oID=' . $oID . '&action=list&spage=' . (int)$_GET['spage'] . '&vID=' . $vID));
        break;

      case 'setvaluesfilter':
        $vID = (int)$_GET['vID'];
        $oID = (int)$_GET['oID'];
        $status = (int)$_GET['flag'];
        xtc_db_query("UPDATE " . TABLE_PRODUCTS_TAGS_VALUES . " 
                         SET filter = '" . xtc_db_input($status) . "' 
                       WHERE values_id = '" . $vID . "'"); 
        xtc_redirect(xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . (int)$_GET['page'] . '&oID=' . $oID . '&action=list&spage=' . (int)$_GET['spage'] . '&vID=' . $vID));
        break;
        
      case 'insert_values':
        $oID = (int)$_GET['oID'];
        $next_id_query = xtc_db_query("SELECT max(values_id) as values_id 
                                         FROM " . TABLE_PRODUCTS_TAGS_VALUES . "");
        $next_id = xtc_db_fetch_array($next_id_query);
        $values_id = $next_id['values_id'] + 1;
        
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {     
          $sql_data_array = array('values_id' => $values_id,
                                  'options_id' => $oID,
                                  'values_name' => xtc_db_prepare_input($_POST['values_name'][$languages[$i]['id']]),
                                  'values_description' => xtc_db_prepare_input($_POST['values_description'][$languages[$i]['id']]),
                                  'values_content_group' => (($_POST['values_content_group'] != '') ? (int)$_POST['values_content_group'] : 'null'),
                                  'languages_id' => $languages[$i]['id'],
                                  'sort_order' => (int)$_POST['sort_order'],
                                  'date_added' => 'now()');
          xtc_db_perform(TABLE_PRODUCTS_TAGS_VALUES, $sql_data_array);
        }
    
        //store image
        if ($values_image = xtc_try_upload('values_image', $dir_values, '644', $accepted_values_image_files_extensions, $accepted_values_image_files_mime_types)) {
          $sql_data_array = array('values_image' => 'tags/'.$values_image->filename);
          xtc_db_perform(TABLE_PRODUCTS_TAGS_VALUES, $sql_data_array, 'update', "values_id = '" . (int)$values_id . "'");
        }

        xtc_redirect(xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . (int)$_GET['page'] . '&oID=' . $oID . '&action=list&spage=' . (int)$_GET['spage'] . '&vID=' . $values_id));
        break;

      case 'save_values':
        $vID = (int)$_GET['vID'];
        $oID = (int)$_GET['oID'];
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {     
          $sql_data_array = array('values_id' => $vID,
                                  'options_id' => $oID,
                                  'values_name' => xtc_db_prepare_input($_POST['values_name'][$languages[$i]['id']]),
                                  'values_description' => xtc_db_prepare_input($_POST['values_description'][$languages[$i]['id']]),
                                  'values_content_group' => (($_POST['values_content_group'] != '') ? (int)$_POST['values_content_group'] : 'null'),
                                  'languages_id' => $languages[$i]['id'],
                                  'sort_order' => (int)$_POST['sort_order'],
                                  'date_added' => 'now()');
          $values_description_query = xtc_db_query("SELECT * 
                                                      FROM ".TABLE_PRODUCTS_TAGS_VALUES." 
                                                     WHERE languages_id = '".$languages[$i]['id']."' 
                                                       AND values_id = '".$vID."'");
          if (xtc_db_num_rows($values_description_query) == 0) {
            $sql_data_array['last_modified'] = 'now()';
            $sql_data_array['values_image'] = xtc_get_values_detail($vID, $languages[$i]['id'], 'values_image');
            xtc_db_perform(TABLE_PRODUCTS_TAGS_VALUES, $sql_data_array);
          } else {
            xtc_db_perform(TABLE_PRODUCTS_TAGS_VALUES, $sql_data_array, 'update', "values_id = '".$vID."' AND languages_id = '".$languages[$i]['id']."'");                    
          }      
        }
 
        //delete image
        if (isset($_POST['delete_image']) && $_POST['delete_image'] == 'on') {
          $values_query = xtc_db_query("SELECT values_image 	 
                                           FROM " . TABLE_PRODUCTS_TAGS_VALUES . " 
                                          WHERE values_id = '" . $vID . "'");
          while ($values = xtc_db_fetch_array($values_query)) {
            if ($values['values_image'] != '') {
              $image_location = DIR_FS_CATALOG_IMAGES . $values['values_image'];
              if (file_exists($image_location)) {
                @unlink($image_location);
                $sql_data_array = array('values_image' => '');
                xtc_db_perform(TABLE_PRODUCTS_TAGS_VALUES, $sql_data_array, 'update', "values_id = '" . $vID . "'"); 
              }
            }
          }
        }
       
        //store image
        if ($values_image = xtc_try_upload('values_image', $dir_values, '644', $accepted_values_image_files_extensions, $accepted_values_image_files_mime_types)) {
          $sql_data_array = array('values_image' => 'tags/'.$values_image->filename);
          xtc_db_perform(TABLE_PRODUCTS_TAGS_VALUES, $sql_data_array, 'update', "values_id = '" . $vID . "'");
        }

        xtc_redirect(xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . (int)$_GET['page'] . '&oID=' . $oID . '&action=list&spage=' . (int)$_GET['spage'] . '&vID=' . $vID));
        break;

      case 'deleteconfirm_values':
        $vID = (int)$_GET['vID'];
        $oID = (int)$_GET['oID'];

        //delete image
        $values_query = xtc_db_query("SELECT values_image 	 
                                        FROM " . TABLE_PRODUCTS_TAGS_VALUES . " 
                                       WHERE values_id = '" . $vID . "'");
        while ($values = xtc_db_fetch_array($values_query)) {
          $image_location = DIR_FS_CATALOG_IMAGES . $values['values_image'];
          if (file_exists($image_location)) {
            @unlink($image_location);
            $sql_data_array = array('values_image' => '');
            xtc_db_perform(TABLE_PRODUCTS_TAGS_VALUES, $sql_data_array, 'update', "values_id = '" . $vID . "'"); 
          }
        }

        xtc_db_query("DELETE FROM " . TABLE_PRODUCTS_TAGS_VALUES . " WHERE values_id = '" . $vID . "'");
        xtc_db_query("DELETE FROM " . TABLE_PRODUCTS_TAGS . " WHERE values_id = '" . $vID . "'");

        xtc_redirect(xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . (int)$_GET['page'] . '&oID=' . $oID . '&action=list&spage=' . (int)$_GET['spage']));
        break;
    }
  }
  
  if (isset($_GET['action'])) {
    switch ($_GET['action']) {
    
      case 'setoptionsflag':
        $oID = (int)$_GET['oID'];
        $status = (int)$_GET['flag'];
        xtc_db_query("UPDATE " . TABLE_PRODUCTS_TAGS_OPTIONS . " 
                         SET status = '" . xtc_db_input($status) . "' 
                       WHERE options_id = '" . $oID . "'"); 
        xtc_redirect(xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . (int)$_GET['page'] . '&oID=' . $oID));
        break;

      case 'setoptionsfilter':
        $oID = (int)$_GET['oID'];
        $status = (int)$_GET['flag'];
        xtc_db_query("UPDATE " . TABLE_PRODUCTS_TAGS_OPTIONS . " 
                         SET filter = '" . xtc_db_input($status) . "' 
                       WHERE options_id = '" . $oID . "'"); 
        xtc_redirect(xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . (int)$_GET['page'] . '&oID=' . $oID));
        break;
        
      case 'insert_options':      
        $next_id_query = xtc_db_query("SELECT max(options_id) as options_id 
                                         FROM " . TABLE_PRODUCTS_TAGS_OPTIONS . "");
        $next_id = xtc_db_fetch_array($next_id_query);
        $options_id = $next_id['options_id'] + 1;
        
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {     
          $sql_data_array = array('options_id' => $options_id,
                                  'options_name' => xtc_db_prepare_input($_POST['options_name'][$languages[$i]['id']]),
                                  'options_description' => xtc_db_prepare_input($_POST['options_description'][$languages[$i]['id']]),
                                  'options_content_group' => (($_POST['options_content_group'] != '') ? (int)$_POST['options_content_group'] : 'null'),
                                  'languages_id' => $languages[$i]['id'],
                                  'sort_order' => (int)$_POST['sort_order'],
                                  'date_added' => 'now()'
                                  );
          xtc_db_perform(TABLE_PRODUCTS_TAGS_OPTIONS, $sql_data_array);
        }                  

        xtc_redirect(xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . (int)$_GET['page'] . '&oID=' . $options_id));
        break;

      case 'save_options':
        $oID = (int)$_GET['oID'];
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {     
          $sql_data_array = array('options_id' => $oID,
                                  'options_name' => xtc_db_prepare_input($_POST['options_name'][$languages[$i]['id']]),
                                  'options_description' => xtc_db_prepare_input($_POST['options_description'][$languages[$i]['id']]),
                                  'options_content_group' => (($_POST['options_content_group'] != '') ? (int)$_POST['options_content_group'] : 'null'),
                                  'languages_id' => $languages[$i]['id'],
                                  'sort_order' => (int)$_POST['sort_order'],
                                  );
          $options_name_query = xtc_db_query("SELECT * 
                                                FROM ".TABLE_PRODUCTS_TAGS_OPTIONS." 
                                               WHERE languages_id = '".$languages[$i]['id']."' 
                                                 AND options_id = '".$oID."'");
          if (xtc_db_num_rows($options_name_query) == 0) {
            $sql_data_array['date_added'] = 'now()';
            xtc_db_perform(TABLE_PRODUCTS_TAGS_OPTIONS, $sql_data_array);
          } else {
            $sql_data_array['last_modified'] = 'now()';
            xtc_db_perform(TABLE_PRODUCTS_TAGS_OPTIONS, $sql_data_array, 'update', "options_id = '".$oID."' AND languages_id = '".$languages[$i]['id']."'");                    
          }
        }

        xtc_redirect(xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . (int)$_GET['page'] . '&oID=' . $oID));
        break;

      case 'deleteconfirm_options':
        $oID = (int)$_GET['oID'];
        xtc_db_query("DELETE FROM " . TABLE_PRODUCTS_TAGS_OPTIONS . " WHERE options_id = '" . $oID . "'");
        xtc_db_query("DELETE FROM " . TABLE_PRODUCTS_TAGS_VALUES . " WHERE options_id = '" . $oID . "'");
        xtc_db_query("DELETE FROM " . TABLE_PRODUCTS_TAGS . " WHERE options_id = '" . $oID . "'");

        xtc_redirect(xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . (int)$_GET['page']));
        break;
        
      case 'import_confirm':
        $javascript = '<script language="javascript" type="text/javascript">setTimeout("document.import.submit()", 10);</script>';
        
        if (isset($_POST['attributes_option']) && $_POST['attributes_option'] == '1') {
          $attributes_option_query = xtc_db_query("SELECT po.products_options_id,
                                                         	po.products_options_name ,	
                                                         	po.products_options_sortorder,
                                                         	po.language_id
                                                     FROM ".TABLE_PRODUCTS_OPTIONS." po
                                                    WHERE products_options_id NOT IN (SELECT pto.products_options_id
                                                                                        FROM ".TABLE_PRODUCTS_TAGS_OPTIONS." pto
                                                                                       WHERE pto.languages_id = po.language_id
                                                                                    GROUP BY pto.products_options_id)
                                                    LIMIT ".IMPORT_LIMIT);
          if (xtc_db_num_rows($attributes_option_query) > 0) {
            while ($attributes_option = xtc_db_fetch_array($attributes_option_query)) {
              $check_query = xtc_db_query("SELECT options_id
                                             FROM ".TABLE_PRODUCTS_TAGS_OPTIONS."
                                            WHERE products_options_id = '".$attributes_option['products_options_id']."'
                                            LIMIT 1");
              if (xtc_db_num_rows($check_query) > 0) {
                $check = xtc_db_fetch_array($check_query);
                $options_id = $check['options_id'];
              } else {
                $next_id_query = xtc_db_query("SELECT max(options_id) as options_id 
                                                 FROM " . TABLE_PRODUCTS_TAGS_OPTIONS . "");
                $next_id = xtc_db_fetch_array($next_id_query);
                $options_id = $next_id['options_id'] + 1;
              }
              $sql_data_array = array('options_id' => $options_id,
                                      'options_name' => $attributes_option['products_options_name'],
                                      'languages_id' => $attributes_option['language_id'],
                                      'sort_order' => $attributes_option['products_options_sortorder'],
                                      'products_options_id' => $attributes_option['products_options_id'],
                                      'date_added' => 'now()'
                                      );
              xtc_db_perform(TABLE_PRODUCTS_TAGS_OPTIONS, $sql_data_array);
            }
          } else {
            $_POST['attributes_option'] = '0';
          }
        } elseif (isset($_POST['attributes_value']) && $_POST['attributes_value'] == '1') {
          $attributes_values_query = xtc_db_query("SELECT pov.products_options_values_id,
                                                         	pov.products_options_values_name,	
                                                         	pov.language_id,
                                                         	pov2po.products_options_id,
                                                         	pto.options_id
                                                     FROM ".TABLE_PRODUCTS_OPTIONS_VALUES." pov
                                                     JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS." pov2po
                                                          ON pov2po.products_options_values_id = pov.products_options_values_id
                                                     JOIN ".TABLE_PRODUCTS_TAGS_OPTIONS." pto
                                                          ON pto.products_options_id = pov2po.products_options_id
                                                    WHERE NOT EXISTS (SELECT 1
                                                                        FROM ".TABLE_PRODUCTS_TAGS_VALUES." ptv
                                                                       WHERE ptv.languages_id = pov.language_id
                                                                         AND ptv.products_options_values_id = pov.products_options_values_id)
                                                    LIMIT ".IMPORT_LIMIT);
          if (xtc_db_num_rows($attributes_values_query) > 0) {
            while ($attributes_values = xtc_db_fetch_array($attributes_values_query)) {
              $check_query = xtc_db_query("SELECT values_id
                                             FROM ".TABLE_PRODUCTS_TAGS_VALUES."
                                            WHERE products_options_values_id = '".$attributes_values['products_options_values_id']."'
                                            LIMIT 1");
              if (xtc_db_num_rows($check_query) > 0) {
                $check = xtc_db_fetch_array($check_query);
                $values_id = $check['values_id'];
              } else {
                $next_id_query = xtc_db_query("SELECT max(values_id) as values_id 
                                                 FROM " . TABLE_PRODUCTS_TAGS_VALUES . "");
                $next_id = xtc_db_fetch_array($next_id_query);
                $values_id = $next_id['values_id'] + 1;
              }
              $sql_data_array = array('values_id' => $values_id,
                                      'options_id' => $attributes_values['options_id'],
                                      'values_name' => $attributes_values['products_options_values_name'],
                                      'languages_id' => $attributes_values['language_id'],
                                      'date_added' => 'now()',
                                      'products_options_values_id' => $attributes_values['products_options_values_id'],
                                      );
              xtc_db_perform(TABLE_PRODUCTS_TAGS_VALUES, $sql_data_array);
            }
          } else {
            $_POST['attributes_value'] = '0';
          }
        } elseif (isset($_POST['attributes']) && $_POST['attributes'] == '1') {
          $attributes_query = xtc_db_query("SELECT pa.options_values_id as products_options_values_id,
                                                   pa.options_id as products_options_id,
                                                   pa.products_id,
                                                   pto.options_id,
                                                   pto.products_options_id,
                                                   ptv.values_id
                                              FROM ".TABLE_PRODUCTS_ATTRIBUTES." pa
                                              JOIN ".TABLE_PRODUCTS_TAGS_OPTIONS." pto
                                                   ON pto.products_options_id = pa.options_id
                                                      AND pto.languages_id = '".(int)$_SESSION['languages_id']."'
                                              JOIN ".TABLE_PRODUCTS_TAGS_VALUES." ptv
                                                   ON ptv.products_options_values_id = pa.options_values_id
                                                      AND ptv.languages_id = '".(int)$_SESSION['languages_id']."'
                                             WHERE NOT EXISTS (SELECT 1
                                                                 FROM ".TABLE_PRODUCTS_TAGS." pt
                                                                WHERE pt.options_id = pto.options_id
                                                                  AND pt.values_id = ptv.values_id
                                                                  AND pt.products_id = pa.products_id)
                                             LIMIT ".IMPORT_LIMIT);
          if (xtc_db_num_rows($attributes_query) > 0) {
            while ($attributes = xtc_db_fetch_array($attributes_query)) {
              xtc_db_perform(TABLE_PRODUCTS_TAGS, $attributes);
            }
          } else {
            $_POST['attributes'] = '0';
          }                                                                       
        } elseif (isset($_POST['attributes_delete']) && $_POST['attributes_delete'] == '1') {
          $delete_query = xtc_db_query("SELECT pt.* 
                                          FROM ".TABLE_PRODUCTS_TAGS." pt
                                         WHERE pt.products_options_values_id != '0'
                                           AND pt.products_options_id != '0'
                                           AND NOT EXISTS (SELECT 1
                                                             FROM ".TABLE_PRODUCTS_ATTRIBUTES." pa
                                                            WHERE pt.products_options_values_id = pa.options_values_id
                                                              AND pt.products_options_id = pa.options_id)");
          if (xtc_db_num_rows($delete_query) > 0) {
            while ($delete = xtc_db_fetch_array($delete_query)) {
              xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_TAGS." 
                                  WHERE products_id = '".$delete['products_id']."'
                                    AND options_id = '".$delete['options_id']."'
                                    AND values_id = '".$delete['values_id']."'
                                    AND products_options_id = '".$delete['products_options_id']."'
                                    AND products_options_values_id = '".$delete['products_options_values_id']."'");
            }
          } else {
            $_POST['attributes_delete'] = '0';
          }
        } else {
          $javascript = '';
        }

        $hidden_fields .= xtc_draw_hidden_field('attributes_option', $_POST['attributes_option']);
        $hidden_fields .= xtc_draw_hidden_field('attributes_value', $_POST['attributes_value']);
        $hidden_fields .= xtc_draw_hidden_field('attributes', $_POST['attributes']);
        break;
    }
  }
  
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
        <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_configuration.png'); ?></div>
        <div class="pageHeading"><?php echo ((isset($_GET['action']) && $_GET['action'] == 'list') ? HEADING_TITLE_DETAIL : HEADING_TITLE); ?></div>       
        <div class="main pdg2 flt-l">Configuration</div>
        <table class="tableCenter">
          <tr>
            <td class="boxCenterLeft">
            <?php
            if (isset($_GET['action']) && $_GET['action'] == 'list') {
            ?>
            <table class="tableBoxCenter collapse">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SORT; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_VALUES_IMAGE; ?></td>
                <td class="dataTableHeadingContent" width="20%"><?php echo TABLE_HEADING_VALUES_NAME; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_VALUES_DESCRIPTION; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_VALUES_CONTENT; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_FILTER; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
              <?php
              $values_query_raw = "SELECT ptv.*
                                     FROM " . TABLE_PRODUCTS_TAGS_VALUES . " ptv
                                     JOIN " . TABLE_PRODUCTS_TAGS_OPTIONS . " pto
                                          ON pto.options_id = ptv.options_id
                                             AND pto.languages_id = '".(int)$_SESSION['languages_id']."'
                                    WHERE ptv.options_id = '".$_GET['oID']."'
                                      AND ptv.languages_id = '".(int)$_SESSION['languages_id']."'
                                 ORDER BY ptv.sort_order, ptv.values_name";
              $values_split = new splitPageResults($_GET['spage'], $page_max_display_values_results, $values_query_raw, $values_query_numrows);
              $values_query = xtc_db_query($values_query_raw);
              while ($values = xtc_db_fetch_array($values_query)) {
                if (((!$_GET['vID']) || (@$_GET['vID'] == $values['values_id'])) && (!$vInfo) && (substr($_GET['saction'], 0, 3) != 'new_value')) {
                  $vInfo = new objectInfo($values);
                }
                if ( (is_object($vInfo)) && ($values['values_id'] == $vInfo->values_id) ) {
                  echo '<tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=list&spage=' . $_GET['spage'] . '&vID=' . $vInfo->values_id . '&saction=edit_value') . '\'">' . "\n";
                } else {
                  echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=list&spage=' . $_GET['spage'] . '&vID=' . $values['values_id']) . '\'">' . "\n";
                }
                ?>
                <td class="dataTableContent" style="width:50px;"><?php echo $values['sort_order']; ?></td>
                <td class="dataTableContent"><?php echo (($values['values_image'] != '') ? '<img style="max-width:100px;" src="'.DIR_WS_CATALOG_IMAGES . $values['values_image'].'" />' : ''); ?></td>
                <td class="dataTableContent"><?php echo $values['values_name']; ?></td>
                <td class="dataTableContent"><?php echo $values['values_description']; ?></td>
                <td class="dataTableContent"><?php echo (($values['values_content_group'] > 0) ? xtc_cfg_display_content($values['values_content_group']) : '&nbsp;'); ?></td>
                <td class="dataTableContent">
                  <?php
                  if ($values['filter'] == 1) {
                    echo xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10, 'style="margin-left: 5px;"') . '<a href="' . xtc_href_link(FILENAME_PRODUCTS_TAGS, xtc_get_all_get_params(array('saction', 'vID')) . 'saction=setvaluesfilter&flag=0&vID='.$values['values_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10, 'style="margin-left: 5px;"') . '</a>';
                  } else {
                    echo '<a href="' . xtc_href_link(FILENAME_PRODUCTS_TAGS, xtc_get_all_get_params(array('saction', 'vID')) . 'saction=setvaluesfilter&flag=1&vID='.$values['values_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10, 'style="margin-left: 5px;"') . '</a>' . xtc_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10, 'style="margin-left: 5px;"');
                  }
                  ?>
                </td>
                <td class="dataTableContent">
                  <?php
                  if ($values['status'] == 1) {
                    echo xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10, 'style="margin-left: 5px;"') . '<a href="' . xtc_href_link(FILENAME_PRODUCTS_TAGS, xtc_get_all_get_params(array('saction', 'vID')) . 'saction=setvaluesflag&flag=0&vID='.$values['values_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10, 'style="margin-left: 5px;"') . '</a>';
                  } else {
                    echo '<a href="' . xtc_href_link(FILENAME_PRODUCTS_TAGS, xtc_get_all_get_params(array('saction', 'vID')) . 'saction=setvaluesflag&flag=1&vID='.$values['values_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10, 'style="margin-left: 5px;"') . '</a>' . xtc_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10, 'style="margin-left: 5px;"');
                  }
                  ?>
                </td>
                <td class="dataTableContent txta-r"><?php if ( (is_object($vInfo)) && ($values['values_id'] == $vInfo->values_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=list&spage=' . $_GET['spage'] . '&vID=' . $values['values_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
              <?php
              }
              ?>
            </table>
            
            <div class="smallText pdg2 flt-l"><?php echo $values_split->display_count($values_query_numrows, $page_max_display_values_results, $_GET['spage'], TEXT_DISPLAY_NUMBER_OF_VALUES); ?></div>
            <div class="smallText pdg2 flt-r"><?php echo $values_split->display_links($values_query_numrows, $page_max_display_values_results, MAX_DISPLAY_PAGE_LINKS, $_GET['spage'], 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=list', 'spage'); ?></div>
            <div class="clear"></div>
            <?php echo draw_input_per_page($PHP_SELF.'?'.xtc_get_all_get_params(array('page')),$cfg_max_display_values_key,$page_max_display_values_results); ?>
            <div class="smallText pdg2 flt-r"><?php if (!$_GET['saction']) echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $_GET['oID']) . '">' . BUTTON_BACK . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=list&spage=' . $_GET['spage'] . '&vID=' . $vInfo->values_id . '&saction=new_value') . '">' . BUTTON_INSERT . '</a>'; ?></div>
            <?php
            } else {
            ?>
            <table class="tableBoxCenter collapse">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SORT; ?></td>
                <td class="dataTableHeadingContent" width="20%"><?php echo TABLE_HEADING_OPTIONS_NAME; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_OPTIONS_DESCRIPTION; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_OPTIONS_CONTENT; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_FILTER; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
              <?php
                $options_query_raw = "SELECT *
                                        FROM " . TABLE_PRODUCTS_TAGS_OPTIONS . " 
                                       WHERE languages_id = '".(int)$_SESSION['languages_id']."'
                                    ORDER BY sort_order, options_name";
                $options_split = new splitPageResults($_GET['page'], $page_max_display_options_results, $options_query_raw, $options_query_numrows);
                $options_query = xtc_db_query($options_query_raw);
                while ($options = xtc_db_fetch_array($options_query)) {
                  if (((!$_GET['oID']) || (@$_GET['oID'] == $options['options_id'])) && (!isset($oInfo)) && (substr($_GET['action'], 0, 3) != 'new_value')) {
                    $num_options_query = xtc_db_query("SELECT count(*) as num_options 
                                                         FROM " . TABLE_PRODUCTS_TAGS_VALUES . " 
                                                        WHERE options_id = '" . $options['options_id'] . "' 
                                                          AND languages_id = '".(int)$_SESSION['languages_id']."'
                                                     GROUP BY options_id");
                    if (xtc_db_num_rows($num_options_query) > 0) {
                      $num_options = xtc_db_fetch_array($num_options_query);
                      $options['num_options'] = $num_options['num_options'];
                    } else {
                      $options['num_options'] = 0;
                    }
                    $oInfo = new objectInfo($options);
                  }
                  if (isset($oInfo) && (is_object($oInfo)) && ($options['options_id'] == $oInfo->options_id) ) {
                    echo '<tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->options_id . '&action=list') . '\'">' . "\n";
                  } else {
                    echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $options['options_id']) . '\'">' . "\n";
                  }
                  ?>
                  <td class="dataTableContent" style="width:50px;"><?php echo $options['sort_order']; ?></td>
                  <td class="dataTableContent"><?php echo '<a href="' . xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $options['options_id'] . '&action=list') . '">' . xtc_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER) . '</a>&nbsp;' . $options['options_name']; ?></td>
                  <td class="dataTableContent"><?php echo $options['options_description']; ?></td>
                  <td class="dataTableContent"><?php echo (($options['options_content_group'] > 0) ? xtc_cfg_display_content($options['options_content_group']) : '&nbsp;'); ?></td>
                  <td class="dataTableContent">
                    <?php
                    if ($options['filter'] == 1) {
                      echo xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10, 'style="margin-left: 5px;"') . '<a href="' . xtc_href_link(FILENAME_PRODUCTS_TAGS, xtc_get_all_get_params(array('action', 'oID')) . 'action=setoptionsfilter&flag=0&oID='.$options['options_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10, 'style="margin-left: 5px;"') . '</a>';
                    } else {
                      echo '<a href="' . xtc_href_link(FILENAME_PRODUCTS_TAGS, xtc_get_all_get_params(array('action', 'oID')) . 'action=setoptionsfilter&flag=1&oID='.$options['options_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10, 'style="margin-left: 5px;"') . '</a>' . xtc_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10, 'style="margin-left: 5px;"');
                    }
                    ?>
                  </td>
                  <td class="dataTableContent">
                    <?php
                    if ($options['status'] == 1) {
                      echo xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10, 'style="margin-left: 5px;"') . '<a href="' . xtc_href_link(FILENAME_PRODUCTS_TAGS, xtc_get_all_get_params(array('action', 'oID')) . 'action=setoptionsflag&flag=0&oID='.$options['options_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10, 'style="margin-left: 5px;"') . '</a>';
                    } else {
                      echo '<a href="' . xtc_href_link(FILENAME_PRODUCTS_TAGS, xtc_get_all_get_params(array('action', 'oID')) . 'action=setoptionsflag&flag=1&oID='.$options['options_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10, 'style="margin-left: 5px;"') . '</a>' . xtc_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10, 'style="margin-left: 5px;"');
                    }
                    ?>
                  </td>
                  <td class="dataTableContent txta-r"><?php if (isset($oInfo) && (is_object($oInfo)) && ($options['options_id'] == $oInfo->options_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $options['options_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                </tr>
                <?php
                }
              ?>
            </table>
            
            <div class="smallText pdg2 flt-l"><?php echo $options_split->display_count($options_query_numrows, $page_max_display_options_results, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_OPTIONS); ?></div>
            <div class="smallText pdg2 flt-r"><?php echo $options_split->display_links($options_query_numrows, $page_max_display_options_results, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], '', 'page'); ?></div>
            <div class="clear"></div>
            <?php echo draw_input_per_page($PHP_SELF,$cfg_max_display_options_key,$page_max_display_options_results); ?> 
            <div class="smallText pdg2 flt-r">
              <?php if (!$_GET['action']) echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->options_id . '&action=new_option') . '">' . BUTTON_INSERT . '</a>'; ?>
              <?php if (!$_GET['action']) echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->options_id . '&action=import_attributes') . '">' . BUTTON_IMPORT . '</a>'; ?>            
            </div>
            <?php
            }
            ?>
            </td>
              <?php
              $heading = array();
              $contents = array();

              if (isset($_GET['action']) && $_GET['action'] == 'list') {
                switch ($_GET['saction']) {
                  case 'new_value':
                    $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_VALUE . '</b>');

                    $contents = array('form' => xtc_draw_form('values', FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=list&spage=' . $_GET['spage'] . '&vID=' . $_GET['vID'] . '&saction=insert_values', 'post', 'enctype="multipart/form-data"'));
                    $contents[] = array('text' => TEXT_INFO_NEW_VALUE_INTRO);
                    $contents[] = array('text' => '<br />' . TEXT_INFO_VALUE_NAME . '<br />');
                    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                      $contents[] = array('text' => xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']) . '&nbsp;' . xtc_draw_input_field('values_name[' . $languages[$i]['id'] . ']'));
                    }
                    $contents[] = array('text' => '<br />' . TEXT_INFO_VALUE_DESCRIPTION . '<br />');
                    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                      $contents[] = array('text' => xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']) . '&nbsp;' . xtc_draw_textarea_field('values_description[' . $languages[$i]['id'] . ']', '', '45', '5'));
                    }
                    $contents[] = array('text' => '<br />' . TEXT_INFO_VALUE_IMAGE . '<br />');
                    $contents[] = array('text' => xtc_draw_file_field('values_image', false, 'class="imgupload"'));
                    $contents[] = array('text' => '<br />' . TEXT_INFO_VALUE_CONTENT . '<br />' . xtc_cfg_select_content('values_content_group', ''));
                    $contents[] = array('text' => '<br />' . TEXT_INFO_VALUE_SORT . '<br />' . xtc_draw_input_field('sort_order'));
                    $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_INSERT . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=list&spage=' . $_GET['spage'] . '&vID=' . $_GET['vID']) . '">' . BUTTON_CANCEL . '</a>');
                    break;

                  case 'edit_value':
                    $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_VALUE . '</b>');

                    $contents = array('form' => xtc_draw_form('values', FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=list&spage=' . $_GET['spage'] . '&vID=' . $vInfo->values_id . '&saction=save_values', 'post', 'enctype="multipart/form-data"'));
                    $contents[] = array('text' => TEXT_INFO_EDIT_VALUE_INTRO);
                    $contents[] = array('text' => '<br />' . TEXT_INFO_VALUE_NAME . '<br />');
                    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                      $contents[] = array('text' => xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']) . '&nbsp;' . xtc_draw_input_field('values_name[' . $languages[$i]['id'] . ']', xtc_get_values_detail($vInfo->values_id, $languages[$i]['id'], 'values_name')));
                    }
                    $contents[] = array('text' => '<br />' . TEXT_INFO_VALUE_DESCRIPTION . '<br />');
                    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                      $contents[] = array('text' => xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']) . '&nbsp;' . xtc_draw_textarea_field('values_description[' . $languages[$i]['id'] . ']', '', '45', '5', xtc_get_values_detail($vInfo->values_id, $languages[$i]['id'], 'values_description')));
                    }
                    $contents[] = array('text' => '<br />' . TEXT_INFO_VALUE_IMAGE . '<br />');
                    if ($vInfo->values_image != '') {
                      $contents[] = array('text' => TEXT_INFO_DELETE_VALUE_IMAGE . '&nbsp;' . xtc_draw_checkbox_field('delete_image', 'on'));
                      $contents[] = array('text' => '<img style="max-width:100px;" src="'.DIR_WS_CATALOG_IMAGES . $vInfo->values_image.'" />');
                    }
                    $contents[] = array('text' => xtc_draw_file_field('values_image', false, 'class="imgupload"'));
                    $contents[] = array('text' => '<br />' . TEXT_INFO_VALUE_CONTENT . '<br />' . xtc_cfg_select_content('values_content_group', $vInfo->values_content_group));
                    $contents[] = array('text' => '<br />' . TEXT_INFO_VALUE_SORT . '<br />' . xtc_draw_input_field('sort_order', $vInfo->sort_order));
                    $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UPDATE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=list&spage=' . $_GET['spage'] . '&vID=' . $vInfo->values_id) . '">' . BUTTON_CANCEL . '</a>');
                    break;

                  case 'delete_value':
                    $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_VALUE . '</b>');

                    $contents = array('form' => xtc_draw_form('values', FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=list&spage=' . $_GET['spage'] . '&vID=' . $vInfo->values_id . '&saction=deleteconfirm_values'));
                    $contents[] = array('text' => TEXT_INFO_DELETE_VALUE_INTRO);
                    $contents[] = array('text' => '<br /><b>' . xtc_get_values_detail($vInfo->values_id, $_SESSION['languages_id'], 'values_name') . '</b>');
                    $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=list&spage=' . $_GET['spage'] . '&vID=' . $vInfo->values_id) . '">' . BUTTON_CANCEL . '</a>');
                    break;

                  default:
                    if (is_object($vInfo)) {
                      $heading[] = array('text' => '<b>' . xtc_get_values_detail($vInfo->values_id, $_SESSION['languages_id'], 'values_name') . '</b>');

                      $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=list&spage=' . $_GET['spage'] . '&vID=' . $vInfo->values_id . '&saction=edit_value') . '">' . BUTTON_EDIT . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=list&spage=' . $_GET['spage'] . '&vID=' . $vInfo->values_id . '&saction=delete_value') . '">' . BUTTON_DELETE . '</a>');
                      $contents[] = array('text' => '<br />' . TEXT_INFO_DATE_ADDED . ' ' . xtc_date_short($vInfo->date_added));
                      if (xtc_not_null($vInfo->last_modified)) $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . xtc_date_short($vInfo->last_modified));
                    }
                    break;
                }
              } else {
                switch ($_GET['action']) {
                  case 'new_option':
                    $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_OPTION . '</b>');

                    $contents = array('form' => xtc_draw_form('options', FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=insert_options'));
                    $contents[] = array('text' => TEXT_INFO_NEW_OPTION_INTRO);
                    $contents[] = array('text' => '<br />' . TEXT_INFO_OPTION_NAME . '<br />');
                    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                      $contents[] = array('text' => xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']) . '&nbsp;' . xtc_draw_input_field('options_name[' . $languages[$i]['id'] . ']'));
                    }
                    $contents[] = array('text' => '<br />' . TEXT_INFO_OPTION_DESCRIPTION . '<br />');
                    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                      $contents[] = array('text' => xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']) . '&nbsp;' . xtc_draw_textarea_field('options_description[' . $languages[$i]['id'] . ']', '', '45', '5'));
                    }
                    $contents[] = array('text' => '<br />' . TEXT_INFO_OPTION_CONTENT . '<br />' . xtc_cfg_select_content('options_content_group', ''));
                    $contents[] = array('text' => '<br />' . TEXT_INFO_OPTION_SORT . '<br />' . xtc_draw_input_field('sort_order'));
                    $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_INSERT . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $_GET['oID']) . '">' . BUTTON_CANCEL . '</a>');
                    break;

                  case 'edit_option':
                    $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_OPTION . '</b>');

                    $contents = array('form' => xtc_draw_form('options', FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->options_id . '&action=save_options'));
                    $contents[] = array('text' => TEXT_INFO_EDIT_OPTION_INTRO);
                    $contents[] = array('text' => '<br />' . TEXT_INFO_OPTION_NAME . '<br />');
                    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                      $contents[] = array('text' => xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']) . '&nbsp;' . xtc_draw_input_field('options_name[' . $languages[$i]['id'] . ']', xtc_get_options_detail($oInfo->options_id, $languages[$i]['id'], 'options_name')));
                    }
                    $contents[] = array('text' => '<br />' . TEXT_INFO_OPTION_DESCRIPTION . '<br />');
                    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                      $contents[] = array('text' => xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']) . '&nbsp;' . xtc_draw_textarea_field('options_description[' . $languages[$i]['id'] . ']', '', '45', '5', xtc_get_options_detail($oInfo->options_id, $languages[$i]['id'], 'options_description')));
                    }
                    $contents[] = array('text' => '<br />' . TEXT_INFO_OPTION_CONTENT . '<br />' . xtc_cfg_select_content('options_content_group', $oInfo->options_content_group));
                    $contents[] = array('text' => '<br />' . TEXT_INFO_OPTION_SORT . '<br />' . xtc_draw_input_field('sort_order', $oInfo->sort_order));
                    $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UPDATE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->options_id) . '">' . BUTTON_CANCEL . '</a>');
                    break;

                  case 'delete_option':
                    $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_OPTION . '</b>');

                    $contents = array('form' => xtc_draw_form('options', FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->options_id . '&action=deleteconfirm_options'));
                    $contents[] = array('text' => TEXT_INFO_DELETE_OPTION_INTRO);
                    $contents[] = array('text' => '<br /><b>' . xtc_get_options_detail($oInfo->options_id, $_SESSION['languages_id'], 'options_name') . '</b>');
                    $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->options_id) . '">' . BUTTON_CANCEL . '</a>');
                    break;

                  case 'import_attributes':
                    $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_IMPORT . '</b>');
                    
                    $on_off_array = array(array('id'=>0,'text'=>NO),array('id'=>1,'text'=>YES));
                    $contents = array('form' => xtc_draw_form('options', FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->options_id . '&action=import_confirm'));
                    $contents[] = array('text' => '<br />' . TEXT_INFO_ATTRIBUTES_OPTION);
                    $contents[] = array('text' => draw_on_off_selection('attributes_option', $on_off_array, false));
                    $contents[] = array('text' => '<br />' . TEXT_INFO_ATTRIBUTES_VALUE);
                    $contents[] = array('text' => draw_on_off_selection('attributes_value', $on_off_array, false));
                    $contents[] = array('text' => '<br />' . TEXT_INFO_ATTRIBUTES);
                    $contents[] = array('text' => draw_on_off_selection('attributes', $on_off_array, false));
                    $contents[] = array('text' => '<br />' . TEXT_INFO_ATTRIBUTES_DELETE);
                    $contents[] = array('text' => draw_on_off_selection('attributes_delete', $on_off_array, false));
                    $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_CONFIRM . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->options_id) . '">' . BUTTON_CANCEL . '</a>');
                    break;

                  case 'import_confirm':
                    $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_IMPORT . '</b>');

                    $contents = array('form' => xtc_draw_form('import', FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->options_id . '&action=import_confirm').$hidden_fields);
                    if ($javascript == '') {
                      $contents[] = array('text' => TEXT_INFO_IMPORT_FINISHED);
                      $contents[] = array('align' => 'center', 'text' => '<br /><a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->options_id) . '">' . BUTTON_BACK . '</a>');
                    } else {
                      $contents[] = array('text' => TEXT_INFO_IMPORT_WAIT);
                      $contents[] = array('align' => 'center','text' => '<img src="images/loading.gif" />');
                      $contents[] = array('text' => $javascript);
                    }
                    break;

                  default:
                    if (is_object($oInfo)) {
                      $heading[] = array('text' => '<b>' . xtc_get_options_detail($oInfo->options_id, $_SESSION['languages_id'], 'options_name') . '</b>');

                      $contents[] = array('align' => 'center', 'text' => '<a class="button btnbox" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->options_id . '&action=edit_option') . '">' . BUTTON_EDIT . '</a> <a class="button btnbox" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->options_id . '&action=delete_option') . '">' . BUTTON_DELETE . '</a>' . ' <a class="button btnbox" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_TAGS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->options_id . '&action=list') . '">' . BUTTON_VALUES . '</a>');
                      $contents[] = array('text' => '<br />' . TEXT_INFO_NUMBER_OPTION . ' ' . $oInfo->num_options);
                      $contents[] = array('text' => '<br />' . TEXT_INFO_DATE_ADDED . ' ' . xtc_date_short($oInfo->date_added));
                      if (xtc_not_null($oInfo->last_modified)) $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . xtc_date_short($oInfo->last_modified));
                    }
                    break;
                }
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