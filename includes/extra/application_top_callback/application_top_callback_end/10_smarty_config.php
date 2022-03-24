<?php
// listing
if (isset($_GET['show'])) {
  $_SESSION['listbox'] = (($_GET['show'] == 'box') ? 'true' : 'false');
}

// load Template config
if (file_exists(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/config/config.php')) {
  defined('SPECIALS_CATEGORIES') or define('SPECIALS_CATEGORIES', false);
  defined('WHATSNEW_CATEGORIES') or define('WHATSNEW_CATEGORIES', false);

  require_once(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/config/config.php');
}
?>