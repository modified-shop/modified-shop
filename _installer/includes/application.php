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
   (c) 2011 Strato document-root function v. 1.00 by web28 - www.rpa-cpm.de

   Released under the GNU General Public License
   --------------------------------------------------------------*/
  
  // clear apc for installer
  if (function_exists('apc_clear_cache')) {
    apc_clear_cache();
    apc_clear_cache('user');
  }

  // Set the level of error reporting
  @ini_set('display_errors', true);
  error_reporting(E_ALL & ~E_NOTICE);
  
  // configuration parameters
  include('includes/config.php');

  include('includes/version.php');

  if (version_compare(PHP_VERSION, '5.1.0', '>=')) {
    date_default_timezone_set('Europe/Berlin');
  }
    
  // Set FileSystem Directories
  if (!defined('DIR_FS_DOCUMENT_ROOT')) {   
    if (strpos($_SERVER['DOCUMENT_ROOT'],'strato') !== false) {
      define('DIR_FS_DOCUMENT_ROOT', rtrim(strato_document_root(),'/') . '/');
    } else {
      define('DIR_FS_DOCUMENT_ROOT', rtrim(detectDocumentRoot(),'/') .'/');
    }    
    define('DIR_FS_CATALOG', DIR_FS_DOCUMENT_ROOT);
  }
  if (!defined('DIR_FS_INC')) {
    define('DIR_FS_INC', DIR_FS_CATALOG.'inc/');
  }
  if (!defined('DIR_FS_INC_INSTALLER')) {
    define('DIR_FS_INC_INSTALLER', DIR_FS_CATALOG.DIR_MODIFIED_INSTALLER.'/inc/');
  }
  if (!defined('DIR_WS_BASE')) {
    define('DIR_WS_BASE',''); //web28 - 2010-12-13 - FIX for $messageStack icons //moved to application.php
  }

  // new error handling
  if (!defined('DIR_FS_LOG')) {
    define('DIR_FS_LOG', DIR_FS_CATALOG.'log/');
  }
  if (!defined('STORE_PARSE_DATE_TIME_FORMAT')) {
    define('STORE_PARSE_DATE_TIME_FORMAT', '%d/%m/%Y %H:%M:%S');
  }
  if (is_file(DIR_FS_CATALOG.'includes/error_reporting.php')) {
    require_once (DIR_FS_CATALOG.'includes/error_reporting.php');
  }

  // set admin directory
  require_once(DIR_FS_INC.'set_admin_directory.inc.php');

  // include General functions
  require_once(DIR_FS_INC.'auto_include.inc.php');
  require_once(DIR_FS_INC.'xtc_set_time_limit.inc.php');
  require_once(DIR_FS_INC.'xtc_check_agent.inc.php');
  require_once(DIR_FS_INC.'xtc_image.inc.php');
 
  require_once(DIR_FS_CATALOG.'includes/classes/boxes.php');
  require_once(DIR_FS_CATALOG.'includes/classes/message_stack.php');
  require_once(DIR_FS_CATALOG.'includes/filenames.php');
  require_once(DIR_FS_CATALOG.'includes/database_tables.php');
  
  define('CR', "\n");
  define('BOX_BGCOLOR_HEADING', '#bbc3d3');
  define('BOX_BGCOLOR_CONTENTS', '#f8f8f9');
  define('BOX_SHADOW', '#b6b7cb');

  // include Html output functions
  require_once(DIR_FS_INC_INSTALLER.'xtc_draw_input_field_installer.inc.php');
  require_once(DIR_FS_INC_INSTALLER.'xtc_draw_password_field_installer.inc.php');
  require_once(DIR_FS_INC_INSTALLER.'xtc_draw_hidden_field_installer.inc.php');
  require_once(DIR_FS_INC_INSTALLER.'xtc_draw_checkbox_field_installer.inc.php');
  require_once(DIR_FS_INC_INSTALLER.'xtc_draw_radio_field_installer.inc.php');
  require_once(DIR_FS_INC_INSTALLER.'xtc_draw_box_heading.inc.php');
  require_once(DIR_FS_INC_INSTALLER.'xtc_draw_box_contents.inc.php');
  require_once(DIR_FS_INC_INSTALLER.'xtc_draw_box_content_bullet.inc.php');

  // include check functions
  require_once(DIR_FS_INC_INSTALLER .'xtc_gdlib_check.inc.php');

  if (!defined('DIR_WS_ICONS')) {
    define('DIR_WS_ICONS','images/');
  }
  $char = '';
  if (isset($_GET['char']) && $_GET['char'] != '') {
    $char = $_GET['char'];
  }
  if (isset($_POST['char']) && $_POST['char'] != '') {
    $char = $_POST['char'];
  }
  if (defined('DB_SERVER_CHARSET') && DB_SERVER_CHARSET != '') {
    $char = DB_SERVER_CHARSET;
  }
  
  switch ($char) {
    case 'utf8':
      define('INSTALL_CHARSET', 'utf8');
      $charset = 'utf-8';
      $character_set = 'utf8';
      $collation = 'utf8_general_ci';
      break;
    default:
      define('INSTALL_CHARSET', 'latin1');
      $charset = 'iso-8859-15';
      $character_set = 'latin1';
      $collation = 'latin1_german1_ci';
      break;
  }
  if (!defined('DB_SERVER_CHARSET')) {
     define('DB_SERVER_CHARSET',$character_set);
  }

  // set default charset
  @ini_set('default_charset', $charset);

  $lang = '';
  if (isset($_GET['lg']) && $_GET['lg'] != '') {
    $lang = $_GET['lg'];
  }
  if (isset($_POST['lg']) && $_POST['lg'] != '') {
    $lang = $_POST['lg'];
  }
  if ($lang == '' || ($lang != 'german' && $lang != 'english')) {
    //BOF - DokuMan - 2010-08-16 - Set browser language on installer start page
    preg_match("/^([a-z]+)-?([^,;]*)/i", $_SERVER['HTTP_ACCEPT_LANGUAGE'], $browser_lang);
    switch (strtolower($browser_lang[1])) {
      case 'de':
        $lang = 'german';
        break;
      default:
        $lang = 'english';
        break;
    }
    //EOF - DokuMan - 2010-08-16 - Set browser language on installer start page
  }
 //include('language/'.$lang.'.php');
  $input_lang  = '<input type="hidden" name="lg" value="'. $lang .'">';
  $input_lang .= '<input type="hidden" name="char" value="'. INSTALL_CHARSET .'">';
  //EOF - web28 - 2010.02.09 - FIX LOST SESSION

/*########### FUNCTIONS #############*/

  // Determine Document Root
  function detectDocumentRoot() {
    $dir_fs_www_root = realpath(dirname(basename(__FILE__)) . "/..");
    if ($dir_fs_www_root == '') $dir_fs_www_root = '/';
    $dir_fs_www_root = str_replace(array('\\','//'), '/', $dir_fs_www_root);
    return $dir_fs_www_root;
  }

  //BOF - web28 - 2011-05-06 - NEW Strato document-root function
  function strato_document_root() {
    // subdomain entfernen
    $domain = $_SERVER["HTTP_HOST"];
    $tmp = explode ('.',$domain);
    if (count($tmp) > 2) {
      $domain = str_replace($tmp[0].'.','',$domain);
    }
    $document_root = str_replace($_SERVER["PHP_SELF"],'',$_SERVER["SCRIPT_FILENAME"]);
    //Unterverzeichnis ermitteln
    $tmp = explode(DIR_MODIFIED_INSTALLER, $_SERVER["PHP_SELF"]);
    $subdir = $tmp[0];
    //Prüfen ob Domain im Pfad enthalten ist, wenn nein Pfad Stratopfad erzeugen: /home/strato/www/ersten zwei_buchstaben/www.wunschname.de/htdocs/
    if(stristr($document_root, $domain) === FALSE) {
      //Korrektur Unterverzeichnis      
      $htdocs = str_replace($_SERVER["SCRIPT_NAME"],'',$_SERVER["SCRIPT_FILENAME"]);
      $htdocs = '/htdocs' . str_replace($_SERVER["DOCUMENT_ROOT"],'',$htdocs);
      //MUSTER: /home/strato/www/wu/www.wunschname.de/htdocs/
      $document_root = '/home/strato/www/'.substr($domain, 0, 2). '/www.'.$domain.$htdocs.$subdir;
    } else {
      $document_root .= $subdir;
    }
    return $document_root;
  }
  //EOF - web28 - 2011-05-06 - NEW Strato document-root function

  function draw_hidden_fields() {
    reset($_POST);
    $hidden_fields = '';
    while (list($key, $value) = each($_POST)) {
      if ($key != 'x' && $key != 'y' && $key != 'lg' && $key != 'char') {
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
  
  function phpLinkCheck($url, $r = false) {
    /*  Purpose: Check HTTP Links
     *  Usage:   $var = phpLinkCheck(absoluteURI)
     *           $var["Status-Code"] will return the HTTP status code
     *           (e.g. 200 or 404). In case of a 3xx code (redirection)
     *           $var["Location-Status-Code"] will contain the status
     *           code of the new loaction.
     *           See print_r($var) for the complete result
     *
     *  Author:  Johannes Froemter <j-f@gmx.net>
     *  Date:    2001-04-14
     *  Version: 0.1 (currently requires PHP4)
     */

    $url = trim($url);

    //http oder https entfernen
    $http = array('http://', 'https://');
    $urltest = str_replace($http,'',$url);
    //Auf // testen
    if (strpos($urltest, '//') !== false)
      return false;
    //Auf falsches Installer Verzeichnis testen
    if (strpos($urltest, DIR_MODIFIED_INSTALLER) !== false)
      return false;

    if (!preg_match("=://=", $url))
      $url = "http://$url";
    $url = parse_url($url);
    $http["Parsed_URL"] = $url;
    if (strtolower($url["scheme"]) != "http")
      return FALSE;
    if (!isset($url["port"]))
      $url["port"] = 80;
    if (!isset($url["path"]))
      $url["path"] = "/";
    $fp = @fsockopen($url["host"], $url["port"], $errno, $errstr, 30);
    if (!$fp) {
      $http["Status-Code"] = '550';  // unknown host // FALSE;
      return $http;
    } else {
      $head = "";
      $httpRequest = "HEAD ". $url["path"] ." HTTP/1.1\r\n"
                    ."Host: ". $url["host"] ."\r\n"
                    ."Connection: close\r\n\r\n";
      fputs($fp, $httpRequest);

      while(!feof($fp)) {
        $head .= fgets($fp, 1024);
      }
      fclose($fp);
      preg_match("=^(HTTP/\d+\.\d+) (\d{3}) ([^\r\n]*)=", $head, $matches);
      $http["Status-Line"] = $matches[0];
      $http["HTTP-Version"] = $matches[1];
      $http["Status-Code"] = $matches[2];
      $http["Reason-Phrase"] = $matches[3];
      if ($r) {
        return $http["Status-Code"];
      }
      $rclass = array("Informational", "Success",
                      "Redirection", "Client Error",
                      "Server Error");
      $http["Response-Class"] = $rclass[$http["Status-Code"][0] - 1];
      preg_match_all("=^(.+): ([^\r\n]*)=m", $head, $matches, PREG_SET_ORDER);
      foreach($matches as $line) {
        $http[$line[1]] = $line[2];
      }
      if ($http["Status-Code"][0] == 3) {
        $http["Location-Status-Code"] = phpLinkCheck($http["Location"], true);
      }
      return $http;
    }
  }
  
  function convert_utf8($string)
  {
    if (INSTALL_CHARSET == 'utf8') {          
      $string = mb_convert_encoding($string, 'utf-8', 'ISO-8859-15');
    }
    return $string;
  
  }
?>