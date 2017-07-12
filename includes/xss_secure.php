<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Based on:
   (c) 2001 by the Post-Nuke Development Team - http://www.postnuke.com/
   (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   Original Author of file: Jim McDonald
   Purpose of file: The PostNuke API

   Protects better diverse attempts of Cross-Site Scripting attacks
   thanks to webmedic, Timax, larsneo.
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------*/
   
//TEST: newsletter.php?email=%3C/script%3E%3Cscript%3Ealert%281%29%3C/script%3E
//############  KONFIGURATION ##############//
define('XSS_SEND_LOG', false); //default: false
define('XSS_WRITE_LOG', true); //default: true
define('XSS_BLACKLIST', true); //default: true
define('XSS_BLACKLIST_TIME', 3600); // time to block IP in seconds. default: 3600
//############  KONFIGURATION ##############//


function xss_secure($params_arr, $ip, $type) 
{
    foreach ($params_arr as $secvalue) 
    {
        if (!is_array($secvalue)) {
         #hs - speed improvement
         #stackoverflow.com/questions/14342427
          if (strpos($secvalue, '<') !== false ||
              ( (
                strpos($secvalue, '=') !== false ||
                stripos($secvalue, 'like') !== false
              ) && (
                stripos($secvalue, ' or ') !== false ||
                stripos($secvalue, ' and ') !== false
              ) )
             )#hs
          xss_secure_params($secvalue, $ip, $type);
        } else {
          xss_secure($secvalue, $ip, $type);
        }
    }
}


function xss_secure_params($secvalue, $ip, $type) 
{
    $error = false;
    switch ($type) 
    {    
        case 'get':
            if ((preg_match("/<[^>]*script.*\"?[^>]*>/i", $secvalue)) ||
                (preg_match("/.*[[:space:]](or|and)[[:space:]].*(=|like).*/i", $secvalue)) ||
                (preg_match("/<[^>]*object.*\"?[^>]*>/i", $secvalue)) ||
                (preg_match("/<[^>]*iframe.*\"?[^>]*>/i", $secvalue)) ||
                (preg_match("/<[^>]*applet.*\"?[^>]*>/i", $secvalue)) ||
                (preg_match("/<[^>]*meta.*\"?[^>]*>/i", $secvalue)) ||
                (preg_match("/<[^>]*style.*\"?[^>]*>/i", $secvalue)) ||
                (preg_match("/<[^>]*form.*\"?[^>]*>/i", $secvalue)) ||
                (preg_match("/<[^>]*window.*\"?[^>]*>/i", $secvalue)) ||
                (preg_match("/<[^>]*alert.*\"?[^>]*>/i", $secvalue)) ||
                (preg_match("/<[^>]*img.*\"?[^>]*>/i", $secvalue)) ||
                (preg_match("/<[^>]*document.*\"?[^>]*>/i", $secvalue)) ||
                (preg_match("/(.*)select(.*)/i", $secvalue)) ||
                (preg_match("/(.*)concat(.*)/i", $secvalue)) ||
                (preg_match("/<[^>]*cookie.*\"?[^>]*>/i", $secvalue))) 
            {
                $error = true;
            }
            break;

        case 'post':        
            if ((preg_match("/<[^>]*script.*\"?[^>]*>/i", $secvalue)) ||
                (preg_match("/<[^>]*object.*\"?[^>]*>/i", $secvalue)) ||
                (preg_match("/<[^>]*iframe.*\"?[^>]*>/i", $secvalue)) ||
                (preg_match("/<[^>]*applet.*\"?[^>]*>/i", $secvalue)) ||
                (preg_match("/<[^>]*window.*\"?[^>]*>/i", $secvalue)) ||
                (preg_match("/<[^>]*alert.*\"?[^>]*>/i", $secvalue)) ||
                (preg_match("/<[^>]*document.*\"?[^>]*>/i", $secvalue)) ||
                (preg_match("/<[^>]*cookie.*\"?[^>]*>/i", $secvalue)) ||
                (preg_match("/(.*)select(.*)/i", $secvalue)) ||
                (preg_match("/(.*)concat(.*)/i", $secvalue)) ||
                (preg_match("/<[^>]*meta.*\"?[^>]*>/i", $secvalue))) 
            {
                $error = true;
            }
            break;

        case 'cookie':        
            if ((preg_match("/<[^>]*script.*\"?[^>]*>/i", $secvalue)) ||
                (preg_match("/.*[[:space:]](or|and)[[:space:]].*(=|like).*/i", $secvalue)) ||
                (preg_match("/<[^>]*object.*\"?[^>]*>/i", $secvalue)) ||
                (preg_match("/<[^>]*iframe.*\"?[^>]*>/i", $secvalue)) ||
                (preg_match("/<[^>]*applet.*\"?[^>]*>/i", $secvalue)) ||
                (preg_match("/<[^>]*meta.*\"?[^>]*>/i", $secvalue)) ||
                (preg_match("/<[^>]*style.*\"?[^>]*>/i", $secvalue)) ||
                (preg_match("/<[^>]*form.*\"?[^>]*>/i", $secvalue)) ||
                (preg_match("/<[^>]*window.*\"?[^>]*>/i", $secvalue)) ||
                (preg_match("/<[^>]*alert.*\"?[^>]*>/i", $secvalue)) ||
                (preg_match("/<[^>]*document.*\"?[^>]*>/i", $secvalue)) ||
                (preg_match("/<[^>]*cookie.*\"?[^>]*>/i", $secvalue)) ||
                (preg_match("/(.*)select(.*)/i", $secvalue)) ||
                (preg_match("/(.*)concat(.*)/i", $secvalue)) ||
                (preg_match("/<[^>]*img.*\"?[^>]*>/i", $secvalue))) 
            {
                $error = true;
            }
            break;
      }
      
      // write log
      if ($error === true) {
          if (defined('XSS_WRITE_LOG') && XSS_WRITE_LOG === true) { 
            xss_log_hack_attempt(__FILE__, __LINE__, 'modified eCommerce Shopsoftware - Security Alert', 'Intrusion detection.');
          }
          if (defined('XSS_BLACKLIST') && XSS_BLACKLIST === true) { 
            xss_add_blacklist($ip);
            //Redirect
            header("Location: ".XSS_BASE."error.html");
          } else {
            //Redirect
            header("Location: ".XSS_BASE."index.php");
          }
          exit();
      }
}


function xss_log_hack_attempt($detecting_file = "(no filename available)",
                              $detecting_line = "(no line number available)",
                              $hack_type = "(no type given)",
                              $message = "(no message given)" ) {
                               
    $url = $_SERVER['HTTP_HOST'].preg_replace("/([^\?]*)(\?.*)/","$1",$_SERVER['REQUEST_URI']);
    $output         =        "#####################################\n";
    $output        .=        @strftime('%Y-%m-%d %H:%M:%S')."\n";
    $output        .=        "URL: ".$url."\n";
    $output        .=        "#####################################\n";
    $output        .=        "The modified Shopsoftware has detected that somebody tried to"
                            ." send information to your site that may have been intended"
                            ." as a hack.\nDo not panic, it may be harmless: maybe this"
                            ." detection was triggered by something you did! Anyway, it"
                            ." was detected and blocked. \n";
    $output        .=        "The suspicious activity was recognized in $detecting_file "
                            ."on line $detecting_line, and is of the type $hack_type. \n";
    $output        .=        "Additional information given by the code which detected this: ".$message;
    $output        .=        "\n\nBelow you will find a lot of information obtained about "
                            ."this attempt, that may help you to find  what happened and "
                            ."maybe who did it.\n\n";

    $output        .=        "\n=====================================\n";
    $output        .=        "Information about this user:\n";
    $output        .=        "=====================================\n";

    if (!isset($_SESSION['customer_id'])) {
        $output    .=        "This person is not logged in.\n";
    }  else {
        $output    .=        "This person is logged in!!\n Customers ID =".$_SESSION['customer_id'];

    }

    $output        .=        "IP numbers: [note: when you are dealing with a real cracker "
                            ."these IP numbers might not be from the actual computer he is "
                            ."working on]"
                            ."\n\t IP according to HTTP_CLIENT_IP: ".$_SERVER['HTTP_CLIENT_IP']
                            ."\n\t IP according to REMOTE_ADDR: ".$_SERVER['REMOTE_ADDR']
                            ."\n\t IP according to GetHostByName(".$_SERVER['REMOTE_ADDR']."): ".@GetHostByName($_SERVER['REMOTE_ADDR'])
                            ."\n\n";

    $output        .=        "\n=====================================\n";
    $output        .=        "Information in the \$_REQUEST array\n";
    $output        .=        "=====================================\n";

    while ( list ( $key, $value ) = @each ( $_REQUEST ) ) {
        $output    .=        "REQUEST * $key : $value\n";
    }

    $output        .=        "\n=====================================\n";
    $output        .=        "Information in the \$_GET array\n";
    $output        .=        "This is about variables that may have been ";
    $output        .=        "in the URL string or in a 'GET' type form.\n";
    $output        .=        "=====================================\n";

    while ( list ( $key, $value ) = @each ( $_GET ) ) {
       $output     .=        "GET * $key : $value\n";
    }

    $output        .=        "\n=====================================\n";
    $output        .=        "Information in the \$_POST array\n";
    $output        .=        "This is about visible and invisible form elements.\n";
    $output        .=        "=====================================\n";

    while ( list ( $key, $value ) = @each ( $_POST ) ) {
        $output    .=        "POST * $key : $value\n";
    }

    $output        .=        "\n=====================================\n";
    $output        .=        "Browser information\n";
    $output        .=        "=====================================\n";

    $output        .=        "HTTP_USER_AGENT: ".$_SERVER['HTTP_USER_AGENT'] ."\n";

    $browser = (array) @get_browser();
    while ( list ( $key, $value ) = @each ( $browser ) ) {
        $output    .=        "BROWSER * $key : $value\n";
    }

    $output        .=        "\n=====================================\n";
    $output        .=        "Information in the \$_SERVER array\n";
    $output        .=        "=====================================\n";

    while ( list ( $key, $value ) = @each ( $_SERVER ) ) {
        $output    .=        "SERVER * $key : $value\n";
    }

    $output        .=        "\n=====================================\n";
    $output        .=        "Information in the \$_ENV array\n";
    $output        .=        "=====================================\n";

    while ( list ( $key, $value ) = @each ( $_ENV ) ) {
        $output    .=        "ENV * $key : $value\n";
    }

    $output        .=        "\n=====================================\n";
    $output        .=        "Information in the \$_COOKIE array\n";
    $output        .=        "=====================================\n";

    while ( list ( $key, $value ) = @each ( $_COOKIE ) )  {
        $output    .=        "COOKIE * $key : $value\n";
    }

    $output        .=        "\n=====================================\n";
    $output        .=        "Information in the \$_FILES array\n";
    $output        .=        "=====================================\n";

    while ( list ( $key, $value ) = @each ( $_FILES ) ) {
        $output    .=        "FILES * $key : $value\n";
    }

    $output        .=        "\n=====================================\n";
    $output        .=        "Information in the \$_SESSION array\n";
    $output        .=        "This is session info.\n";
    $output        .=        "=====================================\n";

    while ( list ( $key, $value ) = @each ( $_SESSION ) ) {
        $output    .=        "SESSION * $key : $value\n";
    }
    
    xss_write_log($output);
}


function xss_write_log($text)
{
  $log_file = XSS_PATH.'log/xss_attacks_'.date('Y-m-d').'.log.gz';
  $fp = @gzopen($log_file,'a');
  @gzwrite($fp,$text . "\r\n");
  @gzclose($fp);
  
  if (XSS_SEND_LOG === true) {
    file_put_contents(XSS_PATH.'log/xss_attacks_'.time().'.mail', $text);
  }
}


function xss_add_blacklist($ip)
{
  global $blacklist_arr;
  
  defined('CHECK_CLIENT_AGENT') OR define('CHECK_CLIENT_AGENT', 'true');
  require_once (XSS_PATH.'inc/xtc_check_agent.inc.php');
  
  $_SERVER['HTTP_USER_AGENT'] = gethostbyaddr($ip);

  if (xtc_check_agent() == 0) {
    $blacklist_arr[$ip] = time();
    xss_write_blacklist($blacklist_arr);
  }
}


function xss_write_blacklist($blacklist_arr)
{
  $blacklist_file = XSS_PATH.'log/xss_blacklist.log';  
  $fp = fopen($blacklist_file, 'w');
  flock($fp, LOCK_EX);
  ftruncate($fp, 0);
  rewind($fp);
  foreach(array_keys($blacklist_arr) as $key){    
    @fwrite($fp, $key . ';' . $blacklist_arr[$key] . "\r\n");
  }
  flock($fp, LOCK_UN);
  @fclose($fp);
}


function xss_read_blacklist()
{
  $blacklist_arr = array();
  $blacklist_file = XSS_PATH.'log/xss_blacklist.log';
  if (is_file($blacklist_file)) {
    $fp = fopen($blacklist_file, 'r');
    flock($fp, LOCK_EX);
    
    $count = 0;
    while (($blacklist_val = @fgetcsv($fp, 4096, ';')) !== false) {
      if (is_array($blacklist_val) && count($blacklist_val) == 2) {
        if (($blacklist_val[1]+XSS_BLACKLIST_TIME) > time()) {
          $blacklist_arr[$blacklist_val[0]] = $blacklist_val[1];
        }
      }
      $count ++;
    }
    flock($fp, LOCK_UN);
    fclose($fp);
    
    if (count($blacklist_arr) != $count) {
      xss_write_blacklist($blacklist_arr);
    }
  }
  return $blacklist_arr;
}

// here comes the action
error_reporting(0);
define('XSS_PATH', str_replace('\\', '/', dirname(dirname(__FILE__))) . '/');

require_once (XSS_PATH.'inc/set_php_self.inc.php');

// set base 
$ssl_proxy = ((isset($_SERVER['HTTP_X_FORWARDED_HOST'])) ? '/' . $_SERVER['HTTP_HOST'] : ''); 
define('XSS_BASE', $ssl_proxy . preg_replace('/\\' . DIRECTORY_SEPARATOR . '\/|\/\//', '/', dirname(set_php_self()) . '/')); 

$ip = '';
if (defined('XSS_BLACKLIST') && XSS_BLACKLIST) {
  require_once (XSS_PATH.'inc/xtc_get_ip_address.inc.php');
  $ip = xtc_get_ip_address();

  $blacklist_arr = xss_read_blacklist();
  if (isset($blacklist_arr[$ip])) {
    //Redirect
    header("Location: ".XSS_BASE."error.html");
    exit();
  }
}

// function call
if (isset($_POST) && count($_POST) > 0)
{
    xss_secure($_POST, $ip, 'post');
}

if (isset($_GET) && count($_GET) > 0)
{
    xss_secure($_GET, $ip, 'get');
}

if (isset($_COOKIE) && count($_COOKIE) > 0)
{
    xss_secure($_COOKIE, $ip, 'cookie');
}
?>