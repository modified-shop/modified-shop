<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

define('TEXT_SETTINGS', 'Einstellungen');

define('HEADING_TITLE', 'Trusted Shops');
define('HEADING_FEATURES', 'Funktionen');

define('TABLE_HEADING_TRUSTEDSHOPS_ID', 'TS-ID');
define('TABLE_HEADING_LANGUAGE', 'Sprache');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_ACTION', 'Aktion');

define('HEADING_TRUSTBADGE', 'Trustbadge');
define('HEADING_ADVANCED', 'Erweitert');

define('TEXT_DATE_ADDED', 'Hinzugef&uuml;gt am:');
define('TEXT_LAST_MODIFIED', 'Aktualisiert am:');
define('TEXT_TRUSTEDSHOPS_STATUS', 'Status:');
define('TEXT_TRUSTEDSHOPS_ID', 'TS-ID:');
define('TEXT_TRUSTEDSHOPS_LANGUAGES', 'Sprache:');
define('TEXT_TRUSTBADGE_INFO', 'Das Trustbadge zeigt Ihr G&uuml;tesiegel und Ihre Kundenbewertungen in Ihrem Shop an und l&auml;sst sich hier in Aussehen und Positionierung anpassen. In der Variante "Standard" wird nur das G&uuml;tesiegel angezeigt, die Variante "Bewertungen" zeigt zus&auml;tzlich Ihre Kundenbewertungen. Weitere Parameter lassen sich bei Auswahl "Custom" individualisieren (dazu werden Programmierkenntnisse ben&ouml;tigt).');

define('TEXT_TRUSTEDSHOPS_BADGE', 'Variante:');
define('TEXT_TRUSTEDSHOPS_POSITION', 'Position:');
define('TEXT_BADGE_DEFAULT', 'Standard');
define('TEXT_BADGE_SMALL', 'Standard (klein)');
define('TEXT_BADGE_REVIEWS', 'Bewertungen');
define('TEXT_BADGE_CUSTOM', 'Custom');
define('TEXT_BADGE_CUSTOM_REVIEWS', 'Custom (Reviews)');
define('TEXT_BADGE_OFFSET', 'Position Y-Achse:');
define('TEXT_BADGE_INSTRUCTION', 'In unserem Integration Center finden Sie eine <a href="https://help.etrusted.com/hc/de/articles/360045842852-Trusted-Shops-nutzen-mit-modified" target="_blank" style="text-decoration:underline">Schritt-f&uuml;r-Schritt Anleitung</a> zur individuellen Konfiguration und Einbindung.');
define('TEXT_BADGE_CUSTOM_CODE', 'Trustbadge Code hier einf&uuml;gen:');

define('TEXT_PRODUCT_STICKER_API', 'Produktbewertung API:');
define('TEXT_PRODUCT_STICKER_API_INFO', 'Mit der Produktbewertung API werden die Bewertungen in den Shop importiert.'.((!defined('TABLE_SCHEDULED_TASKS')) ? ' Dazu ist es notwendig, dass ein Cronjob auf URL '.HTTPS_SERVER.DIR_WS_CATALOG.'api/trustedshops/cronjob.php erstellt wird.' : 'Zus&auml;tzlich muss unter Hilfsprogramme -> Geplante Aufgaben der Task f&uuml;r Trusted Shops aktiviert werden.'));
define('TEXT_PRODUCT_STICKER_API_CLIENT', 'Produktbewertung API Client:');
define('TEXT_PRODUCT_STICKER_API_SECRET', 'Produktbewertung API Secret:');
define('TEXT_PRODUCT_STICKER_STATUS', 'Produktbewertungs Widget Status:');
define('TEXT_PRODUCT_STICKER', 'Produktbewertungs Widget Code editieren:');
define('TEXT_PRODUCT_STICKER_INFO', 'Das Produktbewertungs Widget zeigt die aktuellen Produktbewertungen in Ihrem Shop an.<br/>Mit unserer <a target="_blank" href="https://help.etrusted.com/hc/de/articles/360045842852-Trusted-Shops-nutzen-mit-modified" style="text-decoration:underline">Anleitung konfigurieren</a> Sie Ihr Produktbewertungs Widget.');
define('TEXT_PRODUCT_STICKER_INTRO', 'Kundenbewertungen');

define('TEXT_REVIEW_STICKER_STATUS', 'Review Widget Status:');
define('TEXT_REVIEW_STICKER', 'Review Widget Code editieren:');
define('TEXT_REVIEW_STICKER_INFO', 'Das Review Widget zeigt die aktuellen Bewertungen f&uuml;r Ihren Shop an.<br/>Mit dieser <a target="_blank" href="https://help.etrusted.com/hc/de/articles/360045842852-Trusted-Shops-nutzen-mit-modified" style="text-decoration:underline">Anleitung konfigurieren</a> Sie Ihr Review Widget.');
define('TEXT_REVIEW_STICKER_INTRO', 'Kundenbewertungen');

define('TEXT_HEADING_DELETE_TRUSTEDSHOPS', 'TS-ID l&ouml;schen');
define('TEXT_DELETE_INTRO', 'Sind Sie sicher, dass Sie diese TS-ID l&ouml;schen wollen?');

define('TEXT_DISABLED', 'deaktiviert');
define('TEXT_ENABLED', 'aktiviert');

define('TEXT_LEFT', 'links');
define('TEXT_RIGHT', 'rechts');
define('TEXT_CENTER', 'zentriert');

define('TEXT_DISPLAY_NUMBER_OF_TRUSTEDSHOPS', 'Angezeigt werden <b>%d</b> bis <b>%d</b> (von insgesamt <b>%d</b> TS-ID)');

define('TEXT_TS_MAIN_INFO', '
<img src="images/trustedshops/illustration-ts-products-profile-page.png" style="width:160px;float:right;margin-top:30px;padding-left:30px;"/>
<h2>Trusted Shops</h2>
Mehr als 30.000 Unternehmen in ganz Europa nutzen das Trusted Shops G&uuml;tesiegel, den K&auml;uferschutz und die authentischen Bewertungen f&uuml;r mehr Traffic, h&ouml;heren Umsatz und bessere Konversionsraten. Mit Trusted Shops Trustbadge integrieren Sie die Vertrauensl&ouml;sungen schnell und einfach in Ihre modified eCommerce Shopsoftware.<br/>
<br/>
<b>Vertrauen schaffen - in nur 5 Minuten!</b><br/>
<br/>
Das G&uuml;tesiegel, der K&auml;uferschutz und die authentischen Bewertungen von Trusted Shops stehen seit &uuml;ber 20 Jahren f&uuml;r Vertrauen und sind f&uuml;r mehr als 30.000 Online-Shops in ganz Europa der Hebel f&uuml;r mehr Traffic, h&ouml;heren Umsatz und bessere Konversionsraten.
<br/>
Damit Sie Besucherinnen und Besucher schnell und einfach von der Vertrauensw&uuml;rdigkeit Ihres Online-Shops &uuml;berzeugen k&ouml;nnen, gibt es dieses Modul von Trusted Shops. Die simple Installation garantiert eine Produktnutzung in nur 5 Minuten und erfordert in der Regel keine bis wenige technische Vorkenntnisse. Mit unserem Modul sind Sie technisch immer auf dem neuesten Stand und haben keinen zus&auml;tzlichen Wartungsaufwand.<br/>
<br/>
<b>Ihr Vorteil:</b> Mit nur wenigen Klicks sehen Besucher*innen Ihres Online-Shops Vertrauenselemente wie das Trustbadge oder andere On-Site Widgets, k&ouml;nnen vom K&auml;uferschutz profitieren und werden nach der Bestellung automatisch um Feedback gebeten.');
define('TEXT_TS_FEATURES_INFO', '
<img src="images/trustedshops/illustration-ts-badge.png" style="width:160px;float:right;margin-top:30px;padding-left:30px;"/>
<h2>Funktionen</h2>
<b>Die Funktionen im &Uuml;berblick:</b><br/>
<br/>
<ul>
  <li>Trustbadge anzeigen, K&auml;uferschutz integrieren &amp; Shopbewertungen sammeln</li>
  <li>Produktbewertungen sammeln &amp; anzeigen</li>
  <li>Multishops (z.B. mehrere Sprachshops) mit verschiedenen Trusted-Shops-IDs konfigurieren</li>
</ul>
<br/>
Hinweis: Um das Trusted Shops Modul zu nutzen, ben&ouml;tigen Sie eine bestehende Trusted Shops Mitgliedschaft. Mehr &uuml;ber die Produkte und Vorteile von Trusted Shops erfahren Sie auf unserer Website oder telefonisch unter: +49 221 7753658<br/>
<br/>
Ben&ouml;tigen Sie Hilfe bei der Integration? Eine detaillierte Integrationsanleitung finden Sie in unserem Hilfe-Center.<br/>
<br/>
Link: <a href="https://help.etrusted.com/hc/de/articles/360045842852-Trusted-Shops-nutzen-mit-modified" target="_blank" style="text-decoration:underline">https://help.etrusted.com/hc/de/articles/360045842852-Trusted-Shops-nutzen-mit-modified</a>');
define('TEXT_TS_SPECIAL_INFO', '
<b>Unser Special f&uuml;r Sie:<br/>dauerhaft g&uuml;nstiger f&uuml;r modified-Nutzer!</b><br/>
<a class="btnSmall btnCuracao fitting" target="_blank" href="https://checkout.trustedshops.com/?a_aid=modified-shop">Jetzt Mitglied werden!</a>');
            
?>