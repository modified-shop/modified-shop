<?php

/* --------------------------------------------------------------
   $Id: first_vaild.php

   This File contains the infos about the API Key and provies the saving feature

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommercecoding standards (a typical file) www.oscommerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/


	// The Validation of the API Key Success, so we request more infos
	// About the Key

	// Create The Request	
	$request = array();
	$request["TYPE"] = "get_information";
	$request["APIKEY"] = $safeterms_control["API_KEY"];
	
	// SEND REQUEST & GETTING RESPONSE
	$response = talkto($request);

	// Sprachen des shops abfragen
	$module_query = xtc_db_query("SELECT languages_id, name, code FROM ".TABLE_LANGUAGES." WHERE status='1'");
	$languages="";
	while ($langs = xtc_db_fetch_array($module_query)) {
		$languages.= '<input type="checkbox" name="lang['.$langs['languages_id'].']" value="'.$langs['code'].'" style="margin-right:10px;" checked />'.$langs['name'].' ['.$langs['code'].']<br />';
	}
?>

<div id="main">
	<div id="logo_safeterms">
		<img src="includes/safeterms/images/logo.png" />
	</div>
	<div id="box">
		<div id="content_title_full">
			Safeterms UPDATE einrichten (API-Key: <?php echo $safeterms_control["API_KEY"]; ?>)
		</div>
		<div id="content_full">
			<p>Der angegebene <strong>API Sicherheitsschl&uuml;ssel ist g&uuml;ltig</strong></p>
			<p>Die Rechtstexte sind konfiguriert und d&uuml;rfen f&uuml;r folgenden Shop genutzt werden:</p>
				<p style="margin-top:15px;margin-left:15px;"><strong><?php echo $response["TRADERCOMPANYNAME"]; ?></strong> <?php echo $response["TRADERCOMPANYTYPE"]; ?></p>
				<p style="margin-left:15px;">Vertreten durch: <?php echo $response["TRADERCOMPANYLEADER"]; ?></p>
				<p style="margin-left:15px;"><?php echo $response["TRADERCOMPANYADDRESSA"]; ?></p>
				<p style="margin-left:15px;"><?php echo $response["TRADERCOMPANYADDRESSB"]; ?></p>
				<p style="margin-left:15px;"><?php echo $response["TRADERCOMPANYPOSTALCODE"]; ?> <?php echo $response["TRADERCOMPANYCITY"]; ?></p>
				<p style="margin-left:15px;"><?php echo $response["TRADERCOMPANYCOUNTRY"]; ?></p>
				<p style="margin-left:15px; margin-top:10px;">Shop-Name: <?php echo $response["SHOPNAME"]; ?></p>
				<p style="margin-left:15px; margin-bottom:15px;">Shop-Adresse: <strong><?php echo $response["SHOPURL"]; ?></strong></p>
				<p style="margin-left:15px; margin-bottom:5px;">Bitte w&auml;hlen Sie aus, f&uuml;r welche Sprachprofile die Rechtstexte importiert werden sollen:</p>
				<?php echo xtc_draw_form('safeterms', FILENAME_SAFETERMS, '', 'post'); ?>
				<p style="margin-left:25px; margin-bottom:20px;"><?php echo $languages; ?></p>
			<p>Klicken Sie auf &quot;Rechtstexte einbinden&quot; um die Rechtstexte <strong>JETZT in Ihren Online Shop</strong> einzupflegen.</p>
			<p style="margin-top:10px;margin-left:15px;"><strong>Hinweis: </strong><br /><small>Mit dem Klick auf &quot;Rechtstexte einbinden&quot; best&auml;tigen Sie, dass Sie die Rechtstexte in Ihrem Online-Shop nutzen d&uuml;rfen.</small></p>
			<p style="margin-top:10px;margin-left:15px;margin-bottom:10px;"><strong>ACHTUNG: </strong><br /><small>Mit dem Klick auf &quot;Rechtstexte einbinden&quot; gehen Ihre bisherigen Vorhandenen Rechtstexte auf den Content-Seiten &quot;AGB, Impressum, Datenschutz &amp; Widerrufsbelehrung&quot; verloren!</p>			
		</div>
			<input type="hidden" name="action" value="set_apikey" />
			<input type="hidden" name="apikey" value="<?php echo $safeterms_control["API_KEY"]; ?>" />
			<p><input type="button" style="background-color:#dedede; cursor:pointer; float:left; width:200px; margin-bottom:5px; font-weight: 400; font-size:16px;" value="Zur&uuml;ck" onclick="location.href='safeterms.php'" /> <input type="submit" style="background-color:#dedede; cursor:pointer; float:right; width:400px; margin-bottom:5px; font-weight: 700;" value="Rechtstexte einbinden" /></p>
			</form>
			
		<div style="clear:both;"></div>
	</div>
</div>