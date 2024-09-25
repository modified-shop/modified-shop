<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  define('HEADING_TITLE', 'Scheduled Tasks');

  define('TABLE_HEADING_TASKS', 'Tasks');
  define('TABLE_HEADING_TIME_NEXT', 'Next execution');
  define('TABLE_HEADING_INTERVAL', 'Interval');
  define('TABLE_HEADING_STATUS', 'Status');
  define('TABLE_HEADING_ACTION', 'Action');

  define('TEXT_INFO_HEADING_EDIT_TASKS', 'Edit Tasks');
  define('TEXT_INFO_EDIT_INTRO', 'Please make all necessary changes');

  define('TEXT_INFO_LAST_EXECUTED', 'last execution at:');
  define('TEXT_INFO_LAST_DURATION', 'execution time:');

  define('TEXT_INFO_TIME_REGULARITY', 'Repeat task each:');
  define('TEXT_INFO_TIME_UNIT', 'Unit:');
  define('TEXT_INFO_TIME_OFFSET', 'Start time:');

  define('TEXT_TIME_MINUTE', 'Minute(s)');
  define('TEXT_TIME_HOUR', 'Hour(s)');
  define('TEXT_TIME_DAY', 'Day(s)');
  define('TEXT_TIME_WEEK', 'Week(s)');

  define('TEXT_INFO_ONETIME', 'Will be executed once');
  define('TEXT_INFO_INTERVALL', 'Starts at %s, repeats every %s');
  define('TEXT_DISPLAY_NUMBER_OF_SCHEDULED_TASKS', 'Displayed are %d to %d (of a total of %d tasks)');

  define('TEXT_HEADING_TASKS_STATUS_BANNERS', 'Banners Status');
  define('TEXT_INFO_TASKS_STATUS_BANNERS', 'Banners are checked for validity');

  define('TEXT_HEADING_TASKS_STATUS_SPECIALS', 'Specials Status');
  define('TEXT_INFO_TASKS_STATUS_SPECIALS', 'Specials are checked for validity');

  define('TEXT_HEADING_TASKS_DB_MAINTENANCE', 'Database optimize');
  define('TEXT_INFO_TASKS_DB_MAINTENANCE', 'The database is analyzed and optimized');

  define('TEXT_HEADING_TASKS_DB_BACKUP', 'Database backup');
  define('TEXT_INFO_TASKS_DB_BACKUP', 'Database backup will be created');

  define('TEXT_HEADING_TASKS_LOGS_MAINTENANCE', 'Logfiles cleanup');
  define('TEXT_INFO_TASKS_LOGS_MAINTENANCE', 'Logfiles older than 7 days are deleted');

  define('TEXT_HEADING_TASKS_EXPORT_SITEMAP', 'Sitemap recreation'); 
  define('TEXT_INFO_TASKS_EXPORT_SITEMAP', 'Sitemap will be recreated. (The export module <a href="'.xtc_href_link(FILENAME_MODULE_EXPORT, 'set=export&module=sitemaporg').'"><b>XML Sitemap.org</b></a> must be activated.)');

  define('TEXT_HEADING_TASKS_ADMINLOG_MAINTENANCE', 'Admin Log');
  define('TEXT_INFO_TASKS_ADMINLOG_MAINTENANCE', 'Cleanup Admin log');
