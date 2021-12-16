<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/


  define('MODULE_CATEGORIES_ZETTLE_CATEGORIES_TITLE', 'Zettle by PayPal');
  define('MODULE_CATEGORIES_ZETTLE_CATEGORIES_DESCRIPTION', 'Moderne Kassensysteme als App f&uuml;r Zahlungsannahme und Umsatzverfolgung: Machen Sie sich Ihren Gesch&auml;ftsalltag mit Zettle leichter.<br>
                                                             <br>Folgender Funktionsumfang wird aktuell unterst&uuml;tzt:
                                                             <ul style="padding-left: 20px;">
                                                               <li>Artikel werden vom Shop nach Zettle &uuml;bertragen</li>
                                                               <li>Eigene Kundengruppe f&uuml;r Artikelpreise m&ouml;glich</li>
                                                               <li>Lagerbestand wird zwischen Shop und Zettle auf Wunsch synchronisiert</li>
                                                             </ul>');

  define('MODULE_CATEGORIES_ZETTLE_CATEGORIES_BULK_TITLE', 'Bulk Import');
  define('MODULE_CATEGORIES_ZETTLE_CATEGORIES_BULK_DESC', 'Soll der Bulk import aktiviert werden?<br><b>Hinweis:</b> Dazu ist es notwendig, dass ein Cronjob auf die URL '.HTTP_SERVER.DIR_WS_CATALOG.'api/zettle/cronjob.php erstellt wird.');

  define('MODULE_CATEGORIES_ZETTLE_CATEGORIES_API_KEY_TITLE', 'API Key');
  define('MODULE_CATEGORIES_ZETTLE_CATEGORIES_API_KEY_DESC', 'Geben Sie den Zettle API Key an.');

  define('MODULE_CATEGORIES_ZETTLE_CATEGORIES_LANGUAGE_TITLE', 'Sprache');
  define('MODULE_CATEGORIES_ZETTLE_CATEGORIES_LANGUAGE_DESC', 'W&auml;hlen Sie die Sprache f&uuml;r die &Uuml;bertragung der Artikel.');

  define('MODULE_CATEGORIES_ZETTLE_CATEGORIES_CUSTOMERS_STATUS_TITLE', 'Kundengruppe');
  define('MODULE_CATEGORIES_ZETTLE_CATEGORIES_CUSTOMERS_STATUS_DESC', 'W&auml;hlen Sie die Kundengruppe f&uuml;r die &Uuml;bertragung der Preise.');

  define('MODULE_CATEGORIES_ZETTLE_CATEGORIES_BUTTON_API', 'Zettle API Key erstellen');
