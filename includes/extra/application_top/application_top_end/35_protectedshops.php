<?php
if (defined('MODULE_PROTECTEDSHOPS_STATUS') && MODULE_PROTECTEDSHOPS_STATUS == 'true') {
  require_once(DIR_FS_EXTERNAL.'protectedshops/protectedshops_update.php');
  $protectedshops = new protectedshops_update();
  $protectedshops->check_update();
}
?>