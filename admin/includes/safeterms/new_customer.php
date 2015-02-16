<?php

/* --------------------------------------------------------------
   $Id: new_customer.php

   This File is standard that will be displayed if NO API KEY is installed

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommercecoding standards (a typical file) www.oscommerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/
?>
<div id="main">
	<div id="logo_safeterms">
		<img src="includes/safeterms/images/logo.png" />
	</div>
	<div id="box">
		<div id="content_title">
			Safeterms f&uuml;r modified eCommerce Shopsoftware einrichten...
		</div>
		<div id="offer_title">
			Safeterms Kunde werden...
		</div>
		<div id="content">
			<p>Sie sind bereits Kunde bei Safeterms.de. Dann richten Sie jetzt das Auto-Update f&uuml;r Rechtstexte von Safeterms f&uuml;r Ihre modified eCommerce Shopsoftware ein.</p>
			<p>Um die Einrichtung Ihrer Rechtstexte zu starten geben Sie bitte im folgenden Feld Ihren <strong>API Sicherheitsschl&uuml;ssel</strong> ein. Diesen erhalten Sie in Ihrem pers&ouml;nlichen Bereich auf Safeterms.de</p>
			<p style="margin-top:20px;">Ihr API-Sicherheitsschl&uuml;ssel:</p>
			<?php echo xtc_draw_form('customer', FILENAME_SAFETERMS, '', 'post'); ?>
			<input type="hidden" name="action" value="new_apikey" />
			<p><input type="text" name="apikey" value="" /></p>
			<p><input type="submit" style="background-color:#dedede; cursor:pointer" value="Einrichtung starten" /></p>
			</form>
		</div>
		<div id="offers" onclick="window.open('http://www.safeterms.de/track.php?pid=37');">
			<p style="text-align:center;">
				<img src="http://www.safeterms.de/images/public/safeterms_se_120x120.png" style="margin:2px;" />
			</p>
			<p><strong>Gehen Sie auf Nummer sicher!</strong></p>
			<p>Bei Safeterms.de erhalten Sie abmahnsichere Rechtstexte f&uuml;r Ihren Online Shop, welche Sie pers&ouml;nlich auf sich zuschneiden k&ouml;nnen. <strong>UND DAS AB 9,90 &euro;</strong></p>
			<div style="clear:both;"></div>
		</div>
		<div style="clear:both;"></div>
	</div>
</div>