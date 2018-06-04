<?php
  if (defined('MODULE_SYSTEM_DSGVO_STATUS')
      && MODULE_SYSTEM_DSGVO_STATUS == 'true'
      && MODULE_SYSTEM_DSGVO_CONTENT != ''
      )
  {
    require(DIR_FS_EXTERNAL.'dsgvo/modules/dsgvo_module.php');
  }
?>