<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  define('TEXT_GUARANTEE_LABELS_HEADING', 'EU-Haltbarkeitsgarantie:');
  define('TEXT_GUARANTEE_LABELS_DURATION', 'Garantiedauer in Jahren:');
  define('TEXT_GUARANTEE_LABELS_TERMS', 'Garantiebedingungen:');

  define('TEXT_GUARANTEE_LABELS_NONE', 'nicht angegeben');
  define('TEXT_GUARANTEE_LABELS_TERMS_MISSING', 'kein Anhang vom Typ GARAN-Garantiebedingungen hinterlegt');
  define('TEXT_GUARANTEE_LABELS_TERMS_FILE_MISSING', '%s &ndash; Datei fehlt, sie wird der Auftragsbest&auml;tigung nicht beigelegt');
  define('TEXT_GUARANTEE_LABELS_MANUFACTURER_INACTIVE', 'Hersteller ist nicht aktiv, deshalb entsteht kein Label');
  define('TEXT_GUARANTEE_LABELS_VIRTUAL', 'Artikel enth&auml;lt ausschliesslich digitale Inhalte. Die Haltbarkeitsgarantie gilt f&uuml;r Waren, deshalb entsteht kein Label.');

  define('TEXT_GUARANTEE_LABELS_INFO', 'Dieses Feld nimmt die Dauer der Haltbarkeitsgarantie des Herstellers auf, in ganzen oder halben Jahren. Es ist unabh&auml;ngig von der gesetzlichen Gew&auml;hrleistung: Auf die weist der Shop im Checkout f&uuml;r die gesamte Bestellung hin, ohne dass Sie am Artikel etwas pflegen.');
  define('TEXT_GUARANTEE_LABELS_INFO_RULES', 'Ein GARAN-Label entsteht erst ab 2,5 Jahren. K&uuml;rzere Angaben werden gespeichert und dokumentieren, was der Hersteller zusagt; ein Label erzeugen sie nicht, weil eine Garantie bis zwei Jahre dem Kunden nichts &uuml;ber die gesetzliche Gew&auml;hrleistung hinaus gibt. F&uuml;r ein Label muss die Garantie ausserdem kostenlos sein, die gesamte Ware umfassen und mit derselben Dauer und denselben Bedingungen in allen von Ihnen belieferten L&auml;ndern gelten. Ein leeres Feld bedeutet, dass zur Haltbarkeitsgarantie nichts hinterlegt ist.');
  define('TEXT_GUARANTEE_LABELS_INFO_TERMS', 'Die vollst&auml;ndigen Garantiebedingungen pflegen Sie als Artikel-Anhang vom Typ GARAN-Garantiebedingungen. Fehlen sie, wird das Label trotzdem angezeigt; die Bedingungen liegen dann aber nicht der Auftragsbest&auml;tigung bei.');

  define('ERROR_GUARANTEE_LABELS_DURATION', 'EU-Haltbarkeitsgarantie: &quot;%s&quot; ist keine zul&auml;ssige Garantiedauer. Erlaubt sind ganze und halbe Jahre von 0,5 bis 99,5. Andere Nachkommastellen als 5 sieht der EU-Praxisleitfaden nicht vor. Die Garantiedauer wurde nicht gespeichert.');
  define('ERROR_GUARANTEE_LABELS_MANUFACTURER', 'EU-Haltbarkeitsgarantie: Es ist kein aktiver Hersteller ausgew&auml;hlt. Die Garantiedauer wurde nicht gespeichert.');
  define('ERROR_GUARANTEE_LABELS_MODEL', 'EU-Haltbarkeitsgarantie: Die Hersteller-Modellkennung ist leer. Die Garantiedauer wurde nicht gespeichert.');
  define('ERROR_GUARANTEE_LABELS_MANUFACTURER_WIDTH', 'EU-Haltbarkeitsgarantie: Der Herstellername &quot;%s&quot; passt nicht in das daf&uuml;r vorgesehene Feld der offiziellen Vorlage. Die Garantiedauer wurde nicht gespeichert.');
  define('ERROR_GUARANTEE_LABELS_MODEL_WIDTH', 'EU-Haltbarkeitsgarantie: Die Modellkennung &quot;%s&quot; passt nicht in das daf&uuml;r vorgesehene Feld der offiziellen Vorlage. Die Garantiedauer wurde nicht gespeichert.');
  define('ERROR_GUARANTEE_LABELS_NOT_READY', 'EU-Haltbarkeitsgarantie: Das Modul kann noch kein Label erzeugen (%s). Die Garantiedauer wurde nicht gespeichert.');

  define('ERROR_GUARANTEE_LABELS_IMPORT', 'Artikel %s &ndash; %s');

  define('TEXT_GUARANTEE_LABELS_CONTENT_TYPE', 'Verwendung:');
  define('TEXT_GUARANTEE_LABELS_CONTENT_TYPE_DEFAULT', 'Standard');
  define('TEXT_GUARANTEE_LABELS_CONTENT_TYPE_TERMS', 'GARAN-Garantiebedingungen');

  define('BUTTON_GUARANTEE_LABELS_EDIT', 'EU-Haltbarkeitsgarantie');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_HEADING', 'EU-Haltbarkeitsgarantie der Bestellposition');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_INFO', 'Diese Werte geh&ouml;ren zur Bestellung und werden mit ihr aufbewahrt. Eine &Auml;nderung wirkt nicht auf den Katalogartikel. Leeren Sie Hersteller, Modellkennung und Dauer, wird die Zusage von der Position entfernt.');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_MANUFACTURER', 'Hersteller/Garantiegeber:');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_MODEL', 'Hersteller-Modellkennung:');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_DURATION', 'Garantiedauer in Jahren:');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_TERMS', 'Garantiebedingungen:');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_TERMS_NONE', 'keine hinterlegt');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_TERMS_KEEP', 'unver&auml;ndert lassen');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_TERMS_REMOVE', 'entfernen');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_TERMS_REPLACE', 'ersetzen durch:');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_PREVIEW', 'Aktuelles Label der Bestellposition:');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_PREVIEW_NONE', 'F&uuml;r diese Position ist kein Label hinterlegt.');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_FROM_PRODUCT', 'Aus Artikeldaten &uuml;bernehmen');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_CONFIRM', 'Die Auftragsbest&auml;tigung wurde bereits versendet. &Auml;nderung ausdr&uuml;cklich best&auml;tigen.');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_SAVED', 'Die EU-Haltbarkeitsgarantie der Bestellposition wurde gespeichert.');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_REMOVED', 'Die EU-Haltbarkeitsgarantie wurde von der Bestellposition entfernt.');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_HISTORY', 'EU-Haltbarkeitsgarantie der Position %1$s ge&auml;ndert: %2$s');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_HISTORY_FIELD', '%1$s von &quot;%2$s&quot; auf &quot;%3$s&quot;');

  define('ERROR_GUARANTEE_LABELS_SNAPSHOT_DURATION', 'EU-Haltbarkeitsgarantie: &quot;%s&quot; ist keine zul&auml;ssige Garantiedauer. F&uuml;r ein Label sind ganze und halbe Jahre &uuml;ber zwei bis 99,5 erlaubt.');
  define('ERROR_GUARANTEE_LABELS_SNAPSHOT_MANUFACTURER', 'EU-Haltbarkeitsgarantie: Der Hersteller/Garantiegeber fehlt.');
  define('ERROR_GUARANTEE_LABELS_SNAPSHOT_UNKNOWN', 'EU-Haltbarkeitsgarantie: Die Bestellposition wurde nicht gefunden.');
  define('ERROR_GUARANTEE_LABELS_SNAPSHOT_RENDER', 'EU-Haltbarkeitsgarantie: Das Label konnte nicht erzeugt werden (%s). Der bisherige Stand bleibt erhalten.');
  define('ERROR_GUARANTEE_LABELS_SNAPSHOT_ARCHIVE', 'EU-Haltbarkeitsgarantie: Das Label konnte nicht archiviert werden (%s). Der bisherige Stand bleibt erhalten.');
  define('ERROR_GUARANTEE_LABELS_SNAPSHOT_CONFIRM', 'EU-Haltbarkeitsgarantie: Die &Auml;nderung wurde nicht best&auml;tigt und deshalb nicht gespeichert.');
  define('ERROR_GUARANTEE_LABELS_SNAPSHOT_TERMS', 'EU-Haltbarkeitsgarantie: Die Garantiebedingungen konnten nicht archiviert werden (%s). Der bisherige Stand bleibt erhalten.');
  define('ERROR_GUARANTEE_LABELS_SNAPSHOT_NO_PRODUCT', 'EU-Haltbarkeitsgarantie: Der Katalogartikel liefert keine vollst&auml;ndigen GARAN-Daten.');

  define('ERROR_GUARANTEE_LABELS_TERMS_LINK', 'GARAN-Garantiebedingungen: Ein reiner Link ist kein dauerhafter Datentr&auml;ger. Hinterlegen Sie eine Datei. Der Anhang wurde als normaler Anhang gespeichert.');
  define('ERROR_GUARANTEE_LABELS_SNAPSHOT_FAILED', 'GARAN: Die Bestelldaten konnten nicht vollst&auml;ndig gesichert werden: %s');
  define('ERROR_GUARANTEE_LABELS_TERMS_NAME', 'GARAN-Garantiebedingungen: Der Dateiname &quot;%s&quot; eignet sich nicht als Mailanhang. Der Anhang wurde als normaler Anhang gespeichert.');
  define('ERROR_GUARANTEE_LABELS_TERMS_FILE', 'GARAN-Garantiebedingungen: Die Datei &quot;%s&quot; liegt nicht unter media/products/. Der Anhang wurde als normaler Anhang gespeichert.');
  define('ERROR_GUARANTEE_LABELS_TERMS_DUPLICATE', 'GARAN-Garantiebedingungen: F&uuml;r diesen Artikel und diese Sprache gibt es bereits einen solchen Anhang. Der Anhang wurde als normaler Anhang gespeichert.');
  define('ERROR_GUARANTEE_LABELS_TERMS_GROUPS', 'GARAN-Garantiebedingungen: Der Anhang ist f&uuml;r diese Kundengruppen nicht sichtbar, obwohl sie das Label sehen: %s. Der Anhang wurde als normaler Anhang gespeichert.');
