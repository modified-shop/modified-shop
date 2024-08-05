<?php
  /* --------------------------------------------------------------
   $Id$

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
  require_once(DIR_FS_INC . 'xtc_href_link_from_admin.inc.php');

  if(!defined('CONTENT_CHILDS_ACTIV')) {
    define('CONTENT_CHILDS_ACTIV','true');
  }
  
  //display per page
  $cfg_max_display_results_key = 'MAX_DISPLAY_CONTENT_MANAGER_RESULTS';
  $page_max_display_results = xtc_cfg_save_max_display_results($cfg_max_display_results_key);

  $page = (isset($_GET['page']) ? (int)$_GET['page'] : 1);
  $set = (isset($_GET['set']) ? $_GET['set'] : '');
  $setparam = !empty($set) ? '&set='.$set : '';
  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  $special = (isset($_GET['special']) ? $_GET['special'] : '');
  $id = (isset($_GET['id']) ? $_GET['id'] : '');
  $g_coID = (isset($_GET['coID']) ? (int)$_GET['coID'] : '');
  $coIndex = (isset($_GET['coIndex']) ? (int)$_GET['coIndex'] : '');
  $languages = xtc_get_languages();
  $icon_padding = 'style="padding-right:8px;"';  
  
  if ($special != '') {
    switch ($special) {
      case 'delete':
        $params = '';
        xtc_db_query("DELETE FROM ".TABLE_CONTENT_MANAGER." WHERE content_group='".$g_coID."' AND content_group_index='".$coIndex."'");
        break;
    
      case 'delete_product':
        $params = 'pID='.(int)$_GET['pID'];
        xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_CONTENT." where content_id='".$g_coID."'");
        break;

      case 'delete_content':
        $params = 'cID='.(int)$_GET['cID'];
        xtc_db_query("DELETE FROM ".TABLE_CONTENT_MANAGER_CONTENT." where content_id='".$g_coID."'");
        break;

      case 'delete_email':
        $params = 'eID='.$_GET['eID'];
        xtc_db_query("DELETE FROM ".TABLE_EMAIL_CONTENT." where content_id='".$g_coID."'");
        break;

      case 'file_flag':
        $params = '';
        $_SESSION['file_flag'] = $_GET['file_flag'];
        break;
      case 'setsflag':
        $params = xtc_get_all_get_params(array('special', 'flag'));
        xtc_db_query("UPDATE ".TABLE_CONTENT_MANAGER." 
                         SET content_status = '".(int)$_GET['flag']."'
                       WHERE content_group = '".$g_coID."' 
                         AND content_group_index = '".$coIndex."'");
        break;
        
      case 'setaflag':
        $params = xtc_get_all_get_params(array('special', 'flag'));
        xtc_db_query("UPDATE ".TABLE_CONTENT_MANAGER." 
                         SET content_active = '".(int)$_GET['flag']."'
                       WHERE content_group = '".$g_coID."' 
                         AND content_group_index = '".$coIndex."'");
        break;
    }
    
    foreach(auto_include(DIR_FS_ADMIN.'includes/extra/modules/content_manager/action/','php') as $file) require ($file);

    if (isset($_GET['cPath'])) {
      xtc_redirect(xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('special', 'last_action', 'action', 'coID', 'coIndex')) . 'action='.$_GET['last_action']));
    } else {
      xtc_redirect(xtc_href_link(FILENAME_CONTENT_MANAGER, $params.$setparam));
    }
  }
  
  if (empty($action) && isset($_GET['cPath'])) {
    xtc_redirect(xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('special', 'last_action', 'action', 'coID', 'coIndex', 'search')) . 'action='.$_GET['last_action']));
  }
  
  if ($id == 'update' || $id == 'insert') {    
    foreach ($_POST as $key => $value) {
      if (!isset(${$key}) || !is_object(${$key})) {
        if (is_array($value)) {
          ${$key} = array_map('xtc_db_prepare_input', $value);
        } else {
          ${$key} = xtc_db_prepare_input($value);
        }
      }
    }

    $content_meta_robots = implode(', ', ((isset($content_meta_robots) && is_array($content_meta_robots)) ? $content_meta_robots : array()));    
    if (isset($parent_check) && $parent_check == 'yes') {                                     
      $parent_query = xtc_db_query("SELECT c2.content_id,
                                           c2.languages_id
                                      FROM ".TABLE_CONTENT_MANAGER." c1
                                      JOIN ".TABLE_CONTENT_MANAGER." c2
                                           ON c1.content_group = c2.content_group
                                     WHERE c1.content_id = '".(int)$parent_id."'
                                     ");
      $parent_id = array();
      while ($parent = xtc_db_fetch_array($parent_query)) {
        $parent_id[$parent['languages_id']] = $parent['content_id'];
        
        if ($parent['languages_id'] == $_SESSION['languages_id']) {
          $_GET['pID'] = $parent['content_id'];
        }
      }
    }
    
    if (isset($_GET['pID']) 
        && (!isset($parent_id) || !is_array($parent_id))
        || (isset($parent_id) 
            && is_array($parent_id) 
            && !array_key_exists($_SESSION['languages_id'], $parent_id)
            )
        )
    {
      unset($_GET['pID']);
    }
    
    if ($content_group == '0' || $content_group == '') {
      $content_query = xtc_db_query("SELECT MAX(content_group) AS content_group FROM ".TABLE_CONTENT_MANAGER);
      $content_data = xtc_db_fetch_row($content_query);
      $content_group = $content_data[0] + 1;
    }

    $sql_data_array = array(
      'content_group' => (int)$content_group,
      'sort_order' => $sort_order,
      'file_flag' => $file_flag,
      'content_meta_robots' => $content_meta_robots,
    );

    
    for ($i=0; $i<$content_count; $i++) {
      for ($l=0, $ln=count($languages); $l<$ln; $l++) {
        $error = false;
        /*
        if (strlen($content_title[$i][$languages[$l]['id']]) < 1) {
          $error = true;
          $messageStack->add_session(strtoupper($languages[$l]['name']).': '.ERROR_TITLE, 'error');
        }
        */
        if ($error === false) {
          $content_file_name = '';
          if ($select_file[$i][$languages[$l]['id']] != 'default') {
            $content_file_name = $select_file[$i][$languages[$l]['id']];
          }
          $accepted_file_upload_files_extensions = array("htm","html","txt");
          $accepted_file_upload_files_mime_types = array("text/html","text/html","text/plain");
          if ($content_file = xtc_try_upload('file_upload_'.$i.'_'.$languages[$l]['id'], DIR_FS_CATALOG.'media/content/', '644', $accepted_file_upload_files_extensions, $accepted_file_upload_files_mime_types)) {
            $content_file_name = $content_file->filename;
          }

          // set allowed c.groups
          $group_ids = '';
          if (isset($groups[$i][$languages[$l]['id']])) {
            foreach($groups[$i][$languages[$l]['id']] as $b) {
              $group_ids .= 'c_'.$b."_group,";
            }
          }
          $customers_statuses_array = xtc_get_customers_statuses();
          if (strpos($group_ids,'c_all_group') !== false) {
            $group_ids = '';
            for ($g=0, $x=count($customers_statuses_array); $g<$x; $g++) {
              $group_ids .= 'c_'.$customers_statuses_array[$g]['id'].'_group,';
            }
          }

          $sql_data_lang_array = array(
            'content_status' => (int)$content_status[$i][$languages[$l]['id']],
            'content_active' => (int)$content_active[$i][$languages[$l]['id']],
            'languages_id' => $languages[$l]['id'],
            'parent_id' => ((isset($parent_id) && is_array($parent_id) && array_key_exists($languages[$l]['id'], $parent_id)) ? $parent_id[$languages[$l]['id']] : 0),
            'group_ids' => $group_ids,
            'content_title' => $content_title[$i][$languages[$l]['id']],
            'content_heading' => $content_heading[$i][$languages[$l]['id']],
            'content_text' => $content_text[$i][$languages[$l]['id']],
            'content_meta_title' => $content_meta_title[$i][$languages[$l]['id']],
            'content_meta_description' => $content_meta_description[$i][$languages[$l]['id']],
            'content_meta_keywords' => $content_meta_keywords[$i][$languages[$l]['id']],
            'content_file' => $content_file_name
          );
        
          // check content_group_index 
          $add_and = '';          
          if ($id == 'update' && $content_id[$i][$languages[$l]['id']] > 0) {
            $add_and = " AND content_id != '" . $content_id[$i][$languages[$l]['id']] ."'";
          }          
          $dbQuery = xtc_db_query("SELECT MAX(content_group_index)
                                     FROM ".TABLE_CONTENT_MANAGER."
                                    WHERE languages_id ='" . $sql_data_lang_array['languages_id'] . "'
                                          ".$add_and."
                                      AND content_group ='" . $sql_data_array['content_group'] . "'");
                                                     
          //check change content_group
          $change_content_group = (isset($coID) && $coID != $content_group) ? true : false;    
          $dbData = xtc_db_fetch_row($dbQuery);
          if (!is_null($dbData[0])) { 
            $sql_data_array['content_group_index'] = $dbData[0] + 1;
            if ($id == 'update' && !  $change_content_group) {
              $sql_data_array['content_group_index'] = $content_group_index;
            }
            $content_group_index = $sql_data_array['content_group_index'];
          } else {
            $sql_data_array['content_group_index'] = 0;
          }
          
          if (isset($content_new_group_index[$i][$languages[$l]['id']])) {
            $sql_data_array['content_group_index'] = (int)$content_new_group_index[$i][$languages[$l]['id']];
          }
          
          if ($id == 'update' && $content_id[$i][$languages[$l]['id']] > 0) {
            $sql_data_array['last_modified'] = 'now()';
            xtc_db_perform(TABLE_CONTENT_MANAGER, array_merge($sql_data_array, $sql_data_lang_array), 'update', "content_id = '".$content_id[$i][$languages[$l]['id']]."'");
          } else {
            $sql_data_array['date_added'] = 'now()';
            xtc_db_perform(TABLE_CONTENT_MANAGER, array_merge($sql_data_array, $sql_data_lang_array));
          }
        }
      }
    }

    foreach(auto_include(DIR_FS_ADMIN.'includes/extra/modules/content_manager/action/','php') as $file) require ($file);
    
    if (isset($page_update)) {
      $_GET['coID'] = $content_group;
      $_GET['coIndex'] = $sql_data_array['content_group_index'];
      $setparam = 'action=edit';
    }
    if ($error === true) {
      $_GET['coID'] = (($g_coID != '') ? $g_coID : $content_group);
      $setparam = 'action=edit';
    }

    xtc_redirect(xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'id')).$setparam));
  }

  
  $action_id = array(
    'update_products',
    'insert_products',
    'update_content_manager',
    'insert_content_manager',
    'update_email',
    'insert_email',
  );
  
  if (in_array($id, $action_id)) {
    $action_array = explode('_', $id);
    $subaction = array_shift($action_array);
    
    $type = $path = implode('_', $action_array);
    if ($type == 'content_manager' || $type == 'email') {
      $path = 'content';
    }
    $table = constant('TABLE_'.strtoupper($type).'_CONTENT');
        
    // set allowed c.groups
    $group_ids = '';
    if(isset($_POST['groups']) && is_array($_POST['groups']))  {
      foreach($_POST['groups'] as $b){
        $group_ids .= 'c_'.$b."_group,";
      }
    }
    $customers_statuses_array=xtc_get_customers_statuses();
    if (strpos($group_ids,'c_all_group') !== false) {
      $group_ids = '';
      for ($i=0;$n=sizeof($customers_statuses_array),$i<$n;$i++) {
        $group_ids .= 'c_'.$customers_statuses_array[$i]['id'].'_group,';
     }
    }

    $content_title = xtc_db_prepare_input($_POST['cont_title']);
    $content_link = ((isset($_POST['cont_link'])) ? xtc_db_prepare_input($_POST['cont_link']) : '');
    $content_language_code = xtc_db_prepare_input($_POST['language_code']);
    $product = xtc_db_prepare_input($_POST['product']);
    $file_comment = ((isset($_POST['file_comment'])) ? xtc_db_prepare_input($_POST['file_comment']) : '');
    $select_file = xtc_db_prepare_input($_POST['select_file']);
    $filename = ((isset($_POST['file_name'])) ? xtc_db_prepare_input($_POST['file_name']) : '');
    $sort_order = xtc_db_prepare_input($_POST['sort_order']);

    $error = false;
    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
      if ($languages[$i]['code'] == $content_language_code) {
        $content_language_id = $languages[$i]['id'];
      }
    }

    if (strlen($content_title) < 1) {
      $error = true;
      $messageStack->add(ERROR_TITLE,'error');
    }

    if ($error == false) {
      if ($select_file=='default') {
        require(DIR_WS_INCLUDES.'upload_types.php');

        if ($content_file = xtc_try_upload('file_upload', DIR_FS_CATALOG.'media/'.$path.'/', '644', array_merge($accepted_image_extensions, $accepted_file_extensions, $accepted_extfile_extensions, $accepted_audio_extensions, $accepted_movie_extensions, $accepted_compressed_extensions), array_merge($accepted_image_mime_types, $accepted_file_mime_types, $accepted_extfile_mime_types, $accepted_audio_mime_types, $accepted_movie_mime_types, $accepted_compressed_mime_types))) {
          $content_file_name = $content_file->filename;
          if ($_POST['keep_filename'] != '1') {
            $old_filename = $content_file_name;
            $timestamp = str_replace('.','',microtime());
            $timestamp = str_replace(' ','',$timestamp);
            $content_file_name = $timestamp.strstr($content_file_name,'.');
            rename(DIR_FS_CATALOG.'media/'.$path.'/'.$old_filename, DIR_FS_CATALOG.'media/'.$path.'/'.$content_file_name);
          }
          copy(DIR_FS_CATALOG.'media/'.$path.'/'.$content_file_name, DIR_FS_CATALOG.'media/'.$path.'/backup/'.$content_file_name);
        }
        if (!isset($content_file_name) || $content_file_name == '') {
          $content_file_name = $filename;
        }
      } else {
        $content_file_name = $select_file;
      }

      $sql_data_array = array(
        $type.'_id' => $product,
        'group_ids' => $group_ids,
        'content_name' => $content_title,
        'content_file' => $content_file_name,
        'content_link' => $content_link,
        'file_comment' => $file_comment,
        'languages_id' => $content_language_id,
        'sort_order' => $sort_order,
      );

      if ($subaction == 'update') {
        $coID = xtc_db_prepare_input($_POST['coID']);
        xtc_db_perform($table, $sql_data_array, 'update', "content_id = '" . $coID . "'");
      } else {
        xtc_db_perform($table, $sql_data_array);
        $_GET[$type[0].'ID'] = $product;
        $_GET['coID'] = xtc_db_insert_id();
      }

      foreach(auto_include(DIR_FS_ADMIN.'includes/extra/modules/content_manager/action/','php') as $file) require ($file);

      if (isset($_GET['cPath'])) {
        xtc_redirect(xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('last_action', 'action', 'id', 'coID')) . 'action='.$_GET['last_action']));
      } else {
        xtc_redirect(xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action', 'id', 'last_action')).((isset($_GET['last_action'])) ? 'action='.$_GET['last_action'] : '')));
      }
    }
  }

  function check_content_childs($content_id,$languages_id) {    
    $contents_query = xtc_db_query("SELECT parent_id                              
                                      FROM " . TABLE_CONTENT_MANAGER . "
                                     WHERE parent_id = '" . (int) $content_id . "'
                                       AND languages_id = '" . (int)$languages_id . "'");
    if (xtc_db_num_rows($contents_query) > 0) {
      return true;
    }
    return false;
  }

  function get_file_flag($content_id) {
    $contents_query = xtc_db_query("SELECT file_flag                              
                                      FROM " . TABLE_CONTENT_MANAGER . "
                                     WHERE content_id = '" . (int)$content_id . "'");
    $contents = xtc_db_fetch_array($contents_query);
    return $contents['file_flag'];
  }
  
  require (DIR_WS_INCLUDES.'head.php');

  if (USE_WYSIWYG=='true') {
    echo PHP_EOL . (!function_exists('editorJSLink') ? '<script type="text/javascript" src="includes/modules/fckeditor/fckeditor.js"></script>' : '') . PHP_EOL;
    if ($set != '') {
      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        echo xtc_wysiwyg('products_content', $_SESSION['language_code'], $languages[$i]['id']);
      }
    }
  }
 
  $content_pages_array = array(
    array('id' => '', 'text' => BOX_PAGES_CONTENT),
    array('id' => 'product', 'text' => BOX_PRODUCTS_CONTENT),
    array('id' => 'content', 'text' => BOX_CONTENT_CONTENT),
    array('id' => 'email', 'text' => BOX_EMAIL_CONTENT),
  );
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
          <?php
            if ($set == '') {
              //content
              include(DIR_WS_MODULES.'content_manager_pages.php');
              $newaction = 'new';
            } elseif ($set == 'product') {
              //products content
              include(DIR_WS_MODULES.'content_manager_products.php');
              $newaction = 'new_products_content';
            } elseif ($set == 'content') {
              //products content
              include(DIR_WS_MODULES.'content_manager_content.php');
              $newaction = 'new_content_manager_content';
            } elseif ($set == 'email') {
              //products content
              include(DIR_WS_MODULES.'content_manager_email.php');
              $newaction = 'new_email_content';
            }
          ?>
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