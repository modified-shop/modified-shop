<?php
  if (defined('MODULE_SYSTEM_DSGVO_STATUS')
      && MODULE_SYSTEM_DSGVO_STATUS == 'true'
      && MODULE_SYSTEM_DSGVO_CONTENT != ''
      && $_SERVER['REQUEST_METHOD'] == 'POST'
      )
  {
    require_once (DIR_WS_LANGUAGES.$_SESSION['language'].'/modules/system/system_dsgvo.php');
    
    if (isset ($_GET['action']) && ($_GET['action'] == 'dsgvo')) {
      require(DIR_FS_EXTERNAL.'dsgvo/modules/dsgvo_action.php');
      require(DIR_FS_EXTERNAL.'dsgvo/modules/dsgvo_module.php');
    } elseif (isset($_SESSION['dsgvo']) 
              && count($_SESSION['dsgvo']) > 0
              && basename($PHP_SELF) != FILENAME_POPUP_CONTENT
              )
    {
      unset($_SESSION['dsgvo']);
      xtc_redirect(xtc_href_link(FILENAME_LOGOFF, '', 'SSL'));
    }
  }
?>