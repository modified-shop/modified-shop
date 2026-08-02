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
          if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            xtc_redirect(xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action', 'subaction')).'action=edit'));
          }

          require_once(DIR_FS_EXTERNAL.'dhl/DHLInternetmarke.php');
          $oID = (int)$_GET['oID'];
          $DHLInternetmarke = new DHLInternetmarke($_POST);
          $response = $DHLInternetmarke->CreateLabel($oID);
          
          if (is_array($response['message']) && count($response['message']) > 0) {
            foreach ($response['message'] as $error => $messages) {
              if (!is_array($messages)) {
                continue;
              }
              foreach ($messages as $message) {
                if (is_scalar($message)) {
                  $messageStack->add_session(encode_htmlspecialchars((string)$message), 'warning');
                }
              }
            }
          }

          if (is_array($response['label']) && count($response['label']) > 0) {
            $messageStack->add_session(TEXT_IM_LABEL_CREATED, 'success');
          }
          xtc_redirect(xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action','subaction')).'action=edit'));              
          break;
       
        case 'im_delete':
          if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            xtc_redirect(xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action', 'tID', 'subaction')).'action=edit'));
          }

          $tracking_id = (int)$_GET['tID'];
          $oID = (int)$_GET['oID'];
          
          $tracking_links_query = xtc_db_query("SELECT * 
                                                  FROM ".TABLE_ORDERS_TRACKING."
                                                 WHERE tracking_id = '".(int)$tracking_id."'
                                                   AND orders_id = '".(int)$oID."'
                                                   AND (external = 3
                                                        OR (external = 1 AND im_orders_id IS NOT NULL AND im_orders_id != '')
                                                       )");
          if (xtc_db_num_rows($tracking_links_query) > 0) {
            $tracking_links = xtc_db_fetch_array($tracking_links_query);

            if (!array_key_exists('im_retoure_transaction_id', $tracking_links)
                || !array_key_exists('im_retoure_id', $tracking_links)
                )
            {
              $messageStack->add_session(TEXT_IM_MODULE_UPDATE_REQUIRED, 'warning');
            } else {
              require_once(DIR_FS_EXTERNAL.'dhl/DHLInternetmarke.php');
              $DHLInternetmarke = new DHLInternetmarke(array());

              if ($tracking_links['external'] == '1') {
                $shipment_number = $tracking_links['im_orders_id'];
                $identifier = 'shopOrderId';
              } else {
                $shipment_number = isset($tracking_links['im_voucher_id']) && $tracking_links['im_voucher_id'] != ''
                  ? $tracking_links['im_voucher_id']
                  : $tracking_links['parcel_id'];
                $identifier = 'voucherId';
              }

              if (!empty($tracking_links['im_retoure_transaction_id'])
                  && !empty($tracking_links['im_retoure_id'])
                  )
              {
                $response = $DHLInternetmarke->GetRefundStatus(
                  $tracking_links['im_retoure_transaction_id'],
                  $tracking_links['im_retoure_id'],
                  $shipment_number,
                  $identifier
                );
              } else {
                $response = $DHLInternetmarke->DeleteLabel($shipment_number, $identifier);
              }

              $has_messages = false;
              if (isset($response['message'])
                  && is_array($response['message'])
                  && count($response['message']) > 0
                  )
              {
                foreach ($response['message'] as $error => $messages) {
                  if (!is_array($messages)) {
                    continue;
                  }
                  foreach ($messages as $message) {
                    if (is_scalar($message)) {
                      $has_messages = true;
                      $messageStack->add_session(encode_htmlspecialchars((string)$message), 'warning');
                    }
                  }
                }
              }

              $reference_saved = !empty($tracking_links['im_retoure_transaction_id'])
                                 && !empty($tracking_links['im_retoure_id']);
              if ($reference_saved === false
                  && isset($response['status'])
                  && in_array($response['status'], array('successful', 'pending'), true)
                  && isset($response['refund'])
                  && is_array($response['refund'])
                  )
              {
                $reference_saved = $DHLInternetmarke->SaveRefundReference(
                  $tracking_id,
                  $oID,
                  $response['refund']
                );
              }

              if (isset($response['status']) && $response['status'] == 'successful') {
                $delete_query = xtc_db_query("DELETE FROM ".TABLE_ORDERS_TRACKING."
                                                    WHERE tracking_id = '".(int)$tracking_id."'
                                                      AND orders_id = '".(int)$oID."'");
                $messageStack->add_session(
                  $delete_query !== false ? TEXT_IM_LABEL_DELETED : TEXT_IM_LABEL_LOCAL_DELETE_ERROR,
                  $delete_query !== false ? 'success' : 'warning'
                );
                if ($delete_query === false && $reference_saved === false) {
                  $messageStack->add_session(TEXT_IM_LABEL_REFUND_REFERENCE_ERROR, 'warning');
                }
              } elseif (isset($response['status']) && $response['status'] == 'pending') {
                $messageStack->add_session(
                  $reference_saved === true ? TEXT_IM_LABEL_REFUND_PENDING : TEXT_IM_LABEL_REFUND_REFERENCE_ERROR,
                  'warning'
                );
              } else {
                if (!empty($tracking_links['im_retoure_transaction_id'])
                    && !empty($tracking_links['im_retoure_id'])
                    )
                {
                  $DHLInternetmarke->ClearRefundReference($tracking_id, $oID);
                }
                if ($has_messages === false) {
                  $messageStack->add_session(TEXT_IM_LABEL_DELETE_ERROR, 'warning');
                }
              }
            }
          } else {
            $messageStack->add_session(TEXT_IM_LABEL_NOT_FOUND, 'warning');
          }
          xtc_redirect(xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action', 'tID', 'subaction')).'action=edit'));
          break;
      }
    }
  }
