<?php
/* -----------------------------------------------------------------------------------------
   $Id: billpayCallback.php 4307 2013-01-14 07:38:50Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   Copyright (c) 2010 Billpay GmbH

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
class billpayCallback {

  /**
   * @param unknown_type $backlink
   * @param unknown_type $projectName
   * @return string
   */
  function getBillpayRegistrationFormPage($backlink, $projectHomepage, $paymentIdentifier){

    $html = '
      <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
      <html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">
      <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <title>Schnellregistrierung | Billpay</title>
      </head>
      <body onload="document.getElementById(\'form\').submit()">
        <form method="post" action="https://www.billpay.de/landingPages/modified-shop/" id="form">
          <input type="hidden" name="IhrAnliegen_" value="Haendleranfrage">
          <input type="hidden" name="Firma_" value="'.STORE_NAME.'">
          <input type="hidden" name="Vorname_Name_" value="'.STORE_OWNER.'">
          <input type="hidden" name="Strasse_Hausnr_" value="">
          <input type="hidden" name="PLZ_" value="">
          <input type="hidden" name="Ort_" value="">
          <input type="hidden" name="Telefonnummer_" value="">
          <input type="hidden" name="E_Mail_" value="'.STORE_OWNER_EMAIL_ADDRESS.'">
          <input type="hidden" name="Shop_System_" value="modified-shop">
          <input type="hidden" name="Shop_URL_" value="'.$projectHomepage.'">
          <input type="hidden" name="IhreNachricht_" value="">
          <input type="hidden" name="backlink" value="'.$backlink.'">';

    if ($paymentIdentifier == 'BILLPAY') {
      $html .= '<input type="hidden" name="KaufaufRechnung[KaufaufRechnung]" value="KaufaufRechnung">';
    }
    else if ($paymentIdentifier == 'BILLPAYDEBIT') {
      $html .= '<input type="hidden" name="KaufperLastschrift[KaufperLastschrift]" value="KaufperLastschrift">';
    }

    $html .=    '<input type="hidden" name="debug" value="0">
            <input type="hidden" name="origin" value="modified-shop">
            <input type="hidden" name="init" value="1">
          <noscript><input type="submit"></noscript>
        </form>
      </body>
      </html>
      ';
    return $html;
  }

}
?>