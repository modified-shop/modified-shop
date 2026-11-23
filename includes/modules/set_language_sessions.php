<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
   
$language_not_found = false;

// the admin used another language for its last page, the session gets the language of the shop back;
// read by its id, because directory and charset may have been changed in the admin since
if (isset($_SESSION['language_shop'])) {
  $language_shop_query = xtc_db_query("SELECT languages_id,
                                              code,
                                              directory,
                                              language_charset
                                         FROM ".TABLE_LANGUAGES."
                                        WHERE languages_id = '".(int)$_SESSION['language_shop']."'");
  if ($language_shop = xtc_db_fetch_array($language_shop_query)) {
    $_SESSION['language'] = $language_shop['directory'];
    $_SESSION['languages_id'] = $language_shop['languages_id'];
    $_SESSION['language_charset'] = $language_shop['language_charset'];
    $_SESSION['language_code'] = $language_shop['code'];
  } else {
    // deleted in the meantime, the language is chosen again below
    unset($_SESSION['languages_id']);
  }
  unset($_SESSION['language_shop']);
}

foreach(auto_include(DIR_FS_CATALOG.'includes/extra/modules/set_language_sessions/','php') as $file) require_once ($file);

// an open session can still hold a language that has been deleted since
if (!isset($_GET['language']) && isset($_SESSION['languages_id'])) {
  $check_language_query = xtDBquery("SELECT languages_id
                                       FROM ".TABLE_LANGUAGES."
                                      WHERE languages_id = '".(int)$_SESSION['languages_id']."'");
  if (xtc_db_num_rows($check_language_query, true) < 1) {
    unset($_SESSION['languages_id']);
  }
}

if (isset($_GET['language'])
    || !isset($_SESSION['language'])
    || !isset($_SESSION['languages_id'])
    || !isset($_SESSION['language_charset'])
    || !isset($_SESSION['language_code'])
    )
{
  require_once (DIR_WS_CLASSES.'language.php');
  
  if (isset($_GET['language'])) {
    $_GET['language'] = xtc_input_validation($_GET['language'], 'lang');
    $lng = new language($_GET['language']);
  } else {
    $lng = new language(DEFAULT_LANGUAGE);
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
set_session_charset();
