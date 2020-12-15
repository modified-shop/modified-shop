<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
 
   Released under the GNU General Public License
   --------------------------------------------------------------*/

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

if (!$action) {
  $email_id_query = xtc_db_query("SELECT ec.email_id
                                    FROM ".TABLE_EMAIL_CONTENT." ec
                                GROUP BY ec.email_id");
  $email_ids = array();
  while ($email_id_data = xtc_db_fetch_array($email_id_query)) {
    $email_ids[] = array(
      'id' => $email_id_data['email_id'],
      'name' => ucwords(implode(' ', explode('_', $email_id_data['email_id']))),
    );
  }
  ?>
  <div class="pageHeadingTaba pdg2 flt-l"><a onclick="this.blur();" href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER); ?>"><?php echo HEADING_CONTENT; ?></a></div>
  <div class="pageHeadingTaba pdg2 flt-l"><a onclick="this.blur();" href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER, 'set=product'); ?>"><?php echo HEADING_PRODUCTS_CONTENT; ?></a></div>
  <div class="pageHeadingTaba pdg2 flt-l"><a onclick="this.blur();" href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER, 'set=content'); ?>"><?php echo HEADING_CONTENT_MANAGER_CONTENT; ?></a></div>
  <div class="pageHeadingTab pdg2 flt-l"><?php echo HEADING_EMAIL_CONTENT; ?></div>
  <div class="borderTab">
  <?php
    $total_space_media_products = xtc_spaceUsed(DIR_FS_CATALOG.'media/content/');
    echo '<div class="main clear">'.USED_SPACE.xtc_format_filesize($total_space_media_products).'</div><br />';
  ?>
  <table class="tableCenter">
    <tr class="dataTableHeadingRow">
      <td class="dataTableHeadingContent nobr txta-c"><?php echo TABLE_HEADING_EMAIL_ID; ?></td>
      <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_EMAIL; ?></td>
    </tr>
    <?php
      for ($i=0,$n=sizeof($email_ids); $i<$n; $i++) {
        echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\'" onmouseout="this.className=\'dataTableRow\'">' . "\n";
          ?>
          <td class="dataTableContent_products txta-c" style="width:5%"><?php echo $email_ids[$i]['id']; ?></td>
          <td class="dataTableContent_products"><b>
            <?php echo xtc_image(DIR_WS_CATALOG.'images/icons/arrow.gif'); ?>
            <a href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER,'eID='.$email_ids[$i]['id'].$setparam);?>"><?php echo $email_ids[$i]['name']; ?></a></b>
          </td>
        </tr>
        <?php
        if ($_GET['eID'] && $_GET['eID'] != '') {
          // display content elements
          $content_query=xtc_db_query("SELECT *
                                         FROM ".TABLE_EMAIL_CONTENT."
                                        WHERE email_id = '".xtc_db_input($_GET['eID'])."'
                                     ORDER BY content_name");
          $content_array = array();
          while ($content_data = xtc_db_fetch_array($content_query)) {
            $content_array[] = array(
              'id' => $content_data['content_id'],
              'name' => $content_data['content_name'],
              'file' => $content_data['content_file'],
              'link' => $content_data['content_link'],
              'comment' => $content_data['file_comment'],
              'languages_id' => $content_data['languages_id'],
              'read' => $content_data['content_read'],
            );
          }

          if (xtc_db_input($_GET['eID']) == $email_ids[$i]['id']) {
            ?>
            <tr>
              <td class="dataTableContent"></td>
              <td class="dataTableContent">
                <table class="tableCenter">
                  <tr class="dataTableHeadingRow">
                    <td class="dataTableHeadingContent nobr txta-c" style="width:2%" ><?php echo TABLE_HEADING_PRODUCTS_CONTENT_ID; ?></td>
                    <td class="dataTableHeadingContent nobr" style="width:2%" >&nbsp;</td>
                    <td class="dataTableHeadingContent nobr" style="width:5%" ><?php echo TABLE_HEADING_LANGUAGE; ?></td>
                    <td class="dataTableHeadingContent nobr" style="width:15%" ><?php echo TABLE_HEADING_CONTENT_NAME; ?></td>
                    <td class="dataTableHeadingContent nobr" style="width:30%" ><?php echo TABLE_HEADING_CONTENT_FILE; ?></td>
                    <td class="dataTableHeadingContent nobr" style="width:1%" ><?php echo TABLE_HEADING_CONTENT_FILESIZE; ?></td>
                    <td class="dataTableHeadingContent nobr txta-c" style="width:20%" ><?php echo TABLE_HEADING_CONTENT_LINK; ?></td>
                    <td class="dataTableHeadingContent nobr" style="width:5%" ><?php echo TABLE_HEADING_CONTENT_HITS; ?></td>
                    <td class="dataTableHeadingContent nobr" style="width:20%" ><?php echo TABLE_HEADING_CONTENT_ACTION; ?></td>
                  </tr>
                  <?php
                  for ($ii=0,$nn=sizeof($content_array); $ii<$nn; $ii++) {
                    echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\'" onmouseout="this.className=\'dataTableRow\'">' . "\n";
                    ?>
                      <td class="dataTableContent txta-c"><?php echo  $content_array[$ii]['id']; ?> </td>
                      <td class="dataTableContent txta-c">
                        <?php
                          if ($content_array[$ii]['file'] != '') {
                            $filename = DIR_FS_CATALOG . 'media/content/' . $content_array[$ii]['file'];
                            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                            echo xtc_image('../' . DIR_WS_IMAGES . 'icons/filetype/icon_' . $ext . '.gif');
                          } else {
                            echo xtc_image('../' . DIR_WS_IMAGES . 'icons/filetype/icon_link.gif');
                          }
                          for ($xx=0,$zz=sizeof($languages); $xx<$zz;$xx++){
                            if ($languages[$xx]['id'] == $content_array[$ii]['languages_id']) {
                              $lang_dir = $languages[$xx]['directory'];
                              break;
                            }
                          }
                        ?>
                      </td>
                      <td class="dataTableContent txta-c"><?php echo xtc_image(DIR_WS_CATALOG.'lang/'.$lang_dir.'/admin/images/icon.gif'); ?></td>
                      <td class="dataTableContent"><?php echo $content_array[$ii]['name']; ?></td>
                      <td class="dataTableContent"><?php echo $content_array[$ii]['file']; ?></td>
                      <td class="dataTableContent txta-c"><?php echo xtc_filesize($content_array[$ii]['file'], 'content'); ?></td>
                      <td class="dataTableContent txta-c">
                        <?php
                          if ($content_array[$ii]['link']!='') {
                            echo '<a href="'.$content_array[$ii]['link'].'" target="new">'.$content_array[$ii]['link'].'</a>';
                          }
                        ?>
                        &nbsp;
                      </td>
                      <td class="dataTableContent txta-c"><?php echo $content_array[$ii]['read']; ?></td>
                      <td class="dataTableContent">
                        <a href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER,'special=delete_email&coID='.$content_array[$ii]['id'].'&eID='.$email_ids[$i]['id'].'&set='.$set); ?>" onclick="return confirmLink('<?php echo DELETE_ENTRY; ?>', '', this)">
                        <?php
                          echo xtc_image(DIR_WS_ICONS.'delete.gif', ICON_DELETE,'','','style="cursor:pointer" onclick="return confirmLink(\''. DELETE_ENTRY .'\', \'\' ,this)"').'  '.TEXT_DELETE.'</a>&nbsp;&nbsp;';
                        ?>
                        <a href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER,'action=edit_email_content&coID='.$content_array[$ii]['id'].'&eID='.$email_ids[$i]['id'].$setparam); ?>">
                          <?php
                          echo xtc_image(DIR_WS_ICONS.'icon_edit.gif', ICON_EDIT,'','','style="cursor:pointer"').'  '.TEXT_EDIT.'</a>';
                        // display preview button if filetype in array
                        $allowed_filetypes = array('.gif','.jpg','.png','.html','.htm','.txt','.bmp'); 
                        if (in_array(substr($content_array[$ii]['file'], 0, strrpos($content_array[$ii]['file'], '.') - 1), $allowed_filetypes)) {
                          ?>
                          <a style="cursor:pointer" onclick="javascript:window.open('<?php echo xtc_href_link(FILENAME_CONTENT_PREVIEW,'eID=media&coID='.$content_array[$ii]['id']); ?>', 'popup', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no, width=640, height=600')">
                            <?php
                            echo xtc_image(DIR_WS_ICONS.'preview.gif', ICON_PREVIEW,'','',' style="cursor:pointer"').'&nbsp;&nbsp;'.TEXT_PREVIEW.'</a>';
                        }
                        ?>
                      </td>
                    </tr>
                    <?php
                  }
                echo '    </table>';
              echo '  </td>';
            echo '</tr>';
          }
        }
      }
    ?>
  </table>
  </div>
  <?php
} else {
  switch ($action) {
    case 'edit_email_content':
    case 'new_email_content':
      if ($action =='edit_email_content') {
        $content_query=xtc_db_query("SELECT*
                                       FROM ".TABLE_EMAIL_CONTENT."
                                      WHERE content_id = '".$g_coID."'
                                      LIMIT 1");
        $content=xtc_db_fetch_array($content_query);
      }
      
      // get templates
      $invalid_template = array(
        'signatur',
        'widerruf',
        'contact_us',
      );

      $template_array = auto_include(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/','html');
      $template_array = array_merge($template_array, auto_include(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/admin/mail/'.$_SESSION['language'].'/','html'));

      foreach ($template_array as $index => $template) {
        $template = strstr(basename($template), '.html', true);
        $template_array[$index] = $template;
        if (in_array($template, $invalid_template)) {
          unset($template_array[$index]);
        }
      }
      $template_array = array_unique($template_array);
      sort($template_array);

      $email_array = array();
      foreach ($template_array as $template) {  
        $email_array[] = array(
          'id' => $template,
          'text' => ucwords(implode(' ', explode('_', $template))),
        );
      }

      // get languages
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
      $content_files = array();
      $content_files_query=xtc_db_query("SELECT DISTINCT *
                                                    FROM ".TABLE_EMAIL_CONTENT."
                                                   WHERE content_file != ''
                                                ORDER BY content_name ASC");
      while ($content_files_data=xtc_db_fetch_array($content_files_query)) {
        $content_files[] = array(
          'id' => $content_files_data['content_file'],
          'text' => $content_files_data['content_name'],
        );
        
        if (in_array($content_files_data['content_file'], $files_array)) {
          $key = array_search ($content_files_data['content_file'], $files_array);
          unset($files_array[$key]);
        }
      }

      $content_files_query=xtc_db_query("SELECT DISTINCT *
                                                    FROM ".TABLE_CONTENT_MANAGER_CONTENT."
                                                   WHERE content_file != ''
                                                ORDER BY content_name ASC");
      while ($content_files_data=xtc_db_fetch_array($content_files_query)) {
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
      <div style="width:99%; margin:5px;">
      <div class="pageHeading"><br /><?php echo HEADING_EMAIL_CONTENT; ?><br /></div>
      <div class="main"><?php echo TEXT_EMAIL_DESCRIPTION; ?></div>
        <?php 
        if ($action != 'new_email_content') {
          echo xtc_draw_form('edit_content',FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action')) . 'action=edit_email_content&id=update_email&coID='.$g_coID,'post','enctype="multipart/form-data"').xtc_draw_hidden_field('coID',$g_coID);
        } else {
          echo xtc_draw_form('edit_content',FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action')) . 'action=edit_email_content&id=insert_email','post','enctype="multipart/form-data"');
        }
        ?>
        <table class="tableConfig borderall">
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_EMAIL_CONTENT; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo xtc_draw_pull_down_menu('product',$email_array,$content['email_id']); ?></td>
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
                        if (strpos($content['group_ids'],'c_'.$customers_statuses_array[$i]['id'].'_group')) {
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
                <td class="dataTableConfig col-single-right"><?php echo xtc_draw_hidden_field('file_name',$content['content_file']).xtc_image('../'. DIR_WS_IMAGES. 'icons/filetype/icon_'.str_replace('.','',strstr($content['content_file'],'.')).'.gif').$content['content_file']; //DokuMan - 2011-09-06 - change path ?></td>
              </tr>
              <?php
            }
          ?>          
        </table>
        <div class="flt-r mrg5 pdg2">
          <?php echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SAVE . '"/>'; ?>
        </div>
        <div class="flt-r mrg5 pdg2">
          <a class="button" onclick="this.blur();" href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action'))); ?>"><?php echo BUTTON_BACK; ?></a>
        </div>
      </form>
      </div>
      <?php
      break;
  }
}