<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


  function scanDirectories($dir, $allData=array()) {
      foreach (glob($dir.'/*') as $file) {
          if (is_dir($file)) {
              $allData['dirs'][] = str_replace(DIR_FS_CATALOG, '', $file);
              $allData = scanDirectories($file, $allData);
          } else {
              $allData['files'][] = str_replace(DIR_FS_CATALOG, '', $file);
          }
      }
      return $allData;
  }
  
  
  function is_make_writeable($filename) {
    return (
      is_writable($filename)
      ? true
      : ( @chmod($filename, CHMOD_WRITEABLE) && is_writable($filename)
          ? true
          : false
        )
    );
  }
  
  
  function rrmdir($dir) {
    global $unlinked_files, $error;
    
    $dir = rtrim($dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    $files = new DirectoryIterator(DIR_FS_DOCUMENT_ROOT.$dir);
    
    foreach ($files as $file) {
      $filename = $file->getFilename();
    
      if ($file->isDot() === false) {
        if(is_dir(DIR_FS_DOCUMENT_ROOT.$dir.$filename)) {
          rrmdir($dir.$filename);
        } else {
          if (unlink(DIR_FS_DOCUMENT_ROOT.$dir.$filename) === true) {
            $unlinked_files['success']['files'][] = $filename;
          } else {
            $unlinked_files['error']['files'][] = $filename;
          }
        }
      }
    }

    if (rmdir(DIR_FS_DOCUMENT_ROOT.$dir) === true) {
      $unlinked_files['success']['dir'][] = $dir;
    } else {
      $unlinked_files['error']['dir'][] = $dir;
      $error = true;
    }
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
    global $messageStack;
  
    if ($plain === false) {
      $sql_file = file_get_contents($file);
    } else {
      $sql_file = $file;
    }
    $sql_array = (split_sql_file($sql_file, ';'));
    
    foreach ($sql_array as $sql) {
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
        if (DB_SERVER_CHARSET == 'utf8') {
          $sql = encode_utf8($sql, '', true);
        }
        $result = xtc_db_query($sql);

        if ($result === true) {
          if (get_messagestack_size('update', 'success') < 1) {
            $messageStack->add_session('update', TEXT_EXECUTED_SUCCESS, 'success');
          }
          $messageStack->add_session('update', sprintf(TEXT_SQL_SUCCESS, encode_htmlspecialchars($sql)), 'success');
        } else {
          if (get_messagestack_size('update', 'error') < 1) {
            $messageStack->add_session('update', TEXT_EXECUTED_ERROR, 'error');
          }
          $messageStack->add_session('update', sprintf(TEXT_SQL_SUCCESS, encode_htmlspecialchars($sql)), 'error');
        }
      }
    }
  }
  
  
  function get_messagestack_size($class, $type) {
    $count = 0;
    if (isset($_SESSION['messageToStack'])) {
      $messages = array();
      for ($i=0, $n=sizeof($_SESSION['messageToStack']); $i<$n; $i++) {
        $messages[$_SESSION['messageToStack'][$i]['class']][$_SESSION['messageToStack'][$i]['type']][] = $_SESSION['messageToStack'][$i]['text'];        
      }
    }

    if (isset($messages[$class][$type])) {
      $count = count($messages[$class][$type]);
    }
      
    return $count;
  }
  
  
  function parse_sql_file($file) {
    $sql_array = array();
    $restore_query = file_get_contents($file);
    $sql_length = strlen($restore_query);
    $pos = strpos($restore_query, ';');
    for ($i=$pos; $i<$sql_length; $i++) {
      if ($restore_query[0] == '#' || $restore_query[0] == '-') {
        $restore_query = ltrim(substr($restore_query, strpos($restore_query, "\n")));
        $sql_length = strlen($restore_query);
        $i = strpos($restore_query, ';')-1;
        continue;
      }
      if ($restore_query[($i+1)] == "\n") {
        for ($j=($i+2); $j<$sql_length; $j++) {
          if (trim($restore_query[$j]) != '') {
            $next = substr($restore_query, $j, 6);
            if ($next[0] == '#' || $next[0] == '-') {
              // find out where the break position is so we can remove this line (#comment line)
              for ($k=$j; $k<$sql_length; $k++) {
                if ($restore_query[$k] == "\n") break;
              }
              $query = substr($restore_query, 0, $i+1);
              $restore_query = substr($restore_query, $k);
              // join the query before the comment appeared, with the rest of the dump
              $restore_query = $query . $restore_query;
              $sql_length = strlen($restore_query);
              $i = strpos($restore_query, ';')-1;
              continue 2;
            }
            break;
          }
        }
        if ($next == '') { // get the last insert query
          $next = 'insert';
        }
        if ( (preg_match('/create/i', $next)) || (preg_match('/insert/i', $next)) || (preg_match('/drop t/i', $next)) ) {
          $next = '';
          $sql_array[] = substr($restore_query, 0, $i);
          $restore_query = ltrim(substr($restore_query, $i+1));
          $sql_length = strlen($restore_query);
          $i = strpos($restore_query, ';')-1;
        }
      }
    }
    
    return $sql_array;
  }
  
  
  function get_sql_create_data($table) {
    static $sql_data;

    if (!isset($sql_data) || $sql_data == '') {
      $sql_data = file_get_contents(DIR_FS_CATALOG.'_installer/includes/sql/modified.sql');
    }

    preg_match("/CREATE TABLE([\s]+)".$table."(.*?\);)/si", $sql_data, $sql_match);

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
    $dir = rtrim($dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    $ignore_files = array('.htaccess', 'index.html');
    if ($handle = opendir($dir)) {
      while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") {
          if (is_dir($dir.$file)) {
            clear_dir($dir.$file, true);
            rmdir($dir.$file);
          } else {
            if (!$basefiles && in_array($file, $ignore_files)) {
              continue;
            }
            unlink($dir.$file);
          }
        }
      }
      closedir($handle);
    }
  }


  function get_document_root() {
    if (strpos($_SERVER['DOCUMENT_ROOT'],'strato') !== false) {
      return rtrim(strato_document_root(),'/') . '/';
    } else {
      return rtrim(detectDocumentRoot(),'/') . '/';
    }    
  }
  
  
  function detectDocumentRoot() {
    $dir_fs_www_root = realpath(dirname(basename(__FILE__)) . "/..");
    if ($dir_fs_www_root == '') $dir_fs_www_root = '/';
    $dir_fs_www_root = str_replace(array('\\','//'), '/', $dir_fs_www_root);
    return $dir_fs_www_root;
  }


  function strato_document_root() {
    // subdomain entfernen
    $domain = $_SERVER["HTTP_HOST"];
    $tmp = explode ('.',$domain);
    if (count($tmp) > 2) {
      $domain = str_replace($tmp[0].'.','',$domain);
    }
    $document_root = str_replace($_SERVER["PHP_SELF"],'',$_SERVER["SCRIPT_FILENAME"]);
    //Unterverzeichnis ermitteln
    $tmp = explode(DIR_MODIFIED_INSTALLER, $_SERVER["PHP_SELF"]);
    $subdir = $tmp[0];
    //Prüfen ob Domain im Pfad enthalten ist, wenn nein Pfad Stratopfad erzeugen: /home/strato/www/ersten zwei_buchstaben/www.wunschname.de/htdocs/
    if(stristr($document_root, $domain) === FALSE) {
      //Korrektur Unterverzeichnis      
      $htdocs = str_replace($_SERVER["SCRIPT_NAME"],'',$_SERVER["SCRIPT_FILENAME"]);
      $htdocs = '/htdocs' . str_replace($_SERVER["DOCUMENT_ROOT"],'',$htdocs);
      //MUSTER: /home/strato/www/wu/www.wunschname.de/htdocs/
      $document_root = '/home/strato/www/'.substr($domain, 0, 2). '/www.'.$domain.$htdocs.$subdir;
    } else {
      $document_root .= $subdir;
    }
    return $document_root;
  }
  
  
  function get_mysql_type() {
    if (function_exists('mysqli_connect')) {
      return 'mysqli';
    }
    
    return 'mysql';
  }
?>