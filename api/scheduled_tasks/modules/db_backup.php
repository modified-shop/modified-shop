<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  function cron_db_backup() {
    global $dump;
    
    define('TEXT_INFO_DO_BACKUP', '');
    define('PHP_DATE_TIME_FORMAT', 'd.m.Y H:i:s');
    
    // paths
    define('DIR_FS_BACKUP', DIR_FS_CATALOG.DIR_ADMIN.'backups/');
    
    // include needed functions
    require_once(DIR_FS_INC . 'xtc_set_time_limit.inc.php');
    require_once(DIR_FS_CATALOG.DIR_ADMIN.'includes/functions/db_functions.php');    
    
    $action = 'backupnow';
    $_POST['compress'] = 'gzip';
    include (DIR_FS_CATALOG.DIR_ADMIN.'includes/db_actions.php');
    
    if (count($dump['tables']) > 0) {
      $dump['complete_inserts'] = 'yes';
      $dump['remove_collate'] = 'no';
      $dump['remove_engine'] = 'no';
      
      foreach ($dump['tables'] as $tables) {
        $dump['table_records'] = GetTableInfo($tables);
        $dump['anzahl_zeilen'] = BACKUP_ROWS;
        $dump['zeilen_offset'] = 0;
        $dump['table_offset'] = 1;

        while ($dump['table_offset'] == 1) {
          $time = time();
          GetTableData($tables);
          
          $time_gap = time() - $time; 
          if ($time_gap > BACKUP_GAP && $dump['anzahl_zeilen'] > 10) {
            $dump['anzahl_zeilen'] -= BACKUP_ROWS_STEP;
            if ($dump['anzahl_zeilen'] < 10) {
              $dump['anzahl_zeilen'] = 10;
            }
          } elseif ($time_gap < BACKUP_GAP) {
            $dump['anzahl_zeilen'] += BACKUP_ROWS_STEP;
          }
          if ($dump['anzahl_zeilen'] >= BACKUP_ROWS_MAX) {
            $dump['anzahl_zeilen'] = BACKUP_ROWS_MAX;
          }
        }
      }    
    }
    
    // cleanup
    $exts = array("sql","sql\.zip","sql\.gz");
    
    $dir = dir(DIR_FS_BACKUP);
    $contents_array = array();
    while ($file = $dir->read()) {
      if (!is_dir(DIR_FS_BACKUP . $file)) {
        foreach ($exts as $ext) {
          if (preg_match('/\.'.$ext.'$/i', $file)
              && filectime(DIR_FS_BACKUP . $file) < (time() - (86400 * 7))
              ) 
          {
            unlink(DIR_FS_BACKUP . $file);
          }          
        }
      }
    }
    
    return true;
  }