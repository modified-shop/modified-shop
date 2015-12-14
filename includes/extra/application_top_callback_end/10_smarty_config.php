<?php
// listing
if (isset($_GET['show'])) {
  $_SESSION['listbox'] = (($_GET['show'] == 'box') ? 'true' : 'false');
}

// load Template config
if (file_exists(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/config/config.php')) {
  require(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/config/config.php');
}
?>