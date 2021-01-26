<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  define('MODULE_SEMKNOX_SYSTEM_TEXT_TITLE', 'Site Search 360 Productsearch');
  define('MODULE_SEMKNOX_SYSTEM_TEXT_DESCRIPTION', 'Revolutionary next generation product search for your business with fully automatic data processing.');
  define('MODULE_SEMKNOX_SYSTEM_STATUS_TITLE', 'Module status');
  define('MODULE_SEMKNOX_SYSTEM_STATUS_DESC', 'Activate Site Search 360 Productsearch');
  define('MODULE_SEMKNOX_SYSTEM_DEFAULT_CSS_TITLE', 'Default CSS');
  define('MODULE_SEMKNOX_SYSTEM_DEFAULT_CSS_DESC', 'Do you want to load the default Site Search 360 CSS?');
  define('MODULE_SEMKNOX_SYSTEM_COLOR_TITLE', 'Accent color');
  define('MODULE_SEMKNOX_SYSTEM_COLOR_DESC', 'Specify the accent color for the title and buttons (setting only works if the standard CSS is loaded).');
  
  $languages = xtc_get_languages();
  foreach ($languages as $language) {
    define('MODULE_SEMKNOX_SYSTEM_API_'.$language['id'].'_TITLE', '['.strtoupper($language['code']).'] API Key');
    define('MODULE_SEMKNOX_SYSTEM_API_'.$language['id'].'_DESC', 'API Key f&uuml;r '.strtoupper($language['code']).'');

    define('MODULE_SEMKNOX_SYSTEM_PROJECT_'.$language['id'].'_TITLE', '['.strtoupper($language['code']).'] Project ID');
    define('MODULE_SEMKNOX_SYSTEM_PROJECT_'.$language['id'].'_DESC', 'Project ID f&uuml;r '.strtoupper($language['code']).'');
  }