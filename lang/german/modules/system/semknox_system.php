<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  define('MODULE_SEMKNOX_SYSTEM_TEXT_TITLE', 'Site Search 360 Produktsuche');
  define('MODULE_SEMKNOX_SYSTEM_TEXT_DESCRIPTION', 'Revolution&auml;re Produktsuche der n&auml;chsten Generation f&uuml;r Ihr Business mit einer voll automatischen Datenaufbereitung.');
  define('MODULE_SEMKNOX_SYSTEM_STATUS_TITLE', 'Modulstatus');
  define('MODULE_SEMKNOX_SYSTEM_STATUS_DESC', 'Site Search 360 Produktsuche aktivieren');
  define('MODULE_SEMKNOX_SYSTEM_DEFAULT_CSS_TITLE', 'Standard CSS');
  define('MODULE_SEMKNOX_SYSTEM_DEFAULT_CSS_DESC', 'Soll das Standard CSS von Site Search 360 geladen werden ?');
  define('MODULE_SEMKNOX_SYSTEM_COLOR_TITLE', 'Akzent Farbe');
  define('MODULE_SEMKNOX_SYSTEM_COLOR_DESC', 'Geben Sie die Akzentfarbe an f&uuml;r Titel und Buttons (Einstellung greift nur wenn das Standard CSS geladen wird).');
  
  $languages = xtc_get_languages();
  foreach ($languages as $language) {
    define('MODULE_SEMKNOX_SYSTEM_API_'.$language['id'].'_TITLE', '['.strtoupper($language['code']).'] API Key');
    define('MODULE_SEMKNOX_SYSTEM_API_'.$language['id'].'_DESC', 'API Key f&uuml;r '.strtoupper($language['code']).'');

    define('MODULE_SEMKNOX_SYSTEM_PROJECT_'.$language['id'].'_TITLE', '['.strtoupper($language['code']).'] Project ID');
    define('MODULE_SEMKNOX_SYSTEM_PROJECT_'.$language['id'].'_DESC', 'Project ID f&uuml;r '.strtoupper($language['code']).'');
  }