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
    global ${$link};
    
    if (!is_array($data) || count($data) < 1) {
      return false;
    }
    
    reset($data);

    if ($action == 'insert') {
      $query = 'INSERT INTO ' . $table . ' (';
      
      $sub_query = array();
      while (list($columns, ) = each($data)) {
        $sub_query[] = $columns;
      }
      $query .= implode(', ', $sub_query) . ') VALUES (';
      reset($data);
      
      $sub_query = array();
      while (list(, $value) = each($data)) {
        $value = (string)$value;
        switch ($value) {
          case 'now()':
            $sub_query[] = 'now()';
            break;
          case 'null':
            $sub_query[] = 'null';
            break;
          default:
            $sub_query[] = '\'' . xtc_db_input($value) . '\'';
            break;
        }
      }
      $query .= implode(', ', $sub_query) . ')';
    } elseif ($action == 'update') {
      $query = 'UPDATE ' . $table . ' SET ';
      
      $sub_query = array();
      while (list($columns, $value) = each($data)) {
        $value = (string)$value;
        switch ($value) {
          case 'now()':
            $sub_query[] = $columns . ' = now()';
            break;
          case 'null':
            $sub_query[] = $columns . ' = null';
            break;
          default:
            $sub_query[] = $columns . ' = \'' . xtc_db_input($value) . '\'';
            break;
        }
      }
      $query .= implode(', ', $sub_query) . ' WHERE ' . $parameters;
    }

    return xtc_db_query($query, $link);
  }


  function xtDBquery($query, $link='db_link') {
    global ${$link};

    if (defined('DB_CACHE') && DB_CACHE == 'true') {
      $result = xtc_db_queryCached($query, $link);
    } else {
      $result = xtc_db_query($query, $link);
    }
    return $result;
  }


  function xtc_db_queryCached($query, $link='db_link') {
    global ${$link};
    
    if (defined('STORE_DB_TRANSACTIONS') && STORE_DB_TRANSACTIONS == 'true') {    
      $queryStartTime = array_sum(explode(" ",microtime()));
    }
    
    // include needed class
    require_once (DIR_FS_EXTERNAL . 'phpfastcache/phpfastcache.php');

    $cache = phpFastCache();
    
    $id = md5(strtolower(preg_replace("'[\r\n\s]+'", '', $query)));
    
    // get cache
    $records = $cache->get($id);

    if ($records == null) {
      $result = xtc_db_query($query, $link);

      // fetch data into array
      $records = array('query' => array());
      
      while ($record = xtc_db_fetch_array($result)) {
          $records['query'][] = $record;
      }
      
      // set cache  
      $cache->set($id, $records , DB_CACHE_EXPIRE);
    } else {
      if (defined('STORE_DB_TRANSACTIONS') && STORE_DB_TRANSACTIONS == 'true') {
        $queryEndTime = array_sum(explode(" ",microtime())); 
        $processTime = number_format(round($queryEndTime - $queryStartTime, 3), 3, '.', '');
        if (defined('STORE_DB_SLOW_QUERY') && ((STORE_DB_SLOW_QUERY == 'true' && $processTime >= STORE_DB_SLOW_QUERY_TIME) || STORE_DB_SLOW_QUERY == 'false')) {
          xtc_db_slow_query_log($processTime, $query, 'QUERY');
        }
      }
    }
    
    return $records['query'];
  }


  function xtc_db_slow_query_log($processTime, $query, $type) {
    $backtrace = debug_backtrace();
    
    error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' ' . $type . ' [' . $processTime . 's] ' . $query . "\n", 3, DIR_FS_LOG.'mod_sql_'.strtolower($type).'_'. date('Y-m-d') .'.log');
    $err = 0;
    for ($i=0, $n=count($backtrace); $i<$n; $i++) {
      if (isset($backtrace[$i]['file'])) {
        error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' Backtrace #'.$err.' - '.$backtrace[$i]['file'].' called at Line '.$backtrace[$i]['line'] . "\n", 3, DIR_FS_LOG.'mod_sql_'.strtolower($type).'_'. date('Y-m-d') .'.log');
        $err ++;
      }
    }
  }

?>