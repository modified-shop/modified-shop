<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org] 
   --------------------------------------------------------------*/

function rrmdir($dir) {
  global $error, $success;
    
  foreach(glob($dir . '/*') as $file) {
    if(is_dir($file)) {
      rrmdir($file);
    } else {
      @unlink($file) ? $success.=$file.'<br/>' : $error.=$file.'<br/>';
    }
  }
  @rmdir($dir) ? $success.=$dir.'<br/>' : $error.=$dir.'<br/>';
}

function remove_comments($sql, $remark) {
  $lines = explode("\n", $sql);
  $sql = '';
        
  $linecount = count($lines);
  $output = '';

  for ($i = 0; $i < $linecount; $i++)  {
    if (($i != ($linecount - 1)) || (strlen($lines[$i]) > 0)) {
      if ($lines[$i][0] != $remark) {
        $output .= $lines[$i] . "\n";
      } else {
        $output .= "\n";
      }
      $lines[$i] = '';
    }
  }      
  return $output;
}

function split_sql_file($sql, $delimiter) {

  //first remove comments
  $sql = remove_comments($sql, '#');
  
  // Split up our string into "possible" SQL statements.
  $tokens = explode($delimiter, $sql);

  $sql = '';
  $output = array();
  $matches = array();
  
  $token_count = count($tokens);
  for ($i = 0; $i < $token_count; $i++) {
  
    // Don't wanna add an empty string as the last thing in the array.
    if (($i != ($token_count - 1)) || (strlen($tokens[$i] > 0))) {
          
      // This is the total number of single quotes in the token.
      $total_quotes = preg_match_all("/'/", $tokens[$i], $matches);
      // Counts single quotes that are preceded by an odd number of backslashes, 
      // which means they're escaped quotes.
      $escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$i], $matches);
       
      $unescaped_quotes = $total_quotes - $escaped_quotes;
      
      // If the number of unescaped quotes is even, then the delimiter did NOT occur inside a string literal.
      if (($unescaped_quotes % 2) == 0) {
        // It's a complete sql statement.
        $output[] = $tokens[$i];
        $tokens[$i] = '';
      } else {
        // incomplete sql statement. keep adding tokens until we have a complete one.
        // $temp will hold what we have so far.
        $temp = $tokens[$i] . $delimiter;
        $tokens[$i] = '';
        
        $complete_stmt = false;
        
        for ($j = $i + 1; (!$complete_stmt && ($j < $token_count)); $j++) {
          // This is the total number of single quotes in the token.
          $total_quotes = preg_match_all("/'/", $tokens[$j], $matches);
          // Counts single quotes that are preceded by an odd number of backslashes, 
          // which means they're escaped quotes.
          $escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$j], $matches);
         
          $unescaped_quotes = $total_quotes - $escaped_quotes;
         
          if (($unescaped_quotes % 2) == 1) {
            // odd number of unescaped quotes. In combination with the previous incomplete
            // statement(s), we now have a complete statement. (2 odds always make an even)
            $output[] = $temp . $tokens[$j];
      
            $tokens[$j] = '';
            $temp = '';
            
            $complete_stmt = true;
            $i = $j;
          } else {
            // even number of unescaped quotes. We still don't have a complete statement. 
            // (1 odd and 1 even always make an odd)
            $temp .= $tokens[$j] . $delimiter;
            $tokens[$j] = '';
          }
        }
      }
    }
  }
  return $output;
}

function sql_update($file, $plain=false) {
  global $success;
  
  if ($plain === false) {
    $sql_file = file_get_contents($file);
  } else {
    $sql_file = $file;
  }
  $sql_array = (split_sql_file($sql_file, ';'));
    
  foreach ($sql_array as $sql) {
    $success .= $sql;
    $exists = false;
    if (preg_match("|[\z\s]?(?:ALTER TABLE?){1}[\Z\s]+([^ ]*)[\z\s]+(?:ADD?){1}[\z\s]+([^ ]*)[\z\s]+([^ ]*)|", $sql, $matches)) {
      if ($matches[2] == strtoupper('INDEX')) {
        $check_query = xtc_db_query("SHOW KEYS FROM ".$matches[1]." WHERE Key_name='".$matches[3]."'");
        if (xtc_db_num_rows($check_query)>0) {
          xtc_db_query("ALTER TABLE ".$matches[1]." DROP INDEX ".$matches[3]);
        }
      } else {
        $check_query = xtc_db_query("SHOW COLUMNS FROM " . $matches[1]);
        while ($check = xtc_db_fetch_array($check_query)) {
          if ($check['Field']==$matches[2]) { 
            $exists = true;
          }
        }
      }
    }
    if (!$exists) {
      if (INSTALL_CHARSET == 'utf8') {
        $sql = mb_convert_encoding($sql, 'utf-8', 'ISO-8859-15');
      }
      xtc_db_query($sql);
      
      if (xtc_db_affected_rows() > 0) {
        $success .= ' - <span style="color:red;">Success!</span><br/>';
      }
    }
  }
}

function get_sql_create_data($table) {
  static $sql_data;

  if (!isset($sql_data) || $sql_data == '') {
    $sql_data = file_get_contents(DIR_FS_CATALOG.'_installer/includes/sql/modified.sql');
  }

  preg_match("/CREATE TABLE([\s]+)".$table."(.*?)ENGINE/si", $sql_data, $sql_match);

  $result = '';
  if (isset($sql_match[2])) {
    $result = "CREATE TABLE _mod_".$table.$sql_match[2];
  }

  return $result;
}

function array_diff_assoc_recursive($array1, $array2) {
  $difference=array();

  foreach ($array1 as $key => $value) {
    if (is_array($value)) {
      if (!isset($array2[$key]) || !is_array($array2[$key])) {
        $difference[$key] = $value;
      } else {
        $new_diff = array_diff_assoc_recursive($value, $array2[$key]);
        if (!empty($new_diff)) {
          $difference[$key] = $new_diff;
        }
      }
    } elseif (!array_key_exists($key,$array2) || $array2[$key] !== $value) {
      $difference[$key] = $value;
    }
  }

  return $difference;
}

function clear_dir($dir, $basefiles = false) {
  if ($basefiles === true) {
    $files = glob(rtrim($dir, '/').'/{,.}[!.,!..]*', GLOB_BRACE);
  } else {
    $files = glob(rtrim($dir, '/').'/*');
  }
  foreach ($files as $file) {
    if(is_dir($file)) {
      clear_dir($file, true);
      rmdir($file);
    } else {
      if ($basefiles === false && basename($file) != 'index.html') {
        unlink($file);
      } elseif ($basefiles === true) {
        unlink($file);
      }
    }
  }
}
?>