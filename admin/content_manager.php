<?php
  /* --------------------------------------------------------------
   $Id: content_manager.php 4143 2012-12-18 14:55:48Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommercecoding standards www.oscommerce.com
   (c) 2003 nextcommerce (content_manager.php,v 1.18 2003/08/25); www.nextcommerce.org
   (c) 2006 XT-Commerce (content_manager.php 1304 2005-10-12)

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  require('includes/application_top.php');
  require_once(DIR_FS_INC . 'xtc_format_filesize.inc.php');
  require_once(DIR_FS_INC . 'xtc_filesize.inc.php');
  require_once(DIR_FS_INC . 'xtc_wysiwyg.inc.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  $special = (isset($_GET['special']) ? $_GET['special'] : '');
  $id = (isset($_GET['id']) ? $_GET['id'] : '');
  $g_coID = (isset($_GET['coID']) ? (int)$_GET['coID'] : '');
  $languages = xtc_get_languages();

  if ($special=='delete') {
    xtc_db_query("DELETE FROM ".TABLE_CONTENT_MANAGER." where content_id='".$g_coID."'");
    xtc_redirect(xtc_href_link(FILENAME_CONTENT_MANAGER));
  } // if get special

  if ($special=='delete_product') {
    xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_CONTENT." where content_id='".$g_coID."'");
    xtc_redirect(xtc_href_link(FILENAME_CONTENT_MANAGER,'pID='.(int)$_GET['pID']));
  } // if get special

  if ($id=='update' or $id=='insert') {
    // set allowed c.groups
    $group_ids='';
    if(isset($_POST['groups'])) foreach($_POST['groups'] as $b){
      $group_ids .= 'c_'.$b."_group ,";
    }
    $customers_statuses_array=xtc_get_customers_statuses();
    if (strstr($group_ids,'c_all_group')) {
      $group_ids='c_all_group,';
      for ($i=0;$n=sizeof($customers_statuses_array),$i<$n;$i++) {
        $group_ids .='c_'.$customers_statuses_array[$i]['id'].'_group,';
      }
    }

    $content_title=xtc_db_prepare_input($_POST['cont_title']);
    $content_header=xtc_db_prepare_input($_POST['cont_heading']);
    $content_text=xtc_db_prepare_input($_POST['cont']);
    $coID=xtc_db_prepare_input($_POST['coID']);
    $upload_file=xtc_db_prepare_input($_POST['file_upload']);
    $content_status=xtc_db_prepare_input($_POST['status']);
    $content_language=xtc_db_prepare_input($_POST['language']);
    $select_file=xtc_db_prepare_input($_POST['select_file']);
    $file_flag=xtc_db_prepare_input($_POST['file_flag']);
    $parent_check=xtc_db_prepare_input($_POST['parent_check']);
    $parent_id=xtc_db_prepare_input($_POST['parent']);

    $content_query = xtc_db_query("SELECT MAX(content_group) AS content_group FROM ".TABLE_CONTENT_MANAGER."");
    $content_data = mysql_fetch_row($content_query);
    if ($_POST['content_group'] == '0' || $_POST['content_group'] == '') {
      $group_id = $content_data[0] + 1;
    } else {
      $group_id = xtc_db_prepare_input($_POST['content_group']);
    }

    $group_ids = $group_ids;
    $sort_order=xtc_db_prepare_input($_POST['sort_order']);
    $content_meta_title = xtc_db_prepare_input($_POST['cont_meta_title']);
    $content_meta_description = xtc_db_prepare_input($_POST['cont_meta_description']);
    $content_meta_keywords = xtc_db_prepare_input($_POST['cont_meta_keywords']);

    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
      if ($languages[$i]['code']==$content_language) {
       $content_language=$languages[$i]['id'];
      }
    } // for

    $error=false; // reset error flag
    if (strlen($content_title) < 1) {
      $error = true;
      $messageStack->add(ERROR_TITLE,'error');
    }  // if

    if ($content_status=='yes'){
      $content_status=1;
    } else{
      $content_status=0;
    }  // if

    if ($parent_check=='yes'){
      $parent_id=$parent_id;
    } else{
      $parent_id='0';
    }  // if

    if ($error == false) {
      // file upload
      if ($select_file!='default') {
        $content_file_name=$select_file;
      }
      $accepted_file_upload_files_extensions = array("xls","xla","hlp","chm","ppt","ppz","pps","pot","doc","dot","pdf","rtf","swf","cab","tar","zip","au","snd","mp2","rpm","stream","wav","gif","jpeg","jpg","jpe","png","tiff","tif","bmp","csv","txt","rtf","tsv","mpeg","mpg","mpe","qt","mov","avi","movie","rar","7z");
      $accepted_file_upload_files_mime_types = array("application/msexcel","application/mshelp","application/mspowerpoint","application/msword","application/pdf","application/rtf","application/x-shockwave-flash","application/x-tar","application/zip","audio/basic","audio/x-mpeg","audio/x-pn-realaudio-plugin","audio/x-qt-stream","audio/x-wav","image/gif","image/jpeg","image/png","image/tiff","image/bmp","text/comma-separated-values","text/plain","text/rtf","text/tab-separated-values","video/mpeg","video/quicktime","video/x-msvideo","video/x-sgi-movie","application/x-rar-compressed","application/x-7z-compressed");
      if ($content_file = xtc_try_upload('file_upload', DIR_FS_CATALOG.'media/content/','644',$accepted_file_upload_files_extensions,$accepted_file_upload_files_mime_types)) {
        $content_file_name=$content_file->filename;
      }

      // update data in table
      $sql_data_array = array(
                            'languages_id' => $content_language,
                            'content_title' => $content_title,
                            'content_heading' => $content_header,
                            'content_text' => $content_text,
                            'content_file' => $content_file_name,
                            'content_status' => $content_status,
                            'parent_id' => $parent_id,
                            'group_ids' => $group_ids,
                            'content_group' => $group_id,
                            'sort_order' => $sort_order,
                            'file_flag' => $file_flag,
                            'content_meta_title' => $content_meta_title,
                            'content_meta_description' => $content_meta_description,
                            'content_meta_keywords' => $content_meta_keywords);
      if ($id=='update') {
        xtc_db_perform(TABLE_CONTENT_MANAGER, $sql_data_array, 'update', "content_id = '" . $coID . "'");
      } else {
        xtc_db_perform(TABLE_CONTENT_MANAGER, $sql_data_array);
      } // if get id
      xtc_redirect(xtc_href_link(FILENAME_CONTENT_MANAGER));
    } // if error
  } // if

  if ($id=='update_product' or $id=='insert_product') {
    // set allowed c.groups
    $group_ids='';
    if(isset($_POST['groups'])) foreach($_POST['groups'] as $b){
      $group_ids .= 'c_'.$b."_group ,";
    }
    $customers_statuses_array=xtc_get_customers_statuses();
    if (strstr($group_ids,'c_all_group')) {
      $group_ids='c_all_group,';
      for ($i=0;$n=sizeof($customers_statuses_array),$i<$n;$i++) {
        $group_ids .='c_'.$customers_statuses_array[$i]['id'].'_group,';
     }
    }

    $content_title=xtc_db_prepare_input($_POST['cont_title']);
    $content_link=xtc_db_prepare_input($_POST['cont_link']);
    $content_language=xtc_db_prepare_input($_POST['language']);
    $product=xtc_db_prepare_input($_POST['product']);
    $upload_file=xtc_db_prepare_input($_POST['file_upload']);
    $filename=xtc_db_prepare_input($_POST['file_name']);
    $coID=xtc_db_prepare_input($_POST['coID']);
    $file_comment=xtc_db_prepare_input($_POST['file_comment']);
    $select_file=xtc_db_prepare_input($_POST['select_file']);
    $group_ids = $group_ids;
    $error=false; // reset error flag

    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
      if ($languages[$i]['code']==$content_language) $content_language=$languages[$i]['id'];
    } // for

    if (strlen($content_title) < 1) {
      $error = true;
      $messageStack->add(ERROR_TITLE,'error');
    }  // if

    if ($error == false) {
       // mkdir() wont work with php in safe_mode
       //if  (!is_dir(DIR_FS_CATALOG.'media/products/'.$product.'/')) {
       //  $old_umask = umask(0);
       //  xtc_mkdirs(DIR_FS_CATALOG.'media/products/'.$product.'/',0777);
       //  umask($old_umask);
       //}
      if ($select_file=='default') {
        $accepted_file_upload_files_extensions = array("xls","xla","hlp","chm","ppt","ppz","pps","pot","doc","dot","pdf","rtf","swf","cab","tar","zip","au","snd","mp2","rpm","stream","wav","gif","jpeg","jpg","jpe","png","tiff","tif","bmp","csv","txt","rtf","tsv","mpeg","mpg","mpe","qt","mov","avi","movie","rar","7z");
        $accepted_file_upload_files_mime_types = array("application/msexcel","application/mshelp","application/mspowerpoint","application/msword","application/pdf","application/rtf","application/x-shockwave-flash","application/x-tar","application/zip","audio/basic","audio/x-mpeg","audio/x-pn-realaudio-plugin","audio/x-qt-stream","audio/x-wav","image/gif","image/jpeg","image/png","image/tiff","image/bmp","text/comma-separated-values","text/plain","text/rtf","text/tab-separated-values","video/mpeg","video/quicktime","video/x-msvideo","video/x-sgi-movie","application/x-rar-compressed","application/x-7z-compressed");
        if ($content_file = xtc_try_upload('file_upload', DIR_FS_CATALOG.'media/products/','644',$accepted_file_upload_files_extensions,$accepted_file_upload_files_mime_types)) {
          $content_file_name = $content_file->filename;
          $old_filename = $content_file->filename;
          $timestamp = str_replace('.','',microtime());
          $timestamp = str_replace(' ','',$timestamp);
          $content_file_name = $timestamp.strstr($content_file_name,'.');
          $rename_string = DIR_FS_CATALOG.'media/products/'.$content_file_name;
          rename(DIR_FS_CATALOG.'media/products/'.$old_filename,$rename_string);
          copy($rename_string,DIR_FS_CATALOG.'media/products/backup/'.$content_file_name);
        }
        if ($content_file_name=='')
          $content_file_name=$filename;
      } else {
        $content_file_name = $select_file;
      }

      // update data in table
      // set allowed c.groups
      $group_ids='';
      if(isset($_POST['groups'])) foreach($_POST['groups'] as $b){
        $group_ids .= 'c_'.$b."_group ,";
      }
      $customers_statuses_array=xtc_get_customers_statuses();
      if (strstr($group_ids,'c_all_group')) {
        $group_ids='c_all_group,';
        for ($i=0;$n=sizeof($customers_statuses_array),$i<$n;$i++) {
          $group_ids .='c_'.$customers_statuses_array[$i]['id'].'_group,';
       }
      }

      $sql_data_array = array(
                              'products_id' => $product,
                              'group_ids' => $group_ids,
                              'content_name' => $content_title,
                              'content_file' => $content_file_name,
                              'content_link' => $content_link,
                              'file_comment' => $file_comment,
                              'languages_id' => $content_language);

      if ($id=='update_product') {
        xtc_db_perform(TABLE_PRODUCTS_CONTENT, $sql_data_array, 'update', "content_id = '" . $coID . "'");
        $content_id = xtc_db_insert_id();
      } else {
        xtc_db_perform(TABLE_PRODUCTS_CONTENT, $sql_data_array);
        $content_id = xtc_db_insert_id();
      } // if get id

      // rename filename
      xtc_redirect(xtc_href_link(FILENAME_CONTENT_MANAGER,'pID='.$product));
    }// if error
  }

require (DIR_WS_INCLUDES.'head.php');

  if (USE_WYSIWYG=='true') {
    $query=xtc_db_query("SELECT code FROM ". TABLE_LANGUAGES ." WHERE languages_id='".$_SESSION['languages_id']."'");
    $data=xtc_db_fetch_array($query);
    if ($action != 'new_products_content' && $action != '')
      echo xtc_wysiwyg('content_manager',$data['code']);
    if ($action =='new_products_content')
      echo xtc_wysiwyg('products_content',$data['code']);
    if ($action =='edit_products_content')
      echo xtc_wysiwyg('products_content',$data['code']);
  }
?>
</head>
<body>
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php');?>
    <!-- header_eof //-->
    <!-- body //-->
    <table border="0" width="100%" cellspacing="2" cellpadding="2">
      <tr>
        <td class="columnLeft2" width="<?php echo BOX_WIDTH; ?>" valign="top">
          <table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
            <!-- left_navigation //-->
            <?php require(DIR_WS_INCLUDES . 'column_left.php');?>
            <!-- left_navigation_eof //-->
          </table>
        </td>
        <!-- body_text //-->
        <td class="boxCenter" width="100%" valign="top">
          <div style="max-width:1240px; min-width:980px;">
          <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
              <td>
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                  <tr>
                    <td width="80" rowspan="2"><?php echo xtc_image(DIR_WS_ICONS.'heading_content.gif'); ?></td>
                    <td class="pageHeading"><?php echo HEADING_TITLE;?></td>
                  </tr>
                  <tr>
                    <td class="main" valign="top">Tools</td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr>
              <td>
                <table width="100%" border="0">
                  <tr>
                    <td>
                      <?php
                        if (!$action) {
                          ?>
                          <div class="pageHeading"><br /><?php echo HEADING_CONTENT; ?><br /></div>
                          <div class="main"><?php echo CONTENT_NOTE; ?></div>
                          <?php
                          $total_space_media_content = xtc_spaceUsed(DIR_FS_CATALOG.'media/content/'); // DokuMan - 2011-09-06 - sum up correct filesize avoiding global variable
                          echo '<div class="main">'.USED_SPACE.xtc_format_filesize($total_space_media_content).'</div>';
                          ?>
                          <?php
                          // Display Content
                          for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                            $content=array();
                            $content_query=xtc_db_query("SELECT
                                                                content_id,
                                                                categories_id,
                                                                parent_id,
                                                                group_ids,
                                                                languages_id,
                                                                content_title,
                                                                content_heading,
                                                                content_text,
                                                                sort_order,
                                                                file_flag,
                                                                content_file,
                                                                content_status,
                                                                content_group,
                                                                content_delete,
                                                                content_meta_title,
                                                                content_meta_description,
                                                                content_meta_keywords
                                                           FROM ".TABLE_CONTENT_MANAGER."
                                                          WHERE languages_id='".$languages[$i]['id']."'
                                                            AND parent_id='0'
                                                       ORDER BY content_group,sort_order
                                                         ");
                            while ($content_data=xtc_db_fetch_array($content_query)) {
                              $content[]=array(
                                               'CONTENT_ID' =>$content_data['content_id'] ,
                                               'PARENT_ID' => $content_data['parent_id'],
                                               'GROUP_IDS' => $content_data['group_ids'],
                                               'LANGUAGES_ID' => $content_data['languages_id'],
                                               'CONTENT_TITLE' => $content_data['content_title'],
                                               'CONTENT_HEADING' => $content_data['content_heading'],
                                               'CONTENT_TEXT' => $content_data['content_text'],
                                               'SORT_ORDER' => $content_data['sort_order'],
                                               'FILE_FLAG' => $content_data['file_flag'],
                                               'CONTENT_FILE' => $content_data['content_file'],
                                               'CONTENT_DELETE' => $content_data['content_delete'],
                                               'CONTENT_GROUP' => $content_data['content_group'],
                                               'CONTENT_STATUS' => $content_data['content_status'],
                                               'CONTENT_META_TITLE' => $content_data['content_meta_title'],
                                               'CONTENT_META_DESCRIPTION' => $content_data['content_meta_description'],
                                               'CONTENT_META_KEYWORDS' => $content_data['content_meta_keywords']);
                            } // while content_data
                            ?>
                            <br />
                            <div class="main"><?php echo xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']).'&nbsp;&nbsp;'.$languages[$i]['name']; ?></div>
                            <table border="0" width="100%" cellspacing="0" cellpadding="2">
                              <tr class="dataTableHeadingRow">
                                <td class="dataTableHeadingContent" width="10" ><?php echo TABLE_HEADING_CONTENT_ID; ?></td>
                                <td class="dataTableHeadingContent" width="10" >&nbsp;</td>
                                <td class="dataTableHeadingContent" width="30%" align="left"><?php echo TABLE_HEADING_CONTENT_TITLE; ?></td>
                                <td class="dataTableHeadingContent" width="1%" align="middle"><?php echo TABLE_HEADING_CONTENT_GROUP; ?></td>
                                <td class="dataTableHeadingContent" width="1%" align="middle"><?php echo TABLE_HEADING_CONTENT_SORT; ?></td>
                                <td class="dataTableHeadingContent" width="25%"align="left"><?php echo TABLE_HEADING_CONTENT_FILE; ?></td>
                                <td class="dataTableHeadingContent" nowrap width="5%" align="left"><?php echo TABLE_HEADING_CONTENT_STATUS; ?></td>
                                <td class="dataTableHeadingContent" nowrap width="" align="middle"><?php echo TABLE_HEADING_CONTENT_BOX; ?></td>
                                <td class="dataTableHeadingContent" width="30%" align="middle"><?php echo TABLE_HEADING_CONTENT_ACTION; ?>&nbsp;</td>
                              </tr>
                              <?php
                              for ($ii = 0, $nn = sizeof($content); $ii < $nn; $ii++) {
                                $file_flag_sql = xtc_db_query("SELECT file_flag_name FROM " . TABLE_CM_FILE_FLAGS . " WHERE file_flag=" . $content[$ii]['FILE_FLAG']);
                                $file_flag_result = xtc_db_fetch_array($file_flag_sql);
                                echo '              <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\'" onmouseout="this.className=\'dataTableRow\'">' . "\n";
                                  if ($content[$ii]['CONTENT_FILE']=='') $content[$ii]['CONTENT_FILE']='database';
                                    ?>
                                    <td class="dataTableContent" align="left"><?php echo $content[$ii]['CONTENT_ID']; ?></td>
                                    <td bgcolor="<?php echo substr((6543216554/$content[$ii]['CONTENT_GROUP']),0,6); ?>" class="dataTableContent" align="left">&nbsp;</td>
                                    <td class="dataTableContent" align="left">
                                      <?php echo $content[$ii]['CONTENT_TITLE']; ?>
                                      <?php
                                      if ($content[$ii]['CONTENT_DELETE']=='0'){
                                        echo '<font color="#ff0000">*</font>';
                                      } ?>
                                    </td>
                                    <td class="dataTableContent" align="middle"><?php echo $content[$ii]['CONTENT_GROUP']; ?></td>
                                    <td class="dataTableContent" align="middle"><?php echo $content[$ii]['SORT_ORDER']; ?>&nbsp;</td>
                                    <td class="dataTableContent" align="left"><?php echo $content[$ii]['CONTENT_FILE']; ?></td>
                                    <td class="dataTableContent" align="middle"><?php if ($content[$ii]['CONTENT_STATUS']==0) { echo TEXT_NO; } else { echo TEXT_YES; } ?></td>
                                    <td class="dataTableContent" align="middle"><?php echo $file_flag_result['file_flag_name']; ?></td>
                                    <td class="dataTableContent" align="right">
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
                                        <a style="cursor:pointer" onclick="javascript:window.open('<?php echo xtc_href_link(FILENAME_CONTENT_PREVIEW,'coID='.$content[$ii]['CONTENT_ID']); ?>', 'popup', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no, width=640, height=600')">
                                          <?php
                                          echo xtc_image(DIR_WS_ICONS.'preview.gif', ICON_PREVIEW,'','','style="cursor:pointer"').'&nbsp;&nbsp;'.TEXT_PREVIEW.'</a>';
                                        ?>
                                    </td>
                                  </tr>
                                    <?php
                                    $content_1=array();
                                    $content_1_query = xtc_db_query("SELECT
                                                                            content_id,
                                                                            categories_id,
                                                                            parent_id,
                                                                            group_ids,
                                                                            languages_id,
                                                                            content_title,
                                                                            content_heading,
                                                                            content_text,
                                                                            file_flag,
                                                                            content_file,
                                                                            content_status,
                                                                            content_delete,
                                                                            content_meta_title,
                                                                            content_meta_description,
                                                                            content_meta_keywords
                                                                       FROM ".TABLE_CONTENT_MANAGER."
                                                                      WHERE languages_id='".$i."'
                                                                        AND parent_id='".$content[$ii]['CONTENT_ID']."'
                                                                   ORDER BY content_group,sort_order
                                                                     ");
                                    while ($content_1_data=xtc_db_fetch_array($content_1_query)) {
                                      $content_1[]=array(
                                                         'CONTENT_ID' =>$content_1_data['content_id'] ,
                                                         'PARENT_ID' => $content_1_data['parent_id'],
                                                         'GROUP_IDS' => $content_1_data['group_ids'],
                                                         'LANGUAGES_ID' => $content_1_data['languages_id'],
                                                         'CONTENT_TITLE' => $content_1_data['content_title'],
                                                         'CONTENT_HEADING' => $content_1_data['content_heading'],
                                                         'CONTENT_TEXT' => $content_1_data['content_text'],
                                                         'SORT_ORDER' => $content_1_data['sort_order'],
                                                         'FILE_FLAG' => $content_1_data['file_flag'],
                                                         'CONTENT_FILE' => $content_1_data['content_file'],
                                                         'CONTENT_DELETE' => $content_1_data['content_delete'],
                                                         'CONTENT_STATUS' => $content_1_data['content_status'],
                                                         'CONTENT_META_TITLE' => $content_1_data['content_meta_title'],
                                                         'CONTENT_META_DESCRIPTION' => $content_1_data['content_meta_description'],
                                                         'CONTENT_META_KEYWORDS' => $content_1_data['content_meta_keywords']);
                                    }
                                    for ($a = 0, $x = sizeof($content_1); $a < $x; $a++) {
                                      if ($content_1[$a]!='') {
                                        $file_flag_sql = xtc_db_query("SELECT file_flag_name FROM " . TABLE_CM_FILE_FLAGS . " WHERE file_flag=" . $content_1[$a]['FILE_FLAG']);
                                        $file_flag_result = xtc_db_fetch_array($file_flag_sql);
                                        echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\'" onmouseout="this.className=\'dataTableRow\'">' . "\n";

                                          if ($content_1[$a]['CONTENT_FILE']=='') $content_1[$a]['CONTENT_FILE']='database';
                                            ?>
                                            <td class="dataTableContent" align="left"><?php echo $content_1[$a]['CONTENT_ID']; ?></td>
                                            <td class="dataTableContent" align="left">--<?php echo $content_1[$a]['CONTENT_TITLE']; ?></td>
                                            <td class="dataTableContent" align="left"><?php echo $content_1[$a]['CONTENT_FILE']; ?></td>
                                            <td class="dataTableContent" align="middle"><?php if ($content_1[$a]['CONTENT_STATUS']==0) { echo TEXT_NO; } else { echo TEXT_YES; } ?></td>
                                            <td class="dataTableContent" align="middle"><?php echo $file_flag_result['file_flag_name']; ?></td>
                                            <td class="dataTableContent" align="right">
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
                        } else {
                          switch ($action) {
                            // Diplay Editmask
                            case 'new':
                            case 'edit':
                              if ($action != 'new') {
                                $content_query=xtc_db_query("SELECT
                                                                    content_id,
                                                                    categories_id,
                                                                    parent_id,
                                                                    group_ids,
                                                                    languages_id,
                                                                    content_title,
                                                                    content_heading,
                                                                    content_text,
                                                                    sort_order,
                                                                    file_flag,
                                                                    content_file,
                                                                    content_status,
                                                                    content_group,
                                                                    content_delete,
                                                                    content_meta_title,
                                                                    content_meta_description,
                                                                    content_meta_keywords
                                                               FROM ".TABLE_CONTENT_MANAGER."
                                                              WHERE content_id='".$g_coID."'");
                                $content=xtc_db_fetch_array($content_query);
                              }
                              $languages_array = array();
                              for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                                if ($languages[$i]['id']==$content['languages_id']) {
                                  $languages_selected=$languages[$i]['code'];
                                  $languages_id=$languages[$i]['id'];
                                }
                                $languages_array[] = array('id' => $languages[$i]['code'],
                                                         'text' => $languages[$i]['name']);
                              } // for
                              $query_string = '';
                              if (!empty($languages_id)) {
                                $query_string='languages_id='.$languages_id.' AND';
                              }
                              $categories_query=xtc_db_query("SELECT
                                                                     content_id,
                                                                     content_title
                                                                FROM ".TABLE_CONTENT_MANAGER."
                                                               WHERE ".$query_string." parent_id='0'
                                                                 AND content_id!='".$g_coID."'");
                              while ($categories_data=xtc_db_fetch_array($categories_query)) {
                                $categories_array[]=array('id'=>$categories_data['content_id'],
                                                          'text'=>$categories_data['content_title']);
                              }
                              ?>
                              <br /><br />
                              <?php
                                if ($action != 'new') {
                                  echo xtc_draw_form('edit_content',FILENAME_CONTENT_MANAGER,'action=edit&id=update&coID='.$g_coID,'post','enctype="multipart/form-data"').xtc_draw_hidden_field('coID',$g_coID);
                                } else {
                                  echo xtc_draw_form('edit_content',FILENAME_CONTENT_MANAGER,'action=edit&id=insert','post','enctype="multipart/form-data"').xtc_draw_hidden_field('coID',$g_coID);
                                }
                              ?>
                                <table class="main" width="980" border="0">
                                  <tr>
                                    <td width="10%"><?php echo TEXT_LANGUAGE; ?></td>
                                    <td width="90%"><?php echo xtc_draw_pull_down_menu('language',$languages_array,$languages_selected); ?></td>
                                  </tr>
                                  <?php
                                    if ($content['content_delete']!=0 or $action == 'new') {
                                      ?>
                                      <tr>
                                        <td width="10%"><?php echo TEXT_GROUP; ?></td>
                                        <td width="90%"><?php echo xtc_draw_input_field('content_group',isset($content['content_group'])?$content['content_group']:'','size="5"'); ?><?php echo TEXT_GROUP_DESC; ?></td>
                                      </tr>
                                      <?php
                                    } else {
                                      echo xtc_draw_hidden_field('content_group',$content['content_group']);
                                      ?>
                                      <tr>
                                        <td width="10%"><?php echo TEXT_GROUP; ?></td>
                                        <td width="90%"><?php echo $content['content_group']; ?></td>
                                      </tr>
                                      <?php
                                    }
                                    $file_flag_sql = xtc_db_query("SELECT file_flag as id, file_flag_name as text FROM " . TABLE_CM_FILE_FLAGS);
                                    while($file_flag = xtc_db_fetch_array($file_flag_sql)) {
                                      $file_flag_array[] = array('id' => $file_flag['id'], 'text' => $file_flag['text']);
                                    }
                                  ?>
                                  <tr>
                                    <td width="10%"><?php echo TEXT_FILE_FLAG; ?></td>
                                    <td width="90%"><?php echo xtc_draw_pull_down_menu('file_flag',$file_flag_array,$content['file_flag']); ?></td>
                                  </tr>
                                  <?php
                                    /*  build in not completed yet
                                    <tr>
                                      <td width="10%"><?php echo TEXT_PARENT; ?></td>
                                      <td width="90%"><?php echo xtc_draw_pull_down_menu('parent',$categories_array,$content['parent_id']); ?><?php echo xtc_draw_checkbox_field('parent_check', 'yes',false).' '.TEXT_PARENT_DESCRIPTION; ?></td>
                                    </tr>
                                    */
                                  ?>
                                  <tr>
                                    <td width="10%"><?php echo TEXT_SORT_ORDER; ?></td>
                                    <td width="90%"><?php echo xtc_draw_input_field('sort_order',isset($content['sort_order'])?$content['sort_order']:'','size="5"'); ?></td>
                                  </tr>
                                  <tr>
                                    <td valign="top" width="10%"><?php echo TEXT_STATUS; ?></td>
                                    <td width="90%">
                                      <?php
                                        if (isset($content['content_status']) && $content['content_status']=='1') {
                                          echo xtc_draw_checkbox_field('status', 'yes',true).' '.TEXT_STATUS_DESCRIPTION;
                                        } else {
                                          echo xtc_draw_checkbox_field('status', 'yes',false).' '.TEXT_STATUS_DESCRIPTION;
                                        }
                                      ?>
                                      <br /><br />
                                    </td>
                                  </tr>
                                  <?php
                                    if (GROUP_CHECK=='true') {
                                      $customers_statuses_array = xtc_get_customers_statuses();
                                      $customers_statuses_array=array_merge(array(array('id'=>'all','text'=>TXT_ALL)),$customers_statuses_array);
                                      ?>
                                      <tr>
                                        <td valign="top" class="main" ><?php echo ENTRY_CUSTOMERS_STATUS; ?></td>
                                        <td class="main">
                                          <div style="width: 380px; border: 1px solid; border-right: 1px solid; border-color: #ff0000; background:#FFCC33;">
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
                                    <td width="10%"><?php echo TEXT_TITLE; ?></td>
                                    <td width="90%"><?php echo xtc_draw_input_field('cont_title',isset($content['content_title'])?$content['content_title']:'','size="60"'); ?></td>
                                  </tr>
                                  <tr>
                                    <td width="10%"><?php echo TEXT_HEADING; ?></td>
                                    <td width="90%"><?php echo xtc_draw_input_field('cont_heading',isset($content['content_heading'])?$content['content_heading']:'','size="60"'); ?></td>
                                  </tr>
                                  <tr>
                                    <td width="10%"><?php echo 'Meta Title'; ?></td>
                                    <td width="90%"><?php echo xtc_draw_input_field('cont_meta_title',isset($content['content_meta_title'])?$content['content_meta_title']:'','size="60"'); ?></td>
                                  </tr>
                                  <tr>
                                    <td width="10%"><?php echo 'Meta Description'; ?></td>
                                    <td width="90%"><?php echo xtc_draw_input_field('cont_meta_description',isset($content['content_meta_description'])?$content['content_meta_description']:'','size="60"'); ?></td>
                                  </tr>
                                  <tr>
                                    <td width="10%"><?php echo 'Meta Keywords'; ?></td>
                                    <td width="90%"><?php echo xtc_draw_input_field('cont_meta_keywords',isset($content['content_meta_keywords'])?$content['content_meta_keywords']:'','size="60"'); ?></td>
                                  </tr>
                                  <tr>
                                    <td width="10%" valign="top"><?php echo TEXT_UPLOAD_FILE; ?></td>
                                    <td width="90%"><?php echo xtc_draw_file_field('file_upload').' '.TEXT_UPLOAD_FILE_LOCAL; ?></td>
                                  </tr>
                                  <tr>
                                    <td width="10%" valign="top"><?php echo TEXT_CHOOSE_FILE; ?></td>
                                    <td width="90%">
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
                                        echo '<br />'.TEXT_CHOOSE_FILE_SERVER.'</br>';
                                        echo xtc_draw_pull_down_menu('select_file',$files,$default_value);
                                        if (!empty($content['content_file'])) {
                                          echo TEXT_CURRENT_FILE.' <b>'.$content['content_file'].'</b><br />';
                                        }
                                      ?>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td width="10%" valign="top"></td>
                                    <td colspan="90%" valign="top"><br /><?php echo TEXT_FILE_DESCRIPTION; ?></td>
                                  </tr>
                                  <tr>
                                    <td width="10%" valign="top"><?php echo TEXT_CONTENT; ?></td>
                                    <td width="90%">
                                      <?php
                                        echo xtc_draw_textarea_field('cont','','100%','35',isset($content['content_text'])?$content['content_text']:'');
                                      ?>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td colspan="2" align="right" class="main"><?php echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SAVE . '"/>'; ?><a class="button" onclick="this.blur();" href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER); ?>"><?php echo BUTTON_BACK; ?></a></td>
                                  </tr>
                                </table>
                              </form>
                              <?php
                              break;
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

                              if ($action !='new_products_content') {
                                echo xtc_draw_form('edit_content',FILENAME_CONTENT_MANAGER,'action=edit_products_content&id=update_product&coID='.$g_coID,'post','enctype="multipart/form-data"').xtc_draw_hidden_field('coID',$g_coID);
                              } else {
                                echo xtc_draw_form('edit_content',FILENAME_CONTENT_MANAGER,'action=edit_products_content&id=insert_product','post','enctype="multipart/form-data"');
                              }
                                ?>
                                <div class="main"><?php echo TEXT_CONTENT_DESCRIPTION; ?></div>
                                <table class="main" width="980" border="0">
                                  <tr>
                                    <td width="10%"><?php echo TEXT_PRODUCT; ?></td>
                                    <td width="90%"><?php echo xtc_draw_pull_down_menu('product',$products_array,$content['products_id']); ?></td>
                                  </tr>
                                  <tr>
                                    <td width="10%"><?php echo TEXT_LANGUAGE; ?></td>
                                    <td width="90%"><?php echo xtc_draw_pull_down_menu('language',$languages_array,$languages_selected); ?></td>
                                  </tr>
                                  <?php
                                    if (GROUP_CHECK=='true') {
                                      $customers_statuses_array = xtc_get_customers_statuses();
                                      $customers_statuses_array=array_merge(array(array('id'=>'all','text'=>TXT_ALL)),$customers_statuses_array);
                                      ?>
                                        <td class="main">
                                          <div style="width: 380px; border: 1px solid; border-right: 1px solid; border-color: #ff0000; background:#FFCC33;">
                                            <?php
                                              for ($i=0;$n=sizeof($customers_statuses_array),$i<$n;$i++) {
                                                if (strstr($content['group_ids'],'c_'.$customers_statuses_array[$i]['id'].'_group')) {
                                                  $checked = 'checked ';
                                                } else {
                                                  $checked = '';
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
                                    <td width="10%"><?php echo TEXT_TITLE_FILE; ?></td>
                                    <td width="90%"><?php echo xtc_draw_input_field('cont_title',$content['content_name'],'size="60"'); ?></td>
                                  </tr>
                                  <tr>
                                    <td width="10%"><?php echo TEXT_LINK; ?></td>
                                    <td width="90%"><?php echo xtc_draw_input_field('cont_link',$content['content_link'],'size="60"'); ?></td>
                                  </tr>
                                  <tr>
                                    <td width="10%" valign="top"><?php echo TEXT_FILE_DESC; ?></td>
                                    <td width="90%"><?php echo xtc_draw_textarea_field('file_comment','','100','30',$content['file_comment']); ?></td>
                                  </tr>
                                  <tr>
                                    <td width="10%"><?php echo TEXT_CHOOSE_FILE; ?></td>
                                    <td width="90%"><?php echo xtc_draw_pull_down_menu('select_file',$content_files,$default_value); ?><?php echo ' '.TEXT_CHOOSE_FILE_DESC; ?></td>
                                  </tr>
                                  <tr>
                                    <td width="10%" valign="top"><?php echo TEXT_UPLOAD_FILE; ?></td>
                                    <td width="90%"><?php echo xtc_draw_file_field('file_upload').' '.TEXT_UPLOAD_FILE_LOCAL; ?></td>
                                  </tr>
                                  <?php
                                    if ($content['content_file']!='') {
                                      ?>
                                      <tr>
                                        <td width="10%"><?php echo TEXT_FILENAME; ?></td>
                                        <td width="90%" valign="top"><?php echo xtc_draw_hidden_field('file_name',$content['content_file']).xtc_image('../'. DIR_WS_IMAGES. 'icons/icon_'.str_replace('.','',strstr($content['content_file'],'.')).'.gif').$content['content_file']; //DokuMan - 2011-09-06 - change path ?></td>
                                      </tr>
                                      <?php
                                    }
                                  ?>
                                  <tr>
                                    <td colspan="2" align="right" class="main"><?php echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SAVE . '"/>'; ?><a class="button" onclick="this.blur();" href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER); ?>"><?php echo BUTTON_BACK; ?></a></td>
                                  </tr>
                                </table>
                              </form>
                              <?php
                              break;
                          }
                        }
                        if (!$action) {
                          ?>
                          <br/>
                          <a class="button" onclick="this.blur();" href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER,'action=new'); ?>"><?php echo BUTTON_NEW_CONTENT; ?></a>
                          <?php
                        }
                      ?>
                    </td>
                  </tr>
                </table>
                <?php
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
                    <div class="pageHeading"><br /><?php echo HEADING_PRODUCTS_CONTENT; ?><br /></div>
                    <?php
                      $total_space_media_products = xtc_spaceUsed(DIR_FS_CATALOG.'media/products/'); // DokuMan - 2011-09-06 - sum up correct filesize avoiding global variable
                      echo '<div class="main">'.USED_SPACE.xtc_format_filesize($total_space_media_products).'</div></br>';
                    ?>
                    <table border="0" width="100%" cellspacing="0" cellpadding="2">
                      <tr class="dataTableHeadingRow">
                        <td class="dataTableHeadingContent" nowrap width="5%" ><?php echo TABLE_HEADING_PRODUCTS_ID; ?></td>
                        <td class="dataTableHeadingContent" width="95%" align="left"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
                      </tr>
                      <?php
                        for ($i=0,$n=sizeof($products_ids); $i<$n; $i++) {
                          echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\'" onmouseout="this.className=\'dataTableRow\'">' . "\n";
                            ?>
                            <td class="dataTableContent_products" align="left"><?php echo $products_ids[$i]['id']; ?></td>
                            <td class="dataTableContent_products" align="left"><b><?php echo xtc_image(DIR_WS_CATALOG.'images/icons/arrow.gif'); ?><a href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER,'pID='.$products_ids[$i]['id']);?>"><?php echo $products_ids[$i]['name']; ?></a></b></td>
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
                            while ($content_data=xtc_db_fetch_array($content_query)) {
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
                                <td class="dataTableContent" align="left"></td>
                                <td class="dataTableContent" align="left">
                                  <table border="0" width="100%" cellspacing="0" cellpadding="2">
                                    <tr class="dataTableHeadingRow">
                                      <td class="dataTableHeadingContent" nowrap width="2%" ><?php echo TABLE_HEADING_PRODUCTS_CONTENT_ID; ?></td>
                                      <td class="dataTableHeadingContent" nowrap width="2%" >&nbsp;</td>
                                      <td class="dataTableHeadingContent" nowrap width="5%" ><?php echo TABLE_HEADING_LANGUAGE; ?></td>
                                      <td class="dataTableHeadingContent" nowrap width="15%" ><?php echo TABLE_HEADING_CONTENT_NAME; ?></td>
                                      <td class="dataTableHeadingContent" nowrap width="30%" ><?php echo TABLE_HEADING_CONTENT_FILE; ?></td>
                                      <td class="dataTableHeadingContent" nowrap width="1%" ><?php echo TABLE_HEADING_CONTENT_FILESIZE; ?></td>
                                      <td class="dataTableHeadingContent" nowrap align="middle" width="20%" ><?php echo TABLE_HEADING_CONTENT_LINK; ?></td>
                                      <td class="dataTableHeadingContent" nowrap width="5%" ><?php echo TABLE_HEADING_CONTENT_HITS; ?></td>
                                      <td class="dataTableHeadingContent" nowrap width="20%" ><?php echo TABLE_HEADING_CONTENT_ACTION; ?></td>
                                    </tr>
                                    <?php
                                    for ($ii=0,$nn=sizeof($content_array); $ii<$nn; $ii++) {
                                      echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\'" onmouseout="this.className=\'dataTableRow\'">' . "\n";
                                      ?>
                                        <td class="dataTableContent" align="left"><?php echo  $content_array[$ii]['id']; ?> </td>
                                        <td class="dataTableContent" align="left">
                                          <?php
                                            if ($content_array[$ii]['file']!='') {
                                              echo xtc_image('../'. DIR_WS_IMAGES.'icons/icon_'.str_replace('.','',strstr($content_array[$ii]['file'],'.')).'.gif'); //web28 - 2010-09-03 - change path
                                            } else {
                                              echo xtc_image('../'. DIR_WS_IMAGES.'icons/icon_link.gif'); //web28 - 2010-09-03 - change path
                                            }
                                            for ($xx=0,$zz=sizeof($languages); $xx<$zz;$xx++){
                                              if ($languages[$xx]['id']==$content_array[$ii]['languages_id']) {
                                                $lang_dir=$languages[$xx]['directory'];
                                                break;
                                              }
                                            }
                                          ?>
                                        </td>
                                        <td class="dataTableContent" align="left"><?php echo xtc_image(DIR_WS_CATALOG.'lang/'.$lang_dir.'/admin/images/icon.gif'); ?></td>
                                        <td class="dataTableContent" align="left"><?php echo $content_array[$ii]['name']; ?></td>
                                        <td class="dataTableContent" align="left"><?php echo $content_array[$ii]['file']; ?></td>
                                        <td class="dataTableContent" align="left"><?php echo xtc_filesize($content_array[$ii]['file']); ?></td>
                                        <td class="dataTableContent" align="left" align="middle">
                                          <?php
                                            if ($content_array[$ii]['link']!='') {
                                              echo '<a href="'.$content_array[$ii]['link'].'" target="new">'.$content_array[$ii]['link'].'</a>';
                                            }
                                          ?>
                                          &nbsp;
                                        </td>
                                        <td class="dataTableContent" align="left"><?php echo $content_array[$ii]['read']; ?></td>
                                        <td class="dataTableContent" align="left">
                                          <a href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER,'special=delete_product&coID='.$content_array[$ii]['id']).'&pID='.$products_ids[$i]['id']; ?>" onclick="return confirm('<?php echo CONFIRM_DELETE; ?>')">
                                          <?php
                                            echo xtc_image(DIR_WS_ICONS.'delete.gif', ICON_DELETE,'','','style="cursor:pointer" onclick="return confirm(\''.DELETE_ENTRY.'\')"').'  '.TEXT_DELETE.'</a>&nbsp;&nbsp;';
                                          ?>
                                          <a href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER,'action=edit_products_content&coID='.$content_array[$ii]['id']); ?>">
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
                    <a class="button" onclick="this.blur();" href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER,'action=new_products_content'); ?>"><?php echo BUTTON_NEW_CONTENT; ?></a>
                    <?php
                  } // if !$action
                ?>
              </td>
            </tr>
          </table>
        </td>
        <!-- body_text_eof //-->
      </tr>
    </table>
    </div>
    <!-- body_eof //-->
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
