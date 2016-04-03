<?php
  ### xs:booster
  if (isset($_SESSION['xtb0'])) {
    define('XTB_CHECKOUT_PROCESS', __LINE__);
    require_once (DIR_FS_CATALOG.'callback/xtbooster/xtbcallback.php');
  }
?>