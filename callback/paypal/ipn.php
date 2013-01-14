<?php
/**
 * Project:           xt:Commerce - eCommerce Engine
 * @version $Id
 *
 * xt:Commerce - Shopsoftware
 * (c) 2003-2007 xt:Commerce (Winger/Zanier), http://www.xt-commerce.com
 *
 * xt:Commerce ist eine geschŸtzte Handelsmarke und wird vertreten durch die xt:Commerce GmbH (Austria)
 * xt:Commerce is a protected trademark and represented by the xt:Commerce GmbH (Austria)
 *
 * @copyright Copyright 2003-2007 xt:Commerce (Winger/Zanier), www.xt-commerce.com
 * @license http://www.xt-commerce.com.com/license/2_0.txt GNU Public License V2.0
 *
 * For questions, help, comments, discussion, etc., please join the
 * xt:Commerce Support Forums at www.xt-commerce.com
 *
 * ab 15.08.2008 Teile vom Hamburger-Internetdienst geändert
 * Hamburger-Internetdienst Support Forums at www.forum.hamburger-internetdienst.de
 * Stand 29.04.2009
*/
include('../../includes/application_top_callback.php');
include(DIR_WS_CLASSES . 'language.php');
$lng = new language(xtc_input_validation($_GET['language'], 'char', ''));
if(!isset($_GET['language']))
  $lng->get_browser_language();
include(DIR_WS_LANGUAGES.$lng->language['directory'].'/'.$lng->language['directory'].'.php');
// nur zum Testen Dateien in ein Verzeichnis root/paypaltest
//foreach ($_POST as $key => $value) {
//    $text.= "Schlüssel: $key; Wert: $value\n";
//}
//$file='paypal_'.date('d.m.Y-H.i.s').'.txt';
//$fp = fopen('../../paypaltest/' . $file, "a");
//fwrite($fp, $text);
//fclose($fp);
// testen ende
require_once('../../includes/classes/paypal_checkout.php');
$o_paypal = new paypal_checkout();
if(is_array($_POST)) {
	$response = $o_paypal->callback_process($_POST,$lng->language['language_charset']);
}
?>