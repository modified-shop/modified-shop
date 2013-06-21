<?php

/* --------------------------------------------------------------
   $Id: module_status.php

   File to check the Status of the Safeterms.de Module

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommercecoding standards (a typical file) www.oscommerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/
  //VERSION


// Query the Config for an existing Safeterms API Key
$module_query = xtc_db_query("SELECT configuration_value as apikey FROM ".TABLE_CONFIGURATION." WHERE configuration_key='SAFETERMS_APIKEY'");
if (xtc_db_num_rows($module_query)<1) {
	// NO API KEY INSTALLED SHOWING THE WELCOME WITH START CONF AND OFFERS
	@$safeterms_control["API_INSTALLED"] = 0;
} else {
	// API KEY INSTALLED SHOWING JUST INFO AND CONF
	$apikey = xtc_db_fetch_array($module_query);

	@$safeterms_control["API_INSTALLED"] = 1;
	@$safeterms_control["API_KEY"] = $apikey["apikey"];
}

?>