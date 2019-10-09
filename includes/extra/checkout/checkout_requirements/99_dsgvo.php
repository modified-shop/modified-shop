<?php
  if (defined('MODULE_SYSTEM_DSGVO_STATUS')
      && MODULE_SYSTEM_DSGVO_STATUS == 'true'
      && MODULE_SYSTEM_DSGVO_CONTENT != ''
      && $checkout_position[$current_page] == 1
      )
  {
    require(DIR_FS_EXTERNAL.'dsgvo/modules/dsgvo_module.php');
  }
?>