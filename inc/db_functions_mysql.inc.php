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
    return mysql_select_db($database);
  }


  function xtc_db_close($link='db_link') {
    global $$link;

    return mysql_close($$link);
  }


  function xtc_db_fetch_fields($db_query) {
    return mysql_fetch_field($db_query);
  }


  function xtc_db_free_result($db_query) {
    return mysql_free_result($db_query);
  }


  function xtc_db_insert_id($link='db_link') {
    global $$link;

    return mysql_insert_id($$link);
  }


  function xtc_db_connect($server=DB_SERVER, $username=DB_SERVER_USERNAME, $password=DB_SERVER_PASSWORD, $database=DB_DATABASE, $link='db_link') {
    global $$link;

    if (!function_exists('mysql_connect')) {
      die ('Call to undefined function: mysql_connect(). Please install the MySQL Connector for PHP');
    }

    if (USE_PCONNECT == 'true') {
      $$link = @mysql_pconnect($server, $username, $password);
    } else {
      $$link = @mysql_connect($server, $username, $password);
    }

    if(version_compare(@mysql_get_server_info(), '5.0.0', '>=')) {
      @mysql_query("SET SESSION sql_mode=''");
    }

    if ($$link) {
      if (!@mysql_select_db($database, $$link)) {
        xtc_db_error('', mysql_errno($$link), mysql_error($$link));
        die();
      }
    } else {
      xtc_db_error('', mysql_errno(), mysql_error());
      die();
    }

    // set charset defined in configure.php
    if(!defined('DB_SERVER_CHARSET')) {
      define('DB_SERVER_CHARSET','utf8');
    }
    if(function_exists('mysql_set_charset')) { //requires MySQL 5.0.7 or later
      mysql_set_charset(DB_SERVER_CHARSET);
    } else {
      mysql_query('SET NAMES '.DB_SERVER_CHARSET);
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
        return mysql_data_seek($db_query, $row_number);
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


  function xtc_db_fetch_array(&$db_query, $cq=false) {

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
      return mysql_fetch_array($db_query, MYSQL_ASSOC);
    }
  }


  function xtc_db_query($query, $link='db_link') {
    global $$link;

    if (defined('STORE_DB_TRANSACTIONS') && STORE_DB_TRANSACTIONS == 'true') {    
      $queryStartTime = array_sum(explode(" ",microtime()));
    }
    
    $result = mysql_query($query, $$link) or xtc_db_error($query, mysql_errno(), mysql_error());

    if (defined('STORE_DB_TRANSACTIONS') && STORE_DB_TRANSACTIONS == 'true') {
      $queryEndTime = array_sum(explode(" ",microtime())); 
      $processTime = number_format(round($queryEndTime - $queryStartTime, 3), 3, '.', '');

      if (defined('STORE_DB_SLOW_QUERY') && ((STORE_DB_SLOW_QUERY == 'true' && $processTime >= STORE_DB_SLOW_QUERY_TIME) || STORE_DB_SLOW_QUERY == 'false')) {
        error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' [' . $processTime . 's] ' . 'QUERY ' . $query . "\n", 3, DIR_FS_LOG.STORE_PAGE_PARSE_TIME_LOG);
      }
      $result_error = mysql_error();
      if ($result_error) {
        error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' [' . $processTime . 's] ' . 'ERROR ' . $result_error . "\n", 3, DIR_FS_LOG.STORE_PAGE_PARSE_TIME_LOG);
      }
    }

    return $result;
  }


  function xtc_db_queryCached($query, $link = 'db_link') {
    global $$link, $dbTablesArray;
    $query = trim($query);
  
    // First of all check what kind of Query this is
    $isSelect = stripos($query, 'SELECT') === 0;
  
    // Now find all Tablenames and extract them
    $foundTables = array();
    foreach ($dbTablesArray AS $tbName=>$tbShort) {
      if (strpos($query, $tbName) !== false) {
        $foundTables[] = $tbShort;
      }
    }
    $foundTables = array_unique($foundTables);
  
    // get HASH ID for filename
    $id = md5($query);
    $filename = 'sql_'.implode('_', $foundTables).'_'.$id.'.php';
  
    // cache File Name (absolute path)
    $file = SQL_CACHEDIR . $filename;


    if (STORE_DB_TRANSACTIONS == 'true') {
      error_log('QUERY ' . $query . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
    }
  
    if ($isSelect) {
      // Only SELECT queries have to be cached
      if (file_exists($file) && filemtime($file) > (time() - DB_CACHE_EXPIRE)) {

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
          $result_error = mysql_error();
          if ($result_error) {
            error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' [' . $processTime . 's] ' . 'ERROR CACHED ' . $result_error . "\n", 3, DIR_FS_LOG.STORE_PAGE_PARSE_TIME_LOG);
          }
        }

      } else {
        // Nothing found or too old file
        if (file_exists($file))
          @unlink($file);

        if (defined('STORE_DB_TRANSACTIONS') && STORE_DB_TRANSACTIONS == 'true') {    
          $queryStartTime = array_sum(explode(" ",microtime()));
        }
      
        // get result from DB and create new file
        $res = mysql_query($query, $$link) or xtc_db_error($query, mysql_errno(), mysql_error());
      
        if (defined('STORE_DB_TRANSACTIONS') && STORE_DB_TRANSACTIONS == 'true') {
          $queryEndTime = array_sum(explode(" ",microtime())); 
          $processTime = number_format(round($queryEndTime - $queryStartTime, 3), 3, '.', '');
          if (defined('STORE_DB_SLOW_QUERY') && ((STORE_DB_SLOW_QUERY == 'true' && $processTime >= STORE_DB_SLOW_QUERY_TIME) || STORE_DB_SLOW_QUERY == 'false')) {
            error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' [' . $processTime . 's] ' . 'QUERY ' . $query . "\n", 3, DIR_FS_LOG.STORE_PAGE_PARSE_TIME_LOG);
          }
          $result_error = mysql_error();
          if ($result_error) {
            error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' [' . $processTime . 's] ' . 'ERROR ' . $result_error . "\n", 3, DIR_FS_LOG.STORE_PAGE_PARSE_TIME_LOG);
          }
        }
      
        $result = array(); //DokuMan - 2010-08-23 - set undefinded variable
        // fetch data into array
        while ($record = xtc_db_fetch_array($res))
          $result[] = $record;
      
        //BOF - DokuMan - 2010-08-23 - check if record exists
        if (count($result) > 0) {
          //EOF - DokuMan - 2010-08-23 - check if record exists
          // safe result into file.
          $stream = serialize($result);
          $fp = fopen($file, "w");
          fwrite($fp, $stream);
          fclose($fp);
        }
      }
    }
    else {
      // If the query is no SELECT it changes something in the DB
      // that means we need to delete all cache files which are reading from these tables
      $handle = opendir(SQL_CACHEDIR);
      while (($file = readdir($handle)) !== false) {
        // Jump over files that are no sql-cache
        if (strpos($file, 'sql_') !== 0) {
          continue;
        }
        $tmp = explode('_', $file);
        // get rid of the md5 hash and the sql_ string at the beginning
        array_pop($tmp);
        array_shift($tmp);
      }
    
      // Now let us see if there is a cached table which is also in the query
      foreach($foundTables as $tb) {
        if (in_array($tb, $tmp)) {
          // Hit! Delete the cachefile and get out of the foreach iteration
          @unlink(SQL_CACHEDIR.$file);
          break;
        }
      }

      if (defined('STORE_DB_TRANSACTIONS') && STORE_DB_TRANSACTIONS == 'true') {    
        $queryStartTime = array_sum(explode(" ",microtime()));
      }
    
      // Everything done now fire the query already
      $result = mysql_query($query, $$link) or xtc_db_error($query, mysql_errno(), mysql_error());

      if (defined('STORE_DB_TRANSACTIONS') && STORE_DB_TRANSACTIONS == 'true') {
        $queryEndTime = array_sum(explode(" ",microtime())); 
        $processTime = number_format(round($queryEndTime - $queryStartTime, 3), 3, '.', '');
        if (defined('STORE_DB_SLOW_QUERY') && ((STORE_DB_SLOW_QUERY == 'true' && $processTime >= STORE_DB_SLOW_QUERY_TIME) || STORE_DB_SLOW_QUERY == 'false')) {
          error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' [' . $processTime . 's] ' . 'QUERY ' . $query . "\n", 3, DIR_FS_LOG.STORE_PAGE_PARSE_TIME_LOG);
        }
        $result_error = mysql_error();
        if ($result_error) {
          error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' [' . $processTime . 's] ' . 'ERROR ' . $result_error . "\n", 3, DIR_FS_LOG.STORE_PAGE_PARSE_TIME_LOG);
        }
      }
    }
    return $result;
  }



  function xtc_db_input($string, $link='db_link') {
    global $$link;

    if (function_exists('mysql_real_escape_string')) {
      return mysql_real_escape_string($string, $$link);
    } elseif (function_exists('mysql_escape_string')) {
      return mysql_escape_string($string);
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
        return mysql_num_rows($db_query);
      }
    }
  }
?>