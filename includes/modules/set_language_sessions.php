<?php
/* -----------------------------------------------------------------------------------------
   $Id: set_language_sessions.php 3859 2012-11-08 10:18:16Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
   
$language_not_found = false;

foreach(auto_include(DIR_FS_CATALOG.'includes/extra/modules/set_language_sessions/','php') as $file) require_once ($file); 

if (!isset($_SESSION['language']) || isset($_GET['language']) || (isset($_SESSION['language']) && !isset($_SESSION['language_charset']))) {
  include (DIR_WS_CLASSES.'language.php');
  if (isset($_GET['language'])) {
    $_GET['language'] = xtc_input_validation($_GET['language'], 'char', '');
    $lng = new language($_GET['language']);
  } elseif (isset($_SESSION['language'])) {
    $lng = new language(xtc_input_validation($_SESSION['language'], 'char', ''));
  } else {
    $lng = new language(xtc_input_validation(DEFAULT_LANGUAGE, 'char', ''));
    if (USE_BROWSER_LANGUAGE == 'true') {
      $lng->get_browser_language();
    }
  }
  $_SESSION['language'] = $lng->language['directory'];
  $_SESSION['languages_id'] = $lng->language['id'];
  $_SESSION['language_charset'] = $lng->language['language_charset'];
  $_SESSION['language_code'] = $lng->language['code'];

  if (isset($_GET['language']) && !isset($lng->catalog_languages[$_GET['language']])) {
    $_GET['language'] = DEFAULT_LANGUAGE;
    $language_not_found = true;
  }
}

// set default charset
@ini_set('default_charset', $_SESSION['language_charset']);
