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
    global $$link;

    return mysqli_close($$link);
  }


  function xtc_db_fetch_fields($db_query) {
    return mysqli_fetch_field($db_query);
  }


  function xtc_db_free_result($db_query) {
    return mysqli_free_result($db_query);
  }


  function xtc_db_get_client_info($link='db_link') {
    global $$link;

    return mysqli_get_client_info($$link);
  }


  function xtc_db_get_server_info($link='db_link') {
    global $$link;

    return mysqli_get_server_info($$link);
  }


  function xtc_db_fetch_object($db_query) {
    return mysqli_fetch_object($db_query);
  }


  function xtc_db_affected_rows($link='db_link') {
    global $$link;

    return mysqli_affected_rows($$link);
  }


  function xtc_db_insert_id($link='db_link') {
    global $$link;

    return mysqli_insert_id($$link);
  }


  function xtc_db_connect($server=DB_SERVER, $username=DB_SERVER_USERNAME, $password=DB_SERVER_PASSWORD, $database=DB_DATABASE, $link='db_link') {
    global $$link;

    if (!function_exists('mysqli_connect')) {
      die ('Call to undefined function: mysqli_connect(). Please install the MySQL Connector for PHP');
    }

    if (USE_PCONNECT == 'true') {
      $$link = @mysqli_connect('p:'.$server, $username, $password);
    } else {
      $$link = @mysqli_connect($server, $username, $password);
    }


    if(version_compare(@xtc_db_get_server_info(), '5.0.0', '>=')) {
      @mysqli_query($$link, "SET SESSION sql_mode=''");
    }

    if ($$link) {
      if (!@mysqli_select_db($$link, $database)) {
        xtc_db_error('', mysqli_errno($$link), mysqli_error($$link));
        die();
      }
    } else {
      xtc_db_error('', mysqli_errno($$link), mysqli_error($$link));
      die();
    }

    // set charset defined in configure.php
    if(!defined('DB_SERVER_CHARSET')) {
      define('DB_SERVER_CHARSET','latin1');
    }
    if(function_exists('mysqli_set_charset')) { //requires MySQL 5.0.6 or later
      mysqli_set_charset($$link, DB_SERVER_CHARSET);
    } else {
      mysqli_query($$link, 'SET NAMES '.DB_SERVER_CHARSET);
    }    

    return $$link;
  }


  function xtc_db_data_seek($db_query, $row_number, $cq=false) {

    if (defined('DB_CACHE') && DB_CACHE == 'true' && $cq) { //Dokuman - 2011-02-11 - check for defined DB_CACHE
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
      header("HTTP/1.1 503 Service Temporarily Unavailable");
      header("Status: 503 Service Temporarily Unavailable");
      header("Connection: Close");
    }
    
    // Send an email to the shop owner if a sql error occurs
    if (defined('EMAIL_SQL_ERRORS') && EMAIL_SQL_ERRORS == 'true') {
      if (defined('RUN_MODE_ADMIN')) {
        require_once (DIR_FS_CATALOG.DIR_WS_CLASSES.'class.phpmailer.php');
        require_once (DIR_FS_INC.'xtc_php_mail.inc.php');
      }
      $subject = 'DATA BASE ERROR AT - ' . STORE_NAME;
      $message = '<font color="#000000"><strong>' . $errno . ' - ' . $error . '<br /><br />' . $query . '<br /><br />Request URL: ' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'].'<br /><br /><small><font color="#ff0000">[XT SQL Error]</font></small><br /><br /></strong></font>';
      xtc_php_mail(STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, '', '', STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER, '', '', $subject, nl2br($message), $message);
    }
    
    // show the full sql error + full query only to logged-in admins or error_reporting() != 0
    if (isset($_SESSION['customers_status']['customers_status']) && $_SESSION['customers_status']['customers_status'] == '0' || error_reporting() != 0) {
      die('<font color="#000000"><strong>' . $errno . ' - ' . $error . '<br /><br />' . $query . '<br /><br /><small><font color="#ff0000">[MOD SQL Error]</font></small><br /><br /></strong></font>');
    } else {
      die('<font color="#ff0000"><strong>Es ist ein Fehler aufgetreten!<br />There was an error!<br />Il y avait une erreur!</strong></font>');
    }

    //and display an info message for the shop customer and redirect him
    echo '<p>'.ERROR_SQL_DB_QUERY.'</p>';    
    if ($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] != $_SERVER['HTTP_HOST']) {
      $redirect_time = 5; // in seconds
      echo '<p>'.sprintf(ERROR_SQL_DB_QUERY_REDIRECT, $redirect_time).'</p>';      
      echo '<script language="javascript">';
      $redirect_time = $redirect_time * 1000; // convert to milliseconds for javascript redirect
      echo 'setTimeout(\'location.href="http://' . $_SERVER['HTTP_HOST'] . '"\','.$redirect_time.');';
      echo '</script>';
    }
    exit(); 
  }


  function xtc_db_fetch_array(&$db_query, $cq=false, $result_type=MYSQL_ASSOC) {
    
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
    global $$link;

    if (defined('STORE_DB_TRANSACTIONS') && STORE_DB_TRANSACTIONS == 'true') {    
      $queryStartTime = array_sum(explode(" ",microtime()));
    }
    
    $result = mysqli_query($$link, $query) or xtc_db_error($query, mysqli_errno($$link), mysqli_error($$link));

    if (defined('STORE_DB_TRANSACTIONS') && STORE_DB_TRANSACTIONS == 'true') {
      $queryEndTime = array_sum(explode(" ",microtime())); 
      $processTime = number_format(round($queryEndTime - $queryStartTime, 3), 3, '.', '');

      if (defined('STORE_DB_SLOW_QUERY') && ((STORE_DB_SLOW_QUERY == 'true' && $processTime >= STORE_DB_SLOW_QUERY_TIME) || STORE_DB_SLOW_QUERY == 'false')) {
        error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' [' . $processTime . 's] ' . 'QUERY ' . $query . "\n", 3, DIR_FS_LOG.STORE_PAGE_PARSE_TIME_LOG);
      }
      $result_error = mysqli_error($$link);
      if ($result_error) {
        error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' [' . $processTime . 's] ' . 'ERROR ' . $result_error . "\n", 3, DIR_FS_LOG.STORE_PAGE_PARSE_TIME_LOG);
      }
    }

    return $result;
  }


  function xtc_db_queryCached($query, $link='db_link') {
    global $$link;

    // get HASH ID for filename
    $id = md5($query);

    // cache File Name
    $file = SQL_CACHEDIR . $id . '.mod.cache';

    // file life time
    $expire = DB_CACHE_EXPIRE;

    if (file_exists($file) && filemtime($file) > (time() - $expire)) {

      if (defined('STORE_DB_TRANSACTIONS') && STORE_DB_TRANSACTIONS == 'true') {    
        $queryStartTime = array_sum(explode(" ",microtime()));
      }

      // get cached resulst
      $result = unserialize(base64_decode(file_get_contents($file)));

      if (defined('STORE_DB_TRANSACTIONS') && STORE_DB_TRANSACTIONS == 'true') {
        $queryEndTime = array_sum(explode(" ",microtime())); 
        $processTime = number_format(round($queryEndTime - $queryStartTime, 3), 3, '.', '');
        if (defined('STORE_DB_SLOW_QUERY') && ((STORE_DB_SLOW_QUERY == 'true' && $processTime >= STORE_DB_SLOW_QUERY_TIME) || STORE_DB_SLOW_QUERY == 'false')) {
          error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' [' . $processTime . 's] ' . 'QUERY CACHED ' . $query . "\n", 3, DIR_FS_LOG.STORE_PAGE_PARSE_TIME_LOG);
        }
        $result_error = mysqli_error($$link);
        if ($result_error) {
          error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' [' . $processTime . 's] ' . 'ERROR CACHED ' . $result_error . "\n", 3, DIR_FS_LOG.STORE_PAGE_PARSE_TIME_LOG);
        }
      }

    } else {

      if (file_exists($file)) @unlink($file);

      if (defined('STORE_DB_TRANSACTIONS') && STORE_DB_TRANSACTIONS == 'true') {    
        $queryStartTime = array_sum(explode(" ",microtime()));
      }

      // get result from DB and create new file
      $result = mysqli_query($link, $query) or xtc_db_error($query, mysqli_errno($link), mysqli_error($link));

      if (defined('STORE_DB_TRANSACTIONS') && STORE_DB_TRANSACTIONS == 'true') {
        $queryEndTime = array_sum(explode(" ",microtime())); 
        $processTime = number_format(round($queryEndTime - $queryStartTime, 3), 3, '.', '');
        if (defined('STORE_DB_SLOW_QUERY') && ((STORE_DB_SLOW_QUERY == 'true' && $processTime >= STORE_DB_SLOW_QUERY_TIME) || STORE_DB_SLOW_QUERY == 'false')) {
          error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' [' . $processTime . 's] ' . 'QUERY ' . $query . "\n", 3, DIR_FS_LOG.STORE_PAGE_PARSE_TIME_LOG);
        }
        $result_error = mysqli_error($link);
        if ($result_error) {
          error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' [' . $processTime . 's] ' . 'ERROR ' . $result_error . "\n", 3, DIR_FS_LOG.STORE_PAGE_PARSE_TIME_LOG);
        }
      }

      // fetch data into array
      $records = array();
      while ($record = xtc_db_fetch_array($result)) {
        $records[]=$record;
      }
      
      // safe result into file.
      $stream = base64_encode(serialize($records));
      $fp = fopen($file,"w");
            fwrite($fp, $stream);
            fclose($fp);
      $result = unserialize(base64_decode(file_get_contents($file)));
    }

    return $result;
  }


  function xtc_db_input($string, $link='db_link') {
    global $$link;

    if (function_exists('mysqli_real_escape_string')) {
      return mysqli_real_escape_string($$link, $string);
    }

    return addslashes($string);
  }


  function xtc_db_num_rows($db_query, $cq=false) {
    if ($db_query === false) {
      return false;
    }
    if (defined('DB_CACHE') && DB_CACHE == 'true' && $cq) { //Dokuman - 2011-02-11 - check for defined DB_CACHE
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