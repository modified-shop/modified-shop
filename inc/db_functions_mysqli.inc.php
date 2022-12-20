<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


  function xtc_db_select_db($database) {
    return mysqli_select_db($database);
  }


  function xtc_db_close($link='db_link') {
    global ${$link};

    return mysqli_close(${$link});
  }


  function xtc_db_fetch_fields($db_query) {
    return mysqli_fetch_field($db_query);
  }


  function xtc_db_free_result($db_query) {
    return mysqli_free_result($db_query);
  }


  function xtc_db_get_client_info($link='db_link') {
    global ${$link};

    return mysqli_get_client_info(${$link});
  }


  function xtc_db_get_server_info($link='db_link') {
    global ${$link};

    return mysqli_get_server_info(${$link});
  }


  function xtc_db_fetch_object($db_query) {
    return mysqli_fetch_object($db_query);
  }


  function xtc_db_affected_rows($link='db_link') {
    global ${$link};

    return mysqli_affected_rows(${$link});
  }


  function xtc_db_insert_id($link='db_link') {
    global ${$link};

    return mysqli_insert_id(${$link});
  }


  function xtc_db_set_charset($charset, $link='db_link') {
    global ${$link};
    
    if (function_exists('mysqli_set_charset')) { //requires MySQL 5.0.7 or later
      mysqli_set_charset(${$link}, $charset);
    } else {
      xtc_db_query('SET NAMES '.$charset);
    }  
  }


  function xtc_db_connect($server=DB_SERVER, $username=DB_SERVER_USERNAME, $password=DB_SERVER_PASSWORD, $database=DB_DATABASE, $link='db_link') {
    global ${$link};

    if (!function_exists('mysqli_connect')) {
      die ('Call to undefined function: mysqli_connect(). Please install the MySQL Connector for PHP');
    }
    
    // enable error reporting
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    try {
      $socket = explode(':', $server);
      if (USE_PCONNECT == 'true') {
        ${$link} = mysqli_connect('p:'.$socket[0], $username, $password, NULL, ((isset($socket[1]) && $socket[1] != '') ? $socket[1] : NULL), ((isset($socket[2]) && $socket[2] != '') ? $socket[2] : NULL));
      } else {
        ${$link} = mysqli_connect($socket[0], $username, $password, NULL, ((isset($socket[1]) && $socket[1] != '') ? $socket[1] : NULL), ((isset($socket[2]) && $socket[2] != '') ? $socket[2] : NULL));
      }
    } catch (Exception $ex) {
      xtc_db_error('', mysqli_connect_errno(), mysqli_connect_error());
      return false;
    }

    if (${$link}) {
      if (!mysqli_select_db(${$link}, $database)) {
        xtc_db_error('', mysqli_errno(${$link}), mysqli_error(${$link}));
        return false;
      }
    } else {
      xtc_db_error('', mysqli_connect_errno(), mysqli_connect_error());
      return false;
    }

    if(version_compare(xtc_db_get_server_info(), '5.0.0', '>=')) {
      xtc_db_query("SET SESSION sql_mode=''");
    }

    // set charset defined in configure.php
    if(!defined('DB_SERVER_CHARSET')) {
      define('DB_SERVER_CHARSET','latin1');
    }
    xtc_db_set_charset(DB_SERVER_CHARSET);

    // set charset defined in configure.php
    if(!defined('DB_SERVER_ENGINE')) {
      define('DB_SERVER_ENGINE','MyISAM');
    }
    xtc_db_query("SET default_storage_engine = ".DB_SERVER_ENGINE);

    return ${$link};
  }


  function xtc_db_data_seek($db_query, $row_number, $cq=false) {
    if (defined('DB_CACHE') && DB_CACHE == 'true' && $cq) {
      if (is_array($db_query) && isset($db_query[$row_number])) {
        return $db_query[$row_number];
      }
    } else {
      if (is_object($db_query)) {
        return mysqli_data_seek($db_query, $row_number);
      }
    }

    return false;
  }


  function xtc_db_error($query, $errno, $error) { 
    // Send an email to the shop owner if a sql error occurs
    if (defined('EMAIL_SQL_ERRORS') && EMAIL_SQL_ERRORS == 'true') {
      require_once (DIR_FS_INC.'xtc_php_mail.inc.php');
      $subject = 'DATA BASE ERROR AT - ' . STORE_NAME;
      $message = '<b style="color:#ff0000;">' . $errno . ' - ' . $error . '<br><br>' . $query . '<br><br>Request URL: ' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'].'<br><br><small style="color:#ff0000">[XT SQL Error]</small></b>';
      xtc_php_mail(STORE_OWNER_EMAIL_ADDRESS, 
                   STORE_OWNER, 
                   STORE_OWNER_EMAIL_ADDRESS, 
                   '', 
                   '', 
                   STORE_OWNER_EMAIL_ADDRESS, 
                   STORE_OWNER, 
                   '', 
                   '', 
                   $subject, 
                   nl2br($message), 
                   $message);
    }
    
    trigger_error($errno.' - '.$error.'<br/><br/>'.$query, E_USER_WARNING);
  }


  function xtc_db_fetch_array(&$db_query, $cq=false, $result_type=MYSQLI_ASSOC) {
    if (defined('DB_CACHE') && DB_CACHE=='true' && $cq) {
      if (is_array($db_query)) {
        $curr = current($db_query);
        next($db_query);
        return $curr;
      }
    } else {
      if (is_object($db_query)) {
        return mysqli_fetch_array($db_query, $result_type);
      }
    }

    return false;
  }


  function xtc_db_fetch_row(&$db_query, $cq=false) {
    if (defined('DB_CACHE') && DB_CACHE=='true' && $cq) {
      if (is_array($db_query)) {
        $curr = current($db_query);
        $curr = array_values($curr);      
        next($db_query);
        return $curr;
      }
    } else {
      if (is_object($db_query)) {
        return mysqli_fetch_row($db_query);
      }
    }

    return false;
  }


  function xtc_db_query($query, $link='db_link') {
    global ${$link};

    if (defined('STORE_DB_TRANSACTIONS') && STORE_DB_TRANSACTIONS == 'true') {    
      $queryStartTime = array_sum(explode(" ",microtime()));
    }

    if (stripos(trim($query), 'INSERT INTO '.TABLE_CONFIGURATION.' ') !== false
        || stripos(trim($query), "INSERT INTO '".TABLE_CONFIGURATION."' ") !== false
        || stripos(trim($query), 'INSERT INTO `'.TABLE_CONFIGURATION.'` ') !== false
        ) 
    {
      str_replace('INSERT INTO', 'REPLACE INTO', $query);
      str_replace('insert into', 'REPLACE INTO', $query);
    }
    
    try {
      $result = mysqli_query(${$link}, $query);
    } catch (Exception $ex) {
      xtc_db_error($query, mysqli_errno(${$link}), mysqli_error(${$link}));
      return false;
    }
    
    if (defined('STORE_DB_TRANSACTIONS') && STORE_DB_TRANSACTIONS == 'true') {
      $queryEndTime = array_sum(explode(" ",microtime())); 
      $processTime = number_format(round($queryEndTime - $queryStartTime, 5), 5, '.', '');

      if (defined('STORE_DB_SLOW_QUERY') && ((STORE_DB_SLOW_QUERY == 'true' && $processTime >= STORE_DB_SLOW_QUERY_TIME) || STORE_DB_SLOW_QUERY == 'false')) {
        xtc_db_slow_query_log($processTime, $query, 'QUERY');
      }
      $result_error = mysqli_error(${$link});
      if ($result_error) {
        xtc_db_slow_query_log($processTime, $result_error, 'ERROR');
      }
      
      require_once(DIR_FS_INC.'auto_include.inc.php');
      foreach(auto_include(DIR_FS_CATALOG.'includes/extra/db_query/','php') as $file) require ($file);
    }

    return $result;
  }


  function xtc_db_input($string, $link='db_link') {
    global ${$link};

    if (function_exists('mysqli_real_escape_string')) {
      return mysqli_real_escape_string(${$link}, $string);
    }

    return addslashes($string);
  }


  function xtc_db_num_rows($db_query, $cq=false) {
    if (defined('DB_CACHE') && DB_CACHE == 'true' && $cq) {
      if (is_array($db_query)) {
        return count($db_query);
      }
    } else {
      if (is_object($db_query)) {
        return mysqli_num_rows($db_query);
      }
    }
    
    return false;
  }
?>