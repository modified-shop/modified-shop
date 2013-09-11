<?php
if (defined('MODULE_JANOLAW_STATUS') && MODULE_JANOLAW_STATUS == 'True') {
  require_once(DIR_FS_EXTERNAL.'janolaw/janolaw.php');
  $janolaw = new janolaw_content();
}
?>