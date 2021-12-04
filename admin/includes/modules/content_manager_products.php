<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
 
   Released under the GNU General Public License
   --------------------------------------------------------------*/

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

if (!$action || $action == 'delete') {
  ?>
  <div class="main flt-r pdg2 mrg5">
    <?php echo xtc_draw_form('pages', FILENAME_CONTENT_MANAGER, '', 'get'); ?>
    <?php echo HEADING_TITLE_GOTO . ' ' . xtc_draw_pull_down_menu('set', $content_pages_array, isset($_GET['set']) ? $_GET['set'] : '', 'onChange="this.form.submit();"'); ?>
    </form>
  </div>
  <div class="clear"></div>     
  <table class="tableCenter">      
    <tr>
      <td class="boxCenterLeft">
        <table class="tableBoxCenter collapse">
          <?php
          if (isset($_GET['pID']) && $_GET['pID'] != '') {
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
              <td class="dataTableContent"><?php echo '<a href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('coID', 'pID'))). '">'.xtc_image(DIR_WS_ICONS . 'folder_parent.gif', ICON_FOLDER) . ' ..</a>'; ?></td>
              <td class="dataTableContent txta-c">--</td>
              <td class="dataTableContent txta-c">--</td>
              <td class="dataTableContent txta-c">--</td>
              <td class="dataTableContent txta-c">--</td>
              <td class="dataTableContent txta-c">--</td>
              <td class="dataTableContent txta-c">--</td>
            </tr>
            <?php
              $content_query = xtc_db_query("SELECT *
                                               FROM ".TABLE_PRODUCTS_CONTENT." pc
                                               JOIN ".TABLE_LANGUAGES." l
                                                    ON pc.languages_id = l.languages_id
                                              WHERE products_id = '".(int)$_GET['pID']."'
                                           ORDER BY content_id");
              while ($content_data = xtc_db_fetch_array($content_query)) {
                if ((!isset($_GET['coID']) || $_GET['coID'] == $content_data['content_id']) && !isset($oInfo)) {
                  $oInfo = new objectInfo($content_data);
                }

                if (isset($oInfo) && is_object($oInfo) && $content_data['content_id'] == $oInfo->content_id) {
                  echo '<tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'coID')) . 'coID=' . $oInfo->content_id . '&action=edit_products_content') . '\'">' . "\n";
                } else {
                  echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action','coID')) . 'coID=' . $content_data['content_id']) . '\'">' . "\n";
                }
                ?>
                  <td class="dataTableContent txta-c"><?php echo $content_data['content_id']; ?> </td>
                  <td class="dataTableContent"><?php echo $content_data['content_name']; ?></td>
                  <td class="dataTableContent txta-c"><?php echo xtc_image(DIR_WS_CATALOG.'lang/'.$content_data['directory'].'/admin/images/icon.gif'); ?></td>
                  <td class="dataTableContent"><?php echo $content_data['content_file']; ?></td>
                  <td class="dataTableContent txta-c"><?php echo xtc_filesize($content_data['content_file']); ?></td>
                  <td class="dataTableContent txta-c">
                    <?php
                      if ($content_data['content_link'] != '') {
                        echo '<a href="'.$content_data['content_link'].'" target="_blank">'.$content_data['content_link'].'</a>';
                      } else {
                        echo '--';
                      }
                    ?>
                  </td>
                  <td class="dataTableContent txta-c"><?php echo $content_data['content_read']; ?></td>
                  <td class="dataTableContent txta-r"><?php if (isset($oInfo) && is_object($oInfo) && $content_data['content_id'] == $oInfo->content_id) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('coID')) . 'coID=' . $content_data['content_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                </tr>
                <?php
              }
              ?>
        </table>
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
            $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'coID')) . 'coID=' . $oInfo->content_id) . '">' . BUTTON_CANCEL . '</a>');
            break;

          default:
            if (isset($oInfo) && is_object($oInfo)) {
              $heading[] = array('text' => '<b>' . $oInfo->content_name . '</b>');

              $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'coID')) . 'coID=' . $oInfo->content_id . '&action=edit_products_content') . '">' . BUTTON_EDIT . '</a> 
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
          <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
        </tr>
        <?php
          $content_query_raw = "SELECT DISTINCT p.products_id,
                                                p.products_model,
                                                pd.products_name
                                           FROM ".TABLE_PRODUCTS_CONTENT." pc
                                           JOIN ".TABLE_PRODUCTS." p
                                                ON pc.products_id = p.products_id
                                           JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                                ON pd.products_id = pc.products_id
                                                   AND pd.language_id = '".(int)$_SESSION['languages_id']."'
                                       ORDER BY p.products_id ASC";
          $content_query_split = new splitPageResults($page, $page_max_display_results, $content_query_raw, $content_query_numrows, 'p.products_id');          
          $content_query = xtc_db_query($content_query_raw);
          while ($content = xtc_db_fetch_array($content_query)) {
            ?>
            <tr class="dataTableRow" onmouseover="this.className='dataTableRowOver'" onmouseout="this.className='dataTableRow'">
              <td class="dataTableContent txta-c"><?php echo $content['products_id']; ?></td>
              <td class="dataTableContent"><?php echo '<a href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('pID')).'pID='.$content['products_id']). '">'.xtc_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER, '', '', $icon_padding) . '</a>' . '<span style="vertical-align: 3px;">' . $content['products_name'] .'</span>'; ?></td>
            </tr>
            <?php
          }
          ?>
        </table>
        <div class="smallText pdg2 flt-l"><?php echo $content_query_split->display_count($content_query_numrows, $page_max_display_results, $page, TEXT_DISPLAY_NUMBER_OF_CONTENT_MANAGER); ?></div>
        <div class="smallText pdg2 flt-r"><?php echo $content_query_split->display_links($content_query_numrows, $page_max_display_results, MAX_DISPLAY_PAGE_LINKS, $page); ?></div>
        <?php echo draw_input_per_page($PHP_SELF, $cfg_max_display_results_key, $page_max_display_results); ?>
      </td>
      <?php
        $heading = array();
        $contents = array();
        switch ($action) {
          default:
            $heading[] = array('text' => '<b>' . USED_SPACE . '</b>');

            $total_space_media_products = xtc_spaceUsed(DIR_FS_CATALOG.'media/products/');
            $contents[] = array('align' => 'center', 'text' => '<div style="font-weight:bold; margin-bottom:10px;">'.xtc_format_filesize($total_space_media_products).'</div>');
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
  switch ($action) {
    case 'edit_products_content':
    case 'new_products_content':
      if ($action == 'edit_products_content' && isset($g_coID) && (int)$g_coID > 0) {
        $content_query = xtc_db_query("SELECT *
                                         FROM ".TABLE_PRODUCTS_CONTENT."
                                        WHERE content_id = '".$g_coID."'
                                        LIMIT 1");
        $content = xtc_db_fetch_array($content_query);
      } else {
        $content = xtc_get_default_table_data(TABLE_PRODUCTS_CONTENT);
      }
      
      // get products names
      $products_query = xtc_db_query("SELECT products_id,
                                             products_name
                                        FROM ".TABLE_PRODUCTS_DESCRIPTION."
                                       WHERE language_id = '".(int)$_SESSION['languages_id']."'
                                    ORDER BY products_name");
      $products_array = array();
      while ($products_data = xtc_db_fetch_array($products_query)) {
        $products_array[] = array(
          'id' => $products_data['products_id'],
          'text' => $products_data['products_name'],
        );
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
      <div style="width:99%; margin:5px;">
      <div class="pageHeading"><br /><?php echo HEADING_PRODUCTS_CONTENT; ?><br /></div>
      <div class="main"><?php echo TEXT_CONTENT_DESCRIPTION; ?></div>
        <?php 
        if ($action != 'new_products_content') {
          echo xtc_draw_form('edit_content',FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action')) . 'action=edit_products_content&id=update_products&coID='.$g_coID,'post','enctype="multipart/form-data"').xtc_draw_hidden_field('coID',$g_coID);
        } else {
          echo xtc_draw_form('edit_content',FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action')) . 'action=edit_products_content&id=insert_products','post','enctype="multipart/form-data"');
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
                <td class="dataTableConfig col-single-right"><?php echo xtc_draw_hidden_field('file_name',$content['content_file']).xtc_image('../'. DIR_WS_IMAGES. 'icons/filetype/icon_'.str_replace('.','',strstr($content['content_file'],'.')).'.gif').$content['content_file']; //DokuMan - 2011-09-06 - change path ?></td>
              </tr>
              <?php
            }
          ?>          
        </table>

        <?php 
          foreach(auto_include(DIR_FS_ADMIN.'includes/extra/modules/content_manager/products/','php') as $file) require ($file);
        ?>    

        <div class="flt-r mrg5 pdg2">
          <?php echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SAVE . '"/>'; ?>
        </div>
        <div class="flt-r mrg5 pdg2">
          <?php
          if (isset($_GET['last_action']) && isset($_GET['cPath'])) {
            echo '<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('set', 'last_action', 'action', 'coID', 'search')) . 'action='.$_GET['last_action']).'">'.BUTTON_BACK.'</a>';
          } else {
            echo '<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action'))).'">'.BUTTON_BACK.'</a>';
          }
          ?>
        </div>
      </form>
      </div>
      <?php
      break;
  }
}