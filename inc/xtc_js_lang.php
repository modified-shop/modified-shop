<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_js_lang.php 4203 2013-01-10 20:36:14Z Tomcraft1980 $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2005 XT-Commerce


   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
   
   function xtc_js_lang($message) {
   	
//BOF - Dokuman - 2009-18-12 - replace more entities than just the German ones
/*   	
   	$message = str_replace ("&auml;","%E4", $message );
   	$message = str_replace ("&Auml;","%C4", $message );
   	$message = str_replace ("&ouml;","%F6", $message );
   	$message = str_replace ("&Ouml;","%D6", $message );
   	$message = str_replace ("&uuml;","%FC", $message );
   	$message = str_replace ("&Uuml;","%DC", $message );
*/

   	$replace_array = array('%A1','%A2','%A3','%A4','%A5','%A6','%A6','%A7','%A8','%A8','%A9','%AA','%AB','%AC','%AD','%AE','%AF','%AF','%B0','%B1','%B2','%B3','%B4','%B5','%B6','%B7','%B8','%B9','%BA','%BB','%BC','%BD','%BD','%BE','%BF','%C0','%C1','%C2','%C3','%C4','%C5','%C6','%C7','%C8','%C9','%CA','%CB','%CC','%CD','%CE','%CF','%D0','%D1','%D2','%D3','%D4','%D5','%D6','%D7','%D8','%D9','%DA','%DB','%DC','%DD','%DE','%DF','%E0','%E1','%E2','%E3','%E4','%E5','%E6','%E7','%E8','%E9','%EA','%EB','%EC','%ED','%EE','%EF','%F0','%F1','%F2','%F3','%F4','%F5','%F6','%F7','%F8','%F9','%FA','%FB','%FC','%FD','%FE','%FF');
   	$search_array = array('&iexcl;','&cent;','&pound;','&curren;','&yen;','&brkbar;','&brvbar;','&sect;','&die;','&uml;','&copy;','&ordf;','&laquo;','&not;','&shy;','&reg;','&hibar;','&macr;','&deg;','&plusmn;','&sup2;','&sup3;','&acute;','&micro;','&para;','&middot;','&cidil;','&sup1;','&ordm;','&raquo;','&frac14;','&half;','&frac12;','&frac34;','&iquest;','&Agrave;','&Aacute;','&Acirc;','&Atilde;','&Auml;','&Aring;','&AElig;','&Ccedil;','&Egrave;','&Eacute;','&Ecirc;','&Euml;','&Igrave;','&Iacute;','&Icirc;','&Iuml;','&ETH;','&Ntilde;','&Ograve;','&Oacute;','&Ocirc;','&Otilde;','&Ouml;','&times;','&Oslash;','&Ugrave;','&Uacute;','&Ucirc;','&Uuml;','&Yacute;','&THORN;','&szlig;','&agrave;','&aacute;','&acirc;','&atilde;','&auml;','&aring;','&aelig;','&ccedil;','&egrave;','&eacute;','&ecirc;','&euml;','&igrave;','&iacute;','&icirc;','&iuml;','&eth;','&ntilde;','&ograve;','&oacute;','&ocirc;','&otilde;','&ouml;','&divide;','&oslash;','&ugrave;','&uacute;','&ucirc;','&uuml;','&yacute;','&thorn;','&yuml;');  	
   	$message=str_replace($search_array,$replace_array,$message);
   	   	
   	return $message;
   	
   }
   
   
?>
