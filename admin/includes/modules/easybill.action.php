<?php
/* -----------------------------------------------------------------------------------------
   $Id: easybill.action.php 4241 2013-01-11 13:47:24Z gtb-modified $

   Modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 Modified
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  require_once (DIR_FS_CATALOG.'includes/classes/class.easybill.php');

  if (isset($_GET['action']) && $_GET['action']=='easybill') {
  
    $easybill = new easybill($oID);
    //$easybill->order($oID);
    $easybill->setCustomer();
    
    if ($easybill->checkOrder()) {    
      $details = $easybill->details;
  
      // Invoice ID
      $documentID = $easybill->getDocumentID($details['billing_id']);
  
      if (isset($_GET['payment']) && $_GET['payment']=='true') {
        $easybill->setPayment($documentID);
      }

      if ($_GET['save']=='true') {
        // save Invoice
        $easybill->saveDocument($documentID, ((isset($_GET['download']) && $_GET['download']=='true') ? true:false));
      } elseif ($_GET['download']=='true') {
        // download Invoice
        $easybill->downloadDocument($documentID); 
      }
    } else {    
      // set Invoice ID
      $bill_nr = '';
      if (MODULE_EASYBILL_BILLINGID == 'Shop') {
        // todo Rechnungsnummer
        $bill_nr = '';
      }
      
      // create Invoice
      $easybill->createDocument($bill_nr, ((MODULE_EASYBILL_BILLSAVE=='Shop')?true:false), ((isset($_GET['download']) && $_GET['download']=='true') ? true:false));
    }
  }
?>