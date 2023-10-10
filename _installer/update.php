<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  

  require_once ('includes/application_top.php');

  // Database
  $db_type = get_mysql_type();
  require_once (DIR_FS_INC.'db_functions_'.$db_type.'.inc.php');
  require_once (DIR_FS_INC.'db_functions.inc.php');

  // include needed classes
  require_once (DIR_WS_CLASSES.'modified_api.php');

  // make a connection to the database... now
  xtc_db_connect() or die('Unable to connect to database server!');

  // load configuration
  $configuration_query = xtc_db_query('SELECT configuration_key, configuration_value FROM '.TABLE_CONFIGURATION);
  while ($configuration = xtc_db_fetch_array($configuration_query)) {
    defined($configuration['configuration_key']) OR define($configuration['configuration_key'], stripslashes($configuration['configuration_value']));
  }

  // language
  require_once(DIR_FS_INSTALLER.'lang/'.$_SESSION['language'].'.php');
 
  if (!isset($_SESSION['visited'])) $_SESSION['visited'] = array();
  
  // smarty
  $smarty = new Smarty();
  $smarty->setTemplateDir(__DIR__.'/templates')
         ->registerResource('file', new EvaledFileResource())
         ->setConfigDir(__DIR__.'/lang')
         ->SetCaching(0);

  $smarty->assign('LINK_DB_BACKUP', xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=db_backup', $request_type));
  $smarty->assign('LINK_DB_RESTORE', xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=db_restore', $request_type));
  $smarty->assign('LINK_SQL_MANUELL', xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=sql_manuell', $request_type));
  $smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(DIR_WS_INSTALLER.'index.php', '', $request_type).'">'.BUTTON_BACK.'</a>');
  
  $modulelist = array(
    array(
      'NAME' => TEXT_DELETE_FILES,
      'LINK' => xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=delete_files', $request_type),
      'BUTTON' => BUTTON_DELETE_FILES,
      'VISITED' => in_array('delete_files', $_SESSION['visited']),
    ),
    array(
      'NAME' => TEXT_UPDATE_CONFIG,
      'LINK' => xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=configure', $request_type),
      'BUTTON' => BUTTON_CONFIGURE,
      'VISITED' => in_array('configure', $_SESSION['visited']),
    ),
    array(
      'NAME' => TEXT_UPDATE_SYSTEM,
      'LINK' => xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=system_updates', $request_type),
      'BUTTON' => BUTTON_SYSTEM_UPDATES,
      'VISITED' => in_array('system_updates', $_SESSION['visited']),
    ),
    array(
      'NAME' => TEXT_SQL_UPDATE,
      'LINK' => xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=sql_update', $request_type),
      'BUTTON' => BUTTON_SQL_UPDATE,
      'VISITED' => in_array('sql_update', $_SESSION['visited']),
    ),
    array(
      'NAME' => TEXT_DB_UPDATE,
      'LINK' => xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=updatenow', $request_type),
      'BUTTON' => BUTTON_DB_UPDATE,
      'VISITED' => in_array('updatenow', $_SESSION['visited']),
    ),
  );
  $smarty->assign('modulelist', $modulelist);
    
  if (isset($_GET['action'])) {
    switch ($_GET['action']) {
      case 'system_updates':
        $_SESSION['visited']['system_updates'] = 'system_updates';
        include(DIR_FS_INSTALLER.'includes/update_system.php');
        $messageStack->add_session('update', TEXT_UPDATE_SYSTEM_SUCCESS, 'success');
        xtc_redirect(xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), '', $request_type));
        break;
        
      case 'configure':
        include(DIR_FS_INSTALLER.'includes/update_configure.php');
        if ($error === true) {
          $messageStack->add_session('update', TEXT_CONFIGURE_ERROR, 'error');
        } else {
          $_SESSION['visited']['configure'] = 'configure';
          $messageStack->add_session('update', TEXT_CONFIGURE_SUCCESS, 'success');
        }
        xtc_redirect(xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), '', $request_type));
        break;

      case 'delete_files':
        // check for errors
        $error = false;
  
        // set all files to be deleted
        require_once('includes/delete_files.php');

        // set all directories to be deleted
        require_once('includes/delete_dirs.php');
  
        if ($error === true) {
          if (count($unlinked_files['error']['files']) > 0) {
            $output = '<ul>';
            foreach ($unlinked_files['error']['files'] as $files) {
              $output .= '<li>'.$files.'</li>';
            }
            $output .= '</ul>';
            $messageStack->add_session('update', TEXT_DELETE_FILES_ERROR.$output, 'error');
          }
          if (count($unlinked_files['error']['dir']) > 0) {
            $output = '<ul>';
            foreach ($unlinked_files['error']['dir'] as $dir) {
              $output .= '<li>'.$dir.'</li>';
            }
            $output .= '</ul>';
            $messageStack->add_session('update', TEXT_DELETE_DIR_ERROR.$output, 'error');
          }
        } else {
          $_SESSION['visited']['delete_files'] = 'delete_files';
          $messageStack->add_session('update', TEXT_DELETE_FILES_SUCCESS, 'success');
        }
        xtc_redirect(xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), '', $request_type));
        break;
    
      case 'sql_update':
      case 'sql_update_confirm':
      case 'sql_update_process':
        // form
        $smarty->assign('FORM_ACTION', xtc_draw_form('sql_update', xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=sql_update_confirm', $request_type), 'post').xtc_draw_hidden_field('action', 'processnow').xtc_draw_hidden_field(xtc_session_name(), xtc_session_id()));
        $smarty->assign('BUTTON_SUBMIT', '<button type="submit">'.BUTTON_SUBMIT.'</button>');
        $smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), '', $request_type).'">'.BUTTON_BACK.'</a>');
        $smarty->assign('FORM_END', '</form>');

        if ((isset($_POST['action']) && $_POST['action'] == 'processnow') 
            || (isset($_GET['action']) && $_GET['action'] == 'sql_update_process')
            )
        {
          $action = (isset($_GET['action']) ? $_GET['action'] : '');
          if (isset($_POST['action']) && $_POST['action'] == 'processnow') {
            $action = 'processnow';
          }

          if (isset($_POST['sql_files']) && count($_POST['sql_files']) > 0) {
            $sql_data_array = array();
            foreach ($_POST['sql_files'] as $sql_file) {
              if ($sql_file == 'complete.sql' && is_file(DIR_FS_INSTALLER.'update/'.$sql_file)) {
                $sql_data_content = file_get_contents(DIR_FS_INSTALLER.'update/'.$sql_file);
                $sql_data = json_decode($sql_data_content, true);
                $sql_data_array = array_merge($sql_data_array, $sql_data);
              } elseif ($sql_file != 'complete.sql' && is_file(DIR_FS_INSTALLER.'update/'.$sql_file)) {
                $sql_data = sql_update(DIR_FS_INSTALLER.'update/'.$sql_file);
                $sql_data_array = array_merge($sql_data_array, $sql_data);
              }
            }
          }

          include(DIR_FS_INSTALLER.'includes/update_sql.php');
          
          $javascript = '
          <script type="text/javascript">
            var debug = true;
            var button_back = \'<a href="'.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), '', $request_type).'">'.BUTTON_BACK.'</a>\';
            var ajax_url = \''.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=sql_update_process', $request_type).'\';
            var maxReloads = '.UPDATE_MAX_RELOADS.';
          </script>
          ';

          ob_start();
          $process = 'update';
          require(DIR_FS_INSTALLER.'templates/javascript/jquery.database.js.php');
          $javascript .= ob_get_contents();
          ob_end_clean();
          $smarty->assign('JAVASCRIPT', $javascript);

          $smarty->assign('PROCESSING', 'db_update');
          $smarty->clear_assign('BUTTON_SUBMIT');
          $smarty->clear_assign('BUTTON_BACK');
          $_SESSION['visited']['sql_update'] = 'sql_update';
        }
        
        // DB version
        $dbversion = get_database_version_installer();
        
        modified_api::reset();
        $sql_files = modified_api::request('modified/version/update/'.$dbversion['plain']);

        $sql_data_array = array();
        $sql_files_array = array();
        if (is_array($sql_files)) {
          $dir = opendir(DIR_FS_INSTALLER.'update/');
          while($file = readdir($dir)) {
            if (strpos($file, '.sql') !== false 
                && strpos($file, 'update') !== false
                && in_array($file, $sql_files)
                )
            {
              $sql_files_array[] = $file;
            }
          }
          sort($sql_files_array);
        }
        
        $sql_files_array = array_unique($sql_files_array);
        
        if (count($sql_files_array) > 0) {
          foreach ($sql_files_array as $file) {
            $sql_data_array[] = array(
              'NAME' => $file,
              'CHECKBOX' => xtc_draw_hidden_field('sql_files[]', $file),
            );
          }
          $smarty->assign('sql_data_array', $sql_data_array);
        } else {
          $smarty->clear_assign('BUTTON_SUBMIT');   
        }
        $smarty->assign('UPDATE_ACTION', 'sql_update');
        break;
        
      case 'sql_manuell':
      case 'sql_manuell_confirm':
        if ($_GET['action'] == 'sql_manuell_confirm') {
          if (isset($_POST['sql']) && $_POST['sql'] != '') {
            sql_update($_POST['sql'], true);
          }
          xtc_redirect(xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=sql_manuell', $request_type));
        }
        $smarty->assign('UPDATE_ACTION', 'sql_manuell');
        $smarty->assign('SQL_MANUELL', xtc_draw_textarea_field('sql', 'soft', '60', '5'));

        // form
        $smarty->assign('FORM_ACTION', xtc_draw_form('sql_manuell', xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=sql_manuell_confirm', $request_type), 'post').xtc_draw_hidden_field(xtc_session_name(), xtc_session_id()));
        $smarty->assign('BUTTON_SUBMIT', '<button type="submit">'.BUTTON_SUBMIT.'</button>');
        $smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), '', $request_type).'">'.BUTTON_BACK.'</a>');
        $smarty->assign('FORM_END', '</form>');
        break;
      
      case 'db_update':
      case 'doupdate':
      case 'updatenow':
        // form
        $smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), '', $request_type).'">'.BUTTON_BACK.'</a>');

        $smarty->assign('UPDATE_ACTION', 'db_update');
        if ((isset($_GET['action']) && $_GET['action'] == 'updatenow') 
            || (isset($_GET['action']) && $_GET['action'] == 'doupdate')
            )
        {
          $action = (isset($_GET['action']) ? $_GET['action'] : '');

          include(DIR_FS_INSTALLER.'includes/update_action.php');
          
          $javascript = '
          <script type="text/javascript">
            var debug = true;
            var button_back = \'<a href="'.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), '', $request_type).'">'.BUTTON_BACK.'</a>\';
            var ajax_url = \''.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=doupdate', $request_type).'\';
            var maxReloads = '.UPDATE_MAX_RELOADS.';
          </script>
          ';

          ob_start();
          $process = 'update';
          require(DIR_FS_INSTALLER.'templates/javascript/jquery.database.js.php');
          $javascript .= ob_get_contents();
          ob_end_clean();
          $smarty->assign('JAVASCRIPT', $javascript);

          $smarty->assign('PROCESSING', 'db_update');
          $smarty->clear_assign('BUTTON_BACK');
          $_SESSION['visited']['updatenow'] = 'updatenow';
        }
        break;
        
      case 'db_backup':
      case 'readdb':        
        // form
        $smarty->assign('FORM_ACTION', xtc_draw_form('db_backup', xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=db_backup', $request_type), 'post', 'name="db_backup"').xtc_draw_hidden_field('action', 'backupnow').xtc_draw_hidden_field(xtc_session_name(), xtc_session_id()));
        $smarty->assign('BUTTON_SUBMIT', '<button type="submit">'.BUTTON_SUBMIT.'</button>');
        $smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), '', $request_type).'">'.BUTTON_BACK.'</a>');
        $smarty->assign('FORM_END', '</form>');
        
        $smarty->assign('INPUT_COMPRESS_GZIP', xtc_draw_radio_field('compress', 'gzip', (function_exists('gzopen')), 'id="compress_gzip"'));
        $smarty->assign('INPUT_COMPRESS_RAW', xtc_draw_radio_field('compress', 'no', (!function_exists('gzopen')), 'id="compress_raw"'));        
        $smarty->assign('INPUT_REMOVE_COLLATE', xtc_draw_checkbox_field('remove_collate', 'yes', false, 'id="remove_collate"'));
        $smarty->assign('INPUT_REMOVE_ENGINE', xtc_draw_checkbox_field('remove_engine', 'yes', false, 'id="remove_engine"'));
        $smarty->assign('INPUT_COMPLETE_INSERTS', xtc_draw_checkbox_field('complete_inserts', 'yes', true, 'id="complete_inserts"'));

        $type_array = array();
        $type_array[] = array('id' => 'all', 'text' => TEXT_DB_BACKUP_ALL);
        $type_array[] = array('id' => 'custom', 'text' => TEXT_DB_BACKUP_CUSTOM);
        $smarty->assign('INPUT_BACKUP_TYPE', xtc_draw_pull_down_menu('backup_type', $type_array, 'all', 'id="backup_type"'));
                              
        $tables_data = array();
        $tables_data[] = array(
          'CHECKBOX' => xtc_draw_checkbox_field('backup_all_tables', 'on', false, 'id="backup_all_tables"'),
          'TABLE' => TEXT_DB_SELECT_ALL,
          'ID' => 'backup_all_tables'
        );
        $tables_query = xtc_db_query("SHOW TABLES FROM `".DB_DATABASE."`");
        while ($tables = xtc_db_fetch_array($tables_query)) {
          $tables_data[] = array(
            'CHECKBOX' => xtc_draw_checkbox_field('backup_tables[]', $tables['Tables_in_'.DB_DATABASE], false, 'id="'.$tables['Tables_in_'.DB_DATABASE].'"'),
            'TABLE' => $tables['Tables_in_'.DB_DATABASE],
            'ID' => $tables['Tables_in_'.DB_DATABASE],
          );
        }
        $smarty->assign('BACKUP_TABLES_ARRAY', $tables_data);

        $utf8_query = xtc_db_query("SHOW TABLE STATUS WHERE Name='customers'");
        $utf8_array = xtc_db_fetch_array($utf8_query);
        $check_utf8 = (strpos($utf8_array['Collation'], 'utf8') === false ? false : true);
        
        if (!$check_utf8) {
          $smarty->assign('INPUT_UFT8_CONVERT', xtc_draw_checkbox_field('utf8-convert', 'yes', false, 'id="utf8-convert"'));
        }

        $smarty->assign('UPDATE_ACTION', 'db_backup');
        if ((isset($_POST['action']) && $_POST['action'] == 'backupnow') 
            || (isset($_GET['action']) && $_GET['action'] == 'readdb')
            )
        {
          define('_VALID_XTC', true);
          $action = (isset($_GET['action']) ? $_GET['action'] : '');
          if (isset($_POST['action']) && $_POST['action'] == 'backupnow') {
            $action = 'backupnow';
          }

          include (DIR_FS_CATALOG.DIR_ADMIN.'includes/functions/db_functions.php');
          include (DIR_FS_CATALOG.DIR_ADMIN.'includes/db_actions.php');
          
          $javascript = '
          <script type="text/javascript">
            var debug = true;
            var button_back = \'<a href="'.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), '', $request_type).'">'.BUTTON_BACK.'</a>\';
            var ajax_url = \''.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=readdb', $request_type).'\';
            var maxReloads = '.MAX_RELOADS.';
          </script>
          ';

          ob_start();
          $process = 'backup';
          require(DIR_FS_INSTALLER.'templates/javascript/jquery.database.js.php');
          $javascript .= ob_get_contents();
          ob_end_clean();
          $smarty->assign('JAVASCRIPT', $javascript);
          
          $smarty->assign('PROCESSING', 'db_backup');
          $smarty->clear_assign('BUTTON_SUBMIT');
          $smarty->clear_assign('BUTTON_BACK');
        }
        break;

      case 'db_restore':
      case 'restoredb':
        // form
        $smarty->assign('FORM_ACTION', xtc_draw_form('db_backup', xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=db_restore', $request_type), 'post', 'name="db_backup"').xtc_draw_hidden_field('action', 'restorenow').xtc_draw_hidden_field(xtc_session_name(), xtc_session_id()));
        $smarty->assign('BUTTON_SUBMIT', '<button type="submit">'.BUTTON_SUBMIT.'</button>');
        $smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), '', $request_type).'">'.BUTTON_BACK.'</a>');
        $smarty->assign('FORM_END', '</form>');

        $sql_data_array = array();
        $sql_files_array = array();
        $dir = opendir(DIR_FS_BACKUP);
        while($file = readdir($dir)) {
          if (strpos($file, '.sql') !== false || strpos($file, '.gz') !== false) {
            $sql_files_array[] = $file;
          }
        }
        rsort($sql_files_array);
        
        foreach ($sql_files_array as $file) {
          $sql_data_array[] = array(
            'NAME' => $file,
            'MTIME' => filemtime(DIR_FS_BACKUP.$file),
            'SIZE' => number_format(filesize(DIR_FS_BACKUP.$file)).' bytes',
            'DATE' => date('Y-m-d H:i:s', filemtime(DIR_FS_BACKUP.$file)),
            'CHECKBOX' => xtc_draw_radio_field('restore_file', $file, false, 'id="'.$file.'"'),
          );
        }
           
        $smarty->assign('UPDATE_ACTION', 'db_restore');
        $smarty->assign('sql_data_array', $sql_data_array);
        
        if ((isset($_POST['action']) && $_POST['action'] == 'restorenow' && isset($_POST['restore_file'])) 
            || (isset($_GET['action']) && $_GET['action'] == 'restoredb')
            )
        {
          define('_VALID_XTC', true);
          include (DIR_FS_CATALOG.DIR_ADMIN.'includes/functions/db_functions.php');

          $action = (isset($_GET['action']) ? $_GET['action'] : '');
          if (isset($_POST['action']) && $_POST['action'] == 'restorenow') {
            $action = 'restorenow';
          }
          $_GET['file'] = $_POST['restore_file'];

          $utf8_query = xtc_db_query("SHOW TABLE STATUS WHERE Name='customers'");
          $utf8_array = xtc_db_fetch_array($utf8_query);
          $check_utf8 = (strpos($utf8_array['Collation'], 'utf8') === false ? false : true);

          $file_array = getBackupData($_GET['file']);
          if (!$check_utf8 && $file_array['charset'] == 'utf8') {
            $_POST['utf8-convert'] = 'yes';
          }
          
          include (DIR_FS_CATALOG.DIR_ADMIN.'includes/db_actions.php');
          
          $javascript = '
          <script type="text/javascript">
            var debug = true;
            var button_back = \'<a href="'.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), '', $request_type).'">'.BUTTON_BACK.'</a>\';
            var ajax_url = \''.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=restoredb', $request_type).'\';
            var maxReloads = '.MAX_RELOADS.';
          </script>
          ';

          ob_start();
          $process = 'restore';
          require(DIR_FS_INSTALLER.'templates/javascript/jquery.database.js.php');
          $javascript .= ob_get_contents();
          ob_end_clean();
          $smarty->assign('JAVASCRIPT', $javascript);
          
          $smarty->assign('PROCESSING', 'db_restore');
          $smarty->clear_assign('BUTTON_SUBMIT');
          $smarty->clear_assign('BUTTON_BACK');
        }
        break;
    }
  }
  
  $javascriptcheck = '
  <script type="text/javascript">
    $(document).ready(function(){	
      $(".ActionLink").show();	
    });
  </script>
  ';
  $smarty->assign('JAVASCRIPTCHECK', $javascriptcheck);
  
  // checks  
  require_once('includes/checks.php');

  if ($messageStack->size('update') > 0) {
    $smarty->assign('error_message', $messageStack->output('update'));
  }
  if ($messageStack->size('update', 'success') > 0) {
    $smarty->assign('success_message', $messageStack->output('update', 'success'));
  }

  $smarty->assign('language', $_SESSION['language']);
  $module_content = $smarty->fetch('update.html');

  require ('includes/header.php');
  $smarty->assign('module_content', $module_content);
  $smarty->assign('logo', xtc_href_link(DIR_WS_INSTALLER.'images/logo_head.png', '', $request_type));

  if (!defined('RM')) {
    $smarty->load_filter('output', 'note');
  }
  $smarty->display('index.html');
  require_once ('includes/application_bottom.php');