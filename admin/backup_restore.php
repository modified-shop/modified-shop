<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2011 (c) by  web28 - www.rpa-com.de

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  define ('_VALID_XTC', true);
  define('RUN_MODE_ADMIN',true);

  // no error reporting
  error_reporting(0);

  // Set the local configuration parameters
  if (file_exists('../includes/local/configure.php')) {
    include('../includes/local/configure.php');
  } else {
    require('../includes/configure.php');
  }

  // include functions
  require_once(DIR_FS_INC.'auto_include.inc.php');
  require_once(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'database_tables.php');
  require_once(DIR_FS_ADMIN.DIR_WS_FUNCTIONS.'general.php');

  // Database
  require_once(DIR_FS_INC.'db_functions_'.DB_MYSQL_TYPE.'.inc.php');
  require_once(DIR_FS_INC.'db_functions.inc.php');
  
  xtc_db_connect() or die('Unable to connect to database server!');

  //Start Session
  @ini_set('session.use_only_cookies', 1);
  require(DIR_WS_FUNCTIONS . 'sessions.php');

  // set the session name and save path
  xtc_session_name('MODsid');
  if (STORE_SESSIONS == '') {
    define('SESSION_WRITE_DIRECTORY', sys_get_temp_dir());
    xtc_session_save_path(SESSION_WRITE_DIRECTORY);
  }
  $session_started = xtc_session_start();  
  
  // verfiy SECURE Token
  if (is_array($_POST) && count($_POST) > 0) {
    if (isset($_POST[$_SESSION['SECName']])) {
      if ($_POST[$_SESSION['SECName']] != $_SESSION['SECToken']) {
        trigger_error("SECToken manipulation.\n".print_r($_POST, true), E_USER_WARNING);
        unset($_POST);
        unset($_GET['action']);
        unset($_GET['saction']);
        die('Direct Access to this location is not allowed.');

      }
    } else {
      trigger_error("SECToken not defined.\n".print_r($_POST, true), E_USER_WARNING);
      unset($_POST);
      unset($_GET['action']);
      unset($_GET['saction']);
      die('Direct Access to this location is not allowed.');
    }
  } elseif (!isset($_SESSION['SECName']) || !isset($_SESSION['SECToken'])) {
    die('Direct Access to this location is not allowed.');
  }
  
  // set the language
  if (!isset($_SESSION['language']) || isset($_GET['language']) || (isset($_SESSION['language']) && !isset($_SESSION['language_charset']))) {
    require_once (DIR_WS_CLASSES.'language.php');
    if (isset($_GET['language'])) {
      $_GET['language'] = xtc_input_validation($_GET['language'], 'lang');
      $lng = new language($_GET['language']);
    } elseif (isset($_SESSION['language'])) {
      $lng = new language(xtc_input_validation($_SESSION['language'], 'lang'));
    } else {
      $lng = new language(xtc_input_validation(DEFAULT_LANGUAGE, 'lang'));
      if (defined('USE_BROWSER_LANGUAGE') && USE_BROWSER_LANGUAGE == 'true') {
        $lng->get_browser_language();
      }
    }
    $_SESSION['language'] = $lng->language['directory'];
    $_SESSION['languages_id'] = $lng->language['id'];
    $_SESSION['language_charset'] = $lng->language['language_charset'];
    $_SESSION['language_code'] = $lng->language['code'];
  }

  // include the language translations
  require(DIR_FS_LANGUAGES . $_SESSION['language'] . '/admin/'.$_SESSION['language'] . '.php');
  require(DIR_FS_LANGUAGES . $_SESSION['language'] . '/admin/buttons.php');
  if (file_exists(DIR_FS_LANGUAGES . $_SESSION['language'] . '/admin/'.'backup_db.php')) {
    include(DIR_FS_LANGUAGES . $_SESSION['language'] . '/admin/'. 'backup_db.php');
  }

  if (!defined('TITLE')) {
    define('TITLE', HEADING_TITLE);
  }
  include ('includes/functions/db_functions.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  //Animierte Gif-Datei und Hinweistext
  $info_wait = '<img src="images/loading.gif"> '. TEXT_INFO_WAIT ;
  $button_back = '';

  include ('includes/db_actions.php');
  
  require (DIR_WS_INCLUDES.'head.php');
?>
<link rel="stylesheet" type="text/css" href="includes/css/backup_db.css">
<script type="text/javascript">
  //Check if jQuery is loaded
  !window.jQuery && document.write('<script src="includes/javascript/jquery-1.8.3.min.js" type="text/javascript"><\/script>');
  $(document).ready(function() {
    document.title = "<?php echo HEADING_TITLE; ?>";
  });
</script>
</head>
  <body>
    <table class="tableBody">
      <tr>
        <!-- body_text //-->
         <td class="boxCenter"> 
           <div class="pageHeading pdg2"><?php echo HEADING_TITLE; ?><span class="smallText"> [<?php echo VERSION; ?>]</span></div>
           <div class="main txta-c">
             <div id="info_text" class="pageHeading txta-c mrg10"><?php echo $info_text; ?></div>
             <div id="info_wait" class="pageHeading txta-c mrg10" style="margin-top:20px;"><?php echo $info_wait; ?></div>
             <div style="clear:both;"></div>
             <div class="process_wrapper" style="display:none;">
                  <div class="process_inner_wrapper">
                    <div id="backup_process"></div>
                  </div>
                  <div id="backup_precents">0%</div>
                </div>
             <div id="data_ok" class="main txta-c" style="margin-top:30px;"></div>
             <div id="button_back" class="main txta-c" style="margin-top:20px;"></div>
             <div id="button_log" class="main txta-c" style="margin-top:10px;"></div>
             <div style="clear:both"></div>
          </div>       
        </td>
        <!-- body_text_eof //-->
      </tr>
    </table>
    <!-- body_eof //-->
    <?php
    require (DIR_WS_INCLUDES.'javascript/jquery.backup_restore.js.php');
    ?>
  </body>
</html>