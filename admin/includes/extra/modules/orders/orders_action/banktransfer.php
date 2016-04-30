<?php
  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  ## banktransfer
  if (isset($_GET['subaction']) && $_GET['subaction'] == 'deletebanktransfer') {
    xtc_db_query("DELETE FROM ".TABLE_BANKTRANSFER." WHERE orders_id = '".$oID."'");
    xtc_redirect(xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action', 'subaction')).'action=edit'));
  }
?>