<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  define('MODULE_CAMPAIGN_TRACKING_TEXT_TITLE', 'Kampagnen-Tracking');
  define('MODULE_CAMPAIGN_TRACKING_TEXT_DESCRIPTION', 'Legt fest, wie lange Bestellungen einer Kampagne zugeordnet werden. Ohne dieses Modul gilt eine Kampagne nur f&uuml;r den aktuellen Besuch, f&uuml;r ein &uuml;ber die Kampagne angelegtes Kundenkonto dagegen unbegrenzt.<br /><br />Mit aktivem Modul z&auml;hlt au&szlig;erdem der zuletzt geklickte Kampagnen-Link.');

  define('MODULE_CAMPAIGN_TRACKING_STATUS_TITLE', 'Status');
  define('MODULE_CAMPAIGN_TRACKING_STATUS_DESC', 'Modulstatus');

  define('MODULE_CAMPAIGN_TRACKING_ACCOUNT_DAYS_TITLE', 'Kampagne am Kundenkonto (Tage)');
  define('MODULE_CAMPAIGN_TRACKING_ACCOUNT_DAYS_DESC', 'Wie viele Tage nach der Kontoanlage Bestellungen eines Kunden noch der Kampagne zugeordnet werden, &uuml;ber die er sein Konto angelegt hat. 0 = unbegrenzt.');
  define('MODULE_CAMPAIGN_TRACKING_ACCOUNT_DAYS_ERROR', 'Kampagne am Kundenkonto: Bitte geben Sie eine ganze Zahl ab 0 ein.');

  define('MODULE_CAMPAIGN_TRACKING_COOKIE_TITLE', 'Kampagnen-Cookie setzen');
  define('MODULE_CAMPAIGN_TRACKING_COOKIE_DESC', 'Speichert die Kampagne mit Einwilligung des Besuchers im Cookie &quot;MODref&quot;. So wird auch ein sp&auml;terer Besuch ohne Kampagnen-Link der Kampagne zugeordnet.<br />Ben&ouml;tigt das Modul Cookie Consent. Beim Speichern legt das Modul dort einen Eintrag in der Kategorie Marketing an und schaltet die lesbare Cookie-Entscheidung ein. Erg&auml;nzen Sie Ihre Datenschutzerkl&auml;rung.');

  define('MODULE_CAMPAIGN_TRACKING_COOKIE_DAYS_TITLE', 'G&uuml;ltigkeit des Cookies (Tage)');
  define('MODULE_CAMPAIGN_TRACKING_COOKIE_DAYS_DESC', 'Wie viele Tage nach dem Klick auf den Kampagnen-Link das Cookie gilt. H&ouml;chstens 400 Tage, l&auml;nger speichern Browser kein Cookie.');
  define('MODULE_CAMPAIGN_TRACKING_COOKIE_DAYS_ERROR', 'G&uuml;ltigkeit des Cookies: Bitte geben Sie eine ganze Zahl von 1 bis 400 ein.');

  define('MODULE_CAMPAIGN_TRACKING_ERROR_CONSENT_MODULE', 'Kampagnen-Tracking: Das Kampagnen-Cookie ben&ouml;tigt das aktive Modul Cookie Consent. Ohne dieses Modul setzt der Shop kein Cookie.');
  define('MODULE_CAMPAIGN_TRACKING_ERROR_CONSENT_ID', 'Kampagnen-Tracking: Im Cookie Consent ist keine ID von 1 bis 24 mehr frei. Das Kampagnen-Cookie hat deshalb keinen Eintrag, Besucher k&ouml;nnen ihm nicht zustimmen.');
  define('MODULE_CAMPAIGN_TRACKING_INFO_READABLE_COOKIE', 'Kampagnen-Tracking: Die lesbare Cookie-Entscheidung (MODOilTrack) im Cookie Consent ist jetzt eingeschaltet. Das Modul ben&ouml;tigt sie, um die Entscheidung des Besuchers zu lesen.');
