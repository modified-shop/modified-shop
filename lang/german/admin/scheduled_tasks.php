<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  define('HEADING_TITLE', 'Geplante Aufgaben');

  define('TABLE_HEADING_TASKS', 'Aufgabe');
  define('TABLE_HEADING_TIME_NEXT', 'N&auml;chste Ausf&uuml;hrung');
  define('TABLE_HEADING_INTERVAL', 'Intervall');
  define('TABLE_HEADING_STATUS', 'Status');
  define('TABLE_HEADING_ACTION', 'Aktion');

  define('TEXT_INFO_HEADING_EDIT_TASKS', 'Aufgabe bearbeiten');
  define('TEXT_INFO_EDIT_INTRO', 'Bitte f&uuml;hren Sie alle notwendigen &Auml;nderungen durch');

  define('TEXT_INFO_LAST_EXECUTED', 'letzte Ausf&uuml;rung am:');
  define('TEXT_INFO_LAST_DURATION', 'Ausf&uuml;rungszeit:');

  define('TEXT_INFO_TIME_REGULARITY', ' Wiederhole Aufgabe jede(n):');
  define('TEXT_INFO_TIME_UNIT', 'Einheit:');
  define('TEXT_INFO_TIME_OFFSET', 'Startzeit:');

  define('TEXT_TIME_MINUTE', 'Minute(n)');
  define('TEXT_TIME_HOUR', 'Stunde(n)');
  define('TEXT_TIME_DAY', 'Tag(e)');
  define('TEXT_TIME_WEEK', 'Woche(n)');

  define('TEXT_INFO_ONETIME', 'Wird einmalig ausgef&uuml;hrt');
  define('TEXT_INFO_INTERVALL', 'Beginnt um %s, wiederholt sich alle %s');
  define('TEXT_DISPLAY_NUMBER_OF_SCHEDULED_TASKS', 'Angezeigt werden <b>%d</b> bis <b>%d</b> (von insgesamt <b>%d</b> Aufgaben)');

  define('TEXT_HEADING_TASKS_STATUS_BANNERS', 'Banners Status');
  define('TEXT_INFO_TASKS_STATUS_BANNERS', 'Banner werden auf ihre G&uuml;ltigkeit gepr&uuml;ft');

  define('TEXT_HEADING_TASKS_STATUS_SPECIALS', 'Sonderangebote Status');
  define('TEXT_INFO_TASKS_STATUS_SPECIALS', 'Sonderangebote werden auf ihre G&uuml;ltigkeit gepr&uuml;ft');

  define('TEXT_HEADING_TASKS_DB_MAINTENANCE', 'Datenbank optimieren');
  define('TEXT_INFO_TASKS_DB_MAINTENANCE', 'Die Datenbank wird analysiert und optimiert');

  define('TEXT_HEADING_TASKS_DB_BACKUP', 'Datenbank Backup erstellen');
  define('TEXT_INFO_TASKS_DB_BACKUP', 'Datenbank Backup wird erstellt');

  define('TEXT_HEADING_TASKS_LOGS_MAINTENANCE', 'Logfiles bereinigen');
  define('TEXT_INFO_TASKS_LOGS_MAINTENANCE', 'Logfiles &auml;lter als 7 Tage werden gel&ouml;scht');

  define('TEXT_HEADING_TASKS_EXPORT_SITEMAP', 'Sitemap erstellen');
  define('TEXT_INFO_TASKS_EXPORT_SITEMAP', 'Die Sitemap wird neu erstellt. (Das Export Modul <a href="'.xtc_href_link(FILENAME_MODULE_EXPORT, 'set=export&module=sitemaporg').'"><b>XML Sitemap.org</b></a> muss aktiviert sein.)');

  define('TEXT_HEADING_TASKS_ADMINLOG_MAINTENANCE', 'Admin Log');
  define('TEXT_INFO_TASKS_ADMINLOG_MAINTENANCE', 'Admin Log bereinigen');

  define('TEXT_HEADING_TASKS_CURRENCIES_UPDATE', 'W&auml;hrungen aktualisieren');
  define('TEXT_INFO_TASKS_CURRENCIES_UPDATE', 'Die W&auml;hrungskurse werden aktualisiert');

  define('TEXT_HEADING_TASKS_CUSTOMERS_IP_MAINTENANCE', 'Kunden IP Log');
  define('TEXT_INFO_TASKS_CUSTOMERS_IP_MAINTENANCE', 'Kunden IP Log bis auf das letzte Jahr');
