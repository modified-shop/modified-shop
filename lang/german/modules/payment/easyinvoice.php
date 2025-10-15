<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  $lang_array = array(
    'MODULE_PAYMENT_EASYINVOICE_TEXT_TITLE' => 'easyCredit-Rechnung',
    'MODULE_PAYMENT_EASYINVOICE_TEXT_INFO' => '',
    'MODULE_PAYMENT_EASYINVOICE_TEXT_DESCRIPTION' => '',
    'MODULE_PAYMENT_EASYINVOICE_ALLOWED_TITLE' => 'Erlaubte Zonen',
    'MODULE_PAYMENT_EASYINVOICE_ALLOWED_DESC' => 'Geben Sie <b>einzeln</b> die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))',
    'MODULE_PAYMENT_EASYINVOICE_STATUS_TITLE' => 'Modul aktivieren',
    'MODULE_PAYMENT_EASYINVOICE_STATUS_DESC' => 'M&ouml;chten Sie Zahlungen mit easyCredit-Rechnungskauf akzeptieren?',
    'MODULE_PAYMENT_EASYINVOICE_SORT_ORDER_TITLE' => 'Anzeigereihenfolge',
    'MODULE_PAYMENT_EASYINVOICE_SORT_ORDER_DESC' => 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.',
    'MODULE_PAYMENT_EASYINVOICE_ZONE_TITLE' => 'Zahlungszone',
    'MODULE_PAYMENT_EASYINVOICE_ZONE_DESC' => 'Wenn eine Zone ausgew&auml;hlt ist, gilt die Zahlungsmethode nur f&uuml;r diese Zone.',
    'MODULE_PAYMENT_EASYINVOICE_ORDER_STATUS_ID_TITLE' => 'Tempor&auml;rer Bestellstatus',
    'MODULE_PAYMENT_EASYINVOICE_ORDER_STATUS_ID_DESC' => 'Geben Sie den Bestellstatus f&uuml;r nicht best&auml;tigte Bestellungen an.',
    'MODULE_PAYMENT_EASYINVOICE_ORDER_STATUS_SUCCESS_ID_TITLE' => 'Erfolgreicher Bestellstatus',
    'MODULE_PAYMENT_EASYINVOICE_ORDER_STATUS_SUCCESS_ID_DESC' => 'Geben Sie den Bestellstatus f&uuml;r erfolgreiche Bestellungen an.',
    'MODULE_PAYMENT_EASYINVOICE_SHOP_ID_TITLE' => 'Webshop ID',
    'MODULE_PAYMENT_EASYINVOICE_SHOP_ID_DESC' => 'Ihre Webshop ID finden Sie im easyCredit H&auml;ndlerinterface im Unterpunkt Shopadministration.',
    'MODULE_PAYMENT_EASYINVOICE_SHOP_TOKEN_TITLE' => 'API Kennwort',
    'MODULE_PAYMENT_EASYINVOICE_SHOP_TOKEN_DESC' => 'Ihr API Kennwort legen Sie im easyCredit H&auml;ndlerinterface im Unterpunkt Shopadministration selbst fest.',
    'MODULE_PAYMENT_EASYINVOICE_SHOP_SECRET_TITLE' => 'API Secret',
    'MODULE_PAYMENT_EASYINVOICE_SHOP_SECRET_DESC' => 'Ihr API Secret legen Sie im easyCredit H&auml;ndlerinterface im Unterpunkt Shopadministration selbst fest.',
    'MODULE_PAYMENT_EASYINVOICE_LOG_LEVEL_TITLE' => 'Loglevel',
    'MODULE_PAYMENT_EASYINVOICE_LOG_LEVEL_DESC' => 'Geben Sie den Loglevel an. Standard: "error"',
  
    'MODULE_PAYMENT_EASYINVOICE_TEXT_ERROR_HEADING' => 'Hinweis',
    'MODULE_PAYMENT_EASYINVOICE_TEXT_ERROR_MESSAGE' => 'Die Zahlung mit easyCredit-Rechnungskauf wurde abgebrochen',
    'MODULE_PAYMENT_EASYINVOICE_TEXT_ERROR_CHECKBOX' => 'Bitte akzeptieren Sie die zus&auml;tzlich notwendigen Vereinbarungen f&uuml;r easyCredit-Rechnungskauf',
    'MODULE_PAYMENT_EASYINVOICE_TEXT_LEGAL' => 'Vorvertragliche Informationen zum Rechnungskauf hier abrufen',
  
    'TEXT_EASYINVOICE_TBAID' => 'Vorgangskennung',
    'TEXT_EASYINVOICE_RATING_PLAN' => 'Finanzierung ab %s in %s Raten mit easyCredit-Rechnungskauf',
    'TEXT_EASYINVOICE_RATING_PLAN_SHORT' => 'Finanzierung ab %s im Monat',
    'TEXT_EASYINVOICE_RATING_PLAN_CALC' => 'mehr Infos zum Rechnungskauf',
    'TEXT_EASYINVOICE_LEGAL' => 'Repr&auml;sentatives Beispiel gem. &sect; 6a PAngV',
    'TEXT_EASYINVOICE_NOMINAL_RATE' => 'Sollzinssatz p.a. fest f&uuml;r die gesamte Laufzeit',
    'TEXT_EASYINVOICE_EFFECTIVE_RATE' => 'effektiver Jahreszins',
    'TEXT_EASYINVOICE_TOTAL_COST' => 'Gesamtbetrag',
    'TEXT_EASYINVOICE_TOTAL_NETTO' => 'Nettodarlehensbetrag',
    'TEXT_EASYINVOICE_TOTAL_INTEREST' => 'Zinsbetrag',
    'TEXT_EASYINVOICE_MONTHLY_PAYMENT' => 'monatliche Raten in H&ouml;he von je',
    'TEXT_EASYINVOICE_LAST_PAYMENT' => 'letzte Rate',  

    'BUTTON_EASYCREDIT_CHECK' => 'API Daten pr&uuml;fen',  
  );
  
  foreach ($lang_array as $key => $val) {
    defined($key) or define($key, $val);
  }
