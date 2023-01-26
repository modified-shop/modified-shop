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
<div class="pageHeading flt-l"><?php echo HEADING_CONTENT; ?>
  <div class="main pdg2">Tools</div>
</div>
<?php

if (!$action || $action == 'delete') {
  $file_flag_array = array();
  $file_flag_select_array = array();
  $file_flag_query = xtc_db_query("SELECT * 
                                     FROM ".TABLE_CM_FILE_FLAGS);
  while ($file_flag_result = xtc_db_fetch_array($file_flag_query)) {
    $file_flag_array[$file_flag_result['file_flag']] = $file_flag_result['file_flag_name'];
    $file_flag_select_array[] = array('id' => $file_flag_result['file_flag'], 'text' => $file_flag_result['file_flag_name']);
  }

  $sorting = (isset($_GET['sorting']) ? $_GET['sorting'] : '');
  if (xtc_not_null($sorting)) {
    switch ($sorting) {
      case 'name':
        $sort = 'content_title ASC';
        break;
      case 'name-desc':
        $sort = 'content_title DESC';
        break;
      case 'group':
        $sort = 'content_group ASC';
        break;
      case 'group-desc':
        $sort = 'content_group DESC';
        break;
      case 'sort':
        $sort = 'sort_order ASC';
        break;
      case 'sort-desc':
        $sort = 'sort_order DESC';
        break;
      case 'active':
        $sort = 'content_active ASC';
        break;
      case 'active-desc':
        $sort = 'content_active DESC';
        break;
      case 'file':
        $sort = 'content_file ASC';
        break;
      case 'file-desc':
        $sort = 'content_file DESC';
        break;
      case 'box':
        $sort = 'file_flag ASC';
        break;
      case 'box-desc':
        $sort = 'file_flag DESC';
        break;
      case 'status':
        $sort = 'content_status ASC';
        break;
      case 'status-desc':
        $sort = 'content_status DESC';
        break;
      default:
        $sort = 'content_group ASC';
        break;
    }
  } else {
    $sort = 'content_group ASC';
  }
  $sort .= ', sort_order ASC, content_id ASC';
  ?>
  <div class="main flt-l pdg2 mrg5" style="margin-left:50px;">
    <?php echo xtc_draw_form('file_flag', FILENAME_CONTENT_MANAGER, '', 'get').xtc_draw_hidden_field('special', 'file_flag'); ?>
    <?php echo TEXT_FILE_FLAG . ' ' . xtc_draw_pull_down_menu('file_flag', array_merge(array (array ('id' => '', 'text' => TXT_ALL)), $file_flag_select_array), isset($_SESSION['file_flag']) ? $_SESSION['file_flag'] : '', 'onChange="this.form.submit();"'); ?>
    </form>
  </div>
  <div class="main flt-l pdg2 mrg5" style="margin-left:50px;">
    <?php
    echo xtc_draw_form('product_keywords', FILENAME_CONTENT_MANAGER, '', 'GET').PHP_EOL;
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
          <tr class="dataTableHeadingRow">
            <td class="dataTableHeadingContent txta-c" style="width:8%"><?php echo TABLE_HEADING_CONTENT_GROUP.xtc_sorting(FILENAME_CONTENT_MANAGER, 'group'); ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONTENT_TITLE.xtc_sorting(FILENAME_CONTENT_MANAGER, 'name'); ?></td>
            <td class="dataTableHeadingContent txta-c" style="width:8%"><?php echo TABLE_HEADING_CONTENT_SORT.xtc_sorting(FILENAME_CONTENT_MANAGER, 'sort'); ?></td>
            <td class="dataTableHeadingContent txta-c" style="width:8%"><?php echo TABLE_HEADING_STATUS_ACTIVE.xtc_sorting(FILENAME_CONTENT_MANAGER, 'active') ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONTENT_FILE.xtc_sorting(FILENAME_CONTENT_MANAGER, 'file'); ?></td>
            <td class="dataTableHeadingContent txta-c"><?php echo TABLE_HEADING_CONTENT_BOX.xtc_sorting(FILENAME_CONTENT_MANAGER, 'box'); ?></td>
            <td class="dataTableHeadingContent txta-c" style="width:8%"><?php echo TABLE_HEADING_CONTENT_STATUS.xtc_sorting(FILENAME_CONTENT_MANAGER, 'status'); ?></td>
            <td class="dataTableHeadingContent txta-c"><?php echo TEXT_CONTENT_META_ROBOTS ?></td>
            <td class="dataTableHeadingContent txta-c" style="width:5%"><?php echo TABLE_HEADING_CONTENT_ACTION; ?>&nbsp;</td>
          </tr>
          <?php
          $where = '';
          if (isset($_SESSION['file_flag']) && $_SESSION['file_flag'] != '') {
            $where .= " AND file_flag = '".(int)$_SESSION['file_flag']."'";
          }
          if (isset($_GET['pID']) && (int)$_GET['pID'] > 0) {
            ?>
            <tr class="dataTableRow" onmouseover="this.className='dataTableRowOver'" onmouseout="this.className='dataTableRow\">
              <td class="dataTableContent txta-c">--</td>
              <td class="dataTableContent"><?php echo '<a href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('pID', 'coID', 'coIndex'))). '">'.xtc_image(DIR_WS_ICONS . 'folder_parent.gif', ICON_FOLDER) . ' ..</a>'; ?></td>
              <td class="dataTableContent txta-c">--</td>
              <td class="dataTableContent txta-c">--</td>
              <td class="dataTableContent txta-c">--</td>
              <td class="dataTableContent txta-c">--</td>
              <td class="dataTableContent txta-c">--</td>
              <td class="dataTableContent txta-c">--</td>
              <td class="dataTableContent txta-c">--</td>
            </tr>
            <?php
            $where .= " AND parent_id = '".$_GET['pID']."'";
          } elseif (isset($_GET['keywords']) && $_GET['keywords'] != '') {
            $keywords = $_GET['keywords'] = !empty($_GET['keywords']) ? stripslashes(trim(urldecode($_GET['keywords']))) : false;
            if ($keywords) {
              require_once (DIR_FS_INC.'xtc_parse_search_string.inc.php');
              $keywordcheck = xtc_parse_search_string($_GET['keywords'], $search_keywords);

              if ($keywordcheck) {             
                $where .= " AND ( ";
                for ($i = 0, $n = sizeof($search_keywords); $i < $n; $i ++) {
                  switch ($search_keywords[$i]) {
                    case '(' :
                    case ')' :
                    case 'and' :
                    case 'or' :
                      $where .= " ".$search_keywords[$i]." ";
                      break;
                    default :
                      $ent_keyword = encode_htmlentities($search_keywords[$i]); // umlauts
                      $ent_keyword = $ent_keyword != $search_keywords[$i] ? xtc_db_input($ent_keyword) : false;
                      $keyword = xtc_db_input($search_keywords[$i]);
                      $where .= " ( ";
                      $where .= "content_title LIKE ('%".$keyword."%') ";
                      $where .= $ent_keyword ? "OR content_title LIKE ('%".$ent_keyword."%') " : '';
                      $where .= "OR content_heading LIKE ('%".$keyword."%') ";
                      $where .= $ent_keyword ? "OR content_heading LIKE ('%".$ent_keyword."%') " : '';
                      $where .= "OR content_text LIKE ('%".$keyword."%') ";
                      $where .= $ent_keyword ? "OR content_text LIKE ('%".$ent_keyword."%') " : '';
                      $where .= " ) ";
                      break;
                  }
                }
                $where .= " )";
              }
            }
          } else {
            $where .= " AND parent_id = '0'";
          }
          $content_query_raw = "SELECT *
                                  FROM ".TABLE_CONTENT_MANAGER."
                                 WHERE languages_id = '".(int)$_SESSION['languages_id']."'
                                       ".$where."
                              ORDER BY ".$sort;
          $content_query_split = new splitPageResults($page, $page_max_display_results, $content_query_raw, $content_query_numrows);
          $content_query = xtc_db_query($content_query_raw);
          while ($content_data = xtc_db_fetch_array($content_query)) {

            if ((!isset($_GET['coID']) || $_GET['coID'] == $content_data['content_group']) && (!isset($_GET['coIndex']) || $_GET['coIndex'] == $content_data['content_group_index']) && !isset($oInfo)) {
              $oInfo = new objectInfo($content_data);
            }
          
            $content_data['content_count'] = 0;
            if (!isset($_GET['pID']) || (int)$_GET['pID'] == 0) {
              $check_query = xtc_db_query("SELECT *
                                             FROM ".TABLE_CONTENT_MANAGER."
                                            WHERE languages_id = '".(int)$_SESSION['languages_id']."'
                                              AND parent_id = '".(int)$content_data['content_id']."'");
              $content_data['content_count'] = xtc_db_num_rows($check_query);
            }
          
            if (isset($oInfo) && is_object($oInfo) && $content_data['content_group'] == $oInfo->content_group && $content_data['content_group_index'] == $oInfo->content_group_index) {
              echo '<tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'coID', 'coIndex')) . 'coID=' . $oInfo->content_group . '&coIndex=' . $oInfo->content_group_index . '&action=edit') . '\'">' . "\n";
            } else {
              echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'coID', 'coIndex')) . 'coID=' . $content_data['content_group'] . '&coIndex=' . $content_data['content_group_index']) . '\'">' . "\n";
            }
            ?>
              <td class="dataTableContent txta-c"><?php echo $content_data['content_group']; ?></td>
              <td class="dataTableContent">
              <?php 
                if (isset($_GET['pID']) && (int)$_GET['pID'] > 0) {
                  echo $content_data['content_title'] . (($content_data['content_delete'] == '0') ? ' <span class="col-red">*</span>' : '');
                } else {
                  echo '<a href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('coID', 'coIndex', 'pID')) . 'pID=' . $content_data['content_id']). '">'.xtc_image(DIR_WS_ICONS . (($content_data['content_count'] > 0) ? 'folder_page.gif' : 'folder.gif'), ICON_FOLDER, '', '', $icon_padding) . '</a>' . '<span style="vertical-align: 3px;">' . $content_data['content_title'] . (($content_data['content_delete'] == '0') ? ' <span class="col-red">*</span>' : '').'</span>'; 
                }
              ?>
              </td>
              <td class="dataTableContent txta-c"><?php echo $content_data['sort_order']; ?></td>
              <td  class="dataTableContent txta-c">
                <?php
                if ($content_data['content_active'] == '1') {
                  echo xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 12, 12, 'style="margin-right:5px;"') . '<a href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('special', 'coID', 'flag', 'coIndex')).'special=setaflag&flag=0&coID=' . $content_data['content_group'] . '&coIndex=' . $content_data['content_group_index'], 'NONSSL') . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 12, 12) . '</a>';
                } else {
                  echo '<a href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('special', 'coID', 'flag', 'coIndex')).'special=setaflag&flag=1&coID=' . $content_data['content_group'] . '&coIndex=' . $content_data['content_group_index'], 'NONSSL') . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 12, 12, 'style="margin-right:5px;"') . '</a>' . xtc_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 12, 12);
                }
                ?>
              </td>
              <td class="dataTableContent"><?php echo (($content_data['content_file'] != '') ? $content_data['content_file'] : '--'); ?></td>
              <td class="dataTableContent txta-c"><?php echo $file_flag_array[$content_data['file_flag']]; ?></td>
              <td  class="dataTableContent txta-c">
                <?php
                if ($content_data['content_status'] == '1') {
                  echo xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 12, 12, 'style="margin-right:5px;"') . '<a href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('special', 'coID', 'flag', 'coIndex')).'special=setsflag&flag=0&coID=' . $content_data['content_group'] . '&coIndex=' . $content_data['content_group_index'], 'NONSSL') . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 12, 12) . '</a>';
                } else {
                  echo '<a href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('special', 'coID', 'flag', 'coIndex')).'special=setsflag&flag=1&coID=' . $content_data['content_group'] . '&coIndex=' . $content_data['content_group_index'], 'NONSSL') . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 12, 12, 'style="margin-right:5px;"') . '</a>' . xtc_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 12, 12);
                }
                ?>
              </td>
              <td class="dataTableContent txta-c"><?php echo $content_data['content_meta_robots']; ?>&nbsp;</td>
              <td class="dataTableContent txta-r"><?php if (isset($oInfo) && is_object($oInfo) && $content_data['content_group'] == $oInfo->content_group) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'coID', 'coIndex')) . '&coID=' . $content_data['content_group'] . '&coIndex=' . $content_data['content_group_index']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
            </tr> 
            <?php
          }
          ?>
        </table>

        <div class="smallText pdg2 flt-l"><?php echo $content_query_split->display_count($content_query_numrows, $page_max_display_results, $page, TEXT_DISPLAY_NUMBER_OF_CONTENT_MANAGER); ?></div>
        <div class="smallText pdg2 flt-r"><?php echo $content_query_split->display_links($content_query_numrows, $page_max_display_results, MAX_DISPLAY_PAGE_LINKS, $page, ((isset($_GET['pID']) && (int)$_GET['pID'] > 0) ? '&pID='.(int)$_GET['pID'] : '')); ?></div>
        <?php echo draw_input_per_page($PHP_SELF, $cfg_max_display_results_key, $page_max_display_results); ?>
        <div class="smallText pdg2 flt-r"><?php echo ((isset($_GET['pID']) && (int)$_GET['pID'] > 0) ? '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'coID', 'pID'))) . '">' . BUTTON_BACK . '</a>' : '') . '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'coID')).'action=new') . '">' . BUTTON_NEW_CONTENT . '</a>'; ?></div>
      </td>
      <?php
        $heading = array();
        $contents = array();
        switch ($action) {
          case 'delete':
            $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_CONTENT_MANAGER . '</b>');

            $contents = array('form' => xtc_draw_form('status', FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'flag')) . 'special=delete'));
            $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
            $contents[] = array('text' => '<br /><b>' . $oInfo->content_title . '</b>');
            $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'flag'))) . '">' . BUTTON_CANCEL . '</a>');
            break;

          default:
            if (isset($oInfo) && is_object($oInfo)) {
              $heading[] = array('text' => '<b>' . $oInfo->content_title . '</b>');

              $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('coID', 'coIndex')) . 'coID=' . $oInfo->content_group . '&coIndex=' . $oInfo->content_group_index . '&action=edit') . '">' . BUTTON_EDIT . '</a>'
                                                                  .((!isset($_GET['pID']) || (int)$_GET['pID'] < 1) ? '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('coID', 'coIndex', 'pID')) . 'pID=' . $oInfo->content_id) . '">' . BUTTON_DETAILS . '</a>' : '').'
                                                                  <a class="button" onclick="javascript:window.open(\''.xtc_href_link_from_admin('popup_content.php','coID='.$oInfo->content_group).'&preview=true&coIndex='.$oInfo->content_group_index.'\', \'popup\', \'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=640,height=600\')">'.TEXT_PREVIEW.'</a>'
                                                                  .(($oInfo->content_delete == '1') ? '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('coID', 'coIndex')) . 'coID=' . $oInfo->content_group . '&coIndex=' . $oInfo->content_group_index . '&action=delete') . '">' . BUTTON_DELETE . '</a>' : ''));

              $contents[] = array ('text' => '<br />'.TEXT_DATE_ADDED.' '.xtc_datetime_short($oInfo->date_added));
              $contents[] = array ('text' => TEXT_LAST_MODIFIED.' '.xtc_datetime_short($oInfo->last_modified));
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
  <?php 
} else {

  $content_status_array = array(
    array('id'=>1,'text'=>CFG_TXT_YES),
    array('id'=>0,'text'=>CFG_TXT_NO),
  );

  // content array
  $content = array();
  for ($i=0, $n=count($languages); $i<$n; $i++) {
    $content_query = xtc_db_query("SELECT *
                                     FROM ".TABLE_CONTENT_MANAGER."
                                    WHERE content_group='".$g_coID."'
                                      AND content_group_index ='". $coIndex ."'
                                      AND languages_id = '".$languages[$i]['id']."'
                                      ORDER BY content_id
                                  ");
    $z=0;
    if (xtc_db_num_rows($content_query) > 0) {
      while ($cont = xtc_db_fetch_array($content_query)) {
        $content[$z][$languages[$i]['id']] = $cont;
        $z++;
      }
    } else {
      $content_array = xtc_get_default_table_data(TABLE_CONTENT_MANAGER);
      $content_array['languages_id'] = $languages[$i]['id'];
      $content[$z][$languages[$i]['id']] = $content_array;
      $z++;
    }
  }
  
  // some defaults
  $default_content = $content[0][$_SESSION['languages_id']];
  $content_count = count($content);
  $languages_count = count($languages);
  $counter = $languages_count * $content_count;    

  // check content array
  for ($i=0; $i<$content_count; $i++) {
    for ($l=0; $l<$languages_count; $l++) {
      if (!isset($content[$i][$languages[$l]['id']])) {
        $content[$i][$languages[$l]['id']] = array('languages_id' => $languages[$i]['id']);
      }
    }
  }

  // sub content
  $query_string = (($action != 'new') ? " AND file_flag = '".(int)$default_content['file_flag']."'" : '');
  $content_data_query = xtc_db_query("SELECT content_id,
                                             content_title
                                        FROM ".TABLE_CONTENT_MANAGER."
                                       WHERE parent_id = '0'
                                             ".$query_string."
                                         AND content_group != '".$g_coID."'
                                         AND languages_id = '".(int)$_SESSION['languages_id']."'
                                         ORDER BY content_id
                                         ");
  $content_data_array = array(array('id' => '', 'text' => '---'));   
  while ($content_data = xtc_db_fetch_array($content_data_query)) {
    $content_data_array[] = array('id' => $content_data['content_id'],
                                  'text' => $content_data['content_title']);
  }

  // file flag
  $file_flag_array = array();    
  $file_flag_sql = xtc_db_query("SELECT file_flag as id, 
                                        file_flag_name as text 
                                   FROM " . TABLE_CM_FILE_FLAGS);
  while ($file_flag = xtc_db_fetch_array($file_flag_sql)) {
    $file_flag_array[] = array('id' => $file_flag['id'], 
                               'text' => $file_flag['text']);
  }

  // content file
  $content_files = array();
  $files = new DirectoryIterator(DIR_FS_CATALOG.'media/content/');
  foreach ($files as $file) {
    if ($file->isDot() === false
        && $file->isDir() === false
        )
    {
      $content_files[] = array(
        'id' => $file->getFilename(),
        'text' => $file->getFilename()
      );
    }
  }
  array_multisort(array_column($content_files, 'text'), SORT_ASC, $content_files);

  ?>
  <div class="clear"></div>
  <table class="tableCenter">      
    <tr>
      <td class="boxCenterLeft">
        <?php
        if ($action != 'new') {
          echo xtc_draw_form('edit_content', FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('id')) . 'id=update', 'post', 'enctype="multipart/form-data"'). PHP_EOL;
          echo xtc_draw_hidden_field('coID',$g_coID). PHP_EOL;
          echo xtc_draw_hidden_field('content_group_index', $coIndex). PHP_EOL;
        } else {
          echo xtc_draw_form('edit_content', FILENAME_CONTENT_MANAGER, 'action=edit&id=insert', 'post', 'enctype="multipart/form-data"'). PHP_EOL;
        }
        echo xtc_draw_hidden_field('content_count', $content_count). PHP_EOL;
        ?>
        <div style="padding:5px;clear:both;">
          <table class="tableConfig borderall" style="width:99%">
            <?php
              if ($default_content['content_delete'] != '0' || $action == 'new') {
                ?>
                <tr>
                  <td class="dataTableConfig col-left" style="min-width:205px;"><?php echo TEXT_GROUP; ?></td>
                  <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('content_group',((isset($default_content['content_group'])) ? $default_content['content_group'] : ''),'size="5"') . ' '. TEXT_GROUP_DESC; ?></td>
                </tr>
                <?php
              } elseif ($action == 'edit') {
                echo xtc_draw_hidden_field('content_group', $default_content['content_group']);
                ?>
                <tr>
                  <td class="dataTableConfig col-left" style="min-width:205px;"><?php echo TEXT_GROUP; ?></td>
                  <td class="dataTableConfig col-single-right"><?php echo $default_content['content_group']; ?></td>
                </tr>
                <?php
              }
            ?>
            <tr>
              <td class="dataTableConfig col-left"><?php echo TEXT_FILE_FLAG; ?></td>
              <td class="dataTableConfig col-single-right"><?php echo xtc_draw_pull_down_menu('file_flag', $file_flag_array, ((isset($_SESSION['file_flag']) && $_SESSION['file_flag'] != '' && $action == 'new') ? $_SESSION['file_flag'] : ((isset($_GET['pID']) && (int)$_GET['pID'] > 0 && $action == 'new') ? get_file_flag($_GET['pID']) : $default_content['file_flag'])), 'id="file_flag"'); ?></td>
            </tr>
            <?php 
            if (CONTENT_CHILDS_ACTIV == 'true' 
                && count($content_data_array) > 1 
                && (check_content_childs($default_content['content_id'], $_SESSION['languages_id']) === false
                    || $action == 'new'
                    )
                )
            { 
              ?>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PARENT; ?></td>
                <td class="dataTableConfig col-single-right"><?php echo xtc_draw_pull_down_menu('parent_id', $content_data_array, ((isset($_GET['pID']) && (int)$_GET['pID'] > 0 && $action == 'new') ? (int)$_GET['pID'] : $default_content['parent_id']), 'id="parent_id"'); ?><span style="display:inline-block;vertical-align:top;padding:5px 0 0 5px;line-height:24px;"><?php echo xtc_draw_checkbox_field('parent_check', 'yes', (((isset($_GET['pID']) && (int)$_GET['pID'] > 0 && $action == 'new') || $default_content['parent_id'] > 0) ? true : false)).' '.TEXT_PARENT_DESCRIPTION; ?></span></td>
              </tr>
              <?php 
            }
            ?>
            <tr>
              <td class="dataTableConfig col-left"><?php echo TEXT_SORT_ORDER; ?></td>
              <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('sort_order', ((isset($default_content['sort_order'])) ? $default_content['sort_order'] : ''), 'size="5"'); ?></td>
            </tr>                                  
            <?php
              $meta_robots = explode(', ', $default_content['content_meta_robots']);
              $content_meta = array();
              foreach ($meta_robots as $key => $value) {
                $content_meta[0]['meta_robots'][$value] = $value;
              }
            ?>
            <tr>
              <td class="dataTableConfig col-left"><?php echo TEXT_CONTENT_META_ROBOTS; ?>: </td>
              <td class="dataTableConfig col-single-right">
                <?php echo xtc_draw_checkbox_field('content_meta_robots[]','noindex', ((isset($content_meta[0]['meta_robots']['noindex'])) ? $content_meta[0]['meta_robots']['noindex'] : false)).TEXT_CONTENT_NOINDEX.'<br/>'.
                           xtc_draw_checkbox_field('content_meta_robots[]','nofollow', ((isset($content_meta[0]['meta_robots']['nofollow'])) ? $content_meta[0]['meta_robots']['nofollow'] : false)).TEXT_CONTENT_NOFOLLOW.'<br/>'.
                           xtc_draw_checkbox_field('content_meta_robots[]','noodp', ((isset($content_meta[0]['meta_robots']['noodp'])) ? $content_meta[0]['meta_robots']['noodp'] : false)).TEXT_CONTENT_NOODP;
                ?>
              </td>
            </tr>
          </table>
        </div>

        <?php 
          foreach(auto_include(DIR_FS_ADMIN.'includes/extra/modules/content_manager/pages/','php') as $file) require ($file);
        ?>    

        <div style="padding:5px;clear:both;">
          <div class="flt-r mrg5 pdg2">
            <input type="submit" class="button" onclick="this.blur();" value="<?php echo BUTTON_SAVE; ?>"/>
          </div>
          <div class="flt-r mrg5 pdg2">
            <input class="button" type="submit" onclick="this.blur();" value="<?php echo BUTTON_UPDATE; ?>" name="page_update"/>
          </div>
          <div class="flt-r mrg5 pdg2">
            <a class="button" onclick="this.blur();" href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action'))); ?>"><?php echo BUTTON_BACK; ?></a>
          </div>
        </div>

        <div style="padding:5px;clear:both;">
        <script type="text/javascript" src="includes/lang_tabs_menu/lang_tabs_menu.js"></script>
        <?php
        if (USE_WYSIWYG=='true') {
          $query = xtc_db_query("SELECT code FROM ". TABLE_LANGUAGES ." WHERE languages_id='".(int)$_SESSION['languages_id']."'");
          $data = xtc_db_fetch_array($query);
          for ($i=0; $i<$content_count; $i++) {
            for ($l=0; $l<$languages_count; $l++) {
              echo xtc_wysiwyg('content_manager', $data['code'], $content[$i][$languages[$l]['id']]['languages_id'], $i);
            }
          }
        }
        $langtabs = '<div class="tablangmenu"><ul>';
        $csstabstyle = 'border: 1px solid #aaaaaa; padding: 4px; width: 99%; margin-top: -1px; margin-bottom: 10px; float: left;background: #F3F3F3;';
        $csstab = '<style type="text/css">' .  '#tab_lang_0' . '{display: block;' . $csstabstyle . '}';
        $csstab_nojs = '<style type="text/css">';    
        $cnt = 0;
        $hidden_coIndex = '';
  
        for ($i=0; $i<$content_count; $i++) {
          for ($l=0; $l<$languages_count; $l++) {
            $tabtmp = "\'tab_lang_$cnt\'," ;
            $coIndex = '';
            //FIX wenn es bei gleicher languages_id mehrere gleiche content_group gibt
            if ($counter > $languages_count && $i > 0) {
               $coIndex = ' ('. $i .')';
               $hidden_coIndex .= xtc_draw_hidden_field('content_new_group_index['.$i.']['.$languages[$l]['id'].']', $i). PHP_EOL;
            }
            $langtabs.= '<li onclick="showTab('. $tabtmp. $counter.')" style="cursor: pointer;" id="tabselect_' . $cnt .'">' .xtc_image(DIR_WS_LANGUAGES . $languages[$l]['directory'] .'/admin/images/'. $languages[$l]['image'], $languages[$l]['name']) . ' ' . $languages[$l]['name'].$coIndex.'</li>';
            if($cnt > 0) $csstab .= '#tab_lang_' . $cnt .'{display: none;' . $csstabstyle . '}';
            $csstab_nojs .= '#tab_lang_' . $cnt .'{display: block;' . $csstabstyle . '}';
            $cnt ++;
          }
        }
        $csstab .= '</style>';
        $csstab_nojs .= '</style>';
        $langtabs.= '</ul></div>';
        if ($hidden_coIndex) {
          echo $hidden_coIndex;
          echo '<div class="main important_info">'.TEXT_CONTENT_DOUBLE_GROUP_INDEX.'</div>'. PHP_EOL;
        }
        ?>
        <?php if (USE_ADMIN_LANG_TABS != 'false') { ?>
        <script type="text/javascript">
          $.get("includes/lang_tabs_menu/lang_tabs_menu.css", function(css) {
            $("head").append("<style type='text/css'>"+css+"<\/style>");
          });
          document.write('<?php echo ($csstab);?>');
          document.write('<?php echo ($langtabs);?>');
        </script>
        <?php 
        } else { 
          echo ($csstab_nojs);
        }
        ?>
        <noscript>
          <?php echo ($csstab_nojs);?>
        </noscript>

        <?php
        $cnt=0;
        for ($i=0; $i<$content_count; $i++) {
          for ($l=0; $l < $languages_count; $l++) {
            echo ('<div id="tab_lang_' . $cnt . '" style="padding:0px;">');
            $content_lang = array();
            if (isset($content[$i][$languages[$l]['id']]['content_id'])) {
              //$content_lang = get_content_details($content[$i][$languages[$l]['id']]['content_id']);
              $content_lang = $content[$i][$languages[$l]['id']];
              echo xtc_draw_hidden_field('content_id['.$i.']['.$languages[$l]['id'].']', $content_lang['content_id']);
            }
            $lang_img = '<div style="float:left;margin-right:5px;">'.xtc_image(DIR_WS_LANGUAGES . $languages[$l]['directory'] .'/admin/images/'. $languages[$l]['image'], $languages[$l]['name']).'</div>';   
            ?>
            <table class="tableConfig" style="margin-top:0;">
            <tr>
              <td class="dataTableConfig col-left" style="min-width:205px;border-top:0;border-right:1px solid #a3a3a3;"><?php echo $lang_img.TEXT_STATUS_ACTIVE; ?></td>
              <td class="dataTableConfig col-single-right" style="border-top:0;"><?php echo draw_on_off_selection('content_active['.$i.']['.$languages[$l]['id'].']', $content_status_array, ((isset($content_lang['content_active']) && $content_lang['content_active'] != '') ? $content_lang['content_active'] : 0)).'<span style="display:inline-block;vertical-align:top;padding:5px 0 0 5px;line-height:24px;">'.TEXT_STATUS_ACTIVE_DESCRIPTION.'</span>' ;?></td>
            </tr>
            <tr>
              <td class="dataTableConfig col-left" style="border-right:1px solid #a3a3a3;"><?php echo $lang_img.TEXT_STATUS; ?></td>
              <td class="dataTableConfig col-single-right"><?php echo draw_on_off_selection('content_status['.$i.']['.$languages[$l]['id'].']', $content_status_array, ((isset($content_lang['content_status']) && $content_lang['content_status'] != '') ? $content_lang['content_status'] : 0)).'<span style="display:inline-block;vertical-align:top;padding:5px 0 0 5px;line-height:24px;">'.TEXT_STATUS_DESCRIPTION.'</span>' ;?></td>
            </tr>
              <tr>
                <td class="dataTableConfig col-left" style="border-right:1px solid #a3a3a3;"><?php echo $lang_img.TEXT_TITLE; ?></td>
                <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('content_title['.$i.']['.$languages[$l]['id'].']', ((isset($content_lang['content_title'])) ? $content_lang['content_title'] : ''), 'style="width:100%"'); ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left" style="border-right:1px solid #a3a3a3;"><?php echo $lang_img.TEXT_HEADING; ?></td>
                <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('content_heading['.$i.']['.$languages[$l]['id'].']', ((isset($content_lang['content_heading'])) ? $content_lang['content_heading'] : ''), 'style="width:100%"'); ?></td>
              </tr>
              <?php
              if (GROUP_CHECK=='true') {
                $customers_statuses_array = xtc_get_customers_statuses();
                $customers_statuses_array = array_merge(array(array('id'=>'all', 'text'=>TXT_ALL)), $customers_statuses_array);
                ?>
                <tr>
                  <td class="dataTableConfig col-left" style="border-right:1px solid #a3a3a3;"><?php echo $lang_img.ENTRY_CUSTOMERS_STATUS; ?></td>
                  <td class="dataTableConfig col-single-right">
                    <div class="customers-groups">
                      <?php
                        foreach ($customers_statuses_array as $customers_statuses) {
                          $checked = false;
                          if (strpos($content_lang['group_ids'], 'c_'.$customers_statuses['id'].'_group') !== false || (!isset($_GET['coID']) && $customers_statuses['id'] == 'all')) {
                            $checked = true;
                          }
                          echo xtc_draw_checkbox_field('groups['.$i.']['.$languages[$l]['id'].'][]', $customers_statuses['id'], $checked).' ' .$customers_statuses['text'].'<br />';
                        }
                      ?>
                    </div>
                  </td>
                </tr>
                <?php
              }
              ?>
              <tr>
                <td class="dataTableConfig col-left" style="border-right:1px solid #a3a3a3;"><?php echo $lang_img.'Meta Title:<br/>(max. ' . META_TITLE_LENGTH . ' ' . TEXT_CHARACTERS .')'; ?></td>
                <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('content_meta_title['.$i.']['.$languages[$l]['id'].']', ((isset($content_lang['content_meta_title'])) ? $content_lang['content_meta_title'] : ''), 'style="width:100%" maxlength="' . META_TITLE_LENGTH . '"'); ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left" style="border-right:1px solid #a3a3a3;"><?php echo $lang_img.'Meta Description:<br/>(max. ' . META_DESCRIPTION_LENGTH . ' ' . TEXT_CHARACTERS .')'; ?></td>
                <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('content_meta_description['.$i.']['.$languages[$l]['id'].']', ((isset($content_lang['content_meta_description'])) ? $content_lang['content_meta_description'] : ''), 'style="width:100%" maxlength="' . META_DESCRIPTION_LENGTH . '"'); ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left" style="border-right:1px solid #a3a3a3;"><?php echo $lang_img.'Meta Keywords:<br/>(max. ' . META_KEYWORDS_LENGTH . ' ' . TEXT_CHARACTERS .')'; ?></td>
                <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('content_meta_keywords['.$i.']['.$languages[$l]['id'].']', ((isset($content_lang['content_meta_keywords'])) ? $content_lang['content_meta_keywords'] : ''), 'style="width:100%" maxlength="' . META_KEYWORDS_LENGTH . '"'); ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left" style="border-right:1px solid #a3a3a3;"><?php echo $lang_img.TEXT_UPLOAD_FILE; ?></td>
                <td class="dataTableConfig col-single-right"><?php echo xtc_draw_file_field('file_upload_'.$i.'_'.$languages[$l]['id']).'<span style="display:inline-block;vertical-align:top;padding:5px 0 0 5px;line-height:24px;">'.TEXT_UPLOAD_FILE_LOCAL.'</span>'; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left" style="border-right:1px solid #a3a3a3;"><?php echo $lang_img.TEXT_CHOOSE_FILE; ?></td>
                <td class="dataTableConfig col-single-right">
                  <?php
                    echo TEXT_CHOOSE_FILE_SERVER.'<br /><br />';
                    echo xtc_draw_pull_down_menu('select_file['.$i.']['.$languages[$l]['id'].']', array_merge(array(array('id' => 'default','text' => (($content_lang['content_file'] != '') ? TEXT_NO_FILE : TEXT_SELECT))), $content_files), $content_lang['content_file']);
                    if ($content_lang['content_file'] != '') {
                      echo ' '.TEXT_CURRENT_FILE.' <b>'.$content_lang['content_file'].'</b><br />';
                    }
                  ?>
                </td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left" style="border-right:1px solid #a3a3a3;"></td>
                <td class="dataTableConfig col-single-right"><?php echo TEXT_FILE_DESCRIPTION; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left" style="border-bottom:0;border-right:1px solid #a3a3a3;vertical-align:top;"><?php echo $lang_img.TEXT_CONTENT; ?></td>
                <td class="dataTableConfig col-single-right" style="border-bottom:0;"><?php  echo xtc_draw_textarea_field('content_text['.$i.']['.$languages[$l]['id'].']', $languages[$l]['id'], '100%', '35', ((isset($content_lang['content_text'])) ? $content_lang['content_text'] : ''), '', true, true); ?>
                </td>
              </tr>          
            </table>          
            <?php
            echo ('</div>');
            $cnt++;
          }
        }
        ?>
        </div>

        <div style="padding:5px;clear:both;">
          <div class="flt-r mrg5 pdg2">
            <input type="submit" class="button" onclick="this.blur();" value="<?php echo BUTTON_SAVE; ?>"/>
          </div>
          <div class="flt-r mrg5 pdg2">
            <input class="button" type="submit" onclick="this.blur();" value="<?php echo BUTTON_UPDATE; ?>" name="page_update"/>
          </div>
          <div class="flt-r mrg5 pdg2">
            <a class="button" onclick="this.blur();" href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action'))); ?>"><?php echo BUTTON_BACK; ?></a>
          </div>
        </div>
  
        </form>
      </td>
    </tr>
  </table>

  <script type="text/javascript">
    var parentid = $('#parent_id').val();  
    var stateparent = $('[name="parent_check"]').is(":checked") ? true : false; 
    var checkparent = false;

    $('#file_flag').on('change', function() {
      get_content_pages();
    });

    $(document).ready(function(){
      get_content_pages();
    });

    function get_content_pages() {
      var flag = $('#file_flag').val();
      var lang = <?php echo $_SESSION['languages_id']; ?>;
      var contentgroup = <?php echo (isset($default_content['content_group']) && $default_content['content_group'] != '') ? $default_content['content_group'] : "''"; ?>;
      $.get('../ajax.php', {ext: 'get_content_flag', file_flag: flag, language: lang, content_group: contentgroup, speed: 1}, function(data) {
        if (data != '' && data != undefined) { 
      
          <?php if (NEW_SELECT_CHECKBOX == 'true') { ?>
            $('#parent_id').replaceWith('<select id="parent_id" name="parent_id" class="SlectBox" style="visibility: hidden;"></select>');
            $('#parent_id').nextAll('.optWrapper').replaceWith('<div class="optWrapper"><ul class="options" id="options"></ul></div>');
            $('<li data-val=""><label>---</label></li>').appendTo('#options');
          <?php } else { ?>
            $('#parent_id').replaceWith('<select id="parent_id" name="parent_id" class="SlectBox"></select>');
          <?php } ?>
        
          $('<option value="">---</option>').appendTo('#parent_id');
        
          $.each(data, function(id, arr) {
            if (arr.id == parentid) {
              checkparent = true;
            }
            $('<option value="'+arr.id+'"'+((arr.id == parentid) ? 'selected="selected"' : '')+'>'+arr.name+'</option>').appendTo('#parent_id');
            <?php if (NEW_SELECT_CHECKBOX == 'true') { ?>
              $('<li data-val="'+arr.id+'"'+((arr.id == parentid) ? 'class="selected"' : '')+'><label>'+arr.name+'</label></li>').appendTo('#options');        
            <?php } ?>
          });
        
          <?php if (NEW_SELECT_CHECKBOX == 'true') { ?>
            $('.SlectBox').not('.noStyling').SumoSelect({ createElems: 'mod', placeholder: '-'});
          <?php } ?>
        
          if (checkparent === true) {
            $('[name="parent_check"]').prop("checked", stateparent);
          } else {
            $('[name="parent_check"]').prop("checked", false);
          }
          checkparent = false;
        }
      });
    }
  </script>

<?php
}
?>