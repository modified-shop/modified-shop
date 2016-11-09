<?php
/* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------

   Released under the GNU General Public License
   --------------------------------------------------------------*/
  
  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

  require_once(DIR_FS_INC . 'xtc_format_filesize.inc.php');
  require_once(DIR_FS_INC . 'xtc_filesize.inc.php');

  if (file_exists(DIR_FS_LANGUAGES . $_SESSION['language'] . '/admin/'.FILENAME_CONTENT_MANAGER)) {
    include_once(DIR_FS_LANGUAGES . $_SESSION['language'] . '/admin/'. FILENAME_CONTENT_MANAGER);
  }
  ?>

  <?php
    $content_query=xtc_db_query("SELECT content_id,
                                        content_name,
                                        content_file,
                                        content_link,
                                        languages_id,
                                        file_comment,
                                        content_read 
                                   FROM ".TABLE_PRODUCTS_CONTENT."
                                  WHERE products_id='".(int)$_GET['pID']."'
                                    AND languages_id='".$languages[$i]['id']."'
                               ORDER BY content_name");

    if (xtc_db_num_rows($content_query)>0) {
      ?>
      <div class="main" style="margin:15px 5px 2px 5px"><b><?php echo $lng_image . '&nbsp;' . HEADING_PRODUCTS_CONTENT; ?></b></div>
        <table width="100%" cellspacing="0" cellpadding="5" border="0" style="padding: 0 2px 0 4px;">
          <tr class="dataTableHeadingRow">
            <td class="dataTableHeadingContent" nowrap width="2%" ><?php echo TABLE_HEADING_PRODUCTS_CONTENT_ID; ?></td>
            <td class="dataTableHeadingContent" nowrap width="2%" >&nbsp;</td>
            <td class="dataTableHeadingContent" nowrap width="20%" ><?php echo TABLE_HEADING_CONTENT_NAME; ?></td>
            <td class="dataTableHeadingContent" nowrap width="28%" ><?php echo TABLE_HEADING_CONTENT_FILE; ?></td>
            <td class="dataTableHeadingContent" nowrap width="1%" ><?php echo TABLE_HEADING_CONTENT_FILESIZE; ?></td>
            <td class="dataTableHeadingContent" nowrap align="middle" width="20%" ><?php echo TABLE_HEADING_CONTENT_LINK; ?></td>
            <td class="dataTableHeadingContent" nowrap width="5%" ><?php echo TABLE_HEADING_CONTENT_HITS; ?></td>
            <td class="dataTableHeadingContent" nowrap width="22%" ><?php echo TABLE_HEADING_CONTENT_ACTION; ?></td>
          </tr>
          <?php
          while ($content_array = xtc_db_fetch_array($content_query)) {
            echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\'" onmouseout="this.className=\'dataTableRow\'">' . "\n";
            ?>
              <td class="dataTableContent" align="left"><?php echo  $content_array['content_id']; ?> </td>
              <td class="dataTableContent" align="left">
                <?php
                  if ($content_array['content_file']!='') {
                    echo xtc_image('../'. DIR_WS_IMAGES.'icons/filetype/icon_'.str_replace('.','',strstr($content_array['content_file'],'.')).'.gif');
                  } else {
                    echo xtc_image('../'. DIR_WS_IMAGES.'icons/filetype/icon_link.gif');
                  }
                ?>
              </td>
              <td class="dataTableContent" align="left"><?php echo $content_array['content_name']; ?></td>
              <td class="dataTableContent" align="left"><?php echo $content_array['content_file'] . '&nbsp;'; ?></td>
              <td class="dataTableContent" align="left"><?php echo xtc_filesize($content_array['content_file']); ?></td>
              <td class="dataTableContent" align="left" align="middle">
                <?php
                  if ($content_array['content_link']!='') {
                    echo '<a href="../'.$content_array['content_link'].'" target="new">'.$content_array['content_link'].'</a>';
                  }
                ?>
                &nbsp;
              </td>
              <td class="dataTableContent" align="left"><?php echo $content_array['content_read']; ?></td>
              <td class="dataTableContent" align="left">
                <a href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action')) . 'last_action='.$_GET['action'].'&special=delete_product&coID='.$content_array['content_id'].'&set=product'); ?>" onclick="return confirmLink('<?php echo CONFIRM_DELETE.'<br/>'.CONTINUE_WITHOUT_SAVE; ?>', '', this)">
                <?php
                  echo xtc_image(DIR_WS_ICONS.'delete.gif', ICON_DELETE,'','','style="cursor:pointer"').'  '.TEXT_DELETE.'</a>&nbsp;&nbsp;';
                ?>
                <a href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action')) . 'last_action='.$_GET['action'].'&action=edit_products_content&coID='.$content_array['content_id'].'&set=product'); ?>" onclick="return confirmLink('<?php echo CONTINUE_WITHOUT_SAVE; ?>', '', this)">
                <?php
                  echo xtc_image(DIR_WS_ICONS.'icon_edit.gif', ICON_EDIT,'','','style="cursor:pointer"').'  '.TEXT_EDIT.'</a>';
                  if (preg_match('/.gif/i',$content_array['content_file'])
                    ||
                      preg_match('/.jpg/i',$content_array['content_file'])
                    ||
                      preg_match('/.png/i',$content_array['content_file'])
                    ||
                      preg_match('/.html/i',$content_array['content_file'])
                    ||
                      preg_match('/.htm/i',$content_array['content_file'])
                    ||
                      preg_match('/.txt/i',$content_array['content_file'])
                    ||
                      preg_match('/.bmp/i',$content_array['content_file'])
                    ) {
                    ?>
                    <a style="cursor:pointer" onclick="javascript:window.open('<?php echo xtc_href_link(FILENAME_CONTENT_PREVIEW,'pID=media&coID='.$content_array['content_id']); ?>', 'popup', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no, width=640, height=600')">
                    <?php
                    echo xtc_image(DIR_WS_ICONS.'preview.gif', ICON_PREVIEW,'','',' style="cursor:pointer"').'&nbsp;&nbsp;'.TEXT_PREVIEW.'</a>';
                  }
                ?>
              </td>
            </tr>
            <?php
          }
          ?>
        </table>
      <?php
    }
  ?>