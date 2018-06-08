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
      $backup_query = xtc_db_query("SELECT configuration_value 
                                      FROM ".TABLE_CONFIGURATION." 
                                     WHERE configuration_key = '".xtc_db_input($configuration[$i])."'"
                                   );
      if (xtc_db_num_rows($backup_query) > 0) {
        $backup = xtc_db_fetch_array($backup_query);

        xtc_db_query("INSERT INTO " . TABLE_MODULE_BACKUP . " (configuration_key, configuration_value, last_modified)
                           VALUES ('". xtc_db_input($configuration[$i]) ."', '".xtc_db_input($backup['configuration_value'])."', now())
                           ON DUPLICATE KEY UPDATE configuration_value = '".xtc_db_input($backup['configuration_value'])."', last_modified = now()");
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
    xtc_db_query("DELETE FROM ".TABLE_MODULE_BACKUP." WHERE configuration_key LIKE '" . xtc_db_input($configuration_key) . "'");
  }
  
?>