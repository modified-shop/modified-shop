<?php
  /* --------------------------------------------------------------
   $Id: content_manager_products.php 5007 2013-07-04 09:31:37Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
 
   Released under the GNU General Public License
   --------------------------------------------------------------*/

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

if (!$action) {
  // products content
  // load products_ids into array
  $products_id_query=xtc_db_query("SELECT DISTINCT
                                                   pc.products_id,
                                                   pd.products_name
                                              FROM ".TABLE_PRODUCTS_CONTENT." pc,
                                                   ".TABLE_PRODUCTS_DESCRIPTION." pd
                                             WHERE pd.products_id=pc.products_id
                                               AND pd.language_id='".(int)$_SESSION['languages_id']."'");
  $products_ids=array();
  while ($products_id_data=xtc_db_fetch_array($products_id_query)) {
    $products_ids[]=array('id'=>$products_id_data['products_id'],
                        'name'=>$products_id_data['products_name']);
  } // while
  ?>
  <div class="pageHeadingTaba pdg2 flt-l"><a onclick="this.blur();" href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER); ?>"><?php echo HEADING_CONTENT; ?></a></div>
  <div class="pageHeadingTab pdg2 flt-l"><?php echo HEADING_PRODUCTS_CONTENT; ?></div>
  <div class="borderTab">
  <?php
    $total_space_media_products = xtc_spaceUsed(DIR_FS_CATALOG.'media/products/'); // DokuMan - 2011-09-06 - sum up correct filesize avoiding global variable
    echo '<div class="main clear">'.USED_SPACE.xtc_format_filesize($total_space_media_products).'</div><br />';
  ?>
  <table class="tableCenter">
    <tr class="dataTableHeadingRow">
      <td class="dataTableHeadingContent nobr txta-c"><?php echo TABLE_HEADING_PRODUCTS_ID; ?></td>
      <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
    </tr>
    <?php
      for ($i=0,$n=sizeof($products_ids); $i<$n; $i++) {
        echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\'" onmouseout="this.className=\'dataTableRow\'">' . "\n";
          ?>
          <td class="dataTableContent_products txta-c" style="width:5%"><?php echo $products_ids[$i]['id']; ?></td>
          <td class="dataTableContent_products"><b>
            <?php echo xtc_image(DIR_WS_CATALOG.'images/icons/arrow.gif'); ?>
            <a href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER,'pID='.$products_ids[$i]['id'].$setparam);?>"><?php echo $products_ids[$i]['name']; ?></a></b>
          </td>
        </tr>
        <?php
        if ($_GET['pID']) {
          // display content elements
          $content_query=xtc_db_query("SELECT
                                              content_id,
                                              content_name,
                                              content_file,
                                              content_link,
                                              languages_id,
                                              file_comment,
                                              content_read
                                         FROM ".TABLE_PRODUCTS_CONTENT."
                                        WHERE products_id='".$_GET['pID']."'
                                     ORDER BY content_name");
          $content_array='';
          while ($content_data = xtc_db_fetch_array($content_query)) {
            $content_array[]=array('id'=> $content_data['content_id'],
                                 'name'=> $content_data['content_name'],
                                 'file'=> $content_data['content_file'],
                                 'link'=> $content_data['content_link'],
                              'comment'=> $content_data['file_comment'],
                         'languages_id'=> $content_data['languages_id'],
                                 'read'=> $content_data['content_read']);
          } // while content data

          if ($_GET['pID']==$products_ids[$i]['id']){
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
                          if ($content_array[$ii]['file']!='') {
                            echo xtc_image('../'. DIR_WS_IMAGES.'icons/filetype/icon_'.str_replace('.','',strstr($content_array[$ii]['file'],'.')).'.gif'); //web28 - 2010-09-03 - change path
                          } else {
                            echo xtc_image('../'. DIR_WS_IMAGES.'icons/filetype/icon_link.gif'); //web28 - 2010-09-03 - change path
                          }
                          for ($xx=0,$zz=sizeof($languages); $xx<$zz;$xx++){
                            if ($languages[$xx]['id']==$content_array[$ii]['languages_id']) {
                              $lang_dir=$languages[$xx]['directory'];
                              break;
                            }
                          }
                        ?>
                      </td>
                      <td class="dataTableContent txta-c"><?php echo xtc_image(DIR_WS_CATALOG.'lang/'.$lang_dir.'/admin/images/icon.gif'); ?></td>
                      <td class="dataTableContent"><?php echo $content_array[$ii]['name']; ?></td>
                      <td class="dataTableContent"><?php echo $content_array[$ii]['file']; ?></td>
                      <td class="dataTableContent txta-c"><?php echo xtc_filesize($content_array[$ii]['file']); ?></td>
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
                        <a href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER,'special=delete_product&coID='.$content_array[$ii]['id'].'&pID='.$products_ids[$i]['id'].'&set='.$set); ?>" onclick="return confirm('<?php echo CONFIRM_DELETE; ?>')">
                        <?php
                          echo xtc_image(DIR_WS_ICONS.'delete.gif', ICON_DELETE,'','','style="cursor:pointer" onclick="return confirm(\''.DELETE_ENTRY.'\')"').'  '.TEXT_DELETE.'</a>&nbsp;&nbsp;';
                        ?>
                        <a href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER,'action=edit_products_content&coID='.$content_array[$ii]['id'].$setparam); ?>">
                          <?php
                          echo xtc_image(DIR_WS_ICONS.'icon_edit.gif', ICON_EDIT,'','','style="cursor:pointer"').'  '.TEXT_EDIT.'</a>';
                        // display preview button if filetype in array
                        $allowed_filetypes = array('.gif','.jpg','.png','.html','.htm','.txt','.bmp'); 
                        if (in_array(substr($content_array[$ii]['file'], 0, strrpos($content_array[$ii]['file'], '.') - 1), $allowed_filetypes)) {
                          ?>
                          <a style="cursor:pointer" onclick="javascript:window.open('<?php echo xtc_href_link(FILENAME_CONTENT_PREVIEW,'pID=media&coID='.$content_array[$ii]['id']); ?>', 'popup', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no, width=640, height=600')">
                            <?php
                            echo xtc_image(DIR_WS_ICONS.'preview.gif', ICON_PREVIEW,'','',' style="cursor:pointer"').'&nbsp;&nbsp;'.TEXT_PREVIEW.'</a>';
                        }
                        ?>
                      </td>
                    </tr>
                    <?php
                  } // for content_array
                echo '    </table>';
              echo '  </td>';
            echo '</tr>';
          }
        } // for
      }
    ?>
  </table>
  </div>
  <?php
} else {
  switch ($action) {
    case 'edit_products_content':
    case 'new_products_content':
      if ($action =='edit_products_content') {
        $content_query=xtc_db_query("SELECT
                                      content_id,
                                      products_id,
                                      group_ids,
                                      content_name,
                                      content_file,
                                      content_link,
                                      languages_id,
                                      file_comment,
                                      content_read
                                     FROM ".TABLE_PRODUCTS_CONTENT."
                                     WHERE content_id='".$g_coID."'
                                     LIMIT 1"); //DokuMan - 2011-05-13 - added LIMIT 1
        $content=xtc_db_fetch_array($content_query);
      }
      // get products names.
      $products_query=xtc_db_query("SELECT
                                           products_id,
                                           products_name
                                      FROM ".TABLE_PRODUCTS_DESCRIPTION."
                                     WHERE language_id='".(int)$_SESSION['languages_id']."'
                                  ORDER BY products_name"); // Tomcraft - 2010-09-15 - Added default sort order to products_name for product-content in content-manager
      $products_array=array();
      while ($products_data=xtc_db_fetch_array($products_query)) {
        $products_array[]=array('id' => $products_data['products_id'],
                              'text' => $products_data['products_name']);
      }

      // get languages
      $languages_array = array();
      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        if ($languages[$i]['id']==$content['languages_id']) {
          $languages_selected=$languages[$i]['code'];
          $languages_id=$languages[$i]['id'];
        }
        $languages_array[] = array('id' => $languages[$i]['code'],
                                 'text' => $languages[$i]['name']);
      }

      // get used content files
      $content_files_query=xtc_db_query("SELECT DISTINCT
                                                         content_name,
                                                         content_file
                                                    FROM ".TABLE_PRODUCTS_CONTENT."
                                                   WHERE content_file!=''");
      $content_files=array();
      while ($content_files_data=xtc_db_fetch_array($content_files_query)) {
        $content_files[]=array('id' => $content_files_data['content_file'],
                             'text' => $content_files_data['content_name']);
      }

      // add default value to array
      $default_array[]=array('id' => 'default','text' => TEXT_SELECT);
      $default_value='default';
      $content_files=array_merge($default_array,$content_files);
      // mask for product content
      
      ?>
      <div style="width:99%; margin:5px;">
      <div class="pageHeading"><br /><?php echo HEADING_PRODUCTS_CONTENT; ?><br /></div>
      <div class="main"><?php echo TEXT_CONTENT_DESCRIPTION; ?></div>
        <?php 
        if ($action !='new_products_content') {
          echo xtc_draw_form('edit_content',FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action')) . 'action=edit_products_content&id=update_product&coID='.$g_coID,'post','enctype="multipart/form-data"').xtc_draw_hidden_field('coID',$g_coID);
        } else {
          echo xtc_draw_form('edit_content',FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action')) . 'action=edit_products_content&id=insert_product','post','enctype="multipart/form-data"');
        }
        ?>
        <table class="tableConfig borderall">
          <tr>
            <td class="dataTableConfig col-left"><?php echo TEXT_PRODUCT; ?></td>
            <td class="dataTableConfig col-single-right"><?php echo ((isset($_GET['pID'])) ? xtc_get_products_name($_GET['pID']) . xtc_draw_hidden_field('product', (int)$_GET['pID']) : xtc_draw_pull_down_menu('product',$products_array,$content['products_id'])); ?></td>
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
                        if (strstr($content['group_ids'],'c_'.$customers_statuses_array[$i]['id'].'_group')) {
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