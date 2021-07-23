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

  if (defined('MODULE_DHL_STATUS') && MODULE_DHL_STATUS == 'True') {
    if (isset($_GET['saction']) && $_GET['saction'] == 'createlabel') {
      require_once(DIR_FS_EXTERNAL.'dhl/DHLBusinessShipment.php');
      $oID = (int)$_POST['oID'];
      $dhl = new DHLBusinessShipment($_POST);
      $response = $dhl->CreateLabel($oID, false);
      
      if (is_array($response) && isset($response['message'])) {
        if (is_array($response['message'])) {
          foreach ($response['message'] as $message) {
            $messageStack->add_session(utf8_decode($message), 'warning');
          }
        } else {
          $messageStack->add_session(utf8_decode($response['message']), 'warning');
        }
      } else {
        $_SESSION['DHLparcel_id'] = $response;
        $messageStack->add_session(TEXT_DHL_CREATE_SUCCESS, 'success');
        
        if ($_POST['status_update'] > 0) {
          $check_query = xtc_db_query("SELECT *
                                         FROM ".TABLE_ORDERS_TRACKING."
                                        WHERE parcel_id = '".xtc_db_input($_SESSION['DHLparcel_id'])."'
                                          AND dhl_label_url != ''");
          if (xtc_db_num_rows($check_query) > 0) {
            $check = xtc_db_fetch_array($check_query);
            
            $order = new order($oID);
            require_once(DIR_FS_CATALOG.DIR_WS_CLASSES.'xtcPrice.php');
            $xtPrice = new xtcPrice($order->info['currency'], $order->info['status']);
        
            $status = $_POST['status_update'];
            $comments = sprintf(TEXT_DHL_ORDER_COMMENT, $_SESSION['DHLparcel_id']);
            $order_updated = false;
            $_POST['notify'] = 'on';
            $_POST['notify_comments'] = 'off';
            $_POST['tracking_id'] = array($check['tracking_id']);
      
            include (DIR_WS_MODULES.'orders_update.php');
        
            if ($order_updated) {
              $messageStack->add_session(SUCCESS_ORDER_UPDATED, 'success');
            }
          }
        }
      } 
      xtc_redirect(xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action', 'subaction')).'action=edit'));
    }

    if (isset($_GET['saction']) && $_GET['saction'] == 'deletetracking') {
      $tracking_id = (int)$_GET['tID'];
      $tracking_links_query = xtc_db_query("SELECT * 
                                              FROM ".TABLE_ORDERS_TRACKING."
                                             WHERE tracking_id = '".(int)$tracking_id."'");
      $tracking_links = xtc_db_fetch_array($tracking_links_query);
  
      require_once(DIR_FS_EXTERNAL.'dhl/DHLBusinessShipment.php');
      $dhl = new DHLBusinessShipment(array());
      $response = $dhl->DeleteLabel($tracking_links['parcel_id']);
      
      if (is_array($response) && isset($response['message'])) {
        if (is_array($response['message'])) {
          foreach ($response['message'] as $message) {
            $messageStack->add_session(utf8_decode($message), 'warning');
          }
        } else {
          $messageStack->add_session(utf8_decode($response['message']), 'warning');
        }
      } else {
        $messageStack->add_session(TEXT_DHL_DELETE_SUCCESS, 'success');
      }
      xtc_db_query("DELETE FROM ".TABLE_ORDERS_TRACKING." WHERE tracking_id = '".(int)$tracking_id."'");
    
      xtc_redirect(xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action', 'tID', 'subaction')).'action=edit'));
    }
  }
