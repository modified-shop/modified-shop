<?php

/* --------------------------------------------------------------
   $Id: functions.php

   PUBLIC FUNCTIONS FOR THE SAFETERMS MODULE

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


function set_module_configuration($key, $value) {
	$module_query = xtc_db_query("SELECT configuration_value as lastvalid FROM ".TABLE_CONFIGURATION." WHERE configuration_key='".$key."'");
	if (xtc_db_num_rows($module_query)<1) {
		// INSERT
		$module_query = xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." VALUES ('','".$key."','".$value."',0,NULL,NULL,'".time()."',NULL,NULL)");
	} else {
		// UPDATE
		$module_query = xtc_db_query("UPDATE ".TABLE_CONFIGURATION." SET configuration_value='".$value."' WHERE configuration_key='".$key."' LIMIT 1");
	}
}

function read_configuration($key) {
	$module_query = xtc_db_query("SELECT configuration_value as confvalue FROM ".TABLE_CONFIGURATION." WHERE configuration_key='".$key."'");
	if (xtc_db_num_rows($module_query)>0) {
		// Exists giveback
		$value = xtc_db_fetch_array($module_query);
		return $value["confvalue"];
	} else {
		// UPDATE
		return "";
	}
}
function read_mconfiguration($key) {
	$module_query = xtc_db_query("SELECT configuration_key as confkey, configuration_value as confvalue FROM ".TABLE_CONFIGURATION." WHERE configuration_key like '".$key."%'");
	if (xtc_db_num_rows($module_query)>0) {
		// Exists giveback
		$value = array();
		while ($temp = xtc_db_fetch_array($module_query)) {
			$value[str_replace($key,"",$temp["confkey"])] = $temp["confvalue"];
		}
		return $value;
	} else {
		// UPDATE
		return array();
	}
}

	function safeterms_update() {
		// First getting the configuration
		$safeterms_control["API_KEY"] = read_configuration("SAFETERMS_APIKEY");
		$safeterms_control["API_LASTVALID_RESULT"] = read_configuration("SAFETERMS_APIKEY_LASTVALIDRESULT");
		$safeterms_control["API_LASTVALID_TIME"] = read_configuration("SAFETERMS_APIKEY_LASTVALIDTIME");
		$safeterms_control["API_VERSION"] = read_configuration("SAFETERMS_RECHTSTEXT_VERSION");
		$safeterms_control["LANGS"] = read_mconfiguration("SAFETERMS_LANGUAGE_");


		// NOW WE RUN THROUGHT ALL PROVIDED LANGUAGES
		foreach ($safeterms_control["LANGS"] as $language_code => $language_id) {
			
			$update_error=0;
			$update_log = "<p>".date("d.m.Y - H:i:s")."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Starte Update...</p>\n";

			// AGB
			// Create The Request	
			$request = array();
			$request["TYPE"] = "get_agb";
			$request["APIKEY"] = $safeterms_control["API_KEY"];
			$request["LANG"] = $language_code;

			// SEND REQUEST & GETTING RESPONSE
			$response = talkto($request);

			if ($response["STATUS"]=="SUCCESS" && $response["VALID"]=="TRUE") {
				// The Response was Fine ... Import it;
				$agb = str_replace("'","\'",$response["CONTENT"]);
				$module_query = xtc_db_query("UPDATE content_manager SET content_text='".$agb."' WHERE content_group='3' AND languages_id='".$language_id."'");
				$update_log.= "<p>".date("d.m.Y - H:i:s")."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Update AGB [ERFOLGREICH]</p>\n";
			} else {
				$update_error=1;
				$update_log.= "<p>".date("d.m.Y - H:i:s")."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Update AGB [FEHLGESCHLAGEN]</p>\n";
			}

			// IMPRESSUM
			// Create The Request	
			$request = array();
			$request["TYPE"] = "get_impressum";
			$request["APIKEY"] = $safeterms_control["API_KEY"];
			$request["LANG"] = $language_code;

			// SEND REQUEST & GETTING RESPONSE
			$response = talkto($request);


			if ($response["STATUS"]=="SUCCESS" && $response["VALID"]=="TRUE") {
				// The Response was Fine ... Import it;
				$impressum = str_replace("'","\'",$response["CONTENT"]);
				$module_query = xtc_db_query("UPDATE ".TABLE_CONTENT_MANAGER." SET content_text='".$impressum."' WHERE content_group='4' AND languages_id='".$language_id."'");
				$update_log.= "<p>".date("d.m.Y - H:i:s")."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Update IMPRESSUM [ERFOLGREICH]</p>\n";
			} else {
				$update_error=1;
				$update_log.= "<p>".date("d.m.Y - H:i:s")."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Update IMPRESSUM [FEHLGESCHLAGEN]</p>\n";
			} 

			// Datenschutz
			// Create The Request	
			$request = array();
			$request["TYPE"] = "get_datenschutz";
			$request["APIKEY"] = $safeterms_control["API_KEY"];
			$request["LANG"] = $language_code;

			// SEND REQUEST & GETTING RESPONSE
			$response = talkto($request);

			if ($response["STATUS"]=="SUCCESS" && $response["VALID"]=="TRUE") {
				// The Response was Fine ... Import it;
				$datenschutz = str_replace("'","\'",$response["CONTENT"]);
				$module_query = xtc_db_query("UPDATE ".TABLE_CONTENT_MANAGER." SET content_text='".$datenschutz."' WHERE content_group='2' AND languages_id='".$language_id."'");
				$update_log.= "<p>".date("d.m.Y - H:i:s")."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Update DATENSCHUTZ [ERFOLGREICH]</p>\n";
			} else {
				$update_error=1;
				$update_log.= "<p>".date("d.m.Y - H:i:s")."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Update DATENSCHUTZ [FEHLGESCHLAGEN]</p>\n";
			} 


			// Widerrufsrecht
			// Create The Request	
			$request = array();
			$request["TYPE"] = "get_widerruf";
			$request["APIKEY"] = $safeterms_control["API_KEY"];
			$request["LANG"] = $language_code;

			// SEND REQUEST & GETTING RESPONSE
			$response = talkto($request);

			if ($response["STATUS"]=="SUCCESS" && $response["VALID"]=="TRUE") {
				// The Response was Fine ... Import it;
				$widerruf = str_replace("'","\'",$response["CONTENT"]);
				$module_query = xtc_db_query("UPDATE ".TABLE_CONTENT_MANAGER." SET content_text='".$widerruf."' WHERE content_group='9' AND languages_id='".$language_id."'");
				$update_log.= "<p>".date("d.m.Y - H:i:s")."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Update WIDERRUFSBELEHRUNG [ERFOLGREICH]</p>\n";
			} else {
				$update_error=1;
				$update_log.= "<p>".date("d.m.Y - H:i:s")."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Update WIDERRUFSBELEHRUNG [FEHLGESCHLAGEN]</p>\n";
			} 



			// Batterieverordnung
			// Create The Request	
			$request = array();
			$request["TYPE"] = "get_batterie";
			$request["APIKEY"] = $safeterms_control["API_KEY"];
			$request["LANG"] = $language_code;

			// SEND REQUEST & GETTING RESPONSE
			$response = talkto($request);


			if ($response["STATUS"]=="SUCCESS" && $response["VALID"]=="TRUE") {
				// Prüfen ob eine Batterieverordnung bereits existiert
				$batterie = str_replace("'","\'",$response["CONTENT"]);
				$module_query = xtc_db_query("SELECT content_id as batid FROM ".TABLE_CONTENT_MANAGER." WHERE content_group='999' AND languages_id='".$language_id."'");
				
				if (xtc_db_num_rows($module_query)<1) {
					// INSERT
					$module_query = xtc_db_query("INSERT INTO ".TABLE_CONTENT_MANAGER." VALUES ('','0','0','','".$language_id."','Hinweis Batterieverordnung','Hinweis Batterieverordnung','".$batterie."','0','1','','1','999','0','','','')");
				} else {
					// UPDATE
					$batid = xtc_db_fetch_array($module_query);
					$module_query = xtc_db_query("UPDATE ".TABLE_CONTENT_MANAGER." SET content_text='".$batterie."' WHERE content_id='".$batid["batid"]."' LIMIT 1");
				}
				$update_log.= "<p>".date("d.m.Y - H:i:s")."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Update BATTERIEVERORDNUNG [ERFOLGREICH]</p>\n";
			} else {
				$update_error=1;
				$update_log.= "<p>".date("d.m.Y - H:i:s")."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Update BATTERIEVERORDNUNG [FEHLGESCHLAGEN]</p>\n";
			} 


		}

		// Version anpassen wenn kein Fehler aufgetreten ist
		$request["TYPE"] = "get_version";
		$request["APIKEY"] = $safeterms_control["API_KEY"];

		// SEND REQUEST & GETTING RESPONSE
		$response = talkto($request);
		if ($response["STATUS"]=="SUCCESS" && $response["VALID"]=="TRUE") {
			set_module_configuration("SAFETERMS_RECHTSTEXT_VERSION",$response["CONTENT"]);
		}
		

		return array($update_error,$update_log);

	}

?>