<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
?>
<div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_configuration.png'); ?></div>
<div class="pageHeading flt-l"><?php echo HEADING_PRODUCTS_CONTENT; ?>
  <div class="main pdg2">Products</div>
</div>
<?php

$icon_padding = 'style="padding-right:8px;"';
if( defined('USE_ADMIN_THUMBS_IN_LIST_STYLE')) {
  $admin_thumbs_size = 'style="'.USE_ADMIN_THUMBS_IN_LIST_STYLE.'"';
} else {
  $admin_thumbs_size = 'style="max-width: 40px; max-height: 40px;"';
}
$total_space_media_products = xtc_spaceUsed(DIR_FS_CATALOG.'media/products/');

if (!$action || in_array($action, array('delete', 'list'))) {
  ?>
  <div class="main flt-l pdg2 mrg5" style="margin-left:50px;">
    <?php
    echo xtc_draw_form('product_keywords', FILENAME_CONTENT_MANAGER, '', 'GET').PHP_EOL;
    echo xtc_draw_hidden_field('set', $_GET['set']).PHP_EOL;
    echo TEXT_SEARCH.draw_tooltip(TEXT_CONTENT_HELP).'&nbsp;'.xtc_draw_input_field('keywords', ((isset($_GET['keywords'])) ? $_GET['keywords'] : ''), 'size="30"');
    echo '&nbsp;<input type="submit" class="button no_top_margin"  style="vertical-align:top;" onclick="this.blur();" value="' . BUTTON_SEARCH . '"/>';
    if (isset($_GET['keywords']) && $_GET['keywords'] != '') {
      echo '<a class="button no_top_margin" style="vertical-align:top;" href="'.xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('keywords', 'page'))).'">'.BUTTON_RESET.'</a>';
    }
    ?>
    </form>
  </div>
  <div class="clear"></div>
  <table class="tableCenter">      
    <tr>
      <td class="boxCenterLeft">
        <table class="tableBoxCenter collapse">
          <?php
          if (in_array($action, array('delete', 'list')) && isset($_GET['pID']) && $_GET['pID'] != '') {
            ?>
            <tr class="dataTableHeadingRow">
              <td class="dataTableHeadingContent txta-c" style="width:10%" ><?php echo TABLE_HEADING_PRODUCTS_CONTENT_ID; ?></td>
              <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONTENT_NAME; ?></td>
              <td class="dataTableHeadingContent txta-c" style="width:5%"><?php echo TABLE_HEADING_LANGUAGE; ?></td>
              <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONTENT_FILE; ?></td>
              <td class="dataTableHeadingContent txta-c" style="width:1%"><?php echo TABLE_HEADING_CONTENT_FILESIZE; ?></td>
              <td class="dataTableHeadingContent txta-c" style="width:20%"><?php echo TABLE_HEADING_CONTENT_LINK; ?></td>
              <td class="dataTableHeadingContent txta-c" style="width:5%"><?php echo TABLE_HEADING_CONTENT_HITS; ?></td>
              <td class="dataTableHeadingContent txta-c" style="width:10%"><?php echo TABLE_HEADING_CONTENT_ACTION; ?></td>
            </tr>
            <tr class="dataTableRow" onmouseover="this.className='dataTableRowOver'" onmouseout="this.className='dataTableRow\">
              <td class="dataTableContent txta-c">--</td>
              <td class="dataTableContent"><?php echo '<a href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'coID'))). '">'.xtc_image(DIR_WS_ICONS . 'folder_parent.gif', ICON_FOLDER) . ' ..</a>'; ?></td>
              <td class="dataTableContent txta-c">--</td>
              <td class="dataTableContent txta-c">--</td>
              <td class="dataTableContent txta-c">--</td>
              <td class="dataTableContent txta-c">--</td>
              <td class="dataTableContent txta-c">--</td>
              <td class="dataTableContent txta-c">--</td>
            </tr>
            <?php
              $content_query_raw = "SELECT *
                                      FROM ".TABLE_PRODUCTS_CONTENT." pc
                                      JOIN ".TABLE_LANGUAGES." l
                                           ON pc.languages_id = l.languages_id
                                     WHERE products_id = '".(int)$_GET['pID']."'
                                  ORDER BY content_id";
              $content_query_split = new splitPageResults($page, $page_max_display_results, $content_query_raw, $content_query_numrows);
              $content_query = xtc_db_query($content_query_raw);
              while ($content = xtc_db_fetch_array($content_query)) {
                if ((!isset($_GET['coID']) || $_GET['coID'] == $content['content_id']) && !isset($oInfo)) {
                  $oInfo = new objectInfo($content);
                }

                if (isset($oInfo) && is_object($oInfo) && $content['content_id'] == $oInfo->content_id) {
                  echo '<tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'coID')) . 'coID=' . $oInfo->content_id . '&action=edit_products_content&last_action=list') . '\'">' . "\n";
                } else {
                  echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action','coID')) . 'action=list&coID=' . $content['content_id']) . '\'">' . "\n";
                }
                ?>
                  <td class="dataTableContent txta-c"><?php echo $content['content_id']; ?> </td>
                  <td class="dataTableContent"><?php echo $content['content_name']; ?></td>
                  <td class="dataTableContent txta-c"><?php echo xtc_image(DIR_WS_CATALOG.'lang/'.$content['directory'].'/admin/images/icon.gif'); ?></td>
                  <td class="dataTableContent"><?php echo $content['content_file']; ?></td>
                  <td class="dataTableContent txta-c"><?php echo xtc_filesize($content['content_file']); ?></td>
                  <td class="dataTableContent txta-c">
                    <?php
                      if ($content['content_link'] != '') {
                        echo '<a href="'.$content['content_link'].'" target="_blank">'.$content['content_link'].'</a>';
                      } else {
                        echo '--';
                      }
                    ?>
                  </td>
                  <td class="dataTableContent txta-c"><?php echo $content['content_read']; ?></td>
                  <td class="dataTableContent txta-r"><?php if (isset($oInfo) && is_object($oInfo) && $content['content_id'] == $oInfo->content_id) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('coID')) . 'coID=' . $content['content_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                </tr>
                <?php
              }
            ?>
        </table>
        <div class="smallText pdg2 flt-l"><?php echo $content_query_split->display_count($content_query_numrows, $page_max_display_results, $page, TEXT_DISPLAY_NUMBER_OF_CONTENT_MANAGER); ?></div>
        <div class="smallText pdg2 flt-r"><?php echo $content_query_split->display_links($content_query_numrows, $page_max_display_results, MAX_DISPLAY_PAGE_LINKS, $page, xtc_get_all_get_params(array('page'))); ?></div>
        <?php echo draw_input_per_page($PHP_SELF.'?'.xtc_get_all_get_params(array('page')), $cfg_max_display_results_key, $page_max_display_results); ?>
        <?php
          if ($action == 'list') {
          ?>
            <div class="smallText pdg2 flt-r"><?php echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'coID'))) . '">' . BUTTON_BACK . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action')).'action=new_products_content&last_action=list') . '">' . BUTTON_NEW_ATTACHMENT . '</a>'; ?></div>
          <?php
          }
        ?>
      </td>
      <?php
        $heading = array();
        $contents = array();
        switch ($action) {
          case 'delete':
            $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_CONTENT_MANAGER . '</b>');

            $contents = array('form' => xtc_draw_form('status', FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'special')) . 'special=delete_product'));
            $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
            $contents[] = array('text' => '<br /><b>' . $oInfo->content_name . '</b>');
            $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'coID')) . 'action=list&coID=' . $oInfo->content_id) . '">' . BUTTON_CANCEL . '</a>');
            break;

          default:
            if (isset($oInfo) && is_object($oInfo)) {
              $heading[] = array('text' => '<b>' . $oInfo->content_name . '</b>');

              $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'coID')) . 'coID=' . $oInfo->content_id . '&action=edit_products_content&last_action=list') . '">' . BUTTON_EDIT . '</a> 
                                                                  <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'coID')) . 'coID=' . $oInfo->content_id . '&action=delete') . '">' . BUTTON_DELETE . '</a>');
            }
            break;
        }

        if ( (xtc_not_null($heading)) && (xtc_not_null($contents)) ) {
          echo '            <td class="boxRight">' . "\n";
          $box = new box;
          echo $box->infoBox($heading, $contents);
          echo '            </td>' . "\n";
        }        
      } else {
        ?>
        <tr class="dataTableHeadingRow">
          <td class="dataTableHeadingContent txta-c" style="width:10%"><?php echo TABLE_HEADING_PRODUCTS_ID; ?></td>
          <td class="dataTableHeadingContent txta-c" style="width:10%"><?php echo TABLE_HEADING_MODEL; ?></td>
          <?php
          if( USE_ADMIN_THUMBS_IN_LIST=='true' ) { ?>
            <td class="dataTableHeadingContent txta-c" style="width:10%"><?php echo TABLE_HEADING_IMAGE; ?></td>
            <?php
          }
          ?>
          <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
          <td class="dataTableHeadingContent txta-c" style="width:10%"><?php echo TABLE_HEADING_CONTENT_ACTION; ?></td>
        </tr>
        <?php
          $from_str = " JOIN ".TABLE_PRODUCTS_CONTENT." pc
                             ON pc.products_id = p.products_id";
          $where_str = '';
          if (isset($_GET['keywords']) && $_GET['keywords'] != '') {
            $keywords = $_GET['keywords'] = !empty($_GET['keywords']) ? stripslashes(trim(urldecode($_GET['keywords']))) : false;
          
            if ($keywords) {
              $from_str = '';
              $where_str .= "WHERE p.products_id != 0";

              if (SEARCH_IN_MANU == 'true') {
                $from_str .= " LEFT OUTER JOIN ".TABLE_MANUFACTURERS." AS m ON (p.manufacturers_id = m.manufacturers_id) ";
              }

              if (SEARCH_IN_FILTER == 'true') {
                $from_str .= " LEFT JOIN ".TABLE_PRODUCTS_TAGS." pt ON (pt.products_id = p.products_id)
                               LEFT JOIN ".TABLE_PRODUCTS_TAGS_VALUES." ptv ON (ptv.options_id = pt.options_id AND ptv.values_id = pt.values_id AND ptv.status = '1' AND ptv.languages_id = '".(int)$_SESSION['languages_id']."') ";
              }

              if (SEARCH_IN_ATTR == 'true') {
                $from_str .= " LEFT JOIN ".TABLE_PRODUCTS_ATTRIBUTES." AS pa ON (p.products_id = pa.products_id) 
                               LEFT JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES." AS pov ON (pa.options_values_id = pov.products_options_values_id) ";
              }
          
              require_once (DIR_FS_INC.'xtc_parse_search_string.inc.php');
              $keywordcheck = xtc_parse_search_string($_GET['keywords'], $search_keywords);

              if ($keywordcheck) {
                include(DIR_FS_CATALOG.DIR_WS_INCLUDES.'build_search_query.php');
              }
            }
          }
        
          $content_query_raw = "SELECT p.products_id,
                                       p.products_model,
                                       p.products_image,
                                       pd.products_name
                                  FROM ".TABLE_PRODUCTS." p
                                  JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                       ON pd.products_id = p.products_id
                                          AND pd.language_id = '".(int)$_SESSION['languages_id']."'
                                       ".$from_str."
                                       ".$where_str."
                              GROUP BY p.products_id
                              ORDER BY pd.products_name, p.products_id ASC";
                  
          $content_query_split = new splitPageResults($page, $page_max_display_results, $content_query_raw, $content_query_numrows, 'p.products_id');          
          $content_query = xtc_db_query($content_query_raw);
          while ($content = xtc_db_fetch_array($content_query)) {
            if ((!isset($_GET['pID']) || $_GET['pID'] == $content['products_id']) && !isset($oInfo)) {
              $oInfo = new objectInfo($content);
            }

            if (isset($oInfo) && is_object($oInfo) && $content['products_id'] == $oInfo->products_id) {
              echo '<tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'pID')) . 'action=list&pID=' . $oInfo->products_id) . '\'">' . "\n";
            } else {
              echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action','pID')) . 'pID=' . $content['products_id']) . '\'">' . "\n";
            }
            ?>
              <td class="dataTableContent txta-c"><?php echo $content['products_id']; ?></td>
              <td class="dataTableContent txta-c"><?php echo $content['products_model']; ?></td>
                <?php
                if( USE_ADMIN_THUMBS_IN_LIST=='true' ) { ?>
                 <td class="dataTableContent txta-c">
                   <?php
                   echo xtc_product_thumb_image($content['products_image'], $content['products_name'], '','',$admin_thumbs_size);
                   ?>
                 </td>
                 <?php
                }
                ?>
                <td class="dataTableContent txta-l" style="padding-left: 5px;">
                  <?php 
                  echo '<a href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('pID')).'action=list&pID='.$content['products_id']) . '">' . xtc_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER, '', '', $icon_padding) . '</a>';
                  echo '<span style="vertical-align: 3px;">'.$content['products_name'].'</span>';
                  ?>
                </td>
                <td class="dataTableContent txta-r"><?php if (isset($oInfo) && is_object($oInfo) && $content['products_id'] == $oInfo->products_id) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'pID')) . 'action=list&pID=' . $content['products_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
            </tr>
            <?php
          }
          ?>
        </table>
        <div class="smallText pdg2 flt-l"><?php echo $content_query_split->display_count($content_query_numrows, $page_max_display_results, $page, TEXT_DISPLAY_NUMBER_OF_CONTENT_MANAGER); ?></div>
        <div class="smallText pdg2 flt-r"><?php echo $content_query_split->display_links($content_query_numrows, $page_max_display_results, MAX_DISPLAY_PAGE_LINKS, $page, xtc_get_all_get_params(array('page'))); ?></div>
        <?php echo draw_input_per_page($PHP_SELF.'?'.xtc_get_all_get_params(array('page')), $cfg_max_display_results_key, $page_max_display_results); ?>
        <div class="smallText pdg2 flt-r"><b><?php echo USED_SPACE; ?></b><span class="mrg5"><?php echo xtc_format_filesize($total_space_media_products); ?></span></div>
      </td>
      <?php
        $heading = array();
        $contents = array();
        switch ($action) {
          default:
            if (isset($oInfo) && is_object($oInfo)) {
              $heading[] = array('text' => '<b>' . $oInfo->products_name . '</b>');

              $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'pID')) . 'pID=' . $oInfo->products_id . '&action=list') . '">' . BUTTON_DETAILS . '</a> 
                                                                  <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'pID')) . 'pID=' . $oInfo->products_id . '&action=new_products_content') . '">' . BUTTON_NEW_ATTACHMENT . '</a>');
            }
            break;
        }

        if ( (xtc_not_null($heading)) && (xtc_not_null($contents)) ) {
          echo '            <td class="boxRight">' . "\n";
          $box = new box;
          echo $box->infoBox($heading, $contents);
          echo '            </td>' . "\n";
        }        
      }
      ?>
    </tr>
  </table>
  <?php
} else {
  if ($action == 'edit_products_content' && isset($g_coID) && (int)$g_coID > 0) {
    $content_query = xtc_db_query("SELECT *
                                     FROM ".TABLE_PRODUCTS_CONTENT."
                                    WHERE content_id = '".$g_coID."'
                                    LIMIT 1");
    $content = xtc_db_fetch_array($content_query);
  } else {
    $content = xtc_get_default_table_data(TABLE_PRODUCTS_CONTENT);
  }

  // get languages
  $languages_selected = $_SESSION['language_code'];
  $languages_id = (int)$_SESSION['languages_id'];

  $languages_array = array();
  for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
    if (isset($content) && $languages[$i]['id'] == $content['languages_id']) {
      $languages_selected = $languages[$i]['code'];
      $languages_id = $languages[$i]['id'];
    }
    $languages_array[] = array(
      'id' => $languages[$i]['code'],
      'text' => $languages[$i]['name'],
    );
  }

  // get all content files
  $files_array = array();
  $files = new DirectoryIterator(DIR_FS_CATALOG.'media/products/');
  foreach ($files as $file) {
    if ($file->isDot() === false
        && $file->isDir() === false
        && !in_array($file->getExtension(), array('php', 'html'))
        )
    {
      $files_array[] = $file->getFilename();
    }
  }
  sort($files_array);

  // get used content files
  $content_files_query = xtc_db_query("SELECT *
                                         FROM ".TABLE_PRODUCTS_CONTENT."
                                        WHERE content_file != ''
                                     GROUP BY content_file
                                     ORDER BY content_name");
  $content_files = array();
  while ($content_files_data = xtc_db_fetch_array($content_files_query)) {
    $content_files[] = array(
      'id' => $content_files_data['content_file'],
      'text' => $content_files_data['content_name'],
    );
    if (in_array($content_files_data['content_file'], $files_array)) {
      $key = array_search ($content_files_data['content_file'], $files_array);
      unset($files_array[$key]);
    }
  }

  if (count($files_array) > 0) {
    foreach ($files_array as $file) {
      $content_files[] = array(
        'id' => $file,
        'text' => $file,
      );
    }
  }      

  $keep_filename_array = array(
    array('id' => 1,'text' => YES),
    array('id' => 0,'text' => NO),
  );

  // add default value to array
  $default_array[]=array('id' => 'default','text' => TEXT_SELECT);
  $default_value = 'default';
  $content_files = array_merge($default_array,$content_files);
  // mask for product content      
  ?>
  <div class="clear"></div>
  <table class="tableCenter">      
    <tr>
      <td class="boxCenterLeft">
        <?php 
        if ($action != 'new_products_content') {
          echo xtc_draw_form('edit_content',FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action')) . 'action=edit_products_content&id=update_products&coID='.$g_coID,'post','enctype="multipart/form-data"').xtc_draw_hidden_field('coID',$g_coID);
        } else {
          echo xtc_draw_form('edit_content',FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action')) . 'action=new_products_content&id=insert_products','post','enctype="multipart/form-data"');
        }
        ?>
        <table class="tableConfig borderall">
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_PRODUCT; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo xtc_get_products_name($_GET['pID']) . xtc_draw_hidden_field('product', (int)$_GET['pID']); ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_LANGUAGE; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo xtc_draw_pull_down_menu('language_code',$languages_array,$languages_selected); ?></td>
          </tr>
          <?php
            if (GROUP_CHECK=='true') {
              $customers_statuses_array = xtc_get_customers_statuses();
              $customers_statuses_array = array_merge(array(array('id'=>'all', 'text'=>TXT_ALL)), $customers_statuses_array);
              ?>
              <tr>
                <td class="dataTableConfig col-left"><?php echo ENTRY_CUSTOMERS_STATUS; ?></td>
                <td class="dataTableConfig col-single-right">
                  <div class="customers-groups">
                    <?php
                      foreach ($customers_statuses_array as $customers_statuses) {
                        $checked = false;
                        if (strpos($content['group_ids'], 'c_'.$customers_statuses['id'].'_group') !== false || (!isset($_GET['coID']) && $customers_statuses['id'] == 'all')) {
                          $checked = true;
                        }
                        echo xtc_draw_checkbox_field('groups[]', $customers_statuses['id'], $checked).' ' .$customers_statuses['text'].'<br />';
                      }
                    ?>
                  </div>
                </td>
              </tr>
              <?php
            }
          ?>
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_TITLE_FILE; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('cont_title',$content['content_name'],'size="60"'); ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_LINK; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('cont_link',$content['content_link'],'size="60"'); ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_FILE_DESC; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo xtc_draw_textarea_field('file_comment','','100','30',$content['file_comment']); ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_CHOOSE_FILE; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo xtc_draw_pull_down_menu('select_file',$content_files,$default_value); ?><?php echo ' '.TEXT_CHOOSE_FILE_DESC; ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_UPLOAD_FILE; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo xtc_draw_file_field('file_upload').' '.TEXT_UPLOAD_FILE_LOCAL; ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_KEEP_FILENAME; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo draw_on_off_selection('keep_filename', $keep_filename_array, false, 'style="width: 155px"'); ?></td>
          </tr>
          <?php
            if ($content['content_file']!='') {
              ?>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_FILENAME; ?></td>
                <td class="dataTableConfig col-single-right"><?php echo xtc_draw_hidden_field('file_name',$content['content_file']).xtc_image('../'. DIR_WS_IMAGES. 'icons/filetype/icon_'.str_replace('.','',strstr($content['content_file'],'.')).'.gif').$content['content_file']; ?></td>
              </tr>
              <?php
            }
          ?>          
        </table>

        <?php 
          foreach(auto_include(DIR_FS_ADMIN.'includes/extra/modules/content_manager/products/','php') as $file) require ($file);
        ?>    

        <div class="flt-r mrg5 pdg2">
          <?php 
          if (isset($_GET['last_action']) && isset($_GET['cPath'])) {
            echo '<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('set', 'last_action', 'action', 'coID', 'search')) . 'action='.$_GET['last_action']).'">'.BUTTON_BACK.'</a>';
          } else {
            echo '<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'last_action')).((isset($_GET['last_action'])) ? 'action='.$_GET['last_action'] : '')).'">'.BUTTON_BACK.'</a>';
          }
          echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SAVE . '"/>';
          ?>
        </div>
        </form>
      </td>
    </tr>
  </table>
  <?php
}