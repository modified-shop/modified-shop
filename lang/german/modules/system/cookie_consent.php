<?php
  /* --------------------------------------------------------------
   $Id: cookie_consent.js.php $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2019 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

  define('MODULE_COOKIE_CONSENT_STATUS_TITLE', 'Modul Status');
  define('MODULE_COOKIE_CONSENT_STATUS_DESC', 'Modul aktivieren?');
  define('MODULE_COOKIE_CONSENT_SET_READABLE_COOKIE_TITLE', 'Lesbares Cookie setzen');
  define('MODULE_COOKIE_CONSENT_SET_READABLE_COOKIE_DESC', '');

  define('MODULE_COOKIE_CONSENT_SET_READABLE_COOKIE_DETAIL', 'Verwenden Sie den Google-Tag-Manager oder andere Software, darf auch diese nur dann Scripte einf&uuml;gen und Cookies setzen, wenn der Besucher das erlaubt. Damit der Tag-Manager den Kundenwunsch auslesen kann, kann ein zus&auml;tzliches, lesbares Cookie gesetzt werden.<br /><br />Einrichtung im Google-Tag-Manager:<br />Gehen Sie in das Men&uuml; &quot;Variablen&quot; und legen Sie unter &quot;Benutzerdefinierte Variablen&quot; eine neue Variable an. Nennen Sie diese zum Beispiel &quot;Cookieconsent&quot;.<br />Klicken Sie dann auf &quot;Variable konfigurieren&quot; und w&auml;hlen Sie den Typ &quot;First-Party-Cookie&quot;. Geben Sie den Namen des Cookies &quot;MODOilTrack&quot; ein.<br /><br />Anschlie&szlig;end k&ouml;nnen Sie Ihre &quot;Trigger&quot; bearbeiten.<br />Setzen Sie den Triggertyp &quot;Seitenaufruf&quot; und w&auml;hlen Sie die Option &quot;Einige Seitenaufrufe&quot;.<br />Als Bedingung geben Sie an: <em>&quot;Cookieconsent&quot;</em> (Name der Variable) enth&auml;lt <em>[&quot;1&quot;:true]</em>. Den enthaltenen Wert je Cookie finden Sie in der Cookie-Consent-Konfiguration.<br /><br />Eine bebilderte Anleitung finden Sie hier: <a href="https://www.dair-media.net/blog/dsgvo-cookie-einwilligung-im-google-tag-manager-beruecksichtigen/" target="_blank">https://www.dair-media.net/blog/dsgvo-cookie-einwilligung-im-google-tag-manager-beruecksichtigen/</a>');

  define('MODULE_COOKIE_CONSENT_EXTENDED_DESCRIPTION', '<strong><font color="red">ACHTUNG:</font></strong> Bitte nehmen Sie noch die Einstellungen unter "Konfiguration" -> <a href="'.xtc_href_link(FILENAME_COOKIE_CONSENT).'"><strong>"Cookie Consent"</strong></a> vor!');
  define('MODULE_COOKIE_CONSENT_MORE_INFO', 'Mehr Informationen:');