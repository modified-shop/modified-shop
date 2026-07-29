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
          require_once(DIR_FS_EXTERNAL.'dhl/DHLInternetmarke.php');
          $oID = (int)$_GET['oID'];
          $DHLInternetmarke = new DHLInternetmarke($_POST);
          $response = $DHLInternetmarke->CreateLabel($oID);
          
          if (is_array($response['message']) && count($response['message']) > 0) {
            foreach ($response['message'] as $error => $messages) {
              foreach ($messages as $message) {
                $messageStack->add_session($message, 'warning');
              }
            }
          }

          if (is_array($response['label']) && count($response['label']) > 0) {
            $messageStack->add_session(TEXT_IM_LABEL_CREATED, 'success');
          }
          xtc_redirect(xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action','subaction')).'action=edit'));              
          break;
       
        case 'im_delete':
          $tracking_id = (int)$_GET['tID'];
          $oID = (int)$_GET['oID'];
          
          $tracking_links_query = xtc_db_query("SELECT * 
                                                  FROM ".TABLE_ORDERS_TRACKING."
                                                 WHERE tracking_id = '".(int)$tracking_id."'");
          $tracking_links = xtc_db_fetch_array($tracking_links_query);

          require_once(DIR_FS_EXTERNAL.'dhl/DHLInternetmarke.php');
          $DHLInternetmarke = new DHLInternetmarke(array());
          $response = $DHLInternetmarke->DeleteLabel($tracking_links['parcel_id']);
          
          if (is_array($response['message']) && count($response['message']) > 0) {
            foreach ($response['message'] as $error => $messages) {
              foreach ($messages as $message) {
                $messageStack->add_session($message, 'warning');
              }
            }
          } else {
            $messageStack->add_session(TEXT_IM_LABEL_DELETED, 'success');
            xtc_db_query("DELETE FROM ".TABLE_ORDERS_TRACKING." WHERE tracking_id = '".(int)$tracking_id."'");
          }
          xtc_redirect(xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action', 'tID', 'subaction')).'action=edit'));
          break;
      }
    }
  }
