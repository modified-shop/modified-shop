<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
 	 based on:
	  (c) 2003-2007 xt:Commerce (Winger/Zanier), http://www.xt-commerce.com
	  (c) 2008 Hamburger-Internetdienst, www.forum.hamburger-internetdienst.de

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

include('../../includes/application_top_callback.php');

include(DIR_WS_CLASSES . 'language.php');
$lng = new language(xtc_input_validation($_GET['language'], 'char', ''));
if(!isset($_GET['language'])) {
  $lng->get_browser_language();
}
include(DIR_WS_LANGUAGES.$lng->language['directory'].'/'.$lng->language['directory'].'.php');

require_once(DIR_WS_CLASSES.'paypal_checkout.php');
$o_paypal = new paypal_checkout();
if(is_array($_POST)) {
	$response = $o_paypal->callback_process($_POST,$lng->language['language_charset']);
}
?>