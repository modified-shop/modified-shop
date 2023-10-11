<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


  function scanDirectories($dir, $data_array) {
    if (!is_array($data_array)) {
      $data_array = array(
        'dirs' => array(),
        'files' => array(),
      );
    }

    foreach ((new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir))) as $file) {
      $filename = str_replace(DIR_FS_CATALOG, '', $file->getPathname());
      $filename = rtrim($filename, '.');
         
      if (is_file($file->getPathname()) !== false) {
        $data_array['files'][] = $filename;
      } else {
        if (!in_array($filename, $data_array['dirs'])) {
          $data_array['dirs'][] = $filename;
        }
      }
    }
    
    return $data_array;
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
            $error = true;
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
        if (isset($lines[$i][0]) && $lines[$i][0] != $remark) {
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
          $output[] = trim($tokens[$i]);
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
              $output[] = trim($temp . $tokens[$j]);
      
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
  
  
  function sql_update($file, $plain = false) {    
    if ($plain === false) {
      $sql_file = file_get_contents($file);
    } else {
      $sql_file = $file;
    }
    $sql_array = split_sql_file($sql_file, ';');
    
    $sql_data_array = array();
    foreach ($sql_array as $sql) {
      if (DB_SERVER_CHARSET == 'utf8') {
        $sql = encode_utf8($sql, 'ISO-8859-1', true);
      }
      $sql_data_array[] = trim($sql);
    }
    
    return $sql_data_array;
  }
  
  
  function execute_sql($sql) {
    static $database_tables;
    
    if (!isset($database_tables)) {
      $database_tables = array();
      $tables_query = xtc_db_query("SHOW TABLES");
      while ($tables = xtc_db_fetch_array($tables_query)) {
        $database_tables[] = $tables['Tables_in_'.DB_DATABASE];
      }
    }
    
    $execute = true;
    if (preg_match('#[\\\z\s]?(?:ALTER TABLE){1}[\\\Z\s]+([^ ]*)[\\\z\s]+(?:ADD PRIMARY KEY){1}[\\\z\s]+(.*)#', $sql, $matches)) {
      $table = preg_replace("'[\r\n\s]+'", '', str_replace('`', '', $matches[1]));
      
      if (in_array($table, $database_tables)) {
        $check_query = xtc_db_query("SHOW KEYS FROM `".$table."` WHERE Key_name = 'PRIMARY'");
        if (xtc_db_num_rows($check_query) > 0) {
          $add_sql = "ALTER TABLE `".$table."` DROP PRIMARY KEY";
          xtc_db_query($add_sql);
          installer_log('EXECUTED SQL: '.$add_sql);
        }
      }
    } elseif (preg_match('#[\\\z\s]?(?:ALTER TABLE){1}[\\\Z\s]+([^ ]*)[\\\z\s]+(?:ADD UNIQUE KEY|ADD UNIQUE){1}[\\\z\s]+([^ ]*)[\\\z\s]+(.*)#', $sql, $matches)) {
      $table = preg_replace("'[\r\n\s]+'", '', str_replace('`', '', $matches[1]));
      $key = preg_replace("'[\r\n\s]+'", '', str_replace('`', '', $matches[2]));
      
      if (in_array($table, $database_tables)) {
        $check_query = xtc_db_query("SHOW KEYS FROM `".$table."` WHERE Key_name = '".$key."'");
        if (xtc_db_num_rows($check_query) > 0) {
          $check = xtc_db_fetch_array($check_query);
          if ($check['Key_name'] == $key) {
            $add_sql = "ALTER TABLE `".$table."` DROP INDEX `".$key."`";
            xtc_db_query($add_sql);
            installer_log('EXECUTED SQL: '.$add_sql);
          }
        }
      }
    } elseif (preg_match('#[\\\z\s]?(?:ALTER TABLE){1}[\\\Z\s]+([^ ]*)[\\\z\s]+(?:ADD|DROP INDEX){1}[\\\z\s]+([^ ]*)[\\\z\s]+([^ ]*)#', $sql, $matches)) {    
      $table = preg_replace("'[\r\n\s]+'", '', str_replace('`', '', $matches[1]));
      
      if (in_array($table, $database_tables)) {
        if (strtoupper($matches[2]) == 'INDEX') {
          $key = preg_replace("'[\r\n\s]+'", '', str_replace('`', '', $matches[3]));
        
          $check_query = xtc_db_query("SHOW KEYS FROM `".$table."` WHERE Key_name = '".$key."'");
          if (xtc_db_num_rows($check_query) > 0) {
            $check = xtc_db_fetch_array($check_query);
            if ($check['Key_name'] == $key) {
              $add_sql = "ALTER TABLE `".$table."` DROP INDEX `".$key."`";
              xtc_db_query($add_sql);
              installer_log('EXECUTED SQL: '.$add_sql);
            }
          }
          if (stripos(strtoupper($matches[0]), 'DROP INDEX') !== false) $execute = false;
        } elseif (stripos(strtoupper($matches[0]), 'ADD') !== false) {
          $column = preg_replace("'[\r\n\s]+'", '', str_replace('`', '', $matches[2]));
        
          $check_query = xtc_db_query("SHOW COLUMNS FROM `".$table."` LIKE '".$column."'");
          if (xtc_db_num_rows($check_query) > 0) {
            $execute = false;
          }
        }
      }
    } elseif (preg_match('#[\\\z\s]?(?:ALTER TABLE){1}[\\\Z\s]+([^ ]*)[\\\z\s]+(?:CHANGE){1}[\\\z\s]+([^ ]*)#', $sql, $matches)) {
      $table = preg_replace("'[\r\n\s]+'", '', str_replace('`', '', $matches[1]));

      if (in_array($table, $database_tables)) {
        $column = preg_replace("'[\r\n\s]+'", '', str_replace('`', '', $matches[2]));

        $check_query = xtc_db_query("SHOW COLUMNS FROM `".$table."` LIKE '".$column."'");
        if (xtc_db_num_rows($check_query) < 1) {
          $execute = false;
        }      
      }
    } elseif (preg_match('#[\\\z\s]?(?:ALTER TABLE){1}[\\\Z\s]+([^ ]*)[\\\z\s]+(?:DROP){1}[\\\z\s]+([^ ]*)#', $sql, $matches)) {
      $table = preg_replace("'[\r\n\s]+'", '', str_replace('`', '', $matches[1]));
      
      if (in_array($table, $database_tables)) {
        if (stripos(strtoupper($matches[0]), 'DROP') !== false) {
          $column = preg_replace("'[\r\n\s]+'", '', str_replace('`', '', $matches[2]));
        
          $check_query = xtc_db_query("SHOW COLUMNS FROM `".$table."` LIKE '".$column."'");
          if (xtc_db_num_rows($check_query) > 0) {
            $add_sql = "ALTER TABLE `".$table."` DROP `".$column."`";
            xtc_db_query($add_sql);
            installer_log('EXECUTED SQL: '.$add_sql);
          }
          $execute = false;
        }
      }
    }

    if ($execute === true) {
      xtc_db_query($sql);
      installer_log('EXECUTED SQL: '.$sql);
    } else {
      installer_log('NOT EXECUTED SQL: '.$sql);
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
    return rtrim(strato_document_root(),'/') . '/';   
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
    if (!is_dir($document_root)) {
      $document_root = detectDocumentRoot();
    }
    return $document_root;
  }
  
  
  function get_mysql_type() {
    if (function_exists('mysqli_connect')) {
      return 'mysqli';
    }
    
    return 'mysql';
  }


  function get_shop_version() {  
    require_once(DIR_FS_CATALOG.DIR_ADMIN.'includes/version.php');
    defined('PROJECT_VERSION_NO') OR define('PROJECT_VERSION_NO', PROJECT_MAJOR_VERSION . '.' . PROJECT_MINOR_VERSION);
    
    return PROJECT_VERSION_NO;
  }


  function get_database_version_installer() {
    $check_query = xtc_db_query("SHOW COLUMNS FROM ".TABLE_DATABASE_VERSION." LIKE 'id'");
    if (xtc_db_num_rows($check_query) < 1) {
      xtc_db_query("ALTER TABLE `".TABLE_DATABASE_VERSION."` ADD `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
    }
    return get_database_version();
  }


  function get_checksum_install() {        
    $files_array = array();

    $version_array = get_checksum_version();
    if (is_array($version_array)) {
      foreach ($version_array as $file => $data) {
        $filename = ltrim($file, '/');
        if (substr($filename, 0, 6) == 'admin/') {
          $filename = substr_replace($filename, DIR_ADMIN, 0, 6);
        }

        if (is_file(DIR_FS_CATALOG.$filename)) {
          $files_array[$file] = array(
            'relativePath' => DIR_WS_CATALOG.$filename,
            'absolutePath' => DIR_FS_CATALOG.$filename,
            'filename' => basename($filename),
            'checkSum' => get_hash_from_file(DIR_FS_CATALOG.$filename),
          );
        }
      }
    }   
    ksort($files_array);
    
    return $files_array;
  }

  
  function get_hash_from_file($file) {
    return md5(preg_replace("'[\r\n\s]+'", '', file_get_contents($file)));
  }
  
  
  function get_checksum_version($version = '') {
    if ($version == '') {
      $version = get_shop_version();
    }
    modified_api::reset();
    $files_array = modified_api::request('modified/version/check/'.$version);
    
    return $files_array;
  }


  function get_integrity() {
    $version_array = get_checksum_version();
    $installed_array = get_checksum_install();
    
    $checksum_array = array();
    foreach ($installed_array as $index => $data) {
      if (isset($version_array[$index])
          && $version_array[$index]['checkSum'] != $data['checkSum']
          )
      {
        $data['checkSumOrig'] = $version_array[$index]['checkSum'];      
        $checksum_array[] = $data;      
      }
    }
    
    return $checksum_array;
  }


  function create_backup($checksum_array) {
    global $PHP_SELF;
    
    $backup_file = 'backup_'.date('Y-m-d-H-i').'.zip';
    
    if (count($checksum_array) > 0) {
      $zip = new ZipArchive();
      if ($zip->open(DIR_FS_CATALOG.DIR_ADMIN.'backups/'.$backup_file, ZipArchive::CREATE) === true) {
        foreach ($checksum_array as $data) {      
          $zip->addFile($data['absolutePath'], $data['relativePath']);      
        }
      }
      $zip->close();
      
      $backup = array(array(
        'LINK' => xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=download&file='.$backup_file),
        'NAME' => $backup_file,
        'SIZE' => number_format(filesize(DIR_FS_CATALOG.DIR_ADMIN.'backups/'.$backup_file)).' bytes',
        'DATE' => date(PHP_DATE_TIME_FORMAT, filemtime(DIR_FS_CATALOG.DIR_ADMIN.'backups/'.$backup_file))
      ));

      return $backup;
    }   
    
    return false; 
  }


  function update_shop() {
    global $messageStack;
    
    modified_api::reset();
    $response = modified_api::request('modified/version/install/');

    if (is_dir(DIR_FS_INSTALLER.'tmp')) {
      rrmdir(DIR_WS_INSTALLER.'tmp');
    }
    
    if (mkdir(DIR_FS_INSTALLER.'tmp', 0755)) {
      // save install
      set_time_limit(0);
      $fp = fopen (DIR_FS_INSTALLER.'tmp/'.$response['filename'], 'w+');
      $ch = curl_init($response['download']);
      curl_setopt($ch, CURLOPT_TIMEOUT, 600);
      curl_setopt($ch, CURLOPT_FILE, $fp); 
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_exec($ch); 
      curl_close($ch);
      fclose($fp);
      
      if (mkdir(DIR_FS_INSTALLER.'tmp/update', 0755, true)) {
        // extract install
        $zip = new ZipArchive();
        if ($zip->open(DIR_FS_INSTALLER.'tmp/'.$response['filename']) === true) {    
          $zip->extractTo(DIR_FS_INSTALLER.'tmp/update');
          $zip->close();
        } else {
          $messageStack->add('update', ERROR_INVALID_UPDATE_DOWNLOAD);
          return false;
        }
    
        // process
        $shoproot = DIR_FS_INSTALLER.'tmp/update/'.substr($response['filename'], 0, -4).'/shoproot';
        if (is_dir($shoproot)) {
          foreach ((new RecursiveIteratorIterator(new RecursiveDirectoryIterator($shoproot, RecursiveDirectoryIterator::SKIP_DOTS))) as $file) {
            $install_path = str_replace($shoproot, DIR_FS_CATALOG, $file->getPath());
            $install_path = rtrim($install_path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
            $install_path = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $install_path);
            $install_path = str_replace(DIR_FS_CATALOG.'admin/', DIR_FS_CATALOG.DIR_ADMIN, $install_path);
            
            if (strpos($install_path.$file->getFilename(), DIR_FS_CATALOG.'includes/configure.php') !== false
                || strpos($install_path.$file->getFilename(), DIR_FS_CATALOG.'includes/local/configure.php') !== false
                || strpos($install_path.$file->getFilename(), DIR_FS_INSTALLER) !== false
                ) 
            {
              continue;
            }
            
            if (!is_dir($install_path)) {
              mkdir($install_path, 0755, true);
            }
            rename($file->getPathname(), $install_path.$file->getFilename());
          }
        }
      } else {
        $messageStack->add('update', ERROR_CREATE_TMP_DIR);
        return false;
      }
    } else {
      $messageStack->add('update', ERROR_CREATE_TMP_DIR);
      return false;
    }
    
    return true;
  }


  function installer_log($messages) {
    error_log($messages."\n", 3, DIR_FS_LOG.'mod_installer_info_'.date('Y-m-d').'.log');
  }