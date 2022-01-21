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
<div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_content.png'); ?></div>
<div class="pageHeading flt-l"><?php echo HEADING_CONTENT_MANAGER_CONTENT; ?>
  <div class="main pdg2">Tools</div>
</div>
<?php

$icon_padding = 'style="padding-right:8px;"';
$total_space_media_products = xtc_spaceUsed(DIR_FS_CATALOG.'media/content/');

if (!$action || in_array($action, array('delete', 'list'))) {
  ?>
  <div class="main flt-l pdg2 mrg5" style="margin-left:50px;">
    <?php
    echo xtc_draw_form('product_keywords', FILENAME_CONTENT_MANAGER, '', 'GET').PHP_EOL;
    echo xtc_draw_hidden_field('set', $_GET['set']).PHP_EOL;
    echo TEXT_SEARCH.'&nbsp;'.xtc_draw_input_field('keywords', ((isset($_GET['keywords'])) ? $_GET['keywords'] : ''), 'size="30"');
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
          if (in_array($action, array('delete', 'list')) && isset($_GET['cID']) && (int)$_GET['cID'] > 0) {
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
                                      FROM ".TABLE_CONTENT_MANAGER_CONTENT." cmc
                                      JOIN ".TABLE_LANGUAGES." l
                                           ON cmc.languages_id = l.languages_id
                                     WHERE cmc.content_manager_id = '".(int)$_GET['cID']."'
                                  ORDER BY cmc.content_id";
              $content_query_split = new splitPageResults($page, $page_max_display_results, $content_query_raw, $content_query_numrows, 'cmc.content_manager_id');          
              $content_query = xtc_db_query($content_query_raw);     
              while ($content = xtc_db_fetch_array($content_query)) {
                if ((!isset($_GET['coID']) || $_GET['coID'] == $content['content_id']) && !isset($oInfo)) {
                  $oInfo = new objectInfo($content);
                }

                if (isset($oInfo) && is_object($oInfo) && $content['content_id'] == $oInfo->content_id) {
                  echo '<tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'coID')) . 'coID=' . $oInfo->content_id . '&action=edit_content_manager_content&last_action=list') . '\'">' . "\n";
                } else {
                  echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action','coID')) . 'action=list&coID=' . $content['content_id']) . '\'">' . "\n";
                }
                ?>
                  <td class="dataTableContent txta-c"><?php echo $content['content_id']; ?> </td>
                  <td class="dataTableContent"><?php echo $content['content_name']; ?></td>
                  <td class="dataTableContent txta-c"><?php echo xtc_image(DIR_WS_CATALOG.'lang/'.$content['directory'].'/admin/images/icon.gif'); ?></td>
                  <td class="dataTableContent"><?php echo $content['content_file']; ?></td>
                  <td class="dataTableContent txta-c"><?php echo xtc_filesize($content['content_file'], 'content'); ?></td>
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
            <div class="smallText pdg2 flt-r"><?php echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'coID'))) . '">' . BUTTON_BACK . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action')).'action=new_content_manager_content&last_action=list') . '">' . BUTTON_NEW_ATTACHMENT . '</a>'; ?></div>
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

            $contents = array('form' => xtc_draw_form('status', FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'special')) . 'special=delete_content'));
            $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
            $contents[] = array('text' => '<br /><b>' . $oInfo->content_name . '</b>');
            $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'coID')) . 'action=list&coID=' . $oInfo->content_id) . '">' . BUTTON_CANCEL . '</a>');
            break;

          default:
            if (isset($oInfo) && is_object($oInfo)) {
              $heading[] = array('text' => '<b>' . $oInfo->content_name . '</b>');

              $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'coID')) . 'coID=' . $oInfo->content_id . '&action=edit_content_manager_content&last_action=list') . '">' . BUTTON_EDIT . '</a> 
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
          <td class="dataTableHeadingContent txta-c" style="width:10%"><?php echo TABLE_HEADING_CONTENT_MANAGER_ID; ?></td>
          <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONTENT_MANAGER; ?></td>
          <td class="dataTableHeadingContent txta-c" style="width:10%"><?php echo TABLE_HEADING_CONTENT_ACTION; ?></td>
        </tr>
        <?php
          $from_str = " JOIN ".TABLE_CONTENT_MANAGER_CONTENT." cmc
                             ON cm.content_group = cmc.content_manager_id";
          $where_str = '';
          if ($_GET['keywords']) {
            $keywords = $_GET['keywords'] = !empty($_GET['keywords']) ? stripslashes(trim(urldecode($_GET['keywords']))) : false;
                    
            if ($keywords) {
              $from_str = '';
              require_once (DIR_FS_INC.'xtc_parse_search_string.inc.php');
              $keywordcheck = xtc_parse_search_string($_GET['keywords'], $search_keywords);

              if ($keywordcheck) {
                $where_str .= " AND ( ";
                for ($i = 0, $n = sizeof($search_keywords); $i < $n; $i ++) {
                  switch ($search_keywords[$i]) {
                    case '(' :
                    case ')' :
                    case 'and' :
                    case 'or' :
                      $where_str .= " ".$search_keywords[$i]." ";
                      break;
                    default :
                      $ent_keyword = encode_htmlentities($search_keywords[$i]); // umlauts
                      $ent_keyword = $ent_keyword != $search_keywords[$i] ? xtc_db_input($ent_keyword) : false;
                      $keyword = xtc_db_input($search_keywords[$i]);
                      $where_str .= " ( ";
                      $where_str .= "cm.content_title LIKE ('%".$keyword."%') ";
                      $where_str .= $ent_keyword ? "OR cm.content_title LIKE ('%".$ent_keyword."%') " : '';
                      $where_str .= "OR cm.content_heading LIKE ('%".$keyword."%') ";
                      $where_str .= $ent_keyword ? "OR cm.content_heading LIKE ('%".$ent_keyword."%') " : '';
                      $where_str .= "OR cm.content_text LIKE ('%".$keyword."%') ";
                      $where_str .= $ent_keyword ? "OR cm.content_text LIKE ('%".$ent_keyword."%') " : '';
                      $where_str .= " ) ";
                      break;
                  }
                }
                $where_str .= " )";
              }
            }
          }

          $content_query_raw = "SELECT cm.content_group,
                                       cm.content_title
                                  FROM ".TABLE_CONTENT_MANAGER." cm
                                       ".$from_str."
                                 WHERE cm.languages_id = '".(int)$_SESSION['languages_id']."'
                                       ".$where_str."
                              GROUP BY cm.content_group";
                  
          $content_query_split = new splitPageResults($page, $page_max_display_results, $content_query_raw, $content_query_numrows, 'cm.content_group');          
          $content_query = xtc_db_query($content_query_raw);     
          while ($content = xtc_db_fetch_array($content_query)) {
            if ((!isset($_GET['cID']) || $_GET['cID'] == $content['content_group']) && !isset($oInfo)) {
              $oInfo = new objectInfo($content);
            }

            if (isset($oInfo) && is_object($oInfo) && $content['content_group'] == $oInfo->content_group) {
              echo '<tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'cID')) . 'action=list&cID=' . $oInfo->content_group) . '\'">' . "\n";
            } else {
              echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action','cID')) . 'cID=' . $content['content_group']) . '\'">' . "\n";
            }
            ?>
              <td class="dataTableContent txta-c"><?php echo $content['content_group']; ?></td>
              <td class="dataTableContent txta-l" style="padding-left: 5px;">
                <?php 
                echo '<a href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'cID')).'action=list&cID='.$content['content_group']) . '">' . xtc_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER, '', '', $icon_padding) . '</a>';
                echo '<span style="vertical-align: 3px;">'.$content['content_title'].'</span>';
                ?>
              </td>
              <td class="dataTableContent txta-r"><?php if (isset($oInfo) && is_object($oInfo) && $content['content_group'] == $oInfo->content_group) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'cID')) . 'action=list&cID=' . $content['content_group']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
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
              $heading[] = array('text' => '<b>' . $oInfo->content_title . '</b>');

              $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'cID')) . 'cID=' . $oInfo->content_group . '&action=list') . '">' . BUTTON_DETAILS . '</a> 
                                                                  <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'cID')) . 'cID=' . $oInfo->content_group . '&action=new_content_manager_content') . '">' . BUTTON_NEW_ATTACHMENT . '</a>');
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
  if ($action =='edit_content_manager_content' && isset($g_coID) && (int)$g_coID != 0) {
    $content_query = xtc_db_query("SELECT *
                                     FROM ".TABLE_CONTENT_MANAGER_CONTENT."
                                    WHERE content_id = '".$g_coID."'
                                    LIMIT 1");
    $content = xtc_db_fetch_array($content_query);
  } else {
    $content = xtc_get_default_table_data(TABLE_CONTENT_MANAGER_CONTENT);
  }

  // get content
  $content_query = xtc_db_query("SELECT content_group,
                                        content_title
                                   FROM ".TABLE_CONTENT_MANAGER."
                                  WHERE languages_id = '".(int)$_SESSION['languages_id']."'
                                    AND content_group = '".(int)$_GET['cID']."'");
  $content_data = xtc_db_fetch_array($content_query);

  // get languages
  $languages_selected = $_SESSION['language_code'];
  $languages_id = (int)$_SESSION['languages_id'];

  $languages_array = array();
  for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
    if ($languages[$i]['id'] == $content['languages_id']) {
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
  $files = new DirectoryIterator(DIR_FS_CATALOG.'media/content/');
  foreach ($files as $file) {
    if ($file->isDot() === false
        && $file->isDir() === false
        && !in_array($file->getExtension(), array('php', 'html'))
        )
    {
      $files_array[] = $file->getFilename();
    }
  }

  // get used content files
  $content_files_query = xtc_db_query("SELECT *
                                         FROM ".TABLE_CONTENT_MANAGER_CONTENT."
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
  array_multisort(array_column($content_files, 'text'), SORT_ASC, $content_files);

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
        if ($action != 'new_content_manager_content') {
          echo xtc_draw_form('edit_content',FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action')) . 'action=edit_content_manager_content&id=update_content_manager&coID='.$g_coID,'post','enctype="multipart/form-data"').xtc_draw_hidden_field('coID',$g_coID);
        } else {
          echo xtc_draw_form('edit_content',FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action')) . 'action=new_content_manager_content&id=insert_content_manager','post','enctype="multipart/form-data"');
        }
        ?>
        <table class="tableConfig borderall">
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_CONTENT_MANAGER_CONTENT; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo $content_data['content_title'] . xtc_draw_hidden_field('product', (int)$_GET['cID']); ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_LANGUAGE; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo xtc_draw_pull_down_menu('language_code',$languages_array,$languages_selected); ?></td>
          </tr>
          <?php
            if (GROUP_CHECK=='true') {
              $customers_statuses_array = xtc_get_customers_statuses();
              $customers_statuses_array=array_merge(array(array('id'=>'all','text'=>TXT_ALL)),$customers_statuses_array);
              ?>
                <td class="dataTableConfig col-left"><?php echo ENTRY_CUSTOMERS_STATUS; ?></td>
                <td class="dataTableConfig col-single-right">
                  <div class="customers-groups">
                    <?php
                      for ($i=0;$n=sizeof($customers_statuses_array),$i<$n;$i++) {
                        $checked = false;
                        if (strpos($content['group_ids'],'c_'.$customers_statuses_array[$i]['id'].'_group') !== false) {
                          $checked = true;
                        }
                        echo xtc_draw_checkbox_field('groups[]', $customers_statuses_array[$i]['id'], $checked).' '.$customers_statuses_array[$i]['text'].'<br />';
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
          foreach(auto_include(DIR_FS_ADMIN.'includes/extra/modules/content_manager/content/','php') as $file) require ($file);
        ?>    

        <div class="flt-r mrg5 pdg2">
          <?php
          echo '<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'last_action')).((isset($_GET['last_action'])) ? 'action='.$_GET['last_action'] : '')).'">'.BUTTON_BACK.'</a>';
          echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SAVE . '"/>';
          ?>
        </div>
        </form>
      </td>
    </tr>
  </table>
  <?php
}