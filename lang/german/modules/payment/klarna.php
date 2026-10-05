<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// texts of the module klarna, the texts shared by all Klarna modules are in klarna_shared.php
$klarna_code = 'KLARNA';
include(DIR_FS_CATALOG.'lang/german/modules/payment/klarna_shared.php');

$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_TITLE'] = 'Klarna';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_ERROR_MESSAGE'] = 'Die Zahlung mit Klarna wurde abgebrochen.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_INSTALL_NOTE'] = '<br /><br /><b>Hinweis:</b> Nach der Installation ist Klarna inaktiv. Pr&uuml;fen Sie Bestellstatus, Capture und Zonen, dann setzen Sie den Status auf Ja. Solange Klarna aktiv ist, blendet der Checkout die alten Klarna-Module aus.<br /><br />Regeln, die nur die Codes der alten Module nennen, gelten nicht f&uuml;r klarna. Tragen Sie <b>klarna</b> zus&auml;tzlich ein bei: Nicht erlaubte Zahlungsweisen der Kundengruppen und der Kunden, Unerlaubte Download-Zahlungsmodule, Unerlaubte Zahlungsmodule des Gutscheins, Zahlarten abh&auml;ngig von der Versandart und Rabatt &amp; Zuschlag auf Zahlungsarten. Die Pr&uuml;fung &quot;Klarna pr&uuml;fen&quot; zeigt, wo noch ein alter Code steht.';

foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
