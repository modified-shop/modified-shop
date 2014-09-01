<?php
/* -----------------------------------------------------------------------------------------
   $Id: set_language_sessions.php 3859 2012-11-08 10:18:16Z web28 $

   Modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 Modified
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
   
if (!isset($_SESSION['language']) || isset($_GET['language']) || (isset($_SESSION['language']) && !isset($_SESSION['language_charset']))) {
  include (DIR_WS_CLASSES.'language.php');
  if (isset($_GET['language'])) {
    $_GET['language'] = xtc_input_validation($_GET['language'], 'char', '');
    $lng = new language($_GET['language']);
  } elseif (isset($_SESSION['language'])) {
    $lng = new language(xtc_input_validation($_SESSION['language'], 'char', ''));
  } else {
    $lng = new language(xtc_input_validation(DEFAULT_LANGUAGE, 'char', ''));
    $lng->get_browser_language();
  }
  $_SESSION['language'] = $lng->language['directory'];
  $_SESSION['languages_id'] = $lng->language['id'];
  $_SESSION['language_charset'] = $lng->language['language_charset'];
  $_SESSION['language_code'] = $lng->language['code'];
}