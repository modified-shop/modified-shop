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
  'MODULE_PAYMENT_PAYPALSUBSCRIPTION_TEXT_TITLE' => 'PayPal Abonnement',
  'MODULE_PAYMENT_PAYPALSUBSCRIPTION_TEXT_ADMIN_TITLE' => 'PayPal Abonnement (f&uuml;r wiederkehrende Zahlungen)',
  'MODULE_PAYMENT_PAYPALSUBSCRIPTION_TEXT_INFO' => ((!defined('RUN_MODE_ADMIN') && function_exists('xtc_href_link')) ? '<img src="'.xtc_href_link(DIR_WS_ICONS.'paypal.png', '', 'SSL', false).'" />' : ''),
  'MODULE_PAYMENT_PAYPALSUBSCRIPTION_TEXT_DESCRIPTION' => 'PayPal als Zahlungslink der den Kunden erst nach Bestellabschluss zur Verf&uuml;gung steht. Entscheiden Sie selber, wo der Kunde die Aufforderung zur Zahlung erh&auml;lt.<br /><br /><strong><font color="red">ACHTUNG:</font></strong> Damit der Bestellstatus korrekt gesetzt wird, m&uuml;ssen folgende <a href="'.xtc_href_link('paypal_webhook.php').'">Webhooks</a> in der PayPal Konfiguration eingestellt werden, damit der Status korrekt umgestellt wird:<ul><li>BILLING.SUBSCRIPTION.ACTIVATED</li><li>BILLING.SUBSCRIPTION.CANCELLED</li><li>BILLING.SUBSCRIPTION.CREATED</li><li>BILLING.SUBSCRIPTION.EXPIRED</li><li>BILLING.SUBSCRIPTION.PAYMENT.FAILED</li><li>BILLING.SUBSCRIPTION.RE-ACTIVATED</li><li>BILLING.SUBSCRIPTION.RENEWED</li><li>BILLING.SUBSCRIPTION.SUSPENDED</li><li>BILLING.SUBSCRIPTION.UPDATED</li></ul>',
  'MODULE_PAYMENT_PAYPALSUBSCRIPTION_ALLOWED_TITLE' => 'Erlaubte Zonen',
  'MODULE_PAYMENT_PAYPALSUBSCRIPTION_ALLOWED_DESC' => 'Geben Sie <b>einzeln</b> die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))',
  'MODULE_PAYMENT_PAYPALSUBSCRIPTION_STATUS_TITLE' => 'PayPal Abonnement aktivieren',
  'MODULE_PAYMENT_PAYPALSUBSCRIPTION_STATUS_DESC' => 'M&ouml;chten Sie Zahlungen per PayPal Abonnement akzeptieren?',
  'MODULE_PAYMENT_PAYPALSUBSCRIPTION_SORT_ORDER_TITLE' => 'Anzeigereihenfolge',
  'MODULE_PAYMENT_PAYPALSUBSCRIPTION_SORT_ORDER_DESC' => 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt',
  'MODULE_PAYMENT_PAYPALSUBSCRIPTION_ZONE_TITLE' => 'Zahlungszone',
  'MODULE_PAYMENT_PAYPALSUBSCRIPTION_ZONE_DESC' => 'Wenn eine Zone ausgew&auml;hlt ist, gilt die Zahlungsmethode nur f&uuml;r diese Zone.',
  'MODULE_PAYMENT_PAYPALSUBSCRIPTION_LP' => '<br /><br />F&uuml;r diese Zahlungsart ben&ouml;tigen Sie ein PayPal H&auml;ndler Konto.<br /><a target="_blank" href="http://www.paypal.com/de/webapps/mpp/referral/paypal-business-account2?partner_id=EHALBVD4M2RQS"><strong>Jetzt PayPal Konto hier erstellen.</strong></a>',

  'MODULE_PAYMENT_PAYPALSUBSCRIPTION_TEXT_EXTENDED_DESCRIPTION' => '<strong><font color="red">ACHTUNG:</font></strong> Bitte nehmen Sie noch die Einstellungen unter "Partner Module" -> "PayPal" -> <a href="'.xtc_href_link('paypal_config.php').'"><strong>"PayPal Konfiguration"</strong></a> vor!',

  'MODULE_PAYMENT_PAYPALSUBSCRIPTION_TEXT_ERROR_HEADING' => 'Hinweis',
  'MODULE_PAYMENT_PAYPALSUBSCRIPTION_TEXT_ERROR_MESSAGE' => 'PayPal Abonnement Zahlung wurde abgebrochen',  
);


foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
