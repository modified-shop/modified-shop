<?php
  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  if (defined('MODULE_SHIPCLOUD_STATUS') && MODULE_SHIPCLOUD_STATUS == 'True') {
    if (isset($_GET['subaction'])) {
      switch ($_GET['subaction']) {
        case 'inserttracking':
          $oID = (int)$_GET['oID'];
          require_once(DIR_FS_EXTERNAL.'shipcloud/class.shipcloud.php');
          $shipcloud = new shipcloud($oID);
          $shipcloud->create_label($_POST);
          xtc_redirect(xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action','subaction')).'action=edit'));              
          break;
        
        case 'deletetracking':
          $tracking_id = (int)$_GET['tID'];
          require_once(DIR_FS_EXTERNAL.'shipcloud/class.shipcloud.php');
          $shipcloud = new shipcloud($oID);
          $shipcloud->delete_label($tracking_id);
          xtc_db_query("DELETE FROM ".TABLE_ORDERS_TRACKING." WHERE tracking_id = '".(int)$tracking_id."'");
          xtc_redirect(xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action','subaction')).'action=edit'));
          break;
      }
    }
  }
?>