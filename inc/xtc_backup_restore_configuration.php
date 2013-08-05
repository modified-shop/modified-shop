<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  define('TABLE_MODULE_BACKUP','module_backup');
  
  function xtc_backup_configuration($configuration) {
    if (!is_array($configuration)) {
      $configuration = array($configuration);
    }
    
    for ($i=0, $x=sizeof($configuration); $i<$x; $i++) {
      $check_query = xtc_db_query("SELECT * FROM ".TABLE_MODULE_BACKUP." WHERE configuration_key = '".$configuration[$i]."'");
      if (xtc_db_num_rows($check_query) > 0) {
        $update = xtc_db_fetch_array($check_query);
        xtc_db_query("UPDATE " . TABLE_MODULE_BACKUP . " 
                         SET configuration_value='" . xtc_db_input($update['configuration_value']) . "', 
                             last_modified = now() 
                       WHERE configuration_key='" . $configuration[$i] . "'
                     ");
      } else {
        $backup_query = xtc_db_query("SELECT * FROM ".TABLE_CONFIGURATION." 
                                       WHERE configuration_key = '".$configuration[$i]."'
                                    ");
        if (xtc_db_num_rows($backup_query) > 0) {
          $backup = xtc_db_fetch_array($backup_query);
          unset($backup['configuration_id']);
          unset($backup['configuration_group_id']);
          unset($backup['sort_order']);
          unset($backup['date_added']);
          unset($backup['use_function']);
          unset($backup['set_function']);         
          $backup['configuration_key'] = $backup['configuration_key'];
          $backup['last_modified'] = 'now()';
          xtc_db_perform(TABLE_MODULE_BACKUP, $backup);
        }
      }
    }
  }


  function xtc_restore_configuration($configuration) {
    if (!is_array($configuration)) {
      $configuration = array($configuration);
    }
    for ($i=0, $x=sizeof($configuration); $i<$x; $i++) {
      $check_query = xtc_db_query("SELECT * FROM ".TABLE_CONFIGURATION." WHERE configuration_key = '".$configuration[$i]."'");
      if (xtc_db_num_rows($check_query) > 0) {
        $restore_query = xtc_db_query("SELECT * FROM ".TABLE_MODULE_BACKUP." WHERE configuration_key = '".$configuration[$i]."'");
        if (xtc_db_num_rows($restore_query )> 0) {
          $restore = xtc_db_fetch_array($restore_query);
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " 
                           SET configuration_value = '" . xtc_db_input($restore['configuration_value']) . "', 
                               last_modified = now() 
                         WHERE configuration_key = '" . $configuration[$i] . "'
                      ");
        }
      }
    }
  }


  function xtc_reset_configuration($configuration) {
    if (!is_array($configuration)) {
      $configuration = array($configuration);
    }
    $configuration_key = substr($configuration[0], 0, strrpos($configuration[0], '_'));
    xtc_db_query("DELETE FROM ".TABLE_MODULE_BACKUP." WHERE configuration_key LIKE '" . $configuration_key . "'");
  }
  
?>