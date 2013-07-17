<?php
  /* --------------------------------------------------------------
   $Id: content_manager.php 5007 2013-07-04 09:31:37Z web28 $

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

  if(!defined('CONTENT_CHILDS_ACTIV')) {
    define('CONTENT_CHILDS_ACTIV','false');
  }
  
  $set = (isset($_GET['set']) ? $_GET['set'] : '');
  $setparam = !empty($set) ? '&set='.$set : '';
  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  $special = (isset($_GET['special']) ? $_GET['special'] : '');
  $id = (isset($_GET['id']) ? $_GET['id'] : '');
  $g_coID = (isset($_GET['coID']) ? (int)$_GET['coID'] : '');
  $languages = xtc_get_languages();

  if ($special=='delete') {
    xtc_db_query("DELETE FROM ".TABLE_CONTENT_MANAGER." where content_id='".$g_coID."'");
    xtc_redirect(xtc_href_link(FILENAME_CONTENT_MANAGER,$setparam));
  } // if get special

  if ($special=='delete_product') {
    xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_CONTENT." where content_id='".$g_coID."'");
    if (isset($_GET['cPath'])) {
      xtc_redirect(xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('special', 'last_action', 'action', 'coID')) . 'action='.$_GET['last_action']));
    } else {
      xtc_redirect(xtc_href_link(FILENAME_CONTENT_MANAGER,'pID='.(int)$_GET['pID'].$setparam));
    }
  } // if get special
  
  if (empty($action) && isset($_GET['cPath'])) {
    xtc_redirect(xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('special', 'last_action', 'action', 'coID')) . 'action='.$_GET['last_action']));
  }

  if ($id=='update' || $id=='insert') {
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
    $content_noindex = isset($_POST['noindex']) ? xtc_db_prepare_input($_POST['noindex']) : '';
    
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

    $content_status = $content_status == 'yes' ? 1 : 0;

    $content_noindex = $content_noindex == 'yes' ? 1 : 0;
    
    $parent_id = $parent_check=='yes' ? $parent_id: '0';

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
                            'content_meta_keywords' => $content_meta_keywords,
                            'content_noindex' => $content_noindex
                            );
      if ($id=='update') {
        xtc_db_perform(TABLE_CONTENT_MANAGER, $sql_data_array, 'update', "content_id = '" . $coID . "'");
      } else {
        xtc_db_perform(TABLE_CONTENT_MANAGER, $sql_data_array);
      } // if get id
      xtc_redirect(xtc_href_link(FILENAME_CONTENT_MANAGER,$setparam));
    } // if error
  } // if

  if ($id=='update_product' || $id=='insert_product') {
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
      if (isset($_GET['cPath'])) {
        xtc_redirect(xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('last_action', 'action', 'id', 'coID')) . 'action='.$_GET['last_action']));
      } else {
        xtc_redirect(xtc_href_link(FILENAME_CONTENT_MANAGER,'pID='.$product.$setparam));
      }
    }// if error
  }

  function check_content_childs($content_id,$languages_id) {    
    $contents_query = xtc_db_query("SELECT parent_id                              
                                      FROM " . TABLE_CONTENT_MANAGER . "
                                     WHERE parent_id = '" . (int) $content_id . "'
                                       AND languages_id = '" . (int)$languages_id . "'
                                   ");
    if (xtc_db_num_rows($contents_query) > 0) {
      return true;
    }
    return false;
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
    <table class="tableBody">
      <tr>

        <?php //left_navigation
        if (USE_ADMIN_TOP_MENU == 'false') {
          echo '<td class="columnLeft2">'.PHP_EOL;
          echo '<!-- left_navigation //-->'.PHP_EOL;       
          require_once(DIR_WS_INCLUDES . 'column_left.php');
          echo '<!-- left_navigation eof //-->'.PHP_EOL; 

          echo '</td>'.PHP_EOL;      
        }
        ?>
        <!-- body_text //--> 
        <td class="boxCenter"> 
          <div class="content-manager-width">                  
            <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_content.png'); ?></div>
            <div class="pageHeading pdg2"><?php echo HEADING_TITLE;?></div>          
            <div class="main pdg2">Tools</div>
            <div class="clear"></div>
            <div style="border-top: 3px solid; border-color: #cccccc;"></div>
              <?php
                if ($set != 'product') {
                  //content
                  include(DIR_WS_MODULES.'content_manager_pages.php');
                  $newaction = 'new';
                } else {
                  //products content
                  include(DIR_WS_MODULES.'content_manager_products.php');
                  $newaction = 'new_products_content';
                }
              ?>
              <?php                        
              if (!$action) {
                ?>
                <br/>
                <div><a class="button" onclick="this.blur();" href="<?php echo xtc_href_link(FILENAME_CONTENT_MANAGER,'action='.$newaction.$setparam); ?>"><?php echo BUTTON_NEW_CONTENT; ?></a></div>
                <?php
              }
              ?>
           </div>
        </td>
        <!-- body_text_eof //-->
      </tr>
    </table>   
    <!-- body_eof //-->
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
