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
  'TEXT_PAYPAL_WEBHOOK_HEADING_TITLE' => 'PayPal Webhooks',

  'TEXT_PAYPAL_WEBHOOK_STATUS_NOT_DEFINED' => 'nicht konfiguriert',
  'TEXT_PAYPAL_WEBHOOK_INFO' => '<ul><li>Es sind keine Webhooks vorhanden</li><li>Mit Webhooks erhalten Sie Status&auml;nderungen der Bestellungen und Updates zu Zahlungen</li><li>Damit Sie Webhooks verwenden k&ouml;nnen, ist es zwingend notwendig, dass Sie ein g&uuml;ltiges SSL Zertifikat besitzen</li></ul>',
  'TEXT_PAYPAL_WEBHOOK_CREDENTIAL_INFO' => 'Es konnten keine verf&uuml;gbaren Webhooks von PayPal empfangen werden. Bitte &uuml;berpr&uuml;fen Sie die Einstellungen.',
  
  'TABLE_HEADING_URL' => 'URL',
  'TABLE_HEADING_WEBHOOK' => 'Webhook',
  'TABLE_HEADING_STATUS' => 'Status',
  'TABLE_HEADING_DESCRIPTION' => 'Beschreibung',
  
);


foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
?>