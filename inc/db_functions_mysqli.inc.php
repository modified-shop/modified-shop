<?php
/* -----------------------------------------------------------------------------------------
   $Id:$

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

    $socket = explode(':', $server);
    if (USE_PCONNECT == 'true') {
      ${$link} = @mysqli_connect('p:'.$socket[0], $username, $password, NULL, ((isset($socket[2])) ? $socket[2] : NULL), ((isset($socket[1])) ? $socket[1] : NULL));
    } else {
      ${$link} = @mysqli_connect($socket[0], $username, $password, NULL, ((isset($socket[2])) ? $socket[2] : NULL), ((isset($socket[1])) ? $socket[1] : NULL));
    }

    if (${$link}) {
      if (!@mysqli_select_db(${$link}, $database)) {
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

    return ${$link};
  }


  function xtc_db_data_seek($db_query, $row_number, $cq=false) {

    if (defined('DB_CACHE') && DB_CACHE == 'true' && $cq) {
      if (!count($db_query)) {
        return;
      }
      return $db_query[$row_number];
    } else {
      if (!is_array($db_query)) {
        return mysqli_data_seek($db_query, $row_number);
      }
    }
  }


  function xtc_db_error($query, $errno, $error) { 
  
    // Deliver 503 Error on database error (so crawlers won't index the error page)
    if (!defined('DIR_FS_ADMIN')) {
      //header("HTTP/1.1 503 Service Temporarily Unavailable");
      //header("Status: 503 Service Temporarily Unavailable");
      //header("Connection: Close");
    }
    
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
    
    if ($db_query === false) {
      return false;
    }
    if (defined('DB_CACHE') && DB_CACHE=='true' && $cq) {
      if (!is_array($db_query) || !count($db_query)) {
        return false;
      }
      $curr = current($db_query);
      next($db_query);
      return $curr;
    } else {
      if (is_array($db_query)) {
        $curr = current($db_query);
        next($db_query);
        return $curr;
      }
      return mysqli_fetch_array($db_query, $result_type);
    }
  }


  function xtc_db_fetch_row(&$db_query, $cq=false) {

    if ($db_query === false) {
      return false;
    }
    if (defined('DB_CACHE') && DB_CACHE=='true' && $cq) {
      if (!is_array($db_query) || !count($db_query)) {
        return false;
      }
      $curr = current($db_query);
      next($db_query);
      return $curr;
    } else {
      if (is_array($db_query)) {
        $curr = current($db_query);
        next($db_query);
        return $curr;
      }
      return mysqli_fetch_row($db_query);
    }
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
    
    $result = mysqli_query(${$link}, $query) or xtc_db_error($query, mysqli_errno(${$link}), mysqli_error(${$link}));

    if (defined('STORE_DB_TRANSACTIONS') && STORE_DB_TRANSACTIONS == 'true') {
      $queryEndTime = array_sum(explode(" ",microtime())); 
      $processTime = number_format(round($queryEndTime - $queryStartTime, 5), 5, '.', '');

      if (isset($_GET['query_analyzer']) && $_GET['query_analyzer'] == 'on') {
        $_SESSION['query_analyzer'] = true;
      }
      if (isset($_GET['query_analyzer']) && $_GET['query_analyzer'] == 'off') {
        $_SESSION['query_analyzer'] = false;
      }
      if (isset($_SESSION['query_analyzer'])
          && $_SESSION['query_analyzer'] === true 
          && function_exists('mod_sql_explain') 
          && function_exists('mod_output_sql_explain')) 
      {
        $explain_query_raw = preg_replace("'[\r\n\s]+'", ' ', $query);
      
        if (substr(strtolower($explain_query_raw), 0, 6) == 'select') {
          $explain_log_array = array();
          $explain_query = mysqli_query(${$link}, 'EXPLAIN ' . $explain_query_raw) or xtc_db_error($explain_query_raw, mysqli_errno(${$link}), mysqli_error(${$link}));
          while ($explain = xtc_db_fetch_array($explain_query)) {
            $explain_array = mod_sql_explain($explain);
            $explain_log_array = array_merge($explain_log_array, $explain_array);
          }
          mod_output_sql_explain($explain_log_array, $query, $processTime);
        }
      }

      if (defined('STORE_DB_SLOW_QUERY') && ((STORE_DB_SLOW_QUERY == 'true' && $processTime >= STORE_DB_SLOW_QUERY_TIME) || STORE_DB_SLOW_QUERY == 'false')) {
        xtc_db_slow_query_log($processTime, $query, 'QUERY');
      }
      $result_error = mysqli_error(${$link});
      if ($result_error) {
        xtc_db_slow_query_log($processTime, $result_error, 'ERROR');
      }
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
    if ($db_query === false) {
      return false;
    }
    if (defined('DB_CACHE') && DB_CACHE == 'true' && $cq) {
      if (!count($db_query)) {
        return false;
      }
      return count($db_query);
    } else {
      if (!is_array($db_query)) {
        return mysqli_num_rows($db_query);
      }
    }
  }
?>