<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  define('MODULE_GUARANTEE_LABELS_TEXT_TITLE', 'EU-Gew&auml;hrleistung und GARAN-Label');
  define('MODULE_GUARANTEE_LABELS_TEXT_DESCRIPTION', 'Stellt die ab dem 27. September 2026 vorgeschriebene Mitteilung zum gesetzlichen Gew&auml;hrleistungsrecht sowie das GARAN-Label f&uuml;r qualifizierte Haltbarkeitsgarantien bereit.');

  define('MODULE_GUARANTEE_LABELS_STATUS_TITLE', 'Modul aktivieren?');
  define('MODULE_GUARANTEE_LABELS_STATUS_DESC', 'EU-Kennzeichnungen im Shop ausgeben. Bei inaktivem Modul entstehen keine neuen Bestellsnapshots; bereits vorhandene bleiben erhalten.');

  define('MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS_TITLE', 'B2B-Kundengruppen');
  define('MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS_DESC', 'Kundengruppen, die von der Ausgabe ausgeschlossen werden. Ohne Auswahl gelten alle Kundengruppen einschlie&szlig;lich der G&auml;ste als B2C.');

  define('MODULE_GUARANTEE_LABELS_TEXT_INSTALL_SUCCESS', 'Die Datenbankstruktur f&uuml;r die EU-Kennzeichnungen wurde angelegt und gepr&uuml;ft.');
  define('MODULE_GUARANTEE_LABELS_TEXT_UPDATE_SUCCESS', 'Die Datenbankstruktur f&uuml;r die EU-Kennzeichnungen ist vollst&auml;ndig.');
  define('MODULE_GUARANTEE_LABELS_TEXT_GD_ERROR', 'GD mit FreeType und die Funktion imagettfbbox() sind auf diesem Server nicht verf&uuml;gbar. Ohne sie l&auml;sst sich die Textbreite im GARAN-Label nicht messen. Das Modul wurde nicht installiert.');
  define('MODULE_GUARANTEE_LABELS_TEXT_SCHEMA_ERROR', 'Die Datenbankstruktur f&uuml;r die EU-Kennzeichnungen konnte nicht vollst&auml;ndig angelegt werden. Das Modul wurde nicht aktiviert.');

  define('MODULE_GUARANTEE_LABELS_TEXT_ERROR_TABLE', 'Die Tabelle %s fehlt.');
  define('MODULE_GUARANTEE_LABELS_TEXT_ERROR_INDEX', 'Der Index %s der Tabelle %s fehlt.');
  define('MODULE_GUARANTEE_LABELS_TEXT_ERROR_COLUMN', 'Die Spalte %s der Tabelle %s fehlt.');
  define('MODULE_GUARANTEE_LABELS_TEXT_ERROR_COLUMN_TYPE', 'Die Spalte %s der Tabelle %s hat den Typ %s, erwartet wird %s. Die Spalte wurde nicht ge&auml;ndert.');

  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS', 'Pr&uuml;fung der Voraussetzungen');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_CACHE', 'Das Modul leert von sich aus keinen Cache. Erzeugte Labelgrafiken liegen unter ihrem Inhaltshash und sind nie falsch, nach einer &Auml;nderung aber verwaist. Bei aktivem Shopcache k&ouml;nnen Cross-Selling, neue Artikel und &auml;hnliche Bl&ouml;cke zudem bis zum Ablauf der Cache-Lebensdauer ein altes Label zeigen. Leeren Sie den Shopcache nach Hersteller- und Artikel&auml;nderungen &uuml;ber Konfiguration &raquo; Cache.');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_OK', 'in Ordnung');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_FAILED', 'fehlt');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_EXTENSION', 'Klassenerweiterung %s eingerichtet');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_SELECT', 'Garantiedauer in %s enthalten');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_RENDERER', 'Vorlagen, Schriften und GD f&uuml;r die Labelerzeugung');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_NOTICE', 'Gew&auml;hrleistungshinweis und Texte f&uuml;r %s');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_SCHEMA', 'Tabellen, Indizes und Spalten des Moduls');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_WRITABLE', 'Schreibrecht auf %s');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_PRODUCTS', 'Artikel mit Garantiedauer, aktivem Hersteller und Modellkennung');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_AMBIGUOUS', 'Garantiebedingungen je Artikel und Sprache eindeutig');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_SHARED', 'Dieselbe Datei in mehreren Sprachen als Garantiebedingung markiert');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_ARCHIVE', 'Archivierte Dateien zu bestehenden Bestellungen vorhanden');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_INCOMPLETE', 'unvollst&auml;ndig:');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_LOCKED', 'kein Schreibrecht:');
  define('MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_AFFECTED', 'betroffen:');
