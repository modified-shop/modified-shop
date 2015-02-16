<?php

/* --------------------------------------------------------------
   $Id: overview.php

   The Overview GUI for the installed Module

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommercecoding standards (a typical file) www.oscommerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/

	$safeterms_control["VERSION"] =  read_configuration("SAFETERMS_RECHTSTEXT_VERSION");

	// GETTING THE SERVER-SIDE VERSION OF TEXTES
	if ($safeterms_control["API_LASTVALID_RESULT"] == "TRUE") { 
		$request = array();
		$request["TYPE"] = "get_version";
		$request["APIKEY"] = $safeterms_control["API_KEY"];

		// SEND REQUEST & GETTING RESPONSE
		$response = talkto($request);

		// IF Request SUCCESS
		if ($response["STATUS"] == "SUCCESS" && $response["VALID"]=="TRUE") {
			$safeterms_control["SERVER_VERSION"] = $response["CONTENT"];
		}

		$shop_status='<p style="text-align: center; margin-top:20px;">Letzter Stand der Rechtstexte im Online Shop:</p>';

		if ($safeterms_control["VERSION"]==0) {
			$shop_status.= '<p style="text-align: center; margin:10px; font-size:20px; line-height:30px; background-color:ff0000;">bisher NOCH NIE</p>';
			$shop_update = '<p style="text-align: justify; margin:10px; margin-top:20px;">Es wird empfohlen jetzt ein Update durchzuf&uuml;hren. Dazu klicken Sie einfach auf den Button &quot;Update Starten&quot;.</p>';
		}
		if ($safeterms_control["VERSION"]<>0 && $safeterms_control["VERSION"]<$safeterms_control["SERVER_VERSION"]) {
			$shop_status.= '<p style="text-align: center; margin:10px; font-size:20px; line-height:30px; background-color:ff0000;">'.date("d.m.Y - H:i",$safeterms_control["VERSION"]).'</p>';
			$shop_update = '<p style="text-align: justify; margin:10px; margin-top:20px;font-weight:700">Ihre Rechtstexte wurden bei Safeterms.de Aktualisiert! Es wird ein neues Update empfohlen.</p>';
		}
		if ($safeterms_control["VERSION"]<>0 && $safeterms_control["VERSION"]==$safeterms_control["SERVER_VERSION"]) {
			$shop_status.= '<p style="text-align: center; margin:10px; font-size:20px; line-height:30px; background-color:green;">'.date("d.m.Y - H:i",$safeterms_control["VERSION"]).'</p>';
			$shop_update = '<p style="text-align: justify; margin:10px; margin-top:20px;">Ihre Rechtstexte sind auf dem gleichen Stand wie bei Safeterms.de. Sollten Sie Ihre Rechtstexte versehntlich ge&auml;ndert haben, dann f&uuml;hren Sie einfach ein neues Update aus.</p>';
		}
		$shop_update.= xtc_draw_form('safeterms_update', FILENAME_SAFETERMS, '', 'post') .'<input type="hidden" name="action" value="start_update" /><input type="submit" value="Update Starten" style="width:300px; height:40px;margin-left:50px; margin-top:15px; font-size:22px; background-color:#dedede; cursor:pointer;"; /></form></p>';
		$shop_status.= $shop_update;
	} else {
		$shop_status = '			<p>Sie sind bereits Kunde bei Safeterms.de. Dann richten Sie jetzt das Auto-Update f&uuml;r Rechtstexte von Safeterms f&uuml;r Ihre modified eCommerce Shopsoftware ein.</p>
			<p>Um die Einrichtung Ihrer Rechtstexte zu starten geben Sie bitte im folgenden Feld Ihren <strong>API Sicherheitsschl&uuml;ssel</strong> ein. Diesen erhalten Sie in Ihrem pers&ouml;nlichen Bereich auf Safeterms.de</p>
			<p style="margin-top:20px;">Ihr API-Sicherheitsschl&uuml;ssel:</p>
			'.xtc_draw_form('auto_update', FILENAME_SAFETERMS, '', 'post').'
			<input type="hidden" name="action" value="new_apikey" />
			<p><input type="text" name="apikey" value="" /></p>
			<p><input type="submit" style="background-color:#dedede; cursor:pointer" value="Einrichtung starten" /></p>
			</form>';
	}
	// GETTING API-KEY INFO
	
		$request = array();
		$request["TYPE"] = "get_information";
		$request["APIKEY"] = $safeterms_control["API_KEY"];

		// SEND REQUEST & GETTING RESPONSE
		$response = talkto($request);

		$apikeyinfo = $response;
			
?>
<div id="main">
	<div id="logo_safeterms">
		<img src="includes/safeterms/images/logo.png" />
	</div>
	<div id="box">
		<div id="content_title">
			Status Ihrer Rechtstexte im Shop
		</div>
		<div id="offer_title">
			Status Ihres API Sicherheitsschl&uuml;ssel
		</div>
		<div id="content">
			<?php echo $shop_status; ?>
		</div>
		<div id="offers" onclick="window.open('http://www.safeterms.de/');">
			<p>API-Sicherheitsschl&uuml;ssel:</p>
			<p style="font-weight:700"><?php echo $safeterms_control["API_KEY"]; ?></p>
			<p>Status:</p>
			<p style="font-weight:700"><?php if ($safeterms_control["API_LASTVALID_RESULT"]=="TRUE") {echo "G&uuml;ltig";} else {echo "ung&uuml;ltig";}  ?></p>
			<p>Konfiguriert zur Nutzung f&uuml;r:</p>
			<p style="font-weight:700"><?php if ($apikeyinfo["VALID"]=="TRUE") {echo $apikeyinfo["TRADERCOMPANYNAME"]."<br />".$apikeyinfo["TRADERCOMPANYLEADER"]."<br />".$apikeyinfo["SHOPNAME"]."<br />".$apikeyinfo["SHOPURL"];} else {echo "ung&uuml;ltig";}  ?></p>
			<div style="clear:both;"></div>
		</div>
		<div style="clear:both;"></div>
	</div>
</div>