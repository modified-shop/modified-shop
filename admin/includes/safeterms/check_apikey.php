<?php

/* --------------------------------------------------------------
   $Id: check_apikey.php

   Checks the Provided APIkey with Safeterms.de Server

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


// Check an exisisting APIKEY
$apikey = $safeterms_control["API_KEY"];

	// Livecheck set to 0 to perfom no check
	$perfom_livecheck=0;

	// precheck via DB to reduce datatransfers
	$module_query = xtc_db_query("SELECT configuration_value as lastvalid FROM ".TABLE_CONFIGURATION." WHERE configuration_key='SAFETERMS_APIKEY_LASTVALIDTIME'");
	if (xtc_db_num_rows($module_query)<1) {
		// Nothinf stored in DB So a Live Check is required
		$perfom_livecheck = 1;
	} else {
		// API KEY INSTALLED SHOWING JUST INFO AND CONF
		$lastcheck = xtc_db_fetch_array($module_query);

		if ($lastcheck["lastvalid"]<time()-30) {
			// Lastcheck is older than 24h so perfom livecheck
			$perfom_livecheck=1;
		} else {
			// Lastcheck is not older than 24h so fetching the result
			@$safeterms_control["API_LASTVALID_TIME"] = $lastcheck["lastvalid"];
			$module_query = xtc_db_query("SELECT configuration_value as lastvalid FROM ".TABLE_CONFIGURATION." WHERE configuration_key='SAFETERMS_APIKEY_LASTVALIDRESULT'");
			if (xtc_db_num_rows($module_query)<1) {
				// NO RESULT FOUND IN DB SO PERFORM LIVE CHECK
				$perfom_livecheck = 1;
			} else {
				// RESULT FOUND, NO LIVECHECK IS REQUIERED
				$lastcheck = xtc_db_fetch_array($module_query);
				@$safeterms_control["API_LASTVALID_RESULT"] = $lastcheck["lastvalid"];
			}
		}
	}

	// Check a livecheck is required;
	@$safeterms_control["LIVECHECK_RESULT"] = $perfom_livecheck;

	if ($perfom_livecheck==1) {
		// Perfom livecheck
	
			// Create The Request	
			$request = array();
			$request["TYPE"] = "check_apikey";
			$request["APIKEY"] = $safeterms_control["API_KEY"];

			// SEND REQUEST & GETTING RESPONSE
			$response = talkto($request);

		// IF Request SUCCESS
		if ($response["STATUS"] == "SUCCESS" && @!$nosave) {
			// UPDATE THE DATABASE
				// TIMESTAMP
				$module_query = xtc_db_query("SELECT configuration_value as lastvalid FROM ".TABLE_CONFIGURATION." WHERE configuration_key='SAFETERMS_APIKEY_LASTVALIDTIME'");
				if (xtc_db_num_rows($module_query)<1) {
					// INSERT QUERY
					$module_query = xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." VALUES ('','SAFETERMS_APIKEY_LASTVALIDTIME','".time()."',0,NULL,NULL,'".time()."',NULL,NULL)");	
				} else {
					// UPDATE QUERY
					$module_query = xtc_db_query("UPDATE ".TABLE_CONFIGURATION." SET configuration_value='".time()."' WHERE configuration_key='SAFETERMS_APIKEY_LASTVALIDTIME' LIMIT 1");	
				} 
				// RESULT
				$module_query = xtc_db_query("SELECT configuration_value as lastvalid FROM ".TABLE_CONFIGURATION." WHERE configuration_key='SAFETERMS_APIKEY_LASTVALIDRESULT'");
				if (xtc_db_num_rows($module_query)<1) {
					// INSERT QUERY
					$module_query = xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." VALUES ('','SAFETERMS_APIKEY_LASTVALIDRESULT','".$response["VALID"]."',0,NULL,NULL,'".time()."',NULL,NULL)");	
				} else {
					// UPDATE QUERY
					$module_query = xtc_db_query("UPDATE ".TABLE_CONFIGURATION." SET configuration_value='".$response["VALID"]."' WHERE configuration_key='SAFETERMS_APIKEY_LASTVALIDTIME' LIMIT 1");	
				} 
			@$safeterms_control["API_LASTVALID_RESULT"] = $response["VALID"];
			@$safeterms_control["API_LASTVALID_TIME"] = time();
		} elseif ($response["STATUS"] == "SUCCESS" && $nosave==1) {
			@$safeterms_control["API_LASTVALID_RESULT"] = $response["VALID"];
			@$safeterms_control["API_LASTVALID_TIME"] = time();
		} 

	}

?>