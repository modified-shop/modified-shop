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

  // language
  require_once(DIR_FS_INSTALLER.'lang/'.$_SESSION['language'].'.php');

  // smarty
  $smarty = new Smarty();
  $smarty->setTemplateDir(__DIR__.'/templates')
         ->registerResource('file', new EvaledFileResource())
         ->setConfigDir(__DIR__.'/lang')
         ->SetCaching(0);
  
  // default
  $db_server = ((defined('DB_SERVER')) ? DB_SERVER : '');
  $db_database = ((defined('DB_DATABASE')) ? DB_DATABASE : '');
  $db_username = ((defined('DB_SERVER_USERNAME')) ? DB_SERVER_USERNAME : '');
  $db_password = ((defined('DB_SERVER_PASSWORD')) ? DB_SERVER_PASSWORD : '');
  
  $db_type = ((defined('DB_MYSQL_TYPE')) ? DB_MYSQL_TYPE : '');
  $db_charset = (($upgrade === true && defined('DB_SERVER_CHARSET')) ? DB_SERVER_CHARSET : 'utf8');
  $db_pconnect = ((defined('USE_PCONNECT')) ? USE_PCONNECT : 'false');

  $http_server = HTTP_SERVER;
  $https_server = HTTPS_SERVER;
  $use_ssl = ((defined('ENABLE_SSL') && ENABLE_SSL == true) ? 'true' : 'false');
  $session = 'mysql';

  $sql_file_array = array(
    'modified.sql',
    'banktransfer_blz.sql',
    'customers_status.sql',            
  );

  if (isset($_POST['action']) && $_POST['action'] == 'process') {
    $valid_params = array(
      'db_server',
      'db_username',
      'db_password',
      'db_database',
      'db_type',
      'db_charset',
      'db_pconnect',
      'db_install',
      
      'http_server',
      'https_server',
      'session',
      'use_ssl',
    );

    // prepare variables
    foreach ($_POST as $key => $value) {
      if (!is_object(${$key}) && in_array($key , $valid_params)) {
        ${$key} = addslashes($value);
      }
    }
    
    // Database
    require_once (DIR_FS_INC.'db_functions_'.$db_type.'.inc.php');
    require_once (DIR_FS_INC.'db_functions.inc.php');
    
    $connection = xtc_db_connect($db_server, $db_username, $db_password, $db_database, 'db_link');
    if (is_object($connection) || is_resource($connection)) {
      $error = false;
      if (!isset($db_install)) {
        $check_query = xtc_db_query("SHOW TABLES FROM `".$db_database."`");
        if (xtc_db_num_rows($check_query) > 0) {
          $messageStack->add('install_step1', ERROR_DATABASE_NOT_EMPTY);
          $error = true;
        }
      }
      
      if ($error === false) {
        $_SESSION['language_charset'] = (($db_charset == 'utf8') ? 'utf-8' : 'ISO-8859-15');
        
        $collation = 'latin1_german1_ci';
        if ($_SESSION['language_charset'] == 'utf-8') {
          $collation = 'utf8_general_ci';
        }
        xtc_db_query('ALTER DATABASE `'.$db_database.'` DEFAULT CHARACTER SET '.$db_charset.' COLLATE '.$collation);
        xtc_db_query('SET NAMES '.$db_charset.' COLLATE '.$collation);
        
        $engine = '';
        xtc_db_query("CREATE TABLE IF NOT EXISTS `engine` (`type` VARCHAR( 16 ) NOT NULL)");
        $check_query = xtc_db_query("SHOW CREATE TABLE `engine`");
        $check = xtc_db_fetch_array($check_query);
        
        $pos = stripos($check['Create Table'], 'engine=');
        if ($pos !== false) {
          $engine = trim(substr($check['Create Table'], ($pos + 7), (strpos($check['Create Table'], ' ', $pos) - $pos - 7)));
        }
        if ($engine == '') {
          $pos = stripos($check['Create Table'], 'type=');
          if ($pos !== false) {
            $engine = trim(substr($check['Create Table'], ($pos + 5), (strpos($check['Create Table'], ' ', $pos) - $pos - 5)));
          }
        }
        xtc_db_query("TRUNCATE `engine`");
        xtc_db_query("INSERT INTO `engine` VALUES ('".xtc_db_input($engine)."')");
        
        //create  includes/configure.php
        include (DIR_FS_INSTALLER.'templates/configure.php');
        if (file_exists(DIR_FS_CATALOG.'/includes/local/configure.php')) {
          $fp = fopen(DIR_FS_CATALOG . 'includes/local/configure.php', 'w');
        } else {
          $fp = fopen(DIR_FS_CATALOG . 'includes/configure.php', 'w');
        }
        fputs($fp, $file_contents);
        fclose($fp);
        
        xtc_redirect(xtc_href_link(DIR_WS_INSTALLER.'install_step1.php', 'action=restorenow&sql=0', $request_type));
      }
    } else {
      $messageStack->add('install_step1', ERROR_DATABASE_CONNECTION);
    }
  } elseif (isset($_GET['action'])) {
    // Database
    require_once (DIR_FS_INC.'db_functions_'.DB_MYSQL_TYPE.'.inc.php');
    require_once (DIR_FS_INC.'db_functions.inc.php');

    // make a connection to the database... now
    xtc_db_connect() or die('Unable to connect to database server!');
    xtc_db_query("SET storage_engine = MYISAM");
    
    if ($_GET['action'] == 'restorenow' || $_GET['action'] == 'restoredb') {
      define('_VALID_XTC', true);
      $action = (isset($_GET['action']) ? $_GET['action'] : '');
      if (isset($_POST['action']) && $_POST['action'] == 'restorenow') {
        $action = 'restorenow';
      }
      $_GET['file'] = $sql_file_array[$_GET['sql']];
      $_GET['convert'] = $_SESSION['language_charset'];
      
      include (DIR_FS_CATALOG.DIR_ADMIN.'includes/functions/db_functions.php');
      include (DIR_FS_CATALOG.DIR_ADMIN.'includes/db_actions.php');

      $javascript = '
      <script type="text/javascript">
        var debug = true;
        var continue_url = \''.((isset($sql_file_array[($_GET['sql'] + 1)])) ? xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=restorenow&sql='.($_GET['sql'] + 1), $request_type) : xtc_href_link(DIR_WS_INSTALLER.'install_step1.php', 'action=convertnow', $request_type)).'\';
        var ajax_url = \''.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=restoredb&sql='.$_GET['sql'], $request_type).'\';
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
      
    } elseif ($_GET['action'] == 'convertnow' || $_GET['action'] == 'convertdb') {
      
      $engine_query = xtc_db_query("SELECT * FROM `engine`");
      $engine = xtc_db_fetch_array($engine_query);
      if ($engine['type'] != '') {
        if ($_GET['action'] == 'convertdb') {
          $convert = $_SESSION['convert'];
          if (isset($convert['tables'][$convert['aufruf']])) {
            xtc_db_query("ALTER TABLE ".$convert['tables'][$convert['aufruf']]." ENGINE = ".$engine['type']);
          }
          $convert['aufruf']++;
          $_SESSION['convert'] = $convert;
          
          $sec = time() - $convert['starttime']; 
          $time = sprintf('%d:%02d Min.', floor($sec/60), $sec % 60);
    
          $json_output = array();
          $json_output['aufruf'] = $convert['aufruf'];
          $json_output['table_ready'] = $convert['aufruf'];
          $json_output['time'] = $time;
          $json_output['actual_table'] = (($convert['aufruf'] > $convert['num_tables']) ? '' : $convert['tables'][$convert['aufruf']]);
          $json_output['fileEOF'] = (($convert['aufruf'] > $convert['num_tables']) ? 1 : 0);
          $json_output['nr'] = $convert['aufruf'];
          $json_output['num_tables'] = $convert['num_tables'];

          if ($json_output['fileEOF'])  {
            if (isset($_SESSION['convert'])) {
              unset($_SESSION['convert']);
            }
          }
          
          $json_output = json_encode($json_output);
          echo $json_output;
          exit();
        } elseif ($_GET['action'] == 'convertnow') {
          $convert = array(
            'starttime' => time(),
            'aufruf' => 0,
            'table' => array(),
          );
          $check_query = xtc_db_query("SHOW TABLES");
          while ($check = xtc_db_fetch_array($check_query)) {
            $convert['tables'][] = $check['Tables_in_'.DB_DATABASE];
          }
          $convert['num_tables'] = count($convert['tables']);
          $_SESSION['convert'] = $convert;
          
          $javascript = '
          <script type="text/javascript">
            var debug = true;
            var continue_url = \''.xtc_href_link(DIR_WS_INSTALLER.'install_step2.php', '', $request_type).'\';
            var ajax_url = \''.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=convertdb', $request_type).'\';
            var maxReloads = '.MAX_RELOADS.';
          </script>
          ';

          ob_start();
          $process = 'restore';
          require(DIR_FS_INSTALLER.'templates/javascript/jquery.database.js.php');
          $javascript .= ob_get_contents();
          ob_end_clean();
          $smarty->assign('JAVASCRIPT', $javascript);
      
          $smarty->assign('UPDATE_ACTION', 'convert');
          $smarty->assign('PROCESSING', 'db_restore');
          $smarty->clear_assign('BUTTON_SUBMIT');
          $smarty->clear_assign('BUTTON_BACK');
        }
      } else {
        xtc_redirect(xtc_href_link(DIR_WS_INSTALLER.'install_step2.php', '', $request_type));
      }
    }
  }
  
  if ($messageStack->size('install_step1') > 0) {
    $smarty->assign('error', $messageStack->output('install_step1'));
  }

  // database 
  $db_type_array = array();
  if (function_exists('mysqli_connect')) {
    $db_type_array[] = array('id' => 'mysqli', 'text' => 'MySQLi');
  }
  if (function_exists('mysql_connect')) {
    $db_type_array[] = array('id' => 'mysql', 'text' => 'MySQL');
  }

  $db_charset_array = array(
    array('id' => 'latin1', 'text' => 'ISO-8859-15'),
    array('id' => 'utf8', 'text' => 'UTF-8'),
  );
  $session_array = array(
    array('id' => 'mysql', 'text' => 'Datenbank'),
    array('id' => 'files', 'text' => 'Datei'),
  );
  $boolean_array = array(
    array('id' => 'true', 'text' => 'Ja'),
    array('id' => 'false', 'text' => 'Nein'),
  );
  $smarty->assign('INPUT_DB_SERVER', xtc_draw_input_fieldNote(array('name' => 'db_server')));
  $smarty->assign('INPUT_DB_USERNAME', xtc_draw_input_fieldNote(array('name' => 'db_username')));
  $smarty->assign('INPUT_DB_PASSWORD', xtc_draw_password_fieldNote(array('name' => 'db_password')));
  $smarty->assign('INPUT_DB_DATABSE', xtc_draw_input_fieldNote(array('name' => 'db_database')));    
  $smarty->assign('INPUT_DB_MYSQL_TYPE', xtc_draw_pull_down_menuNote(array ('name' => 'db_type'), $db_type_array, $db_type));
  $smarty->assign('INPUT_DB_CHARSET', xtc_draw_pull_down_menuNote(array ('name' => 'db_charset'), $db_charset_array, $db_charset));
  $smarty->assign('INPUT_DB_PCONNECT', xtc_draw_pull_down_menuNote(array ('name' => 'db_pconnect'), $boolean_array, $db_pconnect));
  
  // server
  $smarty->assign('INPUT_HTTP_SERVER', xtc_draw_input_fieldNote(array('name' => 'http_server')));    
  $smarty->assign('INPUT_HTTPS_SERVER', xtc_draw_input_fieldNote(array('name' => 'https_server')));    
  $smarty->assign('INPUT_SESSION', xtc_draw_pull_down_menuNote(array ('name' => 'session'), $session_array, $session));
  $smarty->assign('INPUT_USE_SSL', xtc_draw_pull_down_menuNote(array ('name' => 'use_ssl'), $boolean_array, $use_ssl));
  
  if ($upgrade === true) {
    $smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(DIR_WS_INSTALLER.'index.php', '', $request_type).'">'.BUTTON_BACK.'</a>');
  }
  if (isset($error) && $error === true) {
    $hidden_fields = xtc_draw_hidden_field('db_install', '1');
    foreach ($_POST as $key => $value) {
      $hidden_fields .= xtc_draw_hidden_field($key, $value);
    }
    $smarty->assign('INPUT_DB_INSTALL', $hidden_fields);
    $smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), '', $request_type).'">'.BUTTON_BACK.'</a>');
  }
  
  // form
  $smarty->assign('FORM_ACTION', xtc_draw_form('db_connection', xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), '', $request_type), 'post').xtc_draw_hidden_field('action', 'process').xtc_draw_hidden_field(xtc_session_name(), xtc_session_id()));
  $smarty->assign('BUTTON_SUBMIT', '<button type="submit">'.BUTTON_SUBMIT.'</button>');
  $smarty->assign('FORM_END', '</form>');

  $smarty->assign('language', $_SESSION['language']);
  $module_content = $smarty->fetch('install_step1.html');
  
  require ('includes/header.php');
  $smarty->assign('module_content', $module_content);
  $smarty->assign('logo', xtc_href_link(DIR_WS_INSTALLER.'images/logo_head.png', '', $request_type));
    
  if (!defined('RM')) {
    $smarty->load_filter('output', 'note');
  }
  $smarty->display('index.html');
  require_once ('includes/application_bottom.php');
?>