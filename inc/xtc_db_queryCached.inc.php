<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2002-2003 osCommerce(database.php,v 1.19 2003/03/22); www.oscommerce.com
   (c) 2006 XT-Commerce (xtcPrice.php 1316 2005-10-21)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

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
?>