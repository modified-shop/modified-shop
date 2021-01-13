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

  $smarty->assign('BUTTON_SYSTEM_UPDATES', '<a href="'.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=system_updates', $request_type).'" class="ActionLink" style="display:none">'.BUTTON_SYSTEM_UPDATES.'</a>');
  $smarty->assign('BUTTON_CONFIGURE', '<a href="'.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=configure', $request_type).'" class="ActionLink" style="display:none">'.BUTTON_CONFIGURE.'</a>');
  $smarty->assign('BUTTON_DB_UPDATE', '<a href="'.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=db_update', $request_type).'" class="ActionLink" style="display:none">'.BUTTON_DB_UPDATE.'</a>');
  $smarty->assign('BUTTON_SQL_UPDATE', '<a href="'.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=sql_update', $request_type).'" class="ActionLink" style="display:none">'.BUTTON_SQL_UPDATE.'</a>');
  $smarty->assign('BUTTON_SQL_MANUELL', '<a href="'.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=sql_manuell', $request_type).'" class="ActionLink" style="display:none">'.BUTTON_SQL_MANUELL.'</a>');
  $smarty->assign('BUTTON_DB_BACKUP', '<a href="'.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=db_backup', $request_type).'" class="ActionLink" style="display:none">'.BUTTON_DB_BACKUP.'</a>');
  $smarty->assign('BUTTON_DB_RESTORE', '<a href="'.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=db_restore', $request_type).'" class="ActionLink" style="display:none">'.BUTTON_DB_RESTORE.'</a>');
  $smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(DIR_WS_INSTALLER.'index.php', '', $request_type).'">'.BUTTON_BACK.'</a>');

  if (isset($_GET['action'])) {
    switch ($_GET['action']) {
      case 'system_updates':
        // downloads
        $downloads_query = xtc_db_query("SELECT opd.orders_id,
                                                opd.orders_products_id, 
                                                opd.orders_products_filename,
                                                opd.orders_products_download_id,
                                                o.customers_id, 
                                                o.customers_email_address
                                           FROM ".TABLE_ORDERS_PRODUCTS_DOWNLOAD." opd 
                                           JOIN ".TABLE_ORDERS." o 
                                                ON o.orders_id = opd.orders_id
                                          WHERE download_key = ''");
        if (xtc_db_num_rows($downloads_query) > 0) {
          while ($downloads = xtc_db_fetch_array($downloads_query)) {
            $download_key = md5($downloads['orders_id'].$downloads['orders_products_id'].$downloads['customers_id'].$downloads['customers_email_address'].$downloads['orders_products_filename']);
            xtc_db_query("UPDATE ".TABLE_ORDERS_PRODUCTS_DOWNLOAD."
                             SET download_key = '".xtc_db_input($download_key)."'
                           WHERE orders_products_download_id = '".(int)$downloads['orders_products_download_id']."'");
          }
        }
        
        // whos online
        $primary = false;
        $whosonline_query = xtc_db_query("SHOW INDEX FROM '".TABLE_WHOS_ONLINE."'");
        while ($whosonline = xtc_db_fetch_array($whosonline_query)) {
          if ($whosonline['Key_name'] == 'PRIMARY' && $whosonline['Column_name'] == 'session_id') {
            $primary = true;
          }
        }
        
        if ($primary === false) {
          xtc_db_query("TRUNCATE '".TABLE_WHOS_ONLINE."'");
          xtc_db_query("ALTER TABLE '".TABLE_WHOS_ONLINE."' ADD PRIMARY KEY (session_id)");
        }
        
        // exclude payments
        if (defined('MODULE_EXCLUDE_PAYMENT_NUMBER')) {
          for ($i = 1; $i < MODULE_EXCLUDE_PAYMENT_NUMBER; $i ++) {
            xtc_db_query("UPDATE " . TABLE_CONFIGURATION . "
                             SET set_function = 'xtc_cfg_checkbox_unallowed_module(\'shipping\', \'configuration[MODULE_EXCLUDE_PAYMENT_SHIPPING_".$i."]\','
                           WHERE configuration_key = 'MODULE_EXCLUDE_PAYMENT_SHIPPING_".$i."'");

            xtc_db_query("UPDATE " . TABLE_CONFIGURATION . "
                             SET set_function = 'xtc_cfg_checkbox_unallowed_module(\'shipping\', \'configuration[MODULE_EXCLUDE_PAYMENT_PAYMENT_".$i."]\','
                           WHERE configuration_key = 'MODULE_EXCLUDE_PAYMENT_PAYMENT_".$i."'");
          }
        }
        
        $messageStack->add_session('update', TEXT_UPDATE_SYSTEM_SUCCESS, 'success');
        xtc_redirect(xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), '', $request_type));
        break;
        
      case 'configure':
      case 'configure_confirm':
        if ($_GET['action'] == 'configure_confirm') {
          $db_server = DB_SERVER;
          $db_username = DB_SERVER_USERNAME;
          $db_password = DB_SERVER_PASSWORD;
          $db_database = DB_DATABASE;
    
          $db_type = get_mysql_type();
          $db_charset = DB_SERVER_CHARSET;
          $db_pconnect = USE_PCONNECT;

          $http_server = HTTP_SERVER;
          $https_server = HTTPS_SERVER;
          $use_ssl = ((ENABLE_SSL == true) ? 'true' : 'false');

          //create  includes/configure.php
          include (DIR_FS_INSTALLER.'templates/configure.php');
          if (file_exists(DIR_FS_CATALOG.'/includes/local/configure.php')) {
            $fp = fopen(DIR_FS_CATALOG . 'includes/local/configure.php', 'w');
          } else {
            $fp = fopen(DIR_FS_CATALOG . 'includes/configure.php', 'w');
          }
          fputs($fp, $file_contents);
          fclose($fp);
          $messageStack->add_session('update', TEXT_CONFIGURE_SUCCESS, 'success');
          xtc_redirect(xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=configure', $request_type));
        }
        $smarty->assign('UPDATE_ACTION', 'configure');
        
        // form
        $smarty->assign('FORM_ACTION', xtc_draw_form('sql_update', xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=configure_confirm', $request_type), 'post').xtc_draw_hidden_field(xtc_session_name(), xtc_session_id()));
        $smarty->assign('BUTTON_SUBMIT', '<button type="submit">'.BUTTON_SUBMIT.'</button>');
        $smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), '', $request_type).'">'.BUTTON_BACK.'</a>');
        $smarty->assign('FORM_END', '</form>');
        break;
    
      case 'sql_update':
      case 'sql_update_confirm':
        if ($_GET['action'] == 'sql_update_confirm') {
          if (isset($_POST['sql_files']) && count($_POST['sql_files']) > 0) {
            foreach ($_POST['sql_files'] as $sql_file) {
              sql_update(DIR_FS_INSTALLER.'update/'.$sql_file);
            }
            xtc_db_query("TRUNCATE `session`");
          } else {
            $messageStack->add_session('update', ERROR_SQL_UPDATE_NO_FILE);
          }
          xtc_redirect(xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=sql_update', $request_type));
        }
        $sql_data_array = array();
        $sql_files_array = array();
        $dir = opendir(DIR_FS_INSTALLER.'update/');
        while($file = readdir($dir)) {
          if (strpos($file, '.sql') !== false && strpos($file, 'update') !== false) {
            $sql_files_array[] = $file;
          }
        }
        sort($sql_files_array);
        foreach ($sql_files_array as $file) {
          $sql_data_array[] = array(
            'NAME' => $file,
            'CHECKBOX' => xtc_draw_checkbox_field('sql_files[]', $file, false, 'id="'.$file.'"'),
          );
        }
        $smarty->assign('UPDATE_ACTION', 'sql_update');
        $smarty->assign('sql_data_array', $sql_data_array);
        
        // form
        $smarty->assign('FORM_ACTION', xtc_draw_form('sql_update', xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=sql_update_confirm', $request_type), 'post').xtc_draw_hidden_field(xtc_session_name(), xtc_session_id()));
        $smarty->assign('BUTTON_SUBMIT', '<button type="submit">'.BUTTON_SUBMIT.'</button>');
        $smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), '', $request_type).'">'.BUTTON_BACK.'</a>');
        $smarty->assign('FORM_END', '</form>');
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
        // form
        $smarty->assign('FORM_ACTION', xtc_draw_form('db_update', xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=db_update', $request_type), 'post', 'name="db_update"').xtc_draw_hidden_field('action', 'updatenow').xtc_draw_hidden_field(xtc_session_name(), xtc_session_id()));
        $smarty->assign('BUTTON_SUBMIT', '<button type="submit">'.BUTTON_SUBMIT.'</button>');
        $smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), '', $request_type).'">'.BUTTON_BACK.'</a>');
        $smarty->assign('FORM_END', '</form>');

        $smarty->assign('UPDATE_ACTION', 'db_update');
        if ((isset($_POST['action']) && $_POST['action'] == 'updatenow') 
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
            var button_back = \'<a href="'.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), '', $request_type).'">'.BUTTON_BACK.'</a>\';
            var ajax_url = \''.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=doupdate', $request_type).'\';
            var maxReloads = '.MAX_RELOADS.';
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
        $tables_query = xtc_db_query("SHOW TABLES FROM `".DB_DATABASE."`");
        while ($tables = xtc_db_fetch_array($tables_query)) {
          $tables_data[] = array(
            'CHECKBOX' => xtc_draw_checkbox_field('backup_tables[]', $tables['Tables_in_'.DB_DATABASE], false, 'id="'.$tables['Tables_in_'.DB_DATABASE].'"'),
            'TABLE' => $tables['Tables_in_'.DB_DATABASE],
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
  
  if ($messageStack->size('update') > 0) {
    $smarty->assign('error', $messageStack->output('update'));
  }
  if ($messageStack->size('update', 'success') > 0) {
    $smarty->assign('success', $messageStack->output('update', 'success'));
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
?>