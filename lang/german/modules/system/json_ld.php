<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  define('MODULE_JSON_LD_TEXT_TITLE', 'Strukturierte Daten (JSON-LD)');
  define('MODULE_JSON_LD_TEXT_DESCRIPTION', 'Gibt die strukturierten Daten des Shops zus&auml;tzlich zu den Rich Snippets als JSON-LD im Kopf jeder Seite aus. Suchmaschinen und KI-Dienste lesen dieses Format bevorzugt. Eigene Angaben lassen sich &uuml;ber Dateien im Verzeichnis includes/extra/json_ld/ erg&auml;nzen.');

  define('MODULE_JSON_LD_STATUS_TITLE', 'Modul aktivieren?');
  define('MODULE_JSON_LD_STATUS_DESC', 'JSON-LD im Kopf jeder Seite ausgeben. Die Rich Snippets in den Templates bleiben davon unber&uuml;hrt.');

  define('MODULE_JSON_LD_ORGANIZATION_TITLE', 'Shopbetreiber ausgeben?');
  define('MODULE_JSON_LD_ORGANIZATION_DESC', 'Gibt auf jeder Seite ein Organization-Objekt mit Shopname, Logo, E-Mail-Adresse und USt-IdNr. aus. Jedes Angebot verweist darauf als Verk&auml;ufer.');

  define('MODULE_JSON_LD_WEBSITE_TITLE', 'Website und Suche ausgeben?');
  define('MODULE_JSON_LD_WEBSITE_DESC', 'Gibt auf der Startseite ein WebSite-Objekt mit dem Einstiegspunkt der Shopsuche aus. Google kann daraus einen Suchschlitz im Suchergebnis anbieten.');

  define('MODULE_JSON_LD_BREADCRUMB_TITLE', 'Navigationspfad ausgeben?');
  define('MODULE_JSON_LD_BREADCRUMB_DESC', 'Gibt den Pfad der aktuellen Seite als BreadcrumbList aus, sobald er mindestens zwei Stationen hat.');

  define('MODULE_JSON_LD_PRODUCT_TITLE', 'Artikeldaten ausgeben?');
  define('MODULE_JSON_LD_PRODUCT_DESC', 'Gibt auf der Artikelseite ein Product-Objekt mit Beschreibung, Bildern, Artikelnummer, EAN, Hersteller, Bewertung und Preis aus. Ohne Preisrecht der Kundengruppe entf&auml;llt das Angebot.');

  define('MODULE_JSON_LD_LISTING_TITLE', 'Artikellisten ausgeben?');
  define('MODULE_JSON_LD_LISTING_DESC', 'Gibt Kategorie-, Such-, Sonderangebots- und Neuheitenlisten als ItemList aus. <b>summary</b> nennt nur Link und Name je Artikel und h&auml;lt den Seitenkopf klein. <b>full</b> wiederholt zus&auml;tzlich Bild, Artikelnummer und Preis, damit ein Dienst nicht jede Artikelseite einzeln aufrufen muss. <b>false</b> schaltet die Ausgabe ab.');

  define('MODULE_JSON_LD_DESCRIPTION_LENGTH_TITLE', 'L&auml;nge der Artikelbeschreibung');
  define('MODULE_JSON_LD_DESCRIPTION_LENGTH_DESC', 'H&ouml;chstzahl der Zeichen, die aus der Artikelbeschreibung in das JSON-LD &uuml;bernommen werden. 0 &uuml;bergibt die vollst&auml;ndige Beschreibung.');

  define('MODULE_JSON_LD_PRETTY_TITLE', 'Lesbar formatieren?');
  define('MODULE_JSON_LD_PRETTY_DESC', 'Gibt das JSON mit Zeilenumbr&uuml;chen und Einr&uuml;ckung aus. Das erleichtert die Pr&uuml;fung im Quelltext, vergr&ouml;&szlig;ert aber jede Seite. Im Livebetrieb abschalten.');
