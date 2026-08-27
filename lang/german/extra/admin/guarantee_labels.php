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
  define('TEXT_GUARANTEE_LABELS_MANUFACTURER', 'Hersteller/Garantiegeber:');
  define('TEXT_GUARANTEE_LABELS_MODEL', 'Hersteller-Modellkennung:');
  define('TEXT_GUARANTEE_LABELS_DURATION', 'Garantiedauer in Jahren:');
  define('TEXT_GUARANTEE_LABELS_TERMS', 'Garantiebedingungen:');

  define('TEXT_GUARANTEE_LABELS_NONE', 'nicht ausgew&auml;hlt');
  define('TEXT_GUARANTEE_LABELS_TERMS_MISSING', 'kein Anhang vom Typ GARAN-Garantiebedingungen hinterlegt');

  define('TEXT_GUARANTEE_LABELS_INFO', 'Eine Laufzeit darf nur eingetragen werden, wenn eine Herstellergarantie vorliegt, sie f&uuml;r den Kunden kostenlos ist, die gesamte Ware umfasst, l&auml;nger als zwei Jahre gilt und mit derselben Dauer und denselben Bedingungen in allen von Ihnen belieferten L&auml;ndern gilt. Zul&auml;ssig sind ganze und halbe Jahre, zum Beispiel 3 oder 2,5. Ein leeres Feld deaktiviert das GARAN-Label f&uuml;r diesen Artikel.');
  define('TEXT_GUARANTEE_LABELS_INFO_TERMS', 'Die vollst&auml;ndigen Garantiebedingungen pflegen Sie als Artikel-Anhang vom Typ GARAN-Garantiebedingungen. Fehlen sie, wird das Label trotzdem angezeigt; die Bedingungen liegen dann aber nicht der Auftragsbest&auml;tigung bei.');

  define('ERROR_GUARANTEE_LABELS_DURATION', 'EU-Haltbarkeitsgarantie: &quot;%s&quot; ist keine zul&auml;ssige Garantiedauer. Erlaubt sind ganze und halbe Jahre &uuml;ber zwei Jahren, h&ouml;chstens 99 ganze oder 9,5 halbe Jahre. Die Garantiedauer wurde nicht gespeichert.');
  define('ERROR_GUARANTEE_LABELS_MANUFACTURER', 'EU-Haltbarkeitsgarantie: Es ist kein aktiver Hersteller ausgew&auml;hlt. Die Garantiedauer wurde nicht gespeichert.');
  define('ERROR_GUARANTEE_LABELS_MODEL', 'EU-Haltbarkeitsgarantie: Die Hersteller-Modellkennung ist leer. Die Garantiedauer wurde nicht gespeichert.');
  define('ERROR_GUARANTEE_LABELS_MANUFACTURER_WIDTH', 'EU-Haltbarkeitsgarantie: Der Herstellername &quot;%s&quot; passt nicht in das daf&uuml;r vorgesehene Feld der offiziellen Vorlage. Die Garantiedauer wurde nicht gespeichert.');
  define('ERROR_GUARANTEE_LABELS_MODEL_WIDTH', 'EU-Haltbarkeitsgarantie: Die Modellkennung &quot;%s&quot; passt nicht in das daf&uuml;r vorgesehene Feld der offiziellen Vorlage. Die Garantiedauer wurde nicht gespeichert.');
  define('ERROR_GUARANTEE_LABELS_NOT_READY', 'EU-Haltbarkeitsgarantie: Das Modul kann noch kein Label erzeugen (%s). Die Garantiedauer wurde nicht gespeichert.');

  define('TEXT_GUARANTEE_LABELS_CONTENT_TYPE', 'Verwendung:');
  define('TEXT_GUARANTEE_LABELS_CONTENT_TYPE_DEFAULT', 'Standard');
  define('TEXT_GUARANTEE_LABELS_CONTENT_TYPE_TERMS', 'GARAN-Garantiebedingungen');
