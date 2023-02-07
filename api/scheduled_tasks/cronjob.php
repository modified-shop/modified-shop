<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  include (dirname(__FILE__).'/../../includes/application_top_callback.php');

  // include needed functions
  require_once(DIR_FS_INC.'next_scheduled_time.inc.php');
  
  $tasks_query = xtc_db_query("SELECT *
                                 FROM ".TABLE_SCHEDULED_TASKS."
                                WHERE status = 1
                                  AND time_next <= ".time());
  if (xtc_db_num_rows($tasks_query) > 0) {      
    while ($tasks = xtc_db_fetch_array($tasks_query)) {

      // When should this next be run?
      $time_next = next_scheduled_time($tasks['time_regularity'], $tasks['time_unit'], $tasks['time_offset']);

      $duration = $tasks['time_regularity'];
      if ($tasks['time_unit'] == 'm') {
        $duration *= 60;
      } elseif ($tasks['time_unit'] == 'h') {
        $duration *= 3600;
      } elseif ($tasks['time_unit'] == 'd') {
        $duration *= 86400;
      } elseif ($tasks['time_unit'] == 'w' || $tasks['time_unit'] == 'o') {
        $duration *= 604800;
      }
    
      // If we were really late running this task actually skip the next one.
      if (time() + ($duration / 2) > $time_next) {
        $time_next += $duration;
      }
    
      xtc_db_query("UPDATE ".TABLE_SCHEDULED_TASKS."
                       SET time_next = ".(int)$time_next."
                     WHERE tasks_id = ".(int)$tasks['tasks_id']);
      
      if (is_file(DIR_FS_CATALOG.'api/scheduled_tasks/modules/'.$tasks['tasks'].'.php')) {
        require_once(DIR_FS_CATALOG.'api/scheduled_tasks/modules/'.$tasks['tasks'].'.php');
        
        if (function_exists('cron_'.$tasks['tasks'])) {        
          ignore_user_abort(true);
        
          $time_start = microtime(true);
          $completed = call_user_func('cron_'.$tasks['tasks']);

          if ($completed) {
            $total_time = round((microtime(true)-$time_start), 5);
          
            $sql_data_array = array(
              'tasks_id' => $tasks['tasks_id'],
              'time_run' => time(),
              'time_taken' => $total_time
            );
            xtc_db_perform(TABLE_SCHEDULED_TASKS_LOG, $sql_data_array);            
          }
        }
      }
      
      if ($tasks['time_unit'] == 'o') {
        xtc_db_query("DELETE FROM ".TABLE_SCHEDULED_TASKS."
                            WHERE tasks_id = ".(int)$tasks['tasks_id']);
      }
    }
  }
  
  $next_event = time() + 86400;
  $check_query = xtc_db_query("SELECT *
                                 FROM ".TABLE_SCHEDULED_TASKS."
                                WHERE status = 1
                             ORDER BY time_next ASC
                                LIMIT 1");
  if (xtc_db_num_rows($check_query) > 0) {
    $check = xtc_db_fetch_array($check_query);
    $next_event = $check['time_next'];
  }
  
  xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                   SET configuration_value = ".(int)$next_event."
                 WHERE configuration_key = 'CRONJOB_NEXT_EVENT_TIME'");
