<?php
  /* --------------------------------------------------------------
   $Id: application.php 3072 2012-06-18 15:01:13Z hhacker $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(application.php,v 1.4 2002/11/29); www.oscommerce.com
   (c) 2003	nextcommerce (application.php,v 1.16 2003/08/13); www.nextcommerce.org
   (c) 2006 xt:Commerce (application.php 1119 2005-07-25); www.xtcommerce.com

   Released under the GNU General Public License
  (c) 2011 Strato document-root function v. 1.00 by web28 - www.rpa-cpm.de
   --------------------------------------------------------------*/

  define('INSTALL_CHARSET', 'iso-8859-15'); //iso-8859-15 oder utf-8
  define('DIR_MODIFIED_INSTALLER', '_installer');
  define('MODIFIED_SQL', 'modified.sql');
  
  /*######################################*/
  
  // Set the level of error reporting
  error_reporting(E_ALL & ~E_NOTICE);
  
  if (INSTALL_CHARSET == 'utf-8') {
    $charset = 'utf-8';
    $character_set = 'utf-8';
    $collation = 'utf8_general_ci';
  } else {
    $charset = 'iso-8859-15';
    $character_set = 'latin1';
    $collation = 'latin1_german1_ci'; 
  }

  if (version_compare(PHP_VERSION, '5.1.0', '>=')) {
    date_default_timezone_set('Europe/Berlin');
  }
  // Some FileSystem Directories
  if (!defined('DIR_FS_DOCUMENT_ROOT')) {
    //BOF - web28 - 2010.02.18 - STRATO ROOT PATCH
    if (strpos($_SERVER['DOCUMENT_ROOT'],'strato') !== FALSE) {
      //BOF -  web28 - 2011-05-06 - NEW Strato document-root function
      define('DIR_FS_DOCUMENT_ROOT', rtrim(strato_document_root(),'/'));
      //EOF -  web28 - 2011-05-06 - NEW Strato document-root function
    } else {
      define('DIR_FS_DOCUMENT_ROOT', rtrim($_SERVER['DOCUMENT_ROOT'],'/'));
    }
    //EOF - web28 - 2010.02.18 - STRATO ROOT PATCH
    $local_install_path=str_replace('/'.DIR_MODIFIED_INSTALLER,'',$_SERVER['PHP_SELF']);
    $local_install_path=str_replace('index.php','',$local_install_path);
    $local_install_path=str_replace('install_step1.php','',$local_install_path);
    $local_install_path=str_replace('install_step2.php','',$local_install_path);
    $local_install_path=str_replace('install_step3.php','',$local_install_path);
    $local_install_path=str_replace('install_step4.php','',$local_install_path);
    $local_install_path=str_replace('install_step5.php','',$local_install_path);
    $local_install_path=str_replace('install_step6.php','',$local_install_path);
    $local_install_path=str_replace('install_step7.php','',$local_install_path);
    $local_install_path=str_replace('install_finished.php','',$local_install_path);
    define('DIR_FS_CATALOG', DIR_FS_DOCUMENT_ROOT . $local_install_path);
  }
  if (!defined('DIR_FS_INC')) {
    define('DIR_FS_INC', DIR_FS_CATALOG.'inc/');
  }
  if (!defined('DIR_WS_BASE')) {
    define('DIR_WS_BASE',''); //web28 - 2010-12-13 - FIX for $messageStack icons //moved to application.php
  }

  //require('../includes/functions/validations.php');
  require(DIR_FS_CATALOG.'includes/classes/boxes.php');
  require(DIR_FS_CATALOG.'includes/classes/message_stack.php');
  require(DIR_FS_CATALOG.'includes/filenames.php');
  require(DIR_FS_CATALOG.'includes/database_tables.php');
  require_once(DIR_FS_CATALOG.'inc/xtc_image.inc.php');

  // Start the Install_Session
  session_start();

  define('CR', "\n");
  define('BOX_BGCOLOR_HEADING', '#bbc3d3');
  define('BOX_BGCOLOR_CONTENTS', '#f8f8f9');
  define('BOX_SHADOW', '#b6b7cb');

  // include General functions
  require_once(DIR_FS_INC.'xtc_set_time_limit.inc.php');
  require_once(DIR_FS_INC.'xtc_check_agent.inc.php');

  // include Database functions for installer
  require_once(DIR_FS_INC.'xtc_db_prepare_input.inc.php');
  require_once(DIR_FS_INC.'xtc_db_connect_installer.inc.php');
  require_once(DIR_FS_INC.'xtc_db_select_db.inc.php');
  require_once(DIR_FS_INC.'xtc_db_close.inc.php');
  require_once(DIR_FS_INC.'xtc_db_query_installer.inc.php');
  require_once(DIR_FS_INC.'xtc_db_fetch_array.inc.php');
  require_once(DIR_FS_INC.'xtc_db_num_rows.inc.php');
  require_once(DIR_FS_INC.'xtc_db_data_seek.inc.php');
  require_once(DIR_FS_INC.'xtc_db_insert_id.inc.php');
  require_once(DIR_FS_INC.'xtc_db_free_result.inc.php');
  require_once(DIR_FS_INC.'xtc_db_test_create_db_permission.inc.php');
  require_once(DIR_FS_INC.'xtc_db_test_connection.inc.php');
  require_once(DIR_FS_INC.'xtc_db_install.inc.php');

  // include Html output functions
  require_once(DIR_FS_INC.'xtc_draw_input_field_installer.inc.php');
  require_once(DIR_FS_INC.'xtc_draw_password_field_installer.inc.php');
  require_once(DIR_FS_INC.'xtc_draw_hidden_field_installer.inc.php');
  require_once(DIR_FS_INC.'xtc_draw_checkbox_field_installer.inc.php');
  require_once(DIR_FS_INC.'xtc_draw_radio_field_installer.inc.php');
  require_once(DIR_FS_INC.'xtc_draw_box_heading.inc.php');
  require_once(DIR_FS_INC.'xtc_draw_box_contents.inc.php');
  require_once(DIR_FS_INC.'xtc_draw_box_content_bullet.inc.php');

  // include check functions
  require_once(DIR_FS_INC .'xtc_gdlib_check.inc.php');

  if (!defined('DIR_WS_ICONS')) {
    define('DIR_WS_ICONS','images/');
  }

  function xtc_check_version($mini='4.1.2') {
    $dummy=phpversion();
    sscanf($dummy,"%d.%d.%d%s",$v1,$v2,$v3,$v4);
    sscanf($mini,"%d.%d.%d%s",$m1,$m2,$m3,$m4);
    if($v1>$m1)
      return(1);
    elseif($v1<$m1)
      return(0);
    if($v2>$m2)
      return(1);
    elseif($v2<$m2)
      return(0);
    if($v3>$m3)
      return(1);
    elseif($v3<$m3)
      return(0);
    if((!$v4)&&(!$m4))
      return(1);
    if(($v4)&&(!$m4)) {
      $dummy=strpos($v4,"pl");
      if(is_integer($dummy))
        return(1);
      return(0);
    } elseif((!$v4)&&($m4)) {
      $dummy=strpos($m4,"rc");
      if(is_integer($dummy))
        return(1);
      return(0);
    }
    return(0);
  }

  //BOF - web28 - 2010.02.09 - FIX LOST SESSION
  if (isset($_SESSION['language']) && $_SESSION['language'] != '') {
    $lang = $_SESSION['language'];
  } else {
    //BOF - DokuMan - 2010-08-16 - Set browser language on installer start page
    preg_match("/^([a-z]+)-?([^,;]*)/i", $_SERVER['HTTP_ACCEPT_LANGUAGE'], $browser_lang);
    switch ($browser_lang[1]) {
      case 'de':
        $lang = 'german';
        break;
      default:
        $lang = 'english';
        break;
    }
    //EOF - DokuMan - 2010-08-16 - Set browser language on installer start page
    if (isset($_GET['lg']) && $_GET['lg'] != '') {
      $lang = $_GET['lg'];
    }
    if (isset($_POST['lg']) && $_POST['lg'] != '') {
      $lang = $_POST['lg'];
    }
  }
  //include('language/'.$lang.'.php');
  $input_lang = '<input type="hidden" name="lg" value="'. $lang .'">';
  //EOF - web28 - 2010.02.09 - FIX LOST SESSION

/*########### FUNCTIONS #############*/

  //BOF - web28 - 2011-05-06 - NEW Strato document-root function
  function strato_document_root() {
    // subdomain entfernen
    $domain = $_SERVER["HTTP_HOST"];
    $tmp = explode ('.',$domain);
    if (count($tmp) > 2) {
      $domain = str_replace($tmp[0].'.','',$domain);
    }
    $document_root = str_replace($_SERVER["PHP_SELF"],'',$_SERVER["SCRIPT_FILENAME"]);
    //Prüfen ob Domain im Pfad enthalten ist, wenn nein Pfad Stratopfad erzeugen: /home/strato/www/ersten zwei_buchstaben/www.wunschname.de/htdocs/
    if(stristr($document_root, $domain) === FALSE) {
      //Erste 2 Buchstaben der Domain ermittlen
      $domain2 = substr($tmp[count($tmp)-2], 0, 2);
      //Korrektur Unterverzeichnis
      $htdocs = str_replace($_SERVER["SCRIPT_NAME"],'',$_SERVER["SCRIPT_FILENAME"]);
      $htdocs = '/htdocs' . str_replace($_SERVER["DOCUMENT_ROOT"],'',$htdocs);
      //MUSTER: /home/strato/www/wu/www.wunschname.de/htdocs/
      $document_root = '/home/strato/www/'.$domain2. '/www.'.$domain.$htdocs;
    }
    return $document_root;
  }
  //EOF - web28 - 2011-05-06 - NEW Strato document-root function

  function draw_hidden_fields() {
    reset($_POST);
    $hidden_fields = '';
    while (list($key, $value) = each($_POST)) {
      if ($key != 'x' && $key != 'y') {
        if (is_array($value)) {
          for ($i=0; $i<sizeof($value); $i++) {
            $hidden_fields .= xtc_draw_hidden_field_installer($key . '[]', $value[$i]).PHP_EOL;
          }
        } else {
          $hidden_fields .= xtc_draw_hidden_field_installer($key, $value).PHP_EOL;
        }
      }
    }
    return $hidden_fields;
  }
?>