<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


// a language pack without its own file gets the English texts, else the constants stay undefined
function klarna_express_include_language() {
  $language = ((isset($_SESSION['language'])) ? $_SESSION['language'] : 'english');
  if (!is_file(DIR_WS_LANGUAGES.$language.'/modules/payment/klarna_express.php')) {
    $language = 'english';
  }

  include_once(DIR_WS_LANGUAGES.$language.'/modules/payment/klarna_express.php');
}
