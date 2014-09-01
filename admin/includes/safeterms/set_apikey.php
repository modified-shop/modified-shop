<?php

/* --------------------------------------------------------------
   $Id: set_apikey.php

   This File saves the configuration for the module

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommercecoding standards (a typical file) www.oscommerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/


	// The Validation of the API Key Success, and the admin wants to save the key
	// and getting an Update!
	

	// First setting the configuration Params
	set_module_configuration("SAFETERMS_APIKEY",$safeterms_control["API_KEY"]);
	set_module_configuration("SAFETERMS_APIKEY_LASTVALIDRESULT",$safeterms_control["API_LASTVALID_RESULT"]);
	set_module_configuration("SAFETERMS_APIKEY_LASTVALIDTIME",$safeterms_control["API_LASTVALID_TIME"]);
	set_module_configuration("SAFETERMS_RECHTSTEXT_VERSION","0");
	foreach ($safeterms_control["LANGS"] as $language_id => $language_code) {
		set_module_configuration("SAFETERMS_LANGUAGE_".strtoupper($language_code),$language_id);
	}

	
?>
<div id="main">
	<div id="logo_safeterms">
		<img src="includes/safeterms/images/logo.png" />
	</div>
	<div id="box">
		<div id="content_full">
			<p style="margin:50px;text-align:center; font-size:20px;"><strong>API Sicherheitsschl&uuml;ssel wurde gespeichert<br /><br /><a href="safeterms.php" style="font-size:26px;">Weiter zum Rechtstexte Update &gt;&gt;&gt;</a></strong></p>
		</div>
		<div style="clear:both;"></div>
	</div>
</div>