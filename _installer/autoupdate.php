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

  // include needed functions
  require_once (DIR_FS_INC.'xtc_random_name.inc.php');
  require_once (DIR_FS_INC.'xtc_unlink_temp_dir.inc.php');
  require_once (DIR_FS_INC.'readfile_chunked.inc.php');
  require_once (DIR_FS_INC.'xtc_get_shop_conf.inc.php'); 
  require_once (DIR_FS_INC.'xtc_php_mail.inc.php');

  // make a connection to the database... now
  xtc_db_connect() or die('Unable to connect to database server!');

  // load configuration
  $configuration_query = xtc_db_query('SELECT configuration_key, configuration_value FROM '.TABLE_CONFIGURATION);
  while ($configuration = xtc_db_fetch_array($configuration_query)) {
    defined($configuration['configuration_key']) OR define($configuration['configuration_key'], stripslashes($configuration['configuration_value']));
  }

  // language
  require_once(DIR_FS_INSTALLER.'lang/'.$_SESSION['language'].'.php');
 
  // smarty
  $smarty = new Smarty();
  $smarty->setTemplateDir(__DIR__.'/templates')
         ->registerResource('file', new EvaledFileResource())
         ->setConfigDir(__DIR__.'/lang')
         ->SetCaching(0);

  if (isset($_GET['action'])
      && $_GET['action'] == 'request'
      )
  {
    modified_api::reset();
    $response = modified_api::request('modified/support/'.$_SESSION['language_code']);
    
    if (!is_array($response) || count($response) < 1) {
      $messageStack->add_session('update', TEXT_AUTOUPDATER_SUPPORT_ALTERNATIVE);
    } else {
      $message_array = array(
        'PHP Version' => phpversion(),
        'Shop Domain' => HTTP_SERVER.DIR_WS_CATALOG,
        'Shop Version' => get_shop_version(),
        'Template' => CURRENT_TEMPLATE,
      );
    
      $message = '';
      foreach ($message_array as $k => $v) {
        $message .= $k . ': ' . xtc_db_prepare_input($v). "\n";
      }
  
      xtc_php_mail(EMAIL_SUPPORT_ADDRESS, 
                   EMAIL_SUPPORT_NAME, 
                   $response['mail']['address'], 
                   $response['mail']['name'], 
                   EMAIL_SUPPORT_FORWARDING_STRING, 
                   EMAIL_SUPPORT_REPLY_ADDRESS, 
                   EMAIL_SUPPORT_REPLY_ADDRESS_NAME, 
                   '', 
                   '', 
                   $response['mail']['templatesubject'].STORE_NAME, 
                   nl2br($message), 
                   $message);
    
      $messageStack->add_session('update', $response['stack']['success'], 'success');
    }

    $_SESSION['template_request'] = true;
    xtc_redirect(xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), '', $request_type));
  }

  $smarty->assign('BUTTON_DB_BACKUP', '<a href="'.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=db_backup', $request_type).'" class="ActionLink" style="display:none">'.BUTTON_DB_BACKUP.'</a>');
  
  $smarty->assign('AUTOUPDATE', true);
  $smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(DIR_WS_INSTALLER, 'action=shop', $request_type).'">'.BUTTON_SHOP.'</a>');
  if (!isset($_SESSION['template_request'])) {
    $smarty->assign('FORM_ACTION', xtc_draw_form('autoupdate', xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'uaction=update', $request_type), 'post', 'name="db_backup"').xtc_draw_hidden_field(xtc_session_name(), xtc_session_id()));
    $smarty->assign('BUTTON_SUBMIT', '<button type="submit">'.BUTTON_UPDATE.'</button>');
    $smarty->assign('FORM_END', '</form>');
  }

  $smarty->assign('LINK_DB_BACKUP', xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=db_backup', $request_type));
  $smarty->assign('LINK_DB_RESTORE', xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=db_restore', $request_type));
  $smarty->assign('LINK_DB_CONVERT', xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=db_convert', $request_type));
  
  $smarty->assign('SITE', 'autoupdate');
  $smarty->assign('BUTTON_TEMPLATE_UPDATE', '<a target="_blank" href="https://www.modified-shop.org/wiki/Tutorial:_Template_eines_xt:Commerce_Shops_in_der_modified_eCommerce_Shopsoftware_weiter_verwenden">'.BUTTON_TEMPLATE_UPDATE.'</a>');
  $smarty->assign('BUTTON_REQUEST_UPDATE', '<a href="'.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=request', $request_type).'">'.BUTTON_REQUEST_UPDATE.'</a>');
  
  $modulelist = array(
    array(
      'NAME' => TEXT_CHECK_UPDATE,
      'LINK' => 'javascript:void(0)',
      'BUTTON' => '<i class="fas fa-check"></i>',
      'VISITED' => false,
    ),
    array(
      'NAME' => TEXT_DO_UPDATE,
      'LINK' => 'javascript:void(0)',
      'BUTTON' => '<i class="fas fa-check"></i>',
      'VISITED' => false,
    ),
    array(
      'NAME' => TEXT_DELETE_FILES,
      'LINK' => 'javascript:void(0)',
      'BUTTON' => '<i class="fas fa-check"></i>',
      'VISITED' => false,
    ),
    array(
      'NAME' => TEXT_UPDATE_CONFIG,
      'LINK' => 'javascript:void(0)',
      'BUTTON' => '<i class="fas fa-check"></i>',
      'VISITED' => false,
    ),
    array(
      'NAME' => TEXT_SQL_UPDATE,
      'LINK' => 'javascript:void(0)',
      'BUTTON' => '<i class="fas fa-check"></i>',
      'VISITED' => false,
    ),
    array(
      'NAME' => TEXT_UPDATE_SYSTEM,
      'LINK' => 'javascript:void(0)',
      'BUTTON' => '<i class="fas fa-check"></i>',
      'VISITED' => false,
    ),
    array(
      'NAME' => TEXT_DB_UPDATE,
      'LINK' => 'javascript:void(0)',
      'BUTTON' => '<i class="fas fa-check"></i>',
      'VISITED' => false,
    ),
  );
  
  if (!isset($unlinked_files)) {
    $unlinked_files = array(
      'error' => array(
        'files' => array(),
        'dir' => array(),
      ),
      'success' => array(
        'files' => array(),
        'dir' => array(),
      ),
    );
  }

  if (isset($_GET['uaction'])) {
    $step = isset($_GET['step']) ? $_GET['step'] : 1;
    
    $smarty->clear_assign('LINK_DB_BACKUP');
    $smarty->clear_assign('LINK_DB_RESTORE');
    $smarty->clear_assign('LINK_SQL_MANUELL');
    $smarty->clear_assign('BUTTON_SUBMIT');
    $smarty->clear_assign('BUTTON_BACK');
    
    if (!isset($_SESSION['offline'])) {
      $_SESSION['offline'] = 1;
      $shop_is_offline = get_shop_offline_status();
      if (!$shop_is_offline) {
        $_SESSION['offline'] = 2;
        xtc_db_query("UPDATE shop_configuration
                         SET configuration_value = 'checked' 
                       WHERE configuration_key = 'SHOP_OFFLINE'");
      }
    }
    
    switch ($step) {
      case '1':
        // check for errors
        $error = false;

        // check requirements
        require_once('includes/check_requirements.php');
        
        // check permissions
        require_once('includes/check_permissions.php');
        
        // check versions
        $integrity_error = false;
        $shopversion = get_shop_version();
        $dbversion = get_database_version_installer();
        $_SESSION['dbversion'] = $dbversion['plain'];
        if (isset($_SESSION['sql_files'])) unset($_SESSION['sql_files']);
        
        $requirement_array[] = array(
          'name' => 'DB VERSION',
          'version' => $dbversion['plain'],
          'version_min' => '',
          'version_max' => '',
          'status' => ($dbversion['plain'] == $shopversion)
        );
        
        $requirement_array[] = array(
          'name' => 'SHOP VERSION',
          'version' => $shopversion,
          'version_min' => '',
          'version_max' => '',
          'status' => ($dbversion['plain'] == $shopversion)
        );  

        if ($dbversion['plain'] != $shopversion) {   
          $integrity_error = true;
        }
        
        // check integrity
        $checksum_array = get_integrity();
        if (count($checksum_array) > 0) {
          $integrity_error = true;
          $backup_content = create_backup($checksum_array);
        }
        $requirement_array[] = array(
          'name' => 'FILE INTEGRITY',
          'version' => '',
          'version_min' => '',
          'version_max' => '',
          'status' => (count($checksum_array) == 0)
        );
        
        if ($error === true || $integrity_error === true) {
          $smarty->assign('LINK_DB_BACKUP', xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=db_backup', $request_type));
          $smarty->assign('LINK_DB_RESTORE', xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=db_restore', $request_type));
        
          $smarty->assign('REQUIREMENT_ARRAY', $requirement_array);
          $smarty->assign('PERMISSION_ARRAY', $permission_array);
          $smarty->clear_assign('FORM_ACTION');

          if (count($permission_array['file_permission']) > 0
              || count($permission_array['folder_permission']) > 0
              || count($permission_array['rfolder_permission']) > 0
              )
          {
            // ftp
            $smarty->assign('INPUT_FTP_HOST', xtc_draw_input_fieldNote(array('name' => 'ftp_host')));
            $smarty->assign('INPUT_FTP_PORT', xtc_draw_input_fieldNote(array('name' => 'ftp_port')));
            $smarty->assign('INPUT_FTP_PATH', xtc_draw_input_fieldNote(array('name' => 'ftp_path')));
            $smarty->assign('INPUT_FTP_USER', xtc_draw_input_fieldNote(array('name' => 'ftp_user')));    
            $smarty->assign('INPUT_FTP_PASS', xtc_draw_input_fieldNote(array('name' => 'ftp_pass')));    

            // form
            $smarty->assign('FORM_ACTION', xtc_draw_form('ftp', xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), '', $request_type), 'post').xtc_draw_hidden_field('action', 'ftp'));
            $smarty->assign('BUTTON_SUBMIT', '<button type="submit">'.BUTTON_SUBMIT.'</button>');
            $smarty->assign('FORM_END', '</form>');
          }

          if ($messageStack->size('ftp_message') > 0) {
            $smarty->assign('error_message', $messageStack->output('ftp_message'));
          }
          
          if ($error === false && count($checksum_array) > 0) {
            $smarty->assign('backup_content', $backup_content);
          }
          
          $smarty->assign('language', $_SESSION['language']);
          $smarty->assign('UPDATE_ERROR', $smarty->fetch('error.html'));
          $smarty->clear_assign('error_message');
          
          if ($error !== true && $integrity_error === true && !isset($_SESSION['template_request'])) {
            $messageStack->add('update', sprintf(ERROR_FILE_INTEGRITY, count($checksum_array)));
            $smarty->assign('FORM_ACTION', xtc_draw_form('autoupdate', xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'uaction=update&step=2', $request_type), 'post', 'name="db_backup"').xtc_draw_hidden_field(xtc_session_name(), xtc_session_id()));
            $smarty->assign('BUTTON_SUBMIT', '<button type="submit">'.BUTTON_UPDATE_CONTINUE.'</button>');
          }
          if ($error === true) {
            $messageStack->add('update', ERROR_AUTOUPDATE);
          }
          $smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(DIR_WS_INSTALLER, 'action=shop', $request_type).'">'.BUTTON_SHOP.'</a>');
          $modulelist[$step - 1]['BUTTON'] = '<i class="fas fa-exclamation-triangle"></i>';
        } else {
          $smarty->assign('FORM_ACTION', xtc_draw_form('autoupdateprocess', xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'uaction=update&step=2', $request_type), 'post', 'name="db_backup"').xtc_draw_hidden_field(xtc_session_name(), xtc_session_id()));
          $modulelist[$step - 1]['BUTTON'] = '<i class="fas fa-clock fa-spin"></i>';
        }
        break;

      case '2':
        $update = update_shop();
        if ($update == false) {
          $smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(DIR_WS_INSTALLER, 'action=shop', $request_type).'">'.BUTTON_SHOP.'</a>');
          $modulelist[$step - 1]['BUTTON'] = '<i class="fas fa-exclamation-triangle"></i>';
        } else {
          $smarty->assign('FORM_ACTION', xtc_draw_form('autoupdateprocess', xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'uaction=update&step=3', $request_type), 'post', 'name="db_backup"').xtc_draw_hidden_field(xtc_session_name(), xtc_session_id()));
          $modulelist[$step - 1]['BUTTON'] = '<i class="fas fa-clock fa-spin"></i>';
        }
        break;

      case '3':
        $error = false;
        require_once('includes/delete_files.php');
        require_once('includes/delete_dirs.php');
        
        if ($error == true) {
          $smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(DIR_WS_INSTALLER, 'action=shop', $request_type).'">'.BUTTON_SHOP.'</a>');
          $modulelist[$step - 1]['BUTTON'] = '<i class="fas fa-exclamation-triangle"></i>';
        } else {
          $smarty->assign('FORM_ACTION', xtc_draw_form('autoupdateprocess', xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'uaction=update&step=4', $request_type), 'post', 'name="db_backup"').xtc_draw_hidden_field(xtc_session_name(), xtc_session_id()));
          $modulelist[$step - 1]['BUTTON'] = '<i class="fas fa-clock fa-spin"></i>';
        }
        break;

      case '4':
        include(DIR_FS_INSTALLER.'includes/update_configure.php');

        if ($error == true) {
          $smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(DIR_WS_INSTALLER, 'action=shop', $request_type).'">'.BUTTON_SHOP.'</a>');
          $modulelist[$step - 1]['BUTTON'] = '<i class="fas fa-exclamation-triangle"></i>';
        } else {
          $smarty->assign('FORM_ACTION', xtc_draw_form('autoupdateprocess', xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=processnow&uaction=update&step=5', $request_type), 'post', 'name="db_backup"').xtc_draw_hidden_field(xtc_session_name(), xtc_session_id()));
          $modulelist[$step - 1]['BUTTON'] = '<i class="fas fa-clock fa-spin"></i>';
        }
        break;

      case '5':
        $smarty->assign('FORM_ACTION', xtc_draw_form('autoupdateprocess', xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'uaction=update&step=6', $request_type), 'post', 'name="db_backup"').xtc_draw_hidden_field(xtc_session_name(), xtc_session_id()));
        $modulelist[$step - 1]['BUTTON'] = '<i class="fas fa-clock fa-spin"></i>';
        
        if (!isset($_SESSION['sql_files'])) {
          modified_api::reset();
          $_SESSION['sql_files'] = modified_api::request('modified/version/update/'.$_SESSION['dbversion']);
        }
        
        if ((isset($_GET['action']) && $_GET['action'] == 'processnow') 
            || (isset($_GET['action']) && $_GET['action'] == 'sql_update_process')
            )
        {
          $action = (isset($_GET['action']) ? $_GET['action'] : '');
          if (isset($_POST['action']) && $_POST['action'] == 'processnow') {
            $action = 'processnow';
          }
          
          $_POST['sql_files'] = $_SESSION['sql_files'];
          if (isset($_POST['sql_files']) 
              && is_array($_POST['sql_files']) 
              && count($_POST['sql_files']) > 0
              && $action == 'processnow'
              )
          {
            $sql_data_array = array();
            foreach ($_POST['sql_files'] as $sql_file) {
              $sql_data = sql_update(DIR_FS_INSTALLER.'update/'.$sql_file);
              $sql_data_array = array_merge($sql_data_array, $sql_data);
            }
          } elseif (is_file(DIR_FS_INSTALLER.'update/complete.sql')) {
            $sql_data_content = file_get_contents(DIR_FS_INSTALLER.'update/complete.sql');
            $sql_data_array = json_decode($sql_data_content, true);
            $sql_data_array = array_map('base64_decode', $sql_data_array);
          }

          include(DIR_FS_INSTALLER.'includes/update_sql.php');
          
          $javascript = '
          <script type="text/javascript">
            var debug = true;
            var continue_url = \''.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'uaction=update&step='.$step, $request_type).'\';
            var ajax_url = \''.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=sql_update_process&uaction=update&step='.$step, $request_type).'\';
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
          $smarty->clear_assign('FORM_ACTION');
        }
        break;

      case '6':
        include(DIR_FS_INSTALLER.'includes/update_system.php');

        $smarty->assign('FORM_ACTION', xtc_draw_form('autoupdateprocess', xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=updatenow&uaction=update&step=7', $request_type), 'post', 'name="db_backup"').xtc_draw_hidden_field(xtc_session_name(), xtc_session_id()));
        $modulelist[$step - 1]['BUTTON'] = '<i class="fas fa-clock fa-spin"></i>';
        break;

      case '7':
        $smarty->assign('FORM_ACTION', xtc_draw_form('autoupdateprocess', xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'uaction=update&step=8', $request_type), 'post', 'name="db_backup"').xtc_draw_hidden_field(xtc_session_name(), xtc_session_id()));
        $modulelist[$step - 1]['BUTTON'] = '<i class="fas fa-clock fa-spin"></i>';
        
        if (is_file(DIR_FS_INSTALLER.'update/complete.sql')) {
          unlink(DIR_FS_INSTALLER.'update/complete.sql');
        }
        
        if ((isset($_GET['action']) && $_GET['action'] == 'updatenow') 
            || (isset($_GET['action']) && $_GET['action'] == 'doupdate')
            )
        {
          $action = (isset($_GET['action']) ? $_GET['action'] : '');
          if (isset($_POST['action']) && $_POST['action'] == 'updatenow') {
            $action = 'updatenow';
          }

          include(DIR_FS_INSTALLER.'includes/update_action.php');
          
          $javascript = '
          <script type="text/javascript">
            var debug = true;
            var continue_url = \''.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'uaction=update&step=finish', $request_type).'\';
            var ajax_url = \''.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=doupdate&uaction=update&step='.$step, $request_type).'\';
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
          $smarty->clear_assign('FORM_ACTION');
        }
        break;

      case 'finish':
        $smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(DIR_WS_INSTALLER, 'action=shop', $request_type).'">'.BUTTON_SHOP.'</a>');
        $smarty->assign('FINISH_INFO', TEXT_AUTOUPDATER_FINISH_INFO);

        modified_api::reset();
        $notes = modified_api::request('modified/version/notes/'.$_SESSION['dbversion']);
        if (is_array($notes) && count($notes) > 0) {
          $smarty->assign('NOTES', $notes);
        }
                
        if (isset($_SESSION['offline'])
            && $_SESSION['offline'] == 2
            )
        {
          xtc_db_query("UPDATE shop_configuration
                           SET configuration_value = '' 
                         WHERE configuration_key = 'SHOP_OFFLINE'");
          unset($_SESSION['offline']);
        }
        break;
    }
    
    if ($step != 'finish') {
      foreach ($modulelist as $k => $v) {
        if ($k >= (int)$step) {
          $modulelist[$k]['BUTTON'] = '';
        }
      }
    }
  }

  if (isset($_GET['action'])) {
    switch ($_GET['action']) {
      case 'download':
        $filename = ((isset($_GET['file'])) ? $_GET['file'] : '');
        if (is_file(DIR_FS_CATALOG.DIR_ADMIN.'backups/'.$filename)) {
          if (function_exists('symlink')) {
            xtc_unlink_temp_dir(DIR_FS_DOWNLOAD_PUBLIC);
            $tempdir = xtc_random_name();
            umask(0000);
            mkdir(DIR_FS_DOWNLOAD_PUBLIC.$tempdir, 0777);
            if (!symlink(DIR_FS_CATALOG.DIR_ADMIN.'backups/'.$filename, DIR_FS_DOWNLOAD_PUBLIC.$tempdir.'/'.$filename)) {
              link(DIR_FS_CATALOG.DIR_ADMIN.'backups/'.$filename, DIR_FS_DOWNLOAD_PUBLIC.$tempdir.'/'.$filename); 
            }
            xtc_redirect(DIR_WS_DOWNLOAD_PUBLIC.$tempdir.'/'.$filename);
          } else {
            //Set chunk size for download
            $chunksize = 1 * (1024 * 1024);
            // Now send the file with header() magic
            header("Expires: Mon, 26 Nov 1962 00:00:00 GMT");
            header("Last-Modified: ".gmdate("D,d M Y H:i:s")." GMT");
            header("Cache-Control: no-cache, must-revalidate");
            header("Pragma: no-cache");
            header("Content-Type: Application/octet-stream");
            header("Content-Length: ".filesize(DIR_FS_CATALOG.DIR_ADMIN.'backups/'.$filename));
            header("Content-disposition: attachment; filename=\"".$filename."\"");
            // This will work on all systems, but will need considerable resources
            // We could also loop with fread($fp, 4096) to save memory
            readfile_chunked(DIR_FS_CATALOG.DIR_ADMIN.'backups/'.$filename, $chunksize);
            exit();
          }
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

      case 'db_convert':
      case 'convertdb':        
        // form
        $smarty->assign('FORM_ACTION', xtc_draw_form('db_convert', xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=db_convert', $request_type), 'post', 'name="db_convert"').xtc_draw_hidden_field('action', 'convertnow').xtc_draw_hidden_field(xtc_session_name(), xtc_session_id()));
        $smarty->assign('BUTTON_SUBMIT', '<button type="submit">'.BUTTON_SUBMIT.'</button>');
        $smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), '', $request_type).'">'.BUTTON_BACK.'</a>');
        $smarty->assign('FORM_END', '</form>');
        
        // database 
        $default_query = xtc_db_query("SELECT @@character_set_database as dbcharset");
        $default = xtc_db_fetch_array($default_query);

        $db_engine_array = array(
          array('id' => 'MyISAM', 'text' => 'MyISAM'),
          array('id' => 'InnoDB', 'text' => 'InnoDB'),
        );
        
        $db_charset = 'latin1';
        $db_engine = 'MyISAM';
        
        $db_charset_array = array(
          array('id' => 'latin1', 'text' => 'ISO-8859-15'),
        );
        $check_query = xtc_db_query("SHOW COLLATION WHERE CHARSET = 'utf8mb4'");
        if (xtc_db_num_rows($check_query) > 0) {
          array_unshift($db_charset_array, array('id' => 'utf8mb4', 'text' => 'UTF-8 MB4'));
          if ($default['dbcharset'] == 'utf8mb4') {
            $db_charset = 'utf8mb4';
            $db_engine = 'InnoDB';
          }
        }
        $check_query = xtc_db_query("SHOW COLLATION WHERE CHARSET = 'utf8mb3'");
        if (xtc_db_num_rows($check_query) > 0) {
          array_unshift($db_charset_array, array('id' => 'utf8mb3', 'text' => 'UTF-8 MB3'));
          if ($default['dbcharset'] == 'utf8mb3') {
            $db_charset = 'utf8mb3';
          }
        }
        $check_query = xtc_db_query("SHOW COLLATION WHERE CHARSET = 'utf8'");
        if (xtc_db_num_rows($check_query) > 0) {
          array_unshift($db_charset_array, array('id' => 'utf8', 'text' => 'UTF-8'));
          if ($default['dbcharset'] == 'utf8') {
            $db_charset = 'utf8';
          }
        }
        
        $smarty->assign('INPUT_DB_CHARSET', xtc_draw_pull_down_menuNote(array ('name' => 'db_charset'), $db_charset_array, $db_charset));
        $smarty->assign('INPUT_DB_ENGINE', xtc_draw_pull_down_menuNote(array ('name' => 'db_engine'), $db_engine_array, $db_engine));
        
        $javascriptdbcheck = '
        <script type="text/javascript">
          $(document).ready(function(){	
            $("select[name=\"db_charset\"]").on("change", function(){
              if ($(this).val() == "utf8mb4") {
                $("select[name=\"db_engine\"]").val("InnoDB");
              }
            });
            $("select[name=\"db_engine\"]").on("change", function(){
              if ($(this).val() == "MyISAM"
                  && $("select[name=\"db_charset\"]").val() == "utf8mb4"
                  )
              {
                $("select[name=\"db_charset\"]").val($("select[name=\"db_charset\"] option:first").val());
              }
            });
          });
        </script>
        ';
        $smarty->assign('JAVASCRIPTDBCHECK', $javascriptdbcheck);

        $smarty->assign('UPDATE_ACTION', 'db_convert');
        if ((isset($_POST['action']) && $_POST['action'] == 'convertnow') 
            || (isset($_GET['action']) && $_GET['action'] == 'convertdb')
            )
        {
          define('_VALID_XTC', true);
          $action = (isset($_GET['action']) ? $_GET['action'] : '');
          if (isset($_POST['action']) && $_POST['action'] == 'convertnow') {
            $action = 'convertnow';

            $new_db_charset = $_POST['db_charset'];
            $new_db_engine = $_POST['db_engine'];
            include(DIR_FS_INSTALLER.'includes/update_configure.php');             
          }

          include (DIR_FS_CATALOG.DIR_ADMIN.'includes/functions/db_functions.php');
          include (DIR_FS_CATALOG.DIR_ADMIN.'includes/db_actions.php');
          
          $javascript = '
          <script type="text/javascript">
            var debug = true;
            var button_back = \'<a href="'.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), '', $request_type).'">'.BUTTON_BACK.'</a>\';
            var ajax_url = \''.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=convertdb', $request_type).'\';
            var maxReloads = '.MAX_RELOADS.';
          </script>
          ';

          ob_start();
          $process = 'convert';
          require(DIR_FS_INSTALLER.'templates/javascript/jquery.database.js.php');
          $javascript .= ob_get_contents();
          ob_end_clean();
          $smarty->assign('JAVASCRIPT', $javascript);
          
          $smarty->assign('PROCESSING', 'db_convert');
          $smarty->clear_assign('BUTTON_SUBMIT');
          $smarty->clear_assign('BUTTON_BACK');
        }
        break;
    }
  }
  
  if (!isset($step)) {
    foreach ($modulelist as $k => $v) {
      $modulelist[$k]['BUTTON'] = '';
    }
  } 
  $smarty->assign('modulelist', $modulelist);

  $javascriptcheck = '
  <script type="text/javascript">
    $(document).ready(function(){	
      $(".ActionLink").show();	
      $("#autoupdateprocess").submit();
    });
  </script>
  ';
  $smarty->assign('JAVASCRIPTCHECK', $javascriptcheck);
  
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