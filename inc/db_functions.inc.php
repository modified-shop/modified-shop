<?php
/* -----------------------------------------------------------------------------------------
   $Id:$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  if (!function_exists('encode_htmlspecialchars')) {
    require_once (DIR_FS_INC.'html_encoding.php'); //new function for PHP5.4
  }


  function xtc_db_output($string) {
    return encode_htmlspecialchars($string);
  }


  function xtc_db_prepare_input($string) {
    if (is_string($string)) {
      return trim(stripslashes($string));
    } elseif (is_array($string)) {
      reset($string);
      while (list($key, $value) = each($string)) {
        $string[$key] = xtc_db_prepare_input($value);
      }
      return $string;
    } else {
      return $string;
    }
  }
  

  function xtc_db_perform($table, $data, $action='insert', $parameters='', $link='db_link') {
    global $$link;
    
    reset($data);

    if ($action == 'insert') {
      $query = 'INSERT INTO ' . $table . ' (';
      while (list($columns, ) = each($data)) {
        $query .= $columns . ', ';
      }
      $query = substr($query, 0, -2) . ') VALUES (';
      reset($data);
      while (list(, $value) = each($data)) {
         $value = (is_float($value) && defined('PHP4_3_10') && PHP4_3_10 === true) ? sprintf("%.F",$value) : (string)($value);
        switch ($value) {
          case 'now()':
            $query .= 'now(), ';
            break;
          case 'null':
            $query .= 'null, ';
            break;
          default:
            $query .= '\'' . xtc_db_input($value) . '\', ';
            break;
        }
      }
      $query = substr($query, 0, -2) . ')';
    } elseif ($action == 'update') {
      $query = 'UPDATE ' . $table . ' SET ';
      while (list($columns, $value) = each($data)) {
         $value = (is_float($value) && defined('PHP4_3_10') && PHP4_3_10 === true) ? sprintf("%.F",$value) : (string)($value);
        switch ($value) {
          case 'now()':
            $query .= $columns . ' = now(), ';
            break;
          case 'null':
            $query .= $columns . ' = null, ';
            break;
          default:
            $query .= $columns . ' = \'' . xtc_db_input($value) . '\', ';
            break;
        }
      }
      $query = substr($query, 0, -2) . ' WHERE ' . $parameters;
    }

    return xtc_db_query($query, $link);
  }


  function xtDBquery($query, $link='db_link') {
    global $$link;

    if (defined('DB_CACHE') && DB_CACHE == 'true') {
      $result = xtc_db_queryCached($query, $link);
    } else {
      $result = xtc_db_query($query, $link);
    }
    return $result;
  }


  function xtc_db_queryCached($query, $link='db_link') {
    global $$link;

    if (defined('STORE_DB_TRANSACTIONS') && STORE_DB_TRANSACTIONS == 'true') {    
      $queryStartTime = array_sum(explode(" ",microtime()));
    }

    // get HASH ID for filename
    $id = md5($query);

    // cache File Name
    $file = SQL_CACHEDIR . $id . '.mod.cache';

    // file life time
    $expire = DB_CACHE_EXPIRE;

    if (file_exists($file) && filemtime($file) > (time() - $expire)) {

      // get cached resulst
      $result = unserialize(base64_decode(file_get_contents($file)));

      if (defined('STORE_DB_TRANSACTIONS') && STORE_DB_TRANSACTIONS == 'true') {
        $queryEndTime = array_sum(explode(" ",microtime())); 
        $processTime = number_format(round($queryEndTime - $queryStartTime, 3), 3, '.', '');
        if (defined('STORE_DB_SLOW_QUERY') && ((STORE_DB_SLOW_QUERY == 'true' && $processTime >= STORE_DB_SLOW_QUERY_TIME) || STORE_DB_SLOW_QUERY == 'false')) {
          error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' [' . $processTime . 's] ' . 'QUERY CACHED ' . $query . "\n", 3, DIR_FS_LOG.'mod_sql_' .date('Y-m-d') .'.log');
        }
      }

    } else {

      if (file_exists($file)) @unlink($file);

      // get result from DB and create new file
      $result = xtc_db_query($query, $link);

      // fetch data into array
      $records = array();
      while ($record = xtc_db_fetch_array($result)) {
        $records[] = $record;
      }
      
      // safe result into file.
      file_put_contents($file, base64_encode(serialize($records)));
      $result = $records;
    }

    return $result;
  }

?>