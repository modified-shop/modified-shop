<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  if (!function_exists('encode_htmlspecialchars')) {
    require_once (DIR_FS_INC.'html_encoding.php');
  }


  function xtc_db_output($string) {
    return encode_htmlspecialchars($string);
  }


  function xtc_db_prepare_input($string) {
    if (is_string($string)) {
      $string = trim(stripslashes($string));
    } elseif (is_array($string)) {
      foreach ($string as $key => $value) {
        $string[$key] = xtc_db_prepare_input($value);
      }
    }
    
    return $string;
  }
  

  function xtc_db_perform($table, $data, $action = 'insert', $parameters = '', $link = 'db_link') {
    global ${$link};
    
    if (!is_array($data) || count($data) < 1) {
      return false;
    }
        
    $sql_array = array();
    foreach ($data as $columns => $values) {
      switch ((string)$values) {
        case 'now()':
          $sql_array[$columns] = 'now()';
          break;
        case 'null':
          $sql_array[$columns] = 'null';
          break;
        default:
          $sql_array[$columns] = "'" . (($values != '') ? xtc_db_input($values) : '') . "'";
          break;
      }
    }

    if ($action == 'insert') {
      $query = 'INSERT INTO ' . $table . ' (' . implode(', ', array_keys($sql_array)) . ') VALUES (' . implode(', ', $sql_array) . ')';
    }
    
    if ($action == 'update') {
      $query = 'UPDATE ' . $table . ' SET ';
      foreach ($sql_array as $col => $val) {
        $query .= $col . ' = ' . $val . ', ';
      }
      $query = rtrim($query, ', ');
      if ($parameters != '') {
        $query .= ' WHERE ' . $parameters;
      }   
    }

    return xtc_db_query($query, $link);
  }


  function xtDBquery($query, $cache_data = '', $link = 'db_link') {
    global ${$link}, $modified_cache;

    if (defined('DB_CACHE') && DB_CACHE == 'true') {
      include(DIR_FS_CATALOG.'includes/modified_cache.php');
      $result = xtc_db_queryCached($query, $cache_data, $link);
    } else {
      $result = xtc_db_query($query, $link);
    }
    
    return $result;
  }

  
  function xtc_db_queryCached($query, $cache_data, $link) {
    global ${$link}, $modified_cache;
    
    if (!is_object($modified_cache)) {
      include(DIR_FS_CATALOG.'includes/modified_cache.php');
    }

    if (!is_array($cache_data)) {
      $cache_data = array('prefix' => 'db');
    }
    if (!isset($cache_data['prefix'])) {
      $cache_data['prefix'] = 'db';
    }
    
    if (defined('STORE_DB_TRANSACTIONS') && STORE_DB_TRANSACTIONS == 'true') {    
      $queryStartTime = microtime(true);
    }
    
    $id = $cache_data['prefix'].'_'.md5(strtolower(preg_replace("'[\r\n\s]+'", '', $query)));
    $modified_cache->setID($id);
    
    $hit = false;
    $records = '';
    if ($modified_cache->isHit() === true) {
      $records = $modified_cache->get();
      $hit = true;
    }
    
    if (!is_array($records) || !isset($records['query'])) {
      // fetch data into array
      $records = array('query' => array());

      $result = xtc_db_query($query, $link);
      while ($record = xtc_db_fetch_array($result)) {
        $records['query'][] = $record;
      }
    
      $modified_cache->set($records);
      $modified_cache->setTags(array_merge(array('database', $cache_data['prefix']), ((isset($cache_data['tags'])) ? $cache_data['tags'] : array())));
    }
    
    if (defined('STORE_DB_TRANSACTIONS') && STORE_DB_TRANSACTIONS == 'true' && $hit === true) {
      $queryEndTime = microtime(true); 
      $processTime = number_format(round($queryEndTime - $queryStartTime, 3), 3, '.', '');
      if (defined('STORE_DB_SLOW_QUERY') && ((STORE_DB_SLOW_QUERY == 'true' && $processTime >= STORE_DB_SLOW_QUERY_TIME) || STORE_DB_SLOW_QUERY == 'false')) {
        xtc_db_slow_query_log($processTime, $query, 'QUERY CACHED');
      }
    }
    
    return $records['query'];
  }


  function xtc_db_slow_query_log($processTime, $query, $type) {
    $backtrace = debug_backtrace();
    
    $filename = DIR_FS_LOG.'mod_sql_'.((defined('RUN_MODE_ADMIN')) ? 'admin_' : '').strtolower(str_replace(' ', '_', $type)).'_'. date('Y-m-d') .'.log';
    error_log(date(STORE_PARSE_DATE_TIME_FORMAT) . ' ' . $type . ' found for URL: ' . mod_error_url(). "\n", 3, $filename);
    error_log(date(STORE_PARSE_DATE_TIME_FORMAT) . ' ' . $type . ' [' . $processTime . 's] ' . $query . "\n", 3, $filename);
    $err = 0;
    for ($i=0, $n=count($backtrace); $i<$n; $i++) {
      if (isset($backtrace[$i]['file'])) {
        error_log(date(STORE_PARSE_DATE_TIME_FORMAT) . ' Backtrace #'.$err.' - '.$backtrace[$i]['file'].' called at Line '.$backtrace[$i]['line'] . "\n", 3, $filename);
        $err ++;
      }
    }
  }
