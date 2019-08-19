<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  if (defined('MODULE_INTERNETMARKE_STATUS') && MODULE_INTERNETMARKE_STATUS == 'true') {
    if (isset($_GET['subaction'])) {
      switch ($_GET['subaction']) {
        case 'im_insert':
          $oID = (int)$_GET['oID'];
          require_once(DIR_FS_EXTERNAL.'internetmarke/internetmarke.php');
          $internetmarke = new mod_internetmarke($oID);
          if ($internetmarke->getError() === false) {
            $internetmarke->createLabel($_POST);
          }
          xtc_redirect(xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action','subaction')).'action=edit'));              
          break;

        case 'im_download':
          $imID = (int)$_GET['imID'];
          $oID = (int)$_GET['oID'];
          require_once(DIR_FS_EXTERNAL.'internetmarke/internetmarke.php');
          $internetmarke = new mod_internetmarke($oID);
          if ($internetmarke->getError() === false) {
            $internetmarke->getLabel($imID);
          }
          xtc_redirect(xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action','subaction')).'action=edit'));
          break;
       
        case 'im_delete':
          $tracking_id = (int)$_GET['tID'];
          $oID = (int)$_GET['oID'];
          
          xtc_db_query("DELETE FROM ".TABLE_ORDERS_TRACKING." WHERE tracking_id = '".(int)$tracking_id."'");
          xtc_redirect(xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action','subaction')).'action=edit'));
          break;
      }
    }
  }
?>