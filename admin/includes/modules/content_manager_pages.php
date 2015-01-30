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
  ?>
  <div class="pageHeadingTab flt-l pdg2"><?php echo HEADING_CONTENT; ?></div>
  <div class="pageHeadingTaba flt-l pdg2"><a onclick="this.blur();" href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER, 'set=product'); ?>"><?php echo HEADING_PRODUCTS_CONTENT; ?></a></div>
  <div class="borderTab">
  <div class="main clear"><?php echo CONTENT_NOTE; ?></div>
  <?php
  $total_space_media_content = xtc_spaceUsed(DIR_FS_CATALOG.'media/content/'); // DokuMan - 2011-09-06 - sum up correct filesize avoiding global variable
  echo '<div class="main">'.USED_SPACE.xtc_format_filesize($total_space_media_content).'</div>';
  ?>
  <?php
  // Display Content
  for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
    $content=array();
    $content_query=xtc_db_query("SELECT *
                                   FROM ".TABLE_CONTENT_MANAGER."
                                  WHERE languages_id='".$languages[$i]['id']."'
                                    AND parent_id='0'
                               ORDER BY content_group,sort_order
                                 ");
    $index = 0;
    while ($content_data=xtc_db_fetch_array($content_query)) {
      foreach($content_data as $key => $entry) {                  
        $content[$index][strtoupper($key)] = $entry;
      }
      $content[$index]['CONTENT_ACTIVE'] = $content[$index]['CONTENT_ACTIVE'] ?  xtc_image(DIR_WS_IMAGES . 'icon_lager_green.gif', BUTTON_STATUS_ON) : xtc_image(DIR_WS_IMAGES . 'icon_lager_red.gif', BUTTON_STATUS_OFF);
      $index++;
    } // while content_data
    ?>
    <br />
    <div class="main"><?php echo xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']).'&nbsp;&nbsp;'.$languages[$i]['name']; ?></div>
    <table class="tableCenter">
      <tr class="dataTableHeadingRow">
        <td class="dataTableHeadingContent txta-c" style="width:10px" ><?php echo TABLE_HEADING_CONTENT_ID; ?></td>
        <td class="dataTableHeadingContent" style="width:10px" >&nbsp;</td>
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
      for ($ii = 0, $nn = sizeof($content); $ii < $nn; $ii++) {
        $file_flag_sql = xtc_db_query("SELECT file_flag_name FROM " . TABLE_CM_FILE_FLAGS . " WHERE file_flag=" . $content[$ii]['FILE_FLAG']);
        $file_flag_result = xtc_db_fetch_array($file_flag_sql);
        echo '              <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\'" onmouseout="this.className=\'dataTableRow\'">' . "\n";
          if ($content[$ii]['CONTENT_FILE']=='') $content[$ii]['CONTENT_FILE']='database';
            ?>
            <td class="dataTableContent txta-c"><?php echo $content[$ii]['CONTENT_ID']; ?></td>
            <td bgcolor="<?php echo substr((6543216554/$content[$ii]['CONTENT_GROUP']),0,6); ?>" class="dataTableContent">&nbsp;</td>
            <td class="dataTableContent">
              <?php echo '&nbsp;'.$content[$ii]['CONTENT_TITLE']; ?>
              <?php
              if ($content[$ii]['CONTENT_DELETE']=='0'){
                echo '<font color="#ff0000">*</font>';
              } ?>
            </td>
            <td class="dataTableContent txta-c"><?php echo $content[$ii]['CONTENT_GROUP']; ?></td>
            <td class="dataTableContent txta-c"><?php echo $content[$ii]['SORT_ORDER']; ?>&nbsp;</td>
            <td class="dataTableContent"><?php echo $content[$ii]['CONTENT_FILE']; ?></td>
            <td class="dataTableContent txta-c"><?php if ($content[$ii]['CONTENT_STATUS']==0) { echo TEXT_NO; } else { echo TEXT_YES; } ?></td>
            <td class="dataTableContent txta-c"><?php echo $file_flag_result['file_flag_name']; ?></td>
            <td class="dataTableContent txta-c"><?php echo $content[$ii]['CONTENT_META_ROBOTS']; ?>&nbsp;</td>
            <td class="dataTableContent txta-c"><?php echo $content[$ii]['CONTENT_ACTIVE']; ?>&nbsp;</td>
            <td class="dataTableContent txta-r nobr">
              <a href="">
                <?php
                if ($content[$ii]['CONTENT_DELETE']=='1'){
                  ?>
                  <a href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER,'special=delete&coID='.$content[$ii]['CONTENT_ID']); ?>" onclick="return confirm('<?php echo CONFIRM_DELETE; ?>')">
                    <?php
                    echo xtc_image(DIR_WS_ICONS.'delete.gif', ICON_DELETE,'','','style="cursor:pointer" onclick="return confirm(\''.DELETE_ENTRY.'\')"').'  '.TEXT_DELETE.'</a>&nbsp;&nbsp;';
                } // if content
                ?>
                <a href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER,'action=edit&coID='.$content[$ii]['CONTENT_ID']); ?>">
                  <?php
                  echo xtc_image(DIR_WS_ICONS.'icon_edit.gif', ICON_EDIT,'','','style="cursor:pointer"').'  '.TEXT_EDIT.'</a>';
                ?>
                <a style="cursor:pointer" onclick="javascript:window.open('<?php echo xtc_href_link_from_admin('popup_content.php','coID='.$content[$ii]['CONTENT_GROUP']); ?>', 'popup', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no, width=640, height=600')">
                  <?php
                  echo xtc_image(DIR_WS_ICONS.'preview.gif', ICON_PREVIEW,'','','style="cursor:pointer"').'&nbsp;&nbsp;'.TEXT_PREVIEW.'</a>';
                ?>
            </td>
          </tr>
            <?php
            $content_1= array();
            $content_1_query = xtc_db_query("SELECT *
                                               FROM ".TABLE_CONTENT_MANAGER."
                                              WHERE languages_id='".$languages[$i]['id']."'
                                                AND parent_id='".$content[$ii]['CONTENT_ID']."'
                                           ORDER BY content_group,sort_order
                                             ");
            $index = 0;
            while ($content_1_data = xtc_db_fetch_array($content_1_query)) { 
              foreach($content_1_data as $key => $entry) {                  
                $content_1[$index][strtoupper($key)] = $entry;
              }
              $content_1[$index]['CONTENT_ACTIVE'] = $content_1[$index]['CONTENT_ACTIVE'] ?  xtc_image(DIR_WS_IMAGES . 'icon_lager_green.gif', BUTTON_STATUS_ON) : xtc_image(DIR_WS_IMAGES . 'icon_lager_red.gif', BUTTON_STATUS_OFF);
              $index++;              
            }
            for ($a = 0, $x = sizeof($content_1); $a < $x; $a++) {
              if ($content_1[$a]!='') {
                $file_flag_sql = xtc_db_query("SELECT file_flag_name FROM " . TABLE_CM_FILE_FLAGS . " WHERE file_flag=" . $content_1[$a]['FILE_FLAG']);
                $file_flag_result = xtc_db_fetch_array($file_flag_sql);
                echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\'" onmouseout="this.className=\'dataTableRow\'">' . "\n";

                  if ($content_1[$a]['CONTENT_FILE']=='') $content_1[$a]['CONTENT_FILE']='database';
                    ?>
                    <td class="dataTableContent txta-c"><?php echo $content_1[$a]['CONTENT_ID']; ?></td>
                    <td class="dataTableContent">--</td>
                    <td class="dataTableContent"><?php echo $content_1[$a]['CONTENT_TITLE']; ?></td>
                    <td class="dataTableContent txta-c"><?php echo $content_1[$a]['CONTENT_GROUP']; ?></td>
                    <td class="dataTableContent txta-c"><?php echo $content_1[$a]['SORT_ORDER']; ?>&nbsp;</td>
                    <td class="dataTableContent"><?php echo $content_1[$a]['CONTENT_FILE']; ?></td>
                    <td class="dataTableContent txta-c"><?php if ($content_1[$a]['CONTENT_STATUS']==0) { echo TEXT_NO; } else { echo TEXT_YES; } ?></td>
                    <td class="dataTableContent txta-c"><?php echo $file_flag_result['file_flag_name']; ?></td>
                    <td class="dataTableContent txta-c"><?php echo $content_1[$a]['TEXT_CONTENT_META_ROBOTS']; ?>&nbsp;</td>
                    <td class="dataTableContent txta-c"><?php echo $content_1[$a]['CONTENT_ACTIVE']; ?>&nbsp;</td>
                    <td class="dataTableContent txta-r">
                      <a href="">
                        <?php
                        if ($content_1[$a]['CONTENT_DELETE']=='1'){
                          ?>
                          <a href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER,'special=delete&coID='.$content_1[$a]['CONTENT_ID']); ?>" onclick="return confirm('<?php echo CONFIRM_DELETE; ?>')">
                            <?php
                            echo xtc_image(DIR_WS_ICONS.'delete.gif', ICON_DELETE,'','','style="cursor:pointer" onclick="return confirm(\''.DELETE_ENTRY.'\')"').'  '.TEXT_DELETE.'</a>&nbsp;&nbsp;';
                        } // if content
                        ?>
                        <a href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER,'action=edit&coID='.$content_1[$a]['CONTENT_ID']); ?>">
                        <?php
                        echo xtc_image(DIR_WS_ICONS.'icon_edit.gif', ICON_EDIT,'','','style="cursor:pointer"').'  '.TEXT_EDIT.'</a>';
                        ?>
                        <a style="cursor:pointer" onclick="javascript:window.open('<?php echo xtc_href_link(FILENAME_CONTENT_PREVIEW,'coID='.$content_1[$a]['CONTENT_ID']); ?>', 'popup', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no, width=640, height=600')">
                          <?php
                          echo xtc_image(DIR_WS_ICONS.'preview.gif', ICON_PREVIEW,'','','style="cursor:pointer"').'&nbsp;&nbsp;'.TEXT_PREVIEW.'</a>';
                        ?>
                    </td>
                  </tr>
                  <?php
                }
              } // for content
            } // for language
            ?>
          </table>          
          <?php
  }
  ?>
  </div>
  <?php
} else {
  switch ($action) {
    // Diplay Editmask
    case 'new':
    case 'edit':
      if ($action != 'new') {
        $content_query=xtc_db_query("SELECT *
                                       FROM ".TABLE_CONTENT_MANAGER."
                                      WHERE content_id='".$g_coID."'");
        $content = xtc_db_fetch_array($content_query);
      }
      $languages_array = array();
      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        if ($languages[$i]['id'] == $content['languages_id']) {          
          $languages_selected = $languages[$i]['code'];
          $languages_id = $languages[$i]['id'];
        }
        $languages_array[] = array('id' => $languages[$i]['code'],
                                 'text' => $languages[$i]['name']);
      } // for
      
      $query_string = trim($languages_id) != '' ? ' AND languages_id='.(int)$languages_id : '';                              
      $query_string .= $action != 'new' ? ' AND file_flag ='.(int)$content['file_flag'] : '';

      $content_data_query = xtc_db_query("SELECT
                                           content_id,
                                           content_title
                                      FROM ".TABLE_CONTENT_MANAGER."
                                     WHERE parent_id ='0'
                                           ".$query_string."
                                       AND content_id!='".$g_coID."'
                                   ");
      $content_data_array[] = array('id' => '', 'text' => '---');   
      while ($content_data = xtc_db_fetch_array($content_data_query)) {
        $content_data_array[] = array('id'=>$content_data['content_id'],
                                      'text'=>$content_data['content_title']
                                     );
      }
      ?>      
      
      <div style="width:99%;padding:5px;">
      <div class="pageHeading"><?php echo HEADING_CONTENT; ?><br /></div>
        <?php
          if ($action != 'new') {
            echo xtc_draw_form('edit_content',FILENAME_CONTENT_MANAGER,'action=edit&id=update&coID='.$g_coID,'post','enctype="multipart/form-data"').xtc_draw_hidden_field('coID',$g_coID);
          } else {
            echo xtc_draw_form('edit_content',FILENAME_CONTENT_MANAGER,'action=edit&id=insert','post','enctype="multipart/form-data"').xtc_draw_hidden_field('coID',$g_coID);
          }
        ?>
        <table class="tableConfig borderall">
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_STATUS_ACTIVE; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo xtc_draw_checkbox_field('active','yes', (isset($content['content_active']) && $content['content_active'] == '1' ? true : false)).' '.TEXT_STATUS_ACTIVE_DESCRIPTION ;?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_LANGUAGE; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo xtc_draw_pull_down_menu('language',$languages_array,$languages_selected); ?></td>
          </tr>
          <?php
            if ($content['content_delete']!=0 or $action == 'new') {
              ?>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_GROUP; ?></td>
                <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('content_group',isset($content['content_group'])?$content['content_group']:'','size="5"') . ' '. TEXT_GROUP_DESC; ?></td>
              </tr>
              <?php
            } else {
              echo xtc_draw_hidden_field('content_group',$content['content_group']);
              ?>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_GROUP; ?></td>
                <td class="dataTableConfig col-single-right"><?php echo $content['content_group']; ?></td>
              </tr>
              <?php
            }
            $file_flag_sql = xtc_db_query("SELECT file_flag as id, file_flag_name as text FROM " . TABLE_CM_FILE_FLAGS);
            while($file_flag = xtc_db_fetch_array($file_flag_sql)) {
              $file_flag_array[] = array('id' => $file_flag['id'], 'text' => $file_flag['text']);
            }
          ?>
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_FILE_FLAG; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo xtc_draw_pull_down_menu('file_flag',$file_flag_array,$content['file_flag']); ?></td>
          </tr>
          <?php if ($action != 'new' && CONTENT_CHILDS_ACTIV == 'true') { //Content Parent/Child  ?>
            <tr>
              <td class="dataTableConfig col-left"><?php echo TEXT_PARENT; ?></td>
              <td class="dataTableConfig col-single-right"><?php echo xtc_draw_pull_down_menu('parent',$content_data_array,$content['parent_id']); ?><?php echo check_content_childs($content['content_id'],$languages_id) ? '' : xtc_draw_checkbox_field('parent_check', 'yes', ($content['parent_id'] > 0 ? true : false)).' '.TEXT_PARENT_DESCRIPTION; ?></td>
            </tr>
          <?php } ?>
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_SORT_ORDER; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('sort_order',isset($content['sort_order'])?$content['sort_order']:'','size="5"'); ?></td>
          </tr>                                  
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_STATUS; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo xtc_draw_checkbox_field('status','yes', (isset($content['content_status']) && $content['content_status'] == '1' ? true : false)).' '.TEXT_STATUS_DESCRIPTION ;?></td>
          </tr>
          <?php
          $meta_robots = explode(', ', $content['content_meta_robots']);
          foreach ($meta_robots as $key => $value) {
            $content['meta_robots'][$value] = $value;
          }
          ?>
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_CONTENT_META_ROBOTS; ?>: </td>
            <td class="dataTableConfig col-single-right">
              <?php echo xtc_draw_checkbox_field('cont_meta_robots[]','noindex', (isset($content['meta_robots']['noindex']) ? $content['meta_robots']['noindex'] : false)).TEXT_CONTENT_NOINDEX.'<br/>'.
                         xtc_draw_checkbox_field('cont_meta_robots[]','nofollow', (isset($content['meta_robots']['nofollow']) ? $content['meta_robots']['nofollow'] : false)).TEXT_CONTENT_NOFOLLOW.'<br/>'.
                         xtc_draw_checkbox_field('cont_meta_robots[]','noodp', (isset($content['meta_robots']['noodp']) ? $content['meta_robots']['noodp'] : false)).TEXT_CONTENT_NOODP;
              ?>
            </td>
          </tr>
          <?php
            if (GROUP_CHECK=='true') {
              $customers_statuses_array = xtc_get_customers_statuses();
              $customers_statuses_array=array_merge(array(array('id'=>'all','text'=>TXT_ALL)),$customers_statuses_array);
              ?>
              <tr>
                <td class="dataTableConfig col-left" ><?php echo ENTRY_CUSTOMERS_STATUS; ?></td>
                <td class="dataTableConfig col-single-right">
                  <div class="customers-groups">
                    <?php
                    for ($i=0;$n=sizeof($customers_statuses_array),$i<$n;$i++) {
                      if (strstr($content['group_ids'],'c_'.$customers_statuses_array[$i]['id'].'_group')) {
                        $checked='checked ';
                      } else {
                        $checked='';
                      }
                      echo '<input type="checkbox" name="groups[]" value="'.$customers_statuses_array[$i]['id'].'"'.$checked.'> '.$customers_statuses_array[$i]['text'].'<br />';
                      }
                    ?>
                  </div>
                </td>
              </tr>
              <?php
            }
          ?>
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_TITLE; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('cont_title',isset($content['content_title'])?$content['content_title']:'','size="60"'); ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_HEADING; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('cont_heading',isset($content['content_heading'])?$content['content_heading']:'','size="60"'); ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo 'Meta Title'; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('cont_meta_title',isset($content['content_meta_title'])?$content['content_meta_title']:'','size="60"'); ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo 'Meta Description'; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('cont_meta_description',isset($content['content_meta_description'])?$content['content_meta_description']:'','size="60"'); ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo 'Meta Keywords'; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('cont_meta_keywords',isset($content['content_meta_keywords'])?$content['content_meta_keywords']:'','size="60"'); ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_UPLOAD_FILE; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo xtc_draw_file_field('file_upload').' '.TEXT_UPLOAD_FILE_LOCAL; ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_CHOOSE_FILE; ?></td>
            <td class="dataTableConfig col-single-right">
              <?php
                if ($dir= opendir(DIR_FS_CATALOG.'media/content/')){
                  while (($file = readdir($dir)) !== false) {
                    if (is_file( DIR_FS_CATALOG.'media/content/'.$file) and ($file !="index.html")){
                      $files[]=array('id' => $file,
                                   'text' => $file);
                    }//if
                  } // while
                  closedir($dir);
                  sort($files);// Tomcraft - 2010-06-17 - Sort files for media-content alphabetically in content manager
                }
                // set default value in dropdown!
                if (empty($content['content_file'])) {
                  $default_array[]=array('id' => 'default','text' => TEXT_SELECT);
                  $default_value='default';
                  if (count($files) == 0) {
                    $files = $default_array;
                  } else {
                    $files=array_merge($default_array,$files);
                  }
                } else {
                  $default_array[]=array('id' => 'default','text' => TEXT_NO_FILE);
                  $default_value=$content['content_file'];
                  if (count($files) == 0) {
                    $files = $default_array;
                  } else {
                    $files=array_merge($default_array,$files);
                  }
                }
                echo TEXT_CHOOSE_FILE_SERVER.'</br>';
                echo xtc_draw_pull_down_menu('select_file',$files,$default_value);
                if (!empty($content['content_file'])) {
                  echo TEXT_CURRENT_FILE.' <b>'.$content['content_file'].'</b><br />';
                }
              ?>
            </td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"></td>
            <td class="dataTableConfig col-single-right"><?php echo TEXT_FILE_DESCRIPTION; ?></td>
          </tr>
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_CONTENT; ?></td>
            <td class="dataTableConfig col-single-right">
              <?php
                echo xtc_draw_textarea_field('cont','','100%','35',isset($content['content_text'])?$content['content_text']:'');
              ?>
            </td>
          </tr>          
        </table>
       
        <div class="flt-r mrg5 pdg2">
          <?php echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SAVE . '"/>'; ?>
        </div>
        <div class="flt-r mrg5 pdg2">
          <a class="button" onclick="this.blur();" href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER); ?>"><?php echo BUTTON_BACK; ?></a>
        </div>
       
      </form>
       </div>
      <?php
      break;
  }
}
