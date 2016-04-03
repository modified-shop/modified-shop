<?php
  /* --------------------------------------------------------------
   $Id: content_manager_pages.php 5007 2013-07-04 09:31:37Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
 
   Released under the GNU General Public License
   --------------------------------------------------------------*/

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

if (!$action) {
  $icon_edit = xtc_image(DIR_WS_ICONS.'icon_edit.gif', ICON_EDIT,'','','style="cursor:pointer"');
  $icon_delete = xtc_image(DIR_WS_ICONS.'delete.gif', ICON_DELETE,'','','style="cursor:pointer" onclick="return confirm(\''.DELETE_ENTRY.'\')"');
  $icon_preview = xtc_image(DIR_WS_ICONS.'preview.gif', ICON_PREVIEW,'','','style="cursor:pointer"');
  $icon_status_on = xtc_image(DIR_WS_IMAGES.'icon_lager_green.gif', BUTTON_STATUS_ON);
  $icon_status_off = xtc_image(DIR_WS_IMAGES.'icon_lager_red.gif', BUTTON_STATUS_OFF);
  ?>
  <div class="pageHeadingTab flt-l pdg2"><?php echo HEADING_CONTENT; ?></div>
  <div class="pageHeadingTaba flt-l pdg2"><a onclick="this.blur();" href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER, 'set=product'); ?>"><?php echo HEADING_PRODUCTS_CONTENT; ?></a></div>
  <div class="borderTab">
    <div class="main clear"><?php echo CONTENT_NOTE; ?></div>
    <?php
      $total_space_media_content = xtc_spaceUsed(DIR_FS_CATALOG.'media/content/');
      echo '<div class="main">'.USED_SPACE.xtc_format_filesize($total_space_media_content).'</div>';
    ?>
    <br />
    <table class="tableCenter">
      <tr class="dataTableHeadingRow">
        <td class="dataTableHeadingContent txta-c" style="width:10px" ><?php echo TABLE_HEADING_CONTENT_ID; ?></td>
        <td class="dataTableHeadingContent" style="width:30%"><?php echo TABLE_HEADING_CONTENT_TITLE; ?></td>
        <td class="dataTableHeadingContent txta-c"><?php echo TABLE_HEADING_CONTENT_GROUP; ?></td>
        <td class="dataTableHeadingContent txta-c"><?php echo TABLE_HEADING_CONTENT_SORT; ?></td>
        <td class="dataTableHeadingContent" style="width:10%"><?php echo TABLE_HEADING_CONTENT_FILE; ?></td>
        <td class="dataTableHeadingContent txta-c"><?php echo TABLE_HEADING_CONTENT_STATUS; ?></td>
        <td class="dataTableHeadingContent txta-c nobr"><?php echo TABLE_HEADING_CONTENT_BOX; ?></td>
        <td class="dataTableHeadingContent txta-c nobr"><?php echo TEXT_CONTENT_META_ROBOTS ?></td>
        <td class="dataTableHeadingContent txta-c"><?php echo TABLE_HEADING_STATUS_ACTIVE ?></td>
        <td class="dataTableHeadingContent txta-c nobr" style="width:10%"><?php echo TABLE_HEADING_CONTENT_ACTION; ?>&nbsp;</td>
      </tr>
      <?php
      $content_query = xtc_db_query("SELECT *
                                       FROM ".TABLE_CONTENT_MANAGER."
                                      WHERE languages_id='".(int)$_SESSION['languages_id']."'
                                        AND parent_id = '0'
                                   ORDER BY content_group, sort_order, content_id");
      while ($content_data = xtc_db_fetch_array($content_query)) {
        $file_flag_query = xtc_db_query("SELECT file_flag_name 
                                         FROM ".TABLE_CM_FILE_FLAGS." 
                                        WHERE file_flag = '".xtc_db_input($content_data['file_flag'])."'");
        $file_flag_result = xtc_db_fetch_array($file_flag_query);
        ?>
        <tr class="dataTableRow" onmouseover="this.className='dataTableRowOver'" onmouseout="this.className='dataTableRow'">
          <td class="dataTableContent txta-c"><?php echo $content_data['content_id']; ?></td>
          <td class="dataTableContent">
          <?php
            echo '<a href="'.xtc_href_link(FILENAME_CONTENT_MANAGER,'action=edit&coID='.$content_data['content_group']).'&coIndex='.$content_data['content_group_index'].'">' . $icon_edit . '</a>';
            echo '&nbsp;'.$content_data['content_title'];
            echo (($content_data['content_delete'] == '0') ? ' <span class="col-red">*</span>' : '');
          ?>
          </td>
          <td class="dataTableContent txta-c"><?php echo $content_data['content_group']; ?></td>
          <td class="dataTableContent txta-c"><?php echo $content_data['sort_order']; ?>&nbsp;</td>
          <td class="dataTableContent"><?php echo (($content_data['content_file'] != '') ? $content_data['content_file'] : 'database'); ?></td>
          <td class="dataTableContent txta-c"><?php echo (($content_data['content_status'] == 0) ? TEXT_NO : TEXT_YES); ?></td>
          <td class="dataTableContent txta-c"><?php echo $file_flag_result['file_flag_name']; ?></td>
          <td class="dataTableContent txta-c"><?php echo $content_data['content_meta_robots']; ?>&nbsp;</td>
          <td class="dataTableContent txta-c"><?php echo (($content_data['content_active'] == '1') ? $icon_status_on : $icon_status_off); ?>&nbsp;</td>
          <td class="dataTableContent txta-r nobr">
          <?php
            if ($content_data['content_delete'] == '1') {
              echo '<a href="'.xtc_href_link(FILENAME_CONTENT_MANAGER,'special=delete&coID='.$content_data['content_group']).'&coIndex='.$content_data['content_group_index'].'" onclick="return confirm(\''.CONFIRM_DELETE.'\')">'.$icon_delete.'  '.TEXT_DELETE.'</a>&nbsp;&nbsp;';
            }
            echo '<a href="'.xtc_href_link(FILENAME_CONTENT_MANAGER,'action=edit&coID='.$content_data['content_group']).'&coIndex='.$content_data['content_group_index'].'">'.$icon_edit.'  '.TEXT_EDIT.'</a>&nbsp;&nbsp;';
            echo '<a style="cursor:pointer" onclick="javascript:window.open(\''.xtc_href_link_from_admin('popup_content.php','coID='.$content_data['content_group']).'&preview=true&coIndex='.$content_data['content_group_index'].'\', \'popup\', \'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=640,height=600\')">'.$icon_preview.'&nbsp;&nbsp;'.TEXT_PREVIEW.'</a>';
          ?>
          </td>
        </tr> 
        <?php
        $sub_content_query = xtc_db_query("SELECT *
                                             FROM ".TABLE_CONTENT_MANAGER."
                                            WHERE languages_id='".(int)$_SESSION['languages_id']."'
                                              AND parent_id = '".$content_data['content_id']."'
                                         ORDER BY content_group, sort_order, content_id");
        while ($sub_content_data = xtc_db_fetch_array($sub_content_query)) {
          $file_flag_query = xtc_db_query("SELECT file_flag_name 
                                           FROM ".TABLE_CM_FILE_FLAGS." 
                                          WHERE file_flag = '".xtc_db_input($sub_content_data['file_flag'])."'");
          $file_flag_result = xtc_db_fetch_array($file_flag_query);
          ?>
          <tr class="dataTableRow" onmouseover="this.className='dataTableRowOver'" onmouseout="this.className='dataTableRow'">
            <td class="dataTableContent txta-c"><?php echo $sub_content_data['content_id']; ?></td>
            <td class="dataTableContent">&nbsp;&nbsp;--
            <?php
              echo '<a href="'.xtc_href_link(FILENAME_CONTENT_MANAGER,'action=edit&coID='.$sub_content_data['content_group']).'&coIndex='.$sub_content_data['content_group_index'].'">' . $icon_edit . '</a>';
              echo '&nbsp;'.$sub_content_data['content_title'];
              echo (($sub_content_data['content_delete'] == '0') ? ' <span class="col-red">*</span>' : '');
            ?>
            </td>
            <td class="dataTableContent txta-c"><?php echo $sub_content_data['content_group']; ?></td>
            <td class="dataTableContent txta-c"><?php echo $sub_content_data['sort_order']; ?>&nbsp;</td>
            <td class="dataTableContent"><?php echo (($sub_content_data['content_file'] != '') ? $sub_content_data['content_file'] : 'database'); ?></td>
            <td class="dataTableContent txta-c"><?php echo (($sub_content_data['content_status'] == 0) ? TEXT_NO : TEXT_YES); ?></td>
            <td class="dataTableContent txta-c"><?php echo $file_flag_result['file_flag_name']; ?></td>
            <td class="dataTableContent txta-c"><?php echo $sub_content_data['content_meta_robots']; ?>&nbsp;</td>
            <td class="dataTableContent txta-c"><?php echo (($sub_content_data['content_active'] == '1') ? $icon_status_on : $icon_status_off); ?>&nbsp;</td>
            <td class="dataTableContent txta-r nobr">
            <?php
              if ($sub_content_data['content_delete'] == '1') {
                echo '<a href="'.xtc_href_link(FILENAME_CONTENT_MANAGER,'special=delete&coID='.$sub_content_data['content_group']).'&coIndex='.$sub_content_data['content_group_index'].'" onclick="return confirm(\''.CONFIRM_DELETE.'\')">'.$icon_delete.'  '.TEXT_DELETE.'</a>&nbsp;&nbsp;';
              }
              echo '<a href="'.xtc_href_link(FILENAME_CONTENT_MANAGER,'action=edit&coID='.$sub_content_data['content_group']).'&coIndex='.$sub_content_data['content_group_index'].'">'.$icon_edit.'  '.TEXT_EDIT.'</a>&nbsp;&nbsp;';
              echo '<a style="cursor:pointer" onclick="javascript:window.open(\''.xtc_href_link_from_admin('popup_content.php','coID='.$sub_content_data['content_group']).'&preview=true&coIndex='.$sub_content_data['content_group_index'].'\', \'popup\', \'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no, width=640, height=600\')">'.$icon_preview.'&nbsp;&nbsp;'.TEXT_PREVIEW.'</a>';
            ?>
            </td>
          </tr>
          <?php
        }
      }
      ?>
    </table>          
  </div>

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
      $content[$z][$languages[$i]['id']] = array('languages_id' => $languages[$i]['id']);
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
  $files = array();
  if ($dir= opendir(DIR_FS_CATALOG.'media/content/')) {
    while (($file = readdir($dir)) !== false) {
      if (is_file( DIR_FS_CATALOG.'media/content/'.$file) and ($file != 'index.html')) {
        $files[] = array('id' => $file,
                         'text' => $file);
      }
    }
    closedir($dir);
    sort($files);
  }      
  ?>

  <div style="width:100%;padding:5px;">
    <div class="pageHeading"><?php echo HEADING_CONTENT; ?><br /></div>
    <?php
    if ($action != 'new') {
      echo xtc_draw_form('edit_content', FILENAME_CONTENT_MANAGER, 'action=edit&id=update&coID='.$g_coID, 'post', 'enctype="multipart/form-data"'). PHP_EOL;
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
          <td class="dataTableConfig col-single-right"><?php echo xtc_draw_pull_down_menu('file_flag', $file_flag_array, $default_content['file_flag']); ?></td>
        </tr>
        <?php if ($action != 'new' 
                  && CONTENT_CHILDS_ACTIV == 'true' 
                  && count($content_data_array) > 1
                  && check_content_childs($default_content['content_id'], 0) === false
                  ) 
        { ?>
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_PARENT; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo xtc_draw_pull_down_menu('parent_id', $content_data_array, $default_content['parent_id']); ?><span style="display:inline-block;vertical-align:top;padding:5px 0 0 5px;line-height:24px;"><?php echo check_content_childs($default_content['content_id'], 0) ? '' : xtc_draw_checkbox_field('parent_check', 'yes', (($default_content['parent_id'] > 0) ? true : false)).' '.TEXT_PARENT_DESCRIPTION; ?></span></td>
          </tr>
        <?php } ?>
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

    <div style="padding:5px;clear:both;">
      <div class="flt-r mrg5 pdg2">
        <input type="submit" class="button" onclick="this.blur();" value="<?php echo BUTTON_SAVE; ?>"/>
      </div>
      <div class="flt-r mrg5 pdg2">
        <input class="button" type="submit" onclick="this.blur();" value="<?php echo BUTTON_UPDATE; ?>" name="page_update"/>
      </div>
      <div class="flt-r mrg5 pdg2">
        <a class="button" onclick="this.blur();" href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER); ?>"><?php echo BUTTON_BACK; ?></a>
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
            <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('content_title['.$i.']['.$languages[$l]['id'].']', ((isset($content_lang['content_title'])) ? $content_lang['content_title'] : ''), 'size="60"'); ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left" style="border-right:1px solid #a3a3a3;"><?php echo $lang_img.TEXT_HEADING; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('content_heading['.$i.']['.$languages[$l]['id'].']', ((isset($content_lang['content_heading'])) ? $content_lang['content_heading'] : ''), 'size="60"'); ?></td>
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
                  for ($g=0, $z=sizeof($customers_statuses_array); $g<$z; $g++) {
                    $checked = false;
                    if (strstr($content_lang['group_ids'], 'c_'.$customers_statuses_array[$g]['id'].'_group')) {
                      $checked = true;
                    }
                    echo xtc_draw_checkbox_field('groups['.$i.']['.$languages[$l]['id'].'][]', $customers_statuses_array[$g]['id'], $checked).' ' .$customers_statuses_array[$g]['text'].'<br />';
                  }
                  ?>
                </div>
              </td>
            </tr>
            <?php
          }
          ?>
          <tr>
            <td class="dataTableConfig col-left" style="border-right:1px solid #a3a3a3;"><?php echo $lang_img.'Meta Title:'; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('content_meta_title['.$i.']['.$languages[$l]['id'].']', ((isset($content_lang['content_meta_title'])) ? $content_lang['content_meta_title'] : ''), 'size="60"'); ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left" style="border-right:1px solid #a3a3a3;"><?php echo $lang_img.'Meta Description:'; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('content_meta_description['.$i.']['.$languages[$l]['id'].']', ((isset($content_lang['content_meta_description'])) ? $content_lang['content_meta_description'] : ''), 'size="60"'); ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left" style="border-right:1px solid #a3a3a3;"><?php echo $lang_img.'Meta Keywords:'; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('content_meta_keywords['.$i.']['.$languages[$l]['id'].']', ((isset($content_lang['content_meta_keywords'])) ? $content_lang['content_meta_keywords'] : ''), 'size="60"'); ?></td>
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
                echo xtc_draw_pull_down_menu('select_file['.$i.']['.$languages[$l]['id'].']', array_merge(array(array('id' => 'default','text' => (($content_lang['content_file'] != '') ? TEXT_NO_FILE : TEXT_SELECT))), $files), $content_lang['content_file']);
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
            <td class="dataTableConfig col-single-right" style="border-bottom:0;"><?php  echo xtc_draw_textarea_field('content_text['.$i.']['.$languages[$l]['id'].']', $languages[$l]['id'], '100%', '35', ((isset($content_lang['content_text'])) ? $content_lang['content_text'] : '')); ?>
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
        <a class="button" onclick="this.blur();" href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER); ?>"><?php echo BUTTON_BACK; ?></a>
      </div>
    </div>
    
    </form>
  </div>

<?php
}
?>