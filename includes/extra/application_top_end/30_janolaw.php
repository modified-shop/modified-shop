<?php
if (defined('MODULE_JANOLAW_STATUS') && MODULE_JANOLAW_STATUS == 'True') {
  if ($_SESSION['customers_status']['customers_status_id'] == 0) {
    require_once(DIR_FS_EXTERNAL.'janolaw/janolaw.php');
    $janolaw = new janolaw_content();
  }
}
?>