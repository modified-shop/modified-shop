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

  define('TEXT_GUARANTEE_LABELS_INFO', 'Dieses Feld nimmt die Dauer der Haltbarkeitsgarantie des Herstellers auf, in ganzen oder halben Jahren ab 2. Es ist unabh&auml;ngig von der gesetzlichen Gew&auml;hrleistung: Auf die weist der Shop im Checkout f&uuml;r die gesamte Bestellung hin, ohne dass Sie am Artikel etwas pflegen.');
  define('TEXT_GUARANTEE_LABELS_INFO_RULES', 'Ein GARAN-Label entsteht erst ab 2,5 Jahren. Der Wert 2 wird gespeichert und dokumentiert, dass der Hersteller genau die gesetzlichen zwei Jahre gibt; ein Label erzeugt er nicht, weil eine solche Garantie dem Kunden nichts &uuml;ber die Gew&auml;hrleistung hinaus gibt. F&uuml;r ein Label muss die Garantie ausserdem kostenlos sein, die gesamte Ware umfassen und mit derselben Dauer und denselben Bedingungen in allen von Ihnen belieferten L&auml;ndern gelten. Ein leeres Feld bedeutet, dass zur Herstellergarantie nichts hinterlegt ist.');
  define('TEXT_GUARANTEE_LABELS_INFO_TERMS', 'Die vollst&auml;ndigen Garantiebedingungen pflegen Sie als Artikel-Anhang vom Typ GARAN-Garantiebedingungen. Fehlen sie, wird das Label trotzdem angezeigt; die Bedingungen liegen dann aber nicht der Auftragsbest&auml;tigung bei.');

  define('ERROR_GUARANTEE_LABELS_DURATION', 'EU-Haltbarkeitsgarantie: &quot;%s&quot; ist keine zul&auml;ssige Garantiedauer. Erlaubt sind ganze und halbe Jahre ab 2, h&ouml;chstens 99 ganze oder 9,5 halbe Jahre. Die Garantiedauer wurde nicht gespeichert.');
  define('ERROR_GUARANTEE_LABELS_MANUFACTURER', 'EU-Haltbarkeitsgarantie: Es ist kein aktiver Hersteller ausgew&auml;hlt. Die Garantiedauer wurde nicht gespeichert.');
  define('ERROR_GUARANTEE_LABELS_MODEL', 'EU-Haltbarkeitsgarantie: Die Hersteller-Modellkennung ist leer. Die Garantiedauer wurde nicht gespeichert.');
  define('ERROR_GUARANTEE_LABELS_MANUFACTURER_WIDTH', 'EU-Haltbarkeitsgarantie: Der Herstellername &quot;%s&quot; passt nicht in das daf&uuml;r vorgesehene Feld der offiziellen Vorlage. Die Garantiedauer wurde nicht gespeichert.');
  define('ERROR_GUARANTEE_LABELS_MODEL_WIDTH', 'EU-Haltbarkeitsgarantie: Die Modellkennung &quot;%s&quot; passt nicht in das daf&uuml;r vorgesehene Feld der offiziellen Vorlage. Die Garantiedauer wurde nicht gespeichert.');
  define('ERROR_GUARANTEE_LABELS_NOT_READY', 'EU-Haltbarkeitsgarantie: Das Modul kann noch kein Label erzeugen (%s). Die Garantiedauer wurde nicht gespeichert.');

  define('ERROR_GUARANTEE_LABELS_IMPORT', 'Artikel %s &ndash; %s');

  define('TEXT_GUARANTEE_LABELS_CONTENT_TYPE', 'Verwendung:');
  define('TEXT_GUARANTEE_LABELS_CONTENT_TYPE_DEFAULT', 'Standard');
  define('TEXT_GUARANTEE_LABELS_CONTENT_TYPE_TERMS', 'GARAN-Garantiebedingungen');
