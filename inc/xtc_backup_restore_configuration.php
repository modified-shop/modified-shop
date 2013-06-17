<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
   
  function xtc_backup_configuration($configuration) {
    if (!is_array($configuration)) {
      $configuration = array($configuration);
    }
    
    for ($i=0, $x=sizeof($configuration); $i<$x; $i++) {
      $check_query = xtc_db_query("SELECT * FROM ".TABLE_CONFIGURATION." WHERE configuration_key = '".$configuration[$i].'_BAK'."'");
      if (xtc_db_num_rows($check_query)>0) {
        $update = xtc_db_fetch_array($check_query);
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value='" . xtc_db_input($update['configuration_value']) . "', last_modified = NOW() WHERE configuration_key='" . $configuration[$i].'_BAK' . "'");
      } else {
        $backup_query = xtc_db_query("SELECT * FROM ".TABLE_CONFIGURATION." WHERE configuration_key = '".$configuration[$i]."'");
        if (xtc_db_num_rows($backup_query)>0) {
          $backup = xtc_db_fetch_array($backup_query);
          unset($backup['configuration_id']);
          $backup['configuration_key'] = $backup['configuration_key'].'_BAK';
          $backup['last_modified'] = 'NOW()';
          xtc_db_perform(TABLE_CONFIGURATION, $backup);
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
      if (xtc_db_num_rows($check_query)>0) {
        $restore_query = xtc_db_query("SELECT * FROM ".TABLE_CONFIGURATION." WHERE configuration_key = '".$configuration[$i].'_BAK'."'");
        if (xtc_db_num_rows($restore_query)>0) {
          $restore = xtc_db_fetch_array($restore_query);
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '" . xtc_db_input($restore['configuration_value']) . "', last_modified = NOW() WHERE configuration_key = '" . $configuration[$i] . "'");
          xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION." WHERE configuration_key = '" . $configuration[$i].'_BAK' . "'");
        }
      }
    }
  }
  
 ?>