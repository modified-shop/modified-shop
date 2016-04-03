<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_browser.inc.php 899 2005-04-29 02:40:57Z hhgag $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   TrackPro v1.0 Web Traffic Analyzer 
   Copyright (C) 2004 Curve2 Design www.curve2.com
 
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
function browser($browser) {
	switch($browser) {
		case 'MSIE': $browser = 'images/icons/icons_browser/iexplore.jpg'; break;
		case 'Netscape': $browser = 'images/icons/icons_browser/netscape.jpg'; break;
		case 'Opera': $browser = 'images/icons/icons_browser/opera.jpg'; break;
		case 'Mozilla': $browser = 'images/icons/icons_browser/mozilla.jpg'; break;
		case 'Safari': $browser = 'images/icons/icons_browser/safari.jpg'; break;
		case 'Firefox': $browser = 'images/icons/icons_browser/firefox.jpg'; break;
		case 'Firebird': $browser = 'images/icons/icons_browser/firebird.jpg'; break;
		case 'AOL': $browser = 'images/icons/icons_browser/aol.jpg'; break;
		case 'Unknown': $browser = 'images/icons/icons_browser/unknown.jpg'; break;
		case 'Konqueror': $browser = 'images/icons/icons_browser/konqueror.jpg'; break;
		case 'Camino': $browser = 'images/icons/icons_browser/camino.jpg'; break;
		case 'Thunderbird': $browser = 'images/icons/icons_browser/thunderbird.jpg'; break;
		case 'Mac': $browser = 'images/icons/icons_browser/mac.jpg'; break;
		case 'AvantGo': $browser = 'images/icons/icons_browser/avantgo.jpg'; break;
		case 'Nautilus': $browser = 'images/icons/icons_browser/nautilus.jpg'; break; 
		case 'Avant Browser': $browser = 'images/icons/icons_browser/avant.jpg'; break; 
		default: $browser = 'images/icons/icons_browser/no_icon.jpg'; break;
	}

	if(trim($browser) == 'images/icons/') { $browser = 'images/icons/icons_browser/unknown.jpg'; }
	
	//echo "BROWSER: $browser<br />"; // TEST
	return $browser;
}
?>